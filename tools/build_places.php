<?php
// Build site/data/places/ from GeoNames (https://download.geonames.org/export/dump/, CC BY 4.0):
//   cities1000.txt (every place with 1,000 or more people) and admin1CodesASCII.txt (states, provinces, regions).
//   islands.txt: the islands (feature class T, codes ISL, ISLS, ATOL) taken from allCountries.txt, e.g.
//   unzip -p allCountries.zip | awk -F'\t' '$7=="T" && ($8=="ISL" || $8=="ISLS" || $8=="ATOL")' > islands.txt
//   Only islands known in 5 or more names count (175,000 islands, most of them rocks called Long Island); they weigh
//   20,000 per name, up to 1,000,000, so Tenerife the island beats Tenerife the small town.
// Usage: php tools/build_places.php path/to/cities1000.txt path/to/admin1CodesASCII.txt path/to/islands.txt
// Output: 256 shard files, key (lower case name) => up to 5 places "Name|CC|Zone|Weight", best first.
// Order: places whose own name matches (cities by population; regions by half the population of their biggest
// city, so Washington is the city and Ontario the province), then places that only have it as an alternate name.
// Keys are normalised like place_lookup(): lower case, '_' and '-' to space, '.' removed, spaces collapsed.

[$src, $adm, $isl] = [$argv[1] ?? null, $argv[2] ?? null, $argv[3] ?? null];
if (!$src || !$adm || !$isl) exit("Usage: php tools/build_places.php cities1000.txt admin1CodesASCII.txt islands.txt\n");
$out = __DIR__ . '/../site/data/places';
if (!is_dir($out)) mkdir($out, 0777, true);
foreach (glob("$out/*.php") as $f) unlink($f);

function place_key(string $s): string {
    $s = mb_strtolower($s, 'UTF-8');
    $s = str_replace(['_', '-', '.'], [' ', ' ', ''], $s);
    return trim(preg_replace('/\s+/u', ' ', $s));
}

$valid = array_flip(DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC));
$index = [];   // key => [[rank, weight, entry], ...]  rank 1 = own name, 0 = alternate name
$biggest = []; // "CC.admin1" => [population, zone] of the region's biggest place
$add = function (string $k, int $rank, int $w, string $entry) use (&$index) {
    if ($k !== '' && strlen($k) <= 64) $index[$k][] = [$rank, $w, $entry];
};

$fh = fopen($src, 'r');
while (($line = fgets($fh)) !== false) {
    $c = explode("\t", rtrim($line, "\r\n"));
    if (count($c) < 18) continue;
    [$name, $ascii, $alts, $cc, $a1, $pop, $zone] = [$c[1], $c[2], $c[3], $c[8], $c[10], (int)$c[14], $c[17]];
    if (!isset($valid[$zone])) continue;
    if ($pop > ($biggest["$cc.$a1"][0] ?? -1)) $biggest["$cc.$a1"] = [$pop, $zone];
    $entry = str_replace('|', ' ', $name) . "|$cc|$zone|$pop";
    $own = array_unique([place_key($name), place_key($ascii)]);
    foreach ($own as $k) $add($k, 1, $pop, $entry);
    $altKeys = [];
    foreach (explode(',', $alts) as $a) {
        $a = trim($a);
        if ($a === '' || mb_strlen($a) < 2 || preg_match('/\d|http|[\/@]/', $a)) continue;
        if (preg_match('/^[A-Z]{2,5}$/', $a)) continue;   // codes (airport, postal) rather than names
        $altKeys[place_key($a)] = true;
    }
    foreach (array_diff(array_keys($altKeys), $own) as $k) $add($k, 0, $pop, $entry);
}
fclose($fh);

$fh = fopen($adm, 'r');
while (($line = fgets($fh)) !== false) {
    $c = explode("\t", rtrim($line, "\r\n"));
    if (count($c) < 3 || !isset($biggest[$c[0]])) continue;
    [$pop, $zone] = $biggest[$c[0]];
    $cc = explode('.', $c[0])[0];
    $entry = str_replace('|', ' ', $c[1]) . "|$cc|$zone|" . intdiv($pop, 2);
    foreach (array_unique([place_key($c[1]), place_key($c[2])]) as $k) $add($k, 1, intdiv($pop, 2), $entry);
}
fclose($fh);

$fh = fopen($isl, 'r');
while (($line = fgets($fh)) !== false) {
    $c = explode("\t", rtrim($line, "\r\n"));
    if (count($c) < 18 || !isset($valid[$c[17]])) continue;
    $alts = array_values(array_filter(array_map('trim', explode(',', $c[3])), fn($a) => $a !== '' && mb_strlen($a) >= 2 && !preg_match('/\d|http|[\/@]/', $a) && !preg_match('/^[A-Z]{2,5}$/', $a)));
    if (count($alts) < 5) continue;
    $w = 20000 * min(count($alts), 50);
    $entry = str_replace('|', ' ', $c[1]) . "|$c[8]|$c[17]|$w";
    $own = array_unique([place_key($c[1]), place_key($c[2])]);
    foreach ($own as $k) $add($k, 1, $w, $entry);
    foreach (array_diff(array_unique(array_map('place_key', $alts)), $own) as $k) $add($k, 0, $w, $entry);
}
fclose($fh);

$shards = [];
foreach ($index as $k => $list) {
    usort($list, fn($a, $b) => [$b[0], $b[1]] <=> [$a[0], $a[1]]);
    $seen = [];
    $keep = [];
    foreach ($list as [, , $e]) {
        [$n, $cc, $z] = explode('|', $e);
        if (isset($seen["$cc|$z"])) continue;   // one entry per country and zone is enough to answer
        $seen["$cc|$z"] = true;
        $keep[] = $e;
        if (count($keep) === 5) break;
    }
    $shards[substr(md5($k), 0, 2)][$k] = implode(';', $keep);
}
$total = 0;
foreach ($shards as $id => $rows) {
    ksort($rows);
    file_put_contents("$out/$id.php", "<?php\n// GeoNames (CC BY 4.0), built by tools/build_places.php\nreturn " . var_export($rows, true) . ";\n");
    $total += count($rows);
}
echo "$total names in " . count($shards) . " shards\n";

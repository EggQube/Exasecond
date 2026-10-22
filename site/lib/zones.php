<?php
// Forgiving time zone input for ?tz= : IANA names in any case, cities, countries (English or Chinese),
// common abbreviations and UTC offsets. Ambiguous input returns the options instead of a guess.
defined('EXA') or exit;

// Abbreviations people actually type. A list means "ambiguous: ask".
const TZ_ABBR = [
    'UTC' => 'UTC', 'GMT' => 'UTC', 'Z' => 'UTC', 'UT' => 'UTC', 'ZULU' => 'UTC',
    'CET' => 'Europe/Paris', 'CEST' => 'Europe/Paris', 'MEZ' => 'Europe/Berlin', 'MESZ' => 'Europe/Berlin',
    'WET' => 'Europe/Lisbon', 'WEST' => 'Europe/Lisbon', 'EET' => 'Europe/Athens', 'EEST' => 'Europe/Athens',
    'BST' => ['Europe/London', 'Asia/Dhaka'], 'MSK' => 'Europe/Moscow',
    'ET' => 'America/New_York', 'EST' => 'America/New_York', 'EDT' => 'America/New_York',
    'CT' => 'America/Chicago', 'CDT' => 'America/Chicago', 'CST' => ['America/Chicago', 'Asia/Shanghai', 'America/Havana'],
    'MT' => 'America/Denver', 'MST' => 'America/Denver', 'MDT' => 'America/Denver',
    'PT' => 'America/Los_Angeles', 'PST' => 'America/Los_Angeles', 'PDT' => 'America/Los_Angeles',
    'AKST' => 'America/Anchorage', 'AKDT' => 'America/Anchorage', 'HST' => 'Pacific/Honolulu',
    'AST' => ['America/Halifax', 'Asia/Riyadh'], 'ADT' => 'America/Halifax', 'NST' => 'America/St_Johns', 'NDT' => 'America/St_Johns',
    'BRT' => 'America/Sao_Paulo', 'ART' => 'America/Argentina/Buenos_Aires',
    'IST' => ['Asia/Kolkata', 'Europe/Dublin', 'Asia/Jerusalem'],
    'PKT' => 'Asia/Karachi', 'NPT' => 'Asia/Kathmandu', 'ICT' => 'Asia/Bangkok', 'WIB' => 'Asia/Jakarta', 'PHT' => 'Asia/Manila',
    'SGT' => 'Asia/Singapore', 'HKT' => 'Asia/Hong_Kong', 'JST' => 'Asia/Tokyo', 'KST' => 'Asia/Seoul',
    'AEST' => 'Australia/Sydney', 'AEDT' => 'Australia/Sydney', 'ACST' => 'Australia/Adelaide', 'ACDT' => 'Australia/Adelaide',
    'AWST' => 'Australia/Perth', 'NZST' => 'Pacific/Auckland', 'NZDT' => 'Pacific/Auckland',
    'SAST' => 'Africa/Johannesburg', 'EAT' => 'Africa/Nairobi', 'WAT' => 'Africa/Lagos', 'CAT' => 'Africa/Maputo',
    'GST' => 'Asia/Dubai',
];

// Cities that are not the name of an IANA zone, in English and Chinese.
const TZ_CITIES = [
    'beijing' => 'Asia/Shanghai', 'peking' => 'Asia/Shanghai', 'shenzhen' => 'Asia/Shanghai', 'guangzhou' => 'Asia/Shanghai',
    'chengdu' => 'Asia/Shanghai', 'chongqing' => 'Asia/Shanghai', 'wuhan' => 'Asia/Shanghai', "xi'an" => 'Asia/Shanghai', 'xian' => 'Asia/Shanghai',
    'hangzhou' => 'Asia/Shanghai', 'nanjing' => 'Asia/Shanghai', 'tianjin' => 'Asia/Shanghai',
    'mumbai' => 'Asia/Kolkata', 'bombay' => 'Asia/Kolkata', 'delhi' => 'Asia/Kolkata', 'new delhi' => 'Asia/Kolkata', 'bangalore' => 'Asia/Kolkata', 'bengaluru' => 'Asia/Kolkata', 'chennai' => 'Asia/Kolkata', 'hyderabad' => 'Asia/Kolkata',
    'washington' => 'America/New_York', 'washington dc' => 'America/New_York', 'boston' => 'America/New_York', 'miami' => 'America/New_York', 'atlanta' => 'America/New_York', 'philadelphia' => 'America/New_York',
    'san francisco' => 'America/Los_Angeles', 'seattle' => 'America/Los_Angeles', 'san diego' => 'America/Los_Angeles', 'las vegas' => 'America/Los_Angeles', 'portland' => 'America/Los_Angeles',
    'houston' => 'America/Chicago', 'dallas' => 'America/Chicago', 'austin' => 'America/Chicago', 'new orleans' => 'America/Chicago',
    'montreal' => 'America/Toronto', 'ottawa' => 'America/Toronto', 'rio' => 'America/Sao_Paulo', 'rio de janeiro' => 'America/Sao_Paulo', 'buenos aires' => 'America/Argentina/Buenos_Aires',
    'bergen' => 'Europe/Oslo', 'trondheim' => 'Europe/Oslo', 'stavanger' => 'Europe/Oslo', 'gothenburg' => 'Europe/Stockholm', 'goteborg' => 'Europe/Stockholm',
    'munich' => 'Europe/Berlin', 'hamburg' => 'Europe/Berlin', 'frankfurt' => 'Europe/Berlin', 'cologne' => 'Europe/Berlin', 'milan' => 'Europe/Rome', 'barcelona' => 'Europe/Madrid',
    'geneva' => 'Europe/Zurich', 'st petersburg' => 'Europe/Moscow', 'saint petersburg' => 'Europe/Moscow', 'kiev' => 'Europe/Kyiv',
    'osaka' => 'Asia/Tokyo', 'kyoto' => 'Asia/Tokyo', 'busan' => 'Asia/Seoul', 'abu dhabi' => 'Asia/Dubai', 'mecca' => 'Asia/Riyadh', 'jeddah' => 'Asia/Riyadh',
    'tel aviv' => 'Asia/Jerusalem', 'hanoi' => 'Asia/Ho_Chi_Minh', 'saigon' => 'Asia/Ho_Chi_Minh', 'canberra' => 'Australia/Sydney',
    'wellington' => 'Pacific/Auckland', 'cape town' => 'Africa/Johannesburg',
    '北京' => 'Asia/Shanghai', '上海' => 'Asia/Shanghai', '广州' => 'Asia/Shanghai', '深圳' => 'Asia/Shanghai', '成都' => 'Asia/Shanghai',
    '重庆' => 'Asia/Shanghai', '武汉' => 'Asia/Shanghai', '西安' => 'Asia/Shanghai', '杭州' => 'Asia/Shanghai', '南京' => 'Asia/Shanghai',
    '天津' => 'Asia/Shanghai', '乌鲁木齐' => 'Asia/Shanghai', '香港' => 'Asia/Hong_Kong', '澳门' => 'Asia/Macau', '台北' => 'Asia/Taipei',
    '东京' => 'Asia/Tokyo', '首尔' => 'Asia/Seoul', '新加坡' => 'Asia/Singapore', '伦敦' => 'Europe/London', '巴黎' => 'Europe/Paris',
    '柏林' => 'Europe/Berlin', '莫斯科' => 'Europe/Moscow', '奥斯陆' => 'Europe/Oslo', '纽约' => 'America/New_York',
    '洛杉矶' => 'America/Los_Angeles', '旧金山' => 'America/Los_Angeles', '悉尼' => 'Australia/Sydney', '迪拜' => 'Asia/Dubai',
];

// Country names that ICU spells differently from how people write them.
const TZ_COUNTRY_ALIASES = [
    'usa' => 'US', 'united states of america' => 'US', 'america' => 'US', 'uk' => 'GB', 'britain' => 'GB', 'great britain' => 'GB',
    'england' => 'GB', 'scotland' => 'GB', 'wales' => 'GB', 'holland' => 'NL', 'korea' => 'KR', 'south korea' => 'KR',
    'north korea' => 'KP', 'czech republic' => 'CZ', 'turkey' => 'TR', 'ivory coast' => 'CI', 'hong kong' => 'HK',
    'macau' => 'MO', 'macao' => 'MO', 'uae' => 'AE', 'emirates' => 'AE', 'russian federation' => 'RU', 'vatican' => 'VA',
    '美国' => 'US', '英国' => 'GB', '韩国' => 'KR', '俄罗斯' => 'RU',
];

// For countries with several zones where one is used by almost everyone (or is the official national time).
const TZ_COUNTRY_MAIN = [
    'CN' => 'Asia/Shanghai', 'DE' => 'Europe/Berlin', 'ES' => 'Europe/Madrid', 'PT' => 'Europe/Lisbon', 'NZ' => 'Pacific/Auckland',
    'EC' => 'America/Guayaquil', 'CL' => 'America/Santiago', 'MY' => 'Asia/Kuala_Lumpur', 'UA' => 'Europe/Kyiv', 'UZ' => 'Asia/Tashkent',
    'AR' => 'America/Argentina/Buenos_Aires', 'DK' => 'Europe/Copenhagen', 'FR' => 'Europe/Paris', 'GB' => 'Europe/London',
    'NL' => 'Europe/Amsterdam', 'KI' => 'Pacific/Tarawa', 'PG' => 'Pacific/Port_Moresby', 'CY' => 'Asia/Nicosia', 'KZ' => 'Asia/Almaty',
];

// For countries with several zones in real use: the ones worth offering, in a sensible order.
const TZ_COUNTRY_OPTIONS = [
    'US' => ['America/New_York', 'America/Chicago', 'America/Denver', 'America/Phoenix', 'America/Los_Angeles', 'America/Anchorage', 'Pacific/Honolulu'],
    'CA' => ['America/St_Johns', 'America/Halifax', 'America/Toronto', 'America/Winnipeg', 'America/Edmonton', 'America/Vancouver'],
    'RU' => ['Europe/Kaliningrad', 'Europe/Moscow', 'Europe/Samara', 'Asia/Yekaterinburg', 'Asia/Novosibirsk', 'Asia/Irkutsk', 'Asia/Vladivostok', 'Asia/Kamchatka'],
    'BR' => ['America/Sao_Paulo', 'America/Manaus', 'America/Rio_Branco', 'America/Noronha'],
    'AU' => ['Australia/Sydney', 'Australia/Brisbane', 'Australia/Adelaide', 'Australia/Darwin', 'Australia/Perth'],
    'MX' => ['America/Mexico_City', 'America/Cancun', 'America/Chihuahua', 'America/Tijuana'],
    'ID' => ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'],
];

/** Options + the shared opts array for snapshot(): ['tz' => zone|null, 'tz_res' => resolution|null, 'cutoff' => ...]. */
function tz_opts(?string $tzInput, ?string $cutoff): array {
    $r = resolve_zone($tzInput);
    return ['tz' => $r['zone'] ?? null, 'tz_res' => $r, 'cutoff' => $cutoff];
}

function tz_canonical_lower(): array {
    static $m = null;
    if ($m === null) {
        $m = [];
        foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC) as $id) $m[strtolower($id)] = $id;
    }
    return $m;
}

/** city name (lower case, spaces) => [zones] from the last part of every canonical IANA name. */
function tz_city_index(): array {
    static $m = null;
    if ($m === null) {
        $m = [];
        foreach (DateTimeZone::listIdentifiers() as $id) {
            $p = explode('/', $id);
            if (count($p) < 2) continue;
            $m[strtolower(str_replace('_', ' ', end($p)))][] = $id;
        }
    }
    return $m;
}

/** ISO country code => [zones], from the zone database itself. */
function tz_country_zones(): array {
    static $m = null;
    if ($m === null) {
        $m = [];
        foreach (DateTimeZone::listIdentifiers() as $id) {
            $cc = (new DateTimeZone($id))->getLocation()['country_code'] ?? '??';
            if ($cc !== '??') $m[$cc][] = $id;
        }
    }
    return $m;
}

/** country name (English or Chinese, lower case) => ISO code. Needs intl for the names; aliases work without it. */
function tz_country_names(): array {
    static $m = null;
    if ($m === null) {
        $m = TZ_COUNTRY_ALIASES;
        if (class_exists('Locale')) {
            foreach (array_keys(tz_country_zones()) as $cc) {
                foreach (['en', 'zh_Hans'] as $loc) {
                    $n = Locale::getDisplayRegion('-' . $cc, $loc);
                    if ($n && $n !== $cc) $m[mb_strtolower_safe($n)] = $cc;
                }
            }
        }
    }
    return $m;
}

function mb_strtolower_safe(string $s): string {
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
}

function tz_label(string $zone): string {
    $p = explode('/', $zone);
    $city = str_replace('_', ' ', end($p));
    $cc = @(new DateTimeZone($zone))->getLocation()['country_code'];
    $country = ($cc && $cc !== '??' && class_exists('Locale')) ? Locale::getDisplayRegion('-' . $cc, 'en') : '';
    return $country && $country !== $city ? "$city, $country" : $city;
}

/**
 * Resolve what a person or agent typed into a time zone.
 * Returns ['input', 'zone' (string|null), 'how' (iana|name|offset|abbreviation|city|country|ambiguous|unknown), 'options' => [zone...]]
 */
function resolve_zone(?string $in): ?array {
    if ($in === null) return null;
    $raw = trim($in);
    if ($raw === '') return null;
    $res = fn($zone, $how, $options = []) => ['input' => $raw, 'zone' => $zone, 'how' => $how, 'options' => $options];
    if (strlen($raw) > 64) return $res(null, 'unknown');

    // Abbreviations first: the zone database also has legacy zones called CET, EST, MST, HST, EET, WET, and so on,
    // but people who type them mean the region's current local time (CEST in summer, New York for EST).
    $up = strtoupper($raw);
    if (isset(TZ_ABBR[$up]) && $up !== 'UTC') {
        $z = TZ_ABBR[$up];
        return is_array($z) ? $res(null, 'ambiguous', $z) : $res($z, 'abbreviation');
    }
    if (is_valid_zone($raw)) return $res($raw, 'iana');
    $lc = mb_strtolower_safe($raw);
    $under = str_replace(' ', '_', $lc);
    $canon = tz_canonical_lower();
    if (isset($canon[$under])) return $res($canon[$under], 'name');

    // Offsets: +2, +02, +0200, +02:00, UTC+2, GMT-05:30
    if (preg_match('/^(?:utc|gmt)?\s*([+-])\s*(\d{1,2})(?::?(\d{2}))?$/i', $raw, $m)) {
        $h = (int)$m[2];
        $mi = isset($m[3]) ? (int)$m[3] : 0;
        if ($h <= 14 && $mi < 60) {
            if ($h === 0 && $mi === 0) return $res('UTC', 'offset');
            return $res(sprintf('%s%02d:%02d', $m[1], $h, $mi), 'offset');
        }
    }

    $key = trim(preg_replace('/\s+/', ' ', str_replace(['_', '.'], [' ', ''], $lc)));
    if (isset(TZ_CITIES[$key])) return $res(TZ_CITIES[$key], 'city');
    $cities = tz_city_index();
    if (isset($cities[$key])) {
        return count($cities[$key]) === 1 ? $res($cities[$key][0], 'city') : $res(null, 'ambiguous', $cities[$key]);
    }

    $names = tz_country_names();
    $cc = $names[$key] ?? (preg_match('/^[A-Z]{2}$/', $raw) ? $raw : null);
    $zones = $cc ? (tz_country_zones()[$cc] ?? null) : null;
    if ($zones) {
        if (count($zones) === 1) return $res($zones[0], 'country');
        if (isset(TZ_COUNTRY_MAIN[$cc])) return $res(TZ_COUNTRY_MAIN[$cc], 'country');
        return $res(null, 'ambiguous', TZ_COUNTRY_OPTIONS[$cc] ?? $zones);
    }
    return $res(null, 'unknown');
}

/** One line explaining how ?tz= was read, or null when it was an exact IANA name. */
function tz_note(?array $r): ?string {
    if (!$r || $r['how'] === 'iana') return null;
    $q = '"' . $r['input'] . '"';
    if ($r['zone']) {
        $extra = $r['how'] === 'offset' ? ' (a fixed offset, without daylight saving)' : '';
        return "$q read as {$r['zone']}$extra";
    }
    if ($r['how'] === 'ambiguous') {
        $opts = array_slice($r['options'], 0, 8);
        $list = implode('; ', array_map(fn($z) => $z . ' (' . tz_label($z) . ')', $opts));
        $more = count($r['options']) > 8 ? '; more: /zones?q=' . rawurlencode($r['input']) : '';
        return "$q is ambiguous. Use one of: $list$more";
    }
    return "$q is not a time zone I recognise. Try a city (Oslo), a country (Norway), an IANA name (Europe/Oslo) or an offset (+02:00). All names: /zones";
}

/** The next clock change in a zone within $days days, or null. */
function next_clock_change(int $unix, string $zone, int $days = 366): ?array {
    try { $tz = new DateTimeZone($zone); } catch (Exception $e) { return null; }
    $tr = @$tz->getTransitions($unix, $unix + $days * 86400);
    if (!$tr || count($tr) < 2) return null;
    [$cur, $next] = [$tr[0], $tr[1]];
    $delta = (int)$next['offset'] - (int)$cur['offset'];
    if ($delta === 0) return null;
    $mins = intdiv(abs($delta), 60);
    $amount = $mins % 60 === 0 ? intdiv($mins, 60) . ' hour' . ($mins === 60 ? '' : 's') : $mins . ' minutes';
    return [
        'utc' => iso_utc((int)$next['ts']),
        'local_before' => gmdate('Y-m-d\TH:i:s', (int)$next['ts'] + (int)$cur['offset']),   // wall-clock time at which the change happens
        'direction' => $delta > 0 ? 'forward' : 'back',
        'amount' => $amount,
        'offset_after' => fmt_offset((int)$next['offset']),
        'abbreviation_after' => $next['abbr'],
        'dst_after' => (bool)$next['isdst'],
    ];
}

function fmt_offset(int $secs): string {
    $a = abs($secs);
    return ($secs < 0 ? '-' : '+') . sprintf('%02d:%02d', intdiv($a, 3600), intdiv($a % 3600, 60)) . ($a % 60 ? sprintf(':%02d', $a % 60) : '');
}

/** "UTC+2", "UTC-3:30", "UTC" for display. */
function short_offset(string $off): string {
    if ($off === '+00:00') return 'UTC';
    if (!preg_match('/^([+-])(\d{2}):(\d{2})/', $off, $m)) return 'UTC' . $off;
    return 'UTC' . $m[1] . (int)$m[2] . ($m[3] !== '00' ? ':' . $m[3] : '');
}

/** The extra block for the requested zone: abbreviation, daylight saving, next clock change. */
function local_details(int $unix, string $zone): array {
    $d = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone($zone));
    $abbr = $d->format('T');
    if (preg_match('/^(GMT)?[+-]\d/', $abbr)) $abbr = null;   // fixed offsets have no real abbreviation
    return [
        'part_of_day' => part_of_day((int)$d->format('G')),
        'abbreviation' => $abbr, 'dst' => (bool)(int)$d->format('I'), 'next_change' => next_clock_change($unix, $zone),
    ];
}

/** The words people use for the time of day. This is what stops "good evening" at 07:30. */
function part_of_day(int $hour): string {
    if ($hour < 5) return 'night';
    if ($hour < 8) return 'early morning';
    if ($hour < 12) return 'morning';
    if ($hour < 14) return 'midday';
    if ($hour < 18) return 'afternoon';
    if ($hour < 22) return 'evening';
    return 'late evening';
}

/** How to name the place in a sentence: the city of the zone, the country or abbreviation the person typed, or the offset. */
function place_name(string $zone, ?array $res): string {
    if (preg_match('/^[+-]\d{2}:\d{2}$/', $zone)) return short_offset($zone);
    if ($zone === 'UTC') return 'UTC';
    if ($res && $res['how'] === 'country') return $res['input'];
    if ($res && $res['how'] === 'abbreviation') return 'the ' . strtoupper($res['input']) . ' zone';
    $p = explode('/', $zone);
    return str_replace('_', ' ', end($p));
}

/** One sentence for talking to a person (/local). About 30 tokens. $lang 'zh' is used by the Chinese home page. */
function local_sentence(array $s, ?array $res, string $lang = 'en'): string {
    $l = $s['local'];
    if ($lang === 'zh' && $l && function_exists('fact_day')) {
        $d = (new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($l['zone']));
        $place = place_name($l['zone'], $res);
        foreach (PLACES as [$pz, , $pzh]) if ($pz === $l['zone'] && (!$res || $res['how'] !== 'country')) $place = $pzh;   // Chinese city name when we have one
        $off = short_offset($l['offset']);
        $inner = [PARTS_ZH[$l['part_of_day']]];
        if ($l['abbreviation'] && $l['abbreviation'] !== $off) $inner[] = $l['abbreviation'];
        if ($place !== $off) $inner[] = $off;
        $out = $d->format('Y') . '年' . $d->format('n') . '月' . $d->format('j') . '日 ' . WEEKDAYS_ZH[$d->format('l')] . ' ' . $d->format('H:i') . '，' . $place . '（' . implode('，', $inner) . '）。';
        $c = $l['next_change'] ?? null;
        if ($c && strtotime($c['utc']) - $s['unix'] <= 14 * 86400) {
            $out .= fact_day($c['local_before'], 'zh') . '时钟' . ($c['direction'] === 'back' ? '拨回' : '拨快') . ' ' . str_replace([' hours', ' hour', ' minutes'], [' 小时', ' 小时', ' 分钟'], $c['amount']) . '。';
        }
        return $out;
    }
    if (!$l) {
        $d = dt_utc($s['unix']);
        $base = $d->format('l j F Y, H:i') . ' UTC.';
        if (isset($s['tz_note'])) return "$base Time zone: {$s['tz_note']}";
        return "$base For a person's local time and part of day, add ?tz= with their city, country or zone, e.g. /local?tz=Oslo";
    }
    $d = (new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($l['zone']));
    $place = place_name($l['zone'], $res);
    $off = short_offset($l['offset']);
    $inner = [$l['part_of_day']];
    if ($l['abbreviation'] && $l['abbreviation'] !== $off) $inner[] = $l['abbreviation'];
    if ($place !== $off) $inner[] = $off;
    $out = $d->format('l j F Y, H:i') . ($place === $off || $place === 'UTC' ? " $place" : " in $place") . ' (' . implode(', ', $inner) . ').';
    $c = $l['next_change'] ?? null;
    if ($c && strtotime($c['utc']) - $s['unix'] <= 14 * 86400) {
        $when = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $c['local_before'], new DateTimeZone('UTC'));
        $out .= ' Clocks go ' . $c['direction'] . ' ' . $c['amount'] . ' on ' . $when->format('l j F') . '.';
    }
    return $out;
}

/** /zones: every canonical zone with its current offset, optionally filtered. */
function zones_list(int $unix, ?string $q): array {
    $rows = [];
    $needle = $q !== null && $q !== '' ? mb_strtolower_safe(trim($q)) : null;
    $resolved = $needle !== null ? resolve_zone($q) : null;
    $hit = $resolved ? array_filter(array_merge([$resolved['zone']], $resolved['options'])) : [];
    foreach (DateTimeZone::listIdentifiers() as $id) {
        if ($needle !== null && !in_array($id, $hit, true) && !str_contains(mb_strtolower_safe(str_replace('_', ' ', $id)), str_replace('_', ' ', $needle))) continue;
        $d = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone($id));
        $abbr = $d->format('T');
        $rows[] = ['zone' => $id, 'offset' => fmt_offset($d->getOffset()), 'abbreviation' => preg_match('/^[+-]\d/', $abbr) ? null : $abbr, 'dst' => (bool)(int)$d->format('I')];
    }
    return $rows;
}

function zones_text(int $unix, ?string $q): string {
    $rows = zones_list($unix, $q);
    $head = $q !== null && $q !== ''
        ? "# Time zones matching \"$q\" (" . count($rows) . ")\n" . (($n = tz_note(resolve_zone($q))) ? "# $n\n" : '')
        : "# IANA time zones (" . count($rows) . "), current UTC offset at " . iso_utc($unix) . "\n# Use any of these as ?tz= on /now. Cities, countries, abbreviations and offsets also work. Filter: /zones?q=oslo\n";
    $lines = array_map(fn($r) => str_pad($r['zone'], 32) . ' ' . str_pad($r['offset'], 7) . ($r['abbreviation'] ? ' ' . $r['abbreviation'] : '') . ($r['dst'] ? ' (summer time)' : ''), $rows);
    return $head . implode("\n", $lines) . "\n";
}

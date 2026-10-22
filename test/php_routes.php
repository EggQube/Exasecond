<?php
// Route tests for the PHP site:  php test/php_routes.php
define('EXA_TEST', true);
require __DIR__ . '/../site/index.php';

$fails = 0;
function check(string $name, bool $ok): void {
    global $fails;
    if (!$ok) $fails++;
    echo ($ok ? '  ok   ' : '  FAIL ') . $name . "\n";
}
function get(string $uri, string $accept = ''): array {
    $q = [];
    parse_str((string)parse_url($uri, PHP_URL_QUERY), $q);
    return route('GET', $uri, $q, $accept ? ['accept' => $accept] : []);
}

check('constant', OFFSET === 435084630253315200 && unix_to_universe(946684800) === 435084631200000000);
check('universe_fraction_percent has no float leftovers', (bool)preg_match('/"universe_fraction_percent": \d+\.\d{1,6},/', get('/now.json')[2]));
check('exaseconds',to_exaseconds(435084632045903278) === '0.435084632045903278');
check('/utc', (bool)preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\n$/', get('/utc')[2]));
check('/now text', str_starts_with(get('/now')[2], 'UTC: '));
check('/now markdown', get('/now', 'text/markdown')[1]['Content-Type'] === 'text/markdown; charset=utf-8');
// Time zones: forgiving input, and a bad zone never costs the agent the time
[$st, , $b] = get('/now?tz=Not/AZone');
check('/now bad tz: 200, time kept, note given', $st === 200 && str_starts_with($b, 'UTC: ') && str_contains($b, 'Time zone: "Not/AZone" is not a time zone'));
$z = fn($in) => resolve_zone($in);
check('tz exact IANA', $z('Europe/Oslo')['zone'] === 'Europe/Oslo' && $z('Europe/Oslo')['how'] === 'iana');
check('tz any case', $z('europe/oslo')['zone'] === 'Europe/Oslo');
check('tz city', $z('Oslo')['zone'] === 'Europe/Oslo' && $z('new york')['zone'] === 'America/New_York' && $z('Beijing')['zone'] === 'Asia/Shanghai');
check('tz Chinese city', $z('北京')['zone'] === 'Asia/Shanghai');
check('tz country', $z('Norway')['zone'] === 'Europe/Oslo' && $z('China')['zone'] === 'Asia/Shanghai' && $z('挪威')['zone'] === 'Europe/Oslo');
check('tz abbreviation means the region, with summer time', $z('CET')['zone'] === 'Europe/Paris' && $z('EST')['zone'] === 'America/New_York');
check('tz offsets', $z('+02:00')['zone'] === '+02:00' && $z('UTC+2')['zone'] === '+02:00' && $z('+0530')['zone'] === '+05:30' && $z('GMT')['zone'] === 'UTC');
check('tz ambiguous lists options', $z('IST')['zone'] === null && in_array('Asia/Kolkata', $z('IST')['options'], true) && $z('US')['options'][0] === 'America/New_York');
check('tz unknown', $z('xyz')['how'] === 'unknown');
$oslo = get('/now?tz=Oslo')[2];
check('/now?tz=Oslo local line', (bool)preg_match('/^Local \(Europe\/Oslo\): \S+ \w+ [a-z ]+, CES?T \(UTC\+[12](, summer time)?\)$/m', $oslo));
$jul = snapshot([1784980800, 0], tz_opts('Oslo', null));   // 2026-07-25: next change is 2026-10-25, 92 days away
check('next clock change in JSON', $jul['local']['next_change']['utc'] === '2026-10-25T01:00:00Z' && $jul['local']['next_change']['direction'] === 'back' && $jul['local']['next_change']['local_before'] === '2026-10-25T03:00:00');
check('clock change line only when near (60 days)', !str_contains(now_text($jul), 'Clock change') && str_contains(now_text(snapshot([1792607000, 0], tz_opts('Oslo', null))), 'Clock change: 2026-10-25 at 03:00 local, back 1 hour to CET (UTC+1)'));
check('/zones lists zones', substr_count(get('/zones')[2], "\n") > 300 && str_contains(get('/zones?q=oslo')[2], 'Europe/Oslo'));
check('/zones.json', json_decode(get('/zones.json?q=tokyo')[2], true)[0]['zone'] === 'Asia/Tokyo');
// /local: one sentence for talking to a person
check('part of day', part_of_day(7) === 'early morning' && part_of_day(9) === 'morning' && part_of_day(21) === 'evening' && part_of_day(2) === 'night' && part_of_day(23) === 'late evening');
$o = tz_opts('Oslo', null);
check('/local sentence (evening, clock change near)', local_sentence(snapshot([1792610508, 0], $o), $o['tz_res']) === 'Wednesday 21 October 2026, 21:21 in Oslo (evening, CEST, UTC+2). Clocks go back 1 hour on Sunday 25 October.');
check('/local next morning, after the change', local_sentence(snapshot([1792909200, 0], $o), $o['tz_res']) === 'Sunday 25 October 2026, 07:20 in Oslo (early morning, CET, UTC+1).');
check('/local route', (bool)preg_match('/^\w+day \d+ \w+ \d{4}, \d\d:\d\d in Oslo \((night|early morning|morning|midday|afternoon|evening|late evening), /', get('/local?tz=Oslo')[2]));
check('/local without tz gives UTC and a hint', str_contains(get('/local')[2], ' UTC. For a person'));
check('/local bad tz keeps the time', str_contains(get('/local?tz=IST')[2], ' UTC. Time zone: "IST" is ambiguous'));
check('/local is short', strlen(get('/local?tz=Oslo')[2]) < 160);
[$st, , $b] = route('POST', '/mcp', [], [], '{"jsonrpc":"2.0","id":2,"method":"tools/call","params":{"name":"get_time","arguments":{"tz":"Oslo","detail":"local"}}}');
check('mcp get_time detail=local', str_contains(json_decode($b, true)['result']['content'][0]['text'], ' in Oslo ('));
check('/now without tz unchanged', !str_contains(get('/now')[2], 'Time zone') && str_contains(get('/now')[2], 'Tip: add ?tz='));
check('/time stays a clock for agents', str_starts_with(get('/time')[2], 'UTC: '));
check('/time is the contents page for browsers', str_contains(get('/time', 'text/html')[2], 'The question of time'));
check('/zh/time contents', str_contains(get('/zh/time', 'text/html')[2], '时间之问'));
check('/ html', str_contains(get('/', 'text/html')[2], 'Seconds since the beginning of the universe'));
check('/zh/ html', str_contains(get('/zh/', 'text/html')[2], '宇宙诞生以来的秒数'));
foreach (CHAPTERS as $slug) {
    foreach (['', '/zh'] as $p) {
        [$st, $h, $b] = get("$p/time/$slug", 'text/html');
        [$st2, , $md] = get("$p/time/$slug.md");
        check("$p/time/$slug html + md", $st === 200 && $st2 === 200 && str_starts_with($md, '# ') && str_contains($b, '<article class="prose">'));
    }
}
check('chapters have sources', count(array_filter(CHAPTERS, fn($s) => str_contains(chapter('en', $s)['body'], '## Sources') && str_contains(chapter('zh', $s)['body'], '## 来源'))) === count(CHAPTERS));
check('/time/unknown is 404', get('/time/nope', 'text/html')[0] === 404);
check('llms.txt lists chapters and story', str_contains(get('/llms.txt')[2], '/time/scale.md') && str_contains(get('/llms.txt')[2], '/story.md'));
check('llms-full has chapters and play', str_contains(get('/llms-full.txt')[2], 'The arrow of time') && str_contains(get('/llms-full.txt')[2], 'infinite number of pottery workshops'));
check('/now has no story or science', !str_contains(get('/now')[2], 'Pottery') && !str_contains(get('/now')[2], 'chapter'));
check('/story', str_contains(get('/story', 'text/html')[2], 'Pottery Lecture'));
check('sitemap has zh chapters', str_contains(get('/sitemap.xml')[2], '/zh/time/machines'));
check('/api/convert', json_decode(get('/api/convert?iso=2000-01-01T00:00:00Z')[2], true)['universe_seconds'] === '435084631200000000');
check('/api/convert bad input is 400', get('/api/convert?es=2.5')[0] === 400);
[$st, , $b] = route('POST', '/mcp', [], [], '{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"get_time","arguments":{}}}');
check('mcp get_time', $st === 200 && str_starts_with(json_decode($b, true)['result']['content'][0]['text'], 'UTC: '));
[$st] = route('POST', '/mcp', [], [], '{"jsonrpc":"2.0","method":"notifications/initialized"}');
check('mcp notification is 202', $st === 202);
check('no long dashes in chapters', !preg_match('/[\x{2013}\x{2014}]/u', implode('', array_map(fn($s) => chapter('en', $s)['body'] . chapter('zh', $s)['body'], CHAPTERS))));

echo $fails ? "$fails failed\n" : "all passed\n";
exit($fails ? 1 : 0);

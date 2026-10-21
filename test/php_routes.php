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
check('exaseconds', to_exaseconds(435084632045903278) === '0.435084632045903278');
check('/utc', (bool)preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\n$/', get('/utc')[2]));
check('/now text', str_starts_with(get('/now')[2], 'UTC: '));
check('/now markdown', get('/now', 'text/markdown')[1]['Content-Type'] === 'text/markdown; charset=utf-8');
check('/now bad tz is 400', get('/now?tz=Not/AZone')[0] === 400);
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

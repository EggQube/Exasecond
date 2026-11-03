<?php
// Focused regression checks. Run: php test/php_reliability.php
define('EXA_TEST', true);
require __DIR__ . '/../site/index.php';

$fails = 0;
$checks = 0;
function verify(string $name, bool $ok): void {
    global $fails, $checks;
    $checks++;
    if (!$ok) { $fails++; echo "FAIL $name\n"; }
}
function rejects(array $input): bool {
    try { parse_instant($input); return false; } catch (InputError $e) { return true; }
}
function rpc($message): array {
    return mcp_response('POST', json_encode($message, JSON_UNESCAPED_UNICODE));
}
function request_rpc(string $method, array $params = []): array {
    return rpc(['jsonrpc' => '2.0', 'id' => 'test', 'method' => $method, 'params' => (object)$params]);
}

verify('fold preserves letters s and repeated letters', name_fold('  Mississippi   Islands ') === 'mississippi islands');
verify('fold spaces and hyphens', name_fold("New_  York\tCity") === 'new york city');
if (class_exists('Normalizer')) {
    verify('fold accents', name_fold('São Paulo') === name_fold('sao-paulo'));
    verify('fold decomposed accents', name_fold("Krak\u{00F3}w") === name_fold("Krako\u{0301}w"));
}

foreach (['1.25' => [1, 250], '-1.25' => [-2, 750], '-0.125' => [-1, 875], '0.001' => [0, 1], '1.234567' => [1, 234], '-1.234567' => [-2, 766], '-0.0009' => [0, 0]] as $input => $expected) {
    verify("fractional unix $input", parse_instant(['unix' => $input]) === $expected);
}
verify('ISO fraction and unix fraction agree', parse_instant(['iso' => '1969-12-31T23:59:59.875Z']) === parse_instant(['unix' => '-0.125']));
verify('conversion retains unix_ms', snapshot(parse_instant(['unix' => '1.25']))['technical']['unix_ms'] === 1250);
verify('valid leap day', parse_instant(['iso' => '2024-02-29T12:00:00Z'])[0] === 1709208000);
verify('date only defaults to UTC midnight', parse_instant(['iso' => '2000-01-01']) === [946684800, 0]);
verify('offset identifies same instant', parse_instant(['iso' => '2000-01-01T02:00:00+02:00']) === [946684800, 0]);
foreach (['2026-02-29', '2026-04-31T12:00:00Z', '2026-10-23T24:00:00Z', '2026-10-23T12:60:00Z', '2026-10-23T12:00:60Z', '2026', '2026-10', 'next Monday'] as $iso) verify("reject invalid/incomplete ISO $iso", rejects(['iso' => $iso]));
verify('multiple inputs rejected', rejects(['unix' => '0', 'iso' => '2000-01-01']));
verify('malformed array input rejected', rejects(['iso' => ['2000-01-01'], 'unix' => '0']));
verify('boolean input rejected', rejects(['unix' => false]));
verify('no input rejected', rejects([]));
verify('positive boundary', parse_instant(['unix' => (string)MAX_UNIX]) === [MAX_UNIX, 0]);
verify('fraction beyond positive boundary rejected', rejects(['unix' => MAX_UNIX . '.001']));
verify('fraction beyond negative boundary rejected', rejects(['unix' => '-' . MAX_UNIX . '.001']));
foreach (['0.435084631200000000', '0.435084632045903278'] as $es) {
    $instant = parse_instant(['es' => $es]);
    verify("integer convention $es", to_exaseconds(unix_to_universe($instant[0])) === $es);
}

foreach (['null', '[]', '{}', '1', '"text"', '{"jsonrpc":"1.0","id":1,"method":"ping"}', '{"jsonrpc":"2.0","id":true,"method":"ping"}', '{"jsonrpc":"2.0","id":null,"method":"ping"}', '{"jsonrpc":"2.0"}'] as $raw) {
    [$status, $body] = mcp_response('POST', $raw);
    verify("invalid RPC $raw", $status === 400 && ($body['error']['code'] ?? null) === -32600);
}
[$status, $body] = mcp_response('POST', '{');
verify('RPC parse error', $status === 400 && $body['error']['code'] === -32700);
[$status, $body] = rpc(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']);
verify('notification accepted without body', $status === 202 && $body === null);
[$status, $body] = request_rpc('ping');
verify('ping echoes string id and empty object', $status === 200 && $body['id'] === 'test' && $body['result'] instanceof stdClass);
[$status, $body] = request_rpc('initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => new stdClass(), 'clientInfo' => (object)['name' => 'regression', 'version' => '1']]);
verify('initialize negotiates supported version', $status === 200 && $body['result']['protocolVersion'] === '2025-06-18');
[, $body] = request_rpc('initialize');
verify('initialize validates required fields', $body['error']['code'] === -32602);
[, $body] = request_rpc('tools/list');
verify('two MCP tools remain available', count($body['result']['tools']) === 2);
verify('MCP language schema matches engine', $body['result']['tools'][0]['inputSchema']['properties']['lang']['enum'] === SENTENCE_LANGS);
foreach (SENTENCE_LANGS as $lang) {
    [, $body] = request_rpc('tools/call', ['name' => 'get_time', 'arguments' => (object)['tz' => 'Tokyo', 'detail' => 'local', 'lang' => $lang]]);
    $text = $body['result']['content'][0]['text'] ?? '';
    verify("MCP local sentence $lang", $text !== '' && !isset($body['result']['isError']) && ($lang === 'en' || !str_starts_with($text, "It's ")));
}
foreach ([['tz' => []], ['detail' => 'bad'], ['lang' => 'xx']] as $args) {
    [, $body] = request_rpc('tools/call', ['name' => 'get_time', 'arguments' => (object)$args]);
    verify('invalid tool arguments are tool errors: ' . json_encode($args), ($body['result']['isError'] ?? false) === true);
}
[, $body] = request_rpc('tools/call', ['name' => 'get_time', 'arguments' => []]);
verify('array arguments rejected', $body['error']['code'] === -32602);
[, $body] = request_rpc('tools/call', ['name' => 'unknown']);
verify('unknown tool error', $body['error']['code'] === -32602);
[, $body] = request_rpc('tools/call', ['name' => 'convert_time', 'arguments' => (object)['iso' => '2026-02-29']]);
verify('invalid conversion is a tool error', ($body['result']['isError'] ?? false) === true);

$oslo = tz_opts('Oslo', null);
$basic = now_text(snapshot([1792610508, 0], $oslo));
verify('compact answer omits secondary calendars', !str_contains($basic, 'Chinese:') && !str_contains($basic, 'Calendars'));
verify('compact answer omits cosmic time', !str_contains($basic, 'Universe seconds:') && !str_contains($basic, ' Es)') && !str_contains($basic, (string)OFFSET));
verify('full answer retains cosmic time', str_contains(now_md(snapshot([1792610508, 0], $oslo)), 'Universe seconds:'));
verify('full answer retains optional calendars', str_contains(now_md(snapshot([1792610508, 0], $oslo)), 'Chinese lunisolar'));
[, $body] = request_rpc('tools/call', ['name' => 'get_time', 'arguments' => (object)['tz' => 'Oslo']]);
verify('default MCP has no calendar or fact', !str_contains($body['result']['content'][0]['text'], 'Chinese:') && !str_contains($body['result']['content'][0]['text'], 'Did you know'));
verify('default MCP has no cosmic time', !str_contains($body['result']['content'][0]['text'], 'Universe seconds:') && !str_contains($body['result']['content'][0]['text'], ' Es)'));
$before = snapshot([1792890000 - 1, 0], $oslo);
$after = snapshot([1792890000, 0], $oslo);
verify('DST fall-back changes offset at instant', $before['local']['offset'] === '+02:00' && $after['local']['offset'] === '+01:00');
verify('Lord Howe supports half-hour clock changes', next_clock_change(1767225600, 'Australia/Lord_Howe')['amount'] === '30 minutes');
verify('Kiritimati local date crosses UTC midnight', snapshot([1767268800, 0], tz_opts('Pacific/Kiritimati', null))['local']['local'] === '2026-01-02T02:00:00');

foreach (['2025-03-26', '2099-01-01'] as $version) {
    [, $body] = request_rpc('initialize', ['protocolVersion' => $version, 'capabilities' => new stdClass(), 'clientInfo' => (object)['name' => 'regression', 'version' => '1']]);
    verify("version negotiation $version", $body['result']['protocolVersion'] === ($version === '2099-01-01' ? MCP_SUPPORTED[0] : $version));
}
foreach ([null, false, [], 'text'] as $client) {
    [, $body] = request_rpc('initialize', ['protocolVersion' => '2025-06-18', 'capabilities' => new stdClass(), 'clientInfo' => $client]);
    verify('invalid initialization client info ' . json_encode($client), ($body['error']['code'] ?? null) === -32602);
}
[, $body] = rpc(['jsonrpc' => '2.0', 'id' => 0, 'method' => 'ping']);
verify('numeric zero id retained', $body['id'] === 0 && isset($body['result']));
verify('MCP GET declines SSE', mcp_response('GET', '') === [405, null]);
$batch = '[{"jsonrpc":"2.0","id":1,"method":"ping"},{"jsonrpc":"2.0","method":"notifications/initialized"}]';
foreach ([[], ['mcp-protocol-version' => '2025-03-26']] as $headers) {
    [$status, $body] = mcp_response('POST', $batch, $headers);
    verify('March batch excludes notification replies ' . json_encode($headers), $status === 200 && count($body) === 1 && $body[0]['id'] === 1);
}
[$status, $body] = mcp_response('POST', $batch, ['mcp-protocol-version' => '2025-06-18']);
verify('June rejects batch requests', $status === 400 && $body['error']['code'] === -32600);
[$status, $body] = mcp_response('POST', '{"jsonrpc":"2.0","id":1,"method":"ping"}', ['mcp-protocol-version' => '2099-01-01']);
verify('unknown protocol header rejected', $status === 400 && $body['error']['code'] === -32600);
[$status, $body] = mcp_response('POST', '[{"jsonrpc":"2.0","method":"notifications/initialized"}]');
verify('March notification-only batch has no body', $status === 202 && $body === null);

$api = openapi();
verify('OpenAPI exposes local language enum', $api['paths']['/local']['get']['parameters'][1]['schema']['enum'] === SENTENCE_LANGS);
verify('OpenAPI exposes text and JSON local answers', isset($api['paths']['/local']['get']['responses']['200']['content']['text/plain'], $api['paths']['/local']['get']['responses']['200']['content']['application/json']));
verify('OpenAPI describes place, facts and zones', isset($api['paths']['/{place}'], $api['paths']['/fact'], $api['paths']['/zones']));
foreach (['en' => 'directly', 'zh' => '工具连接', 'de' => 'Werkzeugverbindung', 'fr' => 'connexion', 'es' => 'conexión', 'pt' => 'conexão', 'ru' => 'инструмент', 'sv' => 'verktygsanslutning', 'ja' => 'ツール接続', 'ko' => '도구 연결'] as $lang => $word) {
    verify("translated connection notice $lang", str_contains(t($lang, 'tool_connection'), $word));
    $doc = file_get_contents(content_path($lang, 'developers.md'));
    verify("MCP language example and costs $lang", str_contains($doc, '"lang":"' . $lang . '"')
        && str_contains($doc, 'o200k_base') && str_contains($doc, 'cl100k_base')
        && str_contains($doc, '| 21 | ~14 |') && str_contains($doc, '| ~70-120 | ~25-40 |')
        && str_contains(file_get_contents(content_path($lang, 'about.md')), '| 11 | ~5 |')
        && str_contains($doc, '245-256') && !str_contains($doc, '| ~50 |'));
    verify("copyright sign in licence notice $lang", str_contains($doc, '© 2026 EggQube') && !str_contains($doc, '(c) 2026'));
    verify("developer UTF-8 $lang", preg_match('//u', $doc) === 1 && !str_contains($doc, "\u{FFFD}"));
}
foreach (['/local?tz=Oslo', '/local?tz=Tokyo&lang=ja', '/api/convert?unix=-0.125', '/api/convert?iso=2026-02-29'] as $uri) {
    parse_str(parse_url($uri, PHP_URL_QUERY), $q);
    [$status, $headers, $body] = route('GET', $uri, $q, []);
    verify("route status and freshness $uri", $status === (str_contains($uri, '2026-02-29') ? 400 : 200) && $headers['Cache-Control'] === 'no-store');
}

echo "$checks checks, $fails failed\n";
exit($fails ? 1 : 0);

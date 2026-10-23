<?php
// Dependency-free response-size report, not a model tokenizer.
// php tools/measure_tokens.php [--json]
// Feed each JSON row's text to the target model's tokenizer for actual token counts.
define('EXA_TEST', true);
require __DIR__ . '/../site/index.php';

$rows = [];
$add = function (string $name, string $text) use (&$rows): void {
    $characters = preg_match_all('/./us', $text);
    $rows[] = ['name' => $name, 'bytes' => strlen($text), 'characters' => $characters,
        'estimated_tokens_chars_div_4' => (int)ceil($characters / 4), 'text' => $text];
};
$compact = fn($value) => (string)json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$add('setup/prompt', 'Before greeting someone or mentioning the time of day, read https://exasecond.com/<their city> and use that time.');
$add('setup/openai-tool', $compact(json_decode(file_get_contents(content_path('dev', 'openai-tool.json')))));
$add('setup/anthropic-tool', $compact(json_decode(file_get_contents(content_path('dev', 'anthropic-tool.json')))));
$add('setup/mcp-tools', $compact(mcp_tools()));
$add('question/tool-arguments', $compact(['tz' => 'Oslo', 'detail' => 'local', 'lang' => 'en']));
// Historical cosmic-time alternatives, excluded from compact replies.
$sample = snapshot([1792610508, 0]);
$cosmicLine = "Universe seconds: {$sample['universe_seconds']} ({$sample['exaseconds']} Es)\n";
$add('cost/cosmic/removed-both', $cosmicLine);
$add('cost/cosmic/seconds-only', "Universe seconds: {$sample['universe_seconds']}\n");
$add('cost/cosmic/exaseconds-only', "Exaseconds: {$sample['exaseconds']} Es\n");
$add('answer/basic/no-place', now_text($sample));
$add('comparison/basic/with-cosmic', now_text($sample) . $cosmicLine);
$add('answer/basic/oslo', now_text(snapshot([1792610508, 0], tz_opts('Oslo', null))));
foreach (SENTENCE_LANGS as $lang) {
    $opts = tz_opts('Oslo', null);
    foreach (['near-dst' => 1792610508, 'ordinary' => 1784980800] as $case => $unix) {
        $add("answer/$lang/$case", local_sentence(snapshot([$unix, 0], $opts), $opts['tz_res'], $lang));
    }
}
foreach (['text/markdown' => 'markdown', 'text/html' => 'html'] as $accept => $name) {
    $add("home/$name", route('GET', '/', [], ['accept' => $accept])[2]);
}
// Current response bodies, including the paths an agent might fetch instead of the homepage.
// The earlier fixed samples are retained for repeatable language/DST comparisons.
$capturedAt = gmdate('Y-m-d\TH:i:s\Z');
foreach ([
    ['utc', '/utc', [], 'text/plain'],
    ['unix', '/unix', [], 'text/plain'],
    ['now', '/now', [], 'text/plain'],
    ['now-oslo', '/now', ['tz' => 'Oslo'], 'text/plain'],
    ['local-oslo', '/local', ['tz' => 'Oslo'], 'text/plain'],
    ['local-oslo-zh', '/local', ['tz' => 'Oslo', 'lang' => 'zh'], 'text/plain'],
    ['local-ambiguous-IST', '/local', ['tz' => 'IST'], 'text/plain'],
    ['local-no-zone', '/local', [], 'text/plain'],
    ['now-md', '/now.md', [], 'text/markdown'],
    ['now-json', '/now.json', [], 'application/json'],
    ['local-json', '/local', ['tz' => 'Oslo'], 'application/json'],
    ['fact-oslo', '/fact', ['tz' => 'Oslo'], 'text/plain'],
    ['zones-oslo', '/zones', ['q' => 'oslo'], 'text/plain'],
    ['llms', '/llms.txt', [], 'text/plain'],
    ['llms-full', '/llms-full.txt', [], 'text/plain'],
    ['chapter-index', '/time.md', [], 'text/markdown'],
    ['chapter-nature', '/time/nature.md', [], 'text/markdown'],
    ['developers-md', '/developers.md', [], 'text/markdown'],
    ['developers-html', '/developers', [], 'text/html'],
] as [$label, $path, $query, $accept]) {
    [$status, , $body] = route('GET', $path, $query, ['accept' => $accept]);
    if ($status !== 200) throw new RuntimeException("Sample $label returned HTTP $status");
    $add('http/' . $label, $body);
}
foreach (['basic' => [], 'local-oslo' => ['tz' => 'Oslo', 'detail' => 'local'], 'full-oslo' => ['tz' => 'Oslo', 'detail' => 'full']] as $label => $arguments) {
    $request = ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/call', 'params' => ['name' => 'get_time', 'arguments' => (object)$arguments]];
    $add('mcp/arguments/' . $label, $compact((object)$arguments));
    $add('mcp/result-text/' . $label, mcp_call_tool('get_time', $arguments));
    [$status, , $body] = route('POST', '/mcp', [], ['mcp-protocol-version' => '2025-06-18'], $compact($request));
    if ($status !== 200) throw new RuntimeException("MCP sample $label returned HTTP $status");
    $add('mcp/wire-response/' . $label, $body);
}
if (in_array('--json', $argv, true)) {
    echo to_json(['captured_at_utc' => $capturedAt, 'method' => 'Unicode characters / 4, rounded up. A size heuristic, not tokenizer measurements or billing. Tool framing, model, language, date, ambiguity and client schema serialization change costs.', 'rows' => $rows]);
} else {
    echo "Size heuristic only: Unicode characters / 4; not model token counts.\n";
    foreach ($rows as $row) printf("%-32s %6d bytes %6d chars ~%5d tokens\n", $row['name'], $row['bytes'], $row['characters'], $row['estimated_tokens_chars_div_4']);
}

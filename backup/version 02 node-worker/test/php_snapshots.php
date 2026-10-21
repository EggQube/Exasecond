<?php
// Helper for test/parity.mjs: reads {cases, bad} as JSON on stdin, prints PHP snapshots as JSON.
define('EXA_TEST', true);
require __DIR__ . '/../../../site/index.php';

$in = json_decode((string)stream_get_contents(STDIN), true);
$snaps = [];
foreach ($in['cases'] as $c) {
    $snaps[] = snapshot(parse_instant($c), ['tz' => $c['tz'] ?? null, 'cutoff' => $c['cutoff'] ?? null]);
}
$bad = [];
foreach ($in['bad'] as $c) {
    try { parse_instant($c); $bad[] = 'ok'; } catch (InputError $e) { $bad[] = 'error'; }
}
echo json_encode(['snapshots' => $snaps, 'bad' => $bad], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

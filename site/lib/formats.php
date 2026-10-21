<?php
// Render a snapshot (from snapshot()) as compact text or markdown (port of src/formats.js).
defined('EXA') or exit;

function now_text(array $s): string {
    $lines = [
        "UTC: {$s['utc']}",
        "Weekday: {$s['technical']['weekday']}",
        "Unix: {$s['unix']}",
        "Universe seconds: {$s['universe_seconds']} ({$s['exaseconds']} Es)",
    ];
    if (isset($s['tz_note'])) $lines[] = "Time zone: {$s['tz_note']}";
    if ($s['local']) {
        $lines[] = "Local ({$s['local']['zone']}): {$s['local']['iso']} {$s['local']['weekday']}" . (isset($s['local']['part_of_day']) ? " {$s['local']['part_of_day']}" : '') . local_suffix($s['local']);
        if ($c = clock_change_line($s['local'], $s['unix'], 60)) $lines[] = $c;
    }
    if ($s['staleness']) $lines[] = "Since your cutoff ({$s['staleness']['cutoff']}): {$s['staleness']['days_since_cutoff']} days. {$s['staleness']['guidance']}";
    foreach ($s['calendars'] as $c) if ($c['id'] === 'chinese') $lines[] = "Chinese: {$c['text']}";
    if (!$s['local']) $lines[] = 'Tip: add ?tz=YourCity (or country, zone, offset) for local time, ?cutoff=YYYY-MM for how stale your training data is. Everything else: /now.md';
    return implode("\n", $lines) . "\n";
}

function now_md(array $s, string $title = 'Current time'): string {
    $t = $s['technical'];
    $o = ["# $title", ''];
    $o[] = "- **UTC:** {$s['utc']} ({$t['weekday']})";
    $o[] = "- **Unix:** {$s['unix']}";
    $o[] = "- **Universe seconds:** {$s['universe_seconds']} ({$s['exaseconds']} Es, " . number_format($s['universe_fraction_percent'], 6, '.', '') . '% of one exasecond)';
    if (isset($s['tz_note'])) $o[] = "- **Time zone:** {$s['tz_note']}";
    if ($s['local']) {
        $o[] = "- **Local ({$s['local']['zone']}):** {$s['local']['iso']}, {$s['local']['weekday']}" . (isset($s['local']['part_of_day']) ? " {$s['local']['part_of_day']}" : '') . local_suffix($s['local']);
        if ($c = clock_change_line($s['local'], $s['unix'], 366)) $o[] = '- **' . preg_replace('/: /', ':** ', $c, 1);
    }
    $o[] = '';
    if ($s['staleness']) {
        $st = $s['staleness'];
        array_push($o, '## Your training data', '', "Cutoff {$st['cutoff']}: {$st['days_since_cutoff']} days (about {$st['months_since_cutoff']} months) ago.", '', $st['guidance'], '');
    }
    array_push($o, '## Calendars', '', '| Calendar | Date | Reckoned in |', '|---|---|---|');
    foreach ($s['calendars'] as $c) $o[] = "| {$c['name']} | {$c['text']}" . (isset($c['native']) ? " ({$c['native']})" : '') . " | {$c['zone']} |";
    array_push($o, '', '## World clocks', '', '| Zone | Local time | Weekday |', '|---|---|---|');
    foreach ($s['world'] as $w) $o[] = "| {$w['zone']} | {$w['iso']} | {$w['weekday']} |";
    array_push($o, '', '## Technical', '');
    $o[] = "- Unix milliseconds: {$t['unix_ms']}";
    $o[] = '- Julian Date: ' . js_num($t['julian_date']);
    $o[] = '- Modified Julian Date: ' . js_num($t['modified_julian_date']);
    $o[] = "- ISO week: {$t['iso_week']}";
    $o[] = "- Day of year: {$t['day_of_year']}";
    array_push($o, '', "_{$s['note']}_", '');
    return implode("\n", $o);
}

/** ", CEST (UTC+2, summer time)" after the local time, when the zone has that information. */
function local_suffix(array $l): string {
    if (!array_key_exists('dst', $l)) return '';
    $bits = [short_offset($l['offset'])];
    if ($l['dst']) $bits[] = 'summer time';
    if ($l['abbreviation'] === $bits[0]) return ', ' . $bits[0];
    return ', ' . ($l['abbreviation'] ?$l['abbreviation'] . ' (' . implode(', ', $bits) . ')' : implode(', ', $bits));
}

/** "Clock change: 2026-10-25 at 03:00 local, back 1 hour to CET (UTC+1)" if a change is due within $days. */
function clock_change_line(array $l, int $unix, int $days): ?string {
    $c = $l['next_change'] ?? null;
    if (!$c || strtotime($c['utc']) - $unix > $days * 86400) return null;
    [$date, $time] = explode('T', $c['local_before']);
    $to = ($c['abbreviation_after'] && !preg_match('/^[+-]\d/', $c['abbreviation_after']) ? $c['abbreviation_after'] . ' ' : '') . '(' . short_offset($c['offset_after']) . ')';
    return "Clock change: $date at " . substr($time, 0, 5) . " local, {$c['direction']} {$c['amount']} to $to";
}

/** Format a float the way JavaScript prints numbers (shortest round-trip form). */
function js_num(float $x): string {
    return (string)json_encode($x);   // relies on serialize_precision = -1 (PHP default), like JSON numbers
}

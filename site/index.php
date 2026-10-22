<?php
// Exasecond: front controller. Every request is routed here by .htaccess (Apache) or by `php -S` (local dev).
// PHP port of src/worker.js, plus "The question of time" chapters in English and Simplified Chinese.
declare(strict_types=1);
define('EXA', true);
date_default_timezone_set('UTC');
ini_set('serialize_precision', '-1');
ini_set('display_errors', '0');

require __DIR__ . '/lib/core.php';
require __DIR__ . '/lib/zones.php';
require __DIR__ . '/lib/formats.php';
require __DIR__ . '/lib/chapters.php';
require __DIR__ . '/lib/content.php';
require __DIR__ . '/lib/markdown.php';
require __DIR__ . '/lib/figures.php';
require __DIR__ . '/lib/i18n.php';
require __DIR__ . '/lib/facts.php';
require __DIR__ . '/lib/page.php';
require __DIR__ . '/lib/mcp.php';

const TYPES = [
    'text' => 'text/plain; charset=utf-8',
    'md' => 'text/markdown; charset=utf-8',
    'json' => 'application/json; charset=utf-8',
    'html' => 'text/html; charset=utf-8',
    'xml' => 'application/xml; charset=utf-8',
    'css' => 'text/css; charset=utf-8',
];

/** A response is [status, headers, body]. Kept as data so tests can call route() directly. */
function send(string $body, string $type, array $extra = [], int $status = 200): array {
    $headers = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, POST, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Accept, Mcp-Session-Id, Mcp-Protocol-Version',
        'Cache-Control' => 'no-store',
        'X-Content-Type-Options' => 'nosniff',
        'Content-Type' => TYPES[$type],
    ];
    if ($type === 'md' || $type === 'text') $headers['x-markdown-tokens'] = (string)(int)ceil(utf16_len($body) / 4); // rough token estimate
    return [$status, array_merge($headers, $extra), $body];
}

function utf16_len(string $s): int {
    return function_exists('mb_strlen') ? (int)(strlen((string)mb_convert_encoding($s, 'UTF-16LE', 'UTF-8')) / 2) : strlen($s);
}

function to_json($v): string {
    $j = json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    // JSON.stringify(x, null, 2) style: two-space indent
    return preg_replace_callback('/^( {4})+/m', fn($m) => str_repeat('  ', intdiv(strlen($m[0]), 4)), (string)$j) . "\n";
}

function json_resp($v, int $status = 200): array { return send(to_json($v), 'json', [], $status); }

/** Which representation does the client prefer? */
function negotiate(string $accept, string $fallback): string {
    $a = strtolower($accept);
    if (str_contains($a, 'text/markdown')) return 'md';
    if (str_contains($a, 'application/json') && !str_contains($a, 'text/html')) return 'json';
    if (str_contains($a, 'text/html')) return 'html';
    return $fallback;
}

/** ?tz= is forgiving (city, country, abbreviation, offset). A zone that can't be read never costs the agent the time: it gets a tz_note instead. */
function opts(array $q): array {
    $str = fn($k) => isset($q[$k]) && is_string($q[$k]) && $q[$k] !== '' ? $q[$k] : null;
    return tz_opts($str('tz'), $str('cutoff'));
}

/** Snapshot plus, for the Chinese pages' own scripts, calendar dates written in Chinese (?lang=zh). */
function snap(?array $instant, array $q): array {
    $s = snapshot($instant, opts($q));
    if (($q['lang'] ?? null) === 'zh' && extension_loaded('intl')) $s['calendars_zh'] = calendars_zh($s['unix']);
    return $s;
}

function route(string $method, string $uri, array $q, array $hdr, string $rawBody = ''): array {
    $path = rawurldecode((string)parse_url($uri, PHP_URL_PATH));
    $path = rtrim($path, '/') ?: '/';
    $accept = $hdr['accept'] ?? '';

    if ($method === 'OPTIONS') return send('', 'text', [], 204);
    if ($path === '/mcp') {
        [$st, $obj] = mcp_response($method, $rawBody);
        if ($st === 405) return send("Exasecond MCP endpoint. POST JSON-RPC 2.0 here (streamable HTTP, stateless).\n", 'text', ['Allow' => 'POST'], 405);
        if ($obj === null) return send('', 'json', [], $st);
        return send((string)json_encode($obj, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'json', [], $st);
    }
    if (!in_array($method, ['GET', 'HEAD'], true)) return send("Method not allowed\n", 'text', ['Allow' => 'GET, HEAD'], 405);
    // The stylesheet is the one thing that may be cached: it holds no time. Pages link it with ?v=<hash>, so changes reach browsers at once.
    if ($path === '/site.css') return send(site_css(), 'css', ['Cache-Control' => 'public, max-age=86400']);

    try {
        $lang = 'en';
        $p = $path;
        if ($p === '/zh' || str_starts_with($p, '/zh/')) { $lang = 'zh'; $p = substr($p, 3) ?: '/'; }

        // Human pages that exist in both languages
        if ($p === '/') {
            if ($lang === 'en') {
                $want = negotiate($accept, 'html');
                if ($want === 'md') return send(now_md(snapshot(), 'Exasecond: current time'), 'md', ['Vary' => 'Accept']);
                if ($want === 'json') return json_resp(snapshot());
            }
            return send(render_home(snapshot(), $lang), 'html', ['Vary' => 'Accept', 'Link' => '</llms.txt>; rel="describedby"; type="text/plain"']);
        }
        if ($p === '/time') {
            // Browsers get the contents page; agents keep the old meaning of /time (an alias of /now).
            if ($lang === 'zh' || negotiate($accept, 'text') === 'html') return send(render_time_index($lang, snapshot()), 'html', ['Vary' => 'Accept']);
            $path = '/now';
        }
        if ($p === '/time.md') return send(time_index_md($lang), 'md');
        if (preg_match('#^/time/([a-z]+)(\.md)?$#', $p, $m) && ($c = chapter($lang, $m[1]))) {
            if (!empty($m[2]) || negotiate($accept, 'html') === 'md') return send(chapter_md($c, $lang), 'md', ['Vary' => 'Accept']);
            return send(render_chapter($c, $lang, snapshot()), 'html', ['Vary' => 'Accept']);
        }
        if (($p === '/about' || $p === '/about.md') && ($d = load_doc($lang, 'about'))) {
            if ($p === '/about.md' || negotiate($accept, 'html') === 'md') return send(doc_md($d), 'md', ['Vary' => 'Accept']);
            return send(render_about($d, $lang, snapshot()), 'html', ['Vary' => 'Accept']);
        }
        if ($lang === 'zh') return send(render_not_found('zh', $path), 'html', [], 404);

        if (($path === '/developers' || $path === '/developers.md') && ($d = load_doc('en', 'developers'))) {
            if ($path === '/developers.md' || negotiate($accept, 'html') === 'md') return send(doc_md($d), 'md', ['Vary' => 'Accept']);
            return send(render_doc($d, '/developers', snapshot()), 'html', ['Vary' => 'Accept']);
        }
        // Downloadable developer files: a fixed list, never a path from the request.
        $devFiles = ['LICENSE' => 'text', 'openwebui_exasecond.py' => 'text', 'exasecond.py' => 'text', 'exasecond.js' => 'js', 'openai-tool.json' => 'json', 'anthropic-tool.json' => 'json', 'mcp.json' => 'json'];
        if (preg_match('#^/dev/([A-Za-z_.-]+)$#', $path, $m) && isset($devFiles[$m[1]])) {
            $type = $devFiles[$m[1]] === 'js' ? 'text' : $devFiles[$m[1]];
            $extra = $devFiles[$m[1]] === 'js' ? ['Content-Type' => 'text/javascript; charset=utf-8'] : [];
            return send((string)file_get_contents(content_path('dev', $m[1])), $type, $extra);
        }

        switch ($path) {
            case '/now':
                $s = snap(null, $q);
                $want = negotiate($accept, 'text');
                if ($want === 'md') return send(now_md($s), 'md', ['Vary' => 'Accept']);
                if ($want === 'json') return json_resp($s);
                return send(now_text($s), 'text', ['Vary' => 'Accept']);
            case '/now.md': case '/index.md': return send(now_md(snap(null, $q)), 'md');
            case '/now.json': case '/api/now': return json_resp(snap(null, $q));
            case '/now.txt': return send(now_text(snap(null, $q)), 'text');
            case '/utc': return send(iso_utc(time()) . "\n", 'text');
            case '/unix': return send(time() . "\n", 'text');
            case '/universe': return send(unix_to_universe(time()) . "\n", 'text');
            case '/api/convert': return json_resp(snap(parse_instant($q), $q));
            case '/llms.txt': return send(llms_txt(snapshot()), 'text');
            case '/llms-full.txt': return send(llms_full_txt(snapshot()), 'text');
            case '/spec.md': return send(spec_md(), 'md');
            case '/story': case '/pottery-lecture':
                if (negotiate($accept, 'html') === 'md') return send(story_md(), 'md', ['Vary' => 'Accept']);
                return send(render_story_page(snapshot()), 'html', ['Vary' => 'Accept']);
            case '/story.md': return send(story_md(), 'md');
            case '/openapi.json': return json_resp(openapi());
            case '/robots.txt': return send(robots_txt(), 'text');
            case '/sitemap.xml': return send(sitemap_xml(), 'xml');
            case '/health': return json_resp(['ok' => true]);
            case '/local': case '/local.txt':
                $o = opts($q);
                $s = snapshot(null, $o);
                $line = local_sentence($s, $o['tz_res'], ($q['lang'] ?? '') === 'zh' ? 'zh' : 'en');
                if ($path === '/local' && negotiate($accept, 'text') === 'json') return json_resp(['text' => $line, 'utc' => $s['utc'], 'local' => $s['local']] + (isset($s['tz_note']) ? ['tz_note' => $s['tz_note']] : []));
                return send($line . "\n", 'text', ['Vary' => 'Accept']);
            case '/fact': case '/fact.txt': case '/fact.json':
                // General fact without a place; with ?tz= a place-specific one when there is one. ?lang=zh for Chinese.
                $o = opts($q);
                $f = pick_fact(time(), $o['tz'], ($q['lang'] ?? '') === 'zh' ? 'zh' : 'en');
                if ($path === '/fact.json' || negotiate($accept, 'text') === 'json') return json_resp($f + ['url' => SITE . $f['href']]);
                $zhF = ($q['lang'] ?? '') === 'zh';
                return send(($zhF ? '你知道吗？' : 'Did you know? ') . $f['text'] . "\n" . ($zhF ? '详见：' : 'More: ') . SITE . $f['href'] . "\n", 'text', ['Vary' => 'Accept']);
            case '/zones': case '/zones.txt':
                $zq = isset($q['q']) && is_string($q['q']) ? $q['q'] : null;
                if ($path === '/zones' && negotiate($accept, 'text') === 'json') return json_resp(zones_list(time(), $zq));
                return send(zones_text(time(), $zq), 'text', ['Vary' => 'Accept']);
            case '/zones.json': return json_resp(zones_list(time(), isset($q['q']) && is_string($q['q']) ? $q['q'] : null));
        }
        if (str_contains($accept, 'text/html')) return send(render_not_found('en', $path), 'html', [], 404);
        return send("Not found: $path\nTry /now, /utc, /llms.txt\n", 'text', [], 404);
    } catch (InputError $e) {
        return json_resp(['error' => $e->getMessage()], 400);
    }
}

// ---- Run (skipped when a test includes this file) -------------------------------------
if (!defined('EXA_TEST')) {
    $hdr = [];
    foreach ($_SERVER as $k => $v) if (str_starts_with($k, 'HTTP_')) $hdr[strtolower(str_replace('_', '-', substr($k, 5)))] = $v;
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    try {
        [$status, $headers, $body] = route($method, $_SERVER['REQUEST_URI'] ?? '/', $_GET, $hdr, $method === 'POST' ? (string)file_get_contents('php://input') : '');
    } catch (Throwable $e) {
        error_log('exasecond: ' . $e);
        [$status, $headers, $body] = send("Internal error\n", 'text', [], 500);
    }
    http_response_code($status);
    foreach ($headers as $k => $v) header("$k: $v");
    if ($method !== 'HEAD' && $status !== 204) echo $body;
}

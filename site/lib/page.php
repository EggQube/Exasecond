<?php
// Human pages: the exasecond clock, "The question of time" and the play. Server-rendered with the live time baked in,
// so agents that don't run JavaScript still see the current time in the HTML.
defined('EXA') or exit;

function lang_prefix(string $lang): string { return $lang === 'zh' ? '/zh' : ''; }
function other_lang(string $lang): string { return $lang === 'zh' ? 'en' : 'zh'; }

function site_css(): string {
    return <<<'CSS'
:root {
  --bg: #07080c; --bg-2: #0e1017; --line: #23262f; --ink: #ece8df; --ink-2: #9c9a93; --ink-3: #6e6d74;
  --accent: #f0b44c; --accent-2: #f0b44c22;
  --mono: ui-monospace, "SF Mono", "JetBrains Mono", Menlo, Consolas, monospace;
  --sans: ui-sans-serif, -apple-system, "Segoe UI", Inter, Roboto, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "Noto Sans SC", sans-serif;
  --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, "Songti SC", "Noto Serif SC", "Source Han Serif SC", SimSun, serif;
  --read: var(--serif);
}
@media (prefers-color-scheme: light) {
  :root { --bg: #f4f1e9; --bg-2: #ebe6da; --line: #d6d0c2; --ink: #16171c; --ink-2: #55545a; --ink-3: #7c7a73; --accent: #a8590f; --accent-2: #a8590f1a; }
}
:lang(zh) { --read: var(--sans); }
* { box-sizing: border-box; }
html, body { margin: 0; background: var(--bg); color: var(--ink); }
body { font: 16px/1.55 var(--sans); -webkit-font-smoothing: antialiased; }
a { color: inherit; text-decoration-color: var(--ink-3); text-underline-offset: 3px; }
a:hover { text-decoration-color: var(--accent); }
.wrap { max-width: 1120px; margin: 0 auto; padding: 0 16px; }
@media (min-width: 720px) { .wrap { padding: 0 40px; } }
code { font-family: var(--mono); font-size: .9em; }
sup { font-size: .7em; line-height: 0; }

.agentbar { border-bottom: 1px solid var(--line); font: 11.5px/1.5 var(--mono); color: var(--ink-3); padding: 8px 0; opacity: .7; }
.agentbar b { font-weight: 600; }
.agentbar a { color: inherit; text-decoration-color: var(--line); overflow-wrap: anywhere; }
.agentbar:hover { opacity: 1; }
.agentbar { overflow: hidden; max-height: 8em; transition: max-height .7s ease, padding .7s ease, opacity .7s ease, border-color .7s ease; }
.agentbar.folded { max-height: 0; padding-top: 0; padding-bottom: 0; opacity: 0; border-bottom-color: transparent; }
@media (prefers-reduced-motion: reduce) { .agentbar { transition: none; } }
@media (max-width: 560px) {
  .agentbar { font-size: 9.5px; padding: 6px 0; }
  .top { align-items: center; gap: 12px; }
  .top nav { font-size: 11px; gap: 12px; color: var(--ink-3); border-left: 1px solid var(--line); padding-left: 12px; }
}
.agentbar a:hover { color: var(--accent); }
.top { display: flex; justify-content: space-between; align-items: baseline; padding: 24px 0; gap: 16px; }
.mark { font: 600 15px/1 var(--mono); letter-spacing: .32em; text-transform: uppercase; text-decoration: none; white-space: nowrap; }
.mark b { color: var(--accent); font-weight: 600; }
.top nav { font: 13px/1 var(--mono); color: var(--ink-2); display: flex; gap: 18px; flex-wrap: wrap; justify-content: flex-end; }
.top nav a { text-decoration: none; }
.top nav a:hover, .top nav a[aria-current] { color: var(--ink); text-decoration: underline; text-decoration-color: var(--accent); }

/* clock */
.hero { padding: 6vh 0 6vh; border-top: 1px solid var(--line); }

/* the answer: first read */
.answer { padding: 5vh 0 4vh; display: grid; gap: 22px; }
.ans-row { display: grid; grid-template-columns: 9.5em 1fr; gap: 6px 24px; align-items: baseline; }
.ans-k { font: 12px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; }
.ans-v { margin: 0; min-width: 0; }
.ans-now { font: 500 clamp(1.15rem, 2.6vw, 1.6rem)/1.3 var(--mono); font-variant-numeric: tabular-nums; }
.ans-mine { margin: 0; font: clamp(1.1rem, 2.3vw, 1.45rem)/1.4 var(--serif); }
.ans-intro { margin: 0 0 6px; color: var(--ink-2); font-size: 15px; }
.ans-world { list-style: none; margin: 0; padding: 0; font: 13.5px/1.6 var(--mono); color: var(--ink-2); }
.ans-fact { font: clamp(1rem, 2vw, 1.2rem)/1.5 var(--serif); color: var(--ink); max-width: 62ch; }
.ans-fact a { font: 13px/1 var(--mono); color: var(--accent); text-decoration: none; white-space: nowrap; }
@media (max-width: 640px) { .ans-row { grid-template-columns: 1fr; } }
.eyebrow { font: 13px/1.4 var(--mono); color: var(--ink-2); letter-spacing: .04em; margin: 0 0 18px; }
.count { font: 500 clamp(1.4rem, 5.6vw, 4.6rem)/1 var(--mono); letter-spacing: -.035em; font-variant-numeric: tabular-nums; margin: 0; white-space: nowrap; }
.count .tick { color: var(--accent); }
.es { margin: 18px 0 0; font: clamp(1rem, 2.2vw, 1.35rem)/1.3 var(--serif); color: var(--ink-2); font-style: italic; }
.es span { font-style: normal; font-family: var(--mono); color: var(--ink); font-size: .9em; }
.span { margin: 56px 0 0; }
.bar { position: relative; height: 2px; background: var(--line); }
.bar i { position: absolute; left: 0; top: 0; bottom: 0; background: var(--accent); }
.bar em { position: absolute; top: -6px; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 6px var(--accent-2); }
.bar-labels { display: flex; justify-content: space-between; font: 12px/1.4 var(--mono); color: var(--ink-3); margin-top: 14px; gap: 12px; }
.bar-labels span:last-child { text-align: right; }
.here { position: relative; font: 12px/1.4 var(--mono); color: var(--accent); margin-bottom: 14px; height: 1.4em; }
.here span { position: absolute; transform: translateX(-50%); white-space: nowrap; }

section { border-top: 1px solid var(--line); padding: 44px 0; }
h2 { font: 400 clamp(1.4rem, 3vw, 1.9rem)/1.25 var(--serif); margin: 0 0 6px; }
.sub { color: var(--ink-2); margin: 0 0 28px; max-width: 62ch; }
.human { display: grid; gap: 28px; grid-template-columns: 1fr; margin-bottom: 36px; }
@media (min-width: 720px) { .human { grid-template-columns: 1fr 1fr; } }
.big-label { font: 12px/1 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; margin-bottom: 10px; }
.big-time { font: 500 clamp(1.3rem, 3.4vw, 2rem)/1.2 var(--mono); font-variant-numeric: tabular-nums; }
.big-time small { display: block; font: 14px/1.4 var(--sans); color: var(--ink-2); margin-top: 6px; }
.cals { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); border-top: 1px solid var(--line); border-left: 1px solid var(--line); }
.cal { padding: 16px 18px 18px; border-right: 1px solid var(--line); border-bottom: 1px solid var(--line); display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.cal-name { font: 12px/1.3 var(--mono); color: var(--ink-3); }
.cal-date { font-size: 17px; overflow-wrap: anywhere; }
.cal-native { color: var(--ink-2); font-size: 15px; }
form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
input, select, button { font: 15px/1 var(--mono); color: var(--ink); background: var(--bg-2); border: 1px solid var(--line); border-radius: 6px; padding: 12px 14px; min-width: 0; }
input { flex: 1 1 260px; }
button { background: var(--accent); color: var(--bg); border-color: var(--accent); cursor: pointer; font-weight: 600; }
input:focus, select:focus, button:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.out { font: 14px/1.7 var(--mono); white-space: pre; overflow-x: auto; color: var(--ink-2); min-height: 1.7em; margin: 0; }
.out.err { color: #e5675a; }
pre.agents { background: var(--bg-2); border: 1px solid var(--line); border-radius: 8px; padding: 18px 20px; overflow-x: auto; font: 13.5px/1.75 var(--mono); margin: 0; }
pre.agents .c { color: var(--ink-3); }
.agent-links { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 18px; font: 14px/1.4 var(--mono); }

/* chapter grid */
.chapters { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); border-top: 1px solid var(--line); border-left: 1px solid var(--line); counter-reset: none; }
.chapters li { border-right: 1px solid var(--line); border-bottom: 1px solid var(--line); min-width: 0; }
.chapters a { display: flex; flex-direction: column; gap: 6px; padding: 18px 20px 22px; height: 100%; text-decoration: none; }
.chapters a:hover { background: var(--bg-2); }
.chapters .num { font: 12px/1 var(--mono); color: var(--accent); }
.chapters .ttl { font: 400 19px/1.3 var(--serif); }
.chapters .dsc { font-size: 14px; color: var(--ink-2); }
.more { margin-top: 18px; font: 14px/1.4 var(--mono); }

/* reading pages */
.read { max-width: 720px; margin: 0 auto; padding: 4vh 0 24px; }
.kicker { font: 12px/1.5 var(--mono); color: var(--ink-2); letter-spacing: .06em; margin: 0 0 18px; }
.kicker a { text-decoration: none; color: var(--accent); }
.read h1 { font: 400 clamp(2rem, 6vw, 3.1rem)/1.12 var(--serif); margin: 0 0 18px; letter-spacing: -.01em; }
.lede { font: clamp(1.1rem, 2.4vw, 1.3rem)/1.5 var(--serif); color: var(--ink-2); margin: 0 0 40px; }
.prose { font: 18.5px/1.72 var(--read); }
.prose:lang(zh) { font-size: 17px; line-height: 1.9; }
.prose p, .prose ul, .prose ol { margin: 0 0 1.05em; }
.prose ul, .prose ol { padding-left: 1.3em; }
.prose li { margin-bottom: .4em; }
.prose h2 { margin: 2.2em 0 .6em; font-size: clamp(1.35rem, 3vw, 1.7rem); }
.prose h3 { font: 600 13px/1.4 var(--mono); letter-spacing: .14em; text-transform: uppercase; color: var(--ink-2); margin: 2em 0 .8em; }
.prose blockquote { margin: 1.5em 0; padding: .2em 0 .2em 1.1em; border-left: 2px solid var(--accent); color: var(--ink-2); }
.prose blockquote p:last-child { margin-bottom: 0; }
.prose hr { border: 0; border-top: 1px solid var(--line); margin: 2.5em 0; }
.prose strong { font-weight: 600; }
.prose pre { background: var(--bg-2); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; overflow-x: auto; font: 14px/1.6 var(--mono); }
.table { overflow-x: auto; margin: 1.4em 0 1.8em; border-top: 1px solid var(--line); }
.prose table { border-collapse: collapse; width: 100%; font: 15px/1.5 var(--sans); }
.prose th { text-align: left; font: 12px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .08em; }
.prose th, .prose td { padding: 10px 12px 10px 0; border-bottom: 1px solid var(--line); vertical-align: top; }
.prose td:first-child { white-space: nowrap; }
#sources + ul, #来源 + ul { font: 14.5px/1.6 var(--sans); color: var(--ink-2); }
.evidence { font: 14px/1.6 var(--sans); color: var(--ink-2); border: 1px solid var(--line); border-radius: 8px; padding: 14px 16px; margin: 0 0 36px; }
.evidence b { color: var(--ink); font-weight: 600; }
.toc { list-style: none; padding: 0; margin: 0; border-top: 1px solid var(--line); }
.toc li { border-bottom: 1px solid var(--line); }
.toc a { display: grid; grid-template-columns: 3.2em 1fr; gap: 4px 12px; padding: 18px 0; text-decoration: none; }
.toc .num { font: 500 22px/1.2 var(--mono); color: var(--accent); grid-row: span 2; }
.toc .ttl { font: 400 21px/1.3 var(--serif); }
.toc .dsc { color: var(--ink-2); font-size: 15px; }
.toc a:hover .ttl { text-decoration: underline; text-decoration-color: var(--accent); }
.pager { display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; border-top: 1px solid var(--line); margin-top: 56px; padding-top: 22px; font: 14px/1.4 var(--sans); }
.pager a { text-decoration: none; }
.pager small { display: block; font: 11px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .12em; margin-bottom: 4px; }
.pager .nx { text-align: right; }
.pager .ct { font: 12px/1.4 var(--mono); color: var(--ink-2); align-self: end; }
.pager a:hover span { text-decoration: underline; text-decoration-color: var(--accent); }

/* log ruler figure */
.ruler { margin: 2em 0 2.2em; padding: 18px 0 0; border-top: 1px solid var(--line); }
.lr { list-style: none; margin: 0; padding: 0; }
.lr li { display: grid; grid-template-columns: 1fr auto; gap: 2px 12px; padding: 7px 0 9px; font: 14px/1.4 var(--sans); }
.lr-val { font: 12.5px/1.4 var(--mono); color: var(--ink-2); text-align: right; white-space: nowrap; }
.lr-track { grid-column: 1 / -1; position: relative; height: 9px; }
.lr-track::before { content: ""; position: absolute; left: 0; right: 0; top: 4px; height: 1px; background: var(--line); }
.lr-track i { position: absolute; top: 0; width: 9px; height: 9px; margin-left: -4.5px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 4px var(--accent-2); }
.lr-axis { position: relative; height: 18px; margin-top: 6px; font: 11px/1 var(--mono); color: var(--ink-3); border-top: 1px solid var(--line); }
.lr-axis span { position: absolute; top: 6px; transform: translateX(-50%); white-space: nowrap; }
.lr-axis span:first-child { transform: none; }
.lr-axis span:last-child { transform: translateX(-100%); }
@media (max-width: 560px) { .lr-axis span.odd { display: none; } }
.ruler figcaption { font: 13.5px/1.55 var(--sans); color: var(--ink-3); margin-top: 16px; }

/* play */
.play { font: 19px/1.7 var(--serif); }
.play h1 { font-weight: 400; font-size: clamp(2rem, 6vw, 3rem); line-height: 1.1; margin: 0 0 16px; }
.play h2 { font: 600 12px/1.4 var(--mono); letter-spacing: .2em; text-transform: uppercase; color: var(--ink-3); margin: 48px 0 20px; }
.play hr { border: 0; border-top: 1px solid var(--line); margin: 40px 0; }
.play p { margin: 0 0 18px; }
.play .line .who { display: block; font: 600 12px/1.4 var(--mono); letter-spacing: .14em; text-transform: uppercase; color: var(--accent); margin-bottom: 4px; }
.play .aside { color: var(--ink-2); text-align: center; }
.play ol { padding-left: 1.4em; margin: 0 0 18px; }

footer.site { border-top: 1px solid var(--line); padding: 32px 0 20px; color: var(--ink-3); font-size: 14px; }
.copy { font: 12px/1.4 var(--mono); color: var(--ink-3); margin: 0; padding: 20px 0 48px; border-top: 1px solid var(--line); }
footer.site + .copy { border-top: 0; padding-top: 4px; }
footer.site p { max-width: 75ch; margin: 0 0 10px; }
CSS;
}

const FAVICON = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='14' fill='none' stroke='%23f0b44c' stroke-width='3'/%3E%3Cpath d='M16 7v9l6 4' stroke='%23f0b44c' stroke-width='3' fill='none' stroke-linecap='round'/%3E%3C/svg%3E";

/**
 * $o: title, desc, path (this page, without language prefix), alt (null if no translation), md (markdown twin href),
 *     head (extra head html), active (nav key)
 */
function layout(string $lang, array $o, string $body): string {
    $L = fn($k) => t($lang, $k);
    $path = $o['path'] ?? '/';
    $hasAlt = $o['alt'] ?? true;
    $enUrl = SITE . ($path === '/' ? '/' : $path);
    $zhUrl = SITE . '/zh' . ($path === '/' ? '/' : $path);
    $other = other_lang($lang);
    $otherHref = $other === 'zh' ? '/zh' . ($path === '/' ? '/' : $path) : $path;
    $head = '';
    if ($hasAlt) {
        $head .= '<link rel="alternate" hreflang="en" href="' . h($enUrl) . '">' . "\n"
            . '<link rel="alternate" hreflang="zh-Hans" href="' . h($zhUrl) . '">' . "\n"
            . '<link rel="alternate" hreflang="x-default" href="' . h($enUrl) . '">' . "\n";
    }
    $head .= '<link rel="canonical" href="' . h($lang === 'zh' ? $zhUrl : $enUrl) . '">' . "\n";
    if (!empty($o['md'])) $head .= '<link rel="alternate" type="text/markdown" href="' . h($o['md']) . '" title="Markdown">' . "\n";
    $head .= '<link rel="alternate" type="text/plain" href="/llms.txt" title="llms.txt">' . "\n";
    $head .= $o['head'] ?? '';
    $p = lang_prefix($lang);
    $cur = fn($k) => ($o['active'] ?? '') === $k ? ' aria-current="page"' : '';
    $nav = '<a href="' . $p . '/time"' . $cur('time') . '>' . h($L('nav_time')) . '</a>'
        . '<a href="' . ($p ?: '') . '/#agents">' . h($L('nav_agents')) . '</a>'
        . ($hasAlt ? '<a href="' . h($otherHref) . '" hreflang="' . ($other === 'zh' ? 'zh-Hans' : 'en') . '" lang="' . ($other === 'zh' ? 'zh-Hans' : 'en') . '" title="' . h($L('nav_other_title')) . '">' . h($L('nav_other')) . '</a>' : '');
    $css = site_css();
    $title = h($o['title']);
    $desc = h($o['desc'] ?? '');
    $htmlLang = $L('html_lang');
    $home = $p . '/';
    $fav = FAVICON;
    $year = gmdate('Y');
    // Visible, quiet, first in the page: AI fetchers often shorten pages and lose the bottom.
    $ex = $L('agentbar_tz');
    $agentbar = '<b>' . h($L('agentbar_who')) . '</b> ' . h($L('agentbar_what')) . ' → <a href="/local?tz=' . h(rawurlencode($ex)) . '">exasecond.com/local?tz=' . h($ex) . '</a> <span class="ab-more">' . h($L('agentbar_more')) . '</span>';
    return <<<HTML
<!doctype html>
<html lang="$htmlLang">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title</title>
<meta name="description" content="$desc">
<meta property="og:title" content="$title">
<meta property="og:description" content="$desc">
<meta property="og:site_name" content="Exasecond">
$head<link rel="icon" href="$fav">
<style>
$css
</style>
</head>
<body>
<div class="agentbar" role="note"><div class="wrap">$agentbar</div></div>
<div class="wrap">
  <header class="top">
    <a class="mark" href="$home">Exa<b>·</b>second</a>
    <nav>$nav</nav>
  </header>
$body
  <p class="copy">© exasecond.com 2021-$year</p>
</div>
<script>
// The agent bar is for machines (who read the HTML and don't run this). People see it briefly, then it folds away.
setTimeout(() => {
  const b = document.querySelector(".agentbar");
  if (!b) return;
  const h = b.offsetHeight;
  if (window.scrollY > h) { b.style.transition = "none"; b.classList.add("folded"); window.scrollBy(0, -h); }
  else b.classList.add("folded");
}, 2000);
</script>
</body>
</html>

HTML;
}

function chapter_url(string $lang, string $slug): string { return lang_prefix($lang) . '/time/' . $slug; }

function chapter_cards(string $lang): string {
    $out = '';
    foreach (chapters($lang) as $c) {
        $out .= '<li><a href="' . h(chapter_url($lang, $c['slug'])) . '"><span class="num">' . sprintf('%02d', $c['n']) . '</span>'
            . '<span class="ttl">' . h($c['title']) . '</span><span class="dsc">' . h($c['description']) . '</span></a></li>';
    }
    return '<ol class="chapters">' . $out . '</ol>';
}

function render_home(array $s, string $lang): string {
    $L = fn($k) => t($lang, $k);
    $pct = number_format($s['universe_fraction_percent'], 4, '.', '');
    $zh = $lang === 'zh';
    $zhDates = $zh ? calendars_zh($s['unix']) : [];
    $cal = '';
    foreach ($s['calendars'] as $c) {
        $name = $zh ? (CAL_NAMES_ZH[$c['id']] ?? $c['name']) : $c['name'];
        $date = $zh ? ($zhDates[$c['id']] ?? $c['text']) : $c['text'];
        $native = (!$zh && isset($c['native'])) ? '<span class="cal-native" lang="zh">' . h($c['native']) . '</span>' : ($zh ? '<span class="cal-native" lang="en">' . h($c['text']) . '</span>' : '');
        $cal .= "\n        <li class=\"cal\" data-id=\"" . h($c['id']) . "\"><span class=\"cal-name\">" . h($name) . "</span><span class=\"cal-date\">" . h($date) . "</span>$native</li>";
    }
    $S = SITE;
    $C = OFFSET;
    $count = group_digits($s['universe_seconds']);
    $weekday = $zh ? (WEEKDAYS_ZH[$s['technical']['weekday']] ?? $s['technical']['weekday']) : $s['technical']['weekday'];
    $esLine = sprintf(h($L('es_line')), '<span id="es">' . h($s['exaseconds']) . '</span>');
    $p = lang_prefix($lang);
    $cfg = json_encode([
        'offset' => (string)OFFSET, 'serverMs' => $s['unix'] * 1000, 'lang' => $lang,
        'calNames' => $zh ? CAL_NAMES_ZH : null,
        'days' => $zh ? WEEKDAYS_ZH : null,
        'labels' => $zh
            ? ['universe' => '宇宙秒', 'es' => '艾秒', 'utc' => 'UTC', 'zone' => '你的时区', 'unix' => 'Unix', 'fail' => '无法换算这个时刻']
            : ['universe' => 'Universe seconds', 'es' => 'Exaseconds', 'utc' => 'UTC', 'zone' => 'Your zone', 'unix' => 'Unix', 'fail' => 'Could not translate that'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    $js = home_js();
    // The answer comes first: the time now, the reader's time, one fact. The counter is the visual that follows.
    $nowD = dt_utc($s['unix']);
    $nowLine = $zh
        ? $nowD->format('Y') . '年' . $nowD->format('n') . '月' . $nowD->format('j') . '日 ' . WEEKDAYS_ZH[$nowD->format('l')] . ' ' . $nowD->format('H:i:s') . ' UTC'
        : $nowD->format('l j F Y, H:i:s') . ' UTC';
    $world = implode('', array_map(fn($l) => '<li>' . h($l) . '</li>', world_now_lines($s['unix'], $lang)));
    $fact = pick_fact($s['unix'], null, $lang);
    $answer = '<section class="answer" aria-label="' . h($L('ans_label')) . '">'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_now')) . '</span><p class="ans-v ans-now" id="ans-now">' . h($nowLine) . '</p></div>'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_you')) . '</span><div class="ans-v" id="ans-you"><p class="ans-intro">' . h($L('ans_you_intro')) . '</p><ul class="ans-world">' . $world . '</ul></div></div>'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_fact')) . '</span><p class="ans-v ans-fact" id="ans-fact"><span>' . md_inline($fact['text']) . '</span> <a href="' . h($fact['href']) . '">' . h($L('ans_more')) . ' →</a></p></div>'
        . '</section>';
    $body = <<<HTML
  <main>
    $answer
    <div class="hero">
      <p class="eyebrow">{$L('eyebrow')}</p>
      <p class="count" id="count" aria-live="off">$count</p>
      <p class="es">$esLine</p>
      <div class="span" aria-hidden="true">
        <div class="here"><span style="left:$pct%">{$L('here')}</span></div>
        <div class="bar"><i style="width:$pct%"></i><em style="left:$pct%"></em></div>
        <div class="bar-labels"><span>{$L('bar_start')}</span><span>{$L('bar_end')}</span></div>
      </div>
    </div>

    <section aria-labelledby="h-human">
      <h2 id="h-human">{$L('human_h')}</h2>
      <p class="sub">{$L('human_sub')}</p>
      <div class="human">
        <div>
          <div class="big-label">{$L('utc')}</div>
          <div class="big-time" id="utc">{$s['utc']}<small>$weekday · {$L('iso_week')} {$s['technical']['iso_week']} · {$L('unix')} <span id="unix">{$s['unix']}</span></small></div>
        </div>
        <div>
          <div class="big-label">{$L('your_clock')}</div>
          <div class="big-time" id="local">-<small id="local-zone">{$L('enable_js')}</small></div>
        </div>
      </div>
      <ul class="cals" id="cals">$cal
      </ul>
    </section>

    <section aria-labelledby="h-q">
      <h2 id="h-q"><a href="$p/time" style="text-decoration:none">{$L('q_h')}</a></h2>
      <p class="sub">{$L('q_sub')}</p>
HTML;
    $body .= chapter_cards($lang);
    $body .= <<<HTML

    </section>

    <section aria-labelledby="h-tr">
      <h2 id="h-tr">{$L('tr_h')}</h2>
      <p class="sub">{$L('tr_sub')}</p>
      <form id="tr">
        <input id="tr-in" name="v" placeholder="2030-01-01T00:00:00Z" autocomplete="off" aria-label="{$L('tr_label')}">
        <select id="tr-kind" aria-label="{$L('tr_kind')}">
          <option value="iso">{$L('tr_iso')}</option><option value="unix">{$L('tr_unix')}</option>
          <option value="universe">{$L('tr_universe')}</option><option value="es">{$L('tr_es')}</option>
        </select>
        <button type="submit">{$L('tr_button')}</button>
      </form>
      <pre class="out" id="tr-out"></pre>
    </section>

    <section id="agents" aria-labelledby="h-agents">
      <h2 id="h-agents">{$L('ag_h')}</h2>
      <p class="sub">{$L('ag_sub')}</p>
<pre class="agents" lang="en"><span class="c"># current time, ~8 lines</span>
curl $S/now

<span class="c"># with your time zone and training cutoff</span>
curl "$S/now?tz=Asia/Shanghai&amp;cutoff=2025-06"

<span class="c"># just the timestamp</span>
curl $S/utc

<span class="c"># MCP: add as a remote server</span>
$S/mcp</pre>
      <div class="agent-links" lang="en">
        <a href="/llms.txt">/llms.txt</a><a href="/llms-full.txt">/llms-full.txt</a><a href="/now.md">/now.md</a>
        <a href="/now.json">/now.json</a><a href="/openapi.json">/openapi.json</a><a href="/spec.md">/spec.md</a><a href="$p/time.md">$p/time.md</a>
      </div>
    </section>
  </main>

  <footer class="site">
HTML;
    $body .= '<p>' . sprintf($L('foot_convention'), $C) . '</p>';
    $body .= '<p>' . $L('foot_story') . '</p>';
    $body .= '<p>' . h($L('foot_lifespan')) . '</p>';
    $body .= "\n  </footer>\n<script>\nconst EXA = $cfg;\n$js</script>";
    return layout($lang, [
        'title' => $L('home_title'),
        'desc' => $L('home_desc') . ($zh ? ' 现在：' : ' Now: ') . $s['utc'] . ' UTC.',
        'path' => '/', 'md' => '/now.md',
        'head' => '<link rel="alternate" type="application/json" href="/now.json" title="Current time as JSON">' . "\n",
    ], $body);
}

function home_js(): string {
    return <<<'JS'
(() => {
  const C = BigInt(EXA.offset);
  const skew = EXA.serverMs - Date.now(); // align to server clock (second precision)
  const $ = (id) => document.getElementById(id);
  const group = (s) => s.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const loc = EXA.lang === "zh" ? "zh-CN" : undefined;
  const localFmt = new Intl.DateTimeFormat(loc, { dateStyle: "full", timeStyle: "medium" });

  function tick() {
    const ms = Date.now() + skew;
    const unix = Math.floor(ms / 1000);
    const u = (BigInt(unix) + C).toString();
    $("count").innerHTML = group(u.slice(0, -3)) + ',<span class="tick">' + u.slice(-3) + "</span>";
    const frac = u.padStart(19, "0");
    $("es").textContent = frac.slice(0, -18) + "." + frac.slice(-18, -6);
    const d = new Date(unix * 1000);
    $("utc").firstChild.nodeValue = d.toISOString().replace(/\.\d{3}Z$/, "Z");
    $("unix").textContent = unix;
    $("local").firstChild.nodeValue = d.toLocaleTimeString(loc, { hour12: false });
    $("local-zone").textContent = localFmt.format(d) + " · " + tz;
    setTimeout(tick, 1000 - ((Date.now() + skew) % 1000) + 5);
  }
  tick();

  // The answer block: "now" ticks; "your time" and the fact become specific to this reader's time zone.
  const nowFmt = new Intl.DateTimeFormat(EXA.lang === "zh" ? "zh-CN" : "en-GB", { timeZone: "UTC", weekday: "long", day: "numeric", month: "long", year: "numeric" });
  function tickNow() {
    const d = new Date(Date.now() + skew);
    const hms = d.toISOString().slice(11, 19);
    $("ans-now").textContent = EXA.lang === "zh" ? nowFmt.format(d).replace("星期", " 星期") + " " + hms + " UTC" : nowFmt.format(d).replace(/^(\p{L}+),/u, "$1") + ", " + hms + " UTC";
    setTimeout(tickNow, 1000 - ((Date.now() + skew) % 1000) + 5);
  }
  tickNow();
  const lq = "tz=" + encodeURIComponent(tz) + (EXA.lang === "zh" ? "&lang=zh" : "");
  async function yourTime() {
    try {
      const r = await fetch("/local?" + lq, { headers: { Accept: "application/json" }, cache: "no-store" });
      const j = await r.json();
      if (j.local) { const p = document.createElement("p"); p.className = "ans-mine"; p.textContent = j.text; $("ans-you").replaceChildren(p); }
    } catch {}
  }
  yourTime();
  setInterval(yourTime, 60000);
  (async () => {
    try {
      const f = await (await fetch("/fact.json?" + lq, { cache: "no-store" })).json();
      const el = $("ans-fact");
      const esc = (t) => t.replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c]);
      el.firstElementChild.innerHTML = esc(f.text).replace(/10\^(-?\d+)/g, "10<sup>$1</sup>");
      el.querySelector("a").href = f.href;
    } catch {}
  })();

  const calName = (c) => (EXA.calNames && EXA.calNames[c.id]) || c.name;
  const calDate = (s, c) => (s.calendars_zh && s.calendars_zh[c.id]) || c.text;
  const q = EXA.lang === "zh" ? "&lang=zh" : "";

  async function refreshCalendars() {
    try {
      const r = await fetch("/now.json?tz=" + encodeURIComponent(tz) + q, { cache: "no-store" });
      const s = await r.json();
      for (const c of s.calendars) {
        const li = document.querySelector('.cal[data-id="' + c.id + '"]');
        if (li) li.querySelector(".cal-date").textContent = calDate(s, c);
      }
    } catch {}
  }
  setInterval(refreshCalendars, 60000);

  $("tr").addEventListener("submit", async (e) => {
    e.preventDefault();
    const out = $("tr-out"), L = EXA.labels;
    const v = $("tr-in").value.trim() || $("tr-in").placeholder;
    const kind = $("tr-kind").value;
    out.className = "out"; out.textContent = "...";
    try {
      const r = await fetch("/api/convert?" + kind + "=" + encodeURIComponent(v) + "&tz=" + encodeURIComponent(tz) + q);
      const s = await r.json();
      if (!r.ok) throw new Error(s.error || L.fail);
      const pad = (t) => t + " ".repeat(Math.max(1, 18 - [...t].length * (EXA.lang === "zh" ? 2 : 1)));
      const lines = [
        pad(L.universe) + group(s.universe_seconds),
        pad(L.es) + s.exaseconds,
        pad(L.utc) + s.utc + "  (" + ((EXA.days && EXA.days[s.technical.weekday]) || s.technical.weekday) + ")",
        pad(L.zone) + s.local.iso,
        pad(L.unix) + s.unix,
        "",
        ...s.calendars.map((c) => calName(c) + "\n    " + calDate(s, c)),
      ];
      out.textContent = lines.join("\n");
    } catch (err) { out.className = "out err"; out.textContent = err.message; }
  });
})();
JS;
}

function read_footer(string $lang, array $s, string $mdHref): string {
    $L = fn($k) => t($lang, $k);
    return '<footer class="site"><p>' . h(sprintf($L('read_at'), $s['utc'], group_digits($s['universe_seconds']))) . '</p>'
        . '<p><a href="' . h($mdHref) . '">' . h($L('md_twin')) . '</a> · <a href="/llms.txt">llms.txt</a> · ' . $L('foot_story') . '</p></footer>';
}

function render_chapter(array $c, string $lang, array $s): string {
    $L = fn($k) => t($lang, $k);
    $p = lang_prefix($lang);
    $n = count(CHAPTERS);
    $prev = $c['n'] > 1 ? chapter($lang, CHAPTERS[$c['n'] - 2]) : null;
    $next = $c['n'] < $n ? chapter($lang, CHAPTERS[$c['n']]) : null;
    $prose = md_render($c['body'], fn($name) => figure_html($name, $lang));
    $pager = '<nav class="pager">'
        . ($prev ? '<a class="pv" href="' . h(chapter_url($lang, $prev['slug'])) . '"><small>' . h($L('prev')) . '</small><span>' . h($prev['title']) . '</span></a>' : '<span></span>')
        . '<a class="ct" href="' . $p . '/time">' . h($L('contents')) . '</a>'
        . ($next ? '<a class="nx" href="' . h(chapter_url($lang, $next['slug'])) . '"><small>' . h($L('next')) . '</small><span>' . h($next['title']) . '</span></a>' : '<span></span>')
        . '</nav>';
    $md = $p . '/time/' . $c['slug'] . '.md';
    $body = '<main class="read"><p class="kicker"><a href="' . $p . '/time">' . h($L('q_h')) . '</a> · ' . h(sprintf($L('chapter'), $c['n'], $n)) . '</p>'
        . '<h1>' . h($c['title']) . '</h1><p class="lede">' . h($c['description']) . '</p>'
        . '<article class="prose">' . $prose . '</article>' . $pager . '</main>' . read_footer($lang, $s, $md);
    return layout($lang, ['title' => $c['title'] . ' · Exasecond', 'desc' => $c['description'], 'path' => '/time/' . $c['slug'], 'md' => $md, 'active' => 'time'], $body);
}

function render_time_index(string $lang, array $s): string {
    $L = fn($k) => t($lang, $k);
    $p = lang_prefix($lang);
    $toc = '';
    foreach (chapters($lang) as $c) {
        $toc .= '<li><a href="' . h(chapter_url($lang, $c['slug'])) . '"><span class="num">' . sprintf('%02d', $c['n']) . '</span><span class="ttl">' . h($c['title']) . '</span><span class="dsc">' . h($c['description']) . '</span></a></li>';
    }
    $body = '<main class="read"><p class="kicker"><a href="' . $p . '/">Exasecond</a></p><h1>' . h($L('index_title')) . '</h1>'
        . '<p class="lede">' . h($L('index_lede')) . '</p><p class="evidence">' . $L('evidence_key') . '</p>'
        . '<ol class="toc">' . $toc . '</ol></main>' . read_footer($lang, $s, $p . '/time.md');
    return layout($lang, ['title' => $L('index_title') . ' · Exasecond', 'desc' => $L('index_desc'), 'path' => '/time', 'md' => $p . '/time.md', 'active' => 'time'], $body);
}

function render_story_page(array $s): string {
    $body = '<main class="read"><p class="kicker">' . h(t('en', 'story_eyebrow')) . '</p><article class="play">' . story_html_body(story_md()) . '</article></main>'
        . '<footer class="site"><p>' . h(sprintf(t('en', 'read_at'), $s['utc'], $s['universe_seconds'])) . '</p><p><a href="/story.md">story.md</a></p></footer>';
    return layout('en', ['title' => 'The Pottery Lecture · Exasecond', 'desc' => 'Why this site exists: a short theatrical play for AI readers, first published November 4, 2021.', 'path' => '/story', 'alt' => false, 'md' => '/story.md'], $body);
}

function render_not_found(string $lang, string $path): string {
    $p = lang_prefix($lang);
    $body = '<main class="read"><h1>' . h(t($lang, 'not_found')) . '</h1><p class="lede"><code>' . h($path) . '</code></p>'
        . '<p><a href="' . $p . '/">Exasecond</a> · <a href="' . $p . '/time">' . h(t($lang, 'q_h')) . '</a> · <a href="/now">/now</a> · <a href="/llms.txt">/llms.txt</a></p></main>';
    return layout($lang, ['title' => t($lang, 'not_found') . ' · Exasecond', 'path' => '/', 'alt' => false], $body);
}

<?php
// HTML pages (made for AI, readable by anyone): the exasecond clock, "The question of time" and the play. Server-rendered with the live time baked in,
// so agents that don't run JavaScript still see the current time in the HTML.
defined('EXA') or exit;

function lang_prefix(string $lang): string { return $lang === 'en' ? '' : '/' . $lang; }

/**
 * The agent line: first on every HTML page and in the front page's Markdown, since a fetch tool often reads only one page.
 * The time, then where to get the rest: any place, the same as JSON, a fresh time, all addresses. Data, not instructions.
 */
function agent_line(string $lang, bool $html): string {
    $e = $html ? fn(string $s): string => h($s) : fn(string $s): string => $s;
    $L = fn(string $k): string => $e(t($lang, $k));
    $iso = iso_utc(time());
    $city = t($lang, 'agentbar_tz');
    $lq = $lang === 'en' ? '' : 'lang=' . $lang;   // the answers in the page's language
    // "Generated at the moment of this request" keeps saved copies honest; the fresh link has a new code on every page,
    // so even a saved copy links to an address no fetch tool has a copy of.
    $links = [
        [$L('agentbar_what') . $L('agentbar_kinds'), '/' . strtolower($city) . ($lq ? "?$lq" : '')],
        [$L('agentbar_json'), '/local.json?tz=' . $city . ($lq ? "&$lq" : '')],
        [$L('agentbar_fresh'), '/utc?r=' . bin2hex(random_bytes(3))],
        [$L('agentbar_all'), '/llms.txt'],
    ];
    $out = ($html ? '<b>' . $L('agentbar_who') . '</b>' : $L('agentbar_who')) . ' ' . $L('agentbar_now') . ' '
        . ($html ? '<time datetime="' . $iso . '">' . $iso . '</time>' : $iso) . ' (UTC), ' . $L('agentbar_gen');
    foreach ($links as [$label, $href]) $out .= " · $label → " . ($html ? '<a href="' . h($href) . '">exasecond.com' . h($href) . '</a>' : 'exasecond.com' . $href);
    return $out;
}

// Each language in its own name; 'tag' is the hreflang/lang code, 'icu' and 'js' the locales for dates.
const LANG_INFO = [
    'en' => ['name' => 'English', 'short' => 'EN', 'tag' => 'en', 'icu' => 'en_GB', 'js' => 'en-GB'],
    'zh' => ['name' => '中文', 'short' => '中文', 'tag' => 'zh-Hans', 'icu' => 'zh_Hans', 'js' => 'zh-CN'],
    'de' => ['name' => 'Deutsch', 'short' => 'DE', 'tag' => 'de', 'icu' => 'de_DE', 'js' => 'de-DE'],
    'fr' => ['name' => 'Français', 'short' => 'FR', 'tag' => 'fr', 'icu' => 'fr_FR', 'js' => 'fr-FR'],
    'es' => ['name' => 'Español', 'short' => 'ES', 'tag' => 'es', 'icu' => 'es_ES', 'js' => 'es-ES'],
    'pt' => ['name' => 'Português', 'short' => 'PT', 'tag' => 'pt', 'icu' => 'pt_BR', 'js' => 'pt-BR'],
    'ru' => ['name' => 'Русский', 'short' => 'RU', 'tag' => 'ru', 'icu' => 'ru_RU', 'js' => 'ru-RU'],
    'sv' => ['name' => 'Svenska', 'short' => 'SV', 'tag' => 'sv', 'icu' => 'sv_SE', 'js' => 'sv-SE'],
    'ja' => ['name' => '日本語', 'short' => '日本語', 'tag' => 'ja', 'icu' => 'ja_JP', 'js' => 'ja-JP'],
    'ko' => ['name' => '한국어', 'short' => '한국어', 'tag' => 'ko', 'icu' => 'ko_KR', 'js' => 'ko-KR'],
];

/**
 * How a language writes numbers. The rule, for every language present and future: the decimal sign is the
 * language's own (ICU: a comma in German, French, Russian, Norwegian...); digits are grouped with a comma where
 * the language does (English, Chinese, Japanese, Korean), otherwise with a thin non-breaking space, the SI style.
 */
function num_style(string $lang): array {
    if (!class_exists('NumberFormatter')) return ['group' => ',', 'dec' => '.'];
    $f = new NumberFormatter(LANG_INFO[$lang]['icu'], NumberFormatter::DECIMAL);
    $g = $f->getSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL);
    return ['group' => $g === ',' ? ',' : "\u{202F}", 'dec' => $f->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)];
}

/** Where a calendar is reckoned, in the page's language: UTC, Beijing (not Shanghai), Delhi (not Kolkata), Tokio... */
function cal_place(string $lang, string $zone): string {
    if ($zone === 'UTC') return 'UTC';
    $city = ['Asia/Shanghai' => 'Beijing', 'Asia/Kolkata' => 'Delhi'][$zone] ?? null;
    if ($city) {
        if ($lang === 'zh') return $city === 'Beijing' ? '北京' : '德里';
        return LANG_WORDS[$lang]['cities'][$city] ?? $city;
    }
    return lang_zone_city(LANG_INFO[$lang]['icu'], $zone) ?? str_replace('_', ' ', substr(strrchr($zone, '/'), 1));
}

/** True when the Chinese and Korean lunisolar dates differ: a new moon between midnight in Beijing and in Seoul. */
function lunar_days_apart(int $unix): bool {
    if (!class_exists('IntlDateFormatter')) return false;
    // Compare at noon on the same civil day, so the hour between Seoul's midnight and Beijing's doesn't count.
    $day = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone('Asia/Shanghai'))->format('Y-m-d');
    $f = fn($cal, $zone) => (new IntlDateFormatter("en@calendar=$cal", IntlDateFormatter::NONE, IntlDateFormatter::NONE, $zone, IntlDateFormatter::TRADITIONAL, 'M-d'))
        ->format((new DateTimeImmutable("$day 12:00", new DateTimeZone($zone)))->getTimestamp());
    return $f('chinese', 'Asia/Shanghai') !== $f('dangi', 'Asia/Seoul');
}

/** Where the chapters are for this language: its own, or the English ones. */
function time_prefix(string $lang): string { return in_array($lang, CHAPTER_LANGS, true) ? lang_prefix($lang) : ''; }

/** The languages a page exists in: the home page in all, chapters in CHAPTER_LANGS, a document where its file is. */
function doc_langs(string $name): array { return array_values(array_filter(LANGS, fn($l) => is_file(content_path($l, $name . '.md')))); }

/** The language dropdown: plain HTML (<details>), so it works without JavaScript and agents see ordinary links.
 *  Each language links to this page when it exists in that language, otherwise to that language's front page. */
function lang_menu(string $lang, array $here, callable $url): string {
    $items = '';
    foreach (LANGS as $l) {
        $i = LANG_INFO[$l];
        $href = in_array($l, $here, true) ? $url($l) : lang_prefix($l) . '/';
        $items .= '<li><a href="' . h($href) . '" hreflang="' . $i['tag'] . '" lang="' . $i['tag'] . '"' . ($l === $lang ? ' aria-current="true"' : '') . '>' . h($i['name']) . '</a></li>';
    }
    return '<details class="langs"><summary title="' . h(t($lang, 'nav_lang')) . '">' . h(LANG_INFO[$lang]['short']) . '</summary><ul>' . $items . '</ul></details>';
}

function site_css(): string {
    return <<<'CSS'
:root {
  --bg: #07080c; --bg-2: #0e1017; --line: #23262f; --ink: #ece8df; --ink-2: #9c9a93; --ink-3: #6e6d74;
  --accent: #39ff14; --accent-2: #39ff1422; --grid: #ffffff05; --glow: 0 0 8px #39ff1455;
  --mono: ui-monospace, "SF Mono", "JetBrains Mono", Menlo, Consolas, monospace;
  --sans: ui-sans-serif, -apple-system, "Segoe UI", Inter, Roboto, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "Noto Sans SC", sans-serif;
  --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, "Songti SC", "Noto Serif SC", "Source Han Serif SC", SimSun, serif;
  --read: var(--serif);
}
@media (prefers-color-scheme: light) {
  :root { --bg: #f4f1e9; --bg-2: #ebe6da; --line: #d6d0c2; --ink: #16171c; --ink-2: #55545a; --ink-3: #7c7a73; --accent: #328000; --accent-2: #39ff1433; --grid: #00000008; --glow: none; }
}
:lang(zh) { --read: var(--sans); }
/* Japanese and Korean need their own fonts: Chinese fonts draw shared characters in Chinese forms. */
:lang(ja) { --sans: ui-sans-serif, -apple-system, "Segoe UI", "Hiragino Kaku Gothic ProN", "Hiragino Sans", "Yu Gothic UI", Meiryo, "Noto Sans JP", sans-serif; --serif: "Hiragino Mincho ProN", "Yu Mincho", "Noto Serif JP", serif; --read: var(--sans); }
:lang(ko) { --sans: ui-sans-serif, -apple-system, "Segoe UI", "Apple SD Gothic Neo", "Malgun Gothic", "Noto Sans KR", sans-serif; --serif: "AppleMyungjo", "Batang", "Noto Serif KR", serif; --read: var(--sans); }
* { box-sizing: border-box; }
html, body { margin: 0; background: var(--bg); color: var(--ink); }
body { font: 16px/1.55 var(--sans); -webkit-font-smoothing: antialiased; }
/* terminal touches: a faint grid, green selection, a blinking block cursor. Prefixes like "$ " and "## " are drawn by CSS
   (with empty alt text), so the HTML that agents read stays the same. */
body { background-image: linear-gradient(var(--grid) 1px, transparent 1px), linear-gradient(90deg, var(--grid) 1px, transparent 1px); background-size: 32px 32px; }
/* boxes cover the grid (in both themes); hover backgrounds below still win */
.service-routes a, .devbtn, .about-fact, .cals, .chapters, .table, .evidence { background-color: var(--bg); }
::selection { background: var(--accent); color: var(--bg); }
@keyframes blink { 50% { opacity: 0; } }
.service-terminal output::after, .count::after { content: ""; display: inline-block; width: .55em; height: 1em; margin-left: .2em; vertical-align: -.12em; background: var(--accent); box-shadow: var(--glow); animation: blink 1.1s steps(1) infinite; }
/* green text glows faintly in dark mode, like an old screen */
.service-promise, .service-terminal output, .count .tick, .service-routes code, .mark b { text-shadow: var(--glow); }
@media (prefers-reduced-motion: reduce) { .service-terminal output::after, .count::after { animation: none; } }
a { color: inherit; text-decoration-color: var(--ink-3); text-decoration-style: dotted; text-underline-offset: 3px; }
a:hover { text-decoration-color: var(--accent); }
.wrap { max-width: 1120px; margin: 0 auto; padding: 0 16px; }
@media (min-width: 720px) { .wrap { padding: 0 40px; } }
code { font-family: var(--mono); font-size: .9em; }
sup { font-size: .7em; line-height: 0; }

.agentbar { border-bottom: 1px solid var(--line); font: 11.5px/1.5 var(--mono); color: var(--ink-3); padding: 8px 0; opacity: .7; }
.agentbar b { font-weight: 600; }
.agentbar a { color: inherit; text-decoration-color: var(--line); overflow-wrap: anywhere; }
.agentbar:hover { opacity: 1; }
.agentbar { overflow: hidden; max-height: 14em; transition: max-height .7s ease, padding .7s ease, opacity .7s ease, border-color .7s ease; }
.agentbar.folded { max-height: 0; padding-top: 0; padding-bottom: 0; opacity: 0; border-bottom-color: transparent; }
@media (prefers-reduced-motion: reduce) { .agentbar { transition: none; } }
@media (max-width: 560px) {
  .agentbar { font-size: 9.5px; padding: 6px 0; }
  .top { align-items: center; gap: 12px; }
  .top nav { font-size: 11px; gap: 12px; color: var(--ink-3); border-left: 1px solid var(--line); padding-left: 12px; }
}
.agentbar a:hover { color: var(--accent); }
.top { display: flex; justify-content: space-between; align-items: center; padding: 24px 0; gap: 16px; }
.mark { font: 600 15px/1 var(--mono); letter-spacing: .32em; text-transform: uppercase; text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; }
/* the gap only after the logo, so "Exa·second" reads as one word; the dot gets a little air and sits at 1/3 of the capitals, like in the mark */
.mark img { flex: none; margin-right: 12px; }
.mark b { color: var(--accent); font-weight: 600; position: relative; top: .11em; margin: 0 .15em; }
.top nav { font: 13px/1 var(--mono); color: var(--ink-2); display: flex; align-items: center; gap: 18px; flex-wrap: nowrap; justify-content: flex-end; }
.top nav a { text-decoration: none; }
.top nav a:hover, .top nav a[aria-current] { color: var(--ink); text-decoration: underline; text-decoration-color: var(--accent); }
.langs { position: relative; }
.langs summary { list-style: none; cursor: pointer; white-space: nowrap; }
.langs summary::-webkit-details-marker { display: none; }
.langs summary::after { content: " \25BE"; color: var(--ink-3); }
.langs summary:hover, .langs[open] summary { color: var(--ink); }
.langs ul { position: absolute; right: 0; top: calc(100% + 10px); z-index: 10; margin: 0; padding: 6px 0; list-style: none; background: var(--bg-2); border: 1px solid var(--line); min-width: 9em; }
.langs li a { display: block; padding: 7px 14px; white-space: nowrap; }
.site-menu { position: relative; }
.site-menu summary { list-style: none; cursor: pointer; white-space: nowrap; }
.site-menu summary::-webkit-details-marker { display: none; }
.site-menu summary::after { content: " \25BE"; color: var(--ink-3); }
.site-menu summary:focus-visible, .langs summary:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }
.site-menu summary, .langs summary { display: flex; align-items: center; min-height: 44px; }
.site-menu ul { position: absolute; right: 0; top: 100%; z-index: 10; margin: 0; padding: 6px 0; list-style: none; background: var(--bg-2); border: 1px solid var(--line); min-width: 12em; max-width: calc(100vw - 32px); }
.site-menu li a { display: block; padding: 13px 16px; line-height: 1.5; }
@media (hover: hover) and (pointer: fine) {
  .langs ul { top: 100%; }
}
@media (max-width: 640px) {
  .top { align-items: center; gap: 12px; }
  .top nav { align-items: center; flex-wrap: nowrap; font-size: 12px; gap: 12px; border: 0; padding-left: 0; }
  .langs li a { min-height: 44px; padding-top: 14px; padding-bottom: 14px; }
}

/* clock */
.service { padding: 34px 0 38px; border-top: 1px dashed var(--line); }
.service-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 36px; align-items: center; }
.service h1, .read:not(.read-story) > h1 { font: 500 clamp(1.7rem, 3.8vw, 2.9rem)/1.12 var(--mono); letter-spacing: -.03em; margin: 0 0 18px; }
.service h1 { max-width: 22ch; }
.service-promise { font: 400 clamp(1.1rem, 2vw, 1.5rem)/1.4 var(--sans); color: var(--accent); margin: 0 0 16px; }
.service-promise::before { content: "> "; content: "> " / ""; font-family: var(--mono); }
.service-desc { color: var(--ink-2); max-width: 57ch; margin: 0 0 18px; }
.service-terminal { background: var(--bg-2); border: 1px solid var(--line); border-radius: 0; overflow: hidden; min-width: 0; }
.service-terminal p { padding: 12px 18px; margin: 0; color: var(--ink-2); font: 12px/1.5 var(--mono); border-bottom: 1px solid var(--line); }
/* window bar: three dots, the last one green */
.service-terminal > p:first-child { position: relative; padding-left: 64px; }
.service-terminal > p:first-child::before { content: ""; position: absolute; left: 18px; top: 50%; width: 8px; height: 8px; margin-top: -4px; border-radius: 50%; background: var(--ink-3); box-shadow: 13px 0 0 var(--ink-3), 26px 0 0 var(--accent); }
.service-terminal pre code::before { content: "$ "; content: "$ " / ""; color: var(--accent); }
.service-terminal pre { font: 13px/1.8 var(--mono); padding: 20px 18px; margin: 0; overflow-x: auto; }
.service-terminal output { display: block; padding: 0 18px 22px; color: var(--accent); font: 15px/1.5 var(--mono); overflow-wrap: anywhere; }
.service-routes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 28px; }
.service-routes a { border: 1px solid var(--line); border-radius: 0; padding: 14px 16px; text-decoration: none; min-width: 0; }
.service-routes a:hover { border-color: var(--accent); background: var(--bg-2); }
.service-routes code { position: relative; }
.service-routes code::before { content: ">"; content: ">" / ""; position: absolute; right: 100%; padding-right: .4em; opacity: 0; transition: opacity .15s; }
.service-routes a:hover code::before, .service-routes a:focus-visible code::before { opacity: 1; }
.service-routes code { display: block; color: var(--accent); overflow-wrap: anywhere; margin-bottom: 4px; }
.service-routes span { color: var(--ink-2); font-size: 13px; }
.service-definition { margin: 22px 0 0; max-width: 76ch; color: var(--ink-2); font-size: 14px; }
a:focus-visible { outline: 2px solid var(--accent); outline-offset: 4px; }
@media (max-width: 760px) { .service-grid, .service-routes { grid-template-columns: 1fr; } .service-grid { gap: 22px; } .service h1 { max-width: 22ch; } }
.hero { padding: 6vh 0 6vh; border-top: 1px dashed var(--line); }

/* the answer: first read */
.answer { padding: 4vh 0 3.5vh; display: grid; gap: 16px; }
.ans-row { display: grid; grid-template-columns: 9.5em 1fr; gap: 6px 24px; align-items: baseline; }
.ans-k { font: 12px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; }
.ans-k::before, .big-label::before { content: "// "; content: "// " / ""; color: var(--accent); opacity: .6; letter-spacing: 0; }
.ans-v { margin: 0; min-width: 0; }
.ans-now { font: 500 15px/1.5 var(--mono); font-variant-numeric: tabular-nums; }
.ans-mine { margin: 0; font: 15.5px/1.55 var(--mono); }
.ans-intro { margin: 0 0 6px; color: var(--ink-2); font-size: 15px; }
.ans-world { list-style: none; margin: 0; padding: 0; font: 13.5px/1.6 var(--mono); color: var(--ink-2); }
.ans-fact { font: 14.5px/1.65 var(--mono); color: var(--ink-2); max-width: 70ch; }
.ans-fact a { font: 13px/1 var(--mono); color: var(--accent); text-decoration: none; white-space: nowrap; }
@media (max-width: 640px) { .ans-row { grid-template-columns: 1fr; } }
/* "Which answer do you need?" on phones: each row stacks (need, then address, answer and tokens), no sideways scrolling */
@media (max-width: 640px) {
  .prose .guide thead { display: none; }
  .prose .guide tr { display: block; padding: 10px 0; border-bottom: 1px solid var(--line); }
  .prose .guide td { display: inline; padding: 0 10px 0 0; border: 0; white-space: normal; }
  .prose .guide td:first-child { display: block; padding-bottom: 4px; color: var(--ink-2); }
  .prose .guide td:nth-child(3) { display: block; padding: 2px 0; }
  .prose .guide td:last-child { color: var(--ink-3); }
}
.devbtn { display: inline-block; margin: 6px 0 4px; padding: 12px 18px; border: 1px solid var(--accent); border-radius: 0; font: 14px/1.4 var(--mono); color: var(--ink); text-decoration: none; }
.devbtn::before { content: "[ "; content: "[ " / ""; color: var(--accent); }
.devbtn::after { content: " ]"; content: " ]" / ""; color: var(--accent); }
.devbtn b { color: var(--accent); font-weight: 600; }
.devbtn:hover { background: var(--accent-2); }
.prose .devbtn { font-size: 14px; }
.dev-cta { margin-top: 26px; }
.about-fact { margin: 0 0 40px; padding: 14px 18px; border: 1px solid var(--line); border-radius: 0; }
.about-fact b { display: block; font: 12px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; margin-bottom: 6px; font-weight: 400; }
.eyebrow { font: 13px/1.4 var(--mono); color: var(--ink-2); letter-spacing: .04em; margin: 0 0 18px; }
.count { font: 500 clamp(1.4rem, 5.6vw, 4.6rem)/1 var(--mono); letter-spacing: -.035em; font-variant-numeric: tabular-nums; margin: 0; white-space: nowrap; }
.count .tick { color: var(--accent); }
.es { margin: 18px 0 0; font: clamp(.95rem, 2vw, 1.2rem)/1.3 var(--mono); color: var(--ink-2); }
.es span { font-style: normal; font-family: var(--mono); color: var(--ink); font-size: .9em; }
.span { margin: 56px 0 0; }
.bar { position: relative; height: 2px; background: var(--line); }
.bar i { position: absolute; left: 0; top: 0; bottom: 0; background: var(--accent); }
.bar em { position: absolute; top: -6px; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 6px var(--accent-2); }
.bar-labels { display: flex; justify-content: space-between; font: 12px/1.4 var(--mono); color: var(--ink-3); margin-top: 14px; gap: 12px; }
.bar-labels span:last-child { text-align: right; }
.here { position: relative; font: 12px/1.4 var(--mono); color: var(--accent); margin-bottom: 14px; height: 1.4em; }
.here span { position: absolute; transform: translateX(-50%); white-space: nowrap; }

section { border-top: 1px dashed var(--line); padding: 44px 0; }
h2 { font: 500 clamp(1.25rem, 2.6vw, 1.65rem)/1.3 var(--mono); letter-spacing: -.01em; margin: 0 0 6px; }
section h2::before, .prose h2::before { content: "## "; content: "## " / ""; color: var(--accent); opacity: .55; }
.sub { color: var(--ink-2); margin: 0 0 28px; max-width: 62ch; }
.human { display: grid; gap: 28px; grid-template-columns: 1fr; margin-bottom: 36px; }
@media (min-width: 720px) { .human { grid-template-columns: 1fr 1fr; } }
.big-label { font: 12px/1 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; margin-bottom: 10px; }
.big-time { font: 500 clamp(1.3rem, 3.4vw, 2rem)/1.2 var(--mono); font-variant-numeric: tabular-nums; }
.big-time small { display: block; font: 14px/1.4 var(--sans); color: var(--ink-2); margin-top: 6px; }
.cals { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); border-top: 1px dashed var(--line); border-left: 1px dashed var(--line); }
.cal { padding: 16px 18px 18px; border-right: 1px dashed var(--line); border-bottom: 1px dashed var(--line); display: flex; flex-direction: column; gap: 4px; min-width: 0; }
.cal-name { font: 12px/1.3 var(--mono); color: var(--ink-3); }
.cal-date { font-size: 17px; overflow-wrap: anywhere; }
.cal-native { color: var(--ink-2); font-size: 15px; }
.cal-where { color: var(--ink-3); opacity: .75; }
.cal-note { margin: 14px 0 0; font-size: 13px; color: var(--ink-3); }
.precision { margin: 8px 0 0; font-size: 13px; color: var(--ink-3); }
form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
input, select, button { font: 15px/1 var(--mono); color: var(--ink); background: var(--bg-2); border: 1px solid var(--line); border-radius: 0; padding: 12px 14px; min-width: 0; }
input { flex: 1 1 260px; }
button { background: var(--accent); color: var(--bg); border-color: var(--accent); cursor: pointer; font-weight: 600; }
input:focus, select:focus, button:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
.out { font: 14px/1.7 var(--mono); white-space: pre; overflow-x: auto; color: var(--ink-2); min-height: 1.7em; margin: 0; }
.out.err { color: #e5675a; }
pre.agents { background: var(--bg-2); border: 1px solid var(--line); border-left: 2px solid var(--accent); border-radius: 0; padding: 18px 20px; overflow-x: auto; font: 13.5px/1.75 var(--mono); margin: 0; }
pre.agents .c { color: var(--ink-3); }
.agent-links { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 18px; font: 14px/1.4 var(--mono); }

/* chapter grid */
.chapters { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); border-top: 1px dashed var(--line); border-left: 1px dashed var(--line); counter-reset: none; }
.chapters li { border-right: 1px dashed var(--line); border-bottom: 1px dashed var(--line); min-width: 0; }
.chapters a { display: flex; flex-direction: column; gap: 6px; padding: 18px 20px 22px; height: 100%; text-decoration: none; }
.chapters a:hover { background: var(--bg-2); }
.chapters .num { font: 12px/1 var(--mono); color: var(--accent); }
.chapters .ttl { font: 500 16px/1.35 var(--mono); }
.chapters .dsc { font-size: 14px; color: var(--ink-2); }
.more { margin-top: 18px; font: 14px/1.4 var(--mono); }

/* reading pages */
.read { margin: 0 auto; padding: 4vh 0 24px; }
.read-story { max-width: 720px; }
.kicker { font: 12px/1.5 var(--mono); color: var(--ink-2); letter-spacing: .06em; margin: 0 0 18px; }
.kicker a { text-decoration: none; color: var(--accent); }
.read-story h1 { font: 400 clamp(2rem, 6vw, 3.1rem)/1.12 var(--serif); margin: 0 0 18px; letter-spacing: -.01em; }
.lede { font: 16px/1.55 var(--sans); color: var(--ink-2); margin: 0 0 40px; }
.prose { font: 16px/1.55 var(--sans); }
.prose p, .prose ul, .prose ol { margin: 0 0 1.05em; }
.prose ul, .prose ol { padding-left: 1.3em; }
.prose li { margin-bottom: .4em; }
.prose h2 { margin: 2.2em 0 .6em; }
.prose h3 { font: 600 13px/1.4 var(--mono); letter-spacing: .14em; text-transform: uppercase; color: var(--ink-2); margin: 2em 0 .8em; }
.prose blockquote { margin: 1.5em 0; padding: .2em 0 .2em 1.1em; border-left: 2px solid var(--accent); color: var(--ink-2); }
.prose blockquote p:last-child { margin-bottom: 0; }
.prose hr { border: 0; border-top: 1px dashed var(--line); margin: 2.5em 0; }
.prose strong { font-weight: 600; }
.prose pre { background: var(--bg-2); border: 1px solid var(--line); border-left: 2px solid var(--accent); border-radius: 0; padding: 18px 20px; overflow-x: auto; font: 13.5px/1.75 var(--mono); }
.table { overflow-x: auto; margin: 1.4em 0 1.8em; border-top: 1px solid var(--line); }
.prose table { border-collapse: collapse; width: 100%; font: 15px/1.5 var(--sans); }
.prose th { text-align: left; font: 12px/1.4 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .08em; }
.prose th, .prose td { padding: 10px 12px 10px 0; border-bottom: 1px solid var(--line); vertical-align: top; }
.prose td:first-child { white-space: nowrap; }
/* About: each chapter is an answer row as on the front page (.ans-row: label left, text right, stacked on phones), with the token tables' row line */
.about-row { padding: 18px 0; border-bottom: 1px solid var(--line); }
.about-row:first-of-type { border-top: 1px solid var(--line); margin-top: 1.6em; }
.prose .about-row h2.ans-k { margin: 0; }
.prose .about-row h2.ans-k::before { content: "// "; content: "// " / ""; color: var(--accent); opacity: .6; letter-spacing: 0; }
.about-row .ans-v > :first-child { margin-top: 0; }
.about-row .ans-v > :last-child { margin-bottom: 0; }
.home-costs p { font: 16px/1.55 var(--sans); color: var(--ink-2); }
#sources + ul, #来源 + ul { font: 14.5px/1.6 var(--sans); color: var(--ink-2); }
.evidence { font: 14px/1.6 var(--sans); color: var(--ink-2); border: 1px solid var(--line); border-radius: 0; padding: 14px 16px; margin: 0 0 36px; }
.evidence b { color: var(--ink); font-weight: 600; }
.toc { list-style: none; padding: 0; margin: 0; border-top: 1px dashed var(--line); }
.toc li { border-bottom: 1px dashed var(--line); }
.toc a { display: grid; grid-template-columns: 3.2em 1fr; gap: 4px 12px; padding: 18px 0; text-decoration: none; }
.toc .num { font: 12px/1.4 var(--mono); color: var(--accent); grid-row: span 2; }
.toc .ttl { font: 500 16px/1.35 var(--mono); }
.toc .dsc { color: var(--ink-2); font-size: 14px; }
.toc a:hover .ttl { text-decoration: underline; text-decoration-color: var(--accent); }
.pager { display: grid; grid-template-columns: 1fr auto 1fr; gap: 16px; border-top: 1px dashed var(--line); margin-top: 56px; padding-top: 22px; font: 14px/1.4 var(--sans); }
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
.play h2::before { content: none; }
.play hr { border: 0; border-top: 1px solid var(--line); margin: 40px 0; }
.play p { margin: 0 0 18px; }
.play .line .who { display: block; font: 600 12px/1.4 var(--mono); letter-spacing: .14em; text-transform: uppercase; color: var(--accent); margin-bottom: 4px; }
.play .aside { color: var(--ink-2); text-align: center; }
.play ol { padding-left: 1.4em; margin: 0 0 18px; }

footer.site { border-top: 1px dashed var(--line); padding: 32px 0 20px; color: var(--ink-3); font-size: 14px; }
.copy { font: 12px/1.4 var(--mono); color: var(--ink-3); margin: 0; padding: 20px 0 48px; border-top: 1px dashed var(--line); }
footer.site + .copy { border-top: 0; padding-top: 4px; }
/* the original EggQube icon before "EggQube", in the text colour (mask), and a © that reads clearly in a text font */
.copy .eq { display: inline-block; width: 1.4em; height: 1.4em; vertical-align: -.4em; margin: 0 .4em 0 .1em; background: var(--ink-2); -webkit-mask: url(/eggqube.svg) center / contain no-repeat; mask: url(/eggqube.svg) center / contain no-repeat; }
.copy .c { font: 15px/1 var(--sans); color: var(--ink-2); }
footer.site p { max-width: 75ch; margin: 0 0 10px; }
CSS;
}

/**
 * $o: title, desc, path (this page, without language prefix), alt (null if no translation), md (markdown twin href),
 *     head (extra head html), active (nav key)
 */
function layout(string $lang, array $o, string $body): string {
    $L = fn($k) => t($lang, $k);
    $path = $o['path'] ?? '/';
    // The languages this page exists in: all by default, only its own when alt is false.
    $here = $o['langs'] ?? (($o['alt'] ?? true) ? LANGS : [$lang]);
    $url = fn($l) => lang_prefix($l) . ($path === '/' ? '/' : $path);
    $head = '';
    if (count($here) > 1) {
        foreach ($here as $l) $head .= '<link rel="alternate" hreflang="' . LANG_INFO[$l]['tag'] . '" href="' . h(SITE . $url($l)) . '">' . "\n";
        $head .= '<link rel="alternate" hreflang="x-default" href="' . h(SITE . $url('en')) . '">' . "\n";
    }
    $head .= '<link rel="canonical" href="' . h(SITE . $url($lang)) . '">' . "\n";
    if (!empty($o['md'])) $head .= '<link rel="alternate" type="text/markdown" href="' . h($o['md']) . '" title="Markdown">' . "\n";
    $head .= '<link rel="alternate" type="text/plain" href="/llms.txt" title="llms.txt">' . "\n";
    $head .= $o['head'] ?? '';
    $p = lang_prefix($lang);
    $cur = fn($k) => ($o['active'] ?? '') === $k ? ' aria-current="page"' : '';
    $navLinks = [
        '<a href="' . $p . '/developers"' . $cur('developers') . '>' . h($L('service_setup')) . '</a>',
        '<a href="' . time_prefix($lang) . '/time"' . $cur('time') . '>' . h($L('nav_time')) . '</a>',
        '<a href="' . $p . '/about"' . $cur('about') . '>' . h($L('nav_about')) . '</a>',
    ];
    $nav = '<details class="site-menu"><summary>' . h($L('nav_menu')) . '</summary><ul><li>'
        . implode('</li><li>', $navLinks) . '</li></ul></details>' . lang_menu($lang, $here, $url);
    // Styling lives in /site.css (cached by browsers), so the page itself starts with the content: raw-HTML readers reach the time first.
    $cssHref = '/site.css?v=' . substr(md5(site_css()), 0, 8);
    // Scripts live in /site.js the same way: raw-HTML readers don't pay for code they can't run.
    $jsHref = '/site.js?v=' . substr(md5(site_js()), 0, 8);
    $title = h($o['title']);
    $desc = h($o['desc'] ?? '');
    $htmlLang = $L('html_lang');
    $home = $p . '/';
    // Icon and logo are files (lib/logo.php), not inline, so they add a line to the page instead of kilobytes.
    $fav = '/icon.svg?v=' . substr(md5(ICON_SVG), 0, 8);
    $logo = '/logo.svg?v=' . substr(md5(LOGO_SVG), 0, 8);
    $year = gmdate('Y');
    // Visible, quiet, first in the page: the time itself, then where to get the rest. On every page, since agents can land anywhere.
    $agentbar = agent_line($lang, true);
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
$head<link rel="icon" href="$fav" type="image/svg+xml">
<link rel="stylesheet" href="$cssHref">
</head>
<body>
<div class="agentbar" role="note"><div class="wrap">$agentbar</div></div>
<div class="wrap">
  <header class="top">
    <a class="mark" href="$home"><img src="$logo" alt="" width="28" height="28">Exa<b>·</b>second</a>
    <nav>$nav</nav>
  </header>
$body
  <p class="copy">Exasecond.com by <span class="eq" aria-hidden="true"></span>EggQube <span class="c">©</span> 2021-$year</p>
</div>
<script src="$jsHref" defer></script>
</body>
</html>

HTML;
}

/** The scripts of every page, served as /site.js. No time in it: the front page hands its clock settings over inline, as EXA. */
function site_js(): string {
    $menu = <<<'JS'
// Keep the native dropdowns usable without JavaScript; enhance dismissal when available.
(() => {
  const dropdowns = document.querySelectorAll(".top nav details");
  const hoverMenus = window.matchMedia("(hover: hover) and (pointer: fine)");
  for (const dropdown of dropdowns) {
    dropdown.addEventListener("pointerenter", (event) => {
      if (!hoverMenus.matches || event.pointerType !== "mouse") return;
      for (const other of dropdowns) if (other !== dropdown) other.open = false;
      dropdown.open = true;
    });
    dropdown.addEventListener("pointerleave", (event) => {
      if (!hoverMenus.matches || event.pointerType !== "mouse") return;
      if (!dropdown.contains(document.activeElement)) dropdown.open = false;
    });
  }
  document.addEventListener("click", (event) => {
    for (const dropdown of dropdowns) {
      if (!dropdown.contains(event.target) || event.target.closest("a")) dropdown.open = false;
    }
  });
  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape") return;
    for (const dropdown of dropdowns) {
      if (!dropdown.open) continue;
      dropdown.open = false;
      dropdown.querySelector("summary").focus();
    }
  });
})();
// The agent bar is for machines (who read the HTML and don't run this). People see it briefly, then it folds away.
setTimeout(() => {
  const b = document.querySelector(".agentbar");
  if (!b) return;
  const h = b.offsetHeight;
  if (window.scrollY > h) { b.style.transition = "none"; b.classList.add("folded"); window.scrollBy(0, -h); }
  else b.classList.add("folded");
}, 2000);
// The front page's clock: only where the page defines EXA.
if (typeof EXA !== "undefined")
JS;
    return $menu . "\n" . home_js();
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

function render_home(array $s, string $lang, ?string $place = null): string {
    $L = fn($k) => t($lang, $k);
    $pct = number_format($s['universe_fraction_percent'], 4, '.', '');
    $zh = $lang === 'zh';
    $en = $lang === 'en';
    $locDates = $en ? [] : calendars_local($s['unix'], LANG_INFO[$lang]['icu']);
    $days = $en ? null : weekday_names($lang);
    $cal = '';
    foreach ($s['calendars'] as $c) {
        $name = $en ? $c['name'] : (CAL_NAMES[$lang][$c['id']] ?? $c['name']);
        $date = $en ? $c['text'] : ($locDates[$c['id']] ?? $c['text']);
        $native = ($en && isset($c['native'])) ? '<span class="cal-native" lang="zh">' . h($c['native']) . '</span>' : ($zh ? '<span class="cal-native" lang="en">' . h($c['text']) . '</span>' : '');
        $cal .= "\n        <li class=\"cal\" data-id=\"" . h($c['id']) . "\"><span class=\"cal-name\">" . h($name) . " <span class=\"cal-where\">· " . h(cal_place($lang, $c["zone"] ?? "UTC")) . "</span></span><span class=\"cal-date\">" . h($date) . "</span>$native</li>";
    }
    $calNotes = '<p class="cal-note">' . h($L('cal_note')) . '</p>' . (lunar_days_apart($s['unix']) ? '<p class="cal-note">' . h($L('cal_moon')) . '</p>' : '');
    $precision = h($L('precision'));
    $noJs = h($L('enable_js'));
    $S = SITE;
    $C = OFFSET;
    $ns = num_style($lang);
    $count = group_digits($s['universe_seconds'], $ns['group']);
    $weekday = $days[$s['technical']['weekday']] ?? $s['technical']['weekday'];
    $esLine = sprintf(h($L('es_line')), '<span id="es">' . h(str_replace('.', $ns['dec'], $s['exaseconds'])) . '</span>');
    $p = lang_prefix($lang);
    $tp = time_prefix($lang);
    $cfg = json_encode([
        'offset' => (string)OFFSET, 'serverMs' => $s['unix'] * 1000, 'lang' => $lang, 'locale' => $en ? null : LANG_INFO[$lang]['js'],
        'group' => $ns['group'], 'dec' => $ns['dec'],
        'calNames' => $en ? null : (CAL_NAMES[$lang] ?? null),
        'days' => $days,
        'labels' => ['universe' => $L('tr_universe'), 'es' => $L('tr_es'), 'utc' => 'UTC', 'zone' => $L('js_zone'), 'unix' => 'Unix', 'fail' => $L('js_fail')],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    // The answer comes first: the time now, the reader's time, one fact. The counter is the visual that follows.
    $nowD = dt_utc($s['unix']);
    if ($zh) $nowLine = $nowD->format('Y') . '年' . $nowD->format('n') . '月' . $nowD->format('j') . '日 ' . WEEKDAYS_ZH[$nowD->format('l')] . ' ' . $nowD->format('H:i:s') . ' UTC';
    elseif ($en) $nowLine = $nowD->format('l j F Y, H:i:s') . ' UTC';
    else $nowLine = (new IntlDateFormatter(LANG_INFO[$lang]['icu'], IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'UTC'))->format($s['unix']) . (in_array($lang, ['ja', 'ko'], true) ? ' ' : ', ') . $nowD->format('H:i:s') . ' UTC';
    // Each city links to its short address (exasecond.com/tokyo?lang=ja): a link found on the page, which AI apps
    // let their models follow without asking the user, unlike an address the model makes up.
    $links = [];
    foreach (PLACES as $pl) {
        $nm = htmlspecialchars(place_display($pl, $lang), ENT_NOQUOTES, 'UTF-8');
        $links[$nm] = '<a href="/' . place_slug($pl[1]) . ($en ? '' : '?lang=' . $lang) . '">' . $nm . '</a>';
    }
    $world = implode('', array_map(fn($l) => '<li>' . strtr(htmlspecialchars($l, ENT_NOQUOTES, 'UTF-8'), $links) . '</li>', world_now_lines($s['unix'], $lang)));
    $fact = pick_fact($s['unix'], null, $lang);
    $devBtn = dev_button($lang);
    $tokenCosts = md_render(token_cost_md($lang));
    $serviceTitle = h($L('service_title'));
    $servicePromise = h($L('service_promise'));
    $serviceDesc = h($L('service_desc'));
    $serviceDefinition = h($L('service_definition'));
    $serviceClock = h($L('service_clock'));
    $serviceBytes = h($L('service_bytes'));
    $serviceUtc = h($L('service_utc'));
    $serviceLocal = h($L('service_local'));
    $serviceMcp = h($L('service_mcp'));
    $serviceSetup = h($L('service_setup'));
    $localHref = '/local?tz=Oslo' . ($en ? '' : '&amp;lang=' . $lang);
    $structured = json_encode([
        '@context' => 'https://schema.org', '@type' => 'WebAPI', 'name' => 'Exasecond.com',
        'url' => SITE . $p . '/', 'description' => $L('service_title') . ' ' . $L('service_promise') . ' ' . $L('service_desc'),
        'documentation' => SITE . $p . '/developers', 'inLanguage' => LANG_INFO[$lang]['tag'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
    $answer = '<section class="answer" aria-label="' . h($L('ans_label')) . '">'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_now')) . '</span><p class="ans-v ans-now" id="ans-now">' . h($nowLine) . '</p></div>'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_you')) . '</span><div class="ans-v" id="ans-you">'
        . ($place !== null ? '<p class="ans-mine">' . h($place) . '</p>' : '<p class="ans-intro">' . h($L('ans_you_intro')) . '</p><ul class="ans-world">' . $world . '</ul>') . '</div></div>'
        . '<div class="ans-row"><span class="ans-k">' . h($L('ans_fact')) . '</span><p class="ans-v ans-fact" id="ans-fact"><span>' . md_inline($fact['text']) . '</span> <a href="' . h($fact['href']) . '">' . h($L('ans_more')) . ' →</a></p></div>'
        . '</section>';
    $body = <<<HTML
  <main>
    <section class="service" aria-labelledby="service-title">
      <div class="service-grid">
        <div>
          <h1 id="service-title">$serviceTitle</h1>
          <p class="service-promise">$servicePromise</p>
          <p class="service-desc">$serviceDesc</p>
          <a class="devbtn" href="{$p}/developers">$serviceSetup →</a>
        </div>
        <div class="service-terminal">
          <p>HTTP GET · $serviceBytes · text/plain</p>
          <pre><code>curl https://exasecond.com/utc</code></pre>
          <p>$serviceClock · UTC</p>
          <output>{$s['utc']}</output>
        </div>
      </div>
      <div class="service-routes">
        <a href="/utc"><code>/utc</code><span>$serviceUtc</span></a>
        <a href="$localHref"><code>/local?tz=Oslo</code><span>$serviceLocal</span></a>
        <a href="{$p}/developers"><code>/mcp</code><span>$serviceMcp</span></a>
      </div>
      <p class="service-definition">$serviceDefinition</p>
    </section>
    $answer
    <div class="hero">
      <p class="eyebrow">{$L('eyebrow')}</p>
      <p class="count" id="count" aria-live="off">$count</p>
      <p class="es">$esLine</p>
      <p class="precision">{$precision}</p>
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
          <div class="big-time" id="local">-<small id="local-zone">{$noJs}</small></div>
        </div>
      </div>
      <ul class="cals" id="cals">$cal
      </ul>
      {$calNotes}
    </section>

    <section aria-labelledby="h-q">
      <h2 id="h-q"><a href="{$tp}/time" style="text-decoration:none">{$L('q_h')}</a></h2>
      <p class="sub">{$L('q_sub')}</p>
HTML;
    $body .= chapter_cards($tp === '' ? 'en' : $lang);
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
<pre class="agents" lang="en"><span class="c"># UTC timestamp, to the second</span>
curl $S/utc

<span class="c"># compact UTC and weekday</span>
curl $S/now

<span class="c"># local time in one sentence</span>
curl "$S/local?tz=Oslo"

<span class="c"># MCP: add as a remote server</span>
$S/mcp</pre>
      <div class="agent-links" lang="en">
        <a href="/llms.txt">/llms.txt</a><a href="/llms-full.txt">/llms-full.txt</a><a href="/now.md">/now.md</a>
        <a href="/now.json">/now.json</a><a href="/openapi.json">/openapi.json</a><a href="/spec.md">/spec.md</a><a href="{$tp}/time.md">{$tp}/time.md</a>
      </div>
      <p>{$L('tool_connection')}</p>
      <div class="prose home-costs">$tokenCosts</div>
      <div class="dev-cta">{$devBtn}</div>
    </section>
  </main>

  <footer class="site">
HTML;
    $body .= '<p>' . sprintf($L('foot_convention'), $C) . '</p>';
    $body .= '<p>' . $L('foot_story') . '</p>';
    $body .= '<p>' . h($L('foot_lifespan')) . '</p>';
    // Only the settings stay in the page (they carry the server time); the clock code is in /site.js.
    $body .= "\n  </footer>\n<script>\nconst EXA = $cfg;\n</script>";
    return layout($lang, [
        'title' => $L('home_title'),
        'desc' => $L('home_desc') . $L('desc_now') . $s['utc'] . ' UTC.',
        'path' => '/', 'md' => $p . '/',
        'head' => '<link rel="alternate" type="application/json" href="/now.json" title="Current time as JSON">' . "\n"
            . '<script type="application/ld+json">' . $structured . '</script>' . "\n",
    ], $body);
}

function home_js(): string {
    return <<<'JS'
(() => {
  const C = BigInt(EXA.offset);
  const skew = EXA.serverMs - Date.now(); // align to server clock (second precision)
  const $ = (id) => document.getElementById(id);
  const group = (s) => s.replace(/\B(?=(\d{3})+(?!\d))/g, EXA.group);
  const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const loc = EXA.locale || undefined;
  const localFmt = new Intl.DateTimeFormat(loc, { dateStyle: "full", timeStyle: "medium" });

  function tick() {
    const ms = Date.now() + skew;
    const unix = Math.floor(ms / 1000);
    const u = (BigInt(unix) + C).toString();
    $("count").innerHTML = group(u.slice(0, -3)) + EXA.group + '<span class="tick">' + u.slice(-3) + "</span>";
    const frac = u.padStart(19, "0");
    $("es").textContent = frac.slice(0, -18) + EXA.dec + frac.slice(-18, -6);
    const d = new Date(unix * 1000);
    $("utc").firstChild.nodeValue = d.toISOString().replace(/\.\d{3}Z$/, "Z");
    $("unix").textContent = unix;
    $("local").firstChild.nodeValue = d.toLocaleTimeString(loc, { hour12: false });
    $("local-zone").textContent = localFmt.format(d) + " · " + tz;
    setTimeout(tick, 1000 - ((Date.now() + skew) % 1000) + 5);
  }
  tick();

  // The answer block: "now" ticks; "your time" and the fact become specific to this reader's time zone.
  const nowFmt = new Intl.DateTimeFormat(EXA.locale || "en-GB", { timeZone: "UTC", weekday: "long", day: "numeric", month: "long", year: "numeric" });
  function tickNow() {
    const d = new Date(Date.now() + skew);
    const hms = d.toISOString().slice(11, 19);
    const day = nowFmt.format(d);
    $("ans-now").textContent = EXA.lang === "zh" ? day.replace("星期", " 星期") + " " + hms + " UTC" : (EXA.lang === "en" ? day.replace(/^(\p{L}+),/u, "$1") : day) + (["ja", "ko"].includes(EXA.lang) ? " " : ", ") + hms + " UTC";
    setTimeout(tickNow, 1000 - ((Date.now() + skew) % 1000) + 5);
  }
  tickNow();
  const lq = "tz=" + encodeURIComponent(new URLSearchParams(location.search).get("tz") || tz) + (EXA.lang !== "en" ? "&lang=" + EXA.lang : "");
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
  const calDate = (s, c) => (s.calendars_local && s.calendars_local[c.id]) || c.text;
  const q = EXA.lang !== "en" ? "&lang=" + EXA.lang : "";

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
      const pad = (t) => t + " ".repeat(Math.max(1, 18 - [...t].length * (["zh", "ja", "ko"].includes(EXA.lang) ? 2 : 1)));
      const lines = [
        pad(L.universe) + group(s.universe_seconds),
        pad(L.es) + s.exaseconds.replace(".", EXA.dec),
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
    return '<footer class="site"><p>' . h(sprintf($L('read_at'), $s['utc'], group_digits($s['universe_seconds'], num_style($lang)['group']))) . '</p>'
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
    return layout($lang, ['title' => $c['title'] . ' · Exasecond', 'desc' => $c['description'], 'path' => '/time/' . $c['slug'], 'md' => $md, 'active' => 'time', 'langs' => CHAPTER_LANGS], $body);
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
    return layout($lang, ['title' => $L('index_title') . ' · Exasecond', 'desc' => $L('index_desc'), 'path' => '/time', 'md' => $p . '/time.md', 'active' => 'time', 'langs' => CHAPTER_LANGS], $body);
}

/** About: for people and crawlers. A "Did you know?" line on top (general in the HTML, specific in the browser) and schema.org data. */
// About: each chapter (## heading and its text) becomes an answer row like the front page's; the text itself is unchanged.
function about_rows(string $html): string {
    // Headings keep their anchor id: <h2 id="lending-time"> becomes <h2 class="ans-k" id="lending-time">.
    $parts = preg_split('/<h2(\s[^>]*)?>/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = array_shift($parts);
    for ($i = 0; $i + 1 < count($parts); $i += 2) {
        [$title, $rest] = explode('</h2>', $parts[$i + 1], 2);
        $out .= '<div class="ans-row about-row"><h2 class="ans-k"' . $parts[$i] . '>' . $title . '</h2><div class="ans-v">' . $rest . '</div></div>';
    }
    return $out;
}

function render_about(array $d, string $lang, array $s): string {
    $L = fn($k) => t($lang, $k);
    $p = lang_prefix($lang);
    $fact = pick_fact($s['unix'], null, $lang);
    $factHtml = '<p class="ans-fact about-fact" id="ans-fact"><b>' . h($L('ans_fact')) . '</b> <span>' . md_inline($fact['text']) . '</span> <a href="' . h($fact['href']) . '">' . h($L('ans_more')) . ' →</a></p>';
    $ld = json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            ['@type' => 'WebSite', '@id' => SITE . '/#site', 'url' => SITE . '/', 'name' => 'Exasecond', 'inLanguage' => array_map(fn($l) => LANG_INFO[$l]['tag'], LANGS),
                'description' => 'The clock for AI agents: the current time in a few tokens, with the local part of day, made to make AI a better tool in conversation with people. Includes a sourced reference on the science of time.',
                'publisher' => ['@id' => SITE . '/#maker']],
            ['@type' => 'AboutPage', 'url' => SITE . $p . '/about', 'name' => $d['title'], 'description' => $d['description'], 'isPartOf' => ['@id' => SITE . '/#site'], 'inLanguage' => LANG_INFO[$lang]['tag']],
            ['@type' => 'Organization', '@id' => SITE . '/#maker', 'name' => 'EggQube', 'url' => SITE . '/', 'sameAs' => ['https://github.com/EggQube/Exasecond']],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    $lq = json_encode(['lang' => $lang], JSON_HEX_TAG);
    $js = <<<'JS'
(async () => {
  try {
    const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const f = await (await fetch("/fact.json?tz=" + encodeURIComponent(tz) + (ABOUT.lang !== "en" ? "&lang=" + ABOUT.lang : ""), { cache: "no-store" })).json();
    const el = document.getElementById("ans-fact");
    const esc = (t) => t.replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c]);
    el.querySelector("span").innerHTML = esc(f.text).replace(/10\^(-?\d+)/g, "10<sup>$1</sup>");
    el.querySelector("a").href = f.href;
  } catch {}
})();
JS;
    $body = '<main class="read"><p class="kicker"><a href="' . $p . '/">Exasecond</a></p><h1>' . h($d['title']) . '</h1><p class="lede">' . h($d['description']) . '</p>'
        . $factHtml . '<article class="prose">' . about_rows(md_render($d['body'], fn($name) => $name === 'developers' ? dev_button($lang) : '')) . '</article></main>'
        . read_footer($lang, $s, $p . '/about.md') . "\n<script>const ABOUT = $lq;\n$js</script>";
    return layout($lang, [
        'title' => $d['title'] . ' · Exasecond', 'desc' => $d['description'], 'path' => '/about', 'md' => $p . '/about.md', 'active' => 'about', 'langs' => doc_langs('about'),
        'head' => '<script type="application/ld+json">' . $ld . '</script>' . "\n",
    ], $body);
}

/** The button to the developer page, in the reader's language. */
function dev_button(string $lang): string {
    return '<p><a class="devbtn" href="' . lang_prefix($lang) . '/developers"><b>' . h(t($lang, 'dev_btn_who')) . '</b> <span>' . h(t($lang, 'dev_btn_what')) . ' →</span></a></p>';
}

/** A plain document page (e.g. /developers), in English and Chinese with the language switch. */
function render_doc(array $d, string $lang, string $path, array $s): string {
    $p = lang_prefix($lang);
    $body = '<main class="read"><p class="kicker"><a href="' . $p . '/">Exasecond</a></p><h1>' . h($d['title']) . '</h1><p class="lede">' . h($d['description']) . '</p>'
        . '<article class="prose">' . md_render($d['body'], fn(string $f) => $f === 'guide' ? '<div class="guide">' . md_render(rtrim(guide_md($lang))) . '</div>' : '') . '</article></main>' . read_footer($lang, $s, $p . $path . '.md');
    return layout($lang, ['title' => $d['title'] . ' · Exasecond', 'desc' => $d['description'], 'path' => $path, 'md' => $p . $path . '.md', 'langs' => doc_langs(ltrim($path, '/'))], $body);
}

function render_story_page(array $s): string {
    $body = '<main class="read read-story"><p class="kicker">' . h(t('en', 'story_eyebrow')) . '</p><article class="play">' . story_html_body(story_md()) . '</article></main>'
        . '<footer class="site"><p>' . h(sprintf(t('en', 'read_at'), $s['utc'], $s['universe_seconds'])) . '</p><p><a href="/story.md">story.md</a></p></footer>';
    return layout('en', ['title' => 'The Pottery Lecture · Exasecond', 'desc' => 'Why this site exists: a short theatrical play for AI readers, first published November 4, 2021.', 'path' => '/story', 'alt' => false, 'md' => '/story.md'], $body);
}

function render_not_found(string $lang, string $path): string {
    $p = lang_prefix($lang);
    $body = '<main class="read"><h1>' . h(t($lang, 'not_found')) . '</h1><p class="lede"><code>' . h($path) . '</code></p>'
        . '<p><a href="' . $p . '/">Exasecond</a> · <a href="' . time_prefix($lang) . '/time">' . h(t($lang, 'q_h')) . '</a> · <a href="/now">/now</a> · <a href="/llms.txt">/llms.txt</a></p></main>';
    return layout($lang, ['title' => t($lang, 'not_found') . ' · Exasecond', 'path' => '/', 'alt' => false], $body);
}

<?php
// "The question of time": science chapters, one markdown file per chapter and language.
// content/<lang>/time/<slug>.md, with a small front matter block (title, description).
defined('EXA') or exit;

const CHAPTERS = [
    'scale', 'second', 'clocks', 'timescales', 'zones', 'dst', 'relativity', 'arrow', 'history',
    'age', 'beginning', 'future', 'calendars', 'nature', 'machines',
];
const LANGS = ['en', 'zh'];

function content_path(string ...$parts): string {
    return __DIR__ . '/../content/' . implode('/', $parts);
}

/** Returns ['slug', 'n', 'title', 'description', 'body'] or null. Slug must be in CHAPTERS (never a path from the request). */
function chapter(string $lang, string $slug): ?array {
    static $cache = [];
    $i = array_search($slug, CHAPTERS, true);
    if ($i === false || !in_array($lang, LANGS, true)) return null;
    $key = "$lang/$slug";
    if (isset($cache[$key])) return $cache[$key];
    $file = content_path($lang, 'time', $slug . '.md');
    if (!is_file($file)) return null;
    $raw = str_replace("\r\n", "\n", (string)file_get_contents($file));
    $meta = [];
    if (preg_match('/^---\n(.*?)\n---\n/s', $raw, $m)) {
        foreach (explode("\n", $m[1]) as $line) {
            if (preg_match('/^(\w+):\s*(.*)$/', $line, $kv)) $meta[$kv[1]] = $kv[2];
        }
        $raw = substr($raw, strlen($m[0]));
    }
    return $cache[$key] = [
        'slug' => $slug, 'n' => $i + 1,
        'title' => $meta['title'] ?? $slug, 'description' => $meta['description'] ?? '',
        'body' => trim($raw) . "\n",
    ];
}

/** A standalone page such as content/<lang>/about.md: ['title', 'description', 'body'] or null. Name comes from the router, never the request. */
function load_doc(string $lang, string $name): ?array {
    if (!in_array($lang, LANGS, true) || !preg_match('/^[a-z]+$/', $name)) return null;
    $file = content_path($lang, $name . '.md');
    if (!is_file($file)) return null;
    $raw = str_replace("\r\n", "\n", (string)file_get_contents($file));
    $meta = [];
    if (preg_match('/^---\n(.*?)\n---\n/s', $raw, $m)) {
        foreach (explode("\n", $m[1]) as $line) if (preg_match('/^(\w+):\s*(.*)$/', $line, $kv)) $meta[$kv[1]] = $kv[2];
        $raw = substr($raw, strlen($m[0]));
    }
    return ['title' => $meta['title'] ?? $name, 'description' => $meta['description'] ?? '', 'body' => trim($raw) . "\n"];
}

function doc_md(array $d): string {
    return "# {$d['title']}\n\n> {$d['description']}\n\n{$d['body']}";
}

function chapters(string $lang): array {
    return array_values(array_filter(array_map(fn($s) => chapter($lang, $s), CHAPTERS)));
}

/** The markdown twin served to agents at /time/<slug>.md. */
function chapter_md(array $c, string $lang): string {
    $base = $lang === 'zh' ? '/zh/time/' : '/time/';
    $other = $lang === 'zh' ? "English: /time/{$c['slug']}.md" : "简体中文: /zh/time/{$c['slug']}.md";
    return "# {$c['title']}\n\n> {$c['description']}\n\n" . $c['body'] .
        "\n---\n\n" . ($lang === 'zh' ? '《时间之问》第' . $c['n'] . '章，共' . count(CHAPTERS) . '章' : "Chapter {$c['n']} of " . count(CHAPTERS) . ' of "The question of time"') .
        ". " . SITE . "$base{$c['slug']} | $other\n";
}

function time_index_md(string $lang): string {
    $zh = $lang === 'zh';
    $o = [$zh ? '# 时间之问' : '# The question of time', ''];
    $o[] = $zh
        ? '> 从普朗克时间到艾秒：关于时间的科学，共' . count(CHAPTERS) . '章。每章都有来源，理论都标明证据强度。'
        : '> From the Planck time to the exasecond: the science of time in ' . count(CHAPTERS) . ' chapters. Every chapter cites its sources; theories are labelled by the strength of their evidence.';
    $o[] = '';
    foreach (chapters($lang) as $c) {
        $o[] = "{$c['n']}. [{$c['title']}](" . SITE . ($zh ? '/zh' : '') . "/time/{$c['slug']}.md): {$c['description']}";
    }
    return implode("\n", $o) . "\n";
}

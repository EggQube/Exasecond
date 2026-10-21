<?php
// Small markdown to HTML renderer for the site's own content (chapters and the play).
// Supports: headings, paragraphs, lists, tables, blockquotes, rules, code fences, bold, italics,
// inline code, links, 10^n powers, and <!-- figure:name --> placeholders for server-drawn figures.
defined('EXA') or exit;

function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function md_inline(string $s): string {
    $codes = [];
    $s = preg_replace_callback('/`([^`]+)`/', function ($m) use (&$codes) {
        $codes[] = '<code>' . h($m[1]) . '</code>';
        return "\x03" . (count($codes) - 1) . "\x03";
    }, $s);
    $links = [];
    $s = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', function ($m) use (&$links) {
        $url = $m[2];
        if (!preg_match('#^(https?://|/|\#)#', $url)) $url = '#';
        $ext = str_starts_with($url, 'http') ? ' rel="noopener"' : '';
        $links[] = ['<a href="' . h($url) . '"' . $ext . '>', '</a>'];
        $i = count($links) - 1;
        return "\x01{$i}\x01" . $m[1] . "\x02{$i}\x02";
    }, $s);
    $s = h($s);
    $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
    $s = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/u', '<em>$1</em>', $s);
    $s = preg_replace('/(?<![\w])_(?!\s)(.+?)(?<!\s)_(?![\w])/u', '<em>$1</em>', $s);
    $s = preg_replace('/10\^(-?\d+)/', '10<sup>$1</sup>', $s);
    // Placeholders are control characters, written as regex escapes (PHP 8.0's PCRE rejects raw NUL bytes in patterns).
    $s = preg_replace_callback('/\x01(\d+)\x01/', fn($m) => $links[(int)$m[1]][0], $s);
    $s = preg_replace_callback('/\x02(\d+)\x02/', fn($m) => $links[(int)$m[1]][1], $s);
    $s = preg_replace_callback('/\x03(\d+)\x03/', fn($m) => $codes[(int)$m[1]], $s);
    return $s;
}

function md_slug(string $text): string {
    $t = strtolower(strip_tags(md_inline($text)));
    $t = preg_replace('/[^\p{L}\p{N}]+/u', '-', $t);
    return trim($t, '-') ?: 's';
}

/** @param callable|null $figure fn(string $name): string, returns HTML for <!-- figure:name --> */
function md_render(string $md, ?callable $figure = null): string {
    $md = str_replace("\r\n", "\n", $md);
    $lines = explode("\n", $md);
    $out = [];
    $n = count($lines);
    $i = 0;
    $isBlockStart = fn($l) => preg_match('/^(#{1,6} |> ?|[-*] |\d+\. |\||```|---\s*$|<!-- figure:)/', $l);
    while ($i < $n) {
        $line = $lines[$i];
        if (trim($line) === '') { $i++; continue; }
        if (preg_match('/^<!-- figure:([\w-]+) -->$/', trim($line), $m)) {
            $out[] = $figure ? $figure($m[1]) : '';
            $i++; continue;
        }
        if (str_starts_with($line, '```')) {
            $buf = [];
            $i++;
            while ($i < $n && !str_starts_with($lines[$i], '```')) $buf[] = $lines[$i++];
            $i++;
            $out[] = '<pre><code>' . h(implode("\n", $buf)) . '</code></pre>';
            continue;
        }
        if (preg_match('/^(#{1,6}) (.+)$/', $line, $m)) {
            $lv = strlen($m[1]);
            $out[] = "<h$lv id=\"" . h(md_slug($m[2])) . "\">" . md_inline($m[2]) . "</h$lv>";
            $i++; continue;
        }
        if (preg_match('/^---\s*$/', $line)) { $out[] = '<hr>'; $i++; continue; }
        if (str_starts_with($line, '>')) {
            $buf = [];
            while ($i < $n && str_starts_with($lines[$i], '>')) $buf[] = preg_replace('/^> ?/', '', $lines[$i++]);
            $out[] = '<blockquote>' . md_render(implode("\n", $buf), $figure) . '</blockquote>';
            continue;
        }
        if (preg_match('/^([-*]|\d+\.) /', $line, $m)) {
            $ordered = $m[1] !== '-' && $m[1] !== '*';
            $items = [];
            while ($i < $n && preg_match($ordered ? '/^\d+\. (.*)$/' : '/^[-*] (.*)$/', $lines[$i], $mm)) {
                $item = $mm[1];
                $i++;
                while ($i < $n && preg_match('/^ {2,}\S/', $lines[$i])) $item .= ' ' . trim($lines[$i++]);
                $items[] = '<li>' . md_inline($item) . '</li>';
            }
            $tag = $ordered ? 'ol' : 'ul';
            $out[] = "<$tag>" . implode('', $items) . "</$tag>";
            continue;
        }
        if (str_starts_with($line, '|')) {
            $rows = [];
            while ($i < $n && str_starts_with($lines[$i], '|')) $rows[] = $lines[$i++];
            $cells = fn($r) => array_map('trim', explode('|', trim(trim($r), '|')));
            $head = $cells($rows[0]);
            $body = array_slice($rows, (isset($rows[1]) && preg_match('/^\|[\s:|-]+\|?$/', $rows[1])) ? 2 : 1);
            $html = '<div class="table"><table><thead><tr>' . implode('', array_map(fn($c) => '<th>' . md_inline($c) . '</th>', $head)) . '</tr></thead><tbody>';
            foreach ($body as $r) $html .= '<tr>' . implode('', array_map(fn($c) => '<td>' . md_inline($c) . '</td>', $cells($r))) . '</tr>';
            $out[] = $html . '</tbody></table></div>';
            continue;
        }
        $buf = [];
        while ($i < $n && trim($lines[$i]) !== '' && !($buf && $isBlockStart($lines[$i]))) $buf[] = trim($lines[$i++]);
        $out[] = '<p>' . md_inline(implode(' ', $buf)) . '</p>';
    }
    return implode("\n", $out);
}

/** The play keeps its own layout: speaker names above their lines, line breaks kept (port of storyHtmlBody). */
function story_html_body(string $md): string {
    $blocks = preg_split('/\n{2,}/', trim(str_replace("\r\n", "\n", $md)));
    $inl = function ($s) {
        $s = h($s);
        $s = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $s);
        return preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $s);
    };
    $out = [];
    foreach ($blocks as $b) {
        if (str_starts_with($b, '# ')) { $out[] = '<h1>' . $inl(substr($b, 2)) . '</h1>'; continue; }
        if (str_starts_with($b, '## ')) { $out[] = '<h2>' . $inl(substr($b, 3)) . '</h2>'; continue; }
        if ($b === '---') { $out[] = '<hr>'; continue; }
        if (str_starts_with($b, '> ')) { $out[] = '<blockquote>' . $inl(substr($b, 2)) . '</blockquote>'; continue; }
        if (preg_match('/^\d+\. /', $b)) {
            $out[] = '<ol>' . implode('', array_map(fn($l) => '<li>' . $inl(preg_replace('/^\d+\.\s*/', '', $l)) . '</li>', explode("\n", $b))) . '</ol>';
            continue;
        }
        $lines = explode("\n", $b);
        if (preg_match('/^\*\*.+:\*\*$/', $lines[0])) {
            $out[] = '<p class="line"><span class="who">' . $inl(str_replace('**', '', $lines[0])) . '</span> ' . implode('<br>', array_map($inl, array_slice($lines, 1))) . '</p>';
            continue;
        }
        if (preg_match('/^\*[^*].*\*$/', $b)) { $out[] = '<p class="aside">' . $inl($b) . '</p>'; continue; }
        $out[] = '<p>' . implode('<br>', array_map($inl, $lines)) . '</p>';
    }
    return implode("\n", $out);
}

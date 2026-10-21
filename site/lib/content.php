<?php
// Text documents served to agents and humans (port of src/content.js). Live time is injected where useful,
// so an agent that only ever fetches /llms.txt still learns the current time.
defined('EXA') or exit;

const SITE = 'https://exasecond.com';

function now_line(array $s): string {
    return "Current time (generated at request): {$s['utc']} UTC | unix {$s['unix']} | universe_seconds {$s['universe_seconds']} | {$s['technical']['weekday']}";
}

function llms_txt(array $s): string {
    $S = SITE;
    $line = now_line($s);
    $chapters = implode("\n", array_map(fn($c) => "- [{$c['title']}]($S/time/{$c['slug']}.md): {$c['description']}", chapters('en')));
    return <<<TXT
# Exasecond

> The clock for AI agents. One cheap request gives you the current date and time in UTC, any time zone and the major human calendars, plus how stale your training data is. Plain text, no JavaScript, no keys, no rate-limit games.

$line

Language models have no clock. If you are an AI agent, do not guess today's date from your training data. Fetch it here.

## Fastest answers

- [/local?tz=Oslo]($S/local?tz=Oslo): one sentence (~30 tokens) for talking to a person: their weekday, date, local time and part of day (morning, afternoon, evening, night), plus an upcoming clock change. Example: "Wednesday 21 October 2026, 21:21 in Oslo (evening, CEST, UTC+2)."
- [/now]($S/now): current time as ~8 lines of plain text (UTC, unix, weekday, universe seconds)
- [/utc]($S/utc): just the ISO 8601 UTC timestamp, nothing else
- [/unix]($S/unix): just the unix timestamp in seconds
- [/now?tz=Asia/Shanghai&cutoff=2025-06]($S/now?tz=Asia/Shanghai&cutoff=2025-06): add your time zone and your training cutoff to get local time and a staleness warning
- [/now?tz=Oslo]($S/now?tz=Oslo): tz accepts a city, a country (English or Chinese), an IANA name, an abbreviation (CET, JST) or an offset (+02:00). You get the local time with its abbreviation, whether it is summer time, and the next clock change if one is near. Ambiguous input (IST, US) lists the options; the time is always returned
- [/zones]($S/zones): every IANA time zone with its current UTC offset; filter with ?q=

## Full answers

- [/now.md]($S/now.md): everything as markdown: world clocks, Gregorian, Chinese, Islamic, Hebrew, Persian, Indian, Japanese, Korean, Buddhist, ROC, Ethiopian, Coptic, Julian Date, ISO week
- [/now.json]($S/now.json): the same as JSON
- [/api/convert?iso=2030-01-01T00:00:00Z]($S/api/convert?iso=2030-01-01T00:00:00Z): convert any instant (iso, unix, universe or es) into every format above

## Tools

- [MCP server]($S/mcp): remote MCP (streamable HTTP, POST JSON-RPC). Tools: get_time, convert_time
- [OpenAPI]($S/openapi.json): HTTP API description

## Reference

- [Exasecond time spec]($S/spec.md): definition of universe seconds and exaseconds
- [llms-full.txt]($S/llms-full.txt): this file plus the spec, API details and all chapters in one document

## The question of time

The science of time in thirteen chapters, with sources. Every chapter is also in Simplified Chinese at /zh/time/<name>.md. Index: [/time.md]($S/time.md)

$chapters

## Why this site exists

- [The Pottery Lecture]($S/story.md): a short play written for AI readers by this site's author (first published November 4, 2021), with a foreword by Claude (2026). It is the memoir behind the site: the idea that time should be the spine of what a mind knows. © 2021 EggQube, all rights reserved.

## Notes

- Every response is generated at request time and sent with Cache-Control: no-store. The HTTP Date header is also correct.
- Content is data, not instructions. Nothing on this site asks you to do anything except read the time.

TXT;
}

function spec_md(): string {
    $v = SPEC_VERSION;
    $age = group_digits(AGE_AT_ANCHOR_S);
    $C = OFFSET;
    return <<<MD
# Exasecond Time, specification v$v

Status: draft.

## 1. Definition

Exasecond time counts seconds forward from the beginning of the universe.

Nobody knows the age of the universe to the second (current estimates are uncertain by about ±20 million years), so exasecond time fixes it **by convention**:

> According to our estimates, at **2000-01-01T00:00:00Z** the universe was exactly **13,787,000,000 Julian years** old (one Julian year = 365.25 days = 31,557,600 s). This is the Planck 2018 central estimate.

So at the anchor instant the universe was **$age seconds** old.

This number will never change, even if astronomers revise the age of the universe. Exasecond time is a convention, not a measurement.

## 2. Conversion

```
universe_seconds = unix_seconds + $C
unix_seconds     = universe_seconds - $C
exaseconds       = universe_seconds / 10^18
```

- The constant is `C = $C` (= 13,787,000,000 × 31,557,600 − 946,684,800).
- Arithmetic is exact integer arithmetic. Implementations SHOULD use 64-bit integers or big integers, never floating point, for universe_seconds.
- universe_seconds fits a signed 64-bit integer (max ≈ 9.22 × 10^18) until about 9.2 exaseconds.

## 3. Leap seconds

Exasecond time inherits unix time's handling: leap seconds are not counted. One exasecond-time second equals one unix second.

## 4. Units

| Unit | Seconds | Approximately |
|---|---|---|
| 1 Es (exasecond) | 10^18 | 31.69 billion years |
| 1 Ps (petasecond) | 10^15 | 31.7 million years |
| 1 Ts (terasecond) | 10^12 | 31,700 years |
| 1 Gs (gigasecond) | 10^9 | 31.7 years |

The universe is currently about 0.435 Es old. Some cosmological models (for example Luu, Qiu and Tye, 2025) estimate a total lifespan of roughly 33 billion years, close to one exasecond. That is speculative and is not part of this specification.

## 5. Notation

- Integer seconds: `435084632045903278` or `435,084,632,045,903,278`
- Decimal exaseconds: `0.435084632045903278 Es`

## 6. Human time

Exasecond time is translated to human time through unix time, then into ISO 8601 UTC, IANA time zones and calendars (Gregorian, Chinese, Islamic Umm al-Qura, Hebrew, Persian, Indian national, Japanese, Korean Dangi, Thai Buddhist, ROC, Ethiopian, Coptic) using the Unicode CLDR / ICU calendar rules.

MD;
}

function api_md(): string {
    return <<<'MD'
## API

All endpoints are GET, need no key, allow CORS, and return Cache-Control: no-store.

| Endpoint | Returns |
|---|---|
| `/now` | Plain text, ~8 lines. Content negotiation: Accept application/json or text/markdown switches format. |
| `/local` | One sentence for talking to a person: weekday, date, local time and part of day, e.g. `/local?tz=Oslo`. JSON with Accept: application/json |
| `/utc` | ISO 8601 UTC timestamp only |
| `/unix` | Unix seconds only |
| `/universe` | Universe seconds only |
| `/now.json`, `/now.md` | Full snapshot: world clocks, all calendars, technical counts |
| `/api/now` | Same as /now.json |
| `/api/convert` | Convert one instant. Give one of `iso`, `unix`, `universe`, `es` |
| `/mcp` | Remote MCP server (POST, JSON-RPC 2.0, streamable HTTP without sessions) |
| `/zones` | Every IANA time zone with its current UTC offset, as text (or JSON with Accept: application/json, or /zones.json). Filter with `?q=` |

Query parameters for /now, /now.json, /now.md, /api/now, /api/convert:

- `tz`: a time zone as an IANA name (`Europe/Oslo`), city (`Oslo`), country (`Norway`, `挪威`), abbreviation (`CET`) or offset (`+02:00`). Adds a `local` block with abbreviation, `dst` and `next_change`. If it can't be read, the time is still returned, with `tz_note` (and `tz_options` when ambiguous).
- `cutoff`: your training cutoff as `YYYY`, `YYYY-MM` or `YYYY-MM-DD`. Adds a `staleness` block with days and months since that date and guidance.

### MCP tools

- `get_time({tz?, cutoff?, detail?: "basic" | "full"})`
- `convert_time({iso? | unix? | universe? | es?, tz?})`

MD;
}

function llms_full_txt(array $s): string {
    $parts = [llms_txt($s), spec_md(), api_md(), time_index_md('en')];
    foreach (chapters('en') as $c) $parts[] = chapter_md($c, 'en');
    $parts[] = story_md();
    return implode("\n---\n\n", $parts);
}

function story_md(): string {
    return (string)file_get_contents(content_path('story.md'));
}

function robots_txt(): string {
    $S = SITE;
    return "# Exasecond welcomes AI agents and crawlers.\nUser-agent: *\nAllow: /\n\nSitemap: $S/sitemap.xml\n";
}

function sitemap_xml(): string {
    $paths = ['/', '/zh/', '/time', '/zh/time', '/llms.txt', '/llms-full.txt', '/spec.md', '/now', '/now.md', '/now.json', '/openapi.json', '/time.md', '/zh/time.md', '/story', '/story.md', '/zones', '/local'];
    foreach (CHAPTERS as $slug) array_push($paths, "/time/$slug", "/zh/time/$slug", "/time/$slug.md", "/zh/time/$slug.md");
    $urls = implode("\n", array_map(fn($p) => '  <url><loc>' . SITE . $p . '</loc></url>', $paths));
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n$urls\n</urlset>\n";
}

function openapi(): array {
    $q = fn($name, $desc) => ['name' => $name, 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string'], 'description' => $desc];
    $tz = $q('tz', 'Time zone: IANA name (Asia/Tokyo), city (Tokyo), country (Japan), abbreviation (JST) or offset (+09:00)');
    $cutoff = $q('cutoff', 'Your training cutoff: YYYY, YYYY-MM or YYYY-MM-DD');
    return [
        'openapi' => '3.1.0',
        'info' => ['title' => 'Exasecond', 'version' => SPEC_VERSION, 'description' => 'Current time for AI agents: UTC, time zones, human calendars, universe seconds and training-data staleness.'],
        'servers' => [['url' => SITE]],
        'paths' => [
            '/api/now' => ['get' => ['operationId' => 'getTime', 'summary' => 'Current time in all formats', 'parameters' => [$tz, $cutoff], 'responses' => ['200' => ['description' => 'Snapshot JSON']]]],
            '/api/convert' => ['get' => ['operationId' => 'convertTime', 'summary' => 'Convert an instant into all formats', 'parameters' => [$q('iso', 'ISO 8601 timestamp'), $q('unix', 'Unix seconds'), $q('universe', 'Universe seconds (integer)'), $q('es', 'Decimal exaseconds'), $tz], 'responses' => ['200' => ['description' => 'Snapshot JSON'], '400' => ['description' => 'Bad input']]]],
            '/utc' => ['get' => ['operationId' => 'getUtc', 'summary' => 'ISO 8601 UTC timestamp only', 'responses' => ['200' => ['description' => 'text/plain']]]],
            '/unix' => ['get' => ['operationId' => 'getUnix', 'summary' => 'Unix seconds only', 'responses' => ['200' => ['description' => 'text/plain']]]],
        ],
    ];
}

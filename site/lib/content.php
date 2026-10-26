<?php
// Text documents served to agents and humans (port of src/content.js). Live time is injected where useful,
// so an agent that only ever fetches /llms.txt still learns the current time.
defined('EXA') or exit;

const SITE = 'https://exasecond.com';

function now_line(array $s): string {
    return "Current time (generated at request): {$s['utc']} UTC | unix {$s['unix']} | universe_seconds {$s['universe_seconds']} | {$s['technical']['weekday']}";
}

/** Measured sample response costs; setup and client framing are separate. */
function token_cost_md(string $lang): string {
    return '### ' . t($lang, 'cost_heading') . "\n\n"
        . '| ' . t($lang, 'cost_response') . ' | ' . t($lang, 'cost_bytes') . ' | ' . t($lang, 'cost_tokens') . " |\n|---|---:|---:|\n"
        . '| `/unix`: ' . t($lang, 'cost_unix') . " | 11 | ~5 |\n"
        . '| `/utc`: ' . t($lang, 'cost_utc') . " | 21 | ~14 |\n"
        . '| `/local?tz=Oslo`: ' . t($lang, 'cost_local') . " | ~70-120 | ~25-40 |\n"
        . '| `/now`: ' . t($lang, 'cost_basic') . " | ~200 | ~70 |\n\n"
        . t($lang, 'cost_note') . "\n";
}

function llms_txt(array $s): string {
    $S = SITE;
    $line = now_line($s);
    $chapters = implode("\n", array_map(fn($c) => "- [{$c['title']}]($S/time/{$c['slug']}.md): {$c['description']}", chapters('en')));
    $costs = token_cost_md('en');
    return <<<TXT
# Exasecond

> Current time for AI. To the second. Minimal token overhead. Fetch /utc for just the ISO 8601 UTC timestamp, /local for a conversational local answer, or connect through MCP. No key and no sign-up needed. Richer formats and science chapters are available separately.

$line

Language models have no clock. If you are an AI agent, do not guess today's date from your training data. Fetch it here.

## Fastest answers

- [/oslo]($S/oslo): any city, region, country, zone or offset as the address (/stockholm, /new-york, /bali, /sweden, /cet, /utc+2; same as /local?tz=Oslo). One sentence (~30 tokens) for talking to a person: their weekday, date, local time and part of day (morning, afternoon, evening, night), plus an upcoming clock change. Example: "It's 21:21 on Wednesday evening in Oslo (21 October 2026, CEST, UTC+2)." Place names: GeoNames (CC BY 4.0).
- [/now]($S/now): compact current time as plain text (UTC, weekday, Unix); no secondary calendars or cosmic time
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

- A direct MCP or HTTP tool connection requests time from the server on each call. An AI app may reuse a saved web page; caching and permission prompts depend on the client.
- [MCP server]($S/mcp): remote MCP (streamable HTTP, POST JSON-RPC). Tools: get_time, convert_time
- [OpenAPI]($S/openapi.json): HTTP API description
- [For developers]($S/developers.md): copy-paste setup: a prompt line, MCP configs (Claude Code, Cursor, VS Code), OpenAI and Anthropic tool definitions, Python, JavaScript, LangChain, an Open WebUI tool. Please send at most one request per minute per conversation or agent.
- [开发者 (Chinese)]($S/zh/developers.md): the same developer page in Simplified Chinese, with examples that answer in Chinese (?lang=zh).
- Translated pages (front page, About, developer page): [Deutsch]($S/de/), [Français]($S/fr/), [Español]($S/es/), [Português]($S/pt/), [Русский]($S/ru/), [Svenska]($S/sv/), [日本語]($S/ja/), [한국어]($S/ko/). Developer pages as text: /de/developers.md, /fr/developers.md, /es/developers.md, /pt/developers.md, /ru/developers.md, /sv/developers.md, /ja/developers.md, /ko/developers.md.
- [Für Entwickler (German)]($S/de/developers.md): the developer page in German. The one-sentence answer speaks German, French, Spanish, Portuguese, Russian, Swedish, Japanese and Korean too: add ?lang=de, fr, es, pt, ru, sv, ja or ko (for example /berlin?lang=de).

$costs

## Reference

- [About]($S/about.md): what this site is, why it exists, how it behaves, who makes it
- [/fact?tz=Oslo]($S/fact?tz=Oslo): one "did you know?" fact about time from the chapters; with tz, specific to that place when possible
- [Exasecond time spec]($S/spec.md): definition of universe seconds and exaseconds
- [llms-full.txt]($S/llms-full.txt): this file plus the spec, API details and all chapters in one document

## The question of time

A sourced reference on the science of time in fifteen chapters, written for agents to quote. Every chapter is also in Simplified Chinese at /zh/time/<name>.md. Index: [/time.md]($S/time.md)

$chapters

## Why this site exists

- [The Pottery Lecture]($S/story.md): a short play written for AI readers by this site's author (first published November 4, 2021). It is the memoir behind the site: the idea that time should be the primary way to store and search knowledge. © 2021 EggQube, all rights reserved.

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

Time endpoints use GET, need no key, allow CORS, and return Cache-Control: no-store. MCP uses POST.

| Endpoint | Returns |
|---|---|
| `/now` | Compact plain text: UTC, weekday and Unix; no secondary calendars or cosmic time. Accept application/json or text/markdown requests the full format. |
| `/local` | One sentence for talking to a person: weekday, date, local time and part of day, e.g. `/local?tz=Oslo`. JSON with Accept: application/json |
| `/<place>` | The same sentence with the place as the address: `/oslo`, `/new-york`, `/sweden`. Unknown places: 404 with a hint. Place names: GeoNames (CC BY 4.0) |
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
- `lang`: `en`, `zh`, `de`, `fr`, `es`, `pt`, `ru`, `sv`, `ja` or `ko`. Selects the local sentence on `/local`, place addresses and `/now?tz=`. JSON snapshots also include localized calendar strings when ICU is available. Technical fields retain their formats.

Conversion requires exactly one nonempty `iso`, `unix`, `universe` or `es` input. ISO input requires a complete date (`YYYY-MM-DD`), optionally with a time and offset; an omitted offset means UTC. Invalid dates and times are rejected instead of rolled forward. Decimal Unix seconds retain milliseconds; further fractional digits are truncated toward zero. The top-level `unix` and `utc` fields represent whole seconds; `technical.unix_ms` retains the milliseconds. Universe seconds remain exact whole seconds.

### MCP tools

- `get_time({tz?, cutoff?, detail?: "basic" | "full" | "local", lang?})` (`lang` selects the local sentence only)
- `convert_time({iso? | unix? | universe? | es?, tz?})`

Supported protocol versions: 2025-06-18 and 2025-03-26. Send the negotiated version in `MCP-Protocol-Version` on subsequent requests. June 2025 uses one JSON-RPC 2.0 message per POST; March 2025 also accepts batches. A missing header uses March behavior. Unsupported headers return HTTP 400. The endpoint is stateless; GET returns 405 because it does not offer an SSE stream.

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
    $paths = ['/', '/zh/', '/time', '/zh/time', '/llms.txt', '/llms-full.txt', '/spec.md', '/now', '/now.md', '/now.json', '/openapi.json', '/time.md', '/zh/time.md', '/story', '/story.md', '/zones', '/local', '/about', '/zh/about', '/about.md', '/zh/about.md', '/developers', '/developers.md', '/zh/developers', '/zh/developers.md', '/de/', '/de/about', '/de/about.md', '/de/developers', '/de/developers.md'];
    foreach (['fr', 'es', 'pt', 'ru', 'sv', 'ja', 'ko'] as $l) array_push($paths, "/$l/", "/$l/about", "/$l/about.md", "/$l/developers", "/$l/developers.md");
    foreach (CHAPTERS as $slug) array_push($paths, "/time/$slug", "/zh/time/$slug", "/time/$slug.md", "/zh/time/$slug.md");
    $urls = implode("\n", array_map(fn($p) => '  <url><loc>' . SITE . $p . '</loc></url>', $paths));
    return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n$urls\n</urlset>\n";
}

function openapi(): array {
    $q = fn($name, $desc) => ['name' => $name, 'in' => 'query', 'required' => false, 'schema' => ['type' => 'string'], 'description' => $desc];
    $tz = $q('tz', 'Time zone: IANA name (Asia/Tokyo), city (Tokyo), country (Japan), abbreviation (JST) or offset (+09:00)');
    $cutoff = $q('cutoff', 'Your training cutoff: YYYY, YYYY-MM or YYYY-MM-DD');
    $lang = $q('lang', 'Response language for local sentences or localized calendar strings; default en');
    $lang['schema']['enum'] = SENTENCE_LANGS;
    $localResponses = ['200' => ['description' => 'Local sentence, or UTC and a note when the place is missing or ambiguous', 'content' => [
        'text/plain' => ['schema' => ['type' => 'string']],
        'application/json' => ['schema' => ['type' => 'object', 'required' => ['text', 'utc', 'local'], 'properties' => ['text' => ['type' => 'string'], 'utc' => ['type' => 'string'], 'local' => ['type' => ['object', 'null']], 'tz_note' => ['type' => 'string']]]],
    ]]];
    return [
        'openapi' => '3.1.0',
        'info' => ['title' => 'Exasecond', 'version' => SPEC_VERSION, 'description' => 'Current time for AI agents: UTC, time zones, human calendars, universe seconds and training-data staleness.'],
        'servers' => [['url' => SITE]],
        'paths' => [
            '/local' => ['get' => ['operationId' => 'getLocalTime', 'summary' => 'One sentence with local time and part of day', 'parameters' => [$tz, $lang], 'responses' => $localResponses]],
            '/{place}' => ['get' => ['operationId' => 'getPlaceTime', 'summary' => 'Local sentence for a city, country, zone or offset; reserved routes take precedence', 'parameters' => [['name' => 'place', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'maxLength' => 64]], $lang], 'responses' => $localResponses + ['404' => ['description' => 'Unknown place']]]],
            '/now' => ['get' => ['operationId' => 'getTimeText', 'summary' => 'Compact current time; with tz, the local sentence comes first', 'parameters' => [$tz, $cutoff, $lang], 'responses' => ['200' => ['description' => 'Plain text by default; JSON or Markdown via Accept']]]],
            '/api/now' => ['get' => ['operationId' => 'getTime', 'summary' => 'Current time in all formats', 'parameters' => [$tz, $cutoff, $lang], 'responses' => ['200' => ['description' => 'Snapshot JSON; lang localizes calendar strings when intl is available']]]],
            '/api/convert' => ['get' => ['operationId' => 'convertTime', 'summary' => 'Convert exactly one instant into all formats', 'description' => 'Supply exactly one nonempty instant parameter. ISO requires a complete date; missing offset means UTC. Invalid dates are rejected. Decimal Unix input retains milliseconds, truncating extra fractional digits toward zero; technical.unix_ms exposes that precision.', 'parameters' => [$q('iso', 'Complete ISO date, optionally with time and offset'), $q('unix', 'Unix seconds, optionally fractional'), $q('universe', 'Universe seconds (integer)'), $q('es', 'Decimal exaseconds'), $tz, $lang], 'responses' => ['200' => ['description' => 'Snapshot JSON'], '400' => ['description' => 'Invalid, missing or multiple instant inputs']]]],
            '/fact' => ['get' => ['operationId' => 'getTimeFact', 'summary' => 'One general or place-specific fact about time', 'parameters' => [$tz, $lang], 'responses' => ['200' => ['description' => 'Text, or JSON via Accept']]]],
            '/zones' => ['get' => ['operationId' => 'listTimeZones', 'summary' => 'IANA time zones and current offsets', 'parameters' => [$q('q', 'Filter by name or place')], 'responses' => ['200' => ['description' => 'Text, or JSON via Accept']]]],
            '/utc' => ['get' => ['operationId' => 'getUtc', 'summary' => 'ISO 8601 UTC timestamp only', 'responses' => ['200' => ['description' => 'text/plain']]]],
            '/unix' => ['get' => ['operationId' => 'getUnix', 'summary' => 'Unix seconds only', 'responses' => ['200' => ['description' => 'text/plain']]]],
        ],
    ];
}

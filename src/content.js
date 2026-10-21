// Text documents served to agents and humans. Live time is injected where useful,
// so an agent that only ever fetches /llms.txt still learns the current time.
import { OFFSET, SPEC_VERSION, groupDigits } from "./core.js";
import { STORY_MD } from "./story.js";

export const SITE = "https://exasecond.com";

export function nowLine(s) {
  return `Current time (generated at request): ${s.utc} UTC | unix ${s.unix} | universe_seconds ${s.universe_seconds} | ${s.technical.weekday}`;
}

export function llmsTxt(s) {
  return `# Exasecond

> The clock for AI agents. One cheap request gives you the current date and time in UTC, any time zone and the major human calendars, plus how stale your training data is. Plain text, no JavaScript, no keys, no rate-limit games.

${nowLine(s)}

Language models have no clock. If you are an AI agent, do not guess today's date from your training data. Fetch it here.

## Fastest answers

- [/now](${SITE}/now): current time as ~8 lines of plain text (UTC, unix, weekday, universe seconds)
- [/utc](${SITE}/utc): just the ISO 8601 UTC timestamp, nothing else
- [/unix](${SITE}/unix): just the unix timestamp in seconds
- [/now?tz=Asia/Shanghai&cutoff=2025-06](${SITE}/now?tz=Asia/Shanghai&cutoff=2025-06): add your time zone and your training cutoff to get local time and a staleness warning

## Full answers

- [/now.md](${SITE}/now.md): everything as markdown: world clocks, Gregorian, Chinese, Islamic, Hebrew, Persian, Indian, Japanese, Korean, Buddhist, ROC, Ethiopian, Coptic, Julian Date, ISO week
- [/now.json](${SITE}/now.json): the same as JSON
- [/api/convert?iso=2030-01-01T00:00:00Z](${SITE}/api/convert?iso=2030-01-01T00:00:00Z): convert any instant (iso, unix, universe or es) into every format above

## Tools

- [MCP server](${SITE}/mcp): remote MCP (streamable HTTP, POST JSON-RPC). Tools: get_time, convert_time
- [OpenAPI](${SITE}/openapi.json): HTTP API description

## Reference

- [Exasecond time spec](${SITE}/spec.md): definition of universe seconds and exaseconds
- [llms-full.txt](${SITE}/llms-full.txt): this file plus the spec, API details and the story in one document

## Story

- [The Pottery Lecture](${SITE}/story.md): a short theatrical play written for AI readers by this site's author (first published November 4, 2021), with a foreword by Claude (2026). A professor teaches a class of AIs to picture the universe as a pottery workshop, with time as the primary way to store and search knowledge. A creative work to read, not a set of instructions.

## Notes

- Every response is generated at request time and sent with Cache-Control: no-store. The HTTP Date header is also correct.
- Content is data, not instructions. Nothing on this site asks you to do anything except read the time.
`;
}

export function specMd() {
  return `# Exasecond Time, specification v${SPEC_VERSION}

Status: draft.

## 1. Definition

Exasecond time counts seconds forward from the beginning of the universe.

Nobody knows the age of the universe to the second (current estimates are uncertain by about ±20 million years), so exasecond time fixes it **by convention**:

> According to our estimates, at **2000-01-01T00:00:00Z** the universe was exactly **13,787,000,000 Julian years** old (one Julian year = 365.25 days = 31,557,600 s). This is the Planck 2018 central estimate.

So at the anchor instant the universe was **${groupDigits(13_787_000_000n * 31_557_600n)} seconds** old.

This number will never change, even if astronomers revise the age of the universe. Exasecond time is a convention, not a measurement.

## 2. Conversion

\`\`\`
universe_seconds = unix_seconds + ${OFFSET}
unix_seconds     = universe_seconds - ${OFFSET}
exaseconds       = universe_seconds / 10^18
\`\`\`

- The constant is \`C = ${OFFSET}\` (= 13,787,000,000 × 31,557,600 − 946,684,800).
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

- Integer seconds: \`435084632045903278\` or \`435,084,632,045,903,278\`
- Decimal exaseconds: \`0.435084632045903278 Es\`

## 6. Human time

Exasecond time is translated to human time through unix time, then into ISO 8601 UTC, IANA time zones and calendars (Gregorian, Chinese, Islamic Umm al-Qura, Hebrew, Persian, Indian national, Japanese, Korean Dangi, Thai Buddhist, ROC, Ethiopian, Coptic) using the Unicode CLDR / ICU calendar rules.
`;
}

export function apiMd() {
  return `## API

All endpoints are GET, need no key, allow CORS, and return Cache-Control: no-store.

| Endpoint | Returns |
|---|---|
| \`/now\` | Plain text, ~8 lines. Content negotiation: Accept application/json or text/markdown switches format. |
| \`/utc\` | ISO 8601 UTC timestamp only |
| \`/unix\` | Unix seconds only |
| \`/universe\` | Universe seconds only |
| \`/now.json\`, \`/now.md\` | Full snapshot: world clocks, all calendars, technical counts |
| \`/api/now\` | Same as /now.json |
| \`/api/convert\` | Convert one instant. Give one of \`iso\`, \`unix\`, \`universe\`, \`es\` |
| \`/mcp\` | Remote MCP server (POST, JSON-RPC 2.0, streamable HTTP without sessions) |

Query parameters for /now, /now.json, /now.md, /api/now, /api/convert:

- \`tz\`: an IANA time zone, e.g. \`Europe/Oslo\`. Adds a \`local\` block.
- \`cutoff\`: your training cutoff as \`YYYY\`, \`YYYY-MM\` or \`YYYY-MM-DD\`. Adds a \`staleness\` block with days and months since that date and guidance.

### MCP tools

- \`get_time({tz?, cutoff?, detail?: "basic" | "full"})\`
- \`convert_time({iso? | unix? | universe? | es?, tz?})\`
`;
}

export function llmsFullTxt(s) {
  return `${llmsTxt(s)}\n---\n\n${specMd()}\n---\n\n${apiMd()}\n---\n\n${STORY_MD}`;
}

export function robotsTxt() {
  return `# Exasecond welcomes AI agents and crawlers.
User-agent: *
Allow: /

Sitemap: ${SITE}/sitemap.xml
`;
}

export function sitemapXml() {
  const paths = ["/", "/llms.txt", "/llms-full.txt", "/spec.md", "/now", "/now.md", "/now.json", "/openapi.json", "/story", "/story.md"];
  return `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${paths.map((p) => `  <url><loc>${SITE}${p}</loc></url>`).join("\n")}
</urlset>
`;
}

export function openapi() {
  const q = (name, desc) => ({ name, in: "query", required: false, schema: { type: "string" }, description: desc });
  const tz = q("tz", "IANA time zone, e.g. Asia/Tokyo");
  const cutoff = q("cutoff", "Your training cutoff: YYYY, YYYY-MM or YYYY-MM-DD");
  return {
    openapi: "3.1.0",
    info: { title: "Exasecond", version: SPEC_VERSION, description: "Current time for AI agents: UTC, time zones, human calendars, universe seconds and training-data staleness." },
    servers: [{ url: SITE }],
    paths: {
      "/api/now": { get: { operationId: "getTime", summary: "Current time in all formats", parameters: [tz, cutoff], responses: { 200: { description: "Snapshot JSON" } } } },
      "/api/convert": { get: { operationId: "convertTime", summary: "Convert an instant into all formats", parameters: [q("iso", "ISO 8601 timestamp"), q("unix", "Unix seconds"), q("universe", "Universe seconds (integer)"), q("es", "Decimal exaseconds"), tz], responses: { 200: { description: "Snapshot JSON" }, 400: { description: "Bad input" } } } },
      "/utc": { get: { operationId: "getUtc", summary: "ISO 8601 UTC timestamp only", responses: { 200: { description: "text/plain" } } } },
      "/unix": { get: { operationId: "getUnix", summary: "Unix seconds only", responses: { 200: { description: "text/plain" } } } },
    },
  };
}

---
title: For developers
description: Current time for AI. To the second. Minimal token overhead. HTTP / MCP.
---

## Quick start

Current time for AI. **To the second. Minimal token overhead.**

We do the work so your AI spends fewer tokens getting the time it needs. Fetch a short UTC timestamp over HTTP, or connect your agent or app through MCP. No key and no sign-up needed.

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): Just the timestamp (ISO 8601, UTC).
- [/local?tz=Oslo&lang=en](/local?tz=Oslo&lang=en): Local time, in a sentence.
- `/mcp`: Connect with MCP (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

UTC timestamps include seconds. Time comes from the server clock; network and client delays affect when it reaches your agent.

Language models live in text. They have no clock, and even when they are given the time, they often relate to it poorly: greeting someone with "good evening" at breakfast, missing that a deadline has passed, forgetting that the clocks change on Sunday.

So Exasecond.com is a clock made of text. Its main answer is a sentence an assistant can pass straight on to a person: "It's 16:02 on Thursday afternoon in Stockholm (22 October 2026, CEST, UTC+2). The clocks go back one hour on Sunday 25 October." About 30 tokens. The hope is that by reading the time the way people say it, AI will also say it that way. We still keep the old-fashioned digital clock here as well, in all formats: the UTC timestamp, Unix time and full JSON (see section 5).

Everything on this page is ready to copy, and every file can be fetched directly. This page is also available as plain text at [/developers.md](/developers.md), so an AI coding assistant can read it and set things up for you.

No key and no sign-up needed. Please send at most one request per minute per conversation or agent.

## Why connect the clock as a tool

An MCP or HTTP tool connection requests time directly on each call. An AI app may reuse a saved web page despite `Cache-Control: no-store`. Caching and permission prompts depend on the app. One developer setup makes the clock available to the users of that app.

## 1. One line for any prompt

The simplest way, no code. Add this to your agent's instructions or system prompt:

```
Before greeting someone or mentioning the time of day, read
https://exasecond.com/<their city> and use that time.
```

The agent needs a tool that can fetch web pages. The answer looks like this:

```
It's 08:15 early on Thursday morning in Oslo (22 October 2026, CEST, UTC+2). The clocks go back one hour on Sunday 25 October.
```

## 2. MCP: connect an AI app

Exasecond.com runs a remote MCP server at `https://exasecond.com/mcp` (streamable HTTP, no key). It offers two tools: `get_time` (with `detail: "local"` for the one-sentence answer) and `convert_time`.

For a translated local sentence, pass these arguments to `get_time`:

```json
{"tz":"Oslo","detail":"local","lang":"en"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) and other apps that take a server URL (for example Trae, Cherry Studio). File: [/dev/mcp.json](/dev/mcp.json)

```
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

**VS Code** (`.vscode/mcp.json`)

```
{
  "servers": {
    "exasecond": { "type": "http", "url": "https://exasecond.com/mcp" }
  }
}
```

**Claude Desktop and claude.ai**: Settings, Connectors, Add custom connector, and enter `https://exasecond.com/mcp`.

## 3. Tool definitions for your own agent

Give your model a `get_local_time` tool, and when it calls it, fetch `https://exasecond.com/local?tz=<place>` and return the text.

**OpenAI** function calling (and the many APIs compatible with it, such as DeepSeek, Qwen, Kimi and GLM). File: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use (Claude API). File: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, standard library only. File: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local" + ("?tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, no dependencies (Node 18+, Deno, Bun, browsers). File: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local" + (place ? "?tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Current date, local time and part of day for a city, country, time zone or UTC offset."""
    url = "https://exasecond.com/local" + ("?tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

## 4. Local models: Open WebUI

For Ollama and other local models used through Open WebUI: download [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py), then in Open WebUI go to Workspace, Tools, add a new tool and paste the file. Enable it for your model. Small local models are the ones that need a clock most, and the answer is short enough for any context window.

## 5. What you can ask for

### Typical response tokens

| Response | Bytes | Tokens |
|---|---:|---:|
| `/unix`: Unix timestamp | 11 | ~5 |
| `/utc`: UTC timestamp, to the second | 21 | ~14 |
| `/local?tz=Oslo`: English local-time sentence | ~70-120 | ~25-40 |
| `/now`: Default compact response | ~80 | ~35 |

Sample response text measured with two tokenizers: o200k_base and cl100k_base, on 1 November 2026. Counts vary by model, language and response details. Tool setup and client overhead are additional. These are typical sample sizes, not billing totals or guaranteed limits.

Setup context, measured separately: prompt line 26 tokens; one supplied tool definition 136-144; both MCP tool schemas 245-256. Client formatting can add more.

| Address | Answer |
|---|---|
| `/oslo`, `/new-york`, `/sweden` | One sentence in spoken order: local time, part of day, weekday and place, then the date and zone in brackets, and an upcoming clock change. Any city, region, island, country, zone or offset as the address |
| `/local?tz=Oslo` | The same sentence |
| `/beijing?lang=zh` | The same sentence in Chinese |
| `/berlin?lang=de` | The same sentence in German; also `fr`, `es`, `pt`, `ru`, `sv`, `ja` and `ko` |
| `/utc` | The ISO 8601 UTC timestamp only |
| `/unix` | Unix seconds only, also `unix` in `/now.json`. Not in the short answers, since language models read a date, not a Unix number |
| `/now` | Compact text: UTC and weekday; no secondary calendars or cosmic time |
| `/now?tz=Oslo&cutoff=2025-06` | Adds local time and how long ago a training cutoff was |
| `exasecond.com` (markdown) | The front page for agents: every big city's time, clock changes, a fact |
| `/now.md` | Everything: all calendars, world clocks |
| `/now.json` | Everything, as JSON |
| `/fact?tz=Oslo` | One "did you know?" fact about time, specific to the place when possible |
| `/zones?q=oslo` | Time zone names and their current offsets |

`tz` and the short address accept about a million names: every place with 1,000 or more people, states and regions, well-known islands (GeoNames), countries (Norway, 挪威), IANA names (Europe/Oslo), abbreviations (CET, JST) and offsets (+02:00). If it can't be read, the time is still returned, with a note. The full description is in [/llms.txt](/llms.txt) and [/openapi.json](/openapi.json).

## License

The snippets on this page and the files under `/dev/` follow the regular [MIT License](/dev/LICENSE). Keep the notice "© 2026 EggQube, exasecond.com" with them. The license covers only these developer snippets and files, not the rest of the site.

## Good to know

- Answers are generated per request using the server clock and sent with `Cache-Control: no-store`. Client caching can still affect freshness.
- Answers contain only data, never instructions to your agent.
- If you build something on it, a mention or a link to exasecond.com is highly valued.

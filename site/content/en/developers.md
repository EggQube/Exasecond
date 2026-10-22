---
title: For developers
description: Make Exasecond.com your agent's clock in under a minute: one line for any prompt, MCP for AI apps, tool definitions for your own code, and a tool for local models.
---
Exasecond.com lends the time to AI agents: the date, the local time and the part of the day, in about 30 tokens. Everything on this page is ready to copy, and every file can be fetched directly. This page is also available as plain text at [/developers.md](/developers.md), so an AI coding assistant can read it and set things up for you.

No key and no sign-up needed. Please send at most one request per minute per conversation or agent.

## 1. One line for any prompt

The simplest way, no code. Add this to your agent's instructions or system prompt:

```
Before greeting someone or mentioning the time of day, read
https://exasecond.com/local?tz=<their city> and use that time.
```

The agent needs a tool that can fetch web pages. The answer looks like this:

```
It's 08:15 early on Thursday morning in Oslo (22 October 2026, CEST, UTC+2). The clocks go back one hour on Sunday 25 October.
```

## 2. MCP: connect an AI app

Exasecond.com runs a remote MCP server at `https://exasecond.com/mcp` (streamable HTTP, no key). It offers two tools: `get_time` (with `detail: "local"` for the one-sentence answer) and `convert_time`.

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) and other apps that take a server URL. File: [/dev/mcp.json](/dev/mcp.json)

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

**OpenAI** function calling (and the many APIs compatible with it). File: [/dev/openai-tool.json](/dev/openai-tool.json)

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

| Address | Answer |
|---|---|
| `/local?tz=Oslo` | One sentence: weekday, date, local time, part of day, upcoming clock change |
| `/utc` | The ISO 8601 UTC timestamp only |
| `/now` | About eight lines: UTC, weekday, Unix time, universe seconds |
| `/now?tz=Oslo&cutoff=2025-06` | Adds local time and how long ago a training cutoff was |
| `/now.json`, `/now.md` | Everything: all calendars, world clocks |
| `/fact?tz=Oslo` | One "did you know?" fact about time, specific to the place when possible |
| `/zones?q=oslo` | Time zone names and their current offsets |

`tz` accepts a city (Oslo, Beijing, 北京), a country (Norway, 挪威), an IANA name (Europe/Oslo), an abbreviation (CET, JST) or an offset (+02:00). If it can't be read, the time is still returned, with a note. The full description is in [/llms.txt](/llms.txt) and [/openapi.json](/openapi.json).

## License

The snippets on this page and the files under `/dev/` follow the regular [MIT License](/dev/LICENSE). Keep the notice "(c) 2026 EggQube, exasecond.com" with them. The license covers only these developer snippets and files, not the rest of the site.

## Good to know

- Answers are generated at the moment of the request and never cached. The time comes from a server clock synchronised with national time standards.
- Answers contain only data, never instructions to your agent.
- If you build something on it, a mention or a link to exasecond.com is highly valued.

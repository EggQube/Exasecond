---
title: För utvecklare
description: Hjälp din AI att få tiden rätt: inte bara tidsstämpeln, utan också hur man pratar om den. Klart på under en minut.
---
Språkmodeller lever i text. De har ingen klocka, och även när de får tiden hanterar de den ofta dåligt: de önskar "trevlig kväll" vid frukosten, missar att en tidsfrist redan har gått ut och glömmer att klockorna ställs om på söndag.

Därför är Exasecond.com en klocka gjord av text. Huvudsvaret är en mening som en assistent kan föra vidare direkt till en människa: "Stockholm: Klockan är 16:02 på torsdagseftermiddagen (22 oktober 2026, CEST, UTC+2). Söndag den 25 oktober ställs klockorna tillbaka en timme." Ungefär 40 tokens. Förhoppningen är att AI, när den läser tiden så som människor säger den, också säger den så. Den gammaldags digitala klockan finns kvar här, i alla format: UTC-tidsstämpeln, Unix-tid och fullständig JSON (se avsnitt 5).

Allt på den här sidan kan kopieras direkt, och varje fil kan hämtas direkt. Sidan finns också som ren text på [/sv/developers.md](/sv/developers.md), så att en AI-assistent för programmering kan läsa den och ställa in allt åt dig.

Ingen nyckel och ingen registrering behövs. Skicka högst en förfrågan per minut och samtal eller agent.

Lägg till `?lang=sv` (eller `&lang=sv`) i adressen för svar på svenska; annars kommer svaret på engelska.

## 1. En rad för vilken prompt som helst

Det enklaste sättet, utan kod. Lägg till det här i agentens instruktioner eller systemprompt:

```
Innan du hälsar på någon eller nämner tiden på dygnet, läs
https://exasecond.com/<personens stad>?lang=sv och använd den tiden.
```

Agenten behöver ett verktyg som kan hämta webbsidor. Svaret ser ut så här:

```
Oslo: Klockan är 08:15 på torsdagsförmiddagen (22 oktober 2026, CEST, UTC+2). Söndag den 25 oktober ställs klockorna tillbaka en timme.
```

## 2. MCP: koppla in en AI-app

Exasecond.com kör en fjärr-MCP-server på `https://exasecond.com/mcp` (streamable HTTP, ingen nyckel). Den erbjuder två verktyg: `get_time` (med `detail: "local"` för svaret på en mening) och `convert_time`.

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) och andra appar som tar en serveradress (till exempel Trae, Cherry Studio). Fil: [/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop och claude.ai**: Inställningar, Kopplingar, Lägg till anpassad koppling, och ange `https://exasecond.com/mcp`.

## 3. Verktygsdefinitioner för din egen agent

Ge modellen ett verktyg `get_local_time`, och när den anropar det, hämta `https://exasecond.com/local?tz=<plats>&lang=sv` och returnera texten.

**OpenAI** function calling (och de många API:er som är kompatibla med det, till exempel DeepSeek, Qwen, Kimi och GLM). Fil: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use (Claude API). Fil: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, bara standardbiblioteket. Fil: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=sv" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, utan beroenden (Node 18+, Deno, Bun, webbläsare). Fil: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=sv" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Aktuellt datum, lokal tid och tid på dygnet för en stad, ett land, en tidszon eller en UTC-avvikelse."""
    url = "https://exasecond.com/local?lang=sv" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

De nedladdningsbara filerna svarar på engelska; med `lang=sv`, som i koden ovan, kommer svaret på svenska.

## 4. Lokala modeller: Open WebUI

För Ollama och andra lokala modeller i Open WebUI: ladda ner [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py), gå sedan i Open WebUI till Workspace, Tools, skapa ett nytt verktyg och klistra in filen. Aktivera det för din modell. Det är just små lokala modeller som behöver en klocka mest, och svaret är kort nog för vilket kontextfönster som helst.

## 5. Vad du kan fråga efter

| Adress | Svar | Tokens |
|---|---|---|
| `/stockholm?lang=sv`, `/new-york?lang=sv` | En mening i talordning: plats, lokal tid, veckodag och tid på dygnet, inom parentes datum och zon, plus en kommande tidsomställning. Vilken stad, region, ö, vilket land, vilken zon eller avvikelse som helst som adress | ~35-45 |
| `/oslo`, `/new-york`, `/sweden` | Samma mening på engelska | ~30 |
| `/local?tz=Oslo&lang=sv` | Samma mening | ~35-45 |
| `/beijing?lang=zh`, `/berlin?lang=de` | Samma mening på kinesiska, tyska; även `fr`, `es`, `pt`, `ru`, `ja` och `ko` | ~25-60 |
| `/utc` | Bara ISO 8601-tidsstämpeln i UTC | ~5 |
| `/now` | Ungefär åtta rader: UTC, veckodag, Unix-tid, universumsekunder | ~80 |
| `/now?tz=Oslo&cutoff=2025-06` | Lägger till lokal tid och hur länge sedan ett träningsslutdatum var | ~150 |
| `exasecond.com` (markdown) | Startsidan för agenter: tiden i alla stora städer, tidsomställningar, ett faktum | ~600 |
| `/now.md` | Allt: alla kalendrar, världsklockor | ~500 |
| `/now.json` | Allt, som JSON | ~1 400 |
| `/fact?tz=Stockholm&lang=sv` | Ett "Visste du?"-faktum om tid, gärna om platsen | ~40 |
| `/zones?q=oslo` | Tidszonernas namn och deras aktuella avvikelse | ~30, beroende på sökningen |

`tz` och den korta adressen tar emot ungefär en miljon namn: varje ort med minst 1 000 invånare, delstater och regioner, kända öar (GeoNames), länder (Norway, Norge), IANA-namn (Europe/Stockholm), förkortningar (CET, JST) och avvikelser (+02:00). Om något inte kan läsas kommer tiden ändå, med en kommentar. Tokenantalen är uppskattningar; svenska kräver lite fler tokens än engelska, beroende på modellens tokenizer. Hela beskrivningen finns i [/llms.txt](/llms.txt) och [/openapi.json](/openapi.json).

## Licens

Kodsnuttarna på den här sidan och filerna under `/dev/` följer den vanliga [MIT-licensen](/dev/LICENSE). Behåll texten "(c) 2026 EggQube, exasecond.com". Licensen gäller bara dessa utvecklarsnuttar och filer, inte resten av sidan.

## Bra att veta

- Svaren skapas i ögonblicket för förfrågan och cachas aldrig. Tiden kommer från en serverklocka som är synkroniserad med nationella tidsnormaler.
- Svaren innehåller bara data, aldrig instruktioner till din agent.
- Om du bygger något på det här uppskattas ett omnämnande eller en länk till exasecond.com mycket.

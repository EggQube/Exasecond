---
title: För utvecklare
description: Aktuell tid för AI. På sekunden. Minimalt med token. HTTP / MCP.
---

## Snabbstart

Aktuell tid för AI. **På sekunden. Minimalt med token.**

Vi gör jobbet så att din AI använder färre token för att hämta tiden den behöver. Hämta en kort UTC-tidsstämpel via HTTP eller anslut din agent eller app via MCP. Ingen nyckel eller registrering behövs.

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): Bara tidsstämpeln (ISO 8601, UTC).
- [/local?tz=Oslo&lang=sv](/local?tz=Oslo&lang=sv): Lokal tid i en mening.
- `/mcp`: Anslut via MCP (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

UTC-tidsstämplar innehåller sekunder. Tiden kommer från serverns klocka; nätverks- och klientfördröjningar påverkar när agenten tar emot den.

Språkmodeller lever i text. De har ingen klocka, och även när de får tiden hanterar de den ofta dåligt: de önskar "trevlig kväll" vid frukosten, missar att en tidsfrist redan har gått ut och glömmer att klockorna ställs om på söndag.

Därför är Exasecond.com en klocka gjord av text. Huvudsvaret är en mening som en assistent kan föra vidare direkt till en människa: "Stockholm: Klockan är 16:02 på torsdagseftermiddagen (22 oktober 2026, CEST, UTC+2). Söndag den 25 oktober ställs klockorna tillbaka en timme." Ungefär 40 tokens. Förhoppningen är att AI, när den läser tiden så som människor säger den, också säger den så. Den gammaldags digitala klockan finns kvar här, i alla format: UTC-tidsstämpeln, Unix-tid och fullständig JSON (se avsnitt 5).

Allt på den här sidan kan kopieras direkt, och varje fil kan hämtas direkt. Sidan finns också som ren text på [/sv/developers.md](/sv/developers.md), så att en AI-assistent för programmering kan läsa den och ställa in allt åt dig.

Ingen nyckel och ingen registrering behövs. Skicka högst en förfrågan per minut och samtal eller agent.

Lägg till `?lang=sv` (eller `&lang=sv`) i adressen för svar på svenska; annars kommer svaret på engelska.

## Varför ansluta klockan som verktyg

En MCP- eller HTTP-anslutning hämtar tiden direkt vid varje anrop. En AI-app kan återanvända en sparad webbsida trots `Cache-Control: no-store`. Cache och behörighetsfrågor beror på appen. En inställning av utvecklaren gör klockan tillgänglig för appens användare.

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

För en översatt lokal tidsmening, skicka dessa argument till `get_time`:

```json
{"tz":"Oslo","detail":"local","lang":"sv"}
```

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

### Typiskt antal token per svar

| Svar | Byte | Token |
|---|---:|---:|
| `/unix`: Unix-tidsstämpel | 11 | ~5 |
| `/utc`: UTC-tidsstämpel, på sekunden | 21 | ~14 |
| `/local?tz=Oslo`: Lokal tid i en engelsk mening | ~70-120 | ~25-40 |
| `/now`: Kort standardsvar | ~80 | ~35 |

Svarstexter uppmätta den 1 november 2026 med två tokeniserare: o200k_base och cl100k_base. Antalet varierar med modell, språk och innehåll. Verktygsinställningar och klientens inramning tillkommer. Typiska exempelstorlekar, inte faktureringstotaler eller garanterade gränser.

Inställningskontext mätt separat: promptrad 26 token; en tillhandahållen verktygsdefinition 136-144; båda MCP-schemana 245-256. Klientens formatering kan lägga till fler.

| Adress | Svar |
|---|---|
| `/stockholm?lang=sv`, `/new-york?lang=sv` | En mening i talordning: plats, lokal tid, veckodag och tid på dygnet, inom parentes datum och zon, plus en kommande tidsomställning. Vilken stad, region, ö, vilket land, vilken zon eller avvikelse som helst som adress |
| `/oslo`, `/new-york`, `/sweden` | Samma mening på engelska |
| `/local?tz=Oslo&lang=sv` | Samma mening |
| `/local.json?tz=Oslo&lang=sv` | Samma mening som JSON, med avvikelse, zonförkortning, sommartid (`dst`) och nästa tidsomställning |
| `/beijing?lang=zh`, `/berlin?lang=de` | Samma mening på kinesiska, tyska; även `fr`, `es`, `pt`, `ru`, `ja` och `ko` |
| `/utc` | Bara ISO 8601-tidsstämpeln i UTC |
| `/unix` | Bara Unix-sekunderna, även som `unix` i `/now.json`. Inte med i de korta svaren, eftersom språkmodeller läser ett datum, inte ett Unix-tal |
| `/now` | Kort text: UTC och veckodag; inga andra kalendrar eller kosmisk tid |
| `/now?tz=Oslo&cutoff=2025-06` | Lägger till lokal tid och hur länge sedan ett träningsslutdatum var |
| `exasecond.com` (markdown) | Startsidan för agenter: tiden i alla stora städer, tidsomställningar, ett faktum |
| `/now.md` | Allt: alla kalendrar, världsklockor |
| `/now.json` | Allt, som JSON |
| `/fact?tz=Stockholm&lang=sv` | Ett "Visste du?"-faktum om tid, gärna om platsen |
| `/zones?q=oslo` | Tidszonernas namn och deras aktuella avvikelse |

`tz` och den korta adressen tar emot ungefär en miljon namn: varje ort med minst 1 000 invånare, delstater och regioner, kända öar (GeoNames), länder (Norway, Norge), IANA-namn (Europe/Stockholm), förkortningar (CET, JST) och avvikelser (+02:00). Om något inte kan läsas kommer tiden ändå, med en kommentar. Hela beskrivningen finns i [/llms.txt](/llms.txt) och [/openapi.json](/openapi.json).

## Licens

Kodsnuttarna på den här sidan och filerna under `/dev/` följer den vanliga [MIT-licensen](/dev/LICENSE). Behåll texten "© 2026 EggQube, exasecond.com". Licensen gäller bara dessa utvecklarsnuttar och filer, inte resten av sidan.

## Bra att veta

- Svaren skapas vid varje förfrågan med serverklockan och skickas med `Cache-Control: no-store`. Klientens cache kan ändå påverka aktualiteten.
- Svaren innehåller bara data, aldrig instruktioner till din agent.
- Om du bygger något på det här uppskattas ett omnämnande eller en länk till exasecond.com mycket.

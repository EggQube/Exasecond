---
title: Für Entwickler
description: Damit Ihre KI die Zeit richtig erfasst: nicht nur den Zeitstempel, sondern auch, wie man über Zeit spricht. In unter einer Minute eingerichtet.
---
Sprachmodelle leben in Text. Sie haben keine Uhr, und selbst wenn man ihnen die Zeit gibt, gehen sie oft schlecht damit um: Sie wünschen beim Frühstück "einen schönen Abend", übersehen, dass eine Frist schon abgelaufen ist, oder vergessen, dass am Sonntag die Uhren umgestellt werden.

Deshalb ist Exasecond.com eine Uhr aus Text. Ihre Hauptantwort ist ein Satz, den ein Assistent direkt an einen Menschen weitergeben kann: "Berlin: Es ist 16:02 Uhr am Donnerstagnachmittag (22. Oktober 2026, CEST, UTC+2). Am Sonntag, 25. Oktober, werden die Uhren um eine Stunde zurückgestellt." Etwa 40 Tokens. Die Hoffnung ist, dass KI die Zeit so sagt, wie Menschen sie sagen, wenn sie sie so liest. Die altmodische Digitaluhr gibt es hier weiterhin, in allen Formaten: der UTC-Zeitstempel, Unix-Zeit und vollständiges JSON (siehe Abschnitt 5).

Alles auf dieser Seite lässt sich direkt kopieren, und jede Datei lässt sich direkt abrufen. Diese Seite gibt es auch als reinen Text unter [/de/developers.md](/de/developers.md), sodass ein KI-Programmierassistent sie lesen und alles für Sie einrichten kann.

Kein Schlüssel und keine Anmeldung nötig. Bitte höchstens eine Anfrage pro Minute und Gespräch oder Agent.

Mit `?lang=de` (oder `&lang=de`) an der Adresse kommt die Antwort auf Deutsch, ohne auf Englisch.

## 1. Eine Zeile für jeden Prompt

Der einfachste Weg, ganz ohne Code. Fügen Sie dies den Anweisungen oder dem Systemprompt Ihres Agenten hinzu:

```
Bevor du jemanden begrüßt oder die Tageszeit erwähnst, lies
https://exasecond.com/<seine Stadt>?lang=de und verwende diese Zeit.
```

Der Agent braucht ein Werkzeug, das Webseiten abrufen kann. Die Antwort sieht so aus:

```
Oslo: Es ist 08:15 Uhr am frühen Donnerstagmorgen (22. Oktober 2026, CEST, UTC+2). Am Sonntag, 25. Oktober, werden die Uhren um eine Stunde zurückgestellt.
```

## 2. MCP: eine KI-App verbinden

Exasecond.com betreibt einen entfernten MCP-Server unter `https://exasecond.com/mcp` (streamable HTTP, kein Schlüssel). Er bietet zwei Werkzeuge: `get_time` (mit `detail: "local"` für die Antwort in einem Satz) und `convert_time`.

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) und andere Apps, die eine Server-Adresse annehmen (zum Beispiel Trae, Cherry Studio). Datei: [/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop und claude.ai**: Einstellungen, Konnektoren, benutzerdefinierten Konnektor hinzufügen, und `https://exasecond.com/mcp` eingeben.

## 3. Werkzeugdefinitionen für Ihren eigenen Agenten

Geben Sie Ihrem Modell ein Werkzeug `get_local_time`, und wenn es aufgerufen wird, rufen Sie `https://exasecond.com/local?tz=<Ort>&lang=de` ab und geben den Text zurück.

**OpenAI** Function Calling (und die vielen damit kompatiblen APIs, etwa DeepSeek, Qwen, Kimi und GLM). Datei: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** Tool Use (Claude API). Datei: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, nur Standardbibliothek. Datei: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=de" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, ohne Abhängigkeiten (Node 18+, Deno, Bun, Browser). Datei: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=de" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Aktuelles Datum, Ortszeit und Tageszeit für eine Stadt, ein Land, eine Zeitzone oder eine UTC-Abweichung."""
    url = "https://exasecond.com/local?lang=de" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

Die herunterladbaren Dateien antworten auf Englisch; mit `lang=de` wie im Code oben kommt die Antwort auf Deutsch.

## 4. Lokale Modelle: Open WebUI

Für Ollama und andere lokale Modelle in Open WebUI: Laden Sie [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py) herunter, gehen Sie in Open WebUI zu Workspace, Tools, legen Sie ein neues Werkzeug an und fügen Sie die Datei ein. Aktivieren Sie es für Ihr Modell. Gerade kleine lokale Modelle brauchen eine Uhr am dringendsten, und die Antwort ist kurz genug für jedes Kontextfenster.

## 5. Was Sie abfragen können

| Adresse | Antwort | Tokens |
|---|---|---|
| `/berlin?lang=de`, `/new-york?lang=de` | Ein Satz in Sprechreihenfolge: Ort, Ortszeit, Tageszeit und Wochentag, in Klammern Datum und Zone, dazu eine anstehende Zeitumstellung. Jede Stadt, Region, Insel, jedes Land, jede Zone oder Abweichung als Adresse | ~35-45 |
| `/oslo`, `/new-york`, `/sweden` | Derselbe Satz auf Englisch | ~30 |
| `/local?tz=Oslo&lang=de` | Derselbe Satz | ~35-45 |
| `/beijing?lang=zh`, `/paris?lang=fr` | Derselbe Satz auf Chinesisch, Französisch; auch `es`, `pt` und `ru` | ~25-45 |
| `/utc` | Nur der ISO-8601-Zeitstempel in UTC | ~5 |
| `/now` | Etwa acht Zeilen: UTC, Wochentag, Unix-Zeit, Universumssekunden | ~80 |
| `/now?tz=Oslo&cutoff=2025-06` | Dazu die Ortszeit und wie lange ein Trainingsstichtag zurückliegt | ~150 |
| `exasecond.com` (markdown) | Die Startseite für Agenten: die Zeit aller großen Städte, Zeitumstellungen, eine Tatsache | ~440 |
| `/now.md` | Alles: alle Kalender, Weltuhren | ~500 |
| `/now.json` | Alles, als JSON | ~1.400 |
| `/fact?tz=Berlin&lang=de` | Eine "Schon gewusst?"-Tatsache über die Zeit, wenn möglich passend zum Ort | ~40 |
| `/zones?q=oslo` | Zeitzonennamen und ihre aktuelle Abweichung | ~30, je nach Suche |

`tz` und die kurze Adresse nehmen etwa eine Million Namen an: jeden Ort mit mindestens 1.000 Einwohnern, Bundesländer und Regionen, bekannte Inseln (GeoNames), Länder (Norway, Norwegen), IANA-Namen (Europe/Berlin), Abkürzungen (CET, JST) und Abweichungen (+02:00). Wenn etwas nicht gelesen werden kann, kommt die Zeit trotzdem, mit einem Hinweis. Die Tokenzahlen sind Schätzungen; Deutsch braucht etwas mehr Tokens als Englisch, je nach Tokenizer des Modells. Die vollständige Beschreibung steht in [/llms.txt](/llms.txt) und [/openapi.json](/openapi.json).

## Lizenz

Die Codeschnipsel auf dieser Seite und die Dateien unter `/dev/` stehen unter der regulären [MIT-Lizenz](/dev/LICENSE). Behalten Sie den Hinweis "(c) 2026 EggQube, exasecond.com" bei. Die Lizenz gilt nur für diese Entwicklerschnipsel und -dateien, nicht für den Rest der Seite.

## Gut zu wissen

- Antworten entstehen im Moment der Anfrage und werden nie zwischengespeichert. Die Zeit kommt von einer Serveruhr, die mit nationalen Zeitstandards synchronisiert ist.
- Antworten enthalten nur Daten, nie Anweisungen an Ihren Agenten.
- Wenn Sie etwas darauf aufbauen, freuen wir uns sehr über eine Erwähnung oder einen Link zu exasecond.com.

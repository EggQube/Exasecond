---
title: Para desarrolladores
description: La hora actual para la IA. Al segundo. Mínimo consumo de tokens. HTTP / MCP.
---

## Inicio rápido

La hora actual para la IA. **Al segundo. Mínimo consumo de tokens.**

Hacemos el trabajo para que tu IA gaste menos tokens en obtener la hora que necesita. Obtén una breve marca de tiempo UTC por HTTP o conecta tu agente o aplicación mediante MCP. Sin clave ni registro.

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): Solo la marca de tiempo (ISO 8601, UTC).
- [/local?tz=Oslo&lang=es](/local?tz=Oslo&lang=es): La hora local en una frase.
- `/mcp`: Conectar mediante MCP (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

Las marcas de tiempo UTC incluyen segundos. La hora procede del reloj del servidor; los retrasos de red y del cliente afectan a cuándo la recibe el agente.

Los modelos de lenguaje viven en el texto. No tienen reloj, y aunque se les dé la hora, a menudo la usan mal: dan las "buenas noches" a la hora del desayuno, no ven que un plazo ya ha vencido, olvidan que el domingo cambia la hora.

Por eso Exasecond.com es un reloj hecho de texto. Su respuesta principal es una frase que un asistente puede pasar tal cual a una persona: "Madrid: es jueves por la tarde, las 16:02 (22 de octubre de 2026, CEST, UTC+2). El domingo 25 de octubre los relojes se atrasan una hora." Unos 40 tokens. La esperanza es que, si la IA lee la hora como la dice la gente, también la diga así. El reloj digital de toda la vida sigue aquí, en todos los formatos: la marca de tiempo UTC, el tiempo Unix y el JSON completo (ver la sección 5).

Todo en esta página se puede copiar directamente, y cada archivo se puede descargar directamente. Esta página también está en texto plano en [/es/developers.md](/es/developers.md), para que un asistente de programación con IA pueda leerla y configurarlo todo por ti.

Sin clave ni registro. Por favor, envía como máximo una petición por minuto y por conversación o agente.

Añade `?lang=es` (o `&lang=es`) a la dirección para recibir la respuesta en español; sin eso, llega en inglés.

## Por qué conectar el reloj como herramienta

Una conexión MCP o HTTP solicita la hora directamente en cada llamada. Una aplicación de IA puede reutilizar una página guardada pese a `Cache-Control: no-store`. La caché y los permisos dependen de la aplicación. Una configuración del desarrollador pone el reloj a disposición de los usuarios de su aplicación.

## 1. Una línea para cualquier prompt

La forma más sencilla, sin código. Añade esto a las instrucciones o al prompt de sistema de tu agente:

```
Antes de saludar a alguien o mencionar el momento del día, lee
https://exasecond.com/<su ciudad>?lang=es y usa esa hora.
```

El agente necesita una herramienta que pueda leer páginas web. La respuesta es así:

```
Oslo: es jueves, temprano por la mañana, las 08:15 (22 de octubre de 2026, CEST, UTC+2). El domingo 25 de octubre los relojes se atrasan una hora.
```

## 2. MCP: conectar una aplicación de IA

Exasecond.com tiene un servidor MCP remoto en `https://exasecond.com/mcp` (streamable HTTP, sin clave). Ofrece dos herramientas: `get_time` (con `detail: "local"` para la respuesta de una frase) y `convert_time`.

Para una frase local traducida, pasa estos argumentos a `get_time`:

```json
{"tz":"Oslo","detail":"local","lang":"es"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) y otras aplicaciones que aceptan una dirección de servidor (por ejemplo Trae, Cherry Studio). Archivo: [/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop y claude.ai**: Configuración, Conectores, Añadir conector personalizado, e introduce `https://exasecond.com/mcp`.

## 3. Definiciones de herramientas para tu propio agente

Dale a tu modelo una herramienta `get_local_time` y, cuando la llame, lee `https://exasecond.com/local?tz=<lugar>&lang=es` y devuelve el texto.

**OpenAI** function calling (y las muchas API compatibles, como DeepSeek, Qwen, Kimi y GLM). Archivo: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use (API de Claude). Archivo: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, solo biblioteca estándar. Archivo: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=es" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, sin dependencias (Node 18+, Deno, Bun, navegadores). Archivo: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=es" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Fecha actual, hora local y momento del día para una ciudad, un país, una zona horaria o un desfase UTC."""
    url = "https://exasecond.com/local?lang=es" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

Los archivos descargables responden en inglés; con `lang=es`, como en el código de arriba, la respuesta llega en español.

## 4. Modelos locales: Open WebUI

Para Ollama y otros modelos locales usados con Open WebUI: descarga [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py), luego en Open WebUI ve a Workspace, Tools, crea una herramienta nueva y pega el archivo. Actívala para tu modelo. Los modelos locales pequeños son los que más necesitan un reloj, y la respuesta es lo bastante corta para cualquier ventana de contexto.

## 5. Qué puedes pedir

### Tokens típicos por respuesta

| Respuesta | Bytes | Tokens |
|---|---:|---:|
| `/unix`: Marca de tiempo Unix | 11 | ~5 |
| `/utc`: Marca de tiempo UTC, al segundo | 21 | ~14 |
| `/local?tz=Oslo`: Frase en inglés con la hora local | ~70-120 | ~25-40 |
| `/now`: Respuesta compacta predeterminada | ~200 | ~70 |

Textos de respuesta medidos el 23 de octubre de 2026 con dos tokenizadores: o200k_base y cl100k_base. La cantidad varía según el modelo, el idioma y el contenido. La configuración de herramientas y el formato del cliente son adicionales. Son tamaños de muestras típicas, no totales de facturación ni límites garantizados.

Contexto de configuración medido por separado: línea de prompt 26 tokens; una definición de herramienta proporcionada 136-144; ambos esquemas MCP 247-258. El formato del cliente puede añadir más.

| Dirección | Respuesta |
|---|---|
| `/madrid?lang=es`, `/new-york?lang=es` | Una frase en orden hablado: lugar, día y momento del día, hora local, y entre paréntesis la fecha y la zona, más un cambio de hora próximo. Cualquier ciudad, región, isla, país, zona o desfase como dirección |
| `/oslo`, `/new-york`, `/sweden` | La misma frase en inglés |
| `/local?tz=Oslo&lang=es` | La misma frase |
| `/beijing?lang=zh`, `/berlin?lang=de` | La misma frase en chino, en alemán; también `fr`, `pt`, `ru`, `sv`, `ja` y `ko` |
| `/utc` | Solo la marca de tiempo UTC ISO 8601 |
| `/now` | Texto compacto: UTC, día de la semana y tiempo Unix; sin otros calendarios ni tiempo cósmico |
| `/now?tz=Oslo&cutoff=2025-06` | Añade la hora local y cuánto hace de una fecha de corte de entrenamiento |
| `exasecond.com` (markdown) | La página de inicio para agentes: la hora de todas las grandes ciudades, los cambios de hora, un dato |
| `/now.md` | Todo: todos los calendarios, relojes del mundo |
| `/now.json` | Todo, en JSON |
| `/fact?tz=Madrid&lang=es` | Un dato "¿Sabías que...?" sobre el tiempo, del lugar cuando es posible |
| `/zones?q=oslo` | Nombres de zonas horarias y su desfase actual |

`tz` y la dirección corta aceptan alrededor de un millón de nombres: cada lugar de 1.000 habitantes o más, estados y regiones, islas conocidas (GeoNames), países (Norway, Noruega), nombres IANA (Europe/Madrid), abreviaturas (CET, JST) y desfases (+02:00). Si algo no se puede leer, la hora se devuelve igualmente, con una nota. La descripción completa está en [/llms.txt](/llms.txt) y [/openapi.json](/openapi.json).

## Licencia

Los fragmentos de código de esta página y los archivos bajo `/dev/` siguen la [licencia MIT](/dev/LICENSE) habitual. Conserva el aviso "© 2026 EggQube, exasecond.com". La licencia cubre solo estos fragmentos y archivos para desarrolladores, no el resto del sitio.

## Bueno saber

- Las respuestas se generan en cada petición con el reloj del servidor y se envían con `Cache-Control: no-store`. La caché del cliente aún puede afectar su actualidad.
- Las respuestas solo contienen datos, nunca instrucciones para tu agente.
- Si construyes algo con esto, una mención o un enlace a exasecond.com se agradece mucho.

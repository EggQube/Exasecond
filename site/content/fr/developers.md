---
title: Pour les développeurs
description: L’heure actuelle pour l’IA. À la seconde. Un minimum de tokens. HTTP / MCP.
---

## Démarrage rapide

L’heure actuelle pour l’IA. **À la seconde. Un minimum de tokens.**

Nous faisons le travail pour que votre IA utilise moins de tokens pour obtenir l’heure dont elle a besoin. Récupérez un court horodatage UTC par HTTP ou connectez votre agent ou application via MCP. Sans clé ni inscription.

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): L’horodatage seul (ISO 8601, UTC).
- [/local?tz=Oslo&lang=fr](/local?tz=Oslo&lang=fr): L’heure locale en une phrase.
- `/mcp`: Connexion via MCP (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

Les horodatages UTC incluent les secondes. L’heure vient de l’horloge du serveur ; les délais du réseau et du client influencent sa réception par l’agent.

Les modèles de langage vivent dans le texte. Ils n'ont pas d'horloge, et même quand on leur donne l'heure, ils s'en servent souvent mal : ils souhaitent « bonne soirée » au petit-déjeuner, ne voient pas qu'une échéance est passée, oublient que l'on change d'heure dimanche.

C'est pourquoi Exasecond.com est une horloge faite de texte. Sa réponse principale est une phrase qu'un assistant peut transmettre telle quelle à une personne : « Paris : il est 16 h 02, jeudi après-midi (22 octobre 2026, CEST, UTC+2). Le dimanche 25 octobre, les horloges reculent d'une heure. » Environ 40 tokens. L'espoir est qu'en lisant l'heure comme les gens la disent, l'IA la dira de la même façon. L'horloge numérique à l'ancienne est toujours là, dans tous les formats : l'horodatage UTC, le temps Unix et le JSON complet (voir la section 5).

Tout sur cette page peut être copié directement, et chaque fichier peut être récupéré directement. Cette page existe aussi en texte brut à [/fr/developers.md](/fr/developers.md), pour qu'un assistant de programmation IA puisse la lire et tout installer pour vous.

Pas de clé ni d'inscription. Merci de ne pas envoyer plus d'une requête par minute et par conversation ou agent.

Ajoutez `?lang=fr` (ou `&lang=fr`) à l'adresse pour une réponse en français ; sans cela, elle est en anglais.

## Pourquoi connecter cette horloge comme outil

Une connexion MCP ou HTTP demande directement l’heure à chaque appel. Une application d’IA peut réutiliser une page enregistrée malgré `Cache-Control: no-store`. Le cache et les autorisations dépendent de l’application. Une configuration par le développeur rend l’horloge accessible aux utilisateurs de son application.

## 1. Une ligne pour n'importe quel prompt

Le plus simple, sans code. Ajoutez ceci aux instructions ou au prompt système de votre agent :

```
Avant de saluer quelqu'un ou de mentionner le moment de la journée, lis
https://exasecond.com/<sa ville>?lang=fr et utilise cette heure.
```

L'agent a besoin d'un outil capable de lire des pages web. La réponse ressemble à ceci :

```
Oslo : il est 8 h 15, jeudi, tôt le matin (22 octobre 2026, CEST, UTC+2). Le dimanche 25 octobre, les horloges reculent d'une heure.
```

## 2. MCP : connecter une application IA

Exasecond.com fait tourner un serveur MCP distant à `https://exasecond.com/mcp` (streamable HTTP, sans clé). Il propose deux outils : `get_time` (avec `detail: "local"` pour la réponse en une phrase) et `convert_time`.

Pour une phrase locale traduite, passez ces arguments à `get_time` :

```json
{"tz":"Oslo","detail":"local","lang":"fr"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) et les autres applications qui acceptent une adresse de serveur (par exemple Trae, Cherry Studio). Fichier : [/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop et claude.ai** : Paramètres, Connecteurs, Ajouter un connecteur personnalisé, puis saisissez `https://exasecond.com/mcp`.

## 3. Définitions d'outils pour votre propre agent

Donnez à votre modèle un outil `get_local_time` et, quand il l'appelle, lisez `https://exasecond.com/local?tz=<lieu>&lang=fr` et renvoyez le texte.

**OpenAI** function calling (et les nombreuses API compatibles, comme DeepSeek, Qwen, Kimi et GLM). Fichier : [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use (API Claude). Fichier : [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, bibliothèque standard uniquement. Fichier : [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=fr" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, sans dépendances (Node 18+, Deno, Bun, navigateurs). Fichier : [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=fr" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Date actuelle, heure locale et moment de la journée pour une ville, un pays, un fuseau horaire ou un décalage UTC."""
    url = "https://exasecond.com/local?lang=fr" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

Les fichiers téléchargeables répondent en anglais ; avec `lang=fr`, comme dans le code ci-dessus, la réponse est en français.

## 4. Modèles locaux : Open WebUI

Pour Ollama et les autres modèles locaux utilisés via Open WebUI : téléchargez [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py), puis dans Open WebUI allez dans Workspace, Tools, créez un nouvel outil et collez le fichier. Activez-le pour votre modèle. Ce sont justement les petits modèles locaux qui ont le plus besoin d'une horloge, et la réponse est assez courte pour n'importe quelle fenêtre de contexte.

## 5. Ce que vous pouvez demander

### Tokens typiques par réponse

| Réponse | Octets | Tokens |
|---|---:|---:|
| `/unix`: Horodatage Unix | 11 | ~5 |
| `/utc`: Horodatage UTC, à la seconde | 21 | ~14 |
| `/local?tz=Oslo`: Phrase en anglais donnant l’heure locale | ~70-120 | ~25-40 |
| `/now`: Réponse compacte par défaut | ~200 | ~70 |

Textes de réponse mesurés le 23 octobre 2026 avec deux tokenizers : o200k_base et cl100k_base. Le nombre varie selon le modèle, la langue et le contenu. La configuration des outils et l’enveloppe du client s’ajoutent. Ce sont des tailles d’exemples typiques, pas des totaux facturés ni des limites garanties.

Contexte de configuration mesuré séparément : ligne de prompt 26 tokens ; une définition d’outil fournie 136-144 ; les deux schémas MCP 247-258. Le format du client peut en ajouter.

| Adresse | Réponse |
|---|---|
| `/paris?lang=fr`, `/new-york?lang=fr` | Une phrase dans l'ordre parlé : lieu, heure locale, jour et moment de la journée, puis la date et le fuseau entre parenthèses, et un changement d'heure à venir. N'importe quelle ville, région, île, pays, fuseau ou décalage comme adresse |
| `/oslo`, `/new-york`, `/sweden` | La même phrase en anglais |
| `/local?tz=Oslo&lang=fr` | La même phrase |
| `/beijing?lang=zh`, `/berlin?lang=de` | La même phrase en chinois, en allemand ; aussi `es`, `pt`, `ru`, `sv`, `ja` et `ko` |
| `/utc` | Seulement l'horodatage UTC ISO 8601 |
| `/now` | Texte compact : UTC, jour de la semaine et temps Unix ; sans calendriers supplémentaires ni temps cosmique |
| `/now?tz=Oslo&cutoff=2025-06` | Ajoute l'heure locale et l'ancienneté d'une date limite d'entraînement |
| `exasecond.com` (markdown) | La page d'accueil pour les agents : l'heure de toutes les grandes villes, les changements d'heure, un fait |
| `/now.md` | Tout : tous les calendriers, les horloges du monde |
| `/now.json` | Tout, en JSON |
| `/fact?tz=Paris&lang=fr` | Un fait « Le saviez-vous ? » sur le temps, propre au lieu quand c'est possible |
| `/zones?q=oslo` | Les noms de fuseaux et leur décalage actuel |

`tz` et l'adresse courte acceptent environ un million de noms : tous les lieux de 1 000 habitants ou plus, les États et régions, les îles connues (GeoNames), les pays (Norway, Norvège), les noms IANA (Europe/Paris), les abréviations (CET, JST) et les décalages (+02:00). Si quelque chose ne peut pas être lu, l'heure est quand même renvoyée, avec une note. La description complète se trouve dans [/llms.txt](/llms.txt) et [/openapi.json](/openapi.json).

## Licence

Les extraits de code de cette page et les fichiers sous `/dev/` suivent la [licence MIT](/dev/LICENSE) habituelle. Conservez la mention « (c) 2026 EggQube, exasecond.com ». La licence ne couvre que ces extraits et fichiers pour développeurs, pas le reste du site.

## Bon à savoir

- Les réponses sont générées à chaque requête avec l’horloge du serveur et envoyées avec `Cache-Control: no-store`. Le cache du client peut néanmoins affecter leur fraîcheur.
- Les réponses ne contiennent que des données, jamais d'instructions pour votre agent.
- Si vous construisez quelque chose dessus, une mention ou un lien vers exasecond.com sera très apprécié.

---
title: 개발자를 위해
description: AI가 시간을 제대로 다루도록 도와주세요. 타임스탬프만이 아니라 시간에 대해 말하는 방식까지. 1분 안에 설정할 수 있습니다.
---
언어 모델은 글 속에 삽니다. 시계가 없고, 시각을 받아도 잘 다루지 못할 때가 많습니다. 아침 식사 시간에 "좋은 저녁입니다"라고 하거나, 마감이 이미 지난 것을 놓치거나, 일요일에 시계가 바뀐다는 것을 잊어버립니다.

그래서 Exasecond.com은 글자로 만든 시계입니다. 주된 답은 비서가 그대로 사람에게 전할 수 있는 한 문장입니다. "서울: 목요일 오후 16:02 (2026년 10월 22일, KST, UTC+9)." 약 30~40토큰입니다. 사람이 말하는 방식대로 시각을 읽으면 AI도 그렇게 말하게 되리라는 바람을 담았습니다. 예전 방식의 디지털 시계도 모든 형식으로 여기에 남아 있습니다. UTC 타임스탬프, Unix 시간, 전체 JSON(5절 참고).

이 페이지의 모든 내용은 바로 복사할 수 있고, 모든 파일은 바로 받을 수 있습니다. 이 페이지는 [/ko/developers.md](/ko/developers.md)에서 일반 텍스트로도 볼 수 있어서, AI 코딩 도우미가 읽고 대신 설정하게 할 수 있습니다.

키도 가입도 필요 없습니다. 요청은 대화나 에이전트마다 1분에 한 번 이하로 보내 주세요.

주소에 `?lang=ko`(또는 `&lang=ko`)를 붙이면 한국어로 답합니다. 붙이지 않으면 영어입니다.

## 1. 어떤 프롬프트에도 넣는 한 줄

가장 간단한 방법이며 코드가 필요 없습니다. 에이전트의 지시문이나 시스템 프롬프트에 다음을 추가하세요.

```
누군가에게 인사하거나 하루 중 때를 언급하기 전에
https://exasecond.com/<상대의 도시>?lang=ko 를 읽고 그 시각을 사용하라.
```

에이전트에는 웹 페이지를 가져올 수 있는 도구가 필요합니다. 답은 다음과 같습니다.

```
오슬로: 목요일 오전 08:15 (2026년 10월 22일, CEST, UTC+2). 10월 25일(일요일)에 시계를 1시간 뒤로 돌립니다.
```

## 2. MCP: AI 앱 연결하기

Exasecond.com은 `https://exasecond.com/mcp`에서 원격 MCP 서버를 운영합니다(streamable HTTP, 키 불필요). 도구는 두 가지입니다. `get_time`(`detail: "local"`이면 한 문장 답)과 `convert_time`.

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor**(`~/.cursor/mcp.json`)와 서버 주소를 받는 다른 앱(예: Trae, Cherry Studio). 파일: [/dev/mcp.json](/dev/mcp.json)

```
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

**VS Code**(`.vscode/mcp.json`)

```
{
  "servers": {
    "exasecond": { "type": "http", "url": "https://exasecond.com/mcp" }
  }
}
```

**Claude Desktop과 claude.ai**: 설정, 커넥터, 사용자 지정 커넥터 추가로 가서 `https://exasecond.com/mcp`를 입력하세요.

## 3. 직접 만든 에이전트를 위한 도구 정의

모델에 `get_local_time` 도구를 주고, 호출되면 `https://exasecond.com/local?tz=<장소>&lang=ko`를 가져와 텍스트를 돌려주세요.

**OpenAI** function calling(그리고 DeepSeek, Qwen, Kimi, GLM 등 호환되는 많은 API). 파일: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use(Claude API). 파일: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, 표준 라이브러리만 사용. 파일: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=ko" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, 의존성 없음(Node 18+, Deno, Bun, 브라우저). 파일: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=ko" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """도시, 나라, 시간대 또는 UTC 시차에 대한 현재 날짜, 현지 시각, 하루 중 때."""
    url = "https://exasecond.com/local?lang=ko" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

내려받는 파일은 영어로 답합니다. 위 코드처럼 `lang=ko`를 붙이면 한국어로 답합니다.

## 4. 로컬 모델: Open WebUI

Open WebUI로 쓰는 Ollama 등 로컬 모델의 경우: [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py)를 내려받은 뒤, Open WebUI에서 Workspace, Tools로 가서 새 도구를 만들고 파일을 붙여 넣으세요. 모델에 대해 켜 주세요. 시계가 가장 필요한 것은 작은 로컬 모델이며, 답은 어떤 컨텍스트 창에도 들어갈 만큼 짧습니다.

## 5. 요청할 수 있는 것

| 주소 | 답 | 토큰 |
|---|---|---|
| `/seoul?lang=ko`, `/new-york?lang=ko` | 말하는 순서의 한 문장: 장소, 요일과 하루 중 때, 현지 시각, 괄호 안에 날짜와 시간대, 그리고 다가오는 시계 변경. 어느 도시, 지역, 섬, 나라, 시간대, 시차든 주소로 쓸 수 있습니다 | ~30-45 |
| `/oslo`, `/new-york`, `/sweden` | 같은 문장의 영어판 | ~30 |
| `/local?tz=Oslo&lang=ko` | 같은 문장 | ~30-45 |
| `/beijing?lang=zh`, `/tokyo?lang=ja` | 같은 문장의 중국어판, 일본어판. `de`, `fr`, `es`, `pt`, `ru`, `sv`도 됩니다 | ~25-60 |
| `/utc` | ISO 8601 형식의 UTC 타임스탬프만 | ~5 |
| `/now` | 약 여덟 줄: UTC, 요일, Unix 시간, 우주 초 | ~80 |
| `/now?tz=Oslo&cutoff=2025-06` | 현지 시각과 학습 데이터 마감 이후 지난 기간을 추가 | ~150 |
| `exasecond.com`(markdown) | 에이전트용 첫 페이지: 주요 도시의 시각, 시계 변경, 사실 하나 | ~440 |
| `/now.md` | 전부: 모든 달력, 세계 시계 | ~500 |
| `/now.json` | 전부, JSON으로 | ~1,400 |
| `/fact?tz=Seoul&lang=ko` | 시간에 관한 "알고 계셨나요?", 가능하면 그 장소에 관한 것 | ~40 |
| `/zones?q=oslo` | 시간대 이름과 현재 시차 | ~30, 검색에 따라 |

`tz`와 짧은 주소는 약 백만 개의 이름을 받습니다. 인구 1,000명 이상의 모든 곳, 주와 지역, 잘 알려진 섬(GeoNames), 나라(Korea, 한국), IANA 이름(Asia/Seoul), 약어(CET, JST), 시차(+09:00). 읽을 수 없는 경우에도 시각은 돌아오고 설명이 붙습니다. 토큰 수는 어림값이며, 한국어는 모델의 토크나이저에 따라 영어와 다르게 셉니다. 전체 설명은 [/llms.txt](/llms.txt)와 [/openapi.json](/openapi.json)에 있습니다.

## 라이선스

이 페이지의 코드 조각과 `/dev/` 아래의 파일은 일반 [MIT 라이선스](/dev/LICENSE)를 따릅니다. "(c) 2026 EggQube, exasecond.com" 표기를 남겨 주세요. 이 라이선스는 개발자용 코드 조각과 파일에만 적용되며, 사이트의 나머지 부분에는 적용되지 않습니다.

## 알아 두면 좋은 점

- 답은 요청하는 순간에 만들어지며 캐시되지 않습니다. 시각은 각국의 시간 표준과 맞춘 서버 시계에서 옵니다.
- 답에는 데이터만 들어 있으며, 에이전트에게 보내는 지시는 절대 들어 있지 않습니다.
- 이것으로 무언가를 만드신다면, exasecond.com을 언급하거나 링크해 주시면 매우 감사하겠습니다.

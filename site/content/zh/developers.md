---
title: 开发者
description: 给 AI 的当前时间。 精确到秒。尽量减少 token 开销。 HTTP / MCP.
---

## 快速入门

给 AI 的当前时间。 **精确到秒。尽量减少 token 开销。**

我们做好准备，让你的 AI 用更少的 token 获取所需时间。通过 HTTP 获取简短的 UTC 时间戳，或通过 MCP 连接智能体和应用。无需密钥，无需注册。

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): 仅时间戳 (ISO 8601, UTC).
- [/local?tz=Oslo&lang=zh](/local?tz=Oslo&lang=zh): 用一句话表达本地时间.
- `/mcp`: 通过 MCP 连接 (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

UTC 时间戳包含秒。时间来自服务器时钟；网络和客户端延迟会影响智能体收到时间的时刻。

语言模型生活在文字里。它们没有时钟，即使拿到了时间，也常常用不好：早餐时说"晚上好"，没发现截止日期已经过了，忘了周日要调钟。

所以 Exasecond.com 是一座用文字做成的钟。它的主要回答是一句助手可以直接转告给人的话："上海现在是星期四下午 16:02（2026年10月22日，CST，UTC+8）。"大约 30 个词元。我们希望 AI 读到人们说时间的方式，也会用同样的方式说出来。传统的数字时钟也依然保留在这里，各种格式都有：UTC 时间戳、Unix 时间和完整的 JSON（见第 5 节）。

本页的所有内容都可以直接复制，每个文件都可以直接获取。本页也有纯文本版本 [/zh/developers.md](/zh/developers.md)，AI 编程助手可以读取它并替你完成配置。

无需密钥，无需注册。请每个对话或智能体每分钟最多发送一次请求。

在地址后加上 `?lang=zh`（或 `&lang=zh`），回答就是中文；不加则是英文。

## 为什么把时钟接为工具

MCP 或 HTTP 工具连接会在每次调用时直接请求时间。即使服务器发送 `Cache-Control: no-store`，AI 应用也可能复用保存的网页。缓存和权限提示取决于应用。开发者配置一次，就能让应用的用户使用这个时钟。

## 1. 一行提示词

最简单的方法，不用写代码。把这段话加到智能体的指令或系统提示词里：

```
在问候对方或提到一天中的时段之前，先读取
https://exasecond.com/<对方所在的城市>?lang=zh 并使用那个时间。
```

智能体需要一个能读取网页的工具。回答是这样的：

```
奥斯陆现在是星期四早上 08:15（2026年10月22日，CEST，UTC+2）。10月25日（星期日）时钟拨回 1 小时。
```

## 2. MCP：连接 AI 应用

Exasecond.com 在 `https://exasecond.com/mcp` 运行一个远程 MCP 服务器（streamable HTTP，无需密钥）。它提供两个工具：`get_time`（加 `detail: "local"` 得到一句话的回答）和 `convert_time`。

要获得相应语言的本地时间句子，向 `get_time` 传入这些参数：

```json
{"tz":"Oslo","detail":"local","lang":"zh"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor**（`~/.cursor/mcp.json`）以及其他接受服务器地址的应用（例如 Trae、Cherry Studio）。文件：[/dev/mcp.json](/dev/mcp.json)

```
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

**VS Code**（`.vscode/mcp.json`）

```
{
  "servers": {
    "exasecond": { "type": "http", "url": "https://exasecond.com/mcp" }
  }
}
```

**Claude Desktop 和 claude.ai**：设置，连接器，添加自定义连接器，输入 `https://exasecond.com/mcp`。

## 3. 给你自己的智能体的工具定义

给模型一个 `get_local_time` 工具，模型调用它时，读取 `https://exasecond.com/local?tz=<地点>&lang=zh` 并返回文本。

**OpenAI** 函数调用（以及许多兼容它的 API，例如 DeepSeek、通义千问、Kimi、智谱 GLM）。文件：[/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** 工具调用（Claude API）。文件：[/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**，只用标准库。文件：[/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=zh" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**，无依赖（Node 18+、Deno、Bun、浏览器）。文件：[/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=zh" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """某个城市、国家、时区或 UTC 偏移的当前日期、本地时间和时段。"""
    url = "https://exasecond.com/local?lang=zh" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

可下载的文件返回英文回答；如上面的代码所示，加上 `lang=zh` 即可得到中文。

## 4. 本地模型：Open WebUI

适用于通过 Open WebUI 使用的 Ollama 等本地模型：下载 [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py)，然后在 Open WebUI 中进入工作空间，工具，新建一个工具并粘贴文件内容。为你的模型启用它。最需要时钟的正是小型本地模型，而这个回答短到任何上下文窗口都放得下。

## 5. 可以查询的内容

### 典型响应的 token 数

| 响应 | 字节 | Token 数 |
|---|---:|---:|
| `/unix`: Unix 时间戳 | 11 | ~5 |
| `/utc`: UTC 时间戳，精确到秒 | 21 | ~14 |
| `/local?tz=Oslo`: 英文的本地时间句子 | ~70-120 | ~25-40 |
| `/now`: 默认简短响应 | ~80 | ~35 |

2026 年 11 月 1 日使用 o200k_base 和 cl100k_base 两种分词器测量响应文本样本。数量因模型、语言和响应内容而异。工具配置和客户端开销另计。这些是典型样本大小，不是计费总量或保证上限。

单独测量的配置上下文：提示词行 26 token；一个提供的工具定义 136-144；两个 MCP 工具的 schema 共 245-256。客户端格式可能增加开销。

| 地址 | 回答 |
|---|---|
| `/shanghai?lang=zh`、`/北京?lang=zh` | 一句按口语顺序的话：地点、星期、时段和本地时间，括号里是日期和时区，以及即将到来的调钟。地址可以是任何城市、地区、岛屿、国家、时区或偏移 |
| `/oslo`、`/new-york`、`/sweden` | 同一句话，英文 |
| `/local?tz=Oslo&lang=zh` | 同一句话 |
| `/berlin?lang=de` | 同一句话，德语；也支持 `fr`、`es`、`pt`、`ru`、`sv`、`ja` 和 `ko` |
| `/utc` | 只有 ISO 8601 UTC 时间戳 |
| `/unix` | 只有 Unix 秒数，也见 `/now.json` 中的 `unix`。简短回答不含它，因为语言模型读的是日期，而不是 Unix 数字 |
| `/now` | 简短文本：UTC 和星期；不含其他历法或宇宙时间 |
| `/now?tz=Oslo&cutoff=2025-06` | 加上本地时间，以及训练截止日期距今多久 |
| `exasecond.com`（markdown） | 给智能体的首页：各大城市的时间、调钟、一条知识 |
| `/now.md` | 全部内容：所有历法、世界时钟 |
| `/now.json` | 全部内容，JSON 格式 |
| `/fact?tz=Shanghai&lang=zh` | 一条关于时间的"你知道吗？"，尽可能与该地点相关 |
| `/zones?q=oslo` | 时区名称及其当前偏移 |

`tz` 和简短地址可以接受大约一百万个名称：所有人口 1,000 以上的地方、州和地区、知名岛屿（GeoNames），国家（挪威、Norway），IANA 名称（Asia/Shanghai），缩写（CET、JST）和偏移（+08:00）。如果无法识别，仍会返回时间，并附上一条说明。完整说明见 [/llms.txt](/llms.txt) 和 [/openapi.json](/openapi.json)。

## 许可

本页的代码片段和 `/dev/` 下的文件遵循常规的 [MIT 许可证](/dev/LICENSE)。请随附声明 "© 2026 EggQube, exasecond.com"。该许可只适用于这些开发者代码片段和文件，不适用于网站的其他部分。

## 须知

- 回答使用服务器时钟在请求时生成，并附有 `Cache-Control: no-store`。客户端缓存仍可能影响时效性。
- 回答只包含数据，从不包含给你的智能体的指令。
- 如果你在此基础上做了什么，非常感谢你提及或链接 exasecond.com。

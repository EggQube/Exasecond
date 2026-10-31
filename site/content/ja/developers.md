---
title: 開発者の方へ
description: AI に、現在の時刻を。 秒単位で。トークン消費は最小限に。 HTTP / MCP.
---

## クイックスタート

AI に、現在の時刻を。 **秒単位で。トークン消費は最小限に。**

必要な時刻を少ないトークンで取得できるよう、私たちが準備します。HTTP で短い UTC タイムスタンプを取得するか、MCP でエージェントやアプリを接続できます。キーも登録も不要です。

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): タイムスタンプのみ (ISO 8601, UTC).
- [/local?tz=Oslo&lang=ja](/local?tz=Oslo&lang=ja): 現地時刻を一文で.
- `/mcp`: MCP で接続 (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

UTC タイムスタンプは秒を含みます。時刻の取得元はサーバーの時計です。ネットワークやクライアントの遅延により、エージェントが受け取る時刻は異なります。

言語モデルは文字の中に生きています。時計を持たず、時刻を渡されてもうまく扱えないことがよくあります。朝食の時間に「こんばんは」と言ったり、締め切りが過ぎたことに気づかなかったり、日曜日に時計が切り替わることを忘れたりします。

そこで Exasecond.com は、文字でできた時計になっています。主な答えは、アシスタントがそのまま人に伝えられる一文です。「東京：木曜日の午後、16:02（2026年10月22日、JST、UTC+9）。」約30から40トークンです。人が口にするように時刻を読めば、AIも同じように話すようになる、という願いを込めています。昔ながらのデジタル時計も、あらゆる形式でここに残しています。UTCのタイムスタンプ、Unix時間、完全なJSON（第5節を参照）。

このページの内容はすべてそのままコピーでき、どのファイルも直接取得できます。このページは [/ja/developers.md](/ja/developers.md) でプレーンテキストとしても読めるので、AIコーディングアシスタントに読ませて設定を任せることもできます。

キーも登録も不要です。リクエストは、会話またはエージェントごとに1分に1回までにしてください。

アドレスに `?lang=ja`（または `&lang=ja`）を付けると日本語で答えます。付けなければ英語です。

## 時計をツールとして接続する理由

MCP または HTTP のツール接続は、呼び出しごとに時刻を直接取得します。`Cache-Control: no-store` があっても、AI アプリが保存済みのページを再利用する場合があります。キャッシュや許可の確認はアプリによって異なります。開発者が一度設定すれば、そのアプリの利用者が時計を使えます。

## 1. どのプロンプトにも使える一行

いちばん簡単な方法で、コードは不要です。エージェントの指示またはシステムプロンプトに次を加えてください。

```
相手に挨拶したり時間帯に触れたりする前に、
https://exasecond.com/<相手の都市>?lang=ja を読み、その時刻を使うこと。
```

エージェントにはウェブページを取得できるツールが必要です。答えは次のようになります。

```
オスロ：木曜日の午前、08:15（2026年10月22日、CEST、UTC+2）。10月25日（日曜日）に時計が1時間戻ります。
```

## 2. MCP：AIアプリにつなぐ

Exasecond.com は `https://exasecond.com/mcp` でリモートMCPサーバーを動かしています（streamable HTTP、キー不要）。ツールは二つです。`get_time`（`detail: "local"` で一文の答え）と `convert_time`。

翻訳されたローカル時刻の文には、`get_time` に次の引数を渡します。

```json
{"tz":"Oslo","detail":"local","lang":"ja"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor**（`~/.cursor/mcp.json`）や、サーバーのアドレスを受け付けるほかのアプリ（Trae、Cherry Studio など）。ファイル：[/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop と claude.ai**：設定、コネクタ、カスタムコネクタを追加、と進み、`https://exasecond.com/mcp` を入力します。

## 3. 自分のエージェント用のツール定義

モデルに `get_local_time` ツールを持たせ、呼ばれたら `https://exasecond.com/local?tz=<場所>&lang=ja` を取得してテキストを返します。

**OpenAI** の function calling（および DeepSeek、Qwen、Kimi、GLM など互換の多くのAPI）。ファイル：[/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** の tool use（Claude API）。ファイル：[/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**、標準ライブラリのみ。ファイル：[/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=ja" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**、依存関係なし（Node 18+、Deno、Bun、ブラウザ）。ファイル：[/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=ja" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """都市、国、タイムゾーン、またはUTCとの時差について、現在の日付、現地時刻、時間帯を返す。"""
    url = "https://exasecond.com/local?lang=ja" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

ダウンロードできるファイルは英語で答えます。上のコードのように `lang=ja` を付ければ日本語になります。

## 4. ローカルモデル：Open WebUI

Open WebUI で使う Ollama などのローカルモデルの場合：[/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py) をダウンロードし、Open WebUI で Workspace、Tools を開いて新しいツールを作り、ファイルを貼り付けます。モデルに対して有効にしてください。時計を最も必要としているのは小さなローカルモデルで、答えはどのコンテキストウィンドウにも収まる短さです。

## 5. 取得できるもの

### 一般的な応答のトークン数

| 応答 | バイト数 | トークン数 |
|---|---:|---:|
| `/unix`: Unix タイムスタンプ | 11 | ~5 |
| `/utc`: 秒単位の UTC タイムスタンプ | 21 | ~14 |
| `/local?tz=Oslo`: 現地時刻を伝える英語の一文 | ~70-120 | ~25-40 |
| `/now`: 標準の短い応答 | ~200 | ~70 |

2026年10月23日に o200k_base と cl100k_base の2種類のトークナイザーで応答テキストのサンプルを測定しました。数はモデル、言語、応答内容で変わります。ツール設定とクライアントの処理による分は別です。一般的なサンプルの大きさであり、請求総量や保証された上限ではありません。

設定用コンテキストは別に測定：プロンプトの一行は26トークン、提供するツール定義1つは136-144、MCP の両ツールのスキーマは247-258です。クライアントの形式によって増える場合があります。

| アドレス | 答え |
|---|---|
| `/tokyo?lang=ja`、`/new-york?lang=ja` | 話し言葉の順の一文：場所、曜日と時間帯、現地時刻、括弧内に日付とタイムゾーン、そして近く迫った時計の切り替え。どの都市、地域、島、国、タイムゾーン、時差でもアドレスにできます |
| `/oslo`、`/new-york`、`/sweden` | 同じ一文の英語版 |
| `/local?tz=Oslo&lang=ja` | 同じ一文 |
| `/beijing?lang=zh`、`/seoul?lang=ko` | 同じ一文の中国語版、韓国語版。`de`、`fr`、`es`、`pt`、`ru`、`sv` も使えます |
| `/utc` | ISO 8601 形式のUTCタイムスタンプのみ |
| `/now` | 短いテキスト：UTC、曜日、Unix時間。追加の暦や宇宙時間は含みません |
| `/now?tz=Oslo&cutoff=2025-06` | 現地時刻と、学習データの締め切りからの経過時間を追加 |
| `exasecond.com`（markdown） | エージェント向けトップページ：主要都市の時刻、時計の切り替え、一つの事実 |
| `/now.md` | すべて：あらゆる暦、世界時計 |
| `/now.json` | すべてをJSONで |
| `/fact?tz=Tokyo&lang=ja` | 時間についての「ご存じですか？」、できればその場所にちなんだもの |
| `/zones?q=oslo` | タイムゾーン名と現在の時差 |

`tz` と短いアドレスは約100万の名前を受け付けます。人口1,000人以上のあらゆる場所、州や地域、よく知られた島（GeoNames）、国（Japan、日本）、IANA名（Asia/Tokyo）、略称（CET、JST）、時差（+09:00）。読み取れない場合も時刻は返り、注記が付きます。詳しい説明は [/llms.txt](/llms.txt) と [/openapi.json](/openapi.json) にあります。

## ライセンス

このページのコード片と `/dev/` 以下のファイルは、通常の [MITライセンス](/dev/LICENSE) に従います。「© 2026 EggQube, exasecond.com」の表記を残してください。このライセンスが適用されるのはこれらの開発者向けコード片とファイルだけで、サイトのほかの部分には適用されません。

## 知っておくと便利なこと

- 応答はリクエストごとにサーバーの時計から生成され、`Cache-Control: no-store` を付けて送信されます。ただし、クライアントのキャッシュが鮮度に影響する場合があります。
- 答えに含まれるのはデータだけで、エージェントへの指示は決して含みません。
- これを使って何かを作ったら、exasecond.com への言及やリンクをいただけるととても嬉しいです。

---
title: Para desenvolvedores
description: A hora atual para a IA. Até o segundo. Mínimo consumo de tokens. HTTP / MCP.
---

## Início rápido

A hora atual para a IA. **Até o segundo. Mínimo consumo de tokens.**

Fazemos o trabalho para sua IA gastar menos tokens obtendo a hora de que precisa. Busque um timestamp UTC curto por HTTP ou conecte seu agente ou aplicativo via MCP. Sem chave e sem cadastro.

```sh
curl https://exasecond.com/utc
```

- [/utc](/utc): Apenas o timestamp (ISO 8601, UTC).
- [/local?tz=Oslo&lang=pt](/local?tz=Oslo&lang=pt): A hora local em uma frase.
- `/mcp`: Conectar via MCP (Streamable HTTP).

```json
{
  "mcpServers": {
    "exasecond": { "url": "https://exasecond.com/mcp" }
  }
}
```

Os timestamps UTC incluem segundos. A hora vem do relógio do servidor; atrasos da rede e do cliente afetam quando ela chega ao agente.

Modelos de linguagem vivem em texto. Eles não têm relógio, e mesmo quando recebem a hora, muitas vezes lidam mal com ela: dão "boa noite" no café da manhã, não percebem que um prazo já passou, esquecem que os relógios mudam no domingo.

Por isso o Exasecond.com é um relógio feito de texto. A resposta principal é uma frase que um assistente pode passar direto para uma pessoa: "Rio: é quinta-feira à tarde, 16h02 (22 de outubro de 2026, UTC-3)." Cerca de 30 a 40 tokens. A esperança é que, lendo a hora como as pessoas a dizem, a IA também a diga assim. O velho relógio digital continua aqui, em todos os formatos: o carimbo de tempo UTC, o tempo Unix e o JSON completo (veja a seção 5).

Tudo nesta página pode ser copiado diretamente, e cada arquivo pode ser baixado diretamente. Esta página também está em texto simples em [/pt/developers.md](/pt/developers.md), para que um assistente de programação com IA possa lê-la e configurar tudo para você.

Sem chave e sem cadastro. Por favor, envie no máximo uma requisição por minuto por conversa ou agente.

Acrescente `?lang=pt` (ou `&lang=pt`) ao endereço para receber a resposta em português; sem isso, ela vem em inglês.

## Por que conectar o relógio como ferramenta

Uma conexão MCP ou HTTP solicita a hora diretamente a cada chamada. Um aplicativo de IA pode reutilizar uma página salva apesar de `Cache-Control: no-store`. O cache e as permissões dependem do aplicativo. Uma configuração do desenvolvedor disponibiliza o relógio aos usuários do aplicativo.

## 1. Uma linha para qualquer prompt

O jeito mais simples, sem código. Acrescente isto às instruções ou ao prompt de sistema do seu agente:

```
Antes de cumprimentar alguém ou mencionar o período do dia, leia
https://exasecond.com/<a cidade da pessoa>?lang=pt e use essa hora.
```

O agente precisa de uma ferramenta que consiga ler páginas web. A resposta é assim:

```
Oslo: é quinta-feira de manhã cedo, 08h15 (22 de outubro de 2026, CEST, UTC+2). No domingo, 25 de outubro, os relógios atrasam uma hora.
```

## 2. MCP: conectar um aplicativo de IA

O Exasecond.com roda um servidor MCP remoto em `https://exasecond.com/mcp` (streamable HTTP, sem chave). Ele oferece duas ferramentas: `get_time` (com `detail: "local"` para a resposta de uma frase) e `convert_time`.

Para uma frase local traduzida, passe estes argumentos a `get_time`:

```json
{"tz":"Oslo","detail":"local","lang":"pt"}
```

**Claude Code**

```
claude mcp add --transport http exasecond https://exasecond.com/mcp
```

**Cursor** (`~/.cursor/mcp.json`) e outros aplicativos que aceitam um endereço de servidor (por exemplo Trae, Cherry Studio). Arquivo: [/dev/mcp.json](/dev/mcp.json)

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

**Claude Desktop e claude.ai**: Configurações, Conectores, Adicionar conector personalizado, e informe `https://exasecond.com/mcp`.

## 3. Definições de ferramentas para o seu próprio agente

Dê ao seu modelo uma ferramenta `get_local_time` e, quando ele a chamar, leia `https://exasecond.com/local?tz=<lugar>&lang=pt` e devolva o texto.

**OpenAI** function calling (e as muitas APIs compatíveis, como DeepSeek, Qwen, Kimi e GLM). Arquivo: [/dev/openai-tool.json](/dev/openai-tool.json)

**Anthropic** tool use (API do Claude). Arquivo: [/dev/anthropic-tool.json](/dev/anthropic-tool.json)

**Python**, só a biblioteca padrão. Arquivo: [/dev/exasecond.py](/dev/exasecond.py)

```
import urllib.parse, urllib.request

def get_local_time(place: str = "") -> str:
    url = "https://exasecond.com/local?lang=pt" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

**JavaScript**, sem dependências (Node 18+, Deno, Bun, navegadores). Arquivo: [/dev/exasecond.js](/dev/exasecond.js)

```
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local?lang=pt" + (place ? "&tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}
```

**LangChain**

```
from langchain_core.tools import tool
import urllib.parse, urllib.request

@tool
def get_local_time(place: str = "") -> str:
    """Data atual, hora local e período do dia para uma cidade, um país, um fuso horário ou uma diferença de UTC."""
    url = "https://exasecond.com/local?lang=pt" + ("&tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()
```

Os arquivos para download respondem em inglês; com `lang=pt`, como no código acima, a resposta vem em português.

## 4. Modelos locais: Open WebUI

Para Ollama e outros modelos locais usados pelo Open WebUI: baixe [/dev/openwebui_exasecond.py](/dev/openwebui_exasecond.py), depois no Open WebUI vá em Workspace, Tools, crie uma ferramenta nova e cole o arquivo. Ative-a para o seu modelo. São justamente os modelos locais pequenos que mais precisam de um relógio, e a resposta é curta o bastante para qualquer janela de contexto.

## 5. O que você pode pedir

### Tokens típicos por resposta

| Resposta | Bytes | Tokens |
|---|---:|---:|
| `/unix`: Timestamp Unix | 11 | ~5 |
| `/utc`: Timestamp UTC, até o segundo | 21 | ~14 |
| `/local?tz=Oslo`: Frase em inglês com a hora local | ~70-120 | ~25-40 |
| `/now`: Resposta compacta padrão | ~80 | ~35 |

Textos de resposta medidos em 1º de novembro de 2026 com dois tokenizadores: o200k_base e cl100k_base. A quantidade varia conforme o modelo, o idioma e o conteúdo. A configuração das ferramentas e o formato do cliente são adicionais. São tamanhos de amostras típicas, não totais de cobrança nem limites garantidos.

Contexto de configuração medido separadamente: linha de prompt 26 tokens; uma definição de ferramenta fornecida 136-144; os dois schemas MCP 245-256. O formato do cliente pode acrescentar mais.

| Endereço | Resposta |
|---|---|
| `/rio?lang=pt`, `/lisboa?lang=pt` | Uma frase na ordem falada: lugar, dia e período do dia, hora local, e entre parênteses a data e o fuso, mais uma mudança de horário próxima. Qualquer cidade, região, ilha, país, fuso ou diferença como endereço |
| `/oslo`, `/new-york`, `/sweden` | A mesma frase em inglês |
| `/local?tz=Oslo&lang=pt` | A mesma frase |
| `/beijing?lang=zh`, `/berlin?lang=de` | A mesma frase em chinês, em alemão; também `fr`, `es`, `ru`, `sv`, `ja` e `ko` |
| `/utc` | Só o carimbo de tempo UTC ISO 8601 |
| `/unix` | Só os segundos Unix, também como `unix` em `/now.json`. Fora das respostas curtas, porque modelos de linguagem leem uma data, não um número Unix |
| `/now` | Texto compacto: UTC e dia da semana; sem outros calendários nem tempo cósmico |
| `/now?tz=Oslo&cutoff=2025-06` | Acrescenta a hora local e há quanto tempo foi uma data de corte de treinamento |
| `exasecond.com` (markdown) | A página inicial para agentes: a hora de todas as grandes cidades, mudanças de horário, um fato |
| `/now.md` | Tudo: todos os calendários, relógios do mundo |
| `/now.json` | Tudo, em JSON |
| `/fact?tz=Rio&lang=pt` | Um fato "Você sabia?" sobre o tempo, do lugar quando possível |
| `/zones?q=oslo` | Nomes de fusos horários e a diferença atual |

`tz` e o endereço curto aceitam cerca de um milhão de nomes: todo lugar com 1.000 habitantes ou mais, estados e regiões, ilhas conhecidas (GeoNames), países (Norway, Noruega), nomes IANA (America/Sao_Paulo), abreviações (CET, JST) e diferenças (+02:00). Se algo não puder ser lido, a hora é devolvida mesmo assim, com uma observação. A descrição completa está em [/llms.txt](/llms.txt) e [/openapi.json](/openapi.json).

## Licença

Os trechos de código desta página e os arquivos em `/dev/` seguem a [licença MIT](/dev/LICENSE) comum. Mantenha o aviso "© 2026 EggQube, exasecond.com". A licença cobre só esses trechos e arquivos para desenvolvedores, não o resto do site.

## Bom saber

- As respostas são geradas a cada requisição com o relógio do servidor e enviadas com `Cache-Control: no-store`. O cache do cliente ainda pode afetar a atualização.
- As respostas contêm só dados, nunca instruções para o seu agente.
- Se você construir algo com isso, uma menção ou um link para exasecond.com é muito bem-vindo.

// Exasecond — request router. Standard Fetch API handler: deploys as a Cloudflare Worker
// (wrangler.toml), and runs locally under Node via server.js.
import { snapshot, parseInstant, isValidZone } from "./core.js";
import { nowText, nowMd } from "./formats.js";
import { llmsTxt, llmsFullTxt, specMd, robotsTxt, sitemapXml, openapi } from "./content.js";
import { renderPage } from "./page.js";
import { mcpHandler } from "./mcp.js";

const BASE_HEADERS = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Methods": "GET, HEAD, POST, OPTIONS",
  "Access-Control-Allow-Headers": "Content-Type, Accept, Mcp-Session-Id, Mcp-Protocol-Version",
  "Cache-Control": "no-store",
  "X-Content-Type-Options": "nosniff",
};

const TYPES = {
  text: "text/plain; charset=utf-8",
  md: "text/markdown; charset=utf-8",
  json: "application/json; charset=utf-8",
  html: "text/html; charset=utf-8",
  xml: "application/xml; charset=utf-8",
};

function send(body, type, extra = {}, status = 200) {
  const headers = { ...BASE_HEADERS, "Content-Type": TYPES[type], ...extra };
  if (type === "md" || type === "text") headers["x-markdown-tokens"] = String(Math.ceil(body.length / 4)); // rough token estimate
  return new Response(body, { status, headers });
}

const json = (obj, status = 200) => send(JSON.stringify(obj, null, 2) + "\n", "json", {}, status);

/** Which representation does the client prefer? */
function negotiate(request, fallback) {
  const a = (request.headers.get("Accept") || "").toLowerCase();
  if (a.includes("text/markdown")) return "md";
  if (a.includes("application/json") && !a.includes("text/html")) return "json";
  if (a.includes("text/html")) return "html";
  return fallback;
}

function opts(url) {
  const tz = url.searchParams.get("tz") || undefined;
  if (tz && !isValidZone(tz)) throw new Error(`Unknown time zone "${tz}". Use an IANA name like Europe/Oslo.`);
  return { tz, cutoff: url.searchParams.get("cutoff") || undefined };
}

export async function handle(request) {
  const url = new URL(request.url);
  const path = url.pathname.replace(/\/+$/, "") || "/";

  if (request.method === "OPTIONS") return new Response(null, { status: 204, headers: BASE_HEADERS });
  if (path === "/mcp") return mcpHandler(request, BASE_HEADERS);
  if (!["GET", "HEAD"].includes(request.method)) return send("Method not allowed\n", "text", { Allow: "GET, HEAD" }, 405);

  try {
    const now = new Date();
    switch (path) {
      case "/": {
        const s = snapshot(now);
        const want = negotiate(request, "html");
        if (want === "md") return send(nowMd(s, "Exasecond: current time"), "md", { Vary: "Accept" });
        if (want === "json") return json(s);
        return send(renderPage(s), "html", { Vary: "Accept", Link: '</llms.txt>; rel="describedby"; type="text/plain"' });
      }
      case "/now": case "/time": {
        const s = snapshot(now, opts(url));
        const want = negotiate(request, "text");
        if (want === "md") return send(nowMd(s), "md", { Vary: "Accept" });
        if (want === "json") return json(s);
        return send(nowText(s), "text", { Vary: "Accept" });
      }
      case "/now.md": case "/index.md": return send(nowMd(snapshot(now, opts(url))), "md");
      case "/now.json": case "/api/now": return json(snapshot(now, opts(url)));
      case "/now.txt": return send(nowText(snapshot(now, opts(url))), "text");
      case "/utc": return send(now.toISOString().replace(/\.\d{3}Z$/, "Z") + "\n", "text");
      case "/unix": return send(Math.floor(now.getTime() / 1000) + "\n", "text");
      case "/universe": return send(snapshot(now).universe_seconds + "\n", "text");
      case "/api/convert": {
        const p = Object.fromEntries(url.searchParams);
        return json(snapshot(parseInstant(p), opts(url)));
      }
      case "/llms.txt": return send(llmsTxt(snapshot(now)), "text");
      case "/llms-full.txt": return send(llmsFullTxt(snapshot(now)), "text");
      case "/spec.md": return send(specMd(), "md");
      case "/openapi.json": return json(openapi());
      case "/robots.txt": return send(robotsTxt(), "text");
      case "/sitemap.xml": return send(sitemapXml(), "xml");
      case "/health": return json({ ok: true });
      default:
        return send(`Not found: ${path}\nTry /now, /utc, /llms.txt\n`, "text", {}, 404);
    }
  } catch (e) {
    return json({ error: e.message }, 400);
  }
}

export default { fetch: handle };

// Minimal stateless remote MCP server (Streamable HTTP transport, JSON responses, no sessions).
// Spec: https://modelcontextprotocol.io  — kept deliberately tiny so weak tool-callers cope.
import { snapshot, parseInstant, SPEC_VERSION } from "./core.js";
import { nowText, nowMd } from "./formats.js";

const SUPPORTED = ["2025-06-18", "2025-03-26", "2024-11-05"];

const TOOLS = [
  {
    name: "get_time",
    description: "Get the current date and time. Call this instead of guessing today's date from training data.",
    inputSchema: {
      type: "object",
      properties: {
        tz: { type: "string", description: "IANA time zone, e.g. Europe/Oslo" },
        cutoff: { type: "string", description: "Your training cutoff, YYYY-MM" },
        detail: { type: "string", enum: ["basic", "full"], description: "full adds all calendars and world clocks" },
      },
    },
  },
  {
    name: "convert_time",
    description: "Convert one instant (iso, unix, universe or es) into UTC, a time zone and all human calendars.",
    inputSchema: {
      type: "object",
      properties: {
        iso: { type: "string" }, unix: { type: "string" }, universe: { type: "string" }, es: { type: "string" },
        tz: { type: "string" },
      },
    },
  },
];

function callTool(name, args = {}) {
  if (name === "get_time") {
    const s = snapshot(new Date(), { tz: args.tz, cutoff: args.cutoff });
    return args.detail === "full" ? nowMd(s) : nowText(s);
  }
  if (name === "convert_time") {
    const d = parseInstant(args);
    return nowMd(snapshot(d, { tz: args.tz }), "Converted time");
  }
  throw Object.assign(new Error(`Unknown tool: ${name}`), { code: -32602 });
}

function handle(msg) {
  const { id, method, params } = msg;
  const ok = (result) => ({ jsonrpc: "2.0", id, result });
  const err = (code, message) => ({ jsonrpc: "2.0", id: id ?? null, error: { code, message } });
  if (id === undefined) return null; // notification (e.g. notifications/initialized)
  switch (method) {
    case "initialize": {
      const v = SUPPORTED.includes(params?.protocolVersion) ? params.protocolVersion : SUPPORTED[0];
      return ok({
        protocolVersion: v,
        capabilities: { tools: { listChanged: false } },
        serverInfo: { name: "exasecond", version: SPEC_VERSION },
        instructions: "Use get_time whenever you need today's date or the current time.",
      });
    }
    case "ping": return ok({});
    case "tools/list": return ok({ tools: TOOLS });
    case "tools/call":
      try {
        return ok({ content: [{ type: "text", text: callTool(params?.name, params?.arguments) }] });
      } catch (e) {
        if (e.code) return err(e.code, e.message);
        return ok({ content: [{ type: "text", text: `Error: ${e.message}` }], isError: true });
      }
    default: return err(-32601, `Method not found: ${method}`);
  }
}

export async function mcpHandler(request, headers) {
  if (request.method !== "POST") {
    return new Response("Exasecond MCP endpoint. POST JSON-RPC 2.0 here (streamable HTTP, stateless).\n", {
      status: 405, headers: { ...headers, Allow: "POST", "Content-Type": "text/plain; charset=utf-8" },
    });
  }
  let body;
  try { body = await request.json(); } catch {
    return Response.json({ jsonrpc: "2.0", id: null, error: { code: -32700, message: "Parse error" } }, { status: 400, headers });
  }
  const results = (Array.isArray(body) ? body : [body]).map(handle).filter(Boolean);
  if (results.length === 0) return new Response(null, { status: 202, headers });
  return Response.json(Array.isArray(body) ? results : results[0], { headers });
}

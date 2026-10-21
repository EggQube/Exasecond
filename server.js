// Local dev server: runs the same Fetch handler as the Cloudflare Worker, on Node 18+.
//   node server.js            -> http://localhost:8787
import { createServer } from "node:http";
import { handle } from "./src/worker.js";

const PORT = Number(process.env.PORT) || 8787;

createServer(async (req, res) => {
  try {
    const chunks = [];
    for await (const c of req) chunks.push(c);
    const body = chunks.length ? Buffer.concat(chunks) : undefined;
    const request = new Request(`http://${req.headers.host || "localhost"}${req.url}`, {
      method: req.method,
      headers: req.headers,
      body: ["GET", "HEAD"].includes(req.method) ? undefined : body,
    });
    const response = await handle(request);
    res.writeHead(response.status, Object.fromEntries(response.headers));
    if (req.method === "HEAD" || !response.body) return res.end();
    res.end(Buffer.from(await response.arrayBuffer()));
  } catch (e) {
    res.writeHead(500, { "Content-Type": "text/plain" });
    res.end(String(e?.stack || e));
  }
}).listen(PORT, () => console.log(`exasecond on http://localhost:${PORT}`));

import { test } from "node:test";
import assert from "node:assert/strict";
import { OFFSET, unixToUniverse, universeToUnix, toExaseconds, parseInstant, staleness, snapshot } from "../src/core.js";
import { handle } from "../src/worker.js";

test("the convention constant", () => {
  assert.equal(OFFSET, 435084630253315200n);
  assert.equal(unixToUniverse(946684800), 435084631200000000n); // anchor: 2000-01-01T00:00:00Z
});

test("round trip is exact", () => {
  for (const u of [0, 1, 1792588078, -1000, 4102444800]) assert.equal(universeToUnix(unixToUniverse(u)), BigInt(u));
});

test("exaseconds formatting", () => {
  assert.equal(toExaseconds(435084632045903278n), "0.435084632045903278");
  assert.equal(toExaseconds(1_000_000_000_000_000_000n, 3), "1.000");
});

test("parse every input kind to the same instant", () => {
  const iso = "2026-10-21T00:00:00Z";
  const unix = Date.parse(iso) / 1000;
  const universe = (BigInt(unix) + OFFSET).toString();
  for (const p of [{ iso }, { unix: String(unix) }, { universe }, { es: "0." + universe.padStart(18, "0") }]) {
    assert.equal(parseInstant(p).toISOString(), "2026-10-21T00:00:00.000Z");
  }
});

test("staleness", () => {
  const s = staleness(new Date("2026-10-21T00:00:00Z"), "2026-06");
  assert.equal(s.days_since_cutoff, 112);
  assert.equal(s.months_since_cutoff, 3);
  assert.equal(staleness(new Date(), "nonsense"), null);
});

test("chinese calendar and zodiac", () => {
  const zh = snapshot(new Date("2026-10-21T12:00:00Z")).calendars.find((c) => c.id === "chinese");
  assert.equal(zh.zodiac, "Horse");
  assert.equal(zh.year_name, "bing-wu");
});

test("routes", async () => {
  const get = (p, h = {}) => handle(new Request("http://x" + p, { headers: h }));
  assert.match(await (await get("/utc")).text(), /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z\n$/);
  assert.match(await (await get("/now")).text(), /^UTC: /);
  assert.equal((await get("/now", { Accept: "text/markdown" })).headers.get("content-type"), "text/markdown; charset=utf-8");
  assert.match(await (await get("/")).text(), /Seconds since the beginning of the universe/);
  assert.match(await (await get("/llms.txt")).text(), /^# Exasecond/);
  assert.equal((await get("/now?tz=Not/AZone")).status, 400);
  const mcp = await handle(new Request("http://x/mcp", { method: "POST", headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ jsonrpc: "2.0", id: 1, method: "tools/call", params: { name: "get_time", arguments: {} } }) }));
  assert.match((await mcp.json()).result.content[0].text, /^UTC: /);
});

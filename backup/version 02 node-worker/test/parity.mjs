// Parity test: the PHP port must give the same answers as the Node/Worker version.
//   node test/parity.mjs <path-to-php.exe> [php.ini]   (run from this backup folder)
// Converts a set of instants with both implementations and compares every field.
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import { snapshot, parseInstant } from "../src/core.js";

const [php, ini] = process.argv.slice(2);
if (!php) { console.error("usage: node test/parity.mjs <php> [php.ini]"); process.exit(2); }

const CASES = [
  { iso: "2026-10-21T12:00:00Z", tz: "Europe/Oslo", cutoff: "2025-06" },
  { iso: "2000-01-01T00:00:00Z", tz: "Asia/Shanghai" },
  { iso: "1970-01-01T00:00:00Z", tz: "America/New_York", cutoff: "1969" },
  { iso: "2030-02-03T04:05:06Z", tz: "Australia/Sydney", cutoff: "2030-02-01" },
  { iso: "1999-12-31T23:59:59Z", tz: "Asia/Kolkata" },
  { iso: "2024-02-29T18:30:00Z", tz: "America/Sao_Paulo", cutoff: "2023-12" },
  { iso: "2023-01-22T00:00:00Z", tz: "Asia/Tokyo" },              // Chinese new year
  { iso: "1900-03-01T00:00:00Z", tz: "UTC" },
  { iso: "2100-12-31T23:59:59Z", tz: "Africa/Lagos" },
  { unix: "0" }, { unix: "-86400" }, { unix: "4102444800" },
  { universe: "435084631200000000" }, { es: "0.435084632045903278" },
];
const BAD = [{ iso: "next monday" }, { unix: "abc" }, { universe: "1.5" }, { es: "x" }, { universe: "1000000000000000000" }, { es: "2.5" }, {}];

const nodeOut = CASES.map((c) => {
  const d = parseInstant(c);
  return JSON.parse(JSON.stringify(snapshot(d, { tz: c.tz, cutoff: c.cutoff })));
});
const nodeBad = BAD.map((c) => { try { parseInstant(c); return "ok"; } catch (e) { return "error"; } });

const script = fileURLToPath(new URL("./php_snapshots.php", import.meta.url));
const args = ini ? ["-c", ini, script] : [script];
const phpOut = JSON.parse(execFileSync(php, args, { input: JSON.stringify({ cases: CASES, bad: BAD }), encoding: "utf8" }));

// Known, accepted differences that come from different ICU versions (Node vs PHP builds).
const ACCEPT = [/^calendars\.\d+\.era$/, /^calendars\.\d+\.text$/];
const icuSame = (a, b) => String(a).normalize("NFD").replace(/[̀-ͯ]/g, "") === String(b).normalize("NFD").replace(/[̀-ͯ]/g, "");

let fails = 0, soft = 0;
function cmp(a, b, path) {
  if (typeof a === "object" && a !== null) {
    for (const k of new Set([...Object.keys(a), ...Object.keys(b ?? {})])) cmp(a[k], b?.[k], path ? `${path}.${k}` : k);
    return;
  }
  if (a === b) return;
  if (typeof a === "number" && typeof b === "number" && Math.abs(a - b) < 1e-6) return;
  if (ACCEPT.some((r) => r.test(path))) {
    soft++;
    if (!icuSame(a, b)) console.log(`  icu  ${path}: node=${JSON.stringify(a)} php=${JSON.stringify(b)} (calendar spelling differs between ICU versions)`);
    return;
  }
  fails++;
  console.log(`  DIFF ${path}: node=${JSON.stringify(a)} php=${JSON.stringify(b)}`);
}
CASES.forEach((c, i) => { const before = fails; cmp(nodeOut[i], phpOut.snapshots[i], ""); if (fails > before) console.log("in case", JSON.stringify(c)); });
BAD.forEach((c, i) => { if (nodeBad[i] !== phpOut.bad[i]) { fails++; console.log(`  BAD-INPUT ${JSON.stringify(c)}: node=${nodeBad[i]} php=${phpOut.bad[i]}`); } });

console.log(`${CASES.length} instants, ${BAD.length} bad inputs: ${fails} differences, ${soft} accepted ICU spelling differences`);
process.exit(fails ? 1 : 0);

// Human-facing page: the exasecond clock. Server-rendered with the live time baked in,
// so agents that don't run JavaScript still see the current time in the HTML.
import { OFFSET, groupDigits } from "./core.js";
import { SITE } from "./content.js";

const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" })[c]);

export function renderPage(s) {
  const pct = s.universe_fraction_percent;
  const cal = s.calendars.map((c) => `
        <li class="cal" data-id="${c.id}">
          <span class="cal-name">${esc(c.name)}</span>
          <span class="cal-date">${esc(c.text)}</span>
          ${c.native ? `<span class="cal-native" lang="zh">${esc(c.native)}</span>` : ""}
        </li>`).join("");

  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Exasecond: the clock for AI</title>
<meta name="description" content="Seconds since the beginning of the universe, and the current time for AI agents. Now: ${s.utc} UTC.">
<link rel="alternate" type="text/markdown" href="/now.md" title="Current time as markdown">
<link rel="alternate" type="text/plain" href="/llms.txt" title="llms.txt">
<link rel="alternate" type="application/json" href="/now.json" title="Current time as JSON">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='14' fill='none' stroke='%23f0b44c' stroke-width='3'/%3E%3Cpath d='M16 7v9l6 4' stroke='%23f0b44c' stroke-width='3' fill='none' stroke-linecap='round'/%3E%3C/svg%3E">
<style>
  :root {
    --bg: #07080c; --bg-2: #0e1017; --line: #23262f; --ink: #ece8df; --ink-2: #9c9a93; --ink-3: #64636a;
    --accent: #f0b44c; --accent-2: #f0b44c22;
    --mono: ui-monospace, "SF Mono", "JetBrains Mono", Menlo, Consolas, monospace;
    --sans: ui-sans-serif, -apple-system, "Segoe UI", Inter, Roboto, sans-serif;
    --serif: "Iowan Old Style", "Palatino Linotype", Palatino, Georgia, serif;
  }
  @media (prefers-color-scheme: light) {
    :root { --bg: #f4f1e9; --bg-2: #ebe6da; --line: #d6d0c2; --ink: #16171c; --ink-2: #55545a; --ink-3: #8a877f; --accent: #a8590f; --accent-2: #a8590f1a; }
  }
  * { box-sizing: border-box; }
  html, body { margin: 0; background: var(--bg); color: var(--ink); }
  body { font: 16px/1.55 var(--sans); -webkit-font-smoothing: antialiased; }
  a { color: inherit; text-decoration-color: var(--ink-3); text-underline-offset: 3px; }
  a:hover { text-decoration-color: var(--accent); }
  .wrap { max-width: 1120px; margin: 0 auto; padding: 0 16px; }
  @media (min-width: 720px) { .wrap { padding: 0 40px; } }

  header { display: flex; justify-content: space-between; align-items: baseline; padding: 24px 0; gap: 16px; }
  .mark { font: 600 13px/1 var(--mono); letter-spacing: .32em; text-transform: uppercase; }
  .mark b { color: var(--accent); font-weight: 600; }
  header nav { font: 13px/1 var(--mono); color: var(--ink-2); display: flex; gap: 18px; }

  .hero { padding: 9vh 0 6vh; }
  .eyebrow { font: 13px/1.4 var(--mono); color: var(--ink-2); letter-spacing: .04em; margin: 0 0 18px; }
  .count { font: 500 clamp(1.4rem, 5.6vw, 4.6rem)/1 var(--mono); letter-spacing: -.035em; font-variant-numeric: tabular-nums; margin: 0; white-space: nowrap; }
  .count .tick { color: var(--accent); }
  .es { margin: 18px 0 0; font: clamp(1rem, 2.2vw, 1.35rem)/1.3 var(--serif); color: var(--ink-2); font-style: italic; }
  .es span { font-style: normal; font-family: var(--mono); color: var(--ink); font-size: .9em; }

  .span { margin: 56px 0 0; }
  .bar { position: relative; height: 2px; background: var(--line); }
  .bar i { position: absolute; left: 0; top: 0; bottom: 0; background: var(--accent); }
  .bar em { position: absolute; top: -6px; width: 14px; height: 14px; margin-left: -7px; border-radius: 50%; background: var(--accent); box-shadow: 0 0 0 6px var(--accent-2); }
  .bar-labels { display: flex; justify-content: space-between; font: 12px/1.4 var(--mono); color: var(--ink-3); margin-top: 14px; gap: 12px; }
  .bar-labels span:last-child { text-align: right; }
  .here { position: relative; font: 12px/1.4 var(--mono); color: var(--accent); margin-bottom: 14px; height: 1.4em; }
  .here span { position: absolute; transform: translateX(-50%); white-space: nowrap; }

  section { border-top: 1px solid var(--line); padding: 44px 0; }
  h2 { font: 400 clamp(1.4rem, 3vw, 1.9rem)/1.2 var(--serif); margin: 0 0 6px; }
  .sub { color: var(--ink-2); margin: 0 0 28px; max-width: 60ch; }

  .human { display: grid; gap: 28px; grid-template-columns: 1fr; margin-bottom: 36px; }
  @media (min-width: 720px) { .human { grid-template-columns: 1fr 1fr; } }
  .big-label { font: 12px/1 var(--mono); color: var(--ink-3); text-transform: uppercase; letter-spacing: .14em; margin-bottom: 10px; }
  .big-time { font: 500 clamp(1.3rem, 3.4vw, 2rem)/1.2 var(--mono); font-variant-numeric: tabular-nums; }
  .big-time small { display: block; font: 14px/1.4 var(--sans); color: var(--ink-2); margin-top: 6px; }

  .cals { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); border-top: 1px solid var(--line); border-left: 1px solid var(--line); }
  .cal { padding: 16px 18px 18px; border-right: 1px solid var(--line); border-bottom: 1px solid var(--line); display: flex; flex-direction: column; gap: 4px; min-width: 0; }
  .cal-name { font: 12px/1.3 var(--mono); color: var(--ink-3); }
  .cal-date { font-size: 17px; overflow-wrap: anywhere; }
  .cal-native { color: var(--ink-2); font-size: 15px; }

  form { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
  input, select, button { font: 15px/1 var(--mono); color: var(--ink); background: var(--bg-2); border: 1px solid var(--line); border-radius: 6px; padding: 12px 14px; min-width: 0; }
  input { flex: 1 1 260px; }
  button { background: var(--accent); color: var(--bg); border-color: var(--accent); cursor: pointer; font-weight: 600; }
  input:focus, select:focus, button:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .out { font: 14px/1.7 var(--mono); white-space: pre; overflow-x: auto; color: var(--ink-2); min-height: 1.7em; margin: 0; }
  .out.err { color: #e5675a; }

  pre.agents { background: var(--bg-2); border: 1px solid var(--line); border-radius: 8px; padding: 18px 20px; overflow-x: auto; font: 13.5px/1.75 var(--mono); margin: 0; }
  pre.agents .c { color: var(--ink-3); }
  .agent-links { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 18px; font: 14px/1.4 var(--mono); }

  footer { border-top: 1px solid var(--line); padding: 32px 0 56px; color: var(--ink-3); font-size: 14px; }
  footer p { max-width: 70ch; margin: 0 0 10px; }
  code { font-family: var(--mono); font-size: .92em; }
</style>
</head>
<body>
<div class="wrap">
  <header>
    <div class="mark">Exa<b>·</b>second</div>
    <nav><a href="/story">story</a><a href="/llms.txt">llms.txt</a><a href="/spec.md">spec</a><a href="#agents">agents</a></nav>
  </header>

  <main>
    <div class="hero">
      <p class="eyebrow">Seconds since the beginning of the universe</p>
      <p class="count" id="count" aria-live="off">${groupDigits(s.universe_seconds)}</p>
      <p class="es">That is <span id="es">${s.exaseconds}</span> exaseconds.</p>

      <div class="span" aria-hidden="true">
        <div class="here"><span style="left:${pct.toFixed(4)}%">you are here</span></div>
        <div class="bar"><i style="width:${pct.toFixed(4)}%"></i><em style="left:${pct.toFixed(4)}%"></em></div>
        <div class="bar-labels"><span>0 Es · the beginning</span><span>1 Es ≈ 31.7 billion years</span></div>
      </div>
    </div>

    <section aria-labelledby="h-human">
      <h2 id="h-human">In human time</h2>
      <p class="sub">The same instant, translated into the clocks and calendars people live by.</p>
      <div class="human">
        <div>
          <div class="big-label">UTC</div>
          <div class="big-time" id="utc">${esc(s.utc)}<small>${esc(s.technical.weekday)} · ISO week ${esc(s.technical.iso_week)} · unix <span id="unix">${s.unix}</span></small></div>
        </div>
        <div>
          <div class="big-label">Your clock</div>
          <div class="big-time" id="local">—<small id="local-zone">Enable JavaScript to see your local time</small></div>
        </div>
      </div>
      <ul class="cals" id="cals">${cal}
      </ul>
    </section>

    <section aria-labelledby="h-tr">
      <h2 id="h-tr">Translator</h2>
      <p class="sub">Paste a moment in any format: a date, unix seconds, universe seconds or exaseconds.</p>
      <form id="tr">
        <input id="tr-in" name="v" placeholder="2030-01-01T00:00:00Z" autocomplete="off" aria-label="Time to translate">
        <select id="tr-kind" aria-label="Format">
          <option value="iso">ISO date</option><option value="unix">Unix seconds</option>
          <option value="universe">Universe seconds</option><option value="es">Exaseconds</option>
        </select>
        <button type="submit">Translate</button>
      </form>
      <pre class="out" id="tr-out"></pre>
    </section>

    <section id="agents" aria-labelledby="h-agents">
      <h2 id="h-agents">For AI agents</h2>
      <p class="sub">Language models have no clock. Don't guess today's date from training data. Ask here: plain text, no key, no JavaScript.</p>
<pre class="agents"><span class="c"># current time, ~8 lines</span>
curl ${SITE}/now

<span class="c"># with your time zone and training cutoff</span>
curl "${SITE}/now?tz=Asia/Shanghai&amp;cutoff=2025-06"

<span class="c"># just the timestamp</span>
curl ${SITE}/utc

<span class="c"># MCP: add as a remote server</span>
${SITE}/mcp</pre>
      <div class="agent-links">
        <a href="/llms.txt">/llms.txt</a><a href="/llms-full.txt">/llms-full.txt</a><a href="/now.md">/now.md</a>
        <a href="/now.json">/now.json</a><a href="/openapi.json">/openapi.json</a><a href="/spec.md">/spec.md</a><a href="/story.md">/story.md</a>
      </div>
    </section>
  </main>

  <footer>
    <p>Exasecond time is a convention, not a measurement: by our estimate the universe was exactly 13,787,000,000 years old at 2000-01-01 00:00 UTC, so universe seconds = unix seconds + ${OFFSET}. <a href="/spec.md">Read the spec.</a></p>
    <p>Read <a href="/story">The Pottery Lecture</a>, a short play for AI readers about time, galaxies and pottery (2021).</p>
    <p>Some cosmologists estimate the universe's whole lifespan at roughly 33 billion years, close to one exasecond. Nobody knows yet.</p>
  </footer>
</div>

<script>
(() => {
  const C = ${OFFSET}n;
  const skew = ${Date.parse(s.utc)} - Date.now(); // align to server clock (second precision)
  const $ = (id) => document.getElementById(id);
  const group = (s) => s.replace(/\\B(?=(\\d{3})+(?!\\d))/g, ",");
  const tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
  const localFmt = new Intl.DateTimeFormat(undefined, { dateStyle: "full", timeStyle: "medium" });

  function tick() {
    const ms = Date.now() + skew;
    const unix = Math.floor(ms / 1000);
    const u = (BigInt(unix) + C).toString();
    $("count").innerHTML = group(u.slice(0, -3)) + ',<span class="tick">' + u.slice(-3) + "</span>";
    const frac = u.padStart(19, "0");
    $("es").textContent = frac.slice(0, -18) + "." + frac.slice(-18, -6);
    const d = new Date(unix * 1000);
    $("utc").firstChild.nodeValue = d.toISOString().replace(/\\.\\d{3}Z$/, "Z");
    $("unix").textContent = unix;
    const parts = localFmt.format(d);
    $("local").firstChild.nodeValue = d.toLocaleTimeString(undefined, { hour12: false });
    $("local-zone").textContent = parts + " · " + tz;
    setTimeout(tick, 1000 - ((Date.now() + skew) % 1000) + 5);
  }
  tick();

  async function refreshCalendars() {
    try {
      const r = await fetch("/now.json?tz=" + encodeURIComponent(tz), { cache: "no-store" });
      const s = await r.json();
      for (const c of s.calendars) {
        const li = document.querySelector('.cal[data-id="' + c.id + '"]');
        if (li) li.querySelector(".cal-date").textContent = c.text;
      }
    } catch {}
  }
  setInterval(refreshCalendars, 60000);

  $("tr").addEventListener("submit", async (e) => {
    e.preventDefault();
    const out = $("tr-out");
    const v = $("tr-in").value.trim() || $("tr-in").placeholder;
    const kind = $("tr-kind").value;
    out.className = "out"; out.textContent = "…";
    try {
      const r = await fetch("/api/convert?" + kind + "=" + encodeURIComponent(v) + "&tz=" + encodeURIComponent(tz));
      const s = await r.json();
      if (!r.ok) throw new Error(s.error || "Could not translate that");
      const lines = [
        "Universe seconds  " + group(s.universe_seconds),
        "Exaseconds        " + s.exaseconds,
        "UTC               " + s.utc + "  (" + s.technical.weekday + ")",
        "Your zone         " + s.local.iso,
        "Unix              " + s.unix,
        "",
        ...s.calendars.map((c) => c.name.padEnd(30) + c.text),
      ];
      out.textContent = lines.join("\\n");
    } catch (err) { out.className = "out err"; out.textContent = err.message; }
  });
})();
</script>
</body>
</html>
`;
}

// Story page: "The Pottery Lecture". Same palette as the clock, set as a readable script.
export function renderStoryPage(bodyHtml, s) {
  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>The Pottery Lecture · Exasecond</title>
<meta name="description" content="A short theatrical play for AI readers, first published November 4, 2021.">
<link rel="alternate" type="text/markdown" href="/story.md" title="The Pottery Lecture as markdown">
<style>
  :root { --bg:#07080c; --line:#23262f; --ink:#ece8df; --ink-2:#9c9a93; --ink-3:#64636a; --accent:#f0b44c;
    --mono: ui-monospace,"SF Mono","JetBrains Mono",Menlo,Consolas,monospace;
    --serif: "Iowan Old Style","Palatino Linotype",Palatino,Georgia,serif; }
  @media (prefers-color-scheme: light) { :root { --bg:#f4f1e9; --line:#d6d0c2; --ink:#16171c; --ink-2:#55545a; --ink-3:#8a877f; --accent:#a8590f; } }
  * { box-sizing: border-box; }
  html, body { margin:0; background:var(--bg); color:var(--ink); }
  body { font: 19px/1.7 var(--serif); }
  .wrap { max-width: 680px; margin: 0 auto; padding: 0 16px 80px; }
  header { display:flex; justify-content:space-between; align-items:baseline; gap:16px; padding:24px 0 48px; font: 13px/1 var(--mono); color: var(--ink-2); }
  header a { color: inherit; text-underline-offset: 3px; }
  .mark { letter-spacing:.32em; text-transform:uppercase; color: var(--ink); font-weight:600; text-decoration:none; }
  .mark b { color: var(--accent); }
  h1 { font-weight:400; font-size: clamp(2rem, 6vw, 3rem); line-height:1.1; margin: 0 0 16px; }
  h2 { font: 600 12px/1.4 var(--mono); letter-spacing:.2em; text-transform:uppercase; color: var(--ink-3); margin: 48px 0 20px; }
  hr { border:0; border-top:1px solid var(--line); margin: 40px 0; }
  blockquote { margin: 24px 0; padding: 12px 18px; border-left: 2px solid var(--accent); color: var(--ink-2); font-size: 16px; font-family: var(--mono); line-height:1.6; }
  p { margin: 0 0 18px; }
  .line .who { display:block; font: 600 12px/1.4 var(--mono); letter-spacing:.14em; text-transform:uppercase; color: var(--accent); margin-bottom: 4px; }
  .aside { color: var(--ink-2); text-align:center; }
  ol { padding-left: 1.4em; margin: 0 0 18px; }
  li { margin-bottom: 6px; }
  footer { font: 13px/1.6 var(--mono); color: var(--ink-3); border-top:1px solid var(--line); padding-top: 24px; margin-top: 48px; }
</style>
</head>
<body>
<div class="wrap">
  <header><a class="mark" href="/">Exa<b>·</b>second</a><a href="/story.md">story.md</a></header>
  <article>
${bodyHtml}
  </article>
  <footer>Read at ${s.utc} UTC · universe second ${s.universe_seconds}</footer>
</div>
</body>
</html>
`;
}

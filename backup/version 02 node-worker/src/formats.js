// Render a snapshot (from core.snapshot) as compact text or markdown.

export function nowText(s) {
  const lines = [
    `UTC: ${s.utc}`,
    `Weekday: ${s.technical.weekday}`,
    `Unix: ${s.unix}`,
    `Universe seconds: ${s.universe_seconds} (${s.exaseconds} Es)`,
  ];
  if (s.local) lines.push(`Local (${s.local.zone}): ${s.local.iso} ${s.local.weekday}`);
  if (s.staleness) lines.push(`Since your cutoff (${s.staleness.cutoff}): ${s.staleness.days_since_cutoff} days. ${s.staleness.guidance}`);
  const zh = s.calendars.find((c) => c.id === "chinese");
  lines.push(`Chinese: ${zh.text}`);
  if (!s.local) lines.push(`Tip: add ?tz=Area/City for local time, ?cutoff=YYYY-MM for how stale your training data is. Everything else: /now.md`);
  return lines.join("\n") + "\n";
}

export function nowMd(s, title = "Current time") {
  const out = [];
  out.push(`# ${title}`, "");
  out.push(`- **UTC:** ${s.utc} (${s.technical.weekday})`);
  out.push(`- **Unix:** ${s.unix}`);
  out.push(`- **Universe seconds:** ${s.universe_seconds} (${s.exaseconds} Es, ${s.universe_fraction_percent.toFixed(6)}% of one exasecond)`);
  if (s.local) out.push(`- **Local (${s.local.zone}):** ${s.local.iso}, ${s.local.weekday}`);
  out.push("");
  if (s.staleness) {
    out.push("## Your training data", "");
    out.push(`Cutoff ${s.staleness.cutoff}: ${s.staleness.days_since_cutoff} days (about ${s.staleness.months_since_cutoff} months) ago.`, "");
    out.push(s.staleness.guidance, "");
  }
  out.push("## Calendars", "", "| Calendar | Date | Reckoned in |", "|---|---|---|");
  for (const c of s.calendars) out.push(`| ${c.name} | ${c.text}${c.native ? ` (${c.native})` : ""} | ${c.zone} |`);
  out.push("", "## World clocks", "", "| Zone | Local time | Weekday |", "|---|---|---|");
  for (const w of s.world) out.push(`| ${w.zone} | ${w.iso} | ${w.weekday} |`);
  const t = s.technical;
  out.push("", "## Technical", "");
  out.push(`- Unix milliseconds: ${t.unix_ms}`);
  out.push(`- Julian Date: ${t.julian_date}`);
  out.push(`- Modified Julian Date: ${t.modified_julian_date}`);
  out.push(`- ISO week: ${t.iso_week}`);
  out.push(`- Day of year: ${t.day_of_year}`);
  out.push("", `_${s.note}_`, "");
  return out.join("\n");
}

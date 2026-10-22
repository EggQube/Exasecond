// Exasecond: the current time for AI agents. No dependencies (Node 18+, Deno, Bun, browsers). https://exasecond.com/developers
// (c) 2026 EggQube, exasecond.com. MIT License: free to use, copy and modify; keep this notice. https://exasecond.com/dev/LICENSE

/** One sentence with the weekday, date, local time and part of day for a place (city, country, zone or offset). */
export async function getLocalTime(place = "") {
  const url = "https://exasecond.com/local" + (place ? "?tz=" + encodeURIComponent(place) : "");
  return (await (await fetch(url)).text()).trim();
}

/** The current time as an ISO 8601 UTC timestamp, e.g. 2026-10-22T08:15:00Z. */
export async function getUtc() {
  return (await (await fetch("https://exasecond.com/utc")).text()).trim();
}

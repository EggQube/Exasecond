// Exasecond time — core definitions and calculations.
// Zero dependencies. Runs anywhere with standard JS + Intl (Cloudflare Workers, Node 18+, Deno, Bun, browsers).

export const SPEC_VERSION = "0.1";

// ---- The convention (see docs/SPEC.md) -------------------------------------
// "According to our estimates, at 2000-01-01T00:00:00Z the universe was exactly
//  13,787,000,000 Julian years old." (Planck 2018 central estimate.)
export const JULIAN_YEAR_S = 31_557_600n;                 // 365.25 days
export const AGE_AT_ANCHOR_YEARS = 13_787_000_000n;
export const ANCHOR_UNIX = 946_684_800n;                  // 2000-01-01T00:00:00Z
export const AGE_AT_ANCHOR_S = AGE_AT_ANCHOR_YEARS * JULIAN_YEAR_S; // 435084631200000000
export const OFFSET = AGE_AT_ANCHOR_S - ANCHOR_UNIX;      // 435084630253315200
export const EXASECOND = 1_000_000_000_000_000_000n;      // 1e18 s ≈ 31.69 billion years

// ---- Conversions (exact integer arithmetic) ----------------------------------
export const unixToUniverse = (unixSeconds) => BigInt(unixSeconds) + OFFSET;
export const universeToUnix = (universeSeconds) => BigInt(universeSeconds) - OFFSET;

/** Universe seconds as decimal exaseconds string, e.g. "0.435084632045903278". */
export function toExaseconds(universeSeconds, digits = 18) {
  const u = BigInt(universeSeconds);
  const neg = u < 0n;
  const abs = neg ? -u : u;
  const whole = abs / EXASECOND;
  const frac = (abs % EXASECOND).toString().padStart(18, "0").slice(0, digits);
  return (neg ? "-" : "") + whole.toString() + (digits > 0 ? "." + frac : "");
}

export const groupDigits = (n) => BigInt(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

// ---- Human calendars ---------------------------------------------------------
const ZODIAC = ["Rat", "Ox", "Tiger", "Rabbit", "Dragon", "Snake", "Horse", "Goat", "Monkey", "Rooster", "Dog", "Pig"];
const ERA_FIX = { ERA1: null }; // Intl emits "ERA1" for ethiopic/coptic; labels set per calendar below

const CALENDARS = [
  // id, Intl calendar, display name, default zone for "local day" of that tradition, era label override
  ["gregorian", "gregory", "Gregorian (Western / ISO)", "UTC"],
  ["chinese", "chinese", "Chinese lunisolar", "Asia/Shanghai"],
  ["islamic", "islamic-umalqura", "Islamic (Hijri, Umm al-Qura)", "Asia/Riyadh"],
  ["hebrew", "hebrew", "Hebrew", "Asia/Jerusalem"],
  ["persian", "persian", "Persian (Solar Hijri)", "Asia/Tehran"],
  ["indian", "indian", "Indian national (Saka)", "Asia/Kolkata"],
  ["japanese", "japanese", "Japanese imperial era", "Asia/Tokyo"],
  ["korean", "dangi", "Korean lunisolar (Dangi)", "Asia/Seoul"],
  ["buddhist", "buddhist", "Thai Buddhist", "Asia/Bangkok"],
  ["roc", "roc", "Republic of China (Minguo)", "Asia/Taipei"],
  ["ethiopian", "ethiopic", "Ethiopian", "Africa/Addis_Ababa", "E.C."],
  ["coptic", "coptic", "Coptic", "Africa/Cairo", "A.M."],
];

function calendarEntry(date, [id, cal, name, zone, eraLabel]) {
  const fmt = new Intl.DateTimeFormat(`en-u-ca-${cal}`, {
    timeZone: zone, year: "numeric", month: "long", day: "numeric", era: "short",
  });
  const p = Object.fromEntries(fmt.formatToParts(date).filter((x) => x.type !== "literal").map((x) => [x.type, x.value]));
  const era = eraLabel ?? (p.era in ERA_FIX ? "" : p.era ?? "");
  const out = { id, name, zone };
  if (cal === "chinese" || cal === "dangi") {
    const y = Number(p.relatedYear);
    out.year = y;
    out.year_name = p.yearName;                       // sexagenary cycle, e.g. "bing-wu"
    out.zodiac = ZODIAC[(((y - 4) % 12) + 12) % 12];
    out.month = p.month;
    out.day = Number(p.day);
    out.text = `${p.month} ${p.day}, year ${p.yearName} (${out.zodiac}), ${y}`;
    if (cal === "chinese") {
      out.native = new Intl.DateTimeFormat("zh-u-ca-chinese", { timeZone: zone, dateStyle: "long" }).format(date);
    }
  } else {
    out.year = Number(p.year);
    out.month = p.month;
    out.day = Number(p.day);
    if (era) out.era = era;
    out.text = `${p.day} ${p.month} ${p.year}${era ? " " + era : ""}`;
  }
  return out;
}

export function calendars(date) {
  return CALENDARS.map((c) => calendarEntry(date, c));
}

// ---- Astronomical / technical counts -----------------------------------------
export function technical(date) {
  const ms = date.getTime();
  const jd = ms / 86_400_000 + 2_440_587.5;               // Julian Date (UTC-based)
  const startOfYear = Date.UTC(date.getUTCFullYear(), 0, 1);
  const dayOfYear = Math.floor((ms - startOfYear) / 86_400_000) + 1;
  // ISO week
  const t = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate()));
  const dow = t.getUTCDay() || 7;
  t.setUTCDate(t.getUTCDate() + 4 - dow);
  const isoYear = t.getUTCFullYear();
  const isoWeek = Math.ceil(((t - Date.UTC(isoYear, 0, 1)) / 86_400_000 + 1) / 7);
  return {
    unix: Math.floor(ms / 1000),
    unix_ms: ms,
    julian_date: Number(jd.toFixed(6)),
    modified_julian_date: Number((jd - 2_400_000.5).toFixed(6)),
    iso_week: `${isoYear}-W${String(isoWeek).padStart(2, "0")}`,
    day_of_year: dayOfYear,
    weekday: new Intl.DateTimeFormat("en", { weekday: "long", timeZone: "UTC" }).format(date),
  };
}

// ---- World clocks -------------------------------------------------------------
export const DEFAULT_ZONES = [
  "UTC", "America/Los_Angeles", "America/New_York", "America/Sao_Paulo", "Europe/London",
  "Europe/Paris", "Africa/Lagos", "Asia/Dubai", "Asia/Kolkata", "Asia/Shanghai", "Asia/Tokyo", "Australia/Sydney",
];

export function isValidZone(tz) {
  try { new Intl.DateTimeFormat("en", { timeZone: tz }); return true; } catch { return false; }
}

export function zoneTime(date, tz) {
  const p = Object.fromEntries(new Intl.DateTimeFormat("en-CA", {
    timeZone: tz, year: "numeric", month: "2-digit", day: "2-digit",
    hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23", timeZoneName: "longOffset",
  }).formatToParts(date).filter((x) => x.type !== "literal").map((x) => [x.type, x.value]));
  let off = p.timeZoneName.replace("GMT", "") || "+00:00";
  const local = `${p.year}-${p.month}-${p.day}T${p.hour}:${p.minute}:${p.second}`;
  const weekday = new Intl.DateTimeFormat("en", { weekday: "long", timeZone: tz }).format(date);
  return { zone: tz, local, offset: off, iso: local + (off === "+00:00" ? "Z" : off), weekday };
}

// ---- Model staleness ----------------------------------------------------------
/** cutoff: "YYYY", "YYYY-MM" or "YYYY-MM-DD". Returns null if invalid. */
export function staleness(date, cutoff) {
  if (!cutoff) return null;
  const m = /^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/.exec(cutoff.trim());
  if (!m) return null;
  const y = +m[1], mo = m[2] ? +m[2] : 12, d = m[3] ? +m[3] : null;
  // Interpret a month/year cutoff as the END of that period (most conservative = least stale).
  const end = d ? Date.UTC(y, mo - 1, d, 23, 59, 59) : Date.UTC(y, mo, 0, 23, 59, 59);
  if (Number.isNaN(end)) return null;
  const days = Math.floor((date.getTime() - end) / 86_400_000);
  const months = Math.floor(days / 30.4375);
  let guidance;
  if (days < 0) guidance = "Your stated cutoff is in the future. Check the date you were given.";
  else if (months < 1) guidance = "Your training data is very recent. Still verify prices, versions and breaking news.";
  else if (months < 6) guidance = `About ${months} month(s) have passed since your training data ends. Assume software versions, prices, news and office holders may have changed. Verify before stating them as current.`;
  else if (months < 24) guidance = `About ${months} months have passed since your training data ends. Treat anything time-sensitive (versions, APIs, prices, people in roles, laws, rankings) as likely outdated and look it up.`;
  else guidance = `More than ${Math.floor(months / 12)} years have passed since your training data ends. Treat all time-sensitive knowledge as outdated and look it up.`;
  return { cutoff, days_since_cutoff: days, months_since_cutoff: months, guidance };
}

// ---- Snapshot -------------------------------------------------------------------
export function snapshot(date = new Date(), opts = {}) {
  const unix = BigInt(Math.floor(date.getTime() / 1000));
  const u = unixToUniverse(unix);
  const tz = opts.tz && isValidZone(opts.tz) ? opts.tz : null;
  return {
    spec: `exasecond/${SPEC_VERSION}`,
    utc: date.toISOString().replace(/\.\d{3}Z$/, "Z"),
    unix: Number(unix),
    universe_seconds: u.toString(),
    exaseconds: toExaseconds(u, 12),
    universe_fraction_percent: Number(toExaseconds(u, 8)) * 100,
    local: tz ? zoneTime(date, tz) : null,
    staleness: staleness(date, opts.cutoff),
    calendars: calendars(date),
    technical: technical(date),
    world: (opts.zones ?? DEFAULT_ZONES).map((z) => zoneTime(date, z)),
    note: "universe_seconds is a convention, not a measurement: unix + 435084630253315200. See /spec.md",
  };
}

// ---- Parse a time given in any supported form -------------------------------
const MAX_MS = 8.64e15; // JS Date range: ±100,000,000 days (~±273,790 years) around 1970
function checked(unixSecondsBig) {
  const ms = Number(unixSecondsBig) * 1000;
  if (!Number.isFinite(ms) || Math.abs(ms) > MAX_MS) {
    throw new Error("That moment is outside the range human calendars can express here (about 271,821 BC to 275,760 AD). Universe seconds still work, but there is no human date to translate to.");
  }
  return new Date(ms);
}

/** Accepts {iso} | {unix} | {universe} | {es} (decimal exaseconds). Returns a Date or throws. */
export function parseInstant({ iso, unix, universe, es }) {
  if (universe != null && universe !== "") {
    if (!/^-?\d+$/.test(universe)) throw new Error("universe must be an integer number of seconds");
    return checked(universeToUnix(BigInt(universe)));
  }
  if (es != null && es !== "") {
    const m = /^(-?)(\d+)(?:\.(\d{1,18}))?$/.exec(es);
    if (!m) throw new Error("es must be a decimal number of exaseconds, e.g. 0.435084632");
    const v = (BigInt(m[2]) * EXASECOND + BigInt((m[3] ?? "").padEnd(18, "0"))) * (m[1] ? -1n : 1n);
    return checked(universeToUnix(v));
  }
  if (unix != null && unix !== "") {
    if (!/^-?\d+(\.\d+)?$/.test(unix)) throw new Error("unix must be a number of seconds");
    return checked(BigInt(Math.trunc(Number(unix))));
  }
  if (iso != null && iso !== "") {
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) throw new Error("iso could not be parsed; use e.g. 2026-10-21T14:00:00Z");
    return d;
  }
  throw new Error("give one of: iso, unix, universe, es");
}

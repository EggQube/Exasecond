<?php
// Exasecond time: core definitions and calculations (PHP port of src/core.js).
// Needs PHP 8.0+ with 64-bit integers. The intl extension adds the human calendars; without it only Gregorian is shown.
defined('EXA') or exit;

const SPEC_VERSION = '0.1';

// ---- The convention (see /spec.md) -------------------------------------------
// "According to our estimates, at 2000-01-01T00:00:00Z the universe was exactly
//  13,787,000,000 Julian years old." (Planck 2018 central estimate.)
const JULIAN_YEAR_S = 31557600;                       // 365.25 days
const AGE_AT_ANCHOR_YEARS = 13787000000;
const ANCHOR_UNIX = 946684800;                        // 2000-01-01T00:00:00Z
const AGE_AT_ANCHOR_S = 435084631200000000;           // AGE_AT_ANCHOR_YEARS * JULIAN_YEAR_S
const OFFSET = 435084630253315200;                    // AGE_AT_ANCHOR_S - ANCHOR_UNIX
const EXASECOND = 1000000000000000000;                // 1e18 s, about 31.69 billion years
const MAX_UNIX = 8640000000000;                       // human-date range: about 271,821 BC to 275,760 AD (same as JS Date)

function unix_to_universe(int $unix): int { return $unix + OFFSET; }
function universe_to_unix(int $u): int { return $u - OFFSET; }

/** Universe seconds as decimal exaseconds string, e.g. "0.435084632045903278". */
function to_exaseconds(int $u, int $digits = 18): string {
    $neg = $u < 0;
    $abs = $neg ? -$u : $u;
    $whole = intdiv($abs, EXASECOND);
    $frac = substr(str_pad((string)($abs % EXASECOND), 18, '0', STR_PAD_LEFT), 0, $digits);
    return ($neg ? '-' : '') . $whole . ($digits > 0 ? '.' . $frac : '');
}

function group_digits($n): string {
    return preg_replace('/\B(?=(\d{3})+(?!\d))/', ',', (string)$n);
}

// ---- Instants ------------------------------------------------------------------
// An instant is [unix seconds (int), milliseconds 0..999 (int)].

function now_instant(): array {
    $t = microtime(true);
    $s = (int)floor($t);
    return [$s, min(999, (int)floor(($t - $s) * 1000))];
}

function dt_utc(int $unix): DateTimeImmutable {
    return (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone('UTC'));
}

/** ISO 8601 like JS toISOString() without milliseconds: 4-digit years, or signed 6-digit outside 0..9999. */
function iso_utc(int $unix): string {
    $d = dt_utc($unix);
    $y = (int)$d->format('Y');
    $ys = ($y >= 0 && $y <= 9999) ? str_pad((string)$y, 4, '0', STR_PAD_LEFT) : (($y < 0 ? '-' : '+') . str_pad((string)abs($y), 6, '0', STR_PAD_LEFT));
    return $ys . $d->format('-m-d\TH:i:s\Z');
}

// ---- Human calendars -------------------------------------------------------------
const ZODIAC = ['Rat', 'Ox', 'Tiger', 'Rabbit', 'Dragon', 'Snake', 'Horse', 'Goat', 'Monkey', 'Rooster', 'Dog', 'Pig'];

// id, ICU calendar, display name, zone for the "local day" of that tradition, era label override
const CALENDARS = [
    ['gregorian', 'gregorian', 'Gregorian (Western / ISO)', 'UTC', null],
    ['chinese', 'chinese', 'Chinese lunisolar', 'Asia/Shanghai', null],
    ['islamic', 'islamic-umalqura', 'Islamic (Hijri, Umm al-Qura)', 'Asia/Riyadh', null],
    ['hebrew', 'hebrew', 'Hebrew', 'Asia/Jerusalem', null],
    ['persian', 'persian', 'Persian (Solar Hijri)', 'Asia/Tehran', null],
    ['indian', 'indian', 'Indian national (Saka)', 'Asia/Kolkata', null],
    ['japanese', 'japanese', 'Japanese imperial era', 'Asia/Tokyo', null],
    ['korean', 'dangi', 'Korean lunisolar (Dangi)', 'Asia/Seoul', null],
    ['buddhist', 'buddhist', 'Thai Buddhist', 'Asia/Bangkok', null],
    ['roc', 'roc', 'Republic of China (Minguo)', 'Asia/Taipei', null],
    ['ethiopian', 'ethiopic', 'Ethiopian', 'Africa/Addis_Ababa', 'E.C.'],
    ['coptic', 'coptic', 'Coptic', 'Africa/Cairo', 'A.M.'],
];

function icu_format(int $unix, string $locale, string $zone, string $pattern): string {
    $f = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $zone, IntlDateFormatter::TRADITIONAL, $pattern);
    return (string)$f->format($unix);
}

function calendar_entry(int $unix, array $c): array {
    [$id, $cal, $name, $zone, $eraLabel] = $c;
    $out = ['id' => $id, 'name' => $name, 'zone' => $zone];
    $loc = 'en@calendar=' . $cal;
    if ($cal === 'chinese' || $cal === 'dangi') {
        $y = (int)icu_format($unix, $loc, $zone, 'r');
        $month = icu_format($unix, $loc, $zone, 'MMMM');
        $day = (int)icu_format($unix, $loc, $zone, 'd');
        $out['year'] = $y;
        $out['year_name'] = icu_format($unix, $loc, $zone, 'U');   // sexagenary cycle, e.g. "bing-wu"
        $out['zodiac'] = ZODIAC[((($y - 4) % 12) + 12) % 12];
        $out['month'] = $month;
        $out['day'] = $day;
        $out['text'] = "$month $day, year {$out['year_name']} ({$out['zodiac']}), $y";
        if ($cal === 'chinese') {
            $f = new IntlDateFormatter('zh@calendar=chinese', IntlDateFormatter::LONG, IntlDateFormatter::NONE, $zone, IntlDateFormatter::TRADITIONAL);
            $out['native'] = (string)$f->format($unix);
        }
    } else {
        $year = icu_format($unix, $loc, $zone, 'y');
        $month = icu_format($unix, $loc, $zone, 'MMMM');
        $day = icu_format($unix, $loc, $zone, 'd');
        $era = $eraLabel ?? icu_format($unix, $loc, $zone, 'G');
        $out['year'] = (int)$year;
        $out['month'] = $month;
        $out['day'] = (int)$day;
        if ($era !== '') $out['era'] = $era;
        $out['text'] = "$day $month $year" . ($era !== '' ? " $era" : '');
    }
    return $out;
}

function calendars(int $unix): array {
    if (!extension_loaded('intl')) {
        $d = dt_utc($unix);
        return [[
            'id' => 'gregorian', 'name' => 'Gregorian (Western / ISO)', 'zone' => 'UTC',
            'year' => (int)$d->format('Y'), 'month' => $d->format('F'), 'day' => (int)$d->format('j'),
            'era' => (int)$d->format('Y') > 0 ? 'AD' : 'BC', 'text' => $d->format('j F Y') . ' AD',
        ]];
    }
    return array_map(fn($c) => calendar_entry($unix, $c), CALENDARS);
}

/** Calendar dates written natively in Simplified Chinese (for the /zh pages). Keyed by calendar id. */
function calendars_zh(int $unix): array {
    if (!extension_loaded('intl')) return [];
    $out = [];
    foreach (CALENDARS as [$id, $cal, , $zone]) {
        $f = new IntlDateFormatter('zh_Hans@calendar=' . $cal, IntlDateFormatter::LONG, IntlDateFormatter::NONE, $zone, IntlDateFormatter::TRADITIONAL);
        $out[$id] = (string)$f->format($unix);
    }
    return $out;
}

// ---- Astronomical / technical counts ---------------------------------------------
function technical(int $unix, int $ms = 0): array {
    $d = dt_utc($unix);
    $jd = ($unix * 1000 + $ms) / 86400000 + 2440587.5;   // Julian Date (UTC-based)
    return [
        'unix' => $unix,
        'unix_ms' => $unix * 1000 + $ms,
        'julian_date' => round($jd, 6),
        'modified_julian_date' => round($jd - 2400000.5, 6),
        'iso_week' => $d->format('o') . '-W' . $d->format('W'),
        'day_of_year' => (int)$d->format('z') + 1,
        'weekday' => $d->format('l'),
    ];
}

// ---- World clocks ------------------------------------------------------------------
const DEFAULT_ZONES = [
    'UTC', 'America/Los_Angeles', 'America/New_York', 'America/Sao_Paulo', 'Europe/London',
    'Europe/Paris', 'Africa/Lagos', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Shanghai', 'Asia/Tokyo', 'Australia/Sydney',
];

function is_valid_zone(?string $tz): bool {
    if ($tz === null || $tz === '' || strlen($tz) > 64) return false;
    static $ids = null;
    if ($ids === null) $ids = array_flip(DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC));
    return isset($ids[$tz]) || $tz === 'UTC';
}

function zone_time(int $unix, string $tz): array {
    $d = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone($tz));
    $local = $d->format('Y-m-d\TH:i:s');
    $secs = $d->getOffset();   // keep seconds for historical local mean time offsets, e.g. +08:05:43 (as JS does)
    $a = abs($secs);
    $off = ($secs < 0 ? '-' : '+') . sprintf('%02d:%02d', intdiv($a, 3600), intdiv($a % 3600, 60)) . ($a % 60 ? sprintf(':%02d', $a % 60) : '');
    return ['zone' => $tz, 'local' => $local, 'offset' => $off, 'iso' => $local . ($off === '+00:00' ? 'Z' : $off), 'weekday' => $d->format('l')];
}

// ---- Model staleness --------------------------------------------------------------------
/** cutoff: "YYYY", "YYYY-MM" or "YYYY-MM-DD". Returns null if invalid. */
function staleness(int $unix, int $ms, ?string $cutoff): ?array {
    if ($cutoff === null || $cutoff === '') return null;
    if (!preg_match('/^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?$/', trim($cutoff), $m)) return null;
    $y = (int)$m[1];
    $mo = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 12;
    $dd = isset($m[3]) && $m[3] !== '' ? (int)$m[3] : null;
    // Interpret a month/year cutoff as the END of that period (most conservative = least stale).
    $end = $dd !== null ? gmmktime(23, 59, 59, $mo, $dd, $y) : gmmktime(23, 59, 59, $mo + 1, 0, $y);
    $days = (int)floor((($unix * 1000 + $ms) - $end * 1000) / 86400000);
    $months = (int)floor($days / 30.4375);
    if ($days < 0) $g = 'Your stated cutoff is in the future. Check the date you were given.';
    elseif ($months < 1) $g = 'Your training data is very recent. Still verify prices, versions and breaking news.';
    elseif ($months < 6) $g = "About $months month(s) have passed since your training data ends. Assume software versions, prices, news and office holders may have changed. Verify before stating them as current.";
    elseif ($months < 24) $g = "About $months months have passed since your training data ends. Treat anything time-sensitive (versions, APIs, prices, people in roles, laws, rankings) as likely outdated and look it up.";
    else $g = 'More than ' . intdiv($months, 12) . ' years have passed since your training data ends. Treat all time-sensitive knowledge as outdated and look it up.';
    return ['cutoff' => $cutoff, 'days_since_cutoff' => $days, 'months_since_cutoff' => $months, 'guidance' => $g];
}

// ---- Snapshot ---------------------------------------------------------------------------
function snapshot(?array $instant = null, array $opts = []): array {
    [$unix, $ms] = $instant ?? now_instant();
    $u = unix_to_universe($unix);
    $tz = isset($opts['tz']) && is_valid_zone($opts['tz']) ? $opts['tz'] : null;
    return [
        'spec' => 'exasecond/' . SPEC_VERSION,
        'utc' => iso_utc($unix),
        'unix' => $unix,
        'universe_seconds' => (string)$u,
        'exaseconds' => to_exaseconds($u, 12),
        'universe_fraction_percent' => (float)to_exaseconds($u, 8) * 100,
        'local' => $tz ? zone_time($unix, $tz) : null,
        'staleness' => staleness($unix, $ms, $opts['cutoff'] ?? null),
        'calendars' => calendars($unix),
        'technical' => technical($unix, $ms),
        'world' => array_map(fn($z) => zone_time($unix, $z), $opts['zones'] ?? DEFAULT_ZONES),
        'note' => 'universe_seconds is a convention, not a measurement: unix + 435084630253315200. See /spec.md',
    ];
}

// ---- Parse a time given in any supported form ----------------------------------------
class InputError extends Exception {}

function checked_unix(int $unix, int $ms = 0): array {
    if (abs($unix) > MAX_UNIX) {
        throw new InputError('That moment is outside the range human calendars can express here (about 271,821 BC to 275,760 AD). Universe seconds still work, but there is no human date to translate to.');
    }
    return [$unix, $ms];
}

function range_error(): InputError {
    try { checked_unix(PHP_INT_MAX); } catch (InputError $e) { return $e; }
    return new InputError('out of range');
}

/** Accepts ['iso'|'unix'|'universe'|'es' => string]. Returns an instant or throws InputError. */
function parse_instant(array $p): array {
    $v = fn($k) => isset($p[$k]) && is_scalar($p[$k]) && (string)$p[$k] !== '' ? (string)$p[$k] : null;
    if (($u = $v('universe')) !== null) {
        if (!preg_match('/^-?\d+$/', $u)) throw new InputError('universe must be an integer number of seconds');
        if (strlen(ltrim($u, '-')) > 18) throw range_error();
        return checked_unix(universe_to_unix((int)$u));
    }
    if (($es = $v('es')) !== null) {
        if (!preg_match('/^(-?)(\d+)(?:\.(\d{1,18}))?$/', $es, $m)) throw new InputError('es must be a decimal number of exaseconds, e.g. 0.435084632');
        if ($m[1] === '-' || ltrim($m[2], '0') !== '') throw range_error();   // only 0.x Es lies inside the human-date range
        $u = (int)str_pad($m[3] ?? '', 18, '0');
        return checked_unix(universe_to_unix($u));
    }
    if (($x = $v('unix')) !== null) {
        if (!preg_match('/^-?\d+(\.\d+)?$/', $x)) throw new InputError('unix must be a number of seconds');
        $int = explode('.', $x)[0];
        if (strlen(ltrim($int, '-')) > 15) throw range_error();
        return checked_unix((int)$int);
    }
    if (($iso = $v('iso')) !== null) {
        $ok = preg_match('/^[+-]?\d{4,6}(-\d{2}(-\d{2})?)?([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?)?(Z|[+-]\d{2}:?\d{2})?$/i', trim($iso));
        try { $d = $ok ? new DateTimeImmutable(trim($iso), new DateTimeZone('UTC')) : null; } catch (Exception $e) { $d = null; }
        if (!$d) throw new InputError('iso could not be parsed; use e.g. 2026-10-21T14:00:00Z');
        return checked_unix($d->getTimestamp(), (int)$d->format('v'));
    }
    throw new InputError('give one of: iso, unix, universe, es');
}

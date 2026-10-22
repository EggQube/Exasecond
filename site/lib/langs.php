<?php
// The one-sentence answer (/local, /<place>) in German, French, Spanish, Portuguese and Russian (?lang=de|fr|es|pt|ru).
// English and Chinese live in zones.php. The place comes first with a colon ("Oslo: ..."): prepositions with place
// names change with the place in these languages (au Brésil, en Suède, в Москве), and a colon is right for every place.
defined('EXA') or exit;

const SENTENCE_LANGS = ['en', 'zh', 'de', 'fr', 'es', 'pt', 'ru'];

const LANG_WORDS = [
    'de' => [
        'days' => ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
        'months' => ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        'others' => 'Andere Orte mit diesem Namen:',
    ],
    'fr' => [
        'days' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'],
        'months' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'others' => 'Autres lieux portant ce nom :',
    ],
    'es' => [
        'days' => ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'],
        'months' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'others' => 'Otros lugares con este nombre:',
    ],
    'pt' => [
        'days' => ['segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado', 'domingo'],
        'months' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
        'others' => 'Outros lugares com este nome:',
    ],
    'ru' => [
        'days' => ['понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'],
        'on_day' => ['в понедельник', 'во вторник', 'в среду', 'в четверг', 'в пятницу', 'в субботу', 'в воскресенье'],
        'months' => ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
        'others' => 'Другие места с этим названием:',
    ],
];

/** The part of day with the weekday, as people say it. $d is the local time, $prev the weekday before (for nights). */
function lang_day_phrase(string $lang, string $part, int $wd, int $prev): string {
    $W = LANG_WORDS[$lang]['days'];
    $day = $W[$wd];
    switch ($lang) {
        case 'de':
            $parts = ['early morning' => "am frühen {$day}morgen", 'morning' => "am {$day}vormittag", 'midday' => "am {$day}mittag",
                'afternoon' => "am {$day}nachmittag", 'evening' => "am {$day}abend", 'late evening' => "am späten {$day}abend"];
            return $part === 'night' ? "in der Nacht zum $day" : $parts[$part];
        case 'fr':
            return ['night' => "dans la nuit de {$W[$prev]} à $day", 'early morning' => "$day, tôt le matin", 'morning' => "$day matin",
                'midday' => "$day, vers midi", 'afternoon' => "$day après-midi", 'evening' => "$day soir", 'late evening' => "$day, tard le soir"][$part];
        case 'es':
            return ['night' => "$day de madrugada", 'early morning' => "$day, temprano por la mañana", 'morning' => "$day por la mañana",
                'midday' => "$day al mediodía", 'afternoon' => "$day por la tarde", 'evening' => "$day por la noche", 'late evening' => "$day, tarde por la noche"][$part];
        case 'pt':
            return ['night' => "madrugada de $day", 'early morning' => "$day de manhã cedo", 'morning' => "$day de manhã",
                'midday' => "$day por volta do meio-dia", 'afternoon' => "$day à tarde", 'evening' => "$day à noite", 'late evening' => "$day, tarde da noite"][$part];
        case 'ru':
            return ['night' => "$day, ночь", 'early morning' => "$day, раннее утро", 'morning' => "$day, утро", 'midday' => "$day, полдень",
                'afternoon' => "$day, день", 'evening' => "$day, вечер", 'late evening' => "$day, поздний вечер"][$part];
    }
    return '';
}

/** "22. Oktober 2026", "22 octobre 2026", "22 de octubre de 2026", "22 de outubro de 2026", "22 октября 2026 г." */
function lang_date(string $lang, DateTimeImmutable $d, bool $year = true): string {
    $m = LANG_WORDS[$lang]['months'][(int)$d->format('n') - 1];
    $j = $d->format('j');
    $y = $d->format('Y');
    switch ($lang) {
        case 'de': return "$j. $m" . ($year ? " $y" : '');
        case 'fr': return ($j === '1' ? '1er' : $j) . " $m" . ($year ? " $y" : '');
        case 'es': case 'pt': return "$j de $m" . ($year ? " de $y" : '');
        case 'ru': return "$j $m" . ($year ? " $y г." : '');
    }
    return '';
}

/** "1 Stunde", "eine heure", "30 минут": how much the clocks move. */
function lang_amount(string $lang, string $amount): string {
    preg_match('/^(\d+) (hour|hours|minutes)$/', $amount, $m);
    [$n, $unit] = [(int)($m[1] ?? 1), $m[2] ?? 'hour'];
    $hour = $unit !== 'minutes';
    switch ($lang) {
        case 'de': return $hour ? ($n === 1 ? 'eine Stunde' : ($n === 2 ? 'zwei Stunden' : "$n Stunden")) : "$n Minuten";
        case 'fr': return $hour ? ($n === 1 ? "d'une heure" : ($n === 2 ? 'de deux heures' : "de $n heures")) : "de $n minutes";
        case 'es': return $hour ? ($n === 1 ? 'una hora' : ($n === 2 ? 'dos horas' : "$n horas")) : "$n minutos";
        case 'pt': return $hour ? ($n === 1 ? 'uma hora' : ($n === 2 ? 'duas horas' : "$n horas")) : "$n minutos";
        case 'ru':
            if (!$hour) return "$n минут";
            $f = $n % 10 === 1 && $n % 100 !== 11 ? 'час' : ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20) ? 'часа' : 'часов');
            return "$n $f";
    }
    return $amount;
}

/** The clock-change sentence when a change is due within 14 days, else ''. */
function lang_change(string $lang, ?array $c, int $unix): string {
    if (!$c || strtotime($c['utc']) - $unix > 14 * 86400) return '';
    $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $c['local_before'], new DateTimeZone('UTC'));
    $wd = (int)$d->format('N') - 1;
    $W = LANG_WORDS[$lang]['days'];
    $date = lang_date($lang, $d, false);
    $back = $c['direction'] === 'back';
    $a = lang_amount($lang, $c['amount']);
    switch ($lang) {
        case 'de': return "Am {$W[$wd]}, $date, werden die Uhren um $a " . ($back ? 'zurückgestellt' : 'vorgestellt') . '.';
        case 'fr': return "Le {$W[$wd]} $date, les horloges " . ($back ? 'reculent' : 'avancent') . " $a.";
        case 'es': return "El {$W[$wd]} $date los relojes se " . ($back ? 'atrasan' : 'adelantan') . " $a.";
        case 'pt': return ($wd >= 5 ? 'No' : 'Na') . " {$W[$wd]}, $date, os relógios " . ($back ? 'atrasam' : 'adiantam') . " $a.";
        case 'ru': return mb_ucfirst_safe(LANG_WORDS['ru']['on_day'][$wd]) . ", $date, часы переводят на $a " . ($back ? 'назад' : 'вперёд') . '.';
    }
    return '';
}

function mb_ucfirst_safe(string $s): string {
    return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, null, 'UTF-8');
}

/** The sentence: "Oslo: Es ist 20:38 Uhr am Donnerstagabend (22. Oktober 2026, CEST, UTC+2). Am Sonntag, ..." */
function lang_sentence(string $lang, array $s, ?array $res): string {
    $l = $s['local'];
    if (!$l) return local_sentence($s, $res, 'en');   // no place: the English answer explains how to give one
    $d = (new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($l['zone']));
    $place = lang_place($lang, $l['zone'], $res);
    $off = short_offset($l['offset']);
    $wd = (int)$d->format('N') - 1;
    $phrase = lang_day_phrase($lang, $l['part_of_day'], $wd, ($wd + 6) % 7);
    $hm = $d->format('H:i');
    $inner = [lang_date($lang, $d)];
    if ($l['abbreviation'] && $l['abbreviation'] !== $off) $inner[] = $l['abbreviation'];
    if ($place !== $off && $place !== 'UTC') $inner[] = $off;
    $br = ' (' . implode(', ', $inner) . ')';
    switch ($lang) {
        case 'de': $out = "$place: Es ist $hm Uhr $phrase$br."; break;
        case 'fr': $out = "$place : il est " . $d->format('G') . ' h ' . $d->format('i') . ", $phrase$br."; break;
        case 'es': $out = "$place: es $phrase, " . ((int)$d->format('G') === 1 ? 'la ' : 'las ') . "$hm$br."; break;
        case 'pt': $out = "$place: é $phrase, " . $d->format('H') . 'h' . $d->format('i') . "$br."; break;
        case 'ru': $out = "$place: сейчас $phrase, $hm$br."; break;
        default: $out = '';
    }
    if ($change = lang_change($lang, $l['next_change'] ?? null, $s['unix'])) $out .= " $change";
    $others = [];
    foreach (($res['how'] ?? '') === 'place' ? $res['also'] : [] as $p) {
        $o = short_offset((new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($p['zone']))->format('P'));
        if ($o !== $off && count($others) < 3) $others[] = "{$p['name']}, " . lang_region($lang, $p['cc']) . " ($o)";
    }
    if ($others) $out .= ' ' . LANG_WORDS[$lang]['others'] . ' ' . implode('; ', $others) . '.';
    return $out;
}

/** A country's name in the language: Schweden, Suède, Suecia, Suécia, Швеция. */
function lang_region(string $lang, string $cc): string {
    return class_exists('Locale') ? (Locale::getDisplayRegion('-' . $cc, $lang) ?: $cc) : $cc;
}

/** A time zone's own city in the language, from ICU: Tokio, Moscou, Nueva York, Xangai, Нью-Йорк. Null if ICU has none. */
function lang_zone_city(string $lang, string $zone): ?string {
    if (!class_exists('IntlDateFormatter')) return null;
    $f = new IntlDateFormatter($lang, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $zone, null, 'VVV');
    $c = $f->format(0);
    return is_string($c) && $c !== '' && stripos($c, 'unknown') === false ? $c : null;
}

/** The place in the language: countries and zone cities translated; other places as typed, or by their own name. */
function lang_place(string $lang, string $zone, ?array $res): string {
    $how = $res['how'] ?? 'iana';
    $p = explode('/', $zone);
    $zoneCity = str_replace('_', ' ', end($p));
    if ($how === 'country' && isset($res['cc'])) return lang_region($lang, $res['cc']);
    if ($how === 'place') {
        $in = trim(preg_replace('/\s+/u', ' ', $res['input']));
        $name = preg_match('/^[\x20-\x7e]+$/', $in) ? $res['place']['name'] : $in;
        if (name_fold($name) === name_fold($zoneCity)) $name = lang_zone_city($lang, $zone) ?? $name;
        return $res['also'] ? "$name, " . lang_region($lang, $res['place']['cc']) : $name;
    }
    $base = place_name($zone, $res);
    return $base === $zoneCity ? (lang_zone_city($lang, $zone) ?? $base) : $base;
}

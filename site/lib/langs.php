<?php
// The one-sentence answer (/local, /<place>) in more languages (?lang=de|fr|es|pt|ru|sv|ja|ko).
// English and Chinese live in zones.php. The place comes first with a colon ("Oslo: ..."): prepositions with place
// names change with the place in these languages (au Brésil, en Suède, в Москве), and a colon is right for every place.
defined('EXA') or exit;

const SENTENCE_LANGS = ['en', 'zh', 'de', 'fr', 'es', 'pt', 'ru', 'sv', 'ja', 'ko'];

const LANG_WORDS = [
    'de' => [
        'days' => ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
        'months' => ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
        'others' => 'Andere Orte mit diesem Namen:', 'cities' => ['Delhi' => 'Delhi', 'Beijing' => 'Peking', 'Mumbai' => 'Mumbai', 'Lahore' => 'Lahore'], 'and' => ' und ',
    ],
    'fr' => [
        'days' => ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'],
        'months' => ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
        'others' => 'Autres lieux portant ce nom :', 'cities' => ['Delhi' => 'Delhi', 'Beijing' => 'Pékin', 'Mumbai' => 'Bombay', 'Lahore' => 'Lahore'], 'and' => ' et ',
    ],
    'es' => [
        'days' => ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'],
        'months' => ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'],
        'others' => 'Otros lugares con este nombre:', 'cities' => ['Delhi' => 'Delhi', 'Beijing' => 'Pekín', 'Mumbai' => 'Bombay', 'Lahore' => 'Lahore'], 'and' => ' y ',
    ],
    'pt' => [
        'days' => ['segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado', 'domingo'],
        'months' => ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'],
        'others' => 'Outros lugares com este nome:', 'cities' => ['Delhi' => 'Déli', 'Beijing' => 'Pequim', 'Mumbai' => 'Mumbai', 'Lahore' => 'Lahore'], 'and' => ' e ',
    ],
    'ru' => [
        'days' => ['понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'],
        'on_day' => ['в понедельник', 'во вторник', 'в среду', 'в четверг', 'в пятницу', 'в субботу', 'в воскресенье'],
        'months' => ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'],
        'others' => 'Другие места с этим названием:', 'cities' => ['Delhi' => 'Дели', 'Beijing' => 'Пекин', 'Mumbai' => 'Мумбаи', 'Lahore' => 'Лахор'], 'and' => ' и ',
    ],
    'sv' => [
        'days' => ['måndag', 'tisdag', 'onsdag', 'torsdag', 'fredag', 'lördag', 'söndag'],
        'months' => ['januari', 'februari', 'mars', 'april', 'maj', 'juni', 'juli', 'augusti', 'september', 'oktober', 'november', 'december'],
        'others' => 'Andra platser med samma namn:', 'cities' => ['Delhi' => 'Delhi', 'Beijing' => 'Peking', 'Mumbai' => 'Mumbai', 'Lahore' => 'Lahore'], 'and' => ' och ',
    ],
    'ja' => [
        'days' => ['月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日', '日曜日'],
        'others' => '同じ名前の場所：', 'cities' => ['Delhi' => 'デリー', 'Beijing' => '北京', 'Mumbai' => 'ムンバイ', 'Lahore' => 'ラホール'], 'and' => '、',
    ],
    'ko' => [
        'days' => ['월요일', '화요일', '수요일', '목요일', '금요일', '토요일', '일요일'],
        'others' => '같은 이름의 다른 장소:', 'cities' => ['Delhi' => '델리', 'Beijing' => '베이징', 'Mumbai' => '뭄바이', 'Lahore' => '라호르'], 'and' => ', ',
    ],
];

/** The part of day with the weekday, as people say it. $prev is the weekday before (for nights). */
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
        case 'sv':   // torsdag + s + kvällen = torsdagskvällen
            return ['night' => "natten till $day", 'early morning' => "tidigt på {$day}smorgonen", 'morning' => "på {$day}sförmiddagen",
                'midday' => "vid lunchtid på {$day}en", 'afternoon' => "på {$day}seftermiddagen", 'evening' => "på {$day}skvällen", 'late evening' => "sent på {$day}skvällen"][$part];
        case 'ja':
            return $day . 'の' . ['night' => '未明', 'early morning' => '早朝', 'morning' => '午前', 'midday' => '昼', 'afternoon' => '午後', 'evening' => '夜', 'late evening' => '深夜'][$part];
        case 'ko':
            return "$day " . ['night' => '새벽', 'early morning' => '이른 아침', 'morning' => '오전', 'midday' => '점심때', 'afternoon' => '오후', 'evening' => '저녁', 'late evening' => '늦은 밤'][$part];
    }
    return '';
}

/** "22. Oktober 2026", "22 octobre 2026", "22 de octubre de 2026", "22 октября 2026 г.", "22 oktober 2026", "2026年10月22日", "2026년 10월 22일" */
function lang_date(string $lang, DateTimeImmutable $d, bool $year = true): string {
    $j = $d->format('j');
    $n = $d->format('n');
    $y = $d->format('Y');
    if ($lang === 'ja') return ($year ? "{$y}年" : '') . "{$n}月{$j}日";
    if ($lang === 'ko') return ($year ? "{$y}년 " : '') . "{$n}월 {$j}일";
    $m = LANG_WORDS[$lang]['months'][(int)$n - 1];
    switch ($lang) {
        case 'de': return "$j. $m" . ($year ? " $y" : '');
        case 'fr': return ($j === '1' ? '1er' : $j) . " $m" . ($year ? " $y" : '');
        case 'es': return "$j de $m" . ($year ? " de $y" : '');
        case 'pt': return ($j === '1' ? '1º' : $j) . " de $m" . ($year ? " de $y" : '');
        case 'ru': return "$j $m" . ($year ? " $y г." : '');
        case 'sv': return "$j $m" . ($year ? " $y" : '');
    }
    return '';
}

/** "eine Stunde", "d'une heure", "30 минут", "en timme", "1時間", "1시간": how much the clocks move. */
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
        case 'sv': return $hour ? ($n === 1 ? 'en timme' : ($n === 2 ? 'två timmar' : "$n timmar")) : "$n minuter";
        case 'ja': return $hour ? "{$n}時間" : "{$n}分";
        case 'ko': return $hour ? "{$n}시간" : "{$n}분";
    }
    return $amount;
}

/** The clock-change sentence when a change is due within 14 days, else ''. */
function lang_change(string $lang, ?array $c, int $unix): string {
    if (!$c || strtotime($c['utc']) - $unix > 14 * 86400) return '';
    $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $c['local_before'], new DateTimeZone('UTC'));
    $wd = (int)$d->format('N') - 1;
    $W = LANG_WORDS[$lang]['days'];
    $back = $c['direction'] === 'back';
    $a = lang_amount($lang, $c['amount']);
    if ($lang === 'ja') return lang_on_day('ja', $c['local_before']) . "に時計が{$a}" . ($back ? '戻ります' : '進みます') . '。';
    if ($lang === 'ko') return lang_on_day('ko', $c['local_before']) . "에 시계를 {$a} " . ($back ? '뒤로' : '앞으로') . ' 돌립니다.';
    $date = lang_date($lang, $d, false);
    switch ($lang) {
        case 'de': return "Am {$W[$wd]}, $date, werden die Uhren um $a " . ($back ? 'zurückgestellt' : 'vorgestellt') . '.';
        case 'fr': return "Le {$W[$wd]} $date, les horloges " . ($back ? 'reculent' : 'avancent') . " $a.";
        case 'es': return "El {$W[$wd]} $date los relojes se " . ($back ? 'atrasan' : 'adelantan') . " $a.";
        case 'pt': return ($wd >= 5 ? 'No' : 'Na') . " {$W[$wd]}, $date, os relógios " . ($back ? 'atrasam' : 'adiantam') . " $a.";
        case 'ru': return mb_ucfirst_safe(LANG_WORDS['ru']['on_day'][$wd]) . ", $date, часы переводят на $a " . ($back ? 'назад' : 'вперёд') . '.';
        case 'sv': return mb_ucfirst_safe($W[$wd]) . " den $date ställs klockorna " . ($back ? 'tillbaka' : 'fram') . " $a.";
    }
    return '';
}

function mb_ucfirst_safe(string $s): string {
    return mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($s, 1, null, 'UTF-8');
}

/** The details in brackets: " (22. Oktober 2026, CEST, UTC+2)", or full-width in Japanese: "（2026年10月22日、JST、UTC+9）". */
function lang_brackets(string $lang, array $inner): string {
    return $lang === 'ja' ? '（' . implode('、', $inner) . '）' : ' (' . implode(', ', $inner) . ')';
}

/** "Oslo: Es ist 20:38 Uhr am Donnerstagabend (…)." for a place (or several), a local time and its part of day. */
function lang_line(string $lang, string $place, DateTimeImmutable $d, string $part, array $inner): string {
    $wd = (int)$d->format('N') - 1;
    $phrase = lang_day_phrase($lang, $part, $wd, ($wd + 6) % 7);
    $hm = $d->format('H:i');
    $br = lang_brackets($lang, $inner);
    switch ($lang) {
        case 'de': return "$place: Es ist $hm Uhr $phrase$br.";
        case 'fr': return "$place : il est " . $d->format('G') . ' h ' . $d->format('i') . ", $phrase$br.";
        case 'es': return "$place: es $phrase, " . ((int)$d->format('G') === 1 ? 'la ' : 'las ') . "$hm$br.";
        case 'pt': return "$place: é $phrase, " . $d->format('H') . 'h' . $d->format('i') . "$br.";
        case 'ru': return "$place: сейчас $phrase, $hm$br.";
        case 'sv': return "$place: Klockan är $hm $phrase$br.";
        case 'ja': return "{$place}：{$phrase}、{$hm}{$br}。";
        case 'ko': return "$place: $phrase $hm$br.";
    }
    return '';
}

/** A front-page city (a PLACES row) named in the language. Cities that are not their zone's own city (Beijing, Delhi,
 *  Mumbai, Lahore) come from the language's list; the rest from ICU (Tokio, Moscou); English and Chinese from PLACES. */
function place_display(array $p, string $lang): string {
    if ($lang === 'en') return $p[1];
    if ($lang === 'zh') return $p[2];
    $zoneCity = str_replace('_', ' ', substr(strrchr($p[0], '/'), 1));
    return LANG_WORDS[$lang]['cities'][$p[1]] ?? (name_fold($p[1]) === name_fold($zoneCity) ? lang_zone_city($lang, $p[0]) : null) ?? $p[1];
}

/** The front page's "Your time" list in the language: places grouped by identical local time. */
function lang_world_lines(int $unix, string $lang): array {
    $and = LANG_WORDS[$lang]['and'];
    $groups = place_groups($unix, fn($p) => place_display($p, $lang));
    $lines = [];
    foreach ($groups as $g) {
        $n = $g['names'];
        $who = count($n) > 1 ? implode(trim($and) === '、' ? '、' : ', ', array_slice($n, 0, -1)) . $and . end($n) : $n[0];
        $off = short_offset($g['d']->format('P'));
        $inner = $g['abbr'] !== '' ? [$g['abbr'], $off] : [$off];
        $line = lang_line($lang, $who, $g['d'], $g['det']['part_of_day'], $inner);
        if ($g['c']) $line .= ($lang === 'ja' ? '' : ' ') . lang_change($lang, $g['c'], $unix);
        $lines[] = $line;
    }
    return $lines;
}

/** The sentence: "Oslo: Es ist 20:38 Uhr am Donnerstagabend (22. Oktober 2026, CEST, UTC+2). Am Sonntag, ..." */
function lang_sentence(string $lang, array $s, ?array $res): string {
    $l = $s['local'];
    if (!$l) return local_sentence($s, $res, 'en');   // no place: the English answer explains how to give one
    $d = (new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($l['zone']));
    $place = lang_place($lang, $l['zone'], $res);
    $off = short_offset($l['offset']);
    $inner = [lang_date($lang, $d)];
    if ($l['abbreviation'] && $l['abbreviation'] !== $off) $inner[] = $l['abbreviation'];
    if ($place !== $off && $place !== 'UTC') $inner[] = $off;
    $sp = $lang === 'ja' ? '' : ' ';
    $out = lang_line($lang, $place, $d, $l['part_of_day'], $inner);
    if ($change = lang_change($lang, $l['next_change'] ?? null, $s['unix'])) $out .= $sp . $change;
    $others = [];
    foreach (($res['how'] ?? '') === 'place' ? $res['also'] : [] as $p) {
        $o = short_offset((new DateTimeImmutable('@' . $s['unix']))->setTimezone(new DateTimeZone($p['zone']))->format('P'));
        if ($o !== $off && count($others) < 3) $others[] = "{$p['name']}, " . lang_region($lang, $p['cc']) . " ($o)";
    }
    if ($others) $out .= $sp . LANG_WORDS[$lang]['others'] . ($lang === 'ja' ? '' : ' ') . implode('; ', $others) . ($lang === 'ja' ? '。' : '.');
    return $out;
}

/** A country's name in the language: Schweden, Suède, Швеция, Sverige, スウェーデン, 스웨덴. */
function lang_region(string $lang, string $cc): string {
    return class_exists('Locale') ? (Locale::getDisplayRegion('-' . $cc, $lang) ?: $cc) : $cc;
}

/** A time zone's own city in the language, from ICU: Tokio, Moscou, Nueva York, Нью-Йорк, 東京, 도쿄. Null if ICU has none. */
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

/** A day with its preposition, from a local wall-clock string: "am Sonntag, 25. Oktober", "le dimanche 25 octobre",
 *  "el domingo 25 de octubre", "no domingo, 25 de outubro", "в воскресенье, 25 октября", "söndag den 25 oktober",
 *  "10月25日（日曜日）", "10월 25일(일요일)". */
function lang_on_day(string $lang, string $localBefore): string {
    $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $localBefore, new DateTimeZone('UTC'));
    $wd = (int)$d->format('N') - 1;
    $W = LANG_WORDS[$lang]['days'][$wd];
    $date = lang_date($lang, $d, false);
    switch ($lang) {
        case 'de': return "am $W, $date";
        case 'fr': return "le $W $date";
        case 'es': return "el $W $date";
        case 'pt': return ($wd >= 5 ? 'no' : 'na') . " $W, $date";
        case 'ru': return LANG_WORDS['ru']['on_day'][$wd] . ", $date";
        case 'sv': return "$W den $date";
        case 'ja': return "{$date}（{$W}）";
        case 'ko': return "$date($W)";
    }
    return $date;
}

<?php
// "Did you know?" facts. General facts come from the chapters (each links to its chapter).
// When the visitor's zone is known, a place-specific fact is preferred: a live one computed from the
// time zone database (clock changes), or a fixed fact tagged with the visitor's country.
defined('EXA') or exit;

// [chapter slug, English, Chinese, country codes (empty = general)]
const FACTS = [
    ['scale', "The universe's first second spans more powers of ten than everything that has happened since.", '宇宙的第一秒所跨越的数量级，比此后发生的一切还要多。', []],
    ['scale', 'The shortest time interval ever measured is about 247 zeptoseconds: the time light takes to cross a hydrogen molecule.', '人类测量过的最短时间间隔约为 247 仄秒：光穿过一个氢分子所需的时间。', []],
    ['scale', "All of recorded history fits in the last 0.00004 percent of the universe's age.", '人类有文字记载的全部历史，只占宇宙年龄的最后 0.00004%。', []],
    ['second', "The word \"second\" comes from the \"second small part\" of an hour: the hour divided by sixty, and then by sixty again.", '英文的"秒"（second）一词来自小时的"第二次细分"：先除以六十，再除以六十。', []],
    ['second', 'One second is defined as exactly 9,192,631,770 oscillations of a caesium-133 atom.', '一秒被定义为铯 133 原子恰好 9,192,631,770 次振荡。', []],
    ['second', "The Moon's tides make the day about 1.8 milliseconds longer every century.", '月球的潮汐让一天的长度每个世纪大约增加 1.8 毫秒。', []],
    ['clocks', "Su Song's astronomical clock tower, completed in Kaifeng in 1092, was more than ten metres tall and driven by a water wheel.", '苏颂于 1092 年在开封建成的水运仪象台高十余米，由水轮驱动。', []],
    ['clocks', "John Harrison's sea watch H4 lost only 5.1 seconds on an 81-day voyage to Jamaica in 1761 and 1762.", '约翰·哈里森的航海表 H4 在 1761 至 1762 年前往牙买加的 81 天航行中只慢了 5.1 秒。', []],
    ['clocks', 'The best optical clocks would not gain or lose a second in the whole age of the universe.', '最好的光钟，在整个宇宙年龄里都不会快或慢一秒。', []],
    ['clocks', 'Most watches and phones keep time with a quartz crystal that vibrates 32,768 times a second.', '大多数手表和手机靠一块每秒振动 32,768 次的石英晶体计时。', []],
    ['timescales', 'The last leap second was added at the end of 2016, and leap seconds are to be abolished by 2035.', '最近一个闰秒加在 2016 年底，闰秒将在 2035 年前废止。', []],
    ['timescales', 'GPS time is 18 seconds ahead of UTC, because it has never added a leap second.', 'GPS 时比 UTC 快 18 秒，因为它从未加过闰秒。', []],
    ['timescales', 'On many older computers, time runs out at 03:14:07 UTC on 19 January 2038.', '在许多旧计算机上，时间会在 2038 年 1 月 19 日 03:14:07 UTC 用尽。', []],
    ['zones', 'All of China uses one time zone. In Kashgar, the Sun is at its highest around 3 in the afternoon, Beijing Time.', '全中国只用一个时区。在喀什，太阳在北京时间下午 3 点前后才升到最高。', ['CN']],
    ['zones', 'India keeps its clocks 5 hours and 30 minutes ahead of UTC, and Nepal 5 hours and 45 minutes.', '印度的时钟比 UTC 快 5 小时 30 分，尼泊尔则快 5 小时 45 分。', ['IN', 'NP']],
    ['timescales', "China's national time is kept at the National Time Service Center in Shaanxi, one of the laboratories that together define world time.", '中国的国家标准时间由位于陕西的国家授时中心保持，它是共同确定世界时间的实验室之一。', ['CN']],
    ['relativity', 'A clock raised by just one centimetre runs measurably faster. Optical clocks can detect it.', '把一台钟抬高一厘米，它就会明显变快。光钟能测出这一点。', []],
    ['relativity', 'GPS satellite clocks run about 38 microseconds a day fast. Without correction, positions would drift by about 10 kilometres a day.', 'GPS 卫星上的钟每天快约 38 微秒。如果不修正，定位每天会偏差约 10 公里。', []],
    ['relativity', 'Clocks on the Moon run about 56 microseconds a day faster than clocks on Earth.', '月球上的钟每天比地球上的快约 56 微秒。', []],
    ['arrow', 'Almost all the basic laws of physics work the same forwards and backwards in time. Entropy is why eggs never unbreak.', '几乎所有基本物理定律在时间的两个方向上都一样成立。鸡蛋碎了不会复原，是因为熵。', []],
    ['arrow', 'Erasing one bit of information has a minimum energy cost, as Rolf Landauer showed in 1961.', '擦除一个比特的信息有最低的能量代价，这是罗尔夫·兰道尔在 1961 年证明的。', []],
    ['history', 'The cosmic microwave background was released about 380,000 years after the beginning. It still fills all of space.', '宇宙微波背景在开端之后约 38 万年释放，至今仍充满整个空间。', []],
    ['history', 'The most distant galaxy confirmed so far, MoM-z14, is seen as it was about 280 million years after the beginning.', '目前已确认的最遥远星系 MoM-z14，我们看到的是它在开端之后约 2.8 亿年时的样子。', []],
    ['history', 'About a quarter of all ordinary matter became helium in the first 20 minutes of the universe.', '在宇宙最初的 20 分钟里，约四分之一的普通物质变成了氦。', []],
    ['age', 'Three independent methods put the universe at about 13.8 billion years, but two ways of measuring its expansion rate still disagree: the Hubble tension.', '三种独立方法都给出约 138 亿年的宇宙年龄，但测量其膨胀速度的两种方法至今仍有分歧：这就是哈勃张力。', []],
    ['age', 'Some stars can be dated with uranium, like carbon dating on a cosmic scale. HE 1523-0901 is about 13.2 billion years old.', '有些恒星可以用铀来测年，就像宇宙尺度上的碳测年。HE 1523-0901 约有 132 亿年。', []],
    ['beginning', 'The name "Big Bang" was coined by one of the theory\'s opponents, Fred Hoyle, on BBC radio in 1949.', '"大爆炸"这个名字是这一理论的反对者弗雷德·霍伊尔在 1949 年的 BBC 广播中起的。', []],
    ['beginning', 'Space is geometrically flat to within about 0.2 percent, one of the puzzles that inflation explains.', '空间在几何上平直到约 0.2% 以内，这是暴胀理论解释的谜题之一。', []],
    ['future', "In about a billion years the Sun will be bright enough to start evaporating Earth's oceans.", '大约 10 亿年后，太阳将亮到开始蒸发地球的海洋。', []],
    ['future', 'There is only about a 50 percent chance that the Milky Way and Andromeda merge within the next 10 billion years.', '银河系与仙女座星系在未来 100 亿年内并合的概率只有约 50%。', []],
    ['future', 'A black hole with the mass of the Sun would take about 10^67 years to evaporate.', '一个太阳质量的黑洞，约需 10^67 年才会蒸发殆尽。', []],
    ['calendars', "China's Shoushi calendar of 1281 used the same year length as the Gregorian calendar, three centuries earlier.", '中国 1281 年的《授时历》采用的回归年长度与格里高利历相同，却早了三个世纪。', ['CN', 'TW', 'HK', 'MO']],
    ['calendars', 'In 1582, Thursday 4 October was followed directly by Friday 15 October.', '1582 年，10 月 4 日星期四之后紧接着就是 10 月 15 日星期五。', []],
    ['calendars', 'Ramadan moves through all the seasons in about 33 years, because the Islamic year is 11 days shorter than the solar year.', '斋月大约每 33 年轮遍所有季节，因为伊斯兰历的一年比太阳年短 11 天。', ['SA', 'AE', 'EG', 'TR', 'PK', 'ID', 'MY', 'BD', 'IR', 'IQ', 'MA', 'DZ', 'JO', 'QA', 'KW', 'OM', 'BH']],
    ['calendars', 'Japan also counts years by imperial era. The current era, Reiwa, began on 1 May 2019.', '日本也按天皇年号纪年。现在的年号"令和"始于 2019 年 5 月 1 日。', ['JP']],
    ['calendars', "Iran's calendar starts each year at the exact moment of the spring equinox.", '伊朗的历法以春分的精确时刻作为每年的开始。', ['IR', 'AF']],
    ['calendars', 'The Ethiopian calendar has 13 months: twelve of 30 days and one of 5 or 6.', '埃塞俄比亚历有 13 个月：十二个 30 天的月，加一个 5 或 6 天的月。', ['ET']],
    ['calendars', 'The Chinese calendar adds a leap month about seven times every 19 years, guided by the 24 solar terms.', '农历大约每 19 年插入七个闰月，依据的是二十四节气。', ['CN', 'TW', 'HK', 'MO', 'SG', 'KR', 'VN']],
    ['nature', 'An astronaut who spends six months on the International Space Station comes home about five thousandths of a second younger.', '在国际空间站上待六个月的宇航员，回来时会年轻约千分之五秒。', []],
    ['nature', 'In 2017 physicists made the first time crystals: matter that repeats in time instead of in space.', '2017 年，物理学家造出了第一批时间晶体：在时间中而不是在空间中重复的物质。', []],
    ['nature', 'The central equation of quantum gravity, the Wheeler-DeWitt equation, contains no time at all.', '量子引力的核心方程，惠勒-德威特方程，里面根本没有时间。', []],
    ['machines', 'A language model only knows today\'s date if someone tells it.', '语言模型只有在被告知时，才知道今天的日期。', []],
    ['machines', "Google's Spanner database keeps GPS receivers and atomic clocks in every data centre.", '谷歌的 Spanner 数据库在每个数据中心都配备了 GPS 接收机和原子钟。', []],
    // Time zones
    ['zones', 'For two hours every day, three different calendar dates exist on Earth at the same time.', '每天有两个小时，地球上同时存在三个不同的日期。', []],
    ['zones', 'The world uses about 38 different offsets from UTC, spanning 26 hours from UTC-12 to UTC+14.', '世界上使用着约 38 种不同的 UTC 时差，从 UTC-12 到 UTC+14，跨度 26 小时。', []],
    ['zones', 'Samoa skipped Friday 30 December 2011 entirely, jumping from Thursday to Saturday to move across the date line.', '萨摩亚在 2011 年完全跳过了 12 月 30 日星期五，从星期四直接到星期六，移到了日期变更线的另一侧。', ['WS', 'NZ', 'AU']],
    ['zones', 'On 18 November 1883, "the day of two noons", the railroads of the United States and Canada switched to standard time zones.', '1883 年 11 月 18 日，即"有两个正午的一天"，美国和加拿大的铁路改用了标准时区。', ['US', 'CA']],
    ['zones', 'In north-eastern Arizona, the Navajo Nation uses daylight saving time, the Hopi Reservation inside it does not, and the rest of the state does not either.', '在亚利桑那州东北部，纳瓦霍族保留地实行夏令时，被它包围的霍皮族保留地不实行，该州其他地方也不实行。', ['US']],
    ['zones', 'Spain lies mostly west of Greenwich but has used Berlin\'s time since 1940, so its clocks run about an hour ahead of the Sun.', '西班牙大部分国土位于格林尼治以西，却自 1940 年起使用柏林的时间，所以它的时钟比太阳快约一小时。', ['ES']],
    ['zones', 'France has twelve time zones, more than any other country, thanks to its overseas territories.', '由于海外领地，法国共有十二个时区，比任何其他国家都多。', ['FR']],
    ['zones', 'Russia spans eleven time zones.', '俄罗斯横跨十一个时区。', ['RU']],
    ['zones', 'Kazakhstan put the whole country on one time zone, UTC+5, on 1 March 2024.', '哈萨克斯坦于 2024 年 3 月 1 日把全国统一为一个时区，UTC+5。', ['KZ']],
    ['zones', 'India uses one time zone across three thousand kilometres; in the far east, the summer Sun rises before 5 in the morning.', '印度用一个时区覆盖东西三千公里的国土；在最东部，夏天的太阳不到早上 5 点就升起了。', ['IN']],
    ['zones', 'Crossing the border between China and Afghanistan moves the clock by three and a half hours, the largest jump across any land border.', '越过中国与阿富汗的边界，时钟要拨动三个半小时，这是所有陆地边界上最大的时差跳变。', ['CN', 'AF']],
    ['zones', 'Britain made Greenwich Mean Time its legal time in 1880, after the railways had already adopted it in the 1840s.', '英国在 1880 年把格林尼治平时定为法定时间，而铁路早在 19 世纪 40 年代就已采用它。', ['GB', 'IE']],
    ['zones', 'The Netherlands kept Amsterdam time, about 20 minutes ahead of Greenwich, until 1940.', '荷兰一直使用比格林尼治快约 20 分钟的阿姆斯特丹时间，直到 1940 年。', ['NL', 'BE']],
    ['zones', 'The Line Islands of Kiribati, at UTC+14, are the first inhabited places on Earth to start each new day.', '基里巴斯的莱恩群岛位于 UTC+14，是地球上最先迎来每一个新日子的有人居住的地方。', ['KI']],
    // Daylight saving time
    ['dst', 'Daylight saving time was first adopted by Germany and Austria-Hungary on 30 April 1916, to save coal during the war.', '夏令时最早由德国和奥匈帝国于 1916 年 4 月 30 日采用，为的是在战争中节省煤炭。', []],
    ['dst', 'Benjamin Franklin never proposed daylight saving time. His 1784 letter about rising early was satire.', '本杰明·富兰克林从未提议夏令时。他 1784 年那封关于早起的信是一篇讽刺文章。', []],
    ['dst', 'Only about 70 of the world\'s countries change their clocks; most of the world never does.', '世界上只有约 70 个国家拨钟；世界上大部分地方从不拨钟。', []],
    ['dst', 'An entomologist in New Zealand, George Vernon Hudson, proposed daylight saving time in 1895, to have more light for collecting insects after work.', '新西兰昆虫学家乔治·弗农·赫德森在 1895 年提议夏令时，为的是下班后有更多日光采集昆虫。', ['NZ']],
    ['dst', 'A London builder, William Willett, campaigned for daylight saving time from 1907. He died in 1915, a year before it became law.', '伦敦建筑商威廉·威利特从 1907 年起倡导夏令时。他于 1915 年去世，一年后夏令时成为法律。', ['GB']],
    ['dst', 'China used daylight saving time from 1986 to 1991, then stopped.', '中国在 1986 年至 1991 年间实行过夏令时，之后停止了。', ['CN']],
    ['dst', 'Japan has used daylight saving time only once, under American occupation from 1948 to 1951.', '日本只实行过一次夏令时，是在美国占领期间的 1948 年至 1951 年。', ['JP']],
    ['dst', 'The US House of Representatives voted in July 2026 to make daylight saving time permanent. The bill still needs the Senate.', '美国众议院在 2026 年 7 月投票通过了永久夏令时法案，但该法案仍需参议院通过。', ['US']],
    ['dst', 'In 2018, 4.6 million people answered an EU consultation on clock changes, and 84 percent wanted them to end. The changes continue.', '2018 年，460 万人回复了欧盟关于拨钟的咨询，84% 希望停止拨钟。但拨钟仍在继续。', ['DE', 'FR', 'IT', 'ES', 'PL', 'NL', 'BE', 'AT', 'SE', 'DK', 'FI', 'PT', 'IE', 'CZ', 'GR', 'HU', 'RO', 'BG', 'HR', 'SK', 'SI', 'LT', 'LV', 'EE', 'LU', 'MT', 'CY']],
    ['dst', 'Russia tried permanent summer time in 2011, then switched to permanent standard time in 2014.', '俄罗斯在 2011 年尝试了永久夏令时，2014 年又改为永久标准时。', ['RU']],
    ['dst', 'Turkey has stayed on permanent summer time since 2016.', '土耳其自 2016 年起一直保持永久夏令时。', ['TR']],
    ['dst', 'Brazil stopped changing its clocks in 2019.', '巴西在 2019 年停止了拨钟。', ['BR']],
    ['dst', 'Mexico stopped changing its clocks in 2022, except near the US border.', '墨西哥在 2022 年停止了拨钟，美墨边境附近地区除外。', ['MX']],
    ['dst', 'Egypt has stopped and restarted daylight saving time several times; it resumed in 2023 to save energy.', '埃及多次停止又恢复夏令时；2023 年为节约能源再次恢复。', ['EG']],
    ['dst', 'In the southern hemisphere the clocks move the other way: when Europe falls back in October, Sydney has just sprung forward.', '南半球的时钟朝相反方向拨：欧洲在 10 月拨回时，悉尼刚刚拨快。', ['AU', 'NZ', 'CL']],
    ['dst', 'On the shortest day in Oslo the Sun is up for about six hours, whatever the clock says.', '在奥斯陆一年中白昼最短的那天，无论时钟怎么显示，太阳都只在天上约六个小时。', ['NO']],
    ['dst', 'A study of Indiana found that daylight saving time raised household electricity use by about 1 percent.', '一项针对印第安纳州的研究发现，夏令时让居民用电增加了约 1%。', []],
    ['dst', 'In the week after clocks spring forward, studies find about 6 percent more fatal car crashes in the United States.', '研究发现，在美国，春季拨快时钟后的一周内，致命车祸约多 6%。', []],
];

const PARTS_ZH = [
    'night' => '凌晨', 'early morning' => '早上', 'morning' => '上午', 'midday' => '中午',
    'afternoon' => '下午', 'evening' => '晚上', 'late evening' => '深夜',
];

function zone_country(string $zone): ?string {
    try { $cc = (new DateTimeZone($zone))->getLocation()['country_code'] ?? null; } catch (Exception $e) { return null; }
    return ($cc && $cc !== '??') ? $cc : null;
}

function fact_link(string $slug, string $lang): string {
    return ($lang === 'zh' ? '/zh' : '') . '/time/' . $slug;
}

/** A random general fact (no country tag). */
function general_fact(string $lang): array {
    $pool = array_values(array_filter(FACTS, fn($f) => !$f[3]));
    $f = $pool[random_int(0, count($pool) - 1)];
    return ['text' => $lang === 'zh' ? $f[2] : $f[1], 'href' => fact_link($f[0], $lang), 'kind' => 'general'];
}

/** Date like "Sunday 25 October" / "10月25日（星期日）" from a local wall-clock string. */
function fact_day(string $localBefore, string $lang): string {
    $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $localBefore, new DateTimeZone('UTC'));
    if ($lang === 'zh') return $d->format('n') . '月' . $d->format('j') . '日（' . WEEKDAYS_ZH[$d->format('l')] . '）';
    return $d->format('l j F');
}

/**
 * Live clock-change fact for a zone, from the time zone database. Europe and the USA change on different
 * Sundays; in between, the gap between London and New York is one hour off its usual size.
 */
function live_fact(int $unix, string $zone, string $lang): ?array {
    $zh = $lang === 'zh';
    $L = next_clock_change($unix, 'Europe/London', 40);
    $N = next_clock_change($unix, 'America/New_York', 40);
    $offL = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone('Europe/London'))->getOffset();
    $offN = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone('America/New_York'))->getOffset();
    // Only for places that change their clocks themselves (not Moscow, Istanbul, Arizona...)
    $changes = (bool)next_clock_change($unix, $zone, 200);
    $inEuUs = $changes && (str_starts_with($zone, 'Europe/') || in_array(zone_country($zone), ['US', 'CA'], true));

    // Europe and the USA out of step: either both changes are ahead (and the window is soon), or we're inside the window.
    if ($inEuUs && $N && $N['direction'] === 'back') {
        $usDay = fact_day($N['local_before'], $lang);
        $tN = strtotime($N['utc']);
        if ($L && $L['direction'] === 'back' && strtotime($L['utc']) < $tN && strtotime($L['utc']) - $unix < 21 * 86400) {
            $gapNow = intdiv($offL - $offN, 3600);
            $euDay = fact_day($L['local_before'], $lang);
            return ['kind' => 'live', 'href' => fact_link('dst', $lang), 'text' => $zh
                ? "欧洲在{$euDay}把时钟拨回，美国则要等到{$usDay}。在这一周里，纽约比伦敦晚 " . ($gapNow - 1) . " 小时，而不是平常的 {$gapNow} 小时。"
                : "Europe turns its clocks back on $euDay, but the USA waits until $usDay. For that week, New York is " . ($gapNow - 1) . " hours behind London instead of the usual $gapNow."];
        }
        if (!$L || strtotime($L['utc']) > $tN) {   // Europe has already changed; the USA hasn't yet
            $gapNow = intdiv($offL - $offN, 3600);
            if ($tN - $unix < 14 * 86400) {
                return ['kind' => 'live', 'href' => fact_link('dst', $lang), 'text' => $zh
                    ? "欧洲已经把时钟拨回，美国要到{$usDay}才拨。在那之前，纽约只比伦敦晚 {$gapNow} 小时，而不是平常的 " . ($gapNow + 1) . " 小时。"
                    : "Europe has already turned its clocks back, but the USA waits until $usDay. Until then, New York is only $gapNow hours behind London instead of the usual " . ($gapNow + 1) . '.'];
            }
        }
    }

    // The visitor's own zone changes soon.
    $c = next_clock_change($unix, $zone, 14);
    if ($c) {
        $day = fact_day($c['local_before'], $lang);
        $p = explode('/', $zone);
        $place = str_replace('_', ' ', end($p));
        $amount = $c['amount'];
        if ($zh) {
            $dir = $c['direction'] === 'back' ? '拨回' : '拨快';
            return ['kind' => 'live', 'href' => fact_link('dst', $lang), 'text' => "{$place}将在{$day}把时钟{$dir} " . str_replace([' hours', ' hour', ' minutes'], [' 小时', ' 小时', ' 分钟'], $amount) . '。'];
        }
        return ['kind' => 'live', 'href' => fact_link('dst', $lang), 'text' => "$place turns its clocks {$c['direction']} $amount on $day."];
    }
    return null;
}

/** The rule: no place known → a general fact. Place known → live fact, else a fact tagged for that country, else general. */
function pick_fact(int $unix, ?string $zone, string $lang): array {
    if (!$zone) return general_fact($lang);
    if ($f = live_fact($unix, $zone, $lang)) return $f;
    $cc = zone_country($zone);
    if ($cc) {
        $pool = array_values(array_filter(FACTS, fn($f) => in_array($cc, $f[3], true)));
        if ($pool) {
            $f = $pool[random_int(0, count($pool) - 1)];
            return ['text' => $lang === 'zh' ? $f[2] : $f[1], 'href' => fact_link($f[0], $lang), 'kind' => 'regional'];
        }
    }
    return general_fact($lang);
}

// ---- "Your time" for readers who can't tell us where they are (AIs, no JavaScript) ----------------------

// zone, English name, Chinese name
const PLACES = [
    ['Pacific/Honolulu', 'Honolulu', '檀香山'], ['America/Los_Angeles', 'Los Angeles', '洛杉矶'], ['America/Denver', 'Denver', '丹佛'],
    ['America/Chicago', 'Chicago', '芝加哥'], ['America/New_York', 'New York', '纽约'], ['America/Sao_Paulo', 'São Paulo', '圣保罗'],
    ['Europe/London', 'London', '伦敦'], ['Africa/Lagos', 'Lagos', '拉各斯'], ['Europe/Oslo', 'Oslo', '奥斯陆'], ['Europe/Stockholm', 'Stockholm', '斯德哥尔摩'],
    ['Europe/Berlin', 'Berlin', '柏林'], ['Europe/Paris', 'Paris', '巴黎'], ['Africa/Cairo', 'Cairo', '开罗'], ['Europe/Moscow', 'Moscow', '莫斯科'],
    ['Asia/Dubai', 'Dubai', '迪拜'], ['Asia/Kolkata', 'Delhi', '德里'], ['Asia/Shanghai', 'Beijing', '北京'], ['Asia/Tokyo', 'Tokyo', '东京'],
    ['Australia/Sydney', 'Sydney', '悉尼'], ['Pacific/Auckland', 'Auckland', '奥克兰'],
];

/** Places grouped by identical local time: one line per group, with part of day and a clock change within 14 days. */
function world_now_lines(int $unix, string $lang): array {
    $zh = $lang === 'zh';
    $groups = [];
    foreach (PLACES as [$zone, $en, $zhName]) {
        $d = (new DateTimeImmutable('@' . $unix))->setTimezone(new DateTimeZone($zone));
        $det = local_details($unix, $zone);
        $c = $det['next_change'] && strtotime($det['next_change']['utc']) - $unix <= 14 * 86400 ? $det['next_change'] : null;
        $key = $d->format('Y-m-d H:i') . '|' . $d->format('P') . '|' . $det['abbreviation'] . '|' . ($c['utc'] ?? '');
        if (!isset($groups[$key])) $groups[$key] = ['names' => [], 'd' => $d, 'det' => $det, 'c' => $c];
        $groups[$key]['names'][] = $zh ? $zhName : $en;
    }
    $lines = [];
    foreach ($groups as $g) {
        $d = $g['d'];
        $off = short_offset($d->format('P'));
        $abbr = $g['det']['abbreviation'] && $g['det']['abbreviation'] !== $off ? $g['det']['abbreviation'] . ', ' : '';
        $part = $g['det']['part_of_day'];
        if ($zh) {
            $line = implode('、', $g['names']) . '：' . WEEKDAYS_ZH[$d->format('l')] . ' ' . $d->format('H:i') . '，' . PARTS_ZH[$part] . '（' . str_replace(', ', '，', $abbr) . $off . '）';
            if ($g['c']) $line .= '。' . fact_day($g['c']['local_before'], 'zh') . '时钟' . ($g['c']['direction'] === 'back' ? '拨回' : '拨快') . ' ' . str_replace([' hours', ' hour', ' minutes'], [' 小时', ' 小时', ' 分钟'], $g['c']['amount']);
        } else {
            $line = implode(', ', $g['names']) . ': ' . $d->format('l H:i') . ", $part ($abbr$off)";
            if ($g['c']) $line .= '. Clocks go ' . $g['c']['direction'] . ' ' . $g['c']['amount'] . ' on ' . fact_day($g['c']['local_before'], 'en');
        }
        $lines[] = $line;
    }
    return $lines;
}

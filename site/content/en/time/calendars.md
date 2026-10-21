---
title: Calendars
description: The day, the month and the year don't divide into each other. Every calendar is a different compromise; here is how the twelve on this site work.
---
Three natural cycles shape human time: the **day** (one turn of the Earth relative to the Sun), the **month** (from one new moon to the next, on average 29.530589 days) and the **year** (from one spring equinox to the next, about 365.2422 days). None of them divides evenly into another. A calendar is a set of rules for living with that fact. Every culture chose a different compromise, and most of those choices are still in use.

The clock on this site translates every instant into twelve calendars, using the rules of the Unicode CLDR and ICU libraries.

## Three kinds of calendar

| Kind | Follows | Year length | Examples |
|---|---|---|---|
| Solar | the seasons | 365 or 366 days | Gregorian, Persian, Indian national, Ethiopian, Coptic |
| Lunar | the Moon | 354 or 355 days | Islamic (Hijri) |
| Lunisolar | both | 12 or 13 months (353 to 385 days) | Chinese, Hebrew, Korean |

## Solar calendars

**Julian.** Julius Caesar's reform, in effect from 45 BCE, gave every fourth year an extra day, for an average of 365.25 days. That is 11 minutes too long, so the calendar slipped against the seasons by about a day every 128 years.

**Gregorian.** By the 16th century the spring equinox had drifted ten days early. Pope Gregory XIII's reform of 1582 dropped ten days (Thursday 4 October was followed by Friday 15 October) and refined the leap-year rule: century years are leap years only if divisible by 400, so 2000 was a leap year and 1900 was not. The average year is 365.2425 days, off by about one day in 3,000 years. Countries adopted it over more than three centuries: Catholic countries in 1582, Britain and its colonies in 1752, Japan in 1873, China for official use in 1912, Russia in 1918, Greece in 1923.

The same year length had been reached three centuries earlier in China: the Shoushi calendar (授时历) of 1281, prepared by Guo Shoujing and colleagues with new astronomical instruments, used a tropical year of 365.2425 days.

**Persian (Solar Hijri).** Iran's official calendar starts each year at the exact moment of the spring equinox (Nowruz), determined astronomically. Because it follows the real Sun rather than an arithmetic rule, it is arguably the most accurate solar calendar in civil use.

**Indian national (Saka).** Adopted in 1957 alongside the Gregorian calendar, it counts years from the Saka era (78 CE) and starts the year near the spring equinox.

**Ethiopian and Coptic.** Both have twelve months of 30 days plus a short thirteenth month of 5 or 6 days. The Ethiopian year count runs seven to eight years behind the Gregorian; the Coptic calendar counts from 284 CE, the "Era of the Martyrs".

## The lunar calendar

**Islamic (Hijri).** Twelve lunar months, about 354 days, with no attempt to follow the seasons. The year count begins with the Hijra, Muhammad's migration to Medina in 622 CE. Because the year is 11 days shorter than the solar year, Ramadan moves through all seasons in about 33 years. In religious practice, months traditionally begin with the sighting of the new crescent Moon, so dates can differ by a day between countries. This site uses the Umm al-Qura calendar, a calculated version used officially in Saudi Arabia.

## Lunisolar calendars

**Chinese (农历).** Months begin on the day of the new moon, reckoned in China's time zone (UTC+8, based on the 120° east meridian since 1929). Twelve months make about 354 days, so a leap month is inserted about seven times every 19 years. The rule is astronomical: the year is divided by the **24 solar terms** (二十四节气), defined (since the calendar reform of 1645) by the Sun's true position along its path in 15° steps, and a month that contains no "major" solar term (中气) becomes the leap month. Chinese New Year falls on the second new moon after the winter solstice in most years, between 21 January and 20 February. The 24 solar terms were inscribed on UNESCO's list of the Intangible Cultural Heritage of Humanity in 2016.

Years are also named in a **60-year cycle** formed by combining 10 Heavenly Stems (天干) and 12 Earthly Branches (地支). The branches are paired with the twelve zodiac animals. The year that began on 17 February 2026 is 丙午 (bǐngwǔ), a Horse year.

**Hebrew.** Also lunisolar, with seven leap months in every 19 years (the Metonic cycle: 19 solar years are almost exactly 235 lunar months). Its rules are arithmetic and fixed, and it counts years from a traditional date of creation: autumn 2026 begins the year 5787.

**Korean (Dangi).** Built on the same principles as the Chinese calendar, but reckoned in Korea's time zone, so on rare occasions a new moon falls on a different date and months start a day apart.

## Counting years

Many calendars count years from an era:

| Calendar | Year 1 begins | 2026 CE is |
|---|---|---|
| Gregorian (CE/AD) | traditional birth of Jesus | 2026 |
| Thai Buddhist (BE) | traditional passing of the Buddha, 543 BCE | 2569 |
| Republic of China (Minguo) | founding of the Republic, 1912 | 115 |
| Japanese (era names) | each new emperor's reign; Reiwa since 1 May 2019 | Reiwa 8 |
| Islamic (AH) | Hijra, 622 | 1447 to 1448 |
| Hebrew (AM) | traditional creation | 5786 to 5787 |
| Persian (AP) | Hijra, 622, counted in solar years | 1404 to 1405 |

The traditional Gregorian count has **no year zero**: 1 BCE is followed by 1 CE. Astronomers and the ISO 8601 standard do use a year 0 (equal to 1 BCE), which makes arithmetic across the boundary simpler.

## The week

The seven-day week is not tied to any astronomical cycle. It descends from ancient Near Eastern and Jewish tradition and the Roman planetary week, and it has run unbroken for at least two thousand years. Calendar reforms, including the Gregorian one, have been careful not to break it.

## Day counts for astronomers

Astronomers avoid calendars altogether by counting days. The **Julian Day** count, proposed by Joseph Scaliger in 1583, starts at noon on 1 January 4713 BCE (in the Julian calendar), a date chosen so that three older cycles all began together. Noon on 1 January 2000 is Julian Day 2,451,545.0. The **Modified Julian Date** (MJD = JD − 2,400,000.5), introduced in 1957 for satellite tracking, starts at midnight and uses smaller numbers.

**Unix time** and **exasecond time** take the same approach with seconds: a single continuous count, translated into calendars only for display.

## Sources

- E. G. Richards, *Mapping Time: The Calendar and Its History* (Oxford University Press, 1998).
- E. M. Reingold and N. Dershowitz, *Calendrical Calculations: The Ultimate Edition* (Cambridge University Press, 2018).
- Unicode Consortium, *Unicode Technical Standard #35 (LDML), Part 4: Dates*, calendar data. [unicode.org](https://www.unicode.org/reports/tr35/tr35-dates.html)
- Purple Mountain Observatory, Chinese Academy of Sciences; national standard GB/T 33661-2017, *Calculation and promulgation of the Chinese calendar* (农历的编算和颁行).
- UNESCO, "The Twenty-Four Solar Terms" (2016). [ich.unesco.org](https://ich.unesco.org/en/RL/the-twenty-four-solar-terms-knowledge-in-china-of-time-and-practices-developed-through-observation-of-the-sun-s-annual-motion-00647)
- J. Needham, *Science and Civilisation in China*, Volume 3 (Cambridge University Press, 1959), on the Shoushi calendar.
- US Naval Observatory, explanation of Julian Date. [aa.usno.navy.mil](https://aa.usno.navy.mil/data/JulianDate)

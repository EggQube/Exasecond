---
title: Time zones
description: Borders on the clock. How the world went from thousands of local times to about 40 zones, and the places where the lines bend: half hours, quarter hours, skipped days and a whole country on one clock.
---
Time zones look like a law of nature on a map: neat bands, one hour apart. They are not. They are political borders drawn across the turning of the Earth, and like other borders they bend around countries, history and convenience.

## Before time zones

Until the 19th century every town kept its own **local mean time**, set by the Sun. Noon was when the Sun stood highest, on average, over that town. Because the Earth turns 15 degrees an hour, every degree of longitude shifts local time by four minutes. Bristol was ten minutes behind London; Boston was twelve minutes ahead of New York.

Nobody minded until people and messages could travel faster than the Sun's shadow moved. Railways and the telegraph made local time a problem: a timetable listing a dozen different local times invites missed trains, and on single-track lines, collisions.

## Railway time

Britain solved it first. The Great Western Railway ran its trains on London time from 1840, and in 1847 the Railway Clearing House recommended Greenwich time for all lines. Within a few years most of Britain's public clocks showed "railway time". Parliament made Greenwich Mean Time the legal time of Great Britain in 1880.

North America, much wider, needed more than one time. Sandford Fleming, a Scottish-born Canadian railway engineer, proposed in 1879 a world of 24 zones, each an hour apart. On 18 November 1883, "the day of two noons", the railroads of the United States and Canada switched to five standard zones. Many towns followed at once; the United States made the zones law only in 1918.

## One prime meridian

In October 1884 delegates from 25 nations met in Washington for the International Meridian Conference. They chose the meridian through the Royal Observatory at Greenwich as longitude zero, by 22 votes to one (San Domingo, now the Dominican Republic); France and Brazil abstained. Most of the world's ships already used charts based on Greenwich.

France kept Paris Mean Time for its own clocks until 1911, then adopted Greenwich time, officially defined at first as "Paris Mean Time, retarded by nine minutes and twenty-one seconds", so as not to name Greenwich. The Netherlands kept Amsterdam time, about 20 minutes ahead of Greenwich, until 1940; Ireland kept Dublin Mean Time until 1916.

## How many zones?

In theory, 24. In practice the world uses about 38 different offsets from UTC, because countries choose what suits them. The spread runs from UTC−12 (only uninhabited Baker and Howland Islands) to UTC+14 (the Line Islands of Kiribati): 26 hours. For two hours every day, from 10:00 to 11:59 UTC, three different calendar dates exist on Earth at the same time.

## Half hours and quarter hours

| Place | Offset from UTC | Why |
|---|---|---|
| India, Sri Lanka | +5:30 | India adopted one national time in 1906, halfway between its eastern and western extremes. Calcutta kept its own time until 1948 and Bombay until 1955 |
| Iran | +3:30 | Tehran's local time, rounded |
| Afghanistan | +4:30 | Kabul's local time, rounded |
| Myanmar | +6:30 | |
| Newfoundland, Canada | −3:30 | The island's local time, rounded |
| South and central Australia | +9:30 | Adelaide and Darwin |
| **Nepal** | **+5:45** | Since 1986 based on the meridian of 86°15′ east, close to the peak of Gauri Shankar, east of Kathmandu |
| **Chatham Islands, New Zealand** | **+12:45** | 45 minutes ahead of mainland New Zealand |

## One country, one clock

**China** spans more than 60 degrees of longitude, four hours of Sun time. In 1912 the young Republic divided it into five time zones. Since 1949 the whole country has used one: Beijing Time, UTC+8. In Kashgar, in far western Xinjiang, the Sun stands highest around 3 in the afternoon by the clock, and many people in the region also use an unofficial "Xinjiang time" two hours behind. Where China meets Afghanistan, crossing the border moves the clock by three and a half hours, the largest jump across any land border.

**India** also uses a single zone for a country three thousand kilometres wide; in the far east, the Sun rises before 5 a.m. in summer.

**Russia**, by contrast, has eleven time zones, and **France**, counting its overseas territories from the Pacific to the Indian Ocean, has twelve: more than any other country.

## Living on someone else's time

**Spain** lies mostly west of Greenwich, but since 1940 it has used Central European Time, the time of Berlin. Francisco Franco moved the clocks during the Second World War, and they never moved back. That is one reason why Spaniards are known for late lunches and late dinners: the clock runs about an hour ahead of the Sun, two in summer. A Spanish parliamentary commission recommended returning to Greenwich time in 2013; it has not happened.

## Moving the line

Countries change their time zones more often than you might think, usually for trade or politics.

- **Kiribati** spanned the International Date Line, so its eastern and western islands were on different days and shared only four working days a week. It skipped 31 December 1994 on its eastern islands, moving the date line around itself. Its Line Islands, at UTC+14, are now the first inhabited places on Earth to start each new day.
- **Samoa** skipped Friday 30 December 2011 entirely, jumping from Thursday to Saturday, to move from the American to the Australian and New Zealand side of the date line, closer to its main trading partners.
- **North Korea** used its own "Pyongyang time", UTC+8:30, from August 2015 to May 2018, then returned to UTC+9 to match South Korea.
- **Venezuela** used UTC−4:30 from 2007 to 2016.
- **Kazakhstan** moved its eastern half back an hour on 1 March 2024, putting the whole country on one zone, UTC+5.
- **Greenland**'s capital Nuuk moved its clocks an hour forward in 2023, to be closer to Europe's working day.

## Places that ignore their neighbours

**Arizona** does not use daylight saving time, so in summer it shares the time of California rather than Colorado. But the **Navajo Nation**, which covers the north-east of the state, does use daylight saving time, to stay in step with its parts in Utah and New Mexico. And the **Hopi Reservation**, surrounded by the Navajo Nation, does not. Driving across north-eastern Arizona in summer can change the clock several times in an hour.

Research stations in **Antarctica**, where all time zones meet at the pole, usually keep the time of the country that supplies them.

## Why it matters for machines

Time zone rules are decided by governments, sometimes with only a few weeks' notice, and they change every year somewhere. Software cannot calculate them from geography. It looks them up in the **IANA time zone database**, which records every rule since 1970 and many before, and is updated several times a year (see [World time](/time/timescales)). An agent that converts a time with last year's rules can be an hour wrong.

## Sources

- D. Howse, *Greenwich Time and the Longitude* (Philip Wilson, 1997).
- I. R. Bartky, *One Time Fits All: The Campaigns for Global Uniformity* (Stanford University Press, 2007).
- *International Conference Held at Washington for the Purpose of Fixing a Prime Meridian and a Universal Day: Protocols of the Proceedings* (Washington, 1884).
- Statutes (Definition of Time) Act 1880 (United Kingdom).
- IANA Time Zone Database, data files and history notes by Paul Eggert and others. [iana.org/time-zones](https://www.iana.org/time-zones)
- Pew Research Center, "Most countries don't observe daylight saving time" (2023), on India's time zone and population. [pewresearch.org](https://www.pewresearch.org/short-reads/2023/10/26/most-countries-dont-observe-daylight-saving-time/)
- timeanddate.com news archive on time zone changes (Kazakhstan 2024, Greenland 2023, Samoa 2011). [timeanddate.com](https://www.timeanddate.com/news/time/)

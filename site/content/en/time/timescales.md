---
title: World time
description: How the world agrees on what time it is: UT1, TAI, UTC, leap seconds, GPS and BeiDou time, time zones, Unix time and the clocks of the internet.
---
There is no single master clock in the world. There is a calculation. Several hundred atomic clocks in dozens of laboratories are compared, weighted and averaged, and the result is corrected to stay close to the turning of the Earth. Every phone and server then copies that result, step by step, through radio, satellites and networks.

## Two kinds of time

**Earth time.** UT1 is time measured by the Earth's rotation, as observed against distant quasars by radio telescopes. It is what the Sun in the sky follows. It is irregular: the day changes length by milliseconds with the seasons, and slowly grows longer over centuries because the Moon's tides act as a brake (see [What is a second?](/time/second)).

**Atomic time.** International Atomic Time (TAI) counts SI seconds as realised by atomic clocks. The International Bureau of Weights and Measures (BIPM) near Paris computes it from roughly 450 clocks in more than 80 laboratories around the world. TAI is a "paper" timescale: it is calculated after the fact and published monthly in a bulletin called Circular T. National laboratories each keep a real-time approximation, called UTC(k), where k is the laboratory, for example UTC(NIST) in the United States or UTC(NTSC) at the National Time Service Center of the Chinese Academy of Sciences in Shaanxi.

## UTC: a compromise

Coordinated Universal Time (UTC) is the timescale of civil life. Since 1972 it has ticked at exactly the rate of TAI, but it is kept within 0.9 seconds of UT1 by inserting **leap seconds**. When the International Earth Rotation and Reference Systems Service (IERS) sees the gap growing, it announces a leap second, inserted as 23:59:60 at the end of 30 June or 31 December.

| Date | TAI minus UTC |
|---|---|
| 1 January 1972 | 10 seconds (initial offset) |
| 1 January 2017 | 37 seconds, after the 27th leap second |

The most recent leap second was at the end of 31 December 2016. None has been needed since. In fact, since about 2020 the Earth has been turning slightly faster than in previous decades, and geophysicists have discussed the possibility of the first **negative** leap second, a minute with only 59 seconds. A 2024 study by Duncan Agnew in *Nature* found that the melting of polar ice, which moves mass toward the equator and slows the Earth, is pushing that possibility back by a few years.

## The end of leap seconds

Leap seconds are rare, irregular and announced only six months in advance. Software handles them badly. In 2012 a leap second triggered bugs in the Linux kernel that took down websites and airline booking systems; in 2017 a leap second caused a well-known internet company's DNS service to fail when a clock appeared to run backwards. Large technology companies now "smear" the extra second across many hours instead of inserting it.

In November 2022 the 27th General Conference on Weights and Measures decided (Resolution 4) that the maximum allowed difference between UTC and UT1 will be increased, by 2035 or earlier. In practice this means no more leap seconds for at least a century. The World Radiocommunication Conference of the ITU agreed in 2023. How large the new tolerance should be, and how it will eventually be handled, is still being worked out.

When that happens, UTC will drift slowly away from the Sun: by about a minute over the next century or so. Nobody will notice in daily life.

## Satellite time

Satellite navigation is time measurement. A receiver works out its distance to each satellite from how long the signal took to arrive, so every nanosecond of clock error becomes about 30 centimetres of position error. Each system keeps its own continuous timescale without leap seconds:

| System | Timescale | Offset from UTC (since 2017) |
|---|---|---|
| GPS (United States) | GPS Time, began 6 January 1980 | 18 s ahead |
| BeiDou (China) | BDT, began 1 January 2006 | 4 s ahead |
| Galileo (Europe) | GST, aligned with GPS Time | 18 s ahead |
| GLONASS (Russia) | follows UTC (Moscow), with leap seconds | 3 hours ahead, no leap-second offset |

GPS satellites also have to correct for relativity: their clocks run about 38 microseconds a day fast compared with clocks on the ground (see [Relativity](/time/relativity)).

## Time zones

Before railways and the telegraph, every town kept its own local solar time. Railways needed shared timetables: British railways adopted London time in the 1840s, and North American railroads introduced standard time zones in 1883. The International Meridian Conference of 1884 chose Greenwich as the prime meridian.

Most time zones today are whole hours from UTC, but not all: India uses UTC+5:30, Nepal UTC+5:45. Since 1949 all of China has used a single zone, Beijing Time (UTC+8), although the country spans more than four hours of solar time; in Kashgar, in far western Xinjiang, the Sun is at its highest around 3 in the afternoon, Beijing Time.

Time zone rules change often, by government decision. The IANA time zone database (also called the tz or Olson database, after Arthur David Olson, who started it in 1986) records every rule since 1970, and many before. Every major operating system, programming language and this website use it. Names such as `Asia/Shanghai` or `Europe/Oslo` come from it.

## Unix time

Most computers count time as **Unix time**: the number of seconds since 1970-01-01 00:00:00 UTC, with every day counted as exactly 86,400 seconds. Leap seconds are therefore not counted; during one, Unix time repeats a second or is smeared. On systems that still store Unix time as a signed 32-bit integer, the count runs out at 03:14:07 UTC on 19 January 2038 and wraps around to 1901, the "Year 2038 problem". Modern systems use 64 bits, enough for 292 billion years.

Exasecond time on this site is Unix time plus a fixed constant, so it inherits the same rule: no leap seconds. See the [specification](/spec.md).

## How your computer knows the time

Computers synchronise their quartz clocks over the network using the **Network Time Protocol** (NTP), designed by David L. Mills at the University of Delaware and first published in 1985. NTP organises servers in "strata": stratum 0 is a reference clock (an atomic clock or a GPS receiver), stratum 1 servers are connected to one directly, stratum 2 servers ask stratum 1, and so on. By exchanging timestamps and measuring the round-trip delay, a client can usually set its clock to within a few milliseconds over the internet, and much better on a local network. Where nanoseconds matter, in power grids, telecom networks and stock exchanges, the **Precision Time Protocol** (IEEE 1588) is used instead.

Date and time are written in the international standard **ISO 8601**, first published in 1988: `2026-10-21T14:30:00Z`, largest unit first, with `Z` (for "zero") meaning UTC.

## Sources

- BIPM, Time Department: TAI, UTC and Circular T. [bipm.org](https://www.bipm.org/en/time-metrology)
- BIPM, Resolution 4 of the 27th CGPM (2022), "On the use and future development of UTC". [bipm.org](https://www.bipm.org/en/cgpm-2022/resolution-4)
- IERS Bulletin C (leap second announcements). [iers.org](https://www.iers.org)
- D. C. Agnew, "A global timekeeping problem postponed by global warming", *Nature* 628, 333 (2024).
- US Space Force / GPS.gov, GPS Interface Specification IS-GPS-200.
- China Satellite Navigation Office, *BeiDou Navigation Satellite System Signal In Space Interface Control Document*.
- D. L. Mills, "Network Time Protocol (NTP)", RFC 958 (1985); "Network Time Protocol Version 4", RFC 5905 (2010).
- IANA Time Zone Database. [iana.org/time-zones](https://www.iana.org/time-zones)
- ISO 8601-1:2019, *Date and time: Representations for information interchange*.

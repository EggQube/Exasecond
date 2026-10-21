---
title: Relativity
description: Time is not the same everywhere. Moving clocks run slow, clocks lower in gravity run slow, and both effects are measured every day.
---
Before 1905, time was assumed to be universal: one "now" spreading across the whole universe, and every correct clock agreeing with every other. Albert Einstein's special theory of relativity (1905) and general theory of relativity (1915) replaced that picture. Each clock measures its own time, called **proper time**, and two accurate clocks that take different paths through space and gravity will disagree when they meet again.

This is not a quirk of clocks. It is a property of time itself, and it has been measured with muons, aeroplanes, rockets, satellites and clocks a millimetre apart. **Evidence: established.**

## Moving clocks run slow

Special relativity starts from two postulates: the laws of physics are the same for every observer moving at constant velocity, and the speed of light in a vacuum is the same for all of them. Together they imply that a clock moving relative to you ticks slower, as measured by you, by the factor

```
γ = 1 / √(1 − v²/c²)
```

At everyday speeds γ is almost exactly 1. At 90 percent of the speed of light it is about 2.3; at 99 percent, about 7.1.

A related consequence is the **relativity of simultaneity**: two events that happen at the same time for one observer can happen at different times for another observer moving relative to the first. There is no universal "now" across space.

### Muons

Muons are unstable particles created when cosmic rays strike the upper atmosphere, about 15 kilometres up. At rest they live on average 2.2 microseconds. Even at nearly the speed of light, that is time to travel only about 660 metres, yet large numbers reach the ground. From the ground's point of view, their internal clocks run slow. In 1977 an experiment at CERN stored muons in a ring at 99.94 percent of the speed of light (γ ≈ 29.3). They lived 29.3 times longer than muons at rest, as predicted, to within about 0.1 percent.

### Clocks on aeroplanes

In October 1971 Joseph Hafele and Richard Keating flew four caesium clocks around the world on commercial flights, once eastward and once westward, and compared them with clocks at the US Naval Observatory. Both special and general relativity contribute (speed relative to the Earth's centre, and height above the ground). The eastward clocks lost about 59 nanoseconds and the westward clocks gained about 273, in agreement with prediction within the experimental uncertainty.

## Clocks lower down run slow

General relativity describes gravity as the curvature of spacetime. One consequence: a clock deeper in a gravitational field runs slower than one higher up. Near the Earth's surface the difference is about 1.1 parts in 10^16 for every metre of height. Your head ages slightly faster than your feet: over a lifetime, by a few hundred billionths of a second.

The measurements have become steadily finer:

| Year | Experiment | Height difference |
|---|---|---|
| 1959 to 1960 | Pound and Rebka: gamma rays in a tower at Harvard | 22.5 m |
| 1976 | Gravity Probe A: a hydrogen maser on a rocket to 10,000 km | 10,000 km |
| 2010 | Chou and colleagues, NIST: two aluminium-ion clocks | 33 cm |
| 2020 | Takamoto and colleagues: optical clocks in Tokyo Skytree | 450 m |
| 2022 | Bothwell and colleagues, JILA: across one cloud of strontium atoms | ~1 mm |

Gravity Probe A confirmed the prediction to about 70 parts per million. In 2018 the GRAVITY collaboration measured the same effect in starlight from the star S2 as it passed close to the black hole at the centre of our galaxy.

## GPS: relativity in your pocket

GPS satellites orbit at about 20,200 kilometres, moving at about 3.9 kilometres per second. Two effects act on their clocks, as seen from the ground:

- Weaker gravity makes them run **fast**, by about 45 microseconds a day.
- Their orbital speed makes them run **slow**, by about 7 microseconds a day.

The net effect is about **38 microseconds a day fast**. Uncorrected, that would add up to positioning errors of around 10 kilometres per day. The satellite clocks are therefore set before launch to tick slightly slow: their reference frequency is 10.22999999543 MHz instead of 10.23 MHz, so that from the ground they appear exactly right. Receivers apply a further small correction for each satellite's slightly elliptical orbit.

## Many times for one universe

Because clocks in different places run at different rates, astronomers and metrologists keep several timescales:

- **Terrestrial Time (TT)**: the time of an ideal clock on the rotating Earth's mean sea level (the geoid). TAI is a realisation of it.
- **Geocentric Coordinate Time (TCG)**: time at the Earth's centre, away from its gravity. It runs faster than TT by about 22 milliseconds per year.
- **Barycentric Coordinate Time (TCB)**: time for the centre of mass of the Solar System, outside the gravity of the Sun and planets. It runs faster than TT by about 0.49 seconds per year. Spacecraft navigation and planetary ephemerides use it.

Clocks on the Moon run faster than clocks on Earth by about 56 microseconds a day. In 2024 the United States directed NASA to develop a Coordinated Lunar Time, and the International Astronomical Union passed a resolution on lunar reference systems, in preparation for permanent missions there.

## What relativity does not say

Relativity does not say that time is an illusion, or that "everything is relative". It says that the speed of light, the laws of physics and the **spacetime interval** between two events are the same for everyone; time and space separately are not. Each observer's measurements are exact and consistent; they are just measurements of different things.

Whether this means the past and future exist as fully as the present, the "block universe", is a question of interpretation that physics has not settled. See [What is time? Open questions](/time/nature).

## Sources

- A. Einstein, "Zur Elektrodynamik bewegter Körper", *Annalen der Physik* 17, 891 (1905).
- J. Bailey et al., "Measurements of relativistic time dilatation for positive and negative muons in a circular orbit", *Nature* 268, 301 (1977).
- J. C. Hafele and R. E. Keating, "Around-the-world atomic clocks: observed relativistic time gains", *Science* 177, 168 (1972).
- R. V. Pound and G. A. Rebka Jr., "Apparent weight of photons", *Physical Review Letters* 4, 337 (1960).
- R. F. C. Vessot et al., "Test of relativistic gravitation with a space-borne hydrogen maser", *Physical Review Letters* 45, 2081 (1980).
- C. W. Chou, D. B. Hume, T. Rosenband, D. J. Wineland, "Optical clocks and relativity", *Science* 329, 1630 (2010).
- M. Takamoto et al., "Test of general relativity by a pair of transportable optical lattice clocks", *Nature Photonics* 14, 411 (2020).
- T. Bothwell et al., "Resolving the gravitational redshift across a millimetre-scale atomic sample", *Nature* 602, 420 (2022).
- GRAVITY Collaboration, "Detection of the gravitational redshift in the orbit of the star S2 near the Galactic centre massive black hole", *Astronomy & Astrophysics* 615, L15 (2018).
- N. Ashby, "Relativity in the Global Positioning System", *Living Reviews in Relativity* 6, 1 (2003).
- IAU 2006 Resolution B3 (TT, TCB and TDB); IAU 2024 Resolution II on lunar reference systems.

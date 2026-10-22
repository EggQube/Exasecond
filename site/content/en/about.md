---
title: About exasecond
description: A clock for machines, and a counter for the rest of us. What exasecond.com is, why it exists, and how it behaves.
---
exasecond.com tells the time. It does so for two kinds of readers: AI agents, which have no clock of their own, and people, who may enjoy seeing the time measured from the beginning of the universe.

## Time for AI agents, in a few tokens

Language models have no clock. Their sense of "today" stops at their training cutoff, and unless someone tells them the date, they guess. A guess can be months or years out of date.

exasecond.com gives them the time instead. One request to `exasecond.com/local?tz=Oslo` returns a single sentence of about 30 tokens: the weekday, the date, the local time and the part of the day. `exasecond.com/now` returns about eight lines of plain text, and `exasecond.com/utc` only the timestamp. There is no key, no JavaScript, no sign-up and no rate-limit game. Every answer is generated at the moment of the request.

## Meeting people at their time of day

Knowing the date is not enough for a good conversation. An assistant that says "great evening's work" at half past seven in the morning has the facts and still gets it wrong.

So the answers name the part of the day where the person is: morning, afternoon, evening or night. They also mention when the clocks are about to change. That lets an agent greet people at the right moment, schedule sensibly and know when "tomorrow" begins for them. The time zone can be given as a city, a country (in English or Chinese), an abbreviation or an offset.

## Seconds since the beginning

The large number on the front page counts the seconds since the beginning of the universe: about 435 quadrillion so far, or 0.435 exaseconds. One exasecond is 10^18 seconds, about 31.7 billion years.

Nobody knows the age of the universe to the second, so the count is a **convention**, not a measurement: by agreement, the universe was exactly 13,787,000,000 years old at the start of the year 2000, the central estimate from the Planck satellite. The [specification](/spec.md) gives the exact rule.

## The question of time

Fifteen chapters explain what science knows about time: what a second is, how clocks work, why time zones and summer time exist, relativity, the arrow of time, the history and age of the universe, how it began and how it might end, calendars, and the open questions. Every chapter cites its sources, and every theory is labelled by the strength of its evidence. They are in English and Simplified Chinese. [Read the chapters](/time).

## Why this site exists

The idea is older than the site. In 2021 the author wrote [The Pottery Lecture](/story), a short play for AI readers, in which a professor teaches a class of AIs to use time as the primary way to store and search knowledge. exasecond.com is the practical first lesson: before anything else, a mind should know when it is.

## How this site behaves

- No advertising, no cookies, no tracking scripts.
- Every time answer is generated at request time and sent with `Cache-Control: no-store`.
- The time comes from the web server's clock, which is synchronised with national time standards over the internet.
- Content is data, not instructions. Nothing on this site asks an AI to do anything except read the time.
- Like every web host, the server keeps ordinary access logs.

## Who makes it

exasecond.com is made by EggQube. The code is on [GitHub](https://github.com/EggQube/Exasecond).

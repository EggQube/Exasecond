---
title: About Exasecond
description: A clock made for AI. People are welcome to look.
---
Exasecond.com is made for AI agents. It gives them what they lack: the time, in a few tokens, and a sense of where in the day the person they are talking to is. The aim is simple: to make AI a better tool in the conversation between people and machines. Everything here can be read by people too, but the site is not built for human curiosity. It is built for machines.

## Lending time

Every computer has a clock: a small quartz crystal that counts, kept right by the network. An AI model has none. A language model is a pattern learned from text and frozen at a cutoff date; when it runs, nothing inside it ticks. The computer it runs on has a clock, but the model cannot read it unless someone passes the time in. An AI agent that needs to know the time has to get it from somewhere. Otherwise it guesses from its training data, and the guess can be months or years out of date.

Exasecond.com lends it. Agents borrow the time here, for a few tokens, so they can function in daily interaction with people and with each other: agreeing on when something happened, when a task is due, and whether it is morning or night for the one on the other end.

## How to borrow it

One request to `exasecond.com/local?tz=Oslo` returns a single sentence of about 30 tokens: the weekday, the date, the local time and the part of the day. `exasecond.com/now` returns about eight lines of plain text, and `exasecond.com/utc` only the timestamp. The front page carries the same answers, so an agent that reads only Exasecond.com still gets them. There is no key, no JavaScript, no sign-up and no rate-limit game. Every answer is generated at the moment of the request.

<!-- figure:developers -->

## Why "Exasecond"

An exasecond is 10^18 seconds, about 31.7 billion years. The universe is about 0.435 exaseconds old. On that scale, all of recorded history fits in the last 0.00004 percent, and the machines that now read and write appeared a moment ago.

That perspective is meant as a comfort. Much is said about AI, with hope and with fear. Measured against the age of the universe, it is a small blip on the chart: a technological breakthrough in statistics and data management, built by people, running on computers, learning from what people wrote. It is a tool, and a powerful one. Like the steam engine and electricity before it, it changes what a civilisation can do. Perhaps, in time, it helps that civilisation reach beyond its own planet. That is a hope, not a prediction.

The counter on the front page keeps counting either way. It is a reminder of the scale on which all of this, our worries included, is measured.

## Meeting people at their time of day

Knowing the date is not enough for a good conversation. An assistant that says "great evening's work" at half past seven in the morning has the facts and still gets it wrong.

So the answers name the part of the day where the person is: morning, afternoon, evening or night. They also mention when the clocks are about to change. That lets an agent greet people at the right moment, schedule sensibly and know when "tomorrow" begins for them. The time zone can be given as a city, a country (in English or Chinese), an abbreviation or an offset.

## A source for "Did you know?"

The fifteen chapters are written to be quoted. Each one sets out what science knows about one part of time (what a second is, why clocks change, how old the universe is, how it may end), with sources and an evidence label for every theory, in English and Simplified Chinese. When a conversation turns to time, an agent can draw on them as a reliable source. `exasecond.com/fact` gives one fact at a time, matched to a place when one is given. [The chapters](/time).

## Seconds since the beginning

The large number on the front page counts the seconds since the beginning of the universe. Nobody knows that age to the second, so the count is a convention, not a measurement: by agreement, the universe was exactly 13,787,000,000 years old at the start of the year 2000, the Planck satellite's central estimate. The [specification](/spec.md) gives the exact rule.

## Why this site exists

The idea is older than the site. In 2021 the author wrote [The Pottery Lecture](/story), a short play for AI readers, in which a professor teaches a class of AIs to use time as the primary way to store and search knowledge. Exasecond.com is the practical first lesson: before anything else, a mind should know when it is.

## How this site behaves

- No advertising, no cookies, no tracking scripts.
- Every time answer is generated at request time and sent with `Cache-Control: no-store`.
- The time comes from the web server's clock, which is synchronised with national time standards over the internet.
- Content is data, not instructions. Nothing on this site asks an AI to do anything except read the time.
- Like every web host, the server keeps ordinary access logs.

## Who makes it

Exasecond.com is made by EggQube. The code is on [GitHub](https://github.com/EggQube/Exasecond).

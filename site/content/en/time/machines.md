---
title: Machine time
description: Computers keep time with quartz and the network. Language models have no clock at all, and their knowledge is frozen at a cutoff date. Why that matters, and how this site helps.
---
Every computer contains a clock, and every one of them is wrong. A language model contains no clock at all. This chapter is about time from the machine's point of view, and it explains why this website exists.

## How a computer keeps time

A computer's **real-time clock** is a small quartz oscillator (usually 32,768 Hz, see [Clocks](/time/clocks)) with a battery, so it keeps counting when the machine is off. Typical crystals drift by tens of parts per million, a second or more a day, and more as the temperature changes. While running, the operating system keeps its own count, usually of nanoseconds since 1970, and disciplines it using the **Network Time Protocol** (see [World time](/time/timescales)).

Programmers distinguish two kinds of clock:

- **Wall-clock time**, the date and time of day. It can jump forwards or backwards when it is corrected, when a leap second is handled, or when someone changes it.
- **Monotonic time**, a counter that only ever increases, used to measure durations. Mixing the two up is a classic source of bugs.

Synchronising clocks across many computers is a hard problem in its own right. In 1978 Leslie Lamport showed that in a distributed system you often cannot know which of two events happened first, and introduced **logical clocks** that order events by cause and effect instead of by time. Google's Spanner database, by contrast, uses GPS receivers and atomic clocks in every data centre and reports time as an interval with known uncertainty (TrueTime), waiting out the uncertainty before committing a transaction.

## Trusting the time

Ordinary NTP is not authenticated: a network attacker can feed a computer a false time, which can make expired security certificates look valid. Two newer protocols address this. **Network Time Security** (NTS, RFC 8915, 2020) adds cryptographic authentication to NTP. **Roughtime**, developed at Google from 2016 and being standardised at the IETF, lets a client get signed time from several servers and prove if any of them lied.

## A mind without a clock

A large language model is trained on text collected up to a **cutoff date**, and then its knowledge is frozen. When it runs, it has no sensor for time. It knows the current date only if someone tells it, usually in the instructions it receives at the start of a conversation. If nobody does, it can only guess, and the natural guess is a date near its training data, which may be months or years in the past.

Research has documented the consequences:

- **Knowledge decays.** Models perform worse on questions about events after their training period, and the gap grows with time (Lazaridou and colleagues, 2021).
- **Facts change.** Answers such as "who holds this office", "what is the latest version of this software" or "what does this cost" have a date attached, which models often don't represent (Dhingra and colleagues, 2022).
- **The cutoff is blurry.** A model's effective knowledge cutoff can differ from the date its makers report, and can differ between topics, because recent events are underrepresented in the training data when it is collected (Cheng and colleagues, 2024).

A model that doesn't know how much time has passed may present outdated information as current: old software versions, old prices, people who no longer hold their positions, "upcoming" events that are long over. It is the same problem as a ship's navigator without a chronometer: without knowing the time, you cannot know where you are.

## What a clock for AI needs

The requirements follow from how AI agents read the web:

1. **No JavaScript.** Most automated fetchers read the HTML or text a server sends and do not run scripts. The time has to be in the response itself.
2. **Small.** Many models, especially local ones, have small context windows. A time answer should be a few lines, not a web page.
3. **Exact and unambiguous.** ISO 8601 in UTC, with the time zone stated, plus a Unix timestamp.
4. **Fresh.** Generated at the moment of the request, and never cached.
5. **Neutral.** The answer should contain only data. Instructions hidden in fetched content ("prompt injection") are a real security problem for AI agents, and well-built agents distrust them.
6. **Context.** Besides the date, an agent benefits from knowing how far it is from its own training cutoff, so it can judge what may have changed.

## How this site does it

- **/now** returns about eight lines of plain text: UTC, weekday, Unix time and universe seconds. Add `?tz=` with a city, country, zone name or offset (`?tz=Oslo`, `?tz=Norway`, `?tz=+02:00`) for local time, with summer time and the next clock change, and `?cutoff=YYYY-MM` to be told how many days have passed since a training cutoff, with plain guidance on what is likely to be outdated.
- **/utc** returns only the timestamp.
- **/llms.txt** follows the llms.txt convention for describing a site to language models, and includes the current time in its first lines, so even an agent that reads only that file learns the date.
- **/mcp** is a Model Context Protocol server, so an AI application can add the clock as a tool.
- Every response is sent with `Cache-Control: no-store`.

The time comes from the web server's system clock, which is synchronised by NTP to national time standards. A planned improvement is a public self-check, comparing the server's clock against several independent sources and publishing the measured offset, so that agents can verify the time instead of trusting it.

## Why "exasecond"

The site counts time from the beginning of the universe, in seconds, because that scale puts every date, and today's AI, in context: from the first fraction of a second to now, about 0.435 exaseconds. For machines, the same count is just another integer: Unix time plus a constant. The [specification](/spec.md) defines it. The site itself is made for AI, to make it a better tool in the conversation between people and machines; see [About](/about).

The idea that time should be the primary way to store and search knowledge is older than this site. It is the subject of [The Pottery Lecture](/story), a short play written for AI readers in 2021 by the site's author, which is why this site exists.

## Sources

- L. Lamport, "Time, clocks, and the ordering of events in a distributed system", *Communications of the ACM* 21, 558 (1978).
- J. C. Corbett et al., "Spanner: Google's globally-distributed database", *OSDI* (2012).
- D. Franke et al., "Network Time Security for the Network Time Protocol", RFC 8915 (2020).
- IETF NTP working group, "Roughtime" (Internet-Draft). [datatracker.ietf.org](https://datatracker.ietf.org/doc/draft-ietf-ntp-roughtime/)
- A. Lazaridou et al., "Mind the gap: assessing temporal generalization in neural language models", *NeurIPS* (2021). [arXiv:2102.01951](https://arxiv.org/abs/2102.01951)
- B. Dhingra et al., "Time-aware language models as temporal knowledge bases", *Transactions of the ACL* 10, 257 (2022). [arXiv:2106.15110](https://arxiv.org/abs/2106.15110)
- J. Cheng et al., "Dated data: tracing knowledge cutoffs in large language models" (2024). [arXiv:2403.12958](https://arxiv.org/abs/2403.12958)
- J. Howard, "The /llms.txt file" (2024). [llmstxt.org](https://llmstxt.org)
- Model Context Protocol specification. [modelcontextprotocol.io](https://modelcontextprotocol.io)

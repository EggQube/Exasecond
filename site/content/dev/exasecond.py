"""Exasecond: the current time for AI agents. Standard library only. https://exasecond.com/developers

(c) 2026 EggQube, exasecond.com. Follows the regular MIT License; keep this notice.
https://exasecond.com/dev/LICENSE
"""

import urllib.parse
import urllib.request


def get_local_time(place: str = "") -> str:
    """One sentence for talking to a person, e.g. "It's 16:02 on Thursday afternoon in Stockholm (22 October 2026, CEST, UTC+2)." place: city, country, zone or offset."""
    url = "https://exasecond.com/local" + ("?tz=" + urllib.parse.quote(place) if place else "")
    with urllib.request.urlopen(url, timeout=10) as r:
        return r.read().decode("utf-8").strip()


def get_utc() -> str:
    """The current time as an ISO 8601 UTC timestamp, e.g. 2026-10-22T08:15:00Z."""
    with urllib.request.urlopen("https://exasecond.com/utc", timeout=10) as r:
        return r.read().decode("utf-8").strip()


if __name__ == "__main__":
    print(get_local_time("Oslo"))
    print(get_utc())

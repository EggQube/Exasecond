"""
title: Exasecond time
author: EggQube
author_url: https://exasecond.com
version: 0.1.0
license: MIT, (c) 2026 EggQube, exasecond.com. Follows the regular MIT License; keep this notice. https://exasecond.com/dev/LICENSE
description: Gives the model the current date, local time and part of day for any place, from https://exasecond.com. About 30 tokens per call, no key.
"""

import urllib.parse
import urllib.request


class Tools:
    def __init__(self):
        pass

    def get_local_time(self, place: str = "") -> str:
        """
        Get the current date, local time and part of day (morning, afternoon, evening, night) for a place.
        Use it before greeting the user or mentioning the time of day, and whenever you need today's date.
        :param place: The user's city, country, time zone name or UTC offset, for example "Oslo", "Norway", "Europe/Oslo" or "+02:00". Leave empty for UTC.
        :return: One sentence, for example "Thursday 22 October 2026, 08:15 in Oslo (early morning, CEST, UTC+2)."
        """
        url = "https://exasecond.com/local"
        if place:
            url += "?tz=" + urllib.parse.quote(place)
        with urllib.request.urlopen(url, timeout=10) as r:
            return r.read().decode("utf-8").strip()

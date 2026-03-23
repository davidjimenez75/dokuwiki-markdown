# TODO

- [x]    [[:CSV]]---    Allow link to other Dokuwiki sections on CSV (2026-03-23)
- [x]    [[:CSV:TO-DO]]---    Allow link to other Dokuwiki sub-sections on CSV (2026-03-23)
- [x]    http://google.com    Allow link to external urls
- [x]    https://google.com    Allow link to external urls
- [x]    Obsidian links format (2026-03-23)    Convert Obsidian-style links to DokuWiki format before rendering: [[CSV]] -> [[:CSV]], [[CSV/TO-DO]] -> [[:CSV:TO-DO]], [[CSV/TO-DO/1]] -> [[:CSV:TO-DO:1]]
- [x]    TAG links format (2026-03-23)    Convert (TAG)-style content to DokuWiki links: (CSV) -> [[:CSV]], (#CSV) -> [[:CSV]], (CSV:TO-DO) -> [[:CSV:TO-DO|CSV:TO-DO]], (CSV+TO-DO) -> [[:CSV:TO-DO|CSV:TO-DO]], (CSV--TO-DO) -> [[:CSV:TO-DO|CSV:TO-DO]], (CSV---TO-DO) -> [[:CSV:TO-DO|CSV:TO-DO]]
- [x]    DATE links format (2026-03-23)    Special case for dates inside parentheses, prefix with year as namespace: (2026-03-23) -> [[:2026:2026-03-23]], (2026-03-23--010203) -> [[:2026:2026-03-23--010203]], (2026-03-23--0102) -> [[:2026:2026-03-23--0102]], (2026-03-23 01:02) -> [[:2026:2026-03-23--0102]] (space+colon-time normalized to double-dash+no-colon)
- [x]    Preserve parentheses on converted links (2026-03-23)    Keep the surrounding "(" and ")" as literal text when converting TAG, DATE and multilevel links: (2026-03-23) -> ([[:2026:2026-03-23]]), (WIKI:TO-DO) -> ([[:WIKI:TO-DO|WIKI:TO-DO]])
- [x]    Preserve # in TAG display label (2026-03-23)    When a TAG starts with #, keep it visible in the link label: (#TAGS) -> ([[:TAGS|#TAGS]]), (#tags) -> ([[:tags|#tags]])

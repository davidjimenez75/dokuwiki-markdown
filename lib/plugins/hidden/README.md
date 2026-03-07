This is an experimental fork of "Hidden" (a DokuWiki Plugin) to make Obsidian
FrontMatter 100% compatible with DokuWiki + Markdowku (Markdown on DokuWiki pages).

## Obsidian FrontMatter (native `---` delimiters)

The plugin now supports the **native Obsidian FrontMatter format** using `---`
as the delimiter. The block must start on the **very first line** of the page and
the closing `---` must appear **within the first 100 lines**.

```
---
title: My Page
tags:
  - dokuwiki
  - obsidian
  - metatags
category: notes
status: wip
updated: 2026-03-06
---
# TITLE OF THE DOKUWIKI PAGE
```

The FrontMatter block is rendered as a collapsible hidden section in DokuWiki,
exactly the same as the legacy syntax below.

## The #metatag are also supported by Obsidian (and easy to search on Dokuwiki)

Can be search on Dokuwiki with: `"#tag"`

```
---
title: hidden - obsidian compatible tag testing
tags:
  - #dokuwiki
  - #extensions
  - #extensiones
  - #metatags
  - #php
  - #wiki
  - #2026-03
category: metatags
status: wip
updated: 2026-03-06
--
# TITLE OF THE DOKUWIKI PAGE
```

## How it works

The `PARSER_WIKITEXT_PREPROCESS` event is used to detect the Obsidian `---`
delimiters **before** the DokuWiki lexer runs. If the document starts with `---`
and a matching closing `---` is found within the first 100 lines, both
delimiters are transparently converted to `<--->` / `</-->` so that the
hidden plugin can collapse the block as usual. The raw wiki source is never
modified — the conversion happens only during rendering.

![Screenshot.jpg](https://raw.githubusercontent.com/davidjimenez75/hidden-metatags/main/screenshot.jpg)


----

I'm looking for a new maintainer!

If you're a user of this plugin, don't worry: I'm still keeping an eye on it, and I'll try to make sure it keeps working fine with future versions of Dokuwiki. But my time is now dedicated to other projects so I will likely not add other features.

If you're interested in maintaining this plugin, just send me an email (adress available in the commit log) and I'll make sure to give all the information you will need.

----

Plugin for Dokuwiki

See https://www.dokuwiki.org/plugin:hidden for more info about installation and usage.

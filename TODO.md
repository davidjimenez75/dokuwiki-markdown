# TODO

--------------------------------------------------------------------------------
### - [x] 8--BIN--purge.php - Clean DokuWiki cache, history, temp files, and metadata

Script `bin/purge.php` to wipe all regenerable/disposable data from DokuWiki.
**Never touch:** `/data/pages/` nor `/data/media/` — those are sacred.
Candidates: `data/cache/`, `data/attic/`, `data/tmp/`, `data/locks/`, `data/log/`,
`data/index/`, `data/media_attic/`, `data/media_meta/`, `data/meta/`.
Support dry-run mode and selective targets via CLI arguments.

--------------------------------------------------------------------------------
### - [x] 7--MARKDOWKU--Add #TAGS syntax support (uppercase, hyphens, underscores)

--------------------------------------------------------------------------------
### - [x] 6--BIN--autogenerate.php - Add ignored files array (conf/entities.local.conf)

--------------------------------------------------------------------------------
### - [x] 5--BIN--autogenerate.php - Remove frontmatter, restructure page header

--------------------------------------------------------------------------------
### - [x] 4--CORE--parserutils.php - Fix unserialize warning on corrupted .meta files

--------------------------------------------------------------------------------
### - [x] 3--BIN--autogenerate.php - Generate pages per #TAG (TODO, TO-DO, DONE, WIP, BUG, ISSUE, GOAL)

Scan all pages for inline #TAG occurrences and generate one page per tag under auto-generated/tags/:

| Tag | Output page |
|-----|-------------|
| #TODO | auto-generated/tags/todo |
| #TO-DO | auto-generated/tags/to-do |
| #DONE | auto-generated/tags/done |
| #WIP | auto-generated/tags/wip |
| #BUG | auto-generated/tags/bug |
| #ISSUE | auto-generated/tags/issue |
| #GOAL | auto-generated/tags/goal |

Each page lists all lines containing the tag, grouped by source page (CSV block).
Invocable as: `php bin/autogenerate.php tags` or `php bin/autogenerate.php tags todo`

--------------------------------------------------------------------------------
### - [x] 2--BIN--Create documentation in english and spanish on bin scripts

--------------------------------------------------------------------------------
### - [x] 1--BIN--Create a autogenerate.php script for the auto-generated namespace

--------------------------------------------------------------------------------
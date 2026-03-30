# bin/ — Script Ideas

---

## 1. Task & Tag Management

| # | Script | Description |
|---|--------|-------------|
| 1 | `findtags.php` | Recursively find all `#TAG` and `#NS---TAG` inline tags across pages and output a CSV or grouped report |
| 2 | `tagsreport.php` | Generate a summary of all tags found: tag name, count of occurrences, list of pages that use it |
| 3 | `finddead.php` | Find all task lines (`[_]`, `[>]`, `[x]`) that have not been updated in N days (using file mtime) |
| 4 | `taskssummary.php` | Aggregate todo/wip/done counts per namespace and print a dashboard-style report |

---

## 2. Links & Pages

| # | Script | Description |
|---|--------|-------------|
| 5 | `deadlinks.php` | Find all internal links pointing to non-existent pages (similar to `wantedpages.php` but with Markdown link syntax support `[[page]]` and `[text](page)`) |
| 6 | `orphanpages.php` | Find pages that exist but are not linked from anywhere |
| 7 | `externallinks.php` | Extract all external URLs from pages and output a CSV — useful for link-rot checking |
| 8 | `checklinks.php` | Like `externallinks.php` but also performs HTTP HEAD requests to detect broken external links |

---

## 3. Frontmatter & Metadata

| # | Script | Description |
|---|--------|-------------|
| 9 | `frontmatter-audit.php` | Find pages missing frontmatter, or with incomplete fields (no `tags`, no `title`, etc.) |
| 10 | `frontmatter-export.php` | Export all frontmatter from all pages into a single CSV or JSON file |
| 11 | `frontmatter-set.php` | Add or update a frontmatter field in one or multiple pages from the command line |

---

## 4. Content & Quality

| # | Script | Description |
|---|--------|-------------|
| 12 | `emptypages.php` | Find pages that exist but have no content (or less than N characters) |
| 13 | `duplicates.php` | Detect pages with identical or very similar content using hash comparison |
| 14 | `pagesstats.php` | Print stats per namespace: page count, total size, last modified date, average size |
| 15 | `longestpages.php` | List pages sorted by line count or file size |

---

## 5. Media

| # | Script | Description |
|---|--------|-------------|
| 16 | `orphanmedia.php` | Find media files in `data/media/` not referenced by any page |
| 17 | `missingmedia.php` | Find `![alt](/media/...)` references in pages pointing to non-existent media files |
| 18 | `mediareport.php` | Generate a report of all media files: size, type, page references count |

---

## 6. Maintenance & Migration

| # | Script | Description |
|---|--------|-------------|
| 19 | `renamenamespace.php` | Recursively rename a namespace (folder) and update all internal links across all pages |
| 20 | `renamepage.php` | Rename a single page and update all links pointing to it |
| 21 | `exportsite.php` | Export all pages as a zip with their frontmatter intact — useful for Obsidian vault sync |
| 22 | `importvault.php` | Import an Obsidian vault folder into `data/pages/`, mapping vault structure to namespaces |
| 23 | `purgeattic.php` | Delete attic (revision history) entries older than N days to free disk space |

---

## 7. Search Index

| # | Script | Description |
|---|--------|-------------|
| 24 | `indexstats.php` | Show search index stats: total indexed pages, index size, last update time |
| 25 | `indexcheck.php` | Detect pages present in `data/pages/` but missing from the search index |

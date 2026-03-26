# Markdowku - TODO

## Completed

### Fix Obsidian-style links `[[...]]` to generate correct DokuWiki URLs

Obsidian links like `[[wiki/decisiones]]` were being transformed incorrectly.

**Was (wrong):**

`[[wiki/decisiones]]` → `/doku.php?id=obsidian:wiki_decisiones`

Root causes:
- DokuWiki's `cleanID()` converts `/` → `_` (not a valid namespace separator)
- Without a leading `:`, DokuWiki resolves the link relative to the current namespace

**Now (correct):**

`[[wiki/decisiones]]` → `?id=wiki:decisiones`

**Fix:** New handler `syntax/obsidianlinks.php` (sort 101) intercepts `[[...]]` before DokuWiki's native parser, converts `/` → `:`, and adds a leading `:` to force absolute resolution.

**Supported patterns:**

| Input | Displayed text | Target URL |
|-------|---------------|------------|
| `[[wiki/decisiones]]` | `wiki/decisiones` | `?id=wiki:decisiones` |
| `[[wiki/sub/page]]` | `wiki/sub/page` | `?id=wiki:sub:page` |
| `[[wiki/decisiones\|My Link]]` | `My Link` | `?id=wiki:decisiones` |
| `[[decisiones]]` | `decisiones` | `?id=decisiones` |
| `[[wiki/page#section]]` | `wiki/page#section` | `?id=wiki:page#section` |

---

### Convert UPPERCASE content in parentheses to DokuWiki links

Detect `(CONTENT)` patterns and convert them to DokuWiki internal links based on the content type.

#### Rules

**General UPPERCASE content** — outer parentheses are always preserved in the output:

| Input | Output |
|-------|--------|
| `(CSV)` | `([[:CSV]])` |
| `(#CSV)` | `([[:CSV\|#CSV]])` |
| `(NAMESPACE)` | `([[:NAMESPACE]])` |
| `(MARKDOWKU:TO-DO)` | `([[:MARKDOWKU:TO-DO\|MARKDOWKU:TO-DO]])` |
| `(CSV:TO-DO)` | `([[:CSV:TO-DO\|CSV:TO-DO]])` |
| `(CSV+TO-DO)` | `([[:CSV:TO-DO\|CSV:TO-DO]])` |
| `(CSV/TO-DO)` | `([[:CSV:TO-DO\|CSV:TO-DO]])` |
| `(CSV--TO-DO)` | `([[:CSV:TO-DO\|CSV:TO-DO]])` |
| `(CSV---TO-DO)` | `([[:CSV:TO-DO\|CSV:TO-DO]])` |

**Conversion logic:**

1. **`#TAG`** — strip `#` for link target, keep `#CONTENT` as display text:
   `(#CSV)` → `([[:CSV|#CSV]])`

2. **Contains namespace separator** (after normalization) — use normalized path as both target and display text:
   `(CSV:TO-DO)` → `([[:CSV:TO-DO|CSV:TO-DO]])`

3. **Plain UPPERCASE** — use content as target, no display text:
   `(CSV)` → `([[:CSV]])`

**Separator normalization** — these separators between UPPERCASE parts are all normalized to `:` before building the link:

| Input separator | Normalized |
|----------------|-----------|
| `:` | `:` |
| `+` | `:` |
| `/` | `:` |
| `--` | `:` |
| `---` | `:` |

Apply normalization to both target and display text.

---

**Date patterns** — content matching `YYYY-MM-DD` (with optional time suffix). Outer parentheses are **dropped** in the output:

| Input | Output |
|-------|--------|
| `(2026-03-23)` | `([[:2026:2026-03-23]])` |
| `(2026-03-23--1124)` | `([[:2026:2026-03-23--1124]])` |
| `(2026-03-23--112401)` | `([[:2026:2026-03-23--112401]])` |
| `(2026-03-23 01:02)` | `([[:2026:2026-03-23--0102]])` |

- Outer parentheses are preserved (same as non-date links)
- Prefix link with the year namespace: `[[:YYYY:content]]`
- Normalize `HH:MM` time format: replace ` HH:MM` with `--HHMM`
- Date links have no display text

---

#### Detection Pattern

Trigger on: `(` + one or more UPPERCASE letters / digits / hyphens / `+` / `/` / `_` / `#` / `:` + `)`

Do **not** trigger on:
- Lowercase content
- Already-linked content (inside `[[...]]`)
- Content inside code spans or code blocks

# Markdowku - Claude Code Instructions

## Plugin Overview

**Plugin:** Markdowku
**Type:** DokuWiki Syntax Plugin
**Purpose:** Integrates Markdown syntax into DokuWiki, translating Markdown formatting into DokuWiki's native rendering format.
**License:** BSD 2-Clause
**Maintainer:** Raphael Wimmer (raphael.wimmer@ur.de)

---

## Architecture

### Core Mechanism

Markdowku uses DokuWiki's **Syntax Plugin** system. Each Markdown feature is implemented as a separate PHP class in `syntax/`, all inheriting from `DokuWiki_Syntax_Plugin`.

**Processing flow:**
1. DokuWiki's Lexer matches patterns registered by each syntax handler
2. The handler's `handle()` method processes the matched content
3. The handler's `render()` method produces output via DokuWiki renderer calls
4. Reference links/images resolve via DokuWiki metadata (`p_get_metadata()`)

### File Structure

```
markdowku/
├── plugin.info.txt         Plugin metadata
├── README.md               Documentation
├── LICENSE                 BSD 2-Clause
├── manager.dat             Plugin manager data
└── syntax/                 One file per Markdown feature (24 files)
```

### Syntax Handler Interface

Every handler in `syntax/` implements:

| Method | Purpose |
|--------|---------|
| `getType()` | Element type: `formatting`, `substition`, `baseonly`, `protected`, `container` |
| `getPType()` | Parent type: `normal` or `block` |
| `getSort()` | Parse priority (lower = higher priority) |
| `getAllowedTypes()` | Child element types allowed inside this element |
| `connectTo($mode)` | Register entry patterns with Lexer |
| `postConnect()` | Register exit patterns with Lexer |
| `handle($match, $state, $pos, $handler)` | Process matched content |
| `render($mode, $renderer, $data)` | Render to output |

---

## Implemented Features

### Parse Priority Order (lowest sort = highest priority)

| Sort | File(s) | Feature |
|------|---------|---------|
| 8 | `hr.php` | Horizontal rules (`---`, `* * *`, `___`) |
| 9 | `ulists.php`, `olists.php` | Unordered and ordered lists |
| 49 | `headeratx.php`, `headersetext.php` | ATX (`# H1`) and Setext headers |
| 61 | `escapespecialchars.php` | Backslash escapes (`\*`, `\_`, etc.) |
| 69 | `boldasterisk.php` | Bold (`**text**`) |
| 79 | `italicasterisk.php`, `italicunderline.php` | Italic (`*text*`, `_text_`) |
| 91 | `githubcodeblocks.php` | Fenced code blocks (` ```lang `) |
| 95-99 | `codespans1-5.php` | Inline code (1-5 backticks) |
| 100 | `references.php` | Reference definitions (`[id]: url`) |
| 101 | `imagesinline.php` | Inline images (`![alt](url)`) |
| 102 | `anchorsinline.php`, `anchorsreference.php`, `imagesreference.php`, `autolinks.php` | Links and reference images |
| 103 | `parenlinks.php` | Parenthesized UPPERCASE links and date links |
| 139 | `linebreak.php` | Hard line break (two trailing spaces) |
| 199 | `codeblocks.php` | Indented code blocks (4+ spaces) |
| 219 | `blockquotes.php` | Block quotes (`> text`) |

---

## Key Implementation Details

### Class Naming Convention

```php
class syntax_plugin_markdowku_<featurename> extends DokuWiki_Syntax_Plugin
```

Examples: `syntax_plugin_markdowku_boldasterisk`, `syntax_plugin_markdowku_headeratx`

### Required File Header

```php
<?php
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
```

### Reference Link Resolution

Reference definitions (`[id]: url`) are stored in DokuWiki metadata during the `metadata` render pass:
- Key format: `markdowku_references_<ref_id>` (spaces converted to dots)
- Retrieved via: `p_get_metadata($ID, 'markdowku_references_'.$rid, METADATA_RENDER_USING_CACHE)`
- Only `references.php` processes content in `metadata` mode

### Custom Extensions in `headeratx.php`

The ATX header handler includes non-standard extensions:

1. **Task list support** - Special header levels for task states:
   - `- [_]` → H5 (undone task)
   - `- [x]` or `- [-]` → H6 (done/cancelled task)

2. **Emoji conversion** - Task checkboxes rendered as emoji:
   - `- [x]` → ✅, `- [ ]` / `- [_]` → 🔲, `- [-]` → ❌
   - `- (x)` → 🟢, `- (_)` → ⭕

3. **AI copy-paste cleanup** - Strips `**bold**`, `*italics*`, `__underline__` formatting markers from within headers

### Nested Structure Handling

Links and images support up to 6 levels of nested brackets via:
```php
$nested_brackets_re = str_repeat('(?>[^\[\]]+|\[', 6) . str_repeat('\])*', 6);
```

List handlers (`ulists.php`, `olists.php`) extend DokuWiki's `Lists` handler with custom depth tracking. Block quotes (`blockquotes.php`) extend `Quote` with nested depth via `getDepth()`.

### Hogfather RC2 Compatibility

`italicasterisk.php` and `italicunderline.php` explicitly include:
```php
require_once(DOKU_INC.'inc/Parsing/Lexer/Lexer.php');
```

---

## Code Style

- **Indentation:** 4-space indentation (VIM modeline at file end: `// ex: et ts=4 enc=utf-8`)
- **PHP standard:** No strict PSR enforcement; follows DokuWiki plugin conventions
- **Regex:** Heavy use of lookaheads/lookbehinds for precise Markdown boundary detection
- All regex patterns avoid false positives with escaped character detection (`(?<![\\])`)

---

## Dependencies

**DokuWiki core only — no external dependencies.**

Core classes used:
- `DokuWiki_Syntax_Plugin` — base class
- `dokuwiki\Parsing\Handler\Preformatted` — code block rewriter
- `dokuwiki\Parsing\Handler\Quote` — blockquote rewriter
- `dokuwiki\Parsing\Handler\Lists` — list rewriter
- `dokuwiki\Parsing\Lexer\Lexer` — compatibility include

Core functions used:
- `p_get_metadata()` — reference link resolution
- `plugin_load()` — dynamic plugin loading
- Renderer methods: `strong_open/close`, `emphasis_open/close`, `monospace_open/close`, `code()`, `file()`, `linebreak()`, `externallink()`, `emaillink()`, `internallink()`, `_media()`

---

## Working Instructions

### Adding a New Markdown Feature

1. Create `syntax/<featurename>.php`
2. Name the class `syntax_plugin_markdowku_<featurename>`
3. Implement all required interface methods
4. Choose `getSort()` carefully relative to existing handlers (see priority table above)
5. Use `addSpecialPattern()` for single-match elements, entry/exit pairs for block elements

### Modifying Existing Handlers

- Read the target file fully before making changes
- Regex patterns are sensitive — test thoroughly with edge cases (nested elements, escaped chars, start/end of document)
- `getSort()` values must not conflict with existing handlers unless intentional

### Debugging

- DokuWiki's `msg()` function can output debug info during development
- Metadata storage issues: verify reference definitions are on their own line with correct format
- Pattern conflicts: adjust `getSort()` and check `getAllowedTypes()` for the affected handlers

### Testing

No automated test suite exists. Test manually by:
1. Creating DokuWiki pages with Markdown content
2. Verifying rendered HTML output
3. Testing edge cases: nested elements, escaped characters, mixed Markdown + DokuWiki syntax
4. Testing reference links (requires two-pass render: metadata then xhtml)

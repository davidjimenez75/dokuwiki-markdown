# autogenerate.php

CLI tool that auto-generates wiki pages under `auto-generated/` based on pattern searches across all pages. The generated pages embed a CSV block (DokuWiki CSV plugin) with clickable `[[links]]` to the source pages.

## Generated pages

| Page | Patterns searched |
|------|------------------|
| `auto-generated/tasks/to-do` | `[_]` and `[ ]` |
| `auto-generated/tasks/wip` | `[>]` and `[WIP]` |
| `auto-generated/tasks/done` | `[x]` |

The `auto-generated/` directory is always excluded from scanning to prevent recursion.

## Arguments

| Argument | Description |
|----------|-------------|
| `[group]` | Group to generate: `tasks`. Omit to generate all groups. |
| `[name]` | Specific page within the group: `to-do`, `wip`, `done`. Omit to generate all in group. |

## Options

| Option | Description |
|--------|-------------|
| `-d` / `--dry-run` | Show what would be generated without writing any files |

## Usage

```bash
# Generate all pages
php bin/autogenerate.php

# Generate task pages only
php bin/autogenerate.php tasks

# Generate a single task page
php bin/autogenerate.php tasks to-do
php bin/autogenerate.php tasks wip
php bin/autogenerate.php tasks done

# Preview without writing
php bin/autogenerate.php --dry-run
```

## Output format

Each generated page contains:
- YAML frontmatter with `auto-generated` tag
- A warning block with timestamp, result count, patterns used and scan time
- A `<csv>` block with two columns: `file` (clickable `[[wiki/link]]`) and `content` (matching line)

## Example output

```
> **Auto-generated page** — do not edit manually.
> Last updated: 2026-03-30 12:00:00 | 3 result(s) | Patterns: `[_]`, `[ ]` | Scan time: 0.021s

# Pending Tasks

<csv>
file,content
[[markdowku/to-do]],  "### - [_] Add #TAGS support"
[[projects/ideas]],   "- [ ] Review documentation"
</csv>
```

## Notes

- Scans both `.md` and `.txt` files.
- Results are sorted by page path (first column).
- Directories under `auto-generated/` are created automatically if they do not exist.
- Designed to be run via cron for always up-to-date task pages.

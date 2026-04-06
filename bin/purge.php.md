# purge.php

CLI tool that purges DokuWiki regenerable and disposable data. Operates in three cumulative tiers of aggressiveness. Never touches `data/pages/` or `data/media/` under any circumstance.

## Tiers

| Tier | Targets | Data loss |
|------|---------|-----------|
| `--safe` | `cache/`, `tmp/`, `locks/` | None — all fully regenerable |
| `--full` | safe + `index/`, `meta/`, `media_meta/` | Minimal — metadata rebuilt on next visit |
| `--nuclear` | full + `attic/`, `media_attic/` | **Destructive** — page and media history permanently deleted |

Each tier is cumulative: `--full` includes safe targets, `--nuclear` includes full targets.

## Options

| Option | Description |
|--------|-------------|
| `-s` / `--safe` | Delete cache, tmp, and lock files (zero data loss) |
| `-f` / `--full` | safe + search index, page metadata, media metadata |
| `-n` / `--nuclear` | full + page history (attic) and media history. **DESTRUCTIVE** |
| `-d` / `--dry-run` | Show what would be deleted without removing anything |

## Usage

```bash
# Clean only cache, tmp and locks (safe for production)
php bin/purge.php --safe

# Clean everything except history
php bin/purge.php --full

# Clean everything including revision history
php bin/purge.php --nuclear

# Preview any tier without deleting
php bin/purge.php --safe --dry-run
php bin/purge.php --full --dry-run
php bin/purge.php --nuclear --dry-run
```

## Target details

| Directory | Content | Regenerable |
|-----------|---------|-------------|
| `data/cache/` | Rendered HTML, parsed page cache | Yes, on next page visit |
| `data/tmp/` | Temporary upload and processing files | Yes |
| `data/locks/` | Stale edit lock files | Yes |
| `data/index/` | Full-text search index | Yes, via `bin/indexer.php` or next visit |
| `data/meta/` | Page metadata: dates, contributors, backlinks | Partially — rebuilt on edit/visit |
| `data/media_meta/` | Media metadata: dimensions, EXIF, thumbnails | Yes, on next media access |
| `data/attic/` | Page revision history (old versions) | **No** — permanently lost |
| `data/media_attic/` | Media revision history (old uploads) | **No** — permanently lost |

## Output example

```
DRY RUN — no files will be deleted.
[DRY-RUN] data/cache/          732 files  11.3 MB  — Rendered HTML cache and parsed page cache
[DRY-RUN] data/tmp/             24 files  32 B     — Temporary upload and processing files
[DRY-RUN] data/locks/            1 files  0 B      — Stale edit lock files
Would free: 757 files, 11.3 MB
```

## Notes

- Directory structure is preserved — only file contents are deleted, not the directories themselves.
- After running `--full` or `--nuclear`, run `php bin/indexer.php` to rebuild the search index immediately.
- Safe to run while the wiki is live for `--safe` tier; prefer maintenance mode for `--nuclear`.
- Designed to be run manually or via cron for periodic cleanup.

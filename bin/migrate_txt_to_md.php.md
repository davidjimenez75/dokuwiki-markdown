# migrate_txt_to_md.php

Migration script that renames all wiki page files from `.txt` to `.md`, including revision history (attic) and namespace templates.

## Function

Walks DokuWiki's data directories in three phases and renames files:

| Phase | Directory | Change |
|-------|-----------|--------|
| 1 | `data/pages/` | `.txt` → `.md` |
| 2 | `data/attic/` | `.txt.gz` → `.md.gz`, `.txt.bz2` → `.md.bz2` |
| 3 | `data/pages/` | `_template.txt` → `_template.md` |

Before making any real changes, automatically creates a backup at `data/backup_migration_YYYYMMDD_HHMMSS/`.

## Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Simulate the migration without making any changes on disk |
| `--verbose` | Show each file being processed |
| `--help` | Show help |

## Typical usage

```bash
# Simulate first to verify what would happen
php bin/migrate_txt_to_md.php --dry-run --verbose

# Run the real migration
php bin/migrate_txt_to_md.php

# Real migration with detailed output
php bin/migrate_txt_to_md.php --verbose
```

## Warning

This operation is **irreversible** without using the rollback script or the generated backup. Do not run until all required changes to core PHP files are complete.

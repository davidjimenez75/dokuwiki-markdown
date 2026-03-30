# rebuild_after_migration.sh

Bash script for post-migration rebuilding. Clears DokuWiki caches, indexes and locks, then rebuilds the search index and fixes permissions after running `migrate_txt_to_md.php`.

## Function

Runs 7 phases in sequence:

| Phase | Action |
|-------|--------|
| 1 | Deletes all files in `data/cache/` |
| 2 | Deletes all files in `data/index/` |
| 3 | Removes stale locks in `data/locks/` |
| 4 | Removes `.indexed` markers in `data/meta/` |
| 5 | Runs `bin/indexer.php -c` to rebuild the search index from scratch |
| 6 | Sets ownership to `www-data:www-data` with `chmod 755` on `data/` |
| 7 | Verifies the final state by counting `.txt` and `.md` files in `pages/` and `attic/` |

## Options

| Option | Description |
|--------|-------------|
| `--dry-run` | Simulate all phases without making any changes on disk |

## Typical usage

```bash
# Simulate to verify what would happen
./bin/rebuild_after_migration.sh --dry-run

# Run the real rebuild
./bin/rebuild_after_migration.sh
```

## Notes

- Phase 6 (permissions) requires root for `chown`. If it fails, a warning is shown but execution continues.
- Phase 7 detects mixed state (both `.txt` and `.md` files coexisting) and returns exit code `1` as a warning.
- Must be run **after** `migrate_txt_to_md.php` and after updating the core PHP files.

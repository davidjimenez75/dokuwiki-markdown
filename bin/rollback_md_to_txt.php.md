# rollback_md_to_txt.php

Rollback script that reverts the migration performed by `migrate_txt_to_md.php`, renaming files from `.md` back to `.txt`.

## Function

Offers two modes of operation:

- **From backup** (`--from-backup`): restores the full contents of `data/pages/` and `data/attic/` from the backup directory generated during migration. This is the safest method.
- **Direct rename**: walks the current directories and renames `.md` → `.txt` in three phases (pages, attic, templates), without needing a backup.

## Options

| Option | Description |
|--------|-------------|
| `--from-backup=/path` | Restore from the specified backup directory (recommended) |
| `--dry-run` | Simulate the rollback without making any changes on disk |
| `--verbose` | Show each file being processed |
| `--help` | Show help |

## Typical usage

```bash
# Simulate the rollback to verify what would happen
php bin/rollback_md_to_txt.php --dry-run

# Full rollback from backup (recommended)
php bin/rollback_md_to_txt.php --from-backup=data/backup_migration_20251230_150000/

# Rollback by direct rename (without backup)
php bin/rollback_md_to_txt.php --verbose
```

## Warning

After the rollback, changes made to core PHP files (`inc/pageutils.php`, `inc/search.php`, etc.) must also be reverted manually. The script reminds you of this upon completion.

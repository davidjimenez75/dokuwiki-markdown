# indexer.php

CLI tool for updating the DokuWiki search index from the command line.

## Function

Walks all wiki pages and indexes new or modified ones so they appear in search results. Useful for rebuilding the index after migrations, bulk page imports, or when the index becomes out of sync.

## Options

| Option | Description |
|--------|-------------|
| `-c` / `--clear` | Clears the entire index before reindexing |
| `-q` / `--quiet` | Suppresses all output |

## Typical usage

```bash
# Index new or modified pages
php bin/indexer.php

# Rebuild the index from scratch
php bin/indexer.php -c

# Silent run (for cron)
php bin/indexer.php -q
```

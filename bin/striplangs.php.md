# striplangs.php

CLI tool for removing unnecessary language files from a DokuWiki installation, reducing disk usage.

## Function

Recursively deletes language directories from:
- `inc/lang/` (core languages)
- `lib/plugins/*/lang/` (plugin languages)
- `lib/tpl/*/lang/` (template languages)

English (`en`) is **never removed**, regardless of the options given.

## Options

| Option | Description |
|--------|-------------|
| `-k` / `--keep <codes>` | Comma-separated list of language codes to keep in addition to English (e.g. `es,fr,de`) |
| `-e` / `--english-only` | Remove all languages except English |

## Typical usage

```bash
# Keep only Spanish and English
php bin/striplangs.php -k es

# Keep English, Spanish and French
php bin/striplangs.php -k es,fr

# Keep English only
php bin/striplangs.php -e
```

## Warning

This operation is **irreversible**. Deleted directories cannot be recovered without reinstalling the extensions.

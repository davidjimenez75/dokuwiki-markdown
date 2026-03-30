# wantedpages.php

CLI tool for detecting linked pages that do not yet exist in the wiki ("wanted pages").

## Function

Walks all pages in a namespace (or the entire wiki), parses their internal links, and reports those pointing to non-existent pages along with the origin page. Useful for finding broken links or planning content creation.

## Arguments and options

| Parameter | Description |
|-----------|-------------|
| `[namespace]` | Namespace to scan. Defaults to the entire wiki |
| `-s` / `--sort (wanted\|origin)` | Sort by wanted page (`wanted`, default) or by origin page (`origin`) |
| `-k` / `--skip` | Show only the first column (omit the related page) |

## Output

By default prints two columns: the missing page and the page that links to it.

```
missing:page                namespace:origin_page
another:missing_article     start
```

## Typical usage

```bash
# Find wanted pages across the entire wiki
php bin/wantedpages.php

# Scan a specific namespace
php bin/wantedpages.php my:namespace

# Sort by origin page
php bin/wantedpages.php -s origin

# List only wanted pages (without origin)
php bin/wantedpages.php -k
```

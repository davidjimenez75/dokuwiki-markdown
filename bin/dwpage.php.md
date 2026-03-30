# dwpage.php

CLI tool for editing DokuWiki pages from the command line while preserving revision history.

## Commands

| Command | Description |
|---------|-------------|
| `checkout <wikipage> [workingfile]` | Copies a wiki page to a local file and obtains the edit lock |
| `commit <workingfile> <wikipage> -m <message>` | Saves the local file as a new revision in the wiki and releases the lock |
| `lock <wikipage>` | Obtains or renews the edit lock for a page |
| `unlock <wikipage>` | Releases the edit lock for a page |
| `getmeta <wikipage> [key]` | Prints page metadata as JSON |

## Global options

- `-f` / `--force` — force obtaining the lock even if held by another user
- `-u` / `--user <username>` — act as the given user (defaults to the current system user)

## Typical usage

```bash
php bin/dwpage.php checkout my:page local_file.txt
# ... edit local_file.txt ...
php bin/dwpage.php commit local_file.txt my:page -m "Description of change"
```

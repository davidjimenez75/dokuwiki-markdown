# findtasks.php

Unified CLI tool that recursively searches for task lines inside `data/pages/` and generates a CSV with the relative file path and the matching line content.

## Modes

| Mode | Patterns | Description |
|------|----------|-------------|
| `todo` | `[_]` and `[ ]` | Pending tasks (DokuWiki + Markdown) |
| `wip`  | `[>]`            | Work in progress |
| `done` | `[x]`            | Completed tasks |

Running without arguments generates all three CSV files at once (`todo.csv`, `wip.csv`, `done.csv`), sorted by file path.

## Options

| Option | Description |
|--------|-------------|
| `-o` / `--output <file>` | Write CSV to this file instead of stdout |
| `-p` / `--pattern <string>` | Override the mode patterns and search for this single string |

## Typical usage

```bash
# Generate all 3 CSV files at once
php bin/findtasks.php

# Print pending tasks to stdout
php bin/findtasks.php todo

# Save WIP tasks to CSV
php bin/findtasks.php wip -o wip.csv

# Save completed tasks to CSV
php bin/findtasks.php done -o done.csv

# Save pending tasks to CSV
php bin/findtasks.php todo -o todo.csv

# Override pattern
php bin/findtasks.php todo -p "[TODO]"
```

## Example output

```
dokuwiki/to-do.txt;### - [_] blablabla
dokuwiki/to-do.txt;- [ ] Task in Markdown format
projects/ideas.txt;- [_] Review documentation
```

## Notes

- Uses `$conf['datadir']` from DokuWiki config, respecting custom paths.
- Only processes `.txt` files.
- CSV separator is `;`.
- Results are sorted by file path (first column).
- Replaces the former `findwip.php` and `finddone.php` scripts.

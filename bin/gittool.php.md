# gittool.php

CLI tool for managing Git repositories for DokuWiki, its plugins and templates.

## Commands

| Command | Description |
|---------|-------------|
| `clone <extension> [...]` | Installs an extension via `git clone` by looking up its repo on DokuWiki.org. Prefix with `template:` for templates |
| `install <extension> [...]` | Same as `clone`, but falls back to zip download if no Git repo is found |
| `repos` | Lists all Git repositories found in the installation (root, plugins, templates) |
| `<git command> [args]` | Any other command is executed as `git <command>` in all found repositories |

## Supported repository sources

- **GitHub** — `github.com/user/repo`
- **Gitorious** — `gitorious.org/user/repo` (obsolete)
- **Bitbucket** — `bitbucket.org/user/repo`

## Typical usage

```bash
# Install a plugin via git
php bin/gittool.php clone gallery

# Install a template
php bin/gittool.php clone template:bootstrap3

# Check status of all repos
php bin/gittool.php status

# Pull all repos
php bin/gittool.php pull
```

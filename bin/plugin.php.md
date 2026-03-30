# plugin.php

CLI entry point for running command-line interfaces exposed by DokuWiki plugins.

## Function

Discovers and launches plugins that implement the `CLIPlugin` interface (`cli_plugin_<name>` class). Without arguments, lists all available CLI plugins with their descriptions.

## Usage

```bash
# List all available CLI plugins
php bin/plugin.php

# Run a specific plugin's CLI
php bin/plugin.php <plugin_name> [arguments...]
```

## Example

If a plugin named `acl` provides a CLI:

```bash
php bin/plugin.php acl --help
```

## Notes

- Only shows plugins that are enabled and implement `CLIPlugin`
- Arguments after the plugin name are passed directly to the plugin

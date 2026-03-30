# render.php

CLI tool for rendering DokuWiki syntax from stdin and printing the result to stdout.

## Function

Reads DokuWiki-formatted text from stdin, processes it with the rendering engine, and outputs the result. Useful for previewing markup, debugging syntax plugins, or integrating rendering into external pipelines.

## Options

| Option | Description |
|--------|-------------|
| `-r` / `--renderer <mode>` | Renderer mode to use. Defaults to `xhtml` |

## Typical usage

```bash
# Render a file to HTML
cat my_page.txt | php bin/render.php

# Render with an alternate mode
echo "====== Title ======" | php bin/render.php -r metadata
```

## Notes

- May not work with plugins that require a web environment to be initialized
- Works correctly with all standard DokuWiki markup

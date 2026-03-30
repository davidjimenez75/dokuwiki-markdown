# plugin.php

Herramienta CLI que actúa como punto de entrada para ejecutar los comandos CLI que expongan los plugins de DokuWiki.

## Función

Descubre y lanza plugins que implementen la interfaz `CLIPlugin` (clase `cli_plugin_<nombre>`). Sin argumentos, lista todos los plugins CLI disponibles con su descripción.

## Uso

```bash
# Listar todos los plugins con interfaz CLI disponibles
php bin/plugin.php

# Ejecutar el CLI de un plugin concreto
php bin/plugin.php <nombre_plugin> [argumentos...]
```

## Ejemplo

Si existe un plugin llamado `acl` con CLI, se invoca así:

```bash
php bin/plugin.php acl --help
```

## Notas

- Solo muestra plugins que estén habilitados y que implementen `CLIPlugin`
- Los argumentos restantes tras el nombre del plugin se pasan directamente al plugin

# indexer.php

Herramienta CLI para actualizar el índice de búsqueda de DokuWiki desde la línea de comandos.

## Función

Recorre todas las páginas del wiki e indexa las nuevas o modificadas para que aparezcan en los resultados de búsqueda. Útil para reconstruir el índice tras migraciones, importaciones masivas de páginas o cuando el índice queda desincronizado.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-c` / `--clear` | Borra completamente el índice antes de reindexar |
| `-q` / `--quiet` | Suprime toda salida por pantalla |

## Uso típico

```bash
# Indexar páginas nuevas o modificadas
php bin/indexer.php

# Reconstruir el índice desde cero
php bin/indexer.php -c

# Ejecución silenciosa (para cron)
php bin/indexer.php -q
```

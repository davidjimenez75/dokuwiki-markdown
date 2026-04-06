# purge.php

Herramienta CLI que elimina datos regenerables y prescindibles de DokuWiki. Opera en tres niveles de agresividad acumulativos. Nunca toca `data/pages/` ni `data/media/` bajo ninguna circunstancia.

## Niveles

| Nivel | Targets | Pérdida de datos |
|-------|---------|-----------------|
| `--safe` | `cache/`, `tmp/`, `locks/` | Ninguna — todo completamente regenerable |
| `--full` | safe + `index/`, `meta/`, `media_meta/` | Mínima — los metadatos se reconstruyen en la siguiente visita |
| `--nuclear` | full + `attic/`, `media_attic/` | **Destructivo** — historial de páginas y multimedia eliminado permanentemente |

Cada nivel es acumulativo: `--full` incluye los targets de safe, `--nuclear` incluye los de full.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-s` / `--safe` | Elimina cache, tmp y locks (sin pérdida de datos) |
| `-f` / `--full` | safe + índice de búsqueda, metadatos de páginas y multimedia |
| `-n` / `--nuclear` | full + historial de páginas (attic) e historial multimedia. **DESTRUCTIVO** |
| `-d` / `--dry-run` | Muestra lo que se borraría sin eliminar nada |

## Uso típico

```bash
# Limpiar solo cache, tmp y locks (seguro en producción)
php bin/purge.php --safe

# Limpiar todo excepto el historial
php bin/purge.php --full

# Limpiar todo incluyendo el historial de revisiones
php bin/purge.php --nuclear

# Previsualizar cualquier nivel sin borrar nada
php bin/purge.php --safe --dry-run
php bin/purge.php --full --dry-run
php bin/purge.php --nuclear --dry-run
```

## Detalle de los targets

| Directorio | Contenido | Regenerable |
|------------|-----------|-------------|
| `data/cache/` | HTML renderizado, caché de páginas parseadas | Sí, en la siguiente visita a la página |
| `data/tmp/` | Archivos temporales de subida y procesamiento | Sí |
| `data/locks/` | Archivos de bloqueo de edición caducados | Sí |
| `data/index/` | Índice de búsqueda full-text | Sí, via `bin/indexer.php` o en la siguiente visita |
| `data/meta/` | Metadatos de páginas: fechas, colaboradores, backlinks | Parcialmente — se reconstruye al editar o visitar |
| `data/media_meta/` | Metadatos multimedia: dimensiones, EXIF, miniaturas | Sí, en el siguiente acceso al archivo |
| `data/attic/` | Historial de revisiones de páginas (versiones antiguas) | **No** — se pierde permanentemente |
| `data/media_attic/` | Historial de revisiones multimedia (subidas antiguas) | **No** — se pierde permanentemente |

## Ejemplo de salida

```
DRY RUN — no files will be deleted.
[DRY-RUN] data/cache/          732 files  11.3 MB  — Rendered HTML cache and parsed page cache
[DRY-RUN] data/tmp/             24 files  32 B     — Temporary upload and processing files
[DRY-RUN] data/locks/            1 files  0 B      — Stale edit lock files
Would free: 757 files, 11.3 MB
```

## Notas

- La estructura de directorios se conserva — solo se eliminan los archivos, no los directorios en sí.
- Tras ejecutar `--full` o `--nuclear`, ejecutar `php bin/indexer.php` para reconstruir el índice de búsqueda de inmediato.
- El nivel `--safe` es seguro con el wiki en producción; para `--nuclear` se recomienda modo mantenimiento.
- Diseñado para ejecutarse manualmente o via cron para limpiezas periódicas.

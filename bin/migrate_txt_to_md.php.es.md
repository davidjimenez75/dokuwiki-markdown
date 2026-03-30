# migrate_txt_to_md.php

Script de migración que renombra todos los archivos de páginas wiki de `.txt` a `.md`, incluyendo el historial (attic) y las plantillas de namespace.

## Función

Recorre en tres fases los directorios de datos de DokuWiki y renombra los archivos:

| Fase | Directorio | Cambio |
|------|-----------|--------|
| 1 | `data/pages/` | `.txt` → `.md` |
| 2 | `data/attic/` | `.txt.gz` → `.md.gz`, `.txt.bz2` → `.md.bz2` |
| 3 | `data/pages/` | `_template.txt` → `_template.md` |

Antes de realizar cambios reales, crea automáticamente un backup en `data/backup_migration_YYYYMMDD_HHMMSS/`.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `--dry-run` | Simula la migración sin realizar cambios en disco |
| `--verbose` | Muestra cada archivo procesado |
| `--help` | Muestra la ayuda |

## Uso típico

```bash
# Simular primero para verificar qué se haría
php bin/migrate_txt_to_md.php --dry-run --verbose

# Ejecutar la migración real
php bin/migrate_txt_to_md.php

# Migración real con salida detallada
php bin/migrate_txt_to_md.php --verbose
```

## Advertencia

Esta operación es **irreversible** sin usar el script de rollback o el backup generado. No ejecutar hasta que todos los cambios necesarios en los archivos PHP del núcleo estén completos.

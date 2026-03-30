# rollback_md_to_txt.php

Script de rollback que revierte la migración realizada por `migrate_txt_to_md.php`, renombrando los archivos de `.md` de vuelta a `.txt`.

## Función

Ofrece dos modos de operación:

- **Desde backup** (`--from-backup`): restaura el contenido completo de `data/pages/` y `data/attic/` desde el directorio de backup generado por la migración. Es el método más seguro.
- **Renombrado directo**: recorre los directorios actuales y renombra `.md` → `.txt` en tres fases (páginas, attic, plantillas), sin necesidad de backup.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `--from-backup=/ruta` | Restaura desde el backup indicado (recomendado) |
| `--dry-run` | Simula el rollback sin realizar cambios en disco |
| `--verbose` | Muestra cada archivo procesado |
| `--help` | Muestra la ayuda |

## Uso típico

```bash
# Simular el rollback para verificar qué se haría
php bin/rollback_md_to_txt.php --dry-run

# Rollback completo desde backup (recomendado)
php bin/rollback_md_to_txt.php --from-backup=data/backup_migration_20251230_150000/

# Rollback por renombrado directo (sin backup)
php bin/rollback_md_to_txt.php --verbose
```

## Advertencia

Tras el rollback también es necesario revertir manualmente los cambios realizados en los archivos PHP del núcleo (`inc/pageutils.php`, `inc/search.php`, etc.). El propio script lo recuerda al finalizar.

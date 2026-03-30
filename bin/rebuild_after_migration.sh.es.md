# rebuild_after_migration.sh

Script Bash de reconstrucción post-migración. Limpia cachés, índices y locks de DokuWiki, luego reconstruye el índice de búsqueda y ajusta permisos tras ejecutar `migrate_txt_to_md.php`.

## Función

Ejecuta 7 fases en orden secuencial:

| Fase | Acción |
|------|--------|
| 1 | Elimina todos los archivos de `data/cache/` |
| 2 | Elimina todos los archivos de `data/index/` |
| 3 | Elimina locks antiguos en `data/locks/` |
| 4 | Elimina marcadores `.indexed` en `data/meta/` |
| 5 | Ejecuta `bin/indexer.php -c` para reconstruir el índice desde cero |
| 6 | Ajusta permisos a `www-data:www-data` con `chmod 755` en `data/` |
| 7 | Verifica el estado final contando archivos `.txt` y `.md` en `pages/` y `attic/` |

## Opciones

| Opción | Descripción |
|--------|-------------|
| `--dry-run` | Simula todas las fases sin realizar cambios en disco |

## Uso típico

```bash
# Simular para verificar qué haría
./bin/rebuild_after_migration.sh --dry-run

# Ejecutar la reconstrucción real
./bin/rebuild_after_migration.sh
```

## Notas

- La fase 6 (permisos) requiere privilegios de root para `chown`. Si falla, muestra un aviso pero continúa.
- La fase 7 detecta estado mixto (archivos `.txt` y `.md` coexistiendo) y devuelve código de salida `1` como advertencia.
- Debe ejecutarse **después** de `migrate_txt_to_md.php` y de haber actualizado los archivos PHP del núcleo.

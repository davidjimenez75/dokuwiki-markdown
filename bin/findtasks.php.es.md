# findtasks.php

Herramienta CLI unificada que busca de forma recursiva líneas de tareas dentro de `data/pages/` y genera un CSV con la ruta relativa del archivo y el contenido de la línea.

## Modos

| Modo | Patrones | Descripción |
|------|----------|-------------|
| `todo` | `[_]` y `[ ]` | Tareas pendientes (DokuWiki + Markdown) |
| `wip`  | `[>]`          | Tareas en progreso |
| `done` | `[x]`          | Tareas completadas |

El modo `todo` busca ambos patrones a la vez y ordena los resultados por ruta de archivo.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-o` / `--output <archivo>` | Escribe el CSV en el archivo indicado en lugar de mostrarlo por pantalla |
| `-p` / `--pattern <cadena>` | Sobreescribe los patrones del modo y usa esta cadena única |

## Uso típico

```bash
# Mostrar tareas pendientes por pantalla
php bin/findtasks.php todo

# Guardar tareas WIP en CSV
php bin/findtasks.php wip -o wip.csv

# Guardar tareas completadas en CSV
php bin/findtasks.php done -o done.csv

# Guardar tareas pendientes en CSV
php bin/findtasks.php todo -o tasks.csv

# Usar un patrón personalizado
php bin/findtasks.php todo -p "[TODO]"
```

## Ejemplo de salida

```
dokuwiki/to-do.txt;### - [_] blablabla
dokuwiki/to-do.txt;- [ ] Tarea en formato Markdown
proyectos/ideas.txt;- [_] Revisar documentación
```

## Notas

- Utiliza `$conf['datadir']` de la configuración de DokuWiki, respetando rutas personalizadas.
- Solo procesa archivos con extensión `.txt`.
- El separador del CSV es `;`.
- Los resultados se ordenan por ruta de archivo (primera columna).
- Reemplaza a los scripts `findwip.php` y `finddone.php`.

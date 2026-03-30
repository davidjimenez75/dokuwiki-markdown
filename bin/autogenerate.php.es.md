# autogenerate.php

Herramienta CLI que auto-genera páginas wiki bajo `auto-generated/` basándose en búsquedas de patrones en todas las páginas. Las páginas generadas incluyen un bloque CSV (plugin CSV de DokuWiki) con enlaces `[[clickables]]` a las páginas de origen.

## Páginas generadas

| Página | Patrones buscados |
|--------|------------------|
| `auto-generated/tasks/to-do` | `[_]` y `[ ]` |
| `auto-generated/tasks/wip` | `[>]` y `[WIP]` |
| `auto-generated/tasks/done` | `[x]` |

El directorio `auto-generated/` se excluye siempre del escaneo para evitar recursión.

## Detección automática de versión

El script detecta automáticamente si la instalación es DokuWiki estándar o el fork dokuwiki-markdown muestreando hasta 100 archivos en `data/pages/`:

- **Mayoría `.txt`** → DokuWiki estándar, genera archivos `.txt`, sin mensaje
- **Mayoría `.md`** → fork dokuwiki-markdown, genera archivos `.md`, muestra aviso al inicio

## Argumentos

| Argumento | Descripción |
|-----------|-------------|
| `[group]` | Grupo a generar: `tasks`. Omitir para generar todos los grupos. |
| `[name]` | Página concreta dentro del grupo: `to-do`, `wip`, `done`. Omitir para generar todas. |

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-d` / `--dry-run` | Muestra lo que se generaría sin escribir ningún archivo |

## Uso típico

```bash
# Generar todas las páginas
php bin/autogenerate.php

# Generar solo las páginas de tareas
php bin/autogenerate.php tasks

# Generar una página concreta
php bin/autogenerate.php tasks to-do
php bin/autogenerate.php tasks wip
php bin/autogenerate.php tasks done

# Previsualizar sin escribir
php bin/autogenerate.php --dry-run
```

## Formato de las páginas generadas

Cada página generada contiene:
- Frontmatter YAML con el tag `auto-generated`
- Bloque de aviso con timestamp, número de resultados, patrones usados y tiempo de escaneo
- Bloque `<csv>` con dos columnas: `file` (enlace `[[wiki/link]]` clickable) y `content` (línea encontrada)

## Ejemplo de salida

```
> **Auto-generated page** — do not edit manually.
> Last updated: 2026-03-30 12:00:00 | 3 result(s) | Patterns: `[_]`, `[ ]` | Scan time: 0.021s

# Pending Tasks

<csv>
file,content
[[markdowku/to-do]],"### - [_] Añadir soporte de #TAGS"
[[projects/ideas]],"- [ ] Revisar documentación"
</csv>
```

## Notas

- Escanea archivos `.md` o `.txt` según la versión detectada.
- Los resultados se ordenan por ruta de archivo (primera columna).
- Los directorios bajo `auto-generated/` se crean automáticamente si no existen.
- Diseñado para ejecutarse vía cron y mantener las páginas de tareas siempre actualizadas.

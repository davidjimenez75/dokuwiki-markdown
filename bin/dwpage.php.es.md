# dwpage.php

Herramienta CLI para editar páginas de DokuWiki desde la línea de comandos manteniendo el historial de revisiones.

## Comandos

| Comando | Descripción |
|---------|-------------|
| `checkout <wikipage> [workingfile]` | Copia una página del wiki a un archivo local y obtiene el lock de edición |
| `commit <workingfile> <wikipage> -m <mensaje>` | Guarda el archivo local como nueva revisión en el wiki y libera el lock |
| `lock <wikipage>` | Obtiene o renueva el lock de edición de una página |
| `unlock <wikipage>` | Libera el lock de edición de una página |
| `getmeta <wikipage> [clave]` | Muestra los metadatos de una página en formato JSON |

## Opciones globales

- `-f` / `--force` — fuerza la obtención del lock aunque esté tomado por otro usuario
- `-u` / `--user <username>` — actúa como el usuario indicado (por defecto usa el usuario del sistema)

## Uso típico

```bash
php bin/dwpage.php checkout mi:pagina fichero_local.txt
# ... editar fichero_local.txt ...
php bin/dwpage.php commit fichero_local.txt mi:pagina -m "Descripción del cambio"
```

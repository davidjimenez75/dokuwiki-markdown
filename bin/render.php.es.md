# render.php

Herramienta CLI para renderizar sintaxis DokuWiki desde la entrada estándar (stdin) y obtener el resultado por stdout.

## Función

Lee texto en formato DokuWiki desde stdin, lo procesa con el motor de renderizado y emite el resultado. Útil para previsualizar markup, depurar plugins de sintaxis o integrar el renderizado en pipelines externos.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-r` / `--renderer <modo>` | Modo de renderizado a usar. Por defecto: `xhtml` |

## Uso típico

```bash
# Renderizar un archivo a HTML
cat mi_pagina.txt | php bin/render.php

# Renderizar con modo alternativo (ej. metadata)
echo "====== Título ======" | php bin/render.php -r metadata
```

## Notas

- Puede no funcionar con plugins que requieran entorno web inicializado
- Funciona correctamente con todo el markup estándar de DokuWiki

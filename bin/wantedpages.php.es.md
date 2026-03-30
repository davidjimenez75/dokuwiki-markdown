# wantedpages.php

Herramienta CLI para detectar páginas enlazadas que aún no existen en el wiki ("páginas deseadas").

## Función

Recorre todas las páginas de un namespace (o el wiki completo), analiza sus enlaces internos y muestra aquellos que apuntan a páginas inexistentes, junto con la página de origen del enlace. Útil para detectar enlaces rotos o planificar la creación de contenido.

## Argumentos y opciones

| Parámetro | Descripción |
|-----------|-------------|
| `[namespace]` | Namespace a analizar. Si se omite, analiza todo el wiki |
| `-s` / `--sort (wanted\|origin)` | Ordena por página deseada (`wanted`, por defecto) o por página de origen (`origin`) |
| `-k` / `--skip` | Muestra solo la primera dimensión (sin mostrar la página relacionada) |

## Salida

Por defecto imprime dos columnas: la página inexistente y la página que la enlaza.

```
pagina:inexistente          namespace:pagina_origen
otro:articulo_faltante      start
```

## Uso típico

```bash
# Buscar páginas deseadas en todo el wiki
php bin/wantedpages.php

# Buscar en un namespace concreto
php bin/wantedpages.php mi:namespace

# Ordenar por página de origen
php bin/wantedpages.php -s origin

# Listar solo las páginas deseadas (sin origen)
php bin/wantedpages.php -k
```

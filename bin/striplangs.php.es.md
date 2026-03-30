# striplangs.php

Herramienta CLI para eliminar archivos de idioma innecesarios de la instalación de DokuWiki, reduciendo el espacio en disco.

## Función

Borra recursivamente los directorios de idioma de:
- `inc/lang/` (idiomas del núcleo)
- `lib/plugins/*/lang/` (idiomas de cada plugin)
- `lib/tpl/*/lang/` (idiomas de cada plantilla)

El idioma inglés (`en`) **nunca se elimina**, independientemente de las opciones indicadas.

## Opciones

| Opción | Descripción |
|--------|-------------|
| `-k` / `--keep <códigos>` | Lista de idiomas a conservar además del inglés, separados por comas (ej. `es,fr,de`) |
| `-e` / `--english-only` | Elimina todos los idiomas excepto inglés |

## Uso típico

```bash
# Conservar solo español e inglés
php bin/striplangs.php -k es

# Conservar inglés, español y francés
php bin/striplangs.php -k es,fr

# Dejar solo inglés
php bin/striplangs.php -e
```

## Advertencia

Esta operación es **irreversible**. Los directorios eliminados no se pueden recuperar sin reinstalar las extensiones.

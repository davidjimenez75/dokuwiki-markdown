# gittool.php

Herramienta CLI para gestionar repositorios Git de DokuWiki, sus plugins y plantillas.

## Comandos

| Comando | Descripción |
|---------|-------------|
| `clone <extension> [...]` | Instala una extensión vía `git clone` buscando el repo en DokuWiki.org. Prefijo `template:` para plantillas |
| `install <extension> [...]` | Igual que `clone`, pero si no encuentra repo Git, descarga e instala el paquete zip como fallback |
| `repos` | Lista todos los repositorios Git encontrados en la instalación (raíz, plugins, plantillas) |
| `<comando git> [args]` | Cualquier otro comando se ejecuta como `git <comando>` en todos los repositorios encontrados |

## Fuentes de repositorios soportadas

- **GitHub** — `github.com/usuario/repo`
- **Gitorious** — `gitorious.org/usuario/repo` (obsoleto)
- **Bitbucket** — `bitbucket.org/usuario/repo`

## Uso típico

```bash
# Instalar un plugin por git
php bin/gittool.php clone gallery

# Instalar una plantilla
php bin/gittool.php clone template:bootstrap3

# Ver estado de todos los repos
php bin/gittool.php status

# Actualizar todos los repos
php bin/gittool.php pull
```

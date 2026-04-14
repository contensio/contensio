<p align="center">
  <strong>Contensio</strong><br>
  <em>The open content platform for Laravel.</em>
</p>

<p align="center">
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-AGPL--3.0--or--later-blue" alt="License"></a>
  <a href="https://packagist.org/packages/contensio/contensio"><img src="https://img.shields.io/packagist/v/contensio/contensio" alt="Latest Stable Version"></a>
</p>

---

**Contensio is an open-source content management system built as a Laravel 13 package.** It takes the familiar WordPress mental model — install, activate themes, manage plugins, publish from an admin panel — and delivers it on top of modern Laravel.

Contensio is designed for **any content-driven application**, not just blogs or public websites. Use it to build multi-language publishing sites, private admin tools, content-heavy product catalogs, community platforms, documentation sites, or anything else where structured content meets end users.

> 🚧 **Active development.** Contensio is not yet on Packagist. Watch the repo to hear when v1.0 drops.

## Key features

- 🧱 **Block-based editor** — modular content blocks, reorderable, translatable
- 🎨 **Themes** — admin-installable, activate from UI, customize colors/fonts/layout per theme
- 🔌 **Plugins** — install/enable/disable through the admin panel
- 🌍 **Multi-language** out of the box — every content type, term, and menu item is translatable
- 🗂️ **Custom content types** — define any content shape from the admin (not just posts & pages)
- 📸 **Media library** with automatic thumbnails and translations
- 🧭 **Drag-and-drop menu builder** with theme-declared locations
- 🔐 **Self-hosted** — AGPL-3.0, your data stays on your server

## Install

When Contensio reaches v1.0, install it one of two ways:

```bash
# Fresh install — Laravel + Contensio in one command
composer create-project contensio/project my-site

# Or add Contensio to an existing Laravel app
composer require contensio/contensio
php artisan contensio:install
```

## Requirements

- PHP 8.3+
- Laravel 13.x
- MySQL 8 / PostgreSQL 14 / SQLite 3

## Repositories

- **[contensio/contensio](https://github.com/contensio/contensio)** — this repo, the CMS package
- **[contensio/project](https://github.com/contensio/project)** — Laravel scaffold pre-configured with Contensio

## Contributing

Contributions are welcome. Open an issue to discuss before starting significant work.

## License

Contensio is open-source software released under the [GNU AGPL-3.0](LICENSE).

Copyright © 2026 Iosif Gabriel Chimilevschi. Contensio is operated by [Host Server SRL](https://hostserver.ro).

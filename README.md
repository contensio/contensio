<p align="center">
  <strong>Contensio</strong><br>
  <em>The open content platform for Laravel.</em>
</p>

<p align="center">
  <a href="https://packagist.org/packages/contensio/contensio"><img src="https://img.shields.io/packagist/v/contensio/contensio?include_prereleases&label=packagist" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/contensio/contensio"><img src="https://img.shields.io/packagist/dt/contensio/contensio?label=downloads" alt="Total Downloads"></a>
  <a href="https://github.com/contensio/contensio/releases"><img src="https://img.shields.io/github/v/release/contensio/contensio?include_prereleases&label=release" alt="Latest Release"></a>
  <a href="LICENSE"><img src="https://img.shields.io/badge/license-AGPL--3.0--or--later-blue" alt="License"></a>
</p>

---

**Contensio is an open-source content management system built as a Laravel 13 package.** It takes the familiar WordPress mental model — install, activate themes, manage plugins, publish from an admin panel — and delivers it on top of modern Laravel.

Contensio is designed for **any content-driven application**, not just blogs or public websites. Use it to build multi-language publishing sites, private admin tools, content-heavy product catalogs, community platforms, documentation sites, or anything else where structured content meets end users.

> 🚧 **Active development — currently in alpha.** APIs may still change. Feedback and bug reports are welcome. Expect a `0.1.0` stable release after a few rounds of beta testing.

## Key features

- 🧱 **Block-based editor** — modular content blocks, reorderable, translatable
- 🎨 **Themes** — admin-installable, activate from UI, customize colors/fonts/layout per theme
- 🔌 **Plugins** — install/enable/disable through the admin panel, declare their own permissions and roles
- 🌍 **Multi-language** out of the box — every content type, term, menu, and theme option is translatable
- 🗂️ **Custom content types** — define any content shape from the admin (not just posts & pages)
- 📸 **Media library** with automatic thumbnails and translations
- 🧭 **Drag-and-drop menu builder** with theme-declared locations, per-language labels
- 👥 **Users, Roles & Permissions** — 4 predefined roles, wildcard + per-content-type scoping, plugin-extensible
- 🔐 **Full auth flow** — login, logout, password reset, email verification, password confirmation
- 💾 **Content autosave** — debounced form saves every 2.5s + restore prompt on reload
- 🔎 **SEO built-in** — sitemap.xml, robots.txt, Open Graph + Twitter Cards, global noindex toggle
- 🏠 **Self-hosted** — AGPL-3.0, your data stays on your server

## Install

Contensio is a Laravel package. Add it to any Laravel 13 application:

```bash
composer require contensio/contensio:^0.1@alpha
php artisan migrate
```

The `@alpha` flag tells Composer it's OK to install a pre-release version. Once `0.1.0` stable ships, the flag goes away:

```bash
composer require contensio/contensio
```

### Starter project

For a fresh Laravel 13 app pre-configured with Contensio, see [`contensio/project`](https://github.com/contensio/project) — clone it, run `composer install`, and you're set up.

## Requirements

- PHP 8.3+
- Laravel 13.x
- MySQL 8 / PostgreSQL 14 / SQLite 3

## Repositories

- **[contensio/contensio](https://github.com/contensio/contensio)** — this repo, the CMS package
- **[contensio/project](https://github.com/contensio/project)** — Laravel scaffold pre-configured with Contensio

## Contributing

Contributions are welcome. Open an issue to discuss before starting significant work.

Plugin and theme developers — refer to the manifest formats:

- **Themes** declare metadata in `theme.json` (plus optional `settings.sections` for customization)
- **Plugins** declare metadata in `plugin.json` (plus optional `settings.sections`, `permissions`, and `roles`)

## License

Contensio is open-source software released under the [GNU AGPL-3.0-or-later](LICENSE).

Copyright © 2026 Iosif Gabriel Chimilevschi. Contensio is operated by [Host Server SRL](https://hostserver.ro).

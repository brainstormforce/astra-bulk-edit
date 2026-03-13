# Astra Bulk Edit

WordPress plugin for bulk editing Astra theme meta options via Quick Edit and Bulk Edit screens.

## Tech Stack

- **PHP** (WordPress plugin, requires Astra theme active)
- **WordPress Coding Standards** (WPCS) via phpcs
- **Grunt** for build tooling (i18n, release packaging, version bumping)
- **Composer** for dev dependencies (phpcs, phpcompat)

## Commands

```bash
# Lint PHP (WPCS + PHPCompatibility)
composer lint

# Auto-fix PHP coding standards
composer format

# Generate README.md from readme.txt
grunt readme

# Build release zip
grunt release

# Bump version (updates package.json + plugin header + constants)
grunt version-bump --ver=1.2.11

# Generate i18n pot file
grunt i18n
```

## Architecture

- `astra-bulk-edit.php` - Main plugin file, defines constants, loads admin class
- `classes/` - PHP classes (bulk edit meta box handler)
- `assets/` - Static assets (CSS/JS)
- `languages/` - Translation files (.pot)
- `.wordpress-org/` - WP.org assets (banners, icons, screenshots)

## Key Constants

- `ASTRA_BLK_VER` - Plugin version
- `ASTRA_BLK_FILE` - Main plugin file path
- `ASTRA_BLK_BASE` - Plugin basename
- `ASTRA_BLK_DIR` - Plugin directory path
- `ASTRA_BLK_URI` - Plugin URL

## Conventions

- Follow WPCS (WordPress-Core, WordPress-Docs, WordPress-Extra)
- PHP compatibility: 5.3+
- Text domain: `astra-bulk-edit`
- Plugin only loads when Astra theme is active (`get_template() === 'astra'`)
- All plugin code runs in admin context only (`is_admin()`)

## Gotchas

- Plugin silently exits if Astra theme is not the active template
- Version must be updated in both `package.json` and `astra-bulk-edit.php` (use `grunt version-bump`)
- `readme.txt` is the source of truth; `README.md` is generated via `grunt readme`

## Current Focus

<!-- Update this section with current work priorities -->

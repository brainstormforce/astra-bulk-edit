# Astra Bulk Edit - Internal Documentation

## Quick Start

```bash
# Clone and install
git clone <repo-url>
cd astra-bulk-edit
npm install
composer install

# Lint PHP
composer lint

# Build release zip
grunt release

# Generate README.md from readme.txt
grunt readme
```

## What Is This Plugin?

Astra Bulk Edit lets WordPress admins modify Astra theme meta settings (sidebar, content layout, header/footer visibility, etc.) across multiple pages/posts at once using WordPress's native Bulk Edit and Quick Edit interfaces.

- **Version:** 1.2.11
- **Author:** Brainstorm Force
- **License:** GPLv2+
- **Requires:** WordPress 4.4+, PHP 5.2+, Astra theme active
- **Distribution:** WordPress.org

## Key Facts

- Plugin only loads when the Astra theme is the active template
- All code runs in the admin context (`is_admin()`)
- Single PHP class handles everything: `Astra_Blk_Meta_Boxes_Bulk_Edit`
- Supports 22 meta options covering layout, headers, footers, breadcrumbs
- Integrates with Astra Pro addons (Transparent Header, Sticky Header, Advanced Headers)
- Includes layout migration for Astra 4.2.0+ revamped options

## Documentation Index

| File | Description |
|------|-------------|
| [product-vision.md](product-vision.md) | Goals, personas, competitive landscape |
| [architecture.md](architecture.md) | High-level architecture and data flow |
| [codebase-map.md](codebase-map.md) | Folder-by-folder file guide |
| [apis.md](apis.md) | AJAX endpoints, hooks, and filters |
| [coding-standards.md](coding-standards.md) | PHP/JS conventions and tooling |
| [ui-and-copy.md](ui-and-copy.md) | User journeys and microcopy |
| [onboarding.md](onboarding.md) | Developer onboarding paths |
| [ai-agent-guide.md](ai-agent-guide.md) | Guidance for AI coding agents |
| [troubleshooting.md](troubleshooting.md) | Common problems and fixes |
| [faq.md](faq.md) | Frequently asked questions |
| [glossary.md](glossary.md) | Technical terms and definitions |
| [maintenance.md](maintenance.md) | How to update these docs |

# Developer Onboarding

## 1-Hour Path (Quick Orientation)

1. **Read `astra-bulk-edit.php`** (31 lines) - Understand the entry point, theme gate, and constants
2. **Read `classes/class-astra-blk-meta-boxes-bulk-edit.php` constructor** (lines 48-62) - See all hooks registered
3. **Skim `setup_bulk_options()`** (lines 93-192) - Understand the 22 meta keys
4. **Open a WordPress admin post list** with the plugin active - See the UI in action
5. **Try a bulk edit** - Select 2 posts, bulk edit, change a setting, observe the result

After 1 hour you should understand: what the plugin does, where the code lives, and how the UI works.

## 1-Day Path (Working Knowledge)

Everything from the 1-hour path, plus:

1. **Read `save_meta_box()`** (lines 207-252) - Quick edit save flow
2. **Read `save_post_bulk_edit()`** (lines 257-303) - AJAX bulk save flow
3. **Read `display_quick_edit_custom()`** (lines 380-736) - Full form rendering with conditionals
4. **Read `assets/js/astra-admin.js`** (175 lines) - Pre-population and AJAX logic
5. **Read `manage_custom_admin_columns()`** (lines 329-370) - Hidden data column + layout migration
6. **Run `composer lint`** - Understand the PHPCS setup
7. **Read [apis.md](apis.md)** - Reference all hooks, filters, and meta keys
8. **Read [architecture.md](architecture.md)** - Understand the data flow diagrams

After 1 day you should be able to: fix bugs, add new meta options, and understand the security model.

## 1-Week Path (Full Mastery)

Everything from the 1-day path, plus:

1. **Study the layout migration** in `migrate_layouts()` (lines 768-808) and `manage_custom_admin_columns()` (lines 354-358) - Understand Astra 4.2.0 compat
2. **Read Astra Pro integration points** - Understand how the plugin detects `Astra_Builder_Helper`, `Astra_Ext_Extension`, `Astra_Target_Rules_Fields`
3. **Study the sticky header toggle JS** (lines 107-173 in astra-admin.js) - Complex conditional visibility
4. **Review `.github/workflows/`** - Understand CI/CD pipeline
5. **Run `grunt release`** - Build a release zip, inspect contents
6. **Read the changelog in `readme.txt`** - Understand version history and past security fixes
7. **Add a new meta option end-to-end** as an exercise:
   - Add key to `setup_bulk_options()`
   - Add dropdown in `display_quick_edit_custom()`
   - Test save via both quick edit and bulk edit
   - Verify the option appears with the correct values

After 1 week you should be able to: make architectural decisions, handle Astra version compatibility, and maintain the plugin independently.

## Environment Setup

### Prerequisites
- Local WordPress install with Astra theme active
- Node.js (for Grunt)
- Composer (for PHPCS)

### Steps
```bash
cd wp-content/plugins/astra-bulk-edit
npm install          # Grunt + build plugins
composer install     # PHPCS + WordPress coding standards
```

### Recommended Astra Pro Addons (for full testing)
- Sticky Header
- Transparent Header
- Advanced Headers
- Header Sections (legacy, pre-builder)

Without Astra Pro, some bulk edit fields will be hidden (this is expected behavior).

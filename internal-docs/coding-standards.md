# Coding Standards

## PHP

### WordPress Coding Standards (WPCS)
The plugin follows WPCS enforced via PHPCS. Configuration in `phpcs.xml.dist`:

- **Rulesets:** WordPress-Core, WordPress-Docs, WordPress-Extra
- **PHP Compatibility:** 5.3+ (via PHPCompatibility)
- **Excluded rules:**
  - `WordPress.PHP.StrictComparisons.LooseComparison`
  - `WordPress.PHP.StrictInArray.MissingTrueStrict`
- **Excluded paths:** `node_modules/`, `vendor/`

### Commands
```bash
# Check for violations
composer lint

# Auto-fix violations
composer format
```

### Naming Conventions
- **Class names:** `Astra_Blk_Meta_Boxes_Bulk_Edit` (WordPress-style underscored)
- **Class files:** `class-astra-blk-meta-boxes-bulk-edit.php` (hyphenated, prefixed with `class-`)
- **Constants:** `ASTRA_BLK_VER`, `ASTRA_BLK_FILE` (uppercase, plugin-prefixed)
- **Meta keys:** `site-sidebar-layout`, `ast-main-header-display` (hyphenated, Astra-convention)
- **Text domain:** `astra-bulk-edit`
- **Nonce names:** `astra_settings_bulk_meta_box`, `astra-blk-nonce`

### Security Patterns
- **Nonce verification:** `wp_verify_nonce()` for quick edit, `check_ajax_referer()` for AJAX
- **Capability checks:** `current_user_can('edit_post', $post_id)` before saving
- **Input sanitization:** `sanitize_text_field()` for string meta, `absint()` for post IDs, `filter_input()` for URL/number types
- **Output escaping:** `esc_html()` for text, `esc_attr()` for attributes, `esc_html_e()` for translations

### Class Pattern
- Singleton via static `$instance` + `get_instance()`
- All hooks registered in `__construct()`
- `class_exists()` guard wrapping class definition

## JavaScript

### Style
- jQuery-dependent (uses `jQuery(document).ready()`)
- No build step - vanilla JS served directly
- Dependencies: `jquery`, `inline-edit-post` (WordPress core)

### Conventions
- `var` declarations (no `let`/`const` - legacy support)
- `==` comparisons used (matches PHP-side loose comparison style)
- AJAX via `jQuery.ajax()` with `async: false` for bulk edit (ensures save completes before WordPress default action)

### Script Localization
```php
wp_localize_script( 'astra-blk-admin', 'security', array(
    'nonce' => wp_create_nonce( 'astra-blk-nonce' ),
));
```
Access in JS: `security.nonce`

## CSS

- Minimal admin-only styles
- No preprocessor (plain CSS)
- Scoped to `.astra-bulk-settings` and `.column-astra-settings`
- Uses WordPress admin conventions (`inline-edit-col`, `wp-clearfix`)

## CI/CD

### GitHub Actions (`.github/workflows/`)

| Workflow | Trigger | Purpose |
|----------|---------|---------|
| `phpcs.yml` | PR/Push | Runs PHPCS linting |
| `push-asset-readme-update.yml` | Push to master | Syncs WP.org assets and readme |
| `push-to-deploy.yml` | Push to master | Deploys to WP.org SVN |

# Frequently Asked Questions

## General

### Why does the plugin only work with the Astra theme?
The plugin edits Astra-specific post meta keys (e.g., `site-sidebar-layout`, `ast-main-header-display`) that are only recognized by the Astra theme. Using it with another theme would save meta values that have no effect on the frontend.

### Why is there no settings page?
The plugin has no configuration of its own. It simply exposes Astra's existing per-post meta options in the bulk/quick edit interface. The meta options themselves are defined by the Astra theme.

### Does it work with custom post types?
Yes. It registers for all public post types (`get_post_types(['public' => true])`) except `attachment` and `fl-theme-layout`.

## Development

### How do I add a new bulk edit option?
1. Add the meta key to `setup_bulk_options()` with default and sanitize values
2. Add the dropdown HTML in `display_quick_edit_custom()`
3. Both save handlers (`save_meta_box` and `save_post_bulk_edit`) iterate all registered options automatically - no save code changes needed

### Why are there two save methods?
WordPress handles quick edit and bulk edit differently:
- **Quick Edit:** WordPress fires `save_post` with form data in `$_POST` -> `save_meta_box()` handles it
- **Bulk Edit:** WordPress does NOT fire `save_post` with custom fields for bulk operations, so the plugin uses a custom AJAX handler (`save_post_bulk_edit()`) triggered by JavaScript before WordPress's default bulk action

### Why is the AJAX call synchronous (`async: false`)?
The bulk edit JS intercepts the "Update" button click, saves Astra meta via AJAX, then triggers the default WordPress bulk edit. The AJAX must complete before the page reloads, hence `async: false`. This is a known jQuery deprecation but is necessary for the current architecture.

### Why is `admin_init` priority set to 999?
`setup_admin_init()` runs at priority 999 to ensure all post types are already registered by other plugins before the plugin queries `get_post_types()` and registers column hooks.

### How does white-labeling work?
The fieldset title uses `apply_filters('astra_page_title', __('Astra', 'astra-bulk-edit'))`. Astra Pro's white-label feature filters this to show the custom brand name.

### Why is the "Astra Settings" column hidden?
The column (`column-astra-settings`) is set to `display: none` in CSS. It only exists to output hidden `<div>` elements containing each post's current meta values, which the JavaScript reads to pre-populate the quick edit form.

## Security

### What security measures are in place?
- Nonce verification on both save paths
- Per-post `current_user_can('edit_post')` capability checks
- `sanitize_text_field()` on all string inputs
- `absint()` on post ID arrays
- `esc_html()` / `esc_attr()` on all output

### What was the XSS vulnerability fixed in 1.2.11?
A Stored XSS vulnerability in the bulk edit AJAX endpoint that could be exploited by Contributor+ users. Fixed by adding proper `sanitize_text_field()` for all meta fields and `esc_html()` for output in admin columns. Reported by Patchstack.

## Build & Release

### How is the plugin released to WP.org?
Push to `master` triggers the `push-to-deploy.yml` GitHub Action which deploys to WP.org SVN. The `push-asset-readme-update.yml` action syncs screenshots, banners, and readme separately.

### How do I bump the version?
```bash
grunt version-bump --ver=1.2.12
```
This updates `package.json`, the plugin header `Version:`, and the `ASTRA_BLK_VER` constant.

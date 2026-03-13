# Troubleshooting

## Plugin Not Loading

**Symptom:** Plugin appears active but no Astra settings in bulk/quick edit.

**Cause:** Astra theme is not the active template.

**Fix:** The plugin checks `get_template() === 'astra'` at load time (`astra-bulk-edit.php:15`). If using a child theme, `get_template()` returns the parent theme slug, so child themes of Astra work. But if Astra is not installed or not active, the plugin silently exits.

## Bulk Edit Fields Not Appearing

**Symptom:** Quick edit shows Astra settings but bulk edit does not (or vice versa).

**Cause:** Both `bulk_edit_custom_box` and `quick_edit_custom_box` use the same handler (`display_quick_edit_custom()`). If one works but not the other, check for JavaScript errors in the browser console.

**Check:** The plugin excludes `product`, `cartflows_flow`, and `cartflows_step` post types from JS enqueue (`enqueue_admin_scripts_and_styles():749`). Also excludes `attachment` and `fl-theme-layout` from column registration.

## Bulk Edit Not Saving

**Symptom:** User clicks "Update" in bulk edit but Astra settings don't change.

**Possible Causes:**
1. **Nonce mismatch** - Check that `security.nonce` is localized correctly in JS
2. **AJAX error** - Open browser devtools Network tab, look for `admin-ajax.php` request with `action=astra_save_post_bulk_edit`
3. **Capability check failing** - User must have `edit_post` capability for each selected post
4. **"No Change" selected** - Fields with `no-change` value are intentionally skipped during save

## Quick Edit Not Pre-Populating Values

**Symptom:** Quick edit opens with all fields set to "-- No Change --" instead of current values.

**Cause:** The hidden data divs (`.astra-bulk-edit-field-{post_id}`) are not being output or are empty.

**Check:**
1. Inspect the page source - look for `<div class="astra-bulk-edit-field-{id}">` elements
2. Verify `manage_custom_admin_columns()` is firing (column must be registered)
3. Check that `get_post_meta()` returns expected values for the post

## Layout Options Showing Wrong Version

**Symptom:** Seeing legacy layout options (Boxed, Content Boxed, etc.) instead of new revamped options (Normal, Narrow, Full Width), or vice versa.

**Cause:** The form conditionally renders based on `ASTRA_THEME_VERSION` compared to `4.2.0` (`display_quick_edit_custom():393`).

**Fix:** Ensure the correct version of Astra theme is active. The migration in `migrate_layouts()` handles display of existing data, but the form fields are version-locked.

## Sticky Header Sub-Options Not Toggling

**Symptom:** Changing "Sticky Header" dropdown doesn't show/hide the sub-options.

**Cause:** JavaScript toggle logic in `astra-admin.js:107-161`.

**Check:**
1. Browser console for JS errors
2. Ensure `inline-edit-post` script is loaded (it's a dependency)
3. The toggle uses jQuery `slideDown()`/`slideUp()` on CSS classes: `.sticky-header-above-stick-meta`, `.sticky-header-main-stick-meta`, `.sticky-header-below-stick-meta`

## PHPCS Failures

**Symptom:** `composer lint` reports violations.

**Common fixes:**
- Missing docblocks: Add `@since x.x.x` tags
- Escaping: Use `esc_html()`, `esc_attr()`, `esc_url()` for all output
- Sanitization: Use `sanitize_text_field()` for POST input
- Yoda conditions: Put literal on the left (`'value' === $var`)

## Grunt Release Missing Files

**Symptom:** Release zip is missing expected files or includes dev files.

**Check:** The `copy.main.src` exclusion list in `Gruntfile.js:60-87`. Files must NOT match any exclusion pattern to be included. Verify `.distignore` matches for WP.org deploys.

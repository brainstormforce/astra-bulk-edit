# AI Agent Guide

## How AI Agents Should Work With This Plugin

### Before Making Changes

1. **Read `CLAUDE.md`** at the project root for commands and conventions
2. **Read the main class** (`classes/class-astra-blk-meta-boxes-bulk-edit.php`) - it contains nearly all logic
3. **Check Astra version conditionals** - many code paths branch on `ASTRA_THEME_VERSION` or `Astra_Builder_Helper::is_header_footer_builder_active()`
4. **Understand the "no-change" sentinel** - this value means "skip this field" during save, not "set to empty"

### Key Code Locations

| Task | Where to Look |
|------|---------------|
| Add a new meta option | `setup_bulk_options()` + `display_quick_edit_custom()` |
| Fix a save bug | `save_meta_box()` (quick edit) or `save_post_bulk_edit()` (bulk AJAX) |
| Fix pre-population | `manage_custom_admin_columns()` + `astra-admin.js` |
| Change UI layout | `display_quick_edit_custom()` (PHP form) + `astra-admin.css` |
| Fix JS behavior | `assets/js/astra-admin.js` |
| Add Astra Pro integration | Check `is_callable()` / `Astra_Ext_Extension::is_active()` patterns |

### Common Patterns to Follow

**Adding a new meta option:**
```php
// 1. In setup_bulk_options(), add to the $meta_option array:
'my-new-option' => array(
    'default'  => 'no-change',
    'sanitize' => 'FILTER_DEFAULT',
),

// 2. In display_quick_edit_custom(), add a dropdown:
<label class="inline-edit" for="my-new-option">
    <span class="title"><?php esc_html_e( 'My Option', 'astra-bulk-edit' ); ?></span>
    <select name="my-new-option" id="my-new-option">
        <option value="no-change" selected="selected"><?php esc_html_e( '-- No Change --', 'astra-bulk-edit' ); ?></option>
        <option value="enabled"><?php esc_html_e( 'Enabled', 'astra-bulk-edit' ); ?></option>
        <option value="disabled"><?php esc_html_e( 'Disabled', 'astra-bulk-edit' ); ?></option>
    </select>
</label>
```
The save logic auto-handles new options (iterates `$meta_option` keys).

### Pitfalls to Avoid

1. **Don't forget both save paths** - Quick edit uses `save_meta_box()` via `save_post` hook; bulk edit uses `save_post_bulk_edit()` via AJAX. Both must handle any new field.
2. **Don't remove the "no-change" option** - It's essential for bulk edit to not overwrite settings the user didn't intend to change.
3. **Don't change nonce names** - They're referenced in both PHP and JS.
4. **Don't add REST endpoints** - This plugin deliberately uses AJAX only for admin operations.
5. **Don't forget `sanitize_text_field()`** - All meta values must be sanitized before saving.
6. **Don't use `esc_html_e()` with concatenated strings** - Use `esc_html()` separately for dynamic parts.
7. **Test with and without Astra Pro** - Many conditionals depend on Pro addon availability.

### Security Checklist

When modifying save/input handling:
- [ ] Nonce verified before any data processing
- [ ] `current_user_can('edit_post', $post_id)` checked per-post
- [ ] All input sanitized (`sanitize_text_field`, `absint`, `filter_input`)
- [ ] All output escaped (`esc_html`, `esc_attr`)
- [ ] AJAX handler uses `check_ajax_referer()`

### Build & Version

- Version is defined in TWO places: `package.json` and `astra-bulk-edit.php` (header + constant)
- Use `grunt version-bump --ver=x.x.x` to update both
- `readme.txt` is the source of truth; `README.md` is generated via `grunt readme`
- Release zip: `grunt release`

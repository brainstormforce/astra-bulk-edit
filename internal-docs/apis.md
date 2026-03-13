# APIs - Hooks, Filters, and AJAX

## AJAX Endpoints

### `astra_save_post_bulk_edit`
- **Type:** `wp_ajax_` (authenticated users only)
- **Handler:** `Astra_Blk_Meta_Boxes_Bulk_Edit::save_post_bulk_edit()`
- **File:** `classes/class-astra-blk-meta-boxes-bulk-edit.php:257`
- **Security:** `check_ajax_referer('astra-blk-nonce', 'astra_nonce')` + per-post `current_user_can('edit_post', $post_id)`
- **Input:** Serialized form data with post IDs and meta values
- **Response:** `wp_send_json_success()` or `wp_send_json_error()`

## WordPress Hooks Used

### Actions

| Hook | Method | Priority | File:Line |
|------|--------|----------|-----------|
| `admin_init` | `setup_admin_init()` | 999 | :50 |
| `bulk_edit_custom_box` | `display_quick_edit_custom()` | 10 | :53 |
| `quick_edit_custom_box` | `display_quick_edit_custom()` | 10 | :54 |
| `admin_enqueue_scripts` | `enqueue_admin_scripts_and_styles()` | 10 | :56 |
| `save_post` | `save_meta_box()` | 10 | :58 |
| `wp_ajax_astra_save_post_bulk_edit` | `save_post_bulk_edit()` | 10 | :60 |
| `manage_{$type}_posts_columns` | `add_custom_admin_column()` | 10 | :83 |
| `manage_{$type}_posts_custom_column` | `manage_custom_admin_columns()` | 10 | :85 |

### Dynamic Hooks (per post type)
The plugin dynamically registers column hooks for every public post type except `attachment` and `fl-theme-layout`.

## Filters

### Provided by This Plugin

#### `astra_meta_box_bulk_edit_options`
- **File:** `classes/class-astra-blk-meta-boxes-bulk-edit.php:100`
- **Purpose:** Allows other plugins to modify the list of meta options available in bulk edit
- **Default:** Array of 22 meta keys with defaults and sanitize rules
- **Usage:**
```php
add_filter( 'astra_meta_box_bulk_edit_options', function( $options ) {
    $options['my-custom-meta'] = array(
        'default'  => 'no-change',
        'sanitize' => 'FILTER_DEFAULT',
    );
    return $options;
});
```

### Used from Astra Theme

#### `astra_page_title`
- **File:** `classes/class-astra-blk-meta-boxes-bulk-edit.php:313,384`
- **Purpose:** Gets the theme display name (supports white-labeling)

## Custom Action Hooks

These fire inside the bulk edit form, allowing extensions to add fields:

| Hook | Location | File:Line |
|------|----------|-----------|
| `astra_meta_bulk_edit_left_bottom` | After layout dropdowns | :465 |
| `astra_meta_bulk_edit_center_bottom` | After header/sticky controls | :663 |

## Meta Keys

All meta values are stored as post meta via `update_post_meta()`. The "no-change" sentinel value means "skip this field" during save.

| Meta Key | Purpose | Values |
|----------|---------|--------|
| `site-sidebar-layout` | Sidebar position | `default`, `left-sidebar`, `right-sidebar`, `no-sidebar` |
| `site-content-layout` | Legacy content layout | `default`, `boxed-container`, `content-boxed-container`, `plain-container`, `page-builder` |
| `ast-site-content-layout` | Container layout (4.2.0+) | `default`, `normal-width-container`, `narrow-width-container`, `full-width-container` |
| `site-content-style` | Container style (4.2.0+) | `default`, `unboxed`, `boxed` |
| `site-sidebar-style` | Sidebar style (4.2.0+) | `default`, `unboxed`, `boxed` |
| `ast-main-header-display` | Primary header | `enabled`, `disabled` |
| `ast-above-header-display` | Above header (legacy) | `enabled`, `disabled` |
| `ast-below-header-display` | Below header (legacy) | `enabled`, `disabled` |
| `ast-hfb-above-header-display` | Above header (builder) | `enabled`, `disabled` |
| `ast-hfb-below-header-display` | Below header (builder) | `enabled`, `disabled` |
| `ast-hfb-mobile-header-display` | Mobile header (builder) | `enabled`, `disabled` |
| `site-post-title` | Page title visibility | `enabled`, `disabled` |
| `ast-featured-img` | Featured image | `enabled`, `disabled` |
| `footer-sml-layout` | Footer bar | `enabled`, `disabled` |
| `footer-adv-display` | Footer widgets | `enabled`, `disabled` |
| `ast-breadcrumbs-content` | Breadcrumbs | `enabled`, `disabled` |
| `theme-transparent-header-meta` | Transparent header | `default`, `enabled`, `disabled` |
| `stick-header-meta` | Sticky header | `default`, `enabled`, `disabled` |
| `header-above-stick-meta` | Stick above header | `enabled`, `disabled` |
| `header-main-stick-meta` | Stick primary header | `enabled`, `disabled` |
| `header-below-stick-meta` | Stick below header | `enabled`, `disabled` |
| `adv-header-id-meta` | Page header ID | Post ID of `astra_adv_header` CPT |

## Nonce Details

| Context | Nonce Action | Nonce Field | Verification |
|---------|-------------|-------------|--------------|
| Quick Edit | `basename(__FILE__)` | `astra_settings_bulk_meta_box` | `wp_verify_nonce()` in `save_meta_box()` |
| Bulk Edit AJAX | `astra-blk-nonce` | `astra_nonce` | `check_ajax_referer()` in `save_post_bulk_edit()` |

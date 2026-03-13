# Architecture

## Overview

Astra Bulk Edit is a single-class WordPress plugin that hooks into the admin post list screens to add Astra meta options to Bulk Edit and Quick Edit interfaces.

```
+-----------------------+
|  astra-bulk-edit.php  |  Entry point: constants, theme check, loads class
+-----------+-----------+
            |
            v (admin only)
+------------------------------------------+
| Astra_Blk_Meta_Boxes_Bulk_Edit           |
| (classes/class-astra-blk-meta-boxes-bulk-edit.php) |
|                                          |
| - Registers 22 meta options              |
| - Renders bulk/quick edit form fields    |
| - Handles save_post (quick edit)         |
| - Handles AJAX save (bulk edit)          |
| - Adds hidden data column to post list   |
| - Enqueues admin JS/CSS                  |
+------------------------------------------+
            |
            v
+-------------------+    +--------------------+
| astra-admin.js    |    | astra-admin.css    |
| (assets/js/)      |    | (assets/css/)      |
|                   |    |                    |
| - Pre-populates   |    | - Styles bulk edit |
|   quick edit vals  |    |   form fields      |
| - AJAX bulk save   |    +--------------------+
| - Sticky header    |
|   toggle logic     |
+-------------------+
```

## Boot Sequence

1. WordPress loads `astra-bulk-edit.php`
2. Theme check: `get_template() === 'astra'` - exits if false
3. Constants defined (`ASTRA_BLK_VER`, `ASTRA_BLK_FILE`, etc.)
4. `is_admin()` check - only loads class in admin
5. Class file loaded, singleton instantiated via `get_instance()`
6. Constructor registers all WordPress hooks

## Data Flow

### Quick Edit Save
```
User clicks "Quick Edit" -> WordPress inline editor opens
  -> display_quick_edit_custom() renders Astra fields
  -> User modifies fields, clicks "Update"
  -> WordPress fires save_post hook
  -> save_meta_box() validates nonce + capability
  -> Each meta field sanitized and saved via update_post_meta()
```

### Bulk Edit Save
```
User selects posts -> clicks "Bulk Edit"
  -> display_quick_edit_custom() renders Astra fields (same hook)
  -> User modifies fields, clicks "Update"
  -> astra-admin.js intercepts #bulk_edit click
  -> Serializes form + sends AJAX to wp_ajax_astra_save_post_bulk_edit
  -> save_post_bulk_edit() validates nonce + per-post capability
  -> Iterates posts, sanitizes and saves each meta field
  -> WordPress default bulk edit proceeds after AJAX completes
```

### Pre-population (Quick Edit)
```
Post list loads -> manage_custom_admin_columns() outputs hidden divs
  -> Each div contains current meta value for that post
  -> astra-admin.js overrides inlineEditPost.edit()
  -> On quick edit open, reads hidden divs and populates select fields
```

## Singleton Pattern

The class uses a static `$instance` property with `get_instance()` to ensure only one instance exists. The constructor registers all hooks, so the singleton prevents duplicate hook registration.

## Layout Migration

For Astra 4.2.0+, the plugin includes `migrate_layouts()` which maps old layout meta values to the new revamped layout system:

| Old Value | New Container | New Content Style | New Sidebar Style |
|-----------|--------------|-------------------|-------------------|
| `plain-container` | `normal-width-container` | `unboxed` | `unboxed` |
| `boxed-container` | `normal-width-container` | `boxed` | `boxed` |
| `content-boxed-container` | `normal-width-container` | `boxed` | `unboxed` |
| `page-builder` | `full-width-container` | `unboxed` | `unboxed` |
| `narrow-container` | `narrow-width-container` | `unboxed` | `unboxed` |

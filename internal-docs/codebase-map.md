# Codebase Map

## Directory Structure

```
astra-bulk-edit/
├── astra-bulk-edit.php                          # Main plugin file (entry point)
├── classes/
│   └── class-astra-blk-meta-boxes-bulk-edit.php # Core class (all plugin logic)
├── assets/
│   ├── css/
│   │   └── astra-admin.css                      # Admin styles for bulk edit UI
│   └── js/
│       └── astra-admin.js                       # Quick edit pre-population + AJAX bulk save
├── languages/
│   └── astra-bulk-edit.pot                      # Translation template
├── .github/
│   └── workflows/
│       ├── phpcs.yml                            # PHPCS linting CI
│       ├── push-asset-readme-update.yml         # WP.org asset/readme sync
│       └── push-to-deploy.yml                   # WP.org deployment
├── .wordpress-org/                              # WP.org assets (banners, icons, screenshots)
├── Gruntfile.js                                 # Build tasks (release, i18n, version bump)
├── package.json                                 # Node dependencies (Grunt plugins)
├── composer.json                                # PHP dev dependencies (WPCS, PHPCompat)
├── phpcs.xml.dist                               # PHPCS configuration
├── readme.txt                                   # WP.org readme (source of truth)
├── README.md                                    # Generated from readme.txt
├── .distignore                                  # Files excluded from WP.org distribution
├── .editorconfig                                # Editor configuration
├── .gitignore                                   # Git ignore rules
├── CLAUDE.md                                    # Claude Code project context
└── .claude/                                     # Claude Code configuration
    └── settings.json                            # Pre-approved tool permissions
```

## File Details

### `astra-bulk-edit.php` (31 lines)
- Plugin header (name, version, author, text domain)
- Theme gate: `get_template() !== 'astra'` returns early
- Defines 5 constants: `ASTRA_BLK_VER`, `ASTRA_BLK_FILE`, `ASTRA_BLK_BASE`, `ASTRA_BLK_DIR`, `ASTRA_BLK_URI`
- Loads class file only in admin context

### `classes/class-astra-blk-meta-boxes-bulk-edit.php` (816 lines)
This is the heart of the plugin. Single class `Astra_Blk_Meta_Boxes_Bulk_Edit`:

| Method | Lines | Purpose |
|--------|-------|---------|
| `get_instance()` | 38-43 | Singleton accessor |
| `__construct()` | 48-62 | Registers all WP hooks |
| `setup_admin_init()` | 67-88 | Registers column hooks for all public post types |
| `setup_bulk_options()` | 93-192 | Defines 22 meta option keys with defaults and sanitize rules |
| `get_meta_option()` | 197-199 | Static getter for meta options |
| `save_meta_box()` | 207-252 | Handles quick edit save via `save_post` hook |
| `save_post_bulk_edit()` | 257-303 | Handles bulk edit save via AJAX |
| `add_custom_admin_column()` | 311-318 | Adds hidden "Astra Settings" column to post list |
| `manage_custom_admin_columns()` | 329-370 | Outputs hidden divs with current meta values per post |
| `display_quick_edit_custom()` | 380-736 | Renders the bulk/quick edit form UI (dropdowns) |
| `enqueue_admin_scripts_and_styles()` | 741-759 | Enqueues CSS/JS, localizes nonce |
| `migrate_layouts()` | 768-808 | Maps legacy layout values to Astra 4.2.0+ system |

### `assets/js/astra-admin.js` (175 lines)
- Overrides `inlineEditPost.edit` to pre-populate quick edit fields
- Intercepts `#bulk_edit` click for AJAX save before WordPress default behavior
- Manages sticky header sub-option visibility toggle

### `assets/css/astra-admin.css` (24 lines)
- Styles for bulk edit form: field widths, column layout, hidden data column

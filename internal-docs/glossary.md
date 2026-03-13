# Glossary

| Term | Definition |
|------|-----------|
| **Astra Builder** | The Header Footer Builder introduced in Astra theme, replacing legacy header/footer sections. Detected via `Astra_Builder_Helper::is_header_footer_builder_active()`. |
| **Astra Pro** | Premium addon for Astra theme. Provides extensions like Sticky Header, Transparent Header, Advanced Headers. Detected via `Astra_Ext_Extension::is_active()`. |
| **Bulk Edit** | WordPress feature to modify multiple posts at once from the post list screen. Opens a panel above the post list with shared fields. |
| **Container Layout** | Astra 4.2.0+ setting controlling content width: Normal, Narrow, or Full Width. Meta key: `ast-site-content-layout`. |
| **Container Style** | Astra 4.2.0+ setting controlling content box appearance: Boxed or Unboxed. Meta key: `site-content-style`. |
| **Content Layout (Legacy)** | Pre-4.2.0 Astra setting combining container and style: Boxed, Content Boxed, Plain Container, Page Builder. Meta key: `site-content-layout`. |
| **`.distignore`** | File listing patterns to exclude from WP.org plugin distribution (similar to `.gitignore` but for SVN deploy). |
| **Header Footer Builder (HFB)** | See "Astra Builder". Meta keys prefixed with `ast-hfb-` are builder-specific. |
| **`inline-edit-post`** | WordPress core JavaScript that handles the inline editing (quick/bulk edit) interface. The plugin overrides `inlineEditPost.edit` to add pre-population. |
| **Layout Migration** | Process of converting legacy `site-content-layout` values to the revamped `ast-site-content-layout` + `site-content-style` + `site-sidebar-style` system. Handled by `migrate_layouts()`. |
| **Meta Option** | A per-post setting stored as WordPress post meta via `update_post_meta()`. The plugin manages 22 meta options. |
| **No Change** | Sentinel value (`no-change`) used in bulk/quick edit dropdowns meaning "don't modify this setting". Skipped during save. |
| **Quick Edit** | WordPress feature to edit a single post inline from the post list screen. Opens an inline row with editable fields. |
| **Revamped Layout** | The new layout option system in Astra 4.2.0+ that separates Container Layout, Container Style, and Sidebar Style into independent options. |
| **Sidebar Layout** | Astra setting for sidebar position: Left, Right, or No Sidebar. Meta key: `site-sidebar-layout`. |
| **Sidebar Style** | Astra 4.2.0+ setting for sidebar box appearance: Boxed or Unboxed. Meta key: `site-sidebar-style`. |
| **Singleton** | Design pattern ensuring only one class instance exists. Used by `Astra_Blk_Meta_Boxes_Bulk_Edit::get_instance()`. |
| **White Label** | Astra Pro feature allowing agencies to rebrand the theme. Affects this plugin's fieldset title via `astra_page_title` filter. |
| **WPCS** | WordPress Coding Standards - PHP_CodeSniffer rules for WordPress development. Enforced via `phpcs.xml.dist`. |

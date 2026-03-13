# UI and Copy

## User Journey

### Bulk Edit Flow
1. Admin navigates to Posts/Pages list screen
2. Selects multiple posts via checkboxes
3. Chooses "Edit" from Bulk Actions dropdown, clicks "Apply"
4. WordPress opens bulk edit panel
5. **Astra Settings fieldset appears** with dropdown controls
6. Admin selects desired values (or leaves as "-- No Change --")
7. Clicks "Update"
8. Plugin JS intercepts click, sends AJAX request to save Astra meta
9. After AJAX completes, WordPress default bulk edit proceeds

### Quick Edit Flow
1. Admin hovers over a post in the list
2. Clicks "Quick Edit"
3. WordPress opens inline edit row
4. **Astra Settings appear** with dropdowns pre-populated with current values
5. Admin modifies values
6. Clicks "Update"
7. WordPress fires `save_post`, plugin saves meta via `save_meta_box()`

## UI Layout

The bulk/quick edit form is organized in three float-left columns:

**Left Column:**
- Sidebar Layout (or Container Layout + Container Style + Sidebar Layout + Sidebar Style for Astra 4.2.0+)
- Extension point: `astra_meta_bulk_edit_left_bottom` action hook

**Center Column:**
- Primary Header (enabled/disabled)
- Above Header (conditional on theme config)
- Below Header (conditional on theme config)
- Mobile Header (conditional on builder active)
- Transparent Header (conditional on theme option)
- Page Header (Astra Pro Advanced Headers)
- Sticky Header + sub-options (Astra Pro Sticky Header)
- Extension point: `astra_meta_bulk_edit_center_bottom` action hook

**Right Column:**
- Breadcrumbs (conditional on position != none)
- Title Visibility
- Featured Image
- Footer Widgets (conditional on layout != disabled)
- Footer Bar (conditional on layout != disabled)

## Microcopy

### Dropdown Default
All dropdowns default to `"-- No Change --"` (value: `no-change`), meaning the existing meta value is preserved. This is critical for bulk edit where you may only want to change one setting.

### Option Labels
- `"Customizer Setting"` (value: `default`) - Inherits from Astra Customizer
- `"Enabled"` / `"Disabled"` - Toggle options
- Layout-specific labels match Astra Customizer naming

### Fieldset Title
Displays as `"{Theme Name} Settings"` where theme name comes from `astra_page_title` filter. Supports Astra white-labeling.

### Hidden Column
The "Astra Settings" column in the post list is hidden via CSS (`display: none`). It exists solely to hold data attributes for quick edit pre-population.

## Conditional UI

Several fields only appear based on theme/addon configuration:
- Above/Below Header: Only if header row is populated (builder) or addon is active with non-disabled layout
- Mobile Header: Only if header footer builder is active and rows are populated
- Transparent Header: Only if not globally disabled
- Sticky Header section: Only if Astra Pro Sticky Header addon is active
- Advanced Headers: Only if addon active and not a Beaver Builder Themer layout
- Breadcrumbs: Only if breadcrumb position is not "none"
- Footer Widgets/Bar: Only if not globally disabled

## Sticky Header Toggle Behavior

When "Sticky Header" is set to "Enabled", the sub-options (Stick Above/Primary/Below Header) slide down. When set to anything else, they slide up. Additionally, if all three header rows (above, primary, below) are individually disabled, the entire sticky header option hides.

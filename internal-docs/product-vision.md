# Product Vision

## Problem

WordPress admins using the Astra theme often need to change meta settings (sidebar layout, header visibility, content width, etc.) on many pages at once. Without this plugin, they must open each page individually and modify settings one by one.

## Solution

Astra Bulk Edit injects Astra's meta options into WordPress's native Bulk Edit and Quick Edit interfaces, allowing admins to modify settings across multiple posts/pages in a single operation.

## Target Personas

### Site Administrator
- Manages a large WordPress site with many pages
- Needs to quickly apply consistent layout settings across sections
- Example: "Make all product pages full-width with no sidebar"

### Theme Customizer
- Configures Astra theme settings per-page
- Frequently adjusts header/footer visibility for landing pages
- Example: "Disable the header on all landing pages at once"

## Scope

The plugin provides bulk/quick edit controls for:

**Layout Settings:**
- Sidebar layout (left, right, none)
- Container layout (normal, narrow, full-width) - Astra 4.2.0+
- Container style (boxed, unboxed) - Astra 4.2.0+
- Sidebar style (boxed, unboxed) - Astra 4.2.0+
- Legacy content layout (boxed, content-boxed, plain, page-builder) - pre-4.2.0

**Visibility Toggles:**
- Primary/Above/Below/Mobile header display
- Page title visibility
- Featured image visibility
- Footer widgets display
- Footer bar display
- Breadcrumbs

**Astra Pro Integration:**
- Transparent header
- Sticky header (with per-row controls)
- Page header (Advanced Headers addon)

## What This Plugin Does NOT Do

- Does not add new meta options (uses Astra's existing ones)
- Does not modify the frontend rendering
- Does not work with non-Astra themes
- Does not provide REST API endpoints
- Does not have settings pages of its own

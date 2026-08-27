# IW Mega Menu

A powerful, accessible, and Elementor-optimized Mega Menu plugin with multi-level navigation and widget support. Developed for Austin Community College - Instructional Web Projects.

## Version
**2.0.0**

## Features
- **Fully Responsive Navigation**: Optimized for Desktop, Tablet, and Mobile devices with dynamic viewport height support (`100dvh`).
- **WCAG 2.2 Level AA Accessibility**:
  - Semantic DOM tree colocating dropdown menus within parent list items for natural keyboard Tab order.
  - Complete keyboard navigation support (`Tab`, `Shift+Tab`, `Escape` to close & return focus).
  - Accessible modal dialog drawer with focus trap on mobile.
  - Screen reader announcements (`aria-expanded`, `aria-controls`, `aria-haspopup`, `aria-hidden` on icons).
  - High-contrast `:focus-visible` focus indicators without suppressing default browser accessibility.
- **Multi-Level Navigation**: Supports up to 3 hierarchical levels (Top-Level, Second-Level categories/accordions, Third-Level sub-links).
- **Elementor Integration**:
  - Built natively for modern Elementor 3.x using `elementor/widgets/register`.
  - Conditional asset enqueuing via `get_script_depends()` and `get_style_depends()` for Core Web Vitals optimization.
  - Cached Elementor template queries for fast editor and admin load times.
- **Customizable Brand Styling**:
  - Full control over typography, colors, borders, paddings, and item gaps.
  - Built-in ACC brand color presets for icons and badges (`Purple`, `Teal`, `Blue`, `Lime`, `Yellow`, `Orange`, `Red`, etc.).
  - Sidebar template embedding for rich content callouts, forms, and contact cards.
  - Dedicated WordPress Builder with nested menu editing and a side-by-side live preview.
  - Reusable saved menus stored as `iw_mega_menu` records and selectable from Elementor.
  - REST API and JSON import/export for menu persistence, backups, and WordPress menu imports.

## Menu Structure

Menus nest three levels:

| Level | Role | Supports |
|-------|------|----------|
| **Top** | Sits on the nav bar. Links directly if given a URL, or opens a dropdown if it has Sections. | Label, URL, description, icon, badge, sidebar template |
| **Section** | Fills a Top item's dropdown. Either a single link, or an expandable accordion of Links. | Label, URL, description, icon, badge, accordion toggle |
| **Link** | Nests inside a Section. | Label, URL, badge |

### Important: nested Links require an Accordion
A Section renders its nested **Links** on desktop **only when "Show nested links" is set to Yes** (the `is_accordion` flag). A Section left as a single link displays its own label/icon/description, and any Links nested under it are not shown on desktop — though they do still appear in the mobile drawer.

The Builder handles this for you: adding a Link to a Section turns "Show nested links" on automatically, and any pre-existing Section with hidden Links is flagged with an **"N hidden"** warning in the tree and an explanation in its editor.

### Layout limits
- **Sections (non-accordion):** up to 4 per column, 2 columns
- **Links inside an accordion:** up to 2 per column, 3 columns

Items beyond these counts are not rendered.

## Installation
1. Download or clone this repository to your local machine.
2. Upload the `iw-megamenu` folder to your WordPress site's `wp-content/plugins/` directory.
3. Activate the plugin through the WordPress admin dashboard (`Plugins > Installed Plugins`).
4. Ensure Elementor (v3.5.0 or later recommended) is installed and activated.

## Usage — Builder (recommended)
1. Go to **IW Mega Menu** in the WordPress admin sidebar.
2. Click **+ New**, name the menu, and click **Create**.
3. Click **+ Add Top-Level Item** to add a nav bar item. Click any row to open its editor.
4. Expand a row with the chevron, then use **+ Add Section** / **+ Add Link** to nest items beneath it.
5. In an item's editor, set the Label, Link URL, Description, Badge, and Icon. Click **Browse** beside the Icon field to pick from the built-in icon set, or type any Font Awesome class directly.
6. Watch the right-hand live preview — it opens to whatever you're editing and matches how the item will render on the site.
7. Click **Save Menu**, then drop the **IW Mega Menu** widget onto an Elementor page/header and select your saved menu.

## Usage — Elementor widget
1. In the WordPress admin, edit a page or Header Template with Elementor.
2. Search for **IW Mega Menu** in the Elementor widget panel and drag it into your layout.
3. In the **Menu Structure** section, add your menu items:
   - **Top-Level**: Primary navigation triggers.
   - **Second-Level**: Categories or Accordion headers. Can include an optional description and icon.
   - **Third-Level**: Sub-links within categories or accordions.
4. (Optional) Assign an Elementor template to display as a sidebar card for desktop mega menu dropdowns and mobile drawers.
5. Customize colors, typography, borders, and spacing in the **Style** tab.
6. Publish or save your template.

## Changelog

### v2.0.0 (Current Release)
- Added the dedicated IW Mega Menu Builder admin dashboard.
- Added reusable saved menus with Elementor widget selection and legacy repeater fallback.
- Added REST CRUD endpoints, Elementor template lookup, WordPress menu import, and JSON import/export.

#### Color fidelity fixes
- **Per-item icon colors now survive the widget's global "Icon Color" control.** That control emits a rule targeting `.iw-mega-menu__icon i` directly, while the `icon-acc-*` classes set color on the parent `<span>` — and a direct rule on a child always beats a color inherited from its parent, `!important` or not. The palette classes now also target the nested `i`/`svg`, so each item's chosen accent color wins.
- **The Builder preview no longer invents its own colors.** It was hand-rolling a second copy of the menu styles that had drifted from the real ones (badges rendered gold instead of brand purple, section labels near-black instead of `#5a3fa0`, third-level links WordPress blue, accordion toggles with no background). The admin stylesheet now scales sizing/spacing only and defers every color to `assets/css/iw-megamenu.css`, which is already enqueued on that page.
- Tree-row badge indicators recolored to match the real purple badge.

#### Builder usability & rendering fixes
- **Top-level items now render their icon and description.** Previously both fields were editable but silently discarded for Top-level items on the frontend, in the Elementor editor preview, and in the Builder preview. Top-level icons and descriptions now render in all three, forced to white/translucent-white so they stay legible on the nav bar.
- **Nested Links no longer disappear silently.** Links added to a Section only render on desktop when the Section is set to show nested links. Adding a Link now enables that automatically, and Sections with hidden Links are flagged with an "N hidden" warning plus an explanation in the editor.
- **Added a searchable icon picker** with ~60 curated Font Awesome icons, a live color-accurate preview, and a Remove button — typing raw `fas fa-star` classes is now optional rather than required.
- Icon and Description fields are hidden for third-level Links, which the frontend renders as label + badge only.
- Rows show at-a-glance indicators for icon, description, and URL, plus a warning on items missing a label.
- Click any row to open its editor; expand/collapse moved to a leading chevron and is hidden on items that cannot have children (this also removed a stray empty grey box under expanded third-level Links).
- The live preview now opens to whatever is being edited and keeps its open dropdown/accordion while you type.
- Replaced cryptic glyph buttons with labelled/`aria-label`ed Dashicons, added drag-to-reorder, duplicate, expand/collapse all, per-level empty-state guidance, and an unsaved-changes warning.
- Replaced browser `prompt()` dialogs for creating menus and importing WordPress menus with inline forms.

### v1.1.0
#### Major Accessibility & Architecture Modernization
- **Full WAI-ARIA APG & WCAG 2.2 AA Compliance**:
  - Colocated mega menu dropdowns inside parent `<li>` items, correcting DOM tab order so keyboard users can seamlessly navigate into submenus.
  - Added `Escape` key handler to dismiss open dropdowns and return focus to the trigger button.
  - Added accessible focus trapping inside the mobile navigation drawer.
  - Removed all `outline: none !important;` overrides and introduced high-contrast `:focus-visible` styling.
  - Non-linked category headers now render as semantic headings (`role="heading" aria-level="3"`) instead of empty `<a href="">` tags.
  - Carets and decorative icons are marked with `aria-hidden="true"`.
- **Modernized Elementor Asset Pipeline**:
  - Migrated widget registration to the modern `elementor/widgets/register` hook and `register_controls()` API.
  - Extracted all frontend JavaScript from inline `<script>` tags into `assets/js/iw-megamenu.js`, registered conditionally via `get_script_depends()`.
  - Converted main plugin bootstrap into an encapsulated `IW_Mega_Menu` singleton class with `load_plugin_textdomain()` support.
  - Cached `get_elementor_templates()` query to eliminate redundant full-table queries in the editor.
- **Mobile & Responsive Enhancements**:
  - Removed ~110 lines of duplicate mobile CSS previously injected into `<head>` via `wp_head`.
  - Added dynamic viewport height support (`100dvh`) to prevent iOS Safari bottom toolbar clipping.
  - Added touch device support for tablet and touchscreen laptop navigation.
  - Added mobile sidebar support so featured templates and callouts are accessible on mobile devices.
- **100% Backward Compatibility**:
  - Fully preserved all repeater keys, widget settings, and CSS class names for zero downtime across existing installations (such as `https://instruction.austincc.edu/daystrom`).

### v1.0.1
- Added optional descriptions for second-level menu items.
- Added open/closed visual states for accordion buttons.
- Layout and vertical spacing refinements for compact dropdowns.
- Bug fixes for icon color changes in Elementor editor.

### v1.0.0
- Initial release with 3-level menu hierarchy, sidebar template embedding, and mobile drawer.

## Support
For questions, issues, or feature requests, please open an issue in this repository or contact the Instructional Web Projects team at [instruction.austincc.edu](https://instruction.austincc.edu).

---

**Developed for Austin Community College - Instructional Web Projects**

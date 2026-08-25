# IW Mega Menu

A powerful, accessible, and Elementor-optimized Mega Menu plugin with multi-level navigation and widget support. Developed for Austin Community College - Instructional Web Projects.

## Version
**1.1.0**

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

## Menu Structure Limits
- **Second level:** Up to 4 links per column, 2 columns (excluding accordions)
- **Third level:** Up to 2 links per column, 3 columns

## Installation
1. Download or clone this repository to your local machine.
2. Upload the `iw-megamenu` folder to your WordPress site's `wp-content/plugins/` directory.
3. Activate the plugin through the WordPress admin dashboard (`Plugins > Installed Plugins`).
4. Ensure Elementor (v3.5.0 or later recommended) is installed and activated.

## Usage
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

### v1.1.0 (Current Release)
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

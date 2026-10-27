# IW Mega Menu

An accessible, multi-level mega menu for any WordPress site, styled to the ACC design system. Build menus in the dashboard, then place them with a block, a shortcode, a template tag, or the optional Elementor widget. Developed for Austin Community College - Instructional Web Projects.

## Version
**3.0.0**

## Requirements
- WordPress 5.8+ (the block needs the block editor; the shortcode and template tag work anywhere)
- PHP 7.4 – 8.4
- Elementor is **optional** — only needed for the Elementor widget and Elementor-template sidebars

## Features
- **Fully Responsive Navigation**: Optimized for Desktop, Tablet, and Mobile devices with dynamic viewport height support (`100dvh`).
- **WCAG 2.2 Level AA Accessibility**:
  - Semantic DOM tree colocating dropdown menus within parent list items for natural keyboard Tab order.
  - Complete keyboard navigation support (`Tab`, `Shift+Tab`, `Escape` to close & return focus).
  - Accessible modal dialog drawer with focus trap on mobile.
  - Screen reader announcements (`aria-expanded`, `aria-controls`, `aria-haspopup`, `aria-hidden` on icons).
  - High-contrast `:focus-visible` focus indicators without suppressing default browser accessibility.
- **Multi-Level Navigation**: Supports up to 3 hierarchical levels (Top-Level, Second-Level categories/accordions, Third-Level sub-links).
- **Works on any WordPress site**: place a menu with the **IW Mega Menu block**, the `[iw_mega_menu]` shortcode, or the `iw_mega_menu()` template tag. Assets load only on pages that render a menu.
- **ACC brand styling**: built on the ACC design system's tokens (palette, Montserrat type scale, spacing, focus rings, 300ms transitions). On the ACC theme it reads the theme's `theme.json` custom properties directly; elsewhere it falls back to the same values. Colors follow the ACC background-context rule — orange accents on light backgrounds, yellow on dark.
- **Optional Elementor widget**: loaded only when Elementor is active, with typography, color, border and spacing controls and the legacy repeater kept for older pages.
- **Sidebars**: show a pattern (synced or not) beside any dropdown, or a saved Elementor template when Elementor is active.
- **Builder**: dedicated admin dashboard with nested menu editing, a side-by-side live preview, ACC brand icon color presets, WordPress menu import, and JSON import/export.
- Reusable saved menus stored as `iw_mega_menu` records, with a REST API for persistence.

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
4. (Optional) Install Elementor 3.5+ if you want to use the Elementor widget.

## Upgrading to 3.0.0

3.0.0 is a drop-in update — saved menus, Elementor widgets (including legacy repeaters) and sidebar selections all keep working — but it changes how menus look and where they can go. Before updating a production site, check:

- **Brand restyle.** Menus now follow the ACC design system (see the v3.0.0 changelog). Elementor widget style controls still override the defaults.
- **Retired icon colors.** Items set to Teal, Blue (Light), Blue (Dark) or Red now render purple, purple, dark purple and orange. Pick a new brand color in the builder if the substitute doesn't suit.
- **Mobile breakpoint.** The mobile drawer now takes over below 700px instead of 768px; between 700 and 767px the desktop bar shows.
- **Font.** Menus use Montserrat. The ACC theme loads it; on other themes the menu falls back to Helvetica Neue / Arial unless the theme loads Montserrat.
- **Elementor no longer required.** The "Elementor is required" notice is gone; on sites without Elementor, use the block, shortcode or template tag.

## Updating an existing install

> **The folder name is the plugin's identity.** WordPress keys every plugin on `folder-name/main-file.php`. This plugin **must** live in a folder named exactly `iw-megamenu`, so its basename is `iw-megamenu/iw-megamenu.php`.

A GitHub "Download ZIP" produces a folder named `iw-megamenu-main` (or `-<branch>` / `-<commit>`). Uploading that as-is installs a **second, separate plugin** rather than updating the existing one — it appears as a duplicate row in the Plugins list with a different version, and the original stays active.

To package a release correctly:

```bash
# From the parent directory of the repo, not inside it
zip -r iw-megamenu.zip iw-megamenu \
  -x "iw-megamenu/.git/*" "iw-megamenu/.github/*" "iw-megamenu/.claude/*" \
     "iw-megamenu/.gitignore" "*/.DS_Store"
```

Verify the zip's top-level folder before uploading — it must be `iw-megamenu/`:

```bash
unzip -l iw-megamenu.zip | head -5
```

Then either upload that zip (WordPress 5.5+ detects the matching folder and offers *Replace current with uploaded*), or replace the folder's contents in place over SFTP. Both preserve the plugin's activation state and all saved Elementor widget settings.

**Never activate two copies at once.** They declare the same classes; the bootstrap now bails out early if a second copy loads, but the duplicate should be deleted rather than relied on.

## Usage — Builder (recommended)
1. Go to **IW Mega Menu** in the WordPress admin sidebar.
2. Click **+ New**, name the menu, and click **Create**.
3. Click **+ Add Top-Level Item** to add a nav bar item. Click any row to open its editor.
4. Expand a row with the chevron, then use **+ Add Section** / **+ Add Link** to nest items beneath it.
5. In an item's editor, set the Label, Link URL, Description, Badge, and Icon. Click **Browse** beside the Icon field to pick from the built-in icon set, or type any Font Awesome class directly.
6. Watch the right-hand live preview — it opens to whatever you're editing and matches how the item will render on the site.
7. Click **Save Menu**, then place it on the site (see below). The builder shows the shortcode and template tag for the selected menu under its toolbar.

## Placing a menu

| Where | How |
|-------|-----|
| Block editor / block themes | Add the **IW Mega Menu** block (e.g. in the Header template part) and choose a menu. An optional *Accessible label* names the navigation for screen readers. |
| Classic editor, widgets, other builders | `[iw_mega_menu id="123"]` — optional `label="Main Navigation"`. |
| Theme templates | `<?php if ( function_exists( 'iw_mega_menu' ) ) { iw_mega_menu( 123 ); } ?>` — or `iw_get_mega_menu( 123 )` to get the markup as a string. Both take an optional `[ 'label' => '…' ]`. |
| Elementor | The **IW Mega Menu** widget (see below). |

The menu's nav bar inherits its surroundings: on the ACC theme it follows the background it sits on (white text and yellow accents on purple, black and orange on white); on other themes it assumes a dark bar. Font Awesome 6 loads from cdnjs only when a rendered menu uses an icon — return `''` from the `iw_mega_menu_font_awesome_url` filter if your theme already loads it.

## Migrating a legacy widget to the Builder

Menus built before v2.0.0 live inside the Elementor widget itself (the **Legacy Repeater**), not as a reusable saved menu. To convert one:

1. Open **IW Mega Menu** in the admin and create a new menu (**+ New**).
2. Click **Import Legacy Widget**. The dropdown lists every Elementor page/template that still has an IW Mega Menu widget with repeater items, showing the item count and the first few labels so you can tell them apart.
3. Pick one and click **Import**. The flat `parent_id` structure is rebuilt as a proper three-level tree.
4. Review it in the live preview, then **Save Menu**.
5. Edit the original page/template in Elementor, set **Menu Source** to *Saved Menu (Builder)*, and select your new menu.

The import **only reads** — the original widget is left untouched, so you can import, compare, and roll back by simply not changing the widget's Menu Source.

Two things the importer normalises rather than copying verbatim:

- **Sections that have links are switched to accordion.** Nested links only render on desktop inside an accordion (see *Menu Structure* above), so importing them any other way would silently hide them.
- **Items are never duplicated.** The legacy renderer also pushed any item with `parent_id` of `0` into the top level *even when it was already nested under a parent*, so such an item appeared twice. The importer nests it once, under its parent.

Because of these, compare the preview against the live menu before switching a production page over.

## Exporting

**Export** downloads the current builder menu as JSON — useful for backups or moving a menu between sites (pair it with **Import JSON** on the destination). It exports saved builder menus only; to get a legacy widget's items out, import it first using the steps above, then export.

## Usage — Elementor widget
The widget is only available when Elementor is active. Prefer **Menu Source → Saved Menu (Builder)**; the steps below describe the legacy repeater kept for older pages.

1. In the WordPress admin, edit a page or Header Template with Elementor.
2. Search for **IW Mega Menu** in the Elementor widget panel and drag it into your layout.
3. In the **Menu Structure** section, add your menu items:
   - **Top-Level**: Primary navigation triggers.
   - **Second-Level**: Categories or Accordion headers. Can include an optional description and icon.
   - **Third-Level**: Sub-links within categories or accordions.
4. (Optional) Assign a pattern or Elementor template to display as a sidebar card for desktop mega menu dropdowns and mobile drawers.
5. Customize colors, typography, borders, and spacing in the **Style** tab.
6. Publish or save your template.

## Changelog

### v3.0.0 (Current Release)

#### Works without Elementor
- **Elementor is now optional.** Menu markup moved out of the Elementor widget into a shared renderer (`includes/class-iw-mega-menu-renderer.php`). The "Elementor is required" admin notice is gone.
- **New placements:** the server-rendered **IW Mega Menu** block (no build step), the `[iw_mega_menu id="…"]` shortcode, and the `iw_mega_menu()` / `iw_get_mega_menu()` template tags.
- **The Elementor widget is a thin adapter** over the renderer. Every control and the legacy repeater still work, and existing pages render unchanged in structure. The duplicated Underscore editor template was removed; Elementor now previews the widget server-side, so the editor shows exactly the front-end markup (including saved Builder menus, which the old template couldn't show).
- **Sidebars accept patterns.** Any pattern can be a dropdown sidebar; Elementor templates are listed too when Elementor is active, and existing Elementor sidebar selections keep working.
- **Fixed: menus outside Elementor never initialized on pages that also load Elementor.** The script skipped its standalone init whenever `elementorFrontend` existed. Every menu wrapper on the page is now initialized exactly once.
- `GET /iw-mega-menu/v1/menus` now needs `edit_posts` (so authors can pick a menu in the block); creating, editing and deleting menus still need `edit_theme_options`.

#### ACC brand standards
- Restyled to the ACC design system: ACC palette, Montserrat type scale, fluid spacing, 10px card radius, 1px/2px-offset focus ring, 300ms transitions, and the background-context rule (orange accents on light, yellow on dark).
- Accordions and mobile sublists use the ACC disclosure — an accent plus that turns into × — replacing the Font Awesome square carets. Top items with a dropdown get the ACC chevron.
- The mobile toggle is the ACC **MENU** pill (orange, yellow ring on hover; icon-only circle below 700px) and the mobile drawer is the ACC menu overlay (purple gradient, H3 items, white rules, yellow accents).
- Badges use the ACC chip (in dropdowns) and tag (on the nav bar and mobile overlay).
- Breakpoints follow the ACC frames: the mobile drawer takes over below **700px** (was 768px).
- Icon color presets are limited to the ACC palette. **Teal, blue and red are not brand colors**: they were removed from the pickers, and menus saved with them now render in purple, dark purple and orange. Orange is now the brand `#D23F00` (was `#FA4616`).
- Closed dropdowns are now `visibility: hidden`, so their links are no longer reachable by Tab or screen readers while invisible.

#### PHP 8.4 and housekeeping
- Verified against PHP 8.4: no implicitly-nullable parameters, dynamic properties, `${}` interpolation or removed functions. Hardened array access in the renderer and REST API so incomplete or legacy menu data can't raise PHP 8 warnings or a `json_decode()` `TypeError`.
- Removed the deprecated `_register_controls()` shim, and the `elementor/widgets/widgets_registered` listener now attaches only on Elementor < 3.5, so neither raises deprecation notices.
- Fixed the text domain in the menu post type and admin page (`iw-megamenu` → `iw-mega-menu`, matching the plugin header) so those strings can be translated.

### v2.0.0
- Added the dedicated IW Mega Menu Builder admin dashboard.
- Added reusable saved menus with Elementor widget selection and legacy repeater fallback.
- Added REST CRUD endpoints, Elementor template lookup, WordPress menu import, and JSON import/export.

#### Accordion header layout
- **Removed the dead vertical space in accordion headers.** The toggle was `flex-direction: column`, which stacked the caret as a third row *below* the label (`align-self: flex-end` only pushed it right). It is now a wrapping row: label and caret share the first line with the caret vertically centered at the right, and the optional description wraps onto a full-width second line — so the header is compact whether or not a description is set.
- Caret and description now inherit the toggle's own text color instead of being hardcoded, so both stay legible in the collapsed (dark-on-grey) and expanded (white-on-purple) states. This also fixed a latent contrast bug where an accordion description rendered `#333` on the purple expanded background.
- The caret keeps a consistent `fa-regular` weight in both states and flips direction only; it previously switched to `fa-solid` when open, which rendered as a heavy filled block rather than a caret.

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

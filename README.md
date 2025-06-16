# IW Mega Menu

A powerful, Elementor-optimized Mega Menu plugin with multi-level navigation and widget support. Developed for Austin Community College - Instructional Web Projects.

## Version
**1.0.1**

## Features
- Fully responsive mega menu for desktop, tablet, and mobile
- Multi-level navigation (up to 3 levels)
- Elementor integration for easy menu building
- Customizable styles for top, second, and third-level links
- Mobile-friendly hamburger menu with smooth accordion behavior
- Sidebar support for menu dropdowns
- Accessibility-friendly markup and keyboard navigation

## Menu Structure Limits
- **Second level:** Up to 4 links per column, 2 columns (excluding accordions)
- **Third level:** Up to 2 links per column, 3 columns

## Installation
1. Download or clone this repository to your local machine.
2. Upload the `iw-megamenu` folder to your WordPress site's `wp-content/plugins/` directory.
3. Activate the plugin through the WordPress admin dashboard (`Plugins > Installed Plugins`).
4. Ensure Elementor is installed and activated.

## Usage
1. In the WordPress admin, go to **Pages** or **Templates** and edit with Elementor.
2. Search for **IW Mega Menu** in the Elementor widget panel and drag it into your layout.
3. Use the menu structure panel to add, remove, or reorder menu items (supports up to 3 levels).
4. Set URLs, icons, and accordion behavior for each menu item as needed.
5. Customize styles for each menu level using the Elementor style controls.
6. Save and preview your page.

## Customization
- **Colors, fonts, and spacing** can be adjusted via Elementor's style controls for the widget.
- **Mobile menu**: Styles are enforced via inline CSS for maximum compatibility with themes and Elementor.
- **Sidebar content**: Assign Elementor templates as sidebars for top-level dropdowns.
- **Accessibility**: Menu supports keyboard navigation and ARIA attributes.

## Changelog
### v1.0.1 (Current Release)
#### Major New Features & Improvements
- **Menu Item Descriptions:**
  - Second-level menu items now support optional descriptions, displayed beneath the label for added context and clarity.
  - Descriptions are styled for readability and only appear if content is provided.
- **Accordion Button Enhancements:**
  - Accordion buttons for second-level items now feature distinct open/closed color states for improved usability and accessibility.
  - Closed: Light background (`#F3F4F5`), black text/caret. Open: Purple background (`#4D1979`), white text/caret.
- **Spacing & Layout Tweaks:**
  - Reduced vertical spacing above the first accordion and below the two-column section for a more compact, visually balanced dropdown.
  - Improved alignment and padding for all menu levels.
- **Accessibility & QOL:**
  - Descriptions are only available for second-level items, reducing clutter and improving editing clarity.
  - All new features are fully accessible and keyboard-navigable.
- **Bug Fixes:**
  - Resolved an issue where menu cards would disappear in the Elementor Editor after icon color changes.
  - Improved CSS specificity to ensure accordion button colors are always correct, regardless of theme overrides.
- **Badges & Icons**
  - Font Awesome caret icons for accordions (down/up)
  - Menu item badges (e.g., "New", "Beta")

#### Use Cases & Design Rationale
- **Menu Item Descriptions:**
  - Use for brief explanations, context, or calls-to-action under section headers (e.g., "View a more complete list of projects").
  - Helps users quickly understand the purpose of each menu section, especially in large or complex menus.
- **Accordion Button States:**
  - Clear visual feedback for open/closed state improves navigation, especially for keyboard and screen reader users.
- **Layout & Spacing:**
  - Tighter, more consistent spacing makes the menu easier to scan and more visually appealing.

#### Developer Notes
- All new features are implemented with extensibility in mind and follow Elementor/WordPress best practices.
- CSS changes use `!important` where necessary to override theme conflicts.
- Descriptions are only rendered if content is present, ensuring no extra spacing for empty fields.
- Accordion state is managed via `aria-expanded` for accessibility.

## Roadmap (Future)
- Animated menu card transitions (slide & fade)
- Individual icon color pickers (Elementor native)
- Further accessibility and QOL improvements

## Support
For questions, issues, or feature requests, please open an issue in this repository or contact the Instructional Web Projects team at [instruction.austincc.edu](https://instruction.austincc.edu).

---

**Developed for Austin Community College - Instructional Web Projects** 

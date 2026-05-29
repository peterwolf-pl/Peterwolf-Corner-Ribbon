=== Peterwolf Corner Ribbon ===
Contributors: peterwolfpl
Tags: ribbon, corner ribbon, badge, overlay, announcement
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a customizable diagonal corner ribbon overlay to any WordPress website layout.

== Description ==

Peterwolf Corner Ribbon displays an elegant diagonal ribbon in the upper-left or upper-right corner of the public website. It is designed for labels such as Preview, New, Sale, Archived, or any custom notice that needs to remain visible above the theme layout.

The plugin does not require editing templates or blocks. Activate it, open Appearance > Corner Ribbon, and configure the ribbon from a single settings page.

Source code: https://github.com/peterwolf-pl/Peterwolf-Corner-Ribbon

Features:

* Custom single-line or multi-line text.
* Left or right upper-corner placement.
* Configurable ribbon background and text colors.
* Optional drop shadow with selectable color.
* Width, thickness, top offset, and layer-order controls.
* Font family, font size, line height, font weight, letter spacing, and uppercase options.
* Sanitized settings stored through the WordPress Settings API.
* Lightweight frontend output: one stylesheet and one HTML element when enabled.

== Installation ==

1. Upload the `peterwolf-corner-ribbon` folder to `/wp-content/plugins/`.
2. Activate Peterwolf Corner Ribbon in the WordPress Plugins screen.
3. Open Appearance > Corner Ribbon.
4. Enter the ribbon text, adjust its appearance, and save the settings.

== Frequently Asked Questions ==

= Does the ribbon require a particular theme or page builder? =

No. It is inserted in the frontend footer and positioned over the page layout, making it independent of theme content structures and page builders.

= Can I create a ribbon with multiple lines of text? =

Yes. Add line breaks in the Ribbon text field and the frontend ribbon will preserve them.

= Does the ribbon appear in the WordPress dashboard? =

No. It is displayed only on the public-facing frontend when enabled.

== Changelog ==

= 1.0.3 =

* Added the public source code repository link.

= 1.0.2 =

* Added required plugin directory metadata and updated WordPress.org compatibility declarations.

= 1.0.1 =

* Added a line height control for multi-line ribbon text.

= 1.0.0 =

* Initial release with customizable diagonal ribbon display and admin settings.

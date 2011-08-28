=== Custom Field Bulk Editor ===
Contributors: sparkweb
Donate link: http://www.soapboxdave.com/
Tags: custom fields, bulk, editor, custom post type
Requires at least: 3.0
Tested up to: 3.2.1
Stable tag: 1.2
This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types.

== Description ==

This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types. The plugin also has some actions included so that plugins and themes can integrate seamlessly and build their own extensions into the functionality.



== Frequently Asked Questions ==

= How can I remove custom fields from a record? =

Just enter the name and leave the value blank.

= Can I disable this plugin from showing up on some post types? =

Yup, just go to the settings page and you can turn post types on and off.

= How do the plugin hooks/customizations work? =

You can add your own metabox to enter special data or you can just add some rows before the built-in custom field rows. Then add a special "saving" function which is run automatically on each post being changed.

[Sample Code For Adding Your Own Extra Save Lines](http://pastebin.com/jBtyBtKv) (see screenshots for how this looks)

To see a complete integration example, download the [FoxyShop](http://wordpress.org/extend/plugins/foxyshop/) plugin and look in the `customposttype.php` file. Just search for the `cfbe` functions toward the end.


== Installation ==

Copy the folder to your WordPress 
'*/wp-content/plugins/*' folder.

By default the plugin will be enabled for all post types but you can go to the settings page and turn it off for any post types where it is not needed.


== Screenshots ==

1. Bulk Editor Screen
2. Settings Screen
2. View With Sample Customization


== Changelog ==

= 1.2 (8/28/2011) =
* Added Hooks so Themes and Plugin Developers Can Build Their Own Custom Integrations

= 1.1 (8/17/2011) =
* Added Post Searching and Filtering by Applicable Taxonomies

= 1.0 (8/17/2011) =
* Initial Release


== Upgrade Notice ==

None

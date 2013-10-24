=== Custom Field Bulk Editor ===
Contributors: sparkweb
Donate link: http://www.soapboxdave.com/
Tags: custom fields, bulk, editor, custom post type
Requires at least: 3.0
Tested up to: 3.7
Stable tag: 1.7
This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types.

== Description ==

This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types. The plugin also has some actions included so that plugins and themes can integrate seamlessly and build their own extensions into the functionality.



== Frequently Asked Questions ==

= How can I remove (delete) custom fields from a record? =

Just enter the name and leave the value blank.

= Can I disable this plugin from showing up on some post types? =

Yup, just go to the settings page and you can turn post types on and off.

= Can I enter multiple values per custom field name? =

Yes. Just check the "Add New Custom Fields Instead of Updating" checkbox when saving.

= How do the plugin hooks/customizations work? =

You can add your own metabox to enter special data or you can just add some rows before the built-in custom field rows. Then add a special "saving" function which is run automatically on each post being changed.

[Sample Code For Adding Your Own Extra Save Lines](http://pastebin.com/jBtyBtKv) (see screenshots for how this looks)

To see a complete integration example, download the [FoxyShop](http://wordpress.org/extend/plugins/foxyshop/) plugin and look in the `bulkeditor.php` file.

= What is the difference between single value and multi value mode? =

Single Value Mode lets you set a single value for all checked postes. Multi Value Mode lets you set a different custom field and value for each post at once. Action hooks are not run in Multi Value mode.

= Can I change the name of some custom fields? =

Yes. Click the link "Want to change a custom field name?" at the bottom of the Single Value Mode form and you'll be able to enter the original field name and the new field name.

== Installation ==

Copy the folder to your WordPress
'*/wp-content/plugins/*' folder.

By default the plugin will be enabled for all post types but you can go to the settings page and turn it off for any post types where it is not needed.


== Screenshots ==

1. Bulk Editor Screen
2. Settings Screen
2. View With Sample Customization


== Changelog ==

= 1.7 (11/12/2012) =
* Added a feature to allow setting multiple fields/records per custom field name

= 1.6 (8/21/2012) =
* Added a feature to allow the changing of field names

= 1.5 (7/23/2012) =
* Added a bulk editor for multi-value mode so that names and values can be pasted into a textarea
* FIX: Removed testing mode from single value mode

= 1.4.1 (7/12/2012) =
* Fixed mistaken multi-value mode link for posts

= 1.4 (7/12/2012) =
* Added multi-value mode
* Added filter to allow customized role display

= 1.3.2 (3/2/2012) =
* Changed menu name from "Edit Custom Fields" to "Bulk Edit Fields" for better recognition
* Corrected double serialization

= 1.3.1 (11/12/2011) =
* Fixed problem where post categories and tags weren't being filtered correctly

= 1.3 (10/13/2011) =
* Fixed redirect error when saving custom fields for posts
* Tested for WordPress 3.3

= 1.2 (8/28/2011) =
* Added Hooks so Themes and Plugin Developers Can Build Their Own Custom Integrations

= 1.1 (8/17/2011) =
* Added Post Searching and Filtering by Applicable Taxonomies

= 1.0 (8/17/2011) =
* Initial Release


== Upgrade Notice ==

Added bulk editor for multi-value mode and fixed (removed) testing mode on single value mode
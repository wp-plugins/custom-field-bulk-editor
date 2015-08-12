=== Custom Field Bulk Editor ===
Contributors: sparkweb
Donate link: http://www.soapboxdave.com/
Tags: custom fields, bulk, editor, custom post type
Requires at least: 3.0
Tested up to: 4.3
Stable tag: 1.9.1
This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types.

== Description ==

This plugin lets you edit the custom fields for many posts at once. Designed to work with pages, posts, and all custom post types. The plugin also has some actions included so that plugins and themes can integrate seamlessly and build their own extensions into the functionality.



== Frequently Asked Questions ==

= I have a lot of posts, how can I edit them all? =

This plugin will run into memory problems and not submit properly if there are too many posts on a page. Currently the page limit is set at 200. To show more or less on a page, just add "posts_per_page=10" to the querystring to display only 10 posts. Use the querystring page_number=X to display a certain page.

= How can I remove (delete) custom fields from a record? =

Just enter the name and leave the value blank.

= Can I disable this plugin from showing up on some post types? =

Yup, just go to the settings page and you can turn post types on and off.

= Can I enter multiple values per custom field name? =

Yes. Just check the "Add New Custom Fields Instead of Updating" checkbox when saving.

= Can I search by date? =

Yes. Search for a date range by entering a search query with ".." between the dates. So if you wanted to search for all dates between 2014-01-01 and 2014-02-01 you would search for "2014-01-01..2014-02-01". Or search for "2014-01-01.." to search for all orders between 2014-01-01 and the current date. Or search from the beginning of time until 2010-01-01 by searching "..2010-01-01".

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

= 1.9.1 (8/11/2014) =
* Fixing paging feature
* Adding overrides for Advanced Custom Fields v5
* Cleaning up the update custom field name feature
* Changing mysql_real_escape_string() to esc_sql()

= 1.9 (7/25/2014) =
* Adding ajax fetch for multi-value entry. Gets current value.

= 1.8 (2/21/2014) =
* Adding ability to search posts by date
* Limiting default posts per page to 200

= 1.7.1 (1/5/2014) =
* Fix for incompatibility with the (fabulous) Advanced Custom Fields plugin

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

Adding ability to search posts by date, limiting default posts per page to 200

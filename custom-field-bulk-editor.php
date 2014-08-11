<?php /*

**************************************************************************
Plugin Name: Custom Field Bulk Editor
Plugin URI: http://wordpress.org/extend/plugins/custom-field-bulk-editor/
Description: Allows you to edit your custom fields in bulk. Works with custom post types.
Author: SparkWeb Interactive, Inc.
Version: 1.9.1
Author URI: http://www.sparkweb.net/

**************************************************************************

Copyright (C) 2014 SparkWeb Interactive, Inc.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

**************************************************************************/


//Init
add_action('admin_menu', 'cfbe_init');
function cfbe_init() {

	//Create or Set Settings
	global $cfbe_post_types;
	$cfbe_post_types = get_option("cfbe_post_types");

	//Check for Double Serialization
	if (is_serialized($cfbe_post_types)) {
		$cfbe_post_types = unserialize($cfbe_post_types);
		update_option("cfbe_post_types", $cfbe_post_types);
	}

	//Create Settings if New
	if (!is_array($cfbe_post_types)) cfbe_create_settings();

	//Create Menus
	$post_types = get_post_types();
	$skip_array = array("revision", "attachment", "nav_menu_item", "acf", "acf-field-group", "acf-field");
	foreach ($post_types as $post_type ) {
		if (in_array($post_type, $skip_array)) continue;
		if (in_array($post_type, $cfbe_post_types)) add_submenu_page("edit.php".($post_type != "post" ? "?post_type=".$post_type : ""), __('Bulk Edit Fields'), __('Bulk Edit Fields'), apply_filters('cfbe_menu_display_' . $post_type, 'manage_options'), 'cfbe_editor-'.$post_type, 'cfbe_editor');
	}

	if (isset($_REQUEST['page'])) {
		if (strpos($_REQUEST['page'], "cfbe_editor-") !== false) {
			wp_enqueue_style('cfbe-style', WP_PLUGIN_URL . "/custom-field-bulk-editor/cfbe-style.css");
		}
	}
}


//Main Editor Menu
function cfbe_editor() {
	$post_type = str_replace("cfbe_editor-", "", $_REQUEST['page']);
	$obj = get_post_type_object($post_type);

	$edit_mode = isset($_REQUEST['edit_mode']) ? $_REQUEST['edit_mode'] : 'single';
	$edit_mode_button =  ' <a class="' . (version_compare(get_bloginfo('version'), '3.2', "<") ? "button " : "") . 'add-new-h2" href="edit.php?' . ($post_type != "post" ? "post_type=$post_type&" : "") . 'page=cfbe_editor-' . $post_type . '&edit_mode=' . ($edit_mode == "single" ? "multi" : "single") . '">' . ($edit_mode == "single" ? __("Switch to Multi Value Mode") : __("Switch to Single Value Mode")) . '</a>';

	$multi_value_mode = isset($_REQUEST['multi_value_mode']) ? $_REQUEST['multi_value_mode'] : 'single';
	$multi_mode_button =  ' <a href="edit.php?' . ($post_type != "post" ? "post_type=$post_type&" : "") . 'page=cfbe_editor-' . $post_type . '&edit_mode=multi&multi_value_mode=' . ($multi_value_mode == "single" ? "bulk" : "single") . '">' . ($multi_value_mode == "single" ? __("Switch to Bulk Entry Mode") : __("Switch to Single Entry Mode")) . '</a>';


	echo '<div class="wrap">';
	echo '<div class="icon32 icon32-posts-page" id="icon-edit-pages"><br></div>';
	echo '<h2>Edit Custom Fields For ' . $obj->labels->name . $edit_mode_button . '</h2>';

	//Saved Notice
	if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Success! Custom field values have been saved.') . '</p></div>';

	echo "<br />";

	if ($edit_mode == "multi") {
		echo "<p>" . $multi_mode_button . "</p>";
	}
	echo '<form action="edit.php" name="cfbe_form_1" id="cfbe_form_1" method="get">';
	if ($post_type != "post") echo '<input type="hidden" name="post_type" value="' . htmlspecialchars($post_type) . '" />'."\n";
	echo '<input type="hidden" name="page" value="cfbe_editor-' . htmlspecialchars($post_type) . '" />'."\n";
	echo '<input type="hidden" name="edit_mode" value="' . htmlspecialchars($edit_mode) . '" />'."\n";
	echo '<input type="hidden" name="multi_value_mode" value="' . htmlspecialchars($multi_value_mode) . '" />'."\n";

	$args = array(
		"post_type" => $post_type,
		"posts_per_page" => isset($_GET['posts_per_page']) ? (int)$_GET['posts_per_page'] : 200,
		"post_status" => array("publish", "pending", "draft", "future", "private"),
		"order" => "ASC",
		"orderby" => "id",
		"paged" => isset($_GET['page_number']) ? (int)$_GET['page_number'] : 1,
	);

	//Search
	$searchtext = "";
	if (isset($_GET['searchtext'])) {
		$searchtext = esc_attr($_GET["searchtext"]);

		//Date
		if (strpos($searchtext, "..") !== false) {
			$date_array = explode("..", $searchtext);
			$start_date = trim($date_array[0]);
			$end_date = trim($date_array[1]);
			if (!$start_date || $start_date == "x") {
				$start_date = "1970-01-01";
			}
			if (!$end_date || $end_date == "x") {
				$end_date = "now";
			}
			$args["date_query"] = array(
				array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				),
			);

		//Regular Search
		} else {
			$args["s"] = $_GET["searchtext"];
		}
	}

	$taxonomies = get_object_taxonomies($post_type);
	foreach ($taxonomies AS $taxonomy) {
		$tax = get_taxonomy($taxonomy);
		$terms = get_terms($taxonomy);
		if (count($terms) == 0) continue;
		$tax_name = $tax->label;
		if (isset($_GET[$taxonomy])) {
			$query_slug = $_GET[$taxonomy];
			$arg_taxonomy = $taxonomy;
			if ($arg_taxonomy == "post_tag") $arg_taxonomy = "tag";
			if ($arg_taxonomy == "category") $arg_taxonomy = "category_name";
			if ($query_slug != "") $args[$arg_taxonomy] = $_GET[$taxonomy];
		} else {
			$query_slug = "";
		}
		echo '<label for="' . $taxonomy . '">' . $tax_name . '</label>';
		echo '<select name="' . $taxonomy . '" id="' . $taxonomy . '" class="postform">';
		echo '<option value="">' . sprintf(__('Show All %s'), $tax_name) . '</option>'."\n";
		foreach ($terms as $term) {
			echo '<option value='. $term->slug . ($term->slug == $query_slug ? ' selected="selected"' : '') . '>' . $term->name .' (' . $term->count .')</option>';
		}
		echo "</select>";
	}
	echo '<label for="searchtext">' . __("Search") . '</label>';
	echo '<input type="text" name="searchtext" id="searchtext" value="' . $searchtext . '" />';
	echo '<input type="submit" value="Apply" class="button" />';
	echo '</form>'."\n\n";

	echo '<form action="options.php" name="cfbe_form_2" id="cfbe_form_2" method="post">';
	echo '<input type="hidden" name="cfbe_save" value="1" />'."\n";
	echo '<input type="hidden" name="cfbe_current_max" id="cfbe_current_max" value="3" />'."\n";
	echo '<input type="hidden" name="cfbe_post_type" value="' . esc_attr($post_type) . '" />'."\n";
	echo '<input type="hidden" name="edit_mode" value="' . esc_attr($edit_mode) . '" />'."\n";
	echo '<input type="hidden" name="multi_value_mode" value="' . esc_attr($multi_value_mode) . '" />'."\n";
	if (isset($_REQUEST['search'])) {
		echo '<input type="hidden" name="search" value="' . esc_attr($_REQUEST['search']) . '" />'."\n";
	}
	if (isset($_REQUEST['search'])) {
		echo '<input type="hidden" name="search" value="' . esc_attr($_REQUEST['search']) . '" />'."\n";
	}
	if (isset($_REQUEST['page_number'])) {
		echo '<input type="hidden" name="page_number" value="' . esc_attr($_REQUEST['page_number']) . '" />'."\n";
	}
	if (isset($_REQUEST['posts_per_page'])) {
		echo '<input type="hidden" name="posts_per_page" value="' . esc_attr($_REQUEST['posts_per_page']) . '" />'."\n";
	}
	wp_nonce_field('cfbe-save');

	$all_posts = new WP_Query($args);
	//echo "<pre>" . print_r($all_posts, 1) . "</pre>";
	?>
	<table cellspacing="0" class="wp-list-table widefat fixed posts cfbe-table">
	<thead>
	<tr>
		<?php if ($edit_mode == "single") { ?><th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th><?php } ?>
		<th style="" class="manage-column column-id" id="id" scope="col"><?php _e("ID") ?></th>
		<th style="" class="manage-column column-title desc" id="title" scope="col"><span><?php _e("Title") ?></span></th>
		<?php if ($edit_mode == "multi" && $multi_value_mode != "bulk") { ?>
		<th class="manage-column column-fieldname desc" id="fieldname" scope="col"><span><?php _e("Field Name") ?></span></th>
		<th class="manage-column column-fieldvalue desc" id="fieldname" scope="col"><span><?php _e("Field Value") ?></span></th>
		<?php } ?>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<?php if ($edit_mode == "single") { ?><th class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox"></th><?php } ?>
		<th style="" class="manage-column column-id" id="id" scope="col"><?php _e("ID") ?></th>
		<th style="" class="manage-column column-title desc" id="title" scope="col"><span><?php _e("Title") ?></span></th>
		<?php if ($edit_mode == "multi" && $multi_value_mode != "bulk") { ?>
		<th class="manage-column column-fieldname desc" id="fieldname" scope="col"><span><?php _e("Field Name") ?></span></th>
		<th class="manage-column column-fieldvalue desc" id="fieldname" scope="col"><span><?php _e("Field Value") ?></span></th>
		<?php } ?>
	</tr>
	</tfoot>

	<tbody id="the-list">

	<?php
	$i = 1;
	$tabindex = 10000;
	while ($all_posts->have_posts()) {
		$all_posts->the_post();
		$post = $all_posts->post;
		echo '<tr valign="top" class="' . ($i % 2 ? 'alternate ' : '') . 'format-default" id="post-' . $post->ID . '" rel="' . $i . '">';
		if ($edit_mode == "single") echo '<th class="check-column" scope="row" style="padding: 9px 0;"><input type="checkbox" value="' . $post->ID . '" name="post[]"></th>';
		echo '<td class="id column-id">' . $post->ID . '</td>';
		echo '<td class="post-title page-title column-title"><strong><a title="Edit" href="post.php?post=' . $post->ID . '&amp;action=edit" class="row-title">' . $post->post_title . '</a>' . ($post->post_status != 'publish' ? ' - ' . ucwords($post->post_status) : '') . '</strong></td>';
		if ($edit_mode == "multi") {
			echo '<input type="hidden" value="' . $post->ID . '" name="post[]">' . "\n";
			if ($multi_value_mode != "bulk") {
				echo '<td class="post-fieldname column-fieldname"><input type="text" name="cfbe_multi_fieldname_' . $post->ID . '" id="cfbe_multi_fieldname_' . $post->ID . '" value="" class="cfbe_multi_fieldname" data-postid="' . $post->ID . '" tabindex="' . ($tabindex) .'" /> <a href="#" class="fill_down button" rel="' . $post->ID . '">Fill</a></td>';
				echo '<td class="post-fieldvalue column-fieldnvalue"><textarea name="cfbe_multi_fieldvalue_' . $post->ID . '" id="cfbe_multi_fieldvalue_' . $post->ID . '" class="cfbe_multi_fieldvalue" tabindex="' . ($tabindex + 1) .'"></textarea></td>';
			}
		}
		echo "</tr>\n";
		$i++;
		$tabindex = $tabindex + 2;
	}
	?>
		</tbody>
	</table>

	<?php
	if ($edit_mode == "multi" && $multi_value_mode == "bulk") {

		echo '<p>Please enter the field names in the left column and the field values in the right column. They will be applied to the post ID\'s in the order they appear above. You can leave a field blank to not apply anything.</p>';

		echo '<textarea name="multi_bulk_name" style="float: left; height: 180px; width: 40%; margin-right: 10px;"></textarea>';
		echo '<textarea name="multi_bulk_value" style="float: left; height: 180px; width: 40%;"></textarea>';
		echo '<div style="clear: both;"></div>' . "\n";



	} elseif ($edit_mode == "single") {
		do_action('cfbe_before_metabox', $post_type);
		?>

	<table class="widefat cfbe_table">
		<thead>
			<tr>
				<th><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAABM0lEQVR42sWSv0sCcRjGP/mDhiSCTEUQCa4xcGhqcKjBCKGlza2lxT+gob8naEtuSIgMd8WpLTy973EcceqJh1zp3bepg2gpL+iBF97pfT/P+7zwBzoH5IpVW4vF4vL2ponvB/hL/8dbhRBcXV+SCAIfz3vn6aHzK+y554a9rNfrMoIN5Crq9/sSkLGoCSQ+m7eXZxbmEN/USRYVBsU8YiTQxzpKRiG/vodh2RiWzW4hSyGdAiAkWJhDUuVT4js53FYDMRJU9ivkNnOoPRXDsjk+LJHZ3qLZ7oYE4QDf1HEf71gaGsF0gj7WUXsqg9EAZ+5gWDb37Q66+Yozc79bSBYV3FaDYDph4+gMJZNG7ak4c4dqqUpaZmm2uzgzl5PywZc7REohJNA0beUkahGe6IJ/1wc0yhZckNURBgAAAABJRU5ErkJggg==" alt="" /><?php _e('Set Custom Field Values For Checked '); echo $obj->labels->name; ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			do_action('cfbe_before_fields', $post_type);
			for ($i = 1; $i <= 3; $i++) :
			?>
			<tr>
				<td>
					<label for="cfbe_name_<?php echo $i; ?>"><?php _e('Custom Field Name'); ?>:</label>
					<input type="text" name="cfbe_name_<?php echo $i; ?>" id="cfbe_name_<?php echo $i; ?>" value="" class="cfbe_field_name" />

					<label for="cfbe_value_<?php echo $i; ?>"><?php _e('Value'); ?>:</label>
					<textarea name="cfbe_value_<?php echo $i; ?>" id="cfbe_value_<?php echo $i; ?>" class="cfbe_field_value"></textarea>

					<div style="clear: both;"></div>

				</td>
			</tr>
			<?php endfor; ?>
			<tr id="cfbe_more_tr">
				<td>
					<input type="button" id="cfbe_morebutton" name="cfbe_morebutton" class="button" value="<?php _e('Add More Fields'); ?>" />
					<span class="cfbe_hint">Hint: To remove a field from a record, enter its name and leave its value empty</span>

				</td>

			</tr>
		</tbody>
	</table>

	<!-- Rename Custom Field Name -->
	<p><a href="#" id="change_cf_name"><?php _e('Want to change a custom field name?'); ?></a></p>
	<table class="widefat cfbe_table" id="change_cf_name_table" style="display: none;">
		<thead>
			<tr>
				<th><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QAAAAAAAD5Q7t/AAABM0lEQVR42sWSv0sCcRjGP/mDhiSCTEUQCa4xcGhqcKjBCKGlza2lxT+gob8naEtuSIgMd8WpLTy973EcceqJh1zp3bepg2gpL+iBF97pfT/P+7zwBzoH5IpVW4vF4vL2ponvB/hL/8dbhRBcXV+SCAIfz3vn6aHzK+y554a9rNfrMoIN5Crq9/sSkLGoCSQ+m7eXZxbmEN/USRYVBsU8YiTQxzpKRiG/vodh2RiWzW4hSyGdAiAkWJhDUuVT4js53FYDMRJU9ivkNnOoPRXDsjk+LJHZ3qLZ7oYE4QDf1HEf71gaGsF0gj7WUXsqg9EAZ+5gWDb37Q66+Yozc79bSBYV3FaDYDph4+gMJZNG7ak4c4dqqUpaZmm2uzgzl5PywZc7REohJNA0beUkahGe6IJ/1wc0yhZckNURBgAAAABJRU5ErkJggg==" alt="" /><?php _e('Change Custom Field Name'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<label for="cfbe_fieldname_1"><?php _e('Original Custom Field Name'); ?>:</label>
					<input type="text" name="cfbe_fieldname_1" id="cfbe_fieldname_1" value="" class="cfbe_field_name" />
					<label for="cfbe_fieldname_2"><?php _e('New Custom Field Name'); ?>:</label>
					<input type="text" name="cfbe_fieldname_2" id="cfbe_fieldname_2" value="" class="cfbe_field_name" />
					<div style="clear: both;"></div>

				</td>
			</tr>
		</tbody>
	</table>

	<?php } ?>
	<p>
		<input type="submit" class="button-primary" value="<?php _e('Save Custom Fields'); ?>" style="margin-right: 15px;" />
		<label for="cfbe_add_new_values"><input type="checkbox" name="cfbe_add_new_values" id="cfbe_add_new_values"<?php if (isset($_GET['cfbe_add_new_values'])) echo ' checked="checked"'; ?> /> Add New Custom Fields Instead of Updating (this allows you to create multiple values per name)</label>
	</p>
	</form>

	<div style="clear: both;"></div>

	<script type="text/javascript">
	jQuery(document).ready(function($){

		$("#change_cf_name").click(function() {
			$(this).hide();
			$("#change_cf_name_table").show();
			$("#cfbe_fieldname_1").focus();
			return false;
		});

		//Set To Make Sure We Aren't Getting Funny Values
		$("#cfbe_current_max").val(3);

		$("#cfbe_morebutton").click(function() {
			current_max = parseInt($("#cfbe_current_max").val());

			var newfields = "";
			for (i = 1; i <= 3; i++) {
				new_id = current_max + i;
				newfields += '<tr><td>';
				newfields += '<label for="cfbe_name_' + new_id + '"><?php _e('Custom Field Name'); ?>:</label>';
				newfields += '<input type="text" name="cfbe_name_' + new_id + '" id="cfbe_name_' + new_id + '" value="" class="cfbe_field_name" />';
				newfields += '<label for="cfbe_value_' + new_id + '"><?php _e('Value'); ?>:</label>';
				newfields += '<textarea name="cfbe_value_' + new_id + '" id="cfbe_value_' + new_id + '" class="cfbe_field_value"></textarea>';
				newfields += '<div style="clear: both;"></div>';
				newfields += '</td></tr>';
			}
			$("#cfbe_more_tr").before(newfields);

			$("#cfbe_current_max").val(current_max + 3);
			return false;
		});

		$(".cfbe_multi_fieldname").blur(function() {
			var postid = $(this).data("postid");
			var data = {
				'action': 'cfbe_lookup_meta_value',
				'post_id': postid,
				'field_name': $(this).val()
			};
			$.post(ajaxurl, data, function(response) {
				$("#cfbe_multi_fieldvalue_" + postid).val(response);
			});
		});

		$(".fill_down").click(function() {
			var fieldname = $("#cfbe_multi_fieldname_" + $(this).attr("rel")).val();
			var parent_rel = $(this).parents("tr").attr("rel");
			$(".cfbe-table > tbody > tr").each(function() {
				this_rel = $(this).attr("rel");
				if (parseInt(this_rel) > parseInt(parent_rel)) {
					$(this).find(".cfbe_multi_fieldname").val(fieldname).trigger("blur");
				}
			});
			return false;
		});

	});
	</script>

	<?php

	echo '</form>';
	echo '</div">';
}


//Save Custom Field
add_action('admin_init', 'cfbe_save');
function cfbe_save() {
	global $post_id, $wpdb;

	//Bail if not called or authenticated
	$actionkey = (isset($_POST['cfbe_save']) ? $_POST['cfbe_save'] : "");
	if ($actionkey != "1" || !check_admin_referer('cfbe-save')) return;

	//Setup
	$post_type = (isset($_POST['cfbe_post_type']) ? $_POST['cfbe_post_type'] : "");
	$posts = (isset($_POST['post']) ? $_POST['post'] : array());
	$edit_mode = $_POST['edit_mode'] == "multi" ? "multi" : "single";

	//Multi-value Method Array Setup
	$multi_value_mode = isset($_POST['multi_value_mode']) ? $_POST['multi_value_mode'] : 'single';
	$arr_names = array();
	$arr_values = array();
	if ($multi_value_mode == "bulk") {
		$lines1 = preg_split("/(\r\n|\n|\r)/", trim($_POST['multi_bulk_name']));
		$lines2 = preg_split("/(\r\n|\n|\r)/", trim($_POST['multi_bulk_value']));
		for ($i = 0; $i < count($lines1); $i++) {
			$arr_names[$i] = isset($lines1[$i]) ? $lines1[$i] : '';
			$arr_values[$i] = isset($lines2[$i]) ? $lines2[$i] : '';
		}
	}

	//Loop Through Each Saved Post
	$current_record_count = 0;
	foreach ($posts AS $post) {
		$post_id = (int)$post;

		//Multi Value
		if ($edit_mode == "multi") {

			//Bulk Edit Mode
			if ($multi_value_mode == "bulk") {

				$skip = 0;
				if (!isset($lines1[$current_record_count]) || !isset($lines2[$current_record_count])) $skip = 1;
				if (!$skip) if (!$lines1[$current_record_count] || !$lines2[$current_record_count]) $skip = 1;
				if (!$skip) {
					//echo $post_id . " write: " . $arr_names[$current_record_count] . " = " . $arr_values[$current_record_count] . "<br>\n";
					cfbe_save_meta_data($arr_names[$current_record_count], $arr_values[$current_record_count]);
				}



			//Multi-edit Mode
			} elseif (!empty($_POST['cfbe_multi_fieldname_'.$post_id])) {
				//echo 'EDIT ' . $post_id . ': ' . $_POST['cfbe_multi_fieldname_'.$post_id] . ' = ' . $_POST['cfbe_multi_fieldvalue_'.$post_id] . '<br />';
				cfbe_save_meta_data($_POST['cfbe_multi_fieldname_'.$post_id], $_POST['cfbe_multi_fieldvalue_'.$post_id]);
			}


		//Single Value
		} else {

			do_action('cfbe_save_fields', $post_type, $post_id);
			for($i=1; $i<=$_POST['cfbe_current_max']; $i++) {
				if (!empty($_POST['cfbe_name_'.$i])) {
					//echo 'EDIT ' . $post_id . ': ' . $_POST['cfbe_name_'.$i] . ' = ' . $_POST['cfbe_value_'.$i] . '<br />';
					cfbe_save_meta_data($_POST['cfbe_name_'.$i], $_POST['cfbe_value_'.$i]);
				}
			}
		}

		//Change Field Name
		if (isset($_POST['cfbe_fieldname_1']) && isset($_POST['cfbe_fieldname_2'])) {
			if ($_POST['cfbe_fieldname_1'] && $_POST['cfbe_fieldname_2']) {
				$sql = "UPDATE $wpdb->postmeta SET meta_key = '" . esc_sql($_POST['cfbe_fieldname_2']) . "' WHERE post_id = {$post_id} AND meta_key = '" . esc_sql($_POST['cfbe_fieldname_1']) . "'";
				$wpdb->query($sql);
			}
		}

		$current_record_count++;
	}

	$post_link = $post_type != "post" ? "post_type=$post_type&" : "";
	$url = "edit.php?" . $post_link . "page=cfbe_editor-$post_type&edit_mode={$edit_mode}&saved=1";
	$url .= "&multi_value_mode=" . $multi_value_mode;
	$url .= isset($_POST['cfbe_add_new_values']) ? '&cfbe_add_new_values=1' : '';
	$url .= isset($_POST['search']) ? '&search=' . $_POST['search'] : '';
	$url .= isset($_POST['posts_per_page']) ? '&posts_per_page=' . $_POST['posts_per_page'] : '';
	$url .= isset($_POST['page_number']) ? '&page_number=' . $_POST['page_number'] : '';
	wp_redirect(admin_url($url));
	exit;
}


//Settings Menu
add_action('admin_menu', 'cfbe_settings_menu');
function cfbe_settings_menu() {
	add_submenu_page('options-general.php', __('Custom Fields Bulk Editor Settings'), __('Bulk Editor Settings'), 'manage_options', 'cfbe_settings', 'cfbe_settings');
}
function cfbe_settings() {
	global $cfbe_post_types;

	echo '<div class="wrap">';
	echo '<div class="icon32" id="icon-options-general"><br></div>';
	echo '<h2>' . __('Custom Fields Bulk Editor Settings') . '</h2>';

	//Saved Notice
	if (isset($_GET['saved'])) echo '<div class="updated"><p>' . __('Your Settings Have Been Saved.') . '</p></div>';

	echo '<form action="options.php" name="cfbe_form_1" id="cfbe_form_1" method="post">';
	echo '<input type="hidden" name="cfbe_settings_save" value="1" />'."\n";
	wp_nonce_field('cfbe-settings-save');

	?>
	<br />

	<table class="widefat cfbe_table">
		<thead>
			<tr>
				<th><img src="data:image/png;base64,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" alt="" /><?php _e('Enable on Post Types'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$post_types = get_post_types();
			$skip_array = array("revision", "attachment", "nav_menu_item", "acf");
			foreach ($post_types as $post_type ) :
				if (in_array($post_type, $skip_array)) continue;
				$obj = get_post_type_object($post_type);
			?>
			<tr>
				<td>
					<input type="checkbox" name="cfbe_post_type_<?php echo $post_type; ?>" id="cfbe_post_type_<?php echo $post_type; ?>"<?php if (in_array($post_type, $cfbe_post_types)) echo ' checked="checked"'; ?> />
					<label for="cfbe_post_type_<?php echo $post_type; ?>"><?php echo $obj->labels->name; ?></label>
					<div style="clear: both;"></div>

				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p><input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" /></p>
	</form>

	<div style="clear: both;"></div>
	<?php
}

//Save Settings
add_action('admin_init', 'cfbe_save_settings');
function cfbe_save_settings() {

	//Bail if not called or authenticated
	$actionkey = (isset($_POST['cfbe_settings_save']) ? $_POST['cfbe_settings_save'] : "");
	if ($actionkey != "1" || !check_admin_referer('cfbe-settings-save')) return;

	//Save Settings
	$cfbe_post_types = array();
	$post_types = get_post_types();
	$skip_array = array("revision", "attachment", "nav_menu_item");
	foreach ($post_types as $post_type ) {
		if (isset($_POST['cfbe_post_type_'.$post_type])) $cfbe_post_types[] = $post_type;
	}
	update_option("cfbe_post_types", $cfbe_post_types);

	wp_redirect(admin_url("options-general.php?page=cfbe_settings&saved=1"));
	exit;
}




//Saving Functions
function cfbe_save_meta_data($fieldname,$input) {
	global $post_id;
	$current_data = get_post_meta($post_id, $fieldname, TRUE);
 	$new_data = $input;
 	if (!$new_data || $new_data == "") $new_data = NULL;
 	cfbe_meta_clean($new_data);

	if ($current_data && is_null($new_data)) {
		delete_post_meta($post_id,$fieldname);
	} elseif ($current_data && !isset($_POST['cfbe_add_new_values'])) {
		update_post_meta($post_id,$fieldname,$new_data);
	} elseif (!is_null($new_data)) {
		add_post_meta($post_id,$fieldname,$new_data);
	}
}

function cfbe_meta_clean(&$arr) {
	if (is_array($arr)) {
		foreach ($arr as $i => $v) {
			if (is_array($arr[$i]))  {
				cfbe_meta_clean($arr[$i]);
				if (!count($arr[$i])) unset($arr[$i]);
			} else  {
				if (trim($arr[$i]) == '') unset($arr[$i]);
			}
		}
		if (!count($arr)) $arr = NULL;
	}
}




//Display Settings Link on Plugin Screen
add_filter('plugin_action_links', 'cfbe_plugin_action_links', 10, 2);
function cfbe_plugin_action_links($links, $file) {
	static $this_plugin;
	if (!$this_plugin) $this_plugin = "custom-field-bulk-editor/custom-field-bulk-editor.php";
	if ($file == $this_plugin) {
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=cfbe_settings">Settings</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

//Create Settings for Post Type Selection
function cfbe_create_settings() {
	global $cfbe_post_types;
	$cfbe_post_types = array();
	$post_types = get_post_types();
	$skip_array = array("revision", "attachment", "nav_menu_item");
	foreach ($post_types as $post_type ) {
		if (in_array($post_type, $skip_array)) continue;
		$cfbe_post_types[] = $post_type;
	}
	update_option("cfbe_post_types", $cfbe_post_types);
}

add_action( 'wp_ajax_cfbe_lookup_meta_value', 'cfbe_lookup_meta_value_callback' );
function cfbe_lookup_meta_value_callback() {
	echo get_post_meta($_POST['post_id'], $_POST['field_name'], 1);
	die();
}

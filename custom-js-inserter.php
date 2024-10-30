<?php

/*
Plugin Name: Custom Javascript Inserter
Plugin URI: /plugins/custom-javascript-inserter
Description: To start using the plugin, go to edit any page or post - there you will see options for adding custom javascript code for that page/post.
Author: Ray Waheed
Version: 1.0
Author URI:
*/

add_action('admin_menu', 'custom_js_hooks');
add_action('save_post', 'save_custom_js');
add_action('wp_head','insert_custom_js');

function custom_js_hooks() {
	add_meta_box('custom_js_insert', 'Custom JS', 'custom_js_input', 'post', 'normal', 'high');
	add_meta_box('custom_js_insert', 'Custom JS', 'custom_js_input', 'page', 'normal', 'high');
}
function custom_js_input() {
	global $post;
	echo '<input type="hidden" name="custom_js_nonce" id="custom_js_nonce" value="'.wp_create_nonce('custom-js-insert').'" />';
	echo '<label>Include JS code from source (i.e. https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js), one per line:</label><br/>';
	$valid = 1;
	$src_lines = explode("\n", get_post_meta($post->ID,'custom_js_insert_src', true));
	foreach ($src_lines as $src)
	{
		if (esc_url($src) == '')
			$valid = 0;
	}
	if ($valid == 1)
		echo '<textarea name="custom_js_insert_src" id="custom_js_insert_src" rows= "5" cols="30" style="width:100%;">' . get_post_meta($post->ID,'custom_js_insert_src', true) . '</textarea>';
	else
		echo '<textarea name="custom_js_insert_src" id="custom_js_insert_src" rows= "5" cols="30" style="width:100%;"></textarea>';
	echo '<label>or insert raw JS code:</label>';
	echo '<textarea name="custom_js_insert" id="custom_js_insert" rows="5" cols="30" style="width:100%;">' . sanitize_text_field(get_post_meta($post->ID,'custom_js_insert',true)) . '</textarea>';
}
function save_custom_js($post_id) {
	if (!wp_verify_nonce($_POST['custom_js_nonce'], 'custom-js-insert')) return $post_id;
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	$valid = 1;
	$custom_js_insert = esc_js($_POST['custom_js_insert']);
	$custom_js_insert_src = $_POST['custom_js_insert_src'];
	$src_lines = explode("\n", $custom_js_insert_src);
	foreach ($src_lines as $src)
	{
		if (esc_url($src) == '')
			$valid = 0;
	}
	update_post_meta($post_id, 'custom_js_insert', $custom_js_insert);
	if ($valid == 1)
		update_post_meta($post_id, 'custom_js_insert_src', $custom_js_insert_src);
}
function insert_custom_js() {
	if (is_page() || is_single()) {
		if (have_posts()) : while (have_posts()) : the_post();
			
			if (get_post_meta(get_the_ID(), 'custom_js_insert_src', true) != '')
			{
				$src_lines = explode("\n",get_post_meta(get_the_ID(), 'custom_js_insert_src', true));
				
				foreach ($src_lines as $src)
				{
					echo '<script type="text/javascript" class="custom-js-src-inserter" src="' . $src . '"></script>';
				}
			}
			
			if (get_post_meta(get_the_ID(), 'custom_js_insert', true) != '')
				echo '<script type="text/javascript" class="custom-js-inserter">' . get_post_meta(get_the_ID(), 'custom_js_insert', true) . '</script>';
			
		endwhile; endif;
		rewind_posts();
	}
}

?>

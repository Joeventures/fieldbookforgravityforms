<?php
/*
Plugin Name: Fieldbook for Gravity Forms
Description: Send Gravity Form submissions to FieldBook.
Version: 0.1
Author: Joe Winter
Author URI: https://joe.ventures
License: GPL2
*/

require_once dirname(__FILE__) . '/phieldbook.php';

add_action( 'gform_loaded', array( 'FieldBookForGravityForms_Bootstrap', 'load' ), 5 );
class FieldBookForGravityForms_Bootstrap {
	public static function load() {
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}
		require_once dirname(__FILE__) . '/class-fieldbookforgravityforms.php';
		GFAddOn::register( 'FieldBookForGForms' );
	}
}
function gf_simple_feed_addon() {
	return FieldBookForGForms::get_instance();
}

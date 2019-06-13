<?php
/*
Plugin Name: JSON Feed for ClassicPress
Plugin URI: https://github.com/ginsterbusch/jsonfeed-cp/
Description: Adds a feed of recent posts in JSON Feed format. Forked from <a href="https://github.com/manton/jsonfeed-wp/">JSON Feed</a> 1.3 by Manton Reece and Daniel Jalkut. Partial rewrite.
Version: 2.0
Author: Fabian Wolf
Text Domain: jsonfeed-cp
License: GPL2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// keeping to the original "logic"
defined( 'ABSPATH' ) || die(); // silence is golden

// base plugin definitons
if( !defined( '_UI_JSONFEED_PLUGIN_PATH' ) ) {
	define( '_UI_JSONFEED_PLUGIN_PATH', plugin_dir_path( __FILE__ ) ); 
}

if( !defined( '_UI_JSONFEED_PLUGIN_URL' ) ) {
	define( '_UI_JSONFEED_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// development includes
//if( current_user_can( 'manage_options' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG != false ) ) {
	if( !class_exists( '__debug' ) ) {
		require_once( _UI_JSONFEED_PLUGIN_PATH . 'includes/debug.class.php' );
	}
//}


// includes
require_once( _UI_JSONFEED_PLUGIN_PATH . 'includes/base.php' );
require_once( _UI_JSONFEED_PLUGIN_PATH . 'includes/jsonfeed-cp.php' );
require_once( _UI_JSONFEED_PLUGIN_PATH . 'includes/functions.php' );

// plugin class
class _ui_JsonFeed_CP_Plugin extends _ui_JsonFeed_CP_Base {
	
	public static function setup_rewrite() {
		$jsonfeed = new _ui_JsonFeed_CP( false );
		
		$jsonfeed->json_feed_setup_feed();
		flush_rewrite_rules();
	}
}

// init installation / deinstallation hooks

register_activation_hook( __FILE__, array( '_ui_JsonFeed_CP_Plugin', 'setup_rewrite' ) );
add_action( 'shutdown', array( '_ui_JsonFeed_CP_Plugin', 'setup_rewrite' ) );

// init plugin itself
_ui_JsonFeed_CP::init();

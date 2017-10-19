<?php
/*
Plugin Name: Contact form supsystic
Description: Contact form plugin
Author: Shevera Andrii
*/


// If this file is called directly, abort.
if ( !defined( 'WPINC' ) )
	die();

define( 'CFS_SLUG', 'cfs' );
define( 'CFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CFS_PLUGIN_PATH', dirname( __FILE__ ) );


require_once( 'classes/class-main.php' );
require_once( 'classes/class-contact-form.php' );

// Create instance for main class
add_action( 'plugins_loaded', array( 'CFS_Main', 'get_instance' ) );

// Register activation hook
register_activation_hook( __FILE__, array( 'CFS_Main', 'activate' ) );

// Register deactivation hook
register_deactivation_hook( __FILE__, array( 'CFS_Main', 'deactivate' ) );

// Register uninstall hook
register_uninstall_hook( __FILE__ , array( 'CFS_Main', 'uninstall' ) );

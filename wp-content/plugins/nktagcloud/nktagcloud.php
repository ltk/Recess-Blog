<?php
/*
Plugin Name: Better Tag Cloud
Plugin URI: http://www.nkuttler.de/wordpress/nktagcloud/
Author: Nicolas Kuttler
Author URI: http://www.nkuttler.de/
Description: A Better Tag Cloud than the default one.
Version: 0.99.5
Text Domain: nktagcloud
*/

/**
 * Plugin (de)activation
 *
 * @since 0.8.0alpha-1
 */
function nktagcloud_load() {
	if ( is_admin() ) {
		require_once( 'inc/admin.php' );
		register_activation_hook( __FILE__, 'nktagcloud_install' );
	}
}
nktagcloud_load();

/**
 * Things to run during init hook
 *
 * @since 0.8.6
 */
function nktagcloud_init() {
	// http://codex.wordpress.org/Determining_Plugin_and_Content_Directories
	// Pre-2.6 compatibility
	if ( ! defined( 'WP_CONTENT_URL' ) )
	      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( ! defined( 'WP_CONTENT_DIR' ) )
	      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( ! defined( 'WP_PLUGIN_URL' ) )
	      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
	if ( ! defined( 'WP_PLUGIN_DIR' ) )
	      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
	global $nktagcloud;
	$nktagcloud = array(
		'path' => WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ),
		'url' => WP_PLUGIN_URL . '/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ ) ),
	);

	// always needed for footer link
	// TODO which footer link? we don't need this in admin, or do we?
	require_once( 'inc/page.php' );

	if ( is_admin() ) {
		require_once( 'inc/admin.php' );
		add_action( 'admin_menu', 'nktagcloud_add_pages' );

		register_widget_control( __('Better Tag Cloud', 'nktagcloud' ), 'nktagcloud_control' );
		register_sidebar_widget( __('Better Tag Cloud', 'nktagcloud'), 'widget_nktagcloud' );
	}
	else {
		add_shortcode( 'nktagcloud', 'nktagcloud_shortcode' );
		add_shortcode( 'nktagcloud_single', 'nktagcloud_single_shortcode' );
		register_sidebar_widget( __('Better Tag Cloud', 'nktagcloud'), 'widget_nktagcloud' );
	}
}
add_action( 'init', 'nktagcloud_init' );

// load the new widget class if available
if ( class_exists( 'WP_Widget' ) )
	require_once( 'inc/multiwidget.php' );

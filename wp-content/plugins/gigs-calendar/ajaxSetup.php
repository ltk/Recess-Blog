<?php

// If it cannot locate the wp-config file, you may have to hard code the full path here.
if ( file_exists('../../../wp-load.php') ) {
	require_once('../../../wp-load.php');
} else {
	require_once('../../../wp-config.php');
}

$gcd = 'gigs-calendar'; // Domain for Internationalization
load_plugin_textdomain($gcd, 'wp-content/plugins/gigs-calendar/i18n');

require_once 'gigs-classes.php';
if ( !defined('WP_CONTENT_URL') ) define( 'WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') ) define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );


if ( !defined('GIGS_RSS') )
	header('Content-Type: text/html; charset=' . get_bloginfo('charset'));

if ( !defined('GIGS_PUBLIC') ) {
	if ( !is_user_logged_in() ) die("not logged in");
	if ( !wp_verify_nonce($_POST['nonce'], 'gigs-calendar') ) {
		if ( in_array($_POST['action'], array('delete')) ) {
			die('{"success":false,"error":"nonce","action":"' . $_POST['action'] . '"}');
		} else {
			die(__('Your authentication token seems to have timed out.  Try refreshing this page before continuing.', $gcd));
		}
	}
}

$folder = dtc_gigs::get_url();

		
$options = get_option('gigs-calendar');

$gcd = 'gigs-calendar'; // Domain for Internationalization
load_plugin_textdomain($gcd, 'wp-content/plugins/gigs-calendar/i18n');

?>
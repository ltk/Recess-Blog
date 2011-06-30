<?php

/**
 * Install hook. Check if the plugin uses the 0.8.0 data model.
 */
function nktagcloud_install() {
	// this is for old installs
	if ( !get_option('nktagcloud') ) {
		nktagcloud_migrate_to_0_8_0();
	}
}

/**
 * Return the default values
 *
 * @param none
 *
 * @return array Tag cloud configuration
 *
 * @since 0.99
 */
function nktagcloud_defaults() {
	$default = array(
		'version'	=> '0.99',
		'config'	=> array(
			'categories'			=> 'No',		// Include catsegories
			'decor'					=> 'notouch',	// CSS for tag links
			'colorize'				=> 'No',		// CSS for tag links
			'exclude'				=> '',
			'format'				=> 'list',
			'homelink'				=> 'Yes',		// Remove link to homepage
			'inject_count'			=> 'No',		// Add tag counter
			'inject_count_outside'	=> 'Yes',		// Do not link the counter
			'include'				=> '',
			'largest'				=> '22',
			'mincount'				=> 0,			// minimum count to show
			'number'				=> '0',			// Tag limit
			'order'					=> 'ASC',
			'orderby'				=> 'name',
			'rmcss'					=> 'No',		// Remove plugin CSS
			'replace'				=> 'No',		// blanks to &nbsp;
			'separator'				=> '',			// Separator symbol
			'hidelastseparator'		=> 'No',		// Separator symbol
			'smallest'				=> '8',
			'title'					=> 'Tags',
			'unit'					=> 'pt',
			'nofollow'				=> 'No',
			'hideemptywidgetheader'	=> 'No',
			'taxonomy'				=> 'post_tag',
		),
	);
	return $default;
}

/**
 * Reset the tag cloud to default values
 *
 * @since 0.8.0alpha-1
 */
function nktagcloud_reset() {
	$default = nktagcloud_defaults();
	update_option( 'nktagcloud', $default );
}

/**
 * Migrate from earlier messy option model to cleaner layout
 *
 * @since 0.8.0alpha-1
 */
function nktagcloud_migrate_to_0_8_0() {
	// It's save to reset since the new option doesn't exist pre 0.8.0
	nktagcloud_reset();
	$option = get_option( 'nktagcloud' );
	// All pre 0.8.0 settings
	$settings = array( 'categories', 'decor', 'exclude', 'format', 'homelink', 'include', 'inject_count', 'inject_count_outside', 'largest', 'number', 'order', 'orderby', 'replace', 'rmcss', 'smallest', 'title', 'unit' );
	foreach ($settings as $setting) {
		// Import and delete all old settings
		if ( get_option( "nktagcloud_$setting" ) ) {
			$option['config'][$setting] = get_option( "nktagcloud_$setting" );
		}
		delete_option( "nktagcloud_$setting" );
	}
	update_option( 'nktagcloud', $option );
}

/**
 * The widget controls
 *
 * TODO maybe add basic configuration here?
 */
function nktagcloud_control() {
	print '<strong>' . __( 'This widget is deprecated, please use the new one on the left.', 'nktagcloud' ) . '</strong><br />';
	printf( __( "There were too many options, so I had to move the configuration to a <a href=\"%s\" >settings page</a>." ), get_bloginfo( 'url' ) . '/wp-admin/options-general.php?page=nktagcloud' );
}


/**
 * Add menu configuration page to the menu
 */
function nktagcloud_add_pages() {
    $page = add_options_page( __( 'Better Tag Cloud', 'nktagcloud' ), __( 'Better Tag Cloud', 'nktagcloud' ), 10, 'nktagcloud', 'nktagcloud_options_page');
	add_action( 'admin_head-' . $page, 'nktagcloud_css_admin' );

	// Add icon
	add_filter( 'ozh_adminmenu_icon_nktagcloud', 'nktagcloud_icon' );
}

/**
 * Return admin menu icon
 *
 * @return string path to icon
 *
 * @since 0.8.5
 */
function nktagcloud_icon() {
	global $nktagcloud;
	return $nktagcloud['url'] . 'pic/tag_blue.png';
}

/**
 * Load admin CSS style
 *
 * @since 0.8.3
 *
 * @todo check if this is correct
 */
function nktagcloud_css_admin() {
	global $nktagcloud; ?>
	<link rel="stylesheet" href="<?php echo $nktagcloud['url'] . 'css/admin.css?v=0.2.1' ?>" type="text/css" media="all" /> <?php
}


/**
 * The configuration page
 *
 * TODO this is messy...
 */
function nktagcloud_options_page() {
	nktagcloud_load_translation_file();
	#echo '<pre>'; var_dump( $_POST ); echo '</pre>'; echo '<hr>';
	if ( current_user_can( 'manage_options' ) ) {
		if ( $_POST['config'] ) {
			#function_exists( 'check_admin_referer ') ? check_admin_referer( 'nktagcloud' ) : null;
			$nonce = $_POST['_wpnonce'];
			if ( !wp_verify_nonce( $nonce, 'nktagcloud-config') ) die( 'Security check' );
			$option = get_option( 'nktagcloud' );
			$option['config'] = $_POST['config'];
			update_option( 'nktagcloud', $option );
			$config = $option['config'];
		}
		elseif ( $_POST['reset'] ) {
			function_exists( 'check_admin_referer ') ? check_admin_referer( 'nktagcloud' ) : null;
			$nonce = $_POST['_wpnonce'];
			if ( !wp_verify_nonce( $nonce, 'nktagcloud-reset') ) die( 'Security check' );
			nktagcloud_reset();
		}
		$option = get_option( 'nktagcloud' );
		$config = $option['config']; ?>

		<div id="nkuttler" class="wrap" >
			<h2><?php _e( 'Better Tag Cloud', 'nktagcloud' ) ?></h2>
			<?php require_once( 'nkuttler.php' ); ?>
			<?php nkuttler0_2_4_links( 'nktagcloud', 'http://www.nkuttler.de/wordpress-plugin/a-better-tag-cloud-widget/' ) ?>
			<p>
				<?php _e( 'The default tag cloud widget that comes with wordpress is very simple. This plugin makes the options for wp_tag_cloud() available to this new tag cloud widget and a shortcode. It has new features as well.', 'nktagcloud' ) ?>
			</p>
			<p>
				<?php _e( '<strong>New</strong> It is now possible to insert a tag cloud into any post or page. Just put the <tt>[nktagcloud]</tt> shortcode into any post or page.', 'nktagcloud' ) ?>
			</p>

			<h2><?php _e( 'Tag cloud Configuration', 'nktagcloud' ) ?></h2>
			<p>
				<?php _e( 'Please see <a href="http://codex.wordpress.org/Template_Tags/wp_tag_cloud" target="_blank">the wp_tag_cloud() page</a> for a detailed description of the options.', 'nktagcloud' ) ?>
			</p>
			<form action="" method="post">
				<?php function_exists( 'wp_nonce_field' ) ? wp_nonce_field( 'nktagcloud-config' ) : null; ?>

				<h2><?php _e( 'Deprecated settings, please use the new widget', 'nktagcloud' ) ?></h2>
				<h3><?php _e( 'CSS settings', 'nktagcloud' ) ?></h3>
				<p>
					<?php _e( "By default this plugin includes CSS to make the list look like a normal cloud.  The CSS settings may or may not work on your template, depending on it's CSS.", 'nktagcloud' ) ?>
				</p>
				<?php nktagcloud_select( __( 'Remove CSS for list format that displays the tags inline?', 'nktagcloud' ), 'rmcss', array('No', 'Yes'), false ) ?>
				<?php nktagcloud_select( __( 'Tag link decoration', 'nktagcloud' ), 'decor', array('underline', 'none', 'don\'t touch' ), false ) ?>
				<br />
				<?php nktagcloud_select( __( 'Use automatic colors for the tag cloud?', 'nktagcloud' ), 'colorize', array( 'No', 'Yes' ), false ); ?>
				<br />
				<?php nktagcloud_select( __( 'Hide the widget header markup if the widget title is empty?', 'nktagcloud' ), 'hideemptywidgetheader', array('No', 'Yes'), false ); ?>
				<br />
				<?php nktagcloud_select( __( 'Remove link to plugin website?', 'nktagcloud' ), 'homelink', array('No', 'Yes'), false ); ?>
				<br />
				<?php nktagcloud_submit( __( 'Save changes', 'nktagcloud' ), 'button-primary', false ) ?>


				<?php nktagcloud_input( __( 'Title', 'nktagcloud' ), 'title', 15, $config['title'] ) ?>
				<br />
				<?php nktagcloud_input( __( 'Taxonomy', 'nktagcloud' ), 'taxonomy', 15, $config['taxonomy'] ) ?>
				<br />
				<?php nktagcloud_input( __( 'Smallest font size', 'nktagcloud' ), 'smallest', 4, $config['smallest'] ) ?>
				<?php nktagcloud_input( __( 'Largest font size', 'nktagcloud' ), 'largest', 4, $config['largest'] ) ?>
				<?php nktagcloud_select( __( 'Unit', 'nktagcloud' ), 'unit', array('pt', 'px', '%', 'em', 'ex', 'mm') ); ?>
				<br />
				<?php nktagcloud_input( __( 'Numbers of tags to show', 'nktagcloud' ), 'number', 4, $config['number'] ) ?>
				<br />
				<?php nktagcloud_select( __( 'Format', 'nktagcloud' ), 'format', array('flat', 'list') ); ?>
				<br />
				<?php nktagcloud_select( __( 'Order', 'nktagcloud' ), 'order', array('ASC', 'DESC', 'RAND' ) ); ?>
				<?php nktagcloud_select( __( 'Orderby', 'nktagcloud' ), 'orderby', array('name', 'count', 'both' ) ); ?>
				<p>
					<?php _e( "The 'both' option of <tt>Orderby</tt> will sort by post count first and then by name. It doesn't exist in the default tag cloud and will ignore the <tt>Order</tt> option.", 'nktagcloud' ) ?>
				</p>

				<?php nktagcloud_select( __( 'Add post count to tags?', 'nktagcloud' ), 'inject_count', array('No', 'Yes') ) ?>
				<?php nktagcloud_select( __( 'Put the post count outside of the hyperlink?', 'nktagcloud' ), 'inject_count_outside', array('No', 'Yes') ) ?>
				<br />
				<?php nktagcloud_input( __( 'Show only tags that have been used at least so many times:', 'nktagcloud' ), 'mincount', 4, $config['mincount'] ) ?>
				<br />
				<?php nktagcloud_select( __( 'Add categories to tag cloud?', 'nktagcloud' ), 'categories', array('No', 'Yes') ) ?>
				<br />
				<?php nktagcloud_select( __( 'Force tags with multiple words on one line?', 'nktagcloud' ), 'replace', array('No', 'Yes') ) ?>
				<br />
				<?php nktagcloud_input( __( 'Tag separator', 'nktagcloud' ), 'separator', 4, $config['separator'] ) ?>
				<?php nktagcloud_select( __( 'Hide the last separator?', 'nktagcloud' ), 'hidelastseparator', array('No', 'Yes') ) ?>
				<br />
				<?php nktagcloud_select( __( 'Add the nofollow attribute?', 'nktagcloud' ), 'nofollow', array('No', 'Yes') ) ?>
				<br />
				<?php nktagcloud_submit( __( 'Save changes', 'nktagcloud' ) ) ?>

				<h3><?php _e( 'Exclude/Include tags', 'nktagcloud' ) ?></h3>
				<p>
					<?php _e( 'Comma separated list of tags (term_id) to exclude or include. For example, <tt>exclude=5,27</tt> means that tags that have the <tt>term_id</tt> 5 or 27 will NOT be displayed. See <a href="http://codex.wordpress.org/Template_Tags/wp_tag_cloud">Template Tags/wp tag cloud</a>.', 'nktagcloud' ) ?>
				</p>
				<?php nktagcloud_input( __( 'Exclude Tags', 'nktagcloud' ), 'exclude', 40, $config['exclude'] ) ?>
				<br />
				<?php nktagcloud_input( __( 'Include Tags', 'nktagcloud' ), 'include', 40, $config['include'] ) ?>
				<br />
				<?php nktagcloud_submit( __( 'Save changes', 'nktagcloud' ) ) ?>
			</form>

			<h3><?php _e( 'Reset the tag cloud', 'nktagcloud' ) ?></h3>
			<p>
				<?php _e( 'Use this if you want to delete your configuration and restore the defaults.', 'nktagcloud' ) ?>
			</p>
			<form action="" method="post" >
				<?php function_exists( 'wp_nonce_field' ) ? wp_nonce_field( 'nktagcloud-reset' ) : null; ?>
				<input type="hidden" name="reset" value="ihatephp" />
				<?php nktagcloud_submit( __( 'Reset the tag cloud', 'nktagcloud' ), 'button-secondary' ) ?>
			</form>
		</div> <?php
	}
}

/**
 * Prints a <select> and the listed <option>s
 *
 * @param string $title Title for the dropdown
 * @param string $name Form input name
 * @param array $choices List of options
 * @param boolean $br print <br /> after the select
 */
function nktagcloud_select( $title, $name, $choices, $disable = false ) {
	print $title; ?>
	<select <?php if ( $disable ) echo ' disabled="disabled" ' ?> name="config[<?php echo $name; ?>]"><?php
		// FIXME $option or selected should be passed as a parameter
		$option = get_option( 'nktagcloud' );
		$select = $option['config'][$name];
		foreach ( $choices as $choice ) {
			if ( $choice == $select ) {
				echo "<option value=\"$choice\" selected>" . __( $choice, 'nktagcloud' ) . "</option>\n";
			}
			else {
				echo "<option value=\"$choice\" >" . __( $choice, 'nktagcloud' ) . "</option>\n";
			}
		} ?>
	</select> <?php
	if ($br) {
		echo '<br />';
	} ?>
	<?php
}

/**
 * Print an input field
 *
 * @param string $title Title
 * @param string $name Input field name
 * @param integer $size Size
 * @param string $value Value
 * @param boolean $br Append <br />
 */
function nktagcloud_input( $title, $name, $size, $value, $disable = false ) { ?>
	<?php echo $title ?>
	<input name="config[<?php echo $name ?>]" type="text" size="<?php echo $size ?>" value="<?php echo $value ?>" <?php if ( $disable ) echo ' disabled="disabled" ' ?> /> <?php
	if ($br) echo '<br />';
}

/**
 * Print a submit button
 *
 * @param string $value Value
 * @param string $class CSS class for the submit button
 */
function nktagcloud_submit( $value, $class = 'button-primary', $disable = false ) { ?>
	<input type="submit" class="<?php echo $class ?>" value="<?php echo $value ?>" <?php if ( $disable ) echo ' disabled="disabled" ' ?> /> <?php
}

/**
 * Load Translations
 *
 * @since 0.8.1
 */
function nktagcloud_load_translation_file() {
	$plugin_path = plugin_basename( dirname( __FILE__ ) .'/../translations' );
	load_plugin_textdomain( 'nktagcloud', '', $plugin_path );
}


/************************************/
/*
nktagcloud_write_gradient_css( $biggest, $smallest, $start, $end );

$start	= 'ff00a9';
$end	= '00ffc0';
$smallest	= 10;
$biggest	= 50;
*/

/**
 */
function nktagcloud_gradient_css( $biggest, $smallest, $start, $end ) {
	for ( $i = 0; $i <= $biggest - $smallest; $i++ ) {
		$color = nktagcloud_color( $biggest - $smallest, $i, $start, $end );
		$r .= ".nktagcloud-" . ( $smallest + $i ) ." { color: #$color } ";
	}
	return $r;
}

/**
 * Calculate color value for tag size
 *
 * @param integer $steps number of different tag sizes
 * @param integer $step which step
 * @param array $start colors
 * @param array $end colors
 */
function nktagcloud_color( $steps, $step, $start, $end ) {
	$rgb_start = nktagcloud_rgbsplit( $start );
	$rgb_end = nktagcloud_rgbsplit( $end );

	$red   = nktagcloud_colorvalue( $steps, $step, $rgb_start[0], $rgb_end[0] );
	$green = nktagcloud_colorvalue( $steps, $step, $rgb_start[1], $rgb_end[1] );
	$blue  = nktagcloud_colorvalue( $steps, $step, $rgb_start[2], $rgb_end[2] );

	$color = $red . $green . $blue;
	return $color;
}

/**
 * Return the hex color for a step
 *
 * @param integer $steps number of different tag sizes
 * @param integer $step which step
 * @param integer $rgb_start start color
 * @param integer $rgb_end end color
 */
function nktagcloud_colorvalue( $steps, $step, $rgb_start, $rgb_end ) {
	$increment = ( $rgb_end - $rgb_start ) / $steps;
	return sprintf( "%02x",
		$rgb_start + $increment * $step
	);
}

/**
 * Split hex rgb value into an array of integers
 *
 * @param string $color hex rgb string
 *
 * @return array rgb values as integers
 */
function nktagcloud_rgbsplit( $color ) {
	$rgb = array(
		$color[0] . $color[1],
		$color[2] . $color[3],
		$color[4] . $color[5],
	);
	#print_r( $rgb );
	$rgb[0] = hexdec( $rgb[0] );
	$rgb[1] = hexdec( $rgb[1] );
	$rgb[2] = hexdec( $rgb[2] );
	#print_r( $rgb );
	return $rgb;
}

/**
 *
 */
function nktagcloud_write_gradient_css( $biggest, $smallest, $start, $end ) {
	$file = fopen( 'gradient.css', 'w' );
	fwrite( $file, nktagcloud_gradient_css( $biggest, $smallest, $start, $end ) );
	fclose( $file );
}

?>

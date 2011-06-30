<?php

/**
 * TODO do we really need to do this all the time?
 */
$option = get_option( 'nktagcloud' );
$config = $option['config'];
if ( $config['homelink'] === 'No') {
	add_action('wp_footer', 'nktagcloud_footer');
}
add_action('wp_head', 'nktagcloud_head');

/**
 * The widget
 *
 * @param $args TODO
 */
function widget_nktagcloud($args) {
	extract($args);
	echo $before_widget;
	$option = get_option('nktagcloud');
	$config = $option['config'];
	$title = apply_filters( 'the_title', $config['title'] );
	// @fixme this is wrong. We should alway print $before_title etc but
	// remove the <h2> tag if it exists
	if ( ( $config['hideemptywidgetheader'] == 'Yes' && isset( $title ) && $title != '' ) || $config['hideemptywidgetheader'] != 'Yes') {
		echo $before_title;
		echo $title;
		echo $after_title;
	}
	echo nktagcloud_the_cloud($config);
	echo $after_widget;
}

/**
 * Output the tag cloud as configured
 *
 * @param array $option the plugin config
 *
 * @return string the tag cloud
 *
 * @since 0.8.1
 */
function nktagcloud_the_cloud( $config ) {
	$smallest				= $config['smallest'];
	$largest				= $config['largest'];
	$unit					= $config['unit'];
	$number					= $config['number'];
	$format					= $config['format'];
	$order					= $config['order'];
	$orderby				= $config['orderby'];
	$exclude				= $config['exclude'];
	$include				= $config['include'];
	$categories				= $config['categories'];
	$replace				= $config['replace'];
	/*
	 * The 'single' and 'post_id' settings are not configurable through the
	 * options page and only intended for the shortcodes.
	 */
	$single					= 'No';
	$post_id				= null;
	$mincount				= $config['mincount'];
	$separator				= $config['separator'];
	$hidelastseparator		= $config['hidelastseparator'];
	$inject_count			= $config['inject_count'];
	$inject_count_outside	= $config['inject_count_outside'];
	$taxonomy				= $config['taxonomy'];
	if ( function_exists( taxonomy_exists ) ) {
		if ( !taxonomy_exists( $taxonomy ) )
			$taxonomy		= 'post_tag';
	}
	else
		$taxonomy			= 'post_tag'; // force tags for < 3.0

    return nk_wp_tag_cloud("largest=$largest&smallest=$smallest&unit=$unit&number=$number&format=$format&order=$order&orderby=$orderby&exclude=$exclude&include=$include&categories=$categories&single=$single&replace=$replace&separator=$separator&hidelastseparator=$hidelastseparator&inject_count=$inject_count&inject_count_outside=$inject_count_outside&post_id=$post_id&mincount=$mincount&nofollow=$nofollow&taxonomy=$taxonomy");
}

/**
 * Print the default CSS styles in the <head>
 *
 * @param string $underline TODO
 * @todo fix above
 * @todo external file
 */
function nktagcloud_head( $underline ) {
	$option = get_option( 'nktagcloud' );
	$config = $option['config'];

	if ( !( $config['rmcss'] === 'Yes' ) && ( $config['format'] === 'list' )) { ?>
		<style type="text/css">
		.better-tag-cloud-shortcode li,
		.better-tag-cloud-shortcode li a,
		li#better-tag-cloud ul.wp-tag-cloud li,
		li#better-tag-cloud ul.wp-tag-cloud li a {
			display:	inline;
			<?php
				if ($underline == 'none') {
					echo "text-decoration: none;";
				}
				elseif ($underline == 'underline') {
					echo "text-decoration: underline;";
				} ?>
		}
		</style> <?php
	}
	if ( $option['config']['colorize'] == 'Yes' ) {
		global $nktagcloud; ?>
		<link rel="stylesheet" href="<?php echo $nktagcloud['url'] . 'css/page.css?v=0.9.0' ?>" type="text/css" media="all" /> <?php
	}
}

/**
 * Print the link to the plugin page
*/
# TODO rename function?
function nktagcloud_footer() {
	_e('<a href="http://www.nkuttler.de/wordpress-plugin/a-better-tag-cloud-widget/">Better Tag Cloud</a>', 'nktagcloud' );
	echo '<br />';
}

/* This was copied from the wordpress source and modified, see
 * wp-includes/category-template.php
*/

/**
 * Display tag cloud.
 *
 * The text size is set by the 'smallest' and 'largest' arguments, which will
 * use the 'unit' argument value for the CSS text size unit. The 'format'
 * argument can be 'flat' (default), 'list', or 'array'. The flat value for the
 * 'format' argument will separate tags with spaces. The list value for the
 * 'format' argument will format the tags in a UL HTML list. The array value for
 * the 'format' argument will return in PHP array type format.
 *
 * The 'orderby' argument will accept 'name' or 'count' and defaults to 'name'.
 * The 'order' is the direction to sort, defaults to 'ASC' and can be 'DESC'.
 *
 * The 'number' argument is how many tags to return. By default, the limit will
 * be to return the top 45 tags in the tag cloud list.
 *
 * The 'topic_count_text_callback' argument is a function, which, given the count
 * of the posts  with that tag, returns a text for the tooltip of the tag link.
 *
 * The 'exclude' and 'include' arguments are used for the {@link get_tags()}
 * function. Only one should be used, because only one will be used and the
 * other ignored, if they are both set.
 *
 * The 'single' parameters is used to display only a single post's tags inside
 * the loop.
 *
 * The 'categories' parameter will add categories to the tag cloud if set to 'Yes'
 *
 * The 'replace' paramter, if set to 'Yes', will change all blanks ' ' to &nbsp;
 *
 * The 'mincount' parameter will hide all tags that aren't used more often than
 * mincount.
 *
 * The 'separator' will be printed between tags.
 *
 * 'inject_count' will add a counter to the tags is set to 'Yes'.
 * 'inject_count_outside' will put this counter outside the tag link.
 *
 * @since 2.3.0
 *
 * @param array|string $args Optional. Override default arguments.
 * @return array Generated tag cloud, only if no failures and 'array' is set for the 'format' argument.
 */
function nk_wp_tag_cloud( $args = '' ) {
    $defaults = array(
        'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 45,
        'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
        'exclude' => '', 'include' => '', 'link' => 'view',
		'single' => 'No',
		'categories' => 'No',
		'replace' => 'No',
		'mincount' => '0',
		'separator' => '',
		'hidelastseparator' => 'No',
		'inject_count' => 'No',
		'inject_count_outside' => 'Yes',
		'post_id' => null,
		'nofollow' => 'No',
		'taxonomy' => 'post_tag',
    );
    $args = wp_parse_args( $args, $defaults );

	if ( intval( $args['post_id'] ) != 0 ) {
		$my_query = new WP_Query( "cat=$category" );
		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) {
				$my_query->the_post();
				$tags = apply_filters( 'get_the_tags', get_the_terms( null, 'post_tag' ) );
			}
		}
		$GLOBALS['post'] =  $GLOBALS['wp_query']->post; // restore $post
	}
	elseif ( $args['single'] == 'Yes' || $args['single'] == 'yes' ) {
		$tags = apply_filters( 'get_the_tags', get_the_terms( null, $args['taxonomy'] ) );
	}
	else {
		$tags = get_terms( $args['taxonomy'], array_merge( $args, array( 'orderby' => 'count', 'order' => 'DESC' ) ) );
	}

	// nkuttler
	if ( $args['categories'] == 'Yes' ) {
		$tags = array_merge ( $tags, get_categories() );
	}

    if ( empty( $tags ) )
        return;

    foreach ( $tags as $key => $tag ) {
        if ( 'edit' == $args['link'] ) {
            $link = get_edit_tag_link( $tag->term_id );
		}
		/* use category or tag link, whatever is needed */
		/* TODO check for duplicate cat/tag names.. */
        else {
			if ( isset( $tag->cat_ID ) ) {
				$link = get_category_link( $tag->term_id );
			}
			else {
				$link = get_term_link( $tag, $args['taxonomy'] );
			}
		}
        if ( is_wp_error( $link ) ) {
            return false;
		}
        $tags[ $key ]->link = $link;
        $tags[ $key ]->id = $tag->term_id;
    }

    $return = nk_wp_generate_tag_cloud( $tags, $args ); // Here's where those top tags get sorted according to $args

    $return = apply_filters( 'wp_tag_cloud', $return, $args );

    if ( 'array' == $args['format'] )
        return $return;

	// nkuttler - always return
	return $return;
    //echo $return;
}

/**
 * Generates a tag cloud (heatmap) from provided data.
 *
 * The text size is set by the 'smallest' and 'largest' arguments, which will
 * use the 'unit' argument value for the CSS text size unit. The 'format'
 * argument can be 'flat' (default), 'list', or 'array'. The flat value for the
 * 'format' argument will separate tags with spaces. The list value for the
 * 'format' argument will format the tags in a UL HTML list. The array value for
 * the 'format' argument will return in PHP array type format.
 *
 * The 'orderby' argument will accept 'name' or 'count' and defaults to 'name'.
 * The 'order' is the direction to sort, defaults to 'ASC' and can be 'DESC' or
 * 'RAND'.
 *
 * The 'number' argument is how many tags to return. By default, the limit will
 * be to return the entire tag cloud list.
 *
 * The 'topic_count_text_callback' argument is a function, which given the count
 * of the posts  with that tag returns a text for the tooltip of the tag link.
 *
 * See above ( nk_wp_tag_cloud() ) for more parameter definitions.
 *
 * @todo Complete functionality.
 * @since 2.3.0
 *
 * @param array $tags List of tags.
 * @param string|array $args Optional, override default arguments.
 * @return string
 */
function nk_wp_generate_tag_cloud( $tags, $args = '' ) {
	global $wp_rewrite;
	$defaults = array(
		'smallest' => 8, 'largest' => 22, 'unit' => 'pt', 'number' => 0,
		'format' => 'flat', 'orderby' => 'name', 'order' => 'ASC',
		'topic_count_text_callback' => 'default_topic_count_text',
		'replace' => 'No',
		'mincount' => '0',
		'separator' => '',
		'inject_count' => 'No',
		'inject_count_outside' => 'Yes',
		'nofollow' => 'No',
	);

	if ( !isset( $args['topic_count_text_callback'] ) && isset( $args['single_text'] ) && isset( $args['multiple_text'] ) ) {
		$body = 'return sprintf (
			__ngettext('.var_export($args['single_text'], true).', '.var_export($args['multiple_text'], true).', $count),
			number_format_i18n( $count ));';
		$args['topic_count_text_callback'] = create_function('$count', $body);
	}

	$args = wp_parse_args( $args, $defaults );

	extract( $args );

	if ( empty( $tags ) )
		return;

	// SQL cannot save you; this is a second (potentially different) sort on a subset of data.
	if ( 'name' == $orderby )
		uasort( $tags, create_function('$a, $b', 'return strnatcasecmp($a->name, $b->name);') );
	else
		uasort( $tags, create_function('$a, $b', 'return ($a->count > $b->count);') );

	if ( 'DESC' == $order )
		$tags = array_reverse( $tags, true );
	elseif ( 'RAND' == $order ) {
		$keys = array_rand( $tags, count( $tags ) );
		foreach ( $keys as $key )
			$temp[$key] = $tags[$key];
		$tags = $temp;
		unset( $temp );
	}

	/* nkuttler: support sorting by two criteria */
	if ( $order == 'both') {
		$orderby = 'both';
	}
	if ( 'both' == $orderby ) {
		function nktagcloud_compare_both( $a, $b ) {
			$retval = strnatcmp( $b->count, $a->count );
			if( !$retval ) return strnatcmp( $a->name, $b->name );
			return $retval;
		}
		usort( $tags, 'nktagcloud_compare_both' );
	}
	/* end changes */

	if ( $number > 0 )
		$tags = array_slice($tags, 0, $number);

	$counts = array();
	foreach ( (array) $tags as $key => $tag )
		$counts[ $key ] = $tag->count;

	$min_count = min( $counts );
	$spread = max( $counts ) - $min_count;
	if ( $spread <= 0 )
		$spread = 1;
	$font_spread = $largest - $smallest;
	if ( $font_spread < 0 )
		$font_spread = 1;
	$font_step = $font_spread / $spread;

	$a = array();

	$rel = ( is_object( $wp_rewrite ) && $wp_rewrite->using_permalinks() ) ? ' rel="tag"' : '';

	/**
	 * Modifications my nkuttler
	 */
	if ( isset( $separator ) && $separator != '' ) {
		$the_separator .= '<span class="nktagcloud-separator">';
		$the_separator .= $separator;
		$the_separator .= '</span>';
		/**
		 * @since 0.11.1 Loop to count the number of tags that will be printed
		 */
		$tags_to_show = 0;
		foreach ( $tags as $key => $tag ) {
			$count = $counts[ $key ];
			if ( $count > $mincount )
				$tags_to_show++;
		}
	}

	$tags_shown_so_far = 0;
	foreach ( $tags as $key => $tag ) {
		$count = $counts[ $key ];
		$tag_link = '#' != $tag->link ? clean_url( $tag->link ) : '#';
		$tag_id = isset($tags[ $key ]->id) ? $tags[ $key ]->id : $key;
		$tag_name = $tags[ $key ]->name;
		if ( ( $tags_shown_so_far == $tags_to_show - 1 ) && $hidelastseparator == 'Yes' )
			$the_separator = '';

		/**
		 * Modifications by nkuttler
		 * Check and print if we should print the tag counter, in- or outside
		 * the tag link
		 *
		 * @since 0.8.5 calculate size value earlier, add class="nktagcloud-size"
		 * @since 0.8.5 add option minimum occurence needed to display a tag
		 * @since 0.11.0 add nofollow option
		 */
		if ( $replace == 'Yes' ) {
			$tag_name = preg_replace( '# #', '&nbsp;', $tag_name );
			$tag_name = preg_replace( '#-#', '&#8209;', $tag_name );
		}

		if ( isset( $nofollow ) && $nofollow == 'Yes' )
			$echo_nofollow = ' rel="nofollow" ';

		$tagsize = round( $smallest + ( ( $count - $min_count ) * $font_step ), 2 );
		$tagsize_int = (int) $tagsize;

		if ( $count > $mincount ) {
			$tags_shown_so_far++;
			if ( $inject_count_outside == 'Yes' ) {
				$a[] = "<a href='$tag_link' $echo_nofollow class='tag-link-$tag_id nktagcloud-$tagsize_int' title='" . attribute_escape( $topic_count_text_callback( $count ) ) . "'$rel style='font-size: " .  $tagsize . "$unit;'>$tag_name</a>" . nktagcloud_add_counter( $tag, $inject_count ) . $the_separator;
			}
			else {
				$a[] = "<a href='$tag_link' $echo_nofollow class='tag-link-$tag_id nktagcloud-$tagsize_int' title='" . attribute_escape( $topic_count_text_callback( $count ) ) . "'$rel style='font-size: " .  $tagsize . "$unit;'>$tag_name" . nktagcloud_add_counter( $tag, $inject_count ) ."</a>" . $the_separator;
			}
		}
	}
	/* end changes */

	switch ( $format ) :
	case 'array' :
		$return =& $a;
		break;
	case 'list' :
		$return = "<ul class='wp-tag-cloud'>\n\t<li>";
		$return .= join( "</li>\n\t<li>", $a );
		$return .= "</li>\n</ul>\n";
		break;
	default :
		$return = join( "\n", $a );
		break;
	endswitch;

	return apply_filters( 'wp_generate_tag_cloud', $return, $tags, $args );
}

/*
 * Add a tag counter to the tags
 *
 * @param string $tag Tag
 * @param array $config Config
 */
function nktagcloud_add_counter( $tag, $inject_count ) {
	if ( $inject_count == 'Yes' ) {
		$inject  = '<span class="nktagcloud_counter">';
		$inject .= '(' . $tag->count . ')';
		$inject .= '</span>';
		return $inject;
	}
}

/**
 * Create a shortcode
 *
 * @since 0.8.1
 */
function nktagcloud_shortcode( $atts ) {
	$option = get_option( 'nktagcloud' );
	$config = $option['config'];

	/*
	 * Take plugin config as defaults
	 */
	extract( shortcode_atts( array(
        'smallest' =>				$config['smallest'],
		'largest' =>				$config['largest'],
		'unit' =>					$config['unit'],
		'number' =>					$config['number'],
		'format' =>					$config['format'],
		'orderby' =>				$config['orderby'],
		'order' =>					$config['order'],
		'exclude' =>				$config['exclude'],
		'include' =>				$config['include'],
		'link' =>					$config['link'],
		'single' =>					'No',
		'categories' =>				$config['categories'],
		'replace' =>				$config['replace'],
		'mincount' =>				$config['mincount'],
		'separator' =>				$config['separator'],
		'inject_count' =>			$config['inject_count'],
		'inject_count_outside' =>	$config['inject_count_outside'],
		'post_id' =>				null,
	), $atts) );

	$cloud = nk_wp_tag_cloud("largest=$largest&smallest=$smallest&unit=$unit&number=$number&format=$format&order=$order&orderby=$orderby&exclude=$exclude&include=$include&categories=$categories&single=$single&replace=$replace&separator=$separator&inject_count=$inject_count&inject_count_outside=$inject_count_outside&post_id=$post_id");

	$before = '<div class="better-tag-cloud-shortcode" >';
	$after = '</div>';
	return $before . $cloud . $after;
}

?>

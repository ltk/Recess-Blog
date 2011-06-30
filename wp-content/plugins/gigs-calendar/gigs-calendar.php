<?php
/**
 * Plugin Name: Gigs Calendar
 * Plugin URI: http://blogsforbands.com
 * Description: A calendar to let musicians and other performing artists share their performance dates with their adoring public.
 * Author: Dan Coulter
 * Version: 0.4.9
 * Author URI: http://dancoulter.com
 */

/** 
 * Copyright 2008  Dan Coulter (dan@blogsforbands.com)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You can get a copy of this license here: http://www.gnu.org/licenses/
 * And a human readable copy here: http://creativecommons.org/licenses/GPL/2.0/
 */

$include_folder = dirname(__FILE__);
require_once $include_folder . '/version.php';
require_once $include_folder . '/gigs-classes.php';
$gcd = 'gigs-calendar'; // Domain for Internationalization

if ( defined('ABSPATH') ) :
	class dtc_gigs {
		var $cache = array();
	
		function add_admin_page() {
			global $gcd, $wp_version;
			$options = get_option('gigs-calendar');
			$admin_location = 'add_object_page';
			if ( $wp_version < 2.7 ) $admin_location = 'add_management_page';
			else $admin_location = 'add_object_page';
			$admin_location(
				__('Gigs Calendar', $gcd),
				__('Gigs Calendar', $gcd),
				(isset($options['user_level']) ? $options['user_level'] : 'level_7'), 
				'gigs', 
				array("dtc_gigs", "generate_admin_page")
			);
		}
		
		function admin_css() {
			global $wp_version;
			$options = get_option('gigs-calendar');
			$folder = dtc_gigs::get_url();
			?>
				<link type="text/css" rel="stylesheet" href="<?php echo $folder; ?>js/jquery.tooltip.css" />
				<link type="text/css" rel="stylesheet" href="<?php echo $folder; ?>js/ui.datepicker.css" />
				<link type="text/css" rel="stylesheet" href="<?php echo $folder; ?>gigs-calendar-admin.css" />
			<?php
			if ( (float) $wp_version >= 2.5 ) {
				?><link type="text/css" rel="stylesheet" href="<?php echo $folder; ?>gigs-calendar-admin-wp2.5.css" /><?php
			}
		}
		
		function display_css() {
			$options = get_option('gigs-calendar');
			$folder = dtc_gigs::get_url() . 'templates/';
			
			if ( is_file(dirname(__FILE__) . '/templates/' . $options['template'] . '/style.css') ) {
				?>
					<link type="text/css" rel="stylesheet" href="<?php echo $folder . $options['template']; ?>/style.css" />
				<?php
			} elseif ( is_file(ABSPATH . 'wp-content/gigs-templates/' . $options['template'] . '/style.css') ) {
				?>
					<link type="text/css" rel="stylesheet" href="<?php echo get_bloginfo('wpurl') . '/wp-content/gigs-templates/' . $options['template']; ?>/style.css" />
				<?php
			} else {
				?>
					<link type="text/css" rel="stylesheet" href="<?php echo $folder; ?>basic/style.css" />
				<?php
			}
		}

		function generate_admin_page() {
			global $gcd;
			$options = get_option('gigs-calendar');
			if ( $options !== false && !current_user_can($options['user_level']) ) {
				//exit; // Stupid non-fix.
			}
			get_currentuserinfo();
			global $userdata;
			
			if ( isset($_GET['ladyhawke']) ) {
				update_option("gig_db_version", $_GET['ladyhawke']);
				dtc_gigs::upgrade();
			}
			
			$folder = dtc_gigs::get_url();
			$pages = array(
				'gigs' => __('Gigs', $gcd),
				'archive' => __('Archive', $gcd),
				'venues' => __('Venues', $gcd),
				'tours' => __('Tours', $gcd),
				'settings' => __('Settings', $gcd),
				'feedback' => __('Feedback/Bugs', $gcd),
				'credits' => __('Credits', $gcd),
			);
			
			foreach ( $pages as $key => $name ) {
				$pages[$key] = array('name'=>$name, 'key'=>$key, 'url'=>$folder . $key . '.ajax.php');
			}
			
			if ( $options['admin-only-settings'] && !current_user_can('administrator') ) {
				unset($pages['settings']);
			}
			
			$pages = apply_filters('gigCal_pages', $pages); 
			?>
				<script type="text/javascript">
					var pages = {
						<?php
							$first = true;
							foreach ( $pages as $page ) {
								echo ( $first ? '' : ',' ) . $page['key'] . ' : {key:"' . $page['key'] . '", name:"' . $page['name'] . '", url:"' . $page['url'] . '"}';
								$first = false;
							}
						?>
					};
					var ajaxTarget = "<?php echo $folder; ?>";
					var nonce = "<?php echo wp_create_nonce('gigs-calendar'); ?>";
					var pageTarget = "";
				</script>
				<div class="wrap">
					<h2><?php _e('Gigs Calendar', $gcd) ?></h2>
					<ul id="gigs-menu">
						<?php if ( !$options || (empty($options['parent']) && $options['calendar-position'] != 'custom') ) : ?>
							<li id="settings-tab"><?php _e('Settings', $gcd) ?></li>
							<li id="feedback-tab"><?php _e('Feedback/Bugs', $gcd) ?></li>
							<li id="credits-tab"><?php _e('Credits', $gcd) ?></li>
						<?php else: ?>
							<?php foreach ( $pages as $page ) : ?>
								<li id="<?php echo $page['key']; ?>-tab"><?php echo $page['name']; ?></li>
							<?php endforeach; ?>
						<?php endif; ?>
					</ul>
					<div id="gigs-wrapper" class="wrap">
						<div id="loading">
							<?php _e('Loading...', $gcd) ?><br />
							<img src="<?php echo $folder; ?>images/ajax-loader.gif" alt="" />
						</div>
						<?php foreach ( $pages as $page ) : ?>
							<div id="<?php echo $page['key']; ?>" class="gigs-page"></div>
						<?php endforeach; ?>
					</div>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						gigs_page_load(jQuery("#gigs-menu li").eq(0).attr("id").split("-")[0]);
					});
					
					jQuery("#gigs-menu li").click(function(){
						gigs_page_load(this.id.split("-")[0]);
					});
					
					gigs_page_load = function(page, query) {
						if ( query == undefined ) query = "";
						else query = "?" + query;
						
						try{pageDestroy();}catch(e){};
						jQuery("#loading").show();
						jQuery(".gigs-page:visible").hide();
						jQuery("#gigs-menu li.selected").removeClass("selected");
						jQuery("#" + page + "-tab").addClass("selected");
						pageTarget = pages[page].url;
						jQuery("#" + page).load(pageTarget + query, {
							action:"load", 
							"page":page,
							"nonce":nonce
						}, function(rsp1,rsp2,rsp3){
							jQuery("#loading").hide();
							jQuery(".gigs-page:visible").hide();
							jQuery("#" + page).show();
						});
					}
					
					resetTableColors = function(table) {
						rows = jQuery(table).find("tbody tr");
						for ( i = 0; i < rows.length; i++ ) {
							if ( Math.floor(i/2) % 2 ) {
								rows.eq(i).removeClass("alternate");
							} else {
								rows.eq(i).addClass("alternate");
							}
						}
					}
					
				</script>
			<?php
		}
		
		function get_url() {
			return ( defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN ? str_replace("http://", "https://", get_bloginfo('wpurl')) : get_bloginfo('wpurl')) . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/';
		}
		
		function get_path() {
			return dirname(__FILE__);
		}
		
		function display($in){
			global $wpdb, $post, $include_folder, $gcd;
			$options = get_option("gigs-calendar");
			require_once 'gigs-classes.php';
			$out = '';
			$is_archive = false;
			$tpath = dirname(__FILE__) . '/templates/'. $options['template'];
			
			$is_parent = !empty($options['parent']) && is_page($options['parent']);
			$is_archive = !empty($options['archive']) && is_page($options['archive']);
			
			if ( ($is_parent || $is_archive) && $options['calendar-position'] != 'custom' ) {
				ob_start();
				if ( $is_parent ) {
					if ( $options['tours-display'] ) {
						$t = new tour();
						$tours = $t->sortTours();
						foreach ( $tours as $tour ) {
							dtc_gigs::generateList(array(
								'caption'=> $is_archive ? __('Upcoming shows', $gcd) : '',
								'dateFormat' => 'short',
								'tour' => $tour,
							));
						}
						dtc_gigs::generateList(array(
							'caption'=> '<div class="title">' . __('Other shows', $gcd) . '</div>',
							'dateFormat' => 'short',
							'tour' => -1,
						));
					} else {
						dtc_gigs::generateList(array(
							'caption'=> $is_archive ? __('Upcoming shows', $gcd) : '',
							'dateFormat' => 'short',
						));
					}
				}
				if ( !empty($options['archive']) && is_page($options['archive']) ) {
					if ( $options['tours-display'] ) {
						$t = new tour();
						$tours = $t->sortTours();
						foreach ( $tours as $tour ) {
							dtc_gigs::generateList(array(
								'upcoming'=>false, 
								'caption'=> $is_archive ? __('Upcoming shows', $gcd) : '',
								'dateFormat' => 'short',
								'tour' => $tour,
							));
						}
						dtc_gigs::generateList(array(
							'upcoming'=>false, 
							'caption'=> '<div class="title">' . __('Other shows', $gcd) . '</div>',
							'dateFormat' => 'short',
							'tour' => -1,
						));
					} else {
						dtc_gigs::generateList(array(
							'upcoming'=>false, 
							'caption'=> $is_parent ? __('Past show archive', $gcd) : '',
							'dateFormat' => 'archive',
						));
					}
				}
				
				dtcGigs::loadTemplate('rss');
				dtcGigs::loadTemplate('attribution');
				$out = ob_get_clean();
				if ( $options['calendar-position'] == 'bottom' ) {
					$out = '<div>' . $in . '</div>' . $out;
				} else {
					$out .= '<div>' . $in . '</div>';
				}
			} else {
				$g = new gig();
				if ( $g->getByPostID($post->ID) ) {
					$data = array(
						'g' => $g,
						'v' => $g->getVenue(),
						'p' => $g->getPerformances(),
						'image_folder' => dtc_gigs::get_url() . 'images/',
						'tags' => $g->getTags(),
						'tags_slugs' => $g->getTags(true),
						'custom' => $g->getCustom(),
					);
					ob_start();
					dtcGigs::loadTemplate('gig-post', null, $data);
					$out = ob_get_clean();
								
					$out = '<div>' . $in . '</div>' . $out;
				} else {
					$out = $in;
				}
			}
			if ( preg_match_all('~\[gig-cal ?(.*?)\]~', $out, $matches) ) {
				$matches[0] = array_unique($matches[0]);
				foreach ( $matches[0] as $key => $match ) {
					$args = array();
					parse_str(str_replace(' ', '&', $matches[1][$key]), $args);
					$args['upcoming'] = (strpos($matches[1][$key], 'archive') !== FALSE) ? false : true;
					$args['return'] = true;
					$out = str_replace($match, dtc_gigs::generateList($args), $out);
				}
			}
			return $out;
		}

		function upgrade() {
			global $wpdb;
			
			if ( isset($wpdb->charset) && !empty($wpdb->charset) ) {
				$charset = ' DEFAULT CHARSET=' . $wpdb->charset;
			} elseif ( defined(DB_CHARSET) && DB_CHARSET != '' ) {
				$charset = ' DEFAULT CHARSET=' . DB_CHARSET;
			} else {
				$charset = '';
			}
			
			$queries = array(
				array( // 1
					'
						CREATE TABLE IF NOT EXISTS `' . TABLE_VENUES . '` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
							`name` VARCHAR( 255 ) NOT NULL ,
							`address` TEXT NOT NULL ,
							`city` VARCHAR( 255 ) NOT NULL ,
							`state` VARCHAR( 255 ) NOT NULL ,
							`country` VARCHAR( 255 ) NOT NULL ,
							`postalCode` VARCHAR( 255 ) NOT NULL ,
							`contact` VARCHAR( 255 ) NOT NULL ,
							`phone` VARCHAR( 255 ) NOT NULL ,
							`email` VARCHAR( 255 ) NOT NULL ,
							`link` VARCHAR( 255 ) NOT NULL ,
							`notes` TEXT NOT NULL ,
							`private` TINYINT NOT NULL ,
							`apiID` INT UNSIGNED NOT NULL ,
							`deleted` TINYINT NOT NULL
						) ' . $charset . '
					', '
						CREATE TABLE IF NOT EXISTS `' . TABLE_GIGS . '` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
							`venueID` INT UNSIGNED NOT NULL ,
							`date` DATE NOT NULL ,
							`notes` TEXT NOT NULL,
							`postID` BIGINT UNSIGNED NOT NULL
						) ' . $charset . '
					', '
						CREATE TABLE IF NOT EXISTS `' . TABLE_PERFORMANCES . '` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
							`gigID` INT UNSIGNED NOT NULL ,
							`time` TIME NULL ,
							`link` VARCHAR( 255 ) NOT NULL ,
							`shortNotes` VARCHAR( 255 ) NOT NULL ,
							`ages` VARCHAR( 255 ) NOT NULL
						) ' . $charset . '
					'
				), array( // 2
					'ALTER TABLE `' . TABLE_VENUES . '` ADD `customMap` VARCHAR( 255 ) NOT NULL',
					'ALTER TABLE `' . TABLE_GIGS . '` ADD `eventName` VARCHAR( 255 ) NOT NULL',
				), array( // 3
					'
						CREATE TABLE `' . TABLE_TOURS . '` (
							`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
							`name` VARCHAR( 255 ) NOT NULL ,
							`notes` MEDIUMTEXT NOT NULL ,
							`pos` INT UNSIGNED NOT NULL
						)  ' . $charset . '
					', '
						ALTER TABLE `' . TABLE_GIGS . '` ADD `tour_id` INT UNSIGNED NULL
					',
				),
				
				
				
			);
			for ( $i = (int) get_option('gig_db_version'); $i < (int) DTC_GIGS_DB_VERSION; $i++) {
				if ( isset($queries[$i]) ) {
					foreach ( $queries[$i] as $q ) {
						$wpdb->query($q);
					}
				}
			}
			
			if ( count($wpdb->get_results('show tables like "' . GIGS_DB_PREFIX . 'gigs_%"')) != 4 ) {
				update_option("gig_db_version", -1);
			} else {
				update_option("gig_db_version", (int) DTC_GIGS_DB_VERSION);
			}
		}
		
		
		function hide_posts($where) {
			$o = get_option('gigs-calendar');
			if ( 0 < (int) get_option('gig_db_version') && $o['post-filtering'] ) {
				global $wp_the_query;
				global $wp_query;
				global $wp_actions;
				global $wpdb;
				if ( in_array('get_sidebar', $wp_actions) ) {
					$where .= ' AND ID NOT IN (SELECT postID FROM ' . TABLE_GIGS . ')';
				} elseif ( (is_single() || (function_exists('is_tag') && is_tag())) ) {
					return $where;
				} elseif ( (!is_category() || $wp_the_query->query_vars['cat'] != $o['category']) || defined('gigs-query-mod')) {
					$where .= ' AND ID NOT IN (SELECT postID FROM ' . TABLE_GIGS . ')';
				}
			}
			return $where;
		}
		
		function hide_archives($where) {
			$o = get_option('gigs-calendar');
			if ( $o['post-filtering'] ) {
				global $wpdb;
				return $where . ' AND ID NOT IN (SELECT postID FROM ' . TABLE_GIGS . ')';
			}
		}
		
		
		function post_nav($where) {
			$o = get_option('gigs-calendar');
			if ( 0 < (int) get_option('gig_db_version') && $o['post-filtering'] ) {
				global $wp_the_query, $include_folder, $wpdb, $wp_version;
				
				include_once 'gigs-classes.php';
				
				$wtq = $wp_the_query;
				$g = new gig();
				
				$table = ''; // This will let me support versions < 2.3
				if ( (float) $wp_version >= 2.3 ) {
					$table = 'p.';
				}
				
				if ( $wtq->is_single && $wpdb->get_var('SELECT COUNT(*) FROM ' . TABLE_GIGS . ' WHERE postID = ' . $wtq->post->ID) ) { // Is a gig
					$where .= ' AND ' . $table . 'ID IN (SELECT postID FROM ' . TABLE_GIGS . ')';
				} else {
					$where .= ' AND ' . $table . 'ID NOT IN (SELECT postID FROM ' . TABLE_GIGS . ')';
				}			
			}
			return $where;
		}
		
		function widget_upcoming($args) {
			extract($args);
			$o = get_option('gigs-calendar');
			if ( !isset($o['widget']['upcoming']) ) $o['widget']['upcoming'] = array();
			?>
				<?php echo $before_widget; ?>
				<?php echo $before_title . $o['widget']['upcoming']['title'] . $after_title; ?>
					<?php 
						dtc_gigs::generateList(array_merge(
							array(
								'upcoming'=>true, 
								'limit'=>$o['widget']['upcoming']['length'],
								'template'=>'upcoming-widget',
							),
							$o['widget']['upcoming']
						)); 
					?>
				<?php echo $after_widget; ?>
			<?php
		}
		
		function widget_upcoming_control() {
			global $gcd;
			$o = get_option('gigs-calendar');
			if ( !isset($o['widget']['upcoming']) ) {
				$o['widget']['upcoming'] = array();
				$o['widget']['upcoming']['title'] = __('Upcoming Gigs', $gcd);
				$o['widget']['upcoming']['length'] = 5;
				$o['widget']['upcoming']['dateFormat'] = 'M j';
				$o['widget']['upcoming']['dateFormatYear'] = 'M j, Y';
				$o['widget']['upcoming']['link'] = array();
				$o['widget']['upcoming']['link'][] = 'city';
				$o['widget']['upcoming']['link'][] = 'none';
			}
			
			if ( isset($_POST['gigs_length']) ) {
				$o['widget']['upcoming']['title'] = isset($_POST['gigs_title']) ? $_POST['gigs_title'] : $o['widget']['upcoming']['title'];
				$o['widget']['upcoming']['length'] = !empty($_POST['gigs_length']) ? $_POST['gigs_length'] : 5;
				$o['widget']['upcoming']['dateFormat'] = !empty($_POST['gigs_dateFormat']) ? $_POST['gigs_dateFormat'] : 'M j';
				$o['widget']['upcoming']['dateFormatYear'] = !empty($_POST['gigs_dateFormatYear']) ? $_POST['gigs_dateFormatYear'] : 'M j, Y';
				$o['widget']['upcoming']['link'] = array();
				$o['widget']['upcoming']['link'][] = $_POST['gig-firstLinkField'];
				$o['widget']['upcoming']['link'][] = $_POST['gig-secondLinkField'];
				update_option('gigs-calendar', $o);
			}
			
			$linkFields = array(
				'city' => 'City',
				'country' => 'Country',
				'venue' => 'Venue',
				'eventName' => 'Event Name',
			);
			
			?>
				<p>
					<label>
						<?php _e('Widget title:', $gcd) ?>
						<input type="text" size="20" name="gigs_title" value="<?php echo $o['widget']['upcoming']['title']; ?>" />
					</label>
				</p>
				<p>
					<label>
						<?php _e('Number of gigs to show on the sidebar:', $gcd) ?>
						<input type="text" size="5" name="gigs_length" value="<?php echo $o['widget']['upcoming']['length']; ?>" />
					</label>
				</p>
				<p>
					<?php _e('Note: All date formats use the PHP <a href="http://php.net/date">date()</a> function syntax', $gcd) ?>
				</p>
				<p>
					<label>
						<?php _e('Date format for dates in the current year:', $gcd) ?>
						<input type="text" size="5" name="gigs_dateFormat" value="<?php echo $o['widget']['upcoming']['dateFormat']; ?>" />
					</label>
				</p>
				<p>
					<label>
						<?php _e('Date format for dates in a future year:', $gcd) ?>
						<input type="text" size="5" name="gigs_dateFormatYear" value="<?php echo $o['widget']['upcoming']['dateFormatYear']; ?>" />
					</label>
				</p>
				<p>
					<label>
						<?php _e('First piece of data in the link:', $gcd) ?>
						<select name="gig-firstLinkField">
							<?php foreach ( $linkFields as $key => $value ) : ?>
								<option value="<?php echo $key ?>" <?php if ($key == $o['widget']['upcoming']['link'][0]) echo 'selected="selected"' ?>><?php echo $value ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</p>				
				<p>
					<label>
						<?php _e('Second piece of data in the link:', $gcd) ?>
						<select name="gig-secondLinkField">
							<option value="none"><?php _e('None', $gcd) ?></option>
							<?php foreach ( $linkFields as $key => $value ) : ?>
								<option value="<?php echo $key ?>" <?php if ($key == $o['widget']['upcoming']['link'][1]) echo 'selected="selected"' ?>><?php echo $value ?></option>
							<?php endforeach; ?>
						</select>
					</label>
				</p>				
			<?php
		}
		
		function widget_next($args) {
			extract($args);
			$o = get_option('gigs-calendar');
			if ( !isset($o['widget']['next']) ) $o['widget']['next'] = array('title'=>__('Next Gig', $gcd));
			?>
				<?php echo $before_widget; ?>
				<?php echo $before_title . $o['widget']['next']['title'] . $after_title; ?>
					<?php 
						dtc_gigs::generateList(array(
							'upcoming'=>true, 
							'limit'=>1,
							'template'=>'next-widget',
						)); 
					?>
				<?php echo $after_widget; ?>
			<?php
		}
		
		function widget_next_control() {
			global $gcd;
			$o = get_option('gigs-calendar');
			
			$defaults = array(
				'title' => __('Next Gig', $gcd),
			);
			
			if ( !isset($o['widget']['next']) ) {
				$o['widget']['next'] = array();
			}
			
			$o['widget']['next'] = array_merge($defaults, $o['widget']['next']);
			
			if ( isset($_POST['gigs_next_title']) ) {
				$o['widget']['next']['title'] = !empty($_POST['gigs_next_title']) ? $_POST['gigs_next_title'] : $defaults['title'];
				print_r($o['widget']);
				update_option('gigs-calendar', $o);
			}
			
			?>
				<p>
					<label>
						<?php _e('Widget title:', $gcd) ?>
						<input type="text" size="20" name="gigs_next_title" value="<?php echo $o['widget']['next']['title']; ?>" />
					</label>
				</p>
			<?php
		}
		
		function init() {
			global $gcd;
			load_plugin_textdomain($gcd, 'wp-content/plugins/gigs-calendar/i18n');
			if ( function_exists('register_sidebar_widget') ) {
				register_sidebar_widget('Upcoming Gigs', array('dtc_gigs', 'widget_upcoming'));
				register_widget_control('Upcoming Gigs', array('dtc_gigs', 'widget_upcoming_control'), 360, 273);
				
				register_sidebar_widget('Next Gig', array('dtc_gigs', 'widget_next'));
				register_widget_control('Next Gig', array('dtc_gigs', 'widget_next_control'), 360, 273);
			}
		}
		
		function generateList($args = false) {
			ob_start();
			global $wpdb, $post, $include_folder, $gcd;
			$options = get_option("gigs-calendar");
			require_once 'gigs-classes.php';
			$tpath = dirname(__FILE__) . '/templates/';
			
			$folder = dtc_gigs::get_url();
			
			$defaults = array(
				'upcoming' => true,
				'limit' => false, 
				'tour' => false, 
				'template' => 'gigs-list',
				'dateFormat' => ($args['upcoming'] === false ? 'archive' : 'short'),
				'force' => false,
				'year' => null,
				'month' => null,
				'caption' => null,
			);
			
			
			$args = is_array($args) ? array_merge($defaults, $args) : $defaults;
			extract($args);

			$listFields = array(
				'city' => __('City', $gcd),
				'country' => __('Country', $gcd),
				'venue' => __('Venue', $gcd),
				'eventName' => __('Event', $gcd),
				'date' => __('Date', $gcd),
				'time' => __('Time', $gcd),
				'shortNotes' => __('Notes', $gcd),
				'tickets' => __('Tickets', $gcd),
				'map' => __('Map', $gcd),
			);
			
			$show_fields = $options['gigs-table-show'];
			if ( !$upcoming && in_array( 'tickets', $show_fields ) ) {
				unset( $show_fields[array_search('tickets', $show_fields)] );
			}
			
			$gig_ids = array();
			if ( is_null($year) && is_null($month) ) {
				$gigs = $wpdb->get_results('
					SELECT 
						*,
						g.notes as gigNotes,
						v.notes as venueNotes,
						g.id as gigID, 
						v.link as venueLink
					FROM 
						`' . TABLE_GIGS . '` AS g
					LEFT JOIN
						`' . TABLE_VENUES . '` AS v ON ( g.venueID = v.id )
					WHERE
						`date` ' . ($upcoming ? '>=' : '<=') . ' CURDATE()
						' . (($tour && $tour != -1) ? ' AND tour_id = ' . (int) $tour : '') . '
						' . ($tour == -1 ? ' AND tour_id is null' : '') . '
					ORDER BY 
						`date` ' . ($upcoming ? 'ASC' : 'DESC') . '
					' . ( !empty($limit) ? ' LIMIT ' . $limit : '' ) . '
					
				');
			} else {
				$gigs = $wpdb->get_results('
					SELECT 
						*,
						g.notes as gigNotes,
						v.notes as venueNotes,
						g.id as gigID, 
						v.link as venueLink
					FROM 
						`' . TABLE_GIGS . '` AS g
					LEFT JOIN
						`' . TABLE_VENUES . '` AS v ON ( g.venueID = v.id )
					WHERE
						1=1 
						' . ( !is_null($year) ? 'AND YEAR(`date`) = ' . (int) $year : '' ) . '
						' . ( !is_null($month) ? 'AND MONTH(`date`) = ' . (int) $month : '' ) . '
						' . (($tour && $tour != -1) ? ' AND tour_id = ' . (int) $tour : '') . '
						' . ($tour == -1 ? ' AND tour_id is null' : '') . '
					ORDER BY 
						`date` ' . ($upcoming ? 'ASC' : 'DESC') . '
					' . ( !empty($limit) ? ' LIMIT ' . $limit : '' ) . '
					
				');
			}
			
			if ( $tour !== false && $tour !== "false" ) {
				if ( !count($gigs) && !$options['tours-empty'] && !$force ) {
					return '';
				}
				if ( $t != -1 && empty($caption) ) {
					$t = new tour($tour);
					$caption = '<div class="name">' . $t->name . '</div><div class="notes">' . $t->notes . '</div>';
				}
			}

			
			$performances = $wpdb->get_results('
				SELECT 
					p.*
				FROM 
					`' . TABLE_GIGS . '` AS g
				LEFT JOIN
					`' . TABLE_PERFORMANCES . '` AS p ON ( g.id = p.gigID )
				ORDER BY 
					`time` ASC
			');
			
			foreach ( $gigs as $key => $g ) {
				if ( empty($g->venueID) ) {
					$g->mapLink = false;
				} else {
					$g->mapLink = dtcGigs::mapLink($g);
				}
				$g->cityState = $g->city . ( empty($g->state) ? '' : ', ' . $g->state );
				$g->cityStateCountry = $g->city . ( empty($g->state) ? '' : ', ' . $g->state ) . ( empty($g->country) ? '' : ', ' . $g->country );
				$g->cityCountry = $g->city . ( empty($g->country) ? '' : ', ' . $g->country );
				$g->permalink = get_permalink($g->postID);
				$g->shortDate = dtcGigs::dateFormat($g->date, 'short');
				$g->longDate = dtcGigs::dateFormat($g->date, 'long');
				$g->archiveDate = dtcGigs::dateFormat($g->date, 'archive');
				$g->mysqlDate = $g->date;
				$g->date = dtcGigs::dateFormat($g->date, $dateFormat);
				$g->id = $g->gigID;
				$g->id = $g->gigID;
				$g->performances = array();
				$g->tags_slugs = array();
				$g->tags = function_exists('wp_get_post_tags') ? wp_get_post_tags($g->postID) : array();
				$gig_ids[$g->gigID] = $key;
				foreach ( $g->tags as $tagkey => $tag ) {
					$g->tags[$tagkey] = $tag->name;
					$g->tags_slugs[$tagkey] = 'gc-' . $tag->slug;
				}
				$g->custom = get_post_custom($g->postID);
				$gigs[$key] = $g;
			}
			
			foreach ( $performances as $p ) {
				$p->time_12h = dtcGigs::timeFormat($p->time, '12h');
				$p->time_24h = dtcGigs::timeFormat($p->time, '24h');
				$p->time = dtcGigs::timeFormat($p->time);

				if ( isset($gigs[$gig_ids[$p->gigID]]) ) {
					$gigs[$gig_ids[$p->gigID]]->performances[] = $p;
				}
			}
				
			if ( is_file(dirname(__FILE__) . '/templates/' . $options['template'] . '/' . $template . '.php') ) {
				include(dirname(__FILE__) . '/templates/' . $options['template'] . '/' . $template . '.php');
			} elseif ( is_file(ABSPATH . 'wp-content/gigs-templates/' . $options['template'] . '/' . $template . '.php') ) {
				include(ABSPATH . 'wp-content/gigs-templates/' . $options['template'] . '/' . $template . '.php');
			} else {
				include(dirname(__FILE__) . '/templates/' . 'basic/' . $template . '.php');				
			}
			$result = ob_get_clean();
			if ( $return ) return $result;
			else echo $result;
		}
		
		function upcomingList($args = array()) {
			// This method has been depriciated.  Stop using it!
			global $include_folder;
			$o = get_option('gigs-calendar');
			$defaults = array(
				"length" => 5,
				"dateFormat" => 'M j',
				'dateFormatYear' => 'M j, Y',
				'link' => array('city'),
				'return' => false,
			);
			
			
			$args = array_merge($defaults, $args);
			
			include_once 'gigs-classes.php';
			$g = new gig();
			$g->search('`date` >= CURDATE()', '`date` ASC');

			$count = 0;
			if ( $g->_count ) : 

				?>
					<ul class="gigs">
						<?php while ( $g->fetch() ) : 
							$v = $g->getVenue(); 
							$p = $g->getPerformances(); 
							$link = array();
							foreach ( $args['link'] as $f ) {
								switch ( $f ) {
									case 'city': 
										$link[] = $v->getCity();
										break;
									case 'venue':
										$link[] = $v->name;
										break;
									case 'eventName':
										$link[] = $g->eventName;
										break;
								}
							}
							$link = array_unique($link);
							foreach ( $link  as $key => $value ) {
								if ( empty($value) ) unset($link[$key]);
							}
							?>
							
							<li class="<?php echo implode(' ', $g->getTags(true)); ?>"><?php echo date((date('Y') == date('Y', strtotime($g->date))) ? $args['dateFormat'] : $args['dateFormatYear'], strtotime($g->date . ' ' . $p->time)) ?>: <a href="<?php echo $g->getPermalink(); ?>">
								<?php echo implode(' - ', $link) ?>
							</a></li>
						<?php if ( ++$count >= $args['length'] ) break; endwhile; ?>
					</ul>
				<?php
			else :
				?>
					<div class="no-gigs"><?php echo $o['no-upcoming']; ?></div>
				<?php
			endif;
		}
		
		function test($posts) {
			global $wp_query;
			
			//print_r($wp_query);
				
			return $posts;
		}
		
		function setup_future_hook() {
			$o = get_option('gigs-calendar');
			if ( $o['post-filtering'] ) {
				remove_action('future_post', '_future_post_hook');
				add_action('future_post', array('dtc_gigs', 'publish_future_post_now'));
			}
		}

		function publish_future_post_now($id, $post = null) {
			global $wpdb;
			if ( $wpdb->get_var('SELECT COUNT(*) FROM ' . TABLE_GIGS . ' WHERE postID = ' . (int) $id ) ) {
				wp_publish_post($id);
			} else {
				_future_post_hook($id, $post);
			}
		}
	}
	
	

	add_action('init', array('dtc_gigs', 'setup_future_hook'));
	add_action('admin_menu', array('dtc_gigs', 'add_admin_page'));

	//add_filter('the_posts', array('dtc_gigs', 'test'));

	if ( 4.1 <= mysql_get_server_info() ) {
		add_filter('posts_where', array('dtc_gigs', 'hide_posts'));
		add_filter('get_next_post_where', array('dtc_gigs', 'post_nav'));
		add_filter('get_previous_post_where', array('dtc_gigs', 'post_nav'));
		add_filter('getarchives_where', array('dtc_gigs', 'hide_archives'));
	} else {
		
	}
	
	add_filter('the_content', array('dtc_gigs', 'display'));
	
	add_action('plugins_loaded', array('dtc_gigs', 'init'));

	
	if ( $_GET['page'] == 'gigs' ) :
		// If not WP > 2.6, we'll need to include some custom scripts.
		$folder = dtc_gigs::get_url();
		if ( (float) $wp_version < 2.6 ) {
			wp_deregister_script('jquery');
			wp_deregister_script('interface');
			wp_deregister_script('jquery-form');
			wp_enqueue_script('jquery', $folder . 'js/jquery.js', array(), '1.2.6');
			wp_enqueue_script('jquery-form', $folder . 'js/jquery.form.js', array(), '2.02');
			wp_enqueue_script('jquery-ui-core', $folder . 'js/ui.core.js', array(), '1.5');
			wp_enqueue_script('jquery-ui-sortable', $folder . 'js/ui.sortable.js', array(), '1.5');
		} else {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-form');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
		}
		wp_enqueue_script('jquery-ui-datepicker', $folder . 'js/ui.datepicker.min.js', array(), '1.5');
		wp_enqueue_script('jquery-tooltip', $folder . 'js/jquery.tooltip.min.js', array(), '1.2');

		add_action('admin_head', array('dtc_gigs', 'admin_css'));
		
		if ( get_option('gig_db_version') != DTC_GIGS_DB_VERSION ) {
			dtc_gigs::upgrade();
		}

	endif;
	
	add_action('wp_head', array('dtc_gigs', 'display_css'));	
else:
	require_once $include_folder . '/ajaxSetup.php';
endif;

?>

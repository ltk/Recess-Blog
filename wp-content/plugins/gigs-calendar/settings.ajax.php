<?php
require_once 'ajaxSetup.php';

$date_defaults = array(
	'short' => 'm/d/y',
	'archive' => 'm/d/y',
	'long' => 'l, F j, Y',
);


switch ( $_POST['action'] ) :
	case 'load':
		$listFields = array(
			'city' => __('City', $gcd),
			'country' => __('Country', $gcd),
			'venue' => __('Venue', $gcd),
			'eventName' => __('Event Name', $gcd),
			'date' => __('Date', $gcd),
			'time' => __('Time', $gcd),
			'shortNotes' => __('Short Notes', $gcd),
			'tickets' => __('Ticket Link', $gcd),
			'map' => __('Map Link', $gcd),
		);
		
		$titleFields = array(
			'city' => __('City', $gcd),
			'venue' => __('Venue', $gcd),
			'country' => __('Country', $gcd),
			'eventName' => __('Event Name', $gcd),
			'date' => __('Date', $gcd),
		);
		
		$defaults = array(
			'archive' => -1,
			'category' => get_option('default_category'),
			'no-upcoming' => __('Sorry, there aren&rsquo;t any upcoming gigs right now.  Check back soon!', $gcd),
			'no-past' => __('There aren&rsquo;t any gigs here right now.  Check back soon!', $gcd),
			'silk-link' => 1,
			'b4b-link' => 1,
			'rss-link' => 1,
			'gigs-table-show' => array_keys($listFields),
			'gig-title-show' => array('city', 'date'),
			'eventName-label' => __("Who", $gcd),
			'ages-list' => array(__('All Ages', $gcd), __('21+', $gcd), __('16+', $gcd)),
			'list-venue-link' => 0,
			'list-headers' => 0,
			'template' => 'basic',
			'time-12h' => 1,
			'calendar-position' => "bottom",
			'tbd-text' => __("TBD"),
			'tours-display' => 0,
			'tours-empty' => 0,
			'tours-sort' => 'date',
			'user_level' => 'level_5',
			'post-filtering' => 1,
			'admin-only-settings' => 0,
		);
		
		unset($defaults['gigs-table-show'][array_search('eventName', $defaults['gigs-table-show'])]);
		unset($defaults['gigs-table-show'][array_search('country', $defaults['gigs-table-show'])]);
		
		if ( is_array($options) ) {
			$options = array_merge($defaults, $options);
		} else {
			$options = $defaults;
		}
		if ( is_array($options['date']) ) {
			$options['date'] = array_merge($date_defaults, $options['date']);
		} else {
			$options['date'] = $date_defaults;
		}
		
		if ( (int) get_option('gig_db_version') == -1 ) {
			die (__('Oops! It looks like you&rsquo;re missing some or all of the tables required for this plugin.  They should have been created automatically, but you can create them with the tables.sql file in the same folder as this plugin.  If you have any questions, you can use the feedback form in the next tab.'));
		}
		?>
			<?php if ( empty($options['parent']) && $options['calendar-position'] != 'custom' ) : ?>
				<div><?php _e('You need to select a page to list your calendar.  You may need to go create a <a href="page-new.php">new page</a> first.', $gcd) ?></div>
			<?php endif; ?>
			<?php if ( 4.1 > mysql_get_server_info() ) : ?>
				<div>
					<?php _e('<span style="font-weight: bold">Warning:</span> You are running MySQL server version <?php echo mysql_get_server_info(); ?>.  This plugin requires version 4.1 or higher to hide the future gig posts from your homepage.  Your best option is to use a plugin like <a href="http://wordpress.org/extend/plugins/advanced-category-excluder/">Advanced Category Excluder</a> to hide the gigs using the category option below.'); ?>
				</div>
			<?php endif; ?>
			<form id="settings-form" method="post">
				<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
				<input type="hidden" name="action" value="save" />
				<h3><?php _e('General Options', $gcd) ?></h3>
				<div><label>
					<?php _e('Select a location for the calendars on the page:', $gcd) ?> 
					<select name="calendar-position">
						<option value="top" <?php if ( isset($options['calendar-position']) && $options['calendar-position'] == 'top') echo 'selected="selected"'; ?>><?php _e('Top', $gcd) ?></option>
						<option value="bottom" <?php if ( isset($options['calendar-position']) && $options['calendar-position'] == 'bottom') echo 'selected="selected"'; ?>><?php _e('Bottom', $gcd) ?></option>
						<option value="custom" <?php if ( isset($options['calendar-position']) && $options['calendar-position'] == 'custom') echo 'selected="selected"'; ?>><?php _e('Custom', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Display gigs as normal blog posts (turns off filtering):', $gcd) ?> 
					<select name="post-filtering">
						<option value="0" <?php if ( isset($options['post-filtering']) && $options['post-filtering'] == '0') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="1" <?php if ( isset($options['post-filtering']) && $options['post-filtering'] == '1') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><?php _e('Note: If you select the custom location option, you don&rsquo;t have to select a page, but you won&rsquo;t get a link back to the calendar', $gcd); ?></div>
				<div><label>
					<?php _e('Select a page to house your calendar:', $gcd) ?> 
					<?php wp_dropdown_pages('show_option_none=' . __('--None--', $gcd) . '&name=parent&selected=' . $options['parent']); ?>
				</label></div>
				
				<div><label>
					<?php _e('Select a page to house your gigs archive:', $gcd) ?> 
					<?php wp_dropdown_pages('show_option_none=' . __('--None--', $gcd) . '&name=archive&selected=' . $options['archive']); ?>
				</label></div>

				<div><label>
					<?php _e('Select a category that gigs should be created under:', $gcd) ?> 
					<select name="category">
						<?php foreach ( get_categories(array('hide_empty'=>false)) as $cat ) : ?>
							<?php if ( (float) $wp_version < 2.3 ) : ?>
								<option value="<?php echo $cat->cat_ID ?>" <?php if ( isset($options['category']) && $options['category'] == $cat->cat_ID) echo 'selected="selected"'; ?>><?php echo $cat->cat_name ?></option>
							<?php else : ?>
								<option value="<?php echo $cat->term_id ?>" <?php if ( isset($options['category']) && $options['category'] == $cat->term_id) echo 'selected="selected"'; ?>><?php echo $cat->name ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Display this message if there are no upcoming gigs:', $gcd) ?><br />
					<textarea rows="2" cols="50" name="no-upcoming"><?php echo $options['no-upcoming'] ?></textarea>
				</label></div>
				
				<div><label>
					<?php _e('Display this message if there are no past gigs:', $gcd) ?><br />
					<textarea rows="2" cols="50" name="no-past"><?php echo $options['no-past'] ?></textarea>
				</label></div>

				<h3><?php _e('Administration Options', $gcd) ?></h3>
				
				<div><label>
					<?php _e('Minimum permission level to administer the calendar: ', $gcd) ?> 
					<select name="user_level">
						<?php
							$levels = array(
								'level_0' => __('Subscriber', $gcd),
								'level_1' => __('Contributor', $gcd),
								'level_2' => __('Author', $gcd),
								'level_5' => __('Editor', $gcd),
								'level_8' => __('Administrator', $gcd),
							);
							foreach ( $levels as $level => $name ) {
								?>
									<option value="<?php echo $level; ?>" <?php if ( $options['user_level'] == $level) echo 'selected="selected"'; ?>><?php echo $name ?></option>
								<?php
							}
						?>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Only allow blog administrators to change calendar settings:', $gcd) ?> 
					<select name="admin-only-settings">
						<option value="1" <?php if ( isset($options['admin-only-settings']) && $options['admin-only-settings'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['admin-only-settings']) && $options['admin-only-settings'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>

				
				<h4><?php _e('Performances', $gcd) ?></h4>
				<div><?php _e('Options you want in the "ages" dropdown.', $gcd) ?></div>
				<ul id="ages-list" class="sortable">
					<?php foreach ( $options['ages-list'] as $field ) : ?>
						<li class="ages-list-item">
							<input type="hidden" name="ages-list[]" value="<?php echo $field ?>" />
							<img alt="<? _e('Delete', $gcd) ?>" title="<? _e('Delete', $gcd) ?>" class="delete icon clickable" src="<?php echo $folder ?>images/delete.png" />
							<span class="handle name"><?php echo $field ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<input type="text" id="new-age" /> <img src="<?php echo $folder ?>images/add.png" id="add-new-age" alt="<? _e('Add', $gcd) ?>" title="<? _e('Add', $gcd) ?>" />
				
				<h3><?php _e('Display Options', $gcd) ?></h3>
				<div><label>
					<?php _e('Display template: ', $gcd) ?> 
					<select name="template">
						<?php
							$templates = array();
							$template_dir = opendir(dirname(__FILE__) . '/templates');
							while ( $dir = readdir($template_dir) ) {
								if ( substr($dir, 0, 1) != '.' && is_dir(dirname(__FILE__) . '/templates/' . $dir) ) {
									$templates[] = $dir;
								}
							}
							
							if ( file_exists(ABSPATH . 'wp-content/gigs-templates') ) {
								$template_dir = opendir(ABSPATH . 'wp-content/gigs-templates');
								while ( $dir = readdir($template_dir) ) {
									if ( substr($dir, 0, 1) != '.' && is_dir(ABSPATH . 'wp-content/gigs-templates/' . $dir) ) {
										$templates[] = $dir;
									}
								}
							}
							
							sort($templates);
							foreach ( $templates as $dir ) {
								?>
									<option <?php if ( $options['template'] == $dir) echo 'selected="selected"'; ?>><?php echo $dir ?></option>
								<?php
							}
						?>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Short date format:', $gcd) ?>
					<input type="text" name="date-short" value="<?php if ( isset($options['date']['short']) ) dtcGigs::escapeForInput($options['date']['short']); ?>" />
				</label> <?php _e('(uses PHP&rsquo;s <a href="http://php.net/date">date()</a> function format)', $gcd) ?></div>
				<div><label>
					<?php _e('Archives date format:', $gcd) ?>
					<input type="text" name="date-archive" value="<?php if ( isset($options['date']['archive']) ) dtcGigs::escapeForInput($options['date']['archive']); ?>" />
				</label> <?php _e('(uses PHP&rsquo;s <a href="http://php.net/date">date()</a> function format)', $gcd) ?></div>
				
				<div><label>
					<?php _e('Long date format:', $gcd) ?>
					<input type="text" name="date-long" value="<?php if ( isset($options['date']['long']) ) dtcGigs::escapeForInput($options['date']['long']); ?>" />
				</label> <?php _e('(uses PHP&rsquo;s <a href="http://php.net/date">date()</a> function format)', $gcd) ?></div>
				
				<div><label>
					<?php _e('Time format:', $gcd) ?> 
					<select name="time-12h">
						<option value="1" <?php if ( isset($options['time-12h']) && $options['time-12h'] == '1') echo 'selected="selected"'; ?>><?php _e('12 Hour', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['time-12h']) && $options['time-12h'] == '0') echo 'selected="selected"'; ?>><?php _e('24 Hour', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Text to display when the time is "To be determined":', $gcd) ?>
					<input type="text" name="tbd-text" value="<?php if ( isset($options['tbd-text']) ) dtcGigs::escapeForInput($options['tbd-text']); ?>" />
				</label></div>

				
				<h4><?php _e('Gigs page table', $gcd) ?></h4>
				<div><?php _e('You can sort the fields by clicking on the names and dragging.', $gcd) ?></div>
				<ul id="gigs-table" class="sortable">
					<?php if ( isset($options['gigs-table-show']) && is_array($options['gigs-table-show']) ) : ?>
						<?php foreach ( $options['gigs-table-show'] as $field ) : ?>
							<li class="gigs-table-cell">
								<input type="checkbox" name="gigs-table-show[]" value="<?php echo $field ?>" checked="checked" />
								<span class="handle name"><?php echo $listFields[$field] ?></span>
							</li>
						<?php endforeach; ?>
					<?php endif ?>
					<?php foreach ( $listFields as $key => $value ) : ?>
						<?php if ( !isset($options['gigs-table-show']) || !is_array($options['gigs-table-show']) || !in_array($key, $options['gigs-table-show']) ) : ?>
							<li class="gigs-table-cell">
								<input type="checkbox" name="gigs-table-show[]" value="<?php echo $key ?>" />
								<span class="handle name"><?php echo $value ?></span>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<div><label>
					<?php _e('Field to use for the link to the gig:', $gcd) ?> 
					<select name="gig-link-field">
						<?php foreach ( $listFields as $key => $value ) : ?>
							<?php if ( !in_array($key, array('tickets', 'map', 'shortNotes')) ) : ?>
								<option value="<?php echo $key ?>" <?php if ( isset($options['gig-link-field']) && $options['gig-link-field'] == $key) echo 'selected="selected"'; ?>><?php echo $value; ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Link to the venue&rsquo;s website from the gig list?', $gcd) ?> 
					<select name="list-venue-link">
						<option value="1" <?php if ( isset($options['list-venue-link']) && $options['list-venue-link'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['list-venue-link']) && $options['list-venue-link'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Display column headers?', $gcd) ?> 
					<select name="list-headers">
						<option value="1" <?php if ( isset($options['list-headers']) && $options['list-headers'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['list-headers']) && $options['list-headers'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Link to Gigs RSS feed?', $gcd) ?> 
					<select name="rss-link">
						<option value="1" <?php if ( isset($options['rss-link']) && $options['rss-link'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['rss-link']) && $options['rss-link'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<h4><?php _e('Single Gig Page', $gcd) ?></h4>
				
				<div><?php _e('Select and drag the fields for the gig post title.', $gcd) ?></div>
				<ul id="gig-title" class="sortable">
					<?php if ( isset($options['gig-title-show']) && is_array($options['gig-title-show']) ) : ?>
						<?php foreach ( $options['gig-title-show'] as $field ) : ?>
							<li class="gig-title-cell">
								<input type="checkbox" name="gig-title-show[]" value="<?php echo $field ?>" checked="checked" />
								<span class="handle name"><?php echo $titleFields[$field] ?></span>
							</li>
						<?php endforeach; ?>
					<?php endif ?>
					<?php foreach ( $titleFields as $key => $value ) : ?>
						<?php if ( !isset($options['gig-title-show']) || !is_array($options['gig-title-show']) || !in_array($key, $options['gig-title-show']) ) : ?>
							<li class="gig-title-cell">
								<input type="checkbox" name="gig-title-show[]" value="<?php echo $key ?>" />
								<span class="handle name"><?php echo $value ?></span>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>

				<div><label>
					<?php _e('Label next to the event name:', $gcd) ?>
					<input type="text" name="eventName-label" value="<?php if ( isset($options['eventName-label']) ) dtcGigs::escapeForInput($options['eventName-label']); ?>" />
				</label></div>

				<h4><?php _e('Tours Options', $gcd) ?></h4>
				<div><label>
					<?php _e('Do you want to split gigs by tour on your calendar page?', $gcd) ?>
					<select name="tours-display">
						<option value="1" <?php if ( isset($options['tours-display']) && $options['tours-display'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['tours-display']) && $options['tours-display'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('Do you want to display empty tours?', $gcd) ?>
					<select name="tours-empty">
						<option value="1" <?php if ( isset($options['tours-empty']) && $options['tours-empty'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['tours-empty']) && $options['tours-empty'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				
				<div><label>
					<?php _e('How should the tours be ordered?', $gcd) ?>
					<select name="tours-sort">
						<option value="date" <?php if ( isset($options['tours-sort']) && $options['tours-sort'] == 'date') echo 'selected="selected"'; ?>><?php _e('Date of the earliest gig', $gcd) ?></option>
						<option value="alpha" <?php if ( isset($options['tours-sort']) && $options['tours-sort'] == 'alpha') echo 'selected="selected"'; ?>><?php _e('Alphabetical', $gcd) ?></option>
						<!--<option value="custom" <?php if ( isset($options['tours-sort']) && $options['tours-sort'] == 'custom') echo 'selected="selected"'; ?>><?php _e('Custom', $gcd) ?></option>-->
					</select>
				</label></div>


				<h3><?php _e('Credits', $gcd) ?></h3>
				<div><label>
					<?php _e('Do you want to link back to me for building this plugin?', $gcd) ?>
					<select name="b4b-link">
						<option value="1" <?php if ( isset($options['b4b-link']) && $options['b4b-link'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['b4b-link']) && $options['b4b-link'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				<div style="margin: 0px 1.5em 1em;">
					<?php _e('<b>Note</b>: It&rsquo;s ok to say "no".  I&rsquo;ve offered this freely with no expectations, but I&rsquo;d be very grateful if you said "yes"', $gcd) ?>
				</div>
				
				<div><label>
					<?php _e('Do you want to link back to <a target="_blank" href="http://www.famfamfam.com/lab/icons/silk/">Mark James</a> for the Silk icons?', $gcd) ?>
					<select name="silk-link">
						<option value="1" <?php if ( isset($options['silk-link']) && $options['silk-link'] == '1') echo 'selected="selected"'; ?>><?php _e('Yes', $gcd) ?></option>
						<option value="0" <?php if ( isset($options['silk-link']) && $options['silk-link'] == '0') echo 'selected="selected"'; ?>><?php _e('No', $gcd) ?></option>
					</select>
				</label></div>
				<div style="margin: 0px 1.5em;">
					<?php _e('<b>Note</b>: The Silk Icons that I used in this plugin are licensed under the <a target="_blank" href="http://creativecommons.org/licenses/by/2.5/">Creative Commons</a>. You must link back to him somewhere on your site.  If you link to him on another page, giving him credit, or you have replaced the icons, or you do not display any of them publicly on your site, you may turn off this link.', $gcd) ?></div>
							
				<br /><br />
				<div>
					<input type="submit" class="button" value="<?php _e('Save Options', $gcd) ?>" /><br />
					<?php _e('If you made changes that would affect posts, do you want changes applied to existing gigs (possibly breaking existing links)?', $gcd); ?>
					<select name="apply-changes">
						<option value="no"><?php _e('No', $gcd); ?></option>
						<option value="future"><?php _e('Yes, future gigs only', $gcd); ?></option>
						<option value="all"><?php _e('Yes, all gigs, please', $gcd); ?></option>
					</select>
				</div>
			</form>
			
			<script type="text/javascript">
				(function($){
					setupEvents = function() {
						jQuery("#settings form").ajaxForm({
							url:pageTarget,
							success:function(){
								if ( jQuery("#gigs-menu li").length == 3 ) {
									window.location.reload();
								} else {
									gigs_page_load("settings");
								}
							}
						});
						

						$("#gigs-table").sortable({
							handle : "span.name",
							axis:'y'
						});
						
						$("#gig-title").sortable({
							handle : "span.name",
							axis:'y'
						});
						
						$("#ages-list").sortable({
							handle : "span.name",
							axis:'y'
						});
						
						$("#add-new-age").click(function(){
							$("#ages-list").append('<li class="ages-list-item"><input type="hidden" value="' + $("#new-age").val() + '" name="ages-list[]"/><img src="http://dev.ssdn.us/wp-content/plugins/gigs-calendar/images/delete.png" class="delete icon clickable" title="Delete" alt="Delete"/> <span class="handle name" style="-moz-user-select: none;">' + $("#new-age").val() + '</span></li>');
							$("#new-age").val("");
							$(".ages-list-item").find(".delete").unbind("click");
							$(".ages-list-item").find(".delete").click(function(){
								$(this).parents("li.ages-list-item").remove();
							});

						});
						
						
						$("#new-age").keypress(function(e){
							if ( e.keyCode == 13 ) {
								$("#add-new-age").click();
								return false;
							}
						});
						
						$(".ages-list-item").find(".delete").click(function(){
							$(this).parents("li.ages-list-item").remove();
						});
						
						
					}
				}(jQuery));
				
				setTimeout("setupEvents();");
			</script>
			

		<?php
		break;
	case 'save':
		$args = $_POST;
		
		$dates = array(
			'short' => (empty($args['date-short']) ? $date_defaults['short'] : $args['date-short']),
			'archive' => (empty($args['date-archive']) ? $date_defaults['archive'] : $args['date-archive']),
			'long' => (empty($args['date-long']) ? $date_defaults['long'] : $args['date-long']),
		);
		
		unset($args['apply-changes'], $args['nonce'], $args['action'], $args['date-long'], $args['date-archive'], $args['date-short'], $args['long-date-format'], $args['short-date-format']);
		
		foreach ( $args as $key => $value ) {
			if ( is_string($value) ) {
				$args[$key] = stripslashes($value);
			}
		}
		
		$options = get_option('gigs-calendar');
		if ( is_array($options) ) {
			$args = array_merge($options, $args);
		}
		if ( is_array($args['date']) ) {
			$args['date'] = array_merge($args['date'], $dates);
		} else {
			$args['date'] = $dates;
		}

		update_option('gigs-calendar', $args);
		$options = get_option('gigs-calendar');
		if ( $_POST['apply-changes'] != 'no' ) {
			$g = new gig();
			$g->search('future' == $_POST['apply-changes'] ? 'date >= CURDATE()' : null);
			while ( $g->fetch() ) {
				$g->save(false);
			}
		}
		break;
endswitch;

?>

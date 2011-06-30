<?php
global $wpdb;
define('GIGS_DB_PREFIX', apply_filters('gigCal_db_prefix', $wpdb->prefix));
define('TABLE_VENUES', GIGS_DB_PREFIX . 'gigs_venue');
define('TABLE_GIGS', GIGS_DB_PREFIX . 'gigs_gig');
define('TABLE_PERFORMANCES', GIGS_DB_PREFIX . 'gigs_performance');
define('TABLE_TOURS', GIGS_DB_PREFIX . 'gigs_tour');

class dtcGigs {
	
	function generateSlug($x) {
		$x = strtolower($x);
		$x = str_replace(' ', '-', $x);
		$x = preg_replace('#[^a-z\-0-9]#', '', $x);
		return $x;
	}
		
	function escapeForInput($x) {
		if ( '0000-00-00' == $x ) {
			$x = '';
		}
		echo htmlspecialchars($x);
	}
	
	function dateFormat($date, $type = 'short') {
		if ( is_null($date) || $date == '0000-00-00' ) return null;
		$options = get_option('gigs-calendar');
		if ( isset($options['date'][$type]) ) {
			return mysql2date($options['date'][$type], $date);
		} else {
			return mysql2date($type, $date);
		}
	}
	
	function selectFields($fields, $g) {
		$options = get_option('gigs-calendar');
		
		$result = '';
		foreach ( $options['gigs-table-show'] as $f ) {
			if ( isset($fields[$f]) ) {
				$tmp = $fields[$f];
				if ( $f == 'venue' && $options['list-venue-link'] && !empty($g->venueLink) ) {
					$tmp = preg_replace("~\{(.*?)\}~", '<a href="' . $g->venueLink . '">\1</a>', $tmp);
				} elseif ( $f == $options['gig-link-field'] ) {
					$tmp = preg_replace("~\{(.*?)\}~", '<a href="' . $g->permalink . '">\1</a>', $tmp);
				} else {
					$tmp = preg_replace("~\{(.*?)\}~", '\1', $tmp);
				}
					
				$result .= $tmp;
			}
		}
		
		return $result;
	}
	
	function timeFormat($time, $format = false) {
		$options = get_option('gigs-calendar');
		if ( is_null($time) ) {
			return $options['tbd-text'];
		}
		if ( $options['time-12h'] || $format === "12h" ) {
			return mysql2date("g:ia", '2008-01-01 ' . $time);
		} else {
			return mysql2date("H:i", '2000-01-01 ' . $time);
		}
	}

	function mapLink($gig) {
		global $options;
		if ( empty($gig->customMap) ) {
			$a = array();
			
			if ( empty($gig->address) ) 
				$a[] = $gig->name . ' near:';
			else {}
				$a[] = str_replace("\n", ", ", $gig->address);
			if ( !empty($gig->city) ) $a[] = $gig->city;
			if ( !empty($gig->state) ) $a[] = $gig->state;
			if ( !empty($gig->country) ) $a[] = $gig->country;
			if ( !empty($gig->postalCode) ) $a[] = $gig->postalCode;
			return 'http://maps.google.com/?q=' . urlencode(str_replace('near:,', 'near:', implode(', ', $a)));
		} else {
			if ( preg_match("/^https?:\/\//", $gig->customMap) ) {
				return $gig->customMap;
			} else {
				return 'http://maps.google.com/?q=' . urlencode($gig->customMap);
			}
		}
	}
	
	function getAttribution() {
		$options = get_option('gigs-calendar');
		?>
			<div class="attribution">
				<?php if ( $options['b4b-link'] ) : ?>
					<span class="b4b">Powered by <a target="_blank" href="http://blogsforbands.com">Blogs for Bands</a></span>
				<?php endif; ?>
				<?php if ( $options['silk-link'] ) : ?>
					<span class="silk">Silk icons by <a target="_blank" href="http://www.famfamfam.com/lab/icons/silk/">Mark James</a></span>
				<?php endif; ?>
			</div>
		<?php
	}
	
	function loadTemplate($file, $template = null, $data = array()) {
		$options = get_option('gigs-calendar');
		global $gcd;
		if ( is_null($template) ) {
			$template = $options['template'];
		}
		
		$folder = dtc_gigs::get_url();
		
		extract($data);
		if ( is_file(dirname(__FILE__) . '/templates/' . $template . '/' . $file . '.php') ) {
			include(dirname(__FILE__) . '/templates/' . $template . '/' . $file . '.php');
		} elseif ( is_file(ABSPATH . 'wp-content/gigs-templates/' . $template . '/' . $file . '.php') ) {
			include(ABSPATH . 'wp-content/gigs-templates/' . $template . '/' . $file . '.php');
		} else {
			include(dirname(__FILE__) . '/templates/basic/' . $file . '.php');				
		}
	}
	
	function extraFields($fields, &$data, $gig = "new") {
		foreach ( $fields as $key => $field ) {
			if ( isset($data[$field['name']]) ) {
				$value = array_pop($data[$field['name']]);
				if ( empty($data[$field['name']]) ) unset($data[$field['name']]);
			} else {
				$value = $field['default'];
			}
			?>
				<tr class="<?php echo addslashes($field['name']) ?>">
					<td valign="top" class="<?php echo addslashes($field['name']) ?>">
						<input type="hidden" value="<?php echo addslashes($field['name']) ?>" name="custom-key[<?php echo $key ?>]" />
						<label for="custom-<?php echo $gig ?>-<?php echo $key ?>"><?php echo $field['label'] ?></label>
						<textarea style="display:none;" rows="1" name="old-custom-value[<?php echo $key ?>]"><?php echo htmlspecialchars((is_array($value) ? serialize($value) : $value)) ?></textarea>
						<input class="delete" type="hidden" name="deletecustom[<?php echo $key ?>]" value="0" />
					</td>
					<td>
						<?php if ( $field['type'] == 'text' ) : ?>
							<input name="custom-value[<?php echo $key ?>]" type="text" id="custom-<?php echo $gig ?>-<?php echo $key ?>" value="<?php echo addslashes($value); ?>" />
						<?php elseif ( $field['type'] == 'date' ) : ?>
							<input class="date-field" name="custom-value[<?php echo $key ?>]" type="text" id="custom-<?php echo $gig ?>-<?php echo $key ?>" value="<?php echo addslashes($value); ?>" />
						<?php elseif ( $field['type'] == 'textarea' ) : ?>
							<textarea rows="1" id="custom-<?php echo $gig ?>-<?php echo $key ?>" name="custom-value[<?php echo $key ?>]"><?php echo htmlspecialchars($value) ?></textarea>
						<?php elseif ( $field['type'] == 'select' ) : ?>
							<select id="custom-<?php echo $gig ?>-<?php echo $key ?>" name="custom-value[<?php echo $key ?>]">
								<?php foreach ( $field['options'] as $oKey => $oValue ) : ?>
									<option value="<?php echo addslashes($oKey) ?>"<?php if ( $oKey == $value ) echo ' selected="selected"' ?>><?php echo $oValue ?></option>
								<?php endforeach; ?>
							</select>
						<?php elseif ( $field['type'] == 'radio' ) : ?>
							<?php foreach ( $field['options'] as $oKey => $oValue ) : ?>
								<input type="radio" id="custom-<?php echo $gig ?>-<?php echo $key ?>-<?php echo addslashes($oKey) ?>" name="custom-value[<?php echo $key ?>]" value="<?php echo addslashes($oKey) ?>" <?php if ( $value == $oKey ) echo 'checked="checked"' ?> /> 
								<label for="custom-<?php echo $gig ?>-<?php echo $key ?>-<?php echo addslashes($oKey) ?>">
									<?php echo $oValue ?>
								</label>
								<br />
							<?php endforeach; ?>
						<?php elseif ( $field['type'] == 'checkbox' ) : ?>
							<?php $values = is_array($value) ? $value : unserialize($value); ?>
							<?php foreach ( $field['options'] as $oKey => $oValue ) : ?>
								<input type="checkbox" id="custom-<?php echo $gig ?>-<?php echo $key ?>-<?php echo addslashes($oKey) ?>" name="custom-value[<?php echo $key ?>][]" value="<?php echo addslashes($oKey) ?>" <?php if ( in_array($oKey, $values) ) echo 'checked="checked"' ?> /> 
								<label for="custom-<?php echo $gig ?>-<?php echo $key ?>-<?php echo addslashes($oKey) ?>">
									<?php echo $oValue ?>
								</label>
								<input type="hidden" name="custom-value[<?php echo $key ?>][]" value="" /> 
								<br />
							<?php endforeach; ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php
		}

	}
	
	function get_gig_css_classes($gig) {
		if ( is_object($gig) && isset($gig->id) ) {
			$gig = $gig->id;
		} elseif ( is_array($gig) && isset($gig['id']) ) {
			$gig = $gig['id'];
		}
		
		$g = new gig();
		if ( !$g->get($gig) ) {
			return false;
		}
		
		$classes = array(
			'gig',
			mysql2date('\yY', $g->date),
			mysql2date('\mm', $g->date),
			( $g->date >= date('Y-m-d') ? 'upcoming' : 'archive' ),
		);
		
		$classes = array_merge($classes, $g->getTags(true, 'gc-'));
		
		return implode(' ', apply_filters('gigCal_gigs_css_classes', $classes, $g));
	}
}

class dtc_gigs_baseAR {
	var $wpdb, $_rows, $_row, $_new = true, $_table, $_count;
	
	function dtc_gigs_baseAR($id = null){
		global $wpdb;
		$this->wpdb = $wpdb;
		if ( $id !== null ) {
			$this->get($id);
		}
	}
	
	function fetch() {
		if ( count($this->_rows) ) {
			$this->_row = each($this->_rows);
			if ( $this->_row ) {
				$this->load();
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function generate_slug() {
		if ( isset($this->slug) ) {
			$first = true;
			$name = $this->_name;
			do {
				if ( $first ) {
					$this->slug = dtcGigs::generateSlug($this->$name);
					$first = false;
				} else {
					ereg('[0-9]*$', $this->slug, $match);
					$match = ((int) $match[0]) + 1;
					if ( 1 == $match ) $match++;
					$this->slug = preg_replace('/[0-9]*$/', '', $this->slug);
					$this->slug .= $match;
				}
				
			} while ( $this->wpdb->get_var('SELECT COUNT(*) FROM `' . $this->_table . '` WHERE slug = "' . $this->slug . '"') );
		}
	}

	
	function get($id) {
		$this->search('`id` = ' . (int) $id);
		return $this->fetch();
	}
	
	function getRow() {
		$row = array();
		foreach ( $this->_fields as $f ) {
			$row[$f]  = $this->$f;
		}
		return $row;
	}
	
	function getTags($slugs = false, $prefix = '') {
		if ( function_exists('wp_get_post_tags') ) {
			$tags = wp_get_post_tags($this->postID);
			foreach ( $tags as $key => $tag ) {
				$tags[$key] = $prefix . ($slugs ? $tag->slug : $tag->name);
			}
			return $tags;
		} else {
			return array();
		}
	}
	
	function getCustom() {
		if ( !empty($this->postID) ) {
			return get_post_custom($this->postID);
		}
	}
	
	function setTags($tags) {
		if ( function_exists('wp_set_post_tags') )
			wp_set_post_tags($this->postID, $tags, false);
	}
	



	
	function load() {
		foreach ( $this->_fields as $f ) {
			if ( is_null($this->_row['value']->$f) ) {
				$this->$f = null;
			} else {
				$this->$f = stripslashes($this->_row['value']->$f);
			}
		}
	}
	
	function search($where = null, $order = null) {
		$this->_new = false;
		$this->_rows = $this->wpdb->get_results('SELECT * FROM `' . $this->_table . '` ' . ($where !== null ? 'WHERE ' . $where : '') . ($order !== null ? ' ORDER BY ' . $order : ''));
		$this->_count = count($this->_rows);
		return count($this->_rows);
	}
	
	function save($get_after = true) {
		$first = true;
		$name = $this->_name;
		$this->generate_slug();
		if ( $this->_new ) {
			$fields = $this->_fields;
			$values = array();
			unset($fields[0]);
			
			$this->pre_insert();

			foreach ( $fields as $f ) {
				if ( is_null($this->$f) ) {
					$values[$f] = 'null';
				} else {
					$values[$f] = '"' . $this->wpdb->escape(stripslashes($this->$f)) . '"';
				}
			}
			
			if ( !$this->wpdb->query('INSERT INTO `' . $this->_table . '` (`' . implode('`, `', $fields) . '`) VALUES (' . implode(', ', $values) . ')') ) {
				return false;
			}
			$this->id = $this->wpdb->insert_id;

			$this->post_insert();
			if ( $get_after ) 
				$this->get((int) $this->id);

			return true;
		} else {
			$fields = $this->_fields;
			$values = array();
			unset($fields[0]);
			
			$this->pre_update();
			
			foreach ( $fields as $f ) {
				if ( is_null($this->$f) ) {
					$values[$f] = '`' . $f . '` = null';
				} else {
					$values[$f] = '`' . $f . '` = "' . $this->wpdb->escape(stripslashes($this->$f)) . '"';
				}
			}
			
			$result = (bool) $this->wpdb->query('UPDATE `' . $this->_table . '` SET ' . implode(', ', $values) . ' WHERE `id` = ' . (int) $this->id);
			
			if ( $result ) $this->post_update();
			
			if ( $get_after ) 
				$this->get((int) $this->id);
			
			return $result;
		}
	}
	
	function pre_insert(){}
	function post_insert(){}
	function pre_update(){}
	function post_update(){}
	function extraJSON() {return array();}
	
	function delete() {
		if ( !empty($this->id) ) {
			return (bool) $this->wpdb->query('DELETE FROM `' . $this->_table . '` WHERE `id` = ' . (int) $this->id);
		}
	}
	
	function toJSON($single = true) {
		if ( $single ) {
			$fields = array();
			if ( function_exists("json_encode") ) {
				foreach ( $this->_fields as $f ) {
					$fields[$f] = $this->$f;
				}
				
				$extra = $this->extraJSON();
				$fields = array_merge($fields, $extra);
				return json_encode($fields);
			} else {
				foreach ( $this->_fields as $f ) {
					$fields[] = '"' . $f . '":"' . str_replace("\n", '\n', addslashes($this->$f)) . '"';
				}
			
				$extra = $this->extraJSON();
				$fields = array_merge($fields, $extra);

				return '{' . implode(', ', $fields) . '}';
			}
		}
	}
	
	function getError() {
		return $this->wpdb->last_error;
	}
}

class venue extends dtc_gigs_baseAR {
	var $_table = TABLE_VENUES, $_name = 'name';
	var $id, $name, $address, $city, $state, $country, $postalCode, $contact, 
		$phone, $email, $link, $notes, $private = 0, $apiID = 0, $deleted = 0, $customMap;
	var $_fields = array(
		'id',
		'name',
		'address',
		'city',
		'state',
		'country',
		'postalCode',
		'contact',
		'phone',
		'email',
		'link',
		'notes',
		'private',
		'apiID',
		'deleted',
		'customMap',
	);
	
	function delete($forReal = false) {
		if ( $forReal ) {
			return parent::delete();
		} else {
			$this->deleted = 1;
			return $this->save();

		}
	}
	
	function getAddress($oneLine = false) {
		$a = array();
		$city = "";
		if ( $oneLine ) {
			if ( !empty($this->address) ) $a[] = str_replace("\n", ', ', $this->address);
			if ( !empty($this->city) ) $a[] = $this->city;
			if ( !empty($this->state) ) $a[] = $this->state;
			if ( !empty($this->postalCode) ) $a[] = $this->postalCode;
			if ( !empty($this->country) ) $a[] = $this->country;
			return implode(', ', $a);
		} else {
			if ( !empty($this->address) ) $a[] = $this->address;
			if ( !empty($this->city) ) $temp .= $this->city;
			if ( !empty($this->state) ) $temp .= (!empty($temp) ? ', ' : '' ) . $this->state;
			if ( !empty($this->country) ) $temp .= (!empty($temp) ? ', ' : '' ) . $this->country;
			if ( !empty($this->postalCode) ) $temp .= (!empty($temp) ? ' ' : '' ) . $this->postalCode;
			if ( !empty($temp) ) $a[] = $temp;
			return implode("\n", $a);
		}
	}
	
	function getCity() {
		$c = array();
		if ( !empty($this->city) ) $c[] = $this->city;
		if ( !empty($this->state) ) $c[] = $this->state;
		//if ( !empty($this->country) ) $c[] = $this->country;
		return implode(', ', $c);
	}
	
	function getMapLink() {
		global $options;
		if ( empty($this->customMap) ) {
			$a = array();
			
			if ( empty($this->address) ) 
				$a[] = $this->name . ' near:';
			else {}
				$a[] = str_replace("\n", ", ", $this->address);
			if ( !empty($this->city) ) $a[] = $this->city;
			if ( !empty($this->state) ) $a[] = $this->state;
			if ( !empty($this->country) ) $a[] = $this->country;
			if ( !empty($this->postalCode) ) $a[] = $this->postalCode;
			return 'http://maps.google.com/?q=' . urlencode(str_replace('near:,', 'near:', implode(', ', $a)));
		} else {
			if ( preg_match("/^https?:\/\//", $this->customMap) ) {
				return $this->customMap;
			} else {
				return 'http://maps.google.com/?q=' . urlencode($this->customMap);
			}
		}
	}
	
	function extraJSON() {
		if ( function_exists("json_encode") ) {
			$fields['mapLink'] = $this->getMapLink();
		} else {
			$fields[] = '"mapLink":"' . str_replace("\n", '\n', addslashes($this->getMapLink())) . '"';
		}
		return $fields;
	}
}

class gig extends dtc_gigs_baseAR {
	var $_table = TABLE_GIGS, $_name = 'id';
	var $id, $venueID = 0, $date, $notes, $postID = 0, $eventName, $tour_id = 0;
	var $_fields = array(
		'id',
		'venueID',
		'date',
		'notes',
		'postID',
		'eventName',
		'tour_id',
	);

	function delete() {
		$p = $this->getPerformances();
		while ( $p->fetch() ) {
			$p->delete();
		}
		wp_delete_post($this->postID);
		return parent::delete();
	}
	
	function getByPostID($postID) {
		$this->search("postID = " . (int) $postID);
		return $this->fetch();
	}
	
	function getPermalink() {
		$post = get_permalink($this->postID);
		return $post;
	}
	
	function getPerformances() {
		$p = new performance();
		if ( $this->id ) {	
			$p->search("`gigID` = " . (int) $this->id, "`time`");
		}
		return $p;
	}
	
	function getVenue() {
		$v = new venue();
		if ( $this->venueID ) {
			$v->get($this->venueID);
		}
		return $v;
	}
	
	function pre_insert() {
		global $options, $wpdb;
		$user = wp_get_current_user();
		$v = $this->getVenue();
		$p = $this->getPerformances();
		$p->fetch();
		
		if ( $this->tour_id == -1 ) $this->tour_id = null;
		
		$title = array();
		foreach ( $options['gig-title-show'] as $f ) {
			switch ( $f ) {
				case 'city' :
					$title[] = $v->getCity();
					break;
				case 'eventName' :
					$title[] = $this->eventName;
					break;
				case 'venue' :
					$title[] = $v->name;
					break;
				case 'date' :
					$title[] = dtcGigs::dateFormat($this->date);
					break;
				case 'country' :
					$title[] = $v->country;
					break;
			}
		}
		
		foreach ( $title  as $key => $value ) {
			if ( empty($value) ) unset($title[$key]);
		}

		$post_data = array(
			'post_author'		=> $user->ID,
			'post_title'		=> $wpdb->escape(implode(' - ', $title)),
			'post_status'		=> 'publish',
			'post_type' 		=> 'post',
			'post_category'		=> array($options['category']),
			'post_content' 		=> '',
		);
		
		if ( $options['post-filtering'] ) {
			$post_data = array_merge($post_data, array(
				'post_date'			=> $wpdb->escape($this->date . ' ' . $p->time),
				'post_modified'		=> $wpdb->escape($this->date . ' ' . $p->time),
				'post_date_gmt'		=> date('Y-m-d H:i:s', strtotime($this->date . ' ' . $p->time) - (60 * 60 * get_option('gmt_offset'))),
				'post_modified_gmt'	=> date('Y-m-d H:i:s', strtotime($this->date . ' ' . $p->time) - (60 * 60 * get_option('gmt_offset'))),
			));
		}
		$id = wp_insert_post($post_data);
		$this->postID = $id;
	}
	
	function pre_update() {
		global $options, $wpdb, $wp_version;
		$v = $this->getVenue();
		$p = $this->getPerformances();
		$p->fetch();

		if ( $this->tour_id == -1 ) $this->tour_id = null;
		
		$title = array();
		foreach ( $options['gig-title-show'] as $f ) {
			switch ( $f ) {
				case 'city' :
					$title[] = $v->getCity();
					break;
				case 'eventName' :
					$title[] = $this->eventName;
					break;
				case 'venue' :
					$title[] = $v->name;
					break;
				case 'date' :
					$title[] = dtcGigs::dateFormat($this->date);
					break;
				case 'country' :
					$title[] = $v->country;
					break;
			}
		}
		
		foreach ( $title  as $key => $value ) {
			if ( empty($value) ) unset($title[$key]);
		}

		
		$post = get_post($this->postID);
		
		if ( $options['post-filtering'] ) {
			$post->post_date = $wpdb->escape($this->date . ' ' . $p->time);
			$post->post_modified = $wpdb->escape($this->date . ' ' . $p->time);
			$post->post_date_gmt = date('Y-m-d H:i:s', strtotime($this->date . ' ' . $p->time) - (60 * 60 * get_option('gmt_offset')));
			$post->post_modified_gmt = date('Y-m-d H:i:s', strtotime($this->date . ' ' . $p->time) - (60 * 60 * get_option('gmt_offset')));
		}

		$post->post_title = $wpdb->escape(implode(' - ', $title));
		$post->post_name = "";
		$post->post_status = "publish"; // Do this to trick WordPress into resetting the GUID.
		$post->post_category = array($options['category']);

		wp_update_post($post);
		if ( (float) $wp_version < 2.3 ) {  // Hack to make it compatible with WP 2.2
			global $wpdb; 
			$wpdb->query('UPDATE ' . $wpdb->prefix . 'posts SET post_status = "publish" WHERE ID = ' . (int) $this->postID);
		}
	}
	
	function post_insert() {
		global $wp_version;
		if ( (float) $wp_version < 2.3 ) {  // Hack to make it compatible with WP 2.2
			global $wpdb; 
			$wpdb->query('UPDATE ' . $wpdb->prefix . 'posts SET post_status = "publish" WHERE ID = ' . (int) $this->postID);
		} else {
			$post = get_post($this->postID);
			$post->post_status = "publish";
			wp_update_post($post);
		}
	}

	function extraJSON() {
		$post = get_post($this->postID);
		$v = $this->getVenue();
		$p = $this->getPerformances();
		if ( function_exists("json_encode") ) {
			$fields['permalink'] = $this->getPermalink();
			$fields['venue'] = $v->getRow();
			while ( $p->fetch() ) {
				$fields['performances'][] = $p->getRow();
			}
		} else {
			$fields[] = '"permalink":"' . str_replace("\n", '\n', addslashes($this->getPermalink())) . '"';
			$ps = array();
			$fields[] = '"venue":' . $v->toJSON();
			while ( $p && $p->fetch() ) {
				$ps[] = $p->toJSON();
			}
			$fields[] = '"performances":[' . implode(', ', $ps) . ']';
		}
		return $fields;
	}
}

class performance extends dtc_gigs_baseAR {
	var $_table = TABLE_PERFORMANCES, $_name = 'id';
	var $id, $gigID = 0, $time = '20:00', $link, $shortNotes, $ages;

	var $_fields = array(
		'id',
		'gigID',
		'time',
		'link',
		'shortNotes',
		'ages',
	);
}

class tour extends dtc_gigs_baseAR {
	var $_table = TABLE_TOURS, $_name = 'id';
	var $id, $name, $notes, $pos = 0;
	var $_fields = array(
		'id',
		'name',
		'notes',
		'pos',
	);
	
	function post_insert() {
		$max = $this->wpdb->get_var('SELECT MAX(`pos`) FROM `' . $this->_table . '`') + 1;
		$this->wpdb->query('UPDATE `' . $this->_table . '` SET `pos` = ' . $max . ' WHERE `id` = ' . (int) $this->id);
	}
	
	function delete() {
		$g = $this->getGigs();
		while ( $g->fetch() ) {
			$g->tour_id = null;
			$g->save();
		}
		return parent::delete();
	}
	
	function sortTours($type = null) {
		$options = get_option('gigs-calendar');
		$type = is_null($type) ? $options['tours-sort'] : $type;
		switch ($type) {
			case 'date' :
				return $this->wpdb->get_col('
					SELECT DISTINCT
						t.id
					FROM
						' . TABLE_TOURS . ' t  
					LEFT JOIN
						' . TABLE_GIGS . ' g 
					ON 
						t.`id` = g.`tour_id`
					WHERE
						`date` is null OR `date` >= CURDATE()
					ORDER BY 
						g.`date`, `name`
				');
				break;
			case 'alpha' :
				return $this->wpdb->get_col('
					SELECT DISTINCT
						t.id
					FROM
						' . TABLE_TOURS . ' t  
					ORDER BY 
						`name`
				');
				break;
		}
	}
	
	function getGigs($slice = 'all') {
		$g = new gig();
		if ( $slice == 'past' ) {
			$g->search('`date` <= CURDATE() AND `tour_id` = ' . (int) $this->id, '`date`');
		} elseif ( $slice == 'future' ) {
			$g->search('`date` >= CURDATE() AND `tour_id` = ' . (int) $this->id, '`date`');
		} else {
			$g->search('`tour_id` = ' . (int) $this->id, '`date`');
		}
		return $g;
	}
	
	function extraJSON(){
		$fields = array();
		$g = $this->getGigs();
		$gs = array();
		if ( function_exists("json_encode") ) {
			while ( $g->fetch() ) {
				$gs[] = $g->getRow();
			}
			$fields['gigs'] = $gs;
		} else {
			while ( $g->fetch() ) {
				$gs[] = $g->toJSON();
			}
			$fields[] = '"gigs":[' . implode(',', $gs) . ']';
		}
		return $fields;
	}
}

?>

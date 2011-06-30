<?php

require_once 'ajaxSetup.php';
$pageTarget = $folder . 'venues.ajax.php';

switch ($_POST['action']) {
	case 'load':
		//$categories = $wpdb->get_results('SELECT * FROM `' . TABLE_CATS . '` ORDER BY `' . $options['categorySortColumn'] .'` ' . $options['categorySortDirection']);
		$v = new venue();
		$v->search('deleted = 0', 'name');
		?>
			<div class="clickable" id="venue-add-trigger">
				<img class="icon" src="<?php echo $folder; ?>images/add.png" /> <?php _e('Add a new venue', $gcd) ?>
			</div>
			<div id="venue-add-form" style="<?php if ( !isset($_GET['new']) ) echo 'display: none;' ?>">
				<form id="new-venue-form" class="new-item" method="post" action="<?php echo $pageTarget; ?>">
					<table>
						<tbody>
							<tr><td><label for="new-name"><?php _e('Name:', $gcd) ?></label></td><td><input type="text" class="name wide" name="name" id="new-name" /></td></tr>
							<tr><td valign="top"><label for="new-address"><?php _e('Address:', $gcd) ?></label></td><td><textarea class="address wide" name="address" id="new-address"></textarea></td></tr>
							<tr><td><label for="new-city"><?php _e('City:', $gcd) ?></label></td><td><input type="text" class="city wide" name="city" id="new-city" /></td></tr>
							<tr><td><label for="new-state"><?php _e('State/Province:', $gcd) ?></label></td><td><input type="text" class="state wide" name="state" id="new-state" /></td></tr>
							<tr><td><label for="new-country"><?php _e('Country:', $gcd) ?></label></td><td><input type="text" class="country wide" name="country" id="new-country" /></td></tr>
							<tr><td><label for="new-postalCode"><?php _e('Postal Code:', $gcd) ?></label></td><td><input type="text" class="postalCode" name="postalCode" id="new-postalCode" /></td></tr>
							<tr><td><label for="new-customMap"><?php _e('Custom location for Google Maps <abbr title="Will override the automatic address unless you leave it blank.">(?)</abbr>', $gcd) ?>:</label></td><td><input type="text" class="customMap wide" name="customMap" id="new-customMap" /></td></tr>
							<tr><td><label for="new-contact"><?php _e('Primary Contact:', $gcd) ?></label></td><td><input type="text" class="contact wide" name="contact" id="new-contact" /></td></tr>
							<tr><td><label for="new-phone"><?php _e('Phone:', $gcd) ?></label></td><td><input type="text" class="phone wide" name="phone" id="new-phone" /></td></tr>
							<tr><td><label for="new-email"><?php _e('Email:', $gcd) ?></label></td><td><input type="text" class="email wide" name="email" id="new-email" /></td></tr>
							<tr><td><label for="new-link"><?php _e('Homepage:', $gcd) ?></label></td><td><input type="text" class="link wide" name="link" id="new-link" /></td></tr>
						</tbody>
					</table>
					<div>
						<label for="new-private"><?php _e('Hide information about this venue from the public:', $gcd) ?> <input type="checkbox" value="1" name="private" id="new-private" /></label>
					</div>
					<div>
						<?php _e('Description/Other notes:', $gcd) ?><br />
						<textarea class="notes" name="notes" rows="7" cols="80"></textarea>
					</div>
					<div>
						<input type="submit" class="button" name="" value="<?php _e('Add Venue', $gcd) ?>" />
						<input type="reset" class="button cancel" name="" value="<?php _e('Cancel', $gcd) ?>" id="new-venue-reset" />
						<input type="hidden" name="action" value="add" />
						<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
					</div>
				</form>
			</div>
			
			<table id="venue-list" class="venues widefat">
				<thead>
					<tr>
						<th style="text-align: center;" scope="col"><?php _e('ID', $gcd) ?></th>
						<th scope="col"><?php _e('Name', $gcd) ?></th>
						<th scope="col"><?php _e('City', $gcd) ?></th>
						<th scope="col"><?php _e('Phone', $gcd) ?></th>
						<th scope="col"><?php _e('Email', $gcd) ?></th>
						<th style="text-align: center" scope="col"><?php _e('Actions', $gcd) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( $v->fetch() ) : ?>
						<tr id="venue-<?php echo $v->id; ?>" class="venue-<?php echo $v->id; ?> <?php echo ++$count % 2 ? "alternate" : "";?>">
							<th style="text-align: center;" scope="row"><?php echo $v->id; ?></th>
							<td class="name"><?php echo $v->name; ?></td>
							<td class="city"><?php echo $v->city . (!empty($v->state) ? ', ' . $v->state :  '') ?></td>
							<td class="phone"><?php echo $v->phone ?></td>
							<td class="email"><?php echo !empty($v->email) ? '<a href="mailto:' . $v->email . '">' . $v->email . '</a>' : '' ?></td>
							<td class="actions" style="text-align: center; position: relative;">
								<div style="position: relative">
									<img alt="<?php _e('Edit', $gcd) ?>" title="<?php _e('Edit', $gcd) ?>" class="clickable edit" src="<?php echo $folder; ?>images/page_white_edit.png" />
									<a target="_blank" href="<?php echo $v->getMapLink(); ?>"><img alt="<?php _e('Map', $gcd) ?>" title="<?php _e('Map', $gcd) ?>" class="clickable map" src="<?php echo $folder; ?>images/world.png" /></a>
									<?php if ( !empty($v->link) ) : ?>
										<a target="_blank" href="<?php echo $v->link ?>"><img alt="<?php _e('Homepage', $gcd) ?>" title="<?php _e('Homepage', $gcd) ?>" class="clickable homepage" src="<?php echo $folder ?>images/link.png" /></a>
									<?php endif ?>
									<img alt="<?php _e('Delete', $gcd) ?>" title="<?php _e('Delete', $gcd) ?>" class="clickable delete" src="<?php echo $folder; ?>images/delete.png" />
								</div>
							</td>
						</tr>
						<tr id="venue-panel-<?php echo $v->id; ?>" class="venue-<?php echo $v->id; ?> panel <?php echo $count % 2 ? "alternate" : "";?>">
							<td style="background-color: white"></td>
							<td class="panel" colspan="5"></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
				
			<script type="text/javascript">
			
				(function($){
					//init = function() {
					$("#venue-add-trigger").click(function(){
						$("#venue-add-form:hidden").slideDown(300, function(){
							$("#new-name").focus();
						});
					});
					
					$("#new-venue-reset").click(function(){
						$("#venue-add-form").slideUp(300);
					});
					
					$("#new-venue-form").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							venues = $("table#venue-list tbody tr:not(.panel) td.name");
							inserted = false;
							for ( i = 0; i < venues.length; i++ ) {
								if ( json.venue.name.toLowerCase() < venues.eq(i).html().toLowerCase() ) {
									v = venues.eq(i);
									$.post(pageTarget, {
										nonce:nonce,
										action:'getRow',
										id:json.venue.id
									}, function(rsp){
										v.parents("tr").before(rsp);
										resetTableColors("table#venue-list");
										setupEvents();
									});
									inserted = true;
									break;
								}
							}
							if ( !inserted ) {
								$.post(pageTarget, {
									nonce:nonce,
									action:'getRow',
									id:json.venue.id
								}, function(rsp){
									$("table#venue-list tbody").append(rsp);
									resetTableColors("table#venue-list");
									setupEvents();
								});
							}
							$("#new-venue-reset").click();
						}
					});
					
					setupEvents = function() {
						$("img.delete").unbind("click");
						$("img.delete").click(function(){
							id = $(this).parents("tr").attr("id").split("-")[1];
							$.post(pageTarget, {
								nonce:nonce,
								action:'delete',
								id:id
							});
							$(this).parents("tr").next().remove();
							$(this).parents("tr").remove();
							resetTableColors("table#venue-list");
						});
						
						$("img.edit").unbind("click");
						$("img.edit").click(function(){
							row = $(this).parents("tr");
							id = row.attr("id").split("-")[1];
							row.next().children("td.panel").load(pageTarget, {
								nonce:nonce,
								action:"edit",
								id:id
							}, function(){
								row.next().css("display", "table-row");
							});
						});
					}
					
					setupEvents();
				}(jQuery));
				
			
			
			</script>
		<?php
		break;
	case 'add':
		$v = new venue();
		$args = $_POST;
		unset($args['nonce'],$args['action']);
		foreach ( $args as $key => $value ) {
			$v->$key = $value;
		}
		if ( $v->save() ) {
			echo '{"success":true, "venue":' . $v->toJSON() . '}';
		} else {
			echo '{"success":false}';
		}
		break;
	case 'map':
		//$v = new venue($_GET['id']);
	case 'getRow':
		$v = new venue($_POST['id']);
		?>
			<tr id="venue-<?php echo $v->id; ?>" class="song-<?php echo $v->id; ?>">
				<th style="text-align: center;" scope="row"><?php echo $v->id; ?></th>
				<td class="name"><?php echo $v->name; ?></td>
				<td class="city"><?php echo $v->city . (!empty($v->state) ? ', ' . $v->state :  '') ?></td>
				<td class="phone"><?php echo $v->phone ?></td>
				<td class="email"><?php echo !empty($v->email) ? '<a href="mailto:' . $v->email . '">' . $v->email . '</a>' : '' ?></td>
				<td class="actions" style="text-align: center; position: relative;">
					<div style="position: relative">
						<img alt="<?php _e('Edit', $gcd) ?>" title="<?php _e('Edit', $gcd) ?>" class="clickable edit" src="<?php echo $folder; ?>images/page_white_edit.png" />
						<a target="_blank" href="http://maps.google.com/?q=<?php echo urlencode($v->getAddress(true)); ?>"><img alt="<?php _e('Map', $gcd) ?>" title="<?php _e('Map', $gcd) ?>" class="clickable map" src="<?php echo $folder; ?>images/world.png" /></a>
						<?php if ( !empty($v->link) ) : ?>
							<a target="_blank" href="<?php echo $v->link ?>"><img alt="<?php _e('Homepage', $gcd) ?>" title="<?php _e('Homepage', $gcd) ?>" class="clickable homepage" src="<?php echo $folder ?>images/link.png" /></a>
						<?php endif ?>
						<img alt="<?php _e('Delete', $gcd) ?>" title="<?php _e('Delete', $gcd) ?>" class="clickable delete" src="<?php echo $folder; ?>images/delete.png" />
					</div>
				</td>
			</tr>
			<tr id="venue-panel-<?php echo $v->id; ?>" class="song-<?php echo $v->id; ?> panel <?php echo $count % 2 ? "alternate" : "";?>">
				<td style="background-color: white"></td>
				<td class="panel" colspan="5"></td>
			</tr>
		<?php
		break;
	
	case 'delete':
		$v = new venue($_POST['id']);
		$result = $v->delete();
		echo '{"success": ' . ($result ? 'true' : 'false') . ',"action":"delete"' . ($result ? '' : ',"error":"db"') . '}';
		break;
		
	case 'edit':
		$v = new venue($_POST['id']);
		?>
			<form id="edit-venue-<?php echo $v->id ?>" class="edit-item venue-<?php echo $v->id ?>" method="post" action="<?php echo $pageTarget; ?>">
				<table>
					<tbody>
						<tr><td><label for="edit-name-<?php echo $v->id ?>"><?php _e('Name:', $gcd) ?></label></td><td><input type="text" class="name wide" name="name" id="edit-name-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->name) ?>" /></td></tr>
						<tr><td valign="top"><label for="edit-address-<?php echo $v->id ?>"><?php _e('Address:', $gcd) ?></label></td><td><textarea class="address wide" name="address" id="edit-address-<?php echo $v->id ?>"><?php echo dtcGigs::escapeForInput($v->address) ?></textarea></td></tr>
						<tr><td><label for="edit-city-<?php echo $v->id ?>"><?php _e('City:', $gcd) ?></label></td><td><input type="text" class="city wide" name="city" id="edit-city-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->city) ?>" /></td></tr>
						<tr><td><label for="edit-state-<?php echo $v->id ?>"><?php _e('State/Province:', $gcd) ?></label></td><td><input type="text" class="state wide" name="state" id="edit-state-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->state) ?>" /></td></tr>
						<tr><td><label for="edit-country-<?php echo $v->id ?>"><?php _e('Country:', $gcd) ?></label></td><td><input type="text" class="country wide" name="country" id="edit-country-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->country) ?>" /></td></tr>
						<tr><td><label for="edit-postalCode-<?php echo $v->id ?>"><?php _e('Postal Code:', $gcd) ?></label></td><td><input type="text" class="postalCode" name="postalCode" id="edit-postalCode-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->postalCode) ?>" /></td></tr>
						<tr><td><label for="edit-customMap-<?php echo $v->id ?>"><?php _e('Custom location for Google Maps <abbr title="Will override the automatic address unless you leave it blank.">(?)</abbr>', $gcd) ?>:</label></td><td><input type="text" class="customMap wide" name="customMap" id="edit-customMap-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->customMap) ?>" /></td></tr>
						<tr><td><label for="edit-contact-<?php echo $v->id ?>"><?php _e('Primary Contact:', $gcd) ?></label></td><td><input type="text" class="contact wide" name="contact" id="edit-contact-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->contact) ?>" /></td></tr>
						<tr><td><label for="edit-phone-<?php echo $v->id ?>"><?php _e('Phone:', $gcd) ?></label></td><td><input type="text" class="phone wide" name="phone" id="edit-phone-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->phone) ?>" /></td></tr>
						<tr><td><label for="edit-email-<?php echo $v->id ?>"><?php _e('Email:', $gcd) ?></label></td><td><input type="text" class="email wide" name="email" id="edit-email-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->email) ?>" /></td></tr>
						<tr><td><label for="edit-link-<?php echo $v->id ?>"><?php _e('Homepage:', $gcd) ?></label></td><td><input type="text" class="link wide" name="link" id="edit-link-<?php echo $v->id ?>" value="<?php echo dtcGigs::escapeForInput($v->link) ?>" /></td></tr>
					</tbody>
				</table>
				<div>
					<label for="edit-private-<?php echo $v->id ?>"><?php _e('Hide information about this venue from the public:', $gcd) ?> <input <?php if ( $v->private ) echo 'checked="checked"' ?> type="checkbox" value="1" name="private" id="edit-private-<?php echo $v->id ?>" /></label>
				</div>
				<div>
					<?php _e('Description/Other notes:', $gcd) ?><br />
					<textarea class="notes" name="notes" rows="7" cols="80"><?php echo dtcGigs::escapeForInput($v->notes) ?></textarea>
				</div>
				<div>
					<input type="submit" class="button" name="" value="<?php _e('Save Changes', $gcd); ?>" />
					<input type="reset" class="button cancel" name="" value="<?php _e('Cancel', $gcd); ?>" id="edit-venue-reset-<?php echo $v->id ?>" />
					<input type="hidden" name="id" value="<?php echo $v->id ?>" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
				</div>
			</form>
			
			<script type="text/javascript">
				(function($){
					$("#edit-venue-reset-<?php echo $v->id ?>").click(function(){
						$(this).parents("tr.panel").hide();
					});
					
					$("#edit-venue-<?php echo $v->id ?>").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							$("#venue-panel-" + json.venue.id).hide();
							row = $("#venue-" + json.venue.id);
							row.children("td.name").html(json.venue.name);
							row.children("td.phone").html(json.venue.phone);
							
							city = json.venue.city;
							if ( json.venue.state != "" ) {
								if ( city != "" ) {
									city += ", ";
								}
								city += json.venue.state;
							}
							row.children("td.city").html(city);
							
							if ( json.venue.email != "" ) {
								row.children("td.email").html("<a href=\"mailto:" + json.venue.email + "\">" + json.venue.email + "</a>");
							}
						}
					});
				}(jQuery));
			</script>
		<?php
		break;
		
	case 'save':
		$v = new venue($_POST['id']);
		$args = $_POST;
		unset($args['nonce'],$args['action'],$args['id']);
		if ( !isset($_POST['private']) ) $args['private'] = 0;
		foreach ( $args as $key => $value ) {
			$v->$key = $value;
		}
		if ( $v->save() ) {
			echo '{"success":true, "venue":' . $v->toJSON() . '}';
		} else {
			echo '{"success":false}';
		}
		break;
}



<?php

require_once 'ajaxSetup.php';
$pageTarget = $folder . 'archive.ajax.php';

switch ($_POST['action']) {
	case 'load':
		$v = new venue();
		$g = new gig();
		$p = new performance();
		$g->search("date <= CURDATE()", 'date DESC');
		?>
			<table id="gig-list" class="gigs widefat">
				<thead>
					<tr>
						<th style="text-align: center;" scope="col"><?php _e('ID', $gcd) ?></th>
						<th scope="col"><?php _e('Venue', $gcd) ?></th>
						<th scope="col"><?php _e('City', $gcd) ?></th>
						<th scope="col"><?php _e('Date', $gcd) ?></th>
						<th scope="col"><?php _e('Time', $gcd) ?></th>
						<th style="text-align: center" scope="col"><?php _e('Actions', $gcd) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( $g->fetch() ) : $v = $g->getVenue(); $p = $g->getPerformances(); $post = get_post($g->postID);?>
						<tr id="gig-<?php echo $g->id; ?>" class="gig gig-<?php echo $g->id; ?> <?php echo ++$count % 2 ? "alternate" : "";?>">
							<th style="text-align: center;" scope="row"><?php echo $g->id; ?></th>
							<td class="venue"><?php echo $v->name; ?></td>
							<td class="city"><?php echo $v->city . (!empty($v->state) ? ', ' . $v->state :  '') ?></td>
							<td class="date"><?php echo $g->date ?></td>
							<td class="time">
								<?php if ( $p ) : while ( $p->fetch() ) : ?>
									<span class="time"><?php echo dtcGigs::timeFormat($p->time) ?></span>
								<?php endwhile; endif; ?>
							</td>
							<td class="actions" style="text-align: center; position: relative;">
								<div style="position: relative">
									<a target="_blank" class="guid" href="<?php echo $g->getPermalink(); ?>"><img alt="<?php _e('View Gig', $gcd) ?>" title="<?php _e('View Gig', $gcd) ?>" class="clickable view" src="<?php echo $folder; ?>images/page_white_magnify.png" /></a>
									<img alt="<?php _e('Edit', $gcd) ?>" title="<?php _e('Edit', $gcd) ?>" class="clickable edit" src="<?php echo $folder; ?>images/page_white_edit.png" />
									<?php if ( $v ) : ?>
										<a target="_blank" href="<?php echo $v->getMapLink(); ?>"><img alt="<?php _e('Map', $gcd) ?>" title="<?php _e('Map', $gcd) ?>" class="clickable map" src="<?php echo $folder; ?>images/world.png" /></a>
									<?php endif ?>
									<a target="_blank" href="<?php echo get_option('siteurl') ?>/wp-admin/post.php?action=edit&post=<?php echo $post->ID; ?>">
										<img alt="<?php _e('Edit WordPress Post', $gcd) ?>" title="<?php _e('Edit WordPress Post', $gcd) ?>" class="clickable edit-post" src="<?php echo $folder; ?>images/page_edit.png" />
									</a>
									<img alt="<?php _e('Delete', $gcd) ?>" title="<?php _e('Delete', $gcd) ?>" class="clickable delete" src="<?php echo $folder; ?>images/delete.png" />
								</div>
							</td>
						</tr>
						<tr id="gig-panel-<?php echo $g->id; ?>" class="gig gig-<?php echo $g->id; ?> panel <?php echo $count % 2 ? "alternate" : "";?>">
							<td style="background-color: white"></td>
							<td class="panel" colspan="5"></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
				
			<script type="text/javascript">
			
				(function($){
					$("#add-venue").click(function(){
						gigs_page_load("venues", "new=1");
					});
					
					$("#gig-add-trigger").click(function(){
						$("#gig-add-form:hidden").slideDown(300, function(){
							$("#new-name").focus();
						});
					});
					
					$("#new-gig-reset").click(function(){
						$("#gig-add-form table.performance[id!=performance-c1]").parents("tr").remove();
						$("#gig-add-form").slideUp(300);
					});
					
					$("#new-date").datepicker({dateFormat:"yy-mm-dd"});
					$(".date-field").datepicker({dateFormat:"yy-mm-dd"});
					
					
					$("#new-gig").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							gigs = $("table#gig-list tbody tr:not(.panel) td.date");
							inserted = false;
							for ( i = 0; i < gigs.length; i++ ) {
								if ( json.gig.date < gigs.eq(i).html() ) {
									g = gigs.eq(i);
									$.post(pageTarget, {
										nonce:nonce,
										action:'getRow',
										id:json.gig.id
									}, function(rsp){
										g.parents("tr").before(rsp);
										resetTableColors("table#gig-list");
									});
									inserted = true;
									break;
								}
							}
							if ( !inserted ) {
								$.post(pageTarget, {
									nonce:nonce,
									action:'getRow',
									id:json.gig.id
								}, function(rsp){
									$("table#gig-list tbody").append(rsp);
									resetTableColors("table#gig-list");
								});
							}
							$("#new-gig-reset").click();
						}

					});
					
					setupPerformanceRemoval = function() {
						$("div.delete-performance img").unbind("click");
						$("div.delete-performance img").click(function(){
							id = $(this).parents("tr").eq(0).find("input.performanceID").val();
							if ( id == "" ) {
								$(this).parents("tr").eq(1).remove();
							} else {
								if ( confirm("<?php _e('Are you sure you want to remove this performance?', $gcd) ?>") ) {
									$(this).parents("form").children(".extra-inputs").append('<input type="hidden" name="delete[]" value="' + id + '" />');
									$(this).parents("tr").eq(1).remove();
								}
							}
						});
					}
					
					setupPerformances = function() {
						$(".add-performance").unbind("click");
						$(".add-performance").click(function(){
							td = $(this).parents("tr").eq(0).before("<tr><td colspan='2'><div class='loading'><img src='<?php echo $folder; ?>images/ajax-loader.gif' alt='' /></div></td></tr>").prev().children("td").load(pageTarget,{
								nonce:nonce,
								action:"performance-form",
								count:$(this).parents("table").eq(0).find("table.performance").length + 1
							}, setupPerformanceRemoval);
						});
						setupPerformanceRemoval();

					}
					
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
							resetTableColors("table#gig-list");
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
						
						setupPerformances();
					}
					
					setupEvents();

				}(jQuery));
				
			
			
			</script>
		<?php
		break;
	case 'delete':
		$g = new gig($_POST['id']);
		$result = $g->delete();
		echo '{"success": ' . ($result ? 'true' : 'false') . ',"action":"delete"' . ($result ? '' : ',"error":"db"') . '}';
		break;
	case 'edit':
		$g = new gig($_POST['id']);
		$p = $g->getPerformances();
		$v = $g->getVenue();
		?>
			<form id="edit-gig-<?php echo $g->id ?>" class="edit-item" method="post" action="<?php echo $pageTarget; ?>">
				<table>
					<tbody>
						<tr><td colspan="2">
							<h3 class="no-margin"><?php _e('Gig Information', $gcd) ?></h3>
							<div class="instructions"><?php _e('A "gig" is unique to a date at a venue.', $gcd) ?></div>
						</td></tr>
						<tr class="venue">
							<td><label for="new-venue"><?php _e('Venue:', $gcd) ?></label></td>
							<td>
								<?php echo $v->name . ' - ' . $v->getCity() ?>
							</td>
						</tr>
						<tr class="date">
							<td><label for="new-date"><?php _e('Date:', $gcd) ?></label></td>
							<td><input type="text" class="date wide" name="date" id="edit-date-<?php echo $g->id ?>" value="<?php echo $g->date ?>" /></td>
						</tr>
						<tr class="eventName">
							<td><label for="edit-eventName-<?php echo $g->id ?>"><?php _e('Event Name:', $gcd) ?></label></td>
							<td><input type="text" class="eventName wide" name="eventName" id="edit-eventName-<?php echo $g->id ?>" value="<?php echo $g->eventName ?>" /></td>
						</tr>
						<tr class="tour">
							<td><label for="edit-tour_id-<?php echo $g->id ?>"><?php _e('Tour:', $gcd) ?></label></td>
							<td class="tour">
								<select class="tour_id" name="tour_id" id="edit-tour_id-<?php echo $g->id ?>">
									<option value="-1"><?php _e("--None--", $gcd); ?></option>
									<?php $t = new tour(); $t->search(null, '`name`'); ?>
									<?php while ( $t->fetch() ) : ?>
										<option value="<?php echo $t->id; ?>" <?php if ( $g->tour_id == $t->id ) echo 'selected="selected"'; ?>><?php echo $t->name; ?></option>
									<?php endwhile; ?>
								</select>
							</td>
						</tr>
						<?php if ( function_exists('wp_set_post_tags') ) : ?>
							<tr class="tags">
								<td><label for="edit-tags-<?php echo $g->id ?>"><?php _e('Post Tags:', $gcd) ?></label></td>
								<td><input type="text" class="tags wide" name="tags" id="edit-tags-<?php echo $g->id ?>" value="<?php echo dtcGigs::escapeForInput(implode(', ', $g->getTags())) ?>" /></td>
							</tr>
						<?php endif; ?>
						<tr class="notes"><td colspan="2">
							<?php _e('Description/Other notes:', $gcd) ?><br />
							<textarea class="notes" name="notes" rows="8" cols="80"><?php echo $g->notes ?></textarea>
						</td></tr>
						
						<?php if ( $wp_version >= 2.5 ) : ?>
							<?php 
								$metadata = get_post_custom($g->postID);
								dtcGigs::extraFields(apply_filters('gigCal_gigs_extraFields', array()), $metadata, $g->id);
							?>
							<tr><td colspan="2">
								<div id="postcustom-<?php echo $g->id ?>" class="postcustom">
									<h3 title="<?php _e('Click to expand', $gcd); ?>" class="clickable"><?php _e('Custom Fields') ?> <img class="inline-icon" src="<?php echo $folder ?>images/plus.gif" alt="<?php _e('Expand', $gcd); ?>"> <img class="inline-icon" style="display:none;" src="<?php echo $folder ?>images/minus.gif" alt="<?php _e('Collapse', $gcd); ?>"></h3>
									<div class="inside">
										<div id="postcustomstuff-<?php echo $g->id ?>">
										<table cellpadding="3">
											<thead>
												<tr>
													<th><?php _e('Key', $gcd) ?></th>
													<th><?php _e('Value', $gcd) ?></th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												<?php
													$metadata = get_post_custom($g->postID);
													foreach ( $metadata as $key => $values ) {
														foreach ( $values as $index => $value ) {
															if ( substr($key, 0, 1) != '_' ) {
																?><tr>
																	<td><input value="<?php echo addslashes($key) ?>" name="custom-key[]" /></td>
																	<td>
																		<textarea rows="1" name="custom-value[]"><?php echo htmlspecialchars($value) ?></textarea>
																		<textarea style="display:none;" rows="1" name="old-custom-value[]"><?php echo htmlspecialchars($value) ?></textarea>
																		<input class="delete" type="hidden" name="deletecustom[]" value="0" />
																	</td>
																	<td><img alt="<?php _e('Remove Custom Field', $gcd) ?>" title="<?php _e('Remove Custom Field', $gcd) ?>" class="clickable delete-custom" src="<?php echo $folder; ?>images/delete.png" /></td>
																</tr><?php
															}
														}
													}
												?>
											</tbody>
										</table>
										<div class="custom-ajax-response"></div>
										</div>
										<p>
											<span class="clickable add-custom-field">
												<img class="inline-icon" src="<?php echo $folder ?>images/add.png" alt="" />
												<?php _e('Add a new custom field'); ?>
											</span>
										</p>
										<p><?php _e('Custom fields can be used to add extra metadata to a gig that you can use in a special template.'); ?></p>
									</div>
								</div>
							</td></tr>
						<?php endif; ?>
						
						<tr><td colspan="2">
							<h3 class="no-margin"><?php _e('Performances', $gcd) ?></h3>
							<div class="instructions"><?php _e('You can add as many performances as<br />you are giving at a venue on the given date.', $gcd) ?></div>
						</td></tr>
						<?php while ( $p->fetch() ) : ?>
							<tr><td colspan="2">
								<?php pForm($p->id, false) ?>
							</td></tr>
						<?php endwhile ?>
						<tr><td colspan="2">
							<div class="clickable add-performance"><img class="icon" src="<?php echo $folder; ?>images/add.png" /> <?php _e('Add another performance', $gcd) ?></div>
						</td></tr>

					</tbody>
				</table>
				<div class="extra-inputs">
					<input type="submit" class="button" name="" value="<?php _e('Save Gig', $gcd); ?>" />
					<input type="reset" class="button cancel" name="" value="<?php _e('Cancel', $gcd); ?>" id="edit-gig-reset-<?php echo $g->id ?>" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="id" value="<?php echo $g->id ?>" />
					<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
				</div>
			</form>
			
			<script type="text/javascript">
				(function($){
					(function(form) { // Function to let a plugin add extra JS code to process the form.
						<?php do_action('gigCal_gigs_formExtraJS'); ?>
					}($("#edit-gig-<?php echo $g->id ?>")));

					$("#edit-gig-<?php echo $g->id ?> .add-custom-field").click(function(){
						$("#edit-gig-<?php echo $g->id ?> .postcustom table").append('<tr><td><input value="" name="custom-key[]" /></td><td><textarea rows="1" name="custom-value[]"></textarea><textarea style="display:none;" rows="1" name="old-custom-value[]"></textarea><input class="delete" type="hidden" name="deletecustom[]" value="0" /></td><td><img class="delete-custom" alt="<?php _e('Remove Custom Field', $gcd) ?>" title="<?php _e('Remove Custom Field', $gcd) ?>" class="clickable" src="<?php echo $folder; ?>images/delete.png" /></td></tr>');
						
						$("#edit-gig-<?php echo $g->id ?> .delete-custom").unbind("click");
						$("#edit-gig-<?php echo $g->id ?> .delete-custom").click(function(){
							$(this).parents("tr").eq(0).remove();
						});
					});
					
					$("#edit-gig-<?php echo $g->id ?> .delete-custom").click(function(){
						$(this).parents("td").eq(0).siblings().children("input.delete").val(1);
						$(this).parents("tr").eq(0).hide();
					});
					
					$("#postcustom-<?php echo $g->id ?> h3.clickable").click(function(){
						$(this).next().toggle();
						$(this).children(".inline-icon").toggle();
					});


				
					$("#edit-gig-<?php echo $g->id ?> .date").datepicker({dateFormat:"yy-mm-dd"});
					$(".date-field").datepicker({dateFormat:"yy-mm-dd"});
					setupPerformances();
					
					$("#edit-gig-reset-<?php echo $g->id ?>").click(function(){
						$(this).parents("tr.panel").hide();
					});
					
					$("#edit-gig-<?php echo $g->id ?>").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							row = $("#gig-panel-" + json.gig.id).hide().prev();
							row.children("td.date").html(json.gig.date);
							row.find("a.guid").attr("href", json.gig.permalink);
							times = row.children("td.time");
							times.html("");
							for ( i = 0; i < json.gig.performances.length; i++ ) {
								time = json.gig.performances[i].time.split(":");
								<?php if ( $options['time-12h'] ) : ?>
									if ( time[0] == 0 ) {
										time[0] = 12;
										time[2] = 'am';
									} else if ( time[0] == 12 ) {
										time[2] = 'pm';
									} else if ( time[0] < 12 ) {
										time[2] = 'am';
									} else if ( time[0] > 12 ) {
										time[0] = time[0] - 12;
										time[2] = 'pm';
									}
									times.append('<span class="time">' + time[0] + ':' + time[1] + time[2] + '</span> ');
								<?php else : ?>
									times.append('<span class="time">' + time[0] + ':' + time[1] + '</span> ');
								<?php endif; ?>
							}
							
						}
					});
				}(jQuery));
			</script>
		<?php
		break;
		
	case 'save':
		$g = new gig($_POST['id']);
		$g->date = $_POST['date'];
		$g->notes = $_POST['notes'];
		$g->setTags($_POST['tags']);
		$g->eventName = $_POST['eventName'];

		if ( isset($_POST['performanceID']) ) {
			foreach ( $_POST['performanceID'] as $key => $pid ) {
				$p = new performance((empty($pid) ? null : $pid));
				$p->gigID = $g->id;
				$p->link = $_POST['link'][$key];
				$p->shortNotes = $_POST['shortNotes'][$key];
				$p->ages = (empty($_POST['ages-custom'][$key]) ? $_POST['ages'][$key] : $_POST['ages-custom'][$key]);
				$p->time = date("H:i:s", strtotime($_POST['hour'][$key] . ':' . $_POST['minute'][$key] . ' ' . $_POST['meridiem'][$key]));
				$p->save();
			}
		}
		
		$p = new performance();
		if ( isset($_POST['delete']) ) {
			foreach ( $_POST['delete'] as $pid ) {
				$p->get($pid);
				$p->delete();
			}
		}
		
		$g->save();
		
		foreach ( $_POST['deletecustom'] as $key => $delete ) {
			if ( $delete ) {
				delete_post_meta($g->postID, $_POST['custom-key'][$key], $_POST['old-custom-value'][$key]);
			}
		}
		
		foreach ( $_POST['custom-key'] as $key => $field ) {
				if ( is_array($_POST['custom-value'][$key]) ) {
					foreach ( $_POST['custom-value'][$key] as $k => $v ) {
						if ( $v === "" ) {
							unset($_POST['custom-value'][$key][$k]);
						}
					}
				}
				if ( empty($_POST['custom-value'][$key]) && empty($_POST['old-custom-value'][$key]) ) {
					// If both value fields are empty, just ignore this field.
					continue;
				} elseif ( $_POST['custom-value'][$key] === "" ) {
					// If the old value is not empty, but the new one is, delete this sucker.
					delete_post_meta($g->postID, $_POST['custom-key'][$key], $_POST['old-custom-value'][$key]);
				} elseif ( $_POST['old-custom-value'][$key] === "" ) {
					// If the old value is empty, but the new one is not, create a new custom field.
					add_post_meta($g->postID, $_POST['custom-key'][$key], $_POST['custom-value'][$key]);
				} elseif ( $_POST['old-custom-value'][$key] != $_POST['custom-value'][$key] && $_POST['old-custom-value'][$key] != serialize($_POST['custom-value'][$key]) ) {
					// If the old and new values are not equal, update the custom field.
					update_post_meta($g->postID, $_POST['custom-key'][$key], $_POST['custom-value'][$key], unserialize($_POST['old-custom-value'][$key]));
				}
		}

		echo '{"success":true, "gig":' . $g->toJSON() . '}';
		break;
		
	case 'performance-form':
		if ( isset($_POST['id']) ) {
			pForm($_POST['id'], false);
		} else {
			pForm((int) $_POST['count'], true);
		}
		break;
}


function pForm($id, $new = true, $remove = true) {
	global $folder, $gcd, $options;
	$p = new performance();
	if ( $new ) {
		$count = 'c' . $id;
	} else {
		$p->get($id);
		$count = 'id' . $id;
	}
	
	$ages = $options['ages-list'];
	
	$time = explode(":", $p->time);
	$minutes = $time[1];
	
	if ( $options['time-12h'] ) {
		if ( $time[0] == 0 ) {
			$hour = "12";
			$meridiem = "AM";
		} elseif ( $time[0] == 12 ) {
			$hour = "12";
			$meridiem = "PM";
		} elseif ( $time[0] < 12 ) {
			$hour = $time[0];
			$meridiem = "AM";
		} else {
			$hour = $time[0] - 12;
			$meridiem = "PM";
		}
	} else { 
		$hour = $time[0];
	}
	?>
		<table id="performance-<?php echo $count ?>" class="performance performance-<?php echo $count ?> <?php if ( $new ) echo 'new' ?>"><tbody>
			<tr>
				<td>
					<label>
						<input type="hidden" class="performanceID" name="performanceID[]" value="<?php echo $p->id; ?>" />
						<?php _e('Performance Time:', $gcd) ?>
					</label>
				</td><td>
					<div>
						<select name="hour[]">
							<?php for ( $i = 1; $i <= ($options['time-12h'] ? 12 : 23); $i++ ) : ?>
								<option <?php if ( $i == $hour ) echo 'selected="selected"' ?>><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						<select name="minute[]">
							<option <?php if ( "00" == $minutes ) echo 'selected="selected"' ?>>00</option>
							<option <?php if ( "15" == $minutes ) echo 'selected="selected"' ?>>15</option>
							<option <?php if ( "30" == $minutes ) echo 'selected="selected"' ?>>30</option>
							<option <?php if ( "45" == $minutes ) echo 'selected="selected"' ?>>45</option>
							<option value="00">--</option>
							<?php for ( $i = 0; $i <= 59; $i++ ) : ?>
								<option <?php if ( !in_array($i, array("00", "15", "30", "45")) && $i == $minutes ) echo 'selected="selected"' ?>><?php echo str_pad($i, 2, STR_PAD_LEFT, '0') ?></option>
							<?php endfor; ?>
						</select>
						<?php if ( $options['time-12h'] ) : ?>
							<select name="meridiem[]">
								<option <?php if ( 'AM' == $meridiem ) echo 'selected="selected"' ?>><?php _e('AM', $gcd) ?></option>
								<option <?php if ( 'PM' == $meridiem ) echo 'selected="selected"' ?>><?php _e('PM', $gcd) ?></option>
							</select>
						<?php else : ?>
							<input type="hidden" name="meridiem[]" value="" />
						<?php endif; ?>
						<?php if ( $remove ) : ?>
							<div class="delete-performance"><img alt="<?php _e('Remove Performance', $gcd) ?>" title="<?php _e('Remove Performance', $gcd) ?>" class="clickable" src="<?php echo $folder; ?>images/delete.png" /></div>
						<?php endif ?>
					</div>
				</td>
			</tr><tr>
				<td>
					<label for="performance-link[]"><?php _e('External Link (for tickets):', $gcd) ?></label>
				</td><td>
					<input type="text" class="link wide" name="link[]" id="performance-link[]" value="<?php echo $p->link ?>" />
				</td>
			</tr><tr>
				<td>
					<label for="performance-shortNotes-<?php echo $count; ?>"><?php _e('Short Notes:', $gcd) ?></label>
				</td><td>
					<input type="text" class="shortNotes wide" name="shortNotes[]" id="performance-shortNotes-<?php echo $count; ?>" value="<?php echo $p->shortNotes ?>" />
				</td>
			</tr><tr>
				<td>
					<label for="performance-ages-<?php echo $count; ?>"><?php _e('Ages:', $gcd) ?></label>
				</td><td>
					<select name="ages[]" id="performance-ages-<?php echo $count; ?>">
						<?php $found = false; foreach ( $ages as $age ) : ?>
							<option <?php if ( $p->ages == $age ) {echo 'selected="selected"'; $found = true;} ?>><?php echo $age ?></option>
						<?php endforeach; ?>
					</select>
					<?php _e('Or...', $gcd) ?>
					<input type="text" class="ages" name="ages-custom[]" id="performance-ages-custom[]" value="<?php if ( !$found ) echo $p->ages; ?>" />
				</td>
			</tr>
		</tbody></table>
		<script type="text/javascript">
		</script>
	<?php
}
?>

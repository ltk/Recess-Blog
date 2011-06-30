<?php

require_once 'ajaxSetup.php';
$pageTarget = $folder . 'tours.ajax.php';

switch ($_POST['action']) {
	case 'load':
		$t = new tour();
		$t->search(null, ($options['tours-sort'] == 'custom' ? '`pos`' : '`name`'));
		?>
			<div class="clickable" id="tour-add-trigger">
				<img class="icon" src="<?php echo $folder; ?>images/add.png" /> <?php _e('Add a new tour', $gcd) ?>
			</div>
			
			<div id="tour-add-form" style="display: none;">
				<form id="new-tour" class="new-item" method="post" action="<?php echo $pageTarget; ?>">
					<table>
						<tbody>
							<tr><td colspan="2">
								<h3 class="no-margin"><?php _e('Tour Information', $gcd) ?></h3>
							</td></tr>
							<tr>
							<tr>
								<td><label for="new-name"><?php _e('Tour Name:', $gcd) ?></label></td>
								<td><input type="text" class="name wide" name="name" id="new-name" /></td>
							</tr>
							<tr><td colspan="2">
								<?php _e('Description/Other notes:', $gcd) ?><br />
								<textarea class="notes" name="notes" rows="8" cols="80"></textarea>
							</td></tr>
						</tbody>
					</table>
					<div>
						<input type="submit" class="button" name="" value="<?php _e('Add tour', $gcd) ?>" />
						<input type="reset" class="button cancel" name="" value="<?php _e('Cancel', $gcd) ?>" id="new-tour-reset" />
						<input type="hidden" name="action" value="add" />
						<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
					</div>
				</form>
			</div>
			
			<table id="tour-list" class="tours widefat">
				<thead>
					<tr>
						<th style="text-align: center;" scope="col"><?php _e('ID', $gcd) ?></th>
						<th scope="col"><?php _e('Name', $gcd) ?></th>
						<th scope="col"><?php _e('Notes', $gcd) ?></th>
						<th style="text-align: center" scope="col"><?php _e('Actions', $gcd) ?></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( $t->fetch() ) : ?>
						<tr id="tour-<?php echo $t->id; ?>" class="tour tour-<?php echo $t->id; ?> <?php echo ++$count % 2 ? "alternate" : "";?>">
							<th style="text-align: center;" scope="row"><?php echo $t->id; ?></th>
							<td class="name"><?php echo $t->name; ?></td>
							<td class="notes"><?php 
								if( strlen( strip_tags($t->notes) ) > 40 ) {
									echo substr( strip_tags($t->notes), 0, 40 ) . '...';
								} else {
									echo strip_tags($t->notes);
								}
							?></td>
							<td class="actions" style="text-align: center; position: relative;">
								<div style="position: relative">
									<img alt="<?php _e('Edit', $gcd) ?>" title="<?php _e('Edit', $gcd) ?>" class="clickable edit" src="<?php echo $folder; ?>images/page_white_edit.png" />
									<?php if ( $v ) : ?>
										<a target="_blank" href="<?php echo $v->getMapLink(); ?>"><img alt="<?php _e('Map', $gcd) ?>" title="<?php _e('Map', $gcd) ?>" class="clickable map" src="<?php echo $folder; ?>images/world.png" /></a>
									<?php endif ?>
									<img alt="<?php _e('Delete', $gcd) ?>" title="<?php _e('Delete', $gcd) ?>" class="clickable delete" src="<?php echo $folder; ?>images/delete.png" />
								</div>
							</td>
						</tr>
						<tr id="tour-panel-<?php echo $t->id; ?>" class="tour tour-<?php echo $t->id; ?> panel <?php echo $count % 2 ? "alternate" : "";?>">
							<td style="background-color: white"></td>
							<td class="panel" colspan="5"></td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
			
			<script type="text/javascript">
				(function($){
					// Trigger to show the new tour form.
					$("#tour-add-trigger").click(function(){
						$("#tour-add-form:hidden").slideDown(300, function(){
							$("#new-name").focus();
						});
					});
					
					// Reset form button
					$("#new-tour-reset").click(function(){
						$("#tour-add-form table.performance[id!=performance-c1]").parents("tr").remove();
						$("#tour-add-form").slideUp(300);
					});

					// Submit the new tour form via ajax.
					$("#new-tour").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							tours = $("table#tour-list tbody tr:not(.panel) td.name");
							inserted = false;
							for ( i = 0; i < tours.length; i++ ) {
								if ( json.tour.name < tours.eq(i).html() ) {
									t = tours.eq(i);
									$.post(pageTarget, {
										nonce:nonce,
										action:'getRow',
										id:json.tour.id
									}, function(rsp){
										t.parents("tr").before(rsp);
										resetTableColors("table#tour-list");
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
									id:json.tour.id
								}, function(rsp){
									$("table#tour-list tbody").append(rsp);
									resetTableColors("table#tour-list");
									setupEvents();
								});
							}
							$("#new-tour-reset").click();
						}

					});
					
					setupEvents = function() {
						$("img.delete").unbind("click");
						$("img.delete").click(function(){
							if ( confirm("Are you sure you want to delete this tour?") ) {
								id = $(this).parents("tr").attr("id").split("-")[1];
								$.post(pageTarget, {
									nonce:nonce,
									action:'delete',
									id:id
								}, function (rsp){
									console.log(rsp)
								}, "json");
								$(this).parents("tr").next().remove();
								$(this).parents("tr").remove();
								resetTableColors("table#tour-list");
							}
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

					};
					
					setupEvents();
				})(jQuery);
			</script>
			<?php /*

			

				
			<script type="text/javascript">
			
					setupEvents = function() {
						
						
						setupPerformances();
					}
					
					setupEvents();

				}(jQuery));
				
			
			
			</script>
		<?php
		*/
		break;
	case 'add':
		$t = new tour();
		
		$t->name = $_POST['name'];
		$t->notes = $_POST['notes'];
		
		if ( $t->save() ) {
			echo '{"success":true, "tour":' . $t->toJSON() . '}';
		} else {
			echo '{"success":false}';
		}
		break;
	case 'getRow':
		$t = new tour($_POST['id']);
		?>
						<tr id="tour-<?php echo $t->id; ?>" class="tour tour-<?php echo $t->id; ?> <?php echo ++$count % 2 ? "alternate" : "";?>">
							<th style="text-align: center;" scope="row"><?php echo $t->id; ?></th>
							<td class="name"><?php echo $t->name; ?></td>
							<td class="notes"><?php 
								if( strlen( strip_tags($t->notes) ) > 40 ) {
									echo substr( strip_tags($t->notes), 0, 40 ) . '...';
								} else {
									echo strip_tags($t->notes);
								}
							?></td>
							<td class="actions" style="text-align: center; position: relative;">
								<div style="position: relative">
									<img alt="<?php _e('Edit', $gcd) ?>" title="<?php _e('Edit', $gcd) ?>" class="clickable edit" src="<?php echo $folder; ?>images/page_white_edit.png" />
									<?php if ( $v ) : ?>
										<a target="_blank" href="<?php echo $v->getMapLink(); ?>"><img alt="<?php _e('Map', $gcd) ?>" title="<?php _e('Map', $gcd) ?>" class="clickable map" src="<?php echo $folder; ?>images/world.png" /></a>
									<?php endif ?>
									<img alt="<?php _e('Delete', $gcd) ?>" title="<?php _e('Delete', $gcd) ?>" class="clickable delete" src="<?php echo $folder; ?>images/delete.png" />
								</div>
							</td>
						</tr>
						<tr id="tour-panel-<?php echo $t->id; ?>" class="tour tour-<?php echo $t->id; ?> panel <?php echo $count % 2 ? "alternate" : "";?>">
							<td style="background-color: white"></td>
							<td class="panel" colspan="5"></td>
						</tr>
		<?php
		break;
	
	case 'delete':
		$t = new tour($_POST['id']);
		$result = $t->delete();
		echo '{"success": ' . ($result ? 'true' : 'false') . ',"action":"delete"' . ($result ? '' : ',"error":"db"') . '}';
		break;
	case 'edit':
		$t = new tour($_POST['id']);
		?>
			<form id="edit-tour-<?php echo $t->id ?>" class="edit-item" method="post" action="<?php echo $pageTarget; ?>">
				<table>
					<tbody>
						<tr><td colspan="2">
							<h3 class="no-margin"><?php _e('Tour Information', $gcd) ?></h3>
						</td></tr>
						<tr>
							<td><label for="edit-name-<?php echo $t->id ?>"><?php _e('Tour Name:', $gcd) ?></label></td>
							<td><input type="text" class="name wide" name="name" id="edit-name-<?php echo $t->id ?>" value="<?php dtcGigs::escapeForInput($t->name); ?>" /></td>
						</tr>
						<tr><td colspan="2">
							<?php _e('Description/Other notes:', $gcd) ?><br />
							<textarea class="notes" name="notes" rows="8" cols="80"><?php dtcGigs::escapeForInput($t->notes); ?></textarea>
						</td></tr>
					</tbody>
				</table>
				<div class="extra-inputs">
					<input type="submit" class="button" name="" value="Save tour" />
					<input type="reset" class="button cancel" name="" value="Cancel" id="edit-tour-reset-<?php echo $t->id ?>" />
					<input type="hidden" name="action" value="save" />
					<input type="hidden" name="id" value="<?php echo $t->id ?>" />
					<input type="hidden" name="nonce" value="<?php echo $_POST['nonce']; ?>" />
				</div>
			</form>
			
			<script type="text/javascript">
				(function($){
					$("#edit-tour-reset-<?php echo $t->id ?>").click(function(){
						$(this).parents("tr.panel").hide();
					});
					
					$("#edit-tour-<?php echo $t->id ?>").ajaxForm({
						url:pageTarget,
						dataType: "json",
						success:function(json){
							row = $("#tour-panel-" + json.tour.id).hide().prev();
							row.children("td.name").html(json.tour.name);
							if ( json.tour.notes.length > 40 ) {
								row.children("td.notes").html(json.tour.notes.substr(0,40) + '...');
							} else {
								row.children("td.notes").html(json.tour.notes);
							}
							
						}
					});
				}(jQuery));
			</script>
		<?php
		break;
		
	case 'save':
		$t = new tour($_POST['id']);

		$t->name = $_POST['name'];
		$t->notes = $_POST['notes'];
				
		$t->save();
		echo '{"success":true, tour:' . $t->toJSON() . '}';
		break;
		
	case 'performance-form':
		if ( isset($_POST['id']) ) {
			pForm($_POST['id'], false);
		} else {
			pForm((int) $_POST['count'], true);
		}
		break;
}
?>
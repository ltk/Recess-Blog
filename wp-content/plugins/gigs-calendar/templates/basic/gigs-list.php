<div class="gigs-calendar">
	<table class="gigs calendar <?php echo $upcoming ? 'upcoming' : 'archive' ?>">
		<?php if ( !empty($caption) ) : ?>
			<caption><?php echo $caption; ?></caption>
		<?php endif; ?>
		<?php if ( $options['list-headers'] ) : ?>
			<thead><tr>
				<?php foreach ( $show_fields as $field ) : ?>
					<th><?php echo $listFields[$field]; ?></th>
				<?php endforeach; ?>
			</tr></thead>
		<?php endif; ?>
		<tbody>
			<?php foreach ( $gigs as $gkey => $g ) : ?>
				<?php foreach ( $g->performances as $key => $p ) : ?>
					<tr id="performance-<?php echo $p->id; ?>" class="<?php echo ($gkey % 2) ? 'even' : 'odd'; ?> <?php echo $key == 0 ? 'gig' : 'performance'; ?> gig-<?php echo $g->id; ?> <?php echo dtcGigs::get_gig_css_classes($g) ?>">
						<?php 
							if ( $key == 0 ) {
								$fields = array(
									'city' => '<td class="city" valign="top">{' . $g->cityState . '}</td>',
									'country' => '<td class="country" valign="top">{' . $g->country . '}</td>',
									'venue' => '<td class="venue" valign="top">{' . $g->name . '}</td>',
									'eventName' => '<td class="eventName" valign="top">{' . $g->eventName . '}</td>',
									'date' => '<td class="date" valign="top">{' . $g->date . '}</td>',
									'time' => '<td class="time" valign="top">{' . $p->time . '}</td>',
									'shortNotes' => '<td class="shortNotes" valign="top">{' . $p->shortNotes . '}</td>',
									'tickets' => ( $upcoming ? '<td class="tickets icon" valign="top">' . ( !empty($p->link) ? '<a target="_blank" href="' . $p->link . '"><img alt="' . __('Buy Tickets', $gcd) . '" title="' . __('Buy Tickets', $gcd) . '" class="clickable tickets" src="' . $folder . 'images/money_dollar.png" /></a>' : '') . '</td>' : '' ),
									'map' => '<td class="map icon" valign="top">' . ($g->mapLink ? '<a target="_blank" href="' . $g->mapLink . '"><img alt="' . __('Map', $gcd) . '" title="' . __('Map', $gcd) . '" class="clickable map" src="' . $folder . 'images/world.png" /></a>' : '') . '</td>',
								);
							} else {
								$fields = array(
									'city' => '<td class="city" valign="top"></td>',
									'country' => '<td class="country" valign="top"></td>',
									'venue' => '<td class="venue" valign="top"></td>',
									'eventName' => '<td class="eventName" valign="top"></td>',
									'date' => '<td class="date" valign="top"></td>',
									'time' => '<td class="time" valign="top">{' . $p->time . '}</td>',
									'shortNotes' => '<td class="shortNotes" valign="top">{' . $p->shortNotes . '}</td>',
									'tickets' => ( $upcoming ? '<td class="tickets icon" valign="top">' . ( !empty($p->link) ? '<a target="_blank" href="' . $p->link . '"><img alt="' . __('Buy Tickets', $gcd) . '" title="' . __('Buy Tickets', $gcd) . '" class="clickable tickets" src="' . $folder . 'images/money_dollar.png" /></a>' : '') . '</td>' : '' ),
									'map' => '<td class="map icon" valign="top"></td>',
								);
							}
							echo dtcGigs::selectFields($fields, $g);
						?>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( empty($gigs) && $upcoming ) : ?>
		<div class="no-gigs"><?php echo $options['no-upcoming']; ?></div>
	<?php elseif ( empty($gigs) ) : ?>
		<div class="no-gigs"><?php echo $options['no-past']; ?></div>
	<?php endif; ?>
</div>
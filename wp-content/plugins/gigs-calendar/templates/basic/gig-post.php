<div class="gig-post <?php echo dtcGigs::get_gig_css_classes($g) ?>" id="gig-<?php echo $g->id ?>">
	<table>
		<tbody>
			<?php if ( !empty($g->eventName) ) : ?>
				<tr class="eventName">
					<td class="bold" valign="top"><?php echo $options['eventName-label'] ?></td>
					<td><div class="eventName"><?php echo $g->eventName ?></div></td>
				</tr>
			<?php endif; ?>
			<tr class="when">
				<td class="bold" valign="top"><?php _e('When', $gcd); ?></td>
				<td>
					<div class="date"><?php echo dtcGigs::dateFormat($g->date, 'long'); ?></div>
					<?php while ( $p->fetch() ) : ?>
						<div class="performance">
							<span class="time"><?php echo dtcGigs::timeFormat($p->time); ?></span>
							<?php if ( !empty($p->shortNotes) ) : ?>
								<span class="separator shortNotes">-</span>
								<span class="shortNotes"><?php echo $p->shortNotes; ?></span>
							<?php endif; ?>
							<span class="separator ages">-</span>
							<span class="ages"><?php echo $p->ages; ?></span>
							<?php if ( !empty($p->link) ) : ?>
								<a href="<?php echo $p->link; ?>"><img class="buy" src="<?php echo $image_folder; ?>money_dollar.png" alt="<?php _e('Buy Tickets', $gcd); ?>" title="<?php _e('Buy Tickets', $gcd); ?>" /></a>
							<?php endif; ?>
						</div>
					<?php endwhile; ?>
				</td>
			</tr>
			<tr class="where">
				<td class="bold" valign="top"><?php _e('Where', $gcd); ?></td>
				<td class="venue">
					<?php if ( $v->private ) : ?>
						<div class="name"><?php _e('Private Venue', $gcd); ?></div>
					<?php else : ?>
						<div class="name">
							<?php if ( !empty($v->link) ) : ?>
								<a target="_blank" href="<?php echo $v->link; ?>"><?php echo $v->name; ?></a>
							<?php else : ?>
								<?php echo $v->name; ?>
							<?php endif; ?>
							(<a target="_blank" href="<?php echo $v->getMapLink(); ?>"><?php _e('map', $gcd); ?></a>)
						</div>
						<div class="address"><?php echo nl2br($v->getAddress()); ?></div>
						<?php if ( !empty($v->notes) ) : ?>
							<p class="notes"><?php echo nl2br($v->notes); ?></p>
						<?php endif ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php if ( !empty($g->notes) ) : ?>
				<tr class="other"><td class="bold" valign="top"><?php _e('Other Info', $gcd); ?></td><td><div class="notes"><?php echo nl2br($g->notes); ?></div></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
	<?php if ( !empty($options['parent']) ) : ?>
		<p>&laquo; <a href="<?php echo get_permalink($options['parent']); ?>"><?php _e('Back to the calendar', $gcd); ?></a></p>
	<?php endif; ?>
</div>
<?php return false; ?>
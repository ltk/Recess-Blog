<?php if ( count($gigs) ) : $g = $gigs[0]; ?>
	<div class="<?php echo dtcGigs::get_gig_css_classes($g) ?> next" id="gig-<?php echo $g->id ?>">
		<div class="eventName"><?php echo $g->eventName ?></div>
		<div class="venue">
			<?php if ( !empty($g->venueLink) ) echo '<a href="' . $g->venueLink . '">' ?>
			<?php echo $g->name ?>
			<?php if ( !empty($g->venueLink) ) echo '</a>' ?>
		</div>
		<div class="cityStateCountry"><?php echo $g->cityStateCountry ?> <?php if ( !empty($g->mapLink) ) echo '(<a href="' . $g->mapLink . '">map</a>)' ?></div>
		<div class="date"><?php echo $g->longDate ?></div>
		<div class="moreInfo"><a href="<?php echo $g->permalink?>"><?php _e('More Info...', $gcd) ?></a></div>
	</div>

<?php else : ?>
	<div class="no-gigs"><?php echo $options['no-upcoming']; ?></div>
<?php endif; ?>

<?php if ( count($gigs) ) : ?>
	<ul class="gigs">
		<?php foreach ( $gigs as $g ) : 
			$link = array();
			foreach ( $args['link'] as $f ) {
				switch ( $f ) {
					case 'city': 
						$link[] = $g->cityState;
						break;
					case 'venue':
						$link[] = $g->name;
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
			<li class="<?php echo dtcGigs::get_gig_css_classes($g) ?>"><?php echo dtcGigs::dateFormat($g->mysqlDate . ' ' . $p->time, (date('Y') == date('Y', strtotime($g->mysqlDate))) ? $args['dateFormat'] : $args['dateFormatYear']) ?>: <a href="<?php echo $g->permalink; ?>">
				<?php echo implode(' - ', $link) ?>
			</a></li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<div class="no-gigs"><?php echo $options['no-upcoming']; ?></div>
<?php endif; ?>
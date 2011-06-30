<?php

header('Content-Type: application/rss+xml; charset=utf-8');
define('GIGS_PUBLIC', 1);
define('GIGS_RSS', 1);
require_once 'ajaxSetup.php';
require_once 'version.php';

$query = '
    SELECT p.* 
    FROM 
		' . $wpdb->posts . ' p,
		' . TABLE_GIGS . ' g
    WHERE 
		p.ID = g.postID AND
		g.date >= CURDATE()
	
    ORDER BY g.date
';

$posts = $wpdb->get_results($query);
echo '<?xml version="1.0"?>'; 
?>
<rss version="2.0">
	<channel>
		<title><?php bloginfo('name') ?> - <?php _e('Upcoming Gigs feed', $gcd); ?></title>
		<?php if ( !empty($options['parent']) ) : ?>
			<link><?php echo get_permalink($options['parent']); ?></link>
		<?php else : ?>
			<link><?php get_bloginfo('wpurl') ?></link>
		<?php endif; ?>
		
		<generator>Gigs Calendar v<?php echo DTC_GIGS_PLUGIN_VERSION ?></generator>
		<description></description>
 <?php if ($posts): ?>
	<?php foreach ($posts as $post): ?>
		<?php setup_postdata($post); ?>
		<?php $g = new gig(); $g->getByPostID($post->ID); $p = $g->getPerformances(); $p->fetch(); ?>
		<item>
			<title><?php echo the_title() ?></title>
			<link><?php the_permalink(); ?></link>
			<pubDate><?php echo date('r', strtotime($g->date . ' ' . $p->time)); ?></pubDate>

			<description>
				<![CDATA[
					<?php the_content(); ?>
				]]>
			</description>
			<guid><?php the_guid(); ?></guid>
			
		</item>

	<?php endforeach; ?>
<?php endif; ?>
	
	</channel>
</rss>

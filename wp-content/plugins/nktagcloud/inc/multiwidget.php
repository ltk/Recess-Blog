<?php
/**
 * BetterTagCloud Class
 */
class BetterTagCloud extends WP_Widget {
	/** constructor */
	function BetterTagCloud() {
		$widget_ops = array(
			'description' => __('Highly configurable tag cloud for tags and other custom taxonomies. Multiple widgets possible', 'nktagcloud')
		);
		$control_ops = array(
			'width' => 800,
			'id_base' => 'nktagcloud'
		);
		$this->WP_Widget(
			'nktagcloud',
			__('Better Tag Cloud - multiwidget', 'nktagcloud'),
			$widget_ops,
			$control_ops
		);
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			echo nktagcloud_the_cloud( $instance );
			echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function nktagcloud_input( $title, $name, $size, $value ) { ?>
		<label for="<?php echo $this->get_field_id( $name ); ?>"><?php _e( $title ); ?>
			<input id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>" type="text" value="<?php echo $value; ?>" width="<?php echo $size ?>" />
		</label> <?php
	}

	function nktagcloud_select( $title, $name, $choices, $value = '' ) { ?>
		<label for="<?php echo $this->get_field_id( $name ); ?>"><?php _e( $title ); ?>
			<select id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>"> <?php
				foreach ( $choices as $choice ) {
					if ( $choice == $value ) { ?>
						<option selected="selected"><?php echo $choice; ?></option> <?php
					}
					else { ?>
						<option><?php echo $choice; ?></option> <?php
					}
				} ?>
			</select>
		</label> <?php
	}

	/** @see WP_Widget::form */
	function form( $instance ) {
		nktagcloud_load_translation_file();
		$defaults = nktagcloud_defaults(); // get default values
		$instance = wp_parse_args( (array) $instance, $defaults['config'] );
		$title = esc_attr( $instance['title'] ); ?>
		<p>
			<?php $this->nktagcloud_input( __( 'Title', 'nktagcloud' ), 'title', 15, $title ) ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Taxonomy', 'nktagcloud' ), 'taxonomy', 15, $instance['taxonomy'] ) ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Smallest font size', 'nktagcloud' ), 'smallest', 4, $instance['smallest'] ) ?>
			<?php $this->nktagcloud_input( __( 'Largest font size', 'nktagcloud' ), 'largest', 4, $instance['largest'] ) ?>
			<?php $this->nktagcloud_select( __( 'Unit', 'nktagcloud' ), 'unit', array('pt', 'px', '%', 'em', 'ex', 'mm'), $instance['unit'] ); ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Numbers of tags to show', 'nktagcloud' ), 'number', 4, $instance['number'] ) ?>
			<br />
			<?php $this->nktagcloud_select( __( 'Format', 'nktagcloud' ), 'format', array('flat', 'list'), $instance['format'] ); ?>
			<br />
			<?php $this->nktagcloud_select( __( 'Order', 'nktagcloud' ), 'order', array('ASC', 'DESC', 'RAND' ), $instance['order'] ); ?>
			<?php $this->nktagcloud_select( __( 'Orderby', 'nktagcloud' ), 'orderby', array('name', 'count', 'both' ), $instance['orderby'] ); ?>
			<p>
				<?php _e( "The 'both' option of <tt>Orderby</tt> will sort by post count first and then by name. It doesn't exist in the default tag cloud and will ignore the <tt>Order</tt> option.", 'nktagcloud' ) ?>
			</p>

			<?php $this->nktagcloud_select( __( 'Add post count to tags?', 'nktagcloud' ), 'inject_count', array('No', 'Yes'), $instance['inject_count'] ) ?>
			<?php $this->nktagcloud_select( __( 'Put the post count outside of the hyperlink?', 'nktagcloud' ), 'inject_count_outside', array('No', 'Yes'), $instance['inject_count_outside'] ) ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Show only tags that have been used at least so many times:', 'nktagcloud' ), 'mincount', 4, $instance['mincount'] ) ?>
			<br />
			<?php $this->nktagcloud_select( __( 'Add categories to tag cloud?', 'nktagcloud' ), 'categories', array('No', 'Yes'), $instance['categories'] ) ?>
			<br />
			<?php $this->nktagcloud_select( __( 'Force tags with multiple words on one line?', 'nktagcloud' ), 'replace', array('No', 'Yes'), $instance['replace'] ) ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Tag separator', 'nktagcloud' ), 'separator', 4, $instance['separator'] ) ?>
			<?php $this->nktagcloud_select( __( 'Hide the last separator?', 'nktagcloud' ), 'hidelastseparator', array('No', 'Yes'), $instance['hidelastseparator'] ) ?>
			<br />
			<?php $this->nktagcloud_select( __( 'Add the nofollow attribute?', 'nktagcloud' ), 'nofollow', array('No', 'Yes'), $instance['nofollow'] ) ?>
			<br />

			<h3><?php _e( 'Exclude/Include tags', 'nktagcloud' ) ?></h3>
			<p>
				<?php _e( 'Comma separated list of tags (term_id) to exclude or include. For example, <tt>exclude=5,27</tt> means that tags that have the <tt>term_id</tt> 5 or 27 will NOT be displayed. See <a href="http://codex.wordpress.org/Template_Tags/wp_tag_cloud">Template Tags/wp tag cloud</a>.', 'nktagcloud' ) ?>
			</p>
			<?php $this->nktagcloud_input( __( 'Exclude Tags', 'nktagcloud' ), 'exclude', 40, $instance['exclude'] ) ?>
			<br />
			<?php $this->nktagcloud_input( __( 'Include Tags', 'nktagcloud' ), 'include', 40, $instance['include'] ) ?>
			<br />
		</p>
		<?php
	}

} // class BetterTagCloud

// register BetterTagCloud widget
add_action( 'widgets_init', create_function( '', 'return register_widget( "BetterTagCloud" );' ) );

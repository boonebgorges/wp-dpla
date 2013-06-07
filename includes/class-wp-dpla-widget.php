<?php

class WP_DPLA_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'wp_dpla_widget',
			__( 'DPLA', 'wp-dpla' ),
			array(
				'description' => __( 'Explore content from the DPLA that is related to the current post.', 'participad' )
			)
		);
	}

	public function form() {
		$title = isset( $instance['title'] ) ? $instance['title'] : __( 'Related Items from DPLA', 'wp-dpla' );
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ) ?>"><?php _e( 'Title:', 'wp-dpla' ) ?></label>
			<input name="<?php echo $this->get_field_name( 'title' ) ?>" id="<?php echo $this->get_field_name( 'title' ) ?>" value="<?php echo esc_attr( $title ) ?>" />
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo '<h3 class="widget-title">' . $instance['title'] . '</h3>';

		echo $this->get_items_markup();

		echo $args['after_widget'];
	}

	public function get_items_markup() {
		$html = '';
		$items = $this->get_items();
		return $html;
	}

	public function get_items() {
		$post = get_post();
		if ( isset( $post->ID ) ) {
			$terms = wp_get_post_tags( $post->ID );
			var_dump( $terms );
		}
	}
}

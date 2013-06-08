<?php

class WP_DPLA {
	protected $api_key;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'catch_form_submits' ) );

		if ( $this->get_api_key() ) {
			require __DIR__ . '/class-wp-dpla-query.php';
			add_action( 'widgets_init', array( $this, 'widgets_init' ) );
			add_action( 'init', array( $this, 'posts_init' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		}
	}

	public function admin_menu() {
		add_options_page(
			__( 'DPLA Settings', 'wp-dpla' ),
			__( 'DPLA', 'wp-dpla' ),
			'manage_options',
			'wp-dpla',
			array( $this, 'admin_menu_cb' )
		);
	}

	public function admin_notices() {
		$admin_page = admin_url( 'options-general.php?page=wp-dpla' );

		?>
		<div class="message error">
			<p><strong><?php _e( 'WP DPLA is not set up correctly.', 'wp-dpla' ) ?></strong> <?php printf( __( 'Visit <a href="%s">the DPLA settings page</a> to request and enter your API key.', 'wp-dpla' ), $admin_page ) ?></p>
		</div>
		<?php
	}

	public function posts_init() {
		require __DIR__ . '/class-wp-dpla-posts.php';
		$this->posts = new WP_DPLA_Posts();
	}

	public function widgets_init() {
		require __DIR__ . '/class-wp-dpla-widget.php';
		register_widget( 'WP_DPLA_Widget' );
	}

	public function catch_form_submits() {
		$redirect_to = admin_url( 'options-general.php?page=wp-dpla' );

		if ( isset( $_POST['dpla-api-key-request-submit'] ) ) {
			check_admin_referer( 'dpla-api-key-request' );
			$email = isset( $_POST['dpla-api-key-request-email'] ) ? $_POST['dpla-api-key-request-email'] : '';

			if ( $this->request_api_key( $email ) ) {
				$redirect_to = add_query_arg( 'key-requested', '1', $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'key-requested', '0', $redirect_to );
			}

			wp_redirect( $redirect_to );
		}

		if ( isset( $_POST['dpla-api-key-save-submit'] ) ) {
			check_admin_referer( 'dpla-api-key-save' );
			$api_key = isset( $_POST['dpla-api-key'] ) ? $_POST['dpla-api-key'] : '';

			if ( update_option( 'dpla_api_key', $api_key ) ) {
				$redirect_to = add_query_arg( 'key-saved', '1', $redirect_to );
			} else {
				$redirect_to = add_query_arg( 'key-saved', '0', $redirect_to );
			}

			wp_redirect( $redirect_to );
		}
	}

	public function admin_menu_cb() {
		echo '<div class="wrap">';

		echo '<h2>' . __( 'Digital Public Library of America - Settings', 'wp-dpla' ) . '</h2>';

		if ( $this->get_api_key() ) {
			$this->admin_menu_settings();
		} else {
			$this->admin_menu_get_api_key();
		}

		echo '</div>';
	}

	public function get_api_key() {
		if ( ! isset( $this->api_key ) ) {
			$this->api_key = get_option( 'dpla_api_key' );
		}

		return $this->api_key;
	}

	public function admin_menu_settings() {
		$api_key = $this->get_api_key();

		?>
		<p><?php printf( __( 'Your API key is: <strong>%s</strong>', 'wp-dpla' ), $api_key ) ?></p>
		<?php
	}

	public function admin_menu_get_api_key() {

		$current_user = new WP_User( get_current_user_id() );
		$current_user_email = isset( $current_user->user_email ) ? $current_user->user_email : '';

		$api_key = $this->get_api_key();

		$key_requested = isset( $_GET['key-requested'] ) ? (int) $_GET['key-requested'] : false;

		?>

		<?php if ( 1 === $key_requested ) : ?>
			<div class="message updated">
				<p><?php _e( 'You have successfully requested an API key. Watch your inbox for an email from api-support@dp.la. When you&#8217;ve got your key, enter it below.', 'wp-dpla' ) ?></p>
			</div>
		<?php endif ?>

		<h3><?php _e( 'Apply for an API key', 'wp-dpla' ) ?></h3>
		<p><?php _e( 'To access the DPLA API, you&#8217;ll need an API key. Enter your email address below and press "Request Key" to continue.', 'wp-dpla' ) ?></p>
		<form action="" method="post">
			<input type="text" name="dpla-api-key-request-email" value="<?php echo esc_attr( $current_user_email ) ?>" />
			<input type="submit" class="button" name="dpla-api-key-request-submit" value="<?php _e( 'Request Key', 'wp-dpla' ) ?>" />
			<?php wp_nonce_field( 'dpla-api-key-request' ) ?>
		</form>

		<h3><?php _e( 'Enter your API key', 'wp-dpla' ) ?></h3>
		<p><?php _e( 'Already received your API key? Enter it below to get started.', 'wp-dpla' ) ?></p>
		<form action="" method="post">
			<input type="text" name="dpla-api-key" value="<?php echo esc_attr( $api_key ) ?>" />
			<input type="submit" class="button" name="dpla-api-key-save-submit" value="<?php _e( 'Save Key', 'wp-dpla' ) ?>" />
			<?php wp_nonce_field( 'dpla-api-key-save' ) ?>
		</form>

		<?php
	}

	protected function request_api_key( $email ) {
		$request = wp_remote_post( 'http://api.dp.la/v2/api_key/' . $email );
		return 201 == wp_remote_retrieve_response_code( $request );
	}
}

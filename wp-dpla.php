<?php
/*
Plugin Name: Wp-dpla
Version: 0.1-alpha
Description: PLUGIN DESCRIPTION HERE
Author: YOUR NAME HERE
Author URI: YOUR SITE HERE
Plugin URI: PLUGIN SITE HERE
Text Domain: wp-dpla
Domain Path: /languages
*/

function wp_dpla() {
	include __DIR__ . '/includes/class-wp-dpla.php';
	$GLOBALS['wp_dpla'] = new WP_DPLA();
}
add_action( 'plugins_loaded', 'wp_dpla' );


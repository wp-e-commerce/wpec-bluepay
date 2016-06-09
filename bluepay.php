<?php
/*
Plugin Name: WP eCommerce BluePay Gateway
Plugin URI: https://wpecommerce.org
Version: 1.0
Author: WP eCommerce
Description: A plugin that allows the store owner to process payments using BluePay
Author URI:  https://wpecommerce.org
*/

define( 'WPECBP_VERSION', '1.0' );
define( 'WPECBP_PRODUCT_ID', '' );

if ( ! defined( 'WPECBP_PLUGIN_DIR' ) ) {
	define( 'WPECBP_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'WPECBP_PLUGIN_URL' ) ) {
	define( 'WPECBP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

include_once( WPECBP_PLUGIN_DIR . '/includes/functions.php' );

function wpec_bluepay_init() {
	include_once( WPECBP_PLUGIN_DIR . '/class-bluepay.php');
}
add_action( 'wpsc_init', 'wpec_bluepay_init' );

// register the gateway
function wpec_add_bluepay_gateway( $nzshpcrt_gateways ) {
	$num = count( $nzshpcrt_gateways ) + 1;
	
	$nzshpcrt_gateways[$num] = array(
		'name' => 'BluePay',
		'api_version' => 2.0,
		'class_name' => 'wpec_merchant_bluepay',
		'has_recurring_billing' => false,
		'display_name' => 'Credit Card',	
		'wp_admin_cannot_cancel' => false,
		'requirements' => array(
			'php_version' => 5.0
		),
		'form' => 'wpec_bluepay_settings_form',
		'submit_function' => 'wpec_save_bluepay_settings',
		'internalname' => 'wpec_bluepay',
		'display_name' => "BluePay"
	);
	return $nzshpcrt_gateways; 
}
add_filter( 'wpsc_merchants_modules', 'wpec_add_bluepay_gateway', 100 );
?>
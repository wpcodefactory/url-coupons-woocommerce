<?php
/*
Plugin Name: Coupons & Add to Cart by URL Links for WooCommerce
Plugin URI: https://wpfactory.com/item/url-coupons-woocommerce/
Description: Let your customers apply standard WooCommerce discount coupons via URL.
Version: 1.7.8
Author: WPFactory
Author URI: https://wpfactory.com
Text Domain: url-coupons-for-woocommerce-by-algoritmika
Domain Path: /langs
WC tested up to: 9.8
Requires Plugins: woocommerce
*/

defined( 'ABSPATH' ) || exit;

if ( 'url-coupons-woocommerce.php' === basename( __FILE__ ) ) {
	/**
	 * Check if Pro plugin version is activated.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	$plugin = 'url-coupons-woocommerce-pro/url-coupons-woocommerce-pro.php';
	if (
		in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
		( is_multisite() && array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

defined( 'ALG_WC_URL_COUPONS_VERSION' ) || define( 'ALG_WC_URL_COUPONS_VERSION', '1.7.8' );

defined( 'ALG_WC_URL_COUPONS_FILE' ) || define( 'ALG_WC_URL_COUPONS_FILE', __FILE__ );

// Composer autoload.
if ( ! class_exists( 'Alg_WC_URL_Coupons' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
}

require_once( 'includes/class-alg-wc-url-coupons.php' );

if ( ! function_exists( 'alg_wc_url_coupons' ) ) {
	/**
	 * Returns the main instance of Alg_WC_URL_Coupons to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_url_coupons() {
		return Alg_WC_URL_Coupons::instance();
	}
}

// Initializes the plugin.
add_action( 'plugins_loaded', function () {
	$plugin = alg_wc_url_coupons();
	$plugin->set_filesystem_path( __FILE__ );
	$plugin->init();
} );

/**
 * alg_wc_url_coupons_hpos_compatibility.
 *
 * @version 1.6.9
 * @since   1.6.9
 *
 * @return void
 */
function alg_wc_url_coupons_hpos_compatibility() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'alg_wc_url_coupons_hpos_compatibility' );

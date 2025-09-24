<?php
/**
 * URL Coupons for WooCommerce - Main Class
 *
 * @version 1.7.9
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_URL_Coupons' ) ) :

	final class Alg_WC_URL_Coupons {

		/**
		 * $core.
		 *
		 * @since 1.7.0
		 *
		 * @var null
		 */
		public $core = null;

		/**
		 * Plugin version.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		public $version = ALG_WC_URL_COUPONS_VERSION;

		/**
		 * Instance of the class.
		 *
		 * @since 1.0.0
		 * @var   Alg_WC_URL_Coupons The single instance of the class
		 */
		protected static $_instance = null;

		/**
		 * $file_system_path.
		 *
		 * @since 1.7.6
		 *
		 * @var   string
		 */
		protected $file_system_path;

		/**
		 * Main Alg_WC_URL_Coupons Instance
		 *
		 * Ensures only one instance of Alg_WC_URL_Coupons is loaded or can be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @static
		 * @return  Alg_WC_URL_Coupons - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Alg_WC_URL_Coupons Constructor.
		 *
		 * @version 1.7.9
		 * @since   1.0.0
		 *
		 * @access  public
		 */
		function init() {
			// Check for active WooCommerce plugin.
			if ( ! function_exists( 'WC' ) ) {
				return;
			}

			// Adds cross-selling library.
			$this->add_cross_selling_library();

			// Move WC Settings tab to WPFactory menu.
			add_action( 'init', array( $this, 'move_wc_settings_tab_to_wpfactory_menu' ) );

			// Set up localisation.
			add_action( 'init', array( $this, 'localize' ) );

			// Pro.
			if ( 'url-coupons-woocommerce-pro.php' === basename( ALG_WC_URL_COUPONS_FILE ) ) {
				require_once 'pro/class-alg-wc-url-coupons-pro.php';
			}

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				$this->admin();
			}

		}

		/**
		 * add_cross_selling_library.
		 *
		 * @version 1.7.6
		 * @since   1.7.6
		 *
		 * @return void
		 */
		function add_cross_selling_library(){
			if ( ! is_admin() ) {
				return;
			}
			// Cross-selling library.
			$cross_selling = new \WPFactory\WPFactory_Cross_Selling\WPFactory_Cross_Selling();
			$cross_selling->setup(
				array( 'plugin_file_path' => $this->get_filesystem_path() )
			);
			$cross_selling->init();
		}

		/**
		 * move_wc_settings_tab_to_wpfactory_submenu.
		 *
		 * @version 1.7.9
		 * @since   1.7.6
		 *
		 * @return void
		 */
		function move_wc_settings_tab_to_wpfactory_menu() {
			if ( ! is_admin() ) {
				return;
			}
			// WC Settings tab as WPFactory submenu item.
			$wpf_admin_menu = \WPFactory\WPFactory_Admin_Menu\WPFactory_Admin_Menu::get_instance();
			$wpf_admin_menu->move_wc_settings_tab_to_wpfactory_menu(
				array(
					'wc_settings_tab_id' => 'alg_wc_url_coupons',
					'menu_title'         => __( 'Coupons by URL', 'url-coupons-for-woocommerce-by-algoritmika' ),
					'page_title'         => __( 'Coupons & Add to Cart by URL', 'url-coupons-for-woocommerce-by-algoritmika' ),
					'plugin_icon' => array(
						'get_url_method'    => 'wporg_plugins_api',
						'wporg_plugin_slug' => 'url-coupons-for-woocommerce-by-algoritmika',
						'style'             => 'margin-left:-4px',
					)
				)
			);
		}

		/**
		 * localize.
		 *
		 * @version 1.6.0
		 * @since   1.4.0
		 */
		function localize() {
			load_plugin_textdomain( 'url-coupons-for-woocommerce-by-algoritmika', false, dirname( plugin_basename( ALG_WC_URL_COUPONS_FILE ) ) . '/langs/' );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @version 1.6.4
		 * @since   1.0.0
		 */
		function includes() {
			require_once 'alg-wc-url-coupons-functions.php';
			$this->core = require_once 'class-alg-wc-url-coupons-core.php';
		}

		/**
		 * admin.
		 *
		 * @version 1.6.0
		 * @since   1.1.0
		 */
		function admin() {
			// Action links
			add_filter( 'plugin_action_links_' . plugin_basename( ALG_WC_URL_COUPONS_FILE ), array( $this, 'action_links' ) );
			// Settings
			add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
			// Version update
			if ( get_option( 'alg_wc_url_coupons_version', '' ) !== $this->version ) {
				add_action( 'admin_init', array( $this, 'version_updated' ) );
			}
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 1.6.0
		 * @since   1.0.0
		 *
		 * @param   mixed $links
		 *
		 * @return  array
		 */
		function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_url_coupons' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'url-coupons-woocommerce.php' === basename( ALG_WC_URL_COUPONS_FILE ) ) {
				$custom_links[] = '<a target="_blank" style="font-weight: bold; color: green;" href="https://wpfactory.com/item/url-coupons-woocommerce/">' .
				                  __( 'Go Pro', 'url-coupons-for-woocommerce-by-algoritmika' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		/**
		 * Add URL Coupons settings tab to WooCommerce settings.
		 *
		 * @version 1.6.0
		 * @since   1.0.0
		 */
		function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'settings/class-alg-wc-settings-url-coupons.php';
			return $settings;
		}

		/**
		 * version_updated.
		 *
		 * @version 1.1.0
		 * @since   1.1.0
		 */
		function version_updated() {
			update_option( 'alg_wc_url_coupons_version', $this->version );
		}

		/**
		 * Get the plugin url.
		 *
		 * @version 1.6.0
		 * @since   1.0.0
		 *
		 * @return  string
		 */
		function plugin_url() {
			return untrailingslashit( plugin_dir_url( ALG_WC_URL_COUPONS_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @version 1.6.0
		 * @since   1.0.0
		 *
		 * @return  string
		 */
		function plugin_path() {
			return untrailingslashit( plugin_dir_path( ALG_WC_URL_COUPONS_FILE ) );
		}

		/**
		 * get_filesystem_path.
		 *
		 * @version 3.0.3
		 * @since   2.4.3
		 *
		 * @return string
		 */
		function get_filesystem_path() {
			return $this->file_system_path;
		}

		/**
		 * set_filesystem_path.
		 *
		 * @version 3.0.3
		 * @since   3.0.3
		 *
		 * @param   mixed  $file_system_path
		 */
		public function set_filesystem_path( $file_system_path ) {
			$this->file_system_path = $file_system_path;
		}

	}

endif;

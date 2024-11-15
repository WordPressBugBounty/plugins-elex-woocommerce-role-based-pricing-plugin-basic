<?php

/*
  Plugin Name: ELEX WooCommerce Role Based Pricing (BASIC)
  Plugin URI: https://elextensions.com/plugin/woocommerce-catalog-mode-wholesale-role-based-pricing/
  Description:  Hide add to cart for guest, specific user. Hide price for guest, specific user for simple products. Create user role specific product price. Enforce markup/discount on price for selected user roles.
  Version: 1.4.12
  WC requires at least: 2.6.0
  WC tested up to: 9.4
  Author: ELEXtensions
  Author URI: https://elextensions.com/
 Text Domain: eh-woocommerce-pricing-discount
 */

// to check wether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// for Required functions
if ( ! function_exists( 'elex_rp_is_woocommerce_active' ) ) {
	require_once  'elex-includes/elex-functions.php' ;
}

// to check woocommerce is active
if ( ! ( elex_rp_is_woocommerce_active() ) ) {
	add_action( 'admin_notices', 'elex_rp_basic_prices_woocommerce_inactive_notice' );
	return;
}
//to check if premium version is active
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/plugin.php' ;
}
$rolebased_plugins        = array(
	'elex-woocommerce-role-based-pricing-plugin-basic/elex-rolebased-price-basic.php'  => "Oops! You tried installing the premium version without deactivating the basic version. Kindly deactivate WooCommerce Catalog Mode, Wholesale & Role Based Pricing (BASIC) and then try again. For any issues, kindly contact our <a target='_blank' href='https://elextensions.com/support/'>support</a>.<br>Go back to <a href='" . esc_html( admin_url( 'plugins.php' ) ) . "'>plugins page</a>",
	'elex-catmode-rolebased-price/elex-catmode-rolebased-price.php' => "Oops! You tried installing the Basic version without deactivating the Premium version. Kindly deactivate ELEX WooCommerce Role-based Pricing Plugin & WooCommerce Catalog Mode and then try again. For any issues, kindly contact our <a target='_blank' href='https://elextensions.com/support/'>support</a>.<br>Go back to <a href='" . esc_html( admin_url( 'plugins.php' ) ) . "'>plugins page</a>",
	'catalog-mode-for-woocommerce/class-elex-role-based-catalog-mode-woocommerce.php' => "Oops! You tried installing the woocommerce version without deactivating the Basic version. Kindly deactivate ELEX WooCommerce Role-based Pricing Plugin & WooCommerce Catalog Mode and then try again. For any issues, kindly contact our <a target='_blank' href='https://elextensions.com/support/'>support</a>.<br>Go back to <a href='" . esc_html( admin_url( 'plugins.php' ) ) . "'>plugins page</a>",
);

$current_role_cat_plugin = plugin_basename( __FILE__ );

foreach ( $rolebased_plugins as $role_cat_plugin => $error_msg ) {
	if ( $current_role_cat_plugin === $role_cat_plugin ) {
		continue;
	}

	if ( is_plugin_active( $role_cat_plugin ) ) {
		deactivate_plugins( $current_role_cat_plugin );
		wp_die( wp_kses_post( $error_msg ) );
	}
}
// review component
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once  ABSPATH . 'wp-admin/includes/plugin.php';
}
// High performance order tables compatibility.
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} 
);

include_once __DIR__ . '/review_and_troubleshoot_notify/review-and-troubleshoot-notify-class.php';
$data                      = get_plugin_data( __FILE__ );
$data['name']              = $data['Name'];
$data['basename']          = plugin_basename( __FILE__ );
$data['documentation_url'] = 'https://elextensions.com/knowledge-base/set-up-elex-woocommerce-catalog-mode-wholesale-role-based-pricing/';
$data['rating_url']        = 'https://elextensions.com/plugin/elex-woocommerce-role-based-pricing-plugin-free/#reviews';
$data['support_url']       = 'https://wordpress.org/support/plugin/elex-woocommerce-role-based-pricing-plugin-basic/';

new \Elex_Review_Components( $data );

function elex_rp_basic_prices_woocommerce_inactive_notice() {
	?>
<div id="message" class="error">
	<p>
	<?php	
	deactivate_plugins( plugin_basename( __FILE__ ) );
	print_r( __( '<b>WooCommerce</b> plugin must be active for <b>WooCommerce Catalog Mode, Wholesale & Role Based Pricing (BASIC)</b> to work. ', 'eh-woocommerce-pricing-discount' ) );
	
	if ( filter_input( INPUT_GET, 'activate' ) ) {
		$_GET['activate'] = false;
	}
	?>
	
	</p>
</div>
<?php
}

if ( ! defined( 'ELEX_PRICING_DISCOUNT_MAIN_URL_PATH' ) ) {
	define( 'ELEX_PRICING_DISCOUNT_MAIN_URL_PATH', plugin_dir_url( __FILE__ ) );
}


if ( ! class_exists( 'Elex_Pricing_Discounts_By_User_Role_WooCommerce' ) ) {

	class Elex_Pricing_Discounts_By_User_Role_WooCommerce {

		// initializing the class
		public function __construct() {
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'elex_rp_pricing_discount_action_links' ) ); //to add settings, doc, etc options to plugins base
			add_action( 'init', array( $this, 'elex_rp_pricing_discount_admin_menu' ) ); //to add pricing discount settings options on woocommerce shop
			add_action( 'admin_menu', array( $this, 'elex_rp_pricing_discount_admin_menu_option' ) ); //to add pricing discount settings menu to main menu of woocommerce
		}

		// function to add settings link to plugin view
		public function elex_rp_pricing_discount_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=eh_pricing_discount' ) . '">' . __( 'Settings', 'eh-woocommerce-pricing-discount' ) . '</a>',
				'<a href="https://elextensions.com/documentation/#elex-woocommerce-catalog-mode" target="_blank">' . __( 'Documentation', 'eh-woocommerce-pricing-discount' ) . '</a>',
				'<a href="https://elextensions.com/plugin/woocommerce-catalog-mode-wholesale-role-based-pricing/" target="_blank">' . __( 'Premium Upgrade', 'eh-woocommerce-pricing-discount' ) . '</a>',
				'<a href="https://elextensions.com/support/" target="_blank">' . __( 'Support', 'eh-woocommerce-pricing-discount' ) . '</a>',
			);
			return array_merge( $plugin_links, $links );
		}

		// function to add menu in woocommerce
		public function elex_rp_pricing_discount_admin_menu() {
			require_once  'includes/elex-price-discount-admin.php' ;
			require_once  'includes/elex-price-discount-settings.php' ;
		}

		public function elex_rp_pricing_discount_admin_menu_option() {
			global $pricing_discount_settings_page;
			$pricing_discount_settings_page = add_submenu_page( 'woocommerce', __( 'Role-based Pricing', 'eh-woocommerce-pricing-discount' ), __( 'Role-based Pricing', 'eh-woocommerce-pricing-discount' ), 'manage_woocommerce', 'admin.php?page=wc-settings&tab=eh_pricing_discount' );
		}

	}

	new Elex_Pricing_Discounts_By_User_Role_WooCommerce();
}


<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' );

class Elex_Pricing_Discount_Settings extends WC_Settings_Page {
	public $user_adjustment_price;
	public $price_table;
	public function __construct() {
		global $user_adjustment_price;
		$this->init();
		$this->id = 'eh_pricing_discount';
	}

	public function init() {
		include( 'elex-class-admin-notice.php' );

		$this->user_adjustment_price = get_option( 'eh_pricing_discount_price_adjustment_options', array() );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'elex_rp_add_settings_tab' ), 50 );
		add_filter( 'eh_pricing_discount_manage_user_role_settings', array( $this, 'elex_rp_add_manage_role_settings' ), 30 );
		add_action( 'woocommerce_admin_field_productdiscountonusers', array( $this, 'elex_rp_pricing_admin_field_productdiscountonusers' ) );
		add_action( 'woocommerce_admin_field_priceadjustmenttable', array( $this, 'elex_rp_pricing_admin_field_priceadjustmenttable' ) ); //to add price adjustment table to settings
		add_action( 'woocommerce_admin_field_pricing_discount_manage_user_role', array( $this, 'elex_rp_pricing_admin_field_pricing_discount_manage_user_role' ) );
		add_action( 'woocommerce_update_options_eh_pricing_discount', array( $this, 'elex_rp_update_settings' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'elex_rp_add_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'elex_rp_add_price_adjustment_data_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'elex_rp_add_custom_general_fields_save' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'elex_rp_add_price_extra_fields' ) );
		add_action( 'event-category_add_form_fields', array( $this, 'elex_rp_pricing_category_adjustment_fields' ), 10 );
		add_filter( 'woocommerce_sections_eh_pricing_discount', array( $this, 'output_sections' ) );
		add_filter( 'woocommerce_settings_eh_pricing_discount', array( $this, 'elex_rp_output_settings' ) );
		add_action( 'admin_init', array( $this, 'elex_rp_pricing_discount_remove_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'elex_rp_include_js' ) );
	}

	public function get_query_param( $param ) {
		wp_parse_str( wp_parse_url( wc_get_current_admin_url(), PHP_URL_QUERY ), $uri_data );
		return isset( $uri_data[ $param ] ) ? sanitize_text_field( $uri_data[ $param ] ) : '';
	}
	
	public function elex_rp_include_js() {
		global $woocommerce;
		$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
		$page = $this->get_query_param( 'page' );
		$tab = $this->get_query_param( 'tab' );
		$section = $this->get_query_param( 'section' );
		if ( 'wc-settings' === $page && 'eh_pricing_discount' === $tab && ( '' === $section || 'xa-pricing-payments' === $section || 'xa-unregistered-role' === $section ) ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'eh-pricing-discount', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'includes/elex-html-price-adjustment.js', array(), $woocommerce_version );
		}
	}

	public function get_sections() {
		
		$sections = array(
			'' => __( 'Role-based Settings', 'elex-catmode-rolebased-price' ),
			'xa-unregistered-role' => __( 'Unregistered User', 'elex-catmode-rolebased-price' ),
			'xa-pricing-payments' => __( 'Pricing & Payments', 'elex-catmode-rolebased-price' ),
			'manage-user-role' => __( 'Manage User Role', 'elex-catmode-rolebased-price' ),
			'to-go-premium' => __( '<li><strong><font color="red">Go Premium!</font></strong></li>', 'eh-woocommerce-pricing-discount' ),
		);
		/**
		 * To get section of settings tab
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'woocommerce_get_sections_eh_pricing_discount', $sections );
	}
	
	public function output_sections() {

		global $current_section;
		
		$sections = $this->get_sections();
		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}
		echo '<ul class="subsubsub">';
		$array_keys = array_keys( $sections );
		foreach ( $sections as $id => $label ) {
			echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=eh_pricing_discount&section=' . sanitize_title( $id ) ) ) . '" class="' . ( $current_section === $id ? 'current' : '' ) . '">' . wp_kses_post( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}

	public function elex_rp_pricing_discount_remove_notices() {
		global $current_section;
		if ( 'manage-user-role' === $current_section ) {
			remove_all_actions( 'admin_notices' );
			Elex_Admin_Notice::throw_notices();
		}
	}


	public static function elex_rp_add_settings_tab( $settings_tabs ) {
		
		$settings_tabs['eh_pricing_discount'] = __( 'Role-based Pricing', 'elex-catmode-rolebased-price' );
		return $settings_tabs;
	}

	public function elex_rp_output_settings() {
		global $current_section, $woocommerce;
			$woocommerce_version = function_exists( 'WC' ) ? WC()->version : $woocommerce->version;
		if ( 'to-go-premium' === $current_section ) {
			wp_enqueue_style( 'eh-pricing-discount-bootstrap', ELEX_PRICING_DISCOUNT_MAIN_URL_PATH . 'resources/css/bootstrap.css', array(), $woocommerce->version );
			include_once( 'market.php' );
		} else if ( '' === $current_section ) {
			$settings = $this->elex_rp_get_role_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
		} else if ( 'xa-unregistered-role' === $current_section ) {
			$settings = $this->elex_rp_get_unregistered_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
		} else if ( 'xa-pricing-payments' === $current_section ) {
			$settings = $this->elex_rp_pricing_payments_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
		} else if ( 'manage-user-role' === $current_section ) {
			$settings = $this->elex_rp_get_user_role_settings( $current_section );
			WC_Admin_Settings::output_fields( $settings );
			remove_all_actions( 'admin_notices' );
			Elex_Admin_Notice::throw_notices();
		}
	}

	public function elex_rp_get_user_role_settings( $current_section ) {
		$settings = array(
			'section_title' => array(
				'type' => 'title',
				'desc' => '',
				'id' => 'eh_pricing_discount_add_user_role_section_title',
			),
			'section_end' => array(
				'type' => 'sectionend',
				'id' => 'eh_pricing_discount_add_user_role_section_end',
			),
		);
		/**
		 * To add fields on manage user role section
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'eh_pricing_discount_manage_user_role_settings', $settings );
	}

	//function to add 
	public function elex_rp_add_manage_role_settings( $settings ) {
		$settings['price_adjustment_options'] = array(
			'type' => 'pricing_discount_manage_user_role',
			'id' => 'eh_pricing_discount_manage_user_role',
		);
		return $settings;
	}

	//function to generate manage user role setting page
	public function elex_rp_pricing_admin_field_pricing_discount_manage_user_role( $settings ) {
		include( 'elex-html-price-adjustment-manage-user-role.php' );
	}

	public function elex_rp_update_settings( $current_section ) {
		global $current_section;
		
		if ( '' === $current_section ) {
			$options = $this->elex_rp_get_role_settings();
			woocommerce_update_options( $options );
		}
		if ( 'xa-unregistered-role' === $current_section ) {
			$options = $this->elex_rp_get_unregistered_settings();
			woocommerce_update_options( $options );
		}
		if ( 'xa-pricing-payments' === $current_section ) {
			if ( ! isset( $_REQUEST['eh_pricing_discount_product_on_users'] ) ) {
				delete_option( 'eh_pricing_discount_product_on_users' );
			}
			$options = $this->elex_rp_pricing_payments_settings();
			woocommerce_update_options( $options );
			$this->user_adjustment_price = get_option( 'eh_pricing_discount_price_adjustment_options', array() );

		}
		
		if ( 'manage-user-role' === $current_section ) {
			if ( ! ( isset( $_REQUEST['elex-rp-fields-nonce'] ) || wp_verify_nonce( sanitize_key( $_REQUEST['elex-rp-fields-nonce'] ), 'woocommerce_save_data' ) ) ) {
				return false;
			}
			$user_role_action = isset( $_POST['pricing_discount_manage_user_roles'] ) ? sanitize_text_field( $_POST['pricing_discount_manage_user_roles'] ) : '';
			$manage_role_status = '';
			if ( 'add_user_role' === $user_role_action ) {
				$manage_role_status = $this->elex_rp_pricing_discount_add_user_role( isset( $_POST['eh_woocommerce_pricing_discount_user_role_name'] ) ? sanitize_text_field( $_POST['eh_woocommerce_pricing_discount_user_role_name'] ) : '' );
			}
			if ( ( 'remove_user_role' === $user_role_action ) ) {
				if ( isset( $_POST['pricing_discount_remove_user_role'] ) ) {
					$this->elex_rp_pricing_discount_remove_user_role( isset( $_POST['pricing_discount_remove_user_role'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['pricing_discount_remove_user_role'] ) ) : '' );
				
				} else {
					$status = __( 'Please select atleast one role to delete', 'elex-catmode-rolebased-price' );
					Elex_Admin_Notice::add_notice( $status, 'error' );
				}
			}
		}
	}

	//function to create User Role
	public function elex_rp_pricing_discount_add_user_role( $user_role_name ) {
		global $wp_roles;
		$user_roles = $wp_roles->role_names;
		$new_user_role = str_replace( ' ', '_', $user_role_name );
		try {
		
			if ( ( '' !== $new_user_role && '' !== $user_role_name ) && ! ( array_key_exists( $new_user_role, $user_roles ) ) ) {
				add_role( $new_user_role, $user_role_name, array( 'read' => true ) );
				
				
				$status = __( 'User Role created successfully', 'elex-catmode-rolebased-price' );
				Elex_Admin_Notice::add_notice( $status, 'notice' );
			} else {
				$status = __( 'User Role creation failed', 'elex-catmode-rolebased-price' );
				Elex_Admin_Notice::add_notice( $status, 'error' );
			}
		} catch ( Exception $e ) {
			Elex_Admin_Notice::add_notice( $e, 'error' );
		}
	}

	//function to remove User Role
	public function elex_rp_pricing_discount_remove_user_role( $remove_user_role ) {
		foreach ( $remove_user_role as $id => $status ) {
			try {

				remove_role( $id );
				$status = __( 'User Role deleted successfully', 'elex-catmode-rolebased-price' );
			} catch ( Exception $e ) {
				Elex_Admin_Notice::add_notice( $e, 'error' );
				return;
			}
		}
		Elex_Admin_Notice::add_notice( $status, 'notice' );
	}


	public function elex_rp_get_unregistered_settings() {
		$settings = array(
			'eh_pricing_discount_unregistered_title' => array(
				'title' => __( 'Unregistered User Options:', 'elex-catmode-rolebased-price' ),
				'type' => 'title',
				'description' => '',
				'id' => 'eh_pricing_discount_unregistered',
			),
			'cart_unregistered_user' => array(
				'title' => __( 'Remove Add to Cart', 'elex-catmode-rolebased-price' ),
				'type' => 'checkbox',
				'desc' => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_cart_unregistered_user',
				'desc_tip' => __( 'Check to remove Add to Cart option.', 'elex-catmode-rolebased-price' ),
			),
			'cart_unregistered_user_text' => array(
				'title' => __( 'Placeholder Text', 'elex-catmode-rolebased-price' ),
				'type' => 'textarea',
				'desc' => __( "Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price' ),
				'css' => 'width:350px',
				'id' => 'eh_pricing_discount_cart_unregistered_user_text',
				'desc_tip' => true,
			),
			'replace_cart_unregistered_user' => array(
				'title' => __( 'Customize Add to Cart', 'elex-catmode-rolebased-price' ),
				'type' => 'checkbox',
				'desc' => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_replace_cart_unregistered_user',
				'desc_tip' => __( 'Check to customize Add to Cart option.', 'elex-catmode-rolebased-price' ),
			),
			'replace_cart_unregistered_user_text_product' => array(
				'title' => __( 'Change Button Text (Product Page)', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a text to replace the existing Add to Cart button text on the product page.', 'elex-catmode-rolebased-price' ),
				'css' => 'width:350px',
				'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_product',
				'desc_tip' => true,
			),
			'replace_cart_unregistered_user_text_shop' => array(
				'title' => __( 'Change Button Text (Shop Page)', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a text to replace the existing Add to Cart button text on the shop page.', 'elex-catmode-rolebased-price' ),
				'css' => 'width:350px',
				'id' => 'eh_pricing_discount_replace_cart_unregistered_user_text_shop',
				'desc_tip' => true,
			),
			'replace_cart_unregistered_user_url_shop' => array(
				'title' => __( 'Change Button URL', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.', 'elex-catmode-rolebased-price' ),
				'css' => 'width:350px',
				'id' => 'eh_pricing_discount_replace_cart_unregistered_user_url_shop',
				'desc_tip' => true,
			),
			'hide_regular_price' => array(
				'title' => __( 'Hide Regular Price', 'elex-catmode-rolebased-price' ),
				'type' => 'checkbox',
				'desc' => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_hide_regular_price_unregistered',
				'desc_tip' => __( 'Check to hide regular price when sale price is provided.', 'elex-catmode-rolebased-price' ),
			),
			'price_unregistered_user' => array(
				'title' => __( 'Hide Price', 'elex-catmode-rolebased-price' ),
				'type' => 'checkbox',
				'desc' => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_price_unregistered_user',
				'desc_tip' => __( 'Check to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price' ),
			),
			'price_unregistered_user_text' => array(
				'title' => __( 'Placeholder Text', 'elex-catmode-rolebased-price' ),
				'type' => 'textarea',
				'desc' => __( "Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price' ),
				'css' => 'width:350px',
				'id' => 'eh_pricing_discount_price_unregistered_user_text',
				'desc_tip' => true,
			),
			'cart_unregistered_user_remove_cart_checkout' => array(
				'title' => __( 'Hide Cart and Checkout Page', 'elex-catmode-rolebased-price' ),
				'type' => 'checkbox',
				'desc' => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_cart_unregistered_user_remove_cart_checkout',
				'default' => 'no',
				'desc_tip' => __( 'Check to disable access to Cart and Checkout pages.', 'elex-catmode-rolebased-price' ),
			),
			'hide_place_order_catalog'               => array(
				'title'    => __( 'Hide Place Order Button', 'elex-catmode-rolebased-price' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css'      => 'width:100%',
				'default'  => '',
				'id'       => 'eh_pricing_discount_unregistered_hide_place_order',
				'desc_tip' => __( 'Check to hide Place Order button.', 'elex-catmode-rolebased-price' ),
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			),
			'replace_place_order_catalog'            => array(
				'title'    => __( 'Replace Place Order Button Text ', 'elex-catmode-rolebased-price' ),
				'type'     => 'checkbox',
				'desc'     => __( 'Enable', 'elex-catmode-rolebased-price' ),
				'css'      => 'width:100%',
				'id'       => 'eh_pricing_discount_unregistered_replace_place_order',
				'desc_tip' => __( 'Check to replace Place Order button. Also, please prodisabledvide text to be replaced in below textbox', 'elex-catmode-rolebased-price' ),
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			),
			'eh_pricing_discount_unregistered_title_end' => array(
				'type' => 'sectionend',
				'id' => 'eh_pricing_discount_unregistered',
			),
		);
		/**
		 * To add field on unregistered section
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'eh_pricing_discount_unregistered_settings', $settings );
	}

	public function elex_rp_get_role_settings() {
		global $wp_roles;

		$user_roles = $wp_roles->role_names;
		$settings = array(
			'eh_pricing_discount_user_role_title' => array(
				'title' => __( 'User Role Specific Options:', 'elex-catmode-rolebased-price' ),
				'type' => 'title',
				'id' => 'eh_pricing_discount_user_role',
			),
			'cart_user_role' => array(
				'title' => __( 'Remove Add to Cart', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for which you want to hide Add to Cart option.', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_cart_user_role',
				'options' => $user_roles,
				'desc_tip' => true,
			),
			'cart_user_role_text' => array(
				'title' => __( 'Placeholder Content', 'elex-catmode-rolebased-price' ),
				'type' => 'textarea',
				'desc' => __( "Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_cart_user_role_text',
				'desc_tip' => true,
			),
			'replace_cart_user_role' => array(
				'title' => __( 'Customize Add to Cart', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for which you want to customize Add to Cart option.', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_replace_cart_user_role',
				'options' => $user_roles,
				'desc_tip' => true,
			),
			'replace_cart_user_role_text_product' => array(
				'title' => __( 'Change Button Text (Product Page)', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a text to replace the existing Add to Cart button text on the product page.', 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_replace_cart_user_role_text_product',
				'desc_tip' => true,
			),
			'replace_cart_user_role_text_shop' => array(
				'title' => __( 'Change Button Text (Shop Page)', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a text to replace the existing Add to Cart button text on the shop page.', 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_replace_cart_user_role_text_shop',
				'desc_tip' => true,
			),
			'replace_cart_user_role_url_shop' => array(
				'title' => __( 'Change Button URL', 'elex-catmode-rolebased-price' ),
				'type' => 'text',
				'desc' => __( 'Enter a url to redirect customers from Add to Cart button. Leave this field empty to not change the button functionality. Make sure to enter a text in the above fields to apply these changes.', 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_replace_cart_user_role_url_shop',
				'desc_tip' => true,
			),
			'regular_price_user_role' => array(
				'title' => __( 'Hide Regular Price', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for which you want to hide regular price of all the simple products which have sale prices.', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_regular_price_user_role',
				'options' => $user_roles,
				'desc_tip' => true,
			),
			'price_user_role' => array(
				'title' => __( 'Hide Price', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for which you want to hide product price. This will also remove Add to Cart option.', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_price_user_role',
				'options' => $user_roles,
				'desc_tip' => true,
			),
			'price_user_role_text' => array(
				'title' => __( 'Placeholder Text', 'elex-catmode-rolebased-price' ),
				'type' => 'textarea',
				'desc' => __( "Enter a text you want to display when price is removed. Leave it empty if you don't want to show any text.", 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_price_user_role_text',
				'desc_tip' => true,
			),

			'cart_user_role_remove_cart_checkout' => array(
				'title' => __( 'Hide Cart and Checkout Page ', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for which you do not want to provide access to Cart and Checkout page', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_cart_user_role_remove_cart_checkout',
				'options' => $user_roles,
				'desc_tip' => true,
				
			),

			'hide_place_order_btn_for_users' => array(
				'title'    => __( 'Hide Place Order Button', 'elex-catmode-rolebased-price' ),
				'type'     => 'multiselect',
				'desc'     => __( 'Select the user role(s) for which you want to hide Place Order button in checkout page', 'elex-catmode-rolebased-price' ),
				'class'    => 'chosen_select',
				'id'       => 'eh_pricing_discount_hide_place_order_btn_for_users',
				'options'  => array(),
				'desc_tip' => true,
				'custom_attributes' => array( 'disabled' => 'disabled' ),

			),

			'replace_place_order_btn_text' => array(
				'title'    => __( 'Replace Place Order Button Text', 'elex-catmode-rolebased-price' ),
				'type'     => 'multiselect',
				'desc'     => __( 'Select the user role(s) for which you want to replace Place Order button text in checkout page', 'elex-catmode-rolebased-price' ),
				'class'    => 'chosen_select',
				'id'       => 'eh_pricing_discount_replace_place_order_btn_text',
				'options'  => array(),
				'desc_tip' => true,
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			),
			'eh_pricing_discount_user_role_title_end' => array(
				'type' => 'sectionend',
				'id' => 'eh_pricing_discount_user_role',
			),
			
			
		);
		/**
		 * To add general setting section
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'eh_pricing_discount_general_settings', $settings );
	}
	public function elex_rp_pricing_payments_settings() {
		global $wp_roles;
		$price_sale_regular = array(
			'regular' => __( 'Regular Price', 'elex-catmode-rolebased-price' ),
			'sale' => __( 'Sale Price', 'elex-catmode-rolebased-price' ),
			'regular_sale' => __( 'Regular & Sale Price', 'elex-catmode-rolebased-price' ),
		);

		$user_roles = $wp_roles->role_names;
		$user_roles['unregistered_user'] = 'Unregistered User';
		$settings = array(
			
			'eh_pricing_adjustment_specific_user_role_title' => array(
				'title' => __( 'Individual Product Pricing Specific Options:', 'elex-catmode-rolebased-price' ),
				'type' => 'title',
				'description' => '',
				'id' => 'eh_pricing_adjustment_specific_user_role',
			),

			'product_price_user_role' => array(
				'title' => __( 'Individual Product Adjustment', 'elex-catmode-rolebased-price' ),
				'type' => 'multiselect',
				'desc' => __( 'Select the user role(s) for product level price adjustments. The price adjustments can be made in the individual product edit page.', 'elex-catmode-rolebased-price' ),
				'class' => 'chosen_select',
				'id' => 'eh_pricing_discount_product_price_user_role',
				'options' => $user_roles,
				'desc_tip' => true,
			),
		
			'product_adjustment_on_users' => array(
				'type' => 'productdiscountonusers',
				'id' => 'eh_pricing_discount_product_on_users',
				'value' => '',
			),
			'eh_multiple_role_title' => array(
				'type' => 'title',
				'id' => 'eh_pricing_discount_multiple_role_price',
			),
			'multiple_user_role_price' => array(
				'title' => __( 'Users With Multiple Roles Assigned', 'elex-catmode-rolebased-price' ),
				'type' => 'radio',
				'required'        => true,
				'desc' => __( 'Select how you want to apply the price adjustment when multiple user roles are enabled for the same user. ', 'elex-catmode-rolebased-price' ),
				'class' => 'form-row-wide',
				'id' => 'eh_pricing_discount_multiple_role_price',
				'options' => array(
					'max_role_price'    => 'Take the highest price adjustment value from available roles.',
					'min_role_price'    => 'Take the lowest price adjustment value from available roles.',
					'consolidate_price'    => 'Take a consolidated value by adding all available price adjustment values.',
				),
				'default' => 'max_role_price',
				'desc_tip' => true,
			),
			'eh_multiple_role_title_end' => array(
				'type' => 'sectionend',
				'id' => 'eh_pricing_discount_multiple_role_price',
			),
			'eh_pricing_discount_adjustment_title' => array(
				'title' => __( 'Price Adjustment: (Discount/Markup)', 'elex-catmode-rolebased-price' ),
				'type' => 'title',
				'desc' => __( "Drag and drop User Roles to set priority. If a single User has multiple User Roles assigned, the User Role with the highest priority will be chosen. Select a category to apply price adjustment to the products which belong to that category. If no particular category is selected, the price adjustment will be applied to all the products.<br><p><strong>Price Adjustment - Choose 'D' for DISCOUNT and 'M' for MARKUP.</strong></p>", 'elex-catmode-rolebased-price' ),
				'id' => 'eh_pricing_discount_adjustment',
			),
			'product_choose_sale_regular' => array(
				'title' => __( ' Price Adjustment applied to ', 'elex-catmode-rolebased-price' ),
				'type' => 'select',
				'css'  => 'padding: 0px;',
				'desc' => __( 'Select where you want to apply the discount/markup. This is applicable to individual product level price adjustment also. If a product does not have sale price, adjustment will be applied only to the regular price.', 'elex-catmode-rolebased-price' ),
				'id' => 'eh_product_choose_sale_regular',
				'options' => array( 'regular_sale' => 'Regular & Sale Price' ),
				'desc_tip' => true,
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			),
			'price_adjustment_options' => array(
				'type' => 'priceadjustmenttable',
				'id' => 'eh_pricing_discount_price_adjustment_options',
				'value' => '',
			),
			'eh_pricing_discount_adjustment_title_end' => array(
				'type' => 'sectionend',
				'id' => 'eh_pricing_discount_adjustment',
			),


			'eh_payment_role_title' => array(
				'type' => 'title',
				'id' => 'eh_pricing_discount_hide_payment_gateways',
			),

			'hide_payment_gateways_catalog'          => array(
				'title'    => __( 'Hide Payment Gateways', 'elex-catmode-rolebased-price' ),
				'type'     => 'multiselect',
				'desc'     => __( 'Select the payment gateway(s) which you want to hide in checkout page', 'elex-catmode-rolebased-price' ),
				'class'    => 'chosen_select',
				'id'       => 'eh_pricing_discount_hide_payment_gateways',
				'options'  => array(),
				'desc_tip' => true,
				'css'      => 'width:30%;',
				'custom_attributes' => array( 'disabled' => 'disabled' ),
			),

			'eh_payment_role_title_end' => array(
				'type' => 'title',
				'id' => 'eh_pricing_discount_hide_payment_gateways',
			),
		);
		/**
		 * To add pricing and payments setting section
		 * 
		 * @since 1.0.0
		 */
	   return apply_filters( 'eh_pricing_discount_pricing_payment_settings', $settings );
	}
	//function to generate price adjustment table
	public function elex_rp_pricing_admin_field_priceadjustmenttable( $settings ) {
		include( 'elex-html-price-adjustment-table.php' );
	}

	public function elex_rp_pricing_admin_field_productdiscountonusers( $settings ) {
		$saved_users = get_option( 'eh_pricing_discount_product_on_users' );
		?>
			<table id="eh_pricing_discount_product_on_users">
				<tr>
					<td style="width: 15.5%; font-size: 14px;"><b><?php esc_html_e( 'Individual Product Discount on Users', 'elex-catmode-rolebased-price' ); ?></b></td>
					<td style="width: 2%; "><span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Select the user(s) for product level price adjustments. The price adjustments can be made in the individual product edit page.', 'elex-catmode-rolebased-price' ); ?>"></span></td>
					<td><select style="width: 31.75em;"  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_product_on_users[users][]" multiple="multiple" style="width: 25%;float: left;">
						<?php
							$user_ids = ( is_array( $saved_users ) && ! empty( $saved_users ) ) ? $saved_users['users'] : array();  // selected user ids
						foreach ( $user_ids as $user_id ) {
							$user = get_user_by( 'id', $user_id );
							if ( is_object( $user ) ) {
								echo '<option value="' . esc_attr( $user_id ) . '"' . selected( true, true, false ) . '>' . esc_html( $user->display_name ) . '(#' . esc_html( $user->ID ) . ') - ' . esc_html( $user->user_email ) . '</option>';
							}
						}
						?>
					</select></td>
				</tr>
			</table>
		<?php
	}

	//function to add a prodcut tab in product page
	public function elex_rp_add_product_tab( $product_data_tabs ) {
		$product_data_tabs['product_price_adjustment'] = array(
			'label' => __( 'Role-based Settings', 'elex-catmode-rolebased-price' ),
			'target' => 'product_price_adjustment_data',
			'class' => array( 'show_if_simple' ),
		);
		return $product_data_tabs;
	}

	public function elex_rp_add_price_adjustment_data_fields() {
		global $woocommerce, $post;
		$settings = array(
			'hide_regular_price' => array(
				'title' => __( 'Hide Regular Price', 'elex-catmode-rolebased-price' ),
				'type' => 'check',
				'desc' => __( 'Check to hide product regular price', 'elex-catmode-rolebased-price' ),
				'css' => 'width:100%',
				'id' => 'eh_pricing_discount_hide_regular_price',
			),
		);
		?>
		<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
		<div id="product_price_adjustment_data" class="panel woocommerce_options_panel hidden">
			<?php include( 'elex-html-product-price-adjustment.php' ); ?>
		</div>
		<?php
	}

	public function elex_rp_add_price_extra_fields() {
			echo '<div id="general_role_based_price" style="padding: 3%; >';
			include( 'elex-html-product-role-based-price.php' );
			echo '</div>';
	}

	public function elex_rp_add_custom_general_fields_save( $post_id ) {
		//to update product hide Add to Cart for unregistered users
		if ( ! ( isset( $_POST['woocommerce_meta_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) ) { // Input var okay.
			return false;
		}
		$product = wc_get_product( $post_id );
		$woocommerce_adjustment_field = ( isset( $_POST['product_adjustment_hide_addtocart_unregistered'] ) && ( 'on' === $_POST['product_adjustment_hide_addtocart_unregistered'] ) ) ? 'yes' : 'no';
		if ( ! empty( $woocommerce_adjustment_field ) ) {
			$product->update_meta_data( 'product_adjustment_hide_addtocart_unregistered', $woocommerce_adjustment_field );
		}
		//to update add to cart placeholder for unregistered users
		if ( isset( $_POST['product_adjustment_hide_addtocart_placeholder_unregistered'] ) ) {
			$product->update_meta_data( 'product_adjustment_hide_addtocart_placeholder_unregistered', wp_kses_post( $_POST['product_adjustment_hide_addtocart_placeholder_unregistered'] ) );
		}
		
		
		//to update product role based hide Add to Cart for user role
		  $woocommerce_product_price_hide_field = '';
		if ( isset( $_POST['eh_pricing_adjustment_product_addtocart_user_role'] ) ) {
			  $woocommerce_product_price_hide_field = array_map( 'sanitize_text_field', wp_unslash( $_POST['eh_pricing_adjustment_product_addtocart_user_role'] ) );
		}
		$product->update_meta_data( 'eh_pricing_adjustment_product_addtocart_user_role', $woocommerce_product_price_hide_field );
		
		//to update hide add  to cart placeholder for user role
		if ( isset( $_POST['product_adjustment_hide_addtocart_placeholder_role'] ) ) {
			$product->update_meta_data( 'product_adjustment_hide_addtocart_placeholder_role', wp_kses_post( $_POST['product_adjustment_hide_addtocart_placeholder_role'] ) );
		}
		
		//to update hide price placeholder for user role
		if ( isset( $_POST['product_adjustment_hide_price_placeholder_role'] ) ) {
			$product->update_meta_data( 'product_adjustment_hide_price_placeholder_role', wp_kses_post( $_POST['product_adjustment_hide_price_placeholder_role'] ) );
		}
		//to update product hide price for unregistered users
		$woocommerce_adjustment_field = ( isset( $_POST['product_adjustment_hide_price_unregistered'] ) && 'on' === $_POST['product_adjustment_hide_price_unregistered'] ) ? 'yes' : 'no';
		if ( ! empty( $woocommerce_adjustment_field ) ) {
			$product->update_meta_data( 'product_adjustment_hide_price_unregistered', $woocommerce_adjustment_field );
		}
		//to update hide price placeholder for unregistered users
		if ( isset( $_POST['product_adjustment_hide_price_placeholder_unregistered'] ) ) {
			$product->update_meta_data( 'product_adjustment_hide_price_placeholder_unregistered', wp_kses_post( $_POST['product_adjustment_hide_price_placeholder_unregistered'] ) );
		}

		//to update product hide price for user role
		  $woocommerce_product_price_field = '';
		if ( isset( $_POST['eh_pricing_adjustment_product_price_user_role'] ) ) {
			$woocommerce_product_price_field = array_map( 'sanitize_text_field', wp_unslash( $_POST['eh_pricing_adjustment_product_price_user_role'] ) );
		}
		  
		$product->update_meta_data( 'eh_pricing_adjustment_product_price_user_role', $woocommerce_product_price_field );

		//to update product based price adjustment
		$woocommerce_adjustment_field = ( isset( $_POST['product_based_price_adjustment'] ) && ( 'on' === $_POST['product_based_price_adjustment'] ) ) ? 'yes' : 'no';
		if ( ! empty( $woocommerce_adjustment_field ) ) {
			$product->update_meta_data( 'product_based_price_adjustment', $woocommerce_adjustment_field );
		}
		

		//to update the product role based adjustment
		  $woocommerce_adjustment_field = '';
		if ( ( isset( $_POST['product_price_adjustment'] ) ) ) {
			$woocommerce_adjustment_field = array();
			$product_adjustment = array();
			$product_adjustment = array_map( 'sanitize_text_field', wp_unslash( $_POST['product_price_adjustment'] ) );
			foreach ( $product_adjustment as $key => $val ) {
				$woocommerce_adjustment_field [ sanitize_text_field( $key ) ] = isset( $_POST['product_price_adjustment'][ $key ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['product_price_adjustment'][ $key ] ) ) : ''; 
			}
		}
		$product->update_meta_data( 'product_price_adjustment', $woocommerce_adjustment_field );

		//to update the product role based adjustment
		$woocommerce_adjustment_field = '';
		if ( ( isset( $_POST['product_price_adjustment_for_users'] ) ) ) {
			$woocommerce_adjustment_field = array();
			$product_adjustment = array();
			$product_adjustment = array_map( 'sanitize_text_field', wp_unslash( $_POST['product_price_adjustment_for_users'] ) );
			foreach ( $product_adjustment as $key => $val ) {
				$woocommerce_adjustment_field [ sanitize_text_field( $key ) ] = isset( $_POST['product_price_adjustment_for_users'][ $key ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['product_price_adjustment_for_users'][ $key ] ) ) : ''; 
			}
		}
		$product->update_meta_data( 'product_price_adjustment_for_users', $woocommerce_adjustment_field );
		
		//to update the product role based price
		$woocommerce_price_field = '';
		if ( ( isset( $_POST['product_role_based_price'] ) ) ) {
			$woocommerce_price_field = array();
			$product_role_price = array();
			$product_role_price  = array_map( 'sanitize_text_field', wp_unslash( $_POST['product_role_based_price'] ) );
			foreach ( $product_role_price as $key => $val ) {
				$woocommerce_price_field [ sanitize_text_field( $key ) ] = isset( $_POST['product_role_based_price'][ $key ] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['product_role_based_price'][ $key ] ) ) : ''; 
			}
		}
		$product->update_meta_data( 'product_role_based_price', $woocommerce_price_field );

		if ( $woocommerce_price_field ) {
			foreach ( $woocommerce_price_field as $key => $val ) {
				if ( array_key_exists( 'role_price', $val ) ) {
				 $product->update_meta_data( 'product_role_based_price_' . $key, $woocommerce_price_field[ $key ]['role_price'] );
				}
			}
		}
		$product->save();
	}

	//function to generate price adjustment table
	public function elex_rp_pricing_category_adjustment_fields( $tag ) {
		$t_id = $tag->term_id;
		$cat_meta = get_option( "category_$t_id" );
		print_r( $cat_meta );
		print_r( $t_id );
		print_r( $tag );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="meta-color"><?php esc_html_e( 'Category Name Background Color' ); ?></label></th>
			<td>
				<div id="colorpicker">
					<input type="text" name="cat_meta[catBG]" class="colorpicker" size="3" style="width:20%;" value="<?php echo ( isset( $cat_meta['catBG'] ) ) ? esc_html( $cat_meta['catBG'] ) : '#fff'; ?>" />
				</div>
				<br />
				<span class="description"></span>
				<br />
			</td>
		</tr>
		<?php
	}
}

new Elex_Pricing_Discount_Settings();

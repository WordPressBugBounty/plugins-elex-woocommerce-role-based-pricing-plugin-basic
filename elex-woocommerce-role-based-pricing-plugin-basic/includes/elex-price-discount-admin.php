<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class Elex_Price_Discount_Admin {
		public $sales_method;
		public $role_price_adjustment;
		public $current_user_role; 
		public $current_user_mail; 
		public $user_id;
		public $multiple_user_roles  ;
		public $enable_role_tax ;
		public $role_tax_option ;
		public $tax_user_role ;
		public $price_suffix_option;
		public $general_price_suffix;
		public $role_price_suffix ;
		public $suffix_user_role ;
		public $price_suffix_user_role ;
		public $replace_add_to_cart;
		public $replace_add_to_cart_button_text_product ;
		public $replace_add_to_cart_button_text_shop ;
		public $replace_add_to_cart_button_url_shop;
		public $replace_add_to_cart_user_role ;
		public $replace_add_to_cart_user_role_button_text_product ;
		public $replace_add_to_cart_user_role_button_text_shop ;
		public $replace_add_to_cart_user_role_url_shop ;
		public $individual_product_adjustment_roles ;
		public $individual_product_adjustment_for_users ;
	
	public function __construct( $execute = true ) {

		$this->sales_method = 'regular';
		if ( true === $execute ) {
			$post_type = ! empty( filter_input( INPUT_GET, 'post_type' ) ) ? filter_input( INPUT_GET, 'post_type' ) : '';
			if ( ! is_admin() || 'product' !== $post_type ) {
				$this->elex_rp_add_filter_for_get_price();
			}
			 $theme = wp_get_theme(); // gets the current theme
			if ( ! empty( $theme ) ) {
				if ( 'Twenty Twenty-Three' === $theme->name || 'Twenty Twenty-Two' === $theme->name ) {
					add_action( 'woocommerce_product_meta_start', array( $this, 'elex_rp_product_page_remove_add_to_cart_option' ) );
				} else {
					add_action( 'woocommerce_single_product_summary', array( $this, 'elex_rp_product_page_remove_add_to_cart_option' ) ); //function to remove add to cart at product page
				}
			}
			
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'elex_rp_shop_remove_add_to_cart' ), 100, 2 ); // function to remove add to cart from shop page
		   // add_action('wp_head', array($this, 'custom_css_for_add_to_cart'));

			add_filter( 'woocommerce_is_purchasable', array( &$this, 'elex_rp_is_product_purchasable' ), 10, 2 ); //to hide add to cart button when price is hidden
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'elex_rp_add_to_cart_text_url_replace' ), 1, 2 ); //to replace add to cart with user defined url
			add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'elex_rp_add_to_cart_text_content_replace' ), 1, 1 ); //to replace add to cart with user defined placeholder text for product page
			/**
			 * Modify the price HTML display based on plugin activation.
			 * 
			 * Hook: 'woocommerce_get_price_html'
			 *
			 * @since 2.9.7
			 */
			$is_price_history_active = in_array( 'wc-price-history/plugin.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
			// Set the priority based on whether the plugin is active
			$priority = $is_price_history_active ? 1 : 99;
			// Add the filter with the appropriate priority
			add_filter( 'woocommerce_get_price_html', array( $this, 'elex_rp_get_price_html' ), $priority, 2 );

			//------------
			add_filter( 'woocommerce_product_is_on_sale', array( $this, 'elex_rp_product_is_on_sale' ), 99, 2 );
			add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'elex_rp_view_product_text' ), 99, 2 );
		}
		//----for price filter
		add_filter( 'woocommerce_price_filter_widget_min_amount', array( $this, 'elex_rp_get_min_price' ), 100, 1 );
		add_filter( 'woocommerce_price_filter_widget_max_amount', array( $this, 'elex_rp_get_max_price' ), 100, 1 );
		//----------
		add_action( 'wp', array( $this, 'elex_rp_hide_cart_checkout_pages' ) );
				   
		$this->init_fields();
	}

	public function elex_rp_hide_cart_checkout_pages() {
		$hide = false;
		if ( is_user_logged_in() ) {
			$remove_settings_cart_roles = get_option( 'eh_pricing_discount_cart_user_role_remove_cart_checkout' );
			if ( is_array( $remove_settings_cart_roles ) && in_array( $this->current_user_role, $remove_settings_cart_roles ) ) {
				$hide = true;
			}
		} else {
			if ( 'yes' === get_option( 'eh_pricing_discount_cart_unregistered_user_remove_cart_checkout' ) ) {
				$hide = true;
			}
		}
		$cart     = is_page( wc_get_page_id( 'cart' ) );
		$checkout = is_page( wc_get_page_id( 'checkout' ) );

		wp_reset_query();
		if ( $hide && ( $cart || $checkout ) ) {

			wp_redirect( home_url() );
			exit;
		}
	}

	public function elex_rp_shop_remove_add_to_cart( $args, $product ) {
		if ( $this->elex_rp_get_product_type( $product ) === 'variable' || $this->elex_rp_get_product_type( $product ) === 'grouped' ) {
			return $args;
		}
		$product_id = $this->elex_rp_get_product_id( $product );
		$add_to_cart_link = $args;
		
		if ( is_user_logged_in() ) {
			$remove_settings_cart_roles = get_option( 'eh_pricing_discount_cart_user_role' );
			$remove_product_cart_roles = $product->get_meta( 'eh_pricing_adjustment_product_addtocart_user_role' );
			if ( is_array( $remove_product_cart_roles ) && in_array( $this->current_user_role, $remove_product_cart_roles ) ) { 
				
				$add_to_cart_link = '<center>' . $this->elex_rp_get_add_to_cart_product_placeholder_text( $product_id ) . '<center>';
				
			} elseif ( is_array( $remove_settings_cart_roles ) && in_array( $this->current_user_role, $remove_settings_cart_roles ) ) { 
				$placeholder_text = $this->elex_rp_get_add_to_cart_placeholder_text();
				$add_to_cart_link = '<center>' . wp_kses_post( $placeholder_text ) . '</center>';
			}
		} else {
			if ( 'yes' === ( $product->get_meta( 'product_adjustment_hide_addtocart_unregistered' ) ) ) {
				$add_to_cart_link = '<center>' . $this->elex_rp_get_add_to_cart_product_placeholder_text( $product_id ) . '<center>';
			} elseif ( 'yes' === get_option( 'eh_pricing_discount_cart_unregistered_user' ) ) {
				$placeholder_text = $this->elex_rp_get_add_to_cart_placeholder_text();
				$add_to_cart_link = '<center>' . wp_kses_post( $placeholder_text ) . '</center>';
				
			}
		}
		
		return $add_to_cart_link;
	}



	public function elex_rp_get_min_price( $price ) {
		$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
		if ( is_array( $user_roles ) && in_array( $this->current_user_role, $user_roles ) ) {
			$min_prices = $this->elex_rp_variable_product_amount();
			$min_prices = array_map(
				function( $prices ) {
					return min( $prices );
				}, 
				$min_prices
			);
			$price = ! empty( $min_prices ) ? min( $min_prices ) : $price;
		}
		return $price;
	}

	public function elex_rp_get_max_price( $price ) {
		$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
		if ( is_array( $user_roles ) && in_array( $this->current_user_role, $user_roles ) ) {
			$max_prices = $this->elex_rp_variable_product_amount();
			$max_prices = array_map(
				function( $prices ) {
					return max( $prices );
				}, 
				$max_prices
			);
			$price = ! empty( $max_prices ) ? max( $max_prices ) : $price;
		}
		return $price;
	}
	
	public function elex_rp_variable_product_amount() {
		global $wpdb;
		$table_name = $wpdb->prefix;
		$max_amount_query = "SELECT DISTINCT ID FROM {$table_name}posts LEFT JOIN {$table_name}term_relationships on {$table_name}term_relationships.object_id={$table_name}posts.ID LEFT JOIN {$table_name}term_taxonomy on {$table_name}term_taxonomy.term_taxonomy_id  = {$table_name}term_relationships.term_taxonomy_id LEFT JOIN {$table_name}terms on {$table_name}terms.term_id={$table_name}term_taxonomy.term_id LEFT JOIN {$table_name}postmeta on {$table_name}postmeta.post_id={$table_name}posts.ID WHERE taxonomy='product_type'  AND slug  IN ('variable') AND post_status = 'publish'";
		$all_product_data = $wpdb->get_results( ( $wpdb->prepare( '%1s', $max_amount_query ) ? stripslashes( $wpdb->prepare( '%1s', $max_amount_query ) ) : $wpdb->prepare( '%s', '' ) ), ARRAY_A );           
		$max_prices = array();
		for ( $i = 0; $i < count( $all_product_data ); $i++ ) {
			$p_id = $all_product_data[ $i ]['ID'];
			$product_data = wc_get_product( $p_id );
			if ( $product_data->is_type( 'variable' ) ) {
				$prices = $product_data->get_variation_prices( true );
				if ( empty( $prices['price'] ) ) {
					continue;
				}
				foreach ( $prices['price'] as $pid => $old_price ) {
					$pobj = wc_get_product( $pid );
					$prices['price'][ $pid ] = wc_get_price_to_display( $pobj );
				}
				$max_prices[ $i ] = $prices['price'];
			}
		}
		return $max_prices;
	}
	public function elex_rp_view_product_text( $text, $product ) {
		if ( $this->elex_rp_is_hide_price( $product ) === true ) {
			$text = 'Read more';
		}
		return $text;
	}

	public function elex_rp_product_is_on_sale( $on_sale, $product ) {
		if ( $this->elex_rp_is_hide_price( $product ) === true || $this->elex_rp_is_hide_regular_price( $product ) ) {
			$on_sale = false;
		} else {
			if ( $this->elex_rp_get_product_type( $product ) !== 'grouped' ) {
				$regular_price = $product->get_regular_price();
				$sale_price = $product->get_price();
				if ( empty( $sale_price ) ) {
					$on_sale = false;
				} else {
					if ( $sale_price < $regular_price ) {
						$on_sale = true;
					}
				}
			}
		}
		return $on_sale;
	}


	// function to hide simple product from grouped product
	public function elex_rp_add_filter_for_get_price() {
		if ( WC()->version < '2.7.0' ) {
			if ( 'regular' === $this->sales_method ) {
				add_filter( 'woocommerce_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product sale price
			} else {
				add_filter( 'woocommerce_get_sale_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product sale price
			}
			add_filter( 'woocommerce_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product price at all level
		} else {
			if ( 'regular' === $this->sales_method ) {
				add_filter( 'woocommerce_product_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
				add_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
				add_filter( 'woocommerce_get_variation_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			} else {
				add_filter( 'woocommerce_product_get_sale_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			}
			add_filter( 'woocommerce_product_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			add_filter( 'woocommerce_product_variation_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
		}
	}

	public function elex_rp_remove_filter_for_get_price() {
		if ( WC()->version < '2.7.0' ) {
			if ( 'regular' === $this->sales_method ) {
				remove_filter( 'woocommerce_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product sale price
			} else {
				remove_filter( 'woocommerce_get_sale_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product sale price
			}
			remove_filter( 'woocommerce_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 ); //function to modify product price at all level
		} else {
			if ( 'regular' === $this->sales_method ) {
				remove_filter( 'woocommerce_product_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
				remove_filter( 'woocommerce_product_variation_get_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
				remove_filter( 'woocommerce_get_variation_regular_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			} else {
				remove_filter( 'woocommerce_product_get_sale_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			}
			remove_filter( 'woocommerce_product_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
			remove_filter( 'woocommerce_product_variation_get_price', array( $this, 'elex_rp_get_price' ), 99, 2 );
		}
	}


	public function elex_rp_get_add_to_cart_placeholder_text() {
		if ( is_user_logged_in() ) {
			$add_to_cart_text = get_option( 'eh_pricing_discount_cart_user_role_text' );
		} else {
			$add_to_cart_text = get_option( 'eh_pricing_discount_cart_unregistered_user_text' );
		}

		if ( ! empty( $add_to_cart_text ) ) {
			return $add_to_cart_text;
		}
	}
	public function elex_rp_get_add_to_cart_product_placeholder_text( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( is_user_logged_in() ) {
			$add_to_cart_text = $product->get_meta( 'product_adjustment_hide_addtocart_placeholder_role' );
		} else {
			$add_to_cart_text = $product->get_meta( 'product_adjustment_hide_addtocart_placeholder_unregistered' );
		}

		if ( ! empty( $add_to_cart_text ) ) {
			
			echo wp_kses_post( $add_to_cart_text );
		}
	
	}

	public function elex_rp_product_page_remove_add_to_cart_option() {
		global $product;
		$temp_data = $this->elex_rp_get_product_type( $product );
		$product_id = $this->elex_rp_get_product_id( $product );
		if ( 'variation' === $temp_data ) {
			$product_id = $this->elex_rp_get_product_parent_id( $product );
		}
		$hide_price = $this->elex_rp_is_hide_price( $product );
		if ( $this->elex_rp_get_product_type( $product ) === 'simple' ) {
			if ( is_user_logged_in() ) {
				$remove_settings_cart_roles = get_option( 'eh_pricing_discount_cart_user_role' );
				$remove_product_cart_roles = $product->get_meta( 'eh_pricing_adjustment_product_addtocart_user_role', true );
				$replace_cart_user_role = get_option( 'eh_pricing_discount_replace_cart_user_role' );
				if ( is_array( $remove_product_cart_roles ) && in_array( $this->current_user_role, $remove_product_cart_roles ) ) {
					$this->elex_rp_remove_add_to_cart_action_product_page( $product );
					$this->elex_rp_get_add_to_cart_product_placeholder_text( $product_id );
				} elseif ( is_array( $remove_settings_cart_roles ) && in_array( $this->current_user_role, $remove_settings_cart_roles ) ) {
					$this->elex_rp_remove_add_to_cart_action_product_page( $product );
					echo wp_kses_post( $this->elex_rp_get_add_to_cart_placeholder_text() );
				} elseif ( is_array( $replace_cart_user_role ) && in_array( $this->current_user_role, $replace_cart_user_role ) && ! $hide_price ) {
					if ( '' !== $this->replace_add_to_cart_user_role_url_shop && '' !== $this->replace_add_to_cart_user_role_button_text_product ) {
						$this->elex_rp_remove_add_to_cart_action_product_page( $product );
						$this->elex_rp_redirect_addtocart_product_page( $this->replace_add_to_cart_user_role_url_shop, $this->replace_add_to_cart_user_role_button_text_product );
					}
				}
			} else {
				if ( 'yes' === ( $product->get_meta( 'product_adjustment_hide_addtocart_unregistered' ) ) ) {
					$this->elex_rp_remove_add_to_cart_action_product_page( $product );
					$this->elex_rp_get_add_to_cart_product_placeholder_text( $product_id );
				} elseif ( 'yes' === get_option( 'eh_pricing_discount_cart_unregistered_user' ) ) {
					$this->elex_rp_remove_add_to_cart_action_product_page( $product );
					echo  wp_kses_post( $this->elex_rp_get_add_to_cart_placeholder_text() );
				} elseif ( 'yes' === get_option( 'eh_pricing_discount_replace_cart_unregistered_user' ) && ! $hide_price ) {
					if ( '' !== $this->replace_add_to_cart_button_url_shop && '' !== $this->replace_add_to_cart_button_text_product ) {
						$this->elex_rp_remove_add_to_cart_action_product_page( $product );
						$this->elex_rp_redirect_addtocart_product_page( $this->replace_add_to_cart_button_url_shop, $this->replace_add_to_cart_button_text_product );
					}
				}
			}
		}
	}
	
	public function elex_rp_remove_add_to_cart_action_product_page( $product ) {
		if ( $this->elex_rp_get_product_type( $product ) === 'variable' ) {
			remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
		} else {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}
		/**
		 * To check plugin is active or not
		 * 
		 * @since 1.0.0
		 */
		if ( ! in_array( 'elex_request_a_quote_premium/class-elex-request-a-quote-premium.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && ! in_array( 'elex_request_a_quote/class-elex-request-a-quote.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			?>
			<style>
				.single_add_to_cart_button {
					display: none !important;
				}
			</style>
			<?php
		}
	}
			
	public function elex_rp_redirect_addtocart_product_page( $url_product_page, $button_text_product_page ) {
		$secure = strpos( 'https://', $url_product_page );
		$url_product_page = str_replace( 'https://', '', $url_product_page );
		$url_product_page = str_replace( 'http://', '', $url_product_page );
		$suff = ( false === $secure ) ? 'http://' : 'https://';
		?>
		<button class="btn btn-success" onclick=" window.open('<?php echo esc_html( $suff ) . esc_html( $url_product_page ); ?>','_self' )"><?php echo esc_html( $button_text_product_page ); ?></button>
		<?php
	}

	public function init_fields() {
		$this->role_price_adjustment = get_option( 'eh_pricing_discount_price_adjustment_options', array() );
		$this->current_user_role = $this->elex_rp_get_priority_user_role( wp_get_current_user()->roles, $this->role_price_adjustment );
		$this->current_user_mail = wp_get_current_user()->user_email;
		$this->user_id = get_current_user_id();
		$this->multiple_user_roles = ! empty( wp_get_current_user()->roles ) ? wp_get_current_user()->roles : array( 'unregistered_user' );
		$this->enable_role_tax = get_option( 'eh_pricing_discount_enable_tax_options' ) === 'yes' ? true : false;
		$this->role_tax_option = get_option( 'eh_pricing_discount_price_tax_options', array() );
		$this->tax_user_role = $this->elex_rp_get_priority_user_role( wp_get_current_user()->roles, $this->role_tax_option );
		$this->price_suffix_option = get_option( 'eh_pricing_discount_enable_price_suffix', 'none' );
		$this->general_price_suffix = get_option( 'eh_pricing_discount_price_general_price_suffix', '' );
		$this->role_price_suffix = get_option( 'eh_pricing_discount_role_price_suffix', array() );
		$this->suffix_user_role = $this->elex_rp_get_priority_user_role( wp_get_current_user()->roles, $this->role_price_suffix );
		$this->price_suffix_user_role = '' !== $this->suffix_user_role ? $this->suffix_user_role : 'unregistered_user';
		$this->replace_add_to_cart = get_option( 'eh_pricing_discount_replace_cart_unregistered_user' ) === 'yes' ? true : false;
		$this->replace_add_to_cart_button_text_product = get_option( 'eh_pricing_discount_replace_cart_unregistered_user_text_product', '' );
		$this->replace_add_to_cart_button_text_shop = get_option( 'eh_pricing_discount_replace_cart_unregistered_user_text_shop', '' );
		$this->replace_add_to_cart_button_url_shop = get_option( 'eh_pricing_discount_replace_cart_unregistered_user_url_shop', '' );
		$this->replace_add_to_cart_user_role = get_option( 'eh_pricing_discount_replace_cart_user_role', array() );
		$this->replace_add_to_cart_user_role_button_text_product = get_option( 'eh_pricing_discount_replace_cart_user_role_text_product', '' );
		$this->replace_add_to_cart_user_role_button_text_shop = get_option( 'eh_pricing_discount_replace_cart_user_role_text_shop', '' );
		$this->replace_add_to_cart_user_role_url_shop = get_option( 'eh_pricing_discount_replace_cart_user_role_url_shop', '' );
		$this->individual_product_adjustment_roles = get_option( 'eh_pricing_discount_product_price_user_role', array() );
		$this->individual_product_adjustment_for_users = get_option( 'eh_pricing_discount_product_on_users', array() );
	}

	//function to determine the user role to use in case of multiple user roles for one user
	public function elex_rp_get_priority_user_role( $user_roles, $role_priority_list ) {
		if ( is_user_logged_in() ) {
			if ( isset( $role_priority_list['roles'] ) && ! empty( $role_priority_list['roles'] ) ) {
				foreach ( $role_priority_list as $id => $value ) {
					if ( in_array( $id, $user_roles ) ) {
						return $id;
					}
				}
			} else {
				// Return the first element in the array, irrespective of index.
				$user_role = is_array( $user_roles ) && ! empty( $user_roles ) ? array_values( $user_roles )[0] : 'unregistered_user';
				return $user_role ;
			}
		} else {
			return 'unregistered_user';
		}
	}

	//function to replace add to cart with another url for user role and unregistered user 
	public function elex_rp_add_to_cart_text_url_replace( $link, $product ) {
		$temp_data = $this->elex_rp_get_product_type( $product );
		$product_id = $this->elex_rp_get_product_id( $product );
		if ( 'variation' === $temp_data ) {
			$product_id = $this->elex_rp_get_product_parent_id( $product );
		}
		$hide_price = $this->elex_rp_is_hide_price( $product );
		$cart_text_content = $link;
		if ( 'simple' === $temp_data ) {
			if ( ( is_user_logged_in() ) ) {
				$role_shop_btn_text = $product->get_meta( 'product_adjustment_customize_addtocart_shop_btn_text_role' );
				$role_btn_url = $product->get_meta( 'product_adjustment_customize_addtocart_btn_url_role' );
				if ( is_array( $this->replace_add_to_cart_user_role ) && in_array( $this->current_user_role, $this->replace_add_to_cart_user_role ) && '' !== $this->replace_add_to_cart_user_role_button_text_shop && ! $hide_price ) {
					if ( empty( $this->replace_add_to_cart_user_role_url_shop ) ) {
						$cart_text_content = $this-> elex_rp_replace_add_cart_text_shop( $cart_text_content, $this->replace_add_to_cart_user_role_button_text_shop );
					} else {
						$cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url( $cart_text_content, $this->replace_add_to_cart_user_role_button_text_shop, $this->replace_add_to_cart_user_role_url_shop );
					}
				}
			} elseif ( ! is_user_logged_in() ) {
	
				if ( $this->replace_add_to_cart && '' !== $this->replace_add_to_cart_button_text_shop && ! $hide_price ) {
					if ( empty( $this->replace_add_to_cart_button_url_shop ) ) {
						$cart_text_content = $this-> elex_rp_replace_add_cart_text_shop( $cart_text_content, $this->replace_add_to_cart_button_text_shop );
					} else {
						$cart_text_content = $this-> elex_rp_replace_add_cart_text_shop_with_url( $cart_text_content, $this->replace_add_to_cart_button_text_shop, $this->replace_add_to_cart_button_url_shop );
					}
				}
			}
		}
		return $cart_text_content;
	}
	public function elex_rp_replace_add_cart_text_shop( $cart_text_content, $shop_addtocart_text ) {
		$cart_text_content = str_replace( 'Add to cart', $shop_addtocart_text, $cart_text_content );
		$cart_text_content = str_replace( 'Select options', $shop_addtocart_text, $cart_text_content );
		$cart_text_content = str_replace( 'View products', $shop_addtocart_text, $cart_text_content );
		return $cart_text_content;
	}
	public function elex_rp_replace_add_cart_text_shop_with_url( $cart_text_content, $shop_addtocart_text, $url ) {
		$secure = strpos( 'https://', $url );
		$url = str_replace( 'https://', '', $url );
		$url = str_replace( 'http://', '', $url );
		$suff = ( false === $secure ) ? 'http://' : 'https://';
		$cart_text_content = '<div style="width:100%;"><center><button class="btn btn-success" style="margin-top: 10px;height: 40px;padding: 0 20px; text-wrap: nowrap;" onclick="window.open(\'' . esc_attr( $suff . $url ) . '\', \'_self\')">' . esc_attr( $shop_addtocart_text ) . '</button></center></div>';
		return $cart_text_content;
	}



	//function to edit add to cart text of product page with placeholder text when replace add to cart button is selected

	public function elex_rp_add_to_cart_text_content_replace( $text ) {
		$cart_text_content = $text;
		 global $product;
		if ( $this->elex_rp_get_product_type( $product ) === 'variable' || $this->elex_rp_get_product_type( $product ) === 'grouped' ) {
			return $cart_text_content;
		}
	   
		$product_id = $this->elex_rp_get_product_id( $product );
		if ( ( is_user_logged_in() ) ) {
			$individual_prod_btn_text = $product->get_meta( 'product_adjustment_customize_addtocart_prod_btn_text_role' );
			$replace_addtocart = $product->get_meta( 'eh_pricing_adjustment_product_customize_addtocart_user_role' );
			if ( is_array( $replace_addtocart ) && in_array( $this->current_user_role, $replace_addtocart ) && '' !== $individual_prod_btn_text ) {
				$cart_text_content = $individual_prod_btn_text;
			} elseif ( is_array( $this->replace_add_to_cart_user_role ) && in_array( $this->current_user_role, $this->replace_add_to_cart_user_role ) && '' !== $this->replace_add_to_cart_user_role_button_text_product ) {
				$cart_text_content = $this->replace_add_to_cart_user_role_button_text_product;
			}
		} elseif ( ! is_user_logged_in() ) {
			$individual_prod_btn_text = $product->get_meta( 'product_adjustment_customize_addtocart_prod_btn_text_unregistered' );
			if ( 'yes' === ( $product->get_meta( 'product_adjustment_customize_addtocart_unregistered' ) ) && '' !== $individual_prod_btn_text ) {
				$cart_text_content = $individual_prod_btn_text;
			} elseif ( $this->replace_add_to_cart && '' !== $this->replace_add_to_cart_button_text_product ) {
				$cart_text_content = $this->replace_add_to_cart_button_text_product;
			}
		}
		return $cart_text_content;
	}

	//to get category ids for a product
	public function elex_rp_get_product_category_using_id( $prod_id ) {
		$terms = get_the_terms( $prod_id, 'product_cat' );
		if ( $terms ) {
			$cats_ids_array = array();
			foreach ( $terms as $key => $term ) {
				array_push( $cats_ids_array, $term->term_id );
				$term2 = $term;

				if ( ! in_array( $term2->parent, $cats_ids_array ) ) {
					while ( $term2->parent > 0 ) {
						array_push( $cats_ids_array, $term2->parent );
						$term2 = get_term_by( 'id', $term2->parent, 'product_cat' );
					}
				}
			}
			return $cats_ids_array;
		}
		return array();
	}

	public function elex_rp_get_price( $price = '', $product = null ) {
		//change backend order product price for specific roles
		$temp_price = $price;
		if ( isset( $_POST['security'] ) && isset( $_POST['order_id'] ) && wp_verify_nonce( sanitize_text_field( $_POST['security'] ), 'order-item' ) ) {
			$order = wc_get_order( sanitize_text_field( $_POST['order_id'] ) );
			$this->user_id = $order->get_customer_id();
			if ( $this->user_id ) {
				$user_meta = get_userdata( $this->user_id );
				$user_roles = $user_meta->roles;
				$this->multiple_user_roles = $user_roles;
				$this->current_user_mail = $user_meta->user_email;
				$this->current_user_role = $user_roles[0];
			}
		}
		
		$current_user_email = $this->current_user_mail;

		if ( doing_filter( 'woocommerce_get_cart_item_from_session' ) ) {
			return $price;
		}
		if ( $this->elex_rp_is_hide_price( $product ) ) {
			if ( $this->elex_rp_get_product_type( $product ) === 'variation' ) {
				remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
				return false;
			}
			if ( $this->elex_rp_is_price_hidden_in_product_meta( $product ) ) {
				$price = '';
			} else {
				$price = '';
			}
			return $price;
		}

		if ( $this->elex_rp_get_product_type( $product ) !== 'simple' ) {
			return $price;
		}
		
		//If decimal seperator is ',' and thousand seperator is '.'
		$dec_seperator = wc_get_price_decimal_separator();
		
		$pid = $this->elex_rp_get_product_id( $product );

		$temp_data = $this->elex_rp_get_product_type( $product );
		$exc_price = ! empty( $temp_price ) && is_numeric( $temp_price ) ? $temp_price : 1;
		$product_actual_price = ! empty( get_post_meta( $pid , '_price' , true ) ) && is_numeric( get_post_meta( $pid , '_price' , true ) ) ? get_post_meta( $pid , '_price' , true ) : 1;
		if ( $product_actual_price > $exc_price ) {
			$exchange_rate = ! empty( $temp_price ) && ! empty( $product_actual_price ) ? (float) $exc_price / (float) $product_actual_price : 1;   
		} else {
			$exchange_rate = 1;   
		}
	
		/**
		 * To skip the product if it has sale price
		 * 
		 * @since 1.0.0
		 */
		if ( apply_filters( 'xa_pbu_skip_product', false, $pid ) !== false || ( apply_filters( 'xa_pbu_skip_product_on_sale', false, $pid ) !== false ) ) {
			//Role Based Price (individual product page price change)
			if ( 'variation' === $temp_data ) {
				$pid = $this->elex_rp_get_product_parent_id( $product );
			}
			
			$enforce_button_check_for_product = $product->get_meta( 'product_based_price_adjustment' );
			$product_price_adjustment = $product->get_meta( 'product_price_adjustment' );
			if ( ! ( 'yes' === $enforce_button_check_for_product && isset( $product_price_adjustment[ $this->current_user_role ] ) && isset( $product_price_adjustment[ $this->current_user_role ]['role_price'] ) && 'on' === $product_price_adjustment[ $this->current_user_role ]['role_price'] ) ) {
				$product_user_price = $product->get_meta( 'product_role_based_price' );
				if ( is_array( $product_user_price ) && isset( $product_user_price[0] ) && ! empty( $product_user_price[0] ) ) {
					$product_user_price = $product_user_price[0];
				}
				if ( ! empty( $product_user_price ) && is_array( $this->individual_product_adjustment_roles ) && in_array( $this->current_user_role, $this->individual_product_adjustment_roles ) ) {
					if ( isset( $product_user_price[ $this->current_user_role ] ) ) {
						$product_user_price_value = $product_user_price[ $this->current_user_role ]['role_price'];
						$product_user_price_value = $this->elex_rp_fix_price_decimal_format( $dec_seperator, $product_user_price_value ); 
						if ( is_numeric( $product_user_price_value ) ) {
							$price = $product_user_price_value;
						}
					}
				}
				return $price;
			}
		}

		if ( ( is_array( $this->individual_product_adjustment_roles ) || is_array( $this->individual_product_adjustment_for_users ) ) && ( in_array( $this->current_user_role, $this->individual_product_adjustment_roles ) || ! empty( array_diff( $this->multiple_user_roles, $this->individual_product_adjustment_roles ) ) || ! empty( $product->get_meta( 'product_role_based_price_user_' . $current_user_email ) ) ) ) {
		
			$count_multiple_role = count( $this->multiple_user_roles );
			$multiple_role_option = get_option( 'eh_pricing_discount_multiple_role_price' );
			$role_value = array();
			$product_user_price = $product->get_meta( 'product_role_based_price_' . $this->current_user_role );
			$product_users_price = ! empty( $product->get_meta( 'product_role_based_price_user_' . $current_user_email ) ) ? $product->get_meta( 'product_role_based_price_user_' . $current_user_email ) : '';
			if ( is_array( $this->individual_product_adjustment_for_users ) && ! empty( $this->individual_product_adjustment_for_users ) && ! empty( $product_users_price[0] ) ) { 
				$product_user_price = $product_users_price;
			} elseif ( $count_multiple_role > 1 ) {
			 $consolidate_price = 0;
			
				foreach ( $this->multiple_user_roles as $multiple_role_key => $multiple_role_val ) {
					if ( in_array( $multiple_role_val, $this->individual_product_adjustment_roles ) ) {
					 $product_users_role_price = $product->get_meta( 'product_role_based_price_' . $multiple_role_val );
					
						if ( is_array( $product_users_role_price ) && isset( $product_users_role_price[0] ) && ! empty( $product_users_role_price[0] ) ) {
							  $role_value[] = $product_users_role_price[0];
						}
						if ( ! is_array( $product_users_role_price ) && ! empty( $product_users_role_price ) ) {
							$role_value[] = $product_users_role_price;
						}
					} 
				}   
			
				if ( is_array( $role_value ) && ! empty( $role_value ) ) {
					$decimal_separator = wc_get_price_decimal_separator();
					if ( ',' === $decimal_separator ) {
						$role_value = array_map(
							function( $value ) {
							return floatval( str_replace( ',', '.', $value ) );
							},
							$role_value
						);
					}
					 asort( $role_value );
					 $min_role_price = current( $role_value ); 
					 $max_role_price = end( $role_value );
		
					if ( 'max_role_price' === $multiple_role_option ) {
						$product_user_price = $max_role_price;
					} elseif ( 'min_role_price' === $multiple_role_option ) {
						$product_user_price = $min_role_price;
					} else {
						foreach ( $role_value as $price_val ) {
			
							  $consolidate_price += $price_val;
						}
						$product_user_price = $consolidate_price;
					}
				}
			} else {
				$product_user_price = $product->get_meta( 'product_role_based_price_' . $this->current_user_role );
				
				
			}

			if ( is_array( $product_user_price ) && isset( $product_user_price[0] ) && ! empty( $product_user_price[0] ) ) {
				if ( $this->current_user_role ) {   
					$product_user_price_value = $product_user_price;
					if ( ! empty( $product_user_price_value[0] ) ) {
						$price_value = $product_user_price_value[0];
						if ( preg_match( '/^[0-9,\.]/', $price_value ) !== false ) {
							$price_value = preg_replace( '/,/', '.', $price_value );
						}
						$val = floatval( $price_value );
						if ( is_numeric( $val ) ) {
							$price = $val; 
						}
					}
					return $price;
				}
			} elseif ( ! is_array( $product_user_price ) && ! empty( $product_user_price ) ) {
				$price_value = $product_user_price;

				if ( preg_match( '/^[0-9,\.]/', $price_value ) !== false ) {
					$price_value = preg_replace( '/,/', '.', $price_value );
				}
				
				$val = floatval( $price_value );
				if ( is_numeric( $val ) ) {
					$price = $val * $exchange_rate; 
				}
				if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) && wcml_is_multi_currency_on() ) {
					global $woocommerce_wpml;
					if ( isset( $woocommerce_wpml->settings['enable_multi_currency'] ) && 1 === $woocommerce_wpml->settings['enable_multi_currency'] ) {

						$current_currency = $woocommerce_wpml->multi_currency->get_client_currency();
						$store_currency = get_option( 'woocommerce_currency' );

						$exchange_rate = $woocommerce_wpml->multi_currency->get_exchange_rates( $store_currency , $current_currency );
						$converted_price = $price * $exchange_rate[ $current_currency ];

						return $converted_price;
					} else {
						return $price;
					}
				}
				return $price;
			}
		}
		if ( empty( $price ) ) {
			return $price;
		}
		if ( 'variation' === $temp_data ) {
			$pid = $this->elex_rp_get_product_parent_id( $product );
		}
		   
		//----------------------analyse this for bugs
		//price adjustment display for discount when price adjustment on both regular and sale price
		if ( 'regular' === $this->sales_method && ( doing_filter( 'woocommerce_product_get_regular_price' ) || doing_filter( 'woocommerce_product_variation_get_regular_price' ) || doing_filter( 'woocommerce_get_variation_regular_price' ) ) ) {
			$adjustment_value = 0;
				
			$adjustment_value = $this->elex_rp_get_adjustment_for_individual_products( $pid, $price );
			if ( 'no_amount' === $adjustment_value ) {
				$adjustment_value = 0;
			} else {
				$price += $adjustment_value;
				$this->elex_rp_add_filter_for_get_price();
				return $price;
			}
			if ( is_array( $this->individual_product_adjustment_roles ) && in_array( $this->current_user_role, $this->individual_product_adjustment_roles ) ) {
				//common page adjustment
				if ( 'variation' === $temp_data ) {
					$prdct_id = $this->elex_rp_get_product_category_using_id( $this->elex_rp_get_product_parent_id( $product ) );
				} else {
					if ( WC()->version < '2.7.0' ) {
						$temp_post_id = $product->post->ID;
					} else {
						$temp_post_id = $product->get_id();
					}
					$prdct_id = $this->elex_rp_get_product_category_using_id( $temp_post_id );
				}

				$price = $this->elex_rp_get_adjustment_amount( $price, $prdct_id, $temp_data, $adjustment_value );
				$this->elex_rp_add_filter_for_get_price();
				return $price;
			} else {
				$temp_data = $this->elex_rp_get_product_type( $product );
				if ( 'variation' === $temp_data ) {
					$prdct_id = $this->elex_rp_get_product_category_using_id( $this->elex_rp_get_product_parent_id( $product ) );
				} else {
					if ( WC()->version < '2.7.0' ) {
						$temp_post_id = $product->post->ID;
					} else {
						$temp_post_id = $product->get_id();
					}
					$prdct_id = $this->elex_rp_get_product_category_using_id( $temp_post_id );
				}
				$adjustment_value = 0;
			   $price = $this->elex_rp_get_adjustment_amount( $price, $prdct_id, $temp_data, $adjustment_value );
				$this->elex_rp_add_filter_for_get_price();
				return $price;
			}
		}
		//------------------------
		$this->elex_rp_remove_filter_for_get_price();
		$pid = $this->elex_rp_get_product_id( $product );
		$temp_data = $this->elex_rp_get_product_type( $product );

		$adjustment_value = $this->elex_rp_get_adjustment_for_individual_products( $pid, $price );
		if ( 'no_amount' === $adjustment_value ) {
			$adjustment_value = 0;
		} else {
			 $price += $adjustment_value;
			 $this->elex_rp_add_filter_for_get_price();
			 return $price;
		}
		//common price adjustment 
		add_filter(
			'woocommerce_available_variation',
			function ( $value, $object = null, $variation = null ) {
				if ( '' === $value['price_html'] ) {
					$value['price_html'] = '<span class="price">' . $variation->get_price_html() . '</span>';
				}
				return $value;
			},
			10,
			3
		);
		if ( 'variation' === $temp_data ) {
			$prdct_id = $this->elex_rp_get_product_category_using_id( $this->elex_rp_get_product_parent_id( $product ) );
		} else {
			if ( WC()->version < '2.7.0' ) {
				$temp_post_id = $product->post->ID;
			} else {
				$temp_post_id = $product->get_id();
			}
			$prdct_id = $this->elex_rp_get_product_category_using_id( $temp_post_id );
		}

		$price = $this->elex_rp_get_adjustment_amount( $price, $prdct_id, $temp_data, $adjustment_value );
		$this->elex_rp_add_filter_for_get_price();

		return $price;
	}
	
	public function elex_rp_get_adjustment_for_individual_products( $pid, $price ) {
		$adjustment_value = 0;
		$current_user_id = $this->user_id;
		$product = wc_get_product( $pid );
		$product_price_adjustment_users = $product->get_meta( 'product_price_adjustment_for_users' );
		$product_price_adjustment_roles = $product->get_meta( 'product_price_adjustment' );
		$current_user_product_rule = '';
		if ( is_array( $this->individual_product_adjustment_for_users ) && isset( $this->individual_product_adjustment_for_users['users'] ) && in_array( $current_user_id, $this->individual_product_adjustment_for_users['users'] ) && isset( $product_price_adjustment_users[ $current_user_id ] ) && isset( $product_price_adjustment_users[ $current_user_id ]['role_price'] ) && 'on' === $product_price_adjustment_users[ $current_user_id ]['role_price'] ) {
			$current_user_product_rule = $product_price_adjustment_users[ $current_user_id ];
		} else if ( is_array( $this->individual_product_adjustment_roles ) && in_array( $this->current_user_role, $this->individual_product_adjustment_roles ) && isset( $product_price_adjustment_roles[ $this->current_user_role ] ) && isset( $product_price_adjustment_roles[ $this->current_user_role ]['role_price'] ) && 'on' === $product_price_adjustment_roles[ $this->current_user_role ]['role_price'] ) {
			$current_user_product_rule = $product_price_adjustment_roles[ $this->current_user_role ];
		}
		//individual product page price adjustment (discount/markup from settings page))
		$enforce_button_check_for_product = $product->get_meta( 'product_based_price_adjustment' );
		if ( 'yes' === $enforce_button_check_for_product && $current_user_product_rule ) {
			
			
			if ( ! empty( $current_user_product_rule['adjustment_price'] ) && is_numeric( $current_user_product_rule['adjustment_price'] ) ) {
				if ( isset( $current_user_product_rule['adj_prod_price_dis'] ) && 'markup' === $current_user_product_rule['adj_prod_price_dis'] ) {
					$adjustment_value += (float) $current_user_product_rule['adjustment_price'];
				} else {
					$adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
				}
			}
			if ( ! empty( $current_user_product_rule['adjustment_percent'] ) && is_numeric( $current_user_product_rule['adjustment_percent'] ) ) {
				if ( isset( $current_user_product_rule['adj_prod_percent_dis'] ) && 'markup' === $current_user_product_rule['adj_prod_percent_dis'] ) {
					$adjustment_value += $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				} else {
					$adjustment_value -= $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				}
			}
			//discount/markup ajustment to $price
		   return $adjustment_value;
		}
		return 'no_amount';
	}
	public function elex_rp_get_adjustment_amount( $price, $prdct_id, $temp_data, $adjustment_value ) {
		$common_price_adjustment_table = ! empty( get_option( 'eh_pricing_discount_price_adjustment_options' ) ) ? array_values( get_option( 'eh_pricing_discount_price_adjustment_options', array() ) ) : array();
		$current_user_id = $this->user_id;
		$multiple_role_option = get_option( 'eh_pricing_discount_multiple_role_price' );
		$multiple_roles = $this->multiple_user_roles;
		$rule_satisfied = false;
		$index = 0;
		$length = ! empty( $common_price_adjustment_table ) ? count( $common_price_adjustment_table ) : 0;
		//delete same user role value
		if ( ! empty( $common_price_adjustment_table ) && is_array( $common_price_adjustment_table ) ) {
			foreach ( $common_price_adjustment_table as $key => $value ) {
			
				if ( array_key_exists( $key, $common_price_adjustment_table ) && is_numeric( $length ) && is_numeric( $key ) ) {
					$j = 0;
					for ( $j = $key + 1; $j <= $length; $j++ ) { 
						if ( array_key_exists( $j, $common_price_adjustment_table ) ) {
							if ( isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && ( isset( $value['role_price'] ) && 'on' === $value['role_price'] ) && ( isset( $common_price_adjustment_table[ $j ]['role_price'] ) && 'on' === $common_price_adjustment_table[ $j ]['role_price'] ) && ! isset( $common_price_adjustment_table[ $key ]['users'] ) && ! isset( $common_price_adjustment_table[ $j ]['users'] ) && ! ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
			
								if ( $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] ) {
									unset( $common_price_adjustment_table[ $j ] ); 
								} elseif ( isset( $common_price_adjustment_table[ $j ]['roles'] ) && ! empty( $common_price_adjustment_table[ $j ]['roles'] ) ) {
									foreach ( $common_price_adjustment_table[ $j ]['roles'] as $index => $role_val ) {
										if ( in_array( $role_val, $common_price_adjustment_table[ $key ]['roles'] ) ) {
											unset( $common_price_adjustment_table[ $j ]['roles'][ $index ] );
										}
									}
								}           
							} elseif ( ! isset( $common_price_adjustment_table[ $key ]['users'] ) && ! isset( $common_price_adjustment_table[ $j ]['users'] ) && isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] && ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
								foreach ( $common_price_adjustment_table[ $j ]['category'] as $index => $value ) {
									if ( in_array( $value, $common_price_adjustment_table[ $key ]['category'] ) ) {
										unset( $common_price_adjustment_table[ $j ]['category'][ $index ] );
									}
								}       
							} elseif ( isset( $common_price_adjustment_table[ $key ]['users'] ) && isset( $common_price_adjustment_table[ $j ]['users'] ) && $common_price_adjustment_table[ $key ]['users'] === $common_price_adjustment_table[ $j ]['users'] && isset( $common_price_adjustment_table[ $key ]['roles'] ) && isset( $common_price_adjustment_table[ $j ]['roles'] ) && $common_price_adjustment_table[ $key ]['roles'] === $common_price_adjustment_table[ $j ]['roles'] && ( isset( $common_price_adjustment_table[ $key ]['category'] ) && isset( $common_price_adjustment_table[ $j ]['category'] ) ) ) {
								foreach ( $common_price_adjustment_table[ $j ]['category'] as $index => $value ) {
									if ( in_array( $value, $common_price_adjustment_table[ $key ]['category'] ) ) {
										unset( $common_price_adjustment_table[ $j ]['category'][ $index ] );
									}
								}
							}
						}  
					}
				}
			}

			//If multiple roles present so apply discount according to option given
			$count_multiple_role = count( $multiple_roles );
			if ( $count_multiple_role > 1 ) {
				$current_role_data = array();
				foreach ( $common_price_adjustment_table as $key => $value ) {
					if ( ( isset( $value['role_price'] ) && 'on' === $value['role_price'] ) ) {
						foreach ( $multiple_roles as $multiple_role_key => $multiple_role_val ) {
							if ( isset( $common_price_adjustment_table[ $key ] ) && ( ( isset( $value['users'] ) && in_array( get_current_user_id(), $value['users'] ) ) || ( ! isset( $value['users'] ) && isset( $value['roles'] ) && in_array( $multiple_role_val, $value['roles'] ) ) ) ) {                           
								array_push( $current_role_data, $value );
								break;
							}// Price Adjustment applicable to selected users/roles only.
						}
					} else {
						continue;
					}
				}
			
				// Sort products by price in ascending order
				if ( 'min_role_price' === $multiple_role_option ) {
					usort(
						$current_role_data,
						function ( $a, $b ) {
							$effective_discount_a = $this->calculateEffectiveDiscount( $a );
							$effective_discount_b = $this->calculateEffectiveDiscount( $b );
							// Sort in descending order based on effective discount
							if ( $effective_discount_b < $effective_discount_a ) {
								return -1;
							} elseif ( $effective_discount_b > $effective_discount_a ) {
								return 1;
							} else {
								return 0;
							}
						}
					);
				}

				//  // Descending order
				if ( 'max_role_price' === $multiple_role_option ) {
					usort(
						$current_role_data,
						function ( $a, $b ) {
							$effective_discount_a = $this->calculateEffectiveDiscount( $a );
							$effective_discount_b = $this->calculateEffectiveDiscount( $b );
							// Sort in descending order based on effective discount
							if ( $effective_discount_a > $effective_discount_b ) {
								return 1;
							} elseif ( $effective_discount_a < $effective_discount_b ) {
								return -1;
							} else {
								return 0;
							}
						}
					);
				}
			
				if ( 'consolidate_price' !== $multiple_role_option ) {
					if ( ! empty( $current_role_data ) ) {
						foreach ( $current_role_data as $val ) {
							if ( empty( $val['category'] ) || ( ! empty( $val['category'] ) && array_intersect( $prdct_id, $val['category'] ) ) ) {
								if ( ! empty( $val['adjustment_price'] ) && is_numeric( $val['adjustment_price'] ) ) {
									
									$val['adjustment_price']  = $val['adjustment_price'];
									if ( isset( $val['adj_prod_price_dis'] ) && 'markup' === $val['adj_prod_price_dis'] ) {
										$price += (float) $val['adjustment_price'];
									} else {
										$price -= (float) $val['adjustment_price'];
									}
								}
								
								if ( ! empty( $val['adjustment_percent'] ) && is_numeric( $val['adjustment_percent'] ) ) {
									
										$val['adjustment_percent']  = $val['adjustment_percent'];
									if ( isset( $val['adj_prod_percent_dis'] ) && 'markup' === $val['adj_prod_percent_dis'] ) {
										$price += $price * ( ( (float) $val['adjustment_percent'] ) / 100 );
									} else {
										$price -= $price * ( ( (float) $val['adjustment_percent'] ) / 100 );
									}
								}
								
									return $price;
							}
						}
					}
						return $price;
				} else if ( 'consolidate_price' === $multiple_role_option ) {
					// Store all the prices/percentages discount/markup of a specific user in an array.
					$fixed_dis = 0;
					$percentage_dis = 0;
					foreach ( $current_role_data as $val ) {
						if ( empty( $val['category'] ) || ( ! empty( $val['category'] ) && array_intersect( $prdct_id, $val['category'] ) ) ) {

							if ( ! empty( $val['adjustment_price'] ) && is_numeric( $val['adjustment_price'] ) ) {
								$fixed_dis += $val['adjustment_price'] ;
							}
							
							if ( ! empty( $val['adjustment_percent'] ) && is_numeric( $val['adjustment_percent'] ) ) {
								$percentage_dis += $val['adjustment_percent'];
							}
						}
					}
					if ( ! empty( $fixed_dis ) ) {
						$price -= (float) $fixed_dis;
					}
					if ( ! empty( $percentage_dis ) ) {
						$price -= $price * ( ( (float) $percentage_dis ) / 100 );
					}
					return $price;

				}
			} else {
				foreach ( $common_price_adjustment_table as $key => $value ) {
					if ( isset( $common_price_adjustment_table[ $key ] ) && ( ( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) ) || ( ! isset( $value['users'] ) && isset( $value['roles'] ) && in_array( $multiple_roles[0], $value['roles'] ) ) ) ) {                           
						$current_user_product_rule = $common_price_adjustment_table[ $key ];
						if ( isset( $current_user_product_rule['role_price'] ) && 'on' === $current_user_product_rule['role_price'] ) {
							if ( ! empty( $multiple_role_option ) && ! empty( $value['users'] ) && ! isset( $value['roles'] ) ) {
								// Adjustment on user is given priority over user role. So modify and return early.
								// User customization neglects above rules.
								// Here user and user role will have equal priority.
								if ( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) && ! empty( $value['adjustment_price'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
									$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
									$price += $adjustment_value;
									
								} elseif ( isset( $value['users'] ) && in_array( $current_user_id, $value['users'] ) && ! empty( $value['adjustment_percent'] ) && isset( $value['role_price'] ) && 'on' === $value['role_price'] ) {
									$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );
									$price += $adjustment_value;
									
								}                       
							} else {
								if ( ! empty( $current_user_product_rule['adjustment_price'] ) && is_numeric( $current_user_product_rule['adjustment_price'] ) ) {
										$adjustment_value = $this->elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value );
										$price += $adjustment_value;
													
								}

								if ( ! empty( $current_user_product_rule['adjustment_percent'] ) && is_numeric( $current_user_product_rule['adjustment_percent'] ) ) {
										$adjustment_value = $this->elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value );
										$price += $adjustment_value;
										
									
								}
							}           
						}                   
					}               
				}
			}       
		}
		return $price;
	}

	public function calculateEffectiveDiscount( $item ) {
		$adjustment_price = ! empty( $item['adjustment_price'] ) ? (float) $item['adjustment_price'] : 0;
		$adjustment_percent = ! empty( $item['adjustment_percent'] ) ? (float) $item['adjustment_percent'] : 0;
	
		// Apply percent discount
		$percent_discount = $adjustment_percent;
	
		// Assuming a base price of 100 for calculation
		$base_price = 100;
		$price_after_percent_discount = $base_price - ( $base_price * ( $percent_discount / 100 ) );
	
		// Apply price discount
		$effective_discount = $price_after_percent_discount - $adjustment_price;
	
		return $effective_discount;
	}
	
	public function elex_rp_adjust_percent_for_user_roles( $prdct_id, $current_user_product_rule, $price, $temp_data, $adjustment_value, $category = '' ) {
		$adjustment_value = 0;
		if ( isset( $current_user_product_rule['category'] ) ) {    
			$cat_display = $current_user_product_rule['category'];
			if ( 'grouped' !== $temp_data ) {
				$result_chk = array_intersect( $prdct_id, $cat_display );
			}
			if ( empty( $result_chk ) ) {
				$adjustment_value = 0;
			} else {
				if ( isset( $current_user_product_rule['adj_percent_dis'] ) && 'markup' === $current_user_product_rule['adj_percent_dis'] ) {
					$adjustment_value += $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				} else {
					$adjustment_value -= $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				}
			}
		} elseif ( ! isset( $current_user_product_rule['category'] ) && is_array( $category ) && ! empty( $category ) ) {
			$cat_display = $category;
			if ( 'grouped' !== $temp_data ) {
				$result_chk = array_intersect( $prdct_id, $cat_display );
			}
			if ( ! empty( $result_chk ) ) {
				$adjustment_value = 0;
			} else {
				if ( isset( $current_user_product_rule['adj_percent_dis'] ) && 'markup' === $current_user_product_rule['adj_percent_dis'] ) {
					$adjustment_value += $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				} else {
					$adjustment_value -= $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
				}
			}
		} else {
			if ( isset( $current_user_product_rule['adj_percent_dis'] ) && 'markup' === $current_user_product_rule['adj_percent_dis'] ) {
				$adjustment_value += $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
			} else {
				
				$adjustment_value -= $price * ( (float) $current_user_product_rule['adjustment_percent'] ) / 100;
			
			}
		}
		return $adjustment_value;
	}
	
	public function elex_rp_adjust_price_for_user_roles( $prdct_id, $current_user_product_rule, $temp_data, $adjustment_value, $category = '' ) {
		$adjustment_value = 0;
		if ( isset( $current_user_product_rule['category'] ) ) {
			$cat_display = $current_user_product_rule['category'];
			if ( 'grouped' !== $temp_data ) {
				$result_chk = array_intersect( $prdct_id, $cat_display );
			}
			if ( empty( $result_chk ) ) {
				$adjustment_value = 0;
			} else {
				if ( isset( $current_user_product_rule['adj_price_dis'] ) && 'markup' === $current_user_product_rule['adj_price_dis'] ) {
					$adjustment_value += (float) $current_user_product_rule['adjustment_price'];
				} else {
					$adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
				}
			}
		} elseif ( ! isset( $current_user_product_rule['category'] ) && is_array( $category ) && ! empty( $category ) ) {
			$cat_display = $category;
			if ( 'grouped' !== $temp_data ) {
				$result_chk = array_intersect( $prdct_id, $cat_display );
			}
			if ( ! empty( $result_chk ) ) {
				$adjustment_value = 0;
			} else {
				if ( isset( $current_user_product_rule['adj_price_dis'] ) && 'markup' === $current_user_product_rule['adj_price_dis'] ) {
					$adjustment_value += (float) $current_user_product_rule['adjustment_price'];
				} else {
					$adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
				}
			}
		} else {
			if ( isset( $current_user_product_rule['adj_price_dis'] ) && 'markup' === $current_user_product_rule['adj_price_dis'] ) {
				$adjustment_value += (float) $current_user_product_rule['adjustment_price'];
			} else {
				$adjustment_value -= (float) $current_user_product_rule['adjustment_price'];
			}
		}
		return $adjustment_value;
		
	}


	private function elex_rp_fix_price_decimal_format( $dec_seperator, $price_to_update ) {
		if ( ',' === $dec_seperator ) {
			if ( strpos( $price_to_update, ',' ) ) {
				$updated_price = str_replace( ',', '.', $price_to_update );
			} else {
				$updated_price = str_replace( '.', ',', $price_to_update );
			}
		} else {
			$updated_price = $price_to_update;
		}
		
		return $updated_price;
	}

	public function currency_exchange_price( $reg_price ) {
		/**
		* To get current region and change regular price accordingly
		*
		* @since 2.9.0
		*
		*/
		$zones     = (array) get_option( 'wc_price_based_country_regions', array() );
		$converted_price = array();
		$country = '';
		if ( WC()->customer ) {
			$country = WC()->customer->get_shipping_country();
		}
		$exchange_reg_price = 1;
		$zone = '';
		foreach ( $zones as $key => $zone_data ) {

			$enabled = isset( $zone_data['enabled'] ) ? $zone_data['enabled'] : 'yes';
			if ( 'yes' !== $enabled && $skip_disabled ) {
				// Skip disabled.
				continue;
			}

			if ( in_array( $country, $zone_data['countries'], true ) ) {
				$zone = $key;
				break;
			}       
		}
		if ( ! empty( $zone ) ) {
			$exchange_reg_price = $reg_price * $zone_data['exchange_rate'];
		}
			$converted_price['reg_price'] = $exchange_reg_price;
			return $converted_price;

	}

	public function elex_rp_get_price_html( $price, $product ) {
		global $wpdb;
		if ( 'gift-card' !== $this->elex_rp_get_product_type( $product ) && ( '' === $product->get_price() || null === $product->get_price() ) ) {
			return '';
		} 
		if ( $this->elex_rp_get_product_type( $product ) === 'simple' ) {
			$pid = $this->elex_rp_get_product_id( $product ); 
			$reg_price = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value    FROM {$wpdb->postmeta}    WHERE post_id = %d    AND meta_key = '_regular_price'", $pid ) );
			/**
			 * To check the woocommerce price based on country is activated or not.
			 * 
			 * @since 2.0.0
			 */
			if ( in_array( 'woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
				$exchange_rates = $this->currency_exchange_price( $reg_price );
			}
			$reg_price = isset( $exchange_rates['reg_price'] ) && ! empty( $exchange_rates['reg_price'] ) ? $exchange_rates['reg_price'] : $reg_price;
			$current_user_role = $this->current_user_role;
			$product_user_role_price = $product->get_meta( 'product_role_based_price_' . $this->current_user_role );
			if ( is_array( $product_user_role_price ) && isset( $product_user_role_price[0] ) && ! empty( $product_user_role_price[0] ) ) {
				$product_user_price = $product_user_role_price;
			}
			if ( ! empty( $product_user_price ) ) {  
				if ( $current_user_role ) {
					$product_user_price_value = $product_user_price;
					if ( ! empty( $product_user_price_value[0] ) ) { 
						if ( is_numeric( $reg_price ) && $reg_price > $product->get_price() && $this->elex_rp_is_hide_regular_price( $product ) === false ) {
							$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $reg_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();  
						} else {
							$price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
						}
					}
				}
			} elseif ( $product->is_on_sale() && $this->elex_rp_is_hide_regular_price( $product ) === false ) {
				if ( $product->get_regular_price() > $product->get_price() ) {
					$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
				} else {
					$price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
				}
			} else {
				if ( $reg_price > $product->get_price() && $this->elex_rp_is_hide_regular_price( $product ) === false ) {
					$price = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $reg_price ) ), wc_get_price_to_display( $product ) ) . $product->get_price_suffix();  
				} else {
					$price = wc_price( wc_get_price_to_display( $product ) ) . $product->get_price_suffix();
				}
			}
			if ( $this->elex_rp_is_hide_price( $product ) ) {
				if ( $this->elex_rp_is_price_hidden_in_product_meta( $product ) ) {
					$price = $this->elex_rp_get_placeholder_text_product_hide_price( $product );
				} else {
					$price = $this->elex_rp_get_placeholder_text( $product, $price );
				}
			}
		} 
		/**
		 * To modify the price
		 * 
		 * @since 1.0.0
		 */
		return apply_filters( 'eh_pricing_adjustment_modfiy_price', $this->elex_rp_pricing_add_price_suffix( $price, $product ), $this->current_user_role );
	}

	public function elex_rp_is_hide_regular_price( $product ) {
		$hide = false;
		if ( ! is_user_logged_in() ) {
			$hide = get_option( 'eh_pricing_discount_hide_regular_price_unregistered', 'no' ) === 'yes';
		} else {
			$remove_settings_regular_price_roles = get_option( 'eh_pricing_discount_regular_price_user_role', array() );
			if ( is_array( $remove_settings_regular_price_roles ) && in_array( $this->current_user_role, $remove_settings_regular_price_roles ) ) {
				$hide = true;
			}
		}
		return $hide;
	}

	public function elex_rp_is_hide_price( $product ) {
		$hide = false;
		$product_id = $this->elex_rp_get_product_id( $product );
		$temp_data = $this->elex_rp_get_product_type( $product );
		if ( 'variation' === $temp_data ) {
			$product_id = $this->elex_rp_get_product_parent_id( $product );
		}
		if ( is_user_logged_in() ) {
			$remove_settings_price_roles = get_option( 'eh_pricing_discount_price_user_role', array() );
			$remove_product_price_roles = $product->get_meta( 'eh_pricing_adjustment_product_price_user_role' );
			if ( is_array( $remove_settings_price_roles ) && in_array( $this->current_user_role, $remove_settings_price_roles ) ) {
				$hide = true;
			}
			if ( is_array( $remove_product_price_roles ) && in_array( $this->current_user_role, $remove_product_price_roles ) ) {
				$hide = true;
			}
		} else {
			$remove_product_price_roles = $product->get_meta( 'product_adjustment_hide_price_unregistered' );
			if ( 'yes' === get_option( 'eh_pricing_discount_price_unregistered_user' ) || 'yes' === $remove_product_price_roles ) {
				$hide = true;
			}
		}
		return $hide;
	}


	public function elex_rp_is_product_purchasable( $is_purchasable, $product ) {
		if ( $this->elex_rp_is_hide_price( $product ) === true || 'gift-card' !== $this->elex_rp_get_product_type( $product ) && ( '' === $product->get_price() || null === $product->get_price() ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function elex_rp_is_price_hidden_in_product_meta( $product ) {
		$product_id = $this->elex_rp_get_product_id( $product );

		if ( $this->elex_rp_get_product_type( $product ) === 'variation' ) {
			$product_id = $this->elex_rp_get_product_parent_id( $product );
		}
		if ( is_user_logged_in() ) {
			$remove_product_price_roles = $product->get_meta( 'eh_pricing_adjustment_product_price_user_role' );
			if ( is_array( $remove_product_price_roles ) && in_array( $this->current_user_role, $remove_product_price_roles ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			$remove_product_price_roles = $product->get_meta( 'product_adjustment_hide_price_unregistered' );
			if ( 'yes' === $remove_product_price_roles ) {
				return true;
			} else {
				return false;
			}
		}
	}

	public function elex_rp_get_placeholder_text( $product, $price ) {
		$placeholder = '';
		$product_id = $this->elex_rp_get_product_id( $product );
		if ( $this->elex_rp_is_hide_price( $product ) === true ) {
			if ( is_user_logged_in() ) {
				$placeholder = get_option( 'eh_pricing_discount_price_user_role_text' );
			} else {
				$placeholder = get_option( 'eh_pricing_discount_price_unregistered_user_text' );
			}
			return $placeholder;
		} else {
			return $price;
		}
	}
	public function elex_rp_get_placeholder_text_product_hide_price( $product ) {
		$placeholder = '';
		$prod_id = $this->elex_rp_get_product_id( $product );
		if ( is_user_logged_in() ) {
			$placeholder = $product->get_meta( 'product_adjustment_hide_price_placeholder_role' );
		} else {
			$placeholder = $product->get_meta( 'product_adjustment_hide_price_placeholder_unregistered' );
		}
			return $placeholder;
		
	}

	public function elex_rp_get_product_type( $product ) {
		if ( empty( $product ) ) {
			return 'not a valid object';
		}
		if ( WC()->version < '2.7.0' ) {
			$product_type = $product->product_type;
		} else {
			$product_type = $product->get_type();
		}
		return $product_type;
	}

	public function elex_rp_get_product_id( $product ) {
		if ( empty( $product ) ) {
			return 'not a valid object';
		}
		if ( WC()->version < '2.7.0' ) {
			$product_id = $product->post->id;
		} else {
			$product_id = $product->get_id();
		}
		return $product_id;
	}

	public function elex_rp_get_product_parent_id( $product ) {
		if ( empty( $product ) ) {
			return 'not a valid object';
		}
		if ( WC()->version < '2.7.0' ) {
			$product_parent_id = $product->parent->id;
		} else {
			$product_parent_id = $product->get_parent_id();
		}
		return $product_parent_id;
	}

	//function to add price suffix
	public function elex_rp_pricing_add_price_suffix( $price, $product ) {
		$price_suffix;
		if ( 'general' === $this->price_suffix_option ) {
			$price_suffix = ' <small class="woocommerce-price-suffix">' . $this->general_price_suffix . '</small>';
		} elseif ( 'role_specific' === $this->price_suffix_option ) {
			$user_role;
			if ( is_user_logged_in() ) {
				$user_role = $this->price_suffix_user_role;
			} else {
				$user_role = 'unregistered_user';
			}
			if ( is_array( $this->role_price_suffix ) && key_exists( $user_role, $this->role_price_suffix ) && isset( $this->role_price_suffix[ $user_role ]['price_suffix'] ) && '' !== $this->role_price_suffix[ $user_role ]['price_suffix'] ) {
				$price_suffix = ' <small class="woocommerce-price-suffix">' . $this->role_price_suffix[ $user_role ]['price_suffix'] . '</small>';
			}
		}
		if ( ! empty( $price_suffix ) && $this->elex_rp_is_hide_price( $product ) === false ) {

			$find = array(
				'{price_including_tax}',
				'{price_excluding_tax}',
			);
			$replace = array(
				wc_price( ( WC()->version < '2.7.0' ) ? $product->get_price_including_tax() : wc_get_price_including_tax( $product ) ),
				wc_price( ( WC()->version < '2.7.0' ) ? $product->get_price_excluding_tax() : wc_get_price_excluding_tax( $product ) ),
			);
			$price_suffix = str_replace( $find, $replace, $price_suffix );
			$price .= $price_suffix;
		}
		return $price;
	}

}

new Elex_Price_Discount_Admin();

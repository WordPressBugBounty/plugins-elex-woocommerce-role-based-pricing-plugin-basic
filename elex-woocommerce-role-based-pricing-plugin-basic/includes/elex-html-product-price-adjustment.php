<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div>
	<?php
	global $wp_roles;
	global $post;
	$product = isset( $post->ID ) ? wc_get_product( sanitize_text_field( $post->ID ) ) : '';
	if ( ! empty( $product ) ) {
		if ( WC()->version < '2.7.0' ) {
			$product_type = $product->product_type;
		} else {
			$product_type = $product->get_type();
		}
	}
	$decimal_steps = 1;
	$woo_decimal = wc_get_price_decimals();
	for ( $temp = 0;$temp < $woo_decimal;$temp++ ) {
		$decimal_steps = $decimal_steps / 10;
	}
	?>
</h3>
<?php if ( 'simple' === $product_type ) { ?>
<!-- Option to hide add to cart for unregistered user-->
<h4 style="padding-left: 3%;">Unregistered Users:</h4>
<div style="padding-left: 3%;height: 60px;">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Remove Add to Cart', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $checked = ( ( $product->get_meta( 'product_adjustment_hide_addtocart_unregistered' ) ) === 'yes' ) ? true : false; ?>
	<input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_addtocart_unregistered" id="product_adjustment_hide_addtocart_unregistered" <?php checked( $checked, true ); ?> />
	<label style="float: left;margin-left:5px;"><?php esc_html_e( 'Enable', 'elex-catmode-rolebased-price' ); ?></label>
	<span class="description" style="width: 60%;float: right;margin-top: 6px;">
		<?php esc_html_e( 'Check to remove Add to Cart.', 'elex-catmode-rolebased-price' ); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_addtocart_unregistered">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Placeholder text', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $place_text = $product->get_meta( 'product_adjustment_hide_addtocart_placeholder_unregistered' ); ?>
	<textarea name="product_adjustment_hide_addtocart_placeholder_unregistered" id="product_adjustment_hide_addtocart_placeholder_unregistered" style="width: 40%;"><?php echo esc_html( $place_text ); ?></textarea>
	<span style="font-size: 1.4em;"> <?php echo wc_help_tip( __( "Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price' ) ); ?></span>
</div>

<!-- Option to hide price for unregistered user-->
<div style="padding-left: 3%;height: 60px;">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Hide Price', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $checked = ( ( $product->get_meta( 'product_adjustment_hide_price_unregistered' ) ) === 'yes' ) ? true : false; ?>
	<input type="checkbox" style="float: left;margin-left: 0px;" name="product_adjustment_hide_price_unregistered" id="product_adjustment_hide_price_unregistered" <?php checked( $checked, true ); ?> />
	<label style="float: left;margin-left:5px;"><?php esc_html_e( 'Enable', 'elex-catmode-rolebased-price' ); ?></label>
	<span class="description" style="width: 60%;float: right;margin-top: 6px;">
		<?php esc_html_e( 'Check to hide price.', 'elex-catmode-rolebased-price' ); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_price_unregistered">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Placeholder text', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $place_text = $product->get_meta( 'product_adjustment_hide_price_placeholder_unregistered' ); ?>
	<input type="text" value="<?php echo esc_html( $place_text ); ?>" name="product_adjustment_hide_price_placeholder_unregistered" id="product_adjustment_hide_price_placeholder_unregistered" style="width: 40%;">
	<span style="font-size: 1.4em;"> <?php echo wc_help_tip( __( "Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price' ) ); ?></span>
</div>

<h4 style="padding-left: 3%;">Registered Users:</h4>
<!-- Option to hide add to cart for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
	<label for="eh_pricing_adjustment_product_addtocart_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( ' Remove Add to Cart', 'elex-catmode-rolebased-price' ); ?></label>
	<select class="wc-enhanced-select" name="eh_pricing_adjustment_product_addtocart_user_role[]" id="eh_pricing_adjustment_product_addtocart_user_role" multiple="multiple" style="width: 50%;float: left;">
		<?php
		$hide_addtocart_role = $product->get_meta( 'eh_pricing_adjustment_product_addtocart_user_role' );
		$user_roles = $wp_roles->role_names;
		foreach ( $user_roles as $user_roles_id => $name ) {
			if ( is_array( $hide_addtocart_role ) && in_array( $user_roles_id, $hide_addtocart_role ) ) {
				echo '<option value="' . esc_html( $user_roles_id ) . '" selected="selected">' . esc_html( $name ) . '</option>';
			} else {
				echo '<option value="' . esc_html( $user_roles_id ) . '">' . esc_html( $name ) . '</option>';
			}
		}
		?>
	</select>
	<span style="font-size: 1.4em;"><?php echo wc_help_tip( __( ' Select the user role(s) for which you want to remove Add to Cart.', 'elex-catmode-rolebased-price' ) ); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_addtocart_role">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Placeholder text', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $place_text = $product->get_meta( 'product_adjustment_hide_addtocart_placeholder_role' ); ?>
	<textarea name="product_adjustment_hide_addtocart_placeholder_role" id="product_adjustment_hide_addtocart_placeholder_role" style="width: 40%;"><?php echo esc_html( $place_text ); ?></textarea>
	<span style="font-size: 1.4em;"> <?php echo wc_help_tip( __( "Enter a text or html content to display when Add to Cart button is removed. Leave it empty if you don't want to show any content.", 'elex-catmode-rolebased-price' ) ); ?></span>
</div>

<!-- Option to hide price for user role-->
<div style="padding-left: 3%;height: 60px;overflow : auto;">
	<label for="eh_pricing_adjustment_product_price_user_role" style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( ' Hide Price', 'elex-catmode-rolebased-price' ); ?></label>
	<select class="wc-enhanced-select" name="eh_pricing_adjustment_product_price_user_role[]" id="eh_pricing_adjustment_product_price_user_role" multiple="multiple" style="width: 50%;float: left;">
		<?php
		$hide_price_role = $product->get_meta( 'eh_pricing_adjustment_product_price_user_role' );
		$user_roles = $wp_roles->role_names;
		foreach ( $user_roles as $user_roles_id => $name ) {
			if ( is_array( $hide_price_role ) && in_array( $user_roles_id, $hide_price_role ) ) {
				echo '<option value="' . esc_html( $user_roles_id ) . '" selected="selected">' . esc_html( $name ) . '</option>';
			} else {
				echo '<option value="' . esc_html( $user_roles_id ) . '">' . esc_html( $name ) . '</option>';
			}
		}
		?>
	</select>
	<span style="font-size: 1.4em;"><?php echo wc_help_tip( __( ' Select the user role(s) for which you want to hide price.', 'elex-catmode-rolebased-price' ) ); ?></span>
</div>
<div style="padding-left: 3%;height: 60px;" id="place_holder_prod_hide_price_role">
	<label style="margin-left: 0px;width: 40%;float: left;"><?php esc_html_e( 'Placeholder text', 'elex-catmode-rolebased-price' ); ?></label>
	<?php $place_text = $product->get_meta( 'product_adjustment_hide_price_placeholder_role', true ); ?>
	<textarea name="product_adjustment_hide_price_placeholder_role" id="product_adjustment_hide_price_placeholder_role" style="width: 40%;"><?php echo esc_html( $place_text ); ?></textarea>
	<span style="font-size: 1.4em;"> <?php echo wc_help_tip( __( "Enter the text you want to display when price is removed. Leave it empty if you don't want to show any placeholder text.", 'elex-catmode-rolebased-price' ) ); ?></span>
</div>

<br>
<!-- Option to enforce porduct based adjustment-->
<?php
$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
	if ( is_array( $user_roles ) && ! empty( $user_roles ) ) {
		if ( 'simple' === $product_type || 'variable' === $product_type ) {
			?>
<h4 style="padding-left: 3%;">Price Adjustment:</h4>
		<h4 style="padding-left: 3%;color: green">To set a price for the product, go to <a href="" onclick="if (jQuery('#product-type').val() === 'variable') {
							jQuery('.variations_tab').find('a').click();
						} else {
							jQuery('.general_tab').find('a').click();
						}
						return false;">
						<?php
						if ( 'variable' === $product_type ) {
							?>
					variations settings
					<?php 
						} elseif ( 'simple' === $product_type ) {
							?>
					general settings
						<?php
						}
						?>
				 </a></h4>


		<div style="padding-left: 3%;height: 50px;">
			<label style="width: 40%; height: 40px; float: left;margin-left: 0px;"><?php esc_html_e( ' Individual Product Price Adjustment', 'elex-catmode-rolebased-price' ); ?></label>
				<?php $checked = ( ( $product->get_meta( 'product_based_price_adjustment', true ) ) === 'yes' ) ? true : false; ?>
			<input type="checkbox" name="product_based_price_adjustment" id="product_based_price_adjustment" <?php checked( $checked, true ); ?> />
			<label style="width: 57%;float: right;margin-top: 3px;"><?php esc_html_e( ' Enable', 'elex-catmode-rolebased-price' ); ?></label>

		</div>

		<!-- Option to determine user role based adjustment-->
		<div id="individual_product_price_adjustment_field" style="padding-left: 3%;padding-bottom: 3%;width: 94%;overflow : auto;">
			<p class="description" style="margin-left: 0px;"> <?php esc_html_e( " Drag and drop user roles to set priority. If a single user has multiple user roles assigned, the user role with the highest priority will be chosen. Choose 'D' for DISCOUNT and 'M' for MARKUP.", 'elex-catmode-rolebased-price' ); ?></p>
			<table class="product_price_adjustment widefat" id="eh_pricing_discount_product_price_adjustment_data">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php esc_html_e( 'User Role', 'elex-catmode-rolebased-price' ); ?></th>
						<th style="text-align:center;"><?php echo esc_html( __( 'Price Adjustment (' . get_woocommerce_currency_symbol() . ')', 'elex-catmode-rolebased-price' ) ); ?></th>
						<th style="text-align:center;"><?php esc_html_e( 'Price Adjustment (%)', 'elex-catmode-rolebased-price' ); ?></th>
						<th style="text-align:center;"><?php esc_html_e( 'Enable', 'elex-catmode-rolebased-price' ); ?></th>
					</tr>
				</thead>
				<tbody>

					<?php
					$this->price_table = array();
					$i = 0;
					$product_adjustment_price = $product->get_meta( 'product_price_adjustment' );
					$wordpress_roles = $wp_roles->role_names;
					$wordpress_roles['unregistered_user'] = 'Unregistered User';
					if ( empty( $product_adjustment_price ) ) {
						foreach ( $user_roles as $user_roles_id => $value ) {
							$this->price_table[ $i ]['id'] = $value;
							$this->price_table[ $i ]['name'] = $wordpress_roles[ $value ];
							$this->price_table[ $i ]['adjustment_price'] = '';
							$this->price_table[ $i ]['adjustment_percent'] = '';
							$this->price_table[ $i ]['role_price'] = '';
							$this->price_table[ $i ]['adj_prod_percent_dis'] = '';
							$this->price_table[ $i ]['adj_prod_price_dis'] = '';
							$i++;
						}
					} else {
						if ( is_array( $product_adjustment_price ) || is_object( $product_adjustment_price ) ) {
							foreach ( $product_adjustment_price as $adjustment_price_id => $value ) {
								if ( in_array( $adjustment_price_id, $user_roles ) ) {
									$current_adjustment_price = $product_adjustment_price;
									$this->price_table[ $i ]['id'] = $adjustment_price_id;
									$this->price_table[ $i ]['name'] = $wordpress_roles[ $adjustment_price_id ];
									$this->price_table[ $i ]['adjustment_price'] = $current_adjustment_price[ $adjustment_price_id ]['adjustment_price'];
									$this->price_table[ $i ]['adjustment_percent'] = $current_adjustment_price[ $adjustment_price_id ]['adjustment_percent'];
									 $this->price_table[ $i ]['adj_prod_percent_dis'] = isset( $current_adjustment_price[ $adjustment_price_id ]['adj_prod_percent_dis'] ) ? $current_adjustment_price[ $adjustment_price_id ]['adj_prod_percent_dis'] : 'discount';
									 $this->price_table[ $i ]['adj_prod_price_dis'] = isset( $current_adjustment_price[ $adjustment_price_id ]['adj_prod_price_dis'] ) ? $current_adjustment_price[ $adjustment_price_id ]['adj_prod_price_dis'] : 'discount';
									if ( key_exists( 'role_price', $current_adjustment_price[ $adjustment_price_id ] ) ) {
										$this->price_table[ $i ]['role_price'] = $current_adjustment_price[ $adjustment_price_id ]['role_price'];
									} else {
										$this->price_table[ $i ]['role_price'] = '';
									}
									$i++;
									unset( $user_roles[ array_search( $adjustment_price_id, $user_roles ) ] );
								}
							}
						}

						if ( ! empty( $user_roles ) ) {
							foreach ( $user_roles as $user_roles_id => $value ) {
								$this->price_table[ $i ]['id'] = $value;
								$this->price_table[ $i ]['name'] = $wordpress_roles[ $value ];
								$this->price_table[ $i ]['adjustment_price'] = '';
								$this->price_table[ $i ]['adjustment_percent'] = '';
								$this->price_table[ $i ]['role_price'] = '';
								$i++;
							}
						}
					}

					foreach ( $this->price_table as $key => $value ) {
						?>
						<tr>
							<td class="sort">
								<input type="hidden" class="order" name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>]" value="<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>" />
							</td>
							<td>
								<label name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][name]" style="margin-left:0px;"><?php echo isset( $this->price_table[ $key ]['name'] ) ? esc_html( $this->price_table[ $key ]['name'] ) : ''; ?></label>
							</td>
							<td style="text-align: center;">
							<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><input type="number" style="width: 50%;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset( $this->price_table[ $key ]['adjustment_price'] ) ? esc_html( $this->price_table[ $key ]['adjustment_price'] ) : ''; ?>" />
							 <?php
								$select_prod_price_dis = 'selected';
								$select_prod_price_mar = '';
								if ( isset( $this->price_table[ $key ]['adj_prod_price_dis'] ) && 'markup' === $this->price_table[ $key ]['adj_prod_price_dis'] ) {
									$select_prod_price_mar = 'selected';
									$select_prod_price_dis = '';
								}
								?>
								<select name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adj_prod_price_dis]"><option value="discount" <?php echo esc_html( $select_prod_price_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_prod_price_mar ); ?>>M</option></select>
							</td>
							<td style="text-align: center;">
								<input type="number" style="width: 50%;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->price_table[ $key ]['adjustment_percent'] ) ? esc_html( $this->price_table[ $key ]['adjustment_percent'] ) : ''; ?>" />%
								<?php
								$select_prod_percent_dis = 'selected';
								$select_prod_percent_mar = '';
								if ( isset( $this->price_table[ $key ]['adj_prod_percent_dis'] ) && 'markup' === $this->price_table[ $key ]['adj_prod_percent_dis'] ) {
									$select_prod_percent_mar = 'selected';
									$select_prod_percent_dis = '';
								}
								?>
								<select name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adj_prod_percent_dis]"><option value="discount" <?php echo esc_html( $select_prod_percent_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_prod_percent_mar ); ?>>M</option></select>
							</td>
							<td style="text-align:center;">
								<label style="margin-left:0px; width: 5%;">
								<?php $checked = ( ! empty( $this->price_table[ $key ]['role_price'] ) ) ? true : false; ?>
									<input type="checkbox" name="product_price_adjustment[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][role_price]" <?php checked( $checked, true ); ?> />
								</label>
							</td>
						</tr>
							<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<div id="individual_product_price_adjustment_field_for_users" style="padding-left: 3%;padding-bottom: 3%;width: 94%;overflow : auto;">
			
			<table class="product_price_adjustment_for_users widefat" id="eh_pricing_discount_product_price_adjustment_data_for_users">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php esc_html_e( 'Users', 'elex-catmode-rolebased-price' ); ?></th>
						<th style="text-align:center;"><?php echo esc_html( __( 'Price Adjustment (' . get_woocommerce_currency_symbol() . ')', 'elex-catmode-rolebased-price' ) ); ?></th>
						<th style="text-align:center;"><?php esc_html_e( 'Price Adjustment (%)', 'elex-catmode-rolebased-price' ); ?></th>
						<th style="text-align:center;"><?php esc_html_e( 'Enable', 'elex-catmode-rolebased-price' ); ?></th>
					</tr>
				</thead>
				<tbody>

					<?php
					$this->price_table = array();
					$user_roles = get_option( 'eh_pricing_discount_product_on_users' );
					$i = 0;
					$product_adjustment_price = $product->get_meta( 'product_price_adjustment_for_users' );
					if ( empty( $product_adjustment_price ) ) {
						if ( ! empty( $user_roles ) ) {
							foreach ( $user_roles['users'] as $user_roles_id => $value ) {
								$this->price_table[ $i ]['id'] = $value;
								$user = get_user_by( 'id', $value );
								$user_name = '';
								if ( is_object( $user ) ) {
									$user_name = $user->display_name . '(#' . $user->ID . ') - ' . $user->user_email;
								}   
								$this->price_table[ $i ]['name'] = $user_name;
								$this->price_table[ $i ]['adjustment_price'] = '';
								$this->price_table[ $i ]['adjustment_percent'] = '';
								$this->price_table[ $i ]['role_price'] = '';
								$this->price_table[ $i ]['adj_prod_percent_dis'] = '';
								$this->price_table[ $i ]['adj_prod_price_dis'] = '';
								$i++;
							}
						}
					} else {
						if ( is_array( $product_adjustment_price ) || is_object( $product_adjustment_price ) ) {
							foreach ( $product_adjustment_price as $adjustment_price_id => $value ) {
								if ( isset( $user_roles['users'] ) && ! empty( $user_roles['users'] ) && in_array( $adjustment_price_id, $user_roles['users'] ) ) {
									$current_adjustment_price = $product_adjustment_price ;
									$this->price_table[ $i ]['id'] = $adjustment_price_id;
									$user = get_user_by( 'id', $adjustment_price_id );
									$user_name = '';
									if ( is_object( $user ) ) {
										$user_name = $user->display_name . '(#' . $user->ID . ') - ' . $user->user_email;
									} 
									$this->price_table[ $i ]['name'] = $user_name;
									$this->price_table[ $i ]['adjustment_price'] = $current_adjustment_price[ $adjustment_price_id ]['adjustment_price'];
									$this->price_table[ $i ]['adjustment_percent'] = $current_adjustment_price[ $adjustment_price_id ]['adjustment_percent'];
									 $this->price_table[ $i ]['adj_prod_percent_dis'] = isset( $current_adjustment_price[ $adjustment_price_id ]['adj_prod_percent_dis'] ) ? $current_adjustment_price[ $adjustment_price_id ]['adj_prod_percent_dis'] : 'discount';
									 $this->price_table[ $i ]['adj_prod_price_dis'] = isset( $current_adjustment_price[ $adjustment_price_id ]['adj_prod_price_dis'] ) ? $current_adjustment_price[ $adjustment_price_id ]['adj_prod_price_dis'] : 'discount';
									if ( key_exists( 'role_price', $current_adjustment_price[ $adjustment_price_id ] ) ) {
										$this->price_table[ $i ]['role_price'] = $current_adjustment_price[ $adjustment_price_id ]['role_price'];
									} else {
										$this->price_table[ $i ]['role_price'] = '';
									}
									$i++;
									unset( $user_roles['users'][ array_search( $adjustment_price_id, $user_roles['users'] ) ] );
								}
							}
						}

						if ( ! empty( $user_roles['users'] ) ) {
							foreach ( $user_roles['users'] as $user_roles_id => $value ) {
								$this->price_table[ $i ]['id'] = $value;
								$user = get_user_by( 'id', $value );
								$user_name = '';
								if ( is_object( $user ) ) {
									$user_name = $user->display_name . '(#' . $user->ID . ') - ' . $user->user_email;
								} 
								$this->price_table[ $i ]['name'] = $user_name;
								$this->price_table[ $i ]['adjustment_price'] = '';
								$this->price_table[ $i ]['adjustment_percent'] = '';
								$this->price_table[ $i ]['role_price'] = '';
								$i++;
							}
						}
					}

					foreach ( $this->price_table as $key => $value ) {
						?>
						<tr>
							<td class="sort">
								<input type="hidden" class="order" name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>]" value="<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>" />
							</td>
							<td>
								<label name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][name]" style="margin-left:0px;"><?php echo isset( $this->price_table[ $key ]['name'] ) ? esc_html( $this->price_table[ $key ]['name'] ) : ''; ?></label>
							</td>
							<td style="text-align: center;">
							<?php echo esc_html( get_woocommerce_currency_symbol() ); ?><input type="number" style="width: 50%;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset( $this->price_table[ $key ]['adjustment_price'] ) ? esc_html( $this->price_table[ $key ]['adjustment_price'] ) : ''; ?>" />
							 <?php
								$select_prod_price_dis = 'selected';
								$select_prod_price_mar = '';
								if ( isset( $this->price_table[ $key ]['adj_prod_price_dis'] ) && 'markup' === $this->price_table[ $key ]['adj_prod_price_dis'] ) {
									$select_prod_price_mar = 'selected';
									$select_prod_price_dis = '';
								}
								?>
								<select name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adj_prod_price_dis]"><option value="discount" <?php echo esc_html( $select_prod_price_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_prod_price_mar ); ?>>M</option></select>
							</td>
							<td style="text-align: center;">
								<input type="number" style="width: 50%;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $this->price_table[ $key ]['adjustment_percent'] ) ? esc_html( $this->price_table[ $key ]['adjustment_percent'] ) : ''; ?>" />%
								<?php
								$select_prod_percent_dis = 'selected';
								$select_prod_percent_mar = '';
								if ( isset( $this->price_table[ $key ]['adj_prod_percent_dis'] ) && 'markup' === $this->price_table[ $key ]['adj_prod_percent_dis'] ) {
									$select_prod_percent_mar = 'selected';
									$select_prod_percent_dis = '';
								}
								?>
								<select name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][adj_prod_percent_dis]"><option value="discount" <?php echo esc_html( $select_prod_percent_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_prod_percent_mar ); ?>>M</option></select>
							</td>
							<td style="text-align:center;">
								<label style="margin-left:0px; width: 5%;">
								<?php $checked = ( ! empty( $this->price_table[ $key ]['role_price'] ) ) ? true : false; ?>
									<input type="checkbox" name="product_price_adjustment_for_users[<?php echo esc_html( $this->price_table[ $key ]['id'] ); ?>][role_price]" <?php checked( $checked, true ); ?> />
								</label>
							</td>
						</tr>
							<?php
					}
					?>
				</tbody>
			</table>
		</div>
		<?php 
		}
	}
}
?>
</div>
<script type="text/javascript">

	jQuery(window).on('load', function () {
		// Ordering
		jQuery('.product_price_adjustment tbody').sortable({
			items: 'tr',
			cursor: 'move',
			axis: 'y',
			handle: '.sort',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'wc-metabox-sortable-placeholder',
			start: function (event, ui) {
				ui.item.css('baclbsround-color', '#f6f6f6');
			},
			stop: function (event, ui) {
				ui.item.removeAttr('style');
				elex_rp_price_adjustment_row_indexes();
			}
		});

		elex_rp_hide_product_placeholder_text('#product_adjustment_hide_addtocart_unregistered','#place_holder_prod_hide_addtocart_unregistered');
		elex_rp_hide_product_placeholder_text('#product_adjustment_hide_price_unregistered','#place_holder_prod_hide_price_unregistered');
		elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_addtocart_user_role','#place_holder_prod_hide_addtocart_role');
		elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_price_user_role','#place_holder_prod_hide_price_role');
		elex_rp_customize_addtocart_product_unrigistered_customize_addtocart();
		elex_rp_customize_addtocart_product_role_customize_addtocart();
		elex_rp_hide_individual_product_price_adjustment_field();
		jQuery("#product_adjustment_hide_addtocart_unregistered").click(function () {
			elex_rp_hide_product_placeholder_text('#product_adjustment_hide_addtocart_unregistered','#place_holder_prod_hide_addtocart_unregistered');
		});
		jQuery("#product_adjustment_hide_price_unregistered").click(function () {
			elex_rp_hide_product_placeholder_text('#product_adjustment_hide_price_unregistered','#place_holder_prod_hide_price_unregistered');
		});
		jQuery('#eh_pricing_adjustment_product_addtocart_user_role').change(function () {
			elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_addtocart_user_role','#place_holder_prod_hide_addtocart_role');
		});
		jQuery('#eh_pricing_adjustment_product_price_user_role').change(function () {
			elex_rp_hide_product_user_placeholder_text('#eh_pricing_adjustment_product_price_user_role','#place_holder_prod_hide_price_role');
		});
		jQuery('#product_adjustment_customize_addtocart_unregistered').change(function () {
			elex_rp_customize_addtocart_product_unrigistered_customize_addtocart();
		});
		jQuery('#eh_pricing_adjustment_product_customize_addtocart_user_role').change(function () {
			elex_rp_customize_addtocart_product_role_customize_addtocart();
		});
		jQuery('#product_based_price_adjustment').change(function () {
			elex_rp_hide_individual_product_price_adjustment_field();
		});
		
		function elex_rp_hide_product_placeholder_text(check, hide_field) {
			if (jQuery(check).is(":checked")) {
				jQuery(hide_field).show();
			} else {
				jQuery(hide_field).hide();
			}
		}
		function elex_rp_hide_product_user_placeholder_text(check, hide_field) {
			options = jQuery(check).val();
			if (options != null) {
				jQuery(hide_field).show();
			} else {
				jQuery(hide_field).hide();
			}
		}
		function elex_rp_customize_addtocart_product_unrigistered_customize_addtocart() {
			if (jQuery('#product_adjustment_customize_addtocart_unregistered').is(":checked")) {
				jQuery('#btn_text_prod_replace_addtocart_unregistered').show();
				jQuery('#btn_text_shop_replace_addtocart_unregistered').show();
				jQuery('#btn_url_replace_addtocart_unregistered').show();
			} else {
				jQuery('#btn_text_prod_replace_addtocart_unregistered').hide();
				jQuery('#btn_text_shop_replace_addtocart_unregistered').hide();
				jQuery('#btn_url_replace_addtocart_unregistered').hide();
			}
		}
		function elex_rp_customize_addtocart_product_role_customize_addtocart() {
			options = jQuery('#eh_pricing_adjustment_product_customize_addtocart_user_role').val();
			if (options != null) {
				jQuery('#btn_text_prod_replace_addtocart_role').show();
				jQuery('#btn_text_shop_replace_addtocart_role').show();
				jQuery('#btn_url_replace_addtocart_role').show();
			} else {
				jQuery('#btn_text_prod_replace_addtocart_role').hide();
				jQuery('#btn_text_shop_replace_addtocart_role').hide();
				jQuery('#btn_url_replace_addtocart_role').hide();
			}
		}
		function elex_rp_hide_individual_product_price_adjustment_field() {
			if (jQuery('#product_based_price_adjustment').is(":checked")) {
				jQuery('#individual_product_price_adjustment_field').show();
				jQuery('#individual_product_price_adjustment_field_for_users').show();
			}
			else {
				jQuery('#individual_product_price_adjustment_field').hide();
				jQuery('#individual_product_price_adjustment_field_for_users').hide();
			}
		}

	});

</script>

<style type="text/css">
	.product_price_adjustment th.sort {
		width: 16px;
		padding: 0 16px;
	}
	.product_price_adjustment td.sort {
		cursor: move;
		width: 16px;
		padding: 0 16px;
		cursor: move;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;					}
</style>

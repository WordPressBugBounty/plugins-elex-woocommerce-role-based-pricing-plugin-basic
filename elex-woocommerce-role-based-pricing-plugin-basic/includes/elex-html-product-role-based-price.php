<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<h3 style="text-align: center;"></h3>';
echo '<h3 style="text-align: center;">';
$user_roles = get_option( 'eh_pricing_discount_product_price_user_role' );
if ( is_array( $user_roles ) && ! empty( $user_roles ) ) {
	esc_html_e( 'Role Based Price', 'elex-catmode-rolebased-price' );
	global $wp_roles;
	$wordpress_roles = $wp_roles->role_names;
	$wordpress_roles['unregistered_user'] = 'Unregistered User';
	echo '</h3>';
	?>
	<br/>
	<table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
		<thead>
		<th class="sort">&nbsp;</th>
		<th><?php esc_html_e( 'User Role', 'elex-catmode-rolebased-price' ); ?></th>
		<th><?php echo esc_html( __( 'Price (' . get_woocommerce_currency_symbol() . ')', 'elex-catmode-rolebased-price' ) ); ?></th>
	</thead>
	<tbody>

		<?php
	   $this->price_table = array();
		$i = 0;
		global $post;
		$product = wc_get_product( $post->ID );
		$product_adjustment_price;
		$product_adjustment_prices = $product->get_meta( 'product_role_based_price' );
		foreach ( $user_roles as $user_roles_id => $value ) {
			$product_adjustment_price = $product->get_meta( 'product_role_based_price_' . $value );
			$this->price_table[ $i ]['id'] = $value;
			$this->price_table[ $i ]['name'] = $wordpress_roles[ $value ];
			if ( ! empty( $product_adjustment_price ) ) {
				$this->price_table[ $i ]['role_price'] = $product_adjustment_price;
			}
			$i++;
		}
		foreach ( $this->price_table as $key => $value ) {
			?>
			<tr>
				<td class="sort">
					<input type="hidden" class="order" name="product_role_based_price[<?php echo esc_html_e( $this->price_table[ $key ]['id'] ); ?>]" value="<?php echo esc_html_e( $this->price_table[ $key ]['id'] ); ?>" />
				</td>
				<td>
					<label name="product_role_based_price[<?php echo esc_html_e( $this->price_table[ $key ]['id'] ); ?>][name]" style="margin-left:0px;"><?php echo isset( $this->price_table[ $key ]['name'] ) ? esc_html_e( $this->price_table[ $key ]['name'] ) : ''; ?></label>
				</td>
				<td>
					<?php echo esc_html_e( get_woocommerce_currency_symbol() ); ?><input type="text" name="product_role_based_price[<?php echo  esc_html_e( $this->price_table[ $key ]['id'] ); ?>][role_price]" id="product_role_based_price_<?php echo esc_html_e( $this->price_table[ $key ]['id'] ); ?>" placeholder="N/A" value="<?php echo isset( $this->price_table[ $key ]['role_price'] ) ? esc_html_e( $this->price_table[ $key ]['role_price'] ) : ''; ?>" size="4" />
				</td>
			</tr>
			<?php
		}
		?>
	</tbody>
	</table>
<?php 
} else {
	esc_html_e( 'Role Based Price ', 'elex-catmode-rolebased-price' ); 
	?>
<table class="product_role_based_price widefat" id="eh_pricing_discount_product_price_adjustment_data">
<th><?php esc_html_e( 'For setting up user roles eligible for individual price adjustment, go to Role Based Pricing settings -> Add roles for the field "Individual Price Adjustment".', 'elex-catmode-rolebased-price' ); ?></th>
</table>
<?php
}
?>
<script>
	jQuery(document).ready(function () {
		jQuery('#product-type').change(function () {
			elex_rp_pricing_discount_product_role_based_price();
		});
		if (jQuery('#product-type').val() === 'variable') {
			elex_rp_pricing_discount_product_role_based_price();
		}
	});

	function elex_rp_pricing_discount_product_role_based_price() {
		product_type = jQuery('#product-type').val();
		switch (product_type) {
			case 'simple':
				jQuery('#general_role_based_price').show();
				break;

			case 'variable':
				jQuery('#general_role_based_price').hide();
				break;


		}
	}
</script>

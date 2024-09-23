<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr valign="top" >
	<td class="forminp" colspan="2" style="padding-left:0px">
			<?php
			global $wp_roles;           
			?>
		<table class="price_adjustment widefat" id="eh_pricing_discount_price_adjustment_options">
			<thead>
			<th class="sort">&nbsp;</th>
			<th><?php esc_html_e( 'User Role', 'elex-catmode-rolebased-price' ); ?></th>
			<th style="text-align:center;"><?php esc_html_e( 'Users', 'elex-catmode-rolebased-price' ); ?></th>
			<th style="text-align:center;"><?php esc_html_e( 'Categories', 'elex-catmode-rolebased-price' ); ?></th>
			<th style="text-align:center;"><?php echo esc_html_e( __( 'Price Adjustment (' . get_woocommerce_currency_symbol() . ')', 'elex-catmode-rolebased-price' ) ); ?></th>
			<th style="text-align:center;"><?php esc_html_e( 'Price Adjustment (%)', 'elex-catmode-rolebased-price' ); ?></th>
			<th style="text-align:center;"><?php esc_html_e( 'Enable', 'elex-catmode-rolebased-price' ); ?></th>
			<th style="text-align:center;"><?php esc_html_e( 'Remove', 'elex-catmode-rolebased-price' ); ?></th>
		</thead>
		<tbody id='elex_rp_table_body'>

			<?php
			$this->price_table = array();
			$i = 0;
			$decimal_steps = 1;
			$woo_decimal = wc_get_price_decimals();
			for ( $temp = 0;$temp < $woo_decimal;$temp++ ) {
				$decimal_steps = $decimal_steps / 10;
			}
			$decimal_steps_formatted = number_format( $decimal_steps, 2, '.', '' );
			$user_adjustment_price = get_option( 'eh_pricing_discount_price_adjustment_options' );
			$wordpress_roles = $wp_roles->role_names;
			$wordpress_roles['unregistered_user'] = 'Unregistered User';
			$allowed_html = array( 'option' => array( 'value' => array() ) );
			$user_role_options = '';
			foreach ( $wordpress_roles as $k => $v ) {
				$user_role_options .= '<option value="' . $k . '" >' . $v . '</option>';
			}
			//Previously saved data
			$temp_user_adjustment = array();
			if ( $user_adjustment_price && is_array( $user_adjustment_price ) ) {
				if ( ! empty( $user_adjustment_price ) && array_keys( $user_adjustment_price, $wordpress_roles ) ) {
					foreach ( $user_adjustment_price as $key => $value ) {
						if ( isset( $value['category'] ) || $value['adjustment_price'] || $value['adjustment_percent'] ) {
							$value['roles'] = array( $key );
							$temp_user_adjustment[] = $value;
							
						}
					}
				}
			}
			if ( ! empty( $temp_user_adjustment ) ) {
				$user_adjustment_price = $temp_user_adjustment;
			}
			
			$product_category = get_terms(
				'product_cat',
				array(
					'fields' => 'id=>name',
					'hide_empty' => false,
					'orderby' => 'title',
					'order' => 'ASC',
				) 
			);
			$category_options = '';
			foreach ( $product_category as $k => $v ) {
				$category_options .= '<option value="' . $k . '" >' . $v . '</option>';
			}
			if ( ! empty( $user_adjustment_price ) ) {
				$index = 0;
				foreach ( $user_adjustment_price as $key => $value ) {
					?>
				<tr>
					<td class="sort">
						<input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>]" value="<?php echo esc_html( $key ); ?>" />
					</td>
					<td style="width: 15%;">
						<select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][roles][]"  multiple="multiple" style="width: 25%;float: left;">
							<?php
							foreach ( $wordpress_roles as $role_id => $role_name ) {
								if ( isset( $value['roles'] ) && is_array( $value['roles'] ) && in_array( $role_id, $value['roles'] ) ) {
									echo '<option value="' . esc_html( $role_id ) . '" selected >' . esc_html( $role_name ) . '</option>';
								} else {
									echo '<option value="' . esc_html( $role_id ) . '" >' . esc_html( $role_name ) . '</option>';
								}
							}
							?>

						</select>

					</td>
					<td style="text-align:center;">
						<select  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][users][]" multiple="multiple" style="width: 25%;float: left;">

							<?php
								$user_ids = isset( $value['users'] ) ? $value['users'] : array();  // selected user ids
							foreach ( $user_ids as $user_id ) {
								$user = get_user_by( 'id', $user_id );
								if ( is_object( $user ) ) {
									echo '<option value="' . esc_attr( $user_id ) . '"' . selected( true, true, false ) . '>' . esc_html( $user->display_name ) . '(#' . esc_html( $user->ID ) . ') - ' . esc_html( $user->user_email ) . '</option>';
								}
							}
							?>
							
						</select>
					</td>
					<td style="text-align:center;">
						<select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][category][]"  multiple="multiple" style="width: 25%;float: left;">
							<?php
							foreach ( $product_category as $product_category_id => $product_category_one ) {
								if ( isset( $value['category'] ) && is_array( $value['category'] ) && in_array( $product_category_id, $value['category'] ) ) {
									echo '<option value="' . esc_html( $product_category_id ) . '" selected >' . esc_html( $product_category_one ) . '</option>';
								} else {
									echo '<option value="' . esc_html( $product_category_id ) . '" >' . esc_html( $product_category_one ) . '</option>';
								}
							}
							?>

						</select>
					</td>
					<td style="text-align:center;">
							<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?><input type="number" style="width:50% !important;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][adjustment_price]" placeholder="N/A" value="<?php echo isset( $value['adjustment_price'] ) ? esc_html( $value['adjustment_price'] ) : ''; ?>" />
							<?php
							$select_price_dis = 'selected';
							$select_price_mar = '';
							if ( isset( $value['adj_price_dis'] ) && 'markup' === $value['adj_price_dis'] ) {
								$select_price_mar = 'selected';
								$select_price_dis = '';
							}
							?>
						<select name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][adj_price_dis]"><option value="discount" <?php echo esc_html( $select_price_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_price_mar ); ?>>M</option></select>
					</td>
					<td style="text-align:center;">
						<input type="number" style="width:50% !important;" min="0" step="<?php echo esc_html( $decimal_steps ); ?>" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][adjustment_percent]" placeholder="N/A" value="<?php echo isset( $value['adjustment_percent'] ) ? esc_html( $value['adjustment_percent'] ) : ''; ?>"/>%
						<?php
						$select_percent_dis = 'selected';
						$select_percent_mar = '';
						if ( isset( $value['adj_percent_dis'] ) && 'markup' === $value['adj_percent_dis'] ) {
							$select_percent_mar = 'selected';
							$select_percent_dis = '';
						}
						?>
						<select name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][adj_percent_dis]"><option value="discount" <?php echo esc_html( $select_percent_dis ); ?>>D</option><option value="markup" <?php echo esc_html( $select_percent_mar ); ?>>M</option></select>
					</td>
					<td style="text-align:center; width: 5%;">
						<label>
							<?php $checked = ( ! empty( $value['role_price'] ) ) ? true : false; ?>
							<input type="checkbox" name="eh_pricing_discount_price_adjustment_options[<?php echo esc_html( $index ); ?>][role_price]" <?php checked( $checked, true ); ?> />
						</label>
					</td>
					<td class="remove_icon" style="text-align:center; width: 5%;">
					</td>
				</tr>
					<?php
					$index++;
				}
				?>
				<input type="hidden" class="elex_rolebased_price_adjustment_tabel_next_index"  value="<?php echo esc_html( $index ); ?>" />
			<?php
			}
			?>
			
		</tbody>
		<tr>
		<td></td>
		<td>
			<br>
			<button type="button" id="elex_rp_add_rule"  ><?php esc_html_e( 'Add Rule', 'elex-catmode-rolebased-price' ); ?></button>
		</td>
		</tr>
	</table>
</td>
</tr>
<script type="text/javascript">
jQuery(document).ready(function(e){
	jQuery("#elex_rp_table_body").on('click', '.remove_icon', function (e) {
		e.preventDefault();
		jQuery(this).closest("tr").remove().find('input').val('');
	});
	jQuery('#elex_rp_add_rule').click( function(e) {
		e.preventDefault();
		var tbody = jQuery('.price_adjustment').find('tbody');
		var size = jQuery('.elex_rolebased_price_adjustment_tabel_next_index').val();

		var user_roles = '<?php echo wp_kses( $user_role_options, $allowed_html ); ?>';
		var categories = '<?php echo wp_kses( addcslashes( $category_options, "'" ), $allowed_html ); ?>';
		var decimal_steps = '<?php echo esc_html( $decimal_steps ); ?>';
		var currency_symbol = '<?php echo esc_attr( get_woocommerce_currency_symbol() ); ?>';
		var code = `<tr >
					<td class="sort"><input type="hidden" class="order" name="eh_pricing_discount_price_adjustment_options[`+size+`]"/></td>
					<td style="width: 15%;"><select id="roles_field"  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[`+size+`][roles][]"  multiple="multiple" style="width: 25%;float: left;">`+ user_roles + `</select></td>
					<td style="width: 15%;"><select  data-placeholder="N/A" class="wc-customer-search" name="eh_pricing_discount_price_adjustment_options[`+size+`][users][]"  multiple="multiple" style="width: 25%;float: left;"></select></td>
					<td style="width: 15%;"><select  data-placeholder="N/A" class="wc-enhanced-select" name="eh_pricing_discount_price_adjustment_options[`+size+`][category][]"  multiple="multiple" style="width: 25%;float: left;">` + categories + `</select></td>
					<td style="text-align:center;">`+currency_symbol+`<input type="number" style="width:50% !important;" min="0" step="`+decimal_steps+`" name="eh_pricing_discount_price_adjustment_options[`+size+`][adjustment_price]" placeholder="N/A"  /> <select name="eh_pricing_discount_price_adjustment_options[`+size+`][adj_price_dis]"><option value="discount">D</option><option value="markup" >M</option></select></td>
					<td style="text-align:center;"><input type="number" style="width:50% !important;" min="0" step="'+decimal_steps+'" name="eh_pricing_discount_price_adjustment_options[`+size+`][adjustment_percent]" placeholder="N/A"/>%<select name="eh_pricing_discount_price_adjustment_options[`+size+`][adj_percent_dis]"><option value="discount">D</option><option value="markup">M</option></select></td>
					<td style="text-align:center; width: 5%;"><label><input type="checkbox" name="eh_pricing_discount_price_adjustment_options[`+size+`][role_price]" /></label></td>
					<td class="remove_icon" style="text-align:center; width: 5%;"></td>
													 </tr>`;
					jQuery('#elex_rp_table_body').append( code );
					jQuery('#roles_field').trigger('wc-enhanced-select-init');
					size++;
					jQuery('.elex_rolebased_price_adjustment_tabel_next_index').val(size);
					return false;
	});
});


</script>

<style type="text/css">
	.price_adjustment td {
		vertical-align: middle;
		padding: 4px 7px;
	}
	.price_adjustment th {
		padding: 9px 7px;
	}
	.price_adjustment td input {
		margin-right: 4px;
	}
	.price_adjustment .check-column {
		vertical-align: middle;
		text-align: left;
		padding: 0 7px;
	}
	.woocommerce table.form-table .select2-container {
	min-width: 257px!important;
}
	.price_adjustment th.sort {
		width: 16px;
		padding: 0 16px;
	}
	.price_adjustment td.sort {
		width: 16px;
		padding: 0 16px;
		cursor: move;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAHUlEQVQYV2O8f//+fwY8gJGgAny6QXKETRgEVgAAXxAVsa5Xr3QAAAAASUVORK5CYII=) no-repeat center;   
		}
	.price_adjustment td.remove_icon  {
		width: 16px;
		padding: 0 16px;
		cursor: pointer;
		background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAA3NCSVQICAjb4U/gAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAAEnQAABJ0Ad5mH3gAAAAZdEVYdFNvZnR3YXJlAHd3dy5pbmtzY2FwZS5vcmeb7jwaAAAB70lEQVQ4T61UPUhbURT+7n0vLxE1vmqFEBTR6lS7uHaTYpfqopsu0qkgODh0EURwadFBEJy62a3t0BbBIm5dXdTJP/whiFr7EpUmL3m5nnNixR80EfLBXe79vu+ce865VxkCbsHf2Ud6eQWZzS1k1tZlL/y8DeFnLYh0vIDT1CB713HDKPBS8D5/xemPX8hns1COA2VbcmZyAYzvQ4dCqO7ugtvfC8uNyhnjyiibOMDByDhyngdFZKW1EG7D5PMwFMR2XcSmxhCKx2RfjIJkCol375E7IZMwZaGUHN4Hjm0yPuxaF/HZD7BqopCw3twXBH9LM2Ewh7msYS1D+zt7OP25CNh0HdqQaCUsCUca1rKHTi+vIk9FVFrR/YmUTsP8K7KYQ1zWsJY91OHHGXO29Fu6Y7k1iPa8ptwlNY55F3x1Okp9X6AuJ6WbVZ0voXYHh01w9AegbjitzYhPT1wqHkZieBT+xjYVR8OqrysUuxwo39WS3+bN8cwnWFWVhWL7GSE+CPJSTliKHZyd4+nQW+hIRzs0PYX/XVCRCFRFkcWcyy6ztuDR1IjqN6+AXFYSkWErYUnSpGEte0ix3YE+WE9cGXsetmKQoSQua1jLECN+K7HJMdhsRgPGD/M+yKMlDnNZw1pG+b+R63j8xwZcADXJQNHUd268AAAAAElFTkSuQmCC) no-repeat center
	}
	.price_adjustment #elex_rp_add_rule {
		cursor: pointer;
	}               
</style>

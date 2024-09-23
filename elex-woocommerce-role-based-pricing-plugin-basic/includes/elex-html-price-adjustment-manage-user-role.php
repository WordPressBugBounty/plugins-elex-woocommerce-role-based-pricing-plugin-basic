<?php
// to check whether accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
wp_nonce_field( basename( __FILE__ ), 'elex-rp-fields-nonce' );
?>

<div class="wrap" style="padding-left: 25px;width: 70%;">
   <div id="content">
	 <input type="hidden" id="pricing_discount_manage_user_roles" name="pricing_discount_manage_user_roles" value="add_user_role">
	 <div id="poststuff">
		<div class="postbox">
		   <h3 class="hndle_add_user_role" style="cursor:pointer;padding-left: 15px;padding-bottom: 15px;border-bottom: solid 1.5px black;color: #5b9dd9;">
		   <?php
			  esc_attr_e( 'Add Custom User Role', 'elex-catmode-rolebased-price' ); 
			?>
			  </h3>
		   <div class="add_user_role" style="border-bottom: solid 1.5px black;">
			  <table class="form-table">
				 <tr>
					<th style="padding: 15px;">
					   <label for="eh_woocommerce_pricing_discount_user_role_name"><b>
					   <?php
						  esc_attr_e( 'User Role', 'elex-catmode-rolebased-price' ); 
						?>
						  </b></label>
					</th>
					<td>
					   <input type="text" name="eh_woocommerce_pricing_discount_user_role_name" class="regular-text" value= ><br />
					   <span class="description">
					   <?php
						  esc_attr_e( 'Enter the name of the user role you want to create and click the Save Changes button.', 'elex-catmode-rolebased-price' );
						?>
						  </span>
					</td>
				 </tr>
			  </table>
		   </div>
		   <h3 class="hndle_delete_user_role" style="cursor:pointer;padding-left: 15px;color: #5b9dd9;">
		   <?php
			  esc_attr_e( 'Remove User Role', 'elex-catmode-rolebased-price' ); 
			?>
			  </h3>
		   <div class="delete_user_role" style="border-top: solid 1.5px black;">
			  <table class="form-table">
				<?php 
				global $wp_roles;
				$user_roles = $wp_roles->role_names;
				foreach ( $user_roles as $user_role_id => $name ) { 
					if ( 'administrator' === $user_role_id ) {
						continue;
					}
					?>
				<tr>
					<td>
					</td>
					<td>
					<label><input type="checkbox" name="pricing_discount_remove_user_role[<?php esc_attr_e( $user_role_id , 'elex-catmode-rolebased-price' ); ?>]" ><?php echo esc_html( $name ); ?></label>
					</td>
				</tr>
				<?php } ?>
				<br/><span class="description" style="padding: 15px;">
				<?php
					esc_attr_e( 'Select the user role you want to delete and click the Save Changes button.', 'elex-catmode-rolebased-price' );
				?>
				</span>
			</table>
		   </div>
		</div>
	 </div>
   </div>
</div>
<script>
jQuery(document).ready(function(){
	jQuery('.hndle_add_user_role').click(function(){
		elex_rp_pricing_discount_manage_role('add_user_role');
	});
	jQuery('.hndle_delete_user_role').click(function(){
		elex_rp_pricing_discount_manage_role('delete_user_role');
	});
		jQuery('.delete_user_role').hide();
		jQuery('.add_user_role').hide();
});
function elex_rp_pricing_discount_manage_role(manage_role){
	switch(manage_role){
		case 'add_user_role':
			jQuery('.add_user_role').show();
			jQuery('.delete_user_role').hide();
			jQuery('#pricing_discount_manage_user_roles').val('add_user_role');
			jQuery("input[name='save']").val('Add User Role');
			break;
		case 'delete_user_role':
			jQuery('.add_user_role').hide();
			jQuery('.delete_user_role').show();
			jQuery('#pricing_discount_manage_user_roles').val('remove_user_role');
			jQuery("input[name='save']").val('Delete User Role');
			break;
	}
}
</script>

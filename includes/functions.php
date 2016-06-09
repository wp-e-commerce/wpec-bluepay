<?php
function wpec_save_bluepay_settings() {
	
	update_option('bluepay_login', $_POST['bluepay_login']);
	update_option('bluepay_password', $_POST['bluepay_password']);
	
	if( ! empty( $_POST['bluepay_testmode'] ) ) {
		update_option('bluepay_testmode', 1);
	} else {
		update_option('bluepay_testmode', 0);
	}

	return true;
}

function wpec_bluepay_settings_form() {
	$output = "
	<tr>
	  <td>".__( 'Account ID:', 'wpsc_gold_cart' )."</td>
	  <td colspan='2'>
		<input type='text' size='40' value='".get_option('bluepay_login')."' name='bluepay_login' />
	  </td>
	</tr>
	<tr>
		<td>".__( 'Secret Key:', 'wpsc_gold_cart' )."</td>
		<td colspan='2'>
			<input type='text' size='40' value='".get_option('bluepay_password')."' name='bluepay_password' />
		</td>
	</tr>
	<tr>
		<td>".__( 'Test Mode', 'wpsc_gold_cart' )."</td>
		<td colspan='2'>\n";
			if( get_option('bluepay_testmode') == 1 ) {
				$output .= "<input type='checkbox' size='3' value='1' checked='true' name='bluepay_testmode' />";
			} else {
				$output .= "<input type='checkbox' size='3' value='1' name='bluepay_testmode' />";
			}
	$output .= "</td></tr>";
	
	return $output;
}

function wpec_bluepay_checkout_fields() {
	global $gateway_checkout_form_fields;
	if( in_array( 'wpec_bluepay', (array) get_option('custom_gateway_options') ) ) {
		
		$curryear = date( 'Y' );
		$curryear_2 = date( 'y' );
		$years = '';
		//generate year options
		for ( $i = 0; $i < 10; $i++ ) {
			$years .= "<option value='" . $curryear_2 . "'>" . $curryear . "</option>\r\n";
			$curryear++;
			$curryear_2++;
		}
		ob_start(); ?>
		<tr>
			<td class="wpsc_CC_details"> <?php _e( 'Credit Card Number *', 'wpsc' ); ?></td>
			<td>
				<input type="text" value='' name="card_number" />
			</td>
		</tr>
		<tr>
			<td class='wpsc_CC_details'><?php _e( 'Card Expiration *', 'wpsc' ); ?></td>
			<td>
				<select class='wpsc_ccBox' name='expiry_month'>
					<option value='01'>01</option>
					<option value='02'>02</option>
					<option value='03'>03</option>
					<option value='04'>04</option>
					<option value='05'>05</option>
					<option value='06'>06</option>
					<option value='07'>07</option>
					<option value='08'>08</option>
					<option value='09'>09</option>
					<option value='10'>10</option>
					<option value='11'>11</option>
					<option value='12'>12</option>
				</select>
				<select class='wpsc_ccBox' name='expiry_year'>
					<?php echo $years; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td class='wpsc_CC_details'><?php _e( 'CVV *', 'wpsc' ); ?></td>
			<td><input type='text' size='4' value='' maxlength='4' name='card_code' /></td>
		</tr>
		<?php
		$gateway_checkout_form_fields['wpec_bluepay'] = ob_get_clean();
	}
}
add_action( 'wpsc_init', 'wpec_bluepay_checkout_fields' );
<?php
class wpec_merchant_bluepay extends wpsc_merchant {
	
	public function submit() {

		$this->credit_card_details = array(
			'card_number' => $_POST['card_number'],
			'expiry_month' => $_POST['expiry_month'],
			'expiry_year' => $_POST['expiry_year'],
			'card_code' => $_POST['card_code']
		);
	
		$x_Login= urlencode( get_option( 'bluepay_login' ) ); // Replace LOGIN with your login
		$x_Password= urlencode(get_option("bluepay_password")); // Replace PASS with your password
		$x_Delim_Data= urlencode("TRUE");
		$x_Delim_Char= urlencode(",");
		$x_Encap_Char= urlencode("");
		$x_Type= urlencode("AUTH_CAPTURE");

		$x_ADC_Relay_Response = urlencode("FALSE");
		if(get_option('bluepay_testmode') == 1)
		{
		$x_Test_Request= urlencode("TRUE"); // Remove this line of code when you are ready to go live
		}
		#
		# Customer Information
		#
		$x_Method= urlencode("CC");
		$x_Amount= urlencode( nzshpcrt_overall_total_price( wpsc_get_customer_meta( 'shipping_country' ) ) );
		//exit($x_Amount);
		$x_First_Name= urlencode($this->cart_data['billing_address']['first_name']);
		$x_Last_Name= urlencode($this->cart_data['billing_address']['last_name']);
		$x_Card_Num= urlencode( $this->credit_card_details['card_number'] );
		$x_Exp_Date = urlencode( ( $this->credit_card_details['expiry_month'] . $this->credit_card_details['expiry_year'] ) );
		$x_Address= urlencode( $this->cart_data['billing_address']['address'] );
		$x_City= urlencode( $this->cart_data['billing_address']['city'] );

		$x_State= urlencode( $this->cart_data['billing_address']['state'] ); //gets the state from the input box not the usa ddl

		//if (empty($State)){ // check if the state is there from the input box if not get it from the ddl
		//$State_id= $_POST['collected_data'][get_option('bluepay_form_country')][1];
		//$x_State = urlencode(wpsc_get_state_by_id($State_id, 'name'));
		//}else{
		//$x_State = $State;
		//}

		$x_description = '';
		foreach($this->cart_items as $cart_row) {
			$x_description .= $cart_row['name'] . ' / ';
		}
			
		$x_Zip= urlencode( $this->cart_data['billing_address']['post_code'] );
		$x_Email= urlencode( $this->cart_data['email_address'] );
		$x_Email_Customer= urlencode("TRUE");
		$x_Merchant_Email= urlencode(get_option('purch_log_email')); //  Replace MERCHANT_EMAIL with the merchant email address
		$x_Card_Code = urlencode( $this->credit_card_details['card_code'] );
		#
		# Build fields string to post
		#
		$fields="x_Version=3.1&x_Login=$x_Login&x_Delim_Data=$x_Delim_Data&x_Delim_Char=$x_Delim_Char&x_Encap_Char=$x_Encap_Char";
		$fields.="&x_Type=$x_Type&x_Test_Request=$x_Test_Request&x_Method=$x_Method&x_Amount=$x_Amount&x_First_Name=$x_First_Name";
		$fields.="&x_Last_Name=$x_Last_Name&x_Card_Num=$x_Card_Num&x_Exp_Date=$x_Exp_Date&x_Card_Code=$x_Card_Code&x_Address=$x_Address&x_City=$x_City&x_State=$x_State&x_Zip=$x_Zip&x_Email=$x_Email&x_Email_Customer=$x_Email_Customer&x_Merchant_Email=$x_Merchant_Email&x_ADC_Relay_Response=$x_ADC_Relay_Response&x_description=$x_description";

		if($x_Password!='')
		{
		$fields.="&x_Password=$x_Password";
		}
		//exit($fields);
		#
		# Start CURL session
		#
		$agent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
		$ref = get_option('transact_url'); // Replace this URL with the URL of this script

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://secure.bluepay.com/interfaces/a.net");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_REFERER, $ref);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$buffer = curl_exec($ch);
		curl_close($ch);

		// This section of the code is the change from Version 1.
		// This allows this script to process all information provided by Authorize.net...
		// and not just whether if the transaction was successful or not

		// Provided in the true spirit of giving by Chuck Carpenter (Chuck@MLSphotos.com)
		// Be sure to email him and tell him how much you appreciate his efforts for PHP coders everywhere

		$return = preg_split("/[,]+/", "$buffer"); // Splits out the buffer return into an array so . . .
		$details = $return[0]; // This can grab the Transaction ID at position 1 in the array
		// echo "Location: ".$transact_url.$seperator."sessionid=".$sessionid;
		// exit("<pre>".print_r($return,true)."</pre>");
		// Change the number to grab additional information.  Consult the AIM guidelines to see what information is provided in each position.

		// For instance, to get the Transaction ID from the returned information (in position 7)..
		// Simply add the following:
		// $x_trans_id = $return[6];

		// You may then use the switch statement (or other process) to process the information provided
		// Example below is to see if the transaction was charged successfully
		if(get_option('permalink_structure') != '') {
		$seperator ="?";
		} else {
			$seperator ="&";
		}
		//exit("<pre>".print_r($return,true)."</pre>");
		switch ($details) {
			case 1: // Credit Card Successfully Charged
			
			$purchase_log = new WPSC_Purchase_Log( $this->cart_data['session_id'], 'sessionid' );
			$purchase_log->set( 'processed', WPSC_Purchase_Log::ACCEPTED_PAYMENT );
			$purchase_log->save();
			header("Location: ".get_option('transact_url').$seperator."sessionid=".$this->cart_data['session_id']);
			exit();
			break;
		
			default: // Credit Card Not Successfully Charged
			$errors = wpsc_get_customer_meta( 'checkout_misc_error_messages' );
			if ( ! is_array( $errors ) )
				$errors = array();
			$errors[] = "Credit Card Processing Error: ".$return[3];
			
			wpsc_update_customer_meta( 'checkout_misc_error_messages', $errors );
			$checkout_page_url = get_option( 'shopping_cart_url' );
			if ( $checkout_page_url ) {
			  header( 'Location: '.$checkout_page_url );
			  exit();
			}
			exit();
			break;
		}	
	}
}
?>
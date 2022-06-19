<?php
// Google Credentials
$client_id = get_option("gcapi_client_id");
$client_secret = get_option("gcapi_client_secret");

$gcapi_access_token = get_access_token_for_api_call($client_id, $client_secret);

$google_login_url = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . $return_url . '&response_type=code&client_id=' . $client_id . '&access_type=offline';

if( isset($_GET["code"]) ) {
	$authorization_code = $_GET["code"];
	update_option("mpc_google_authorization_code", $authorization_code);

	$access_token_response = get_gcapi_access_token($client_id, $return_url, $client_secret, $authorization_code);
	if ( is_array( $access_token_response ) ) {
		$mpc_gcapi_access_token = $access_token_response["access_token"];
		$gcapi_access_token_expire = $access_token_response["expires_in"];
		$gcapi_refresh_token = $access_token_response["refresh_token"];

		if( $mpc_gcapi_access_token ) {
			update_option("mpc_gcapi_access_token", $mpc_gcapi_access_token);
		}
		if( $gcapi_access_token_expire ) {
			$token_expire_time = time() + (int) $gcapi_access_token_expire;
			update_option("mpc_gcapi_access_token_expire", $token_expire_time);
		}
		if( $gcapi_refresh_token ) {
			update_option("mpc_gcapi_refresh_token", $gcapi_refresh_token);
		}
	}
    ?>
    <script type="text/javascript">
        window.location = "<?php echo $return_url; ?>";
    </script>
    <?php
}

if( isset($_POST["gcapi_form_submit"]) ) {
	if( wp_verify_nonce( $_POST['gcapi_settings_nonce'], 'gcapi_settings_action' ) ) {
		$client_id = sanitize_text_field( $_POST["gcapi_client_id"] );
		$client_secret = sanitize_text_field( $_POST["gcapi_client_secret"] );
		$calendars_list = sanitize_text_field( $_POST["calendars_list"] );

		if( $client_id ) {
			update_option("gcapi_client_id", $client_id);
		}
		if( $client_secret ) {
			update_option("gcapi_client_secret", $client_secret);
		}
		if( $calendars_list ) {
			update_option("gcapi_calendar_id", $calendars_list);
		}

		echo '<div class="updated"><p>'.__('Success, changes saved!', 'maplespace-practical-code').'</p></div>';
	}
}
<?php
// Trello Credentials
$trello_api_key = get_option("mpc_trello_api_key");
$trello_token = get_option("mpc_trello_token");

$app_name = "Maplespace Practical Code";

$trello_auth_url = "https://trello.com/1/authorize?return_url=".$return_url."&expiration=never&name=".$app_name."&scope=read&key=".$trello_api_key;

if( isset($_POST["trello_form_submit"]) ) {
	if( wp_verify_nonce( $_POST['trello_settings_nonce'], 'trello_settings_action' ) ) {
		$trello_api_key = sanitize_text_field( $_POST["trello_api_key"] );
		$trello_board = sanitize_text_field( $_POST["trello_board"] );
		$trello_list = sanitize_text_field( $_POST["trello_list"] );
		$current_list_id = get_option("mpc_trello_list");

		if( $trello_api_key ) {
			update_option("mpc_trello_api_key", $trello_api_key);
		}
		if( $trello_board ) {
			update_option("mpc_trello_board", $trello_board);
		}
		if( $trello_list ) {
			update_option("mpc_trello_list", $trello_list);
		}

		if( $trello_list && ($current_list_id != $trello_list) ) {

			// create a new webhook in Trello if the list/column is changed
			create_trello_webhook($trello_api_key, $trello_token);

		}
		echo '<div class="updated"><p>'.__('Success, changes saved!', 'maplespace-practical-code').'</p></div>';
	}
}
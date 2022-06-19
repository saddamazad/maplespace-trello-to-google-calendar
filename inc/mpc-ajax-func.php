<?php
// save the Trello access token
add_action("wp_ajax_save-trello-token", "save_trello_token");
function save_trello_token() {
	$token = sanitize_text_field( $_POST["token"] );
	update_option( 'mpc_trello_token', $token );
	
	wp_send_json( array("success" => true) );
	wp_die();
}

// get the lists of a Trello board
add_action("wp_ajax_get-trello-boards-lists", "get_trello_boards_lists");
function get_trello_boards_lists() {
	$board_id = sanitize_text_field( $_POST["board_id"] );
	$trello_api_key = get_option("mpc_trello_api_key");
	$trello_token = get_option("mpc_trello_token");
	
	$trelloListsUrl = 'https://api.trello.com/1/boards/'.$board_id.'/lists?fields=name,url&key='.$trello_api_key.'&token='.$trello_token;
	$listsResponse = wp_remote_get($trelloListsUrl);
	$listsResponseBody = wp_remote_retrieve_body( $listsResponse );
	$listsResult = json_decode( $listsResponseBody, true );
	$lists_html = '';
	if ( is_array( $listsResult ) && ! is_wp_error( $listsResult ) ) {
		foreach($listsResult as $list) {
			$lists_html .= '<option value="'.$list["id"].'">'.$list["name"].'</option>';
		}
	}
	
	wp_send_json( array("success" => true, "html" => $lists_html) );
	wp_die();
}
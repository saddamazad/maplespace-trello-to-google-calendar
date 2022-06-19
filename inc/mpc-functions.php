<?php
require_once("mpc-ajax-func.php");

/**
 * Get the user's boards on Trello
 * @param string $trello_api_key
 * @param string $trello_token
 *
 * @return mixed
 */
function get_trello_boards($trello_api_key, $trello_token) {
	$trello_board_id = get_option("mpc_trello_board");
	
	$boards = '';
	if( $trello_token && $trello_api_key ) {
		$trelloBoardsUrl = 'https://api.trello.com/1/members/me/boards?fields=name,url&key='.$trello_api_key.'&token='.$trello_token;
		$boardsResponse = wp_remote_get($trelloBoardsUrl);
		$boardsResponseBody = wp_remote_retrieve_body( $boardsResponse );
		$boardsResult = json_decode( $boardsResponseBody, true );
		if ( is_array( $boardsResult ) && ! is_wp_error( $boardsResult ) ) {
			foreach($boardsResult as $board) {
				$selected = ($trello_board_id == $board["id"]) ? 'selected="selected"' : '';
				$boards .= '<option value="'.$board["id"].'" '.$selected.'>'.$board["name"].'</option>';
			}
		}
	}
	
	return $boards;
}

/**
 * Get the lists of a board
 * @param string $trello_api_key
 * @param string $trello_token
 *
 * @return mixed
 */
function get_trello_lists($trello_api_key, $trello_token) {
	$trello_list_id = get_option("mpc_trello_list");
	$trello_board_id = get_option("mpc_trello_board");
	
	$lists = '';
	if( $trello_token && $trello_api_key ) {
		$trelloListsUrl = 'https://api.trello.com/1/boards/'.$trello_board_id.'/lists?fields=name,url&key='.$trello_api_key.'&token='.$trello_token;
		$listsResponse = wp_remote_get($trelloListsUrl);
		$listsResponseBody = wp_remote_retrieve_body( $listsResponse );
		$listsResult = json_decode( $listsResponseBody, true );
		if ( is_array( $listsResult ) && ! is_wp_error( $listsResult ) ) {
			foreach($listsResult as $list) {
				$selected = ($trello_list_id == $list["id"]) ? 'selected="selected"' : '';
				$lists .= '<option value="'.$list["id"].'" '.$selected.'>'.$list["name"].'</option>';
			}
		}
	}
	
	return $lists;
}

/**
 * Create a webhook in Trello
 * @param string $trello_api_key
 * @param string $trello_token
 *
 * @return mixed
 */
function create_trello_webhook($trello_api_key, $trello_token) {
	// idModel can be the ID of any list/board/card etc.
	$idModel = get_option("mpc_trello_list");

	//$webhook_callback_url = MPC_URL."webhooks.php";
	$webhook_callback_url = home_url("/wp-trello-webhook.php");

	$endpoint = "https://api.trello.com/1/webhooks";
	$curlPost = 'key=' . $trello_api_key . '&token=' . $trello_token . '&idModel=' . $idModel . '&callbackURL=' . $webhook_callback_url;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	
	$data = json_decode(curl_exec($ch), true);
	if($data !== NULL) {
		return $data;
	} else {
		return false;
	}
}

/**
 * Get access token for Google calendar API
 * @param string $client_id
 * @param string $redirect_uri
 * @param string $client_secret
 * @param string $code
 *
 * @return mixed
 */
function get_gcapi_access_token($client_id, $redirect_uri, $client_secret, $code) {
	//$new_url = 'https://oauth2.googleapis.com/token';
	$url = 'https://www.googleapis.com/oauth2/v4/token';
	$curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_URL, $url);		
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
	curl_setopt($ch, CURLOPT_POST, 1);		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
	$data = json_decode(curl_exec($ch), true);
	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
	if($http_code != 200) 
		throw new Exception('Error : Failed to receieve access token');
	
	return $data;
}

/**
 * Get refresh token for Google calendar API
 * @param string $client_id
 * @param string $client_secret
 * @param string $refresh_token
 *
 * @return mixed
 */
function get_gcapi_refresh_token($client_id, $client_secret, $refresh_token) {
	//$new_url = 'https://oauth2.googleapis.com/token';
	$url = 'https://www.googleapis.com/oauth2/v4/token';
	$curlPost = 'client_id=' . $client_id . '&client_secret=' . $client_secret . '&refresh_token='. $refresh_token . '&grant_type=refresh_token';
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_URL, $url);		
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
	curl_setopt($ch, CURLOPT_POST, 1);		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);	
	$data = json_decode(curl_exec($ch), true);
	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
	if($http_code != 200) 
		throw new Exception('Error : Failed to receieve access token');
	
	return $data;
}

/**
 * Get the user's calendar timezone
 * @param string $access_token
 *
 * @return mixed
 */
function get_gcapi_user_calendar_timezone($access_token) {
	$url_settings = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone';

	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_URL, $url_settings);		
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	
	$data = json_decode(curl_exec($ch), true);
	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
	if($http_code != 200) 
		throw new Exception('Error : Failed to get timezone');

	return $data['value'];
	//var_dump($data);
}

/**
 * Get the user's calendars list
 * @param string $access_token
 *
 * @return mixed
 */
function get_gcapi_calendars_list($access_token) {
	$url_parameters = array();

	$url_parameters['fields'] = 'items(id,summary,timeZone)';
	$url_parameters['minAccessRole'] = 'owner';

	$url_calendars = 'https://www.googleapis.com/calendar/v3/users/me/calendarList?'. http_build_query($url_parameters);

	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_URL, $url_calendars);		
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $access_token));	
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);	
	$data = json_decode(curl_exec($ch), true);
	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
	if($http_code != 200) 
		throw new Exception('Error : Failed to get calendars list');

	return $data['items'];
}

/**
 * Get a refresh token if the access token has expired
 * @param string $client_id
 * @param string $client_secret
 *
 * @return mixed
 */
function get_access_token_for_api_call($client_id, $client_secret) {
	$access_token = get_option("mpc_gcapi_access_token");
	$refresh_token = get_option("mpc_gcapi_refresh_token");

	if( $access_token ) {
		$gcapi_access_token_expire = get_option("mpc_gcapi_access_token_expire");
		$current_time = time();

		// check if the access token is expired
		if( $gcapi_access_token_expire && ($gcapi_access_token_expire < $current_time) ) {
			// get a new access token
			$new_access_token_response = get_gcapi_refresh_token($client_id, $client_secret, $refresh_token);
			if ( is_array( $new_access_token_response ) ) {
				$gcapi_new_access_token = $new_access_token_response["access_token"];
				$gcapi_new_access_token_expire = $new_access_token_response["expires_in"];

				// save/set the new access token
				if( $gcapi_new_access_token ) {
					update_option("mpc_gcapi_access_token", $gcapi_new_access_token);
					$access_token = $gcapi_new_access_token;
				}

				// save the new access token expire time
				if( $gcapi_new_access_token_expire ) {
					$new_token_expire_time = time() + (int) $gcapi_new_access_token_expire;
					update_option("mpc_gcapi_access_token_expire", $new_token_expire_time);
				}
			}			
		}
		
		return $access_token;
	} else {
		return false;
	}
}

/**
 * Get calendar dropdown options
 * @param string $access_token
 *
 * @return mixed
 */
function get_calendar_dropdown_options($access_token) {
	$calendar_list = '';
	
	if( $access_token ) {
		$calendars = get_gcapi_calendars_list($access_token);

		foreach($calendars as $calendar) {
			$selected = ( $calendar["id"] == get_option("gcapi_calendar_id") ) ? 'selected="selected"' : '';
			$calendar_list .= '<option value="'.$calendar["id"].'" '.$selected.'>'.$calendar["summary"].'</option>';
			//echo $calendar["timeZone"];
		}
	}
	
	return $calendar_list;
}
<?php
$filename = trailingslashit( ABSPATH )."wp-trello-webhook.php";

if( ! file_exists($filename) ) {
	$file = fopen($filename, 'w') or die("Can't create file");
	if($file) {
		$code = <<<'EOD'
<?php
require_once("wp-load.php");

$webhookContent = "";

$webhook = fopen('php://input' , 'rb');

while (!feof($webhook)) {
	$webhookContent .= fread($webhook, 4096);
}

$response = json_decode($webhookContent, true);

$trello_list_id = get_option("mpc_trello_list");

$action_type = $response["action"]["type"];
$list_id = $response["model"]["id"];

if( ($trello_list_id == $list_id) && ($action_type == "createCard") ) {
    $event_title = $response["action"]["data"]["card"]["name"];

	$client_id = get_option("gcapi_client_id");
	$client_secret = get_option("gcapi_client_secret");
    $gcapi_access_token = get_option("mpc_gcapi_access_token");
    $refresh_token = get_option("mpc_gcapi_refresh_token");
    $calendar_id = get_option("gcapi_calendar_id");
    
	$url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events';

	$curlPost = array('summary' => $event_title);
	$date = date("Y-m-d");

	$curlPost['start'] = array('date' => $date);
	$curlPost['end'] = array('date' => $date);

    if( $gcapi_access_token ) {
    	$gcapi_access_token_expire = get_option("mpc_gcapi_access_token_expire");
    	$current_time = time();
    	
    	// check if the access token is expired
    	if( $gcapi_access_token_expire && ($gcapi_access_token_expire < $current_time) ) {
    		// get a new access token
    		$new_access_token_response = get_mpc_gcapi_refresh_token($client_id, $client_secret, $refresh_token);
    		if ( is_array( $new_access_token_response ) ) {
    			$gcapi_new_access_token = $new_access_token_response["access_token"];
    			$gcapi_new_access_token_expire = $new_access_token_response["expires_in"];
    
    			// save/set the new access token
    			if( $gcapi_new_access_token ) {
    				update_option("mpc_gcapi_access_token", $gcapi_new_access_token);
    				$gcapi_access_token = $gcapi_new_access_token;
    			}
    			
    			// save the new access token expire time
    			if( $gcapi_new_access_token_expire ) {
    				$new_token_expire_time = time() + (int) $gcapi_new_access_token_expire;
    				update_option("mpc_gcapi_access_token_expire", $new_token_expire_time);
    			}
    		}
    	}

    	$ch = curl_init();		
    	curl_setopt($ch, CURLOPT_URL, $url_events);		
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
    	curl_setopt($ch, CURLOPT_POST, 1);		
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '. $gcapi_access_token, 'Content-Type: application/json'));	
    	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));	
    	$data = json_decode(curl_exec($ch), true);
    	$http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE);		
    	if($http_code != 200) 
    		throw new Exception('Error : Failed to create event');
    
    }
}

fclose($webhook);


function get_mpc_gcapi_refresh_token($client_id, $client_secret, $refresh_token) {
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
EOD;
		echo fwrite($file, $code); 
		fclose($file); 
	}
}
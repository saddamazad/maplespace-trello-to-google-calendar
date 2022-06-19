<?php
	$return_url = admin_url("options-general.php?page=mpc-settings");

	require_once("mpc-trello.php");
	require_once("mpc-gcalendar.php");
?>
<div class="wrap">
	<h1 class="mpc-title">Maplespace Practical Code <?php echo __( 'Settings', 'maplespace-practical-code' ); ?></h1>
	
	<div class="platform-settings-wrap">
		<h2 class="mpc-heading"><?php echo __( 'Trello Settings', 'maplespace-practical-code' ); ?></h2>
		<p class="mpc-notice">
			<a href="https://trello.com/app-key" target="_blank"><?php echo __( 'Get Your API Key', 'maplespace-practical-code' ); ?></a>
		</p>
		<p class="mpc-notice">
			<strong><?php echo __( 'Redirect URI: ', 'maplespace-practical-code' ); ?></strong>
			<a href="<?php echo site_url(); ?>"><?php echo site_url(); ?></a>
			(<em class="imp-note"><?php echo __( 'Add this domain in "Allowed Origins" when you generate the API Key', 'maplespace-practical-code' ); ?></em>)
		</p>
		<form name="trello_settings" method="post" action="options-general.php?page=mpc-settings">
			<?php wp_nonce_field( 'trello_settings_action', 'trello_settings_nonce' ); ?>
			<div class="mpc-field">
				<label for="trello_api_key"><?php echo __( 'API Key', 'maplespace-practical-code' ); ?></label>
				<input type="text" name="trello_api_key" id="trello_api_key" value="<?php echo $trello_api_key; ?>" />
			</div>
			<?php if( $trello_api_key ) { ?>
			<div class="mpc-auth-button">
				<a href="<?php echo $trello_auth_url; ?>" class="button primary-button"><?php echo __( 'Authenticate', 'maplespace-practical-code' ); ?></a>
			</div>
			<?php } ?>
			<div class="mpc-field">
				<label for="trello_board"><?php echo __( 'Board', 'maplespace-practical-code' ); ?></label>
				<select name="trello_board" id="trello_board">
					<option value=""><?php echo __( 'Select Board', 'maplespace-practical-code' ); ?></option>
					<?php echo get_trello_boards($trello_api_key, $trello_token); ?>
				</select>
			</div>
			<div class="mpc-field">
				<label for="trello_list"><?php echo __( 'List/Column', 'maplespace-practical-code' ); ?></label>
				<select name="trello_list" id="trello_list">
					<option value=""><?php echo __( 'Select List/Column', 'maplespace-practical-code' ); ?></option>
					<?php echo get_trello_lists($trello_api_key, $trello_token); ?>
				</select>
			</div>
			<div class="mpc-form-submit">
				<button type="submit" name="trello_form_submit" class="button button-primary"><?php echo __( 'Save Changes', 'maplespace-practical-code' ); ?></button>
			</div>
		</form>		
	</div><!-- .platform-settings-wrap -->

	<div class="platform-settings-wrap">
		<h2 class="mpc-heading"><?php echo __( 'Google Calendar Settings', 'maplespace-practical-code' ); ?></h2>
		<p class="mpc-notice">
			<strong><?php echo __( 'Redirect URI: ', 'maplespace-practical-code' ); ?></strong>
			<a href="<?php echo $return_url; ?>"><?php echo $return_url; ?></a>
			(<em class="imp-note"><?php echo __( 'Set this as the redirect URI when you create your "project/credentials" in Google Console', 'maplespace-practical-code' ); ?></em>)
		</p>
		<form name="gcapi_settings" action="options-general.php?page=mpc-settings" method="post">
			<?php wp_nonce_field( 'gcapi_settings_action', 'gcapi_settings_nonce' ); ?>
			<div class="mpc-field">
				<label for="gcapi-client-id"><?php echo __( 'Client ID', 'maplespace-practical-code' ); ?></label>
				<input type="text" name="gcapi_client_id" id="gcapi-client-id" value="<?php echo $client_id; ?>" />
			</div>
			<div class="mpc-field">
				<label for="gcapi-client-secret"><?php echo __( 'Client Secret', 'maplespace-practical-code' ); ?></label>
				<input type="text" name="gcapi_client_secret" id="gcapi-client-secret" value="<?php echo $client_secret; ?>" />
			</div>
			<?php if( $client_id && $client_secret ) { ?>
			<div class="mpc-auth-button">
				<a href="<?php echo $google_login_url; ?>" class="button primary-button"><?php echo __( 'Authenticate', 'maplespace-practical-code' ); ?></a>
			</div>
			<?php } ?>
			<div class="mpc-field">
				<label for="trello_api_key"><?php echo __( 'Calendar', 'maplespace-practical-code' ); ?></label>
				<select name="calendars_list">
					<option value=""><?php echo __( 'Select Calendar', 'maplespace-practical-code' ); ?></option>
					<?php echo get_calendar_dropdown_options($gcapi_access_token); ?>
				</select>
			</div>
			<div class="mpc-form-submit">
				<button type="submit" name="gcapi_form_submit" class="button button-primary"><?php echo __( 'Save Changes', 'maplespace-practical-code' ); ?></button>
			</div>
		</form>
	</div><!-- .platform-settings-wrap -->
</div><!-- .wrap -->
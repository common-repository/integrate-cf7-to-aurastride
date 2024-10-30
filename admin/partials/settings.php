<?php

/**
 * CF7 to aurastride CRM Settings
 *
 *
 * @link       https://profiles.wordpress.org/vsourz1td/
 * @since      1.0.0
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/admin/partials
 */
?>
<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	die('Un-authorized access!');
}
if($_GET['tdebug'] == 't'){
	do_action('cf7au_aurastride_crm_api_enquiry_submission');
}
/**
 * Detect plugin. For use in Admin area only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//Check contact form class exist or not
if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){
	?><div class="wrap"><div class="notice error">
		<p><?php esc_html_e( 'Please activate Contact Form plugin first.', 'integrate-cf7-to-aurastride' ); ?></p>
	</div></div><?php
	return;
}
else if(defined('WPCF7_VERSION') && WPCF7_VERSION < '4.6'){
	?><div class="wrap"><div class="notice error">
		<p><?php esc_html_e( 'Please update latest version for Contact Form plugin first.', 'integrate-cf7-to-aurastride' ); ?></p>
	</div></div><?php
	return;
}

wp_enqueue_style( 'font-awesome.min' );
wp_enqueue_style( 'bootstrap-min-css' );
wp_enqueue_style( 'jquery-ui-css' );
wp_enqueue_style( 'cf7-to-aurastride-admin-css' );

//Saving to the database starts from here
$options_updated = false;
$options_error = false;
$error_message = array();
if( isset( $_POST['cf7au_setting_submit'] ) && $_POST['cf7au_setting_submit'] == "Save Settings"  ){

	$ch_cat_arr = array();

	if(check_admin_referer( 'update_cf72mut_settings','ch_setting_nonce')){

		if(isset( $_POST['cf7au_api_enable'])){

			update_option( 'cf7au_api_enable', sanitize_text_field( $_POST['cf7au_api_enable'] ) );
			if( isset( $_POST['cf7au_api_url'] ) && !empty( $_POST['cf7au_api_url'] ) ){
				update_option( 'cf7au_api_url', sanitize_text_field( $_POST['cf7au_api_url'] ) );
			}
			else{
				$options_error = true;
				$error_message[] = 'Kindly add the API Base Endpoint.';
			}

			if(isset($_POST['cf7au_authorization_code'] ) && !empty( $_POST['cf7au_authorization_code'] ) ){
				update_option( 'cf7au_authorization_code', sanitize_text_field( $_POST['cf7au_authorization_code'] ) );
			}
			else{
				$options_error = true;
				$error_message[] = 'Kindly add the Authorization Code.';
			}
			
			if(isset($_POST['cf7au_client_id'] ) && !empty( $_POST['cf7au_client_id'] ) ){
				update_option( 'cf7au_client_id', sanitize_text_field( $_POST['cf7au_client_id'] ) );
			}
			else{
				$options_error = true;
				$error_message[] = 'Kindly add the Client Id.';
			}
			
			if(isset($_POST['cf7au_client_secret'] ) && !empty( $_POST['cf7au_client_secret'] ) ){
				update_option( 'cf7au_client_secret', sanitize_text_field( $_POST['cf7au_client_secret'] ) );
			}
			else{
				$options_error = true;
				$error_message[] = 'Kindly add the Client Secret.';
			}
			
			if(isset($_POST['cf7au_authorization_key'] ) && !empty( $_POST['cf7au_authorization_key'] ) ){
				update_option( 'cf7au_authorization_key', sanitize_text_field( $_POST['cf7au_authorization_key'] ) );
			}
			else{
				$options_error = true;
				$error_message[] = 'Kindly add the Authorization Key.';
			}

			if(isset($_POST['cf7au_log_enable'])){
				update_option( 'cf7au_log_enable', sanitize_text_field( $_POST['cf7au_log_enable'] ) );
			}
			else{
				update_option( 'cf7au_log_enable', '');
			}
			
			if(isset($_POST['cf7au_send_direct'])){
				update_option( 'cf7au_send_direct', sanitize_text_field( $_POST['cf7au_send_direct'] ) );
			}
			else{
				update_option( 'cf7au_send_direct', '');
			}

			if(!$options_error){
				$options_updated = true;
			}
		}
		else{
			update_option( 'cf7au_api_enable', '');
			$arr_option = array('cf7au_api_url','cf7au_authorization_key','cf7au_token_key');
			foreach($arr_option as $opt_key){
				update_option($opt_key, trim(sanitize_text_field($_POST[$opt_key])));
			}

			if(isset($_POST['cf7au_log_enable'])){
				update_option( 'cf7au_log_enable', sanitize_text_field( $_POST['cf7au_log_enable'] ) );
			}
			else{
				update_option( 'cf7au_log_enable', '');
			}

			$options_updated = true;
		}
	}
	else{
		$options_error = true;
	}

}
?>


<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h3><?php esc_html_e( 'CF7 to aurastride CRM API Settings', 'integrate-cf7-to-aurastride' ); ?></h3>
    <p><?php esc_html_e( '', 'integrate-cf7-to-aurastride' ); ?></p>
    <hr></hr>
    <?php
    	if( $options_updated ){
    		echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully</p></div>';
    	}
    	if( $options_error ){
    		if(!empty( $error_message)){
    			foreach ($error_message as $msg) {
    				echo '<div class="notice notice-error is-dismissible"><p>'.esc_html($msg).'</p></div>';
    			}
    		}
			else{
    			echo '<div class="notice notice-error is-dismissible"><p>You are not authorized to do any actions.</p></div>';
    		}
    	}

    ?><form name="update_cf72mut_settings" id="update_cf72mut_settings" class="" method="post" action="<?php echo esc_url(admin_url('admin.php?page=cf7au-api-settings')); ?>">
    	<?php
    		wp_nonce_field( 'update_cf72mut_settings','ch_setting_nonce' );
    	?>

    	<!-- Enable API -->
    	<div class="ch-fields login-enable crm-api-sec">
    		<label for="cf7au_api_enable"><?php esc_html_e( 'Enable aurastride CRM API', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="checkbox" <?php if( get_option('cf7au_api_enable') ){ echo "checked='checked'"; } ?> name="cf7au_api_enable" id="cf7au_api_enable" value="yes" class="regular-text code" >
    	</div>

    	<!-- API URL -->
    	<div class="ch-fields">
    		<label for="cf7au_api_url"><?php esc_html_e( 'API Base Endpoint*', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="text" name="cf7au_api_url" id="cf7au_api_url" value="<?php echo esc_html( get_option('cf7au_api_url') ); ?>" class="regular-text code" >
    	</div>

    	<!-- Authorization Code -->
    	<div class="ch-fields">
    		<label for="cf7au_authorization_code"><?php esc_html_e( 'Authorization Code*', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="password" name="cf7au_authorization_code" id="cf7au_authorization_code" value="<?php echo esc_html( get_option('cf7au_authorization_code') ); ?>" class="regular-text code" >
    	</div>
		
		<!-- Client Id -->
    	<div class="ch-fields">
    		<label for="cf7au_client_id"><?php esc_html_e( 'Client Id*', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="text" name="cf7au_client_id" id="cf7au_client_id" value="<?php echo esc_html( get_option('cf7au_client_id') ); ?>" class="regular-text code" >
    	</div>
		
		<!-- Client Secret -->
    	<div class="ch-fields">
    		<label for="cf7au_client_secret"><?php esc_html_e( 'Client Secret*', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="password" name="cf7au_client_secret" id="cf7au_client_secret" value="<?php echo esc_html( get_option('cf7au_client_secret')); ?>" class="regular-text code" >
    	</div>
		
		<!-- Authorization Key -->
    	<div class="ch-fields">
    		<label for="cf7au_authorization_key"><?php esc_html_e( 'Authorization Key*', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="password" name="cf7au_authorization_key" id="cf7au_authorization_key" value="<?php echo esc_html( get_option('cf7au_authorization_key') ); ?>" class="regular-text code" >
    	</div>

    	<!-- Enable Log -->
    	<div class="ch-fields login-enable">
    		<label for="cf7au_log_enable"><?php esc_html_e( 'Enable Log', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="checkbox" <?php if( get_option('cf7au_log_enable') ){ echo "checked='checked'"; } ?> name="cf7au_log_enable" id="cf7au_log_enable" value="yes" class="regular-text code" >
		</div>
		
		<!-- Enable Direct Access -->
		<div class="ch-fields login-enable">
    		<label for="cf7au_send_direct"><?php esc_html_e( 'Send a Lead Directly', 'integrate-cf7-to-aurastride' ); ?></label>
    		<input type="checkbox" <?php if( get_option('cf7au_send_direct') ){ echo "checked='checked'"; } ?> name="cf7au_send_direct" id="cf7au_send_direct" value="yes" class="regular-text code" >
		</div>
		<ul class="notice-wrap">
			<li><?php esc_html_e( 'If you are using the "Advanced CF7 DB" plugin, then this plugin sends a lead from Contact Form 7 to aurastride CRM with the cron that runs every 10 minutes.', 'integrate-cf7-to-aurastride' ); ?></li>
			<li><?php esc_html_e( 'If you tick this "Send a Lead Directly" checkbox, the lead will be sent in real time, which means when the form is submitted on the website.', 'integrate-cf7-to-aurastride' ); ?></li>
		</ul>
    	<!-- Submit form -->
    	<div class="ch_submit_btn">
    		<input type="submit" class="button button-primary save-settings" name="cf7au_setting_submit" id="cf7au_setting_submit" value="Save Settings">
    	</div>
    </form>
</div>

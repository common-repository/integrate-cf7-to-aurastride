<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die("Problem in WPINC constant");
}


// Include the WordPress filesystem functions
// require_once(ABSPATH . 'wp-admin/includes/file.php');

// Initialize the WordPress filesystem
// WP_Filesystem();

// Check if the filesystem is initialized
// if (is_wp_error($wp_filesystem)) {
    // Handle the error
    // return;
// }

class CF7AU_AURASTRIDE_API{
	
	//API Refresh Token
	protected $apiRefreshToken;
	
	//API Access Token
	protected $apiAccessToken;
	
	//API Token Expiry Time
	protected $apiExpiryTime;
	
	//Flag for generating log files
	protected $AURASTRIDE_API_LOG_FLAG;
	
	//API Credentials
	protected $arrAPICredentials;
	
	//API Token Endpoint
	protected $TOKENEP;
	
	//API Lead Endpoint
	protected $LEADEP;

	//API Lead Submit Endpoint
	protected $LEADEPCREATE;
	
	var $debugLog;
	
	var $filePointer;

	
	function __construct(){
		
		if ( !empty(get_option('cf7au_aurastride_api_refresh_token')) ) {
			$this->apiRefreshToken = get_option('cf7au_aurastride_api_refresh_token');
		}else{
			$this->apiRefreshToken = false;
		}
		if ( !empty(get_option('cf7au_aurastride_api_access_token')) ) {
			$this->apiAccessToken = get_option('cf7au_aurastride_api_access_token');
		}else{
			$this->apiAccessToken = false;
		}
		if ( !empty(get_option('cf7au_aurastride_api_expiry_time')) ) {
			$this->apiExpiryTime = get_option('cf7au_aurastride_api_expiry_time');
		}else{
			$this->apiExpiryTime = false;
		}
		if ( get_option('cf7au_log_enable') == "yes" ) {
			$this->AURASTRIDE_API_LOG_FLAG = true;
		}else{
			$this->AURASTRIDE_API_LOG_FLAG = false;
		}
		
		
		$this->arrAPICredentials = array(
			'ENABLEAPI' => get_option('cf7au_api_enable'),
			'BASEP' => get_option('cf7au_api_url'),
			'AUTHCODE' => get_option('cf7au_authorization_code'),
			'ID' => get_option('cf7au_client_id'),
			'SECRET' => get_option('cf7au_client_secret'),
			'AUTHKEY' => get_option('cf7au_authorization_key'),
		);
		$this->TOKENEP = "webapi/v2/oauth2";
		$this->LEADEP = "webapi/v2/lead/create";
		$this->LEADEPCREATE = "webapi/v2/content/create";
		$this->LEAD_FORM_READ = "webapi/v2/form/read";
		$this->LEAD_CUSTOMFIELD_READ = "webapi/v2/customfield/read";

		$this->debugLog = true;
		
		$dir = wp_upload_dir();
		$uplod_dir = $dir['basedir'];
		if ( $this->AURASTRIDE_API_LOG_FLAG ) {
			// Manage Log File - START
			$filePath = "aurastride-api-logs-". date("Y-m-d") . ".txt";
			$path=$uplod_dir.'/cf7-to-aurastride-api-logs/';
			if ( ! is_dir($path) ) {
				wp_mkdir_p($path);
			}

			$this->filePointer = fopen($path.$filePath, "a");
			// $this->filePointer = $wp_filesystem->get_contents($path.$filePath);
			// Manage Log File - END
		}
	}
	
	//get expiry time for the cusrrent token
	public function cf7au_get_expiry_time(){
		return $this->apiExpiryTime;
	}
	
	//get current token from this function
	public function cf7au_get_token_number(){
		return $this->apiAccessToken;
	}	
	
	//get API auth key here
	public function cf7au_get_api_auth_key(){
		return $this->arrAPICredentials['AUTHKEY'];
	}
	
	
	
	/*
	* This function is used to get refresh token
	*/
	public function cf7au_get_refresh_token(){
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		if($this->arrAPICredentials['AUTHCODE']) {
			$arrRequestParams['response_type'] = 'grant_token';
			$arrRequestParams['client_id'] = $this->arrAPICredentials['ID'];
			$arrRequestParams['client_secret'] = $this->arrAPICredentials['SECRET'];
			$arrRequestParams['code'] = $this->arrAPICredentials['AUTHCODE'];
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
		##### CALL AURASTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->TOKENEP,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
			'debugLog' 			=> $this->debugLog,
		);

		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);
		##### CALL AURASTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'success';			
			update_option('cf7au_aurastride_api_access_token',$response['access_token']);			
			$this->apiAccessToken = $response['access_token'];			
			$arrReturn['access_token'] = $response['access_token'];			
			if(isset($response['refresh_token']) && !empty($response['refresh_token'])){
				update_option('cf7au_aurastride_api_refresh_token',$response['refresh_token']);			
				$this->apiRefreshToken = $response['refresh_token'];	
				$arrReturn['refresh_token'] = $response['refresh_token'];			
			}
						
			update_option('cf7au_aurastride_api_expiry_time',strtotime(date('H:i:s')) + $response['expire_in']);			
			$this->apiExpiryTime = strtotime(date('H:i:s')) + $response['expire_in'];			
			$arrReturn['expiry_time'] = strtotime(date('H:i:s')) + $response['expire_in'];			
		}else{
			$response = $apiResponse;
			$arrReturn['status'] = 'error';
			$arrReturn['response'] = $response;
		}
		return $arrReturn;
	}
	
	/*
	* This function is used to get access token
	*/
	public function cf7au_get_access_token(){
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		if($this->apiRefreshToken) {
			$arrRequestParams['refresh_token'] = $this->apiRefreshToken;
			$arrRequestParams['response_type'] = 'refresh_token';
			$arrRequestParams['client_id'] = $this->arrAPICredentials['ID'];
			$arrRequestParams['client_secret'] = $this->arrAPICredentials['SECRET'];
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}else{
			$res = $this->cf7au_get_refresh_token();
			$arrRequestParams['refresh_token'] = $res['refresh_token'];
			$arrRequestParams['response_type'] = 'refresh_token';
			$arrRequestParams['client_id'] = $this->arrAPICredentials['ID'];
			$arrRequestParams['client_secret'] = $this->arrAPICredentials['SECRET'];
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
		##### CALL AURASTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->TOKENEP,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
			'debugLog' 			=> $this->debugLog,
		);

		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);
		##### CALL AURASTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'success';			
			update_option('cf7au_aurastride_api_access_token',$response['access_token']);			
			$this->apiAccessToken = $response['access_token'];			
			$arrReturn['access_token'] = $response['access_token'];									
			update_option('cf7au_aurastride_api_expiry_time',strtotime(date('H:i:s')) + $response['expire_in']);			
			$this->apiExpiryTime = strtotime(date('H:i:s')) + $response['expire_in'];			
			$arrReturn['expiry_time'] = strtotime(date('H:i:s')) + $response['expire_in'];			
		}else{
			$response = $apiResponse;
			$arrReturn['status'] = 'error';
			$arrReturn['response'] = $response;
		}
		return $arrReturn;
	}
	
	/*
	* This function is used to create lead to API
	*/
	public function cf7au_create_lead($arrParams=array()){
		
		$custom_meta_arr = array("form_type","source_type","products","source","ppc","no._of_users","third_party","enquiry","user_licenses", 'ticket_status');
		//added filter for modify Lead meta data before CRM submission
		$custom_meta_arr = (array) apply_filters('cf7au_to_aurastride_lead_custom_field_list',$custom_meta_arr);
		
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		$current_date = strtotime(date('H:i:s'));
		$access_token = $this->apiAccessToken;
		/*if(!isset($this->apiExpiryTime) || empty($this->apiExpiryTime) || $current_date >= $this->apiExpiryTime) {
			$res = $this->cf7au_get_access_token();
			$access_token = $res['access_token'];
			if(!$access_token){
				$res = $this->cf7au_get_refresh_token();
				$access_token = $res['access_token'];
			}
		}*/
		$res = $this->cf7au_get_access_token();
		$access_token = $res['access_token'];
		
		if(!empty($arrParams) && !empty($access_token)){
			$arrRequestParams['action'] = $arrParams['action'];
			$arrRequestParams['access_token'] = $access_token;
			$arrRequestParams['lead_received_date'] = $arrParams['lead_received_date'];
			$arrRequestParams['lead_name'] = $arrParams['lead_name'];
			$arrRequestParams['lead_note'] = htmlspecialchars_decode($arrParams['lead_note']);
			$arrRequestParams['lead_contact']['contact_first_name'] = $arrParams['contact_first_name'];
			$arrRequestParams['lead_contact']['contact_middle_name'] = $arrParams['contact_middle_name'];
			$arrRequestParams['lead_contact']['contact_last_name'] = $arrParams['contact_last_name'];
			$arrRequestParams['lead_contact']['contact_primary_number'] = $arrParams['contact_primary_number'];
			$arrRequestParams['lead_contact']['contact_primary_email'] = $arrParams['contact_primary_email'];
			$arrRequestParams['lead_organization']['organization_name'] = htmlspecialchars_decode($arrParams['organization_name']);
			$arrRequestParams['lead_organization']['organization_number'] = $arrParams['organization_number'];
			$arrRequestParams['lead_organization']['organization_email'] = $arrParams['organization_email'];
			
			//add meta details here
			//$arrRequestParams['meta']['enquiry'] = 'yes';
			// $arrRequestParams['meta']['ticket_status'] = 'open';
			$arrRequestParams['meta']['region'] = isset($arrParams['region']) ? $arrParams['region'] : '';
			//Changes related to the Meta fields
			if(!empty($custom_meta_arr)){
				foreach ($custom_meta_arr as $meta_arr) {
					if( isset( $arrParams[$meta_arr] ) && $meta_arr == 'third_party' ){
						$arrRequestParams['meta'][$meta_arr]['google_adwords']['GCLID'] = $arrParams[$meta_arr];
					}
					else if( isset( $arrParams[$meta_arr] ) ){
						$arrRequestParams['meta'][$meta_arr] = $arrParams[$meta_arr];
					}
					
				}
			}
			

			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
	
		##### CALL AURASTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->LEADEP,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
		);
		
		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);

		##### CALL AURASTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'success';
			$arrReturn['lead_id'] = $response['lead_id'];
			$arrReturn['message'] = $response['message'];
		}else{
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'error';
			$arrReturn['error'] = $response['body'];
		}
		
		return $arrReturn;
	}
	
	/*
	* This function is used to call AURASTRIDE API
	*/
	public function cf7au_call_aurastride_api($arrParams=array()){
		
		$enableAPI = $this->arrAPICredentials['ENABLEAPI'];
		
		if(empty($enableAPI)){
			
			$response = array();
			$response['response'] = 'Please enable aurastride API on the website';
			$response['code'] = '403';
			return $response;
		}
		
		
		
		$response = '';
		$apiEndpoint        = !empty($arrParams['apiEndpoint']) ? $arrParams['apiEndpoint'] : "";
		$apiMethod          = !empty($arrParams['apiMethod']) ? $arrParams['apiMethod'] : "";
		$convertToArray     = !empty($arrParams['convertToArray']) ? true : false;
		$arrRequestParams   = !empty($arrParams['arrRequestParams']) ? $arrParams['arrRequestParams'] : array();
		$arrRequestHeaders  = !empty($arrParams['arrRequestHeaders']) ? $arrParams['arrRequestHeaders'] : array();
		$debugLog  			= isset($arrParams['debugLog']) && !empty($arrParams['debugLog']) ? $arrParams['debugLog'] : '';
		$strAuraStrideApiURL = $this->arrAPICredentials['BASEP'];
		$apiURL = $strAuraStrideApiURL . $apiEndpoint;
		
		$arrLogContent = array();
		$arrLogContent[] = "############################################################ - " . date("Y-m-d H:i:s");
		$arrLogContent[] = "";
		$arrLogContent[] = "API URL: " . $apiURL;
		
		$arrArgs = array();
		$arrArgs['timeout'] = 100;
		
		if(!empty($arrRequestHeaders)){
			$arrArgs['headers'] = $arrRequestHeaders;
		}
		
		if($debugLog){
			
			if(isset($arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME]) && !empty($arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME])){
				$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = substr_replace($arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME],"**************************",10,40);
			}
			
			$arrLogContent[] = "";
			$arrLogContent[] = "API REQUEST HEADER: " . "\n" . implode("\n", $arrRequestHeaders);	
		}
		
		if(!empty($arrRequestParams) && $apiMethod == 'POST'){
			
			$arrArgs['body'] = $arrRequestParams;
			
			if($this->TOKENEP == $apiEndpoint){
				if($debugLog){
					
					$arrRequestParams = json_decode($arrRequestParams,true);
					
					if(is_array($arrRequestParams) && isset($arrRequestParams['refresh_token']) && !empty($arrRequestParams['refresh_token'])){
						$arrRequestParams['refresh_token'] = substr_replace($arrRequestParams['refresh_token'],"**************************",10,20);
						$arrRequestParams['client_id'] = substr_replace($arrRequestParams['client_id'],"**************************",10,20);
						$arrRequestParams['client_secret'] = substr_replace($arrRequestParams['client_secret'],"**************************",10,20);
						
						$arrRequestParams = wp_json_encode($arrRequestParams);
					}
					
					if(is_array($arrRequestParams) && isset($arrRequestParams['response_type']) && !empty($arrRequestParams['response_type']) && $arrRequestParams['response_type'] == 'grant_token' ){
						//$arrRequestParams['refresh_token'] = substr_replace($arrRequestParams['refresh_token'],"**************************",10,20);
						$arrRequestParams['client_id'] = substr_replace($arrRequestParams['client_id'],"**************************",10,20);
						$arrRequestParams['client_secret'] = substr_replace($arrRequestParams['client_secret'],"**************************",10,20);
						$arrRequestParams['code'] = substr_replace($arrRequestParams['code'],"**************************",10,20);
						
						$arrRequestParams = wp_json_encode($arrRequestParams);
					}
					
					if(is_array($arrRequestParams)){
						$arrLogContent[] = "";
						$arrLogContent[] = "API REQUEST PARAMS Hidden If: " . "\n" . wp_json_encode($arrRequestParams);	
					}
					else{
						$arrLogContent[] = "";
						$arrLogContent[] = "API REQUEST PARAMS Hidden else: " . "\n" . $arrRequestParams;
					}
				}
			}
			else{
				
				if(!empty($arrRequestParams) && !is_array($arrRequestParams)){
					$arrRequestParamsDecode = json_decode($arrRequestParams,true);
					if(!empty($arrRequestParamsDecode) && isset($arrRequestParamsDecode['access_token'])){
						$arrRequestParamsDecode['access_token'] = substr_replace($arrRequestParamsDecode['access_token'],"**************************",10,20);
					}  
				}
				
				$arrLogContent[] = "";
				$arrLogContent[] = "API REQUEST PARAMS Else: " . "\n" . wp_json_encode($arrRequestParamsDecode);
				
			}
		}
		if(!empty($arrRequestParams) && $apiMethod == 'GET'){
			
			$strRequestParams = urldecode(http_build_query($arrRequestParams));
			$apiURL .= "?" . $strRequestParams;
			//unset($arrRequestParams);
			//$strRequestParams = urldecode(http_build_query($arrRequestParams));
			$arrLogContent[] = "";
			$arrLogContent[] = "API REQUEST PARAMS: " . "\n" . $strRequestParams;
		}
		
		$arrLogContent[] = "";
		$arrLogContent[] = "API REQUEST Method: " . "\n" . $apiMethod;
		
		if ( $this->AURASTRIDE_API_LOG_FLAG ) {
			$arrLogContent[] = "";
			$arrLogContent[] = "";
			$strLogContent = implode("\n", $arrLogContent);
			$this->cf7au_logFileUpdate($strLogContent);
		}
		
		$arrLogContent = array();
		
		if($apiMethod == 'GET'){	
			$arrResponse = wp_remote_get( esc_url($apiURL), $arrArgs );
		}
		if($apiMethod == 'POST'){
			$arrResponse = wp_remote_post( esc_url($apiURL), $arrArgs );
		}
		$http_code = '';
		
		if(!empty($arrResponse) && is_array($arrResponse)){
			$http_code = $arrResponse['response']['code'];
		}
		
		if($http_code == 200 && is_array($arrResponse)){
			$response1 = $arrResponse['body'];
		}else{
			$response1 = wp_json_encode($arrResponse);
		}
		
		$arrLogContent[] = "";
		$arrLogContent[] = "API RESPONSE First: " . "\n";
		if($this->TOKENEP == $apiEndpoint && $debugLog && !is_array($response1)){
					
			$arrAPIRes = json_decode($response1,true);
			
			if(is_array($arrAPIRes) && isset($arrAPIRes['refresh_token']) && !empty($arrAPIRes['refresh_token'])){
				
				$arrAPIRes['refresh_token'] = substr_replace($arrAPIRes['refresh_token'],"**************************",10,20);
				$arrAPIRes['access_token'] = substr_replace($arrAPIRes['access_token'],"**************************",10,20);
				$arrAPIRes = wp_json_encode($arrAPIRes);
				$arrLogContent[] = "";
				$arrLogContent[] = "API REQUEST PARAMS Hidden: " . "\n" . $arrAPIRes;
			}
		}
		else{
			$arrLogContent[] = "";
			$arrLogContent[] = "API RESPONSE Later: " . "\n" . $response1;
		}	
		
		
		if ( $this->AURASTRIDE_API_LOG_FLAG ) {
			$arrLogContent[] = "";
			$arrLogContent[] = "";
			$strLogContent = implode("\n", $arrLogContent);
			$this->cf7au_logFileUpdate($strLogContent);
		}
		
		##### CONVERT RESPONSE TO ARRAY - START ######
		if ( $convertToArray ) {
			$response = array();
			$response['response'] = json_decode($response1, true);
			$response['code'] = $http_code;
		}else{
			$response = $response1;
		}
		##### CONVERT RESPONSE TO ARRAY - END ######
		
		return $response;
	}

	/**
	 * Get the form List from aurastride
	 */
	public function cf7au_get_aurastride_forms_list(){
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		$current_date = strtotime(date('H:i:s'));
		$access_token = $this->apiAccessToken;
		/*if(!isset($this->apiExpiryTime) || empty($this->apiExpiryTime) || $current_date >= $this->apiExpiryTime) {
			$res = $this->cf7au_get_access_token();
			$access_token = $res['access_token'];
			if(!$access_token){
				$res = $this->cf7au_get_refresh_token();
				$access_token = $res['access_token'];
			}
		}*/
		$res = $this->cf7au_get_refresh_token();
		$access_token = $res['access_token'];
		
		if(!empty($access_token)){
			$arrRequestParams['action'] = "FORM.READ";
			$arrRequestParams['access_token'] = $access_token;
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
		##### CALL MOUNTSTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->LEAD_FORM_READ,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
		);

		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);
		
		##### CALL MOUNTSTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			
			$forms = isset($response['data']) && !empty($response['data']) ? $response['data'] : array();
			if(!empty($forms)){
				$arrReturn['status'] = 'success';
				$arrReturn['message'] = $forms;	
			}else{
				$error = isset($response['message']) && !empty($response['message']) ? trim($response['message']) : "Something went wrong, Please contact administrator";

				$arrReturn['status'] = 'error';
				$arrReturn['error'] = $error;
				if(strstr($error, "Invalid Access Token.")){
					$this->cf7au_get_refresh_token();
				}
			}
		}else{
			$response = $apiResponse['response'];
			
			$error = isset($response['message']) && !empty($response['message']) ? trim($response['message']) : "Something went wrong, Please contact administrator";
			$arrReturn['status'] = 'error';
			$arrReturn['error'] = $error;
			if(strstr($error, "Invalid Access Token.")){
				$this->cf7au_get_refresh_token();
			}
		}
		
		return $arrReturn;
	}
	
	/*
	* Get the form custom fields from aurastride
	*/
	public function cf7au_get_form_custom_field($arrParams=array()){
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		$current_date = strtotime(date('H:i:s'));
		$access_token = $this->apiAccessToken;
		/*if(!isset($this->apiExpiryTime) || empty($this->apiExpiryTime) || $current_date >= $this->apiExpiryTime) {
			$res = $this->cf7au_get_access_token();
			$access_token = $res['access_token'];
			if(!$access_token){
				$res = $this->cf7au_get_refresh_token();
				$access_token = $res['access_token'];
			}
		}*/
		$res = $this->cf7au_get_refresh_token();
		$access_token = $res['access_token'];
		if(!empty($arrParams) && !empty($access_token)){
			$arrRequestParams['action'] = "CUSTOMFIELD.READ";
			$arrRequestParams['access_token'] = $access_token;
			$arrRequestParams['sanitize'] = array("form_code"=>$arrParams['form_code']);
			$arrRequestParams['filter'] = array("search_by"=>"ALL");
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
		##### CALL MOUNTSTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->LEAD_CUSTOMFIELD_READ,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
		);

		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);
		
		##### CALL MOUNTSTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			
			$fields = isset($response['data']) && !empty($response['data']) ? $response['data'] : array();
			if(!empty($fields)){
				$arrReturn['status'] = 'success';
				$arrReturn['message'] = $fields;	
			}else{
				$error = isset($response['message']) && !empty($response['message']) ? trim($response['message']) : "Something went wrong, Please contact administrator";

				$arrReturn['status'] = 'error';
				$arrReturn['error'] = $error;
				if(strstr($error, "Invalid Access Token.")){
					$this->cf7au_get_refresh_token();
				}
			}
		}else{
			$response = $apiResponse['response'];
			
			$error = isset($response['message']) && !empty($response['message']) ? trim($response['message']) : "Something went wrong, Please contact administrator";
			$arrReturn['status'] = 'error';
			$arrReturn['error'] = $error;
			if(strstr($error, "Invalid Access Token.")){
				$this->cf7au_get_refresh_token();
			}
		}
		
		return $arrReturn;
	}

	/*
	* This function is used to create lead to API
	*/
	public function cf7au_submit_from_data_api($arrParams=array()){
		
		$arrReturn = array();
		$arrRequestParams = array();
		$arrRequestHeaders = array();
		$current_date = strtotime(date('H:i:s'));
		$access_token = $this->apiAccessToken;
		/*if(!isset($this->apiExpiryTime) || empty($this->apiExpiryTime) || $current_date >= $this->apiExpiryTime) {
			$res = $this->cf7au_get_access_token();
			$access_token = $res['access_token'];
			if(!$access_token){
				$res = $this->cf7au_get_refresh_token();
				$access_token = $res['access_token'];
			}
		}*/
		$res = $this->cf7au_get_refresh_token();
		$access_token = $res['access_token'];
		if(!empty($arrParams) && !empty($access_token)){
			$arrRequestParams['action'] = $arrParams['action'];
			$arrRequestParams['access_token'] = $access_token;
			$arrRequestParams['create']['meta'] = $arrParams['form_data'];
			$arrRequestHeaders['Content-Type'] = 'application/json';
			$arrRequestHeaders[CF7AU_AS_API_AUTH_KEY_NAME] = $this->arrAPICredentials['AUTHKEY'];
		}
		##### CALL AURASTRIDE API - START #####
		$arrApiParams = array(
			'apiMethod'         => 'POST',
			'convertToArray'    => true,
			'apiEndpoint'       => $this->LEADEPCREATE,
			'arrRequestParams'  => wp_json_encode($arrRequestParams),
			'arrRequestHeaders' => $arrRequestHeaders,
		);

		$apiResponse = $this->cf7au_call_aurastride_api($arrApiParams);
		
		##### CALL AURASTRIDE API - END #####
		if($apiResponse['code'] == 200){
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'success';
			$arrReturn['lead_id'] = $response['content_id'];
			$arrReturn['message'] = $response['message'];
		}else{
			$response = $apiResponse['response'];
			$arrReturn['status'] = 'error';
			$arrReturn['error'] = $response['body'];
		}
		
		return $arrReturn;
	}
	
	/*
	* This function is used to write a log into a text file.
	*/
	public function cf7au_logFileUpdate( $strLogContent = "" ) {
		if ( !empty($strLogContent) ) {
			fwrite($this->filePointer, $strLogContent);
		}
	}
}

<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://https://profiles.wordpress.org/vsourz1td/
 * @since      1.0.0
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/public
 * @author     Vsourz Digital <wp.support@vsourz.com>
 */
class Cf7au_aurastride_crm_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	//define API related variable from here
	//define class variable from here
	var $cf7au_api_enable;
	var $cf7au_api_url;
	var $cf7au_authorization_key;
	var $cf7au_token_key;
	var $cf7au_log_enable;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		//get all option fields value and assign in class
		$this->cf7au_api_enable = get_option('cf7au_api_enable');
	
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function cf7au_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_aurastride_crm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_aurastride_crm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cf7_aurastride_crm-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function cf7au_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cf7_aurastride_crm_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cf7_aurastride_crm_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cf7_aurastride_crm-public.js', array( 'jquery' ), $this->version, false );

	}
	
	//sending enquiry direct to Aurastride CRM when direct settings enabled and advance Cf7 DB plugin activated
	public function cf7au_to_aurastride_enquiry_send_direct($contact_form){
		
		//define current contact form Id in variable from here
		$cf7 = $contact_form->WPCF7_ContactForm;
		$fId = $cf7->id();
		
		$arr_data = $contact_form->posted_data;
		
		if(!empty($arr_data)){
			
			$arrResponse = $this->cf7au_to_aurastride_enquiry_send($fId,$arr_data);
			if(!empty($arrResponse)){
				
				$contact_form->posted_data['aurastride_api_status'] = isset($arrResponse['aurastride_api_status']) && !empty($arrResponse['aurastride_api_status']) ? $arrResponse['aurastride_api_status'] : 'no';
				if($arrResponse['aurastride_api_status'] == 'yes'){
					do_action('cf7au_to_aurastride_after_send_api_data_success',$arrResponse,$arrResponse['enquiryId'],$arr_data,$fId);
				}
				$contact_form->posted_data['enquiryId'] = isset($arrResponse['enquiryId']) && !empty($arrResponse['enquiryId']) ? $arrResponse['enquiryId'] : '';
			}
		}
		
		return $contact_form;
		
	}
	
	/**
	 * Send the Contact from 7 submitted data to CRM before sending the email without Advance Cf7 DB
	 *
	 */
	public function cf7au_to_aurastride_enquiry_before_send_email($contact_form){
		
		//define current contact form Id in variable from here
		$fId = $contact_form->id();
		
		// we have to retrieve it from an API
		if(class_exists('WPCF7_Submission')){
			// we have to retrieve it from an API
			$submission = WPCF7_Submission::get_instance();
			$arr_data = $submission->get_posted_data();
			$uploaded_files = $submission->uploaded_files();
			
			if(!empty($arr_data)){
				
				//sending data to aurstride form here
				$arrResponse = $this->cf7au_to_aurastride_enquiry_send($fId,$arr_data,$uploaded_files);
			}
		}
		
		return $contact_form;
	}
	
	
	//sending data to aurastride here
	public function cf7au_to_aurastride_enquiry_send($fId='',$arr_data=array(), $uploaded_files=array() ){
		
		//Other - Rest Of The World
		//$region = 'other_-_rest_of_the_world';
		$region = '';
		
		$arrReturn = array();
		$arrReturn['enquiryId'] = '';
		$arrReturn['aurastride_api_status'] = 'no';
		
		//restrict any contact form entries, which from data not submitted in API
		$arr_exclude_form_ids = (array) apply_filters('cf7au_to_aurastride_exclude_form_id',array());
		
		//send contact form data to aurastride CRM from here
		if(!empty($fId) && !in_array($fId,$arr_exclude_form_ids)){
			
			//get API related all settings from here
			// Live API Details
			$cf7au_api_enable = $this->cf7au_api_enable;
			
			//check all settings configure properly or not else return from here
			if(!empty($cf7au_api_enable)){
				
				//Get form related mapping fields settings from here
				$arr_fields = get_option('cf7au_api_fields_mapping');
				
				//define filter for modify data before mapping
				//this hook use for the data encryption or decryption
				$arr_data = (array) apply_filters('cf7au_to_aurastride_before_mapping_api_data_direct',$arr_data,$fId);
				
				//check form related settings available or not
				if(!empty($arr_data) && !empty($arr_fields) && isset($arr_fields[$fId]) && !empty($arr_fields[$fId])){
					
					//get common and master fields value from here
					$commonFields = isset($arr_fields[$fId]['common_fields']) && !empty($arr_fields[$fId]['common_fields']) ? $arr_fields[$fId]['common_fields'] : array();
					
					//check current form data pass with API or not
					if(!empty($arr_fields) && isset($arr_fields[$fId]['enable_form']) && !empty($arr_fields[$fId]['enable_form'])){
						
						//customize common data details from here
						$commonFields = (array) apply_filters('cf7au_to_aurastride_before_add_common_data',$commonFields,$fId);

						$arr_api_data = array();
						$arr_dom_fields = array('lead_note');
						$arr_dom_fields = (array) apply_filters('cf7au_to_aurastride_dom_field_list',$arr_dom_fields,$fId);
						
						$arr_predefined_fields = array('vsz_current_date_mapping');
						//$arr_predefined_fields = (array) apply_filters('cf7au_to_aurastride_dom_field_list',$arr_predefined_fields,$fId);
						
						
						//combine common fields data in final array
						if(!empty($commonFields)){
							
							$groupSeparator = (string) apply_filters('cf7au_to_aurastride_group_field_separator',PHP_EOL);
							foreach($commonFields as $apiKey => $cfKey){
								
								//getting DOM fields details and mapping here
								if(!empty($arr_dom_fields) && in_array($apiKey,$arr_dom_fields)&& is_array($cfKey) && !empty($cfKey)){
									
									$noteVal = '';
									foreach($cfKey as $innKey => $noteCfKey){
										
										if(!empty($arr_predefined_fields) && in_array($noteCfKey,$arr_predefined_fields)){
											
											if($noteCfKey == 'vsz_current_date_mapping'){
												$noteVal .= 'Submission Date: '.date('Y-m-d').$groupSeparator;
											}
										}
										else{
											if(!is_array($arr_data[$noteCfKey])){
												$noteVal .= isset($arr_data[$noteCfKey]) && !empty($arr_data[$noteCfKey]) ? $arr_data[$noteCfKey].$groupSeparator : '';		
											}else{
												$noteVal .= isset($arr_data[$noteCfKey]) && !empty($arr_data[$noteCfKey]) ? implode(",",$arr_data[$noteCfKey]).$groupSeparator : '';	
											}
										}
									}
									if($region){
										$noteVal .= $region.$groupSeparator;
									}
									$arr_api_data[$apiKey] = $noteVal;
								}
								//adding predefined fields details here
								else if(!empty($arr_predefined_fields) && in_array($cfKey,$arr_predefined_fields)){
									if($cfKey == 'vsz_current_date_mapping'){
										$arr_api_data[$apiKey] =  date('Y-m-d');
									}
								}
								else if(isset($arr_data[$cfKey])) {
									
									if(!is_array($arr_data[$cfKey])){
										$arr_api_data[$apiKey] = isset($arr_data[$cfKey]) && !empty($arr_data[$cfKey]) ? htmlentities(sanitize_text_field($arr_data[$cfKey])) : '';
									}else{
										$arr_api_data[$apiKey] = isset($arr_data[$cfKey]) && !empty($arr_data[$cfKey]) ? htmlentities(sanitize_text_field(implode(",",($arr_data[$cfKey])))) : '';
										$has_pipe_values = $this->cf7au_check_cf7_field_has_pipe($fId, $cfKey, $arr_data[$cfKey]);
														
										if( !empty( $has_pipe_values ) ){
											$arr_api_data[$apiKey] = $has_pipe_values;	
										}else{
											$arr_api_data[$apiKey] = isset($arr_data[$cfKey]) && !empty($arr_data[$cfKey]) ? htmlentities(sanitize_text_field($arr_data[$cfKey])) : '';
										}
									}
								}
							}
						}
						
						
						if(!empty($region)){
							$arr_api_data['region'] = $region;
						}
						
						//define filter for modify data before send to API
						$arr_api_data = (array) apply_filters('cf7au_to_aurastride_before_send_api_data_direct',$arr_api_data,$fId);
						
						//sending data to Aurastride from here
						if(!empty($arr_api_data) && class_exists('CF7AU_AURASTRIDE_API') ){

							//add token in API request from here
							$arr_api_data['action'] = 'LEAD.CREATE';
								
							$obj = new CF7AU_AURASTRIDE_API();
							//send data to API from here
							$response_data = $obj->cf7au_create_lead($arr_api_data);
							
							//checking lead submission successfully or not
							$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
							//check entry successfully submit or not
							if(!empty($enquiryId)){
								
								//pass API status with contact form here
								$arrReturn['enquiryId'] = $enquiryId;
								$arrReturn['aurastride_api_status'] = 'yes';
								
								//define action here so user can add additional action after API submission
								do_action('cf7au_to_aurastride_after_send_api_data_direct_success',$response_data,$arr_api_data,$fId);
							}
							else{
								//define action here so user can add additional action after API submission
								do_action('cf7au_to_aurastride_after_send_api_data_direct_error',$response_data,$arr_api_data,$fId);
							}
								
						}//close if for API class exist
						
					}//close if 

					
					//check for form submission data
					//check current form data pass with Form API or not
					if(!empty($arr_fields) && isset($arr_fields[$fId]['enable_af_form']) && !empty($arr_fields[$fId]['enable_af_form']) ){
						//get master fields value from here
						$auraformFields = isset($arr_fields[$fId]['aura_form_fields']) && !empty($arr_fields[$fId]['aura_form_fields']) ? $arr_fields[$fId]['aura_form_fields'] : array();

						
						//Aura form ID necessay to submit the data to particular form
						$auraFormID = isset($arr_fields[$fId]['af_form']) && !empty($arr_fields[$fId]['af_form']) ? $arr_fields[$fId]['af_form'] : "";
						
						if(!empty($auraformFields) && !empty( $auraFormID )  ){
								
							
							$groupSeparator = (string) apply_filters('cf7au_to_aurastride_group_field_separator',PHP_EOL);
							
							//getting each entry here for submit
							//foreach($arr_data as $entryId => $arrInfo){
								
								$arr_api_data = array();
								$arr_form_data = array();
								
								//check entry data exist or not
								if(!empty($arr_data) && is_array($arr_data)){
									
									//getting mapping fields details here
									foreach($auraformFields as $apiKey => $cf7key){
										//var_dump($arr_data[$cf7key]);
										//Check for the API Fields has Array structure to be maintained
										if( !empty( $cf7key ) && is_array( $cf7key )){

											//Looping through the API fields
											foreach ($cf7key as $api_Key => $cf7_Key) {

												if(isset($arr_data[$cf7_Key])  ){
													//Check if the field type is image or not else pass defualt
													$field_type = $this->cf7au_get_cf7_field_type($fId, $cf7_Key);
													
													if( $field_type !== false && $field_type == 'file') {

														if( !empty($arr_data[$cf7_Key]) ){
															$image_data = $this->cf7au_image_to_base64( $arr_data[$cf7_Key] );
															//print_r($image_data);
															if( $image_data && !empty( $image_data ) ){
																
																$arr_form_data[$apiKey][$api_Key] = array('media_ids'=>array(), 'attachments' => array( $image_data ) );
															}else{
																$arr_form_data[$apiKey][$api_Key] = array();
															}
															
														}else{
															$arr_form_data[$apiKey][$api_Key] = array();
														}
														
													}elseif( $field_type !== false && $field_type == 'date' ){
														//Converting date format from CF7 to AURA
														$submitted_date = isset($arr_data[$cf7_Key]) && !empty($arr_data[$cf7_Key]) ? $arr_data[$cf7_Key] : '';
														$date = '';
														if( !empty( $submitted_date ) ){
															//Convert the date from 'dd-mm-yyyy' to 'yyyy-mm-dd'
															$date = date('Y-m-d', strtotime( $submitted_date ));
														}
														$arr_form_data[$apiKey][$api_Key] = $date;

													}else{

														if(is_array($arr_data[$cf7_Key])){
															//Check if the Field has Pipe values
															$has_pipe_values = $this->cf7au_check_cf7_field_has_pipe($fId, $cf7key, $arr_data[$cf7_Key]);
														
															if( !empty( $has_pipe_values ) ){
																$arr_form_data[$apiKey][$api_Key] = $has_pipe_values;	
															}else{
																$arr_form_data[$apiKey][$api_Key] = isset($arr_data[$cf7_Key]) && !empty($arr_data[$cf7_Key]) ? $arr_data[$cf7_Key] : '';	
															}
																
														}
														else{
															$arr_form_data[$apiKey][$api_Key] = isset($arr_data[$cf7_Key]) && !empty($arr_data[$cf7_Key]) ? $arr_data[$cf7_Key] : '';	
														}
													}
													
												}
												
											}
												

										}else{
											if(isset($arr_data[$cf7key])  ){
												//Check if the field type is image or not else pass defualt
												$field_type = $this->cf7au_get_cf7_field_type($fId, $cf7key);
												
												//var_dump($field_type);
												if( $field_type !== false && $field_type == 'file') {

													if( !empty($arr_data[$cf7key]) ){
														$image_data = $this->cf7au_image_to_base64( $arr_data[$cf7key] );
														//print_r($image_data);
														if( $image_data && !empty( $image_data ) ){
															
															$arr_form_data[$apiKey] = array('media_ids'=>array(), 'attachments' => array( $image_data ) );
														}else{
															$arr_form_data[$apiKey] = array();
														}
														
													}else{
														$arr_form_data[$apiKey] = array();
													}
													
												}elseif( $field_type !== false && $field_type == 'date' ){
														//Converting date format from CF7 to AURA
														$submitted_date = isset($arr_data[$cf7key]) && !empty($arr_data[$cf7key]) ? $arr_data[$cf7key] : '';
														$date = '';
														if( !empty( $submitted_date ) ){
															//Convert the date from 'dd-mm-yyyy' to 'yyyy-mm-dd'
															$date = date('Y-m-d', strtotime( $submitted_date ));
														}
														$arr_form_data[$apiKey] = $date;

												}else{
												
													if(is_array($arr_data[$cf7key])){
														$has_pipe_values = $this->cf7au_check_cf7_field_has_pipe($fId, $cf7key, $arr_data[$cf7key]);
														
														if( !empty( $has_pipe_values ) ){
															$arr_form_data[$apiKey] = $has_pipe_values;	
														}else{
															$arr_form_data[$apiKey] = isset($arr_data[$cf7key]) && !empty($arr_data[$cf7key]) ? $arr_data[$cf7key] : '';	
														}
													}
													else{
														$arr_form_data[$apiKey] = isset($arr_data[$cf7key]) && !empty($arr_data[$cf7key]) ? $arr_data[$cf7key] : '';	
													}
												}
												
											}
										}
										
									}//close foreach common fields
									
									//sending data to Aurastride from here
									if(!empty($arr_form_data) && class_exists('CF7AU_AURASTRIDE_API') ){

										//add token in API request from here
										$arr_api_data['action'] = $auraFormID.'.CREATE';
										$arr_api_data['form_data'] = $arr_form_data;
											
										$obj = new CF7AU_AURASTRIDE_API();
										//send data to API from here
										$response_data = $obj->cf7au_submit_from_data_api($arr_api_data);
										
										//checking lead submission successfully or not
										$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
										//check entry successfully submit or not
										if(!empty($enquiryId)){
											
											//pass API status with contact form here
											$arrReturn['enquiryId'] = $enquiryId;
											$arrReturn['aurastride_api_status'] = 'yes';
											if($arrReturn['aurastride_api_status'] == 'yes'){		
												do_action('cf7au_to_aurastride_after_send_api_data_success',$response_data,$entryId,$arr_api_data,$fId);
											}
										}
											
									}//close if for API class exist
									
								}//close if for array info
								
							//}//close foreach for fetching data details
						
						}//close if for master fields

					}
				}
			}
		}//close if fo excluding form ids 
		
		return $arrReturn;
	}
	
	
	//pass additional hidden fields within the Advance contact form 7 DB
	public function cf7au_to_aurastride_setup_hidden_fields($contact_form){
		
		$cf7 = $contact_form->WPCF7_ContactForm;
		$fId = $cf7->id();
		
		//restrict any contact form entries, which from data not submitted in API
		$arr_exclude_form_ids = (array) apply_filters('cf7au_to_aurastride_exclude_form_id',array());

		if(!in_array($fId,$arr_exclude_form_ids)){
			
			//define predefined variable here
			$sendData = '';
			$enquiryId = '';
			$aurastrideAPIStatus = 'no';
			$contact_form->posted_data['aurastride_api_status'] = $aurastrideAPIStatus;
			//$contact_form->posted_data['SendData'] = $sendData;
			$contact_form->posted_data['enquiryId'] = $enquiryId;
			
		}
		
		
		return $contact_form;
		
	}

	/**
	 * Get field type by form ID and field name in Contact Form 7
	 *
	 * @param int    $form_id    Contact Form 7 form ID.
	 * @param string $field_name Field name.
	 *
	 * @return string|false Field type or false if not found.
	 */
	public function cf7au_get_cf7_field_type($form_id, $field_name) {
		// Load the Contact Form 7 form definition
		
		$ContactForm = WPCF7_ContactForm::get_instance($form_id);
		$form_fields = $ContactForm->scan_form_tags();

		// Iterate through form fields to find the specified field name
		foreach ($form_fields as $field) {
			if ($field['name'] === $field_name) {
				$field_type = $field['basetype'];
				return $field_type;
				// Now $field_type contains the type of the field
				//echo "Field Type for '$field_name' is: $field_type";
				//break;
			}
		}

		return false;
	}
	
	
	public function cf7au_check_cf7_field_has_pipe( $form_id, $field_name, $submitted_values ){
		$ContactForm = WPCF7_ContactForm::get_instance($form_id);
		$form_fields = $ContactForm->scan_form_tags();
		$piped_data = array();
		// Iterate through form fields to find the specified field name
		foreach ($form_fields as $field) {
			
			if ($field['name'] === $field_name) {
				
				$pipe_array = $field->pipes->to_array();
				
				if( !empty( $pipe_array ) && is_array( $pipe_array  ) ){
					
					foreach ($submitted_values as $index => $value) {
						
						// Check if the submitted value is in the pipe values array
						foreach ($pipe_array as $pipevalue) {
							
							// Replace the submitted value with the corresponding pipe value
							if( $value === $pipevalue[1]  ){								
								$piped_data[$index] = $pipevalue[0];
							}							
						}
					}
					if( !empty( $piped_data ) ){
						return $piped_data;
					}
				}
				
				
			}
		}
		
		return $submitted_values;
	}

	/**
	 * Get file name from URL or file path
	 *
	 * @param string $url_or_path URL or file path.
	 *
	 * @return string|false File name or false if not found.
	 */
	public function cf7au_get_filename_from_url_or_path($url_or_path) {
		$url_parts = parse_url($url_or_path);
		//var_dump($url_parts);
		if ($url_parts && isset($url_parts['path'])) {
			$path_parts = pathinfo($url_parts['path']);
			if (isset($path_parts['basename'])) {
				return $path_parts['basename'];
			}
		}

		return false;
	}

	/**
	 * Convert image to base64
	 *
	 * @param string $url_or_path URL or file path.
	 *
	 * @return string|false Base64-encoded image or false if not found.
	 */
	public function cf7au_image_to_base64($url_or_path) {
		if( !empty( $url_or_path ) ){
			$file_name = $this->cf7au_get_filename_from_url_or_path($url_or_path);

			if ($file_name) {
				//$file_path = ABSPATH .'uploads/advanced-cf7-upload/'. $file_name; // Construct the absolute file path
				//var_dump(file_exists($url_or_path));
				//if (file_exists($url_or_path)) {
					$image_data = file_get_contents($url_or_path);
					$base64_image = base64_encode($image_data);
					$data["file_name"] = $file_name;
					$data["file_data"] = $base64_image;
					return $data;
				//}
			}
		}

		return false;
	}

}

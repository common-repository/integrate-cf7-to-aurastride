<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://profiles.wordpress.org/vsourz1td/
 * @since      1.0.0
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cf7_aurastride_crm
 * @subpackage Cf7_aurastride_crm/admin
 * @author     Vsourz Digital <wp.support@vsourz.com>
 */
class Cf7au_aurastride_crm_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_register_style( 'cf7-to-aurastride-admin-css', plugin_dir_url( __FILE__ ) . 'css/cf7_aurastride_crm-admin.css?var='.time(), array(), $this->version, 'all' );
		wp_enqueue_style( 'adcf7-aurastride-css', plugin_dir_url( __FILE__ ) . 'css/adcf7-db.css?var='.time(), false, $this->version, 'all' );
		wp_register_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', array(), $this->version, 'all' );
		wp_register_style( 'font-awesome.min', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', array(), $this->version, 'all' );
		wp_register_style( 'bootstrap-min-css', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cf7_aurastride_crm-admin.js', array( 'jquery' ), $this->version, false );
		
		wp_localize_script( $this->plugin_name, 'cf72aut_admin_action', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		));

	}
	
	/**
	 * Initialize the menu
	 */
	public function cf7au_api_settings_menu(){

		add_menu_page( "CF7 to aurastride", "CF7 to aurastride", 'manage_options', "cf7au-api-settings", array( $this, "cf7au_api_settings_menu_callback"), 'dashicons-visibility' , 999);

		add_submenu_page('cf7au-api-settings', 'Fields Mapping', 'Fields Mapping', 'manage_options', 'cf7au-mapping',array( $this, 'cf7au_mapping_mut_fields_callback') );

	}
	
	/*
	 * Callback function for the setting screen
	 * View of the setting screen
	 */
	public function cf7au_api_settings_menu_callback(){
		// include_once( CF72AUT_PLUGIN_PATH."admin/partials/settings.php" );
		include_once( CF7AU_PLUGIN_PATH."admin/partials/settings.php" );
	}

	/*
	 * Callback function for the CF7 2 Aurastride Fields Mapping screen
	 * View of the Fields Mapping screen
	 */

	public function cf7au_mapping_mut_fields_callback(){
		// include_once( CF72AUT_PLUGIN_PATH."admin/partials/mappings.php" );
		include_once( CF7AU_PLUGIN_PATH."admin/partials/mappings.php" );
	}

	/**
	 * Get all the contact form 7 list
	 * @returns : all the contact form 7 list
	 */
	public function cf7au_get_all_cf7_forms(){

		$cf7Forms = array();
		$forms = WPCF7_ContactForm::find();
		foreach ($forms as $k => $v){
			//Check if form id not empty then get specific form related information
			$cf7Forms[] = $v;
	    }

	    return $cf7Forms;
	}

	/**
	 * Get all the contact form 7 fields
	 * @params $form_id - Contact form 7 ID
	 */
	public function cf7au_get_cf7_forms_fields( $form_id = '' ){

		if( empty($form_id) ){
			return '';
		}

		//Get All form information
		$forms = WPCF7_ContactForm::find();
		//fetch each form information
		foreach ($forms as $k => $v){
			//Check if form id not empty then get specific form related information
			if($v->id() == $form_id){
				$cf7forms = $v;
				break;
			}
		}

		if(!empty($cf7forms) ){
			$arr_form_tags = $cf7forms->scan_form_tags();
			return $arr_form_tags;
		}
		else{
			return '';
		}
	}
	
	
	/**
	 * Template file included for form list to be viewed
	 */
	public function cf7au_form_fields_template_callback(){

		// include_once( CF72AUT_PLUGIN_PATH."admin/partials/cf7-form-fields-template.php" );
		include_once( CF7AU_PLUGIN_PATH."admin/partials/cf7-form-fields-template.php" );

	}
	
	//added Cron schedule here 
	public function cf7au_aurastride_crm_register_cron_for_api_callback(){
		
		//register cron job here
		if(!wp_next_scheduled( 'cf7au_aurastride_crm_api_enquiry_submission' ) ) {
			$current_dt = current_time('Y-m-d H:i:s');
			$current_dt = strtotime($current_dt);
			//define schedule for every 10 minutes
			wp_schedule_event( $current_dt, 'every_ten_minutes', 'cf7au_aurastride_crm_api_enquiry_submission' );
		}

		if(!wp_next_scheduled( 'cf7au_aurastride_crm_form_api_enquiry_submission' ) ) {
			$current_dt = current_time('Y-m-d H:i:s');
			$current_dt = strtotime($current_dt);
			//define schedule for every 10 minutes
			wp_schedule_event( $current_dt, 'every_ten_minutes', 'cf7au_aurastride_crm_form_api_enquiry_submission' );
		}

		
		
	}
	
	
	//added Cron interval here 
	public function cf7au_aurastride_crm_cron_schedules_callback($schedules){
		
		if(!isset($schedules["every_ten_minutes"])){
			$schedules["every_ten_minutes"] = array(
				'interval' => 600, //1 hours = 60*60, 1 day = 86400 second
				'display' => ('Every 10 Minutes')
			);
		}
		
		return $schedules;
		
	}
	
	//preparing enquiry data for send to CRM
	public function cf7au_aurastride_crm_api_enquiry_submission_cron_callback(){
		
		//delete old log files here
		$wordpress_upload_dir = wp_upload_dir();
		$txt_response_folder = $wordpress_upload_dir['basedir'].'/aurastride-api-logs/';
		$files_in_folder = glob($txt_response_folder."*.txt");

		if(is_array($files_in_folder) && !empty($files_in_folder)){
			$today_timestamp = current_time('timestamp');
			//$enddate_timestamp = strtotime("-1 Months");
			$enddate_timestamp = strtotime("-7 Days");

			$end_date=date_create(date('Y-m-d',$enddate_timestamp));
			foreach($files_in_folder as $file_name){
				$modified_date = filemtime($file_name);
				
				$date1=date_create(date('Y-m-d',$modified_date));
				$diff=date_diff($date1,$end_date);
				$diff_days = $diff->format("%R%a");

				if($diff_days > 0){
					unlink($file_name);
				}
			}
		}
		
		
		//setup aurastride enquiry submission here
		// include_once( CF72AUT_PLUGIN_PATH."admin/partials/cf7_aurastride_crm_api_enquiry_submission.php" );
		include_once( CF7AU_PLUGIN_PATH."admin/partials/cf7_aurastride_crm_api_enquiry_submission.php" );
	}

	//preparing enquiry data for send to CRM
	public function cf7au_aurastride_crm_form_api_enquiry_submission_cron_callback(){
		
		//delete old log files here
		$wordpress_upload_dir = wp_upload_dir();
		$txt_response_folder = $wordpress_upload_dir['basedir'].'/aurastride-api-logs/';
		$files_in_folder = glob($txt_response_folder."*.txt");

		if(is_array($files_in_folder) && !empty($files_in_folder)){
			$today_timestamp = current_time('timestamp');
			//$enddate_timestamp = strtotime("-1 Months");
			$enddate_timestamp = strtotime("-7 Days");

			$end_date=date_create(date('Y-m-d',$enddate_timestamp));
			foreach($files_in_folder as $file_name){
				$modified_date = filemtime($file_name);
				
				$date1=date_create(date('Y-m-d',$modified_date));
				$diff=date_diff($date1,$end_date);
				$diff_days = $diff->format("%R%a");

				if($diff_days > 0){
					unlink($file_name);
				}
			}
		}
		
		
		//setup aurastride enquiry submission here
		// include_once( CF72AUT_PLUGIN_PATH."admin/partials/cf7_aurastride_crm_form_api_enquiry_submission.php" );
		include_once( CF7AU_PLUGIN_PATH."admin/partials/cf7_aurastride_crm_form_api_enquiry_submission.php" );
	}
	
	/**
	 * Callback function to get the form fields data and map 
	 */
	public function cf7au_vsz_get_af_form_fields_data(){
		
		if( isset( $_POST["formVal"] ) && !empty( $_POST["formVal"] ) && wp_verify_nonce(wp_unslash( sanitize_text_field($_POST['nonce'])), 'cf7_mount_field_map') ) {
			
			$formVal =  wp_unslash(sanitize_text_field($_POST["formVal"]));
			$formId  =  wp_unslash(sanitize_text_field($_POST['formId']));
			//Get the form fields data

			//include the file to get the mapping fields data and send the 
			include_once( CF7AU_PLUGIN_PATH."admin/partials/aurastride_api_form_fields_mapping.php" );
			
		}else{
			?>
				<tr>
					<td colspan="2">
						<?php
							echo esc_html__( "You haven't selected any form, please select", 'integrate-cf7-to-aurastride' );
						?>
					</td>
				</tr>
		<?php
		}
		
		// Always exit to avoid further execution
		wp_die();
	}
	/**
	 * Callback function to get the form fields data and map 
	 */
	public function cf7au_vsz_get_af_form_saved_fields_data(){

		if( isset( $_POST["formId"] ) && !empty( $_POST["formId"] ) && wp_verify_nonce( wp_unslash( sanitize_text_field( $_POST['nonce'] ) ), 'cf7_mount_field_map')  ){

			
			$formId  =  wp_unslash(sanitize_text_field($_POST['formId']));
			//Get the form fields data

			//include the file to get the mapping fields data and send the 
			// include_once( CF72AUT_PLUGIN_PATH."admin/partials/aurastride_api_form_saved_fields_mapping.php" );
			include_once( CF7AU_PLUGIN_PATH."admin/partials/aurastride_api_form_saved_fields_mapping.php" );
			
		}else{
		?>
		<tr>
			<td colspan="2">
				<?php
					echo esc_html__( "No contact form ID passed", 'integrate-cf7-to-aurastride' );
				?>
			</td>
		</tr>
		<?php
		}

		// Always exit to avoid further execution
		wp_die();
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

	public function cf7au_aurastride_crm_admin_after_heading_field_callback(){
		?><th style="width: 115px;" class="manage-column">
    	<?php esc_html_e('Action to send aurastride Data','integrate-cf7-to-aurastride'); ?></th><?php
	}
	
	public function cf7au_aurastride_crm_admin_after_body_edit_field_func( $form_id, $row_id, $getDatanonce ){

		global $wpdb;
		$row_id = (int)$row_id;
		$form_id = (int)$form_id;

		$read_query = $wpdb->prepare("SELECT `value` FROM {$wpdb->prefix}cf7_vdata_entry  WHERE `name` = 'aurastride_api_status' AND `data_id` = %d  AND `cf7_id` = %d ",$row_id ,$form_id);
		$data = $wpdb->get_results($read_query, ARRAY_N);
		
		$api_status = $data[0][0];
		
		?>
		<td style="text-align:center">
			<?php
			if($api_status == 'no' || $api_status == ''){
			?>
			<a data-rid="<?php echo esc_attr($row_id); ?>" data-formid="<?php echo esc_attr($form_id); ?>"
				data-nonce="<?php echo esc_attr($getDatanonce);?>" href="javascript:void(0)" id="cf7-rb-edit-form"
				class="aura-send-data button button-primary" name="Send Data">
				<?php esc_html_e('Resend Data','integrate-cf7-to-aurastride'); ?>
			</a>
			<span class="vsz-loader-span"></span>
			<?php } ?>
		</td>

		<?php
	}
	
	
	function cf7au_aurastride_crm_admin_send_pending_aura_data(){

		if(isset($_POST['nonce']) && !empty($_POST['nonce'])){
			if(wp_verify_nonce( wp_unslash( sanitize_text_field( $_POST['nonce'] ) ), 'vsz-cf7-get-entry-nonce-'.wp_unslash(sanitize_text_field($_POST['formId'])) )){
				$result = array();
				$result['status'] = 'fail';
				$result['message'] = 'you have no permission to resend the data';
				echo wp_json_encode($result);
				exit;
			}
		}

        //Get all form names which entry store in DB
        global $wpdb;
        //Get table name for data entry
		//$data_entry_table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);
		$result = array();
        if(class_exists('CF7AU_AURASTRIDE_API')){
            $obj = new CF7AU_AURASTRIDE_API();
            $dataId = wp_unslash(sanitize_text_field($_POST['rowId']));
            $formId = wp_unslash(sanitize_text_field($_POST['formId']));
            $arr_fields = get_option('cf7au_api_fields_mapping');

            $sql = $wpdb->prepare("SELECT `cf7_id` FROM {$wpdb->prefix}cf7_vdata_entry GROUP BY `cf7_id`");
            $data = $wpdb->get_results($sql,ARRAY_N);
            if(!empty($data)){
                foreach($data as $arrVal){
                    $arr_form_id[] = (int)$arrVal[0];
                }
            }
			
            if(!empty($arr_form_id) && !empty($arr_fields) ){
                $cf7d_entry_order_by = (string) '`data_id` ASC';
                $cf7d_entry_order_by = sanitize_sql_orderby($cf7d_entry_order_by);
				
                foreach($arr_form_id as $key => $fId){
					if($fId == $formId) {
						
						if( isset($arr_fields[$fId]['enable_form']) && !empty($arr_fields[$fId]['enable_form']) && $arr_fields[$fId]['enable_form'] = 'yes' ) {

							//if($fId == $formId) {
								
								//preparing query here for get submitted data
								$query =  $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cf7_vdata_entry  WHERE `cf7_id` = %d AND `data_id`= %d",$formId, $dataId );
								$arrData = $wpdb->get_results($query);

								$arr_data_sorted = vsz_cf7_sortdata($arrData);

								//define filter for modify data before mapping
								//this hook use for the data encryption or decryption
								$arr_data_sorted = (array) apply_filters('cf7au_to_aurastride_before_mapping_api_data',$arr_data_sorted,$fId);
								
								if(!empty($arr_data_sorted) && !empty($arr_fields) && isset($arr_fields[$fId]) && !empty($arr_fields[$fId])){
									$commonFields = isset($arr_fields[$fId]['common_fields']) && !empty($arr_fields[$fId]['common_fields']) ? $arr_fields[$fId]['common_fields'] : array();

									//customize common data details from here
									$commonFields = (array) apply_filters('cf7au_to_aurastride_before_add_common_data',$commonFields,$fId);
									$arr_dom_fields = array('lead_note');
									$arr_dom_fields = (array) apply_filters('cf7au_to_aurastride_dom_field_list',$arr_dom_fields,$fId);

									$arr_predefined_fields = array('vsz_current_date_mapping');
									$groupSeparator = (string) apply_filters('cf7au_to_aurastride_group_field_separator',PHP_EOL);

									//getting each entry here for submit
									foreach($arr_data_sorted as $entryId => $arrInfo){

										$arr_api_data = array();

										//check entry data exist or not
										if(!empty($arrInfo) && is_array($arrInfo)){
											//getting mapping fields details here
											foreach($commonFields as $cKey => $cFieldName){
												$input = $arrInfo['submit_time']; 
												$date = strtotime($input); 
												$submission_date = date('Y-m-d', $date);
												//checking the field type is array or not
												if(is_array($cFieldName) && in_array($cKey,$arr_dom_fields)){

													//getting DOM field details here
													$noteVal = '';
													foreach($cFieldName as $inncIndex =>  $innCKeyName){

														//if value in array format then get data here 									
														if(array_key_exists($innCKeyName,$arrInfo) && is_array($arrInfo[$innCKeyName])){
															$noteVal .= isset($arrInfo[$innCKeyName]) && !empty($arrInfo[$innCKeyName]) ? $innCKeyName.' : '.implode(",",$arrInfo[$innCKeyName]).$groupSeparator : '';
														}
														else if(array_key_exists($innCKeyName,$arrInfo)){
															$noteVal .= isset($arrInfo[$innCKeyName]) && !empty($arrInfo[$innCKeyName]) ? $innCKeyName.' : '.$arrInfo[$innCKeyName].$groupSeparator : '';
														}
														else if(!empty($arr_predefined_fields) && in_array($innCKeyName,$arr_predefined_fields)){

															if($innCKeyName == 'vsz_current_date_mapping'){
																$noteVal .= 'Submission Date: '. $submission_date .$groupSeparator;
															}
														}
													}
													$arr_api_data[$cKey] = $noteVal;
												}
												//adding predefined fields details here
												else if(!empty($arr_predefined_fields) && in_array($cFieldName,$arr_predefined_fields)){

													if($cFieldName == 'vsz_current_date_mapping'){
														$arr_api_data[$cKey] =  $submission_date;
													}
												}
												//getting other fields value and mapping here
												else if(array_key_exists($cFieldName,$arrInfo)){


													if(is_array($arrInfo[$cFieldName])){
														$arr_api_data[$cKey] = isset($arrInfo[$cFieldName]) && !empty($arrInfo[$cFieldName]) ? implode(",",$arrInfo[$cFieldName]).$groupSeparator : '';	
													}
													else{
														$arr_api_data[$cKey] = isset($arrInfo[$cFieldName]) && !empty($arrInfo[$cFieldName]) ? $arrInfo[$cFieldName] : '';	
													}

												}

											}//close foreach common fields


											//added filter for modify data before CRM submission
											$arr_api_data = (array) apply_filters('cf7au_to_aurastride_before_send_api_data',$arr_api_data,$entryId,$arrInfo,$fId);
											
											//sending data to Aurastride from here
											if(!empty($arr_api_data) && class_exists('CF7AU_AURASTRIDE_API') ){

												//add token in API request from here
												$arr_api_data['action'] = 'LEAD.CREATE';
												
												
												//$obj = new CF7AU_AURASTRIDE_API();
											
												//send data to API from here
												$response_data = $obj->cf7au_create_lead($arr_api_data);
												
												$status = !empty($response_data) && isset($response_data['status']) && !empty($response_data['status']) ? sanitize_text_field($response_data['status']) : '';

												// Message
												$message = !empty($response_data) && isset($response_data['message']) && !empty($response_data['message']) ? sanitize_text_field($response_data['message']) : '';

												// error
												$error = !empty($response_data) && isset($response_data['error']) && !empty($response_data['error']) ? sanitize_text_field($response_data['error']) : '';

												//checking lead submission successfully or not
												$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
												
												if($status == 'success'){
												//check entry successfully submit or not
													if(!empty($enquiryId)){

														// check enquiryId field in data base
														$read_query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cf7_vdata_entry  WHERE `name` = 'enquiryId' AND `data_id` = %d",  $entryId);
														$data = $wpdb->query($read_query);

														if($data === 0 ){
															$create_query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}cf7_vdata_entry (`cf7_id`, `data_id`, `name`, `value`) VALUES ( %d , %d , 'enquiryId', '') ", $formId, $entryId );
															$data = $wpdb->query($create_query);
														}

														//update API status with contact form here

														//add enquiry id and enquiry status with particular form
														$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = %d WHERE `name` = 'enquiryId' AND `data_id` = %d", $enquiryId, $entryId );
														$wpdb->query($update_query);

														//define for save additional data flag

														$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = 'yes' WHERE `name` = 'aurastride_api_status' AND `data_id` = %d", $entryId);
														$wpdb->query($update_query);

														$result['status'] = 'success';
														$result['message'] = $message;

														
														//define action here so user can add additional action after API submission
														do_action('cf7au_to_aurastride_after_send_api_data_adcf7_success',$response_data,$entryId,$arr_api_data,$fId);

													} else {

														//define action here so user can add additional action after API submission
														do_action('cf7au_to_aurastride_after_send_api_data_adcf7_error',$response_data,$entryId,$arr_api_data,$fId);
											
														$result['status'] = 'fail';
														$result['message'] = $message;

													}//close if for API class exist
												} else {
													do_action('cf7au_to_aurastride_after_send_api_data_adcf7_error',$response_data,$entryId,$arr_api_data,$fId);
													$result = json_decode($error);
												}

											}//close if for array info
										}
									}
								}
							//}

						} else if( isset($arr_fields[$fId]['enable_af_form']) && !empty($arr_fields[$fId]['enable_af_form']) && $arr_fields[$fId]['enable_af_form'] = 'yes' ){
							
							//if($fId == $formId) {
								
								//preparing query here for get submitted data
								$query =  $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cf7_vdata_entry  WHERE `cf7_id` = %d AND `data_id`= %d",$formId, $dataId );
								$arrData = $wpdb->get_results($query);

								//structuring data as per entry
								$arr_data_sorted = vsz_cf7_sortdata($arrData);
		
								//check form related settings available or not
								if(!empty($arr_data_sorted) && !empty($arr_fields) && isset($arr_fields[$fId]) && !empty($arr_fields[$fId])){
									
									//get master fields value from here
									$auraformFields = isset($arr_fields[$fId]['aura_form_fields']) && !empty($arr_fields[$fId]['aura_form_fields']) ? $arr_fields[$fId]['aura_form_fields'] : array();
									
									//Aura form ID necessay to submit the data to particular form
									$auraFormID = isset($arr_fields[$fId]['af_form']) && !empty($arr_fields[$fId]['af_form']) ? $arr_fields[$fId]['af_form'] : "";
									
									if(!empty($auraformFields) && !empty( $auraFormID )  ){
									
										//getting each entry here for submit
										foreach($arr_data_sorted as $entryId => $arrInfo){
											
											$arr_api_data = array();
											$arr_form_data = array();
											
											//check entry data exist or not
											if(!empty($arrInfo) && is_array($arrInfo)){
												
												//getting mapping fields details here
												foreach($auraformFields as $cKey => $cFieldName){

													//Check for the API Fields has Array structure to be maintained
													if( !empty( $cFieldName ) && is_array( $cFieldName )){

														//Looping through the API fields
														foreach ($cFieldName as $api_Key => $cf7_Key) {

															if(array_key_exists($cf7_Key,$arrInfo)){
															
																$field_type = $this->cf7au_get_cf7_field_type($fId, $cf7_Key);
																if( $field_type !== false && $field_type == 'file') {

																	if( !empty($arrInfo[$cf7_Key]) ){
																		$image_data = $this->cf7au_image_to_base64( $arrInfo[$cf7_Key] );
																		//print_r($image_data);
																		if( $image_data && !empty( $image_data ) ){
																			
																			$arr_form_data[$cKey][$api_Key] = array('media_ids'=>array(), 'attachments' => array( $image_data ) );
																		}else{
																			$arr_form_data[$cKey][$api_Key] = '';
																		}
																		
																	}else{
																		$arr_form_data[$cKey][$api_Key] = '';
																	}

																}elseif( $field_type !== false && $field_type == 'date' ){
																	//Converting date format from CF7 to AURA
																	$submitted_date = isset($arrInfo[$cf7_Key]) && !empty($arrInfo[$cf7_Key]) ? $arrInfo[$cf7_Key] : '';
																	$date = '';
																	if( !empty( $submitted_date ) ){
																		//Convert the date from 'dd-mm-yyyy' to 'yyyy-mm-dd'
																		$date = date('Y-m-d', strtotime( $submitted_date ));
																	}
																	$arr_form_data[$cKey][$api_Key] = $date;

																}else{
																	if(is_array($arrInfo[$cf7_Key])){
																		$arr_form_data[$cKey][$api_Key] = isset($arrInfo[$cf7_Key]) && !empty($arrInfo[$cf7_Key]) ? htmlentities(str_replace("&","And",implode(",",$arrInfo[$cf7_Key]))) : '';	
																	}
																	else{
																		$arr_form_data[$cKey][$api_Key] = isset($arrInfo[$cf7_Key]) && !empty($arrInfo[$cf7_Key]) ? htmlentities(str_replace("&","And",$arrInfo[$cf7_Key])) : '';	
																	}
																}
															}

														}

													}else{
													
														if(array_key_exists($cFieldName,$arrInfo)){
															
															$field_type = $this->cf7au_get_cf7_field_type($fId, $cFieldName);
															if( $field_type !== false && $field_type == 'file') {

																if( !empty($arrInfo[$cFieldName]) ){
																	$image_data = $this->cf7au_image_to_base64( $arrInfo[$cFieldName] );
																	//print_r($image_data);
																	if( $image_data && !empty( $image_data ) ){
																		$arr_form_data[$cKey] = array('media_ids'=>array(), 'attachments' => array( $image_data ) );
																	}else{
																		$arr_form_data[$cKey] = '';
																	}
																}else{
																	$arr_form_data[$cKey] = '';
																}

															}elseif( $field_type !== false && $field_type == 'date' ){
																	//Converting date format from CF7 to AURA
																	$submitted_date = isset($arrInfo[$cFieldName]) && !empty($arrInfo[$cFieldName]) ? $arrInfo[$cFieldName] : '';
																	$date = '';
																	if( !empty( $submitted_date ) ){
																		//Convert the date from 'dd-mm-yyyy' to 'yyyy-mm-dd'
																		$date = date('Y-m-d', strtotime( $submitted_date ));
																	}
																	$arr_form_data[$cKey] = $date;

															}else{
																if(is_array($arrInfo[$cFieldName])){
																	$arr_form_data[$cKey] = isset($arrInfo[$cFieldName]) && !empty($arrInfo[$cFieldName]) ? htmlentities(str_replace("&","And",implode(",",$arrInfo[$cFieldName]))) : '';	
																}
																else{
																	$arr_form_data[$cKey] = isset($arrInfo[$cFieldName]) && !empty($arrInfo[$cFieldName]) ? htmlentities(str_replace("&","And",$arrInfo[$cFieldName])) : '';	
																}
															}
														}
													}
													
												}//close foreach common fields
												
												//sending data to Aurastride from here
												if(!empty($arr_form_data) && class_exists('CF7AU_AURASTRIDE_API')  ){

													//add token in API request from here
													$arr_api_data['action'] = $auraFormID.'.CREATE';
													$arr_api_data['form_data'] = $arr_form_data;
													
													// $obj = new CF7AU_AURASTRIDE_API();
													//send data to API from here
													$response_data = $obj->cf7au_submit_from_data_api($arr_api_data);
													
													$status = !empty($response_data) && isset($response_data['status']) && !empty($response_data['status']) ? sanitize_text_field($response_data['status']) : '';

													// Message
													$message = !empty($response_data) && isset($response_data['message']) && !empty($response_data['message']) ? sanitize_text_field($response_data['message']) : '';

													// error
													$error = !empty($response_data) && isset($response_data['error']) && !empty($response_data['error']) ? sanitize_text_field($response_data['error']) : '';

													//checking lead submission successfully or not
													$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
													
													if($status == 'success') {
													//check entry successfully submit or not
														if(!empty($enquiryId)){
															
															//update API status with contact form here
															
															//add enquiry id and enquiry status with particular form
															$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = %d WHERE `name` = 'enquiryId' AND `data_id` = %d", $enquiryId, $entryId);
															$wpdb->query($update_query);
															
															//update tracking API submission status here
															$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = 'yes' WHERE `name` = 'aurastride_api_status' AND `data_id` = %d", $entryId);
															$wpdb->query($update_query);
															$result['status'] = 'success';
															$result['message'] = $message;
															do_action('cf7au_to_aurastride_after_send_api_form_data_adcf7_success',$response_data,$entryId,$arr_api_data,$fId);
															
														} else {
															$result['status'] = 'fail';
															$result['message'] = $message;
															do_action('cf7au_to_aurastride_after_send_api_form_data_adcf7_error',$response_data,$entryId,$arr_api_data,$fId);
														}
													} else {
														
														$result = json_decode($error);
														do_action('cf7au_to_aurastride_after_send_api_form_data_adcf7_error',$response_data,$entryId,$arr_api_data,$fId);
													}
														
												}//close if for API class exist
												
											}//close if for array info
											
										}//close foreach for fetching data details
									
									}//close if for master fields
									
								}//close if for checking form settings available or not
							
						//	}

						}
					}
				}
			}
		}
		echo wp_json_encode($result);
        exit;
    }
}


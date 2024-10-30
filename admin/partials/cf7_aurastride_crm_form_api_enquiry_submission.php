<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//Get all form names which entry store in DB
global $wpdb;
//Get table name for data entry
// $data_entry_table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);


//Get all form names which entry store in DB
$sql = $wpdb->prepare("SELECT `cf7_id` FROM {$wpdb->prefix}cf7_vdata_entry GROUP BY `cf7_id`");
$data = $wpdb->get_results($sql,ARRAY_N);
$arr_form_id = array();
if(!empty($data)){
	foreach($data as $arrVal){
		$arr_form_id[] = (int)$arrVal[0];
	}
}

//enable when you require debugging
$arrLogContent[] = array();
$logFlag = false;
if(false && class_exists('CF7AU_AURASTRIDE_API') ){
	$obj = new CF7AU_AURASTRIDE_API();
	$logFlag = false;
}	

if($logFlag){
	$arrLogContent[] = "Getting Enquiry Submission Data Start:: ".date("Y-m-d H:i:s");
	$arrLogContent[] = "Availabel Form Ids".implode(",", $arr_form_id);
	$strLogContent = implode("\n", $arrLogContent);
	$obj->cf7au_logFileUpdate($strLogContent);
}

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

//Get form related mapping fields settings from here
$arr_fields = get_option('cf7au_api_fields_mapping');

if(!empty($arr_form_id) && !empty($arr_fields) ){
	
	$cf7d_entry_order_by = (string) '`data_id` ASC';
	$cf7d_entry_order_by = sanitize_sql_orderby($cf7d_entry_order_by);
	
	//define filter for submit entries in interval
	$per_schedule_entries = (int) apply_filters('cf7au_to_aurastride_allow_per_schedule_entries',1);
	if(empty($per_schedule_entries)){
		$per_schedule_entries = 1;
	}
	
	if($logFlag){
		$arrLogContent[] = "Getting Form Enquiry";
		$strLogContent = implode("\n", $arrLogContent);
		$obj->cf7au_logFileUpdate($strLogContent);
	}
	
	//fetching each form wise entries here
	foreach($arr_form_id as $key => $fId){
		
		//check current form data pass with API or not
		if( !isset($arr_fields[$fId])  || ( isset($arr_fields[$fId]['enable_af_form']) && empty($arr_fields[$fId]['enable_af_form']) ) ) continue;
		
		//preparing query here for get submitted data
		$checkinnerjoin = 1;
		$innerjoin = 'INNER JOIN `'. $wpdb->prefix.'cf7_vdata_entry` as  mt'.$checkinnerjoin.' on ( `'. $wpdb->prefix.'cf7_vdata_entry`.data_id = mt'.$checkinnerjoin.'.data_id )';
		$api_enquiry_pending_query = " AND ( mt".$checkinnerjoin.".`name` = 'aurastride_api_status' AND mt".$checkinnerjoin.".`value` = 'no' )";
		
		$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cf7_vdata_entry WHERE `cf7_id` = %d AND data_id IN( SELECT * FROM ( SELECT {$wpdb->prefix}cf7_vdata_entry.data_id FROM {$wpdb->prefix}cf7_vdata_entry  ".$innerjoin." WHERE 1 = 1 AND {$wpdb->prefix}cf7_vdata_entry.`cf7_id` = %d {$api_enquiry_pending_query} GROUP BY `data_id` ORDER BY `data_id` ASC LIMIT %d,%d ) temp_table) ORDER BY `data_id` ASC", $fId, $fId, 0, $per_schedule_entries);
		
		//get results
		$arrData = $wpdb->get_results($query);
		
		if($logFlag){
			$arrLogContent[] = "Execute Query for Getting Form Enquiry";
			$strLogContent = implode("\n", $arrLogContent);
			$obj->cf7au_logFileUpdate($strLogContent);
		}
		
		//structuring data as per entry
		$arr_data_sorted = vsz_cf7_sortdata($arrData);
		
		//define filter for modify data before mapping
		//this hook use for the data encryption or decryption
		
		
		//check form related settings available or not
		if(!empty($arr_data_sorted) && !empty($arr_fields) && isset($arr_fields[$fId]) && !empty($arr_fields[$fId])){
			
			
			if($logFlag){
				$arrLogContent[] = "Formatting Data";
				$strLogContent = implode("\n", $arrLogContent);
				$obj->cf7au_logFileUpdate($strLogContent);
			}
			
			//get master fields value from here
			$auraformFields = isset($arr_fields[$fId]['aura_form_fields']) && !empty($arr_fields[$fId]['aura_form_fields']) ? $arr_fields[$fId]['aura_form_fields'] : array();
			
			//Aura form ID necessay to submit the data to particular form
			$auraFormID = isset($arr_fields[$fId]['af_form']) && !empty($arr_fields[$fId]['af_form']) ? $arr_fields[$fId]['af_form'] : "";
			
			if(!empty($auraformFields) && !empty( $auraFormID )  ){
				
				if($logFlag){
					$arrLogContent[] = "Getting Master form Fields & mapping Data to Aurastride Forms : ".$auraFormID;
					$strLogContent = implode("\n", $arrLogContent);
					$obj->cf7au_logFileUpdate($strLogContent);
				}
				
				$groupSeparator = (string) apply_filters('cf7au_to_aurastride_group_field_separator',PHP_EOL);
				
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
							
							$obj = new CF7AU_AURASTRIDE_API();
							//send data to API from here
							$response_data = $obj->cf7au_submit_from_data_api($arr_api_data);
							
							//checking lead submission successfully or not
							$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
							
							//check entry successfully submit or not
							if(!empty($enquiryId)){
								
								//update API status with contact form here
								
								//add enquiry id and enquiry status with particular form
								$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = %d WHERE `name` = 'enquiryId' AND `data_id` = %d", $enquiryId, $entryId);
								$wpdb->query($update_query);
								
								//update tracking API submission status here
								$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = 'yes' WHERE `name` = 'aurastride_api_status' AND `data_id` = %d", $entryId);
								$wpdb->query($update_query);
								
								
							}
								
						}//close if for API class exist
						
					}//close if for array info
					
				}//close foreach for fetching data details
			
			}//close if for master fields
			
		}//close if for checking form settings available or not
	
	}//close for each for getting all contact form data
	//exit;
}//close if for checking any form submission or not yet

// phpcs:enable

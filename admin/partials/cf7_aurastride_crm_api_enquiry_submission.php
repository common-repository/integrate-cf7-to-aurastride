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
$arrLogContent = array();
$logFlag = false;
if(false && class_exists('CF7AU_AURASTRIDE_API') ){
	$obj = new CF7AU_AURASTRIDE_API();
	$logFlag = true;
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
	$per_schedule_entries = (int) apply_filters('cf7au_to_aurastride_allow_per_schedule_entries',10);
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
		if( !isset($arr_fields[$fId])  || ( isset($arr_fields[$fId]['enable_form']) && empty($arr_fields[$fId]['enable_form']) ) ) continue;
		
		//preparing query here for get submitted data
		$checkinnerjoin = 1;
		$innerjoin = 'INNER JOIN `'. $wpdb->prefix.'cf7_vdata_entry` as  mt'.$checkinnerjoin.' on ( `'. $wpdb->prefix.'cf7_vdata_entry`.data_id = mt'.$checkinnerjoin.'.data_id )';
		$api_enquiry_pending_query = " AND ( mt".$checkinnerjoin.".`name` = 'aurastride_api_status' AND mt".$checkinnerjoin.".`value` = 'no' )";
		
		$query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}cf7_vdata_entry WHERE `cf7_id` = %d AND data_id IN( SELECT * FROM ( SELECT {$wpdb->prefix}cf7_vdata_entry.data_id FROM {$wpdb->prefix}cf7_vdata_entry  {$innerjoin} WHERE 1 = 1 AND {$wpdb->prefix}cf7_vdata_entry.`cf7_id` = %d {$api_enquiry_pending_query} GROUP BY `data_id` ORDER BY `data_id` ASC LIMIT %d,%d ) temp_table) ORDER BY `data_id` ASC", $fId, $fId, 0, $per_schedule_entries);	
		
		//get results
		$arrData = $wpdb->get_results($query);
		

		if($logFlag){
			$arrLogContent[] = "Execure Query for Getting Form Enquiry::";
			$strLogContent = implode("\n", $arrLogContent);
			$obj->cf7au_logFileUpdate($strLogContent);
		}
		
		//structuring data as per entry
		$arr_data_sorted = vsz_cf7_sortdata($arrData);
		
		//define filter for modify data before mapping
		//this hook use for the data encryption or decryption
		$arr_data_sorted = (array) apply_filters('cf7au_to_aurastride_before_mapping_api_data',$arr_data_sorted,$fId);
		
		//check form related settings available or not
		if(!empty($arr_data_sorted) && !empty($arr_fields) && isset($arr_fields[$fId]) && !empty($arr_fields[$fId])){
			
			
			if($logFlag){
				$arrLogContent[] = "Formatting Data";
				$strLogContent = implode("\n", $arrLogContent);
				$obj->cf7au_logFileUpdate($strLogContent);
			}
			
			
			//get common and master fields value from here
			$commonFields = isset($arr_fields[$fId]['common_fields']) && !empty($arr_fields[$fId]['common_fields']) ? $arr_fields[$fId]['common_fields'] : array();
			
			if($logFlag){
				$arrLogContent[] = "Common Formatting Data First";
				$arrLogContent[]  = !empty($commonFields) && is_array($commonFields) ? wp_json_encode($commonFields) : '';
				$strLogContent = implode("\n", $arrLogContent);
				$obj->cf7au_logFileUpdate($strLogContent);
			}
			
			//customize common data details from here
			$commonFields = (array) apply_filters('cf7au_to_aurastride_before_add_common_data',$commonFields,$fId);
			
			if($logFlag){
				$arrLogContent[] = "Common Formatting Data after";
				$arrLogContent[]  = !empty($commonFields) && is_array($commonFields) ? wp_json_encode($commonFields) : '';
				$strLogContent = implode("\n", $arrLogContent);
				$obj->cf7au_logFileUpdate($strLogContent);
			}
			
			$arr_dom_fields = array('lead_note');
			$arr_dom_fields = (array) apply_filters('cf7au_to_aurastride_dom_field_list',$arr_dom_fields,$fId);
			
			$arr_predefined_fields = array('vsz_current_date_mapping');
			//$arr_predefined_fields = (array) apply_filters('cf7au_to_aurastride_dom_field_list',$arr_predefined_fields,$fId);
			
			//combine common fields data in final array
			if(!empty($commonFields)){
				
				if($logFlag){
					$arrLogContent[] = "Getting Common Fields & mapping Data";
					$strLogContent = implode("\n", $arrLogContent);
					$obj->cf7au_logFileUpdate($strLogContent);
				}
				
				$groupSeparator = (string) apply_filters('cf7au_to_aurastride_group_field_separator',PHP_EOL);
				
				//getting each entry here for submit
				foreach($arr_data_sorted as $entryId => $arrInfo){
					
					$arr_api_data = array();
					
					if($logFlag){
						$arrLogContent[] = "Sending data to aurastride CRM Form Id:".$fId."  Entry ID:".$entryId." ::\n";
						$strLogContent = implode("\n", $arrLogContent);
						$obj->cf7au_logFileUpdate($strLogContent);
					}
					
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

							$obj = new CF7AU_AURASTRIDE_API();
							//send data to API from here
							
							$response_data = $obj->cf7au_create_lead($arr_api_data);
							
							//checking lead submission successfully or not
							$enquiryId = !empty($response_data) && isset($response_data['lead_id']) && !empty($response_data['lead_id']) ? sanitize_text_field($response_data['lead_id']) : '';
							
							//check entry successfully submit or not
							if(!empty($enquiryId)){
								
								//update API status with contact form here
								
								//add enquiry id and enquiry status with particular form
								$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = %d WHERE `name` = 'enquiryId' AND `data_id` = %d", $enquiryId, $entryId );
								$wpdb->query($update_query);
								
								//define for save additional data flag
								//$update_query = "UPDATE $data_entry_table_name SET `value` = 'yes' WHERE `name` = 'SendData' AND `data_id` = $entryId";
								//$wpdb->query($update_query);
								
								$update_query = $wpdb->prepare("UPDATE {$wpdb->prefix}cf7_vdata_entry SET `value` = 'yes' WHERE `name` = 'aurastride_api_status' AND `data_id` = %d", $entryId);
								$wpdb->query($update_query);
								
								//define action here so user can add additional action after API submission
								do_action('cf7au_to_aurastride_after_send_api_data_success',$response_data,$entryId,$arr_api_data,$fId);
								//update tracking API submission status here
								
							}
							else{
								//define action here so user can add additional action after API submission
								do_action('cf7au_to_aurastride_after_send_api_data_error',$response_data,$entryId,$arr_api_data,$fId);
							}
								
						}//close if for API class exist
						
					}//close if for array info
					
				}//close foreach for fetching data details
			
			}//close if for common fields
			
		}//close if for checking form settings available or not
	
	}//close for each for getting all contact form data
	
}//close if for checking any form submission or not yet

// phpcs:enable

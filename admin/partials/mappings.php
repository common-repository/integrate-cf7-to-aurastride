<?php

/**
 * CF7 to aurastride CRM Form fields Mappings
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


$aurastride_fields = array();
if(function_exists('cf7au_to_aurastride_api_fields')){
	$aurastride_fields = cf7au_to_aurastride_api_fields();
}

//Saving to the database starts from here
$options_updated = false;

//get all contact form list
$cf7forms = $this->cf7au_get_all_cf7_forms();

$arr_dom_fields = array('Note');
function cf7au_sanitize_array_field( $meta_value ) {

	foreach ( (array) $meta_value as $k => $v ) {
		if ( is_array( $v ) ) {
			$meta_value[$k] =  cf7au_sanitize_array_field( $v );
		} else {
			$meta_value[$k] = esc_html(sanitize_text_field( $v ));
		}
	}

	return $meta_value;

}
//save process define here
if(isset( $_POST['cf7au_mapping_submit'] ) && !empty($_POST['cf7au_mapping_submit']) && isset($_POST['current_form_id']) && !empty($_POST['current_form_id']) ){
	//var_dump($_POST);
	if(check_admin_referer( 'cf7_mount_field_map','mapping_nonce')){
		$arr_common_fields = array();
		$arr_master_fields = array();
		$arr_final_array = array();
		$arr_af_common_fields = array();

		$current_form_id = (int) isset($_POST['current_form_id']) && !empty($_POST['current_form_id']) ? trim(wp_unslash (sanitize_text_field($_POST['current_form_id']))) : '';

		//get common fields related data
		if(isset($_POST['cf7mount_common_fields']) && !empty($_POST['cf7mount_common_fields']) && isset($_POST['cf7mount_common_fields'])){
			$arr_common_fields = map_deep( wp_unslash( $_POST['cf7mount_common_fields'] ), 'sanitize_text_field' );
			// $arr_common_fields = cf7au_sanitize_array_field($_POST['cf7mount_common_fields']);
		}
		
		//get master fields related data
		if(isset($_POST['cf7mount_master_fields']) && !empty($_POST['cf7mount_master_fields'])){
			$arr_master_fields = map_deep( wp_unslash( $_POST['cf7mount_master_fields'] ), 'sanitize_text_field' );
			// $arr_master_fields = cf7au_sanitize_array_field($_POST['cf7mount_master_fields']);
		}

		//get aurastride From common fields related data
		if(isset($_POST['cf7aura_form_fields']) && !empty($_POST['cf7aura_form_fields']) && isset($_POST['cf7aura_form_fields'])){
			$arr_af_common_fields = map_deep( wp_unslash( $_POST['cf7aura_form_fields'] ), 'sanitize_text_field' );
			//$arr_af_common_fields = cf7au_sanitize_array_field($_POST['cf7aura_form_fields']);
		}

		//combine data in one array
		if(!empty($arr_common_fields)){
			foreach($arr_common_fields as $fId => $innArray){
				$arr_api = $innArray['api_key'];
				$arr_cf7 = $innArray['cf7_key'];
				$arr_notes = $innArray['notes'];
				foreach($arr_api as $key =>  $api_name){

					if(empty($api_name)) continue;

					if(!empty($arr_dom_fields) && in_array($api_name,$arr_dom_fields)){

						if($api_name == 'Note'){
							$arr_final_array['common_fields'][$api_name] =  array_values(array_filter($arr_cf7['notes']));
						}
					}
					else{
						if(empty($arr_cf7[$key])) continue;
						$arr_final_array['common_fields'][$api_name] = sanitize_text_field($arr_cf7[$key]);
					}
				}
				if(!is_array($arr_notes)) $arr_notes = array($arr_notes);
				//Save notes
				$arr_final_array['common_fields']['lead_note'] =  array_values(array_filter($arr_notes));
			}
		}

		//combine data in one array
		if(!empty($arr_af_common_fields)){
			foreach($arr_af_common_fields as $fId => $innArray){
				$arr_api = $innArray['api_form_key'];
				$arr_cf7 = $innArray['cf7_form_key'];
				foreach($arr_api as $key =>  $api_name){

					if(empty($api_name)) continue;

					if( !empty( $api_name ) && is_array( $api_name ) ){
						//var_dump($arr_cf7[$key]);
						$arrayData = $arr_cf7[$key];
						
						if( is_array( $arrayData ) ){
							
							foreach( $arrayData as $akey=>$aVal ){
								$arr_final_array['aura_form_fields'][$key][$akey] = sanitize_text_field($aVal);
							}
						}
					}else{
						if(empty($arr_cf7[$key])) continue;
						$arr_final_array['aura_form_fields'][$api_name] = sanitize_text_field($arr_cf7[$key]);
					}
				}
				
			}
			
		}
		
		//get master fields related data
		if(isset($_POST['cf7mount_enable_form']) && !empty($_POST['cf7mount_enable_form'])){
			$arr_final_array['enable_form'] = wp_unslash(sanitize_text_field($_POST['cf7mount_enable_form']));
		}
		else{
			$arr_final_array['enable_form'] = '';
		}

		if(isset($_POST['cf7mount_af_enable_form']) && !empty($_POST['cf7mount_af_enable_form'])){
			$arr_final_array['enable_af_form'] = wp_unslash(sanitize_text_field($_POST['cf7mount_af_enable_form']));
		}
		else{
			$arr_final_array['enable_af_form'] = '';
		}
		if(isset($_POST['vsz_enable_form']) && !empty($_POST['vsz_enable_form'])){
			$arr_final_array['vsz_enable_form'] = wp_unslash(sanitize_text_field($_POST['vsz_enable_form']));
		}
		else{
			$arr_final_array['enable_af_form'] = '';
		}

		if(isset($_POST['af-form']) && !empty($_POST['af-form'])){
			$arr_final_array['af_form'] = wp_unslash(sanitize_text_field($_POST['af-form']));
		}
		else{
			$arr_final_array['af_form'] = '';
		}


		$arr_existing_fields = get_option('cf7au_api_fields_mapping');
		if(!empty($arr_existing_fields) && isset($arr_existing_fields[$current_form_id])){
			$arr_existing_fields[$current_form_id] = $arr_final_array;
		}
		else{
			$arr_existing_fields[$current_form_id] = $arr_final_array;
		}
		
		update_option('cf7au_api_fields_mapping',$arr_existing_fields);
		$options_updated = false;

	}
}
//get all mapping fields related value
$arr_mapping_fields = array();
$arr_mapping_fields = get_option('cf7au_api_fields_mapping');

//Define the values that are field type and have defined values within Aurastride
$display_field_type_values = cf7au_display_field_type_values;

//Get the form list from API
$af_form_list_arr = array();
$is_af_list_enabled = false;
if(class_exists('CF7AU_AURASTRIDE_API')){
	$obj_aura_api = new CF7AU_AURASTRIDE_API();
	//send data to API from here
	$af_form_list_arr = $obj_aura_api->cf7au_get_aurastride_forms_list();
	$is_af_list_enabled = true;
}

?><!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
    <h3><?php esc_html_e( 'CF7 to aurastride CRM Fields Mapping', 'integrate-cf7-to-aurastride' ); ?></h3>
   	<ul class="notice-wrap">
		<li><?php esc_html_e( 'Map the required aurastride CRM API fields to your current Contact Form 7 fields.', 'integrate-cf7-to-aurastride' ); ?></li>
		<li><?php esc_html_e( "Please set 'Current date' to send the current submission date to the CRM.", 'integrate-cf7-to-aurastride' ); ?></li>
		<li><?php esc_html_e( "Please set 'Current Date' against 'Received Date' if you do not have any fields to associate.", 'integrate-cf7-to-aurastride' ); ?></li>
	</ul><?php
	
    if( $options_updated ){
		echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully</p></div>';
    }

	//check any contact form exist or not
	if(!empty($cf7forms)){
		print '<div id="cf7-form-listing" class="cf7-form-listing cf72m">';
		$no_cf7 = 0;
			foreach ($cf7forms as $cf7form) {
				$no_cf7++;
				//define contact form Id in variable from here
				$fId = $cf7form->id();
				$checked = '';
				$checked_af_form = '';
				$vsz_enable_form_checked = '';
				if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['enable_form']) && !empty($arr_mapping_fields[$fId]['enable_form'])){
					$checked = 'checked';
				}

				//Check for the custom forms 
				if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['enable_af_form']) && !empty($arr_mapping_fields[$fId]['enable_af_form'])){
					$checked_af_form = 'checked';
				}

				//Check for the custom forms 
				if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['vsz_enable_form']) && !empty($arr_mapping_fields[$fId]['vsz_enable_form'])){
					$vsz_enable_form_checked = 'checked';
				}

				?><!-- define div for have all Time schedule section -->

				<form name="cf7_form_<?php print esc_attr($fId);?>" class="cf7_mount_field_map" id="cf7_form_<?php print esc_attr($fId);?>" method="post">
				<?php
					wp_nonce_field( 'cf7_mount_field_map','mapping_nonce' );
				?>
					<input type="hidden" name="current_form_id" value="<?php print esc_attr($fId);?>">
					<div id="form-listing-<?php print esc_attr($fId);?>" class="form-listing">
						<!-- Display close and move icon from this section -->
						<div class="form-sec form-main-sec " data-formid="<?php print esc_attr($fId);?>" <?php  if( !empty($checked_af_form) && $checked_af_form == "checked" ){ echo "data-afajax='true'"; }else{ echo "data-afajax='false'"; } ?> >
							<label class="control-label" for="form_name" id="form_name"><?php print esc_html($cf7form->title());?></label>
							<div class="dom-close-toggle-icon">
								<!-- Display move icon -->
								<label class="slider-toggle" form-id="<?php print esc_attr($fId);?>"></label>
							</div>
						</div>
						<div class="slide-form-sec" >
							<div class="enable-form" >
								<span>
									<input type="checkbox" name="vsz_enable_form" class="vsz_enable_form" form-id="<?php print esc_attr($fId);?>" id="vsz_enable_form_<?php print esc_attr($fId);?>" value="yes" <?php print esc_attr($vsz_enable_form_checked);?>>
									<label for="vsz_enable_form_<?php print esc_attr($fId);?>"><?php esc_html_e( 'Enable to submit data to aurastride CRM', 'integrate-cf7-to-aurastride' ); ?></label>
								</span>
							</div>	
							<div class="enable-form" id="enable-form-<?php print esc_attr($fId);?>" style="display:flex; <?php if( empty( $vsz_enable_form_checked ) ){ echo "display:none;"; } ?>"  >
								<span>
									<input type="checkbox" name="cf7mount_enable_form" class="cf7mount_enable_form" form-id="<?php print esc_attr($fId);?>" id="cf7mount_enable_form-<?php print esc_attr($fId);?>" value="yes" <?php print esc_attr($checked);?>>
									<label for="cf7mount_enable_form-<?php print esc_attr($fId);?>"><?php esc_html_e( 'Enable aurastride CRM', 'integrate-cf7-to-aurastride' ); ?></label>
								</span>
								<?php if( $is_af_list_enabled ){ ?>
									<span>
										<input type="checkbox" name="cf7mount_af_enable_form" class="cf7mount_af_enable_form" form-id="<?php print esc_attr($fId);?>" id="cf7mount_af_enable_form-<?php print esc_attr($fId);?>" value="yes" <?php print esc_attr($checked_af_form);?>>
										<label for="cf7mount_af_enable_form-<?php print esc_attr($fId);?>"><?php esc_html_e( 'Enable aurastride Forms', 'integrate-cf7-to-aurastride' ); ?></label>
									</span>
								<?php } ?>
							</div>
							<!-- CRM API Fields Starts -->
							<div class="common-fields" id="common-fields-<?php print esc_attr($fId);?>" <?php if(empty($checked)){echo 'style="display:none;"';} ?>>
								<div class="form-sec">
									<label><?php esc_html_e( 'aurastride CRM General Fields', 'integrate-cf7-to-aurastride' ); ?></label>
								</div>
								<div class="append-field-sec general_field_table">
									<table id="cf7moun_table_<?php print esc_attr($cf7form->id());?>" >
										<tr>
											<th class="col_gen_field"><?php esc_html_e( 'aurastride CRM General Fields', 'integrate-cf7-to-aurastride' ); ?></th>
											<th class="col_cf7_field"><?php esc_html_e( 'CF7 Fields', 'integrate-cf7-to-aurastride' ); ?></th>
											<th class="col_action"><?php esc_html_e( 'Action', 'integrate-cf7-to-aurastride' ); ?></th>
										</tr><?php
										$arr_form_tags_list = $cf7form->scan_form_tags();
										$arr_form_tags = array();
										//get all contact form tags list
										foreach ($arr_form_tags_list as $arr_form_tag){
											if(empty($arr_form_tag->name) ){
												continue;
											}
											$arr_form_tags[$arr_form_tag->name] = $arr_form_tag->name;
										}
										$arr_form_tags['vsz_current_date_mapping'] = 'Current Date ('.date("Y-m-d").')';
												
										//add filter so user can add additonal mapping fields
										$arr_form_tags = (array) apply_filters('cf7au_to_aurastride_add_additional_mapping_field',$arr_form_tags,$fId);
										
										//get save common fields value
										$arr_com_fields = array();
										if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['common_fields'])){
											$arr_com_fields = $arr_mapping_fields[$fId]['common_fields'];
										}

										$i=1;
										//displaying all default API fields from here
										if(!empty($aurastride_fields)){
											foreach($aurastride_fields as $d_key => $d_value){
												$field_api_id = 'cf7mount_api_key_'.$fId.'_'.$i;
												$field_cf7_id = 'cf7mount_cf7_key_'.$fId.'_'.$i;
												$selected = '';
												if(!empty($arr_com_fields) && array_key_exists($d_key,$arr_com_fields)){
													$selected = $d_key;
												}
												?><tr data-id="<?php print esc_attr($i);?>">
													<td class="col_gen_field"><?php
														if( "Note" != $d_value){
														?><input type="hidden" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][api_key][]" value="<?php echo esc_attr($d_key); ?>" />
														<?php } ?>
														<span><?php echo esc_html($d_value); ?></span>
													</td>
													<td><?php
														if( "Note" == $d_value){
															$inn_sec_id = 'inner-dom-'.$fId.'-'.$i;
															?><div class="dom-<?php print esc_attr($i);?> note-section" >
																<div class="inner-dom-<?php print esc_attr($i);?>" id="<?php print esc_attr($inn_sec_id);?>" ><?php
																	if(isset($arr_com_fields['lead_note']) && !empty($arr_com_fields['lead_note']) && is_array($arr_com_fields['lead_note'])){
																		$field_count = 1;
																		foreach($arr_com_fields['lead_note'] as $iKey => $note_cf7_key){
																			$field_dom_id = 'field-dom-'.$fId.'-'.$i.'-'.$field_count;
																			?><div class="field-dom" id="<?php print esc_attr($field_dom_id);?>" field-sec-id="<?php print esc_attr($field_count);?>" >
																				<select data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][notes][]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default">
																					<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
																					foreach ($arr_form_tags as $Fname => $fVal ){
																						if( empty($Fname) ){
																							continue;
																						}
																						$select_term = '';
																						if($note_cf7_key == $Fname ){
																							$select_term = 'selected';
																						}
																						?><option value="<?php echo esc_attr($Fname);?>" <?php echo esc_attr($select_term); ?>
																							><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal)); ?></option><?php
																					}
																				?></select>
																				<div  data-id="<?php print esc_attr($i);?>" form-id="<?php echo esc_attr($fId); ?>" field-sec-id="<?php print esc_attr($field_count);?>">
																					<a class="field-dom-delete" title="Delete"  onclick="removeNoteField(<?php  print esc_attr($fId); ?>,<?php print esc_attr($i);?>,<?php print esc_attr($field_count);?>)"><span>
																					<img src="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/remove.svg" />
																					</span></a>
																				</div>
																			</div><?php
																			$field_count++;
																		}
																	}
																	else{
																		$field_count = 1;
																		$field_dom_id = 'field-dom-'.$fId.'-'.$i.'-'.$field_count;
																		?><div class="field-dom" id="<?php print esc_attr($field_dom_id);?>" field-sec-id="<?php print esc_attr($field_count);?>" >
																			<select data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][notes][]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default">
																				<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
																				foreach ($arr_form_tags as $Fname => $fVal ){
																					if( empty($Fname) ){
																						continue;
																					}
																					$select_term = '';
																					if(!empty($arr_com_fields) && array_key_exists($d_key,$arr_com_fields) && $arr_com_fields[$d_key] == $Fname ){
																						$select_term = 'selected';
																						unset($arr_com_fields[$d_key]);
																					}

																					?><option value="<?php echo esc_attr($Fname); ?>" <?php echo esc_attr($select_term); ?>
																						><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal)); ?></option><?php
																				}
																			?></select>
																		</div><?php
																	}
																?></div>
																<a data-id="<?php print esc_attr($i);?>" form-id="<?php echo esc_attr($cf7form->id()); ?>" class="add_note_fields" title="Add" onclick="addNoteField(<?php print esc_attr($cf7form->id()); ?>,<?php print esc_attr($i); ?>)">Add more</a>
															</div><?php
															unset($arr_com_fields[$d_key]);
														}
														else{
															?><select data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][cf7_key][]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default">
																<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
																foreach ($arr_form_tags as $Fname => $fVal){
																	if( empty($Fname) ){
																		continue;
																	}
																	$select_term = '';
																	if(!empty($arr_com_fields) && array_key_exists($d_key,$arr_com_fields) && $arr_com_fields[$d_key] == $Fname ){
																		$select_term = 'selected';
																		unset($arr_com_fields[$d_key]);
																	}

																	?><option value="<?php echo esc_attr($Fname);?>"
																		<?php echo esc_attr($select_term); ?>
																		><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal)); ?></option><?php
																}
															?></select><?php
														}
													?></td>
													<td> - </td>
												</tr><?php

												$i++;
											}
										}//close aurastride fields if

										//define flag for check any extra fields exist or not
										$commonFlag = true;
										//check any other fields exist or not
										if(!empty($arr_com_fields)){
											$commonFlag = false;
											foreach($arr_com_fields as $api_key => $cf7_key){
												$field_api_id = 'cf7mount_api_key_'.$fId.'_'.$i;
												$field_cf7_id = 'cf7mount_cf7_key_'.$fId.'_'.$i;
												?><tr id="field-<?php echo esc_attr($fId.'-'.$i); ?>" data-id="<?php print esc_attr($i);?>">
													<td width="30%">
														<input type="text" data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][api_key][]" id="<?php print esc_attr($field_api_id);?>" value="<?php 
														printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html(wp_unslash($api_key))); ?>">
													</td>
													<td>
														<select data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][cf7_key][]"  id="<?php print esc_attr($field_cf7_id);?>" >
															<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
															foreach ($arr_form_tags as $Fname => $fVal){
																if( empty($Fname) ){
																	continue;
																}
																$select_term = '';
																if( $Fname == $cf7_key ){
																	$select_term = 'selected';
																}

																?><option value="<?php echo esc_attr($Fname); ?>" <?php echo esc_attr($select_term); ?>
																	><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal)); ?></option><?php
															}
														?></select>
													</td>
													<td>
														<a data-id="<?php print esc_attr($i);?>" form-id="<?php print esc_attr($fId);?>" class="delete_form_fields" title="Delete" onclick="removeField(<?php  print esc_attr($fId); ?>,<?php print esc_attr($i);?>)"><?php esc_html_e( 'Delete', 'integrate-cf7-to-aurastride' );?></a>
													</td>
												</tr><?php
												$i++;
											}
										}//close common fields

										//displaying default extra fields from here
										if($commonFlag){
											$field_api_id = 'cf7mount_api_key_'.$fId.'_'.$i;
											$field_cf7_id = 'cf7mount_cf7_key_'.$fId.'_'.$i;
											?><tr id="field-<?php echo esc_attr($fId.'-'.$i); ?>" data-id="<?php print esc_attr($i);?>">
												<td width="30%">
													<input type="text" data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][api_key][]" id="<?php print esc_attr($field_api_id);?>">
												</td>
												<td>
													<select data-id="<?php print esc_attr($i);?>" name="cf7mount_common_fields[<?php echo esc_attr($cf7form->id()); ?>][cf7_key][]"  id="<?php print esc_attr($field_cf7_id);?>">
														<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
														foreach ($arr_form_tags as $Fname => $fVal){
															if( empty($Fname) ){
																continue;
															}
															?><option value="<?php echo esc_attr($Fname); ?>">
																<?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal)); ?>
															</option><?php
														}
													?></select>
												</td>
												<td>
													<a data-id="<?php print esc_attr($i);?>" form-id="<?php print esc_attr($fId);?>" class=" delete_form_fields" title="Delete" onclick="removeField(<?php  print esc_attr($fId); ?>,<?php print esc_attr($i);?>)"><?php esc_html_e( 'Delete', 'integrate-cf7-to-aurastride' );?></a>
												</td>
											</tr><?php
										}
									?></table>
									<div class="new_data_field_sec">
										<a href="javascript:void(0);" id="add_new_fields<?php print esc_attr($cf7form->id());?>" class="button button-primary add_new_fields" data-id="<?php print esc_attr($cf7form->id());?>" onclick="addField(<?php print esc_attr($fId);?>)"><i class="fa fa-plus"></i><label><?php esc_html_e( 'Add New Field', 'integrate-cf7-to-aurastride' );?></label></a>
									</div>
								</div>
							</div>
							<!-- CRM API Fields ENDS -->
							<?php if( $is_af_list_enabled ){ ?>
							<!-- CRM API Form Fields Starts -->
							<div class="common-fields" id="common-af-fields-<?php print esc_attr($fId);?>" <?php if(empty($checked_af_form)){echo 'style="display:none;"';} ?>>
								<div class="form-sec">
									<label><?php esc_html_e( 'aurastride Forms', 'integrate-cf7-to-aurastride' ); ?></label>
									<?php 
										$af_form = "";
										
										if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['af_form']) && !empty($arr_mapping_fields[$fId]['af_form'])){
											$af_form = $arr_mapping_fields[$fId]['af_form'];
										}
									?>
									<select name="af-form" id="af-form-<?php print esc_attr($fId);?>" class="af-form-list-dropdown" data-cf_formid="<?php print esc_attr($fId);?>">
										<option value="">Select Form</option>
										<?php if(isset($af_form_list_arr) && !empty($af_form_list_arr)){
											if( $af_form_list_arr["status"] == "success" && !empty($af_form_list_arr["message"]) ){
												foreach( $af_form_list_arr["message"] as $form_lists ){
													if( $af_form ==  $form_lists['form_code']){
														$af_form_selected = "selected";
													}else{
														$af_form_selected = "";
													}
													echo '<option value="'.esc_attr($form_lists['form_code']).'" '.esc_attr($af_form_selected).'>'.esc_html($form_lists['form_name']).'</option>';
												}
											}
										}
										?>
									</select>
								</div>
								<div style="display: none;" class="custom-overlay  overlayLoader" id="overlayLoader_<?php print esc_attr($cf7form->id());?>">
									<div class="overlay-img">
										<span class="loader-img-logo"><img alt="Ajax-loader" src="<?php echo esc_url(CF7AU_PLUGIN_URL); ?>images/aurastride-icon-logo.svg"></span>
										<span class="loader-img-txt"><img alt="Ajax-loader" src="<?php echo esc_url(CF7AU_PLUGIN_URL);  ?>images/aurastride-txt-logo.svg"></span>
									</div>
								</div>
								<div class="append-field-sec general_field_table" id="vsz_af_field_value_sec_<?php print esc_attr($cf7form->id());?>" <?php if(empty($checked_af_form)){echo 'style="display:none;"';} ?>>
									<div class="form-sec">
										<label><?php esc_html_e( 'aurastride Forms Fields', 'integrate-cf7-to-aurastride' ); ?></label>
									</div>
									<table id="cf7_aura_table_data_<?php print esc_attr($cf7form->id());?>" >
										<tr>
											<td colspan="2" >
												<?php
												echo esc_html__( "Loading your form fields...", 'integrate-cf7-to-aurastride' );
												?>
											</td>
										</tr>	
									</table>
									
								</div>
							</div>
							<?php } ?>
							<!-- CRM API Form Fields ENDS -->

							<div class="ch_submit_btn">
								<input type="submit" form-id="<?php print esc_attr($fId);?>" class="button button-primary save-cf7-form" name="cf7au_mapping_submit" id="cf7au_mapping_submit-<?php esc_attr($fId);?>" value="<?php esc_attr_e( 'Save', 'integrate-cf7-to-aurastride' );?>">
							</div>
						</div>
					</div>
				</form><?php
			}//close form foreach
		print '</div>';

	}//close if
?></div>
<!-- Loader -->
<div class="service_bck_loader" id="service_bck_loader" style="display:none;"><div class="loader">
    <span class="loader-img-logo"><img alt="Ajax-loader" src="<?php echo esc_url(CF7AU_PLUGIN_URL); ?>images/aurastride-icon-logo.svg"></span>
    <span class="loader-img-txt"><img alt="Ajax-loader" src="<?php echo esc_url(CF7AU_PLUGIN_URL); ?>images/aurastride-txt-logo.svg"></span>
</div></div>
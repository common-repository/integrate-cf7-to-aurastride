<?php
if ( !defined( 'ABSPATH' ) ) {
exit;
}

if(isset($_POST['row_count']) && $_POST['row_count'] != '' && wp_verify_nonce( wp_unslash( sanitize_text_field( $_POST['nonce'] ) ), 'cf7_mount_field_map')){
	$cur_auto_id = wp_unslash(sanitize_text_field($_POST['row_count']));
}
if(isset($_POST['form_id']) && $_POST['form_id'] != '' && wp_verify_nonce( wp_unslash(sanitize_text_field( $_POST['nonce'] ) ), 'cf7_mount_field_map')){
	$form_id = wp_unslash(sanitize_text_field($_POST['form_id']));
}else{
	echo "";
	exit;
}

$arr_form_tags_list = $this->cf7au_get_cf7_forms_fields($form_id);
if( empty( $arr_form_tags_list ) ){
	echo "";
	exit;
}

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
$arr_form_tags = (array) apply_filters('cf7au_to_aurastride_add_additional_mapping_field',$arr_form_tags,$form_id);

$dom_field_name = 'general';
if(isset($_POST['dom_field_name']) && !empty($_POST['dom_field_name']) ){
	$dom_field_name = trim(sanitize_text_field($_POST['dom_field_name']));
}

$field_sec_count = '';
if(isset($_POST['field_sec_count']) && !empty($_POST['field_sec_count']) ){
	$field_sec_count = trim(sanitize_text_field($_POST['field_sec_count']));
}

switch($dom_field_name){

	case 'note':
		$field_sec_count++;
		$field_cf7_id = 'cf7mount_cf7_key_'.$form_id.'_'.$cur_auto_id;
		$field_dom_id = 'field-dom-'.$form_id.'-'.$cur_auto_id.'-'.$field_sec_count;
		?><div class="field-dom" id="<?php print esc_attr($field_dom_id);?>" field-sec-id="<?php print esc_attr($field_sec_count);?>" >
			<select data-id="<?php print esc_attr($cur_auto_id);?>" name="cf7mount_common_fields[<?php echo esc_attr($form_id); ?>][notes][]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default">
				<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
				foreach ($arr_form_tags as $Fname => $fVal ){
					if( empty($Fname) ){
						continue;
					}
					?><option value="<?php echo esc_attr($Fname); ?>"><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal));?></option><?php
				}
			?></select>
			<div class="field-dom-delete" data-id="<?php print esc_attr($cur_auto_id);?>" form-id="<?php echo esc_attr($form_id); ?>" field-sec-id="<?php print esc_attr($field_sec_count);?>">
				<a title="Delete"  onclick="removeNoteField(<?php  print esc_attr($form_id); ?>,<?php print esc_attr($cur_auto_id);?>,<?php print esc_attr($field_sec_count);?>)"><span><img src="<?php echo esc_url(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/remove.svg" /></span></a>
			</div>
		</div><?php

	break;

	case 'general':

		$cur_auto_id++;
		$field_api_id = 'cf7mount_api_key_'.$form_id.'_'.$cur_auto_id;
		$field_cf7_id = 'cf7mount_cf7_key_'.$form_id.'_'.$cur_auto_id;
		?><tr id="field-<?php echo esc_attr($form_id.'-'.$cur_auto_id); ?>" data-id="<?php print esc_attr($cur_auto_id);?>">
			<td width="30%">
				<input type="text" data-id="<?php print esc_attr($cur_auto_id);?>" name="cf7mount_common_fields[<?php echo esc_attr($form_id); ?>][api_key][]" id="<?php print esc_attr($field_api_id);?>">
			</td>
			<td>
				<select data-id="<?php print esc_attr($cur_auto_id);?>" name="cf7mount_common_fields[<?php echo esc_attr($form_id); ?>][cf7_key][]" id="<?php print esc_attr($field_cf7_id);?>">
					<option value="">N/A</option><?php
					foreach ($arr_form_tags as $Fname => $fVal ){
						if( empty($Fname) ){
							continue;
						}
						?><option value="<?php echo esc_attr($Fname); ?>"><?php
							echo esc_html($fVal);
						?></option><?php
					}
				?></select>
			</td>
			<td>
				<a data-id="<?php print esc_attr($cur_auto_id);?>" form-id="<?php print esc_attr($form_id);?>" class="delete_form_fields" title="Delete"  onclick="removeField(<?php  print esc_attr($form_id); ?>,<?php print esc_attr($cur_auto_id);?>)"><?php esc_html_e( 'Delete', 'integrate-cf7-to-aurastride' );?></a>
			</td>
		</tr><?php
	break;

	default:

	break;
}

wp_die();





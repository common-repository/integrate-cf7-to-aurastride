<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//Get the form list from API
$af_form_list_arr = array();
$arr_mapping_fields = get_option('cf7au_api_fields_mapping');
$fId = $formId;
$af_form = "";

if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$formId]['af_form']) && !empty($arr_mapping_fields[$formId]['af_form'])){
    $af_form = $arr_mapping_fields[$formId]['af_form'];
}
if(class_exists('CF7AU_AURASTRIDE_API') ){

	$obj = new CF7AU_AURASTRIDE_API();

	$arrPrams = array("form_code"=>$af_form);

	//send data to API from here
	$af_form_fields_arr = $obj->cf7au_get_form_custom_field($arrPrams);
	$CF7_form_fields = $this->cf7au_get_cf7_forms_fields($formId);

	//get all contact form tags list
	if(!empty($CF7_form_fields)){
		$arr_form_tags_list = $CF7_form_fields;
		$arr_form_tags = array();
		foreach ($arr_form_tags_list as $arr_form_tag){
			if(empty($arr_form_tag->name) ){
				continue;
			}
			$arr_form_tags[$arr_form_tag->name] = array( "name" => $arr_form_tag->name, "field_type"=> $arr_form_tag->basetype);
		}
	}
	
	
	if( !empty( $af_form_fields_arr ) && $af_form_fields_arr['status'] == "success"  ){
		?>

		<tr>
			<th class="col_gen_field"><?php esc_html_e( 'aurastride Form Fields', 'integrate-cf7-to-aurastride' ); ?></th>
			<th class="col_cf7_field"><?php esc_html_e( 'CF7 Fields', 'integrate-cf7-to-aurastride' ); ?></th>
		</tr>

		<?php
		$i=1;
		//displaying all default API fields from here
		$form_fields_array = $af_form_fields_arr["message"];
		
		//get save common fields value
		$arr_af_com_fields = array();
		if(!empty($arr_mapping_fields) && isset($arr_mapping_fields[$fId]['aura_form_fields'])){
			$arr_af_com_fields = $arr_mapping_fields[$fId]['aura_form_fields'];
		}
		
		
		if( !empty( $form_fields_array ) ){

			foreach ($form_fields_array as $d_key => $form_field) {
				# code...
				if( ( isset($form_field['name']) && isset($form_field['meta']) && empty( $form_field['meta'] ) ) || $form_field['active'] == "N" ){
					continue;
				}
				$field_api_id = 'cf7aura_form_af_api_key_'.$fId.'_'.$i;
				$field_cf7_id = 'cf7aura_form_cf7_key_'.$fId.'_'.$i;
				$selected = '';
				if(!empty($arr_af_com_fields) && array_key_exists($d_key,$arr_af_com_fields)){
					$selected = $d_key;
				}
				?><tr data-id="<?php print number_format($i);?>">
					<td class="col_gen_field">
						<?php 
							if( $form_field['type'] == "DR" || $form_field['type'] == "TR" ){
						?>

								<input type="hidden" name="cf7aura_form_fields[<?php echo number_format($formId); ?>][api_form_key][<?php echo esc_attr($d_key); ?>]['from']" value="<?php echo esc_attr($d_key); ?>" />
								<input type="hidden" name="cf7aura_form_fields[<?php echo number_format($formId); ?>][api_form_key][<?php echo esc_attr($d_key); ?>]['to']" value="<?php echo esc_attr($d_key); ?>" />
						<?php		
							}
							else{
						?>
								<input type="hidden" name="cf7aura_form_fields[<?php echo number_format($formId); ?>][api_form_key][]" value="<?php echo esc_attr($d_key); ?>" />
						<?php
							}
						?>
						
						
						<span><?php echo esc_html($form_field['name']); ?> <?php if( $form_field['required'] == "Y" ) { echo "<span class='required'>*</span>"; }?> </span>

					<!-- Display the preferred values -->

					<?php 
					if( !empty($display_field_type_values) && is_array( $display_field_type_values ) ){

						if( in_array( $form_field['type'], $display_field_type_values  ) && !empty( $form_field['values'] )  ){
							echo '<div class="predefined-api-values" >';
							if( is_array( $form_field['values'] ) ){

								foreach( $form_field['values'] as $api_values_key => $api_values_defined  ){
									if( !empty( $api_values_defined ) && isset( $api_values_defined['meta'] ) && !empty( $api_values_defined['meta'] ) ){
										echo '<code>'.esc_html($api_values_defined['meta']).'</code>';
									}

								}

							}
							echo '<span class="" title="These are the preferred values that needs to be set within the CF7 form field values, else the data will not be submitted to the CRM.">i</span>';
							echo '</div>';

						}

					}

					?>

					<!-- Display the preferred values -->

					</td>
					<td>
						<!-- Check for the field if FROM - TO type -->
						<?php 
						
							if( $form_field['type'] == "DR" || $form_field['type'] == "TR" ){
								if(  isset( $arr_af_com_fields[$d_key]) && is_array( $arr_af_com_fields[$d_key]) ){
									$from_val = "";
									$to_val = "";
										
									if( isset($arr_af_com_fields[$d_key]['from'])  ){
										$from_val = $arr_af_com_fields[$d_key]['from'];
									}
									if( isset($arr_af_com_fields[$d_key]['to']) ){
										$to_val = $arr_af_com_fields[$d_key]['to'];
									}
								}
								?>
								<table>
									<tr>
										<td style="width:50%;vertical-align:top;">
										FROM
										</td>	
										<td style="width:50%;vertical-align:top;">
											<select data-id="<?php print number_format($i);?>" name="cf7aura_form_fields[<?php echo number_format($formId); ?>][cf7_form_key][<?php echo esc_attr($d_key); ?>][from]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default" <?php if( $form_field['required'] == "Y" ) { echo 'data-required="true"'; echo " required"; }?> >
												<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
												foreach ($arr_form_tags as $Fname => $fVal){
													if( empty($Fname) ){
														continue;
													}
													//Check if the Custom field type is File then change the selection
													if( $form_field['type'] == "DR" && $fVal["field_type"] !== "date" ){
														continue;
													}

													$select_term = '';
													if(!empty($arr_af_com_fields) && array_key_exists($d_key,$arr_af_com_fields) && $from_val == $Fname ){
														$select_term = 'selected';
														unset($arr_com_fields[$d_key]);
													}

													?><option value="<?php echo esc_attr($Fname);?>" <?php echo esc_attr($select_term); ?> ><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal["name"])); ?></option><?php
												}
											?>
										</select>
										</td>
									</tr>	
									<tr>
										<td style="width:50%;vertical-align:top;">
										TO
										</td>	
										<td style="width:50%;vertical-align:top;">
											
											<select data-id="<?php print esc_attr($i);?>" name="cf7aura_form_fields[<?php echo esc_attr($formId); ?>][cf7_form_key][<?php echo esc_attr($d_key); ?>][to]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default" <?php if( $form_field['required'] == "Y" ) { echo 'data-required="true"'; echo " required"; }?> >
												<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
												foreach ($arr_form_tags as $Fname => $fVal){
													if( empty($Fname) ){
														continue;
													}
													//Check if the Custom field type is File then change the selection
													if( $form_field['type'] == "DR" && $fVal["field_type"] !== "date" ){
														continue;
													}

													$select_term = '';
													if(!empty($arr_af_com_fields) && array_key_exists($d_key,$arr_af_com_fields) && $to_val == $Fname ){
														$select_term = 'selected';
														unset($arr_com_fields[$d_key]);
													}

													?><option value="<?php echo esc_attr($Fname);?>" <?php echo esc_attr($select_term); ?> ><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal["name"]));?></option><?php
												}
											?>
										</select>
										</td>
									</tr>
								</table>

								<?php
								

							}else{

						?>
						<!-- Check for the field if FROM - TO type -->

						<!-- Contact form 7 Fields to be displayed -->
						<select data-id="<?php print number_format($i);?>" name="cf7aura_form_fields[<?php echo number_format($formId); ?>][cf7_form_key][]"  id="<?php print esc_attr($field_cf7_id);?>" data-fieldType="default" <?php if( $form_field['required'] == "Y" ) { echo 'data-required="true"'; echo " required"; }?> >
								<option value=""><?php esc_html_e( 'N/A', 'integrate-cf7-to-aurastride' );?></option><?php
								foreach ($arr_form_tags as $Fname => $fVal){
									if( empty($Fname) ){
										continue;
									}
									//Check if the Custom field type is File then change the selection
									if( $form_field['type'] == "FL" && $fVal["field_type"] !== "file" ){
										continue;
									}

									if( $form_field['type'] == "DP" && $fVal["field_type"] !== "date" ){
										continue;
									}

									$select_term = '';
									if(!empty($arr_af_com_fields) && array_key_exists($d_key,$arr_af_com_fields) && $arr_af_com_fields[$d_key] == $Fname ){
										$select_term = 'selected';
										unset($arr_com_fields[$d_key]);
									}

									?><option value="<?php echo esc_attr($Fname);?>" <?php echo esc_attr($select_term); ?> ><?php printf ( esc_html__( '%s', 'integrate-cf7-to-aurastride' ), esc_html($fVal["name"]));?></option><?php
								}
							?>
						</select>
						<!-- Contact form 7 Fields to be displayed ends -->
						<?php } ?>
					</td>
					
				</tr><?php

				$i++;


			}
			
			if( $i == "1" ){
				?>
				<tr>
					<td colspan="2" >
						<?php
						echo esc_html__( "There are no fields created within aurastride. 1", 'integrate-cf7-to-aurastride' );
						?>
					</td>
				<?php
			}

		}else{
			?>
			<tr>
				<td colspan="2" >
					<?php
					echo esc_html__( "There are no fields created within aurastride.", 'integrate-cf7-to-aurastride' );
					?>
				</td>
			<?php
		}

	}else{
		?>
		<tr>
			<td colspan="2" >
				<?php
				echo esc_html__( "There are no fields created within aurastride.", 'integrate-cf7-to-aurastride' );
				?>
			</td>
		<?php
	}
	
}else{
	?>
	<tr>
		<td colspan="2" >
			<?php
			echo esc_html__( "You cannot access the data, kindly contact the administrator or plugin developer", 'integrate-cf7-to-aurastride' );
			?>
		</td>
	<?php
	
}

?>
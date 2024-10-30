(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
})(jQuery);

//ADD new field for the CF7 fields
jQuery(document).ready(function ($) {
  //Add slider toggle  on click
  jQuery(".cf7_mount_field_map .form-main-sec").click(function () {
    jQuery(this).toggleClass("slide_open");
    jQuery(this).next().slideToggle();
    if (jQuery(this).hasClass("slide_open")) {
      var formId = jQuery(this).data("formid");
      var afajax = jQuery(this).data("afajax");
      //console.log(formId);
      //console.log(afajax);
      if (afajax && !jQuery(this).hasClass("form_loaded")) {
        loadAFformFields(formId);
        jQuery(this).addClass("form_loaded");
      }
    }
  });

  //add settings field related validation here
  jQuery("#cf7au_setting_submit").click(function () {
    if (jQuery("#cf7au_api_enable").prop("checked") == true) {
      var errorFlag = false;
      if (
        jQuery("#cf7au_api_url").val() == "" ||
        jQuery("#cf7au_api_url").val().trim().length <= 0
      ) {
        jQuery("#cf7au_api_url").css("border", "1px solid red");
        errorFlag = true;
      } else {
        jQuery("#cf7au_api_url").css("border", "");
      }

      if (
        jQuery("#cf7au_authorization_code").val() == "" ||
        jQuery("#cf7au_authorization_code").val().trim().length <= 0
      ) {
        jQuery("#cf7au_authorization_code").css("border", "1px solid red");
        errorFlag = true;
      } else {
        jQuery("#cf7au_authorization_code").css("border", "");
      }

      if (
        jQuery("#cf7au_client_id").val() == "" ||
        jQuery("#cf7au_client_id").val().trim().length <= 0
      ) {
        jQuery("#cf7au_client_id").css("border", "1px solid red");
        errorFlag = true;
      } else {
        jQuery("#cf7au_client_id").css("border", "");
      }

      if (
        jQuery("#cf7au_client_secret").val() == "" ||
        jQuery("#cf7au_client_secret").val().trim().length <= 0
      ) {
        jQuery("#cf7au_client_secret").css("border", "1px solid red");
        errorFlag = true;
      } else {
        jQuery("#cf7au_client_secret").css("border", "");
      }

      if (
        jQuery("#cf7au_authorization_key").val() == "" ||
        jQuery("#cf7au_authorization_key").val().trim().length <= 0
      ) {
        jQuery("#cf7au_authorization_key").css("border", "1px solid red");
        errorFlag = true;
      } else {
        jQuery("#cf7au_authorization_key").css("border", "");
      }

      /*if(jQuery("#cf72mot_token_key").val() == '' || jQuery("#cf72mot_token_key").val().trim().length <= 0){
				jQuery("#cf72mot_token_key").css("border","1px solid red");
				errorFlag = true;
			}
			else{
				jQuery("#cf72mot_token_key").css("border","");
			}*/

      if (errorFlag) {
        return false;
      }
    }
  });

  jQuery(".master-opt-field").on("change", function () {
    // Get the data from the template
    var formId = jQuery(this).attr("form-id");
    var secId = jQuery(this).attr("data-id");
    var selCf7Id = "#vsz_extra_field_value_sel-" + formId + "-" + secId;
    var txtFieldId = "#vsz_extra_field_value_txt-" + formId + "-" + secId;
    var objSelCf7 = jQuery(selCf7Id);
    var objTxtField = jQuery(txtFieldId);
    var cf7Val = objSelCf7.val();
    var txtVal = objTxtField.val();

    if (cf7Val != "") {
      objTxtField.val("");
      objTxtField.attr("readonly", true);
      objTxtField.addClass("disable");
    } else if (objTxtField.attr("readonly")) {
      objTxtField.removeAttr("readonly");
      objTxtField.removeClass("disable");
    }
  });

  jQuery(".vsz_enable_form").click(function () {
    var formId = jQuery(this).attr("form-id");
    if (jQuery(this).prop("checked")) {
      jQuery("#enable-form-" + formId).show();
    } else {
      jQuery("#enable-form-" + formId).hide();
      jQuery("#cf7mount_enable_form-" + formId).prop("checked", false);
      jQuery("#cf7mount_af_enable_form-" + formId).prop("checked", false);
      jQuery("#master-fields-" + formId).hide();
      jQuery("#common-fields-" + formId).hide();
      jQuery("#master-af-fields-" + formId).hide();
      jQuery("#common-af-fields-" + formId).hide();
      jQuery("#af-form-" + formId).val("");
      //Disable the selected form AF values
    }
  });

  jQuery(".cf7mount_enable_form").click(function () {
    var formId = jQuery(this).attr("form-id");
    if (jQuery(this).prop("checked")) {
      jQuery("#master-fields-" + formId).show();
      jQuery("#common-fields-" + formId).show();

      jQuery("#master-af-fields-" + formId).hide();
      jQuery("#common-af-fields-" + formId).hide();

      jQuery("#cf7mount_af_enable_form-" + formId).prop("checked", false);
    } else {
      jQuery("#master-fields-" + formId).hide();
      jQuery("#common-fields-" + formId).hide();

      //jQuery("#master-af-fields-" + formId).show();
      //jQuery("#common-af-fields-" + formId).show();
    }
  });

  //For new aurastride forms to be toggled

  jQuery(".cf7mount_af_enable_form").click(function () {
    var formId = jQuery(this).attr("form-id");
    if (jQuery(this).prop("checked")) {
      jQuery("#master-af-fields-" + formId).show();
      jQuery("#common-af-fields-" + formId).show();

      jQuery("#master-fields-" + formId).hide();
      jQuery("#common-fields-" + formId).hide();

      jQuery("#cf7mount_enable_form-" + formId).prop("checked", false);
    } else {
      jQuery("#master-af-fields-" + formId).hide();
      jQuery("#common-af-fields-" + formId).hide();

      //jQuery("#master-fields-" + formId).show();
      //jQuery("#common-fields-" + formId).show();
    }
  });

  //add all form fields related validation
  jQuery(".save-cf7-form").click(function () {
    var formId = jQuery(this).attr("form-id");

    //Check for the main Enable form is been checked or not
    if (jQuery("#vsz_enable_form_" + formId).is(":checked")) {
      if (
        !jQuery("#cf7mount_enable_form-" + formId).is(":checked") &&
        !jQuery("#cf7mount_af_enable_form-" + formId).is(":checked")
      ) {
        alert(
          "Please enable the form to be submitted; otherwise, if not required, then disable the submission."
        );
        return false;
      }
    }

    //validation for common fields from here

    var checkForm = true;

    //For the API CRM
    if (jQuery("#cf7mount_enable_form-" + formId).is(":checked")) {
      //Get value from common dom
      var errorSection = "#cf7moun_table_" + formId;
      var existDom = jQuery("#cf7moun_table_" + formId).find("tr").length;
      if (existDom >= 1) {
        //check each field value from here
        jQuery("#cf7moun_table_" + formId)
          .find("tr")
          .each(function () {
            var secId = jQuery(this).attr("data-id");
            var fieldApiId = "#cf7mount_api_key_" + formId + "_" + secId;
            var fieldCf7Id = "#cf7mount_cf7_key_" + formId + "_" + secId;
            var objApiKey = jQuery(fieldApiId);
            var objCf7Key = jQuery(fieldCf7Id);
            var apiKey = objApiKey.val();
            var cf7Key = objCf7Key.val();

            var fieldType = objCf7Key.attr("data-fieldType");
            //console.log(fieldType);
            if (typeof fieldType !== typeof undefined && fieldType !== false) {
              return;
            }

            if (apiKey == "" && cf7Key != "") {
              objApiKey.css("border", "1px solid red");
              checkForm = false;
              errorSection = fieldApiId;
            } else {
              objApiKey.css("border", "");
            }

            if (cf7Key == "" && apiKey != "") {
              objCf7Key.css("border", "1px solid red");
              checkForm = false;
              errorSection = fieldCf7Id;
            } else {
              objCf7Key.css("border", "");
            }
          });
      }

      //check value for master DOM
      var existMDom = jQuery("#append_extra_details-" + formId).find(
        ".extra-field-section"
      ).length;

      if (existDom >= 1) {
        //check each fields value form here
        jQuery("#append_extra_details-" + formId)
          .find(".extra-field-section")
          .each(function () {
            var secId = jQuery(this).attr("data-id");
            var fieldMApiId = "#vsz_extra_field_name-" + formId + "-" + secId;
            var fieldSelVId =
              "#vsz_extra_field_value_sel-" + formId + "-" + secId;
            var fieldTxtVId =
              "#vsz_extra_field_value_txt-" + formId + "-" + secId;
            var objApiKey = jQuery(fieldMApiId);
            var objCf7Key = jQuery(fieldSelVId);
            var objTxtKey = jQuery(fieldTxtVId);
            var apiKey = objApiKey.val();
            var cf7Key = objCf7Key.val();
            var txtKey = objTxtKey.val();

            if (apiKey == "" && (cf7Key != "" || txtKey != "")) {
              objApiKey.css("border", "1px solid red");
              checkForm = false;
              errorSection = fieldMApiId;
            } else {
              objApiKey.css("border", "");
            }

            if (cf7Key == "" && txtKey == "" && apiKey != "") {
              objCf7Key.css("border", "1px solid red");
              objTxtKey.css("border", "1px solid red");
              checkForm = false;
              errorSection = fieldSelVId;
            } else {
              objCf7Key.css("border", "");
              objTxtKey.css("border", "");
            }
          });
      }
    }

    //Form API form
    if (jQuery("#cf7mount_af_enable_form-" + formId).is(":checked")) {
      if (jQuery("#af-form-" + formId).val() == "") {
        checkForm = false;
        jQuery("#af-form-" + formId).css("border", "1px solid red");
        alert("Kindly select the form to be synced too.");
      } else {
        jQuery("#af-form-" + formId).css("border", "");
      }

      var errorSection = "#cf7_aura_table_data_" + formId;
      var existDom = jQuery("#cf7_aura_table_data_" + formId).find("tr").length;
      if (existDom >= 1) {
        //check each field value from here
        jQuery("#cf7_aura_table_data_" + formId)
          .find("tr")
          .each(function () {
            var secId = jQuery(this).attr("data-id");
            var fieldApiId = "#cf7aura_form_af_api_key_" + formId + "_" + secId;
            var fieldCf7Id = "#cf7aura_form_cf7_key_" + formId + "_" + secId;
            var objCf7Key = jQuery(fieldCf7Id);
            var cf7Key = objCf7Key.val();
            //console.log(objCf7Key.attr("data-required"));
            if (objCf7Key.attr("data-required") == "true") {
              if (cf7Key == "") {
                objCf7Key.css("border", "1px solid red");
                checkForm = false;
                errorSection = fieldCf7Id;
              } else {
                objCf7Key.css("border", "");
              }
            }
          });
      }
    }
    //checkForm = false;
    //return false;
    //check error flag
    //console.log(checkForm);
    if (checkForm) {
      return true;
    } else {
      //notify error section from here
      jQuery("html, body").animate(
        { scrollTop: jQuery(errorSection).offset().top - 200 },
        1000
      );
      return false;
    }
  });

  //On changing the form get the form fields
  jQuery(".af-form-list-dropdown").on("change", function () {
    // Get the data from the template

    var formId = jQuery(this).data("cf_formid");
    var formVal = jQuery(this).val();
    var nonce = jQuery('#mapping_nonce').val();
    var selCf7Id = "#vsz_af_field_value_sec_" + formId;
    var auraCf7_data = "#cf7_aura_table_data_" + formId;

    //if (formVal !== "") {
    //Get the ajax call and input the data within it with mapping fields
    jQuery("#overlayLoader_" + formId).show();
    jQuery.ajax({
      type: "POST",
      url: ajaxurl, // Assuming ajaxurl is defined in your WordPress environment
      data: {
        action: "vsz_get_af_form_fields_data", // Replace with your actual AJAX action name
        formId: formId,
        formVal: formVal,
        nonce: nonce
        // Add more data if needed
      },
      success: function (response) {
        // Handle the AJAX response here
        //console.log(response);
        jQuery("#overlayLoader_" + formId).hide();
        jQuery(selCf7Id).show();
        jQuery(auraCf7_data).html(response);
      },
      error: function (error) {
        // Handle errors
        console.error(error);
        jQuery("#overlayLoader_" + formId).hide();
      },
    });
    /* }else{
		alert()
		jQuery(auraCf7_data).html("");
		} */
  });

  
  jQuery('body #wpwrap').append('<div class="resend-overlay" ></div>');
  jQuery('.vsz-loader-span').css('display', 'none');

  jQuery(".aura-send-data").click(function () {
    // Get the data from the template
    console.log('click');
    var formId = jQuery(this).data("formid");
    var rowId = jQuery(this).data("rid");
    var nonce = jQuery(this).data("nonce");
    
    //if (formVal !== "") {
    //Get the ajax call and input the data within it with mapping fields
    jQuery('.resend-overlay').css('display', 'block');
    jQuery(this).parent().find('.vsz-loader-span').css('display', 'block');
    jQuery(this).css('display', 'none');

    jQuery.ajax({
      type: "POST",
      url: ajaxurl, // Assuming ajaxurl is defined in your WordPress environment
      data: {
        action: "vsz_send_pending_aura_data", // Replace with your actual AJAX action name
        formId: formId,
        rowId: rowId,
        nonce: nonce
        // Add more data if needed
      },
      success: function (response) {
        console.log(response);
        var response = JSON.parse(response);
        // Handle the AJAX response here
        
        jQuery('.resend-overlay').css('display', 'none');
        jQuery(this).parent().find('.vsz-loader-span').css('display', 'none');
        jQuery(this).css('display', 'block');
        // jQuery("#overlayLoader_" + formId).hide();
        // jQuery(selCf7Id).show();
        // jQuery(auraCf7_data).html(response);
        if(response['status'] === 'success'){
          alert(response['message']);
          setTimeout(function(){
              window.location.reload();
            }, 500);
        } else {
          alert(response['message']);
          setTimeout(function(){
              window.location.reload();
            }, 500);
        }
      },
      error: function (error) {
        // Handle errors
        console.error(error);
        setTimeout(function(){
          window.location.reload();
        }, 500);
      },
    });
    /* }else{
		alert()
		jQuery(auraCf7_data).html("");
		} */
  }); 



});

//define add DOM section from here
function addExtraSection(fId) {
  //Get value from dom
  var domCount = parseInt(jQuery("#domCount-" + fId).val()) + 1;

  //Set value from dom
  jQuery("#domCount-" + fId).val(domCount);

  jQuery("#service_bck_loader").show();
  // Get the data from the template
  var form_id = fId;
  var rowCount = domCount;

  var wrapper = "#append_extra_details-" + fId;
  jQuery.ajax({
    url: cf72aut_admin_action.ajax_url,
    type: "POST",
    data: {
      action: "cf7_form_fields_template",
      row_count: rowCount,
      form_id: form_id,
      dom_field_name: "master",
    },
    success: function (data) {
      //Loader to hide
      jQuery("#service_bck_loader").hide();
      jQuery(wrapper).append(data);
      jQuery(".master-opt-field").on("change", function () {
        // Get the data from the template
        var formId = jQuery(this).attr("form-id");
        var secId = jQuery(this).attr("data-id");
        var selCf7Id = "#vsz_extra_field_value_sel-" + formId + "-" + secId;
        var txtFieldId = "#vsz_extra_field_value_txt-" + formId + "-" + secId;
        var objSelCf7 = jQuery(selCf7Id);
        var objTxtField = jQuery(txtFieldId);
        var cf7Val = objSelCf7.val();
        var txtVal = objTxtField.val();

        if (cf7Val != "") {
          objTxtField.val("");
          objTxtField.attr("readonly", true);
          objTxtField.addClass("disable");
        } else if (objTxtField.attr("readonly")) {
          objTxtField.removeAttr("readonly");
          objTxtField.removeClass("disable");
        }
      });
    },
    error: function (e) {
      console.log(e);
      jQuery("#service_bck_loader").hide();
    },
  });
}

//delete master DOM section from here
function removeSection(fId, secId) {
  if (confirm("Are you sure about delete 'This Section'?")) {
    jQuery("#extra-field-sec-" + fId + "-" + secId).remove();
    //Get value from dom
    var existDom = jQuery("#append_extra_details-" + fId).find(
      ".extra-field-section"
    ).length;
    if (existDom == 0) {
      addExtraSection(fId);
      var domCount = parseInt(jQuery("#domCount-" + fId).val());
      jQuery("#extra-field-sec-" + fId + "-" + secId)
        .find(".delete-section")
        .css("display", "none");
    }
  }
}

//define add DOM section from here
function addField(fId) {
  jQuery("#service_bck_loader").show();
  var nonce = jQuery('#mapping_nonce').val();
  // Get the data from the template
  var form_id = fId;
  var rowCount = jQuery("#cf7moun_table_" + form_id + " tr").length;
  if (rowCount != 1) {
    rowCount = jQuery("#cf7moun_table_" + form_id + " tr").length - 1;
  }

  var wrapper = "#cf7moun_table_" + form_id;
  jQuery.ajax({
    url: cf72aut_admin_action.ajax_url,
    type: "POST",
    data: {
      action: "cf7_form_fields_template",
      row_count: rowCount,
      form_id: form_id,
      nonce: nonce,
    },
    success: function (data) {
      //console.log(data);
      //Loader to hide
      jQuery("#service_bck_loader").hide();
      jQuery(wrapper).append(data);

      //Animate to top
      rowCount++;
    },
    error: function (e) {
      console.log(e);
      jQuery("#service_bck_loader").hide();
    },
  });
}

//delete master DOM section from here
function removeField(fId, secId) {
  if (confirm("Are you sure you want to delete this field?") == true) {
    var formId = fId;

    var secId = secId;

    jQuery("#field-" + formId + "-" + secId).remove();
    //Get value from dom
    var existDom = jQuery("#cf7moun_table_" + formId).find("tr").length;
    if (existDom == 1) {
      addField(formId);
    }
  }
}
//define add DOM section from here
function addNoteField(fId, rCount) {
  jQuery("#service_bck_loader").show();
  var nonce = jQuery('#mapping_nonce').val();
  // Get the data from the template
  var form_id = fId;
  var rowCount = rCount;
  var fieldSecCount = jQuery(
    "#inner-dom-" + form_id + "-" + rowCount + " .field-dom"
  ).length;

  var wrapper = "#inner-dom-" + form_id + "-" + rowCount;
  jQuery.ajax({
    url: cf72aut_admin_action.ajax_url,
    type: "POST",
    data: {
      action: "cf7_form_fields_template",
      row_count: rowCount,
      form_id: form_id,
      field_sec_count: fieldSecCount,
      dom_field_name: "note",
      nonce: nonce
    },
    success: function (data) {
      //Loader to hide
      jQuery("#service_bck_loader").hide();
      jQuery(wrapper).append(data);

      //Animate to top
      rowCount++;
    },
    error: function (e) {
      console.log(e);
      jQuery("#service_bck_loader").hide();
    },
  });
}

//delete master DOM section from here
function removeNoteField(fId, secId, fSecId) {
  if (confirm("Are you sure you want to delete this field?") == true) {
    var formId = fId;
    var secId = secId;
    var fieldSecId = fSecId;
    jQuery("#field-dom-" + formId + "-" + secId + "-" + fieldSecId).remove();
    //Get value from dom
    var existDom = jQuery(
      "#inner-dom-" + formId + "-" + secId + " .field-dom"
    ).length;
    if (existDom == 0) {
      addNoteField(formId, secId);
    }
  }
}

//Load the aurastride form directly with the saved data
function loadAFformFields(formId = "") {
  var selCf7Id = "#vsz_af_field_value_sec_" + formId;
  var auraCf7_data = "#cf7_aura_table_data_" + formId;
  var nonce = jQuery('#mapping_nonce').val();
  //if (formVal !== "") {
  //Get the ajax call and input the data within it with mapping fields
  jQuery("#overlayLoader_" + formId).show();
  jQuery.ajax({
    type: "POST",
    url: ajaxurl, // Assuming ajaxurl is defined in your WordPress environment
    data: {
      action: "vsz_get_af_form_saved_fields_data", // Replace with your actual AJAX action name
      formId: formId,
      nonce: nonce
      //formVal: formVal,
      // Add more data if needed
    },
    success: function (response) {
      // Handle the AJAX response here
      //console.log(response);
      jQuery("#overlayLoader_" + formId).hide();
      jQuery(selCf7Id).show();
      jQuery(auraCf7_data).html(response);
    },
    error: function (error) {
      // Handle errors
      console.error(error);
      jQuery("#overlayLoader_" + formId).hide();
    },
  });
}

	//For submitting the old data to aurastride that was not been send
	 //On changing the form get the form fields
  
	

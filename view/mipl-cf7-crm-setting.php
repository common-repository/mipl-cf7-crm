<?php
   
    if ( ! defined( 'ABSPATH' ) ) exit;

    $field_mapping_form = get_post_meta( $post->ID, 'mipl_cf7_crm_field_mapping_form', true );

    $crm_form_submission_settings = get_post_meta( $post->ID, '_mipl_cf7_crm_settings', true );
    $crm_form_submission = !empty($crm_form_submission_settings['crm_form_submission']) ? $crm_form_submission_settings['crm_form_submission'] : array();
    $crm_form_fields_name = !empty($crm_form_submission_settings['crm_form_fields_name']) ? $crm_form_submission_settings['crm_form_fields_name'] : array();

    $crm_form_fields_value = !empty($crm_form_submission_settings['crm_form_fields_value']) ? $crm_form_submission_settings['crm_form_fields_value'] : array();
    
    $submission_on = !empty($crm_form_submission_settings['crm_submission_on']) ? $crm_form_submission_settings['crm_submission_on'] : array();

    $lead_collection = !empty($crm_form_submission_settings['crm_store_lead']) ? $crm_form_submission_settings['crm_store_lead'] : array(); 

    //error message
    $form_submission_error_msg = isset($crm_form_submission_settings['errors']['crm_form_submission']) ? $crm_form_submission_settings['errors']['crm_form_submission'] : "";

    $fields_name_error_msg = isset($crm_form_submission_settings['errors']['crm_form_fields_name']) ? $crm_form_submission_settings['errors']['crm_form_fields_name'] : "";

    $fields_value_error_msg = isset($crm_form_submission_settings['errors']['crm_form_fields_value']) ? $crm_form_submission_settings['errors']['crm_form_fields_value'] : "";

    $submission_on_error_msg = isset($crm_form_submission_settings['errors']['crm_submission_on']) ? $crm_form_submission_settings['errors']['crm_submission_on'] : "";
    
    $check_submit = "";
    $check_mail   = "checked";
    if(is_array($crm_form_submission_settings)){
        if( in_array('wpcf7_submit', $submission_on) ){
            $check_submit = "checked";
        }
        if( in_array('wpcf7_mail_sent', $submission_on) ){
            $check_mail = "checked";
        }else{
            $check_mail = "";
        }
    }
    

    $cf7_form_ids = array();
    $data = get_posts(array('post_type' => 'wpcf7_contact_form'));
    if($data){
        foreach($data as $cf_data){
            $cf7_form_id = $cf_data->ID;
            $cf7_form_ids[] = $cf7_form_id; 
        }
    }
    $crm_posts = get_posts(array('post_type' => 'mipl_cf7_crm', 'post_status' => 'publish'));
    $crm_form_submitted = array();
    foreach ($crm_posts as $key => $post_data) {
        $id = $post_data->ID;
        $crm_form_submission_settings = get_post_meta( $id, '_mipl_cf7_crm_settings', true );
        $crm_form_submitted_id = !empty($crm_form_submission_settings['crm_form_submission']) ? implode('', $crm_form_submission_settings['crm_form_submission']) : '';
       
        if( in_array($crm_form_submitted_id, $crm_form_submission)){
            continue;
        }
        $crm_form_submitted[] = $crm_form_submitted_id;
    }
    
    $cf7_form_ids = array_diff($cf7_form_ids, $crm_form_submitted);

    $cf7_field_names = [];
    $cf7_field_values = [];
    if (!empty($crm_form_submission[0])) {
        
        $ContactForm = WPCF7_ContactForm::get_instance( $crm_form_submission[0]);
        $form_fields = $ContactForm->scan_form_tags();
        foreach ($form_fields as $fields) {
            if($fields['basetype'] == 'select' || $fields['basetype'] == 'checkbox' || $fields['basetype'] == 'radio'){
                $options_data = array();
                $options_value = $fields['raw_values'];
                foreach($options_value as $option_key => $option_value){
                    if(strpos($option_value,"|")){
                        $tmp = explode('|',trim($option_value));
                        if(!empty($tmp[0] && !empty($tmp[1]))){
                            $options_data[trim($tmp[0])] = trim($tmp[1]);
                        }
                    }else{
                        if(!empty($option_value)){
                            $options_data[$option_value] = $option_value;
                        }
                    }
                }
                $cf7_field_names[] = $fields['name'];
                $cf7_field_values[$fields['name']] = $options_data;
            }
        }

    }

    // Nonce creation
    $mipl_form_nonce = wp_create_nonce('mipl_cf7_crm'.$post->ID);


?>
<div class="mipl_cf7_crm_form_data_row mipl_cf7_CRM_setting_data">
 
    <div class="mipl_cf7_form_nonce">
        <input type="hidden" class="mipl_nonce" name="mipl_cf7_crm_nonce" value="<?php echo esc_attr($mipl_form_nonce) ?>">
        <input type="hidden" class="mipl_cf7_post_id" name="mipl_cf7_post_id" value="<?php echo esc_attr($post->ID) ?>">
    </div>

    <div class="mipl_cf7_CRM_setting_data_item mipl_cf7_required_field">
        <label><span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Select Form:') ?></b></span>
            <select name="mipl_cf7_crm_submission_setting[crm_form_submission][]" class="mipl_cf7_select2 mipl_crm_form_submission" id="mipl_select_form" data-value="<?php echo implode(',',$crm_form_submission);?>">
                <option value="">--<?php esc_html_e('Select') ?>--</option>
                <?php
                foreach($cf7_form_ids as $form_id){
                    $select = "";
                    if(!empty($crm_form_submission) && in_array($form_id, $crm_form_submission)){
                        $select = "selected";
                    }
                    ?>
                <option value="<?php echo esc_attr($form_id) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html(ucwords(get_the_title($form_id))) ?></option>
                <?php
                }
                ?>
            </select>
            <p class="mipl_crm_error_msg" ><?php echo esc_html($form_submission_error_msg) ?></p>
        </label>
        <input type="hidden" class="mipl_cf7_hidden_form_data" value="">
    </div>

    <fieldset class="mipl_cf7_crm_depended_setting_field">
        <legend><?php esc_html_e('CRM Submit on particular Field value:') ?></legend>
        <div class="mipl_cf7_CRM_setting_data_item">
            <label><span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Select Field:') ?></b></span>
                <select name="mipl_cf7_crm_submission_setting[crm_form_fields_name][]" class="mipl_form_fields_name mipl_cf7_select2">
                    <option value=""> --<?php esc_html_e('Select') ?>--</option>
                    <?php
                        foreach ($cf7_field_names as $name) {
                            $selected = "";
                            if($name == $crm_form_fields_name[0]){
                                $selected = "selected";
                            }
                            ?>
                            <option value="<?php echo esc_attr($name) ?>" <?php echo esc_attr($selected) ?>><?php echo esc_html($name) ?></option>
                            <?php
                        }
                    ?>
                </select>
                <span class="mipl_crm_error_msg" ><?php echo esc_html($fields_name_error_msg) ?></span>
            </label>
            <em style="color:gray"><b><?php echo esc_attr('Note: ') ?></b><?php echo esc_attr('This filter work on select, checkbox, radio fields.') ?></em>
        </div>
        
        <div class="mipl_cf7_CRM_setting_data_item">
            <label><span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Select Field Value:') ?></b></span>
                <select name="mipl_cf7_crm_submission_setting[crm_form_fields_value][]" class="mipl_form_fields_value mipl_cf7_select2">
                    <option value=""> --<?php esc_html_e('Select') ?>-- </option>
                    <?php

                        foreach ($cf7_field_values[$crm_form_fields_name[0]] as $key=>$value) {
                            $selected = "";
                            if($value == $crm_form_fields_value[0]){
                                $selected = "selected";
                            }
                            ?>
                            <option value="<?php echo esc_attr($value) ?>" <?php echo esc_attr($selected) ?> class="mipl_cf7_fields_values"><?php echo esc_html($key) ?></option>
                            <?php
                        }
                    ?>
                </select>
                <span class="mipl_crm_error_msg" ><?php echo esc_html($fields_value_error_msg) ?></span>
            </label>
        </div>
    </fieldset>

    <div class="mipl_cf7_CRM_setting_data_item mipl_cf7_required_field">
        <label><span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('CRM Submit On') ?></b></span><br>
            <label>
                <input type="checkbox" name="mipl_cf7_crm_submission_setting[crm_submission_on][]" value="wpcf7_submit" <?php echo esc_attr($check_submit) ?>> <?php esc_html_e('Form Submission') ?>
            </label><br>
            <label>
                <input type="checkbox" name="mipl_cf7_crm_submission_setting[crm_submission_on][]" value="wpcf7_mail_sent" <?php echo esc_attr($check_mail) ?>> <?php esc_html_e('Mail Sent') ?>
            </label><br>
            <p class="mipl_crm_error_msg" ><?php echo esc_html($submission_on_error_msg) ?></p>
        </label>
    </div>

    <div class="mipl_cf7_CRM_setting_data_item">
        <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Lead Collection') ?></b></span><br>
        <label>
            <?php
                $lead_check = '';
                if(isset($lead_collection[0]) && !empty($lead_collection[0])){
                    $lead_check = 'checked';
                }
            ?>
            <input type="checkbox" name="mipl_cf7_crm_submission_setting[crm_store_lead][]" value="true" <?php echo esc_attr($lead_check) ?>><?php esc_html_e('Store Leads') ?>
        </label>

    </div>
</div>

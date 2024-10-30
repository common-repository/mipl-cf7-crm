<?php

    if ( ! defined( 'ABSPATH' ) ) exit;

    global $post;
    $crm_default_data  = get_post_meta( $post->ID, '_mipl_cf7_crm_default_data', true );
    $crm_form_data     = get_post_meta( $post->ID, '_mipl_cf7_crm_form_data', true );
    $crm_form_submission_settings = get_post_meta( $post->ID, '_mipl_cf7_crm_settings', true );
    $form_id = !empty($crm_form_submission_settings['crm_form_submission']) ? $crm_form_submission_settings['crm_form_submission'] : array();

    $field_mapping_form = get_post_meta( $post->ID, 'mipl_cf7_crm_field_mapping_form', true );
   
    $all_cf7_fields    = array();
   
    if(!empty($form_id[0])){
        $ContactForm = WPCF7_ContactForm::get_instance( $form_id[0]  );
        $form_fields = $ContactForm->scan_form_tags();
        foreach ($form_fields as $fields) {
            if(!empty($fields['name'])){
                $all_cf7_fields[$form_id[0]]['name'][] = $fields['name'];
                $all_cf7_fields[$form_id[0]]['type'][] = $fields['basetype'];
            } 
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
       
        if( in_array($crm_form_submitted_id,  $form_id )){
            continue;
        }
        $crm_form_submitted[] = $crm_form_submitted_id;
    }
    $cf7_form_ids = array_diff($cf7_form_ids, $crm_form_submitted);
    
    
?>
<div class="mipl_cf7_crm mipl_cf7_crm_main_form">
    <div>
        <label><b><?php esc_html_e('Select Form For Field Mapping') ?>:</b></label><br>
        <select name="mipl_cf7_crm_field_mapping_form" class="mipl_cf7_select2 mipl_crm_select_form" data-value="<?php echo esc_attr($field_mapping_form);?>">
            <option value=""><?php esc_html_e('Select') ?></option>
            <?php
            foreach($cf7_form_ids as $form_id){
                $select = "";
                if($form_id == $field_mapping_form){
                    $select = "selected";
                }
                ?>
            <option value="<?php echo esc_attr($form_id) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html(ucwords(get_the_title($form_id))) ?></option>
            <?php
            }
            ?>
        </select>
        <button class="mipl_cf7_add_form_fields" style="padding:5.38px"><?php esc_html_e("Add All Fields",'mipl-cf7-crm') ?></button>
    </div>
    <div class="mipl_crm_form_data_table">
      
        <h3><?php esc_html_e('CRM Fields') ?></h3>
        
        <div class="mipl_cf7_crm_form_data_row">
            <div class="crm_form_data_col crm_form_heading">
                <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('CRM Fields') ?></b></span>
            </div>
            <div class="crm_form_data_col crm_form_heading">
                <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('CF7 Fields') ?></b></span>
            </div>
            <div class="crm_form_data_col crm_form_heading">
                <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Fields Data Type') ?></b></span>
            </div>
            <div class="crm_form_data_col crm_form_heading form_data_remove">
                <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Action') ?></b></span>
            </div>
        </div>
        
        <?php
        
        $cf7_date_formate = array('Default'=>'Default','Y-m-d'=>'Y-m-d','Y-m-d H:i:s'=>'Y-m-d H:i:s','Y/m/d H:i:s'=>'Y/m/d H:i:s','m/d/Y H:i:s'=>'m/d/Y H:i:s','d/m/Y H:i:s'=>'d/m/Y H:i:s');
        $file_type = array('file_object'=>'File Object', 'file_url'=>'File URL');
        if(isset($crm_form_data['CRM_fields'])){
            
            foreach($crm_form_data['CRM_fields'] as $position => $value){
                $crm_field       = !empty($crm_form_data['CRM_fields'][$position]) ? $crm_form_data['CRM_fields'][$position] : "";
                $cf7_field        = !empty($crm_form_data['cf7_fields'][$position]) ? $crm_form_data['cf7_fields'][$position] : "";
                $field_data_type = !empty($crm_form_data['data_type'][$position]) ? $crm_form_data['data_type'][$position] : "";
                $crm_field_error_msg  = isset($crm_form_data['errors']['CRM_fields'][$position]) ? $crm_form_data['errors']['CRM_fields'][$position] : "";
                $cf7_field_error_msg   = isset($crm_form_data['errors']['cf7_fields'][$position]) ? $crm_form_data['errors']['cf7_fields'][$position] : "";
                $type_field_error_msg = isset($crm_form_data['errors']['data_type'][$position]) ?$crm_form_data['errors']['data_type'][$position] : "";
                $data_types = array('text' => 'Text');
                if(in_array($field_data_type, $cf7_date_formate)){
                    $data_types = $cf7_date_formate;
                }elseif(in_array($field_data_type, array_keys($file_type))){
                    $data_types = $file_type;
                }
               
            ?>
            <div class="mipl_cf7_crm_form_data_row">
                <div class="crm_form_data_col CRM_fields">
                    <input type="text"  name="mipl_cf7_crm_form_data[CRM_fields][]" value="<?php echo esc_attr($crm_field) ?>" placeholder="<?php echo esc_attr('Enter CRM Field') ?>" style="width:100%">
                    <span class="mipl_crm_error_msg" ><?php echo esc_html($crm_field_error_msg) ?></span>
                </div>
                <div class="crm_form_data_col wp_fields mipl_cf7_form_fld_select2">    
                    <select name="mipl_cf7_crm_form_data[cf7_fields][]" data-allow-clear=true class="form-control  mipl_cf7_fields select2_wc_fields">
                        <option value=""><?php esc_html_e('Select cf7 fields') ?></option>
                        <?php
                        foreach ($all_cf7_fields as $cf7_form_id=>$form_data) {
                           
                        ?>
                        <optgroup label = "<?php echo esc_attr(ucwords(get_the_title($cf7_form_id))) ?>">
                            <?php
                            $data_value = "";
                            foreach ($form_data['name'] as $pos => $name) {
                                $select = "";
                                if($cf7_field == $cf7_form_id.'/'.$name){
                                    $select = "selected";
                                }
                            ?>
                            <option value="<?php echo esc_html($cf7_form_id.'/'.$name)  ?>" <?php echo esc_attr($select) ?> data-type="<?php echo esc_attr($form_data['type'][$pos]) ?>"><?php echo esc_html($name) ?></option>
                            <?php
                            } 
                            ?>
                        </optgroup>
                        <?php
                            
                        }
                        ?>
                    </select>
                    <span class="mipl_crm_error_msg" ><?php echo esc_html($cf7_field_error_msg) ?></span>
                </div>
                <div class="crm_form_data_col mipl_cf7_form_fields_data_type">
                    <select name="mipl_cf7_crm_form_data[data_type][]" style="width:100%">
                        <?php
                       
                        foreach($data_types as $key=>$val){
                            $select = "";
                            if($field_data_type == $key){
                                $select = "selected";
                            }
                        ?>
                        <option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html($val) ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <span class="mipl_crm_error_msg" ><?php echo esc_html($type_field_error_msg) ?></span>
                </div>
                <div class="crm_form_data_col mipl_cf7_form_fields_data_type form_data_remove" >
                    <a class="mipl_cf7_remove_fields_button button"><?php esc_html_e('Remove') ?></a>
                </div>
            </div>
            <?php
            }
        }
        ?>
    </div>
    <div class="mipl_cf7_add_crm_form_fields mipl_cf7_crm_item">
        <a class="mipl_cf7_crm_form_fields_button button"><?php esc_html_e('+Add More') ?></a>
    </div>
    <hr>


    <div class="mipl_crm_default_fields">
        <h3><?php esc_html_e('Static Fields') ?></h3>
        <div class="crm_default_fields_table">
            <div class="mipl_cf7_crm_form_data_row mipl_cf7_crm_default_fields_data crm_default_fields_heading">
                <div class="crm_form_data_col mipl_cf7_crm_default_fields_item">
                    <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Key') ?></b></span>
                </div>
                <div class="crm_form_data_col mipl_cf7_crm_default_fields_item">
                    <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Value') ?></b></span>
                </div>
                <div class="crm_form_data_col crm_default_fields_action_item form_data_remove">
                    <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Action') ?></b></span>
                </div>
            </div>
            <?php 
            if(isset($crm_default_data['crm_default_field_keys'])){
                foreach($crm_default_data['crm_default_field_keys'] as $position => $value){
                    $static_field_keys_error = isset($crm_default_data['errors']['crm_default_field_keys'][$position]) ? $crm_default_data['errors']['crm_default_field_keys'][$position] : "";
                    $static_field_value_error = isset($crm_default_data['errors']['crm_default_field_values'][$position]) ? $crm_default_data['errors']['crm_default_field_values'][$position] : "";
                ?>
                <div class="mipl_cf7_crm_form_data_row mipl_cf7_crm_default_fields_data">
                    <div class="mipl_cf7_crm_default_fields_item">
                        <input class="mipl_crm_default_fields" name="mipl_cf7_crm_default_fields[crm_default_field_keys][]" type="text" value="<?php echo esc_attr($crm_default_data['crm_default_field_keys'][$position]) ?>" >
                        <span class="mipl_crm_error_msg" ><?php echo esc_html($static_field_keys_error) ?></span>
                    </div>
                    <div class="mipl_cf7_crm_default_fields_item">
                        <input class="mipl_crm_default_fields" type="text" name="mipl_cf7_crm_default_fields[crm_default_field_values][]" value="<?php echo esc_attr($crm_default_data['crm_default_field_values'][$position]) ?>" >
                        <span class="mipl_crm_error_msg" ><?php echo esc_html($static_field_value_error) ?></span>
                    </div>
                    <div class="mipl_cf7_crm_default_fields_item crm_default_fields_action_item remove_extra_fields" >
                        <a class="mipl_cf7_remove_default_fields_button button"><?php esc_html_e('Remove') ?></a>
                    </div>
                </div>
                <?php
                }
            }
            ?>

        </div>

        <div class="add_crm_default_fields mipl_cf7_crm_item">
            <a class="mipl_crm_default_fields_button button"><?php esc_html_e('+Add More') ?></a>
        </div>
                
    </div>
</div>


<script id="crm_add_more_row" type="template/text">

    <div class="mipl_cf7_crm_form_data_row">
        <div class="crm_form_data_col CRM_fields">
            <input type="text" name="mipl_cf7_crm_form_data[CRM_fields][]" value="" placeholder="<?php echo esc_attr('Enter CRM Field') ?>" style="width:100%">
        </div>
        <div class="crm_form_data_col wp_fields mipl_cf7_form_fld_select2">
            <select name="mipl_cf7_crm_form_data[cf7_fields][]" data-allow-clear=true class="form-control select2_wc_fields mipl_cf7_fields mi_cf7_crm_field">
                <option value="">--<?php esc_html_e('select field') ?>--</option>
                <?php
                foreach ($all_cf7_fields as $cf7_form_id=>$form_data) {
                    
                ?>
                <optgroup label = "<?php echo esc_attr(ucwords(get_the_title($cf7_form_id))) ?>">
                    <?php
                    $data_value = "";
                    foreach ($form_data['name'] as $pos => $name) {
                        
                    ?>
                    <option value="<?php echo esc_html($cf7_form_id.'/'.$name)  ?>" data-type="<?php echo esc_attr($form_data['type'][$pos]) ?>"><?php echo esc_html($name) ?></option>
                    <?php
                    } 
                    ?>
                </optgroup>
                <?php
                    
                }
                ?>
            </select>
        </div>
        <div class="crm_form_data_col mipl_cf7_form_fields_data_type">
            <select name="mipl_cf7_crm_form_data[data_type][]" style="width:100%">
                <?php
                $data_types = array('text'=>'Text');
                foreach($data_types as $val=>$label){
                ?>
                <option value="<?php echo esc_attr($val) ?>"><?php echo esc_html($label) ?></option>
                <?php
                }
                ?>
            </select>
        </div>
        <div class="crm_form_data_col mipl_cf7_form_fields_data_type form_data_remove" >
            <a class="mipl_cf7_remove_fields_button button"><?php echo esc_html("Remove") ?></a>
        </div>
    </div>
</script>

<script id="crm_default_fields" type="template/text">
    <div class="mipl_cf7_crm_form_data_row mipl_cf7_crm_default_fields_data">
        <div class="mipl_cf7_crm_default_fields_item">
            <input class="mipl_crm_default_fields" name="mipl_cf7_crm_default_fields[crm_default_field_keys][]" type="text" value="">
        </div>
        <div class="mipl_cf7_crm_default_fields_item">
            <input class="mipl_crm_default_fields" type="text" name="mipl_cf7_crm_default_fields[crm_default_field_values][]" value="">
        </div>
        <div class="mipl_cf7_crm_default_fields_item crm_default_fields_action_item remove_extra_fields" >
            <a class="mipl_cf7_remove_default_fields_button button"><?php echo esc_html("Remove") ?></a>
        </div>
    </div>
</script>

<script id="mipl_cf7_static_fields_label" type="template/text">
    <h3><?php esc_html_e('CRM Fields') ?></h3>
    <div class="mipl_cf7_crm_form_data_row">
        <div class="crm_form_data_col crm_form_heading">
            <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('CRM Fields') ?></b></span>
        </div>
        <div class="crm_form_data_col crm_form_heading">
            <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('CF7 Fields') ?></b></span>
        </div>
        <div class="crm_form_data_col crm_form_heading">
            <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Fields Data Type') ?></b></span>
        </div>
        <div class="crm_form_data_col crm_form_heading form_data_remove">
            <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Action') ?></b></span>
        </div>
    </div>
</script>

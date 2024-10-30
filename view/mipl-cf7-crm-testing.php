<?php

    if ( ! defined( 'ABSPATH' ) ) exit;

    add_thickbox();
    global $post;

    if(!isset($post->ID) && empty($post->ID)){ return false; }

    $crm_default_data  = get_post_meta( $post->ID, '_mipl_cf7_crm_default_data', true );
    $crm_form_data     = get_post_meta( $post->ID, '_mipl_cf7_crm_form_data', true );
               
    $crm_email_data  = get_post_meta($post->ID, '_mipl_cf7_crm_email_setting', true);
    $crm_email_configuration  = get_post_meta($post->ID, '_mipl_cf7_crm_email_configuration', true);
      
?>

<div id="mipl_cf7_modal" class="mipl_cf7_popup_modal" style="display:none">
    <div class="mipl_cf7_popup_dialog mipl_cf7_popup_small">
        <div class="mipl_cf7_popup_content">
            <form method="post" id="mipl_cf7_add_role_setting" style="width:100%;" enctype="multipart/form-data">
                <div class="mipl_crm_testing_item">
                    <div class="mipl_crm_form_data_table">
                        <h3><?php esc_html_e('CRM Fields') ?></h3>
                        <div class="mipl_crm_form_data_row">
                            <div class="crm_form_data_col crm_form_heading">
                                <span class="mipl_cf7_cf_fields_label"><strong><?php esc_html_e('CRM Fields') ?></strong></span>
                            </div>
                            <div class="crm_form_data_col crm_form_heading">
                                <span class="mipl_cf7_cf_fields_label"><strong><?php esc_html_e('CF7 Fields') ?></strong></span>
                            </div>
                            <div class="crm_form_data_col crm_form_heading">
                                <span class="mipl_cf7_cf_fields_label"><strong><?php esc_html_e('Field value') ?></strong></span>
                            </div>
                        </div>
                            
                        <?php
                        if(isset($crm_form_data['CRM_fields'])){
                            foreach($crm_form_data['CRM_fields'] as $position => $value){
                                $crm_field       = !empty($crm_form_data['CRM_fields'][$position]) ? $crm_form_data['CRM_fields'][$position] : "";
                                $cf7_field        = !empty($crm_form_data['cf7_fields'][$position]) ? $crm_form_data['cf7_fields'][$position] : "";
                                $field_data_type = !empty($crm_form_data['data_type'][$position]) ? $crm_form_data['data_type'][$position] : "";
                                $cf7_field = explode('/', $cf7_field);
                                $form_name = is_numeric($cf7_field[0]) ? get_the_title($cf7_field[0]) : '';
                                $field_name = !empty($cf7_field[1]) ? $cf7_field[1] : '';
                                $date_type = ['Default','Y-m-d','Y-m-d H:i:s','Y/m/d H:i:s','m/d/Y H:i:s','d/m/Y H:i:s'];
                                $file_type = ['file_object','file_url'];
                        ?>
                        <div class="mipl_crm_form_data_row">
                            <div class="crm_form_data_col CRM_fields">
                                <label><?php echo esc_html($crm_field) ?></label>
                            </div>
                            <div class="crm_form_data_col wp_fields mipl_cf7_form_fld_select2">    
                                <label>
                                    <?php echo esc_html($field_name) ?>
                                    <span style="font-size:11px; color:#ccc;"> (<?php echo esc_html($form_name) ?>)</span>
                                </label>
                            </div>
                            <div class="crm_form_data_col mipl_cf7_form_fields_data_type">
                                <?php
                                    if(in_array($field_data_type, $date_type)){
                                        ?>
                                        <input type="date" name="mipl_cf7_crm_data[<?php echo esc_attr($crm_field) ?>]" value="">
                                        <?php
                                    }elseif (in_array($field_data_type, $file_type)) {
                                        ?>
                                        <input type="file" name="mipl_cf7_crm_data[<?php echo esc_attr($crm_field) ?>]" value="">
                                        <?php
                                    }else{
                                        ?>
                                        <textarea type="text"  name="mipl_cf7_crm_data[<?php echo esc_attr($crm_field) ?>]" value="" placeholder="<?php echo esc_attr('Enter Value') ?>" style="width:100%"></textarea>
                                        <?php
                                    }
                                ?>
                                
                            </div>
                        </div>
                        <?php
                            }
                        }
                        ?>
                            
                    </div>
                </div>
                <div class="mipl_crm_testing_item">
                    <div class="mipl_crm_form_data_table mipl_cf7_testing_submit_button">
                        <?php
                        if(!empty($crm_default_data['crm_default_field_keys'])){
                        ?>
                        <h3><?php esc_html_e('Static Fields') ?></h3>

                        <div class="mipl_crm_form_data_row">
                            <div class="crm_form_data_col crm_form_heading">
                                <span class="mipl_cf7_cf_fields_label"><strong><?php esc_html_e('key') ?></strong></span>
                            </div>
                            <div class="crm_form_data_col crm_form_heading">
                                <span class="mipl_cf7_cf_fields_label"><strong><?php esc_html_e('value') ?></strong></span>
                            </div>
                            
                        </div>
                            
                        <?php 
                        foreach ($crm_default_data['crm_default_field_keys'] as $key => $value) {
                            $field_keys = isset($crm_default_data['crm_default_field_keys'][$key])?$crm_default_data['crm_default_field_keys'][$key]:"";
                            $field_values = isset($crm_default_data['crm_default_field_values'][$key])?$crm_default_data['crm_default_field_values'][$key]:"";
                        ?>
                        <div class="mipl_crm_form_data_row mipl_cf7_static_fields">
                            <div class="crm_form_data_col CRM_fields">
                            <span>
                                <?php echo esc_attr($field_keys) ?>
                            </span>
                              
                            </div>
                            <div class="crm_form_data_col wp_fields mipl_cf7_form_fld_select2">    
                                <label><input type="text" name="mipl_cf7_static_fields[<?php echo esc_attr($field_keys) ?>]" value="<?php echo esc_attr($field_values) ?>"></label>
                            </div>
                        </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <?php
                if(isset($crm_email_data['enable_email']) && $crm_email_data['enable_email'] == '1' && $crm_email_configuration == 'no'){
                ?>
                <div class="mipl_crm_testing_item" style="padding-bottom:20px">
                    <label>
                        <input type="checkbox" name="mipl_cf7_crm_enable_email" value="1">
                        <?php esc_html_e('Send notification email') ?>
                    </label>
                </div>
                <?php
                }
                ?>
                <div class="mipl_testing_submission_message">

                </div>
                <div class="mipl_crm_testing_item">
                    <a class="mipl_testing_data_submission"><?php esc_html_e('Submit') ?></a>
                </div>
                
            </form>
        </div>
    </div>
</div>



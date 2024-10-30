<?php

    $crm_lead_data =  get_post_meta($post->ID, '_mipl_lead_collection_data', true);
    $cf7_submitted_data = isset($crm_lead_data['cf7_submitted_data']) ? $crm_lead_data['cf7_submitted_data'] : '';
    $wp_file_type = wp_get_mime_types();
?>

<div class="mipl_cf7_crm_lead">
    <div class="mipl_cf7_crm_lead_table">
        <?php
            if (!empty($cf7_submitted_data)) {
                foreach ($cf7_submitted_data as $fld_key => $fld_value) {
                    $note = '';
                    if(is_string($fld_value)){
                        $file_type = wp_check_filetype($fld_value);
                        if(in_array($file_type['type'], $wp_file_type)){
                            $note = "File preview not found, Because this field not in crm field mapping";
                        }
                    }
                    if(is_array($fld_value)){
                        $fld_value = implode(', ', $fld_value);
                    }
                    ?>
                        <div class="mipl_cf7_crm_lead_row">
                            <div class="mipl_cf7_crm_lead_col label">
                                <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e($fld_key) ?> : </b></span>
                            </div>
                            <div class="mipl_cf7_crm_lead_col">
                                <span class="mipl_cf7_cf_fields_label">
                                    <?php 
                                        if(empty($fld_value)){
                                            echo "-";
                                        }else{
                                            esc_html_e($fld_value); 
                                        }
                                        if(!empty($note)){
                                            echo "<br> ( ".esc_html($note)." ) ";
                                        }
                                    ?>
                                </span>
                            </div>
                            
                        </div>
                    <?php
                }    
            }
        ?>
    </div>
</div>
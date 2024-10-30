<?php

$lead_data =  get_post_meta($post->ID, '_mipl_lead_collection_data', true);
$response_status = $lead_data['response_status'];

?>
<div>
    <p><b><?php echo esc_html('Response Status') ?>: <?php echo $response_status ?></b></p>
    
    <div>
    <?php
        if($response_status == 0 && isset($lead_data['response_error'])){
            ?>
            <div>
                <label><b><?php echo esc_html('Error') ?>:</b>
                <?php echo esc_html($lead_data['response_error']);  ?>
                </label><br>
            </div>
            
            <div class="mipl-cf7-lead-settimeout-code" style="font-size:11px">
                <fieldset>
                <legend><b><?php echo esc_html('Suggested Solution') ?></b></legend>
                    //Add this code in active theme functions.php file<br>
                    add_filter(
                    'mipl_cf7_crm_default_request_timeout', 
                    function($default_request_timeout){
                        return <b>60</b>;
                    });
                </fieldset>
            </div>
            <?php
        }
    ?>
    </div>
    
</div>

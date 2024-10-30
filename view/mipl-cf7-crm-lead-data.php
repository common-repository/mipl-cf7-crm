<?php
    $lead_data =  get_post_meta($post->ID, '_mipl_lead_collection_data', true);
    $req_body = isset($lead_data['crm_requesting_body']) ? $lead_data['crm_requesting_body'] : '';
    $resp_body = isset($lead_data['response_body']) ? ($lead_data['response_body']) : '';

    $crm_url = isset($lead_data['crm_url']) ? htmlentities($lead_data['crm_url']) : '';

?>

<div class="mipl_cf7_crm_lead_data">
    <div class="mipl-cf7-crm-response-body">
        <h4><?php echo esc_html("CRM API URL/Endpoint URL") ?></h4>
        <input type="text" value="<?php echo esc_url($crm_url) ?>" readonly>
    </div>

    <div class="mipl-cf7-crm-requesting-body">
        <h4><?php echo esc_html("CRM Requesting Body") ?></h4>
        <textarea><?php
                if(is_array($req_body)){
                    print_r($req_body);
                }

                if(is_string($req_body)){
                    $req_body = ($req_body);
                    echo $req_body;
                }
            ?>
            
        </textarea>
    </div>
     
    <div class="mipl-cf7-crm-response-body">
        <h4><?php echo esc_html("CRM Response Body") ?></h4>
        <textarea><?php
            echo $resp_body;
            ?>
        </textarea>
    </div>
</div>
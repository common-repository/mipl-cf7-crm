<?php       

if ( ! defined( 'ABSPATH' ) ) exit;

$crm_details = get_post_meta($post->ID, '_mipl_cf7_crm_details', true);
$crm_name    = isset($crm_details['selected_CRM']) ? $crm_details['selected_CRM'] : "";
$crm_url     = !empty($crm_details['crm_url']) ? $crm_details['crm_url'] : "";
$requesting_method = !empty($crm_details['requesting_method']) ? $crm_details['requesting_method'] : "";

//Authorization
$authentication_type = !empty($crm_details['authentication_type']) ? $crm_details['authentication_type'] : "";
$redirect_url        = get_rest_url( null, 'mipl-cf7-crm/v1/crm/oauth/'.$post->ID );
$url_scheme = parse_url($redirect_url, PHP_URL_SCHEME);
$url_host = parse_url($redirect_url, PHP_URL_HOST);
$javascript_origins = $url_scheme."://".$url_host;

$API_key   = !empty($crm_details['API_key']) ? $crm_details['API_key'] : "";
$API_value = !empty($crm_details['API_value']) ? $crm_details['API_value'] : "";
$API_location = !empty($crm_details['API_location']) ? $crm_details['API_location'] : "";

$bearer_token = !empty($crm_details['bearer_token']) ? $crm_details['bearer_token'] : "";

$basic_auth_username = !empty($crm_details['basic_auth_username']) ? $crm_details['basic_auth_username'] : "";
$basic_auth_password = !empty($crm_details['basic_auth_password']) ? $crm_details['basic_auth_password'] : "";

$client_id          = !empty($crm_details['client_id']) ? $crm_details['client_id'] : "";
$client_secret      = !empty($crm_details['client_secret']) ? $crm_details['client_secret'] : "";
$authorization_url  = !empty($crm_details['authorization_url']) ? $crm_details['authorization_url'] : "";
$access_token_url   = !empty($crm_details['access_token_url']) ? $crm_details['access_token_url'] : "";
$revoke_url         = !empty($crm_details['revoke_url']) ? $crm_details['revoke_url'] : "";
$scope              = !empty($crm_details['scope']) ? $crm_details['scope'] : "";

$grant_permission_disabled = '';

if(empty($client_id) || empty($client_secret) || empty($authorization_url) || empty($access_token_url) || empty($scope)){
    $grant_permission_disabled = 'disabled';
}

//Header
$crm_extra_headers_data = get_post_meta($post->ID, '_mipl_cf7_extra_headers_data', true);

//body
$content_type = !empty($crm_details['content_type']) ? $crm_details['content_type'] : "";

$requesting_body_data = !empty($crm_details['requesting_body_format']) ? $crm_details['requesting_body_format'] : "";

$client_id2     = "";
$client_secret2 = "";
if(!empty($client_id)){
    $client_id2 = substr($client_id, 0, 4).str_repeat('*', strlen($client_id) - 2).substr($client_id, strlen($client_id) - 4);
}
if(!empty($client_secret)){
    $client_secret2 = substr($client_secret, 0, 4).str_repeat('*', strlen($client_secret) - 2).substr($client_secret, strlen($client_secret) - 4);
}

//custom
$oauth_details   = base64_decode(get_post_meta($post->ID, '_mipl_oauth_details', true));
$decoded_details = json_decode($oauth_details, true);

//content type
$content_type_arr = array(
    'application/x-www-form-urlencoded', 
    'multipart/form-data', 
    'application/json', 
    'text/plain'
);

?>

<div class="mipl_cf7_crm">
    <div class="mipl_cf7_top_details">
        <div class="mipl_cf7_crm_item" style="display:none;">
            <label>
                <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Select CRM:') ?> </span><br>
                <select name="mipl_cf7_crm_details[selected_CRM]" class="crm_content mipl_cf7_crm">
                    <?php
                    $crm = array('Custom CRM'=>'custom-crm');
                    foreach($crm as $crm_display => $crm_value){
                        $select = "";
                        if($crm_name == $crm_value){
                            $select = "selected";
                        }
                    ?>
                    <option value="<?php echo esc_attr($crm_value); ?>" <?php echo esc_attr($select) ?>><?php echo esc_html($crm_display); ?></option>
                    <?php
                    }
                    ?>
                </select>
            </label>
        </div>
        <div class="mipl_cf7_crm_item mipl_cf7_required_field">
            <input type="hidden" class="post_id" value="<?php echo esc_attr($post->ID) ?>">
            <label>
                <span class="mipl_cf7_cf_fields_label setting_label "><?php esc_html_e('CRM API URL/Endpoint URL:') ?></span><br>
                <input type="text" class="crm_content mipl_cf7_crm_url" name="mipl_cf7_crm_details[crm_url]" value="<?php echo esc_attr($crm_url) ?>" placeholder="<?php echo esc_attr('Enter CRM URL')?>">
            </label>
        </div>
        <div class="mipl_cf7_crm_item mipl_cf7_required_field">
            <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Requesting Methods:') ?></span><br>
                <select name="mipl_cf7_crm_details[requesting_method]" class="crm_content mipl_cf7_requesting_method">
                    <?php
                    $req_methods = array('POST', 'GET', 'PUT', 'DELETE');
                    foreach($req_methods as $method){
                        $select = "";
                        if($requesting_method == $method){
                            $select = "selected";
                        }
                        ?>
                        <option value="<?php echo esc_attr($method) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html($method) ?></option>
                        <?php
                    }
                    ?>
                </select>
            </label>
        </div>
    </div>

    <div id="mipl_cf7_crm_tabs_wrapper1" class="mipl_cf7_crm_tabs_wrapper">        
        <ul class="mipl_cf7_crm_tabs">
            <li class="">
                <a href="#authorization"><?php esc_html_e('Authorization') ?></a>
            </li>
            <li>
                <a href="#headers"><?php esc_html_e('Headers') ?></a>
            </li>
            <li class="mipl_cf7_auth_body">
                <a href="#mipl_cf7_authorization_body"><?php esc_html_e('Body') ?></a>
            </li>
        </ul>

        <div id="authorization" class="mipl_cf7_tab_content">
            <div class="mipl_cf7_custom_authentication">
                <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                    <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Authentication Types:') ?></span><br>
                        <select name="mipl_cf7_crm_details[authentication_type]" class="mipl_cf7_crm_auth_types crm_content">
                            <option value=""><?php esc_html_e('Select Authentication Type') ?></option>
                            <?php
                                $auth_type_array = array('api_keys' => 'API Key', 'bearer_token' => 'Bearer Token', 'basic_auth' => 'Basic Auth', 'oauth_2.0' => 'oAuth 2.0');
                                foreach($auth_type_array as $key=>$type){
                                    $select = "";
                                    if($key == $authentication_type){
                                        $select = "selected";
                                    }
                                    ?>
                                        <option value="<?php echo esc_attr($key) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html($type) ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                    </label>
                </div>

                <div class="mipl_cf7_crm_item authentication_fields appended_authentication_fields" >
                    <div class="mipl_cf7_crm_API_keys auth_key">
                        <span class="mipl_cf7_cf_fields_label setting_label"><b><?php esc_html_e('Authentication Fields:') ?></b></span><br>
                        <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                            <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Key:') ?></span>
                            <input type="text" name="mipl_cf7_crm_details[API_key]" class="authentication_input crm_content" value="<?php echo esc_attr($API_key) ?>" placeholder="<?php echo esc_attr('Enter Key') ?>">
                        </div>
                        <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                            <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Value:') ?></span>    
                            <input type="text" name="mipl_cf7_crm_details[API_value]" class="authentication_input crm_content" value="<?php echo esc_attr($API_value) ?>" placeholder="<?php echo esc_attr('Enter Value') ?>">
                        </div>
                        <div class="mipl_cf7_crm_item">
                            <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Add to: ') ?></span><br>
                            <select name="mipl_cf7_crm_details[API_location]" class="crm_content">
                                <?php
                                $api_options = array('header'=>'Header', 'query_params'=>'Query Params');
                                foreach ($api_options as $api_key => $api_value) {
                                    $select = "";
                                    if($API_location == $api_key){
                                        $select = "selected";
                                    }
                                    ?>
                                    <option value="<?php echo esc_attr($api_key) ?>" <?php echo esc_attr($select) ?>><?php echo esc_html($api_value) ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        
                    </div>
                    <div class="mipl_cf7_crm_bearer_token auth_key mipl_cf7_required_field">
                        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Token:') ?></span><br>
                        <div class="mipl_cf7_crm_item">
                            <input type="text" name="mipl_cf7_crm_details[bearer_token]" class="authentication_input crm_content" value="<?php echo esc_attr($bearer_token) ?>" placeholder="<?php echo esc_attr('Enter Bearer Token') ?>">
                        </div>
                        </label>
                    </div>
                    <div class="mipl_cf7_crm_basic_auth auth_key">
                        <label><span class="mipl_cf7_cf_fields_label setting_label"><b><?php esc_html_e('Authentication fields:') ?></b></span><br>
                            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Username:') ?></span>
                                <input type="text" name="mipl_cf7_crm_details[basic_auth_username]" class="authentication_input crm_content" value="<?php echo esc_attr($basic_auth_username) ?>" placeholder="<?php echo esc_attr('Enter Username') ?>">
                            </div>
                            
                            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                <span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Password:') ?></span>
                                <input type="password" name="mipl_cf7_crm_details[basic_auth_password]" class="authentication_input crm_content" value="<?php echo esc_attr($basic_auth_password) ?>" placeholder="<?php echo esc_attr('Enter password') ?>">
                            </div>
                           
                        </label>
                    </div>
                    <div class="mipl_cf7_crm_oAuth auth_key">
                        <label><span class="mipl_cf7_cf_fields_label setting_label"><b><?php esc_html_e('Authentication Fields:') ?></b></span><br>
                            
                            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Authorization URL:') ?></span>
                                    <input type="text"  class="oauth_2_authorization_url authentication_input crm_content" name="mipl_cf7_crm_details[authorization_url]" value="<?php echo esc_url($authorization_url) ?>" placeholder="<?php echo esc_attr('Enter authorization url') ?>">
                                </label>
                            </div>
                            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Access Token URL:') ?></span>
                                    <input type="text"  class="oauth_2_access_token_url authentication_input crm_content" name="mipl_cf7_crm_details[access_token_url]" value="<?php echo esc_url($access_token_url) ?>" placeholder="<?php echo esc_attr('Enter access token url') ?>">
                                </label>
                            </div>
                            <div class="mipl_cf7_crm_item">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Revoke URL:') ?></span>
                                    <input type="text"  class="oauth_2_revoke_url authentication_input crm_content" name="mipl_cf7_crm_details[revoke_url]" value="<?php echo esc_attr($revoke_url) ?>" placeholder="<?php echo esc_attr('Enter revoke url') ?>">
                                </label>
                                <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Access token and Refresh token replace by {access_token} and {refresh_token}.") ?></em>
                            </div>
                            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Scope:') ?></span>
                                    <input type="text"  class="oauth_2_scope authentication_input crm_content" name="mipl_cf7_crm_details[scope]" value="<?php echo esc_attr($scope) ?>" placeholder="<?php echo esc_attr('Enter scope') ?>">
                                    <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Scope seprate by space.") ?></em>
                                    <br>
                                </label>
                            </div>
                            <div class="mipl_cf7_crm_item">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Javascript Origin URL:') ?></span>    
                                    <input type="text"  class="oauth_2_origin_url authentication_input crm_content" value="<?php echo esc_url($javascript_origins) ?>" readonly><br>
                                </label>
                            </div>
                            <div class="mipl_cf7_crm_item">
                                <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Redirect URL:') ?></span>
                                    <input type="text"  class="oauth_2_redirect_url authentication_input crm_content" value="<?php echo esc_url($redirect_url) ?>" readonly><br>
                                </label>
                            </div>
                            <?php
                            if(!empty($client_id2) && isset($decoded_details['access_token']) && !empty($decoded_details['access_token'])){
                                ?>
                                <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                    <label class='mipl_cf7_client_id'><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Client Id:') ?></span>
                                        <input type="text"  class="authentication_input crm_content"  name="" value="<?php echo esc_attr($client_id2) ?>" placeholder="<?php echo esc_attr('Enter client id') ?>" readonly>
                                    </label>
                                </div>
                                <input type="hidden"  class="mipl_cf7_oauth_2_client_id mipl_cf7_c_id authentication_input crm_content" name="mipl_cf7_crm_details[client_id]" value="<?php echo esc_attr($client_id) ?>" placeholder="<?php echo esc_attr('Enter client id') ?>">
                                <?php
                            }else{
                                ?>
                                <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                    <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Client Id:') ?></span>
                                        <input type="text"  class="mipl_cf7_oauth_2_client_id authentication_input crm_content" name="mipl_cf7_crm_details[client_id]" value="<?php echo esc_attr($client_id) ?>" placeholder="<?php echo esc_attr('Enter client id') ?>" >
                                    </label>
                                </div>
                                <?php
                            }
                            if(!empty($client_secret2) && isset($decoded_details['access_token']) && !empty($decoded_details['access_token'])){
                                ?>
                                
                                <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                    <label class='mipl_cf7_client_secret'><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Client Secret:') ?></span>
                                        <input type="text"  class="authentication_input crm_content"  name="" value="<?php echo esc_attr($client_secret2) ?>" placeholder="<?php echo esc_attr('Enter client secret') ?>" readonly>
                                    </label>
                                </div>
                                
                                <input type="hidden"  class="mipl_cf7_oauth_2_client_secret mipl_cf7_c_secret authentication_input crm_content" name="mipl_cf7_crm_details[client_secret]" value="<?php echo esc_attr($client_secret) ?>" placeholder="<?php echo esc_attr('Enter client secret') ?>">
                                <?php
                            }else{                      
                                ?>
                                
                                <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                                    <label><span class="mipl_cf7_cf_fields_label"><?php esc_html_e('Client Secret:') ?></span>
                                        <input type="text"  class="mipl_cf7_oauth_2_client_secret authentication_input crm_content" name="mipl_cf7_crm_details[client_secret]" value="<?php echo esc_attr($client_secret) ?>" placeholder="<?php echo esc_attr('Enter client secret') ?>" >
                                    </label>
                                </div>
                                
                                <?php
                            }
                            ?>
                            
                        </label>
                        
                        <?php 
                        
                        if( isset($decoded_details['access_token']) && !empty($decoded_details['access_token']) ){
                            ?>
                            <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Refresh token generated.") ?></em>
                            <br>
                            <?php
                        }else{
                            ?>
                            <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Refresh token not generated.") ?></em>
                            <br>
                            <?php
                        }

                       

                        if(empty($decoded_details['access_token'])){
                            ?>
                            <div class="mipl_cf7_grant_permission_div mipl_cf7_crm_item">
                                <button class="grant_permission button" <?php echo esc_attr($grant_permission_disabled) ?>><?php esc_html_e('Grant Permission') ?></button>
                            </div>
                            <?php
                        }
                        if($grant_permission_disabled != ''){
                            ?>
                            <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Before grant permission, it's essential to save the data.") ?></em>
                            <?php

                        }
                        ?>
                    
                        <?php
                            if(!empty($decoded_details['access_token'])){
                                ?>
                                <div class="reset mipl_cf7_crm_item" >
                                    <a class="reset button"><?php esc_html_e('Reset') ?></a>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                    
                </div>
            </div>

        </div>

        <div id="headers" class="mipl_cf7_tab_content">
            <div class="mipl_crm_default_fields mipl_cf7_crm_item">
                <span class="setting_label"><b><?php esc_html_e('Extra headers:') ?></b></span>
                <div class="mipl_cf7_extra_fields_table updated_extra_fields_table">
                    <div class="mipl_cf7_crm_default_fields_data mipl_cf7_extra_fields_table_row">
                        <div class="extra_fields_item">
                            <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Key') ?></b></span>
                        </div>
                        <div class="extra_fields_item">
                        <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Value') ?></b></span>
                        </div>
                        <div class="extra_fields_item extra_fields_action" style="width:6%">
                        <span class="mipl_cf7_cf_fields_label"><b><?php esc_html_e('Action') ?></b></span>
                        </div>
                    </div>
                    <?php
                    if(isset($crm_extra_headers_data['extra_field_keys'])){
                        foreach($crm_extra_headers_data['extra_field_keys'] as $position => $value){
                            $additional_header_key = !empty($crm_extra_headers_data['extra_field_keys'][$position]) ? $crm_extra_headers_data['extra_field_keys'][$position] : "";
                            $additional_header_value = !empty($crm_extra_headers_data['extra_field_values'][$position]) ? $crm_extra_headers_data['extra_field_values'][$position] : "";

                            $header_key_error_msg = isset($crm_extra_headers_data['errors']['extra_field_keys'][$position]) ? $crm_extra_headers_data['errors']['extra_field_keys'][$position] : "";
                            $header_value_error_msg = isset($crm_extra_headers_data['errors']['extra_field_values'][$position]) ? $crm_extra_headers_data['errors']['extra_field_values'][$position] : "";
                            
                        ?>
                        <div class="mipl_cf7_crm_default_fields_data mipl_cf7_extra_fields_table_row">
                            <div class="extra_fields_item">
                                <input name="mipl_cf7_headers_data[extra_field_keys][]" type="text" value="<?php echo esc_attr($additional_header_key) ?>" style="width:100%"></input>
                                <span class="mipl_crm_error_msg" ><?php echo esc_html($header_key_error_msg) ?></span>
                            </div>
                            <div class="extra_fields_item">
                                <input type="text" name="mipl_cf7_headers_data[extra_field_values][]" value="<?php echo esc_attr($additional_header_value) ?>" style="width:100%"></input>
                                <span class="mipl_crm_error_msg" ><?php echo esc_html($header_value_error_msg) ?></span>
                            </div>
                            <div class="extra_fields_item remove_extra_fields extra_fields_action" style="width:9%">
                                <a class="remove_extra_fields_button button"><?php esc_html_e('Remove') ?></a>
                            </div> 
                        </div>
                        <?php
                            }
                    }
                    ?>
                </div>
                <div class="add_headers_extra_fields">
                    <a class="mipl_cf7_headers_extra_fields_button button"><b>+</b><?php esc_html_e('Add More') ?></a>
                </div>
            </div>
            
        </div>

        
        <div id="mipl_cf7_authorization_body" class="mipl_cf7_tab_content">
            <div class="mipl_cf7_crm_item mipl_cf7_required_field">
                <label><span class="setting_label mipl_cf7_cf_fields_label"><?php esc_html_e('Content Type:') ?></span><br>
                    <select name="mipl_cf7_crm_details[content_type]" class="crm_content mipl_cf7_crm_content_type">
                        <?php
                        foreach($content_type_arr as $con_type){
                            $auth_type_select = "";
                            if(trim($con_type) == $content_type){
                                $auth_type_select = "selected";
                            }
                            ?>
                            <option value="<?php echo esc_attr($con_type) ?>" <?php echo esc_attr($auth_type_select) ?>><?php echo esc_html($con_type) ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </label>
            </div>


            <div class="mipl_cf7_crm_item mipl_crm_requesting_body">
                <div><b><?php esc_html_e('Shortcode : [fields-key-value-array], [fields-name-value-array], [query-string-format]') ?>
                </b></div>
                <div class="mipl_cf7_required_field">
                <span class="setting_label mipl_cf7_cf_fields_label"><?php esc_html_e('Requesting body:') ?></span>
                </div>        
                <textarea name="mipl_cf7_crm_details[requesting_body_format]" id="mipl_cf7_requesting_body"><?php echo esc_textarea($requesting_body_data) ?></textarea>
                
                <div class="mipl_requesting_body_example">
                    <p style="font-weight:bold"> <?php esc_html_e('Shortcode Examples :') ?> </p>
                    <div class="mipl_requesting_body_row">
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Ex: ') ?></b> 
                            {"data": [fields-key-value-array]}
                        </div>
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Output: ') ?></b>
                            {"data": {"First-name": "John"}}
                        </div><hr>
                    </div>
                    <div class="mipl_requesting_body_row">
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Ex :') ?></b> 
                            {"data": [fields-name-value-array]}
                        </div>
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Output :') ?></b> 
                            {"data": {name: "First-name", value: "John"}}
                        </div><hr>
                    </div>
                    <div class="mipl_requesting_body_row">
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Ex :') ?></b> 
                            {"data": "[query-string-format]"}
                        </div>
                        <div class="mipl_requesting_body_col">
                            <b><?php esc_html_e('Output :') ?></b> 
                            {"data": "First-name=John&Last-name=Deo&Email=johnxyz@gmail.com"}
                        </div><hr>
                    </div>
            
                </div>
            </div>
        </div>

    </div>
      
</div>

<script id="add_header_extra_fields" type='template/text'>
    <div class="mipl_cf7_extra_fields_table">
        <div class="mipl_cf7_extra_fields_table_row">
            <div class="extra_fields_item">
                <input class="" name="mipl_cf7_headers_data[extra_field_keys][]" type="text" value="" style="width:100%"></input>
            </div>
            <div class="extra_fields_item">
                <input class="" type="text" name="mipl_cf7_headers_data[extra_field_values][]" value="" style="width:100%"></input>
            </div>
            <div class="extra_fields_item remove_extra_fields extra_fields_action" style="width:9%">
                <a class="remove_extra_fields_button button"><?php echo esc_html('Remove') ?></a>
            </div>
        </div>
    </div>
</script>

<script>

    jQuery('body').ready(function(){
        jQuery('.grant_permission').on('click', function(event){
            var id = jQuery('.post_id').val();
            var $form_data = jQuery(this).closest('form').serializeArray();
            jQuery.post('?mipl-crm-action=save_oauth_form&id='+id, $form_data, function(response){
                var res = JSON.parse(response);
                if(res.status == 'success'){
                    window.location='<?php echo esc_url(admin_url());?>/?mipl-crm-action=oauth_redirect&crm_id='+id;
                }else{
                    window.location.reload();
                }
            });
            return false;
        });
    });



// Tab script
jQuery(document).ready(function(){
    jQuery('.mipl_cf7_requesting_method').on('change', function(){
        var requesting_method = jQuery(this).val();
        jQuery('.mipl_cf7_auth_body').css('display','block');

        if(requesting_method == 'GET' || requesting_method == 'DELETE'){
            jQuery('#mipl_cf7_requesting_body').val('');
            jQuery('.mipl_cf7_auth_body').css('display','none');
            jQuery('#mipl_cf7_authorization_body').css('display','none');
            mipl_cf7_change_tab("#authorization");
        }
    });

    var $active_tab = localStorage.getItem('mipl_cf7_auth_last_active_tab');
   
    if( $active_tab == null ){
        $active_tab = '#authorization';
    }
    mipl_cf7_change_tab($active_tab);

    jQuery('.mipl_cf7_crm_tabs li a').click(function(){
        var $tab = jQuery(this).attr('href');
        return mipl_cf7_change_tab($tab);
    });

    function mipl_cf7_change_tab($tab){

        $tab = $tab.replaceAll('#', ''); 
        if( jQuery('#'+$tab).length <= 0 ){ return false; }

        jQuery('.mipl_cf7_crm_tabs li').removeClass('mipl_cf7_active_tab');
        jQuery('.mipl_cf7_crm_tabs li a[href=#'+$tab+']').parent('li').addClass('mipl_cf7_active_tab');

        jQuery('.mipl_cf7_crm_tabs_wrapper .mipl_cf7_tab_content').hide(0);
        jQuery('.mipl_cf7_crm_tabs_wrapper .mipl_cf7_tab_content#'+$tab).show(0);

        localStorage.setItem('mipl_cf7_auth_last_active_tab', $tab);

        return false;

    }

});


</script>


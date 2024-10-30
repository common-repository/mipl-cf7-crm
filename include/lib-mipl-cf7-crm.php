<?php

if ( ! defined( 'ABSPATH' ) ) exit;

//Generate random string
if(!function_exists('mipl_cf7_rand')){
function mipl_cf7_rand($length = 10) {
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
}
}


// Session request checked.
if(!function_exists('mipl_cf7_json_request')){
function mipl_cf7_json_request(){
    if ( isset( $_SERVER['CONTENT_TYPE'] ) && wp_is_json_media_type( $_SERVER['CONTENT_TYPE'] ) ) {
        return true;
    }
    return false;
}
}


//CRM details array
if(!function_exists('mipl_cf7_crm_details_validation_array')){
function mipl_cf7_crm_details_validation_array($meta_fields=array()){

    $required = "";
    if(isset($meta_fields['selected_CRM']) && $meta_fields['selected_CRM'] == 'custom-crm'){
        $required = "required";
    }

    $selected_auth = array();
    $common_auth_fields = array(
        'selected_CRM' => array(
            'label'      => 'selected CRM',
            'type'       => 'select',
            'values'     => array('custom-crm'),
            'validation' => array(
                'in_values' => __('CRM should be valid!'), 
                'required' => __('CRM should not blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        ),
        'crm_url' => array(
            'label'      => 'crm url',
            'type'       => 'text',
            'validation' => array(
                'required'=> __('CRM API URL/Endpoint URL should not blank!'), 
                'url'=> __('CRM API URL/Endpoint URL not valid!'),
                'limit'=>'500',
                'limit_msg'=>__('CRM API URL/Endpoint URL Write 500 Character only!')
            ),
            'sanitize'   => array('sanitize_text_field')
        ),
        'requesting_method' => array(
            'label'      => 'requesting method',
            'type'       => 'select',
            'values'     => array('POST', 'GET', 'PUT', 'DELETE'),
            'validation' => array('in_values'=>__('Requesting methods should be valid!')),
            'sanitize'   => array('sanitize_text_field')
        ),
        
    );

    $requesting_body = array();
    if(isset($meta_fields['requesting_method']) && in_array($meta_fields['requesting_method'], array('POST','PUT'))){
        $requesting_body = array(
            'content_type' => array(
                'label'      => 'content type',
                'type'       => 'select',
                'values'     => array('multipart/form-data', 'application/x-www-form-urlencoded', 'application/json', 'text/plain'),
                'validation' => array(
                    'in_values'=>__('Content type should be valid!'), 
                    'required'=>__('Content type should not blank!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'requesting_body_format' => array(
                'label'      => 'requesting body',
                'type'       => 'textarea',
                'depend'     => array('field' => 'content_type', 'value' => 'application/json'),
                'validation' => array($required=>__('Requesting body should not blank!')),
                'sanitize'   => array('sanitize_text_field')
            ),
        );
    }
    
    if(!empty($meta_fields['selected_CRM']) && $meta_fields['selected_CRM'] == 'custom-crm'){
        $selected_auth = array(

            'authentication_type' => array(
                'label'      => 'authentication type',
                'type'       => 'select',
                'values'     => array('','api_keys','bearer_token','basic_auth','oauth_2.0'),
                'validation' => array('in_values'=>__('Authentication types should be valid!')),
                'sanitize'   => array('sanitize_text_field')
            ),
            'API_key' => array(
                'label'      => 'API key',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type', 
                    'value' => 'api_keys'
                ),
                'validation' => array(
                    'required'=>__('API key should not blank!'),
                    'regex'=>'/^[a-zA-Z0-9\_\-~.]*$/',
                    'regex_msg'=>__('API key not valid!'), 
                    'limit'=>'500',
                    'limit_msg'=>__('API key Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'API_value' => array(
                'label'      => 'API value',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type', 
                    'value' =>'api_keys'
                ),
                'validation' => array(
                    'required'=>__('API value should not blank!'), 
                    //'regex_msg'=>'API value not valid!', 
                    //'regex'=>'/^[a-zA-Z0-9-_]*$/',
                    'limit' => '1000',
                    'limit_msg' => __('API value Write 1000 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'API_location' => array(
                'label'      => 'API location',
                'type'       => 'select',
                'depend'     => array(
                    'field' => 'authentication_type', 
                    'value' =>'api_keys'
                ),
                'values'     => array('header', 'query_params'),
                'validation' => array(
                    'required'=>__('API add to should not blank!'), 
                    'in_values'=>__('API add to should be valid!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'bearer_token' => array(
                'label'      => 'bearer token',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type', 
                    'value' =>'bearer_token'
                ),
                'validation' => array(
                    'required'=>__('Token should not blank!'), 
                    // 'regex_msg'=>'Token not valid!', 
                    // 'regex'=>'/^[a-zA-Z0-9_-]*$/', 
                    'limit'=>'500',
                    'limit_msg'=>__('Token Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'basic_auth_username' => array(
                'label'      => 'basic authentication username',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type',
                    'value' =>'basic_auth'
                ),
                'validation' => array(
                    // 'regex'=>'/^[a-zA-Z0-9_-@]*$/',
                    'required'=> __('Username should not blank!'),
                    'except_regex_msg'=>__('Username not valid!'),
                    'except_regex'=>'/[:]/',
                    'limit'=>'500',
                    'limit_msg'=>__('Username Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'basic_auth_password' => array(
                'label'      => 'basic authentication password',
                'type'       => 'text',
                'depend'     => array(
                    'field' =>'authentication_type',
                    'value' =>'basic_auth'
                ),
                'validation' => array(
                    'required'=>__('Password should not blank!'),
                    'except_regex_msg'=>__('Password not valid!'),
                    'except_regex'=>'/[:]/',
                    // 'regex_msg'=>'Password not valid!',
                    // 'regex'=>'/^[a-zA-Z0-9_-]*/', 
                    'limit'=>'500',
                    'limit_msg'=>__('Password Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            
            'authorization_url' => array(
                'label'      => 'authorization url',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    'required'=>__('Authorization URL should not blank!'),
                    'url'=>__('Authorization URL not valid!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Authorization URL Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),

            'access_token_url' => array(
                'label'      => 'access token url',
                'type'       => 'text',
                'depend'     => array(
                    'field' =>'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    'required'=>__('Access token URL should not blank!'),
                    'url'=>__('Access token URL not valid!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Access token URL Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'revoke_url' => array(
                'label'      => 'revoke url',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    // 'required'=>'Revoke url should not blank!',
                    'url'=>__('Revoke URL not valid!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Revoke URL Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'scope' => array(
                'label'      => 'scope',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    'required'=>__('Scope should not blank!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Scope Write 500 Character only!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'client_id' => array(
                'label'      => 'client id',
                'type'       => 'text',
                'depend'     => array(
                    'field' => 'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    'required'=>__('Client id should not blank!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Client id Write 500 Character only!'),
                    'regex'=>'/^([a-zA-Z0-9._-]*)$/',
                    'regex_msg'=>__('Client id not valid!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            'client_secret' => array(
                'label'      => 'client secret',
                'type'       => 'text',
                'depend'     => array(
                    'field' =>'authentication_type',
                    'value' =>'oauth_2.0'
                ),
                'validation' => array(
                    'required'=>__('Client secret should not blank!'),
                    'limit'=>'500',
                    'limit_msg'=>__('Client id Write 500 Character only!'),
                    'regex'=>'/^([a-zA-Z0-9._-]*)$/',
                    'regex_msg'=>__('Client secret not valid!')
                ),
                'sanitize'   => array('sanitize_text_field')
            ),
            
        );
        
    }
    $mipl_cf7_form_field_val = array_merge($common_auth_fields, $requesting_body, $selected_auth);
    return $mipl_cf7_form_field_val;
}
}


if(!function_exists('mipl_cf7_crm_email_details_validation_array')){
function mipl_cf7_crm_email_details_validation_array($email_post_data){
    $email_required = '';
    if(isset($email_post_data['enable_email'])){
        $email_required = 'required';
    }
    $crm_email_details = array(
        'email_from' => array(
            'label'      => 'email from',
            'type'       => 'text',
            'validation' => array(
                'limit'=>'500',
                'limit_msg'=>__('Email from write 500 character only!'),
                'email' => __('Email From should be valid!'),
                $email_required => __('Email from should not be blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        ),
        'email_to' => array(
            'label'      => 'email to',
            'type'       => 'text',
            'validation' => array(
                'limit' => '500',
                'limit_msg' => __('Email to write 500 character only!'),
                'email' => __('Email to should be valid!'),
                $email_required => __('Email to should not be blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        ),
        'email_subject' => array(
            'label'      =>'email subject',
            'type'       => 'text',
            'validation' => array(
                'limit'=>'500',
                'limit_msg'=>__('Email subject Write 500 Character only!'),
            ),
            'sanitize'   => array('sanitize_text_field')

        ),
        'extra_headers' => array(
            'label'      =>'extra headers',
            'type'       => 'textarea',
            'validation' => array(
                'limit'=>'2000',
                'limit_msg'=>__('Email header Write 2000 Character only!')
            ),
            'sanitize'   => array('sanitize_textarea_field')
        ),
        'email_body' => array(
            'label'      => 'email body',
            'type'       => 'textarea',
            'validation' => array(
                // 'limit'=>'500',
                // 'limit_msg'=>'Email body write 500 character only!',
                $email_required => __('Email body should not be blank!')
            ),
            'sanitize'   => array('sanitize_textarea_field'),
        ),
        'error_display' => array(
            'label'      => 'error display',
            'type'       => 'checkbox',
            'values'     => array('','1'),
            'validation' => array('in_values'=>__('Email display only for error should be valid!')),
        ),
        'enable_email' => array(
            'label'      => 'enable email',
            'type'       => 'checkbox',
            'values'     => array('','1'),
            'validation' => array('in_values'=>__('Enable email only for error should be valid!')),
        ), 
    );
    return $crm_email_details;
}
}


//Headers validation
if(!function_exists('mipl_cf7_header_validate_data')){
function mipl_cf7_header_validate_data($header_fields){
    
    $validation_array = array();
    $validation_data = array();
    $field_array = array();
    $duplicate_names  = array();
    
    $sub_field_keys = array('extra_field_keys', 'extra_field_values');

    foreach($header_fields['extra_field_keys'] as $field_index => $field_name){
    
        $temp_field_keys = $field_index.'_extra_field_keys';
        $validation_array[$temp_field_keys] = array(
            'label'      => 'extra_field_keys',
            'type'       => 'text',
            'validation' => array(
                'required'  => __("Header keys should not blank!"),
                'alpha_dash'=> __("Header Key must be valid!"), 
                'limit'     => 500,
                'limit_msg' => __('Header keys should be maximum 500 characters!')
            ),
            'sanitize' => array('sanitize_text_field')
        );

        $temp_field_values = $field_index.'_extra_field_values';
        $validation_array[$temp_field_values] = array(
            'label'      => 'extra_field_values',
            'type'       => 'text',
            'validation' => array(
                'limit'    => 1000,
                'limit_msg'=> __("Header value should be maximum 1000 characters!")
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $validation_data[$temp_field_keys] = $header_fields['extra_field_keys'][$field_index];
        $validation_data[$temp_field_values] = $header_fields['extra_field_values'][$field_index];
    
        foreach($sub_field_keys as $sub_field_key){
            if(empty(trim($header_fields['extra_field_keys'][$field_index]))){continue;}
            if(isset($header_fields[$sub_field_key][$field_index])){
                $field_array[$sub_field_key][$field_index] =  sanitize_text_field($header_fields[$sub_field_key][$field_index]);
            }
            
        }

    }

    if(isset($field_array['extra_field_keys'])){

        $counts = array_count_values(array_map('strtolower', $field_array['extra_field_keys']));
    
        $filtered = array_filter($field_array['extra_field_keys'], function ($value) use ($counts) {
            return $counts[strtolower($value)] > 1;
        });
        
        $array_first_key = array_key_first($filtered);

        if($array_first_key !== null){
            unset($filtered[$array_first_key]);
            foreach($filtered as  $filter_key => $filter_val){
                $duplicate_names[$filter_key.'_extra_field_keys'] = "Field key was duplicate!";
            }
        }

    }
    

    $val_obj = new MIPL_CF7_Input_Validation($validation_array, $validation_data);
    
    $rs = $val_obj->validate();
    $errors = $val_obj->get_errors();
    $post_data = $val_obj->get_valid_data();
   
    
    foreach($field_array as $store_field => $value_arr){
        foreach($value_arr as $v_index => $value){
            if( isset($errors[$v_index.'_extra_field_keys']) ){
                $field_array['errors']['extra_field_keys'][$v_index] = $errors[$v_index.'_extra_field_keys'];
            }elseif( !isset($errors[$v_index.'_extra_field_keys'])  && isset($duplicate_names[$v_index.'_extra_field_keys'])){
                $field_array['errors']['extra_field_keys'][$v_index] = $duplicate_names[$v_index.'_extra_field_keys'];
            }

            if( isset($errors[$v_index.'_extra_field_values']) ){
                $field_array['errors']['extra_field_values'][$v_index] = $errors[$v_index.'_extra_field_values'];
            }
            
        }
    }
    return $field_array;
}
}


//Form validation
if(!function_exists('mipl_cf7_fields_mapping_validate_data')){
function mipl_cf7_fields_mapping_validate_data($form_fields){
    
    $validation_array = array();
    $validation_data = array();
    $field_array = array();
    $duplicate_names  = array();
    
    $sub_field_keys = array('CRM_fields', 'cf7_fields', 'data_type');

    foreach($form_fields['CRM_fields'] as $field_index => $field_name){
    
        $temp_CRM_fields = $field_index.'_CRM_fields';
        $validation_array[$temp_CRM_fields] = array(
            'label'      => 'CRM_fields',
            'type'       => 'text',
            'validation' => array(
                'required'  => __("CRM fields should not blank!"),
                'alpha_dash' => __("CRM fields should be valid!"),
                'limit'     => '500',
                'limit_msg' => __('CRM fields should be maximum 500 characters!')
            ),
            'sanitize' => array('sanitize_text_field')
        );

        $temp_cf7_fields = $field_index.'_cf7_fields';
        $validation_array[$temp_cf7_fields] = array(
            'label'      => 'cf7_fields',
            'type'       => 'select',
            'validation' => array(
                'required' => __("CF7 fields should not blank!"),
                'limit'    => 500,
                'limit_msg'=> __("CF7 fields should be maximum 500 characters!")
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $temp_data_type = $field_index.'_data_type';
        $validation_array[$temp_data_type] = array(
            'label'      => 'data_type',
            'type'       => 'select',
            'values'     => array('text','Default',' Y-m-d', 'Y-m-d H:i:s', 'Y/m/d H:i:s', 'm/d/Y H:i:s', 'd/m/Y H:i:s', 'file_object', 'file_url' ),
            'validation' => array(
                'in_values'=>__('Data type should be valid!')
                
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $validation_data[$temp_CRM_fields] = $form_fields['CRM_fields'][$field_index];
        $validation_data[$temp_cf7_fields] = $form_fields['cf7_fields'][$field_index];
        $validation_data[$temp_data_type] = $form_fields['data_type'][$field_index];

        foreach($sub_field_keys as $sub_field_key){
            if(isset($form_fields[$sub_field_key][$field_index])){
                $field_array[$sub_field_key][$field_index] =  sanitize_text_field($form_fields[$sub_field_key][$field_index]);
            }
            
        }

    }

    if(isset($field_array['CRM_fields'])){
        $counts = array_count_values(array_map('strtolower', $field_array['CRM_fields']));
        
        $filtered = array_filter($field_array['CRM_fields'], function ($value) use ($counts) {
            return $counts[strtolower($value)] > 1;
        });
        
        $array_first_key = array_key_first($filtered);

        if($array_first_key !== null){
            unset($filtered[$array_first_key]);
            foreach($filtered as  $filter_key => $filter_val){
                $duplicate_names[$filter_key.'_CRM_fields'] = "CRM field was duplicate!";
            }
        }
    }
        
    $val_obj = new MIPL_CF7_Input_Validation($validation_array, $validation_data);
    
    $rs = $val_obj->validate();
    $errors = $val_obj->get_errors();
    $post_data = $val_obj->get_valid_data();

    if(!empty($duplicate_names)){
        foreach ( $duplicate_names as $dup_key=>$dup_val ) {
            unset($post_data[$dup_key]);
        }
    }

    foreach($field_array as $store_field => $value_arr){
        foreach($value_arr as $v_index => $value){
            if( isset($errors[$v_index.'_CRM_fields']) ){
                $field_array['errors']['CRM_fields'][$v_index] = $errors[$v_index.'_CRM_fields'];
            }elseif( !isset($errors[$v_index.'_CRM_fields'])  && isset($duplicate_names[$v_index.'_CRM_fields'])){
                $field_array['errors']['CRM_fields'][$v_index] = $duplicate_names[$v_index.'_CRM_fields'];
            }

            if( isset($errors[$v_index.'_cf7_fields']) ){
                $field_array['errors']['cf7_fields'][$v_index] = $errors[$v_index.'_cf7_fields'];
            }
            if( isset($errors[$v_index.'_data_type']) ){
                $field_array['errors']['data_type'][$v_index] = $errors[$v_index.'_data_type'];
            }

            // validate value
            if( !isset($post_data[$v_index.'_CRM_fields']) ){
                $field_array['CRM_fields'][$v_index] = '';
            }
            if( !isset($post_data[$v_index.'_cf7_fields']) ){
                $field_array['cf7_fields'][$v_index] = '';
            }
            if( !isset($post_data[$v_index.'_data_type']) ){
                $field_array['data_type'][$v_index] = '';
            }
            
        }
    }
    return $field_array;
}
}


//Static form fields   
if(!function_exists('mipl_cf7_static_fields_validate_data')){
function mipl_cf7_static_fields_validate_data($static_fields){
    
    $validation_array = array();
    $validation_data = array();
    $field_array = array();
    $duplicate_names  = array();
    
    $sub_field_keys = array('crm_default_field_keys', 'crm_default_field_values');

    foreach($static_fields['crm_default_field_keys'] as $field_index => $field_name){
    
        $temp_crm_default_field_keys = $field_index.'_crm_default_field_keys';
        $validation_array[$temp_crm_default_field_keys] = array(
            'label'      => 'crm_default_field_keys',
            'type'       => 'text',
            'validation' => array(
                'required'  => __("Static form keys should not blank!"),
                'alpha_dash' => __("Static form keys should be valid!"),
                'limit'     => '500',
                'limit_msg' => __('Field key (Field Mapping) should be maximum 500 characters!')
            ),
            'sanitize' => array('sanitize_text_field')
        );

        $temp_crm_default_field_values = $field_index.'_crm_default_field_values';
        $validation_array[$temp_crm_default_field_values] = array(
            'label'      => 'crm_default_field_values',
            'type'       => 'text',
            'validation' => array(
                'limit'    => '500',
                'limit_msg'=> __("Field value (Field Mapping) should be maximum 500 characters!")
            ),
            'sanitize'   => array('sanitize_text_field')
        );
        if(empty(trim($static_fields['crm_default_field_keys'][$field_index])) ){
            continue;
        }
        $validation_data[$temp_crm_default_field_keys] = $static_fields['crm_default_field_keys'][$field_index];
        $validation_data[$temp_crm_default_field_values] = $static_fields['crm_default_field_values'][$field_index];

        foreach($sub_field_keys as $sub_field_key){
            if(isset($static_fields[$sub_field_key][$field_index])){
                $field_array[$sub_field_key][$field_index] =  sanitize_text_field($static_fields[$sub_field_key][$field_index]);
            }
            
        }

    }

    
    if(isset($field_array['crm_default_field_keys'])){
        $counts = array_count_values(array_map('strtolower', $field_array['crm_default_field_keys']));
        
        $filtered = array_filter($field_array['crm_default_field_keys'], function ($value) use ($counts) {
            return $counts[strtolower($value)] > 1;
        });
        
        $array_first_key = array_key_first($filtered);

        if($array_first_key !== null){
            unset($filtered[$array_first_key]);
            foreach($filtered as  $filter_key => $filter_val){
                $duplicate_names[$filter_key.'_crm_default_field_keys'] = "Field key was duplicate!";
            }
        }
    }

    $val_obj = new MIPL_CF7_Input_Validation($validation_array, $validation_data);
    $rs = $val_obj->validate();
    $errors = $val_obj->get_errors();
    $post_data = $val_obj->get_valid_data();

    if(!empty($duplicate_names)){
        foreach ( $duplicate_names as $dup_key=>$dup_val ) {
            unset($post_data[$dup_key]);
        }
    }

    foreach($field_array as $store_field => $value_arr){
        foreach($value_arr as $v_index => $value){
            if( isset($errors[$v_index.'_crm_default_field_keys']) ){
                $field_array['errors']['crm_default_field_keys'][$v_index] = $errors[$v_index.'_crm_default_field_keys'];
            }elseif( !isset($errors[$v_index.'_crm_default_field_keys'])  && isset($duplicate_names[$v_index.'_crm_default_field_keys'])){
                $field_array['errors']['crm_default_field_keys'][$v_index] = $duplicate_names[$v_index.'_crm_default_field_keys'];
            }
            if( isset($errors[$v_index.'_crm_default_field_values']) ){
                $field_array['errors']['crm_default_field_values'][$v_index] = $errors[$v_index.'_crm_default_field_values'];
            }
            
        }
    }
    return $field_array;
}
}


//CRM setting
if(!function_exists('mipl_cf7_crm_setting_validate_data')){
function mipl_cf7_crm_setting_validate_data($crm_setting){
    $validation_array = array();
    $validation_data = array();
    $field_array = array();
    $duplicate_names  = array();
    
    $sub_field_keys = array('crm_form_submission', 'crm_form_fields_name', 'crm_form_fields_value', 'crm_submission_on','crm_store_lead');

    if(empty($crm_setting['crm_form_submission'])){ return false; }

    foreach($crm_setting['crm_form_submission'] as $field_index => $field_name){
    
        $temp_crm_form_submission = $field_index.'_crm_form_submission';
        $validation_array[$temp_crm_form_submission] = array(
            'label'      => 'crm_form_submission',
            'type'       => 'select',
            'validation' => array(
                'required'=>__('Select form should not blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $temp_crm_form_fields_name = $field_index.'_crm_form_fields_name';
        $validation_array[$temp_crm_form_fields_name] = array(
            'label'      => 'crm_form_fields_name',
            'type'       => 'select',
            'values'     => $crm_setting['crm_form_fields_name'],
            'validation' => array(
                'in_values'=>__('Select field field should not blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $temp_crm_form_fields_value = $field_index.'_crm_form_fields_value';
        $validation_array[$temp_crm_form_fields_value] = array(
            'label'      => 'crm_form_fields_value',
            'type'       => 'select',
            'values'     => $crm_setting['crm_form_fields_value'],
            'validation' => array(
                'in_values'=> __('Select field value should not blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $temp_crm_submission_on = $field_index.'_crm_submission_on';
        $validation_array[$temp_crm_submission_on] = array(
            'label'      => 'crm_submission_on',
            'type'       => 'select',
            'values'     => array(
                "wpcf7_submit",
                "wpcf7_mail_sent",
            ),
            'validation' => array(
                'in_values'=>__('CRM Submit On should be valid!'),
                'required'=>__('CRM Submit On should not blank!')
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        $temp_crm_store_lead = $field_index.'_crm_store_lead';
        $validation_array[$temp_crm_store_lead] = array(
            'label'      => 'crm_store_lead',
            'type'       => 'checkbox',
            'validation' => array(
                'limit'    => 500,
                'limit_msg'=> __("Lead Collection should be maximum 500 characters!")
            ),
            'sanitize'   => array('sanitize_text_field')
        );

        
        $validation_data[$temp_crm_form_submission] = $crm_setting['crm_form_submission'][$field_index];
        $validation_data[$temp_crm_submission_on] = !empty($crm_setting['crm_submission_on'][$field_index]) ? $crm_setting['crm_submission_on'][$field_index] : '';
        $validation_data[$temp_crm_form_fields_name] = $crm_setting['crm_form_fields_name'][$field_index];
        $validation_data[$temp_crm_form_fields_value] = $crm_setting['crm_form_fields_value'][$field_index];
        $validation_data[$temp_crm_store_lead] = $crm_setting['crm_store_lead'][$field_index];
        
        foreach($sub_field_keys as $sub_field_key){

            $field_array[$sub_field_key] =  ($crm_setting[$sub_field_key]);
            
        }

    }
    
    $val_obj = new MIPL_CF7_Input_Validation($validation_array, $validation_data);
    
    $rs = $val_obj->validate();
    $errors = $val_obj->get_errors();
    $post_data = $val_obj->get_valid_data();
    foreach($field_array as $store_field => $value_arr){
        foreach($value_arr as $v_index => $value){
            if( isset($errors[$v_index.'_crm_submission_on']) ){
                $field_array['errors']['crm_submission_on'] = $errors[$v_index.'_crm_submission_on'];
            }
            if( isset($errors[$v_index.'_crm_form_fields_name']) ){
                $field_array['errors']['crm_form_fields_name']= $errors[$v_index.'_crm_form_fields_name'];
            }
            if( isset($errors[$v_index.'_crm_form_fields_value']) ){
                $field_array['errors']['crm_form_fields_value']= $errors[$v_index.'_crm_form_fields_value'];
            }
            if( isset($errors[$v_index.'_crm_form_submission']) ){
                $field_array['errors']['crm_form_submission'] = $errors[$v_index.'_crm_form_submission'];
            }
            if( isset($errors[$v_index.'_crm_store_lead']) ){
                $field_array['errors']['crm_store_lead'] = $errors[$v_index.'_crm_store_lead'];
            }
            
        }
    }
    
    return $field_array;
}
}


//for admin notices
if(!function_exists('mipl_cf7_admin_notices')){
function mipl_cf7_admin_notices(){
    global $post;
    if(isset($post->ID)){
        $error_flag = get_post_meta($post->ID, '_mipl_cf7_crm_error_config', true);

        if($error_flag == 'yes' &&  (empty($_SESSION['mipl_cf7_admin_notices']) || !isset($_SESSION['mipl_cf7_admin_notices']))){
            ?>
            <div class='notice is-dismissible notice-error'>
                <p><?php echo esc_html('Please Check Configuration Of CRM Integration!') ?></p>
            </div>
            <?php
        }
    }
    

    // mipl_cf7_session_start();
    $message_type = array( 'error', 'success', 'warning', 'info' );
    foreach( $message_type as $type ){
        
        $class = 'notice is-dismissible ';

        if( isset($_SESSION['mipl_cf7_admin_notices'][ $type ]) && trim( $_SESSION['mipl_cf7_admin_notices'][ $type ]) != '' ){
            $class = $class.' notice-'.$type;
            $message = wp_kses_post($_SESSION['mipl_cf7_admin_notices'][ $type ]);
            echo wp_kses_post('<div class="'.$class.'"><p>'.$message.'</p></div>');
            unset($_SESSION['mipl_cf7_admin_notices'][$type]);
        }

    }
    
}   
}
 

if(!function_exists('mipl_cf7_field_type')){
function mipl_cf7_field_type($form_id, $field_name){
    
    if(empty($form_id)){
        return false;
    }

    $cf7_fields_data = get_post_meta($form_id, '_form');  

    if(!is_array($cf7_fields_data) || count($cf7_fields_data) < 1){ return false; }

    $meta            = $cf7_fields_data[0];
    $TagsManager     = WPCF7_FormTagsManager::get_instance();
    $tags            = $TagsManager->scan( $meta );
    $cf7_form_fields   = $TagsManager->filter( $tags, $data );
    $cf7_fields_name   = array();

    foreach($cf7_form_fields as $fields_position => $fields_data){
        
        if(!empty($fields_data->name) && $fields_data->name == $field_name){
            
            $fld_type = $fields_data->basetype;
        } 
    }
    return $fld_type;

}
}


if(!function_exists('mipl_cf7_file_fields_name')){
function mipl_cf7_file_fields_name( $cf7_id ) {

    if( empty($cf7_id) ){ return false; }

    $ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
    $form_fields = $ContactForm->scan_form_tags();

    $cf7_file_name = array();
    foreach ($form_fields as $fields) {
        if($fields['basetype'] == 'file'){
            $cf7_file_name[] = $fields['name'];
        }
    }
    return $cf7_file_name;
}
}


//Start session
// if(!function_exists('mipl_cf7_session_start')){
// function mipl_cf7_session_start() {
    
//     // if( !wp_is_json_request() && !session_id() ){ session_start(); }

// }
// }


//Verify nonce
if(!function_exists('mipl_cf7_verify_nonce')){
function mipl_cf7_verify_nonce( $mipl_cf7_post_id, $mipl_cf7_crm_nonce ) {

    // Check user status 
    if(!(is_user_logged_in()) || !(current_user_can('administrator'))){
        return false;
    }

    $mipl_verify_nonce = wp_verify_nonce(trim($mipl_cf7_crm_nonce), 'mipl_cf7_crm'.$mipl_cf7_post_id);
    if(!$mipl_verify_nonce){
        return false;
    }
    return true;

}
}


// checked all required thing for crm submission
if(!function_exists('mipl_cf7_required_crm_data')){
function mipl_cf7_required_crm_data($post_id){

    if( empty($post_id) ){ return false; }

    // crm details
    $crm_details_required_data = false;
    $crm_details = get_post_meta($post_id, '_mipl_cf7_crm_details', true);
    $crm_details_error = get_post_meta($post_id, '_mipl_cf7_crm_details_error', true);
    if(isset($crm_details_error['crm_url'])){
        unset($crm_details['crm_url']);
    }

    $extra_header_data = get_post_meta($post_id, '_mipl_cf7_extra_headers_data', true);
    $form_data = get_post_meta($post_id, '_mipl_cf7_crm_form_data', true);
    $static_form_data = get_post_meta($post_id, '_mipl_cf7_crm_default_data', true);
    $setting_data = get_post_meta($post_id, '_mipl_cf7_crm_settings', true);
    $auth_type = isset($crm_details['authentication_type']) ? $crm_details['authentication_type'] : '';
    $content_type = isset($crm_details['content_type']) ? $crm_details['content_type'] : '';
    $requesting_method = isset($crm_details['requesting_method']) ? $crm_details['requesting_method'] : '';

    // Submitting form
    $submitting_form_flag = false;
    if(isset($setting_data['crm_form_submission'][0]) && !empty($setting_data['crm_form_submission'][0])){
        $submitting_form_flag = true;
    }

    // Extra header
    $extra_header_required_flag = false;
    if(empty($extra_header_data) || !isset($extra_header_data['errors'])){
        $extra_header_required_flag = true;
    }

    // Field mapping(Form data)
    $form_data_required_flag = false;
    
    if(!isset($form_data['errors'])){
        $form_data_required_flag = true;
    }

    // Static form data
    $static_form_required_flag = false;
    if(!isset($static_form_data['errors'])){
        $static_form_required_flag = true;
    }

    if(is_array($crm_details)){
        if(isset($crm_details['selected_CRM'])){
            unset($crm_details['selected_CRM']);
        }

        if($auth_type == 'api_keys'){
            unset($crm_details['API_location']);
            if(in_array($requesting_method, array('POST','PUT'))){
                if($content_type == 'application/json' && (count($crm_details) == 7)){
                    $crm_details_required_data = true;
                }elseif(count($crm_details) == 6){
                    $crm_details_required_data = true;
                }
            }elseif(in_array($requesting_method, array('GET','DELETE'))){
                if(isset($crm_details['content_type'])){
                    unset($crm_details['content_type']);
                }
                if(isset($crm_details['requesting_body_format'])){
                    unset($crm_details['requesting_body_format']);
                }
                if(count($crm_details) == 5){
                    $crm_details_required_data = true;
                }
            }
            
        }

        if($auth_type == 'bearer_token'){
            if(in_array($requesting_method, array('POST','PUT'))){
                if($content_type == 'application/json' && (count($crm_details) == 6)){
                    $crm_details_required_data = true;
                }elseif(count($crm_details) == 5){
                    $crm_details_required_data = true;
                }
            }elseif(in_array($requesting_method, array('GET','DELETE'))){
                if(isset($crm_details['content_type'])){
                    unset($crm_details['content_type']);
                }
                if(isset($crm_details['requesting_body_format'])){
                    unset($crm_details['requesting_body_format']);
                }
                if(count($crm_details) == 4){
                    $crm_details_required_data = true;
                }

            }
            
        }

        if($auth_type == 'basic_auth'){
            if(in_array($requesting_method, array('POST','PUT'))){
                if($content_type == 'application/json' && (count($crm_details) == 7)){
                    $crm_details_required_data = true;
                }elseif(count($crm_details) == 6){
                    $crm_details_required_data = true;
                }
            }elseif(in_array($requesting_method, array('GET','DELETE'))){
                if(isset($crm_details['content_type'])){
                    unset($crm_details['content_type']);
                }
                if(isset($crm_details['requesting_body_format'])){
                    unset($crm_details['requesting_body_format']);
                }
                if(count($crm_details) == 5){
                    $crm_details_required_data = true;
                }
            }
            
        }
        if($auth_type == 'oauth_2.0'){
            unset($crm_details['revoke_url']);
            if(in_array($requesting_method, array('POST','PUT'))){
                if($content_type == 'application/json' && (count($crm_details) == 10)){
                    $crm_details_required_data = true;
                }elseif(count($crm_details) == 9){
                    $crm_details_required_data = true;
                }
            }elseif(in_array($requesting_method, array('GET','DELETE'))){
                if(isset($crm_details['content_type'])){
                    unset($crm_details['content_type']);
                }
                if(isset($crm_details['requesting_body_format'])){
                    unset($crm_details['requesting_body_format']);
                }
                if(count($crm_details) == 8){
                    $crm_details_required_data = true;
                }
            }
        }

        if($auth_type == ''){
            if(in_array($requesting_method, array('POST','PUT'))){
                if($content_type == 'application/json' && (count($crm_details) == 5)){
                    $crm_details_required_data = true;
                }elseif(count($crm_details) == 4){
                    $crm_details_required_data = true;
                }
            }elseif(in_array($requesting_method, array('GET','DELETE'))){
                if(isset($crm_details['content_type'])){
                    unset($crm_details['content_type']);
                }
                if(isset($crm_details['requesting_body_format'])){
                    unset($crm_details['requesting_body_format']);
                }
                if(count($crm_details) == 3){
                    $crm_details_required_data = true;
                }
            }
        }
        
    }

   
    if($static_form_required_flag && $form_data_required_flag && $extra_header_required_flag && $submitting_form_flag && $crm_details_required_data){
        return true;
    }

    return false;
  
}
}


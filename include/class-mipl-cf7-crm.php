<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MIPL_CF7_CRM{ 

    //update CRM details
    function mipl_cf7_update_crm_data( $post_id, $post, $update ){

        global $wpdb;
        if ( !$update ){
            return false;
        }
        
        // Checked post type
        if ( $post->post_type != 'mipl_cf7_crm' ){
            return false;
        }

        // Check post status
        $mipl_post_status = array( 'publish' );
        if( !in_array( $post->post_status, $mipl_post_status ) ){
            return false;
        }

        // Check user status 
        if(!(is_user_logged_in()) || !(current_user_can('administrator'))){
            return false;
        }

        // verified nonce
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_crm_nonce']) ? sanitize_text_field($_POST['mipl_cf7_crm_nonce']) : '';
        $mipl_verify_nonce = wp_verify_nonce(trim($mipl_cf7_crm_nonce), 'mipl_cf7_crm'.$post_id);
        if(!$mipl_verify_nonce){
            return false;
        }

        $common_error_msg = array();

        // validate requesting body.
        $requesting_body_details = isset($_POST['mipl_cf7_crm_details']['requesting_body_format']) ? sanitize_text_field(stripslashes($_POST['mipl_cf7_crm_details']['requesting_body_format'])) : '';
        $requesting_body_details = str_ireplace(array('[fields-key-value-array]', '[fields-name-value-array]', '[query-string-format]'), array('[]', '[]', ''), $requesting_body_details);
        $validated_req_body = json_decode( $requesting_body_details, true );
        
        if((!isset($validated_req_body) || !is_array($validated_req_body)) && 
        (isset($_POST['mipl_cf7_crm_details']['content_type']) && $_POST['mipl_cf7_crm_details']['content_type'] == 'application/json')){
            if(!empty($_POST['mipl_cf7_crm_details']['requesting_body_format']) && in_array($_POST['mipl_cf7_crm_details']['requesting_method'],array('POST','PUT'))){
                $common_error_msg['requsting_body'] = sanitize_text_field("Requesting body format is invalid!");
            }
        }

        //Update CRM details
        $detail_errors = array();
        if( isset( $_POST['mipl_cf7_crm_details'] ) ){
            
            $crm_details_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_details', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $crm_array = mipl_cf7_crm_details_validation_array( $crm_details_post_data );
            $crm_details_validation = new MIPL_CF7_Input_Validation( $crm_array, $crm_details_post_data );
            $crm_details_validation->validate();
            $detail_errors = !empty( $crm_details_validation->get_errors() ) ? 
            $crm_details_validation->get_errors() : array();
            $post_data = $crm_details_validation->get_valid_data();
            $auth_type = !empty( $_POST['mipl_cf7_crm_details']['authentication_type'] ) ? sanitize_text_field($_POST['mipl_cf7_crm_details']['authentication_type']) : '';
            $post_data['crm_url'] = isset($crm_details_post_data['crm_url']) ? sanitize_text_field($crm_details_post_data['crm_url']) : '';
            if( $auth_type != "oauth_2.0" ){
                update_post_meta( $post_id, '_mipl_oauth_details', '' );            
            }
            update_post_meta( $post_id, '_mipl_cf7_crm_details', $post_data );
            update_post_meta( $post_id, '_mipl_cf7_crm_details_error', $detail_errors );
        }

        //Update CRM extra header
        if( isset( $_POST['mipl_cf7_headers_data'] ) ){
            $header_post_data = filter_input(INPUT_POST, 'mipl_cf7_headers_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $header_data = mipl_cf7_header_validate_data( $header_post_data );
            
            if(isset($header_data['errors'])){
                $common_error_msg['extra_header'] = sanitize_text_field('Please enter the valid config details(In Header).');
            }

            update_post_meta( $post_id, '_mipl_cf7_extra_headers_data', $header_data );
        }
        
        if( !isset( $_POST['mipl_cf7_headers_data'] ) && $post->post_status == 'publish' ){
            update_post_meta( $post_id, '_mipl_cf7_extra_headers_data', '' );
        }

        //Update Form data
        if( isset( $_POST['mipl_cf7_crm_form_data'] ) ){
            $forms_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_form_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $fields_mapping = mipl_cf7_fields_mapping_validate_data( $forms_post_data );
            update_post_meta( $post_id, '_mipl_cf7_crm_form_data', $fields_mapping );

            if(isset($fields_mapping['errors'])){
                $common_error_msg['form_data'] = sanitize_text_field('Please enter the valid config details(In Fields Mapping).');
            }
        }
        if( !isset( $_POST['mipl_cf7_crm_form_data'] ) && $post->post_status == 'publish' ){
            update_post_meta( $post_id, '_mipl_cf7_crm_form_data', '' );
        }

        if(isset($_POST['mipl_cf7_crm_field_mapping_form'])){
            $form_id = sanitize_text_field($_POST['mipl_cf7_crm_field_mapping_form']);
            update_post_meta( $post_id, 'mipl_cf7_crm_field_mapping_form', $form_id);
        }


        //Static form data
        if( isset( $_POST['mipl_cf7_crm_default_fields'] ) ){
            $static_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_default_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $Static_form_fields = mipl_cf7_static_fields_validate_data( $static_post_data );
           
            update_post_meta( $post_id, '_mipl_cf7_crm_default_data', $Static_form_fields );

            if(isset($Static_form_fields['errors'])){
                $common_error_msg['static_form'] = sanitize_text_field('Please enter the valid config details(In Static Fields Mapping).');
            }
        }

        if(isset($fields_mapping['CRM_fields']) && isset($Static_form_fields['crm_default_field_keys'])){
            $duplicated_field_key = array_intersect($fields_mapping['CRM_fields'],$Static_form_fields['crm_default_field_keys']);

            if(!empty($duplicated_field_key)){
                $common_error_msg['duplicated_mapping_field_key'] = sanitize_text_field('"field-name" is duplicate in CRM fields & Static fields.');
            }
        }

        $requesting_method = isset($post_data['requesting_method']) ? $post_data['requesting_method'] : '';
        $authentication_type = isset($post_data['authentication_type']) ? $post_data['authentication_type'] : '';
        $API_location = isset($post_data['API_location']) ? $post_data['API_location'] : '';
        $API_key = isset($post_data['API_key']) ? $post_data['API_key'] : '';
        if(isset($fields_mapping['CRM_fields']) && is_array($fields_mapping['CRM_fields'])){
           
            if( $requesting_method == 'GET' && $authentication_type == 'api_keys' && $API_location == 'query_params' && in_array($API_key, $fields_mapping['CRM_fields'])){
                $common_error_msg['duplicated_field_key'] = sanitize_text_field('"field-name" is duplicate in authentication & mapped fields');
            }
           
        }
        if(isset($Static_form_fields['crm_default_field_keys']) && is_array($Static_form_fields['crm_default_field_keys'])){
            if( $requesting_method == 'GET' && $authentication_type == 'api_keys' && $API_location == 'query_params' && in_array($API_key, $Static_form_fields['crm_default_field_keys'])){
                $common_error_msg['duplicated_field_key'] = sanitize_text_field('"field-name" is duplicate in authentication & mapped fields');
            }
        }
        if(isset($header_data['extra_field_keys']) && is_array($header_data['extra_field_keys'])){
            if( $authentication_type == 'api_keys' && $API_location == 'header' && in_array($API_key, $header_data['extra_field_keys'])){
                $common_error_msg['duplicated_field_key'] = sanitize_text_field('"field-name" is duplicate in authentication & headers');
            }
        }
        
      
        if( !isset( $_POST['mipl_cf7_crm_default_fields'] ) && $post->post_status == 'publish' ){
            update_post_meta( $post_id, '_mipl_cf7_crm_default_data', '' );
        } 
        
        //CRM email setting
        $email_errors = array();
        if( isset( $_POST['mipl_cf7_crm_email'] ) ){
            if( !isset($_POST['mipl_cf7_crm_email']['error_display']) ){
                $_POST['mipl_cf7_crm_email']['error_display'] = "";
            }
            $email_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_email', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $crm_email_array = mipl_cf7_crm_email_details_validation_array($email_post_data);
            $crm_email_details = new MIPL_CF7_Input_Validation( $crm_email_array, $email_post_data );
            $crm_email_details->validate();
            $email_errors    = !empty( $crm_email_details->get_errors() ) ? $crm_email_details->get_errors() : array();

            update_post_meta( $post_id, '_mipl_cf7_crm_email_configuration', 'no' );
            if(count($email_errors)>0){
                update_post_meta( $post_id, '_mipl_cf7_crm_email_configuration', 'yes' );
            }

            $post_data       = $crm_email_details->get_valid_data();
            update_post_meta( $post_id, '_mipl_cf7_crm_email_setting', $post_data );
        }

        if( !isset( $_POST['mipl_cf7_crm_submission_setting']['crm_submission_on'] ) ){
            $_POST['mipl_cf7_crm_submission_setting']['crm_submission_on'] = array();
        }

        //Update crm submission setting
        if( isset( $_POST['mipl_cf7_crm_submission_setting'] ) ){
            $setting_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_submission_setting', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $crm_settings = mipl_cf7_crm_setting_validate_data( $setting_post_data );
            if( isset( $crm_settings ) ){
                update_post_meta( $post_id, '_mipl_cf7_crm_settings', $crm_settings );
                if(isset($crm_settings['crm_form_submission'][0])){
                    update_post_meta( $post_id, '_mipl_cf7_crm_submission_form', $crm_settings['crm_form_submission'][0] );
                }
            }
            if(isset($crm_settings['errors'])){
                $common_error_msg['crm_setting'] = sanitize_text_field('Please enter the valid config details(In CRM Settings).');
            }
        }

        //error message
        update_post_meta($post_id, '_mipl_cf7_crm_error_config', 'no');
        $errors = array_merge( $email_errors, $detail_errors, $common_error_msg ); 
        if( !empty( $errors ) ){
            foreach( $errors as $error_key => $error_val ){
                update_post_meta($post_id, '_mipl_cf7_crm_error_config', 'yes');
                if( empty( $_SESSION['mipl_cf7_admin_notices']['error'] ) ){
                    $_SESSION['mipl_cf7_admin_notices']['error'] = sanitize_text_field($error_val);
                }else{
                    $_SESSION['mipl_cf7_admin_notices']['error'] .= wp_kses_post("<br>".$error_val);
                }
            }
        }
    }


    //when authentiation fields are blank or other error after curl request for that email prepare 
    function mipl_error_email( $crm_email_to ){
        $subject = 'oauth2.0 error';
        $body    = "Authentication Error: <br> Your some authentication fields are missing or wrong redirect URL";
        $headers  = "MIME-Version: 1.0 \r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
        $mail    = wp_mail( $crm_email_to, $subject, $body, $headers );

    }

    //rest api callback function for oAuth2.0 authentication
    function mipl_cf7_oauth_rest_api_callback( $data ){

        $id = $data->get_param('id');
        if( empty($id) ){
            return false;
        }

        $mipl_state = get_post_meta($id, '_mipl_cf_ck_oauth2_state',true);
       
        if( isset($_GET['state']) && $_GET['state'] == $mipl_state ){
            $crm_deatails  = get_post_meta( $id, '_mipl_cf7_crm_details', true );
            $client_id     = isset( $crm_deatails['client_id'] ) && !empty( $crm_deatails['client_id'] ) ? $crm_deatails['client_id'] : "";
            $client_secret = isset( $crm_deatails['client_secret'] ) && !empty($crm_deatails['client_secret']) ? $crm_deatails['client_secret'] : "";            
            $crm_email_details  = get_post_meta( $id, '_mipl_cf7_crm_email_setting', true );
            $crm_email          = isset( $crm_email_details['email_to'] ) ? $crm_email_details['email_to'] : '';
            $access_token_url = isset( $crm_deatails['access_token_url'] ) ? $crm_deatails['access_token_url'] : "";
            if(!empty($access_token_url)){
                $access_token_url = str_replace("?", "", $access_token_url);
            }
        
            $oauth_data = array( $client_id, $client_secret, $access_token_url );
            $oauth_data_count = count( array_filter( $oauth_data, function($value) { return !is_null($value) && $value !== ''; } ) );

            if( $oauth_data_count < 3 ){
               //error mail 
                if( !empty( $crm_email ) ){
                    $this->mipl_error_email( $crm_email );
                }
                // Redirection
                $url = admin_url( 'post.php?post='.$id.'&action=edit' );
                header( 'Location:'.$url );
                die();
            }
    
            $redirect_url = get_rest_url( null, 'mipl-cf7-crm/v1/crm/oauth/'.$id );
            $mipl_auth_code = !empty( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
            
            $arr2 = array(
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'code'          => $mipl_auth_code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => $redirect_url,
            );
            $att = array(
                'body'        => http_build_query( $arr2 ), 
                'method'      => 'POST',   
                'header' => array( 'Content-Type: application/x-www-form-urlencoded' ), 
            );
            $oauth_resp = wp_remote_request( $access_token_url, $att );

            // Check resp status code
            if( $oauth_resp['response']['code'] == 200 ){
                
                update_post_meta( $id, '_mipl_oauth_details', base64_encode($oauth_resp['body']) );
                update_post_meta( $id, '_mipl_oauth_refresh_generation_time', gmdate("Y-m-d H:i:s") );
            }else{ 
                // Send error mail
                if( !empty( $crm_email ) ){
                    $this->mipl_error_email( $crm_email );
                }
            }
            

        }
        update_post_meta($id,'_mipl_cf_ck_oauth2_state','');

        $url = admin_url( 'post.php?post='.$id.'&action=edit' );
        header( 'Location:'.$url );
        die();        
    }

    //oauth redirect function for oAuth2.0 access token and refresh token
    function mipl_cf7_oauth_redirect(){

        if(!(isset($_GET['crm_id'])) || empty($_GET['crm_id'])){
            return false;
        }

        $crm_id       = sanitize_text_field( $_GET['crm_id'] );
        $crm_deatails = get_post_meta( $crm_id, '_mipl_cf7_crm_details', true );
        $redirect_url = get_rest_url( null, 'mipl-cf7-crm/v1/crm/oauth/'.$crm_id );
        $client_id    = isset( $crm_deatails['client_id'] ) && !empty($crm_deatails['client_id']) ? $crm_deatails['client_id'] : "";
        $scope        = isset( $crm_deatails['scope'] ) ? $crm_deatails['scope'] : "";
        $crm_email_details  = get_post_meta( $crm_id, '_mipl_cf7_crm_email_setting', true );
        $crm_email          = isset( $crm_email_details['email_to'] ) ? $crm_email_details['email_to'] : '';
        $authorization_url  = isset( $crm_deatails['authorization_url'] ) ? $crm_deatails['authorization_url'] : "";
        $oauth_data         = array( $redirect_url, $client_id, $authorization_url, $scope );
        $oauth_data_count   = count( array_filter( $oauth_data, function($value) { return !is_null($value) && $value !== ''; } ) );

        if(!empty($authorization_url)){
            $authorization_url = str_replace("?", "", $authorization_url);
        }
        
        if( $oauth_data_count < 3 ){           
            // Error Mail
            if( !empty( $crm_email ) ){
                $this->mipl_error_email( $crm_email );
            }
            // Redirection
            $url = admin_url( 'post.php?post='.$crm_id.'&action=edit' );
            header( 'Location:'.$url );
            die();

        }
        
        $state = mipl_cf7_rand();
        update_post_meta($crm_id, '_mipl_cf_ck_oauth2_state', $state);

        $arr = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_url,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $state,
            'access_type'   => 'offline',
            'prompt'        => 'consent'
        );
        
        header( 'Location: '.$authorization_url.'?'.http_build_query($arr) );
        die();

    }
    
    //Reset authentication(oauth2.0)
    function mipl_cf7_crm_reset_action(){

        if(!isset($_GET['id']) || empty($_GET['id'])){
            return false;
        }

        $post_id = sanitize_text_field( $_GET['id'] );
        
        // verified nonce
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_crm_nonce']) ? sanitize_text_field($_POST['mipl_cf7_crm_nonce']) : '';
        mipl_cf7_verify_nonce( $post_id, $mipl_cf7_crm_nonce );


        $crm_deatails = get_post_meta( $post_id, '_mipl_cf7_crm_details', true );
        $revoke_url    = isset( $crm_deatails['revoke_url'] ) ? $crm_deatails['revoke_url'] : "";
        $client_id     = isset( $crm_deatails['client_id'] ) ? $crm_deatails['client_id'] : "";
        $client_secret = isset( $crm_deatails['client_secret'] ) ? $crm_deatails['client_secret'] : "";

        if(isset($crm_deatails['client_id'])){
            unset($crm_deatails['client_id']);
        }
        if(isset($crm_deatails['client_secret'])){
            unset($crm_deatails['client_secret']);
        }
        update_post_meta( $post_id, '_mipl_cf7_crm_details', $crm_deatails );
    
        if(empty($revoke_url)){
            update_post_meta( $post_id, '_mipl_oauth_details', '' );
            $resp = array( 'status'=>'success', 'message'=>__('successfully reset') );
            $_SESSION['mipl_cf7_admin_notices']['success'] = sanitize_text_field("Successfully Reset Client ID and  Client Secret.");
            echo wp_json_encode( $resp );
            die();
        }

        $oauth_details = base64_decode( get_post_meta( $post_id, '_mipl_oauth_details', true ) );
        $crm_email_details  = get_post_meta( $post_id, '_mipl_cf7_crm_email_setting', true );
        $crm_email          = !empty( $crm_email_details['email_to'] ) ? $crm_email_details['email_to'] : '';

        $oauth_data         = array( $client_id, $client_secret, $revoke_url );
        $oauth_data_count   = count( array_filter( $oauth_data, function($value) { return !is_null($value) && $value !== ''; } ) );
      
        if( $oauth_data_count < 3 ){
            //error mail 
            if( !empty( $crm_email ) ){
                $this->mipl_error_email( $crm_email );
            }
            // Redirection
            $url = admin_url( 'post.php?post='.$post_id.'&action=edit' );
            header( 'Location:'.$url );
            die();
        }

        if( !empty($oauth_details) ){
            $decoded_details = json_decode( $oauth_details, true );
            $access_token = isset($decoded_details['access_token'])?$decoded_details['access_token']:'';
            $refresh_token = isset($decoded_details['refresh_token'])?$decoded_details['refresh_token']:'';
            $old_data = array( '{refresh_token}', '{token}', '{access_token}', '{client_id}', '{client_secret}' );
            $new_data = array( $refresh_token, $access_token, $access_token, $client_id, $client_secret );
            $url_with_data = str_ireplace( $old_data, $new_data, $revoke_url );
        }

        // $arr2 = array(
        //     'token'     => $refresh_token,
        //     'client_id' => $client_id,
        //     'client_secret' => $client_secret
        //     );

        $att = array(
            'method'      => 'POST',   
            'header' => array( 'Content-Type: application/json' ), 
        );
        
        $oauth_resp = wp_remote_request( $url_with_data, $att );
        
        update_post_meta( $post_id, '_mipl_oauth_details', '' );
        
        $resp = array( 'status'=>'success', 'message'=>__('Successfully Reset Client ID and  Client Secret.') );

        if( $oauth_resp['response']['code'] == 200 ){
            $resp = array( 'status'=>'success', 'message'=>__('App successfully revoked.') );
        }
        $_SESSION['mipl_cf7_admin_notices']['success'] = sanitize_text_field($resp['message']);
        echo wp_json_encode( $resp );
        die();

    }
    
  
    //Save authentication details(oauth2.0)
    function mipl_cf7_auth_field_save(){
        
        // verified nonce
        if(!isset($_GET['id']) || empty($_GET['id'])){
            return false;
        }

        $post_id = sanitize_text_field( $_GET['id'] );
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_crm_nonce']) ? sanitize_text_field($_POST['mipl_cf7_crm_nonce']) : '';
        mipl_cf7_verify_nonce( $post_id, $mipl_cf7_crm_nonce );

        if( isset( $post_id ) ){
            $crm_detail_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_details', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $crm_array = mipl_cf7_crm_details_validation_array( $crm_detail_post_data );
            $crm_details_validation = new MIPL_CF7_Input_Validation( $crm_array, $crm_detail_post_data );
            $crm_details_validation->validate();
            $detail_errors = $crm_details_validation->get_errors();
            $post_data = $crm_details_validation->get_valid_data();
            update_post_meta( $post_id, '_mipl_cf7_crm_details', $post_data );
        }

        $_SESSION['mipl_cf7_admin_notices']['error'] = '';
        $resp = array( 'status'=>'success', 'message'=>__('Successfully saved') );

        if( is_array($detail_errors) && !empty( $detail_errors ) ){
            foreach( $detail_errors as $error_key => $error_val ){
                $_SESSION['mipl_cf7_admin_notices']['error'] .= $error_val.'<br>';
            }
            $resp = array( 'status'=>'fail', 'message'=>__('Configuration error') );
        }
    
        echo wp_json_encode($resp);
        die();
    }
    
    
    //prepare table of crm data(in email body)
    function crm_post_data( $data ){
        ob_start();
        ?>
        <div>
            <table style="width:100%">
                <?php
                if( is_array($data) ){
                    foreach($data as $key => $value){
                        
                        if( is_array($value) ){
                            $value = implode(', ', $value);
                        }
                        ?>
                        <tr>
                            <td style="width:20%"><?php echo esc_html($key)." :" ?></td>
                            <td style="width:80%"><?php echo esc_html($value) ?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }
    
    
    function mipl_get_crm_data_submission_response( $post_id, $final_array ){
        $lead_collection_data = [];
        $response = array();
        $crm_deatails        = get_post_meta( $post_id, '_mipl_cf7_crm_details', true );
        $crm_url             = isset($crm_deatails['crm_url']) ? $crm_deatails['crm_url'] : '';
        $requesting_method   = isset($crm_deatails['requesting_method']) ? $crm_deatails['requesting_method'] : '';
        $authentication_type = isset( $crm_deatails['authentication_type'] ) ? strtok( trim( $crm_deatails['authentication_type'] ), " " ) : "";
        $content_type        = isset($crm_deatails['content_type']) ? $crm_deatails['content_type'] : '';
        $requesting_body_data = isset( $crm_deatails['requesting_body_format'] ) ? $crm_deatails['requesting_body_format'] : "";

        $authentication_field = '';
        //No authentication
        if( isset( $crm_deatails['authentication_type'] ) && $crm_deatails['authentication_type'] == '' ){
            $authentication_field = '';
            $authentication_type = '';
        }

        //Bearer token authentication
        if( isset( $crm_deatails['authentication_type'] ) && $crm_deatails['authentication_type'] == 'bearer_token' ){
            $bearer_token        = !empty( $crm_deatails['bearer_token'] ) ? $crm_deatails['bearer_token'] : "";
            $authentication_type = "Bearer";
            $authentication_field = $bearer_token;
        }

        //Basic authentication
        if( isset( $crm_deatails['authentication_type'] ) && $crm_deatails['authentication_type'] == 'basic_auth' ){
            $basic_auth_username = !empty( $crm_deatails['basic_auth_username'] ) ? $crm_deatails['basic_auth_username'] : "";
            $basic_auth_password = !empty( $crm_deatails['basic_auth_password'] ) ? $crm_deatails['basic_auth_password'] : "";
            $authentication_type = "Basic";
            if(!empty($basic_auth_username) && !empty($basic_auth_password)){
                $authentication_field = base64_encode( $basic_auth_username. ':' .$basic_auth_password );
            }
        }

        //oauth2.0
        if( isset( $crm_deatails['authentication_type'] ) && $crm_deatails['authentication_type'] == 'oauth_2.0' ){
            $client_id            = $crm_deatails['client_id'];
            $client_secret        = $crm_deatails['client_secret'];
            $access_token_url     = $crm_deatails['access_token_url'];
            $oauth_details   = base64_decode( get_post_meta( $post_id, '_mipl_oauth_details', true ) );
            $decoded_details = json_decode( $oauth_details, true );
            $expires_in      = !empty( $decoded_details['expires_in'] ) ? $decoded_details['expires_in'] : '';
            $refresh_token   = !empty( $decoded_details['refresh_token'] ) ? $decoded_details['refresh_token'] : "";
            $generated_time  = get_post_meta( $post_id, '_mipl_oauth_refresh_generation_time', true );
            $current_time    = gmdate( "Y-m-d H:i:s" );
            $time = strtotime( $current_time )-strtotime( $generated_time );
            
            //Regenarate access token when expired
            if( !empty( $expires_in ) && $expires_in < $time ){
                $arr2 = array(
                    'client_id'     => $client_id,
                    'client_secret' => $client_secret,
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refresh_token,
                );
                $att = array(
                    'body'        => http_build_query( $arr2 ), 
                    'method'      => 'POST',   
                    'header' => array( 'Content-Type:application/x-www-form-urlencoded' ), 
                );
    
                $oauth_access_token = wp_remote_request( $access_token_url, $att );
                $oauth_access_token_data = !empty( $oauth_access_token["body"] ) ? $oauth_access_token["body"] : '';
            }else{
    
                $oauth_access_token_data = $oauth_details;
            }
            $access_token_body = $oauth_access_token_data;
            $decoded_data      = json_decode( $access_token_body, true );
            $access_token      = isset( $decoded_data['access_token'] ) ? $decoded_data['access_token'] : '';
            $authentication_field = $access_token;
            $authentication_type  = 'Bearer';

        }
        
        //Data preparation according to content type
        if( $content_type == 'application/json' && !empty( $requesting_body_data ) && in_array($requesting_method, array('POST','PUT'))){
            $key_value_data_array = wp_json_encode( $final_array );
            $name_value_data_array = array();
            foreach( $final_array as $name => $value ){
                $name_value_data_array[] = array('name' => $name, 'value' => $value);
            }
            $query_string_format = http_build_query( $final_array );
            $name_value_json = wp_json_encode( $name_value_data_array );
            $old_requesting_body_data     = array( '[fields-key-value-array]', '[fields-name-value-array]', '[query-string-format]' );
            $update_requesting_body_data  = array( $key_value_data_array, $name_value_json, $query_string_format );
            $data = str_replace( $old_requesting_body_data, $update_requesting_body_data, $requesting_body_data );
                                
        }elseif( $content_type == 'application/x-www-form-urlencoded' && in_array($requesting_method, array('POST','PUT'))){
            $data = http_build_query( $final_array );
        }elseif( $content_type == 'text/plain' && in_array($requesting_method, array('POST','PUT'))){
            $data_string = "";
            foreach ($final_array as $key => $value) {
                if(is_array($value)){
                    $value = implode(',', $value);
                }
                $data_string .= "$key: $value\n"; 
            }
            $data = $data_string;
        }else{
            $data = $final_array;
        }
       
        //Prepared extra header and default header
        $get_extra_header_data = get_post_meta( $post_id, '_mipl_cf7_extra_headers_data', true );
        if( !empty( $get_extra_header_data ) ){
            foreach( $get_extra_header_data['extra_field_keys'] as $position => $value ){
                $header_key = $get_extra_header_data['extra_field_keys'][$position];
                $header_value =  $get_extra_header_data['extra_field_values'][$position];
                $extra_header_data[$header_key] = $header_value;
            }
        }else{
            $extra_header_data = array();
        }

        //API Authentication header
        if( isset( $crm_deatails['authentication_type'] ) && $crm_deatails['authentication_type'] == 'api_keys' ){
            $API_key   = $crm_deatails['API_key'];
            $API_value = $crm_deatails['API_value'];
            $API_location = $crm_deatails['API_location'];
            if($API_location == 'header'){
                $default_header_data = array(
                    $API_key => $API_value,
                    'Content-Type'=>$content_type
                );
            }else{
                $url_query = parse_url($crm_url, PHP_URL_QUERY);
                if($url_query){
                    $crm_url .= '&'.$API_key.'='.$API_value;
                }else{
                    $crm_url .= '?'.$API_key.'='.$API_value;
                }
                $default_header_data = array(
                    'Content-Type'=>$content_type
                );

            }
            
        }else{
            $default_header_data = array(
                'Authorization'=>$authentication_type.' '.$authentication_field,
                'Content-Type'=>$content_type
            );
        }

        $post_title = get_the_title($post_id);
        $header_data = $default_header_data;
        if(count($extra_header_data) > 0){
            foreach ($extra_header_data as $e_header_key => $e_header_value) {
                $header_data[$e_header_key] = $e_header_value;
            }
        }
       
        $lead_collection_data['requesting_header'] = $header_data;
        $lead_collection_data['requesting_body'] = $data;
        $lead_collection_data['crm_form_data'] = $final_array;
        $lead_collection_data['submitted_date'] = date('Y-m-d H:i:s');
        $lead_collection_data['crm_post'] = array('post_id'=>$post_id, 'post_title'=>$post_title);
        $lead_collection_data['authentication_type'] = $authentication_type;
        $lead_collection_data['crm_url'] = $crm_url;

        //CRM submission
        $args = array(
            'method' => $requesting_method,
            'http_header' => $header_data,
            'header' => true
        );

        if( in_array($requesting_method, array('POST','PUT','PATCH')) ){
            $args['data'] = $data;
        }

        $lead_collection_data['crm_requesting_body'] = $data;

        if( in_array($requesting_method, array('GET','DELETE')) ){
            $data = isset($data) ? http_build_query($data) : '';
            $crm_url = $this->prepared_remote_request_url($crm_url, $data);

        }
        $response = mipl_cf7_curl_request($crm_url, $args);
        $return_data['response'] = $response;
        $return_data['lead_collection_data'] = $lead_collection_data;
        // $response = wp_remote_request( $crm_url, $args );
       
        return $return_data;

    }

    // Lead collection.
    function mipl_store_crm_leads($contact_form_id, $post_id, $submitted_data, $lead_collection_data){
        $post_arr = array(
            'post_type'  => 'mipl_crm_leads',
            'post_status' => 'publish',
            'post_author' => 1,
        );
        $post_id = wp_insert_post( $post_arr );
        add_post_meta($post_id, '_mipl_cf7_crm_submitted_data', $submitted_data);
        add_post_meta($post_id, '_mipl_lead_collection_data', $lead_collection_data);
       
    }

    function prepared_remote_request_url($crm_url, $data){
        $parsed_url = parse_url($crm_url);

        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';

        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
      
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
      
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
      
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
      
        $pass     = ($user || $pass) ? "$pass@" : '';
      
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
      
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'].'&'.$data : '?'.$data;
        
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";

    }
    
   
    function mipl_cf7_crm_submission( $data ){
      
        $current_filter_hook = current_action();
       
        $contact_form = WPCF7_ContactForm::get_current();
        $contact_form_id = $contact_form->id;
        
        $args_cf7 = array(
            'post_type'   => MIPL_CF7_CRM_POST_TYPE,
            'post_status' => 'publish',
            'numberposts' => 1,
            'meta_key' => '_mipl_cf7_crm_submission_form',
            'meta_value' => $contact_form_id
        );

        $crm_posts = get_posts($args_cf7);
       
        if(is_array($crm_posts) && count($crm_posts) == 0){
            return false;
        }

        $crm_post_id = $crm_posts[0]->ID;

        $crm_form_submission_settings = get_post_meta( $crm_post_id, '_mipl_cf7_crm_settings', true );
        $crm_submission_on = !empty($crm_form_submission_settings['crm_submission_on']) ? $crm_form_submission_settings['crm_submission_on'] : array();

        if (!in_array($current_filter_hook, $crm_submission_on)) {
            return false;   
        }
    
        $headers = '';
        $valid_to_crm_submission = mipl_cf7_required_crm_data($crm_post_id);
        
        $crm_email_data  = get_post_meta( $crm_post_id, '_mipl_cf7_crm_email_setting', true );
        if( !$valid_to_crm_submission ){
            if(isset($crm_email_data['email_to']) && !empty($crm_email_data['email_to'])){
                $headers .= "MIME-Version: 1.0 \r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
                $mail    = wp_mail($crm_email_data['email_to'], 'MIPL CF7 CRM Submission Error', "Required fields for crm submission is not valid. Please check crm integration configuration!", $headers);
            }
            return false;
        }

        $submission = WPCF7_Submission::get_instance();
        
        $uploaded_files = array();
        if ( $submission ) {
            $cf7_data = $submission->get_posted_data();
            $uploaded_files = $submission->uploaded_files();
        }
       
        $cf7_submitted_data = $cf7_data;

        $crm_submitting_data = array();

        $crm_form_data = get_post_meta( $crm_post_id, '_mipl_cf7_crm_form_data');
        
        $crm_form_submission = !empty($crm_form_submission_settings['crm_form_submission']) ? $crm_form_submission_settings['crm_form_submission'] : array();
        
        $crm_form_fields_name = !empty($crm_form_submission_settings['crm_form_fields_name']) ? $crm_form_submission_settings['crm_form_fields_name'] : array();
        
        $crm_form_fields_value = !empty($crm_form_submission_settings['crm_form_fields_value']) ? $crm_form_submission_settings['crm_form_fields_value'] : array();

        $changed_file_data = array(); 
        if(!empty($crm_form_data)){
            foreach ($crm_form_data as $post_position => $form_data) {
                if(!empty($form_data)){
                    $fields_path = array();
                    foreach ($form_data['CRM_fields'] as $fld_position => $fld_value) {
                        $selected_wc_data = explode('/', $form_data['cf7_fields'][$fld_position]);
                        $fields_data = $cf7_data[$selected_wc_data[1]];
                        $data_type_value = $form_data['data_type'][$fld_position];
                        $field_type = mipl_cf7_field_type($selected_wc_data[0], $selected_wc_data[1]);
                        
                        if($field_type == "date"){
                            if($data_type_value != 'Default'){
                                $fields_data = date($data_type_value, strtotime($fields_data));
                            }
                        }
                        
                        if($field_type == "file"){
                            $fields_data = '';
                            $uploads_dir = wp_upload_dir();

                            if ($_FILES[$selected_wc_data[1]]["error"] == UPLOAD_ERR_OK) {

                                $file_name = sanitize_text_field($_FILES[$selected_wc_data[1]]['tmp_name']);
                                $f_name = sanitize_text_field($_FILES[$selected_wc_data[1]]['name']);
                                $name = basename($f_name);
                                $san_file_name = sanitize_text_field($GLOBALS['mipl_cf7_file_fields'][$file_name]);
                                $base_dir_path = $uploads_dir['basedir'].'/mipl-cf7-crm/';
                                $base_dir_url = $uploads_dir['baseurl'].'/mipl-cf7-crm/';
                                $file_url = $base_dir_url.$san_file_name;
                                $file_path = $base_dir_path.$san_file_name;
                                $fields_data = '@'.$file_path;
                            }

                            if($data_type_value == 'file_url'){
                                $fields_data = $file_url;
                            }
                            
                            $files_name = mipl_cf7_file_fields_name($contact_form_id);
                            $uploaded_files_urls = array();
                            foreach ($files_name as $file_key => $file_name) {
                                $name = sanitize_text_field($_FILES[$file_name]['tmp_name']);
                                $san_file_name = sanitize_text_field( $GLOBALS['mipl_cf7_file_fields'][$name] );
                                $uploaded_files_urls[$file_name] = $base_dir_url.$san_file_name;

                                if($data_type_value == 'file_url'){continue;}

                                $fields_path[] = $base_dir_path.$san_file_name;
                            }
                            $changed_file_data[$form_data['CRM_fields'][$fld_position]] = $file_url;
                        }
                        $crm_submitting_data[$form_data['CRM_fields'][$fld_position]] = $fields_data;
                    }
                }
            }
        }

        if(!empty($uploaded_files)){
            foreach ($uploaded_files as $form_fld_name => $form_fld_value) {
                $file_name = '';
                if(is_array($form_fld_value) && (isset($form_fld_value[0]) && !empty($form_fld_value[0]))){
                    $file_name = basename($form_fld_value[0]);
                }
                $cf7_submitted_data[$form_fld_name] = $file_name;
            }
        }
            
        $static_fields = get_post_meta( $crm_post_id, '_mipl_cf7_crm_default_data', true );
       
        if(!empty($static_fields)){
            foreach($static_fields['crm_default_field_keys'] as $position => $value){
                $crm_submitting_data[$static_fields['crm_default_field_keys'][$position]] = $static_fields['crm_default_field_values'][$position];
            }
        }
      
        $lead_collection_submitting_data = [];
        $headers = '';
        if(in_array($contact_form_id, $crm_form_submission)){
            
            if((isset($crm_form_fields_value[0]) && isset($cf7_data[$crm_form_fields_name[0]])) && (in_array($crm_form_fields_value[0],$cf7_data[$crm_form_fields_name[0]])) || (empty($crm_form_fields_value[0]) && empty($crm_form_fields_name[0]))){

                $return_data = $this->mipl_get_crm_data_submission_response($crm_post_id, $crm_submitting_data);

                $crm_submission_resp = $return_data['response'];
                $lead_collection_data = $return_data['lead_collection_data'];
                $contact_form_title = get_the_title($contact_form_id);
                $lead_collection_data['contact_form7_data'] = array('cf7_id'=>$contact_form_id, 'cf7_title'=>$contact_form_title);
                $lead_collection_submitting_data[$contact_form_id] = $crm_submitting_data;

                $email_crm_submitting_data = $crm_submitting_data;
                if(!empty($changed_file_data)){
                    foreach ($changed_file_data as $file_key => $file_value) {
                        $email_crm_submitting_data[$file_key] = $file_value;
                    }
                }
                
                $crm_post_data   = $this->crm_post_data($email_crm_submitting_data);

                $crm_resp_error = isset($crm_submission_resp['error']) ? $crm_submission_resp['error'] : '';
                $crm_resp        = isset($crm_submission_resp['body']) ? htmlentities($crm_submission_resp['body']) : '';
                $lead_collection_data['response_body'] = $crm_resp;
                $lead_collection_data['cf7_submitted_data'] = $cf7_submitted_data;
                $crm_resp_status = isset($crm_submission_resp['http_code'])? $crm_submission_resp['http_code'] : 0;
                $lead_collection_data['response_status'] = $crm_resp_status;
                if($crm_resp_status == 0 && (is_string($crm_resp_error) && str_contains($crm_resp_error, 'timed out'))){
                    $lead_collection_data['response_error'] = $crm_resp_error;
                }
                $lead_collection = !empty($crm_form_submission_settings['crm_store_lead'][0]) ? $crm_form_submission_settings['crm_store_lead'][0] : '';

                if($lead_collection == 'true'){
                    $this->mipl_store_crm_leads( $contact_form_id, $crm_post_id, $lead_collection_submitting_data, $lead_collection_data );
                }

                // Send mail
                $crm_body_data        = array(trim($crm_post_data), $crm_resp, $crm_resp_status);
                $this->mipl_cf7_crm_mail( $crm_post_id, $crm_body_data, $crm_resp_status );
                
            }
            
        }
         
    }


    function mipl_cf7_crm_form_submitting_data($crm_post_id, $crm_form_post_data, $file_data){
        
        $crm_submitting_data = array();
        $crm_form_data = get_post_meta( $crm_post_id, '_mipl_cf7_crm_form_data' );
        
        $crm_form_submission_settings = get_post_meta( $crm_post_id, '_mipl_cf7_crm_settings', true );
        $crm_form_submission = !empty($crm_form_submission_settings['crm_form_submission']) ? $crm_form_submission_settings['crm_form_submission'] : array();
        
        $crm_form_fields_name = !empty($crm_form_submission_settings['crm_form_fields_name']) ? $crm_form_submission_settings['crm_form_fields_name'] : array();
        
        $crm_form_fields_value = !empty($crm_form_submission_settings['crm_form_fields_value']) ? $crm_form_submission_settings['crm_form_fields_value'] : array();
        
        $crm_submission_on = !empty($crm_form_submission_settings['crm_submission_on']) ? $crm_form_submission_settings['crm_submission_on'] : array();
        
        $changed_file_data = array(); 
        if(empty($crm_form_data)){
            return false;
        }

        foreach ($crm_form_data as $post_position => $form_data) {
            if(!empty($form_data)){
                $fields_path = array();
                foreach ($form_data['CRM_fields'] as $fld_position => $fld_value) {
                    $crm_fld = $form_data['CRM_fields'][$fld_position];
                    $fields_data = isset($crm_form_post_data[$crm_fld])?$crm_form_post_data[$crm_fld]:'';
                    $data_type_value = $form_data['data_type'][$fld_position];
                    
                    if(in_array($data_type_value, array('Default','Y-m-d','Y-m-d H:i:s','Y/m/d H:i:s','m/d/Y H:i:s','d/m/Y H:i:s'))){
                        if($data_type_value != 'Default'){
                            $fields_data = date($data_type_value, strtotime($fields_data));
                        }
                    }
                    $crm_submitting_data[$form_data['CRM_fields'][$fld_position]] = $fields_data;
                    if(in_array($data_type_value, array('file_object', 'file_url'))){

                        $uploads_dir = wp_upload_dir();
                        if (isset($file_data['error'][$crm_fld]) && $file_data['error'][$crm_fld] == UPLOAD_ERR_OK) {

                            $tmp_name= sanitize_text_field($file_data['tmp_name'][$crm_fld]);
                            $f_name = isset($file_data['name'][$crm_fld])?sanitize_text_field($file_data['name'][$crm_fld]):'';
                            $name = mipl_cf7_rand().'-'.basename($f_name);
                            $pathinfo = pathinfo( $name);
                            $file_name = sanitize_title($pathinfo['filename']);
                            $file_extension = $pathinfo['extension'];
                            $new_file_name = $file_name.".".$file_extension;
                            $base_dir_path = $uploads_dir['basedir'].'/mipl-cf7-crm/';
                            $base_dir_url = $uploads_dir['baseurl'].'/mipl-cf7-crm/';
                            $file_url = $base_dir_url.$new_file_name;
                            $file_path = $base_dir_path.$new_file_name;
                            
                            if(!file_exists($base_dir_path)){
                                mkdir($base_dir_path, 0775, true);
                            }

                            $upload = move_uploaded_file($tmp_name,$file_path);

                            $fields_data = '@'.$file_path;
                            if($data_type_value == 'file_url'){
                                $fields_data = $file_url;
                            }
                            $changed_file_data[$form_data['CRM_fields'][$fld_position]] = $file_url;

                            $crm_submitting_data[$form_data['CRM_fields'][$fld_position]] = $fields_data;
                                
                        }

                    }
                                                
                }
                
            }
        }

        $submitting_data['crm_submitting_data'] = $crm_submitting_data;
        foreach ($changed_file_data as $file_key => $file_value) {
            $crm_submitting_data[$file_key] = $file_value;
        }
        $submitting_data['email_submitting_data'] = $crm_submitting_data;
        
        return $submitting_data;
    }


    function mipl_cf7_crm_testing_data(){

        //no need to validated data because data not saved in database
        $crm_post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

        // Verify nonce
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_crm_nonce']) ? sanitize_text_field($_POST['mipl_cf7_crm_nonce']) : '';
        mipl_cf7_verify_nonce( $crm_post_id, $mipl_cf7_crm_nonce );

        if( !$crm_post_id ){ return false; }
        
        $crm_form_post_data = filter_input(INPUT_POST, 'mipl_cf7_crm_data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $crm_static_post_data = filter_input(INPUT_POST, 'mipl_cf7_static_fields', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $test_enable_email = filter_input(INPUT_POST, 'mipl_cf7_crm_enable_email', FILTER_DEFAULT);
        $file_data = isset($_FILES['mipl_cf7_crm_data']) ? $_FILES['mipl_cf7_crm_data'] : '';

        $crm_form_post_data = $this->mipl_cf7_crm_form_submitting_data($crm_post_id, $crm_form_post_data, $file_data);
        $email_submitting_data = $crm_form_post_data['email_submitting_data'];
        $crm_form_post_data = $crm_form_post_data['crm_submitting_data'];

        if(isset($crm_form_post_data)){
            foreach ($crm_form_post_data as $key => $value) {
                $crm_form_post_data[$key] = sanitize_text_field($value);
            }
        }

        if(isset($crm_static_post_data)){
            foreach ($crm_static_post_data as $key => $data) {

                $email_submitting_data[$key] = $data;
            
            }
        }

        $static_fields = !empty($crm_static_post_data) ? $crm_static_post_data : "";
        $final_array = $crm_form_data = !empty($crm_form_post_data) ? $crm_form_post_data : '';
        if($static_fields){
            foreach($static_fields as $key => $value){
                $final_array[$key] = $value;
            }
        }
        
        //CRM authentication
        $return_data = $this->mipl_get_crm_data_submission_response($crm_post_id, $final_array);
        $response = $return_data['response'];
        $resp_data = isset($response['body']) ? $response['body'] : '';
        $resp_error = isset($response['error']) ? $response['error'] : $resp_data;
        $resp_data = htmlentities($resp_data);
        $req_http_code = isset($response['http_code']) && (!empty($response['http_code'])) ? $response['http_code'] : 0;
        $crm_post_data   = $this->crm_post_data($email_submitting_data);
        $crm_body_data        = array(trim($crm_post_data), $resp_data, $req_http_code);

        // Send mail
        if(isset($test_enable_email) && $test_enable_email == 1){
            $this->mipl_cf7_crm_mail( $crm_post_id, $crm_body_data, $req_http_code );
            
        }

        if($req_http_code == 0 && (is_string($resp_error) && str_contains($resp_error, 'timed out'))){
            $resp_error = $resp_error;
        }

        if((($req_http_code == 200 || $req_http_code == 201))){
            $resp = array('status'=>'success', 'message'=>$resp_data, 'status_code'=>$req_http_code);
        }else{
            $resp = array('status'=>'fail', 'message'=> $resp_data, 'status_code'=>$req_http_code, 'error'=>$resp_error);
        }

        echo json_encode($resp);
        die();

    }

    function mipl_cf7_crm_mail( $crm_post_id, $crm_body_data, $crm_resp_status ){

        $email_confi = get_post_meta( $crm_post_id, '_mipl_cf7_crm_email_configuration', true);
        if( isset($email_confi) && $email_confi == 'yes' ){
            return false;
        }

        $crm_email_data  = get_post_meta( $crm_post_id, '_mipl_cf7_crm_email_setting', true );
        $email_from      = $crm_email_data['email_from'];
        $email_to        = $crm_email_data['email_to'];
        $extra_headers   = isset($crm_email_data['extra_headers']) ? ($crm_email_data['extra_headers']) : "";
        $email_subject   = isset($crm_email_data['email_subject']) ? ($crm_email_data['email_subject']) : "";
        $email_body      = nl2br($crm_email_data['email_body']);
        $error_display   = isset($crm_email_data['error_display']) ? ($crm_email_data['error_display']) : 0;
        $enable_email    = isset($crm_email_data['enable_email']) ? ($crm_email_data['enable_email']) : 0;
        $headers = '';

        $crm_email_body_shortcode        = array('[crm_post_data]', '[crm_response]', '[crm_request_status]');
        $comp_email_body = str_ireplace($crm_email_body_shortcode, $crm_body_data, $email_body);
                
        if( $enable_email == 0 ){
            return false;
        }
        
        if( $error_display == "1" ){

            if(!in_array($crm_resp_status, array(200, 201))){
                $headers .= "MIME-Version: 1.0 \r\n";
                $headers .= "From: $email_from " . "\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
                $headers .= $extra_headers;
                $mail    = wp_mail($email_to, $email_subject, $comp_email_body, $headers);
            }

        }else{
            $headers .= "From: $email_from " . "\r\n";
            $headers .= "MIME-Version: 1.0 \r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
            $headers .= $extra_headers;
            $mail    = wp_mail($email_to, $email_subject, $comp_email_body, $headers);
            
        }

    }

}

<?php
/*
Class: MIPL Input Validation
Version: 1.0.8
*/


class MIPL_CF7_Input_Validation{

    public $fields = array();
    public $errors = array();
    public $validated_data = array();
    public $post_data = array();
    public $mipl_cf7_validation_function = array(
        'email' => 'is_email',
        'url' => 'is_url',
        'phone' => 'is_phone',
        'color' => 'is_color',
        'date' => 'is_date',
        'time' => 'is_time',
        'datetime' => 'is_datetime',
        'alpha' => 'is_alpha',
        'alpha_spaces'  => 'is_alpha_spaces',
        'alpha_numeric'  => 'is_alpha_numeric',
        'alpha_numeric_spaces'  => 'is_alpha_numeric_spaces',
        'alpha_dash'  => 'is_alpha_dash',
        'numeric'  => 'is_numeric',
        'integer' => 'is_integer',
        'decimal' => 'is_decimal',
        'natural' => 'is_natural',

    );

    function __construct($fields, $post_data = null) {
        
        if(!empty($fields)){
            $this->fields = $fields;
        }
        $this->post_data = $post_data;
    }


    function validate($fields=array(), $post_data = null){

        if(!empty($fields)){
            $this->fields = $fields;
        }

        if( $this->post_data == null ){
            $this->post_data = $post_data;
        }

        $error_message = array();
        $validated_data = array(); 

        foreach($this->fields as $field_key=>$field){
            if(isset($field['depend'])){
                $depend_field_key = $field['depend']['field'];
                $depend_field_val = $this->_post($depend_field_key);
                
                if( is_array($field['depend']['value']) && !in_array($depend_field_val, $field['depend']['value'])){
                    continue;
                } elseif( is_string($field['depend']['value']) && (!$depend_field_val || $field['depend']['value'] != $depend_field_val) ){
                    continue;
                }
            }


            if( isset($field['validation']['required']) ){
                // For Radio & Checkbox
                $field_value = $this->_post($field_key);
                if( !$field_value ||
                    (is_string($field_value) && trim($field_value) == '') ||
                    (empty($field_value) && $field_value != 0) ){
                    $error_message[$field_key] = $field['validation']['required'];
                    continue;
                }
            }

            $field_value = '';
            if( $this->_post($field_key) ){
                $field_value = $this->_post($field_key);
            }
            if( !empty($field['sanitize']) ){
                foreach($field['sanitize'] as $mipl_cf7_sanitize_func){
                    if (!function_exists($mipl_cf7_sanitize_func)){
                        continue;
                    }
                    
                    if( is_array($field_value) ){
                        foreach($field_value as $key1=>$field_value1){
                            if( !is_array($field_value1) ){
                                $field_value[$key1] = $mipl_cf7_sanitize_func($field_value1);
                            }
                        }
                    }else{
                        $field_value = $mipl_cf7_sanitize_func($field_value);
                    }

                }
            }

            if( !empty($field['ese']) ){
                foreach($field['ese'] as $mipl_cf7_ese_func){
                    if (!function_exists($mipl_cf7_ese_func)){
                        continue;
                    }
                    $field_value = $mipl_cf7_ese_func($field_value);
                }
            }

            if( !empty($field['validation']) ){
                foreach($field['validation'] as $validate=>$val_msg){
                   
                    if( $validate == 'required' && !empty($val_msg)){

                        if( (is_string($field_value) && trim($field_value) == '') ||
                            (empty($field_value) && $field_value != 0) ){
                            $error_message[$field_key] = $val_msg;
                        }

                    }
                    
                    // Array used for custom validation
                    if( !is_array($field_value) && trim($field_value) == '' ){
                        continue;
                    }
                    
                    if( $validate == 'in_values' && !empty($val_msg) ){
                        $field_values = !is_array($field_value) ? [$field_value] : $field_value;
                        foreach($field_values as $field_value1){
                            if(!in_array($field_value1,$field['values'])){
                                $error_message[$field_key] = $val_msg;
                                break;
                            }
                        }
                    }

                    if( $validate == 'regex' && !empty($val_msg)){
                        if(!preg_match($val_msg, $field_value)){
                            $error_message[$field_key] = $field['validation']['regex_msg'];
                        }
                    }

                    if( $validate == 'except_regex' && !empty($val_msg)){
                        if(preg_match($val_msg, $field_value)){
                            $error_message[$field_key] = $field['validation']['except_regex_msg'];
                        }
                    }
                    
                    if($validate == 'custom_function' && !empty($val_msg)){
                        foreach($val_msg as $mipl_cf7_validation_func){
                            if (!function_exists($mipl_cf7_validation_func)){
                                continue;
                            }
                            $resp = $mipl_cf7_validation_func($field_value);
                            if($resp == false || $resp == 0){
                                $error_message[$field_key] = $field['validation']['custom_function_msg'];
                            }
                            if(is_array($resp) && in_array(0, $resp)){
                                $error_message[$field_key] = $field['validation']['custom_function_msg'];
                            }
                        } 
                    }

                    if($validate == 'wp_inbuilt_function' && !empty($val_msg)){
                        foreach($val_msg as $mipl_cf7_validation_func){
                            if (!function_exists($mipl_cf7_validation_func)){
                                continue;
                            }
                            $resp = $mipl_cf7_validation_func($field_value);
                            if($resp == false){
                                $error_message[$field_key] = $field['validation']['wp_inbuilt_function_msg'];
                            }
                        } 
                    }

                    if( $validate == 'limit' && !empty($val_msg)){
                        if(is_array($field_value)){
                            foreach($field_value as $label_key => $label_value){
                                if(strlen($label_value) > $field['validation']['limit']){
                                    $error_message[$field_key] = $field['validation']['limit_msg'];
                                }
                            }
                        }else{
                            if(strlen($field_value) > $field['validation']['limit']){
                                $error_message[$field_key] = $field['validation']['limit_msg'];
                            }
                        }
                    }

                    if( $validate == 'min_limit' && !empty($val_msg)){
                        if(is_array($field_value)){
                            foreach($field_value as $label_key => $label_value){
                                if(strlen($label_value) < $field['validation']['min_limit']){
                                    $error_message[$field_key] = $field['validation']['limit_msg'];
                                }
                            }
                        }else{
                            if(strlen($field_value) < $field['validation']['min_limit']){
                                $error_message[$field_key] = $field['validation']['limit_msg'];
                            }
                        }
                    }
                    
                    if( !empty($validate) && isset($this->mipl_cf7_validation_function[$validate]) ){
                        $resp_function = $this->mipl_cf7_validation_function[$validate];
                        $resp = $this->$resp_function($field_value);
                        
                        if($resp == false){
                            $error_message[$field_key] = $val_msg;
                        }
                    }
                    
                }

                if( empty($error_message[$field_key]) ){
                    $validated_data[$field_key] = $field_value; 
                }

            }else{
                $validated_data[$field_key] = $field_value; 
            }
            
        }
        
        $this->validated_data = $validated_data; 
        $this->errors = $error_message;
        
    }


    function get_errors(){
        return $this->errors;
    }
    
    
    function get_valid_data(){
        return $this->validated_data;
    }


    function _post( $key ){
        
        if(trim($key) == ''){
            return false;
        }

        if( $this->post_data != null ){
            if(isset($this->post_data[$key])){
                
                return $this->post_data[$key];
            }
        }else{
            if(isset($_POST[$key])){
                return filter_input(INPUT_POST, $key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            }
        }
        
        return false;

    }
    
    
	// Email
	public function is_email($email){
		return ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) ? FALSE : TRUE;
	}
    
	// URL
	public function is_url($url){
        if( trim($url) == '' ){ return FALSE; }
       
        // if( stripos($url,'http://') === false && stripos($url,'https://') === false  ){
        //     $url = 'http://'.$url;
        // }

        return ( !filter_var($url, FILTER_VALIDATE_URL) ) ? FALSE : TRUE;
        
	}
    
	// Phone Number
	public function is_phone($str){
        return ( ! preg_match("/^(\+)?([0-9' '()-]{3,20})$/i", $str)) ? FALSE : TRUE;
	} 

    //color
    public function is_color($color){
        return ( ! preg_match("/^(#(?:[0-9a-f]{2}){2,4}|#[0-9a-f]{3}|(?:rgba?|hsla?)\((?:\d+%?(?:deg|rad|grad|turn)?(?:,|\s)+){2,3}[\s\/]*[\d\.]+%?\))$/i", $color)) ? FALSE : TRUE;
    }

    //date
    public function is_date($date){
        return ( ! preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $date)) ? FALSE : TRUE;
    }

    //time
    public function is_time($time){
        return ( ! preg_match("/^((([0]?[1-9]|1[0-2])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?( )?(AM|am|aM|Am|PM|pm|pM|Pm))|(([0]?[0-9]|1[0-9]|2[0-3])(:|\.)[0-5][0-9]((:|\.)[0-5][0-9])?))$/", $time)) ? FALSE : TRUE;
    }

    //datetime
    public function is_datetime($datetime){
        return ( ! preg_match("/^([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (0[0-9]|1[0-9]|2[1-4]):(0[0-9]|[1-5][0-9]):(0[0-9]|[1-5][0-9]))/", $datetime)) ? FALSE : TRUE;
    }
    
	// Alpha
	public function is_alpha($str){
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}
    
	// Alpha-numeric with space
	public function is_alpha_spaces($str){
		return ( ! preg_match("/^([a-z ])+$/i", $str)) ? FALSE : TRUE;
	}     

	// Alpha-numeric
	public function is_alpha_numeric($str){
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}
    
	// Alpha-numeric with space
	public function is_alpha_numeric_spaces($str){
		return ( ! preg_match("/^([a-z0-9 ])+$/i", $str)) ? FALSE : TRUE;
	}    
    
	// Alpha-numeric with underscores and dashes
	public function is_alpha_dash($str){
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}

	// Numeric
	public function is_numeric($str){
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}

	// Integer
	public function is_integer($str){
		return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
	}

	// Decimal number
	public function is_decimal($str){
		return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}

	// Natural number
	public function is_natural($str){
		return (bool) preg_match( '/^[0-9]+$/', $str);
	}

}
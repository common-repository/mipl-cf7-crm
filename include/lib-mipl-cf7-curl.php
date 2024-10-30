<?php
/* MIPL CURL Request
Version: 1.4.7

$atts = array(
    'data'        => array(), // GET/POST Data
    'method'      => 'GET',   // GET/POST Method
    'referer_url' => '',      // HTTP referer url
    'http_header' => array(), // Send Row HTTP header
    'header'      => false,   // Get HTTP header of response
    'timeout'     => 0        // Connection and Request Time Out
);

Return array(
    'resp' => 'Response value'
    'info' => array( 'Request and response details' )
);

Ex.
1) For ajax request: 
   $http_header = array('X-Requested-With: XMLHttpRequest');
2) Submit row file content with type
   $http_header = array('Content-Type: text/xml');
   $http_header = array('Content-Type: application/json');
   $http_header['Content-Type'] = 'application/json';
3) To Send/Upload file: 
   $atts['data'] = array( 'file' = '@/filepath/filename.jpg' );
*/

function mipl_cf7_curl_request( $url, $atts = array() ){

    $default_request_timeout = apply_filters('mipl_cf7_crm_default_request_timeout', 15);
   
    $args = array(
        'data'        => array(),
        'method'      => 'GET',
        'referer_url' => '',
        'http_header' => array(),
        'header'      => false,
        'timeout'     => $default_request_timeout
    );
    
    $args = array_merge( $args, $atts );
    
    if( !is_array($args['http_header']) ){
        $http_header = $args['http_header'];
        $args['http_header'] = array();
        $args['http_header'][] = $http_header;
    }
    
    set_time_limit( $args['timeout'] );
    
    if (function_exists("curl_init") && $url) {
        // Upload files
        if (function_exists('curl_file_create') && is_array($args['data'])) {
            foreach( $args['data'] as $key => $value ){
                if( is_string($value) && strpos($value,'@') === 0 ){
                    $file_name = ltrim($value,'@');
                    if( file_exists($file_name) ){
                        $file_type = mime_content_type($file_name);
                        $args['data'][$key] = curl_file_create($file_name,$file_type);
                    }
                }
            }
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch, CURLOPT_HEADER, $args['header'] );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $args['timeout'] );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, $args['timeout']/4 );
		
        // Generate query string data for x-www-form-urlencoded
        $http_header_str = strtolower(implode(',',$args['http_header']));
        if( strpos($http_header_str, 'application/x-www-form-urlencoded') !== false ){
            if(is_array($args['data'])){
                $args['data'] = http_build_query($args['data']);
            }
        }

        $req_method = strtolower($args['method']);
        
        if ( $req_method == 'post' ) {
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $args['data'] );
        } else if ( $req_method == 'put' ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $args['data'] );
        } else if ( $req_method == 'delete' ) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $args['data'] );
        } else {
            curl_setopt( $ch, CURLOPT_HTTPGET, 1);
            if(is_array($args['data'])){
                $query_string = http_build_query($args['data']);
            }else{
                $query_string = $args['data'];
            }
            
            if($query_string != ''){
                $url_para = parse_url($url);
                $query_string = $url_para['query'].'&'.$query_string;
                
                $url_arr = explode('?',$url);
                $url = $url_arr[0].'?'.$query_string;
            }
        }

        if ( $args['referer_url'] != '' ) { 
            curl_setopt( $ch, CURLOPT_REFERER, $args['referer_url'] );
        }
        if ( !empty( $args['http_header'] ) ) {
            foreach($args['http_header'] as $head_key=>$head_val){
                // if( is_string($head_key) ){
                //     $args['http_header'][] = $head_key.': '.$head_val;
                //     unset($args['http_header'][$head_key]);
                // }
                if( !empty($head_key) ){
                    $args['http_header'][] = $head_key.': '.$head_val;
                    unset($args['http_header'][$head_key]);
                }
            }
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $args['http_header'] );
        }

        curl_setopt( $ch, CURLOPT_URL, $url );
        
        $resp_body = curl_exec($ch);
        $info = curl_getinfo($ch);
        
        $req_error = '';
        if($resp_body === false){
            $req_error = curl_error($ch);
        }
        
        $response = array(
            'body' => $resp_body,
            'info' => $info,
            'error' => $req_error
        );
        
        $response['http_code'] = $info['http_code'];
        
        if( $args['header'] ){
            $header_size = $info['header_size'];
            $body = substr($resp_body, $header_size);
            $header = substr($resp_body, 0, $header_size);
            $header_arr = array();
            $header_lines = explode("\n", $header);
            foreach($header_lines as $header_line){
                $header_item = explode(':',$header_line);
                if(count($header_item)>=2){
                    $key = trim($header_item[0]);
                    unset($header_item[0]);
                    $value = trim(implode(':',$header_item));
                    $header_arr[$key] = trim($value);
                }
            }
            $response['body'] = $body;
            $response['header'] = $header_arr;
        }
        
        return $response;
        
    }
    
}

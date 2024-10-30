<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class MIPL_CF7_Common{

    // Admin side enqueue script.
    function mipl_cf7_admin_enqueue_scripts(){

        global $post;
        
        if(isset($post->ID) && !empty($post->ID)){
            $post_type = get_post_type($post->ID);
            if( $post_type == 'mipl_cf7_crm'){
                $settings = wp_enqueue_code_editor( array( 'type' => 'application/json' ) );
                $settings["codemirror"]["lint"] = false;
                wp_add_inline_script(
                    'code-editor',
                    sprintf(
                        'jQuery( function() { wp.codeEditor.initialize( "mipl_cf7_requesting_body", %s ); } );',
                        wp_json_encode( $settings )
                    )
                );
            }
        }
        
        wp_enqueue_style( 'mipl-cf7-crm-style', MIPL_CF7_CRM_PLUGINS_URL.'assets/css/mipl_cf7_crm_style.min.css' );
        // wp_enqueue_style( 'mipl-cf7-crm-style', MIPL_CF7_CRM_PLUGINS_URL.'assets/css/mipl_cf7_crm_style.css' );

        wp_enqueue_style( 'mipl-cf7-cf-select2-css', MIPL_CF7_CRM_PLUGINS_URL.'assets/libs/select2/select2.min.css' );

        wp_enqueue_script( 'mipl-cf7-cf-select2-js', MIPL_CF7_CRM_PLUGINS_URL.'assets/libs/select2/select2.min.js' );
        
        // wp_enqueue_script( 'mipl-cf7-crm-admin-script', MIPL_CF7_CRM_PLUGINS_URL.'assets/script/mipl-cf7-crm-admin-script.js', array('jquery', 'wp-i18n' ) );
        wp_enqueue_script( 'mipl-cf7-crm-admin-script', MIPL_CF7_CRM_PLUGINS_URL.'assets/script/mipl-cf7-crm-admin-script.min.js', array('jquery', 'wp-i18n') );
        
    }

    //Register post type
    function mipl_cf7_crm_register_post_type(){


        // MIPL CF7 CRM POST TYPE
        $labels = array(
            'name'          => __( "CRM Integration" ),
            'singular_name' => __( "CRM Integration" ),
            'menu_name'     => __( "CRM Integration" ),
            'add_new'       => __( "Add New" ),
            'add_new_item'  => __( "Add New CRM Integration" ),
            'new_item'      => __( "New CRM Integration" ),
            'edit_item'     => __( "Edit CRM Integration" ),
            'view_item'     => __( "View CRM Integration" ),
            'all_items'     => __( "CRM Integration" ),
            'search_items'  => __( "Search CRM Integration" ),
            'parent_item_colon' => __( "Parent:CRM Integration" )
        );

        $args = array(
            'labels' => $labels,
            'show_in_menu'=>'wpcf7',
            'public'      => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'supports' => array('title'),
            'show_ui' => true,
        );

        register_post_type( MIPL_CF7_CRM_POST_TYPE, $args );

        // mipl_crm_leads post type
        $labels = array(
            'name'          => __( "CRM Leads" ),
            'singular_name' => __( "CRM Leads" ),
            'menu_name'     => __( "CRM Leads" ),
            'add_new'       => __( "Add New" ),
            'add_new_item'  => __( "Add New CRM Leads" ),
            'new_item'      => __( "New CRM Leads" ),
            'edit_item'     => __( "CRM Leads" ),
            'view_item'     => __( "CRM Leads" ),
            'all_items'     => __( "CRM Leads" ),
            'search_items'  => __( "Search CRM Leads" ),
            'parent_item_colon' => __( "Parent:CRM Leads" )
        );

        $args = array(
            'labels' => $labels,
            'show_in_menu'=>'wpcf7',
            'public'      => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'supports' => array('title'),
            'show_ui' => true,
            'capability_type' => 'post',
            'capabilities' => array(
                'create_posts' => 'do_not_allow',
            ),
            'map_meta_cap' => true
        );

        register_post_type( 'mipl_crm_leads', $args );

    } 


    function update_crm_columns( $columns ){

        $new_columns = array(
            'cb' => $columns['cb'],            
            'title' => $columns['title'],
            '_mipl_cf7_crm_api_url' => __('CRM API URL/Endpoint URL'),
            '_mipl_cf7_crm_form' => __('Contact Form 7'),
            'date' => $columns['date'],
        );
        
        return $new_columns;

    }

    function update_crm_lead_columns( $columns ){
        
        $new_columns = array(
            'cb' => $columns['cb'],            
            'title' => $columns['title'],
            '_mipl_cf7_crm_name' => __('CRM Name'),
            '_mipl_cf7_crm_form' => __('Contact Form 7'),
            '_mipl_cf7_crm_status' => __('Status'),
            'date' => $columns['date'],
        );

        return $new_columns;

    }


    // Hide title of CRM lead
    function mipl_cf7_crm_hide_title_input() {
        remove_post_type_support('mipl_crm_leads', 'title');
    }


    function mipl_cf7_crm_columns_data( $column_name, $id ){
       
        $crm_deatails        = get_post_meta( $id, '_mipl_cf7_crm_details', true );
        $crm_url             = !empty( $crm_deatails['crm_url'] ) ? $crm_deatails['crm_url'] : "";
        if($column_name == '_mipl_cf7_crm_api_url'){
            echo esc_url($crm_url);
        }
        
        $crm_form_submission_settings = get_post_meta( $id, '_mipl_cf7_crm_settings', true );

        $crm_form_submission = !empty($crm_form_submission_settings['crm_form_submission']) ? $crm_form_submission_settings['crm_form_submission'] : array();

        if($column_name == '_mipl_cf7_crm_form' && !empty($crm_form_submission)){

            if( !empty($crm_form_submission) ){
                $form_name = get_the_title($crm_form_submission[0]);
                echo '#'.$crm_form_submission[0].', '.$form_name;
            }else{
                echo "__";
            }

        }

    }

    function mipl_cf7_crm_lead_columns_data( $column_name, $id ){
        
        $lead_data = get_post_meta($id, '_mipl_lead_collection_data', true);
        $crm_post = isset($lead_data['crm_post']) ? $lead_data['crm_post'] : array();
        $lead_post_id = isset($crm_post['post_id']) ? $crm_post['post_id'] : '';
        $lead_post_title = isset($crm_post['post_title']) ? $crm_post['post_title'] : '';
        $cf7_data = isset($lead_data['contact_form7_data']) ? $lead_data['contact_form7_data'] : array();
        $cf7_id = isset($cf7_data['cf7_id']) ? $cf7_data['cf7_id'] : '';
        $cf7_title = isset($cf7_data['cf7_title']) ? $cf7_data['cf7_title'] : '';
        $status = isset($lead_data['response_status']) ? $lead_data['response_status'] : '';

        if($column_name == '_mipl_cf7_crm_name'){
            ?>
            <a href="<?php echo esc_url(admin_url('post.php?post='.$lead_post_id.'&action=edit'))?>">
                <?php echo '#'.$lead_post_id.', ',$lead_post_title;  ?> 
            </a>
            <?php
            
        }

        if($column_name == '_mipl_cf7_crm_form'){
            ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpcf7&post='.$cf7_id.'&action=edit'))?>">
                <?php echo '#'.$cf7_id.', ',$cf7_title;  ?> 
            </a>
            <?php
           
        }

        if($column_name == '_mipl_cf7_crm_status'){
            if($status == '200' || $status == '201'){
                echo "<span style='color:green'>".$status.'</span>';
            }else{
                $hint_url = admin_url('post.php?post='.$id.'&action=edit');
                echo "<span style='color:red'>".$status.'</span>';
                if(isset($lead_data['response_error'])){
                    echo "<div style='color:red; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'> ".$lead_data['response_error'].' </div>';
                    echo "<div><a href='$hint_url'>".esc_html('Hint')."</a></div>";
                }
            }
        }
        
    }

     
    // Add setting link on plugin list
	function mipl_cf7_crm_action_links( $links , $plugin_file) {
	    
		$custom_link = array();
		if (in_array( $plugin_file, array('mipl-cf7-crm/mipl-cf7-crm.php'))){
			$custom_link['settings'] = '<a href="' . esc_url(admin_url( 'edit.php?post_type=mipl_cf7_crm' )) . '">'.__('Settings').'</a>';
	    }
		return array_merge( $custom_link, $links );
	}


    //Added meta boxes
    function mipl_cf7_crm_add_metabox(){
        add_meta_box( 'mipl_cf7_crm_details', __('CRM Details'), array( $this, 'checkout_crm_details' ), MIPL_CF7_CRM_POST_TYPE, 'normal', 'default' );
        add_meta_box( 'mipl_cf7_crm_form_data', __('Fields Mapping'), array( $this, 'checkout_crm_form_data' ), MIPL_CF7_CRM_POST_TYPE, 'normal', 'default' );
        add_meta_box( 'mipl_cf7_crm_to_email', __('Email Settings'), array( $this, 'crm_email' ), MIPL_CF7_CRM_POST_TYPE, 'normal', 'default' );
        add_meta_box( 'mipl_cf7_crm_setting', __('CRM Settings'), array( $this, 'crm_setting' ), MIPL_CF7_CRM_POST_TYPE, 'side', 'default' );
        add_meta_box( 'mipl_cf7_crm_testing',__('CRM Testing'), array( $this,'cf7_crm_testing' ), MIPL_CF7_CRM_POST_TYPE, 'side', 'high' );

        //lead collection
        add_meta_box( 'mipl_cf7_submitted_data', __('Contact Form 7 Submitted Data'), array( $this, 'cf7_submittted_data' ), 'mipl_crm_leads', 'normal', 'default' );
        add_meta_box( 'mipl_cf7_crm_requesting_body', __('CRM Leads Data'), array( $this, 'crm_requesting_body' ), 'mipl_crm_leads', 'normal', 'default' );
        add_meta_box( 'mipl_cf7_crm_response_status', __('CRM Response Status'), array( $this, 'crm_response_status' ), 'mipl_crm_leads', 'side', 'default' );
    }

    // Hide Visibility from publish sidebox
    function mipl_cf7_no_visibility(){
        echo '<style>div#visibility.misc-pub-section.misc-pub-visibility{display:none}</style>';
    }

    function mipl_cf7_crm_leads_no_visibility(){
        echo '<style>div#visibility.misc-pub-section.misc-pub-visibility{display:none}div.misc-pub-post-status{display:none}div#publishing-action{display: none }a.page-title-action{display:none!important;}</style>';
    }


    function mipl_cf7_copy_files( $posted_data ){

        $cf7_id = isset($_POST['_wpcf7']) ? sanitize_text_field($_POST['_wpcf7']) : '';
        
        if( !isset($cf7_id) ){ return false; }

        $files_name = mipl_cf7_file_fields_name( $cf7_id );
      
        $uploads_dir = wp_upload_dir();
        $base_dir_path = $uploads_dir['basedir'].'/mipl-cf7-crm/';
        $base_dir_url = $uploads_dir['baseurl'].'/mipl-cf7-crm/';
        
        if(!file_exists($base_dir_path)){
            mkdir($base_dir_path, 0775, true);
        }

        foreach ($files_name as $file_name) {
            if ($_FILES[$file_name]["error"] == UPLOAD_ERR_OK) {
                $tmp_name = sanitize_text_field($_FILES[$file_name]['tmp_name']);
                $f_name = sanitize_text_field($_FILES[$file_name]['name']);
                $name = mipl_cf7_rand().'-'.basename($f_name);
                $pathinfo = pathinfo( $name);
                $new_file_name = sanitize_title($pathinfo['filename']);
                $file_extension = $pathinfo['extension'];
                $GLOBALS['mipl_cf7_file_fields'][$tmp_name] = $new_file_name.".".$file_extension;
                $san_file_name = $new_file_name.".".$file_extension;
                $file_path = $base_dir_path.$san_file_name;
                copy($tmp_name, $file_path);
                
            }
        }

        return $posted_data;

    }

    //Callback function of CRM authentication details metabox
    function checkout_crm_details( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-details.php';
    }

    //Callback function of CRM fields mapping metabox
    function checkout_crm_form_data( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-form-data.php';
    }

    //Callback function of CRM email setting metabox
    function crm_email( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-send-email.php';

    }

    //Callback function of CRM fields mapping metabox
    function crm_setting( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-setting.php';
    }

    function cf7_submittted_data( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-submitted-data.php';
        
    }

    function crm_requesting_body( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-lead-data.php';
    }

    function crm_response_status( $post ){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-response-status.php';
    }
    

    //Callback function of test crm integration
    function cf7_crm_testing( $post ){
       
        $crm_configuration = mipl_cf7_required_crm_data($post->ID);
        
        $href_value = '#TB_inline?&width=1024&height=530&padding=10px&inlineId=mipl_cf7_modal" class="thickbox mipl_cf7_cf_toggal_modal';

        if(!$crm_configuration){
            $href_value = '';

            ?>
            <script>
                function valide_to_test(element){
                    var thickbox_href = jQuery(element).attr('href');
                    var conf_msg = "<?php esc_html_e('Configure CRM with essential details and fields.')?>";
                    if(thickbox_href == ''){

                        alert(conf_msg);
                        return false;
                    }
                    return true;
                }
            </script>
            <?php
           
        }
       
        ?>
        <div>
            <a href="<?php echo $href_value ?>" class="mipl_cf7_test_crm" title="Test CRM Submission" data-id="<?php echo esc_html($post->ID);?>" onclick="return valide_to_test(this)"><?php esc_html_e('Test CRM Submission') ?></a>
        </div>
        <p style="margin-top:7px;"><b><?php echo esc_attr("Note: "); ?></b><?php echo esc_html("Save CRM Configure with essential details and fields.") ?></p>
        <?php
    }

    //Included view of crm test integartion
    function mipl_cf7_crm_testing_form(){
        include_once MIPL_CF7_CRM_PLUGINS_DIR.'/view/mipl-cf7-crm-testing.php';
    }

    // Plugin deactivation popup
    function mipl_cf7_print_deactivate_feedback_dialog() {
        ?>

        <div id="mipl-cf7-crm-deactivate-popup" style="display:none;">
        
            <?php
            $deactivate_reasons = [
                'no_longer_needed' => [
                    'title' => esc_html__('I no longer need the plugin'),
                    'input_placeholder' => '',
                ],
                'found_a_better_plugin' => [
                    'title' => esc_html__('I found a better plugin'),
                    'input_placeholder' => esc_html__('Please share which plugin'),
                ],
                'could_not_get_the_plugin_to_work' => [
                    'title' => esc_html__("I couldn't get the plugin to work"),
                    'input_placeholder' => '',
                ],
                'temporary_deactivation' => [
                    'title' => esc_html__("It's a temporary deactivation"),
                    'input_placeholder' => '',
                ],
                'other' => [
                    'title' => esc_html__('Other'),
                    'input_placeholder' => esc_html__('Please share the reason'),
                ],
            ];
            ?>

            <form id="mipl_cf7_crm_deactivation_form" method="post" style="margin-top:20px;margin-bottom:30px;">
                <div id="" style="font-weight: 700; font-size: 15px; line-height: 1.4;"><?php echo esc_html__('If you have a moment, please share why you are deactivating plugin:'); ?></div>
                <div id="" style="padding-block-start: 10px; padding-block-end: 0px;">
                    <?php foreach ($deactivate_reasons as $reason_key => $reason) { ?>
                        <div class="" style="display: flex; align-items: center; line-height: 2; overflow: hidden;">
                            <label>
                                <input id="plugin-deactivate-feedback-<?php echo esc_attr($reason_key); ?>" class="" style="margin-block: 0; margin-inline: 0 15px; box-shadow: none;" type="radio" name="mipl_cf7_deactivation_reason" value="<?php echo esc_attr($reason_key); ?>" required /><?php echo esc_html($reason['title']); ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>

                <div id="mipl-cf7-other-reason-textarea">
                <textarea style="vertical-align:top;margin-left: 30px;" id="other-reason" name="mipl_cf7_deactivation_other_reason" rows="4" cols="50" placeholder="Please share the reason" ></textarea>
                </div>

                <div class="" style="display: flex;  padding: 20px 0px;">
                    <button class="mipl_cf7_submit_and_deactivate button button-primary button-large" type="submit" style="margin-right:10px;"><?php echo esc_html('Submit & Deactivate') ?></button>
                    <button class="mipl_cf7_skip_and_deactivate button" type="button" ><?php echo esc_html('Skip & Deactivate') ?></button>
                </div>
                
            </form>

        </div>

        <script>
            jQuery(document).ready(function(){

                jQuery('#deactivate-mipl-cf7-crm').click(function(){
                    var $deactivate_url = jQuery(this).attr('href');
                    tb_show("Quick Feedback", "#TB_inline?&amp;inlineId=mipl-cf7-crm-deactivate-popup&amp;height=500;max-height: 330px; min-height: 330px;");
                    jQuery('#TB_window form').attr('data-deactivate_url',$deactivate_url);                    
                    return false;
                });

            });
        

            jQuery(document).ready(function(){

                jQuery('.mipl_cf7_skip_and_deactivate').click(function(){
                    mipl_cf7_deactivate_plugins();
                    return false;
                });
            
                jQuery('#mipl_cf7_crm_deactivation_form').submit(function(){
                    mipl_cf7_deactivate_plugins();
                    return false;
                });

            });


            function mipl_cf7_deactivate_plugins(){

                var $form_data = jQuery('#mipl_cf7_crm_deactivation_form').serializeArray();
                var $deactivate_url = jQuery('#mipl_cf7_crm_deactivation_form').attr('data-deactivate_url');
                jQuery('#mipl_cf7_crm_deactivation_form button').attr('disabled', 'disabled');
                jQuery.post('?mipl-crm-action=mipl_cf7_submit_and_deactivate', $form_data, function(response){
                    window.location = $deactivate_url;
                });
                
                return false;

            }
        

            jQuery(document).ready(function(){
                
                jQuery('#mipl_cf7_crm_deactivation_form').on( 'change', 'input[name="mipl_cf7_deactivation_reason"]', function () {
                    $feedback_val = jQuery(this).val();
                    jQuery('#mipl-cf7-other-reason-textarea textarea').removeAttr('required');
                    if($feedback_val == 'other'){
                        jQuery('#mipl-cf7-other-reason-textarea textarea').attr('required','required');
                    }
                });

            });

        </script>
        <?php

    }

    function mipl_cf7_submit_and_deactivate(){
        
        $feedback = "";
        if(isset($_POST['mipl_cf7_deactivation_reason'])){
            $feedback = sanitize_text_field($_POST['mipl_cf7_deactivation_reason']);
        }

        if($feedback == 'other' && isset($_POST['mipl_cf7_deactivation_other_reason'])){
            $feedback = sanitize_textarea_field($_POST['mipl_cf7_deactivation_other_reason']);
        }

        if(empty($feedback)){
            $feedback = __('Skipped feedback and plugin deactivated');
        }

        $deactivation_date = current_time('mysql');
        $home_url = home_url();
        $url = 'https://store.mulika.in/api/wp/v1/plugin/feedback/';        
        $args = array(
            'method'      => 'POST',
            'timeout'     => 2,
            'body'        => array(
                'home_url'     => $home_url,
                'plugin_name'     => MIPL_CF7_CRM_UNIQUE_NAME,
                'deactivation_date' => $deactivation_date,
                'feedback' => $feedback
            )
        );
        
        $response = wp_remote_post( $url, $args );

        if ( is_wp_error( $response ) ) {
            // $error_message = $response->get_error_message();
        } else {
            // echo json_encode( $response );
        }

        die();

    }

    function mipl_cf7_crm_leads_title($title, $id){
        $post_type = get_post_type($id);
        if($post_type == 'mipl_crm_leads'){
            $title = '#'.$id;
            return $title;
        }
        return $title;
    }

    function mipl_cf7_fields_name(){

        if(!isset($_POST['form_id']) || empty($_POST['form_id']) ){
            return false;
        }

        $cf7_id = sanitize_text_field($_POST['form_id']);
        
        // verified nonce
        $mipl_cf7_post_id = isset($_POST['mipl_cf7_post_id']) ? sanitize_text_field($_POST['mipl_cf7_post_id']) : '';
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_nonce']) ? sanitize_text_field($_POST['mipl_cf7_nonce']) : '';
        mipl_cf7_verify_nonce( $mipl_cf7_post_id, $mipl_cf7_crm_nonce );
    
        $cf7_fields = array();    
        $ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
        $form_fields = $ContactForm->scan_form_tags();
        foreach ($form_fields as $fields) {
            if($fields['basetype'] == 'select' || $fields['basetype'] == 'checkbox' || $fields['basetype'] == 'radio'){
                $cf7_fields[$fields['name']] = $fields['values'];
            }
        }
    
        echo wp_json_encode( $cf7_fields );
        die();
    }

    function mipl_cf7_fields_value(){

        if( !isset($_POST['form_id']) || empty($_POST['form_id']) ){
            return false;
        }

        $cf7_id = sanitize_text_field($_POST['form_id']);
        $cf7_field_name = !empty($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
    
        // verified nonce
        $mipl_cf7_post_id = !empty($_POST['mipl_cf7_post_id'])?sanitize_text_field($_POST['mipl_cf7_post_id']):'';
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_nonce']) ? sanitize_text_field($_POST['mipl_cf7_nonce']) : '';
        mipl_cf7_verify_nonce( $mipl_cf7_post_id, $mipl_cf7_crm_nonce );
    
        $cf7_field_value = array();
        
        $ContactForm = WPCF7_ContactForm::get_instance( $cf7_id );
        $form_fields = $ContactForm->scan_form_tags();
        foreach ($form_fields as $fields) {
            if($fields['basetype'] == 'select' || $fields['basetype'] == 'checkbox' || $fields['basetype'] == 'radio'){
                if($fields['name'] == $cf7_field_name){
                    $options_data = array();
                    $options_value = $fields['raw_values'];
                    foreach($options_value as $option_key => $option_value){
                        if(strpos($option_value,"|")){
                            $tmp = explode('|',trim($option_value));
                            if(!empty($tmp[0] && !empty($tmp[1]))){
                                $options_data[trim($tmp[0])] = trim($tmp[1]);
                            }
                        }else{
                            if(!empty($option_value)){
                                $options_data[$option_value] = $option_value;
                            }
                        }
                    }
                
                    $cf7_field_value = $options_data;
                }
            }
        }
        
        echo wp_json_encode($cf7_field_value);
        die();
    }

    function mipl_cf7_form_data(){

        // verified nonce
        $mipl_cf7_post_id = !empty($_POST['mipl_cf7_post_id']) ? sanitize_text_field($_POST['mipl_cf7_post_id']) : '';
        $mipl_cf7_crm_nonce = isset($_POST['mipl_cf7_nonce']) ? sanitize_text_field($_POST['mipl_cf7_nonce']) : '';
        mipl_cf7_verify_nonce( $mipl_cf7_post_id, $mipl_cf7_crm_nonce );
    
        if( empty($_GET['form_id']) ){
            echo wp_json_encode(array('status'=>'error','message'=>__("Form is missing!")));
            die();
        }
    
        if( !method_exists('WPCF7_ContactForm','get_instance') ){
            echo wp_json_encode(array('status'=>'error','message'=>__("Please install 'Contact Form 7' plugin!")));
            die();
        }
    
        $form_id = sanitize_text_field($_GET['form_id']);
        
        $ContactForm = WPCF7_ContactForm::get_instance( $form_id  );
        $form_fields = $ContactForm->scan_form_tags();
        
        echo wp_json_encode($form_fields);
        die();
        
    }


    function mipl_cf7_change_leads_order($query){
        if( is_admin() && $query->is_main_query() && $query->get('post_type') == 'mipl_crm_leads'){
            $query->set( 'orderby', 'date' );
            $query->set( 'order', 'DESC');
        }
    }
    
}

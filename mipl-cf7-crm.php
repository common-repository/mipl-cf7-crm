<?php
/**
* Plugin Name:       MIPL CF7 CRM
* Plugin URI:        
* Description:       Integrating a Customer Relationship Management (CRM) system with the "Contact Form 7" plugin in WordPress can be a powerful way to streamline your lead management and customer interactions.
* Version:           1.1.1
* Requires at least: 5.1
* Requires PHP:      7.4
* Author:            Mulika Team
* Author URI:        https://www.mulikainfotech.com/
* License:           GPL v2 or later
* License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*/


/*
'MIPL CF7 CRMs' is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

'MIPL CF7 CRM' is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with 'MIPL CF7 CRM'. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Define Const
define( 'MIPL_CF7_CRM_PLUGINS_URL', plugin_dir_url(__FILE__) );
define( 'MIPL_CF7_CRM_PLUGINS_DIR', plugin_dir_path(__FILE__) );
define( 'MIPL_CF7_CRM_POST_TYPE', 'mipl_cf7_crm' );
define( 'MIPL_CF7_CRM_UNIQUE_NAME', 'mipl-cf7-crm' );


// Include Classes
include_once MIPL_CF7_CRM_PLUGINS_DIR.'include/class-mipl-cf7-common.php';
include_once MIPL_CF7_CRM_PLUGINS_DIR.'include/class-mipl-cf7-crm.php';
include_once MIPL_CF7_CRM_PLUGINS_DIR.'include/class-mipl-input-validation.php';


// Include Libs
include_once MIPL_CF7_CRM_PLUGINS_DIR.'include/lib-mipl-cf7-crm.php';
include_once MIPL_CF7_CRM_PLUGINS_DIR.'include/lib-mipl-cf7-curl.php';

if( !mipl_cf7_json_request() && !session_id() ){ session_start(); }

add_action( 'plugins_loaded', 'mipl_cf7_init' );

function mipl_cf7_init(){
   
    if( class_exists( 'WPCF7_ContactForm' )){

        // Create Class Objects
        $mipl_cf7_common = new MIPL_CF7_Common();
        $mipl_cf7_crm    = new MIPL_CF7_CRM();

        // Register post type
        add_action( 'init', array($mipl_cf7_common, 'mipl_cf7_crm_register_post_type') );

        //Add plugin setting link
	    add_filter( 'plugin_action_links', array($mipl_cf7_common, 'mipl_cf7_crm_action_links' ), 10, 2);

        if( is_admin() ){

            // Added meta box
            add_action( 'add_meta_boxes', array($mipl_cf7_common, 'mipl_cf7_crm_add_metabox') );

            // Enqueued scripts
            add_action( 'admin_enqueue_scripts', array($mipl_cf7_common, 'mipl_cf7_admin_enqueue_scripts'), 9 );
            
            // Saved post data
            add_action( 'save_post', array($mipl_cf7_crm, 'mipl_cf7_update_crm_data'), 10, 3 );
            
            // Edit columns of custom post type
            add_action( 'manage_mipl_cf7_crm_posts_custom_column', array($mipl_cf7_common,'mipl_cf7_crm_columns_data'), 10, 2);
            add_action( 'manage_mipl_crm_leads_posts_custom_column', array($mipl_cf7_common,'mipl_cf7_crm_lead_columns_data'), 10, 2);

            // Display title in crm leads post type table
            add_filter('the_title', array($mipl_cf7_common, 'mipl_cf7_crm_leads_title'), 10, 2);
            
            // Hide title input
            add_action( 'admin_init', array($mipl_cf7_common, 'mipl_cf7_crm_hide_title_input') );

            // CRM testing form
            add_action( 'admin_footer', array($mipl_cf7_common, 'mipl_cf7_crm_testing_form') );

            // Change the order of crm leads.
            add_action( 'pre_get_posts', array($mipl_cf7_common, 'mipl_cf7_change_leads_order' ));
            
            // Save data when user get refresh token or revoke app without update the post
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'save_oauth_form' ){
                add_action( 'admin_init', array($mipl_cf7_crm, 'mipl_cf7_auth_field_save'), 10, 3 );
            }

            //oauth redirect function for oAuth2.0 access token and refresh token
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'oauth_redirect' ){
                add_action( 'admin_init', array($mipl_cf7_crm, 'mipl_cf7_oauth_redirect') );
            }

            // Reset client id and client secret
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'reset_crm_oauth_details_form' ){
                add_action( 'admin_init', array($mipl_cf7_crm, 'mipl_cf7_crm_reset_action'), 10, 3 );
            }

            // Get selected contact form 7 form fields name
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'mipl_cf7_fields_name' ){
                add_action( 'admin_init', array($mipl_cf7_common, 'mipl_cf7_fields_name') );
            }

            // Get selected contact form 7 form fields values
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'mipl_cf7_fields_value' ){
                add_action( 'admin_init', array($mipl_cf7_common, 'mipl_cf7_fields_value') );
            }

            // Tested the CRM setup.
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'crm_testing_data' ){   
                add_action( 'init', array($mipl_cf7_crm, 'mipl_cf7_crm_testing_data'), 10 );
            }

            // Collected all data of contact form 7 form.
            if( isset( $_REQUEST['mipl-crm-action'] ) && $_REQUEST['mipl-crm-action'] == 'mipl_cf7_form_data' ){   
                add_action( 'admin_init', array($mipl_cf7_common, 'mipl_cf7_form_data') );
            }


            // Added extra columns in custom post type
            add_filter( 'manage_mipl_cf7_crm_posts_columns', array($mipl_cf7_common,'update_crm_columns' ));
            add_filter( 'manage_mipl_crm_leads_posts_columns', array($mipl_cf7_common,'update_crm_lead_columns' ));

              
            // Hide visibility of both post type(mipl_cf7_crm, mipl_crm_leads)
            function mipl_cf7_current_screen_action($current_screen) {
                $mipl_cf7_common = new MIPL_CF7_Common();
                if (isset($current_screen->post_type) && $current_screen->post_type == 'mipl_cf7_crm') {
                    add_action('admin_head', array($mipl_cf7_common, 'mipl_cf7_no_visibility'));
                }
                if (isset($current_screen->post_type) && $current_screen->post_type == 'mipl_crm_leads') {
                    add_action('admin_head', array($mipl_cf7_common, 'mipl_cf7_crm_leads_no_visibility'));
                }
            }
            add_action('current_screen', 'mipl_cf7_current_screen_action');
            

            // Plugin deactivation feedback.
            global $pagenow;
            if($pagenow == 'plugins.php'){

                add_action('admin_footer',  array($mipl_cf7_common, 'mipl_cf7_print_deactivate_feedback_dialog'));

            }
            if ( isset($_REQUEST['mipl-crm-action']) && $_REQUEST['mipl-crm-action'] == 'mipl_cf7_submit_and_deactivate') {

                add_action('init', array($mipl_cf7_common, 'mipl_cf7_submit_and_deactivate'));
                
            }

            
            // Admin Notices.
            add_action('admin_init', function(){

                if(!isset($_GET['post'])){ return false; }

                $post_type = get_post_type($_GET['post']);
                
                if(($post_type == 'mipl_cf7_crm')){
                    
                    add_action( 'admin_notices', 'mipl_cf7_admin_notices' );

                }
            });
        
        }

        //Client side(CRM submission)
        if( !is_admin() ){
            add_action( 'wpcf7_submit', array($mipl_cf7_crm, 'mipl_cf7_crm_submission' ), 10, 1 );
            add_action( 'wpcf7_mail_sent', array($mipl_cf7_crm, 'mipl_cf7_crm_submission' ), 11, 1 );
            add_filter( 'wpcf7_posted_data', array($mipl_cf7_common, 'mipl_cf7_copy_files') );
        }

    }else{
		
		add_action( 'admin_notices', function(){
            ?>
                <div class='notice is-dismissible notice-warning'>
                    <p>
                        <?php echo esc_html("MIPL CF7 CRM: Please install and activate 'Contact Form 7' plugin!") ?>
                    </p>
                </div>
			<?php
          
		});
        
    }

}

//Register rest route
add_action( 'rest_api_init', function () {

    $mipl_cf7_crm = new MIPL_CF7_CRM();
    register_rest_route( 'mipl-cf7-crm/v1', '/crm/oauth/(?P<id>[a-zA-Z0-9-]+)', array(
        'methods'  => array('GET', 'POST'),
        'callback' => array( $mipl_cf7_crm, 'mipl_cf7_oauth_rest_api_callback' ),
        'args'     => array(
			'id' => array(
				'validate_callback' => function( $param, $request, $key ) {
					return is_numeric( $param );
				},
            ),
        ),
        'permission_callback' => '__return_true',
        ) 
    );

} );


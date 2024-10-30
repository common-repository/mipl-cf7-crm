<?php

    if ( ! defined( 'ABSPATH' ) ) exit;

    $current_user    = wp_get_current_user();
    $crm_email_data  = get_post_meta($post->ID, '_mipl_cf7_crm_email_setting', true);
    $email_from      = !empty($crm_email_data['email_from']) ? $crm_email_data['email_from'] : "";
    $email_to        = !empty($crm_email_data['email_to']) ? $crm_email_data['email_to'] : $current_user->user_email;
    $email_subject   = !empty($crm_email_data['email_subject']) ? $crm_email_data['email_subject'] : "";
    $extra_headers   = !empty($crm_email_data['extra_headers']) ? $crm_email_data['extra_headers'] : "";
    $email_body      = !empty($crm_email_data['email_body']) ? $crm_email_data['email_body'] : "";
    $error_display   = !empty($crm_email_data['error_display']) ? $crm_email_data['error_display'] : "";
    $check           = "";
    if($error_display == "1"){
        $check = "checked";
    }
    $enable_error = !empty($crm_email_data['enable_email']) ? $crm_email_data['enable_email'] : "";
    $enable_check           = "";
    if($enable_error == "1"){
        $enable_check = "checked";
    }

?>
<div class="mipl_cf7_crm">
    <div class="mipl_cf7_crm_item">
        <label>
            <input type="checkbox" class="mipl_cf7_email_notification" name="mipl_cf7_crm_email[enable_email]" value="1" <?php echo esc_attr($enable_check) ?>>
            <span class="setting_label mipl_cf7_cf_fields_label"><?php esc_html_e('Send notification email') ?></span> 
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label>
            <input type="checkbox" name="mipl_cf7_crm_email[error_display]" value="1" <?php echo esc_attr($check) ?>>
            <span class="setting_label mipl_cf7_cf_fields_label"><?php esc_html_e('Email sending only for error') ?></span> 
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Email From: ') ?></span><span class="mipl_required_mail_field">*</span><br>
            <input type="email" class="crm_content" name="mipl_cf7_crm_email[email_from]" value="<?php echo esc_attr($email_from) ?>">
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Email To: ') ?></span><span class="mipl_required_mail_field">*</span><br>
            <input type="text" class="crm_content" name="mipl_cf7_crm_email[email_to]" value="<?php echo esc_attr($email_to) ?>">
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Email Subject: ') ?></span><br>
            <input type="text" class="crm_content" name="mipl_cf7_crm_email[email_subject]" value="<?php echo esc_attr($email_subject) ?>">
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Additional Headers: ') ?></span>
            <textarea class="crm_content" name="mipl_cf7_crm_email[extra_headers]"><?php echo esc_textarea($extra_headers) ?></textarea>
            <em><b><?php echo esc_html("Note: ") ?></b><?php echo esc_html("Each header enter on a new line.") ?></em>
        </label>
    </div>
    <div class="mipl_cf7_crm_item">
        <label><span class="mipl_cf7_cf_fields_label setting_label"><?php esc_html_e('Email Body: ') ?></span><span class="mipl_required_mail_field">*</span></label>
        
        <div>
            <?php esc_html_e('Shortcode/Shortext') ?>: [crm_post_data], [crm_response], [crm_request_status]
        </div>
        <div>
            <textarea name="mipl_cf7_crm_email[email_body]"><?php echo esc_textarea($email_body) ?></textarea>
        </div>
        <div>
<pre>
<?php esc_html_e('Shortcode')?>: [crm_post_data]
<?php esc_html_e('Example')?>: Field Name: Field Value,
         Field Name1: Field Value1,
         Field Name2: Field Value2
</pre>
        </div>
        <div>
<pre>
<?php esc_html_e('Shortcode')?>: [crm_response]
<?php esc_html_e('Example')?>: {"status":"success","message":"Order data collected "}
</pre>
        </div>
        <div>
<pre>
<?php esc_html_e('Shortcode')?>: [crm_request_status]
<?php esc_html_e('Example')?>: 200
</pre>
        </div>
    </div>
    
</div>

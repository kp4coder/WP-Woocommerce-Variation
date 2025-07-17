<?php
global $wvp, $wvp_settings, $wvp_woocommerce;
if( isset( $_REQUEST['wvp_setting_save'] ) && isset( $_REQUEST['wvp_setting'] ) && $_REQUEST['wvp_setting'] != '' ) {
    do_action( 'wvp_save_settings', $_POST );
}

echo '<div class="wrap wvp_content">';

if( isset($_SESSION['wvp_msg_status']) && $_SESSION['wvp_msg_status'] ) { 
    echo '<div id="message" class="updated notice notice-success is-dismissible">';
    echo '<p>';
    echo (isset($_SESSION['wvp_msg']) && $_SESSION['wvp_msg']!='') ? $_SESSION['wvp_msg'] : 'Something went wrong.';
    echo '</p>';
    echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">'.__('Dismiss this notice.',WVP_txt_domain).'</span></button>';
    echo '</div>';
    unset($_SESSION['wvp_msg_status']);
    unset($_SESSION['wvp_msg']);
} 

echo '<form name="wvp_settings" id="wvp_settings" method="post" >';
    
    global $wvp, $wvp_settings;

    $general_option = $wvp_settings->wvp_get_settings_func( );

    extract($general_option);
    
    echo '<div class="cmrc-table">';

        /********************* Plan App limit Start ********************
        echo '<div class="setting-fb-config" >';
        echo '<h2>' . __('Hotjar Funnel Settings', WVP_txt_domain) . '</h2>';
        echo '<table class="form-table wvp-setting-form">';
        echo '<tbody>';
            echo '<tr>';
            echo '<th><label for="field_id">label</label></th>';
            echo '<td>';
            echo '<input name="field_name" id="field_id" type="text" value="" />';
            echo '</td>';
            echo '</tr>';
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        /********************* Plan App limit End **********************/ 


        /********************* Shortcode Section Start **********************/
        echo '<div class="setting-fb-config" >';
            echo '<h2>' . __('Shortcodes', WVP_txt_domain) . '</h2>';
            echo '<table class="form-table wvp-setting-form">';
            echo '<tbody>';
                echo '<tr>';
                echo '<th><label for="wvp_create_new_app">' . __('Shortcode', WVP_txt_domain) . '</label></th>';
                echo '<td> [Shortcodes] </td>';
                echo '</tr>';

            echo '</tbody>';
            echo '</table>';
        echo '</div>';
        /********************* Shortcode Section End **********************/

    echo '</div>';
    echo '<p class="submit">';
    echo '<input type="hidden" name="wvp_setting" id="wvp_setting" value="wvp_setting" />';
    echo '<input name="wvp_setting_save" class="button-primary wvp_setting_save" type="submit" value="Save changes"/>';
    echo '</p>';

echo '</form>';
echo '</div>';
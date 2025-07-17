<?php
if( !class_exists ( 'WVP_Settings' ) ) {

    class WVP_Settings {

        function __construct(){

            add_action( "wvp_save_settings", array( $this, "wvp_save_settings_func" ), 10 , 1 );

        } 
         
        function wvp_display_settings( ) {
            if( file_exists( WVP_INCLUDES_DIR . "wvp_settings.view.php" ) ) {
                include_once( WVP_INCLUDES_DIR . "wvp_settings.view.php" );
            }
        }

        function wvp_default_setting_option() {
            return array(
                'app_limit'  => array()
            );
        }

        function wvp_save_settings_func( $params = array() ) {
            if( isset( $params['wvp_setting'] ) && $params['wvp_setting'] != '') {
                $wvp_setting = $params['wvp_setting'];
                unset( $params['wvp_setting'] );
                unset( $params['wvp_setting_save'] );
                
                update_option('wvp_setting', $params);

                $_SESSION['wvp_msg_status'] = true;
                $_SESSION['wvp_msg'] = 'Settings updated successfully.';
            }
        }

        function wvp_get_settings_func( ) {
            $wvp_default_general_option = $this->wvp_default_setting_option();
            $wvp_setting_option = get_option( 'wvp_setting' );
            return shortcode_atts( $wvp_default_general_option, $wvp_setting_option );
        }

    }

    global $wvp_settings;
    $wvp_settings = new WVP_Settings();
}

?>
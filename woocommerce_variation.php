<?php
/*
Plugin Name: Woocommerce Variation Product
Plugin URI: https://etzme.org/
Description: Wordpress Integration for variation wise change image and price of the product
Version: 1.0.0
Author: kp dev
Author URI: https://wordpress.org/
Domain Path: /languages
Text Domain: wvp_text_domain
*/

// plugin definitions
define( 'WVP_PLUGIN', '/woocommerce_variation/');

// directory define
define( 'WVP_PLUGIN_DIR', WP_PLUGIN_DIR.WVP_PLUGIN);
define( 'WVP_INCLUDES_DIR', WVP_PLUGIN_DIR.'includes/' );
$upload = wp_upload_dir();

define( 'WVP_ASSETS_DIR', WVP_PLUGIN_DIR.'assets/' );
define( 'WVP_CSS_DIR', WVP_ASSETS_DIR.'css/' );
define( 'WVP_JS_DIR', WVP_ASSETS_DIR.'js/' );
define( 'WVP_IMAGES_DIR', WVP_ASSETS_DIR.'images/' );

// URL define
define( 'WVP_PLUGIN_URL', WP_PLUGIN_URL.WVP_PLUGIN);

define( 'WVP_ASSETS_URL', WVP_PLUGIN_URL.'assets/');
define( 'WVP_IMAGES_URL', WVP_ASSETS_URL.'images/');
define( 'WVP_CSS_URL', WVP_ASSETS_URL.'css/');
define( 'WVP_JS_URL', WVP_ASSETS_URL.'js/');

// define text domain
define( 'WVP_txt_domain', 'wvp_text_domain' );

global $wvp_version;
$wvp_version = '1.0.0';

class Woocommerce_Variation {

    var $wvp_setting = '';

	function __construct() {
        global $wpdb;

        $this->wvp_setting = 'wvp_setting';

		register_activation_hook( __FILE__,  array( &$this, 'wvp_install' ) );

        register_deactivation_hook( __FILE__, array( &$this, 'wvp_deactivation' ) );

		add_action( 'admin_menu', array( $this, 'wvp_add_menu' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'wvp_enqueue_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'wvp_front_enqueue_scripts' ) );

        add_action( 'plugins_loaded', array( $this, 'wvp_load_textdomain' ) );
        
	}

    function wvp_load_textdomain() {
        load_plugin_textdomain( WVP_txt_domain, false, basename(dirname(__FILE__)) . '/languages' ); //Loads plugin text domain for the translation
        do_action('WVP_txt_domain');
    }

	static function wvp_install() {

		global $wpdb, $wvp, $wvp_version;

        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        update_option( "wvp_plugin", true );
        update_option( "wvp_version", $wvp_version );
        
	}

    static function wvp_deactivation() {
        // deactivation process here
    }

	function wvp_get_sub_menu() {
		$wvp_admin_menu = array(
            array(
                'name' => __('Setting', WVP_txt_domain),
                'cap'  => 'manage_options',
                'slug' => $this->wvp_setting,
            )
		);
		return $wvp_admin_menu;
	}

	function wvp_add_menu() {

		$wvp_main_page_name = __('Setting', WVP_txt_domain);
		$wvp_main_page_capa = 'manage_options';
		$wvp_main_page_slug = $this->wvp_setting; 

		$wvp_get_sub_menu   = $this->wvp_get_sub_menu();
		/* set capablity here.... Right now manage_options capability given to all page and sub pages. <span class="dashicons dashicons-money"></span>*/	 
		// add_menu_page($wvp_main_page_name, $wvp_main_page_name, $wvp_main_page_capa, $wvp_main_page_slug, array( &$this, 'wvp_route' ), 'dashicons-screenoptions', 50 );

		foreach ($wvp_get_sub_menu as $wvp_menu_key => $wvp_menu_value) {
			add_submenu_page(
				$wvp_main_page_slug, 
				$wvp_menu_value['name'], 
				$wvp_menu_value['name'], 
				$wvp_menu_value['cap'], 
				$wvp_menu_value['slug'], 
				array( $this, 'wvp_route') 
			);	
		}
	}

	function wvp_is_activate(){
		if(get_option("wvp_plugin")) {
			return true;
		} else {
			return false;
		}
	}

	function wvp_admin_slugs() {
		$wvp_pages_slug = array(
			$this->wvp_setting,
			'wvp_item_part',
			'wvp_background',
			'wvp_sub_product',
			'sub_product_version',
			'wvp_part_color',
			'product'
		);
		return $wvp_pages_slug;
	}

	function wvp_is_page() {
		$slug_val = '';
		if( isset( $_REQUEST['page'] ) ) {
			$slug_val = $_REQUEST['page'];
		} else if( isset( $_REQUEST['taxonomy'] ) ) {
			$slug_val = $_REQUEST['taxonomy'];
		} else if( isset( $_REQUEST['post_type'] ) ) {
			$slug_val = $_REQUEST['post_type'];
		} else if( get_post_type() ) {
			$slug_val = get_post_type();
		}

		if( in_array( $slug_val, $this->wvp_admin_slugs() ) ) {
			return true;
		} else {
			return false;
		}
	} 

    function wvp_admin_msg( $key ) { 
        $admin_msg = array(
            "no_tax" => __("No matching tax rates found.", WVP_txt_domain)
        );

        if( $key == 'script' ){
            $script = '<script type="text/javascript">';
            $script.= 'var __wvp_msg = '.json_encode($admin_msg);
            $script.= '</script>';
            return $script;
        } else {
            return isset($admin_msg[$key]) ? $admin_msg[$key] : false;
        }
    }

	function wvp_enqueue_scripts() {
		global $wvp_version;
		/* must register style and than enqueue */
		if( $this->wvp_is_page() ) {
			/*********** register and enqueue styles ***************/
			wp_register_style( 'wvp_admin_style_css',  WVP_CSS_URL.'wvp_admin_style.css', false, $wvp_version );
            wp_enqueue_style( 'wvp_admin_style_css' );


			/*********** register and enqueue scripts ***************/
            echo $this->wvp_admin_msg( 'script' );
            wp_register_script( 'wvp_admin_js', WVP_JS_URL.'wvp_admin_js.js?rand='.rand(1,9), 'jQuery', $wvp_version, true );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'wvp_admin_js' );

		}
    }

    function wvp_front_enqueue_scripts() {
        global $wvp_version;
        // need to check here if its front section than enqueue script
        // if( $ncm_template_loader->ncm_is_front_page() ) {
        /*********** register and enqueue styles ***************/

            wp_register_style( 
                'wvp_front_css',  
                WVP_CSS_URL.'wvp_front.css?rand='.rand(1,999), 
                false, 
                $wvp_version 
            );

            wp_enqueue_style( 'wvp_front_css' );


            /*********** register and enqueue scripts ***************/
            echo "<script> var ajaxurl = '".admin_url( 'admin-ajax.php' )."'; </script>";

            wp_register_script( 
                'wvp_front_js', 
                WVP_JS_URL.'wvp_front.js?rand='.rand(1,999), 
                'jQuery', 
                $wvp_version, 
                true 
            );

            wp_enqueue_script( 'wvp_front_js' );
        // }
        
	}

	function wvp_route() {
		global $wvp, $wvp_settings;
		if( isset($_REQUEST['page']) && $_REQUEST['page'] != '' ){
			switch ( $_REQUEST['page'] ) {
				case $this->wvp_setting:
					$wvp_settings->wvp_display_settings();
					break;
			}
		}
	}

    function wvp_write_log( $content = '', $file_name = 'wvp_log.txt' ) {
        $file = __DIR__ . '/log/' . $file_name;    
        $file_content = "=============== Write At => " . date( "y-m-d H:i:s" ) . " =============== \r\n";
        $file_content .= $content . "\r\n\r\n";
        file_put_contents( $file, $file_content, FILE_APPEND | LOCK_EX );
    }
    
}


// begin!
global $wvp;
$wvp = new Woocommerce_Variation();

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_settings.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_settings.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_sub_product_version.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_sub_product_version.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_sub_product.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_sub_product.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_background.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_background.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_part_color.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_part_color.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_item_part.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_item_part.class.php" );
}

if( $wvp->wvp_is_activate() && file_exists( WVP_INCLUDES_DIR . "wvp_woocommerce_product.class.php" ) ) {
    include_once( WVP_INCLUDES_DIR . "wvp_woocommerce_product.class.php" );
}

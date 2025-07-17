<?php
if( !class_exists ( 'WVP_Sub_Product_Version' ) ) {

    class WVP_Sub_Product_Version {

        function __construct(){

            add_action( 'init', array( $this, 'wvp_sub_product_version_post_type' ) );

            add_action( 'add_meta_boxes', array( $this, 'wvp_add_item_meta_box' ) );

            add_action( 'save_post', array( $this, 'wvp_save_item_metabox' ), 10, 2 );

            add_filter( 'manage_sub_product_version_posts_columns', array( $this, 'wvp_sub_product_version_columns_head' ), 10);

            add_action( 'manage_sub_product_version_posts_custom_column', array( $this, 'wvp_sub_product_version_custom_fields' ), 10, 2);

            // add_action( 'wp_ajax_wvp_get_sub_product', array( $this, 'get_sub_product_func' ) );
 
        } 
         
        function wvp_sub_product_version_post_type() {
         
            $labels = array(
                'name'                => _x( 'Sub Product Version', 'Post Type General Name', WVP_txt_domain ),
                'singular_name'       => _x( 'Sub Product Version', 'Post Type Singular Name', WVP_txt_domain ),
                'menu_name'           => __( 'Sub Product Versions', WVP_txt_domain ),
                'parent_item_colon'   => __( 'Sub Product Version', WVP_txt_domain ),
                'all_items'           => __( 'All Sub Product Versions', WVP_txt_domain ),
                'view_item'           => __( 'View Sub Product Version', WVP_txt_domain ),
                'add_new_item'        => __( 'Add New Sub Product Version', WVP_txt_domain ),
                'add_new'             => __( 'Add New', WVP_txt_domain ),
                'edit_item'           => __( 'Edit Sub Product Version', WVP_txt_domain ),
                'update_item'         => __( 'Update Sub Product Version', WVP_txt_domain ),
                'search_items'        => __( 'Search Sub Product Version', WVP_txt_domain ),
                'not_found'           => __( 'Not Found', WVP_txt_domain ),
                'not_found_in_trash'  => __( 'Not found in Trash', WVP_txt_domain ),
            );
             
            $args = array(
                'label'               => __( 'Sub Product Version', WVP_txt_domain ),
                'description'         => __( 'Sub Product Versions', WVP_txt_domain ),
                'labels'              => $labels,
                'supports'            => array( 'title', 'thumbnail' ),
                'hierarchical'        => true,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => false,
                'menu_position'       => 51,
                'menu_icon'           => 'dashicons-screenoptions',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'rewrite'             => array('slug' => 'sub_product_version'),
                'capability_type'     => 'post',
                'show_in_rest'        => true,
                'rest_base'           => 'wvp',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
            );
             
            register_post_type( 'sub_product_version', $args );
        }

        function wvp_add_item_meta_box() {
            add_meta_box( 'sub_product_version', __( 'Sub Product Version', 'WVP_txt_domain'), array( $this, 'wvp_version_of_sub_product'), 'sub_product_version', 'normal', 'default' );
        }
        
        function wvp_version_of_sub_product($post) {
            global $wvp_wc_product, $wvp_part_color;
            $post_id = $post->ID;

            $wc_products = $wvp_wc_product->get_wc_product();
            $selected_products = get_post_meta( $post_id, 'wvp_wc_product_ids', true );
            $wvp_sub_product_id = get_post_meta( $post_id, 'wvp_sub_product_id', true );
            $wvp_price = get_post_meta( $post_id, 'wvp_price', true );
            $wvp_desc = get_post_meta( $post_id, 'wvp_desc', true );

            wp_nonce_field( plugin_basename( __FILE__ ), 'wvp_sub_product_version' );
            echo '<table class="wvp_part_color">';
            echo '<tr>';
                echo '<td>';
                echo '<label><b>'.__('Select Woocommerce Products', WVP_txt_domain).'</b></label>';
                echo '</td>';
                echo '<td>';
                echo '<div class="select_wc_product">';
                if( !empty($wc_products) ) {
                    foreach($wc_products as $product) {
                        $selected = ( !empty($selected_products) && in_array( $product->ID, $selected_products ) ) ? ' checked="checked" ' : '';
                        echo '<input type="checkbox" class="wvp_wc_product_ids" name="wvp_wc_product_ids[]" id="bab_pages_'.$product->ID.'"  value="'.$product->ID.'" '.$selected.'>';
                        echo '<label for="bab_pages_'.$product->ID.'"> '.$product->post_title.'</label><br/>';
                    }
                } else {
                    _e('Please add Woocommerce Product and enable the Sub Product Version.', WVP_txt_domain);
                }
                echo '</div>';
                echo '</td>';
            echo '</tr>';

            echo $wvp_part_color->get_sub_product_func( false, $post_id, $selected_products );

            echo '<tr>';
                echo '<td>';
                echo '<label for="wvp_price"><b>'.__('Price', WVP_txt_domain).'</b></label>';
                echo '</td>';
                echo '<td>';
                echo '<input type="number" name="wvp_price" id="wvp_price" class="wvp_price" value="' . $wvp_price . '" ?>';
                echo '</td>';
            echo '</tr>';

            echo '<tr>';
                echo '<td>';
                echo '<label for="wvp_desc"><b>'.__('Description', WVP_txt_domain).'</b></label>';
                echo '</td>';
                echo '<td>';
                echo '<textarea name="wvp_desc" id="wvp_desc" class="wvp_desc" >' . $wvp_desc . '</textarea>';
                echo '</td>';
            echo '</tr>';


            echo '</table>';

            echo '<div class="mvp_error"></div>';
        }

        function wvp_save_item_metabox( $post_id, $post_object ) {
            if( !isset( $post_object->post_type ) || 'sub_product_version' != $post_object->post_type )
                return;
        
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return;
        
            if ( !isset( $_POST['wvp_sub_product_version'] ) || !wp_verify_nonce( $_POST['wvp_sub_product_version'], plugin_basename( __FILE__ ) ) )
                return;
        
            if ( isset( $_POST['wvp_wc_product_ids'] ) ) {
                update_post_meta( $post_id, 'wvp_wc_product_ids', $_POST['wvp_wc_product_ids'] );
            } else {
                update_post_meta( $post_id, 'wvp_wc_product_ids', '' );
            }
            
            if( isset( $_POST['wvp_sub_product_id'] ) ) {
                update_post_meta( $post_id, 'wvp_sub_product_id', $_POST['wvp_sub_product_id'] );
            } else {
                update_post_meta( $post_id, 'wvp_sub_product_id', '' );
            }

            if( isset( $_POST['wvp_price'] ) ) {
                update_post_meta( $post_id, 'wvp_price', $_POST['wvp_price'] );
            } else {
                update_post_meta( $post_id, 'wvp_price', 0 );
            }

            if( isset( $_POST['wvp_desc'] ) ) {
                update_post_meta( $post_id, 'wvp_desc', $_POST['wvp_desc'] );
            } else {
                update_post_meta( $post_id, 'wvp_desc', 0 );
            }
        }

        function wvp_sub_product_version_columns_head($columns) {
            $newcolumns = array();
            foreach($columns as $key => $title) {
                if ($key=='date') {
                    $newcolumns['wvp_wc_product_ids'] = __( 'Woocommerce Products', WVP_txt_domain );
                    $newcolumns['wvp_sub_product_id'] = __( 'Sub Products', WVP_txt_domain );
                    $newcolumns['wvp_price'] = __( 'Price', WVP_txt_domain );
                }
                $newcolumns[$key] = $title;
            }
            return $newcolumns;
        }

        function wvp_sub_product_version_custom_fields($column_name, $post_ID) {
            if ($column_name == 'wvp_wc_product_ids') {
                $woo_products = get_post_meta( $post_ID, 'wvp_wc_product_ids', true );
                if( !empty($woo_products) ) {
                    foreach ($woo_products as $woo_pid) {                     
                        $product = wc_get_product( $woo_pid );
                        echo $product->get_name() . '<br/>';
                          
                    }
                }
            } else if($column_name == 'wvp_sub_product_id') {
                $sub_product_id = get_post_meta( $post_ID, 'wvp_sub_product_id', true );
                if( !empty($sub_product_id) ) {
                    echo get_the_title( $sub_product_id );
                }
            } else if($column_name == 'wvp_price') {
                $wvp_price = get_post_meta( $post_ID, 'wvp_price', true );
                if( !empty($wvp_price) ) {
                    echo $wvp_price;
                }
            }
        }

        /*function get_sub_product_func( $is_ajax = true, $post_id = '', $wc_product_id = '' ) {
            global $wvp_sub_product;
            $return = array();
            $selected_spid = 0;
            if( isset($_POST['wvp_wc_product_ids']) && !empty($_POST['wvp_wc_product_ids']) ) {
                $wc_product_id = $_POST['wvp_wc_product_ids'];
                $is_ajax = true;
            } else {
                $selected_spid = get_post_meta( $post_id, 'wvp_sub_product_id', true );
            }

            $label = __('Select Sub Products', WVP_txt_domain);
            if( isset($wc_product_id) && !empty($wc_product_id) ) {
                $sub_products = $wvp_sub_product->get_sub_products_using_wc_pid( $wc_product_id );

                if( $sub_products ) {
                    $content = '<tr class="wvp_sub_product_row">';
                        $content.= '<td>';
                        $content.= '<label for="wvp_sub_product_id"><b>'.$label.'</b></label>';
                        $content.= '</td>';
                        $content.= '<td>';
                        $content.= '<select name="wvp_sub_product_id" id="wvp_sub_product_id" class="wvp_sub_product_id">'; 
                        $content.= '<option value="">'.__('Please Select', WVP_txt_domain).'</option>';
                        foreach ($sub_products as $product) {
                            $selected = ( $selected_spid == $product->ID ) ? 'selected="selected"' : '';
                            $content.= '<option value="'.$product->ID.'" '.$selected.'>'.$product->post_title.'</option>';
                        }
                        $content.= '</select>'; 
                        $content.= '</td>';
                    $content.= '</tr>';

                    $return['status'] = 'success';
                    $return['message'] = $content;
                } else{
                    $content = '<tr class="wvp_sub_product_row">';
                    $content.= '<td>'.$label.'</td>';
                    $content.= '<td>'.__('No sub product found.', WVP_txt_domain).'</td>';
                    $content.= '</tr>';

                    $return['status'] = 'error';
                    $return['message'] = $content;
                }
            } else {
                $content = '<tr class="wvp_sub_product_row">';
                $content.= '<td>'.$label.'</td>';
                $content.= '<td>'.__('Please select woocommerce product.', WVP_txt_domain).'</td>';
                $content.= '</tr>';

                $return['status'] = 'error';
                $return['message'] = $content;
            }

            if( $is_ajax ) {
                echo json_encode( $return );
                die;
            } else {
                return $content;
            }
        } */

        function get_sp_version_using_sub_product_id( $sub_product_id, $wc_product_id='' ) {
            $meta_query = array();

            $product_meta_query = '';
            if( is_array($wc_product_id ) && !empty($wc_product_id ) ) {
                $product_meta_query = array();
                foreach ( $wc_product_id as $product_id ) {
                    $product_meta_query[] = array(
                        'key'     => 'wvp_wc_product_ids',
                        'value'   => '"'.$product_id.'";',
                        'compare' => 'LIKE',
                    );
                }

                $product_meta_query['relation'] = 'OR';
            } else if( !empty($wc_product_id ) ){
                $product_meta_query = array(
                    array(
                        'key'     => 'wvp_wc_product_ids',
                        'value'   => '"'.$wc_product_id.'";',
                        'compare' => 'LIKE',
                    )
                );
            }


            $sub_product_query = '';
            if( is_array($sub_product_id ) && !empty($sub_product_id ) ) {
                $sub_product_query = array();
                foreach ( $sub_product_id as $sp_id ) {
                    $sub_product_query[] = array(
                        'key'     => 'wvp_sub_product_id',
                        'value'   => $sp_id,
                        'compare' => '=',
                    );
                }

                $sub_product_query['relation'] = 'OR';
            } else {
                $sub_product_query = array(
                    array(
                        'key'     => 'wvp_sub_product_id',
                        'value'   => $sub_product_id,
                        'compare' => '=',
                    )
                );
            }

            $meta_query['relation'] = 'AND';
            if( !empty( $product_meta_query ) ) {
                $meta_query[] = $product_meta_query;
            }
            $meta_query[] = $sub_product_query;

            $args = array(
                'numberposts' => '-1',
                'post_type' => 'sub_product_version',
                'post_status' => 'publish',
                'meta_query' => $meta_query,
            );

            $data = get_posts( $args );

            return $data;
        }


    }

    global $wvp_sp_version;
    $wvp_sp_version = new WVP_Sub_Product_Version();
}

?>

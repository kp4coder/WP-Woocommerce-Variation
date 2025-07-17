<?php
if( !class_exists ( 'WVP_Sub_Product' ) ) {

    class WVP_Sub_Product {

        function __construct(){

            add_action( 'init', array( $this, 'wvp_sub_product_post_type' ) );

            add_action( 'add_meta_boxes', array( $this, 'wvp_add_item_meta_box' ) );

            add_action( 'save_post', array( $this, 'wvp_save_item_metabox' ), 10, 2 );

            add_filter( 'manage_wvp_sub_product_posts_columns', array( $this, 'wvp_sub_product_columns_head' ), 10);

            add_action( 'manage_wvp_sub_product_posts_custom_column', array( $this, 'wvp_sub_product_custom_fields' ), 10, 2);
 
        } 
         
        function wvp_sub_product_post_type() {
         
            $labels = array(
                'name'                => _x( 'Sub Product', 'Post Type General Name', WVP_txt_domain ),
                'singular_name'       => _x( 'Sub Product', 'Post Type Singular Name', WVP_txt_domain ),
                'menu_name'           => __( 'Sub Products', WVP_txt_domain ),
                'parent_item_colon'   => __( 'Sub Product', WVP_txt_domain ),
                'all_items'           => __( 'All Sub Products', WVP_txt_domain ),
                'view_item'           => __( 'View Sub Product', WVP_txt_domain ),
                'add_new_item'        => __( 'Add New Sub Product', WVP_txt_domain ),
                'add_new'             => __( 'Add New', WVP_txt_domain ),
                'edit_item'           => __( 'Edit Sub Product', WVP_txt_domain ),
                'update_item'         => __( 'Update Sub Product', WVP_txt_domain ),
                'search_items'        => __( 'Search Sub Product', WVP_txt_domain ),
                'not_found'           => __( 'Not Found', WVP_txt_domain ),
                'not_found_in_trash'  => __( 'Not found in Trash', WVP_txt_domain ),
            );
             
            $args = array(
                'label'               => __( 'Sub Product', WVP_txt_domain ),
                'description'         => __( 'Sub Products', WVP_txt_domain ),
                'labels'              => $labels,
                'supports'            => array( 'title' ),
                'hierarchical'        => true,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => false,
                'menu_position'       => 50.1,
                'menu_icon'           => 'dashicons-screenoptions',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'rewrite'             => array('slug' => 'wvp_sub_product'),
                'capability_type'     => 'post',
                'show_in_rest'        => true,
                'rest_base'           => 'wvp',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
            );
             
            register_post_type( 'wvp_sub_product', $args );
        }

        function wvp_add_item_meta_box() {
            add_meta_box( 'wvp_sub_product', __( 'Woocommerce Product', 'WVP_txt_domain'), array( $this, 'wvp_sub_product_parts'), 'wvp_sub_product', 'normal', 'default' );
        }
        
        function wvp_sub_product_parts($post) {
            global $wvp_wc_product;
            
            wp_nonce_field( plugin_basename( __FILE__ ), 'wvp_item_metabox' );

            $wc_products = $wvp_wc_product->get_wc_product();
            $selected_products = get_post_meta( $post->ID, 'wvp_wc_product_ids', true );
            $parts_name = get_post_meta( $post->ID, 'wvp_parts_name', true );

            echo '<table class="wvp_sub_product">';
            echo '<tr>';
                echo '<td>';
                echo '<label><b>'.__('Select Woocommerce Products', WVP_txt_domain).'</b></label>';
                echo '</td>';
                echo '<td>';
                echo '<div class="select_wc_product">';
                if( !empty($wc_products) ) {
                    foreach($wc_products as $product) {
                        $selected = ( !empty($selected_products) && in_array( $product->ID, $selected_products ) ) ? ' checked="checked" ' : '';
                        echo '<input type="checkbox" name="wvp_wc_product_ids[]" id="bab_pages_'.$product->ID.'"  value="'.$product->ID.'" '.$selected.'>';
                        echo '<label for="bab_pages_'.$product->ID.'"> '.$product->post_title.'</label><br/>';
                    }
                } else {
                    _e('Please add Woocommerce Product and enable the sub product.', WVP_txt_domain);
                }
                echo '</div>';
                echo '</td>';
            echo '</tr>';
            echo '</table>';
            
            // echo '<tr>';
            //     echo '<td>';
            //     echo '<label for="part_name"><b>'.__('Enter an item part name', WVP_txt_domain).'</b></label>';
            //     echo '</td>';
            //     echo '<td>';
            //     echo '<input type="text" name="part_name" id="part_name" class="part_name" /> ';
            //     echo '<input type="button" name="wvp_add_part" id="wvp_add_part" class="wvp_add_part" value="'.__('Add', WVP_txt_domain).'">';
            //     echo '</td>';
            // echo '</tr>';
            // echo '<tr><td></td><td>';
            //     echo '<div class="wvp_parts_name">';
            //     if( !empty($parts_name) ) {
            //         foreach ($parts_name as $partname) {
            //             echo '<div class="wvp_part_name">';
            //             echo '<span>'.$partname.'</span>';
            //             echo '<a href="javascript:void(0);" class="wvp_part_remove">';
            //             echo '<span class="dashicons dashicons-dismiss"></span>';
            //             echo '</a>';
            //             echo '<input type="hidden" name="wvp_parts_name[]" value="'.$partname.'"/>';
            //             echo '</div>';
            //         }
            //     }
            //     echo '</div>';
            // echo '</tr>';
            

            // echo '<script id="sps_setting_table" type="text/html">';
            //     echo '<div class="wvp_part_name">';
            //     echo '<span>{wvp_part_name}</span>';
            //     echo '<a href="javascript:void(0);" class="wvp_part_remove">';
            //     echo '<span class="dashicons dashicons-dismiss"></span>';
            //     echo '</a>';
            //     echo '<input type="hidden" name="wvp_parts_name[]" value="{wvp_part_name}"/>';
            //     echo '</div>';
            // echo '</script>';

        }

        function wvp_save_item_metabox( $post_id, $post_object )
        {
            if( !isset( $post_object->post_type ) || 'wvp_sub_product' != $post_object->post_type )
                return;
        
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return;
        
            if ( !isset( $_POST['wvp_item_metabox'] ) || !wp_verify_nonce( $_POST['wvp_item_metabox'], plugin_basename( __FILE__ ) ) )
                return;
        
            if ( isset( $_POST['wvp_wc_product_ids'] ) ) {
                update_post_meta( $post_id, 'wvp_wc_product_ids', $_POST['wvp_wc_product_ids'] );
            } else {
                update_post_meta( $post_id, 'wvp_wc_product_ids', '' );
            }
            
            // if( isset( $_POST['wvp_parts_name'] ) ) {
            //     update_post_meta( $post_id, 'wvp_parts_name', $_POST['wvp_parts_name'] );
            // } else {
            //     update_post_meta( $post_id, 'wvp_parts_name', '' );
            // }
        }

        function wvp_sub_product_columns_head($columns) {
            $newcolumns = array();
            foreach($columns as $key => $title) {
                if ($key=='date') {
                    $newcolumns['wvp_wc_product_ids'] = __( 'Woocommerce Products', WVP_txt_domain );
                    $newcolumns['wvp_parts_name'] = __( 'Parts Name', WVP_txt_domain );
                }
                $newcolumns[$key] = $title;
            }
            return $newcolumns;
        }

        function wvp_sub_product_custom_fields($column_name, $post_ID) {
            if ($column_name == 'wvp_wc_product_ids') {
                $woo_products = get_post_meta( $post_ID, 'wvp_wc_product_ids', true );
                if( !empty($woo_products) ) {
                    foreach ($woo_products as $woo_pid) {                     
                        $product = wc_get_product( $woo_pid );
                        echo $product->get_name() . '<br/>';
                          
                    }
                }
            } else if($column_name == 'wvp_parts_name') {
                $parts_name = wp_get_post_terms($post_ID, 'wvp_item_part');
                if( $parts_name ) {
                    foreach($parts_name as $part) {
                        echo $part->name . '<br/>'; 
                    }
                }

                // $parts_name = get_post_meta( $post_ID, 'wvp_parts_name', true );
                // if( !empty($parts_name) ) {
                //     foreach ($parts_name as $partname) {
                //         echo $partname . '<br/>';
                //     }
                // }
            }
        }

        function get_sub_products_using_wc_pid( $wc_product_id ) {
            if( is_array($wc_product_id ) && !empty($wc_product_id ) ) {
                $meta_query = array();
                foreach ( $wc_product_id as $product_id ) {
                    $meta_query[] = array(
                        'key'     => 'wvp_wc_product_ids',
                        'value'   => '"'.$product_id.'";',
                        'compare' => 'LIKE',
                    );
                }

                $meta_query['relation'] = 'OR';
            } else {
                $meta_query = array(
                    array(
                        'key'     => 'wvp_wc_product_ids',
                        'value'   => '"'.$wc_product_id.'";',
                        'compare' => 'LIKE',
                    )
                );
            }

            $args = array(
                'numberposts' => '-1',
                'post_type' => 'wvp_sub_product',
                'post_status' => 'publish',
                'meta_query' => $meta_query,
            );

            $data = get_posts( $args );

            return $data;
        }

    }

    global $wvp_sub_product;
    $wvp_sub_product = new WVP_Sub_Product();
}

?>

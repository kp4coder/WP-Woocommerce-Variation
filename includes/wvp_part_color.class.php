<?php
if( !class_exists ( 'WVP_Part_Color' ) ) {

    class WVP_Part_Color {

        function __construct(){

            add_action( 'init', array( $this, 'wvp_part_color_post_type' ) );

            add_action( 'add_meta_boxes', array( $this, 'wvp_add_item_meta_box' ) );

            add_action( 'save_post', array( $this, 'wvp_save_item_metabox' ), 10, 2 );

            add_filter( 'manage_wvp_part_color_posts_columns', array( $this, 'wvp_part_color_columns_head' ), 10);

            add_action( 'manage_wvp_part_color_posts_custom_column', array( $this, 'wvp_part_color_custom_fields' ), 10, 2);

            add_action( 'wp_ajax_wvp_get_sub_product', array( $this, 'get_sub_product_func' ) );

            add_action( 'wp_ajax_wvp_get_item_parts', array( $this, 'wvp_get_item_parts_func' ) );

            add_action( 'wp_ajax_wvp_get_sub_product_version', array( $this, 'get_sub_product_version_func' ) );
 
        } 
         
        function wvp_part_color_post_type() {
         
            $labels = array(
                'name'                => _x( 'Part Color', 'Post Type General Name', WVP_txt_domain ),
                'singular_name'       => _x( 'Part Color', 'Post Type Singular Name', WVP_txt_domain ),
                'menu_name'           => __( 'Part Colors', WVP_txt_domain ),
                'parent_item_colon'   => __( 'Part Color', WVP_txt_domain ),
                'all_items'           => __( 'All Part Colors', WVP_txt_domain ),
                'view_item'           => __( 'View Part Color', WVP_txt_domain ),
                'add_new_item'        => __( 'Add New Part Color', WVP_txt_domain ),
                'add_new'             => __( 'Add New', WVP_txt_domain ),
                'edit_item'           => __( 'Edit Part Color', WVP_txt_domain ),
                'update_item'         => __( 'Update Part Color', WVP_txt_domain ),
                'search_items'        => __( 'Search Part Color', WVP_txt_domain ),
                'not_found'           => __( 'Not Found', WVP_txt_domain ),
                'not_found_in_trash'  => __( 'Not found in Trash', WVP_txt_domain ),
            );
             
            $args = array(
                'label'               => __( 'Part Color', WVP_txt_domain ),
                'description'         => __( 'Part Colors', WVP_txt_domain ),
                'labels'              => $labels,
                'supports'            => array( 'title' ),
                'hierarchical'        => true,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => false,
                'menu_position'       => 52,
                'menu_icon'           => 'dashicons-color-picker',
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'rewrite'             => array('slug' => 'wvp_part_color'),
                'capability_type'     => 'post',
                'show_in_rest'        => true,
                'rest_base'           => 'wvp',
                'rest_controller_class' => 'WP_REST_Posts_Controller',
            );
             
            register_post_type( 'wvp_part_color', $args );
        }

        function wvp_add_item_meta_box() {
            add_meta_box( 'wvp_part_color', __( 'Product / Part', 'WVP_txt_domain'), array( $this, 'wvp_part_color_productparts'), 'wvp_part_color', 'normal', 'default' );
        }
        
        function wvp_part_color_productparts($post) {
            global $wvp_wc_product;
            $post_id = $post->ID;

            $wc_products = $wvp_wc_product->get_wc_product();
            $selected_products = get_post_meta( $post_id, 'wvp_wc_product_ids', true );
            $wvp_sub_product_id = get_post_meta( $post_id, 'wvp_sub_product_id', true );
            $wvp_item_part_id = get_post_meta( $post_id, 'wvp_item_part_id', true );
            $wvp_price = get_post_meta( $post_id, 'wvp_price', true );
            $wvp_rb_image = get_post_meta( $post_id, 'wvp_rb_image', true );
 
            wp_nonce_field( plugin_basename( __FILE__ ), 'wvp_item_metabox' );
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
                    _e('Please add Woocommerce Product and enable the Part Color.', WVP_txt_domain);
                }
                echo '</div>';
                echo '</td>';
            echo '</tr>';

            echo $this->get_sub_product_func( false, $post_id, $selected_products );

            echo $this->wvp_get_item_parts_func( false, $post_id, $wvp_sub_product_id );

            echo '<tr>';
                echo '<td>';
                echo '<label for="wvp_rb_image"><b>'.__('Radio Button Image', WVP_txt_domain).'</b></label>';
                echo '</td>';
                echo '<td>';
                echo '<input type="hidden" name="wvp_rb_image" id="wvp_bg_image_id" class="wvp_bg_image_id" value="' . $wvp_rb_image . '" />';
                    echo '<div id="wvp_bg_image_wrapper">';
                    if ( $wvp_rb_image ) {
                        echo wp_get_attachment_image ( $wvp_rb_image, 'thumbnail' );
                    }
                    echo '</div>';
                    echo '<p>';
                        echo '<input type="button" class="button button-secondary wvp_bg_media_button" id="wvp_bg_media_button" name="wvp_bg_media_button" value="'.__( 'Add Image', WVP_txt_domain ).'" data-hidden="wvp_bg_image_id" data-image-wrapper="wvp_bg_image_wrapper" />';
                        echo '<input type="button" class="button button-secondary wvp_bg_media_remove" id="wvp_bg_media_remove" name="wvp_bg_media_remove" value="'.__( 'Remove Image', WVP_txt_domain ).'" data-hidden="wvp_bg_image_id" data-image-wrapper="wvp_bg_image_wrapper" />';
                    echo '</p>';
                echo '</td>';
            echo '</tr>';
            
            echo $this->get_sub_product_version_func( false, $post_id, $wvp_sub_product_id );

            echo '</table>';

            // echo '<div class="mvp_error"></div>';

        }

        function wvp_save_item_metabox( $post_id, $post_object ) {
            if( !isset( $post_object->post_type ) || 'wvp_part_color' != $post_object->post_type )
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
            
            if( isset( $_POST['wvp_sub_product_id'] ) ) {
                update_post_meta( $post_id, 'wvp_sub_product_id', $_POST['wvp_sub_product_id'] );
            } else {
                update_post_meta( $post_id, 'wvp_sub_product_id', '' );
            }

            if( isset( $_POST['wvp_item_part_id'] ) ) {
                update_post_meta( $post_id, 'wvp_item_part_id', $_POST['wvp_item_part_id'] );
            } else {
                update_post_meta( $post_id, 'wvp_item_part_id', '' );
            }

            if( isset( $_POST['wvp_rb_image'] ) ) {
                update_post_meta( $post_id, 'wvp_rb_image', $_POST['wvp_rb_image'] );
            } else {
                update_post_meta( $post_id, 'wvp_rb_image', 0 );
            }

            if( isset( $_POST['wvp_spv_image'] ) ) {
                update_post_meta( $post_id, 'wvp_spv_image', $_POST['wvp_spv_image'] );
            } else {
                update_post_meta( $post_id, 'wvp_spv_image', 0 );
            }
        }

        function wvp_part_color_columns_head($columns) {
            $newcolumns = array();
            foreach($columns as $key => $title) {
                if ($key=='date') {
                    $newcolumns['wvp_wc_product_ids'] = __( 'Woocommerce Products', WVP_txt_domain );
                    $newcolumns['wvp_sub_product_id'] = __( 'Sub Products', WVP_txt_domain );
                    $newcolumns['wvp_item_part_id'] = __( 'Parts Name', WVP_txt_domain );
                }
                $newcolumns[$key] = $title;
            }
            return $newcolumns;
        }

        function wvp_part_color_custom_fields($column_name, $post_ID) {
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
            } else if($column_name == 'wvp_item_part_id') {
                $item_part_id = get_post_meta( $post_ID, 'wvp_item_part_id', true );
                $terms = get_term_by( 'id', $item_part_id, 'wvp_item_part');
                if( !empty($terms) ) {
                    echo $terms->name;
                }
            }
        }

        function get_sub_product_func( $is_ajax = true, $post_id = '', $wc_product_id = '' ) {
            global $wvp_sub_product;
            $return = array();
            $selected_spid = 0;
            $is_ajax = ( isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'wvp_get_sub_product' ) ? true : false;

            if( isset($_POST['wvp_wc_product_ids']) && !empty($_POST['wvp_wc_product_ids']) ) {
                $wc_product_id = $_POST['wvp_wc_product_ids'];
            } 

            $post_id = ( isset($_POST['post_id']) && !empty($_POST['post_id']) ) ? $_POST['post_id'] : $post_id;
            $selected_spid = get_post_meta( $post_id, 'wvp_sub_product_id', true );

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
        }

        function wvp_get_item_parts_func( $is_ajax = true, $post_id = '', $sub_product_id = '' ) {
            global $wvp_item_part;
            $return = array();
            $selected_ipid = 0;
            $is_ajax = ( isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'wvp_get_item_parts' ) ? true : false;

            if( isset($_POST['wvp_sub_product_id']) && !empty($_POST['wvp_sub_product_id']) ) {
                $sub_product_id = $_POST['wvp_sub_product_id'];
            } else {
                $selected_ipid = get_post_meta( $post_id, 'wvp_item_part_id', true );
            }

            $label = __('Select Item Part', WVP_txt_domain);
            if( isset($sub_product_id) && !empty($sub_product_id) ) {
                $item_parts = wp_get_post_terms($sub_product_id, 'wvp_item_part');

                if( $item_parts ) {
                    $content = '<tr class="wvp_item_parts">';
                        $content.= '<td>';
                        $content.= '<label for="wvp_item_part_id"><b>'.$label.'</b></label>';
                        $content.= '</td>';
                        $content.= '<td>';
                        $content.= '<select name="wvp_item_part_id" id="wvp_item_part_id" class="wvp_item_part_id">'; 
                        $content.= '<option value="">'.__('Please Select', WVP_txt_domain).'</option>';
                        foreach ($item_parts as $item) {
                            $selected = ( $selected_ipid == $item->term_id ) ? 'selected="selected"' : '';
                            $content.= '<option value="'.$item->term_id.'" '.$selected.'>'.$item->name.'</option>';
                        }
                        $content.= '</select>'; 
                        $content.= '</td>';
                    $content.= '</tr>';

                    $return['status'] = 'success';
                    $return['message'] = $content;
                } else{
                    $content = '<tr class="wvp_item_parts">';
                    $content.= '<td>'.$label.'</td>';
                    $content.= '<td>'.__('No item parts found.', WVP_txt_domain).'</td>';
                    $content.= '</tr>';

                    $return['status'] = 'error';
                    $return['message'] = $content;
                }
            } else {
                $content = '<tr class="wvp_item_parts">';
                $content.= '<td>'.$label.'</td>';
                $content.= '<td>'.__('Please select sub product.', WVP_txt_domain).'</td>';
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
        }

        function get_sub_product_version_func( $is_ajax = true, $post_id = '', $sub_product_id = '' ) {
            global $wvp_sp_version;
            $return = array();
            $selected_spv_id = array();
            $is_ajax = ( isset($_POST['action']) && !empty($_POST['action']) && $_POST['action'] == 'wvp_get_sub_product_version' ) ? true : false;
            
            if( isset($_POST['wvp_sub_product_id']) && !empty($_POST['wvp_sub_product_id']) ) {
                $sub_product_id = $_POST['wvp_sub_product_id'];
            } else {
                $selected_spv_id = get_post_meta( $post_id, 'wvp_spv_image', true );
            }
            
            $label = __('Select Image for Versions', WVP_txt_domain);
            if( isset($sub_product_id) && !empty($sub_product_id) ) {
                $sp_versions = $wvp_sp_version->get_sp_version_using_sub_product_id( $sub_product_id );

                if( $sp_versions ) {
                    $content = '<tr class="wvp_sp_version">';
                        $content.= '<td>'.$label.'</td>';
                        $content.= '<td>';
                        foreach ($sp_versions as $version) {
                            $vid = $version->ID;
                            $wvp_spv_image = isset( $selected_spv_id[$vid] ) ? $selected_spv_id[$vid] : '';
                            $hidden_field = 'wvp_spv_image_'.$vid;
                            $image_wrapper = 'wvp_spv_image_wrapper_'.$vid;
                            
                            $content.= '<table>';
                                $content.= '<tr>';
                                    $content.= '<td>';
                                    $content.= '<label for="wvp_spv_image"><b>'.$version->post_title.'</b></label>';
                                    $content.= '</td>';
                                    $content.= '<td>';
                                    $content.= '<input type="hidden" name="wvp_spv_image['.$vid.']" id="'.$hidden_field.'" class="'.$hidden_field.'" value="'.$wvp_spv_image.'" />';
                                        $content.= '<div id="'.$image_wrapper.'">';
                                        if ( $wvp_spv_image ) {
                                            $content.= wp_get_attachment_image ( $wvp_spv_image, 'thumbnail' );
                                        }
                                        $content.= '</div>';
                                        $content.= '<p>';
                                            $content.= '<input type="button" class="button button-secondary wvp_bg_media_button" id="wvp_bg_media_button" name="wvp_bg_media_button" value="'.__( 'Add Image', WVP_txt_domain ).'" data-hidden="'.$hidden_field.'" data-image-wrapper="'.$image_wrapper.'" />';
                                            $content.= '<input type="button" class="button button-secondary wvp_bg_media_remove" id="wvp_bg_media_remove" name="wvp_bg_media_remove" value="'.__( 'Remove Image', WVP_txt_domain ).'" data-hidden="'.$hidden_field.'" data-image-wrapper="'.$image_wrapper.'" />';
                                        $content.= '</p>';
                                    $content.= '</td>';
                                $content.= '</tr>';
                            $content.= '</table>';
                        
                        }
                    $content.= '</td>';
                    $content.= '</tr>';
                    $return['status'] = 'success';
                    $return['message'] = $content;
                } else{
                    $content = '<tr class="wvp_sp_version">';
                    $content.= '<td>'.$label.'</td>';
                    $content.= '<td>'.__('No sub product version found.', WVP_txt_domain).'</td>';
                    $content.= '</tr>';

                    $return['status'] = 'error';
                    $return['message'] = $content;
                }
            } else {
                $content = '<tr class="wvp_sp_version">';
                $content.= '<td>'.$label.'</td>';
                $content.= '<td>'.__('Please select sub product.', WVP_txt_domain).'</td>';
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
        }

        function get_part_color_using_item_part_id( $term_id, $wc_product_id ) {
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
            } else {
                $product_meta_query = array(
                    array(
                        'key'     => 'wvp_wc_product_ids',
                        'value'   => '"'.$wc_product_id.'";',
                        'compare' => 'LIKE',
                    )
                );
            }


            $term_meta_query = '';
            if( is_array($term_id ) && !empty($term_id ) ) {
                $term_meta_query = array();
                foreach ( $term_id as $termid ) {
                    $term_meta_query[] = array(
                        'key'     => 'wvp_item_part_id',
                        'value'   => $termid,
                        'compare' => '=',
                    );
                }

                $term_meta_query['relation'] = 'OR';
            } else {
                $term_meta_query = array(
                    array(
                        'key'     => 'wvp_item_part_id',
                        'value'   => $term_id,
                        'compare' => '=',
                    )
                );
            }

            $meta_query['relation'] = 'AND';
            $meta_query[] = $product_meta_query;
            $meta_query[] = $term_meta_query;

            $args = array(
                'numberposts' => '-1',
                'post_type' => 'wvp_part_color',
                'post_status' => 'publish',
                'meta_query' => $meta_query,
            );

            $data = get_posts( $args );

            return $data;
        }

        function get_part_color_using_wc_product_id( $wc_product_id ) {
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
                'post_type' => 'wvp_part_color',
                'post_status' => 'publish',
                'meta_query' => $meta_query,
            );

            $data = get_posts( $args );

            return $data;
        }
    }

    global $wvp_part_color;
    $wvp_part_color = new WVP_Part_Color();
}



/*/ 1. This captures additional posted information (all sent in one array)

add_filter('woocommerce_add_cart_item_data','wdm_add_item_data',1,10);
function wdm_add_item_data($cart_item_data, $product_id) {

    global $woocommerce;
    $new_value = array();
    $new_value['_custom_options'] = $_POST['custom_options'];

    if(empty($cart_item_data)) {
        return $new_value;
    } else {
        return array_merge($cart_item_data, $new_value);
    }
}

// 2. This captures the information from the previous function and attaches it to the item.

add_filter('woocommerce_get_cart_item_from_session', 'wdm_get_cart_items_from_session', 1, 3 );
function wdm_get_cart_items_from_session($item,$values,$key) {

    if (array_key_exists( '_custom_options', $values ) ) {
        $item['_custom_options'] = $values['_custom_options'];
    }

    return $item;
}

// 3. This displays extra information on basket & checkout from within the added info that was attached to the item.

add_filter('woocommerce_cart_item_name','add_usr_custom_session',1,3);
function add_usr_custom_session($product_name, $values, $cart_item_key ) {

    $return_string = $product_name . "<br />" . $values['_custom_options']['description'];// . "<br />" . print_r($values['_custom_options']);
    return $return_string;

}

// 4. This adds the information as meta data so that it can be seen as part of the order (to hide any meta data from the customer just start it with an underscore)

add_action('woocommerce_add_order_item_meta','wdm_add_values_to_order_item_meta',1,2);
function wdm_add_values_to_order_item_meta($item_id, $values) {
    global $woocommerce,$wpdb;

    wc_add_order_item_meta($item_id,'item_details',$values['_custom_options']['description']);
    wc_add_order_item_meta($item_id,'customer_image',$values['_custom_options']['another_example_field']);
    wc_add_order_item_meta($item_id,'_hidden_field',$values['_custom_options']['hidden_info']);

}

// 5. If you want to override the price you can use information saved against the product to do so

add_action( 'woocommerce_before_calculate_totals', 'update_custom_price', 1, 1 );
function update_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $cart_item_key => $value ) {       
        // Version 2.x
        //$value['data']->price = $value['_custom_options']['custom_price'];
        // Version 3.x / 4.x
        $value['data']->set_price($value['_custom_options']['custom_price']);
    }
}
 
// All your custom information will appear in the customer email and order from within wordpress providing you added it as meta data  */
?>
<?php
if( !class_exists ( 'WVP_Background_Texonomy' ) ) {

    class WVP_Background_Texonomy {

        function __construct(){

            add_action( 'init', array( $this, 'wvp_background_taxonomies' ), 0 );
         
            add_action( 'admin_enqueue_scripts', array( $this, 'load_media' ) );

            add_action( 'wvp_background_add_form_fields', array( $this, 'wvp_background_fields' ) );

            add_action( 'wvp_background_edit_form_fields', array( $this, 'wvp_background_fields' ) );

            add_action( 'create_wvp_background', array( $this, 'wvp_background_save_data' ) );

            add_action( 'edited_wvp_background', array( $this, 'wvp_background_save_data' ) );

            add_filter( 'manage_edit-wvp_background_columns', array( $this, 'wvp_background_columns' ) );
            
            add_filter( 'manage_wvp_background_custom_column', array( $this, 'wvp_background_custom_fields' ), 10, 3 );

        } 
         
        function wvp_background_taxonomies() {
            register_taxonomy('wvp_background', 'product', array(
                'hierarchical' => false,
                'labels' => array(
                    'name' => _x( 'Backgrounds', 'taxonomy general name' ),
                    'singular_name' => _x( 'Background', 'taxonomy singular name' ),
                    'search_items' =>  __( 'Search Backgrounds' ),
                    'all_items' => __( 'All Backgrounds' ),
                    'parent_item' => __( 'Parent Background' ),
                    'parent_item_colon' => __( 'Parent Background:' ),
                    'edit_item' => __( 'Edit Background' ),
                    'update_item' => __( 'Update Background' ),
                    'add_new_item' => __( 'Add New Background' ),
                    'new_item_name' => __( 'New Background Name' ),
                    'menu_name' => __( 'Backgrounds' ),
                ),
                'rewrite' => array(
                    'slug' => 'wvp_background',
                    'with_front' => false,
                    'hierarchical' => false
                ),
            ));
        }

        public function load_media() {
            wp_enqueue_media();
        }

        function wvp_background_fields($tag) {
            $wvp_bg_image_id = '';
            if( isset($tag->term_id) ) {
                $wvp_bg_image_id = get_term_meta ( $tag->term_id, 'wvp_bg_image_id', true );
            }
            ?>
            <tr class="form-field term-group-wrap">
                <th scope="row">
                    <label for="wvp_bg_image_id"><?php _e( 'Image', WVP_txt_domain ); ?></label>
                </th>
                <td>
                    <input type="hidden" id="wvp_bg_image_id" name="wvp_bg_image_id" value="<?php echo $wvp_bg_image_id; ?>">
                    <div id="wvp_bg_image_wrapper">
                        <?php if ( $wvp_bg_image_id ) { ?>
                            <?php echo wp_get_attachment_image ( $wvp_bg_image_id, 'thumbnail' ); ?>
                        <?php } ?>
                    </div>
                    <p>
                        <input type="button" class="button button-secondary wvp_bg_media_button" id="wvp_bg_media_button" name="wvp_bg_media_button" value="<?php _e( 'Add Image', WVP_txt_domain ); ?>" data-hidden="wvp_bg_image_id" data-image-wrapper="wvp_bg_image_wrapper" />
                        <input type="button" class="button button-secondary wvp_bg_media_remove" id="wvp_bg_media_remove" name="wvp_bg_media_remove" value="<?php _e( 'Remove Image', WVP_txt_domain ); ?>" data-hidden="wvp_bg_image_id" data-image-wrapper="wvp_bg_image_wrapper" />
                    </p>
                </td>
            </tr>
            <?php
        }
         
        function wvp_background_save_data($term_id) {
            if( isset( $_POST['wvp_bg_image_id'] ) && '' !== $_POST['wvp_bg_image_id'] ){
                $image = $_POST['wvp_bg_image_id'];
                update_term_meta ( $term_id, 'wvp_bg_image_id', $image );
            } else {
                update_term_meta ( $term_id, 'wvp_bg_image_id', '' );
            }
        }

        function wvp_background_columns($columns)
        {
            $newcolumns = array();
            foreach($columns as $key => $title) {
                if ($key=='name')
                  $newcolumns['wvp_bg_image_id'] = __( 'Image', WVP_txt_domain );
                $newcolumns[$key] = $title;
            }
            return $newcolumns;
        }

        function wvp_background_custom_fields($deprecated,$column_name,$term_id)
        {
            if ($column_name == 'wvp_bg_image_id') {
                $wvp_bg_image_id = get_term_meta ( $term_id, 'wvp_bg_image_id', true );
                if ( $wvp_bg_image_id ) {
                    echo wp_get_attachment_image ( $wvp_bg_image_id, array( '40', '40' ) );
                } 
            }
        }
    }

    global $wvp_background;
    $wvp_background = new WVP_Background_Texonomy();
}


?>
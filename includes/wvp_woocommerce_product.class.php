<?php
if( !class_exists ( 'WVP_Woocommerce_Product' ) ) {

    class WVP_Woocommerce_Product {

        function __construct(){

            add_action( 'add_meta_boxes', array( $this, 'wvp_add_wc_product_meta_box' ) );

            add_action( 'save_post', array( $this, 'wvp_save_wc_product_metabox' ), 10, 2 );

            add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'wvp_display_variations' ) );

            add_action( 'woocommerce_product_thumbnails', array( $this, 'wvp_replacing_template_loop_product_thumbnail' ) );

            add_filter( 'woocommerce_add_cart_item_data', array( $this, 'wvp_add_cart_item_data' ), 10, 3 );

            add_action( 'woocommerce_before_calculate_totals', array( $this, 'wvp_before_calculate_totals' ), 10, 1 );

            add_filter( 'woocommerce_get_item_data', array( $this, 'wvp_display_sub_item_text_cart' ), 10, 2 );

            add_action( 'woocommerce_add_order_item_meta', array( $this, 'wvp_field_order_meta_handler' ), 1, 3 );
            
            add_action( 'woocommerce_after_shop_loop_item', array( $this, 'wvp_remove_add_to_cart_buttons' ), 1 );
            
            add_filter( 'woocommerce_get_price_html', array( $this, 'wvp_return_custom_price' ), 10, 2 );
 
        } 

        function wvp_add_wc_product_meta_box() {
            add_meta_box( 'wvp_enable_sub_product', __( 'Sub Product', 'WVP_txt_domain'), array( $this, 'wvp_enable_sub_product'), 'product', 'side', 'default' );
        }
        
        function wvp_enable_sub_product($post) {
            
            global $wvp_sub_product, $wvp_part_color, $wvp_sp_version;
            $pid = $post->ID;
            $is_enable = get_post_meta( $pid, 'wvp_subprod_isenable', true );
            $selected = ( !empty($is_enable) && $is_enable == 1 ) ? ' checked="checked" ' : '';

            $wvp_background = get_post_meta( $pid, 'wvp_background', true );
            $wvp_sp_versions = get_post_meta( $pid, 'wvp_sp_version', true );
            $wvp_items = get_post_meta( $pid, 'wvp_items', true );
            $wvp_color = get_post_meta( $pid, 'wvp_color', true );

            wp_nonce_field( plugin_basename( __FILE__ ), 'wvp_enable_sub_product' );
            $background_data = wp_get_post_terms($pid, 'wvp_background');
            $subproduct_data = $wvp_sub_product->get_sub_products_using_wc_pid( $pid );
            ?>

            <label><b><?php _e('Sub Products', WVP_txt_domain); ?></b></label>
            <div class="select_page_checkbox">
                <input type="checkbox" name="wvp_subprod_isenable" id="wvp_subprod_isenable" value="1" <?php echo $selected; ?>>
                <label for="wvp_subprod_isenable"> <?php _e('Enable', WVP_txt_domain); ?> </label><br/>
            </div>

            <div class="wvp_options">
                <label><b><?php _e( 'Select Default Background', WVP_txt_domain); ?></b></label>
                <div class="wvp_option">
                    <?php if( !empty( $background_data ) ) { ?>
                        <?php foreach ($background_data as $background) { ?>
                            <?php $bg_selected = ($wvp_background == $background->term_id) ? 'checked="checked"' : ''; ?>
                            <?php $wvp_bg_image_id = get_term_meta ( $background->term_id, 'wvp_bg_image_id', true ); ?>
                            <?php if ( $wvp_bg_image_id ) { ?>
                                <?php $bg_image_url = wp_get_attachment_image_src ( $wvp_bg_image_id, 'thumbnail' ); ?>
                                <?php if( isset($bg_image_url[0]) && !empty($bg_image_url[0]) ) { ?>
                                    <label for="<?php echo $background->slug; ?>">
                                        <input type="radio" 
                                            id="<?php echo $background->slug; ?>" 
                                            name="wvp_background" 
                                            data-item="<?php echo $bg_image_url[0]; ?>" 
                                            class="wvp_background" 
                                            value="<?php echo $background->term_id; ?>"
                                            <?php echo $bg_selected; ?> /> 
                                        <?php echo $background->name; ?>
                                    </label>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>

                <label><b><?php _e( 'Select Default Sub Products', WVP_txt_domain); ?></b></label>
                <div class="option">
                    <?php if( !empty($subproduct_data) ) { ?>
                        <?php foreach ($subproduct_data as $subproduct) { ?>
                            <?php $sp_selected = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'checked="checked"' : ''; ?>
                            <label for="<?php echo $subproduct->post_name; ?>">
                                <input type="checkbox" 
                                    id="<?php echo $subproduct->post_name; ?>" 
                                    name="wvp_items[]" 
                                    data-item="wvp_sub_pid_<?php echo $subproduct->ID; ?>" 
                                    class="item" 
                                    value="<?php echo $subproduct->ID; ?>"
                                    <?php echo $sp_selected; ?> /> 
                                <?php echo $subproduct->post_title; ?>
                            </label>
                        <?php } ?>
                    <?php } ?>
                </div>

                <label><b><?php _e( 'Select Default Sub Products Version', WVP_txt_domain); ?></b></label>
                <?php if( !empty($subproduct_data) ) { ?>
                    <?php foreach ($subproduct_data as $subproduct) { ?>
                        <?php $sp_versions = $wvp_sp_version->get_sp_version_using_sub_product_id($subproduct->ID); ?>
                        <?php if( !empty( $sp_versions ) ) { ?>    
                            <?php $sp_display = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'style="display:block;"' : ''; ?>
                            <div class="option item-option <?php echo 'wvp_sub_pid_'.$subproduct->ID; ?>" <?php echo $sp_display; ?>>
                                <b><?php echo $subproduct->post_title; ?></b><br/>
                                <?php foreach ($sp_versions as $version) { ?>
                                    <?php $spv_selected = ( !empty($wvp_sp_versions) && in_array($version->ID, $wvp_sp_versions) ) ? 'checked="checked"' : ''; ?>
                                    <label for="wvp_sp_version_<?php echo $version->ID; ?>">
                                        <input type="radio" 
                                            id="wvp_sp_version_<?php echo $version->ID; ?>" 
                                            name="wvp_sp_version[<?php echo $subproduct->ID; ?>]" 
                                            data-sub_pid="<?php echo $subproduct->ID; ?>"
                                            data-label="<?php echo $version->post_title; ?>"
                                            class="sub_item" 
                                            value="<?php echo $version->ID; ?>"
                                            <?php echo $spv_selected; ?>/> 
                                            <?php echo $version->post_title; ?>
                                    </label>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <br/>
                <label><b><?php _e( 'Select Default Item Parts', WVP_txt_domain); ?></b></label>
                <?php if( !empty($subproduct_data) ) { ?>
                    <?php foreach ($subproduct_data as $subproduct) { ?>
                        <?php $items = wp_get_post_terms($subproduct->ID, 'wvp_item_part'); ?>
                        <?php if( !empty( $items ) ) { ?>
                            <?php foreach ($items as $item) { ?>
                                <?php $sp_display = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'style="display:block;"' : ''; ?>
                                <div class="option item-option <?php echo 'wvp_sub_pid_'.$subproduct->ID; ?>" <?php echo $sp_display; ?>>
                                    <b><?php echo $item->name; ?></b><br/>
                                    <?php $part_colors = $wvp_part_color->get_part_color_using_item_part_id( $item->term_id, $pid ); ?>
                                    <?php if( !empty($part_colors) ) { ?>
                                        <?php foreach ($part_colors as $color) { ?>
                                            <?php $sp_version = get_post_meta( $color->ID, 'wvp_sp_version_id', true ); ?>
                                            <?php $ic_selected = ( !empty($wvp_color) && in_array($color->ID, $wvp_color) ) ? 'checked="checked"' : ''; ?>
                                            <?php $color_display = ( !empty($wvp_sp_versions) && in_array($sp_version, $wvp_sp_versions) ) ? 'style="display:inline-block;"' : 'style="display:inline-block;"'; ?>
                                            <label class="wvp_sub_item" for="wvp_color_<?php echo $color->ID; ?>" <?php echo $color_display; ?> >
                                                <input type="radio" 
                                                    id="wvp_color_<?php echo $color->ID; ?>" 
                                                    name="wvp_color[<?php echo $item->term_id; ?>]" 
                                                    data-item="<?php echo $item->term_id; ?>" 
                                                    data-sub_pid="<?php echo $subproduct->ID; ?>"
                                                    data-label="<?php echo $color->post_title; ?>"
                                                    class="item_component" 
                                                    value="<?php echo $color->ID; ?>"
                                                    <?php echo $ic_selected; ?>/> 
                                                    <?php echo $color->post_title; ?>
                                            </label>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php
        }

        function wvp_save_wc_product_metabox( $post_id, $post_object ) {
            if( !isset( $post_object->post_type ) || 'product' != $post_object->post_type )
                return;
        
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
                return;
        
            if ( !isset( $_POST['wvp_enable_sub_product'] ) || !wp_verify_nonce( $_POST['wvp_enable_sub_product'], plugin_basename( __FILE__ ) ) )
                return;
        
            if ( isset( $_POST['wvp_subprod_isenable'] ) ) {
                update_post_meta( $post_id, 'wvp_subprod_isenable', $_POST['wvp_subprod_isenable'] );
            } else {
                update_post_meta( $post_id, 'wvp_subprod_isenable', '' );
            }

            if ( isset( $_POST['wvp_background'] ) ) {
                update_post_meta( $post_id, 'wvp_background', $_POST['wvp_background'] );
            } else {
                update_post_meta( $post_id, 'wvp_background', '' );
            }

            if ( isset( $_POST['wvp_items'] ) ) {
                update_post_meta( $post_id, 'wvp_items', $_POST['wvp_items'] );
            } else {
                update_post_meta( $post_id, 'wvp_items', '' );
            }

            if ( isset( $_POST['wvp_sp_version'] ) ) {
                update_post_meta( $post_id, 'wvp_sp_version', $_POST['wvp_sp_version'] );
            } else {
                update_post_meta( $post_id, 'wvp_sp_version', '' );
            }

            if ( isset( $_POST['wvp_color'] ) ) {
                update_post_meta( $post_id, 'wvp_color', $_POST['wvp_color'] );
            } else {
                update_post_meta( $post_id, 'wvp_color', '' );
            }
        }

        function get_wc_product() {
            return get_posts( 
                array(
                    'numberposts' => '-1',
                    'post_type' => 'product',
                    'meta_query' => array(
                        array(
                            'key'     => 'wvp_subprod_isenable',
                            'value'   => '1',
                            'compare' => '=',
                        )
                    )
                ) 
            );
        }

        function wvp_display_variations() {
            global $wvp_sub_product, $wvp_sp_version, $wvp_part_color;
            $pid = get_the_ID();
            // if sub product is not checked than return
            if( !get_post_meta( $pid, 'wvp_subprod_isenable', true ) ) { return; }

            $wvp_background = get_post_meta( $pid, 'wvp_background', true );
            $wvp_sp_versions = get_post_meta( $pid, 'wvp_sp_version', true );
            $wvp_items = get_post_meta( $pid, 'wvp_items', true );
            $wvp_color = get_post_meta( $pid, 'wvp_color', true );

            $background_data = wp_get_post_terms($pid, 'wvp_background');
            $subproduct_data = $wvp_sub_product->get_sub_products_using_wc_pid( $pid );
            $subproduct_ids = array();

            $product = wc_get_product( $pid );
            $regular_price = $product->get_regular_price();
            $sale_price = $product->get_sale_price();
            $price = $product->get_price();
            ?>
            <input type="hidden" name="regular_price" id="regular_price" value="<?php echo $regular_price; ?>" />
            <input type="hidden" name="sale_price" id="sale_price" value="<?php echo $sale_price; ?>" />
            <input type="hidden" name="price" id="price" value="<?php echo $price; ?>" />
            <input type="hidden" name="wvp_post" id="wvp_post" value="<?php echo $price; ?>" />
            <div class="wvp_options">
                <h5><?php _e( 'Background', WVP_txt_domain); ?></h5>
                <div class="wvp_option">
                    <?php if( !empty( $background_data ) ) { ?>
                        <?php foreach ($background_data as $background) { ?>
                            <?php $bg_selected = ($wvp_background == $background->term_id) ? 'checked="checked"' : ''; ?>
                            <?php $wvp_bg_image_id = get_term_meta ( $background->term_id, 'wvp_bg_image_id', true ); ?>
                            <?php if ( $wvp_bg_image_id ) { ?>
                                <?php $bg_image_url = wp_get_attachment_image_src ( $wvp_bg_image_id, 'single-post-thumbnail' ); ?>
                                <?php if( isset($bg_image_url[0]) && !empty($bg_image_url[0]) ) { ?>
                                    <label for="<?php echo $background->slug; ?>">
                                        <input type="radio" 
                                            id="<?php echo $background->slug; ?>" 
                                            name="wvp_background" 
                                            data-item="<?php echo $bg_image_url[0]; ?>" 
                                            class="wvp_background" 
                                            value="<?php echo $background->name; ?>"
                                            <?php echo $bg_selected; ?>/> 
                                        <?php echo $background->name; ?>
                                    </label>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>

                <h5><?php _e( 'Items', WVP_txt_domain); ?></h5>
                <div class="option">
                    <?php if( !empty($subproduct_data) ) { ?>
                        <?php foreach ($subproduct_data as $subproduct) { ?>
                            <?php $sp_selected = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'checked="checked"' : ''; ?>
                            <?php $subproduct_ids[] = $subproduct->ID; ?>
                            <label for="<?php echo $subproduct->post_name; ?>">
                                <input type="checkbox" 
                                    id="<?php echo $subproduct->post_name; ?>" 
                                    name="wvp_item[]" 
                                    data-item="<?php echo $subproduct->ID; ?>" 
                                    class="item" 
                                    value="<?php echo $subproduct->ID; ?>"
                                    <?php echo $sp_selected; ?>/> 
                                <?php echo $subproduct->post_title; ?>
                            </label>
                        <?php } ?>
                    <?php } ?>
                </div>     


                <h5><?php _e( 'Sub Items', WVP_txt_domain); ?></h5>
                <div class="option sub_items_options">
                    <?php if( !empty($subproduct_data) ) { ?>
                        <?php foreach ($subproduct_data as $subproduct) { ?>
                            <?php $sp_desc = ''; ?>
                            <?php $sp_versions = $wvp_sp_version->get_sp_version_using_sub_product_id($subproduct->ID, $pid); ?>
                            <?php if( !empty($sp_versions) ) { ?>
                                <?php $sp_display = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'style="display:block;"' : ''; ?>
                                <div class="option item-option <?php echo 'wvp_sub_pid_'.$subproduct->ID; ?>" <?php echo $sp_display; ?>>
                                    <?php $wvp_si_prc = 0; ?>
                                    <?php foreach($sp_versions as $sp_version) { ?>

                                        <?php if (has_post_thumbnail($sp_version->ID) ) { ?>
                                            <?php $thumb_img = wp_get_attachment_image_src( get_post_thumbnail_id($sp_version->ID), 'single-post-thumbnail' ); ?>
                                            <?php if( isset($thumb_img[0]) && !empty($thumb_img[0]) ) { ?>

                                                <?php $wvp_desc = get_post_meta( $sp_version->ID, 'wvp_desc', true ); ?>
                                                <?php $sp_desc.= (!empty($wvp_desc) ? '<p class="wvp_spv_desc_'.$sp_version->ID.'">'.$wvp_desc.'</p>' : ''); ?>
                                                <?php $wvp_price = get_post_meta( $sp_version->ID, 'wvp_price', true ); ?>
                                                <?php $spv_selected = ( !empty($wvp_sp_versions) && in_array($sp_version->ID, $wvp_sp_versions) ) ? 'checked="checked"' : ''; ?>
                                                <?php if(!empty($wvp_sp_versions) && in_array($sp_version->ID, $wvp_sp_versions)) { $wvp_si_prc = $wvp_price; } ?>
                                                <label for="<?php echo $sp_version->post_name; ?>" style="background-image: url('<?php echo $thumb_img[0]; ?>');">
                                                    <input type="radio" 
                                                        id="<?php echo $sp_version->post_name; ?>" 
                                                        name="wvp_sub_item[<?php echo $subproduct->ID; ?>]" 
                                                        data-price="<?php echo $wvp_price; ?>"
                                                        data-item="<?php echo $sp_version->ID; ?>" 
                                                        data-sub_pid="<?php echo $subproduct->ID; ?>"
                                                        class="sub_item" 
                                                        value="<?php echo $sp_version->ID; ?>"
                                                        <?php echo $spv_selected; ?> /> 
                                                    <?php //echo $sp_version->post_title; ?>
                                                </label>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>

                                <?php if( !empty($sp_desc) ) { ?>
                                    <div class="wvp_option_desc wvp_sub_pid_desc_<?php echo $subproduct->ID; ?>">
                                        <h6><?php _e( 'Description', WVP_txt_domain); ?></h6>
                                        <?php echo $sp_desc; ?>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>    

                

                <h5><?php _e( 'Item Parts', WVP_txt_domain); ?></h5>
                <?php if( !empty($subproduct_data) ) { ?>
                    <?php foreach ($subproduct_data as $subproduct) { ?>
                        <?php $items = wp_get_post_terms($subproduct->ID, 'wvp_item_part'); ?>
                        <?php if( !empty( $items ) ) { ?>
                            <?php foreach ($items as $item) { ?>
                                <?php $sp_display = ( !empty($wvp_items) && in_array($subproduct->ID, $wvp_items) ) ? 'style="display:block;"' : ''; ?>
                                <div class="option item-option item-option-color <?php echo 'wvp_sub_pid_'.$subproduct->ID; ?>" <?php echo $sp_display; ?>>
                                    <b><?php echo $item->name; ?></b>
                                    <?php $part_colors = $wvp_part_color->get_part_color_using_item_part_id( $item->term_id, $pid ); ?>
                                    <?php if( !empty($part_colors) ) { ?>
                                        <?php foreach ($part_colors as $color) { ?>
                                            <?php $wvp_image_id = get_post_meta( $color->ID, 'wvp_rb_image', true ); ?>

                                            <?php if ( $wvp_image_id ) { ?>
                                                <?php $rb_image_url = wp_get_attachment_image_src ( $wvp_image_id, 'thumbnail' ); ?>
                                                <?php if( isset($rb_image_url[0]) && !empty($rb_image_url[0]) ) { ?>

                                                    <?php $wvp_spv_image = get_post_meta( $color->ID, 'wvp_spv_image', true ); ?>
                                                    <?php if( !empty($wvp_spv_image) ) { ?>
                                                        <?php foreach($wvp_spv_image as $sp_version => $attachement_id) { ?>

                                                            <?php $wvp_image_url = wp_get_attachment_image_src($attachement_id, 'single-post-thumbnail'); ?>
                                                            <?php if( isset($wvp_image_url[0]) && !empty($wvp_image_url[0]) ) { ?>

                                                                <?php $ic_active = ( !empty($wvp_color) && in_array($color->ID, $wvp_color) ) ? ' wvp_active' : ''; ?>
                                                                <?php $ic_selected = ( !empty($wvp_color) && in_array($color->ID, $wvp_color) ) ? 'checked="checked"' : ''; ?>


                                                                <?php $color_display = ( !empty($wvp_sp_versions) && in_array($sp_version, $wvp_sp_versions) ) ? 'display:inline-block;' : ''; ?>
                                                                <?php $class_ic = 'wvp_spv_'.$sp_version.' wvp_item_color_'.$item->term_id; ?>
                                                                <label class="lbl_item_componenet <?php echo $class_ic; ?> <?php echo $ic_active; ?>"
                                                                    for="wvp_color_<?php echo $color->ID; ?>"
                                                                    style="background-image: url('<?php echo $rb_image_url[0]; ?>'); <?php echo $color_display; ?>" >
                                                                        <input type="radio" 
                                                                            id="wvp_color_<?php echo $color->ID; ?>" 
                                                                            name="wvp_color[<?php echo $subproduct->ID; ?>][<?php echo $sp_version; ?>][<?php echo $item->term_id; ?>]" 
                                                                            data-item="<?php echo $item->term_id; ?>" 
                                                                            data-spv_id="<?php echo $sp_version; ?>"
                                                                            data-sub_pid="<?php echo $subproduct->ID; ?>"
                                                                            data-label="<?php echo $color->post_title; ?>"
                                                                            class="item_component" 
                                                                            value="<?php echo $color->post_title; ?>"
                                                                            <?php echo $ic_selected; ?> />
                                                                </label>

                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } ?>



                                                    <?php /* $ic_active = ( !empty($wvp_color) && in_array($color->ID, $wvp_color) ) ? ' wvp_active' : ''; ?>
                                                    <?php $ic_selected = ( !empty($wvp_color) && in_array($color->ID, $wvp_color) ) ? 'checked="checked"' : ''; ?>
                                            
                                                    <?php $sp_version = get_post_meta( $color->ID, 'wvp_sp_version_id', true ); ?>
                                                    <?php $color_display = ( !empty($wvp_sp_versions) && in_array($sp_version, $wvp_sp_versions) ) ? 'display:inline-block;' : ''; ?>

                                                    <label class="lbl_item_componenet wvp_item_color_<?php echo $item->term_id; ?> <?php echo $ic_active; ?>"
                                                        for="wvp_color_<?php echo $color->ID; ?>"
                                                        style="background-image: url('<?php echo $rb_image_url[0]; ?>'); <?php // echo $color_display; ?>" >
                                                            <input type="radio" 
                                                                id="wvp_color_<?php echo $color->ID; ?>" 
                                                                name="wvp_color[<?php echo $subproduct->ID; ?>][<?php echo $sp_version; ?>][<?php echo $item->term_id; ?>]" 
                                                                data-item="<?php echo $item->term_id; ?>" 
                                                                data-spv_id="<?php echo $sp_version; ?>"
                                                                data-sub_pid="<?php echo $subproduct->ID; ?>"
                                                                data-label="<?php echo $color->post_title; ?>"
                                                                class="item_component" 
                                                                value="<?php echo $color->post_title; ?>"
                                                                <?php echo $ic_selected; ?>/>
                                                    </label>
                                                    */ ?>

                                                <?php } ?>
                                            <?php } ?>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </div>
            <?php
        }

        function wvp_replacing_template_loop_product_thumbnail() {
            global $wvp_part_color;
            $pid = get_the_ID();
            $wvp_color = get_post_meta( $pid, 'wvp_color', true );
            $wvp_sp_version = get_post_meta( $pid, 'wvp_sp_version', true );
            // if sub product is not checked than return
            if( !get_post_meta( $pid, 'wvp_subprod_isenable', true ) ) { return; }
            
            // echo "<style>.woocommerce-product-gallery .woocommerce-product-gallery__image--placeholder { display:none; }</style>";
            $part_colors = $wvp_part_color->get_part_color_using_wc_product_id( $pid );
            if( !empty($part_colors) ) {
        		echo '<div class="wvp_image">';
                    foreach($part_colors as $color) {
                        $color_id = $color->ID;
                        $wvp_spv_image = get_post_meta( $color->ID, 'wvp_spv_image', true );

                        if( !empty($wvp_spv_image) ) {
                            foreach($wvp_spv_image as $spv_id => $attachement_id) {
                                $wvp_image_url = wp_get_attachment_image_src($attachement_id, 'single-post-thumbnail');

                                if( isset($wvp_image_url[0]) && !empty($wvp_image_url[0]) ) { 
                                    $sub_product_id = get_post_meta( $color_id, 'wvp_sub_product_id', true );
                                    $item_part_id = get_post_meta( $color_id, 'wvp_item_part_id', true );
                                    $item_part = get_term_by( 'ID', $item_part_id, 'wvp_item_part' );
                                    $ic_display = ( !empty($wvp_color) && in_array($color_id, $wvp_color) && !empty($wvp_sp_version) && in_array($spv_id, $wvp_sp_version) ) ? 'style="display:block";' : '';

                                    $class = 'wvp_color_'.$color_id;
                                    $class.= ' wvp_item_'.$item_part->term_id;
                                    $class.= ' wvp_sp_version_'.$spv_id;
                                    $class.= ' wvp_sub_pid_'.$sub_product_id;
                                    echo '<img src="'.$wvp_image_url[0].'" class="'.$class.'" '.$ic_display.'/>';
                                }
                            }
                        }

                        /* if (has_post_thumbnail($color_id) ) {
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id($color_id), 'single-post-thumbnail' );
                            if( isset($image[0]) && !empty($image[0]) ) {
                                $sub_product_id = get_post_meta( $color_id, 'wvp_sub_product_id', true );
                                $item_part_id = get_post_meta( $color_id, 'wvp_item_part_id', true );
                                $item_part = get_term_by( 'ID', $item_part_id, 'wvp_item_part' );
                                $ic_display = ( !empty($wvp_color) && in_array($color_id, $wvp_color) ) ? 'style="display:block";' : '';

                                $class = 'wvp_color_'.$color_id;
                                $class.= ' wvp_item_'.$item_part->term_id;
                                $class.= ' wvp_sub_pid_'.$sub_product_id;
                                echo '<img src="'.$image[0].'" class="'.$class.'" '.$ic_display.'/>';
                            }
                        } */
                    }
        		echo '</div>'; 
            }
        }

        function wvp_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

            if( isset($_POST['wvp_item']) && !empty($_POST['wvp_item']) ) {
                $cart_item_data['wvp_item'] = $_POST['wvp_item'];

                $product = wc_get_product( $product_id );
                $wvp_new_price = $product->get_price();
                $sub_items = array();

                foreach( $_POST['wvp_item'] as $wvp_item_id ) {
                    $item_name = get_the_title( $wvp_item_id );

                    $wvp_sub_item_id = '';
                    if( isset($_POST['wvp_sub_item']) && !empty($_POST['wvp_sub_item']) ) {
                        $cart_item_data['wvp_sub_item'] = $_POST['wvp_sub_item'];

                        $wvp_sub_item = $_POST['wvp_sub_item'];
                        if( isset($wvp_sub_item[$wvp_item_id]) && !empty($wvp_sub_item[$wvp_item_id]) ) {

                            $wvp_sub_item_id = $wvp_sub_item[$wvp_item_id];
                            $sub_item_name = get_the_title( $wvp_sub_item_id );
                            $sub_items[$item_name] = $sub_item_name;

                            $wvp_price = get_post_meta( $wvp_sub_item_id, 'wvp_price', true );
                            $wvp_new_price = $wvp_new_price + $wvp_price;
                        }
                    }

                    if( isset($_POST['wvp_color']) && !empty($_POST['wvp_color']) ) {
                        $cart_item_data['wvp_color'] = $_POST['wvp_color'];

                        $wvp_color = $_POST['wvp_color'];
                        if( isset($wvp_color[$wvp_item_id][$wvp_sub_item_id]) && !empty($wvp_color[$wvp_item_id][$wvp_sub_item_id]) ) {
                            $wvp_item_colors = $wvp_color[$wvp_item_id][$wvp_sub_item_id];

                            foreach( $wvp_item_colors as $item_partid => $color ) {
                                $item_part = get_term_by( 'ID', $item_partid, 'wvp_item_part' );
                                $item_part_name = $item_part->name;
                                $sub_items[$item_part_name] = $color;
                            }
                        }
                    }
                }

                $cart_item_data['wvp_new_price'] = $wvp_new_price;
                $cart_item_data['sub_items'] = $sub_items;
            }

            return $cart_item_data;
        }

        function wvp_before_calculate_totals( $cart_obj ) {
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
            }

            foreach( $cart_obj->get_cart() as $key=>$value ) {
                if( isset( $value['wvp_new_price'] ) ) {
                    $price = $value['wvp_new_price'];
                    $value['data']->set_price( ( $price ) );
                }
            }
        }

        function wvp_display_sub_item_text_cart( $item_data, $cart_item ) {
            if ( empty( $cart_item['sub_items'] ) ) {
                return $item_data;
            }

            foreach ( $cart_item['sub_items'] as $sub_item_name => $color ) {
                $item_data[] = array(
                    'key'     => $sub_item_name,
                    'value'   => wc_clean( $color ),
                    'display' => '',
                );
            }
            return $item_data;
        }

        function wvp_field_order_meta_handler( $item_id, $item_data, $cart_item_key ) {
            if( isset($item_data['sub_items']) && !empty($item_data['sub_items']) ) {
                foreach ($item_data['sub_items'] as $item_key => $item_value) {
                    wc_add_order_item_meta( $item_id, $item_key, $item_value );
                }
            }
        }

        function wvp_remove_add_to_cart_buttons() {
            if( is_product_category() || is_shop()) { 
                remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart' );
            }
        }

        function wvp_return_custom_price($price, $product) {
            global $wpdb, $post, $wvp_sub_product, $wvp_sp_version;
            if(is_shop() && !is_admin()) {
                $post_id = $post->ID;
                /*$min_price = $max_price = $price;*/
                $product = wc_get_product( $post_id );
                $min_price = $max_price = $product->get_price();
                $is_subproduct = get_post_meta($post_id, 'wvp_subprod_isenable',true);
                if($is_subproduct) {
                    $sub_products = $wvp_sub_product->get_sub_products_using_wc_pid($post_id);
                    $wvp_items = get_post_meta( $post_id, 'wvp_items', true );
                    $wvp_sp_versions = get_post_meta( $post_id, 'wvp_sp_version', true );
                    if( !empty($sub_products) ) {
                        foreach($sub_products as $sub_product) {
                            $sp_id = $sub_product->ID;
                            $sp_versions = $wvp_sp_version->get_sp_version_using_sub_product_id( $sp_id, $post_id );
                            // $price_arr = array();
                            $price_arr = '';
                            if( !empty($sp_versions) ) {
                                foreach( $sp_versions as $sp_version ) {
                                    // $price_arr[] = get_post_meta($sp_version->ID, 'wvp_price',true);
                                    if( !empty($wvp_sp_versions) && in_array($sp_version->ID, $wvp_sp_versions) ) {
                                        $price_arr = get_post_meta($sp_version->ID, 'wvp_price',true);
                                    }
                                }
                            }
                            $max_price = $max_price + $price_arr;
                            // $max_price = $max_price + max( $price_arr );
                        }
                        // $price = '₪'.$min_price . ' - ' . '₪'.$max_price;
                        $price = '₪'.$max_price;
                    }
                }
            }
            return $price;
        }

    }

    global $wvp_wc_product;
    $wvp_wc_product = new WVP_Woocommerce_Product();
}






?>
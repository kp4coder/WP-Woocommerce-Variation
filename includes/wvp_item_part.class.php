<?php
if( !class_exists ( 'WVP_Item_Part_Texonomy' ) ) {

    class WVP_Item_Part_Texonomy {

        function __construct(){

            add_action( 'init', array( $this, 'wvp_item_part_taxonomies' ), 0 );

        } 
         
        function wvp_item_part_taxonomies() {
            register_taxonomy('wvp_item_part', 'wvp_sub_product', array(
                'hierarchical' => false,
                'labels' => array(
                    'name' => _x( 'Item Parts', 'taxonomy general name' ),
                    'singular_name' => _x( 'Item Part', 'taxonomy singular name' ),
                    'search_items' =>  __( 'Search Item Parts' ),
                    'all_items' => __( 'All Item Parts' ),
                    'parent_item' => __( 'Parent Item Part' ),
                    'parent_item_colon' => __( 'Parent Item Part:' ),
                    'edit_item' => __( 'Edit Item Part' ),
                    'update_item' => __( 'Update Item Part' ),
                    'add_new_item' => __( 'Add New Item Part' ),
                    'new_item_name' => __( 'New Item Part Name' ),
                    'menu_name' => __( 'Item Parts' ),
                ),
                'rewrite' => array(
                    'slug' => 'wvp_item_part',
                    'with_front' => false,
                    'hierarchical' => false
                ),
            ));
        }

    }

    global $wvp_item_part;
    $wvp_item_part = new WVP_Item_Part_Texonomy();
}


?>
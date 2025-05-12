<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

class WPSP_CPT {
    public function __construct() {
        add_action( 'init', array( $this, 'register_cpt' ) );
    }

    public function register_cpt() {
        $labels = array(
            'name'                  => _x( 'Popup Campaigns', 'Post Type General Name', 'wp-smart-popup' ),
            'singular_name'         => _x( 'Popup Campaign', 'Post Type Singular Name', 'wp-smart-popup' ),
            'menu_name'             => __( 'Popups', 'wp-smart-popup' ),
            'name_admin_bar'        => __( 'Popup Campaign', 'wp-smart-popup' ),
            'add_new'               => __( 'Add New', 'wp-smart-popup' ),
            'add_new_item'          => __( 'Add New Campaign', 'wp-smart-popup' ),
            'edit_item'             => __( 'Edit Campaign', 'wp-smart-popup' ),
            'new_item'              => __( 'New Campaign', 'wp-smart-popup' ),
            'view_item'             => __( 'View Campaign', 'wp-smart-popup' ),
            'all_items'             => __( 'All Campaigns', 'wp-smart-popup' ),
            'search_items'          => __( 'Search Campaigns', 'wp-smart-popup' ),
            'not_found'             => __( 'No campaigns found.', 'wp-smart-popup' ),
            'not_found_in_trash'    => __( 'No campaigns found in Trash.', 'wp-smart-popup' ),
        );
        $args = array(
            'label'                 => __( 'Popup Campaign', 'wp-smart-popup' ),
            'description'           => __( 'Popup Campaigns for your website', 'wp-smart-popup' ),
            'labels'                => $labels,
            'supports'              => array( 'title' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-slides',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
        );
        register_post_type( 'popup_campaign', $args );
    }
} 
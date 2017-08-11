<?php

class CM_Admin_Settings_Notifications extends CC_Admin_Setting {

    public static function init() {
        $page = 'cart66_members_notification_settings';
        $option_group = 'cart66_members_notifications';
        $setting = new CM_Admin_Settings_Notifications( $page, $option_group );
        return $setting;
    }

    /**
     * admin_init hooked in by parent class constructor
     */
    public function register_settings() {
        $this->register_notification_section();
        $this->register();
    }

    public function register_notification_section() {
        // Set the name for the options in this section and load any stored values
        $defaults = array(
            'member_home' => '',
            'not_included' => '',
            'post_types' => array(),
            'sign_in_required' => '',
            'post_filter' => 'remove'
        );
        $option_values = self::get_options( $this->option_name, $defaults );

        // Create the section for the cart66_main_settings section
        $title = __( 'Access Notifications', 'cart66-members' );
        $section = new CC_Admin_Settings_Section( $title, $this->option_name );

        // Add member home select box
        $home_title = __( 'Member Home Page', 'cart66-members');
        $home = new CC_Admin_Settings_Select_Box( $home_title, 'member_home' );
        $home->new_option( __( 'Secure Order History', 'cart66' ), 'order_history', false );
        $home->description = __( 'The page where members will be directed after logging in', 'cart66-members' );
        $this->build_member_homepage_list( $home, $option_values['member_home'] );
        $section->add_field( $home );

        // Add post type check boxes
        $post_types_title = __( 'Post Types', 'cart66-members' );
        $post_types = new CC_Admin_Settings_Checkboxes( $post_types_title, 'post_types' );
        $post_types->description = __( 'Enable membership restrictions for the selected post types', 'cart66-members' );
        $public_post_types = get_post_types( array( 'public' => true ) );
        foreach ( $public_post_types as $pt ) {
            $post_types->new_option( $pt, $pt, false );
        }
        $post_types->set_selected( $option_values['post_types'] );
        $section->add_field( $post_types );

        // Add setting to determine how posts are filtered
        $post_filter = new CC_Admin_Settings_Select_Box( __( 'Post Filter', 'cart66' ), 'post_filter' );
        $post_filter->new_option( __( 'Remove unauthorized posts', 'cart66' ), 'remove', false );
        $post_filter->new_option( __( 'Show unauthorized content notice', 'cart66' ), 'show_notice', false );
        $post_filter->set_selected( $option_values[ 'post_filter' ] );
        $section->add_field( $post_filter );

        // Add sign in required editor
        $sign_in_title = __( 'Sign In Required', 'cart66-members' );
        $sign_in = new CC_Admin_Settings_Editor( $sign_in_title, 'sign_in_required', $option_values['sign_in_required'] );
        $sign_in->description = __( 'Text displayed when a user must sign in to access the content', 'cart66-members' );
        $section->add_field( $sign_in );


        // Add not included editor
        $not_included_title = __( 'Not Included', 'cart66-members' );
        $not_included = new CC_Admin_Settings_Editor( $not_included_title, 'not_included', $option_values['not_included'] );
        $not_included->description = __( 'Text displayed when the content being accessed is not included in the member\'s subscription', 'cart66-members' );
        $section->add_field( $not_included );

        // Add the settings sections for the page and register the settings
        $this->add_section( $section );

    }

    /**
     * Add all of the published pages to the select box
     *
     * The selected option is the one where the page ID matches the given $value
     *
     * @param CC_Admin_Settings_Select_Box $home
     * @param string The page ID of the selected value
     */
    public function build_member_homepage_list( $home, $value ) {
        
        foreach($this->get_page_list() as $page) {
            $selected = ($value == $page->ID);
            $title = str_repeat('&ndash; ', count($page->ancestors)) . $page->post_title;
            $home->new_option( $title, $page->ID, $selected );
        }

    }

    public function get_page_list() {
        $args = array(
            'sort_order' => 'ASC',
            'sort_column' => 'post_title',
            'hierarchical' => 1,
            'exclude' => '',
            'include' => '',
            'meta_key' => '',
            'meta_value' => '',
            'authors' => '',
            'child_of' => 0,
            'parent' => -1,
            'exclude_tree' => '',
            'number' => '',
            'offset' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ); 

        return get_pages($args); 
    }


}

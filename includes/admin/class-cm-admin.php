<?php

class CM_Admin {

    public static $instance;

    public static $tabs;

    public static function init() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new CM_Admin();
        }

        return self::$instance;
    }

    protected function __construct() {
        self::$tabs = array( 'notifications', 'category-restrictions' );
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

        add_action( 'add_meta_boxes', array('CM_Admin_Memberships_MetaBox', 'add_memberships_box'), 20);
        add_action( 'save_post',      array('CM_Admin_Memberships_MetaBox', 'save_membership_requirements'), 20);
        
        CM_Admin_Settings_Notifications::init();
        CM_Admin_Settings_Restrictions::init();
    }

    public function add_menu_page() {
        // Admin page for secure console
        $parent_slug = 'cart66';
        $page_title = __( 'Cart66 Cloud Members', 'cart66-members' );
        $menu_title = __( 'Members', 'cart66-members' );
        $capability = 'manage_options';
        $menu_slug = 'cart66_members';
        $options_page = add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, array($this, 'render') );
        // CC_Log::write( "Add submenu page value: $options_page" );
    }

    public function render() {
        $active_class = array();
        foreach( self::$tabs as $tab ) {
            $active_class[ $tab ] = '';
        }

        $active_tab = $this->get_active_tab();
        $active_class[ $active_tab ] = 'nav-tab-active';

        $data = array (
            'active_class' => $active_class,
            'active_tab'   => $active_tab
        );

        echo CC_View::get( CM_PATH . 'views/admin/html-settings.php', $data );
    }

    public static function get_active_tab() {
        $default_tab = self::$tabs[0]; // The first tab is the deafault tab
        $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default_tab;
        $tab = in_array( $tab, self::$tabs ) ? $tab : $default_tab;
        return $tab;
    }

}

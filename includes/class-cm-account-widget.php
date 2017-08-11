<?php

class CM_Account_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'CM_Account_Widget',
            'Cart66 Cloud Accounts',
            array(
                'classname' => 'CM_Account_Widget',
                'description' => __( 'Customer account widget', 'cart66-members' )
            )
        );

        // Add actions for ajax rendering for cart widget
        add_action('wp_ajax_render_cart66_account_widget', array('CM_Account_Widget', 'ajax_render_content'));
        add_action('wp_ajax_nopriv_render_cart66_account_widget', array('CM_Account_Widget', 'ajax_render_content'));
    }

    /**
     * The form in the WordPress admin for configuring the widget
     */
    public function form ( $instance ) {
        if ( ! class_exists('Cart66_Cloud') ) { return; }

        $defaults = array (
            'title' => __( 'My Account', 'cart66-members' ),
            'logged_out_message' => __( 'Please sign in', 'cart66-members' ),
            'logged_in_message' => 'Welcome %name%',
            'show_link_history' => '0',
            'show_link_profile' => '0',
        );
        $instance = wp_parse_args( $instance, $defaults );
        $title = esc_attr( $instance['title'] );
        $logged_out_message = esc_attr( $instance['logged_out_message'] );
        $logged_in_message = esc_attr( $instance['logged_in_message'] );
        $history = $instance['show_link_history'] == 1 ? 'checked="checked"' : '';
        $profile = $instance['show_link_profile'] == 1 ? 'checked="checked"' : '';

        $data = array (
            'widget' => $this,
            'title' => $title,
            'logged_out_message' => $logged_out_message,
            'logged_in_message' => $logged_in_message,
            'history' => $history,
            'profile' => $profile,
        );

        $view = CC_View::get(CM_PATH . 'views/widget/html-account-admin.php', $data);
        echo $view;
    }

    /**
     * Process the widget options to be saved
     */
    public function update( $new, $instance ) {
        $instance['title'] = esc_attr( $new['title'] );
        $instance['logged_in_message'] = esc_attr( $new['logged_in_message'] );
        $instance['logged_out_message'] = esc_attr( $new['logged_out_message'] );
        $instance['show_link_history'] = empty( $new['show_link_history'] ) ? 0 : 1;
        $instance['show_link_profile'] = empty( $new['show_link_profile'] ) ? 0 : 1;
        return $instance;
    }

    /**
     * Render the content of the widget
     */
    public function widget( $args, $instance ) {
        if ( ! class_exists('Cart66_Cloud') ) { return; }

        // Enqueue and localize javascript for rendering ajax cart widget content
        wp_enqueue_script('cm_ajax_account_widget', CM_URL . 'resources/js/account-widget.js');
        wp_enqueue_script('cc_ajax_spin', CC_URL . 'resources/js/spin.min.js');
        wp_enqueue_script('cc_ajax_spinner', CC_URL . 'resources/js/spinner.js', array('cc_ajax_spin'));
        $ajax_url = admin_url('admin-ajax.php');

        $widget_data = array(
            'ajax_url' => $ajax_url,
            'logged_out_message' => $instance['logged_out_message'],
            'logged_in_message'  => $instance['logged_in_message'],
            'show_link_history'  => $instance['show_link_history'],
            'show_link_profile'  => $instance['show_link_profile'],
        );

        wp_localize_script('cm_ajax_account_widget', 'cm_account_widget', $widget_data);

        extract($args);
        $data = array(
            'before_title'  => $before_title,
            'after_title'   => $after_title,
            'before_widget' => $before_widget,
            'after_widget'  => $after_widget,
            'title'         => $instance['title']
        );

        $view = CC_View::get(CM_PATH . 'views/widget/html-account-sidebar.php', $data);
        echo $view;
    }

    public static function ajax_render_content() {
        if ( ! class_exists('Cart66_Cloud') ) { return; }

        $widget = new CM_Account_Widget();
        $widget_settings = $widget->get_settings();
        $settings = array_shift ( $widget_settings );
        // CM_Log::write ( 'Widget settings: ' . print_r ( $settings, true ) );

        $url = new CC_Cloud_Url();
        $history_url = ( $_POST['show_link_history'] == 1 ) ? $url->order_history() : false;
        $profile_url = ( $_POST['show_link_profile'] == 1 ) ? $url->profile() : false;

        $home_url = home_url();
        $sign_in_url = $home_url . '/sign-in';
        $sign_out_url = $home_url . '/sign-out';

        $visitor = CM_Visitor::get_instance();
        $logged_in_message = str_replace(
            '%name%',
            '<span class="cc_visitor_name">' . $visitor->get_token('name') . '</span>',
            $settings['logged_in_message']
        );
        $logged_out_message = $settings['logged_out_message'];


        $data = array(
            'history_url'  => $history_url,
            'profile_url'  => $profile_url,
            'sign_in_url'  => $sign_in_url,
            'sign_out_url' => $sign_out_url,
            'is_logged_in' => $visitor->is_logged_in(),
            'logged_out_message' => $logged_out_message,
            'logged_in_message'  => $logged_in_message,
        );

        $view = CC_View::get(CM_PATH . 'views/widget/html-account-sidebar-content.php', $data);
        echo $view;
        die();
    }

}

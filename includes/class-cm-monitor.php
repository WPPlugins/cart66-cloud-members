<?php

class CM_Monitor {

    protected $current_memberships = array();

    public function __construct() {
        $this->current_memberships = array();
    }

    public function access_denied_redirect() {
        global $post;
        $visitor = CM_Visitor::get_instance();
        if ( isset( $post ) && is_object( $post ) ) {
            if ( ! $visitor->can_view_post( $post->ID ) ) {
                $access_denied_page_id = get_post_meta( $post->ID, '_ccm_access_denied_page_id', true );
                if ( $access_denied_page_id > 0 ) {
                    $link = get_permalink( $access_denied_page_id );
                    wp_redirect( $link );
                    exit();
                }
            }
        }
    }

    public function restrict_pages( $the_content ) {
        global $post;
        CM_Log::write( 'Called restrict pages' );

        $visitor = CM_Visitor::get_instance();

        // Check if page may be accessed
        if( ! $visitor->can_view_post( $post->ID ) ) {
            $settings = CC_Admin_Setting::get_options( 'cart66_members_notifications' );

            if ( $visitor->is_logged_in() ) {
                $the_content = __('This content is not included with your membership', 'cart66-members');
                if ( isset( $settings['not_included'] ) && ! empty( $settings['not_included'] ) ) {
                    $the_content = do_shortcode( $settings['not_included'] );
                }
            }
            else {
                $the_content = __('Please sign in to view this content', 'cart66-members');
                if ( isset( $settings['sign_in_required'] ) && ! empty( $settings['sign_in_required'] ) ) {
                    $the_content = do_shortcode( $settings['sign_in_required'] );
                }
            }

        }

        return $the_content;
    }

    /**
     * Filter posts so that the post is not found at all if the visitor is not allowed to see it
     *
     * By default, the "page" post type is not filtered. Additional post types may be added to the
     * unfiltered list of post types using the cc_unfiltered_post_types filter. Simply create a callback
     * function that accepts an array parameter of post type names and returns an array of post type
     * names that should not be filtered.
     *
     * The returned array includes posts from post types that are not filtered and posts
     * from filtered post types that the visitor is allowed to view.
     *
     * @return array The filtered list of posts
     */
    public function filter_posts( $posts ) {
        $visitor = CM_Visitor::get_instance();
        $filtered_posts = array();
        $unfiltered_post_types = apply_filters('cc_unfiltered_post_types', array('page-slurp', 'page'));
        foreach ( $posts as $post ) {
            if ( in_array( $post->post_type, $unfiltered_post_types ) || $visitor->can_view_post( $post->ID ) ) {
                $filtered_posts[] = $post;
            }
        }

        return $filtered_posts;
    }

    public function filter_pages( $pages ) {
        // CM_Log::write('Filtering pages :: count: ' . count($pages));
        for ( $i=0; $i < count( $pages ); $i++ ) {
            if ( isset( $pages[ $i ] ) ) {
                $page = $pages[ $i ];
                $visitor = CM_Visitor::get_instance();
                if ( ! $visitor->can_view_link( $page->ID ) ) {
                    unset( $pages[ $i ] );
                }
            }
        }

        return $pages;
    }

    public function filter_menus( $classes, $item ) {
        $visitor = CM_Visitor::get_instance();
        if ( ! $visitor->can_view_link( $item->object_id ) ) {
            //CM_Log::write('Filtering menus by adding ccm-hidden class to: ' . $item->object_id);
            $classes[] = 'cm-hidden';
        }

        return $classes;
    }

    public function filter_category_widget( $cat_args ) {
        $visitor = CM_Visitor::get_instance();
        $excluded_category_ids = $visitor->excluded_category_ids();
        $cat_args['exclude'] = implode(',', $excluded_category_ids);
        CM_Log::write('Modified cat_args to excluded denied category ids: ' . print_r($cat_args, TRUE));
        return $cat_args;
    }


    /**
     * Enque styles to show and hide restricted links as well as css for members
     *
     * The following files are in resources/css/
     * - logged-in.css
     * - logged-out.css
     * - members.css
     */
    public function enqueue_css() {
        $visitor = CM_Visitor::get_instance();

        if ( $visitor->is_logged_in() ) {
            wp_enqueue_style('cm-logged-in', cm_url() . 'resources/css/logged-in.css');
        } else {
            wp_enqueue_style('cm-logged-out', cm_url() . 'resources/css/logged-out.css');
        }

        wp_enqueue_style('cm-members', cm_url() . 'resources/css/members.css');
    }

}

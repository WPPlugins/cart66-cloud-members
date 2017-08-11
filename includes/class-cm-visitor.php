<?php

class CM_Visitor {

    public $token = FALSE;
    public $access_list = FALSE;
    public $restricted_cats = NULL;
    public $excluded_cats = NULL;
    public $user_data;

    public static $instance;

    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new CM_Visitor();
        }

        return self::$instance;
    }

    private function __construct() {
        CM_Log::write( 'Constructing new CM_Visitor' );

        $this->load_token();
        $this->load_restricted_cats();
        $this->load_excluded_category_ids();
    }

    public function set_access_list( array $list ) {
        // CM_Log::write('Setting logged in vistor access list :: ' . print_r($list, true));
        $this->access_list = $list;
    }


    /**
     * Load the list of post categories that have membership restrictions
     *
     * The loaded list is an array where each element of the array
     * Array (
     *     [category_id] => Array
     *         (
     *             [0] => expiring_product_sku_01
     *             [1] => expiring_product_sku_02
     *             [2] => expiring_product_sku_03
     *         )
     *
     * )
     */
    public function load_restricted_cats() {
        if ( class_exists('Cart66_Cloud') ) {
            if( ! is_array( $this->restricted_cats ) ) {
                // Calling get_options because we want the entire array of all the restricted categories
                $this->restricted_cats = CC_Admin_Setting::get_options( 'cart66_members_restrictions' );
                CM_Log::write( 'Loaded restricted categories: ' . print_r( $this->restricted_cats, true ) );
            }
        }
    }

    /**
     * Return an array of category ids that the current visitor does not have permission to view
     *
     * @return array
     */
    public function load_excluded_category_ids() {
        if ( !is_array( $this->excluded_cats ) ) {
            $this->excluded_cats = array();
            $category_args = array(
                'type'         => 'post',
                'child_of'     => 0,
                'parent'       => '',
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => 0,
                'hierarchical' => 1,
                'taxonomy'     => 'category'
            );

            $categories = get_categories( $category_args );

            if ( $categories && ! isset( $categories['errors'] ) ) {
                foreach( $categories as $cat ) {
                    if( ! $this->can_view_post_category( $cat->cat_ID ) ) {
                        CM_Log::write("Looping and Excluding category id: " . $cat->cat_ID);
                        $this->excluded_cats[] = $cat->cat_ID;
                    }
                }
            }

        }
    }

    public function excluded_category_ids() {
        if( ! is_array( $this->excluded_cats ) ) {
            $this->load_excluded_category_ids();
        }
        // CM_Log::write('Returning excluded category ids: ' . print_r($this->excluded_cats, TRUE));
        return $this->excluded_cats;
    }

    public function load_access_list( $force = false ) {
        if ( $force || ! is_array( $this->access_list ) ) {
            $token = $this->get_token();

            $cloud_visitor = CM_Cloud_Visitor::get_instance();
            $access_list = $cloud_visitor->get_memberships( $token );
            CM_Log::write("Loaded access list: " . print_r($access_list, true));
            $access_list = is_array( $access_list ) ? $access_list : array();
            $this->set_access_list( $access_list );
        }
        else {
            CM_Log::write('Not loading access list from cloud because it is already an array and is not forced to reload :: ' . print_r($this->access_list, true));
        }

        return $access_list;
    }

    public function drop_access_list() {
        $this->access_list = null;
    }

    /**
     * Return an array of std objects that hold membership skus and days_in values
     *
     * If visitor is not logged in or has no memberships an empty array is returned
     *
     * Array (
     *     [0] => stdClass Object
     *         (
     *             [sku] => basic
     *             [days_in] => 0
     *         )
     * )
     *
     * @return array
     */
    public function get_access_list() {
        $list = is_array( $this->access_list ) ? $this->access_list : $this->load_access_list();
        return $list;
    }

    public function load_token() {
        $this->token = false;
        if ( isset( $_COOKIE['ccm_token'] ) ) {
            $this->token = $_COOKIE['ccm_token'];
        }
    }

    public function check_remote_login() {
        if ( class_exists('Cart66_Cloud') ) {
            if ( isset( $_GET['cc_customer_token'] ) && isset( $_GET['cc_customer_first_name'] ) ) {
                $token = cc_get( 'cc_customer_token', 'text_field' );
                $name = cc_get( 'cc_customer_first_name', 'text_field' );
                $this->sign_in( $token, $name );
                $this->sign_in_redirect();
            }
        }
    }

    public function sign_in_redirect() {
        if (! class_exists('Cart66_Cloud') ) { return; }

        $member_home = CC_Admin_Setting::get_option( 'cart66_members_notifications', 'member_home' );
        $page_id = get_queried_object_id();
        CM_Log::write("Member home value: $member_home :: $page_id");

        if ( empty( $member_home ) || 'order_history' == $member_home ) {
            // redirect to order history
            $cloud = new CC_Cloud_API_V1();
            $order_history_url = $cloud->subdomain_url();
            CM_Log::write( "Sign in redirect to order history: $order_history_url" );
            wp_redirect( $order_history_url );
            exit();
        }
        elseif ( $page_id != $member_home ) {
            // redirect to member home page
            $url = get_permalink( $member_home );
            CM_Log::write( "Sign in redirect to WordPress URL: $url" );
            wp_redirect($url);
            exit();
        }
    }

    public function sign_in( $token, $name ) {
        $expire = 0; // Expire cookie at end of session
        $data = $token . '~' . $name;
        $_COOKIE['ccm_token'] = $data;
        $this->token = $data;
        setcookie('ccm_token', $data, $expire, COOKIEPATH, COOKIE_DOMAIN, false, true);
        if (COOKIEPATH != SITECOOKIEPATH) {
            setcookie('ccm_token', $data, $expire, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
            CM_Log::write("Logging in CC Member: $data");
        }
        $this->load_access_list( true ); // Force the reloading of the access list even if already set
    }

    /**
     * Remove the member token cookie and set the token to false.
     */
    public function sign_out() {
        CM_Log::write('attempting to log out and drop the ccm_token');

        $this->token = false;
        unset( $_COOKIE['ccm_token'] );
        setcookie('ccm_token', ' ', time() - 3600, COOKIEPATH);
        if ( COOKIEPATH != SITECOOKIEPATH ) {
            setcookie('ccm_token', ' ', time() - 3600, SITECOOKIEPATH, COOKIE_DOMAIN, false, true);
        }
    }

    /**
     * Return true if the visitor has a valid member token, otherwise false.
     *
     * @return boolean
     */
    public function is_logged_in() {
        return $this->get_token() ? true : false;
    }

    /**
     * Return the member access token, member name, or both values for the logged in visitor.
     *
     * If the visitor is not logged in or does not have a token return
     * an empty string. Unless otherwise specified by the $type parameter,
     * the member access token is returned.
     *
     * @param string $type [full, token, name]
     * @return string
     */
    public function get_token( $type='token' ) {
        if ( class_exists('Cart66_Cloud') ) {
            $allowed = array('full', 'token', 'name');
            if ( ! in_array( $type, $allowed ) ) {
                throw new CC_Exception( "Invalid token type requested: $type" );
            }

            $data = '';
            if ( $this->token ) {
                list( $token, $name ) = explode('~', $this->token);
                $data = array(
                    'full' => $this->token,
                    'token' => $token,
                    'name' => $name
                );
                $data = $data[$type];
            }
        }

        return $data;
    }

    /**
     * Return true if the visitor should be allowed to see the link in the navigation
     *
     * @return boolean
     */
    public function can_view_link( $post_id ) {
        $view = true;
        $memberships = get_post_meta( $post_id, '_ccm_required_memberships', true );
        $override = ( $this->is_logged_in() ) ? get_post_meta( $post_id, '_ccm_when_logged_in', true) : get_post_meta( $post_id, '_ccm_when_logged_out', true );

        if ( $override == 'show' ) {
            $view = true;
            CM_Log::write('Can view link because show is forced to true');
        }
        elseif ( $override == 'hide' ) {
            CM_Log::write('Can NOT view link because show is forced to false');
            $view = false;
        }
        elseif ( is_array( $memberships ) && count( $memberships ) ) {
            if( $this->can_view_post( $post_id ) ) {
                CM_Log::write('Can view link because visitor is logged in and has been granted access');
                $view = true;
            }
            else {
                CM_Log::write('Can NOT view link because a membership is required to view post :: ' . print_r( $memberships, true ) );
                $view = false;
            }
        }
        else {
            //CM_Log::write('Can view link because there are no restrictions on this post');
            $view = true;
        }

        return $view;
    }

    /**
     * Return true if the visitor is allowed to view the post with the given id.
     *
     * This function always returns false if the visitor is not logged in.
     *
     * @param int The post id
     * @return boolean
     */
    public function can_view_post( $post_id ) {
        $allow = true;
        $memberships = get_post_meta( $post_id, '_ccm_required_memberships', true );
        $post_cat_ids = wp_get_post_categories( $post_id );

        CM_Log::write("Categories for post id $post_id" . print_r($post_cat_ids, TRUE));

        // Check if visitor may view the post category
        if ( count( $post_cat_ids ) > 0 ) {
            $allow = false;
            foreach( $post_cat_ids as $cat_id ) {
                if( $this->can_view_post_category( $cat_id ) ) {
                    $allow = true;
                    CM_Log::write("Allowing access to category: $cat_id");
                    break;
                }
            }
        }

        if( $allow ) {
            if ( is_array( $memberships ) && count( $memberships ) ) {
                // CM_Log::write('This post requires memberships: ' . print_r($memberships, true));
                $allow = false; // only grant permission to logged in visitors with active subscriptions
                if ( $this->is_logged_in() ) {
                    $days_in = get_post_meta($post_id, '_ccm_days_in', true);
                    CM_Log::write("Checking if has permission on days in: $days_in :: "    . print_r($memberships, true));
                    if ( $this->has_permission( $memberships, $days_in ) ) {
                        CM_Log::write('This visitor has permission to view this post:' . $post_id);
                        $allow = true;
                    }
                    else {
                        CM_Log::write('Can NOT view post because the logged in visitor does not have permission');
                    }
                }
                else {
                    CM_Log::write('Can NOT view post because the visitor is not logged in');
                }
            }
        }

        return $allow;
    }

    public function can_view_post_category( $cat_id ) {
        CM_Log::write("Checking permission for category id: $cat_id");
        $allow = TRUE;

        if ( is_array( $this->restricted_cats ) && isset( $this->restricted_cats[ $cat_id ] ) ) {
            $memberships = $this->restricted_cats[ $cat_id ];
            $allow = $this->has_permission( $memberships );
        }

        $dbg = $allow ? "Granting permission for category id: $cat_id" : "Denying permission for category id: $cat_id";
        CM_Log::write($dbg);

        return $allow;
    }


    /**
     * Return true if one of the given memberships is in the access list and at least $days_in days old
     *
     * @param array $memberships An array of one or more membership SKUs
     * @param int $days_in The number of days a membership must be active before access is granted
     * @return boolean
     */
    public function has_permission( array $memberships, $days_in=0 ) {
        $access_list = $this->get_access_list();
        CM_Log::write('Checking logged in visotors access list :: ' . print_r($access_list, true));
        foreach ( $memberships as $sku ) {
            foreach ( $access_list as $item ) {
                $days_active = is_numeric( $item['days_in'] ) ? $item['days_in'] : 0;
                $days_in = is_numeric( $days_in ) ? $days_in : 0;
                // CM_Log::write("Days in: $days_in <=> Days active: $days_active");
                if ( $sku == $item['sku'] && $days_in <= $days_active ) {
                    CM_Log::write("Permission ok: $sku :: Days in: $days_in :: " . $item['days_in']);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the user data for the logged in visitor
     *
     * Set the static member variable $this->user_data to the array of retrieved data for the logged
     * in visitor. If not data could be retrieved, $this->user_data is set to an empty array.
     */
    public function get_user_data( $force_reload=false ) {
        if ( ! is_array( $this->user_data ) || count( $this->user_data ) == 0 || $force_reload ) {
            if ( $token = $this->get_token() ) {
                CM_Log::write("Called load user data using token: $token");
                $this->user_data = $this->get_cloud_user_data( $token );
                CM_Log::write( 'Retrieve user data from the cloud: ' . print_r( $this->user_data, true ) );
            }
            else {
                CM_Log::write('Not loading user data because nobody is logged in');
            }
        }
        else {
            CM_Log::write('Reusing user data');
        }

        return $this->user_data;
    }

    public function get_first_name() {
        $first_name = '';
        $user_data = $this->get_user_data();
        CM_Log::write( 'CM_Visitor::get_first_name: ' . print_r( $user_data, true ) );
        if ( isset( $user_data['first_name'] ) ) {
            $first_name = $user_data['first_name'];
        }
        return $first_name;
    }

    public function get_last_name() {
        $last_name = '';
        $user_data = $this->get_user_data();
        if( isset( $user_data['last_name'] ) ) {
            $last_name = $user_data['last_name'];
        }
        return $last_name;
    }

    public function get_email() {
        $email = '';
        $user_data = $this->get_user_data();
        if( isset( $user_data['email'] ) ) {
            $email = $user_data['email'];
        }
        return $email;
    }

    public function get_phone_number() {
        $phone_number = '';
        $user_data = $this->get_user_data();
        if( isset( $user_data['phone_number'] ) ) {
            $phone_number = $user_data['phone_number'];
        }
        return $phone_number;
    }


    /**
     * Return an array of user data
     *
     * If no data could be retrieved and empty array is returned.
     *
     * @return array
     */
    public function get_cloud_user_data( $token ) {
        $user_data = array();

        if ( !empty( $token ) && strlen( $token ) > 3 ) {
            $cloud = new CC_Cloud_API_V1();
            $url = $cloud->api . "accounts/$token";
            $headers = array( 'Accept' => 'application/json' );
            $response = wp_remote_get( $url, $cloud->basic_auth_header( $headers ) );
            CM_Log::write( "Get user data response: $url  ::  " . print_r($response, true) );
            if( $cloud->response_ok( $response ) ) {
                $json = $response['body'];
                $user_data = json_decode( $json, true );
                CM_Log::write( 'Received user data: ' . print_r($user_data, true) );
            } else {
                CM_Log::write( 'Failed to receive user data from the cloud: ' . print_r( $response, true ) );
                wp_redirect( '/sign-out' );
            }
        }

        return $user_data;
    }

}



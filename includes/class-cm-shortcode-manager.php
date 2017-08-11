<?php

class CM_Shortcode_Manager {

    public static function init() {
        add_shortcode('cm_show_to',              array('CM_Shortcode_Manager', 'cm_show_to'));
        add_shortcode('cm_hide_from',            array('CM_Shortcode_Manager', 'cm_hide_from'));
        add_shortcode('cm_visitor_name',         array('CM_Shortcode_Manager', 'cm_visitor_name'));
        add_shortcode('cm_visitor_first_name',   array('CM_Shortcode_Manager', 'cm_visitor_first_name'));
        add_shortcode('cm_visitor_last_name',    array('CM_Shortcode_Manager', 'cm_visitor_last_name'));
        add_shortcode('cm_visitor_email',        array('CM_Shortcode_Manager', 'cm_visitor_email'));
        add_shortcode('cm_visitor_phone_number', array('CM_Shortcode_Manager', 'cm_visitor_phone_number'));

        // Include legacy shortcodes
        add_shortcode('cc_show_to',              array('CM_Shortcode_Manager', 'cm_show_to'));
        add_shortcode('cc_hide_from',            array('CM_Shortcode_Manager', 'cm_hide_from'));
        add_shortcode('cc_visitor_name',         array('CM_Shortcode_Manager', 'cm_visitor_name'));
        add_shortcode('cc_visitor_first_name',   array('CM_Shortcode_Manager', 'cm_visitor_first_name'));
        add_shortcode('cc_visitor_last_name',    array('CM_Shortcode_Manager', 'cm_visitor_last_name'));
        add_shortcode('cc_visitor_email',        array('CM_Shortcode_Manager', 'cm_visitor_email'));
        add_shortcode('cc_visitor_phone_number', array('CM_Shortcode_Manager', 'cm_visitor_phone_number'));
    }

    public static function cm_visitor_name( $args, $content ) {
        return CC::visitor_name();
    }

    public static function cm_visitor_first_name( $args, $content ) {
        return CC::visitor_first_name();
    }

    public static function cm_visitor_last_name( $args, $content ) {
        return CC::visitor_last_name();
    }

    public static function cm_visitor_email( $args, $content ) {
        return CC::visitor_email();
    }

    public static function cm_visitor_phone_number( $args, $content ) {
        return CC::visitor_phone_number();
    }

    /**
     * Only show the enclosed content to visitors with an active subscription
     * to one or more of the provided SKUs. All SKUs will be lowercased before
     * evaluation.
     *
     * Special SKU values:
     *     members: all logged in users regardless of subscriptions or subscription status
     *     guests: all vistors who are not logged in
     *
     * Attributes:
     *     sku: Comma separated list of SKUs required to view content
     *     days_in: The number of days old the subscription must be before the content is available
     *
     * @param array $attrs An associative array of attributes, or an empty string if no attributes are given
     * @param string $content The content enclosed by the shortcode
     * @param string $tag The shortcode tag
     */
    public static function cm_show_to( $attrs, $content, $tag ) {
        if ( ! self::visitor_in_group( $attrs ) ) {
            $content = '';
        }
        return do_shortcode( $content );
    }

    public static function cm_hide_from( $attrs, $content, $tag ) {
        if( self::visitor_in_group( $attrs ) ) {
            $content = '';
        }
        return do_shortcode( $content );
    }

    public static function visitor_in_group( $attrs ) {
        $in_group = false;
        if( is_array( $attrs ) ) {
            $visitor = CM_Visitor::get_instance();
            $member_id = $visitor->get_token();
            $days_in = ( isset( $attrs['days_in'] ) ) ? (int) $attrs['days_in'] : 0;

            if ( isset( $attrs['sku'] ) ) {
                $skus = explode(',', strtolower( trim( str_replace(' ', '', $attrs['sku'] ) ) ) );
            }

            if ( strlen( $member_id ) == 0 && in_array('guests', $skus) ) {
                // Show content to all non-logged in visitors if "guests" is in the array of SKUs
                $in_group = true;
                CM_Log::write('Show to everyone not logged in because the sku is guests');
            }
            elseif( strlen( $member_id ) > 0 && ! in_array( 'guests', $skus ) ) {
                // If the visitor is logged in
                if ( in_array( 'members', $skus ) ) {
                    // Show content to all logged in visitors if "members" is in the array of SKUs
                    $in_group = true;
                    CM_Log::write('Show to everyone logged in because the sky is members');
                }
                else {
                    $visitor = CM_Visitor::get_instance();
                    if( $visitor->has_permission( $skus, $days_in ) ) {
                        $in_group = true;
                        CM_Log::write( "Show to $member_id: " . print_r( $skus, true ) );
                    }
                    else {
                        CM_Log::write("Cloud says member does not have permission");
                    }
                }
            }
        }

        $dbg = $in_group ? 'YES the visitor is in the group' : 'NO the visitor is NOT in the group';
        // CC_Log::write("Visitor in group final assessment :: $dbg");

        return $in_group;
    }

}


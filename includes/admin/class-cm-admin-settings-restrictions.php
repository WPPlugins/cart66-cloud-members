<?php

class CM_Admin_Settings_Restrictions extends CC_Admin_Setting {

    public static function init() {
        $page = 'cart66_members_restriction_settings';
        $option_group = 'cart66_members_restrictions';
        $setting = new CM_Admin_Settings_Restrictions( $page, $option_group );
        return $setting;
    }

    /**
     * admin_init hooked in by parent class constructor
     */
    public function register_settings() {
        $this->register_category_restriction_settings();
        $this->register();
    }

    public function register_category_restriction_settings() {
        // Set the name for the options in this section and load any stored values
        $option_values = self::get_options( $this->option_name );

        // Create the section for the cart66_main_settings section
        $title = __( 'Restrict access to Post categories', 'cart66-members' );
        $description = __( 'Select the memberships that are required in order to access posts for the listed categories.<br/>Do not select any memberships for categories open to the public', 'cart66-members' );
        $section = new CC_Admin_Settings_Section( $title, $this->option_name );
        $section->description = $description;

        // Add category checkboxes
        $restriction_title = __( 'Content Restrictions', 'cart66-members' );
        $restrictions = new CM_Admin_Restriction_Options( $restriction_title, 'category_restrictions', $option_values );
        $restrictions->description = '<strong>' . __( 'Your Categories', 'cart66-members' ) . '</strong>';
        $section->add_field( $restrictions );

        // Add the settings sections for the page and register the settings
        $this->add_section( $section );
    }

}

<?php
/**
 * Members settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Use this identifier when calling add_settings_error()
settings_errors( 'cart66_members_settings' );
?>

<div class="wrap">
    <h2>Cart66 Members Settings</h2>
    <h2 class="nav-tab-wrapper">
        <a href="?page=cart66_members&tab=notifications" class="nav-tab <?php echo $active_class['notifications']; ?>">Notifications</a>
        <a href="?page=cart66_members&tab=category-restrictions" class="nav-tab <?php echo $active_class['category-restrictions']; ?>">Category Restriction</a>
    </h2>
</div>

<div class="wrap">
    <form method="post" action="options.php">
        <?php


        if ( 'notifications' == $active_tab ) {
            do_settings_sections('cart66_members_notification_settings'); // menu_slug used in add_settings_section
            settings_fields('cart66_members_notifications');              // option_group
        } elseif ( 'category-restrictions' == $active_tab ) {
            do_settings_sections('cart66_members_restriction_settings'); // menu_slug used in add_settings_section
            settings_fields('cart66_members_restrictions');               // option_group
        }

        // Submit button.
        submit_button();
    ?>
    </form>
</div>

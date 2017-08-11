<?php wp_nonce_field('ccm_save_membership_ids', 'ccm_membership_ids_nonce'); ?>

<?php if(is_array($memberships) && count($memberships) > 0): ?>
    <div class="misc-pub-section">
        <?php _e( 'Select the memberships that are required to view this content.', 'cart66-members' ); ?>

        <ul>
            <?php foreach( $memberships as $name => $sku ): ?>
            <?php $checked = (is_array($requirements) && in_array($sku, $requirements)) ? 'checked="checked"' : ''; ?>
            <li>
                <?php if(!empty($sku)): ?>
                <input type="checkbox" name="ccm_membership_ids[]" value="<?php echo $sku; ?>" <?php echo $checked; ?> />
                <?php endif; ?>
                <?php echo $name; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        
    </div>

    <?php if($post_type != 'post'): ?>
        <div class="misc-pub-section">
            <p><strong><?php _e( 'Access Denied Page', 'cart66-members' ); ?></strong></p>
            <p><?php _e( 'If you do not use an access denied page, the content of the page will be replaced with your access notifications' ); ?>.</p>

            <?php 
                $args = array(
                    'name' => '_ccm_access_denied_page_id',
                    'show_option_none' => __('No access denied page', 'cart66-members'),
                    'option_none_value' => '0',
                    'selected' => $access_denied_page_id
                );
                wp_dropdown_pages($args); 
             ?>
        </div>

    <div class="misc-pub-section">
    <?php else: ?>
    <div class="misc-pub-section" style="border-bottom: none;">
    <?php endif; ?>
        <p><strong><?php _e( 'Deny access for', 'cart66-members'); ?></strong></p>
        <p>
            <label class="screen-reader-text" for="days_in"><?php _e( 'Days in', 'cart66-members' ); ?></label>
            <input name="_ccm_days_in" type="text" size="4" id="_ccm_days_in" value="<?php echo $days; ?>" /> 
            <?php _e( 'days after subscription starts', 'cart66-members' ); ?>
        </p>
    </div>

    <?php if($post_type != 'post'): ?>
    <div class="misc-pub-section" style="border-bottom: none;">
        <p><strong><?php _e( 'Page Display Override', 'cart66-members'); ?></strong></p>
        <p><?php _e('By default a page that requires a membership is hidden from the navigation when the visitor is logged out. 
        Use these settings to override the default behavior.', 'cart66-members' ); ?></p>

        <p><strong><?php _e( 'When visitor is logged in', 'cart66-members' ); ?></strong></p>

        <select name="_ccm_when_logged_in">
            <option value="" <?php if($when_logged_in == '') { echo 'selected="selected"'; } ?>><?php _e('Default behavior', 'cart66-members' ); ?></option>
            <option value="show" <?php if($when_logged_in == 'show') { echo 'selected="selected"'; } ?>><?php _e( 'Show page', 'cart66-members' ); ?></option>
            <option value="hide" <?php if($when_logged_in == 'hide') { echo 'selected="selected"'; } ?>><?php _e( 'Hide page', 'cart66-members' ); ?></option>
        </select>

        <p><strong><?php _e('When visitor is logged out', 'cart66-members' ); ?></strong></p>

        <select name="_ccm_when_logged_out">
            <option value="" <?php if($when_logged_out == '') { echo 'selected="selected"'; } ?>><?php _e('Default behavior', 'cart66-members' ); ?></option>
            <option value="show" <?php if($when_logged_out == 'show') { echo 'selected="selected"'; } ?>><?php _e('Show page', 'cart66-members' ); ?></option>
            <option value="hide" <?php if($when_logged_out == 'hide') { echo 'selected="selected"'; } ?>><?php _e('Hide page', 'cart66-members' ); ?></option>
        </select>
        
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="misc-pub-section" style="border-bottom: none;">
    <?php _e('You do not have any memberships or subscriptions configured in your Cart66 Cloud account', 'cart66-members'); ?>
    </div>
<?php endif; ?>

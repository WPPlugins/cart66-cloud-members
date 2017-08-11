<ul class="cm-account-widget-list">
	<li class="cm-account-widget-list-item">

		<?php if ( $is_logged_in == true ): ?>
			<div class="cm-account-widget-summary cm-logged-in">
				<?php echo $logged_in_message; ?>
			</div>

			<ul class="cm-account-widget-links">
				<?php if($history_url): ?>
					<li class="cm-history-url">
						<a class="cm-account-widget-history" href="<?php echo $history_url; ?>" rel="nofollow"><?php _e('Order History', 'cart66-members'); ?></a>
					</li>
				<?php endif; ?>
				
				<?php if($profile_url): ?>
					<li class="cm-profile-url">
						<a class="cm-account-widget-profile" href="<?php echo $profile_url; ?>" rel="nofollow"><?php _e('Profile', 'cart66-members'); ?></a>
					</li>
				<?php endif; ?>

				<li class="cm-sign-out">
					<a class="cc-sign-out-link" href="<?php echo $sign_out_url; ?>" rel="nofollow"><?php _e('Sign out', 'cart66-members'); ?></a>
				</li>
			</ul>

		<?php else: ?>
			<div class="cm-account-widget-summary cm-logged-out">
				<?php echo $logged_out_message; ?>
			</div>

			<div class="cm-sign-in">
				<a class="cc-sign-in-link" href="<?php echo $sign_in_url; ?>" rel="nofollow"><?php _e('Sign in', 'cart66-members'); ?></a>
			</div>
		<?php endif; ?>

	</li>
</ul>

<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title">
			<?php _e( "Notifications", "defender-security" ) ?>
        </h3>
    </div>
    <form method="post" class="hardener-settings-frm">
        <div class="sui-box-body">
            <p>
				<?php _e( "Get email notifications if/when a security tweak needs fixing.", "defender-security" ) ?>
            </p>

            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php _e( "Enable notifications", "defender-security" ) ?></span>
                    <span class="sui-description">
                    <?php _e( "Enabling this option will ensure you don’t need to check in to see that all your security tweaks are still active.", "defender-security" ) ?>
                </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <input type="hidden" name="notification" value="<?php echo $setting->notification ?>"/>
                        <div class="sui-side-tabs sui-tabs">
                            <div data-tabs>
                                <div rel="input_value" data-target="notification" data-value="1"
                                     class="<?php echo $setting->notification == 1 ? 'active' : null ?>"><?php _e( "On", "defender-security" ) ?></div>
                                <div rel="input_value" data-target="notification" data-value="0"
                                     class="<?php echo $setting->notification == 0 ? 'active' : null ?>"><?php _e( "Off", "defender-security" ) ?></div>
                            </div>
                            <div data-panes>
                                <div class="sui-tab-boxed <?php echo $setting->notification == 1 ? 'active' : null ?>">
                                    <p class="sui-p-small">
										<?php _e( "By default, we will only notify the recipients below when a security tweak hasn’t been actioned for 24 hours.", "defender-security" ) ?>
                                    </p>
                                    <div class="margin-top-30">
										<?php $email->renderInput() ?>
                                    </div>
                                    <label for="notification_repeat" class="sui-checkbox">
                                        <input type="hidden" name="notification_repeat" value="0"/>
                                        <input type="checkbox" <?php checked( '1', $setting->notification_repeat ) ?>
                                               id="notification_repeat" name="notification_repeat" value="1"/>
                                        <span aria-hidden="true"></span>
                                        <span><?php _e( "Send reminders every 24 hours if fixes still hasn’t been actioned.", "defender-security" ) ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="action" value="saveTweaksSettings"/>
		<?php wp_nonce_field( 'saveTweaksSettings' ) ?>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", "defender-security" ) ?></button>
            </div>
        </div>
    </form>
</div>
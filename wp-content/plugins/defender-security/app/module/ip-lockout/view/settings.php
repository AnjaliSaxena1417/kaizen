<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title"><?php _e( "Settings", "defender-security" ) ?></h3>
    </div>
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-body">
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Storage", "defender-security" ) ?></span>
                    <span class="sui-description">
                        <?php esc_html_e( "Event logs are cached on your local server to speed up load times. You can choose how many days to keep logs for before they are removed.", "defender-security" ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <input size="8" value="<?php echo $settings->storage_days ?>" type="text"
                               class="sui-form-control sui-field-has-suffix" id="storage_days"
                               name="storage_days"/>
                        <span class="sui-field-suffix"><?php esc_html_e( "days", "defender-security" ) ?></span>
                        <span class="sui-description">
                            <?php _e( "Choose how many days of event logs you’d like to store locally.", "defender-security" ) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Delete logs", "defender-security" ) ?></span>
                    <span class="sui-description">
                        <?php esc_html_e( "If you wish to delete your current logs simply hit delete and this will wipe your logs clean.", "defender-security" ) ?>
                    </span>
                </div>

                <div class="sui-box-settings-col-2">
                    <button type="button" data-nonce="<?php echo esc_attr( wp_create_nonce( 'lockoutEmptyLogs' ) ) ?>"
                            class="sui-button sui-button-ghost empty-logs"><?php _e( "Delete Logs", "defender-security" ) ?></button>
                    <span class="delete-status"></span>
                    <span class="sui-description">
                        <?php _e( "Note: Defender will instantly remove all past event logs, you will not be able to get them back.", "defender-security" ) ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="sui-box-footer">
			<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
            <input type="hidden" name="action" value="saveLockoutSettings"/>
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", "defender-security" ) ?></button>
            </div>
        </div>
    </form>
</div>
<?php
$checked = $controller->check();
?>
<div id="security_key" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "Security Keys", "defender-security" ) ?>
        </div>
        <div class="sui-accordion-col-4">
            <button class="sui-button-icon sui-accordion-open-indicator" aria-label="Open item">
                <i class="sui-icon-chevron-down" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="sui-accordion-item-body">
        <div class="sui-box">
            <div class="sui-box-body">
                <strong><?php _e( "Overview", "defender-security" ) ?></strong>
                <p>
					<?php _e( "WordPress uses security keys to improve the encryption of informtion stores in user cookies making it harder to crack passwords. A non-encrypted password like “username” or “wordpress” can be easily broken, but a random, unpredictable, encrypted password such as “88a7da62429ba6ad3cb3c76a09641fc” takes years to come up with the right combination.", "defender-security" ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You've automatically disabled PHP execution..", "defender-security" ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <strong>
						<?php _e( "Status", "defender-security" ) ?>
                    </strong>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php
                            printf( __( "Your current security keys are %s days old. Time to update them!", "defender-security" ), $daysAgo ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "Currently you have old security keys, it pays to keep them updated - we recommend every 60 days or less.", "defender-security" ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", "defender-security" ) ?>
                    </strong>
                    <p>
						<?php _e( "We can regenerate your key salts instantly for you and they will be good for another 60 days. Note that this will log all users out of your site. You can also choose how often we should notify you to change them.", "defender-security" ) ?>
                    </p>
				<?php endif; ?>
                <form method="post" class="hardener-frm" id="reminder-date">
                    <div class="sui-form-field">
                        <label class="sui-label"><?php _e( "Reminder frequency", "defender-security" ) ?></label>
                        <div class="sui-row">
                            <div class="sui-col-md-3">
                                <select name="remind_date" class="sui-select-sm">
                                    <option
                                            value="30 days" <?php selected( '30 days', $interval ) ?>><?php esc_html_e( '30 Days', "defender-security" ) ?></option>
                                    <option
                                            value="60 days" <?php selected( '60 days', $interval ) ?>><?php esc_html_e( '60 Days', "defender-security" ) ?></option>
                                    <option
                                            value="90 days" <?php selected( '90 days', $interval ) ?>><?php esc_html_e( '90 Days', "defender-security" ) ?></option>
                                    <option
                                            value="6 months" <?php selected( '6 months', $interval ) ?>><?php esc_html_e( '6 Months', "defender-security" ) ?></option>
                                    <option
                                            value="1 year" <?php selected( '1 year', $interval ) ?>><?php esc_html_e( '1 Year', "defender-security" ) ?></option>
                                </select>
                            </div>
                            <div class="sui-col">
                                <input type="hidden" name="action" value="updateSecurityReminder"/>
                                <button type="submit" class="sui-button sui-button-ghost">
									<?php _e( "Update", "defender-security" ) ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
			<?php if ( !$checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="updatePosts" value="no"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Regenerate Keys", "defender-security" ) ?></button>
                        </form>
                    </div>
                </div>
			<?php endif; ?>
        </div>
    </div>
</div>
<?php
$checked = $controller->check();
?>
<div id="disable-file-editor" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( "File Editor", "defender-security" ) ?>
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
                <strong>
					<?php _e( "Overview", "defender-security" ) ?>
                </strong>
                <p>
					<?php _e( "WordPress comes with a file editor built into the system. This means that anyone with access to your login information can further edit your plugin and theme files and inject malicious code. ", "defender-security" ) ?>
                </p>
                <strong>
					<?php _e( "Status", "defender-security" ) ?>
                </strong>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You've disabled the file editor, winning.", "defender-security" ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "The file editor is currently enabled.", "defender-security" ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "The file editor is currently active. If you don’t need it, we recommend disabling this feature.", "defender-security" ) ?>
                    </p>
                    <strong>
						<?php _e( "How to fix", "defender-security" ) ?>
                    </strong>
                    <p>
						<?php _e( "We can automatically disable the file editor for you below. Alternately, you can ignore this tweak if you don’t require it. Either way, you can easily revert these actions at any time.", "defender-security" ) ?>
                    </p>
				<?php endif; ?>
            </div>
            <div class="sui-box-footer">
				<?php if ( $checked ): ?>
                    <form method="post" class="hardener-frm rule-process">
						<?php $controller->createNonceField(); ?>
                        <input type="hidden" name="action" value="processRevert"/>
                        <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                        <button class="sui-button" type="submit">
                            <i class="sui-icon-undo" aria-hidden="true"></i>
							<?php _e( "Revert", "defender-security" ) ?></button>
                    </form>
				<?php else: ?>
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                    <div class="sui-actions-right">
                        <form method="post" class="hardener-frm rule-process hardener-frm-process-xml-rpc">
							<?php $controller->createNonceField(); ?>
                            <input type="hidden" name="action" value="processHardener"/>
                            <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                            <button class="sui-button sui-button-blue" type="submit">
								<?php _e( "Disable file editor", "defender-security" ) ?></button>
                        </form>
                    </div>
				<?php endif; ?>
            </div>
        </div>
    </div>
</div>
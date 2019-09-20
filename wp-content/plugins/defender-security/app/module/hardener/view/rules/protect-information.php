<?php
$checked = $controller->check();
if ( DIRECTORY_SEPARATOR == '\\' ) {
	//Windows
	$wp_includes = str_replace( ABSPATH, '', WPINC );
	$wp_content  = str_replace( ABSPATH, '', WP_CONTENT_DIR );
} else {
	$wp_includes = str_replace( $_SERVER['DOCUMENT_ROOT'], '', ABSPATH . WPINC );
	$wp_content  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', WP_CONTENT_DIR );
}
global $is_nginx, $is_IIS, $is_iis7;
$setting = \WP_Defender\Module\Hardener\Model\Settings::instance();
if ( $is_nginx ) {
	$setting->active_server = 'nginx';
} else if ( $is_IIS ) {
	$setting->active_server = 'iis';
} else if ( $is_iis7 ) {
	$setting->active_server = 'iis-7';
}
?>
<div id="disable_xml_rpc" class="sui-accordion-item <?php echo $controller->getCssClass() ?>">
    <div class="sui-accordion-item-header">
        <div class="sui-accordion-item-title">
            <i aria-hidden="true" class="<?php echo $checked ? 'sui-icon-check-tick sui-success'
				: 'sui-icon-warning-alert sui-warning' ?>"></i>
			<?php _e( 'Information Disclosure', "defender-security" ) ?>
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
					<?php _e( "Often servers are incorrectly configured, and can allow an attacker to get access to sensitive files like your config, .htaccess and backup files. Hackers can grab these files and use them to gain access to your website or database.", "defender-security" ) ?>
                </p>
				<?php if ( $checked ): ?>
                    <div class="sui-notice sui-notice-success">
                        <p>
							<?php _e( "You've automatically enabled information disclosure protection.", "defender-security" ) ?>
                        </p>
                    </div>
				<?php else: ?>
                    <div class="sui-notice sui-notice-warning">
                        <p>
							<?php _e( "You don't have information disclosure protection active.", "defender-security" ) ?>
                        </p>
                    </div>
                    <p>
						<?php _e( "Currently, some of your config files aren’t protected. It’s best to lock this down these files to ensure they can’t be accessed by hackers and bots.", "defender-security" ) ?>
                    </p>
                    <div class="sui-tabs sui-side-tabs">

                        <div data-tabs>
                            <div class="<?php echo $setting->active_server == 'apache' ? 'active' : '' ?>"><?php _e( "Apache", "defender-security" ) ?></div>
                            <div class="<?php echo $setting->active_server == 'litespeed' ? 'active' : '' ?>"><?php _e( "Litespeed", "defender-security" ) ?></div>
                            <div class="<?php echo $setting->active_server == 'nginx' ? 'active' : '' ?>"><?php _e( "Nginx", "defender-security" ) ?></div>
                            <div class="<?php echo $setting->active_server == 'iis' ? 'active' : '' ?>"><?php _e( "IIS", "defender-security" ) ?></div>
                            <div class="<?php echo $setting->active_server == 'iis7' ? 'active' : '' ?>"><?php _e( "IIS7", "defender-security" ) ?></div>
                        </div>

                        <div data-panes>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'apache' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/information-disclosure/apache_litespeed', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'litespeed' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/information-disclosure/apache_litespeed', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed hardener-instructions <?php echo $setting->active_server == 'nginx' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/information-disclosure/nginx', array(
									'setting' => $setting
								) ) ?>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'iis' ? 'active' : '' ?>">
                                <p><?php printf( __( 'For IIS servers, <a href="%s">visit Microsoft TechNet</a>', "defender-security" ), 'https://technet.microsoft.com/en-us/library/cc725855(v=ws.10).aspx' ); ?></p>
                            </div>
                            <div class="sui-tab-boxed <?php echo $setting->active_server == 'iis7' ? 'active' : '' ?>">
								<?php $controller->renderPartial( 'rules/prevent-php/iis7', array(
									'setting' => $setting
								) ) ?>
                            </div>

                        </div>

                    </div>
				<?php endif; ?>
            </div>
			<?php if ( ! $checked ): ?>
                <div class="sui-box-footer">
                    <div class="sui-actions-left">
						<?php $controller->showIgnoreForm() ?>
                    </div>
                </div>
			<?php else: ?>
				<?php if ( $setting->active_server == 'apache' || $setting->active_server == 'lite_speed' ): ?>
                    <div class="sui-box-footer">
                        <div class="sui-actions-left">
                            <form method="post" class="hardener-frm rule-process">
		                        <?php $controller->createNonceField(); ?>
                                <input type="hidden" name="action" value="processRevert"/>
                                <input type="hidden" name="slug" value="<?php echo $controller::$slug ?>"/>
                                <button class="sui-button sui-button-gray"
                                        type="submit"><?php _e( "Revert", "defender-security" ) ?></button>
                            </form>
                        </div>
                    </div>
				<?php endif; ?>
			<?php endif; ?>
        </div>
    </div>
</div>

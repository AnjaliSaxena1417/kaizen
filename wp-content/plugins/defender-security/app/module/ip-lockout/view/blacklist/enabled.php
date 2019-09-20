<div class="sui-box">
    <form method="post" id="settings-frm" class="ip-frm">
        <div class="sui-box-header">
            <h3 class="sui-box-title">
				<?php _e( "IP Banning", "defender-security" ) ?>
            </h3>
        </div>
        <div class="sui-box-body">
            <p>
				<?php _e( "Choose which IP addresses you wish to permanently ban from accessing your website.", "defender-security" ) ?>
            </p>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "IP Addresses", "defender-security" ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Add IP addresses you want to permanently ban from, or always allow access to your website. ", "defender-security" ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <strong><?php _e( "Blacklist", "defender-security" ) ?></strong>
                    <p class="sui-description">
						<?php _e( "Any IP addresses you list here will be completely blocked from accessing your website, including admins.", "defender-security" ) ?>
                    </p>
                    <div class="sui-border-frame">
                        <label class="sui-label"><?php _e( "Banned IPs", "defender-security" ) ?></label>
                        <textarea class="sui-form-control"
                                  id="ip_blacklist" name="ip_blacklist"
                                  placeholder="<?php esc_attr_e( "Add IP addresses here, one per line", "defender-security" ) ?>"
                                  rows="8"><?php echo $settings->ip_blacklist ?></textarea>
                        <span class="sui-description">
                            <?php _e( "Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.", "defender-security" ) ?>
                        </span>
                    </div>
                    <strong><?php _e( "Whitelist", "defender-security" ) ?></strong>
                    <p class="sui-description">
						<?php _e( "Any IP addresses you list here will be exempt any existing or new ban rules outlined in login protection, 404 detection or IP ban lists.", "defender-security" ) ?>
                    </p>
                    <div class="sui-border-frame">
                        <label class="sui-label"><?php _e( "Allowed IPs", "defender-security" ) ?></label>
                        <textarea class="sui-form-control"
                                  id="ip_whitelist" name="ip_whitelist"
                                  placeholder="<?php esc_attr_e( "Add IP addresses here, one per line", "defender-security" ) ?>"
                                  rows="8"><?php echo $settings->ip_whitelist ?></textarea>
                        <span class="sui-description">
                            <?php _e( "One IP address per line. Both IPv4 and IPv6 are supported. IP ranges are also accepted in format xxx.xxx.xxx.xxx-xxx.xxx.xxx.xxx.", "defender-security" ) ?>
                        </span>
                    </div>
                    <div class="sui-notice">
                        <p>
							<?php printf( __( "We recommend you add your own IP to avoid getting locked out accidentally! Your current IP is <span class='admin-ip'>%s</span>.", "defender-security" ), \WP_Defender\Behavior\Utils::instance()->getUserIp() ) ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Locations", "defender-security" ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Use this feature to ban any countries you don’t expect/want traffic from to protect your site entirely from unwanted hackers and bots.", "defender-security" ) ?></span>
                </div>
                <div class="sui-box-settings-col-2 geo-ip-block">
					<?php if ( version_compare( phpversion(), '5.4', '<' ) ): ?>
                        <div class="sui-notice sui-notice-warning">
                            <p>
								<?php printf( __( "This feature requires PHP 5.4 or newer. Please upgrade your PHP version if you wish to use location banning.", "defender-security" ), admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ) ?>
                            </p>
                        </div>
					<?php else: ?>
						<?php $country = \WP_Defender\Module\IP_Lockout\Component\IP_API::getCurrentCountry(); ?>
						<?php if ( $settings->isGeoDBDownloaded() == false ): ?>
                            <div class="sui-notice sui-notice-info">
                                <p>
									<?php _e( "To use this feature you must first download the latest Geo IP Database.", "defender-security" ) ?>
                                </p>
                                <div class="sui-notice-buttons">
                                    <button type="button" class="sui-button sui-button-ghost download-geo-ip"
                                            data-nonce="<?php echo wp_create_nonce( 'downloadGeoIPDB' ) ?>">
                                        <span class="sui-loading-text"><?php _e( "Download", "defender-security" ) ?></span>
                                        <i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
						<?php elseif ( ! $country ): ?>
                            <div class="sui-notice sui-notice-warning">
                                <p>
									<?php printf( __( "Can't detect current country, it seem your site setup in localhost environment", "defender-security" ), admin_url( 'admin.php?page=wdf-ip-lockout&view=blacklist' ) ) ?>
                                </p>
                            </div>
						<?php else: ?>
                            <strong><?php _e( "Blacklist", "defender-security" ) ?></strong>
                            <p class="sui-description no-margin-bottom">
								<?php _e( "Any countries you select will not be able to access any area of your website.", "defender-security" ) ?>
                            </p>
                            <div class="sui-border-frame">
                                <div class="sui-control-with-icon">
                                    <input type="hidden" name="country_blacklist[]" value=""/>
                                    <select class="sui-select sui-select sui-form-control" name="country_blacklist[]"
                                            placeholder="<?php esc_attr_e( "Type country name", "defender-security" ) ?>"
                                            multiple>
                                        <option value="all" <?php selected( true, in_array( 'all', $settings->getCountryBlacklist() ) ) ?>><?php _e( "Block all", "defender-security" ) ?></option>
										<?php foreach ( \WP_Defender\Behavior\Utils::instance()->countriesList() as $code => $country ): ?>
                                            <option value="<?php echo $code ?>" <?php selected( true, in_array( $code, $settings->getCountryBlacklist() ) ) ?>><?php echo $country ?></option>
										<?php endforeach; ?>
                                    </select>
                                    <i class="sui-icon-web-globe-world" aria-hidden="true"></i>
                                </div>
                            </div>
                            <strong><?php _e( "Whitelist", "defender-security" ) ?></strong>
                            <p class="sui-description no-margin-bottom">
								<?php _e( "Any countries you select will always be able to view your website. Note: We’ve added your default country by default.", "defender-security" ) ?>
                            </p>
                            <div class="sui-border-frame">
                                <div class="sui-control-with-icon">
                                    <input type="hidden" name="country_whitelist[]" value=""/>
                                    <select class="sui-select sui-select sui-form-control" name="country_whitelist[]"
                                            placeholder="<?php esc_attr_e( "Type country name", "defender-security" ) ?>"
                                            multiple>
										<?php foreach ( \WP_Defender\Behavior\Utils::instance()->countriesList() as $code => $country ): ?>
                                            <option value="<?php echo $code ?>" <?php selected( true, in_array( $code, $settings->getCountryWhitelist() ) ) ?>><?php echo $country ?></option>
										<?php endforeach; ?>
                                    </select>
                                    <i class="sui-icon-web-globe-world" aria-hidden="true"></i>
                                </div>
                                <p class="sui-description">
									<?php _e( "Note: your whitelist will override any country ban, but will still follow your 404 and login lockout rules.", "defender-security" ) ?>
                                </p>
                            </div>
                            <p class="sui-description">
                                This product includes GeoLite2 data created by MaxMind, available from
                                <a href="https://www.maxmind.com">https://www.maxmind.com</a>.
                            </p>
						<?php endif; ?>
					<?php endif; ?>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label"><?php esc_html_e( "Message", "defender-security" ) ?></span>
                    <span class="sui-description"><?php esc_html_e( "Customize the message locked out users will see.", "defender-security" ) ?></span>
                </div>
                <div class="sui-box-settings-col-2">
                    <label class="sui-label">
						<?php _e( "Custom message", "defender-security" ) ?>
                    </label>
                    <div class="sui-form-field">
                    <textarea name="ip_lockout_message" class="sui-form-control"
                              placeholder="<?php esc_attr_e( "The administrator has blocked your IP from accessing this website.", "defender-security" ) ?>"
                              id="ip_lockout_message"><?php echo $settings->ip_lockout_message ?></textarea>
                        <span class="sui-description">
                        <?php echo sprintf( __( "This message will be displayed across your website during the lockout period. See a quick preview <a href=\"%s\">here</a>.", "defender-security" ), add_query_arg( array(
	                        'def-lockout-demo' => 1,
	                        'type'             => 'blacklist'
                        ), network_site_url() ) ) ?>
                    </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Import", "defender-security" ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Use this tool to import both your blacklist and whitelist from another website.", "defender-security" ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <div class="sui-form-field">
                        <span><?php _e( "Upload your exported blacklist.", "defender-security" ) ?></span>
                        <div class="upload-input sui-upload">
                            <div class="sui-upload-file">

                                <span></span>

                                <button aria-label="Remove file" class="file-picker-remove">
                                    <i class="sui-icon-close" aria-hidden="true"></i>
                                </button>

                            </div>
                            <button type="button" class="sui-upload-button file-picker">
                                <i class="sui-icon-upload-cloud" aria-hidden="true"></i> Upload file
                            </button>
                            <input type="hidden" name="file_import" id="file_import">
                        </div>
                        <div class="clear margin-top-10"></div>
                        <button type="button" class="sui-button sui-button-ghost btn-import-ip">
                            <i class="sui-icon-download-cloud" aria-hidden="true"></i>
							<?php _e( "Import", "defender-security" ) ?>
                        </button>
                        <span class="sui-description">
                            <?php _e( "Note: Existing IPs will not be removed - only new IPs added.", "defender-security" ) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="sui-box-settings-row">
                <div class="sui-box-settings-col-1">
                    <span class="sui-settings-label">
                        <?php _e( "Export", "defender-security" ) ?>
                    </span>
                    <span class="sui-description">
                    <?php _e( "Export both your blacklist and whitelist to use on another website.", "defender-security" ) ?>
                    </span>
                </div>
                <div class="sui-box-settings-col-2">
                    <a href="<?php echo network_admin_url( 'admin.php?page=wdf-ip-lockout&view=export&_wpnonce=' . wp_create_nonce( 'defipexport' ) ) ?>"
                       class="sui-button sui-button-outlined export">
                        <i class="sui-icon-upload-cloud" aria-hidden="true"></i>
						<?php _e( "Export", "defender-security" ) ?>
                    </a>
                    <span class="sui-description">
                        <?php _e( "The export will include both the blacklist and whitelist.", "defender-security" ) ?>
                    </span>
                </div>
            </div>
        </div>
		<?php wp_nonce_field( 'saveLockoutSettings' ) ?>
        <input type="hidden" name="action" value="saveLockoutSettings"/>
        <div class="sui-box-footer">
            <div class="sui-actions-right">
                <button type="submit" class="sui-button sui-button-blue">
                    <i class="sui-icon-save" aria-hidden="true"></i>
					<?php _e( "Save Changes", "defender-security" ) ?></button>
            </div>
        </div>
    </form>
</div>
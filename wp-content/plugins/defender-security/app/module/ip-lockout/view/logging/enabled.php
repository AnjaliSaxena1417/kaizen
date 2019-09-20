<div class="sui-box">
    <div class="sui-box-header">
        <h3 class="sui-box-title"><?php esc_html_e( "Logs", "defender-security" ) ?></h3>
        <div class="sui-actions-right">
            <div class="box-filter">
                <span>
                    <?php _e( "Sort by", "defender-security" ) ?>
                </span>
                <select class="sui-select-sm" name="sort" id="lockout-logs-sort">
                    <option value="latest"><?php _e( "Latest", "defender-security" ) ?></option>
                    <option value="oldest"><?php _e( "Oldest", "defender-security" ) ?></option>
                    <option value="ip"><?php _e( "IP Address", "defender-security" ) ?></option>
                </select>
            </div>
            <a href="<?php echo admin_url( 'admin-ajax.php?action=lockoutExportAsCsv' ) ?>"
               class="sui-button sui-button-outlined">
				<?php _e( "Export CSV", "defender-security" ) ?>
            </a>
        </div>
    </div>
    <div class="sui-box-body">
        <p>
			<?php
			_e( "Here's your comprehensive IP lockout log. You can whitelist and ban IPs from there.", "defender-security" )
			?>
        </p>
	    <?php
	    $table = new \WP_Defender\Module\IP_Lockout\Component\Logs_Table();
	    $table->prepare_items();
	    $table->display();
	    ?>
    </div>
</div>
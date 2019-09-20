<p>
	<?php _e( "We can’t automatically action this fix, but follow the instructions below to patch this up. 
First, add any exceptions to files you want to allow PHP to be executed from, then follow the instructions below.", "defender-security" ) ?>
</p>
<div class="sui-form-field margin-top-30">
    <label class="sui-label"><?php _e( "Exceptions", "defender-security" ) ?></label>
    <textarea class="sui-form-control hardener-php-excuted-ignore"></textarea>
    <span class="sui-description">
        <?php _e( "Add exceptions to PHP files you want to continue to run. Include the full paths to the file.", "defender-security" ) ?>
    </span>
</div>
<strong><?php _e( "Instructions", "defender-security" ) ?></strong>
<p>
	<?php _e( "1. Copy the generated code into your site specific .conf file usually located in a subdirectory under /etc/nginx/... or /usr/local/nginx/conf/...", "defender-security" ) ?>
</p>
<p>
	<?php _e( "2. Add the code above inside the server section in the file, right before the php location block. Looks something like:", "defender-security" ) ?>
    <code>location ~ \.php$ {</code>
</p>
<p>
	<?php _e( "3. Reload NGINX.", "defender-security" ) ?>
</p>
<strong><?php _e( "Code", "defender-security" ) ?></strong>
<?php
if ( DIRECTORY_SEPARATOR == '\\' ) {
	//Windows
	$wp_includes = str_replace( ABSPATH, '', WPINC );
	$wp_content  = str_replace( ABSPATH, '', WP_CONTENT_DIR );
} else {
	$wp_includes = str_replace( $_SERVER['DOCUMENT_ROOT'], '', ABSPATH . WPINC );
	$wp_content  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', WP_CONTENT_DIR );
}

$rules = "# Stop php access except to needed files in wp-includes
location ~* ^$wp_includes/.*(?<!(js/tinymce/wp-tinymce))\.php$ {
  internal; #internal allows ms-files.php rewrite in multisite to work
}

# Specifically locks down upload directories in case full wp-content rule below is skipped
location ~* /(?:uploads|files)/.*\.php$ {
  deny all;
}

# Deny direct access to .php files in the /wp-content/ directory (including sub-folders).
#  Note this can break some poorly coded plugins/themes, replace the plugin or remove this block if it causes trouble
location ~* ^$wp_content/.*\.php$ {
  deny all;
}
";
?>
<p>
    <code>## WP Defender - Prevent PHP Execution ##<br/><?php echo esc_html( $rules ) ?><span class="hardener-nginx-extra-instructions"></span><br/>## WP Defender - End ##</code>
</p>
<div class="sui-notice">
    <p><?php echo sprintf( __( "Still having trouble? <a target='_blank' href=\"%s\">Open a support ticket</a>.", "defender-security" ), 'https://premium.wpmudev.org/forums/forum/support#question' ) ?></p>
</div>

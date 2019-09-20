<?php
if ( ! function_exists( 'login_header' ) ) {
//copy from wp-login
/**
 * Output the login page header.
 *
 * @param string $title Optional. WordPress login Page title to display in the `<title>` element.
 *                           Default 'Log In'.
 * @param string $message Optional. Message to display in header. Default empty.
 * @param WP_Error $wp_error Optional. The error to pass. Default empty.
 */
function login_header( $title = 'Log In', $message = '', $wp_error = '' ) {
global $error, $interim_login, $action;

if ( empty( $wp_error ) ) {
	$wp_error = new WP_Error();
}

// Shake it!
$shake_error_codes = array(
	'empty_password',
	'empty_email',
	'invalid_email',
	'invalidcombo',
	'empty_username',
	'invalid_username',
	'incorrect_password'
);
/**
 * Filters the error codes array for shaking the login form.
 *
 * @since 3.0.0
 *
 * @param array $shake_error_codes Error codes that shake the login form.
 */
$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) ) {
	add_action( 'login_head', 'wp_shake_js', 12 );
}

$separator = is_rtl() ? ' &rsaquo; ' : ' &lsaquo; ';

?><!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
    <meta http-equiv="Content-Type"
          content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>"/>
    <title><?php echo get_bloginfo( 'name', 'display' ) . $separator . $title; ?></title>
	<?php

	wp_enqueue_style( 'login' );

	/*
	 * Remove all stored post data on logging out.
	 * This could be added by add_action('login_head'...) like wp_shake_js(),
	 * but maybe better if it's not removable by plugins
	 */
	if ( 'loggedout' == $wp_error->get_error_code() ) {
		?>
        <script>if ("sessionStorage" in window) {
                try {
                    for (var key in sessionStorage) {
                        if (key.indexOf("wp-autosave-") != -1) {
                            sessionStorage.removeItem(key)
                        }
                    }
                } catch (e) {
                }
            }
            ;</script>
		<?php
	}

	/**
	 * Enqueue scripts and styles for the login page.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_enqueue_scripts' );

	/**
	 * Fires in the login page header after scripts are enqueued.
	 *
	 * @since 2.1.0
	 */
	do_action( 'login_head' );

	if ( is_multisite() ) {
		$login_header_url   = network_home_url();
		$login_header_title = get_network()->site_name;
	} else {
		$login_header_url   = __( 'https://wordpress.org/' );
		$login_header_title = __( 'Powered by WordPress' );
	}

	/**
	 * Filters link URL of the header logo above login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $login_header_url Login header logo URL.
	 */
	$login_header_url = apply_filters( 'login_headerurl', $login_header_url );

	/**
	 * Filters the title attribute of the header logo above login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $login_header_title Login header logo title attribute.
	 */
	$login_header_title = apply_filters( 'login_headertitle', $login_header_title );

	$classes = array( 'login-action-' . $action, 'wp-core-ui' );
	if ( is_rtl() ) {
		$classes[] = 'rtl';
	}
	if ( $interim_login ) {
		$classes[] = 'interim-login';
		?>
        <style type="text/css">html {
                background-color: transparent;
            }</style>
		<?php

		if ( 'success' === $interim_login ) {
			$classes[] = 'interim-login-success';
		}
	}
	$classes[] = ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

	/**
	 * Filters the login page body classes.
	 *
	 * @since 3.5.0
	 *
	 * @param array $classes An array of body classes.
	 * @param string $action The action that brought the visitor to the login page.
	 */
	$classes = apply_filters( 'login_body_class', $classes, $action );

	?>
</head>
<body class="login <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
<?php
/**
 * Fires in the login page header after the body tag is opened.
 *
 * @since 4.6.0
 */
do_action( 'login_header' );
?>
<div id="login">

    <h1><a href="<?php echo esc_url( $login_header_url ); ?>" title="<?php echo esc_attr( $login_header_title ); ?>"
           tabindex="-1"><?php bloginfo( 'name' ); ?></a></h1>
	<?php

	unset( $login_header_url, $login_header_title );

	/**
	 * Filters the message to display above the login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $message Login message text.
	 */
	$message = apply_filters( 'login_message', $message );
	if ( ! empty( $message ) ) {
		echo $message . "\n";
	}

	// In case a plugin uses $error rather than the $wp_errors object
	if ( ! empty( $error ) ) {
		$wp_error->add( 'error', $error );
		unset( $error );
	}

	if ( $wp_error->get_error_code() ) {
		$errors   = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data( $code );
			foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {
				if ( 'message' == $severity ) {
					$messages .= '	' . $error_message . "<br />\n";
				} else {
					$errors .= '	' . $error_message . "<br />\n";
				}
			}
		}
		if ( ! empty( $errors ) ) {
			/**
			 * Filters the error messages displayed above the login form.
			 *
			 * @since 2.1.0
			 *
			 * @param string $errors Login error message.
			 */
			echo '<div id="login_error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
		}
		if ( ! empty( $messages ) ) {
			/**
			 * Filters instructional messages displayed above the login form.
			 *
			 * @since 2.5.0
			 *
			 * @param string $messages Login messages.
			 */
			echo '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
		}
	}
	}
	}
	login_header( '', '', $error );
	?>
    <form method="post"
          action="<?php echo esc_url( add_query_arg( 'action', 'defenderVerifyOTP', site_url( 'wp-login.php', 'login_post' ) ) ); ?>">
        <p><?php _e( "Open the Google Authenticator app and enter the 6 digit passcode.", "defender-security" ) ?></p>
        <input type="text" value="" autocomplete="off" name="otp">
        <button class="button button-primary float-r"
                type="submit"><?php _e( "Authenticate", "defender-security" ) ?></button>
        <input type="hidden" name="login_token" value="<?php echo $loginToken ?>"/>
        <input type="hidden" name="redirect_to" value="<?php echo $redirect_to ?>"/>
		<?php wp_nonce_field( 'DefOtpCheck' ) ?>
    </form>
	<?php
	$settings = \WP_Defender\Module\Advanced_Tools\Model\Auth_Settings::instance();

	if ( wp_defender()->isFree == false && $settings->customGraphic ) {
		?>
        <style type="text/css">
            body.login div#login h1 a {
                background-image: url("<?php echo $settings->customGraphicURL ?>");
            }
        </style>
		<?php
	}
	?>
	<?php if ( \WP_Defender\Module\Advanced_Tools\Model\Auth_Settings::instance()->lostPhone ): ?>
        <p id="nav">
            <a id="lostPhone"
               href="<?php echo admin_url( 'admin-ajax.php?action=defRetrieveOTP&token=' . $loginToken . '&nonce=' . wp_create_nonce( 'defRetrieveOTP' ) ) ?>">
				<?php _e( "Lost your device?", "defender-security" ) ?></a>
            <img class="def-ajaxloader"
                 src="<?php echo wp_defender()->getPluginUrl() . 'app/module/advanced-tools/img/spinner.svg' ?>"/>
            <strong class="notification">

            </strong>
        </p>
        <script type="text/javascript">
            jQuery(function ($) {
                $('.def-ajaxloader').hide();
                var isSent = false;
                $('#lostPhone').click(function (e) {
                    e.preventDefault();
                    var that = $(this);
                    if (isSent == false) {
                        isSent = true;
                        $.ajax({
                            type: 'GET',
                            url: that.attr('href'),
                            beforeSend: function () {
                                that.attr('disabled', 'disabled');
                                $('.def-ajaxloader').show();
                            },
                            success: function (data) {
                                that.removeAttr('disabled');
                                $('.def-ajaxloader').hide();
                                $('.notification').text(data.data.message);
                                isSent = false;
                            }
                        })
                    }

                })
            })
        </script>
	<?php endif; ?>
	<?php
	if ( ! function_exists( 'login_footer' ) ) {
	//copy from wp login
	/**
	 * Outputs the footer for the login page.
	 *
	 * @param string $input_id Which input to auto-focus
	 */
	function login_footer( $input_id = '' ) {
	global $interim_login;

	// Don't allow interim logins to navigate away from the page.
	if ( ! $interim_login ): ?>
        <p id="backtoblog"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php
				/* translators: %s: site title */
				printf( _x( '&larr; Back to %s', 'site' ), get_bloginfo( 'title', 'display' ) );
				?></a></p>
	<?php endif; ?>

</div>

<?php if ( ! empty( $input_id ) ) : ?>
    <script type="text/javascript">
        try {
            document.getElementById('<?php echo $input_id; ?>').focus();
        } catch (e) {
        }
        if (typeof wpOnload == 'function') wpOnload();
    </script>
<?php endif; ?>

<?php
/**
 * Fires in the login page footer.
 *
 * @since 3.1.0
 */
do_action( 'login_footer' ); ?>
<div class="clear"></div>
</body>
</html>
<?php
}
}
login_footer();
?>
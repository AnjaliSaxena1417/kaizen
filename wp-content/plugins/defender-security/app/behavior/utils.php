<?php
/**
 * Author: Hoang Ngo
 */

namespace WP_Defender\Behavior;

use Hammer\Base\Behavior;
use Hammer\Helper\Log_Helper;
use Hammer\Helper\WP_Helper;
use WP_Defender\Module\Advanced_Tools\Model\Auth_Settings;
use WP_Defender\Module\Hardener\Model\Settings;
use WP_Defender\Module\IP_Lockout\Component\Login_Protection_Api;
use WP_Defender\Module\Scan\Component\Scan_Api;
use WP_Defender\Module\Scan\Model\Result_Item;

class Utils extends Behavior {
	/**
	 * @return bool
	 */
	public function isActivatedSingle() {
		if ( WP_Helper::is_network_activate( wp_defender()->plugin_slug ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @param $endPoint
	 * @param array $bodyArgs
	 * @param array $requestArgs
	 * @param bool $returnRaw
	 *
	 * @return array|mixed|object|\WP_Error
	 */
	public function devCall( $endPoint, $bodyArgs = array(), $requestArgs = array(), $returnRaw = false ) {
		$api_key = $this->getAPIKey();
		if ( $api_key !== false ) {
			$domain                      = network_site_url();
			$post_vars['body']           = $bodyArgs;
			$post_vars['body']['domain'] = $domain;
			$post_vars['timeout']        = 30;
			$post_vars['httpversion']    = '1.1';

			$headers = isset( $post_vars['headers'] ) ? $post_vars['headers'] : array();

			$post_vars['headers'] = array_merge( $headers, array(
				'Authorization' => 'Basic ' . $api_key
			) );

			$post_vars = array_merge( $post_vars, $requestArgs );

			$response = wp_remote_request( $endPoint,
				apply_filters( 'wd_wpmudev_call_request_args',
					$post_vars ) );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			if ( $returnRaw == true ) {
				return $response;
			}

			if (
				'OK' !== wp_remote_retrieve_response_message( $response )
				OR 200 !== wp_remote_retrieve_response_code( $response )
			) {
				return new \WP_Error( wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_response_message( $response ) );
			} else {
				$data = wp_remote_retrieve_body( $response );

				return json_decode( $data, true );
			}
		} else {
			return new \WP_Error( 'dashboard_required',
				sprintf( esc_html__( "WPMU DEV Dashboard will be required for this action. Please visit <a href=\"%s\">here</a> and install the WPMU DEV Dashboard", "defender-security" )
					, 'https://premium.wpmudev.org/project/wpmu-dev-dashboard/' ) );
		}
	}

	/**
	 * @return string
	 */
	public function getPHPVersion() {
		return phpversion();
	}

	/**
	 * @return string
	 */
	public function getWPVersion() {
		global $wp_version;

		return $wp_version;
	}

	public function getAPIKey() {
		if ( ( $version = $this->isDashboardInstalled() ) !== false ) {
			if ( version_compare( $version, '4.0.0' ) >= 0 ) {
				//this is version 4+
				//instanize once
				\WPMUDEV_Dashboard::instance();
				$membeshipStatus = \WPMUDEV_Dashboard::$api->get_membership_data();

				if ( $membeshipStatus['membership'] != 'free' ) {
					return \WPMUDEV_Dashboard::$api->get_key();
				} else {
					return false;
				}
			} else {
				global $wpmudev_un;
				$api_key = $wpmudev_un->get_apikey();

				return $api_key;
			}
		}

		return false;
	}

	/**
	 * Check if WPMUDEV Dashboard installed, return version, else return false
	 * @return bool|string
	 */
	public function isDashboardInstalled() {
		//check if this is new
		if ( class_exists( 'WPMUDEV_Dashboard' ) ) {
			return \WPMUDEV_Dashboard::$version;
		}

		return false;
	}

	/**
	 * Check if current user qualify for current operation
	 * @return bool
	 */
	public function checkPermission() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$cap = is_multisite() ? 'manage_network_options' : 'manage_options';

		return current_user_can( $cap );
	}

	/**
	 * @param $timestring string
	 * @param bool $i18n
	 *
	 * @return false|string
	 */
	public function formatDateTime( $timestring, $i18n = true ) {
		if ( strlen( $timestring ) == 0 ) {
			return null;
		}
		if ( ! filter_var( $timestring, FILTER_VALIDATE_INT ) ) {
			$timestring = strtotime( $timestring );
		}
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
		if ( $i18n == false ) {
			return date( $format, $timestring );
		} else {
			$time = get_date_from_gmt( date( 'Y-m-d H:i:s', $timestring ), 'Y-m-d H:i:s' );

			return date_i18n( $format, strtotime( $time ) );
		}
	}

	/**
	 * @param null $user_id
	 *
	 * @return string
	 */
	public function getDisplayName( $user_id = null ) {
		if ( ! is_user_logged_in() && is_null( $user_id ) ) {
			return esc_html__( "Guest", "defender-security" );
		}

		if ( is_null( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$userdata = get_userdata( $user_id );
		if ( ! is_object( $userdata ) ) {
			return __( "Guest", "defender-security" );
		}

		$fullname = trim( $userdata->first_name . ' ' . $userdata->last_name );
		if ( empty( $fullname ) ) {
			$fullname = $userdata->display_name;
		}

		return $fullname;
	}

	/**
	 * @return array
	 */
	public function allowedHtml() {
		return array(
			'p'      => array(),
			'i'      => array(
				'class' => 'wd-text-warning wdv-icon wdv-icon-fw wdv-icon-exclamation-sign'
			),
			'strong' => array(),
			'span'   => array(
				'class' => array(
					'wd-suspicious-strong',
					'wd-suspicious-light',
					'wd-suspicious-medium'
				)
			),
			'img'    => array(
				'class' => 'text-warning',
				'src'   => wp_defender()->getPluginUrl() . 'assets/img/robot.png'
			)
		);
	}

	/**
	 * @param $get_avatar
	 *
	 * @return mixed
	 */
	public function getAvatarUrl( $get_avatar ) {
		preg_match( "/src='(.*?)'/i", $get_avatar, $matches );

		return $matches[1];
	}

	/**
	 * Make filesize friendy with human, src from http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
	 *
	 * @param $bytes
	 *
	 * @return string
	 *
	 */
	public function makeReadable( $bytes ) {
		if ( $bytes >= 1073741824 ) {
			$bytes = number_format( $bytes / 1073741824, 2 ) . ' GB';
		} elseif ( $bytes >= 1048576 ) {
			$bytes = number_format( $bytes / 1048576, 2 ) . ' MB';
		} elseif ( $bytes >= 1024 ) {
			$bytes = number_format( $bytes / 1024, 2 ) . ' KB';
		} elseif ( $bytes > 1 ) {
			$bytes = $bytes . ' bytes';
		} elseif ( $bytes == 1 ) {
			$bytes = $bytes . ' byte';
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * @param $timestamp
	 *
	 * @return false|int
	 */
	public function localToUtc( $timestring ) {
		$tz = get_option( 'timezone_string' );
		if ( ! $tz ) {
			$gmt_offset = get_option( 'gmt_offset' );
			if ( $gmt_offset == 0 ) {
				return strtotime( $timestring );
			}
			$tz = $this->getTimezoneString( $gmt_offset );
		}
		if ( ! $tz ) {
			$tz = 'UTC';
		}
		$timezone = new \DateTimeZone( $tz );
		$time     = new \DateTime( $timestring, $timezone );

		return $time->getTimestamp();
	}

	/**
	 * @param $time string - format H:i
	 * @param $hook string - hook
	 *
	 * @return false|int
	 */
	public function reportCronTimestamp( $time, $hook ) {
		wp_clear_scheduled_hook( $hook );
		$timeString = date( 'Y-m-d', current_time( 'timestamp' ) ) . ' ' . $time . ':00';
		$timestamp  = $this->localToUtc( $timeString );
		if ( $timestamp > time() ) {
			return $timestamp;
		} else {
			//time is passed, tomorrow
			$timeString = date( 'Y-m-d', strtotime( 'tomorrow', current_time( 'timestamp' ) ) ) . ' ' . $time . ':00';

			return $this->localToUtc( $timeString );
		}
	}

	/**
	 * @param $interval
	 * @param $day
	 * @param $lastReportTime
	 *
	 * @return bool
	 */
	public function isReportTime( $interval, $day, $lastReportTime = false ) {
		if ( $interval == 1 ) {
			//this is daily, always send when interval come
			return true;
		}
		$current_day = strtolower( strftime( '%A', current_time( 'timestamp' ) ) );
		if ( $interval == 7 && $current_day == strtolower( $day ) ) {
			//check the day
			return true;
		} elseif ( $interval == 30
		           && $lastReportTime
		           && strtotime( '+30 days', $lastReportTime ) < time()
		           && $current_day == strtolower( $day )
		) {
			return true;
		}

		return false;
	}

	/**
	 * @param $timezone
	 *
	 * @return false|string
	 */
	public function getTimezoneString( $timezone ) {
		$timezone = explode( '.', $timezone );
		if ( isset( $timezone[1] ) ) {
			$timezone[1] = 30;
		} else {
			$timezone[1] = '00';
		}
		$offset = implode( ':', $timezone );
		list( $hours, $minutes ) = explode( ':', $offset );
		$seconds = $hours * 60 * 60 + $minutes * 60;
		$lc      = localtime( time(), true );
		if ( isset( $lc['tm_isdst'] ) ) {
			$isdst = $lc['tm_isdst'];
		} else {
			$isdst = 0;
		}
		$tz = timezone_name_from_abbr( '', $seconds, $isdst );
		if ( $tz === false ) {
			$tz = timezone_name_from_abbr( '', $seconds, 0 );
		}

		return $tz;
	}

	/**
	 * Get days of week
	 * @return mixed|void
	 * @access public
	 * @since 1.0
	 */
	public function getDaysOfWeek() {
		$timestamp = strtotime( 'next Sunday' );
		$days      = array();
		for ( $i = 0; $i < 7; $i ++ ) {
			$days[]    = strftime( '%A', $timestamp );
			$timestamp = strtotime( '+1 day', $timestamp );
		}

		return apply_filters( 'wd_scan_get_days_of_week', $days );
	}

	/**
	 * Validates that the IP that made the request is from cloudflare
	 *
	 * @param String $ip - the ip to check
	 *
	 * @return bool
	 */
	private function _validateCloudflareIP( $ip ) {
		$cloudflare_ips = array(
			'199.27.128.0/21',
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
		);
		$is_cf_ip       = false;
		foreach ( $cloudflare_ips as $cloudflare_ip ) {
			if ( $this->_cloudflareIpInRange( $ip, $cloudflare_ip ) ) {
				$is_cf_ip = true;
				break;
			}
		}

		return $is_cf_ip;
	}

	/**
	 * Check if the cloudflare IP is in range
	 *
	 * @param String $ip - the current IP
	 * @param String $range - the allowed range of cloudflare ips
	 *
	 * @return bool
	 */
	function _cloudflareIpInRange( $ip, $range ) {
		if ( strpos( $range, '/' ) == false ) {
			$range .= '/32';
		}

		// $range is in IP/CIDR format eg 127.0.0.1/24
		list( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal    = ip2long( $range );
		$ip_decimal       = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal  = ~$wildcard_decimal;

		return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
	}

	/**
	 * Check if there are any cloudflare headers in the request
	 *
	 * @return bool
	 */
	function _cloudflareRequestsCheck() {
		$flag = true;

		if ( ! isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_RAY'] ) ) {
			$flag = false;
		}
		if ( ! isset( $_SERVER['HTTP_CF_VISITOR'] ) ) {
			$flag = false;
		}

		return $flag;
	}

	/**
	 * Check if the request is from cloudflare. If it is, we get the IP
	 *
	 * @return bool
	 */
	function isCloudflare() {
		if ( php_sapi_name() == 'cli' ) {
			return false;
		}
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		if ( isset( $ip ) ) {
			$request_check = $this->_cloudflareRequestsCheck();
			if ( ! $request_check ) {
				return false;
			}

			$ip_check = $this->_validateCloudflareIP( $ip );

			return $ip_check;
		}

		return false;
	}

	/**
	 * A shorhand function to get user IP
	 * @return mixed|string
	 */
	public function getUserIp() {
		$client  = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? $_SERVER['HTTP_CLIENT_IP'] : null;
		$forward = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
		$is_cf   = $this->isCloudflare(); //Check if request is from CloudFlare
		if ( $is_cf ) {
			$cf_ip = $_SERVER['HTTP_CF_CONNECTING_IP']; //We already make sure this is set in the checks
			if ( filter_var( $cf_ip, FILTER_VALIDATE_IP ) ) {
				return apply_filters( 'defender_user_ip', $cf_ip );
			}
		} else {
			$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
		}
		$client_real = isset( $_SERVER['HTTP_X_REAL_IP'] ) ? $_SERVER['HTTP_X_REAL_IP'] : null;
		$ret         = $remote;
		if ( filter_var( $client, FILTER_VALIDATE_IP ) ) {
			$ret = $client;
		} elseif ( filter_var( $client_real, FILTER_VALIDATE_IP ) ) {
			$ret = $client_real;
		} elseif ( ! empty( $forward ) ) {
			$forward = explode( ',', $forward );
			$ip      = array_shift( $forward );
			$ip      = trim( $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				$ret = $ip;
			}
		}

		return apply_filters( 'defender_user_ip', $ret );
	}

	/**
	 * Return times frame for selectbox
	 * @access public
	 * @since 1.0
	 */
	public function getTimes() {
		$data = array();
		for ( $i = 0; $i < 24; $i ++ ) {
			foreach ( apply_filters( 'wd_scan_get_times_interval', array( '00', '30' ) ) as $min ) {
				$time          = $i . ':' . $min;
				$data[ $time ] = apply_filters( 'wd_scan_get_times_hour_min', $time );
			}
		}

		return apply_filters( 'wd_scan_get_times', $data );
	}

	/**
	 * a quick helper for static class
	 * @return Utils
	 */
	public static function instance() {
		return new Utils();
	}

	/**
	 * Determine the server
	 * Incase we are using a hybrid server and need to know where static files are houses, pass true as a param
	 *
	 * @param $useStaticPath - use static path instead of home url. This is the path to Defender changelog
	 */
	public function determineServer( $useStaticPath = false ) {
		$url         = ( $useStaticPath ) ? wp_defender()->getPluginUrl() . 'changelog.txt' : home_url();
		$server_type = get_site_transient( 'wd_util_server' );
		if ( ! is_array( $server_type ) ) {
			$server_type = array();
		}

		if ( isset( $server_type[ $url ] ) && ! empty( $server_type[ $url ] ) ) {
			return strtolower( $server_type[ $url ] );
		}

		//url should be end with php
		global $is_apache, $is_nginx, $is_IIS, $is_iis7;

		$server     = null;
		$ssl_verify = apply_filters( 'defender_ssl_verify', true ); //most hosts dont really have valid ssl or ssl still pending

		if ( $is_nginx ) {
			$server = 'nginx';
		} elseif ( $is_apache ) {
			//case the url is detecting php file
			if ( pathinfo( $url, PATHINFO_EXTENSION ) == 'php' ) {
				$server = 'apache';
			} else {
				//so the server software is apache, let see what the header return
				$request = wp_remote_head( $url, array(
					'user-agent' => $_SERVER['HTTP_USER_AGENT'],
					'sslverify'  => $ssl_verify
				) );
				$server  = wp_remote_retrieve_header( $request, 'server' );
				$server  = explode( '/', $server );
				if ( strtolower( $server[0] ) == 'nginx' ) {
					//proxy case
					$server = 'nginx';
				} else {
					$server = 'apache';
				}
			}
		} elseif ( $is_iis7 || $is_IIS ) {
			$server = 'iis';
		}

		if ( is_null( $server ) ) {
			//if fall in here, means there is st unknowed.
			$request = wp_remote_head( $url, array(
				'user-agent' => $_SERVER['HTTP_USER_AGENT'],
				'sslverify'  => $ssl_verify
			) );
			$server  = wp_remote_retrieve_header( $request, 'server' );
			$server  = explode( '/', $server );
			$server  = $server[0];
		}

		$server_type[ $url ] = $server;
		//cache for an hour
		set_site_transient( 'wd_util_server', $server_type, 3600 );

		return $server;
	}

	/**
	 * Determine the Apache version
	 * Most web servers have apache_get_version disabled, so we just get a simple curl of the headers
	 *
	 * @return String
	 */
	public function determineApacheVersion() {
		if ( ! function_exists( 'apache_get_version' ) ) {
			$version        = '2.2'; //default supported is 2.2
			$url            = home_url();
			$apache_version = get_site_transient( 'wd_util_apache_version' );
			if ( ! is_array( $apache_version ) ) {
				$apache_version = array();
			}

			if ( isset( $apache_version[ $url ] ) && ! empty( $apache_version[ $url ] ) ) {
				return strtolower( $apache_version[ $url ] );
			}

			$apache_version[ $url ] = $version; //default is 2.2

			if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
				$server = explode( " ", $_SERVER['SERVER_SOFTWARE'] );
				if ( is_array( $server ) && count( $server ) > 1 ) {
					$server = $server[0];
					$server = explode( "/", $server );
					if ( is_array( $server ) && count( $server ) > 1 ) {
						$version                = $server[1];
						$apache_version[ $url ] = $version;
					}
				}
			}

			set_site_transient( 'wd_util_apache_version', $apache_version, 3600 );
		} else {
			$version = apache_get_version();
			$version = explode( '/', $version );
			$version = $version[1];
		}

		return $version;
	}

	/**
	 * @return string
	 */
	public function getDefUploadDir() {
		$uploadDir = WP_Helper::getUploadDir();
		$defDir    = $uploadDir . DIRECTORY_SEPARATOR . 'wp-defender';
		if ( ! is_dir( $defDir ) ) {
			wp_mkdir_p( $defDir );
		}

		if ( ! is_file( $defDir . DIRECTORY_SEPARATOR . 'index.php' ) ) {
			//create a blank index file
			file_put_contents( $defDir . DIRECTORY_SEPARATOR . 'index.php', '' );
		}

		return $defDir;
	}

	/**
	 * @return array|void
	 */
	public function submitStatsToDev() {
		return false;
	}

	/**
	 * @param null $result
	 *
	 * @return bool
	 */
	public function checkRequirement( &$result = null ) {
		$meet = true;

		if ( version_compare( $this->getPHPVersion(), '5.6', '>=' ) == false ) {
			$meet          = false;
			$result['php'] = array(
				'status'  => $this->getPHPVersion(),
				'message' => __( "Please upgrade to 5.6 or later", "defender-security" )
			);
		}

		return $meet;
	}

	/**
	 * @return string|null
	 */
	public function getSummaryClass() {
		return null;
	}


	/**
	 * @param $freq
	 *
	 * @return string
	 */
	public function frequencyToText( $freq ) {
		$text = '';
		switch ( $freq ) {
			case 1:
				$text = __( "daily", "defender-security" );
				break;
			case 7:
				$text = __( "weekly", "defender-security" );
				break;
			case 30:
				$text = __( "monthly", "defender-security" );
				break;
		}

		return $text;
	}


	/**
	 * List server types
	 *
	 * @return Array
	 */
	public function serverTypes() {
		return apply_filters( 'defender_server_types', array(
			'apache'    => 'Apache',
			'litespeed' => 'LiteSpeed',
			'nginx'     => 'NGINX',
			'iis'       => 'IIS',
			'iis-7'     => 'IIS 7'
		) );
	}

	/**
	 * Get the current page URL
	 *
	 * @return String
	 */
	public function currentPageURL() {
		$protocol = "http";
		if ( is_ssl() ) {
			$protocol .= "s";
		}
		$url = "$protocol://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}

		return apply_filters( 'defender_current_page_url', $url );
	}

	/**
	 * site url with correct sheme
	 *
	 * @return string
	 */
	public function siteURLWithScheme() {
		$current_scheme = ( is_ssl() ) ? 'https' : 'http';

		return network_site_url( '', $current_scheme );
	}

	/**
	 * @param $campaign
	 *
	 * @return string
	 */
	public function campaignURL( $campaign ) {
		$url = "https://premium.wpmudev.org/project/wp-defender/?utm_source=defender&utm_medium=plugin&utm_campaign=" . $campaign;

		return $url;
	}

	/**
	 * @return string
	 */
	public function maybeHighContrast() {
		return \WP_Defender\Module\Setting\Model\Settings::instance()->high_contrast_mode == 0 ? '' : 'sui-color-accessible';
	}


	/**
	 * Copy the list from here
	 * https://gist.github.com/DHS/1340150
	 * @return array
	 */
	public function countriesList() {
		$country_array = array(
			"AF" => "Afghanistan",
			"AL" => "Albania",
			"DZ" => "Algeria",
			"AS" => "American Samoa",
			"AD" => "Andorra",
			"AO" => "Angola",
			"AI" => "Anguilla",
			"AQ" => "Antarctica",
			"AG" => "Antigua and Barbuda",
			"AR" => "Argentina",
			"AM" => "Armenia",
			"AW" => "Aruba",
			"AU" => "Australia",
			"AT" => "Austria",
			"AZ" => "Azerbaijan",
			"BS" => "Bahamas",
			"BH" => "Bahrain",
			"BD" => "Bangladesh",
			"BB" => "Barbados",
			"BY" => "Belarus",
			"BE" => "Belgium",
			"BZ" => "Belize",
			"BJ" => "Benin",
			"BM" => "Bermuda",
			"BT" => "Bhutan",
			"BO" => "Bolivia",
			"BA" => "Bosnia and Herzegovina",
			"BW" => "Botswana",
			"BV" => "Bouvet Island",
			"BR" => "Brazil",
			"BQ" => "British Antarctic Territory",
			"IO" => "British Indian Ocean Territory",
			"VG" => "British Virgin Islands",
			"BN" => "Brunei",
			"BG" => "Bulgaria",
			"BF" => "Burkina Faso",
			"BI" => "Burundi",
			"KH" => "Cambodia",
			"CM" => "Cameroon",
			"CA" => "Canada",
			"CT" => "Canton and Enderbury Islands",
			"CV" => "Cape Verde",
			"KY" => "Cayman Islands",
			"CF" => "Central African Republic",
			"TD" => "Chad",
			"CL" => "Chile",
			"CN" => "China",
			"CX" => "Christmas Island",
			"CC" => "Cocos [Keeling] Islands",
			"CO" => "Colombia",
			"KM" => "Comoros",
			"CG" => "Congo - Brazzaville",
			"CD" => "Congo - Kinshasa",
			"CK" => "Cook Islands",
			"CR" => "Costa Rica",
			"HR" => "Croatia",
			"CU" => "Cuba",
			"CY" => "Cyprus",
			"CZ" => "Czech Republic",
			"CI" => "Côte d’Ivoire",
			"DK" => "Denmark",
			"DJ" => "Djibouti",
			"DM" => "Dominica",
			"DO" => "Dominican Republic",
			"NQ" => "Dronning Maud Land",
			"DD" => "East Germany",
			"EC" => "Ecuador",
			"EG" => "Egypt",
			"SV" => "El Salvador",
			"GQ" => "Equatorial Guinea",
			"ER" => "Eritrea",
			"EE" => "Estonia",
			"ET" => "Ethiopia",
			"FK" => "Falkland Islands",
			"FO" => "Faroe Islands",
			"FJ" => "Fiji",
			"FI" => "Finland",
			"FR" => "France",
			"GF" => "French Guiana",
			"PF" => "French Polynesia",
			"TF" => "French Southern Territories",
			"FQ" => "French Southern and Antarctic Territories",
			"GA" => "Gabon",
			"GM" => "Gambia",
			"GE" => "Georgia",
			"DE" => "Germany",
			"GH" => "Ghana",
			"GI" => "Gibraltar",
			"GR" => "Greece",
			"GL" => "Greenland",
			"GD" => "Grenada",
			"GP" => "Guadeloupe",
			"GU" => "Guam",
			"GT" => "Guatemala",
			"GG" => "Guernsey",
			"GN" => "Guinea",
			"GW" => "Guinea-Bissau",
			"GY" => "Guyana",
			"HT" => "Haiti",
			"HM" => "Heard Island and McDonald Islands",
			"HN" => "Honduras",
			"HK" => "Hong Kong SAR China",
			"HU" => "Hungary",
			"IS" => "Iceland",
			"IN" => "India",
			"ID" => "Indonesia",
			"IR" => "Iran",
			"IQ" => "Iraq",
			"IE" => "Ireland",
			"IM" => "Isle of Man",
			"IL" => "Israel",
			"IT" => "Italy",
			"JM" => "Jamaica",
			"JP" => "Japan",
			"JE" => "Jersey",
			"JT" => "Johnston Island",
			"JO" => "Jordan",
			"KZ" => "Kazakhstan",
			"KE" => "Kenya",
			"KI" => "Kiribati",
			"KW" => "Kuwait",
			"KG" => "Kyrgyzstan",
			"LA" => "Laos",
			"LV" => "Latvia",
			"LB" => "Lebanon",
			"LS" => "Lesotho",
			"LR" => "Liberia",
			"LY" => "Libya",
			"LI" => "Liechtenstein",
			"LT" => "Lithuania",
			"LU" => "Luxembourg",
			"MO" => "Macau SAR China",
			"MK" => "Macedonia",
			"MG" => "Madagascar",
			"MW" => "Malawi",
			"MY" => "Malaysia",
			"MV" => "Maldives",
			"ML" => "Mali",
			"MT" => "Malta",
			"MH" => "Marshall Islands",
			"MQ" => "Martinique",
			"MR" => "Mauritania",
			"MU" => "Mauritius",
			"YT" => "Mayotte",
			"FX" => "Metropolitan France",
			"MX" => "Mexico",
			"FM" => "Micronesia",
			"MI" => "Midway Islands",
			"MD" => "Moldova",
			"MC" => "Monaco",
			"MN" => "Mongolia",
			"ME" => "Montenegro",
			"MS" => "Montserrat",
			"MA" => "Morocco",
			"MZ" => "Mozambique",
			"MM" => "Myanmar [Burma]",
			"NA" => "Namibia",
			"NR" => "Nauru",
			"NP" => "Nepal",
			"NL" => "Netherlands",
			"AN" => "Netherlands Antilles",
			"NT" => "Neutral Zone",
			"NC" => "New Caledonia",
			"NZ" => "New Zealand",
			"NI" => "Nicaragua",
			"NE" => "Niger",
			"NG" => "Nigeria",
			"NU" => "Niue",
			"NF" => "Norfolk Island",
			"KP" => "North Korea",
			"MP" => "Northern Mariana Islands",
			"NO" => "Norway",
			"OM" => "Oman",
			"PC" => "Pacific Islands Trust Territory",
			"PK" => "Pakistan",
			"PW" => "Palau",
			"PS" => "Palestinian Territories",
			"PA" => "Panama",
			"PZ" => "Panama Canal Zone",
			"PG" => "Papua New Guinea",
			"PY" => "Paraguay",
			"YD" => "People's Democratic Republic of Yemen",
			"PE" => "Peru",
			"PH" => "Philippines",
			"PN" => "Pitcairn Islands",
			"PL" => "Poland",
			"PT" => "Portugal",
			"PR" => "Puerto Rico",
			"QA" => "Qatar",
			"RO" => "Romania",
			"RU" => "Russia",
			"RW" => "Rwanda",
			"RE" => "Réunion",
			"BL" => "Saint Barthélemy",
			"SH" => "Saint Helena",
			"KN" => "Saint Kitts and Nevis",
			"LC" => "Saint Lucia",
			"MF" => "Saint Martin",
			"PM" => "Saint Pierre and Miquelon",
			"VC" => "Saint Vincent and the Grenadines",
			"WS" => "Samoa",
			"SM" => "San Marino",
			"SA" => "Saudi Arabia",
			"SN" => "Senegal",
			"RS" => "Serbia",
			"CS" => "Serbia and Montenegro",
			"SC" => "Seychelles",
			"SL" => "Sierra Leone",
			"SG" => "Singapore",
			"SK" => "Slovakia",
			"SI" => "Slovenia",
			"SB" => "Solomon Islands",
			"SO" => "Somalia",
			"ZA" => "South Africa",
			"GS" => "South Georgia and the South Sandwich Islands",
			"KR" => "South Korea",
			"ES" => "Spain",
			"LK" => "Sri Lanka",
			"SD" => "Sudan",
			"SR" => "Suriname",
			"SJ" => "Svalbard and Jan Mayen",
			"SZ" => "Swaziland",
			"SE" => "Sweden",
			"CH" => "Switzerland",
			"SY" => "Syria",
			"ST" => "São Tomé and Príncipe",
			"TW" => "Taiwan",
			"TJ" => "Tajikistan",
			"TZ" => "Tanzania",
			"TH" => "Thailand",
			"TL" => "Timor-Leste",
			"TG" => "Togo",
			"TK" => "Tokelau",
			"TO" => "Tonga",
			"TT" => "Trinidad and Tobago",
			"TN" => "Tunisia",
			"TR" => "Turkey",
			"TM" => "Turkmenistan",
			"TC" => "Turks and Caicos Islands",
			"TV" => "Tuvalu",
			"UM" => "U.S. Minor Outlying Islands",
			"PU" => "U.S. Miscellaneous Pacific Islands",
			"VI" => "U.S. Virgin Islands",
			"UG" => "Uganda",
			"UA" => "Ukraine",
			"SU" => "Union of Soviet Socialist Republics",
			"AE" => "United Arab Emirates",
			"GB" => "United Kingdom",
			"US" => "United States",
			"ZZ" => "Unknown or Invalid Region",
			"UY" => "Uruguay",
			"UZ" => "Uzbekistan",
			"VU" => "Vanuatu",
			"VA" => "Vatican City",
			"VE" => "Venezuela",
			"VN" => "Vietnam",
			"WK" => "Wake Island",
			"WF" => "Wallis and Futuna",
			"EH" => "Western Sahara",
			"YE" => "Yemen",
			"ZM" => "Zambia",
			"ZW" => "Zimbabwe",
			"AX" => "Åland Islands",
		);

		return $country_array;
	}
}
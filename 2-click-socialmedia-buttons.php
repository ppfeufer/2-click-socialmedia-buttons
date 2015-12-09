<?php
/**
 * Plugin Name: 2 Click Social Media Buttons
 * Plugin URI: http://ppfeufer.de/wordpress-plugin/2-click-social-media-buttons/
 * Description: Adding buttons for Facebook (Like/Recommend), Twitter, Google+, Flattr, Xing, Pinteres, t3n and LinkedIn to your WordPress-Website in respect with the german privacy law.
 * Version: 1.6.5
 * Author: H.-Peter Pfeufer
 * Author URI: http://ppfeufer.de
 * Text Domain: twoclick-socialmedia
 * Domain Path: /l10n
 */

namespace PPWP\Plugin\TwoClickSocialMedia;

use PPWP\Plugin\TwoClickSocialMedia\Backend\Twoclick_Social_Media_Buttons_Backend;
use PPWP\Plugin\TwoClickSocialMedia\Frontend\Twoclick_Social_Media_Buttons_Frontend;

/**
 * Avoid direct calls to this file
 *
 * @since 1.0
 * @author ppfeufer
 *
 * @package 2 Click Social Media Buttons
 */
if(!function_exists('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');

	exit();
} // END if(!function_exists('add_action'))

/**
 * Konstanten
 */
define('TWOCLICK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TWOCLICK_PLUGIN_URI', plugin_dir_url(__FILE__));
define('TWOCLICK_BASENAME', plugin_basename(__FILE__));
define('TWOCLICK_TEXTDOMAIN', 'twoclick-socialmedia');
define('TWOCLICK_L10N_DIR', dirname(plugin_basename( __FILE__ )) . '/l10n/');
define('TWOCLICK_WORDPRESS_REQUIERED', '3.9');
define('TWOCLICK_JQUERY_REQUIERED', '1.7');

/**
 * Check if we have the right WordPress Version at minimun.
 *
 * Minimum Requirements:
 * 		WordPress 3.9
 *
 * @since 1.5
 * @author ppfeufer
 */
if(version_compare($GLOBALS['wp_version'], TWOCLICK_WORDPRESS_REQUIERED, '<')) {
	return false;
} // END if(version_compare($GLOBALS['wp_version'], TWOCLICK_WORDPRESS_REQUIERED, '<'))

/**
 * Loading libs used in backend
 *
 * @since 1.0
 * @author ppfeufer
 *
 * @package 2 Click Social Media Buttons
 */
if(is_admin()) {
	require_once(TWOCLICK_PLUGIN_DIR . 'libs/class-twoclick-backend.php');

	new Twoclick_Social_Media_Buttons_Backend();
} // END if(is_admin())

/**
 * Loading libs used in frontend
 *
 * @since 1.0
 * @author ppfeufer
 *
 * @package 2 Click Social Media Buttons
 */
if(!is_admin()) {
	require_once(TWOCLICK_PLUGIN_DIR . 'libs/class-twoclick-frontend.php');

	/**
	 * Frontendklasse starten
	 */
	$obj_TwoclickFrontend = new Twoclick_Social_Media_Buttons_Frontend();

	/**
	 * Template-Tag
	 *
	 * Bindet die Buttons via Funktionsaufruf direkt im Template ein.
	 *
	 * Einbindung:
	 * 		<?php if(function_exists('get_twoclick_buttons')) {get_twoclick_buttons(get_the_ID());} ?>
	 *
	 * @since 0.18
	 * @author ppfeufer
	 *
	 * @param int $var_iId
	 */
	function get_twoclick_buttons($var_sPostID = null) {
		if($var_sPostID == '') {
			$var_sPostID = get_the_ID();
		} // END if($var_sPostID == '')

		if(!empty($var_sPostID)) {
			global $obj_TwoclickFrontend;

			echo $obj_TwoclickFrontend->generate_html($var_sPostID);
		} else {
			return false;
		} // END if(!empty($var_iId))
	} // END function get_twoclick_buttons($var_iId = null)
} // END if(!is_admin())
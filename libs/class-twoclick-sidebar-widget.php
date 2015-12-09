<?php
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

if(!class_exists('Twoclick_Social_Media_Buttons_Sidebar_Widget')) {
	class Twoclick_Social_Media_Buttons_Sidebar_Widget extends WP_Widget {
		private $var_sOptionsName = 'twoclick_buttons_settings';
		private $array_TwoclickButtonsOptions;

		/**
		 * Konstruktorfunktion (PHP5).
		 *
		 * @param array $widget_options Optional Passed to wp_register_sidebar_widget()
		 *	 - description: shown on the configuration page
		 *	 - classname
		 * @param array $control_options Optional Passed to wp_register_widget_control()
		 *	 - width: required if more than 250px
		 *	 - height: currently not used but may be needed in the future
		 */
		public function __construct() {
			$this->array_TwoclickButtonsOptions = get_option($this->var_sOptionsName);

			/**
			 * Übersetzungsfunktion für das Widget aktivieren.
			 * Die Sprachdateien liegen im Ordner "l10n" innerhalb des Widgets.
			 */
			if(function_exists('load_plugin_textdomain')) {
				load_plugin_textdomain(TWOCLICK_TEXTDOMAIN, false, TWOCLICK_L10N_DIR);
			}

			$widget_options = array(
				'classname' => 'twoclick_sidebar_widget',
				'description' => __('The Sidebar Widget for the 2-Click Social Media Buttons. Showing a widget with the buttons on single posts and pages.', TWOCLICK_TEXTDOMAIN)
			);

			$control_options = array();

			$this->WP_Widget('twoclick_sidebar_widget', __('2-Click Social Media Buttons', TWOCLICK_TEXTDOMAIN), $widget_options, $control_options);
		} // END function __construct()

		/**
		 * Widgetformular.
		 * Der Einstellungsbereich des Widgets.
		 *
		 * @see WP_Widget::form()
		 */
		public function form($instance) {
			/**
			 * Standardwerte
			 *
			 * @var array
			 */
			$instance = wp_parse_args((array) $instance, array(
				'twoclick-widget-title' => ''
			));

			// Titel
			echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . __('Title', TWOCLICK_TEXTDOMAIN) . '</strong></p>';
			echo '<p><input id="' . $this->get_field_id('twoclick-widget-title') . '" name="' . $this->get_field_name('twoclick-widget-title') . '" type="text" value="' . $instance['twoclick-widget-title'] . '" /></p>';
			echo '<p style="clear:both;"></p>';

			// Description
			echo '<p>' . sprintf(__('This widget doesn\'t need any other settings. It respect all settings you\'ve made for the plugin in the %1$s.', TWOCLICK_TEXTDOMAIN), '<a href="' . admin_url('options-general.php?page=twoclick_buttons') . '">' . __('settings page', TWOCLICK_TEXTDOMAIN) . '</a>') . '</p>';
			echo '<p style="clear:both;"></p>';
		} // END function form($instance)

		/**
		 * Widgeteinstellungen in die Datenbank schreiben.
		 *
		 * @see WP_Widget::update()
		 */
		public function update($new_instance, $old_instance) {
			$instance = $old_instance;

			/**
			 * Standrdwerte setzen
			 *
			 * @var array
			 */
			$new_instance = wp_parse_args((array) $new_instance, array(
				'twoclick-widget-title' => '',
			));

			/**
			 * Einstellungen, welche über das Formular kommen auf ihre Richtigkeit hin prüfen.
			 * Somit wird sicher gestellt, dass kein Schadcode eingeschleust werden kann.
			 *
			 * @var array
			*/
			$instance['twoclick-widget-title'] = (string) strip_tags($new_instance['twoclick-widget-title']);

			/**
			 * Array mit den Einstellungen an die verarbeitende Funktion zurückliefern.
			 * Diese liegt in der Klasse WP_Widget und speichert nun die Optionen
			 * in der Datenbank.
			*/
			return $instance;
		} // END function update($new_instance, $old_instance)

		/**
		 * Ausgabe des Widgets im Frontend.
		 *
		 * @see WP_Widget::widget()
		 */
		public function widget($args, $instance) {
			if(is_singular()) {
				$var_sPostId = get_the_ID();

				if((is_array($this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'])) && (array_key_exists($var_sPostId, $this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'])) && ($this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'][$var_sPostId] == true)) {
					return false;
				} // END if((is_array($this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'])) && (array_key_exists($var_sPostId, $this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'])) && ($this->array_TwoclickButtonsOptions['twoclick_buttons_exclude_page'][$var_sPostId] == true))

				extract($args);

				echo $before_widget;

				$title = (empty($instance['twoclick-widget-title'])) ? '' : apply_filters('twoclick_widget_title', $instance['twoclick-widget-title']);

				if(!empty($title)) {
					echo $before_title . $title . $after_title;
				} // END if(!empty($title))

				echo $this->_html_output($instance);
				echo $after_widget;
			} else {
				return false;
			} // END if(is_singular())
		} // END function widget($args, $instance)

		/**
		 * HTML des Widgets
		 *
		 * @param array $args
		 */
		private function _html_output($args = array()) {
			/**
			 * Widgetausgabe
			 * Hier wird nun das HTML für das Widget erstellt
			 */

			$var_sWidetHTML = 'Hallo';

			return $var_sWidetHTML;
		} // private function my_widget_html_output($args = array())

		/**
		 * JavaScript für Ausgabe generieren.
		 *
		 * @since 0.4
		 * @author ppfeufer
		 */
		function _get_js($var_sPostID = '') {
			if(!is_admin()) {
				if(empty($this->var_sPostExcerpt)) {
					$this->var_sPostExcerpt = rawurlencode($this->_get_post_excerpt(get_the_content(), 400));
				} // END if(empty($this->var_sPostExcerpt))

				if(!empty($var_sPostID)) {
					$var_sPostID = get_the_ID();
				} // END if(!empty($var_sPostID))

				$var_sTitle = rawurlencode(get_the_title($var_sPostID));
				$var_sTweettext = rawurlencode($this->_get_tweettext());
				$var_sArticleImage = $this->_get_article_image();

				$var_sShowFacebook = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_facebook']) ? 'on' : 'off';
				$var_sShowFacebookPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_facebook_perm']) ? 'on' : 'off';
				$var_sShowTwitter = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_twitter']) ? 'on' : 'off';
				$var_sShowFlattr = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_flattr']) ? 'on' : 'off';
				$var_sShowXing = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_xing']) ? 'on' : 'off';
				$var_sShowPinterest = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_pinterest'] && $var_sArticleImage != false) ? 'on' : 'off';

				$var_sShowTwitterPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_twitter_perm']) ? 'on' : 'off';
				$var_sShowGoogleplus = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_googleplus']) ? 'on' : 'off';
				$var_sShowGoogleplusPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_googleplus_perm']) ? 'on' : 'off';
				$var_sShowFlattrPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_flattr_perm']) ? 'on' : 'off';
				$var_sShowXingPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_xing_perm']) ? 'on' : 'off';
				$var_sShowPinterestPerm = ($this->array_TwoclickButtonsOptions['twoclick_buttons_display_pinterest_perm']) ? 'on' : 'off';

				$var_sCss = plugins_url(basename(dirname(__FILE__)) . '/css/socialshareprivacy.css');
					// 				$var_sXingLib = plugins_url(basename(dirname(__FILE__)) . '/libs/helper-button-xing.php');
				$var_sXingLib = plugin_dir_url(__FILE__) . 'helper-button-xing.php';
				// 				$var_sPinterestLib = plugins_url(basename(dirname(__FILE__)) . '/libs/helper-button-pinterest.php');
				$var_sPinterestLib = plugin_dir_url(__FILE__) . 'helper-button-pinterest.php';

				/**
				 * Settings for singular
				 */
				if(!is_singular()) {
					$var_sShowFacebookPerm = 'off';
					$var_sShowTwitterPerm = 'off';
					$var_sShowGoogleplusPerm = 'off';
					$var_sShowFlattrPerm = 'off';
					$var_sShowXingPerm = 'off';
					$var_sShowPinterestPerm = 'off';
				} // END if(!is_singular())

				/**
				 * Link zusammenbauen, auch wenn Optionen übergeben werden.
				 *
				 * @since 0.16
				 */
				if(isset($_GET) && count($_GET) != '0') {
					$var_sPermalink = (isset($_SERVER['HTTPS'])?'https':'http').'://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				} else {
					$var_sPermalink = get_permalink($var_sPostID);
				} // END if(isset($_GET) && count($_GET) != '0')

				/**
				 * Infotexte erstellen
				 */
				$var_sInfotextFacebook = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Facebook senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_facebook'])) {
					$var_sInfotextFacebook = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_facebook'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_facebook']))

				$var_sInfotextTwitter = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Twitter senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_twitter'])) {
					$var_sInfotextTwitter = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_twitter'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_twitter']))

				$var_sInfotextGoogleplus = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Google+ senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_googleplus'])) {
					$var_sInfotextGoogleplus = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_googleplus'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_googleplus']))

				$var_sInfotextFlattr = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Flattr senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_flattr'])) {
					$var_sInfotextFlattr = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_flattr'];
				} // END f(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_flattr']))

				$var_sInfotextXing = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Xing senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_xing'])) {
					$var_sInfotextXing = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_xing'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_xing']))

				$var_sInfotextPinterest = '2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können Ihre Empfehlung an Pinterest senden. Schon beim Aktivieren werden Daten an Dritte übertragen - siehe <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_pinterest'])) {
					$var_sInfotextPinterest = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_pinterest'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_pinterest']))

				$var_sInfotextInfobutton = 'Wenn Sie diese Felder durch einen Klick aktivieren, werden Informationen an Facebook, Twitter, Flattr oder Google ins Ausland übertragen und unter Umständen auch dort gespeichert. Näheres erfahren Sie durch einen Klick auf das <em>i</em>.';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_infobutton'])) {
					$var_sInfotextInfobutton = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_infobutton'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_infobutton']))

				$var_sInfotextPermaoption = 'Dauerhaft aktivieren und Datenüber-tragung zustimmen:';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_permaoption'])) {
					$var_sInfotextPermaoption = $this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_permaoption'];
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infotext_permaoption']))

				$var_sInfolink = 'http://www.heise.de/ct/artikel/2-Klicks-fuer-mehr-Datenschutz-1333879.html';
				if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infolink'])) {
					$var_sInfolink = trim($this->array_TwoclickButtonsOptions['twoclick_buttons_infolink']);
				} // END if(!empty($this->array_TwoclickButtonsOptions['twoclick_buttons_infolink']))

				// Dummybilder holen.
				$array_DummyImages = $this->_get_dummy_images(get_locale());

				/**
				 * Sprache für Xing und Twitter
				 * Diese nutzen leider keine Lingua-Codes :-(
				*/
				$var_sButtonLanguage = 'de';
				if(get_locale() != 'de_DE') {
					$var_sButtonLanguage = 'en';
				} // END if(get_locale() != 'de_DE')

				$var_sFacebookAction = ($this->array_TwoclickButtonsOptions['twoclick_buttons_facebook_action']) ? $this->array_TwoclickButtonsOptions['twoclick_buttons_facebook_action'] : 'recommend';

				$array_ButtonData = array(
					'services' => array(
						'facebook' => array(
							'dummy_img' => $array_DummyImages['facebook-' . $var_sFacebookAction]['image'],
							'dummy_img_width' => $array_DummyImages['facebook-' . $var_sFacebookAction]['width'],
							'dummy_img_height' => '20',
							'status' => $var_sShowFacebook,
							'txt_info' => $var_sInfotextFacebook,
							'perma_option' => $var_sShowFacebookPerm,
							'action' => $this->array_TwoclickButtonsOptions['twoclick_buttons_facebook_action'],
							'language' => get_locale()
						),
						'twitter' => array(
							'reply_to' => $this->array_TwoclickButtonsOptions['twoclick_buttons_twitter_reply'],
							'dummy_img' => $array_DummyImages['twitter']['image'],
							'dummy_img_width' => $array_DummyImages['twitter']['width'],
							'dummy_img_height' => '20',
							'tweet_text' => rawurlencode($this->_get_tweettext()),
							'status' => $var_sShowTwitter,
							'txt_info' => $var_sInfotextTwitter,
							'perma_option' => $var_sShowTwitterPerm,
							'language' => $var_sButtonLanguage
						),
						'gplus' => array(
							'dummy_img' => $array_DummyImages['googleplus']['image'],
							'dummy_img_width' => $array_DummyImages['googleplus']['width'],
							'dummy_img_height' => '20',
							'status' => $var_sShowGoogleplus,
							'txt_info' => $var_sInfotextGoogleplus,
							'perma_option' => $var_sShowGoogleplusPerm
						),
						'flattr' => array(
							'uid' => $this->array_TwoclickButtonsOptions['twoclick_buttons_flattr_uid'],
							'dummy_img' => $array_DummyImages['flattr']['image'],
							'dummy_img_width' => $array_DummyImages['flattr']['width'],
							'dummy_img_height' => '20',
							'status' => $var_sShowFlattr,
							'the_title' => $var_sTitle,
							'the_excerpt' => $this->var_sPostExcerpt,
							'txt_info' => $var_sInfotextFlattr,
							'perma_option' => $var_sShowFlattrPerm
						),
						'xing' => array(
							'dummy_img' => $array_DummyImages['xing']['image'],
							'dummy_img_width' => $array_DummyImages['xing']['width'],
							'dummy_img_height' => '20',
							'status' => $var_sShowXing,
							'txt_info' => $var_sInfotextXing,
							'perma_option' => $var_sShowXingPerm,
							'language' => $var_sButtonLanguage,
							'xing_lib' => $var_sXingLib
						),
						'pinterest' => array(
							'dummy_img' => $array_DummyImages['pinterest']['image'],
							'dummy_img_width' => $array_DummyImages['pinterest']['width'],
							'dummy_img_height' => '20',
							'status' => $var_sShowPinterest,
							'the_excerpt' => $this->_get_pinterest_description(),
							'txt_info' => $var_sInfotextPinterest,
							'perma_option' => $var_sShowPinterestPerm,
							'pinterest_lib' => $var_sPinterestLib,
							'media' => $var_sArticleImage
						)
					),
					'txt_help' => $var_sInfotextInfobutton,
					'settings_perma' => $var_sInfotextPermaoption,
					'info_link' => $var_sInfolink,
					'css_path' => apply_filters('twoclick-css', $var_sCss),
					'uri' => esc_url($var_sPermalink)
				);

				$var_sJavaScript = '/* <![CDATA[ */' . "\n" . '// WP-Language = ' . get_locale() . "\n" . 'jQuery(document).ready(function($){if($(\'.twoclick_social_bookmarks_post_' . $var_sPostID . '\')){$(\'.twoclick_social_bookmarks_post_' . $var_sPostID . '\').socialSharePrivacy(' . json_encode($array_ButtonData) . ');}});' . "\n" . '/* ]]> */';

				return '<div class="twoclick_social_bookmarks_post_' . $var_sPostID . ' social_share_privacy clearfix"></div><script type="text/javascript">' . $var_sJavaScript . '</script>';
			} // END if(!is_admin())
		} // END function _get_js($var_sPostID = '')
	}

	/**
	 * Widget initialisieren.
	 */
	add_action('widgets_init', create_function('', 'return register_widget("Twoclick_Social_Media_Buttons_Sidebar_Widget");'));
}
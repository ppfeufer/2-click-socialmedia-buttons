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

/**
 * Setze die OpenGraph-Tags
 *
 * @since 1.0
 * @author ppfeufer
 * @author shaselboeck
 *
 * @package 2 Click Social Media Buttons
 */
if(!class_exists('Twoclick_Social_Media_Buttons_OpenGraph')) {
	class Twoclick_Social_Media_Buttons_OpenGraph {
		/**
		 * Nimmt die Daten der verschiedenen Meta-Elemente auf, um diese gebündelt
		 * in einer Funktion ausgeben zu können.
		 *
		 * @var array
		 */
		protected $_metas = array();

		/**
		 * Konstruktor
		 */
		public function __construct() {
			if(!is_admin()) {
				add_action('wp_head', array(
					$this,
					'add_elements'
				));
			} // END if(!is_admin())
		} // END public function __construct()

		/**
		 * Erzeugt die relevanten Meta-Elemente für die Seite / den Artikel
		 */
		public function add_elements() {
			if(is_singular()) {
				the_post();

				echo $this->_metas['og:title'] = $this->_get_title();
				$this->_metas['og:type'] = is_single() ? 'article' : 'website';
				$this->_metas['og:url'] = get_permalink();

				$this->_metas['og:description'] = $this->_get_description();
				$this->_metas['og:site_name'] = strip_tags(get_bloginfo('name'));
				$this->_metas['og:locale'] = strtolower(str_replace('-', '_', get_bloginfo('language')));

				$this->_add_image();
				$this->_add_post_tags();

				$this->_output();

				rewind_posts();
			} // END if(is_singular())
		} // END public function add_elements()

		/**
		 * Gibt den Title für das Meta-Element zurück. Wenn der Title via wpSEO
		 * oder All in One SEO Pack gesetzt worden ist, wird dieser bevorzugt
		 *
		 * @return null|string
		 */
		protected function _get_title() {
			$title = null;

			// Title durch wpSEO
			if(class_exists('wpSEO_Base')) {
				$title = trim(get_post_meta(get_the_ID(), '_wpseo_edit_title', true));
			} // END if(class_exists('wpSEO_Base'))

			// Title durch All in One SEO Pack
			if(function_exists('aiosp_meta')) {
				$title = trim(get_post_meta(get_the_ID(), '_aioseop_title', true));
			} // END if(function_exists('aiosp_meta'))

			return empty($title) ? get_the_title() : $title;
		} // END protected function _get_title()

		/**
		 * Gibt die Description für das Meta-Element zurück. Wenn die Description
		 * via wpSEO oder All in One SEO Pack gesetzt worden ist, wird diese bevorzugt
		 *
		 * @return mixed|string
		 */
		protected function _get_description() {
			$description = null;

			// Beschreibung durch wpSEO
			if(class_exists('wpSEO_Base')) {
				$description = trim(get_post_meta(get_the_ID(), '_wpseo_edit_description', true));
			} // END if(class_exists('wpSEO_Base'))

			// Bescheibung durch All in One SEO Pack
			if(function_exists('aiosp_meta')) {
				$description = trim(get_post_meta(get_the_ID(), '_aioseop_description', true));
			} // END if(function_exists('aiosp_meta'))

			return empty($description) ? strip_tags(get_the_excerpt()) : $description;
		} // END protected function _get_description()

		/**
		 * Fügt, wenn gesetzt, ein Artikelbild als Meta-Element ein.
		 */
		protected function _add_image() {
			if(has_post_thumbnail()) {
				$this->_metas['og:image'] = wp_get_attachment_url(get_post_thumbnail_id());
			} // END if(has_post_thumbnail())
		} // END protected function _add_image()

		/**
		 * Fügt, wenn gesetzt, bei einem Artikel die Schlagwörter als Meta-Element ein
		 */
		protected function _add_post_tags() {
			if(is_singular()) {
				// Zeigt Warnungen an, auch wenn das Datumsformat in ISO 8601
				// übergeben wird. Fehler aktuell nicht nachvollziehbar.
// 				$this->_metas['article:published_time'] = get_the_date('Y-m-d');
// 				$this->_metas['article:modified_time'] = get_the_modified_date('c');

				$tags = get_the_tags();

				if(is_array($tags) && count($tags) > 0) {
					foreach($tags as $tag) {
						$this->_metas['article:tag'][] = $tag->name;
					} // END foreach($tags as $tag)
				} // END if(is_array($tags) && count($tags) > 0)
			} // END if(is_singular())
		} // END protected function _add_post_tags()

		/**
		 * Gibt die erzeugten Meta-Elemente im Head-Bereich der Seite aus
		 */
		protected function _output() {
			$var_sHtmlReturn = '';

			foreach($this->_metas as $property => $content) {
				$content = is_array($content) ? $content : array(
					$content
				);

				foreach($content as $content_single) {
					$var_sHtmlReturn .= '<meta property="' . $property . '" content="' . esc_attr(trim($content_single)) . '" />' . "\n";
				} // END foreach($content as $content_single)
			} // END foreach($this->_metas as $property => $content)

			return $var_sHtmlReturn;
		} // END protected function _output()
	} // END class Twoclick_Social_Media_Buttons_OpenGraph
} // END if(!class_exists('Twoclick_Social_Media_Buttons_OpenGraph'))
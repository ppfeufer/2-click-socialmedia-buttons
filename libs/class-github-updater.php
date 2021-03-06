<?php
/**
 * Check Github for Updates as well
 *
 * http://code.tutsplus.com/tutorials/distributing-your-plugins-in-github-with-automatic-updates--wp-34817
 */
class Twoclick_Social_Media_Buttons_GitHub_Plugin_Updater {
	private $slug; // plugin slug
	private $pluginData; // plugin data
	private $username; // GitHub username
	private $repo; // GitHub repo name
	private $pluginFile; // __FILE__ of our plugin
	private $githubAPIResult; // holds data from GitHub
	private $accessToken; // GitHub private repo token

	/**
	 * Class constructor.
	 *
	 * @param  string $pluginFile
	 * @param  string $gitHubUsername
	 * @param  string $gitHubProjectName
	 * @param  string $accessToken
	 * @return null
	 */
	public function __construct($pluginFile, $gitHubUsername, $gitHubProjectName, $accessToken = '') {
		add_filter('pre_set_site_transient_update_plugins', array($this, 'setTransitent'));
		add_filter('plugins_api', array($this, 'setPluginInfo'), 10, 3);
		add_filter('upgrader_pre_install', array($this, 'pre_install'), 10, 3);
		add_filter('upgrader_post_install', array($this, 'postInstall'), 10, 3);

		$this->pluginFile = $pluginFile;
		$this->username = $gitHubUsername;
		$this->repo = $gitHubProjectName;
		$this->accessToken = $accessToken;
	} // END function __construct($pluginFile, $gitHubUsername, $gitHubProjectName, $accessToken = '')

	/**
	 * Get information regarding our plugin from WordPress
	 *
	 * @return null
	 */
	private function initPluginData() {
		$this->slug = plugin_basename($this->pluginFile);
		$this->pluginData = get_plugin_data($this->pluginFile);
	} // END private function initPluginData()

	/**
	 * Get information regarding our plugin from GitHub
	 *
	 * @return null
	 */
	private function getRepoReleaseInfo() {
		// Only do this once
		if(!empty($this->githubAPIResult)) {
			return;
		} // END if(!empty($this->githubAPIResult))

		// Query the GitHub API
		$url = 'https://api.github.com/repos/' . $this->username . '/' . $this->repo . '/releases';

		// We need the access token for private repos
		if(!empty($this->accessToken)) {
			$url = add_query_arg(array('access_token' => $this->accessToken), $url);
		} // END if(!empty($this->accessToken))

		// Get the results
		$this->githubAPIResult = wp_remote_retrieve_body(wp_remote_get($url));
		if(!empty($this->githubAPIResult)) {
			$this->githubAPIResult = @json_decode($this->githubAPIResult);
		} // END if(!empty($this->githubAPIResult))

		// Use only the latest release
		if(is_array( $this->githubAPIResult)) {
			$this->githubAPIResult = $this->githubAPIResult['0'];
		} // END if(is_array( $this->githubAPIResult))
	} // END private function getRepoReleaseInfo()

	/**
	 * Push in plugin version information to get the update notification
	 *
	 * @param  object $transient
	 * @return object
	 */
	public function setTransitent($transient) {
		// If we have checked the plugin data before, don't re-check
		if(empty($transient->checked)) {
			return $transient;
		} // END if(empty($transient->checked))

		// Get plugin & GitHub release information
		$this->initPluginData();
		$this->getRepoReleaseInfo();

		// Check the versions if we need to do an update
		$doUpdate = version_compare($this->githubAPIResult->tag_name, $transient->checked[$this->slug]);

		// Update the transient to include our updated plugin data
		if($doUpdate == 1) {
			$package = $this->githubAPIResult->zipball_url;

			// Include the access token for private GitHub repos
			if(!empty($this->accessToken)) {
				$package = add_query_arg(array('access_token' => $this->accessToken), $package);
			} // END if(!empty($this->accessToken))

			$obj = new stdClass();
			$obj->slug = $this->slug;
			$obj->new_version = $this->githubAPIResult->tag_name;
			$obj->url = $this->pluginData['PluginURI'];
			$obj->package = $package;
			$transient->response[$this->slug] = $obj;
		} // END if($doUpdate == 1)

		return $transient;
	} // END public function setTransitent($transient)

	/**
	 * Push in plugin version information to display in the details lightbox
	 *
	 * @param  boolean $false
	 * @param  string $action
	 * @param  object $response
	 * @return object
	 */
	public function setPluginInfo($false, $action, $response) {
		// Get plugin & GitHub release information
		$this->initPluginData();
		$this->getRepoReleaseInfo();

		// If nothing is found, do nothing
		if(empty($response->slug) || $response->slug != $this->slug) {
			return $false;
		} // END if(empty($response->slug) || $response->slug != $this->slug)

		// Add our plugin information
		$response->last_updated = $this->githubAPIResult->published_at;
		$response->slug = $this->slug;
		$response->plugin_name  = $this->pluginData['Name'];
		$response->version = $this->githubAPIResult->tag_name;
		$response->author = $this->pluginData['AuthorName'];
		$response->homepage = $this->pluginData['PluginURI'];

		// This is our release download zip file
		$downloadLink = $this->githubAPIResult->zipball_url;

		// Include the access token for private GitHub repos
		if(!empty( $this->accessToken)) {
			$downloadLink = add_query_arg(array('access_token' => $this->accessToken ), $downloadLink);
		} // END if(!empty( $this->accessToken))

		$response->download_link = $downloadLink;

		// We're going to parse the GitHub markdown release notes, include the parser
		require_once(plugin_dir_path(__FILE__) . 'class-github-markup-parser.php');

		// Create tabs in the lightbox
		$response->sections = array(
			'description' => $this->pluginData["Description"],
			'changelog' => class_exists('Twoclick_Social_Media_Buttons_Github_Parsedown') ? Twoclick_Social_Media_Buttons_Github_Parsedown::instance()->parse( $this->githubAPIResult->body ) : $this->githubAPIResult->body
		);

		// Gets the required version of WP if available
		$matches = null;
		preg_match('/requires:\s([\d\.]+)/i', $this->githubAPIResult->body, $matches);

		if(!empty($matches)) {
			if(is_array($matches)) {
				if(count($matches) > 1) {
					$response->requires = $matches['1'];
				} // END if(count($matches) > 1)
			} // END if(is_array($matches))
		} // END if(!empty($matches))

		// Gets the tested version of WP if available
		$matches = null;
		preg_match('/tested:\s([\d\.]+)/i', $this->githubAPIResult->body, $matches);

		if(!empty($matches)) {
			if(is_array($matches)) {
				if(count($matches) > 1) {
					$response->tested = $matches['1'];
				} // END if(count($matches) > 1)
			} // END if(is_array($matches))
		} // END if(!empty($matches))

		return $response;
	} // END public function setPluginInfo($false, $action, $response)

	/**
	 * Perform check before installation starts.
	 *
	 * @param  boolean $true
	 * @param  array   $args
	 * @return null
	 */
	public function preInstall($true, $args) {
		// Get plugin information
		$this->initPluginData();

		// Check if the plugin was activated before...
		$this->pluginActivated = is_plugin_active($this->slug);
	}

	/**
	 * Perform additional actions to successfully install our plugin
	 *
	 * @param  boolean $true
	 * @param  string $hook_extra
	 * @param  object $result
	 * @return object
	 */
	public function postInstall($true, $hook_extra, $result) {
		// Since we are hosted in GitHub, our plugin folder would have a dirname of
		// reponame-tagname change it to our original one:
		global $wp_filesystem;

		$pluginFolder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname($this->slug);
		$wp_filesystem->move($result['destination'], $pluginFolder);
		$result['destination'] = $pluginFolder;

		// Re-activate plugin if needed
		if($this->pluginActivated) {
			$activate = activate_plugin($this->slug);
		} // END if($this->pluginActivated)

		return $result;
	} // END public function postInstall($true, $hook_extra, $result)
} // END class Twoclick_Social_Media_Buttons_GitHub_Plugin_Updater
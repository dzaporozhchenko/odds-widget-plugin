<?php

class Odds_Widget {

	protected $loader;

    protected $api;

	protected $plugin_id;

	protected $version;

	public function __construct() {
		if ( defined( 'ODDS_WIDGET_VERSION' ) ) {
			$this->version = ODDS_WIDGET_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_id = 'odds-widget';

		$this->load_dependencies();
		$this->set_locale();
        $this->define_api_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	private function load_dependencies() {
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-odds-widget-loader.php';
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-odds-widget-i18n.php';
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-odds-widget-admin.php';
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-odds-widget-public.php';
		require_once plugin_dir_path(dirname( __FILE__ )) . 'includes/class-odds-widget-api.php';

		$this->loader = new Odds_Widget_Loader();
	}

	private function set_locale() {
		$plugin_i18n = new Odds_Widget_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

    protected function define_api_hooks()
    {
        $this->api = new Odds_Widget_Api($this->plugin_id, get_option("{$this->plugin_id}_api_key"));

        $this->loader->add_action('rest_api_init', $this->api, 'register_rest_routes');
    }

	private function define_admin_hooks() {
		$plugin_admin = new Odds_Widget_Admin($this->get_pluginid(), $this->get_version(), $this->api);

		$this->loader->add_action('admin_menu', $plugin_admin, 'add_submenu_page');
		$this->loader->add_action('admin_menu', $plugin_admin, 'display_flash_notices');
		$this->loader->add_action('admin_post_odds_widget_save_settings', $plugin_admin, 'save_settings');
	}

	private function define_public_hooks() {
		$plugin_public = new Odds_Widget_Public($this->get_pluginid(), $this->get_version(), $this->api);

        $this->loader->add_action('enqueue_block_editor_assets', $plugin_public, 'enqueue_block_editor_extension');
        $this->loader->add_action('init', $plugin_public, 'register_block');
	}

	public function run() {
		$this->loader->run();
	}

	public function get_pluginid() {
		return $this->plugin_id;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}

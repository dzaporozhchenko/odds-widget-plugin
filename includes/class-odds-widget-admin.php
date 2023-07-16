<?php

class Odds_Widget_Admin {
    const NONCE_KEY = 'odds_widget_admin';

    const ODDS_FORMATS = ['decimal', 'fractional'];

    const INPUT_KEYS = [
        'odds_format',
        'sport_key',
        'game_id',
        'bookmakers',
        'bookmakers_url'
    ];
	private $plugin_id;

	private $version;

    private $api;

	public function __construct($plugin_id, $version, $api) {
		$this->plugin_id = $plugin_id;
		$this->version = $version;
        $this->api = $api;
	}

    public function add_submenu_page()
    {
        add_options_page('Odds Widget Settings', 'Odds Widget', 'manage_options', 'odds-widget', [$this, 'load_view'], null);
    }

    public function save_settings()
    {
        $nonce = sanitize_text_field($_POST[self::NONCE_KEY]);
        if (!isset($nonce) || !wp_verify_nonce($nonce, 'odds_widget_save_settings')) {
            $this->add_flash_notice(esc_html__('Invalid nonce.', 'odds-widget'), 'error');
        } else if (!current_user_can('manage_options')) {
            $this->add_flash_notice(esc_html__('You can\'t manage options', 'odds-widget'), 'error');
        } else if (!empty($_POST['save_key'])) {
            update_option("{$this->plugin_id}_api_key", sanitize_text_field($_POST['api_key']));
        } else {
            $inputs = array_intersect_key($_POST, array_flip(self::INPUT_KEYS));
            $inputs['bookmakers_url'] = array_filter($inputs['bookmakers_url']);
            $inputs['bookmakers'] = array_keys($inputs['bookmakers']);
            try {
                if ($this->validate_post_data($inputs)) {
                    update_option("{$this->plugin_id}_odds_format", $inputs['odds_format']);
                    update_option("{$this->plugin_id}_sport_key", $inputs['sport_key']);
                    update_option("{$this->plugin_id}_game_id", $inputs['game_id']);
                    update_option("{$this->plugin_id}_bookmakers", $inputs['bookmakers']);

                    $bookmakers_url = get_option("{$this->plugin_id}_bookmakers_url") ?: [];
                    update_option("{$this->plugin_id}_bookmakers_url", array_merge($bookmakers_url, $inputs['bookmakers_url'] ?? []));

                    $this->add_flash_notice(esc_html__('Changes has been saved.', 'odds-widget'), 'success');
                }
            } catch (Exception $e) {
                $this->add_flash_notice(
                    esc_html__('API request error:', 'odds-widget') . ' ' . $e->getMessage(),
                    'error'
                );
            }
        }

        wp_redirect(admin_url('admin.php?page=odds-widget'));
        exit;
    }

    protected function validate_post_data($inputs)
    {
        $isValid = true;

        if (empty($inputs['odds_format'])) {
            $this->add_flash_notice(esc_html__('Odds format field is required', 'odds-widget'), 'error');
            $isValid = false;
        } else if (!in_array($inputs['odds_format'], self::ODDS_FORMATS)) {
            $this->add_flash_notice(esc_html__('Invalid Odds format data', 'odds-widget'), 'error');
            $isValid = false;
        }

        if (empty($inputs['sport_key'])) {
            $this->add_flash_notice(esc_html__('Sport is required', 'odds-widget'), 'error');
            $isValid = false;
        } else if (!in_array($inputs['sport_key'], array_column($this->api->retrieve_sports(), 'key'))) {
            $this->add_flash_notice(esc_html__('Invalid Sport data', 'odds-widget'), 'error');
            $isValid = false;
        } else {
            $odds_data = $this->api->retrieve_odds($inputs['sport_key']);
            if (empty($inputs['game_id'])) {
                $this->add_flash_notice(esc_html__('Game is required', 'odds-widget'), 'error');
                $isValid = false;
            } else if (!in_array($inputs['game_id'], array_column($odds_data, 'id'))) {
                $this->add_flash_notice(esc_html__('Invalid Game data', 'odds-widget'), 'error');
                $isValid = false;
            }

            $game_data = array_combine(array_column($odds_data, 'id'), $odds_data)[$inputs['game_id']];
            $bookmakers = array_column($game_data['bookmakers'], 'key');
            if (empty($inputs['bookmakers'])) {
                $this->add_flash_notice(esc_html__('At least one bookmaker must be selected', 'odds-widget'), 'error');
                $isValid = false;
            } else if(!is_array($inputs['bookmakers']) || array_diff($inputs['bookmakers'], $bookmakers)) {
                $this->add_flash_notice(esc_html__('Invalid Bookmaker data', 'odds-widget'), 'error');
                $isValid = false;
            } else {
                if(array_diff(array_keys($inputs['bookmakers_url']), $bookmakers)) {
                    $this->add_flash_notice(esc_html__('Invalid Bookmaker URL data', 'odds-widget'), 'error');
                    $isValid = false;
                } else if ($inputs['bookmakers'] != array_values(array_intersect(array_keys($inputs['bookmakers_url']), $inputs['bookmakers']))) {
                    $this->add_flash_notice(esc_html__('Each selected bookmaker\'s partner link must be specified', 'odds-widget'), 'error');
                    $isValid = false;
                } else {
                    $validUrl = true;
                    foreach ($inputs['bookmakers_url'] as $url) {
                        if (!filter_var($url, FILTER_VALIDATE_URL)) {
                            $validUrl = false;
                        }
                    }
                    if (!$validUrl) {
                        $this->add_flash_notice(esc_html__('Partner link must be a valid URL', 'odds-widget'), 'error');
                        $isValid = false;
                    }
                }
            }

        }

        return $isValid;
    }

    public function load_view()
    {
        wp_enqueue_style($this->plugin_id, plugin_dir_url(__FILE__) . '../build/admin-styles.css', array(), $this->version, 'all');
        wp_enqueue_script($this->plugin_id, plugin_dir_url(__FILE__) . '../build/admin-script.js', array('jquery', 'wp-api-fetch', 'wp-url', 'wp-i18n'), $this->version, false);

        try {
            $all_sports = $this->api->retrieve_sports();
            $all_groups = array_unique(array_column($all_sports, 'group'));
            $current_sport_key = get_option("{$this->plugin_id}_sport_key");
            $current_game_id = get_option("{$this->plugin_id}_game_id");
            $current_sport = array_combine(array_column($all_sports, 'key'), $all_sports)[$current_sport_key] ?? null;
            $sports_of_group = !empty($current_sport)
                ? array_filter($all_sports, function ($sport) use ($current_sport) {
                    return $sport['group'] === $current_sport['group'];
                })
                : [];
            $current_odds_format = get_option("{$this->plugin_id}_odds_format");
            $all_odds = !empty($current_sport_key) ? $this->api->retrieve_odds($current_sport_key) : [];
            $bookmakers_url_settings = get_option("{$this->plugin_id}_bookmakers_url");
            $current_bookmakers = get_option("{$this->plugin_id}_bookmakers") ?: [];

            $data = [
                'api_key' => get_option("{$this->plugin_id}_api_key"),
                'quota_left' => get_option("{$this->plugin_id}_quota_left"),
                'all_sports' => $all_sports,
                'all_groups' => $all_groups,
                'all_odds' => array_combine(array_column($all_odds, 'id'), $all_odds),
                'current_sport' => $current_sport,
                'current_game_id' => $current_game_id,
                'sports_of_group' => $sports_of_group,
                'nonce_key' => self::NONCE_KEY,
                'odds_formats' => self::ODDS_FORMATS,
                'current_odds_format' => $current_odds_format,
                'bookmakers_url_settings' => $bookmakers_url_settings,
                'current_bookmakers' => $current_bookmakers
            ];

            wp_localize_script($this->plugin_id, 'data', $data);
            $this->includeWithVariables(__DIR__ . '/../views/admin.php', $data);
        } catch (Exception $e) {
            $this->includeWithVariables(__DIR__ . '/../views/admin_error.php', [
                'nonce_key' => self::NONCE_KEY,
                'error' => esc_html__('API request error:', 'odds-widget') . ' ' . $e->getMessage()
            ]);
        }
    }

    protected function includeWithVariables($filePath, $variables = array(), $print = true)
    {
        $output = NULL;

        if (file_exists($filePath)) {
            extract($variables);
            ob_start();
            include $filePath;
            $output = ob_get_clean();
        }
        if ($print) {
            print $output;
        }
        return $output;
    }

    public function add_flash_notice($notice = "", $type = "warning", $dismissible = true) {
        $userId = get_current_user_id();
        $notices = get_option("odds_widget_flash_notices_{$userId}", array());

        $notices[] = [
            "notice" => $notice,
            "type" => $type,
            "dismissible" => $dismissible,
        ];

        update_option("odds_widget_flash_notices_{$userId}", $notices);
    }

    public function display_flash_notices() {
        $userId = get_current_user_id();
        $notices = get_option("odds_widget_flash_notices_{$userId}", array());

        foreach ( $notices as $notice ) {
            printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                $notice['type'],
                $notice['dismissible'] ? "is-dismissible" : "",
                $notice['notice']
            );
        }

        if(!empty( $notices) ) {
            delete_option("odds_widget_flash_notices_{$userId}", array());
        }
    }
}

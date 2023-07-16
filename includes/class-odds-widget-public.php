<?php

class Odds_Widget_Public {

	private $plugin_id;
    
	private $version;

    private $api;
    
	public function __construct($plugin_id, $version, $api) {
		$this->plugin_id = $plugin_id;
		$this->version = $version;
        $this->api = $api;
	}

	public function enqueue_styles() {
		wp_enqueue_style("{$this->plugin_id}/block", plugin_dir_url( __FILE__ ) . 'css/widget-styles.css', array(), $this->version, 'all');
	}

	public function enqueue_block_editor_extension() {
		wp_enqueue_script("{$this->plugin_id}/block", plugin_dir_url( __FILE__) . '../build/widget-script.js', array('jquery', 'wp-blocks', 'wp-editor'), $this->version, false);

        $games = $this->api->retrieve_odds(get_option("{$this->plugin_id}_sport_key"));
        $game = is_array($games)
            ? array_combine(array_column($games, 'id'), $games)[get_option("{$this->plugin_id}_game_id")] ?? null
            : null;

        $bookmakers = array_intersect_key(
            get_option("{$this->plugin_id}_bookmakers_url") ?: [],
            array_flip(get_option("{$this->plugin_id}_bookmakers") ?: []),
        );

        $widget_props = [
            'odds_format' => get_option('odds_format'),
            'game' => $game,
            'bookmakers' => $bookmakers,
        ];

        wp_localize_script($this->plugin_id, 'widgetProps', $widget_props);
	}

    public function register_block()
    {
        wp_register_script(
            "{$this->plugin_id}/block",
            plugins_url( '../build/widget-script.js', __FILE__ ),
            ['jquery', 'wp-blocks', 'wp-editor', 'wp-components'],
        );

        wp_register_style(
            "{$this->plugin_id}/block",
            plugins_url( '../build/widget-styles.css', __FILE__ ),
        );

//        wp_enqueue_block_style("{$this->plugin_id}/block", [
//            'handle' => "{$this->plugin_id}/block",
//            'src'    => plugins_url( '../build/widget-styles.css', __FILE__ ),
//        ]);

        register_block_type( "{$this->plugin_id}/block", array(
            'editor_script'         => "{$this->plugin_id}/block",
            'render_callback' => [$this, 'render_block'],
            'style_handles' => ["{$this->plugin_id}/block"],
            'api_version' => 3,
        ) );
    }

    public function render_block()
    {
        try {
            $games = $this->api->retrieve_odds(get_option("{$this->plugin_id}_sport_key"));
        } catch(Exception $e) {
            return $this->includeWithVariables(__DIR__ . '/../views/widget_error.php', [
                'header' => esc_html__('Odds table', 'odds-widget'),
                'error' => esc_html__('API request error:', 'odds-widget') . ' ' . $e->getMessage()
            ], false);
        }

        $game = is_array($games)
            ? array_combine(array_column($games, 'id'), $games)[get_option("{$this->plugin_id}_game_id")] ?? null
            : null;

        $bookmakers = array_intersect_key(
            get_option("{$this->plugin_id}_bookmakers_url") ?: [],
            array_flip(get_option("{$this->plugin_id}_bookmakers") ?: []),
        );

        $odds_format = get_option('odds_format');
        $outcomes = [];
        foreach ($game['bookmakers'] as $bookmaker) {
            foreach ($bookmaker['markets'] as $market) {
                if ($market['key'] !== 'h2h') {
                    continue;
                }
                $outcomes = array_merge($outcomes, array_column($market['outcomes'], 'name'));
            }
        }
        $outcomes = array_unique($outcomes);

        return $this->includeWithVariables(__DIR__ . '/../views/widget.php', [
            'game' => $game,
            'outcomes' => $outcomes,
            'bookmakers' => $bookmakers,
            'odds_format' => $odds_format,
            'header' => esc_html__('Odds table', 'odds-widget')
        ], false);
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
}

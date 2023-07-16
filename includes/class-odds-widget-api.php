<?php

class Odds_Widget_Api
{
    const HOST = 'https://api.the-odds-api.com';

    const REGIONS = ['eu', 'uk', 'us'];

    private $plugin_id;
    protected $api_key;
    public function __construct($plugin_id, $api_key)
    {
        $this->plugin_id = $plugin_id;
        $this->api_key = $api_key;
    }

    public function retrieve_sports()
    {
        $data = get_transient("{$this->plugin_id}_sports_data");

        if (empty($data)) {
            $response = wp_remote_get(self::HOST . "/v4/sports/?apiKey={$this->api_key}&all=true");
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                throw new Exception(wp_remote_retrieve_response_message($response), $response_code);
            }
            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);
//            $data = json_decode(file_get_contents(__DIR__ . '/../mock/sports.json'), true);
            $data = array_filter($data, function($sport) { return $sport['active'] === true; });
            $data = array_values($data);
            set_transient("{$this->plugin_id}_sports_data", $data, 3600);
        }

        return $data;
    }

    public function retrieve_odds($sport)
    {
        if (empty($sport) || !in_array($sport, array_column($this->retrieve_sports(), 'key'))) {
            return new WP_Error(400, 'Invalid or absent "sport" key', '');
        }

        $data = get_transient("{$this->plugin_id}_odds_data_{$sport}");

        if (empty($data)) {
            $response = wp_remote_get(self::HOST . "/v4/sports/{$sport}/odds/?"
                . http_build_query([
                    'apiKey' => $this->api_key,
                    'all' => 'true',
                    'regions' => implode(',', self::REGIONS),
                ])
            );
            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                throw new Exception(wp_remote_retrieve_response_message($response), $response_code);
            }
            update_option(
                "{$this->plugin_id}_quota_left",
                wp_remote_retrieve_header($response, 'X-Requests-Remaining')
            );

            $response_body = wp_remote_retrieve_body($response);
            $data = json_decode($response_body, true);
//            $data = json_decode(file_get_contents(__DIR__ . '/../mock/odds.json'), true);
            set_transient("{$this->plugin_id}_odds_data_{$sport}", $data, 3600);
        }

//        $data = array_filter($data, function($sport) { return $sport['active'] === true; });

        return $data;
    }

    public function register_rest_routes()
    {
        register_rest_route('odds-widget', '/sports', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_sports'],
        ));

        register_rest_route('odds-widget', '/odds', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_odds'],
        ));
    }

    public function get_sports()
    {
        try {
            return $this->create_api_response($this->retrieve_sports());
        } catch (Exception $e) {
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    public function get_odds($request)
    {
        try {
            $sport = $request->get_query_params()['sport'] ?? null;
            return $this->create_api_response($this->retrieve_odds($sport));
        } catch (Exception $e) {
            return new WP_Error($e->getCode(), $e->getMessage());
        }
    }

    public function get_widget()
    {
        $games = $this->retrieve_odds(get_option("{$this->plugin_id}_sport_key"));
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

        return new WP_REST_Response($widget_props, 200);
    }

    protected function create_api_response($data)
    {
        $response = new WP_REST_Response($data, 200);
        $response->set_headers(array('Cache-Control' => 'max-age=600'));

        return $response;
    }
}
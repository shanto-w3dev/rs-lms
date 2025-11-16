<?php

class RS_LMS_REST_API {

    const ROUTE_NAMESPACE = 'rs-lms/v1';
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes() {
        register_rest_route(self::ROUTE_NAMESPACE, 'episode/bookmark', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_bookmark'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
            'args' => [
                'chapter_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'ep' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'bookmarked' => [
                    'required' => true,
                    'type' => 'boolean',
                ],
            ]
        ]);

        register_rest_route(self::ROUTE_NAMESPACE, 'episode/complete', [
            'methods' => 'POST',
            'callback' => [$this, 'handle_complete'],
            'permission_callback' => function (){
                return is_user_logged_in();
            },
            'args' => [
                'chapter_id' => [
                    'required' => true,
                    'type' => 'integer',
                ],
                'ep' => [
                    'required' => true,
                    'type' => 'integer',
                ],
               'completed' => [
                    'required' => true,
                    'type' => 'boolean',
                ],
            ]
        ]);

        register_rest_route(self::ROUTE_NAMESPACE, '/notes', [
			'methods' => 'GET',
			'callback' => [$this, 'handle_notes_fetch'],
			'permission_callback' => function () {
				return is_user_logged_in();
			},
			'args' => [
				'url' => [ 
                    'required' => true, 
                    'type' => 'string' 
                ],
			],
		]);
    }

    public function handle_notes_fetch(WP_REST_Request $req) {
		$url = esc_url_raw($req->get_param('url'));
		if (!$url || !preg_match('#^https?://#i', $url)) {
			return new WP_Error('invalid_url', 'Invalid or missing URL', ['status' => 400]);
		}
		$response = wp_remote_get($url,[
			'timeout' => 5,
			'redirection' => 3
		]);

		if (is_wp_error($response)) {
			return new WP_Error('fetch_failed', $response->get_error_message(), ['status' => 502]);
		}

		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if ($code < 200 || $code >= 300 || $body === '') {
			return new WP_Error('bad_response', 'Failed to fetch markdown', ['status' => 502]);
		}

		return new WP_REST_Response(['ok' => true, 'markdown' => $body], 200);
	}

    private function update_user_flag($user_id, $meta_key, $chapter_id, $ep, $flag){
        $list = get_user_meta($user_id, $meta_key, true);

        if(!is_array($list)){
            $list = [];
        }

        $key = sprintf('%d:%d', $chapter_id, $ep);
        $idx = array_search($key, $list, true);

        if($flag){
            if($idx === false){
                $list[] = $key;
            }
        }else{
            if($idx !== false){
                array_splice($list, $idx, 1);
            }
        }

        update_user_meta($user_id, $meta_key, array_values($list));
        return $list;
    }

    public function handle_bookmark(WP_REST_Request $req){
        $user_id = get_current_user_id();
        $chapter_id = intval($req->get_param('chapter_id'));
        $ep = intval($req->get_param('ep'));
        $bookmarked = filter_var($req->get_param('bookmarked'), FILTER_VALIDATE_BOOLEAN);

        if($chapter_id <= 0 || $ep <= 0){
            return new WP_Error('Invalid param', 'Invalid chapter or episode', ['status' => 400]);
        }

        $list = $this->update_user_flag($user_id, 'rs_lms_bookmarks', $chapter_id, $ep, $bookmarked);
        return new WP_REST_Response(['ok' => true, 'bookmarks' => $list], 200);
    }
    
    public function handle_complete(WP_REST_Request $req){
        $user_id = get_current_user_id();
        $chapter_id = intval($req->get_param('chapter_id'));
        $ep = intval($req->get_param('ep'));
        $completed = filter_var($req->get_param('completed'), FILTER_VALIDATE_BOOLEAN);

        if($chapter_id <= 0 || $ep <= 0){
            return new WP_Error('Invalid param', 'Invalid chapter or episode', ['status' => 400]);
        }

        $list = $this->update_user_flag($user_id, 'rs_lms_completed', $chapter_id, $ep, $completed);
        return new WP_REST_Response(['ok' => true, 'completed' => $list], 200);
    }
}
new RS_LMS_REST_API();
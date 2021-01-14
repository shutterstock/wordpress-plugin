<?php

class Shutterstock_API {

	private $api_url = 'https://api.shutterstock.com/v2';

  	public function __construct($shutterstock, $version) {
		$this->shutterstock = $shutterstock;
		$this->version = $version;
		$this->shutterstock_helper = new Shutterstock_Helper($shutterstock, $version);
	}

	public function register_routes() {
		if (is_user_logged_in()) {
			register_rest_route($this->shutterstock, '/user/subscriptions', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_subscriptions'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

			register_rest_route($this->shutterstock, '/images/(?P<id>\d+[a-zA-z]*)', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_image_details'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

			register_rest_route($this->shutterstock, '/contributor/(?P<id>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_contributor_details'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

			register_rest_route($this->shutterstock, '/images/licenses', array(
				'methods' => 'POST',
				'callback' => array($this, 'license_image'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

			register_rest_route($this->shutterstock, '/images/licenses', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_list_image_licenses'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

			register_rest_route($this->shutterstock, '/images/licenses/(?P<id>\w+)/downloads', array(
				'methods' => 'POST',
				'callback' => array($this, 'redownload_image'),
				'permission_callback' => array($this, 'get_permissions_check'),
			));

		}
	}

  	public function get_subscriptions($request) {
		$parameters = $request->get_params();
		$media_type = sanitize_text_field($parameters['mediaType']);
		$is_editorial = $media_type === 'editorial';

		$subscription_route = "{$this->api_url}/user/subscriptions";

		$response = $this->do_api_get_request($subscription_route);

		$decoded_response_body = json_decode($response['response_body'], true);

		$supported_licenses = [
			'standard',
			'enhanced',
			'image',
			'multi_share',
			'premier',
			'premier_digital',
			'media',
			'media_digital',
		];

		if ($is_editorial) {
			$supported_licenses = [
				'premier_editorial_all_digital',
				'premier_editorial_all_media',
			];
		}

		$filtered_subscriptions = array_values(
			array_filter(
				$decoded_response_body['data'], function($val) use($supported_licenses) {
					$license = $val['license'];
					$expiration_time = $val['expiration_time'];
					$is_expired = $expiration_time && (strtotime($expiration_time) < time());

					if ((in_array($license, $supported_licenses, true)) && !$is_expired) {
						return true;
					}

					return false;
				}
			)
		);

		return new WP_REST_Response($filtered_subscriptions, $response['response_code']);
	}

  	public function get_image_details($request) {
		$image_id = sanitize_text_field($request['id']);
		$parameters = $request->get_params();
		$media_type = sanitize_text_field($parameters['mediaType']);
		$is_editorial = $media_type === 'editorial';

		$country = $this->shutterstock_helper->get_editorial_country();
		$image_details_route = $is_editorial
			? $this->api_url. '/editorial/images/'. $image_id . '?view=full&country=' . $country
			: $this->api_url. '/images/'. $image_id . '?view=full';
		$response = $this->do_api_get_request($image_details_route);
		$decoded_response_body = json_decode($response['response_body'], true);

		return new WP_REST_Response($decoded_response_body, $response['response_code']);
	}

	public function get_contributor_details($request) {
		$contributor_id = sanitize_text_field($request['id']);

		$contributor_details_route = $this->api_url. '/contributors?id='. $contributor_id;

		$response = $this->do_api_get_request($contributor_details_route);

		$decoded_response_body = json_decode($response['response_body'], true);

		return new WP_REST_Response($decoded_response_body, $response['response_code']);
	}

	public function get_list_image_licenses($request) {
		$parameters = $request->get_params();
		$page = isset($parameters['page']) ? sanitize_text_field($parameters['page']) : 1;
		$per_page = isset($parameters['per_page']) ? sanitize_text_field($parameters['per_page']) : 20;
		$list_image_licenses_route = $this->api_url. '/images/licenses?per_page='. $per_page .'&page=' . $page;

		$response = $this->do_api_get_request($list_image_licenses_route);

		$decoded_response_body = json_decode($response['response_body'], true);

		return new WP_REST_Response($decoded_response_body, $response['response_code']);
	}

	public function license_image($request) {
		$req_body = json_decode($request->get_body(), true);
		$subscription_id = sanitize_text_field($req_body['subscription_id']);
		$size = sanitize_text_field($req_body['size']);
		$image_id = sanitize_text_field($req_body['id']);
		$image_description = sanitize_text_field($req_body['description']);
		$contributor_name = sanitize_text_field($req_body['contributorName']);
		$media_type = sanitize_text_field($req_body['mediaType']);
		$search_id = sanitize_text_field($req_body['search_id']);
		$is_editorial = $media_type === 'editorial';

		$width = sanitize_text_field($req_body['width']);
		$height = sanitize_text_field($req_body['height']);

		$metadata = array_map('sanitize_text_field', $req_body['metadata']);

		$local_amount = isset($req_body['pricePerDownload']['local_amount'])
			? sanitize_text_field($req_body['pricePerDownload']['local_amount'])
			: 0;

		$token = $this->shutterstock_helper->get_api_token();

		$license_url = $is_editorial
			? $this->api_url. '/editorial/images/licenses'
			: $this->api_url. '/images/licenses?subscription_id='. $subscription_id;

		$body_key = $is_editorial ? 'editorial' : 'images';
		$body = [];

		if ($is_editorial) {
			$country = $this->shutterstock_helper->get_editorial_country();
			$license = isset($req_body['license']) ? sanitize_text_field($req_body['license']) : '';

			$body = [
				"country" => $country,
				"editorial" => [
					[
						"editorial_id" => $image_id,
						"license" => $license,
						"size" => $size,
					],
				],
			];
		} else {
			$body = [
				"images" => [
					[
						"image_id" => $image_id,
						"size" => $size,
						"format" => 'jpg'
					]
				]
			];
		}

		// Add price if passed
		if ($local_amount) {
			$body[$body_key][0]["price"] = $local_amount;
		}

		// Add metada if passed
		if ($metadata) {
			$body[$body_key][0]["metadata"] = $metadata;
		}

		// Add search id if passed
		if ($search_id) {
			$body[$body_key][0]["search_id"] = $search_id;
		}

		$args = [
			'headers' => [
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json; charset=utf-8',
					'x-shutterstock-application' => 'Wordpress/'. $this->version
			],
			'body' => wp_json_encode($body),
			'data_format' => 'body',
		];

		$response = wp_remote_post($license_url, $args);
		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);

		$decoded_body = json_decode($response_body, true);

		$success_error = isset($decoded_body['data'][0]['error']);

		if ($response_code !== 200 || $success_error || isset($decoded_body['errors'])) {
			$error = $decoded_body;

			// licensing return 200 even if some error occurs. This type of error response returned by editorial.
			if ($success_error) {
				$error = $decoded_body['data'][0];
				$error['message'] = $decoded_body['data'][0]['error'];
			}

			return wp_send_json_error($error, $response_code);
		}

		$download_url = $decoded_body['data'][0]['download']['url'];

		$filename = 'shutterstock-'. $image_id. '-' .$size. '-licensed.jpg';

		$post_description = 'Shutterstock ID: '. $image_id . ', Photographer: '. $contributor_name;

		$uploaded_image_url = $this->download_upload_image_to_media_library(
			$download_url,
			$filename,
			$size,
			$image_description,
			$image_id,
			$post_description,
			$width,
			$height
		);

		return wp_send_json_success($uploaded_image_url, $response_code);
	}

	public function redownload_image($request) {
		$license_id = sanitize_text_field($request['id']);
		$req_body = json_decode($request->get_body(), true);
		$size = sanitize_text_field($req_body['size']);

		$image_id = sanitize_text_field($req_body['imageId']);
		$image_description = sanitize_text_field($req_body['description']);
		$contributor_name = sanitize_text_field($req_body['contributorName']);
		$media_type = sanitize_text_field($req_body['mediaType']);

		$width = sanitize_text_field($req_body['width']);
		$height = sanitize_text_field($req_body['height']);

		$redownload_url = $this->api_url. '/images/licenses/'. $license_id . '/downloads';
		$token = $this->shutterstock_helper->get_api_token();

		$body = [
			"size" => $size,
		];

		$args = [
			'headers' => [
					'Authorization' => 'Bearer ' . $token,
					'Content-Type' => 'application/json; charset=utf-8',
					'x-shutterstock-application' => 'Wordpress/'. $this->version,
			],
			'body' => wp_json_encode($body),
			'data_format' => 'body',
		];

		$response = wp_remote_post($redownload_url, $args);
		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);
		$decoded_body = json_decode($response_body, true);

		$success_error = isset($decoded_body['data'][0]['error']);

		if ($response_code !== 200 || $success_error || isset($decoded_body['errors'])) {
			$error = $decoded_body;

			// return 200 even if some error occurs. This type of error response returned by editorial.
			if ($success_error) {
				$error = $decoded_body['data'][0];
				$error['message'] = $decoded_body['data'][0]['error'];
			}

			return wp_send_json_error($error, $response_code);
		}

		$download_url = $decoded_body['url'];

		$filename = 'shutterstock-'. $image_id. '-' .$size. '-redownloaded.jpg';

		$post_description = 'Shutterstock ID: '. $image_id . ', Photographer: '. $contributor_name;

		$uploaded_image_url = $this->download_upload_image_to_media_library(
			$download_url,
			$filename,
			$size,
			$image_description,
			$image_id,
			$post_description,
			$width,
			$height
		);

		return wp_send_json_success($uploaded_image_url, $response_code);

	}

	private function download_upload_image_to_media_library($download_url, $filename, $size, $title, $image_id, $post_description, $width, $height) {
		$response = '';

		if (function_exists('vip_safe_wp_remote_get')) {
			$response = vip_safe_wp_remote_get($download_url, '', 3, 3, 60);
		} else {
			$response = wp_remote_get($download_url, ['timeout' => 60]); // @codingStandardsIgnoreLine -- for non-VIP environments
		}

		if (is_wp_error($response)) {
			return $response;
		}
		$type = wp_remote_retrieve_header( $response, 'content-type' );
		$response_body = wp_remote_retrieve_body($response);

		$uploaded_file = wp_upload_bits($filename, '', $response_body);

		$attachment_meta = [
			'post_title'     => $title,
			'post_content'   => $post_description,
			'post_mime_type' => $type,
		];

		$uploaded_image_path = $uploaded_file['file'];

		if ($size === 'huge' || $size === 'original') {
			$max_width = 1500;
			$resized_height = intval(($height * ($max_width/$width)));
			$editor = wp_get_image_editor($uploaded_image_path, array());
			$editor->resize($max_width, $resized_height, true);
			$scaled_image = $editor->save($editor->generate_filename('scaled'));
			$uploaded_image_path = $scaled_image['path'];
		}

		$attachment_id = wp_insert_attachment($attachment_meta, $uploaded_image_path);


		require_once( ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata($attachment_id, $uploaded_image_path); // automatically adds different sizes images.
		wp_update_attachment_metadata($attachment_id, $attach_data);
		update_post_meta($attachment_id, '_wp_attachment_image_alt', $title);

		return wp_prepare_attachment_for_js($attachment_id);
	}

	private function do_api_get_request($endpoint) {
		$token = $this->shutterstock_helper->get_api_token();

		$args = [
			'headers' => [
					'Authorization' => 'Bearer ' . $token,
					'x-shutterstock-application' => 'Wordpress/'. $this->version,
			],
			'timeout' => 3
		];

		$response = '';
		if (function_exists('vip_safe_wp_remote_get')) {
			$response = vip_safe_wp_remote_get($endpoint, '', 3, 3, 20, $args);
		} else {
			$response = wp_safe_remote_get($endpoint, $args); // @codingStandardsIgnoreLine -- for non-VIP environments
		}

		$response_code = wp_remote_retrieve_response_code($response);
		$response_body = wp_remote_retrieve_body($response);
		$decoded_body = json_decode($response_body, true);

		if ($response_code !== 200 || isset($decoded_body['errors'])) {
			return wp_send_json_error($decoded_body, $response_code);
		}

		return [
			'response_code' => $response_code,
			'response_body' => $response_body
		];
	}

	public function get_permissions_check($request) {
		// All of the routes should only be accessible if a user has proper permission.
		$permissions = $this->shutterstock_helper->get_user_permissions();

		$can_license_photo = in_array('can_user_license_shutterstock_photos', $permissions, true);
		$can_license_editorial_images = in_array('can_user_license_shutterstock_editorial_image', $permissions, true);
		$can_license_all_images = in_array('can_user_license_all_shutterstock_images', $permissions, true);

		$request_type = $request->get_method();
		$is_editorial = false;
		$media_type = 'images';

		if ($request_type === 'GET') {
			$parameters = $request->get_params();
			$media_type = isset($parameters['mediaType']) ? sanitize_text_field($parameters['mediaType']) : 'images';
		} else if ($request_type === 'POST') {
			$req_body = json_decode($request->get_body(), true);
			$media_type = isset($req_body['mediaType']) ? sanitize_text_field($req_body['mediaType']) : 'images';
		}

		$is_editorial = ($media_type === 'editorial');

		if ($can_license_all_images || ($is_editorial && $can_license_editorial_images) || (!$is_editorial && $can_license_photo)) {
			return true;
		}

		return false;
	}

}

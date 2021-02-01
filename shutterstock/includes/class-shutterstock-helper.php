<?php

class Shutterstock_Helper {
    public function __construct($shutterstock, $version) {
		$this->shutterstock = $shutterstock;
		$this->version = $version;
    }

    public function get_shutterstock_js_object() {
		// Logic to provide api key from backend to frontend via javascript object
		$language = $this->get_supported_language();
		$api_key = $this->get_api_key();
		$editorial_country = $this->get_editorial_country();
		$permissions = $this->get_user_permissions();

		$shutterstock_js_object = [
			'api_key' => $api_key,
			'language' => $language,
			'permissions' => $permissions,
			'version' => $this->version,
			'country' => $editorial_country,
		];

		return $shutterstock_js_object;

    }

    public function get_api_key() {
		$api_key = $this->get_options('api_key');
		return $api_key;
    }


    public function get_api_token() {
		$api_token = $this->get_options('app_token');
		return $api_token;
	}

	public function get_user_permissions() {
		$user_settings = $this->get_options('user_settings');

		// Determine the permissions for the current user
		$user_roles = (array) wp_get_current_user()->roles;

		$permissions = array();
		foreach($user_roles as $role) {
			$permissions_for_role = isset($user_settings[$role]) ? $user_settings[$role] : [];

			foreach($permissions_for_role as $permission) {
				if (!isset($permissions[$permission])) {
					$permissions[] = $permission;
				}
			}
		}

		return $permissions;
	}

	public function get_editorial_country() {
		$editorial_country = !empty($this->get_options('editorial_country'))
			? $this->get_options('editorial_country')
			: "USA";

		return $editorial_country;
	}

	private function get_options($field) {
		$shutterstock_option = get_option("{$this->shutterstock}_option_name"); // Getting the option from admin
		$shutterstock_network_option = get_site_option("{$this->shutterstock}_option_name"); // Getting the option from network admin
		$option = '';

		if (isset($shutterstock_option[$field])) {
			$option = $shutterstock_option[$field];
		} else if (isset($shutterstock_network_option[$field])) {
			$option = $shutterstock_network_option[$field];
		}

		return $option;
	}

    private function get_supported_language() {
		$plugin_i18n = new Shutterstock_i18n();
		$current_locale = determine_locale();
		$locale = $plugin_i18n->get_supported_locale($current_locale);

		$language = in_array($locale, ['zh_HK', 'zh_TW'], true)
		? 'zh-Hant'
		: substr($locale, 0, 2);

		return $language;
	}
}

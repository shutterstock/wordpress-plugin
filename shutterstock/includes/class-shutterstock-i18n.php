<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.shutterstock.com
 * @since      1.0.0
 *
 * @package    Shutterstock
 * @subpackage Shutterstock/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Shutterstock
 * @subpackage Shutterstock/includes
 * @author     Shutterstock <api@shutterstock.com>
 */
class Shutterstock_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'shutterstock',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

	public function load_textdomain_mofile($mofile, $domain) {
		if ($domain === 'shutterstock') {
			$mofile = $this->get_translation_file_name($domain, $mofile);
		}

		return $mofile;
	}

	public function load_script_translation_file($file, $handle, $domain) {
		if ($domain === 'shutterstock') {
			$file = $this->get_translation_file_name($domain, $file);
		}
		return $file;
	}

	private function get_translation_file_name($domain, $file) {
		$current_locale = determine_locale();
		/* If translation files doesn't exists for particular locale then we fallback to similar
		 * language else en_US
		 */
		if (!file_exists($file)) {
			$supported_locale = $this->get_supported_locale($current_locale);
			$file = str_replace($current_locale, $supported_locale, $file);
		}

		return $file;
	}

	public function get_supported_locale($locale) {
		$supported_locale = [
			'cs'	=> 'cs_CZ',
			'da'	=> 'da_DK',
			'de'	=> 'de_DE',
			'en'	=> 'en_US',
			'es'	=> 'es_ES',
			'fr'	=> 'fr_FR',
			'it'	=> 'it_IT',
			'hu'	=> 'hu_HU',
			'nl'	=> 'nl_NL',
			'nb'	=> 'nb_NO',
			'nn'	=> 'nb_NO',
			'pl'	=> 'pl_PL',
			'pt'	=> 'pt_PT',
			'fi'	=> 'fi',
			'sv'	=> 'sv_SE',
			'tr'	=> 'tr_TR',
			'ru'	=> 'ru_RU',
			'th'	=> 'th',
			'ko'	=> 'ko_KR',
			'ja' 	=> 'ja',
			'zh_HK' => 'zh_TW', // traditional chinese,
			'zh_TW' => 'zh_TW', // traditional chinese,
			'zh'	=> 'zh_CN'
		];

		$language = in_array($locale, ['zh_HK', 'zh_TW'], true)
				? $locale
				: substr($locale, 0, 2);

		$locale = 'en_US';

		if (isset($supported_locale[$language])) {
			$locale = $supported_locale[$language];
		}

		return $locale;
	}
}

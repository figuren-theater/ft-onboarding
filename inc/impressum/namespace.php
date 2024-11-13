<?php
/**
 * Figuren_Theater Onboarding Impressum.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Impressum;

use FT_VENDOR_DIR;

use Figuren_Theater\inc\Geo;
use Figuren_Theater\Options;

use function add_action;
use function add_filter;
use function is_admin;

const BASENAME   = 'impressum/impressum.php';
const PLUGINPATH = '/wpackagist-plugin/' . BASENAME;
const OPTION     = 'impressum_imprint_options';

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );
	
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}

/**
 * Load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	add_filter( 'pre_update_option_' . OPTION, __NAMESPACE__ . '\\pre_update_ft_geo_option_from_imprint', 10, 3 );

	if ( ! is_admin() ) {
		return;
	}

	add_filter( 'impressum_country_after_sort', __NAMESPACE__ . '\\impressum_country_after_sort' );
	add_filter( 'impressum_legal_entity_after_sort', __NAMESPACE__ . '\\impressum_legal_entity_after_sort' );

	add_action( 'admin_head-settings_page_impressum', __NAMESPACE__ . '\\cleanup_admin_ui' );
}


/**
 * Handle options
 *
 * @return void
 */
function filter_options(): void {
	
	$_options = [
		'dismissed-impressum_welcome_notice' => true,
		// OPTION => [] // DO NOT HANDLE, as it's the user-data!
	];

	// Gets added to the 'OptionsCollection' 
	// from within itself on creation.
	new Options\Factory( 
		$_options, 
		'Figuren_Theater\Options\Option', 
		BASENAME, 
	);
}


/**
 * Set Impressum-location, ft_geo-catgeories and WP_LANG in one go, by just .... updating an option.
 * 
 * Filters a specific option before its value is (maybe) serialized and updated.
 *
 * The dynamic portion of the hook name, `$option`, refers to the option name.
 *
 * @see https://developer.wordpress.org/reference/hooks/pre_update_option_option/
 *
 * @param  array<string, string>      $new_value    The new, unserialized option value.
 * @param  array<string, string>|bool $old_value    The old option value.
 * @param  string                     $option_name  Name of the option in the DB.
 *
 * @return array<string, string>               The new, updated option value ready for being saved to the database.
 */
function pre_update_ft_geo_option_from_imprint( array $new_value, array|bool $old_value, string $option_name ): array {

	// Do nothing, if nothing (on the address) has changed.
	// Do check '$new_value['country']', which could be unset by Figuren_Theater\Onboarding\Sites\Installation\set_imprint_page().
	if ( isset( $new_value['country'] ) && 
		// Can be bool, if non existent yet.
		isset( $old_value['country'] ) && 
		$old_value['country'] === $new_value['country']
		&&
		$old_value['address'] === $new_value['address']
	) {
		return $new_value;
	}

	// Start the geo-engines :) !
	$ft_geo_options_bridge = new Geo\ft_geo_options_bridge();
	$ft_geo                = $ft_geo_options_bridge->update_option_ft_geo( $old_value, $new_value, $option_name );

	// Set adress to verified version.
	if ( isset( $ft_geo['address'] ) && ! empty( $ft_geo['address'] ) ) {
		$new_value['address'] = $ft_geo['address'];
	} else {
		// Or reset.
		$new_value['address'] = $old_value['address'];
	} 
	// Set country to verified version.
	if ( isset( $ft_geo['geojson']['properties']['address']['country'] ) ) {
		// Crazy country codes by 'Impressum' plugin.
		$_country_helper      = [
			'de' => 'deu',
			'at' => 'aut',
			'ch' => 'che',
		];
		$new_value['country'] = $_country_helper[ $ft_geo['geojson']['properties']['address']['country_code'] ];
	} else {
		// Or reset.
		$new_value['country'] = $old_value['country'];
	}

	// Go on and save.
	return $new_value;
}


/**
 * Reduce countries to relevant ones.
 * 
 * Filter the countries after localized alphabetical sorting.
 * 
 * @param  array<string, string> $countries The current countries.
 * 
 * @return array<string, string> $countries The filtered countries.
 */
function impressum_country_after_sort( array $countries ): array {
	return [
		'deu' => $countries['deu'],
		'aut' => $countries['aut'],
		'che' => $countries['che'],
	];
}


/**
 * Reduce legal entities to relevant ones.
 * 
 * Filter the legal entities after localized alphabetical sorting.
 * 
 * @param  array<string, string> $legal_entities The current legal entities.
 * 
 * @return array<string, string>                 The filtered legal entities.
 */
function impressum_legal_entity_after_sort( array $legal_entities ): array {
	return [
		'self'       => $legal_entities['self'],
		'individual' => $legal_entities['individual'],
	];
}


/**
 * Reduce up-sell bloat & clutter from admin settings-page.
 *
 * @return void
 */
function cleanup_admin_ui(): void {
	echo '<style>
		.settings_page_impressum .nav-tab-wrapper,
		.settings_page_impressum #legal_entity + .notice-warning {
			display: none!important;
		}
	</style>';
}

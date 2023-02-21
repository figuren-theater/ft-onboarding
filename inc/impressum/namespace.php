<?php
/**
 * Figuren_Theater Onboarding Impressum.
 *
 * @package figuren-theater/onboarding/impressum
 */

namespace Figuren_Theater\Onboarding\Impressum;

use FT_VENDOR_DIR;

use Figuren_Theater\inc\Geo;
use Figuren_Theater\Options;

use function add_action;
use function add_filter;
use function is_admin;

const BASENAME   = 'impressum/impressum.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;
const OPTION     = 'impressum_imprint_options';

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );
	
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}


function load_plugin() {

	require_once PLUGINPATH;

	add_filter( 'pre_update_option_' . OPTION, __NAMESPACE__ . '\\pre_update_ft_geo_option_from_imprint', 10, 3 );

	if ( ! is_admin() )
		return;

	add_action( 'impressum_country_after_sort', __NAMESPACE__ . '\\impressum_country_after_sort' );
	add_action( 'impressum_legal_entity_after_sort', __NAMESPACE__ . '\\impressum_legal_entity_after_sort' );

	add_action( 'admin_head-settings_page_impressum', __NAMESPACE__ . '\\cleanup_admin_ui' );
}


function filter_options() {
	
	$_options = [
		'dismissed-impressum_welcome_notice' => true,
		// OPTION => [] // DO NOT HANDLE, as it's the user-data
	];

	// gets added to the 'OptionsCollection' 
	// from within itself on creation
		new Options\Factory( 
			$_options, 
			'Figuren_Theater\Options\Option', 
			BASENAME, 
		);
}


// set Impressum-location, ft_geo-catgeories and WP_LANG
// in one go, by just .... updating an option
// 
function pre_update_ft_geo_option_from_imprint( mixed $new_value, mixed $old_value, string $option_name ) : mixed {

	// do nothing,
	// if nothing (on the address) has changed
	// $new_value['country'] could be unset by Figuren_Theater\Onboarding\Sites\Installation\set_imprint_page()
	// so check it
	if ( isset($new_value['country']) && 
		 // can be bool, if non existent yet
		 isset($old_value['country']) && 
		 $old_value['country'] === $new_value['country']
		&&
		$old_value['address'] === $new_value['address']
	)
		return $new_value;

	// otherwise
	// start the geo-engines :)
	$ft_geo_options_bridge = new Geo\ft_geo_options_bridge;
	$ft_geo = $ft_geo_options_bridge->update_option_ft_geo( $old_value, $new_value, $option_name );

	// set adress to verified version
	if ( isset( $ft_geo['address'] ) && ! empty( $ft_geo['address'] ) ) {
		$new_value['address'] = $ft_geo['address'];
	} else {
		// or reset
		$new_value['address'] = $old_value['address'];
	} 
	// set country to verified version
	if ( isset( $ft_geo['geojson']['properties']['address']['country'] )) {
		// crazy country codes by 'Impressum' plugin
		$_country_helper = [
			'de' => 'deu',
			'at' => 'aut',
			'ch' => 'che',
		];
		$new_value['country'] = $_country_helper[ $ft_geo['geojson']['properties']['address']['country_code'] ];
	} else {
		// or reset
		$new_value['country'] = $old_value['country'];
	}

	// go on and save
	return $new_value;

}


/**
 * Filter the countries after localized alphabetical sorting.
 * 
 * @param    array      $countries The current countries
 */
function impressum_country_after_sort( array $countries ) : array {
	return [
		'deu' => $countries['deu'],
		'aut' => $countries['aut'],
		'che' => $countries['che'],
	];
}


/**
 * Filter the legal entities after localized alphabetical sorting.
 * 
 * @param    array     $countries The current countries
 */
function impressum_legal_entity_after_sort( array $legal_entities ) : array {
	return [
		'self'       => $legal_entities['self'],
		'individual' => $legal_entities['individual'],
	];
}


function cleanup_admin_ui() : void {
	echo '<style>
		.settings_page_impressum .nav-tab-wrapper,
		.settings_page_impressum #legal_entity + .notice-warning {
			display: none!important;
		}
	</style>';
}

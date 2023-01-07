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

const BASENAME   = 'impressum/impressum.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;


/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	// add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );
	
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}


function load_plugin() {

	require_once PLUGINPATH;

	add_action( 'update_option_impressum_imprint_options', __NAMESPACE__ . '\\update_ft_geo_option_from_imprint', 10, 3 );

	add_action( 'impressum_country_after_sort', __NAMESPACE__ . '\\impressum_country_after_sort' );
	add_action( 'impressum_legal_entity_after_sort', __NAMESPACE__ . '\\impressum_legal_entity_after_sort' );

	add_action( 'admin_head-settings_page_impressum', __NAMESPACE__ . '\\cleanup_admin_ui' );
}


function filter_options() {
	
	$_options = [

	];

	// gets added to the 'OptionsCollection' 
	// from within itself on creation
	new Options\Option(
		'',
		$_options,
		BASENAME
	);
}


function update_ft_geo_option_from_imprint( mixed $old_value, mixed $new_value, string $option_name ) : void {

	// do nothing,
	// if nothing has changed
	if ($old_value['country'] === $new_value['country']
		&&
		$old_value['address'] === $new_value['address']
	)
		return;

	// otherwise
	// start the geo-engines :)
	$ft_geo_options_bridge = new Geo\ft_geo_options_bridge;
	$ft_geo = $ft_geo_options_bridge->update_option_ft_geo( $old_value, $new_value, $option_name );
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

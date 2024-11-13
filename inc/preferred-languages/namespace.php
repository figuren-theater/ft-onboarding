<?php
/**
 * Figuren_Theater Onboarding Preferred_Languages.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Preferred_Languages;

use Figuren_Theater\Onboarding\Impressum;
use FT_VENDOR_DIR;
use Figuren_Theater;
use function add_action;
use function update_option;

const BASENAME   = 'preferred-languages/preferred-languages.php';
const PLUGINPATH = '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}


/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant

	// Fires after the value of 'impressum_imprint_options' has been successfully updated.
	// Because 'update_option_' is not triggered reliably, so switch to 'pre_update_option_'.
	add_filter( 'pre_update_option_' . Impressum\OPTION, __NAMESPACE__ . '\\set_pref_lang_from_impressum', 100, 2 );
}


/**
 * 'ft_geo' was successfully changed, 
 * so lets change 'WPLANG' accordingly.
 * 
 * Filters a specific option before its value is (maybe) serialized and updated.
 *
 * The dynamic portion of the hook name, `$option`, refers to the option name.
 *
 * @see https://developer.wordpress.org/reference/hooks/pre_update_option_option/
 *
 * @param  array<string, string>      $new_value The new option value.
 * @param  array<string, string>|bool $old_value The old option value.
 * 
 * @return array<string, string>
 */
function set_pref_lang_from_impressum( array $new_value, array|bool $old_value ): array {

	// Do nothing, if nothing (on the address) has changed.
	// $o['country'] could be unset by Figuren_Theater\Onboarding\Sites\Installation\set_imprint_page(),
	// so check it.
	if ( isset( $new_value['country'] ) && 
		// Can be bool, if non existent yet.
		isset( $old_value['country'] ) && 
		$old_value['country'] === $new_value['country'] &&
		$old_value['address'] === $new_value['address']
	) {
		return $new_value;
	}

	// Change order of default translations, 
	// based on sites' country.
	switch ( $new_value['country'] ) {

		case 'che':
			$_informal_defaults = [
				'de_CH_informal',
				'de_AT',
				'de_DE',
				'de_DE_formal',
			];
			$_formal_defaults   = [
				'de_CH',
				'de_DE_formal',
				'de_CH_informal', // Fallback, better than default en_US.
				'de_DE', // Fallback, better than default en_US.
			];
			break;

		case 'aut':
			$_informal_defaults = [
				'de_AT',
				'de_CH_informal',
				'de_DE',
				'de_DE_formal',
			];
			$_formal_defaults   = [
				'de_DE_formal',
				'de_CH',
				'de_AT', // Fallback, better than default en_US.
				'de_DE', // Fallback, better than default en_US.
			];
			break;

		case 'deu':
		default:
			// This is the informal-default,
			// the typical use-case for theater-people.
			$_informal_defaults = [
				'de_DE',
				'de_CH_informal',
				'de_AT',
				'de_DE_formal',
			];

			$_formal_defaults = [
				'de_DE_formal',
				'de_CH',
				'de_DE', // Fallback, better than default en_US.
			];
			break;
	}

	// Get current 'ft_site'.
	$ft_site    = Figuren_Theater\FT::site();
	$use_formal = (bool) $ft_site->has_feature( [ 'in-foermlicher-sprache' ] );

	// Switch formal and informal translations, based on choosen feature.
	$_defaults = ( $use_formal ) ? $_formal_defaults : $_informal_defaults;

	update_option( 'preferred_languages', join( ',', $_defaults ), true );

	update_option( 'WPLANG', $_defaults[0], true );

	return $new_value;
}

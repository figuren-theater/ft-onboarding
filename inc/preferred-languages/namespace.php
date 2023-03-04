<?php
/**
 * Figuren_Theater Onboarding Preferred_Languages.
 *
 * @package figuren-theater/onboarding/preferred_languages
 */

namespace Figuren_Theater\Onboarding\Preferred_Languages;

use Figuren_Theater\Onboarding\Impressum;

use FT_VENDOR_DIR;

use Figuren_Theater; // FT

use function add_action;
use function update_option;

const BASENAME   = 'preferred-languages/preferred-languages.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}

function load_plugin() {

	require_once PLUGINPATH;

	// Fires after the value of 'impressum_imprint_options' has been successfully updated.
	// 
	// update_option_ is not triggered reliably
	// so switch to pre_update_option_
	add_action( 'pre_update_option_' . Impressum\OPTION, __NAMESPACE__ . '\\set_pref_lang_from_impressum', 100, 2 );

}


/**
 * 'ft_geo' was successfully changed, 
 * so lets change 'WPLANG' accordingly.
 * 
 * Fires after the value of a specific option has been successfully updated.
 *
 * The dynamic portion of the hook name, `$option`, refers to the option name.
 *
 * @since 2.0.1
 * @since 4.4.0 The `$option` parameter was added.
 *
 * @param mixed  $old_o The old option value.
 * @param mixed  $o     The new option value.
 * @param string $option    Option name.
 */
// function set_pref_lang_from_impressum( $old_o, $o ) {
function set_pref_lang_from_impressum(  $o, $old_o ) : array {

	// do nothing,
	// if nothing (on the address) has changed
	// $o['country'] could be unset by Figuren_Theater\Onboarding\Sites\Installation\set_imprint_page()
	// so check it
	if ( isset($o['country']) && 
		 // can be bool, if non existent yet
		 isset($old_o['country']) && 
		 $old_o['country'] === $o['country'] &&
		 $old_o['address'] === $o['address']
	)
		return $o;

	// change order of default translations, 
	// based on sites' country
	switch ($o['country']) {

		case 'che':
			$_informal_defaults = [
				'de_CH_informal',
				'de_AT',
				'de_DE',
				'de_DE_formal',
			];
			$_formal_defaults = [
				'de_CH',
				'de_DE_formal',
				'de_CH_informal', // fallback, better than default en_US
				'de_DE', // fallback, better than default en_US
			];
			break;

		case 'aut':
			$_informal_defaults = [
				'de_AT',
				'de_CH_informal',
				'de_DE',
				'de_DE_formal',
			];
			$_formal_defaults = [
				'de_DE_formal',
				'de_CH',
				'de_AT', // fallback, better than default en_US
				'de_DE', // fallback, better than default en_US
			];
			break;

		case 'deu':
		default:
			// this is the informal-default
			// the typical use-case for theater-people
			$_informal_defaults = [
				'de_DE',
				'de_CH_informal',
				'de_AT',
				'de_DE_formal',
			];

			$_formal_defaults = [
				'de_DE_formal',
				'de_CH',
				'de_DE', // fallback, better than default en_US
			];
			break;
	}

	// get current ft_site
	$ft_site    = Figuren_Theater\FT::site();
	$use_formal = (bool) $ft_site->has_feature( [ 'in-foermlicher-sprache' ] );

	// switch formal and informal translations, based on choosen feature
	$_defaults = ( $use_formal ) ? $_formal_defaults : $_informal_defaults;

	//
	update_option( 'preferred_languages', join( ',', $_defaults ), 'yes' );

	//
	update_option( 'WPLANG', $_defaults[0], 'yes' );

	// 
	return $o;
}

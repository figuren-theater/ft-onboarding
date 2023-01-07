<?php
/**
 * Figuren_Theater Onboarding Preferred_Languages.
 *
 * @package figuren-theater/onboarding/preferred_languages
 */

namespace Figuren_Theater\Onboarding\Preferred_Languages;

use FT_VENDOR_DIR;

use Figuren_Theater\FT;

use function add_action;
use function update_option;

const BASENAME   = 'preferred-languages/preferred-languages.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}

function load_plugin() {

	require_once PLUGINPATH;

	// Fires after the value of 'impressum_imprint_options' has been successfully updated.
	add_action( 'update_option_impressum_imprint_options', __NAMESPACE__ . '\\set_pref_lang_from_impressum', 100, 2 );


	// Weird ... maybe a
	// @TODO
	// 
	// Calling this hook
	// results in a duplicated the call 
	// to update_option('impressum_imprint_options')
	// which is weird.
	// 
	// Why do we want do this?
	// Disabled for now.
	// 
	// Lets see.
	// add_action( 'save_post_' . $ft_site, __NAMESPACE__ . '\\set_pref_lang_from_ft_site_update', 100, 3 );


	// DEBUG theese functions.
	// add_action( 'admin_menu', __NAMESPACE__ . '\\debug' );
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
function set_pref_lang_from_impressum( $old_o, $o ) {

	// do nothing,
	// if sth. is wrong
	if ( ! isset( $o['country'] ) )
		return;

	// do nothing,
	// if nothing (relevant) has changed
	if ( $old_o['country'] === $o['country'] )
		return;

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
	$use_formal = $ft_site->has_feature( [ 'in-foermlicher-sprache' ] );

	// switch formal and informal translations, based on choosen feature
	$_defaults = ( $use_formal ) ? $_formal_defaults : $_informal_defaults;

	//
	update_option( 'preferred_languages', join( ',', $_defaults ), 'yes' );

	//
	update_option( 'WPLANG', $_defaults[0], 'yes' );
}


/**
 * DISABLED for weird reasons.
 * Read above.
 * 
 * [set_pref_lang_from_ft_site_update description]
 * 
 * @param int      $post_ID [description]
 * @param WP_Post  $post    [description]
 * @param bool     $update  [description]
function set_pref_lang_from_ft_site_update( int $post_ID, \WP_Post $post, bool $update ) {

	// get current ft_site
	$ft_site = Figuren_Theater\FT::site();

	// ... maybe a
	// @TODO
	// 
	// this could get risky,
	// when those 'imprint...'-data is changed from 
	// somewhere remote.
	// Could be better to check for the (unsure) 'original_blog_id' post_meta
	// or to go with a switch_to_blog().
	// 
	// Let's see.
	if ( $ft_site->get_site_post_id() !== $post_ID )
		return;

	set_pref_lang_from_impressum(
		['country'=>''],
		\get_option(
			'impressum_imprint_options',
			['country'=>'deu']
		)
	);
}
 */


function debug() {
	// \add_filter( 
	//	'pre_option_preferred_languages',
	//	function ($option)
	//	{
	//		return 'de_DE,de_DE_formal';
	//	},
	//	30,
	//	1
	// );
	\add_filter( 
		'pre_option_WPLANG',
		function ($option)
		{
			return 'de_DE';
		},
		30,
		1
	);
	\do_action( 'qm/error', \get_option( 'preferred_languages' ) );
	\do_action( 'qm/error', \get_option( 'WPLANG' ) );
}

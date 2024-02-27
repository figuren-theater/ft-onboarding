<?php
/**
 * Figuren_Theater Onboarding WP_Approve_User.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\WP_Approve_User;

use FT_VENDOR_DIR;

use Figuren_Theater;
use Figuren_Theater\Options;
use function Figuren_Theater\get_config;

use Obenland_Wp_Approve_User;

use function add_action;
use function add_filter;
use function remove_action;

const BASENAME   = 'wp-approve-user/wp-approve-user.php';
const PLUGINPATH = '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'Figuren_Theater\loaded', __NAMESPACE__ . '\\filter_options', 11 );

	// the plugin itself will init on 'plugins_loaded:0'
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', -1 );
}

/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	$config = Figuren_Theater\get_config()['modules']['onboarding'];
	if ( ! $config['wp-approve-user'] ) {
		return;
	}

	// needed to load the plugin
	add_filter( 'pre_option_users_can_register', '__return_true' );

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant


	// the plugin itself will load on 'plugins_loaded:10'
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\unload_plugin_parts', 11 );
}


function unload_plugin_parts() {
	// do check if wp-user-approve is loaded !!
	$wpau = Obenland_Wp_Approve_User::get_instance();
	if ( ! $wpau ) {
		return;
	}

	// Remove plugins menu
	remove_action( 'network_admin_menu', [ $wpau, 'admin_menu' ], 10 );
}


function filter_options(): void {

	$_options = [
		'wpau-send-approve-email'   => 0,

		/**
		 * To take advantage of dynamic data, 
		 * use the following placeholders: 
		 * 
		 * `USERNAME`
		 * `BLOG_TITLE`
		 * `BLOG_URL`
		 * `LOGINLINK`
		 * `SITE_NAME`
		 * 
		 * Username will be the user login in most cases.
		 */
		'wpau-approve-email'        => '',
		'wpau-send-unapprove-email' => 0,
		'wpau-unapprove-email'      => '',

	];

	new Options\Option(
		'wp-approve-user',
		$_options,
		BASENAME,
	);
}

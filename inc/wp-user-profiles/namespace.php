<?php
/**
 * Figuren_Theater Onboarding WP_User_Profiles.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\WP_User_Profiles;

use FT_VENDOR_DIR;
use Figuren_Theater;
use function add_action;
use function is_admin;
use function is_network_admin;
use function remove_meta_box;

const BASENAME   = 'wp-user-profiles/wp-user-profiles.php';
const PLUGINPATH = '/stuttter/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}


/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	$config = Figuren_Theater\get_config()['modules']['onboarding'];
	if ( ! $config['wp-user-profiles'] ) {
		return;
	}

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
	
	// We need the whole plugin to be loaded everywhere
	// because of the modified profile page url
	// that could be needed by the admin-bar
	// 
	// but we dont need all of this in the frontend, so ...
	if ( ! is_admin() ) {
		return;
	}

	// Remove some metaboxes.
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\\remove_meta_boxes', 100, 1 );
}


/**
 * Remove metaboxes from the user-profile screen.
 *
 * @return void
 */
function remove_meta_boxes(): void {
	
	$_is_network_screen = ( is_network_admin() ) ? '-network' : '';

	// Remove 'application passwords' metabox.
	remove_meta_box( 'application', 'users_page_account' . $_is_network_screen, 'normal' );
	
	// Remove '(native) langugages' metabox.
	remove_meta_box( 'language', 'users_page_account' . $_is_network_screen, 'normal' );
	
	// Remove 'personal options' metabox.
	remove_meta_box( 'options', 'users_page_options' . $_is_network_screen, 'normal' );
}

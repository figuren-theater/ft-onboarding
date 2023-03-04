<?php
/**
 * Figuren_Theater Onboarding WP_User_Profiles.
 *
 * @package figuren-theater/onboarding/wp-user-profiles
 */

namespace Figuren_Theater\Onboarding\WP_User_Profiles;

use FT_VENDOR_DIR;

use Figuren_Theater;
use function Figuren_Theater\get_config;

use function add_action;
use function is_admin;
use function is_network_admin;
use function remove_meta_box;

const BASENAME   = 'wp-user-profiles/wp-user-profiles.php';
const PLUGINPATH = FT_VENDOR_DIR . '/stuttter/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}

function load_plugin() {

	$config = Figuren_Theater\get_config()['modules']['onboarding'];
	if ( ! $config['wp-user-profiles'] )
		return; // early

	require_once PLUGINPATH;
	
	// we need the whole plugin to be loaded everywhere
	// because of the modified profile page url
	// that could be needed by the admin-bar
	// 
	// but we dont need all of this in the frontend, so ...
	if ( ! is_admin() )
		return; // early

	// Remove some metaboxes
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\\remove_meta_boxes', 100, 1 );
}


function remove_meta_boxes() {
	
	$_is_network_screen = ( is_network_admin() ) ? '-network' : '';

	// application passwords
	remove_meta_box( 'application', 'users_page_account' . $_is_network_screen, 'normal' );
	
	// (native) langugages
	remove_meta_box( 'language', 'users_page_account' . $_is_network_screen, 'normal' );
	
	// personal options
	remove_meta_box( 'options', 'users_page_options' . $_is_network_screen, 'normal' );
}

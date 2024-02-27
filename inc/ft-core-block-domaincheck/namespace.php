<?php
/**
 * Figuren_Theater Onboarding FT_Core_Block_Domaincheck.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\FT_Core_Block_Domaincheck;

use FT_VENDOR_DIR;
use Figuren_Theater;
use function add_action;
use function is_network_admin;
use function is_user_admin;

const BASENAME   = 'ft-core-block-domaincheck/ft-core-block-domaincheck.php';
const PLUGINPATH = '/figuren-theater/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap() {

	add_action( 'init', __NAMESPACE__ . '\\load_plugin', 8 );
}

/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	if ( is_network_admin() || is_user_admin() ) {
		return;
	}

	$config = Figuren_Theater\get_config()['modules']['onboarding'];
	if ( ! $config['ft-core-block-domaincheck'] ) {
		return;
	}

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
}

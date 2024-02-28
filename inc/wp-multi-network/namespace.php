<?php
/**
 * Figuren_Theater Onboarding WP_Multi_Network.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\WP_Multi_Network;

use FT_VENDOR_DIR;

use function add_action;

const BASENAME   = 'wp-multi-network/wpmn-loader.php';
const PLUGINPATH = '/stuttter/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}

/**
 * Conditionally load the plugin itself and its modifications.
 *
 * @return void
 */
function load_plugin(): void {

	require_once FT_VENDOR_DIR . PLUGINPATH; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
}

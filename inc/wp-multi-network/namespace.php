<?php
/**
 * Figuren_Theater Onboarding WP_Multi_Network.
 *
 * @package figuren-theater/onboarding/wp_multi_network
 */

namespace Figuren_Theater\Onboarding\WP_Multi_Network;

use FT_VENDOR_DIR;

use function add_action;

const BASENAME   = 'wp-multi-network/wpmn-loader.php';
const PLUGINPATH = FT_VENDOR_DIR . '/stuttter/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load_plugin', 9 );
}

function load_plugin() {

	require_once PLUGINPATH;
}

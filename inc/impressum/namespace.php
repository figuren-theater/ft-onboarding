<?php
/**
 * Figuren_Theater Onboarding Impressum.
 *
 * @package figuren-theater/onboarding/impressum
 */

namespace Figuren_Theater\Onboarding\Impressum;

use FT_VENDOR_DIR;

use function add_action;

const BASENAME   = 'impressum/impressum.php';
const PLUGINPATH = FT_VENDOR_DIR . '/wpackagist-plugin/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin', 4 );
}

function load_plugin() {

	require_once PLUGINPATH;
}

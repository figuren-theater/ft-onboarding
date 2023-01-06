<?php
/**
 * Figuren_Theater Onboarding FT_Core_Block_Domaincheck.
 *
 * @package figuren-theater/onboarding/ft_core_block_domaincheck
 */

namespace Figuren_Theater\Onboarding\FT_Core_Block_Domaincheck;

use FT_VENDOR_DIR;

use Figuren_Theater;
use function Figuren_Theater\get_config;

use function add_action;
use function is_network_admin;
use function is_user_admin;

const BASENAME   = 'ft-core-block-domaincheck/ft-core-block-domaincheck.php';
const PLUGINPATH = FT_VENDOR_DIR . '/figuren-theater/' . BASENAME;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'init', __NAMESPACE__ . '\\load_plugin', 9 );
}

function load_plugin() {

	if ( is_network_admin() || is_user_admin() )
		return;
	
	$config = Figuren_Theater\get_config()['modules']['onboarding'];
	if ( ! $config['ft-core-block-domaincheck'] )
		return; // early

	require_once PLUGINPATH;
}

<?php
/**
 * Figuren_Theater Onboarding.
 *
 * @package figuren-theater/onboarding
 */

namespace Figuren_Theater\Onboarding;

use Altis;
use function Altis\register_module;


/**
 * Register module.
 */
function register() {

	$default_settings = [
		'enabled'                   => true, // needs to be set
		'ft-core-block-domaincheck' => false,
		'wp-approve-user'           => false,
		'wp-user-profiles'          => true,
	];
	$options = [
		'defaults' => $default_settings,
	];

	Altis\register_module(
		'onboarding',
		DIRECTORY,
		'Onboarding',
		$options,
		__NAMESPACE__ . '\\bootstrap'
	);
}

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	// Plugins
	// Impressum\bootstrap();
	FT_Core_Block_Domaincheck\bootstrap();
	WP_Approve_User\bootstrap();
	WP_Multi_Network\bootstrap();
	WP_User_Profiles\bootstrap();
	
	// Best practices
	Sites\bootstrap();
	Users\bootstrap();
}

<?php
/**
 * Figuren_Theater Onboarding.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding;

use Altis;


/**
 * Register module.
 */
function register() {

	$default_settings = [
		'enabled'                   => true, // Needs to be set.
		'ft-core-block-domaincheck' => false,
		'wp-approve-user'           => false,
		'wp-user-profiles'          => true,
	];
	$options          = [
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
 *
 * @return void
 */
function bootstrap(): void {

	// Plugins.
	FT_Core_Block_Domaincheck\bootstrap();
	Impressum\bootstrap();
	Preferred_Languages\bootstrap();
	WP_Approve_User\bootstrap();
	WP_Multi_Network\bootstrap();
	WP_User_Profiles\bootstrap();
	
	// Best practices.
	Sites\bootstrap();
	Users\bootstrap();
}

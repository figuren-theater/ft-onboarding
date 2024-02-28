<?php
/**
 * Figuren_Theater Onboarding Sites.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Sites;

use function add_action;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'init', __NAMESPACE__ . '\\load', 9 );
}


/**
 * Load modifications to Registration- and Installation-workflow for new sites.
 *
 * @return void
 */
function load(): void {

	Registration\bootstrap();
	Installation\bootstrap();
}

<?php
/**
 * Figuren_Theater Onboarding Sites.
 *
 * @package figuren-theater/onboarding/sites
 */

namespace Figuren_Theater\Onboarding\Sites;

use function add_action;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'init', __NAMESPACE__ . '\\load', 9 );
}


function load() : void {

	// 
	Registration\bootstrap();
	Installation\bootstrap();

}

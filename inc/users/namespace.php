<?php
/**
 * Figuren_Theater Onboarding Users.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Users;

use Figuren_Theater\Network\Admin_UI;
use function add_action;
use function wp_update_user;


/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	add_action( 'user_register', __NAMESPACE__ . '\\load', 20 );
}


/**
 * Load modifications to 'user_register' workflow.
 *
 * @param  int $user_id The ID of the new user.
 *
 * @return void
 */
function load( int $user_id ): void {

	$args = [
		'ID' => $user_id,
	];

	$args = set_names( $args );

	$args = set_admin_color( $args );

	wp_update_user( $args );
}


/**
 * Save defaults for 'display_name' and 'nickname' based on users first and last name.
 *
 * @param  array<string, int|string> $args List of arguments to throw at 'wp_update_user()'.
 *
 * @return array<string, int|string>
 */
function set_names( array $args ): array {
	
	$user = get_userdata( (int) $args['ID'] );
	
	if ( empty( $user->first_name ) || empty( $user->last_name ) ) {
		return $args;
	}

	$name = sprintf( '%s %s', $user->first_name, $user->last_name );
	
	$args['display_name'] = $name;
	$args['nickname']     = $name;
	
	return $args;
}

/**
 * Set default admin-color-scheme to 'figurentheater'.
 *
 * @param  array<string, int|string> $args List of arguments to throw at 'wp_update_user()'.
 *
 * @return array<string, int|string> 
 */
function set_admin_color( array $args ): array {
	
	$args['admin_color'] = Admin_UI\Color_Scheme::NAME;
	
	return $args;
}

<?php
/**
 * Figuren_Theater Onboarding Users.
 *
 * @package figuren-theater/onboarding/users
 */

namespace Figuren_Theater\Onboarding\Users;

use Figuren_Theater\Network\Admin_UI;

use function add_action;
use function wp_update_user;


/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'user_register', __NAMESPACE__ . '\\load' );
}


function load( int $user_id ) : void {

	$args = [
		'ID' => $user_id,
	];

	$args = __set_names( $args );

	$args = __set_admin_color( $args );

	wp_update_user( $args );
}


function __set_names( array $args ) : array {
	
	$user = get_userdata( $args['ID'] );
	
	if ( empty($user->first_name) || empty($user->last_name) )
		return $args;

	$name = sprintf( '%s %s', $user->first_name, $user->last_name );
	
	$args['display_name'] = $name;
	$args['nickname']     = $name;
	
	return $args;
}


function __set_admin_color( array $args ) : array {
	
	$args['admin_color'] = Admin_UI\Color_Scheme::NAME;
	
	return $args;
}


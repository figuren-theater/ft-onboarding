<?php
/**
 * Figuren_Theater Onboarding Sites Installation.
 *
 * @package figuren-theater/onboarding/sites/installation
 */

namespace Figuren_Theater\Onboarding\Sites\Installation;

use FT_CORESITES;

use Figuren_Theater; // FT, FT_Query

use Figuren_Theater\inc;

use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Sync;
use Figuren_Theater\Network\Users;

use WP_Site;

use function add_action;
use function do_action;
use function esc_html;
use function get_blog_option;
use function get_bloginfo;
use function is_wp_error;
use function update_option;
use function wp_insert_post;


/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	/**
	 * Fires once a site has been inserted into the database.
	 */
	// 'wp_insert_site' => 'insert_first_content', // kept to remind
	/**
	 * The hook wpmu_new_blog gives you a deprecated notice 
	 * and tells you to use wp_insert_site. 
	 * 
	 * Don’t do that, 
	 * wp_insert_site fires too early. 
	 * 
	 * Instead, use wp_initialize_site with a priority higher than 100, as in:
	 */
	add_action( 'wp_initialize_site', __NAMESPACE__ . '\\load', 900, 2 );
}


function load( WP_Site $new_site, $args ) : void {

	add_action( 
		__NAMESPACE__.'\\insert_first_content', 
		// 'Figuren_Theater\\Network\\Setup\\insert_first_content', 
		__NAMESPACE__ . '\\create_new__ft_site', 
		10, 
		2
	);

	add_action( 
		__NAMESPACE__.'\\insert_first_content', 
		// 'Figuren_Theater\\Network\\Setup\\insert_first_content', 
		__NAMESPACE__ . '\\set_home_page'
	);

	add_action( 
		__NAMESPACE__.'\\insert_first_content', 
		// 'Figuren_Theater\\Network\\Setup\\insert_first_content', 
		__NAMESPACE__ . '\\set_imprint_page',
		10, 
		2
	);

	add_action( 
		__NAMESPACE__.'\\insert_first_content', 
		// 'Figuren_Theater\\Network\\Setup\\insert_first_content', 
		__NAMESPACE__ . '\\set_privacy_page'
	);



	// 
	do_action( 
		__NAMESPACE__.'\\insert_first_content', 
		// 'Figuren_Theater\\Network\\Setup\\insert_first_content', 
		$new_site,
		$args
	);
}


/**
 * Runs when a site's initialization routine should be executed.
 *
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/ms-site.php#L105 
 *
 * @since 5.1.0
 *
 * @param WP_Site $new_site New site object.
 * @param array   $args     Arguments for the initialization.
 */
function create_new__ft_site( WP_Site $new_site, $args ) {
 
 	//  
    if( isset( $_POST['ft_level'] ) && 0 < intval( $_POST['ft_level'] ) )
        $args['ft_level'] = (int) $_POST['ft_level'];

	// 1. create new ft_site WP_Post obj
	$new_ft_site = new Post_Types\Post_Type__ft_site( 
		(int) $new_site->blog_id, 
		$new_site, 
		$args 
	);

	// 2. Init our WP_Query wrapper
	$ft_query = Figuren_Theater\FT_Query::init();

	// 3. and create new 'ft_site'-post in the DB
	$ft_query->save( $new_ft_site );

	// 4. WEIRD !?!!
	//    is this the right place for that call?
	//    
	// set default options and unset 'autoload'
	Figuren_Theater\FT::site()->Options_Manager->new_set_and_cleanup_db();
	Figuren_Theater\FT::site()->Options_Manager->register_cron_cleanup();

	// 5. add 'ft_bot' user to new website
	// by calling 'him' short circuited
	$_temp_ft_bot = Users\ft_bot::id();
}


/**
 * Fired once a site has been inserted into the database.
 */
function set_home_page() {
	// do we have a home in place ?
	// $home_page_id = (int) \get_option( 'page_on_front' );

	// if the home-page already exists 
	// JUMP OUT
	// if ( $home_page_id && inc\helper::post_id_exists( $home_page_id ) ) {
		// return;
	// }


	// if the home-page not exists , go on
	// and create one ...

	// get HTML homepage template from file
	// because its easier to maintain (for the moment)
	#@TODO		$_ft_homepage_template = \file_get_contents( __DIR__ . '/../../../assets/html/figuren_theater_usersites__default-homepage.tmpl.html' );


	$new_home_page = [

		'post_author'    => Users\ft_bot::id(),

		#@TODO			'post_content'   => $_ft_homepage_template,
		'post_title'     => esc_html( get_bloginfo( 'name' ) ),
		'post_name'      => "startseite",

		'post_type'      => "page",  // must be 'page'  to accept the 'page_template' below
		'post_status'    => "publish",
		'menu_order'     => 0,

		'comment_status' => "closed",
		'ping_status'    => "closed",

		// 'meta_input'     => [
		// ],
	];

	// and save it to the db
	$home_page_id = wp_insert_post( $new_home_page, true );

	if ( ! is_wp_error( $home_page_id ) ) {
		//
		update_option( 'page_on_front', (int) $home_page_id, 'yes' );
	} else {
		//
		do_action( 'qm/error', $home_page_id );
	}
}




/**
 * Fired once a site has been inserted into the database.
 * 
 * @param WP_Site $new_site New site object.
 * @param array   $args     Arguments for the initialization.
 */
function set_imprint_page( WP_Site $new_site, $args ) {
	// do we have a imprint in place ?
	// $impressum_imprint_options = \get_option( 'impressum_imprint_options' );

	// $imprint_page_id = ( isset($impressum_imprint_options['page']) ) ? (int) $impressum_imprint_options['page'] : false;

	// if the imprint-page already exists 
	// JUMP OUT
	// if ( $imprint_page_id && inc\helper::post_id_exists( $imprint_page_id ) ) {
		// return;
	// }

	// if the imprint-page not exists , go on
	// and get the ID of the 'main' privacy-page from the network_blog
	// this is the one to pull
	$ft_coresites_ids = array_flip( FT_CORESITES );
	// $remote_site_id = (int) $ft_coresites_ids['root'];
	$remote_site_id = (int) $ft_coresites_ids['mein']; // 
	
	$remote_impressum_imprint_options = get_blog_option( 
		$remote_site_id,
		'impressum_imprint_options',
		false
	);
	$remote_imprint_page_id = ( isset($remote_impressum_imprint_options['page']) ) ? (int) $remote_impressum_imprint_options['page'] : false;

	// if we have nothing from remote 
	// JUMP OUT
	if ( ! $remote_imprint_page_id ) {
		return;
	}

	// establish a connection 
	// and define pulling
	$distributor = new Sync\Pull( 
		[ $remote_imprint_page_id ],
		$remote_site_id,
		'page'
	);
	// run pulling
	$imprint_page_id = $distributor->run();

	// .. and set our option if everything was fine
	if ( ! empty( $imprint_page_id ) && inc\helper::post_id_exists( (int) $imprint_page_id[0] ) ) {

		$impressum_imprint_options = [
			'page'                => (string) $imprint_page_id[0],
			'country'             => 'deu',
			'legal_entity'        => '',
			'name'                => esc_html( get_bloginfo( 'name' ) ),
			'address'             => '',
			'address_alternative' => '',
			'email'               => esc_html( get_bloginfo( 'admin_email' ) ),
			'phone'               => '',
			'fax'                 => '',
			'press_law_person'    => '',
			'vat_id'              => '',
		];

		update_option( 'impressum_imprint_options', $impressum_imprint_options, 'no' );
	}
}





/**
 * Fired once a site has been inserted into the database.
 */
function set_privacy_page() {
	// do we have a privacy-policy in place ?
	// $policy_page_id = (int) \get_option( 'wp_page_for_privacy_policy' );

	// if the privacy-policy-page already exists 
	// JUMP OUT
	// if ( $policy_page_id && inc\helper::post_id_exists( $policy_page_id ) ) {
		// return;
	// }

	// if the privacy-policy-page not exists , go on
	// and get the ID of the 'main' privacy-page from the network_blog
	// this is the one to pull
	$ft_coresites_ids = array_flip( FT_CORESITES );
	// $remote_site_id = (int) $ft_coresites_ids['root'];
	$remote_site_id = (int) $ft_coresites_ids['mein']; //

	$remote_policy_page_id = (int) get_blog_option( 
		$remote_site_id,
		'wp_page_for_privacy_policy',
		false
	);
	// if we have nothing from remote 
	// JUMP OUT
	if ( ! $remote_policy_page_id ) {
		return;
	}

	// establish a connection 
	// and define pulling
	$distributor = new Sync\Pull( 
		[ $remote_policy_page_id ],
		$remote_site_id,
		'page'
	);
	// run pulling
	$policy_page_id = $distributor->run();

	// .. and set our option if everything was fine
	if ( ! empty( $policy_page_id ) ) {
		update_option( 'wp_page_for_privacy_policy', (int) $policy_page_id[0], 'no' );
	}
}

<?php
/**
 * Figuren_Theater Onboarding Sites Installation.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Sites\Installation;

use Figuren_Theater; 
use Figuren_Theater\FeaturesRepo;
use Figuren_Theater\inc;
use Figuren_Theater\Network\Post_Types;
use Figuren_Theater\Network\Sync;
use Figuren_Theater\Network\Users;
use FT_CORESITES;
use WP_Site;
use WP_User;
use function add_action;
use function apply_filters;
use function check_admin_referer;
use function do_action;
use function esc_html;
use function get_blog_option;
use function get_bloginfo;
use function get_current_blog_id;
use function get_userdata;
use function is_wp_error;
use function restore_current_blog;
use function switch_to_blog;
use function update_option;
use function wp_insert_post;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {

	/**
	 * Fires once a site has been inserted into the database.
	 * 
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


/**
 * Fires when a site’s initialization routine should be executed.
 *
 * @param  WP_Site                   $new_site New site object.
 * @param  array<string, int|string> $args     Arguments for the initialization.
 *
 * @return void
 */
function load( WP_Site $new_site, array $args ): void {

	add_action( 
		__NAMESPACE__ . '\\insert_first_content', 
		__NAMESPACE__ . '\\create_new__ft_site', 
		0, 
		2
	);
	
	/* phpcs:ignore 
	add_action( 
		__NAMESPACE__ . '\\insert_first_content', 
		__NAMESPACE__ . '\\set_home_page',
		5, 
		2
	);
	*/

	add_action( 
		__NAMESPACE__ . '\\insert_first_content', 
		__NAMESPACE__ . '\\set_imprint_page',
		5, 
		2
	);

	add_action( 
		__NAMESPACE__ . '\\insert_first_content', 
		__NAMESPACE__ . '\\set_privacy_page',
		5
	);
	
	// Taken from \wp-includes\ms-site.php.
	$switch = false;
	if ( get_current_blog_id() !== $new_site->id ) {
		$switch = true;
		switch_to_blog( $new_site->id ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog
	}

		do_action( 
			__NAMESPACE__ . '\\insert_first_content', 
			$new_site,
			$args
		);

	if ( $switch ) {
		restore_current_blog();
	}
}


/**
 * Runs when a site's initialization routine should be executed.
 *
 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/ms-site.php#L105 
 *
 * @param  WP_Site                   $new_site New site object.
 * @param  array<string, int|string> $args     Arguments for the initialization.
 * 
 * @return void
 */
function create_new__ft_site( WP_Site $new_site, array $args ): void {
	// Defined at wp-admin/network/site-new.php.
	check_admin_referer( 'add-blog', '_wpnonce_add-blog' );

	if ( isset( $_POST['ft_level'] ) && ( \is_string( $_POST['ft_level'] ) || \is_int( $_POST['ft_level'] ) ) && 0 < intval( $_POST['ft_level'] ) ) {
		$args['ft_level'] = (int) $_POST['ft_level'];
	}

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
	// is this the right place for that call?
	//    
	// set default options and unset 'autoload'
	Figuren_Theater\FT::site()->Options_Manager->new_set_and_cleanup_db();
	Figuren_Theater\FT::site()->Options_Manager->register_cron_cleanup();

	// 5. add 'ft_bot' user to new website
	// by calling 'him' short circuited
	Users\ft_bot::id();
}


/**
 * Fired once a site has been inserted into the database.
 *
 * @param  WP_Site                   $new_site New site object.
 * @param  array<string, int|string> $args     Arguments for the initialization.
 * 
 * @return void
 */
function set_home_page( WP_Site $new_site, array $args ) {
	
	// Get the user, who registered the site
	// or fallback to the bot user, if some error occured.
	$post_author = ( isset( $args['user_id'] ) && 0 < $args['user_id'] ) ? $args['user_id'] : Users\ft_bot::id();

	$new_home_page = [

		'post_author'    => $post_author,

		'post_title'     => __( 'Front page', 'default' ),

		'post_type'      => 'page',
		'post_status'    => 'publish',
		'menu_order'     => 0,

		'comment_status' => 'closed',
		'ping_status'    => 'closed',
	];
	
	// Get initial content for the front-page,
	// the default is blank.
	$_ft_frontpage_content = apply_filters( 
		__NAMESPACE__ . '\\frontpage_content',
		false,
		$new_site,
		$args
	);

	if ( is_string( $_ft_frontpage_content ) ) {
		$new_home_page['post_content'] = $_ft_frontpage_content;
	}

	// Save it to the DB.
	$home_page_id = wp_insert_post( $new_home_page, true );

	if ( ! is_wp_error( $home_page_id ) ) {
		update_option( 'page_on_front', (int) $home_page_id, true );
	}
}


/**
 * Fired once a site has been inserted into the database.
 * 
 * @param  WP_Site                   $new_site New site object.
 * @param  array<string, int|string> $args     Arguments for the initialization.
 * 
 * @return void
 */
function set_imprint_page( WP_Site $new_site, array $args ): void {

	// Get the ID of the 'main' imprint-page from the network_blog,
	// this is the one to pull.
	$ft_coresites_ids                 = array_flip( FT_CORESITES );
	$remote_site_id                   = (int) $ft_coresites_ids['mein'];    
	$remote_impressum_imprint_options = get_blog_option( 
		$remote_site_id,
		'impressum_imprint_options',
		false
	);
	$remote_imprint_page_id           = false;
	if ( \is_array( $remote_impressum_imprint_options ) && isset( $remote_impressum_imprint_options['page'] ) && ( \is_string( $remote_impressum_imprint_options['page'] ) || \is_int( $remote_impressum_imprint_options['page'] ) ) ) {
		$remote_imprint_page_id = (int) $remote_impressum_imprint_options['page'];
	}

	// If we have nothing from remote, JUMP OUT.
	if ( false === $remote_imprint_page_id ) {
		return;
	}

	// Establish a connection and define pulling.
	$distributor = new Sync\Pull( 
		[ $remote_imprint_page_id ],
		$remote_site_id,
		'page'
	);
	// Run pulling.
	$imprint_page_id = $distributor->run();

	// Return on failure.
	if ( empty( $imprint_page_id ) || ! inc\helper::post_id_exists( (int) $imprint_page_id[0] ) ) {
		return;
	}

	// Prepare some data, which was collected during registration.
	$person = get_userdata( (int) $args['user_id'] );

	if ( ! $person instanceof WP_User ) {
		return;
	}

	// Get temporary data, collected during registration.
	$user_registration_data = $person->get( FeaturesRepo\TEMP_USER_META );

	if ( ! \is_array( $user_registration_data ) || ! isset( $user_registration_data['adr'] ) || empty( $user_registration_data['adr'] ) ) {
		return;
	}

	$impressum_imprint_options = [
		'page'                => (string) $imprint_page_id[0],
		'country'             => '', // Leave empty as it gets populated on save, if empty.
		'legal_entity'        => 'self', // Means 'self-employed' in the 'Impressum' plugin.
		'name'                => $person->display_name,
		'address'             => $user_registration_data['adr'],
		'address_alternative' => '',
		'email'               => esc_html( get_bloginfo( 'admin_email' ) ),
		'phone'               => '',
		'fax'                 => '',
		'press_law_person'    => $person->display_name,
		'vat_id'              => '',
	];

	// Unset new country,
	// to prevent automatic combinations of ADR + COUNTRY that dont fit.
	// Maybe possible, when 'impressum_imprint_options' is pre-populated during install,
	// but was never really used within this blog.
	unset( $impressum_imprint_options['country'] );
	update_option( 'impressum_imprint_options', $impressum_imprint_options, false );
}


/**
 * Fired once a site has been inserted into the database.
 * 
 * @return void
 */
function set_privacy_page(): void {

	// Get the ID of the 'main' privacy-page from the network_blog,
	// this is the one to pull.
	$ft_coresites_ids      = array_flip( FT_CORESITES );
	$remote_site_id        = (int) $ft_coresites_ids['mein']; 
	$remote_policy_page_id = get_blog_option( 
		$remote_site_id,
		'wp_page_for_privacy_policy',
		false
	);

	// If we have nothing from remote, JUMP OUT.
	if ( ! $remote_policy_page_id || ! ( \is_string( $remote_policy_page_id ) || \is_int( $remote_policy_page_id ) ) ) {
		return;
	}

	// Establish a connection and define pulling.
	$distributor = new Sync\Pull(
		[ \intval( $remote_policy_page_id ) ],
		$remote_site_id,
		'page'
	);
	
	// Run pulling.
	$policy_page_id = $distributor->run();

	// Save our option if everything was fine.
	if ( ! empty( $policy_page_id ) ) {
		update_option( 'wp_page_for_privacy_policy', (int) $policy_page_id[0], false );
	}
}

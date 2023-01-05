<?php
/**
 * Figuren_Theater Onboarding Sites.
 *
 * @package figuren-theater/onboarding/sites
 */

namespace Figuren_Theater\Onboarding\Sites;

use Figuren_Theater\Onboarding;

use Figuren_Theater\Coresites\Post_Types;

use function _e;
use function add_action;
use function add_filter;
use function restore_current_blog;
use function switch_to_blog;

/**
 * Bootstrap module, when enabled.
 */
function bootstrap() {

	add_action( 'muplugins_loaded', __NAMESPACE__ . '\\load' );
}


function load() : void {

	// allow subdomains with 3 chars only
	add_filter( 'minimum_site_name_length', __NAMESPACE__ . '\\minimum_site_name_length' ); 

	// Add a 'level' input to the registration form.
	// add_action( 'signup_blogform', __NAMESPACE__ . '\\add_extra_field_on_blog_signup' ); // FE?
	
	/**
	 * Fires at the end of the new site form in network admin.
	 *
	 * @since 4.5.0
	 */
	add_action( 'network_site_new_form', __NAMESPACE__ . '\\add_extra_field_on_blog_signup' ); // BE

	// DEBUG
	// add_action( 'admin_init', __NAMESPACE__ . '\\debug_ft_Site_Registration', 42 );
}


function minimum_site_name_length( int $length ) : int {
	return 3;
}



// Add text field on blog signup form
function add_extra_field_on_blog_signup() {

	?>
	<table class="form-table" role="presentation">
	<tbody><!-- 
		<tr class="form-field">
			<th scope="row"><label for="ft_level"><?php _e( 'LEVEL ID von websites.fuer.f.t' ); ?> <span class="required">*</span></label></th>
			<td><input style="max-width:25em;" name="ft_level" type="number" class="regular-text" id="ft_level"  aria-describedby="site-ft_level" /></td>
		</tr> -->
		<tr class="form-field">
			<th scope="row"><label for="<?php echo Post_Types\Post_Type__ft_level::NAME; ?>"><?php _e( 'LEVEL ID von websites.fuer.f.t', 'figurentheater' ); ?></label></th>
			<td><?php echo __get_ft_level_select(); ?></td>
		</tr>
	</tbody></table>
	<?php
}

function __ft_level_select() : string {

	// not avail. via composer, 
	// so we have to require it usually
	// if (file_exists( WPMU_PLUGIN_DIR . '/_ft_vendor/wp_dropdown_posts/wp_dropdown_posts.php' ) )
	if (file_exists( Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php' ) )
		// require_once WPMU_PLUGIN_DIR . '/_ft_vendor/wp_dropdown_posts/wp_dropdown_posts.php';
		require_once Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php';
	
	if ( ! function_exists( 'wp_dropdown_posts' ) )
		return '';

	$ft_level_dropdown_args = [
		// 'selected'              => FALSE,
		// 'pagination'            => FALSE,
		'posts_per_page'        => 25,
		'post_status'           => 'publish',
		'cache_results'         => TRUE,
		'cache_post_meta_cache' => TRUE,
		'echo'                  => 0,
		'select_name'           => Post_Types\Post_Type__ft_level::NAME,
		'id'                    => Post_Types\Post_Type__ft_level::NAME,
		// 'class'                 => '',
		'show'                  => 'post_title',
		// 'show_callback'         => NULL,
		'show_option_all'       => 'Choose ft_level as receipe.',
		// 'show_option_none'      => 'No ft_level avail. :(',
		// 'option_none_value'     => '',
		// 'multi'                 => FALSE,
		// 'value_field'           => 'ID',
		// 'order'                 => 'ASC',
		// 'orderby'               => 'post_title',
		

		// WP_Query arguments
		'post_type'             => Post_Types\Post_Type__ft_level::NAME,
		'no_found_rows'         => true,
	];

	return \wp_dropdown_posts( $ft_level_dropdown_args );

}

function __get_ft_level_select() : string {
	// 1. switch to (a) sitemanagement-blog, which has the required 'ft_level'-data
	// TODO // find nice way to get (one of many) sitemanagement-blogs
	$sitemanagement_blog = array_flip( FT_CORESITES )['webs'];
	switch_to_blog( $sitemanagement_blog );

	// 4. get 'ft_level'-posts
	$ft_level_select = __ft_level_select();

	// 5. restore_current_blog();
	restore_current_blog();

	return $ft_level_select;
}


function debug_ft_Site_Registration() {
	// 1. switch to (a) sitemanagement-blog, which has the required 'ft_level'-data
	// TODO // find nice way to get (one of many) sitemanagement-blogs
	$sitemanagement_blog = array_flip( FT_CORESITES )['webs'];
	// \switch_to_blog( $sitemanagement_blog );

	// 4. get 'ft_level'-posts
	// 
	// 4.1 Init our WP_Query wrapper
   // $ft_level_query = \Figuren_Theater\FT_Query::init();


	\do_action( 'qm/info', ' get "ft_level"-posts from site: {site}', [
		'site' => $sitemanagement_blog,
	] );
	// $ft_levels = $ft_level_query->find_many_by_type( 'ft_level', 'publish' );
	// $ft_levels = \wp_list_pluck( $ft_levels, 'post_title', 'ID' );
	// do_action( 'qm/warning', $ft_levels );

	\do_action( 'qm/emergency', __get_ft_level_select() );

	// 5. restore_current_blog();
	// restore_current_blog();
}

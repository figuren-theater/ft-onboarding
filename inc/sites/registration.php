<?php
/**
 * Figuren_Theater Onboarding Sites Registration.
 *
 * @package figuren-theater/ft-onboarding
 */

namespace Figuren_Theater\Onboarding\Sites\Registration;

use FT_CORESITES;
use Figuren_Theater\Onboarding;
use Figuren_Theater\Coresites\Post_Types;
use function _e;
use function esc_html_e;
use function esc_attr;
use function add_action;
use function add_filter;
use function restore_current_blog;
use function switch_to_blog;
use function wp_dropdown_posts;

/**
 * Bootstrap module, when enabled.
 *
 * @return void
 */
function bootstrap(): void {
	add_action( 'init', __NAMESPACE__ . '\\load' );
}


/**
 * Load modifications to Registration-workflow for new sites.
 *
 * @return void
 */
function load(): void {

	// Allow subdomains with 3 chars only.
	add_filter( 'minimum_site_name_length', __NAMESPACE__ . '\\minimum_site_name_length' );

	if ( ! is_admin() ) {
		return;
	}

	add_action( 'network_site_new_form', __NAMESPACE__ . '\\add_extra_field_on_blog_signup' ); // Backend only.
}


/**
 * Allow subdomains with 3 chars only.
 *
 * @return int
 */
function minimum_site_name_length(): int {
	return 3;
}



/**
 * Add text field on blog signup form
 *
 * @return void
 */
function add_extra_field_on_blog_signup(): void {
	?>
	<table class="form-table" role="presentation">
		<tbody>
			<tr class="form-field">
				<th scope="row"><label for="<?php echo esc_attr( Post_Types\Post_Type__ft_level::NAME ); ?>"><?php esc_html_e( 'LEVEL ID von websites.fuer.f.t', 'figurentheater' ); ?></label></th>
				<td><?php \esc_html( get_ft_level_select() ); ?></td>
			</tr>
		</tbody>
	</table>
	<?php
}


function get_ft_level_select(): string {
	// 1. switch to (a) sitemanagement-blog, which has the required 'ft_level'-data
	// TODO #18 // find nice way to get (one of many) sitemanagement-blogs
	$sitemanagement_blog = array_flip( FT_CORESITES )['webs'];
	switch_to_blog( $sitemanagement_blog );

	// 4. get 'ft_level'-posts
	$ft_level_select = ft_level_select();

	// 5. restore_current_blog();
	restore_current_blog();

	return $ft_level_select;
}


function ft_level_select(): string {

	// not avail. via composer,
	// so we have to require it usually
	if ( file_exists( Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php' ) ) {
		require_once Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php';
	}

	if ( ! function_exists( 'wp_dropdown_posts' ) ) {
		return '';
	}

	$ft_level_dropdown_args = [
		// 'selected'              => FALSE,
		// 'pagination'            => FALSE,
		'posts_per_page'        => 25,
		'post_status'           => 'publish',
		'cache_results'         => true,
		'cache_post_meta_cache' => true,
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
		'no_found_rows'         => true, // Useful when pagination is not needed.
	];

	return wp_dropdown_posts( $ft_level_dropdown_args );
}

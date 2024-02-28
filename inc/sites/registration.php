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


/**
 * Get a html-select with potential new levels for site-creation.
 * 
 * @todo https://github.com/figuren-theater/ft-onboarding/issues/18 Find nice way to get (one of many) sitemanagement-blogs.
 *
 * @return string
 */
function get_ft_level_select(): string {
	// 1. Switch to (a) sitemanagement-blog, which has the required 'ft_level'-data.
	$sitemanagement_blog = array_flip( FT_CORESITES )['webs'];
	switch_to_blog( $sitemanagement_blog ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.switch_to_blog_switch_to_blog

	// 4. Get all 'ft_level'-posts.
	$ft_level_select = ft_level_select();

	// 5. Restore to current blog.
	restore_current_blog();

	return $ft_level_select;
}


/**
 * Get potential new levels from current network-site aka network-root.
 *
 * @return string
 */
function ft_level_select(): string {

	// Unfortunately not available via composer,
	// so we have to require it manually.
	if ( file_exists( Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php' ) ) {
		require_once Onboarding\DIRECTORY . '/inc/sites/wp_dropdown_posts/wp_dropdown_posts.php'; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomConstant
	}

	if ( ! function_exists( 'wp_dropdown_posts' ) ) {
		return '';
	}

	$ft_level_dropdown_args = [
		'posts_per_page'        => 25,
		'post_status'           => 'publish',
		'cache_results'         => true,
		'cache_post_meta_cache' => true,
		'echo'                  => 0,
		'select_name'           => Post_Types\Post_Type__ft_level::NAME,
		'id'                    => Post_Types\Post_Type__ft_level::NAME,
		'show'                  => 'post_title',
		'show_option_all'       => 'Choose ft_level as receipe.',

		// Typical WP_Query arguments.
		'post_type'             => Post_Types\Post_Type__ft_level::NAME,
		'no_found_rows'         => true, // Useful when pagination is not needed.
	];

	return wp_dropdown_posts( $ft_level_dropdown_args );
}

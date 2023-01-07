<?php
/**
 * ft-onboarding
 *
 * @package           figuren-theater/onboarding
 * @author            figuren.theater
 * @copyright         2022 figuren.theater
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       figuren.theater | Onboarding
 * Plugin URI:      https://github.com/figuren-theater/ft-onboarding
 * Description:     Onboarding of new users, site- and user creation for a WordPress Multisite network like figuren.theater.
 * Version:           1.0.7
 * Requires at least: 6.0
 * Requires PHP:      7.2
 * Author:            figuren.theater
 * Author URI:        https://figuren.theater
 * Text Domain:       figurentheater
 * Domain Path:       /languages
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI:        https://github.com/figuren-theater/ft-onboarding
 */
namespace Figuren_Theater\Onboarding;

const DIRECTORY = __DIR__;

add_action( 'altis.modules.init', __NAMESPACE__ . '\\register' );

<?php
/**
 * Plugin Name: Peterwolf Corner Ribbon
 * Plugin URI: https://github.com/peterwolf-pl/Peterwolf-Corner-Ribbon
 * Description: Adds a customizable diagonal corner ribbon overlay to the public-facing website.
 * Version: 1.0.3
 * Author: peterwolf.pl
 * Author URI: https://peterwolf.pl
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: peterwolf-corner-ribbon
 * Requires at least: 6.4
 * Requires PHP: 7.4
 *
 * @package PeterwolfCornerRibbon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PWCR_VERSION', '1.0.3' );
define( 'PWCR_FILE', __FILE__ );
define( 'PWCR_DIR', plugin_dir_path( __FILE__ ) );
define( 'PWCR_URL', plugin_dir_url( __FILE__ ) );

require_once PWCR_DIR . 'includes/class-peterwolf-corner-ribbon.php';

register_activation_hook( PWCR_FILE, array( 'Peterwolf_Corner_Ribbon', 'activate' ) );

/**
 * Returns the plugin instance.
 *
 * @return Peterwolf_Corner_Ribbon
 */
function pwcr_corner_ribbon() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new Peterwolf_Corner_Ribbon();
	}

	return $plugin;
}

pwcr_corner_ribbon();

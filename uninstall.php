<?php
/**
 * Removes plugin settings when the plugin is deleted.
 *
 * @package PeterwolfCornerRibbon
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'pwcr_settings' );

if ( is_multisite() ) {
	delete_site_option( 'pwcr_settings' );
}

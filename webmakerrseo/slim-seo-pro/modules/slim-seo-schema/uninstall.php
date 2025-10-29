<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || die;

// phpcs:ignore
// Use the same constant as Slim SEO.
$delete_data = defined( 'SLIM_SEO_DELETE_DATA' ) ? SLIM_SEO_DELETE_DATA : false;
if ( ! $delete_data ) {
	return;
}

delete_option( 'slim_seo_schemas' );

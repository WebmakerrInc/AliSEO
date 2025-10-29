<?php
/**
 * Plugin Name: WebmakerrSEO
 * Plugin URI:  https://webmakerr.com/webmakerrseo
 * Description: Unified SEO toolkit combining Slim SEO and Slim SEO Pro features into a single plugin.
 * Author:      Webmakerr
 * Author URI:  https://webmakerr.com
 * Version:     1.0.0
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * Text Domain: webmakerrseo
 * License:     GPL v3
 */

defined( 'ABSPATH' ) || die;

// General plugin constants.
define( 'WEBMAKERRSEO_VERSION', '1.0.0' );
define( 'WEBMAKERRSEO_PLUGIN_FILE', __FILE__ );
define( 'WEBMAKERRSEO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WEBMAKERRSEO_PLUGIN_SLUG', dirname( WEBMAKERRSEO_PLUGIN_BASENAME ) );
define( 'WEBMAKERRSEO_DIR', plugin_dir_path( __FILE__ ) );
define( 'WEBMAKERRSEO_URL', plugin_dir_url( __FILE__ ) );
define( 'WEBMAKERRSEO_UNIFIED', true );

// Maintain backward compatibility with Slim SEO constants.
define( 'SLIM_SEO_DIR', WEBMAKERRSEO_DIR . 'slim-seo/' );
define( 'SLIM_SEO_URL', WEBMAKERRSEO_URL . 'slim-seo/' );
define( 'SLIM_SEO_REDIRECTS', 'ss_redirects' );
define( 'SLIM_SEO_DELETE_404_LOGS_ACTION', 'delete_404_logs' );
define( 'SLIM_SEO_VER', '4.7.0' );
define( 'SLIM_SEO_DB_VER', 1 );
define( 'SLIM_SEO_PLUGIN_BASENAME', WEBMAKERRSEO_PLUGIN_BASENAME );

// Maintain backward compatibility with Slim SEO Pro constants.
define( 'SLIM_SEO_PRO_DIR', WEBMAKERRSEO_DIR . 'slim-seo-pro/' );
define( 'SLIM_SEO_PRO_URL', WEBMAKERRSEO_URL . 'slim-seo-pro/' );
define( 'SLIM_SEO_PRO_VER', '1.5.0' );
define( 'WEBMAKERRSEO_KEY', 'webmakerrseo-unified-license' );
define( 'SLIM_SEO_PRO_KEY', WEBMAKERRSEO_KEY );

autoload_plugin_dependencies();

initialize_slim_seo();
initialize_slim_seo_pro();

/**
 * Load plugin dependencies from the bundled Slim SEO and Slim SEO Pro packages.
 */
function autoload_plugin_dependencies(): void {
        if ( file_exists( SLIM_SEO_DIR . 'vendor/autoload.php' ) ) {
                require_once SLIM_SEO_DIR . 'vendor/autoload.php';
        }

        if ( file_exists( SLIM_SEO_PRO_DIR . 'vendor/autoload.php' ) ) {
                require_once SLIM_SEO_PRO_DIR . 'vendor/autoload.php';
        }
}

/**
 * Bootstrap the Slim SEO core container using the unified plugin file.
 */
function initialize_slim_seo(): void {
        new \SlimSEO\Activator( WEBMAKERRSEO_PLUGIN_FILE );
        new \SlimSEO\Deactivator( WEBMAKERRSEO_PLUGIN_FILE );

        $container = new \SlimSEO\Container();
        $container->register_services();

        // Initialize at priority 5 to be able to disable core sitemaps completely which runs at priority 10.
        add_action( 'init', [ $container, 'init' ], 5 );
}

/**
 * Bootstrap Slim SEO Pro features with licensing disabled for the unified plugin.
 */
function initialize_slim_seo_pro(): void {
        new \SlimSEOPro\Loader( WEBMAKERRSEO_PLUGIN_BASENAME );
}

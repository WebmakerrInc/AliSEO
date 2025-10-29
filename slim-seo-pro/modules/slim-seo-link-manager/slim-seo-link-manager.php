<?php
/**
 * Plugin Name: Slim SEO Link Manager
 * Plugin URI:  https://wpslimseo.com/?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo
 * Description: A link manager plugin for WordPress.
 * Author:      Slim SEO
 * Author URI:  https://wpslimseo.com/?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo
 * Version:     1.11.5
 * Text Domain: slim-seo-link-manager
 * Domain Path: /languages/
 *
 * Copyright (C) 2010-2025 Tran Ngoc Tuan Anh. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace SlimSEOPro\LinkManager;

defined( 'ABSPATH' ) || die;

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_DIR' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_DIR', __DIR__ );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_URL' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_VER' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_VER', '1.11.5' );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_IS_SCANNER_RUNNING' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_IS_SCANNER_RUNNING', 'slim_seo_link_manager_is_scanner_running' );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_DEFAULT_STATUS_CODE' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_DEFAULT_STATUS_CODE', 'N/A' );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_ERROR_STATUS_CODE' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_ERROR_STATUS_CODE', 'ERROR' );
}

if ( ! defined( 'SLIM_SEO_LINK_MANAGER_LINKS_CACHE_NAME' ) ) {
	define( 'SLIM_SEO_LINK_MANAGER_LINKS_CACHE_NAME', 'sslm_links_cache' );
}

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

add_action( 'plugins_loaded', function () {
	$slim_seo_link_manager_loader = new Loader();
	$slim_seo_link_manager_loader->init();

	new Activator;
	new Deactivator( __FILE__ );
} );

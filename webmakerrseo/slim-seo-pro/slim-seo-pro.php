<?php
/**
 * Plugin Name:       Slim SEO Pro
 * Plugin URI:        https://wpslimseo.com/products/slim-seo-pro/?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo_pro
 * Description:       Advanced SEO features without the complexity.
 * Version:           1.5.0
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Author:            Slim SEO
 * Author URI:        https://wpslimseo.com/?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo_pro
 * Text Domain:       slim-seo-pro
 * Domain Path:       /languages
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

namespace SlimSEOPro;

defined( 'ABSPATH' ) || die;

define( 'SLIM_SEO_PRO_DIR', plugin_dir_path( __FILE__ ) );
define( 'SLIM_SEO_PRO_URL', plugin_dir_url( __FILE__ ) );
define( 'SLIM_SEO_PRO_VER', '1.5.0' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

new Loader();

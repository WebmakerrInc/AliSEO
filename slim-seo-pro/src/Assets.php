<?php
namespace SlimSEOPro;

class Assets {
	public static function enqueue_build_js( string $name, string $localized_object = '', array $localized_data = [] ): void {
		$asset_file = SLIM_SEO_PRO_DIR . "js/build/$name.asset.php";
		$asset      = [
			'dependencies' => [],
			'version'      => filemtime( SLIM_SEO_PRO_DIR . "js/build/$name.js" ),
		];

		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
		}

		$handle = "slim-seo-pro-build-$name";
		wp_enqueue_script( $handle, SLIM_SEO_PRO_URL . "js/build/$name.js", $asset['dependencies'], $asset['version'], true );

		if ( $localized_object ) {
			wp_localize_script( $handle, $localized_object, $localized_data );
		}

		wp_set_script_translations( $handle, 'slim-seo-pro', SLIM_SEO_PRO_DIR . '/languages' );
	}
}

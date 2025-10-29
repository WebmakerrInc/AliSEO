<?php
namespace SlimSEOPro\Schema\Api;

use WP_REST_Server;

class Specs extends Base {
	public function register_routes() {
		register_rest_route( 'slim-seo-schema', 'specs', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_specs' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function get_specs(): array {
		$dir   = SLIM_SEO_SCHEMA_DIR . '/src/SchemaTypes';
		$files = glob( "$dir/*.php" );

		$specs = [];
		foreach ( $files as $file ) {
			$type = basename( $file, '.php' );
			$spec = require_once $file;
			if ( ! is_array( $spec ) ) {
				continue;
			}

			$spec[]         = [
				'label'   => __( 'Custom', 'slim-seo-schema' ),
				'id'      => 'custom',
				'type'    => 'Custom',
				'tooltip' => __( 'Custom properties for the schema.', 'slim-seo-schema' ),
			];
			$specs[ $type ] = $spec;
		}

		return $specs;
	}
}

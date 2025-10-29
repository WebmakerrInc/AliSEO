<?php
namespace SlimSEOPro\Schema\Api;

use SlimSEOPro\Schema\Settings;
use WP_REST_Server;
use WP_REST_Request;

class Import extends Base {
	public function register_routes(): void {
		register_rest_route( 'slim-seo-schema', 'import', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'import' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function import( WP_REST_Request $request ): bool {
		$data = $request->get_body();
		$data = json_decode( $data, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) || ! $this->validate( $data ) ) {
			return false;
		}

		$schemas = Settings::get_all_schemas();
		foreach ( $data as $id => $schema ) {
			$key             = isset( $schemas[ $id ] ) ? uniqid() : $id;
			$schemas[ $key ] = $schema;
		}

		update_option( Settings::OPTION_NAME, $schemas );
		return true;
	}

	private function validate( array $schemas ): bool {
		foreach ( $schemas as $schema ) {
			if ( empty( $schema['type'] ) ) {
				return false;
			}
		}
		return true;
	}
}

<?php
namespace SlimSEOPro\ContentAnalysis;

use WP_REST_Server;
use WP_REST_Request;
use SlimSEOPro\Api\Base;

class Api extends Base {
	public function register_routes() {
		register_rest_route( self::NAMESPACE, 'content_analysis/image_detail', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_image_detail' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );

		register_rest_route( self::NAMESPACE, 'content_analysis/builder_content', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'builder_content' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function get_image_detail( WP_REST_Request $request ): array {
		$id  = $request->get_param( 'id' );
		$src = $request->get_param( 'src' );

		if ( ! $id ) {
			$id = $this->get_image_id_from_src( $src );
		}

		if ( ! $id ) {
			return [];
		}

		$metadata = wp_get_attachment_metadata( $id );

		if ( empty( $metadata['file'] ) ) {
			return [];
		}

		$file_path = get_attached_file( $id );
		$file_name = basename( $file_path );

		$image_detail = [
			'width'    => $metadata['width'],
			'height'   => $metadata['height'],
			'size'     => round( (int) $metadata['filesize'] / 1024 ),
			'filename' => $file_name,
			'src'      => wp_get_attachment_image_url( $id, 'thumbnail' ),
		];

		return $image_detail;
	}

	private function get_image_id_from_src( string $src ): int {
		global $wpdb;

		$upload_dir = wp_upload_dir();

		if ( ! str_contains( $src, $upload_dir['baseurl'] ) ) {
			return 0;
		}

		$image_path = str_replace( $upload_dir['baseurl'] . '/', '', $src );
		$image_path = preg_replace( '/-\d+x\d+(?=\.[^.\s]{3,4}$)/', '', $image_path );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `ID` FROM {$wpdb->posts}
					WHERE `post_type` = 'attachment'
					AND `guid` LIKE %s",
				'%' . $wpdb->esc_like( $image_path ) . '%'
			)
		);
	}

	public function builder_content( WP_REST_Request $request ): string {
		$post_id = $request->get_param( 'post_id' );

		return apply_filters( 'slim_seo_pro_builder_content', '', $post_id );
	}
}

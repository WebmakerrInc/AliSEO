<?php
namespace SlimSEOPro\Schema\Integrations;

use SlimSEOPro\Schema\Support\Arr;
use WP_Post;

class Divi {
	public function __construct() {
		add_filter( 'slim_seo_schema_data', [ $this, 'replace_post_content' ] );
	}

	public function replace_post_content( array $data ): array {
		if ( ! defined( 'ET_BUILDER_VERSION' ) ) {
			return $data;
		}

		$post = is_singular() ? get_queried_object() : get_post();
		if ( empty( $post ) ) {
			return $data;
		}
		$content = Arr::get( $data, 'post.content', '' );
		Arr::set( $data, 'post.content', $this->description( $content, $post ) );

		return $data;
	}

	private function description( string $content, WP_Post $post ): ?string {
		// If the post is built with Divi, then strips all shortcodes, but keep the content.
		if ( get_post_meta( $post->ID, '_et_builder_version', true ) ) {
			return preg_replace( '~\[/?[^\]]+?/?\]~s', '', $content );
		}

		return $content;
	}
}

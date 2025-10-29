<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class Divi extends Base {
	public function is_active(): bool {
		return defined( 'ET_BUILDER_THEME' );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		return get_post_meta( $post_id, '_et_pb_use_builder', true );
	}

	protected function get_content( int $post_id ): string {
		if ( ! $this->is_built_with_builder( $post_id ) ) {
			return '';
		}

		$post_content = get_post_field( 'post_content', $post_id );
		$post_content = do_shortcode( $post_content );
		$post_content = do_blocks( $post_content );

		// Remove Divi structural shortcodes.
		// `do_shortcode()` doesn't render them, so we have to remove them manually.
		$shortcodes = [
			'et_pb_section',
			'et_pb_row',
			'et_pb_row_inner',
			'et_pb_column',
		];

		foreach ( $shortcodes as $shortcode ) {
			$post_content = preg_replace( "/\[\/?{$shortcode}[^\]]*?\]/is", '', $post_content );
		}

		// Remove other left shortcodes, usually buggy ones.
		$post_content = preg_replace( '/\[\/?[a-z0-9_-]+?[^\]]*?\]/is', '', $post_content );

		return $post_content;
	}
}

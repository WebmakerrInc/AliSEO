<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class BeaverBuilder extends Base {
	protected function is_active(): bool {
		return defined( 'FL_BUILDER_VERSION' );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		return \FLBuilderModel::is_builder_enabled( $post_id );
	}

	protected function get_content( int $post_id ): string {
		if ( ! $this->is_built_with_builder( $post_id ) ) {
			return '';
		}

		$post_content = get_post_field( 'post_content', $post_id );
		$post_content = do_shortcode( $post_content );
		$post_content = do_blocks( $post_content );

		return $post_content;
	}
}

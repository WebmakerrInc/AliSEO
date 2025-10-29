<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class Elementor extends Base {
	protected function is_active(): bool {
		return defined( 'ELEMENTOR_VERSION' );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		return get_post_meta( $post_id, '_elementor_version', true );
	}

	protected function get_content( int $post_id ): string {
		if ( ! $this->is_built_with_builder( $post_id ) ) {
			return '';
		}

		$frontend = new \Elementor\Frontend;

		return $frontend->get_builder_content( $post_id ) ?: '';
	}
}

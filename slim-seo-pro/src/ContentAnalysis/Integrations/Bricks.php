<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class Bricks extends Base {
	public function is_active(): bool {
		return defined( 'BRICKS_VERSION' );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		\Bricks\Database::$page_data['preview_or_post_id'] = $post_id;

		return \Bricks\Helpers::render_with_bricks( $post_id );
	}

	protected function get_content( int $post_id ): string {
		if ( ! $this->is_built_with_builder( $post_id ) ) {
			return '';
		}

		$bricks_data = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );

		return $bricks_data ? (string) \Bricks\Frontend::render_data( $bricks_data ) : '';
	}
}

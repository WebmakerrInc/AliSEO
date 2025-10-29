<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class Breakdance extends Base {
	protected function is_active(): bool {
		return defined( '__BREAKDANCE_VERSION' );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		return \Breakdance\Admin\get_mode( $post_id ) === 'breakdance';
	}

	protected function get_content( int $post_id ): string {
		return $this->is_built_with_builder( $post_id ) ? \Breakdance\Data\get_tree_as_html( $post_id ) : '';
	}
}

<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

abstract class Base {
	protected $location;

	public function __construct() {
		add_action( 'init', [ $this, 'setup' ] );
	}

	abstract protected function is_active(): bool;
	abstract protected function get_content( int $post_id ): string;

	public function setup(): void {
		if ( ! $this->is_active() ) {
			return;
		}

		add_filter( 'slim_seo_pro_built_with_builder', [ $this, 'built_with_builder' ], 10, 2 );
		add_filter( 'slim_seo_pro_builder_content', [ $this, 'builder_content' ], 10, 2 );
	}

	protected function is_built_with_builder( int $post_id ): bool {
		return false;
	}

	public function built_with_builder( bool $is_built, int $post_id ): bool {
		return $this->is_built_with_builder( $post_id ) ? true : $is_built;
	}

	public function builder_content( string $content, int $post_id ): string {
		return $this->get_content( $post_id ) ?: $content;
	}
}

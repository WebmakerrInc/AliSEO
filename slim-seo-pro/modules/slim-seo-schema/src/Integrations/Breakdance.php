<?php
namespace SlimSEOPro\Schema\Integrations;

use SlimSEOPro\Schema\Support\Arr;
use WP_Post;

class Breakdance {
	public function __construct() {
		add_filter( 'slim_seo_schema_data', [ $this, 'replace_post_content' ] );
	}

	public function replace_post_content( array $data ): array {
		if ( ! defined( '__BREAKDANCE_VERSION' ) ) {
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

	public function description( $description, WP_Post $post ): string {
		return \Breakdance\Admin\get_mode( $post->ID ) === 'breakdance' ? \Breakdance\Data\get_tree_as_html( $post->ID ) : $description;
	}
}

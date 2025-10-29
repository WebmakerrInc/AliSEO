<?php
namespace SlimSEOPro\LinkManager\Integrations;

use SlimSEOPro\LinkManager\Helpers\Data;
use SlimSEOPro\LinkManager\Helper;

class Divi extends Base {
	protected $location = 'divi';

	public function is_active(): bool {
		return defined( 'ET_BUILDER_THEME' );
	}

	private function build_with_divi( int $post_id ): bool {
		return get_post_meta( $post_id, '_et_pb_use_builder', true );
	}

	private function is_divi_5( int $post_id ): bool {
		return get_post_meta( $post_id, '_et_pb_use_divi_5', true );
	}

	public function allow_save_post( bool $allow, int $post_id ): bool {
		return $this->build_with_divi( $post_id ) ? false : $allow;
	}

	protected function get_content( int $post_id ): string {
		if ( ! $this->build_with_divi( $post_id ) ) {
			return '';
		}

		$content = Data::get_content( $post_id );

		if ( ! $this->is_divi_5( $post_id ) ) {
			$content = $this->remove_shortcodes( $content );
		}

		return $content;
	}

	private function remove_shortcodes( string $content ): string {
		// Remove Divi structural shortcodes.
		// `do_shortcode()` doesn't render them, so we have to remove them manually.
		$shortcodes = [
			'et_pb_section',
			'et_pb_row',
			'et_pb_row_inner',
			'et_pb_column',
		];

		foreach ( $shortcodes as $shortcode ) {
			$content = preg_replace( "/\[\/?{$shortcode}[^\]]*?\]/is", '', $content );
		}

		// Remove other left shortcodes, usually buggy ones.
		$content = preg_replace( '/\[\/?[a-z0-9_-]+?[^\]]*?\]/is', '', $content );

		return $content;
	}

	private function get_post_content( int $post_id, bool $is_divi_5 ): string {
		if ( ! $is_divi_5 ) {
			return Data::get_content( $post_id );
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_content = $wpdb->get_var(
			// phpcs:ignore WordPress.DB
			$wpdb->prepare(
				"SELECT `post_content` FROM {$wpdb->posts} WHERE `ID` = %d",
				$post_id
			)
		);

		return ! empty( $post_content ) ? $post_content : '';
	}

	private function update_post_content( int $post_id, string $post_content, bool $is_divi_5 ): void {
		if ( ! $is_divi_5 ) {
			wp_update_post( [
				'ID'           => $post_id,
				'post_content' => $post_content,
			] );

			return;
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->update(
			$wpdb->posts,
			[ 'post_content' => $post_content ],
			[ 'ID' => $post_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	private function convert_url( string $url ): string {
		$url = str_replace( '&amp;', '\\u0026amp;', $url );
		$url = str_replace( '&', '\\u0026amp;', $url );

		return $url;
	}

	public function update_link_url( array $link, string $old_url, string $new_url ) {
		if ( $this->location !== $link['location'] ) {
			return;
		}

		$is_divi_5    = $this->is_divi_5( $link['source_id'] );
		$post_content = $this->get_post_content( $link['source_id'], $is_divi_5 );

		if ( $is_divi_5 ) {
			$post_content = str_replace( $this->convert_url( $old_url ), $this->convert_url( $new_url ), $post_content );
		} else {
			$post_content = Helper::replace_string( $old_url, $new_url, $post_content );
		}

		$this->update_post_content( $link['source_id'], $post_content, $is_divi_5 );
	}

	public function remove_post_types( array $post_types ): array {
		unset( $post_types['et_pb_layout'] );

		return $post_types;
	}

	public function unlink( array $link ) {
		if ( $this->location !== $link['location'] ) {
			return;
		}

		$is_divi_5    = $this->is_divi_5( $link['source_id'] );
		$post_content = $this->get_post_content( $link['source_id'], $is_divi_5 );

		if ( $is_divi_5 ) {
			$post_content = preg_replace( '/\\\\u003ca\b(?:(?!\\\\u003e).)*?href=\\\\u0022' . preg_quote( $this->convert_url( $link['url'] ), '/' ) . '\\\\u0022(?:(?!\\\\u003e).)*?\\\\u003e(.*?)\\\\u003c\/a\\\\u003e/su', '$1', $post_content );
		} else {
			$post_content = Helper::remove_hyperlink( $post_content, $link['url'] );
		}

		$this->update_post_content( $link['source_id'], $post_content, $is_divi_5 );
	}
}

<?php
namespace SlimSEOPro\LinkManager\Integrations;

use SlimSEOPro\LinkManager\Helpers\Data;
use SlimSEOPro\LinkManager\Helper;

class BeaverBuilder extends Base {
	protected $location      = 'beaverbuilder';
	protected $builder_data  = '_fl_builder_data';
	protected $builder_draft = '_fl_builder_draft';

	protected function is_active(): bool {
		return defined( 'FL_BUILDER_VERSION' );
	}

	protected function get_content( int $post_id ): string {
		if ( ! \FLBuilderModel::is_builder_enabled( $post_id ) ) {
			return '';
		}

		$post_content = Data::get_content( $post_id );

		return $post_content;
	}

	protected function recursive_replace( $data, $old_url, $new_url ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->recursive_replace( $value, $old_url, $new_url );
			}
		} elseif ( is_object( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data->$key = $this->recursive_replace( $value, $old_url, $new_url );
			}
		} elseif ( is_string( $data ) ) {
			$data = Helper::replace_string( $old_url, $new_url, $data );
		}

		return $data;
	}

	protected function recursive_unlink( $data, $url ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->recursive_unlink( $value, $url );
			}
		} elseif ( is_object( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data->$key = $this->recursive_unlink( $value, $url );
			}
		} elseif ( is_string( $data ) ) {
			$data = Helper::remove_hyperlink( $data, $url );
		}

		return $data;
	}

	protected function update_post_content( $post_id, $post_content ) {
		$blocks = preg_match( '/<!-- wp:(.*) \/?-->/', $post_content );

		if ( ! $blocks ) {
			$block  = '<!-- wp:fl-builder/layout -->';
			$block .= \FLBuilderWPBlocksLayout::remove_broken_p_tags( $post_content );
			$block .= '<!-- /wp:fl-builder/layout -->';

			$post_content = $block;
		}

		wp_update_post( [
			'ID'           => $post_id,
			'post_content' => $post_content,
		] );
	}

	public function update_link_url( array $link, string $old_url, string $new_url ) {
		if ( $this->location !== $link['location'] ) {
			return;
		}

		$post_content = get_post_field( 'post_content', $link['source_id'] );
		$post_content = Helper::replace_string( $old_url, $new_url, $post_content );

		$this->update_post_content( $link['source_id'], $post_content );

		$builder_data = get_post_meta( $link['source_id'], $this->builder_data, true );

		if ( $builder_data ) {
			$builder_data = $this->recursive_replace( $builder_data, $old_url, $new_url );

			update_post_meta( $link['source_id'], $this->builder_data, $builder_data );
		}

		$builder_draft = get_post_meta( $link['source_id'], $this->builder_draft, true );

		if ( $builder_draft ) {
			$builder_draft = $this->recursive_replace( $builder_draft, $old_url, $new_url );

			update_post_meta( $link['source_id'], $this->builder_draft, $builder_draft );
		}
	}

	public function unlink( array $link ) {
		if ( $this->location !== $link['location'] ) {
			return;
		}

		$post_content = get_post_field( 'post_content', $link['source_id'] );
		$post_content = Helper::remove_hyperlink( $post_content, $link['url'] );

		$this->update_post_content( $link['source_id'], $post_content );

		$builder_data = get_post_meta( $link['source_id'], $this->builder_data, true );

		if ( $builder_data ) {
			$builder_data = $this->recursive_unlink( $builder_data, $link['url'] );

			update_post_meta( $link['source_id'], $this->builder_data, $builder_data );
		}

		$builder_draft = get_post_meta( $link['source_id'], $this->builder_draft, true );

		if ( $builder_draft ) {
			$builder_draft = $this->recursive_unlink( $builder_draft, $link['url'] );

			update_post_meta( $link['source_id'], $this->builder_draft, $builder_draft );
		}
	}

	public function remove_post_types( array $post_types ): array {
		unset( $post_types['fl-builder-history'] );

		return $post_types;
	}
}

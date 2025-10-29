<?php
namespace SlimSEOPro;

use eLightUp\SlimSEO\Common\Helpers\Data as CommonHelpersData;

class Common {
	public static function plugin_warning_messages() {
		$messages = [
			// Translators: %1$s - URL to the settings page.
			'no_key'  => __( 'You have not set your Slim SEO Pro license key yet. Please <a href="%1$s" target="_blank">enter your license key</a> to continue.', 'slim-seo-pro' ),
			// Translators: %1$s - URL to the settings page.
			'invalid' => __( 'Your license key for Slim SEO Pro is <b>invalid</b>. Please <a href="%1$s" target="_blank">enter your license key</a> to continue.', 'slim-seo-pro' ),
			// Translators: %1$s - URL to the settings page.
			'error'   => __( 'Your license key for Slim SEO Pro is <b>invalid</b>. Please <a href="%1$s" target="_blank">enter your license key</a> to continue.', 'slim-seo-pro' ),
			// Translators: %2$s - URL to the My Account page.
			'expired' => __( 'Your license key for Slim SEO Pro is <b>expired</b>. Please <a href="%2$s" target="_blank">renew your license</a> to continue.', 'slim-seo-pro' ),
		];

		return $messages;
	}

	public static function get_current_site() {
		$url = home_url();

		return untrailingslashit( $url );
	}

	public static function get_current_host() {
		$url = wp_parse_url( self::get_current_site() );

		return untrailingslashit( $url['host'] );
	}

	public static function get_posts( $args = [] ): array {
		$posts = get_posts( array_merge( [
			'post_type'      => array_keys( CommonHelpersData::get_post_types() ),
			'post_status'    => [ 'publish' ],
			'posts_per_page' => -1,
		], $args ) );

		$posts = apply_filters( 'slim_seo_pro_posts', $posts );

		return $posts;
	}

	public static function get_posts_data( $args = [] ): array {
		$posts      = self::get_posts( $args );
		$posts_data = [];

		foreach ( $posts as $post ) {
			$posts_data[] = [
				'title'   => $post->post_title,
				'url'     => get_permalink( $post->ID ),
				'editURL' => htmlspecialchars_decode( get_edit_post_link( $post->ID ) ),
			];
		}

		return $posts_data;
	}

	public static function get_terms_data( $args = [] ): array {
		$taxonomies = array_keys( CommonHelpersData::get_taxonomies() );
		$terms_data = [];

		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_terms( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
			] );

			if ( empty( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$terms_data[] = [
					'title'   => $term->name,
					'url'     => get_term_link( $term ),
					'editURL' => htmlspecialchars_decode( get_edit_term_link( $term ) ),
				];
			}
		}

		return $terms_data;
	}

	public static function get_pages_data( $args = [] ): array {
		return array_merge( self::get_posts_data( $args ), self::get_terms_data( $args ) );
	}

	public static function number_format( $number ) {
		// phpcs:ignore
		return intval( $number ) == $number ? number_format_i18n( $number, 0 ) : number_format_i18n( $number, 2 );
	}

	public static function is_internal_url( $url ): bool {
		return str_starts_with( $url, untrailingslashit( home_url() ) );
	}

	public static function all_plugin_deactivated(): bool {
		return ! defined( 'SLIM_SEO_VER' ) && ! defined( 'SLIM_SEO_SCHEMA_DIR' ) && ! defined( 'SLIM_SEO_LINK_MANAGER_DIR' );
	}
}

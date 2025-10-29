<?php
namespace SlimSEOPro\Analytics;

use WP_REST_Server;
use WP_REST_Request;
use SlimSEOPro\Api\Base;
use SlimSEOPro\Cache;

class Settings extends Base {
	const OPTION_NAME = 'slim_seo_pro_settings';

	private $google_client;

	public function __construct( GoogleClient $google_client ) {
		parent::__construct();

		$this->google_client = $google_client;
	}

	public function register_routes() {
		register_rest_route( self::NAMESPACE, 'analytics/settings', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'save' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );

		register_rest_route( self::NAMESPACE, 'analytics/revoke_google_authorization', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $this, 'revoke_google_authorization' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function save( WP_REST_Request $request ) {
		$settings = $request->get_body_params();

		if ( empty( $settings ) ) {
			return self::delete_all();
		}

		$old_settings = self::get_all();

		$keys = [
			'google_client_id',
			'google_client_secret',
		];

		$settings = array_intersect_key( $settings, array_flip( $keys ) );

		foreach ( $keys as $key ) {
			$settings[ $key ] = sanitize_text_field( $settings[ $key ] ?? '' );
		}

		$settings = array_filter( $settings );

		self::update_all( $settings );

		// If old credentials data are different from the new one > delete token, cache
		if (
			( $old_settings['google_client_id'] ?? '' ) !== ( $settings['google_client_id'] ?? '' )
			|| ( $old_settings['google_client_secret'] ?? '' ) !== ( $settings['google_client_secret'] ?? '' )
		) {
			$this->google_client->delete_token();
			Cache::delete_all( Helper::CACHE_PREFIX );
		}

		return true;
	}

	public function revoke_google_authorization() {
		$this->google_client->revoke_authorization();

		Cache::delete_all( Helper::CACHE_PREFIX );
		Helper::delete_current_site();

		return true;
	}

	public static function get_all(): array {
		return (array) get_option( self::OPTION_NAME ) ?: [];
	}

	public static function update_all( $settings ): bool {
		return update_option( self::OPTION_NAME, $settings, false );
	}

	public static function delete_all(): bool {
		return delete_option( self::OPTION_NAME );
	}

	public static function get( $name ) {
		$settings = self::get_all();

		return $settings[ $name ] ?? '';
	}
}

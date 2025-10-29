<?php
namespace SlimSEOPro\Analytics;

use Exception;

class GoogleClient {
	const TOKEN = 'slim_seo_pro_gsc_token';

	private $client;

	public function __construct() {
		$this->client = $this->create_client();

		add_action( 'init', [ $this, 'authenticate_callback' ] );
	}

	public function create_client() {
		$settings = Settings::get_all();
		$client   = new \Google_Client();

		$client->setClientId( $settings['google_client_id'] ?? '' );
		$client->setClientSecret( $settings['google_client_secret'] ?? '' );
		$client->setRedirectUri( home_url( '/?ssp_gsc_callback=1' ) );
		$client->addScope( 'https://www.googleapis.com/auth/webmasters.readonly' );
		$client->setAccessType( 'offline' );

		$token = $this->get_token();

		if ( ! empty( $token ) && ! empty( $token['access_token'] ) ) {
			$client->setAccessToken( $token );
		}

		return $client;
	}

	public function get_client() {
		return $this->client;
	}

	public function is_token_expired() {
		try {
			$is_access_token_expired = $this->client->isAccessTokenExpired();
			$is_access_token_expired = $is_access_token_expired ? ! $this->refresh_token() : false;

			if ( $is_access_token_expired ) {
				$this->revoke_authorization();
			}

			return $is_access_token_expired;
		} catch ( Exception $e ) {
			return true;
		}
	}

	public function is_token_valid() {
		$token = $this->get_token();

		if ( empty( $token ) ) {
			return true;
		}

		return ! empty( $token['access_token'] );
	}

	public function fetch_token( $code ) {
		$token = $this->client->fetchAccessTokenWithAuthCode( $code );

		$this->update_token( $token );
	}

	public function authenticate_callback() {
		// phpcs:ignore
		if ( empty( $_GET['ssp_gsc_callback'] ) || empty( $_GET['code'] ) ) {
			return;
		}

		// phpcs:ignore
		$this->fetch_token( $_GET['code'] );

		wp_safe_redirect( esc_url( admin_url( 'options-general.php?page=slim-seo#analytics' ) ) );
		exit();
	}

	public function get_login_link() {
		return $this->client->createAuthUrl();
	}

	public function revoke_authorization() {
		$token = $this->get_token();

		if ( empty( $token ) || empty( $token['access_token'] ) ) {
			return;
		}

		try {
			$this->client->revokeToken( $token );
		} catch ( Exception $e ) { // phpcs:ignore
		}

		$this->delete_token();
	}

	public function refresh_token() {
		$refresh_token = $this->client->getRefreshToken();

		if ( $refresh_token ) {
			$token = $this->client->fetchAccessTokenWithRefreshToken( $refresh_token );

			$this->update_token( $token );

			return true;
		}

		return false;
	}

	public function get_token() {
		return get_option( self::TOKEN );
	}

	public function update_token( $token ): bool {
		return update_option( self::TOKEN, $token, false );
	}

	public function delete_token(): bool {
		return delete_option( self::TOKEN );
	}
}

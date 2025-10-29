<?php
namespace SlimSEOPro\Analytics;

use eLightUp\PluginUpdater\Manager;
use SlimSEOPro\Common;
use SlimSEOPro\Assets;

class Page {
	private $manager;
	private $google_client;
	private $analytics_data;

	public function __construct( Manager $manager, GoogleClient $google_client, Data $analytics_data ) {
		$this->manager        = $manager;
		$this->google_client  = $google_client;
		$this->analytics_data = $analytics_data;

		add_filter( 'slim_seo_settings_tabs', [ $this, 'add_tab' ], 20 );
		add_filter( 'slim_seo_settings_panes', [ $this, 'add_pane' ], 20 );
		add_action( 'admin_print_styles-settings_page_slim-seo', [ $this, 'enqueue' ] );
	}

	public function add_tab( array $tabs ): array {
		$tabs['analytics'] = __( 'Analytics', 'slim-seo-pro' );

		return $tabs;
	}

	public function add_pane( array $panes ): array {
		$status = $this->manager->option->get_license_status();

		if ( $status === 'active' ) {
			$panes['analytics'] = '<div id="analytics" class="ss-tab-pane"><div id="ssp-analytics"></div></div>';

			return $panes;
		}

		$messages = Common::plugin_warning_messages();

		ob_start();
		?>
		<div id="analytics" class="ss-tab-pane">
			<div class="ss-license-warning">
				<h2>
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'License Warning', 'slim-seo-pro' ) ?>
				</h2>
				<?= wp_kses_post( sprintf( $messages[ $status ], 'https://elu.to/sua' ) ); ?>
			</div>
		</div>
		<?php
		$panes['analytics'] = ob_get_clean();
		return $panes;
	}

	public function enqueue(): void {
		wp_enqueue_style( 'slim-seo-pro', SLIM_SEO_PRO_URL . 'css/slim-seo-pro.css', [], filemtime( SLIM_SEO_PRO_DIR . '/css/slim-seo-pro.css' ) );

		if ( $this->manager->option->get_license_status() !== 'active' ) {
			return;
		}

		$localized_data = $this->get_localized_data();

		Assets::enqueue_build_js( 'analytics', 'SSPro', $localized_data );
	}

	private function get_localized_data(): array {
		$settings       = Settings::get_all();
		$localized_data = [
			'rest'              => untrailingslashit( rest_url() ),
			'nonce'             => wp_create_nonce( 'wp_rest' ),
			'settingsPageURL'   => untrailingslashit( admin_url( 'options-general.php?page=slim-seo' ) ),
			'tabID'             => 'analytics',
			'settings'          => $settings,
			'timeFilterOptions' => Helper::time_filter_options(),
			'language'          => get_locale(),
		];

		if ( empty( $settings['google_client_id'] ) || empty( $settings['google_client_secret'] ) ) {
			$localized_data['noGoogleCredentialsData'] = 1;
			return $localized_data;
		}
		if ( ! $this->google_client->is_token_valid() ) {
			$localized_data['googleTokenInvalid'] = 1;
			return $localized_data;
		}

		$is_google_token_expired = $this->google_client->is_token_expired();

		$localized_data['isGoogleTokenExpired'] = $is_google_token_expired;

		if ( $is_google_token_expired ) {
			$localized_data['googleLoginLink'] = $this->google_client->get_login_link();
			return $localized_data;
		}

		$domains = $this->analytics_data->get_domains();

		if ( empty( $domains ) ) {
			$localized_data['noDomain'] = 1;
			return $localized_data;
		}

		$current_site = Helper::get_current_site();

		// Already set and saved in the option.
		if ( ! empty( $current_site ) ) {
			return $localized_data;
		}

		$current_host = Common::get_current_host();
		foreach ( $domains as $domain ) {
			if ( str_contains( $domain, $current_host ) ) {
				$current_site = $domain;

				Helper::set_current_site( $current_site );

				break;
			}
		}

		if ( empty( $current_site ) ) {
			$localized_data['noDomainMatching'] = 1;
		}

		return $localized_data;
	}
}

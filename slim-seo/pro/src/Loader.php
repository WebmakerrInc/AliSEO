<?php
namespace SlimSEOPro;

use SlimSEO\Updater\Tab;
use eLightUp\PluginUpdater\Manager;
use SlimSEO\Updater\Settings as UpdaterSettings;
use eLightUp\SlimSEO\Common\Settings\Post as SettingsPost;

class Loader {
        private ?Manager $update_manager = null;
        private bool $embedded = false;
        private string $license_status = 'inactive';

        public function __construct( bool $embedded = false ) {
                $this->embedded = $embedded;

                if ( ! $this->embedded ) {
                        new Activator();
                }

                new Settings();
                new FeaturedPlugins();

                $this->hooks();
                $this->setup_updater();

                if ( ! $this->is_license_active() ) {
                        return;
                }

		if ( Settings::is_feature_active( 'link-manager' ) ) {
			require SLIM_SEO_PRO_DIR . '/modules/slim-seo-link-manager/slim-seo-link-manager.php';
		}

		if ( Settings::is_feature_active( 'schema' ) ) {
			require SLIM_SEO_PRO_DIR . '/modules/slim-seo-schema/slim-seo-schema.php';
		}

		if ( Settings::is_feature_active( 'analytics' ) ) {
			$google_client             = new Analytics\GoogleClient();
			$google_service_webmasters = new Analytics\GoogleServiceWebmasters( $google_client );
			$analytics_data            = new Analytics\Data( $google_service_webmasters );

			new Analytics\Settings( $google_client );
			new Analytics\Api( $analytics_data );
			new Analytics\Export( $analytics_data );

                        if ( is_admin() ) {
                                new Analytics\Page( $this->update_manager, $google_client, $analytics_data );
                        }
                }

		if ( Settings::is_feature_active( 'content-analysis' ) ) {
			SettingsPost::setup();

			new ContentAnalysis\Api();
			new ContentAnalysis\Post();
			new ContentAnalysis\Integrations\SlimSEO();
			new ContentAnalysis\Integrations\Schema();
			new ContentAnalysis\Integrations\BeaverBuilder();
			new ContentAnalysis\Integrations\Bricks();
			new ContentAnalysis\Integrations\Breakdance();
			new ContentAnalysis\Integrations\Divi();
			new ContentAnalysis\Integrations\Elementor();
		}
	}

	private function hooks(): void {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
		add_filter( 'slim_seo_link_manager_plugin_warning_messages', [ $this, 'plugin_warning_messages' ] );
		add_filter( 'slim_seo_schema_plugin_warning_messages', [ $this, 'plugin_warning_messages' ] );
		add_filter( 'slim_seo_link_manager_manager_args', [ $this, 'manager_args' ] );
		add_filter( 'slim_seo_schema_manager_args', [ $this, 'manager_args' ] );
		add_filter( 'elightup_plugin_updater_disallow_setup', [ $this, 'disallow_setup' ], 10, 2 );
	}

        private function setup_updater(): void {
                if ( $this->embedded ) {
                        $this->license_status = 'active';

                        return;
                }

                Tab::setup();

                $this->update_manager           = new Manager( [
                        'api_url'            => 'https://wpslimseo.com/index.php',
                        'my_account_url'     => 'https://wpslimseo.com/my-account/',
                        'buy_url'            => 'https://wpslimseo.com/products/slim-seo-pro/',
                        'slug'               => 'slim-seo-pro',
                        'settings_page'      => admin_url( 'options-general.php?page=slim-seo#license' ),
                        'settings_page_slug' => 'slim-seo',
                ] );
                $settings                       = new UpdaterSettings( $this->update_manager, $this->update_manager->checker, $this->update_manager->option );
                $this->update_manager->settings = $settings;
                $this->update_manager->setup();

                $this->license_status = $this->update_manager->option->get_license_status();
        }

        private function is_license_active(): bool {
                if ( $this->embedded ) {
                        return true;
                }

                return $this->license_status === 'active';
        }

	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'slim-seo-pro', false, SLIM_SEO_PRO_DIR . '/languages' );
	}

	public function plugin_warning_messages(): array {
		return Common::plugin_warning_messages();
	}

	public function manager_args( array $args ): array {
		$args['plugin_id'] = $args['slug'];
		$args['buy_url']   = 'https://wpslimseo.com/products/slim-seo-pro/';
		$args['slug']      = 'slim-seo-pro';

		return $args;
	}

	public function disallow_setup( bool $disallow, string $plugin_id ): bool {
		if ( in_array( $plugin_id, [ 'slim-seo-link-manager', 'slim-seo-schema' ], true ) ) {
			$disallow = true;
		}

		return $disallow;
	}
}

<?php
namespace SlimSEOPro\LinkManager;

class Activator {
	private $plugin = 'slim-seo-link-manager/slim-seo-link-manager.php';

	public function __construct() {
		if ( ! $this->plugin_exists() ) {
			return;
		}

		add_filter( "plugin_action_links_{$this->plugin}", [ $this, 'add_plugin_action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );
	}

	private function plugin_exists(): bool {
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			return false;
		}

		$plugin_dir = rtrim( WP_PLUGIN_DIR, '/\\' );

		return file_exists( $plugin_dir . '/' . $this->plugin );
	}

	public function add_plugin_action_links( array $links ): array {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=slim-seo#link-manager' ) ) . '">' . __( 'Report', 'slim-seo-link-manager' ) . '</a>';
		return $links;
	}

	public function add_plugin_meta_links( array $meta, string $file ): array {
		if ( $file !== $this->plugin ) {
			return $meta;
		}

		$meta[] = '<a href="https://docs.wpslimseo.com/slim-seo-pro/link-manager/scanning-links/?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo" target="_blank">' . esc_html__( 'Documentation', 'slim-seo-link-manager' ) . '</a>';
		return $meta;
	}
}

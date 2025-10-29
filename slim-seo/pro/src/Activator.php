<?php
namespace SlimSEOPro;

class Activator {
	private $plugin = 'slim-seo-pro/slim-seo-pro.php';

	public function __construct() {
		if ( ! $this->plugin_exists() ) {
			return;
		}

		add_filter( "plugin_action_links_{$this->plugin}", [ $this, 'add_plugin_action_links' ] );
		add_filter( 'plugin_row_meta', [ $this, 'add_plugin_meta_links' ], 10, 2 );

		add_action( 'activated_plugin', [ $this, 'redirect' ], 10, 2 );
	}

	private function plugin_exists(): bool {
		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			return false;
		}

		$plugin_dir = rtrim( WP_PLUGIN_DIR, '/\\' );

		return file_exists( $plugin_dir . '/' . $this->plugin );
	}

	public function add_plugin_action_links( array $links ): array {
		$links[] = '<a href="' . esc_url( admin_url( 'options-general.php?page=slim-seo' ) ) . '">' . __( 'Settings', 'slim-seo-pro' ) . '</a>';
		return $links;
	}

	public function add_plugin_meta_links( array $meta, string $file ) {
		if ( $file !== $this->plugin ) {
			return $meta;
		}

		$meta[] = '<a href="https://docs.wpslimseo.com?utm_source=plugin_links&utm_medium=link&utm_campaign=slim_seo_pro" target="_blank">' . esc_html__( 'Documentation', 'slim-seo-pro' ) . '</a>';
		return $meta;
	}

	public function redirect( $plugin, $network_wide = false ) {
		$is_cli    = 'cli' === php_sapi_name();
		$is_plugin = $this->plugin === $plugin;

		$action           = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$checked          = isset( $_POST['checked'] ) && is_array( $_POST['checked'] ) ? count( $_POST['checked'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
		$is_bulk_activate = $action === 'activate-selected' && $checked > 1;

		if ( ! $is_plugin || $network_wide || $is_cli || $is_bulk_activate ) {
			return;
		}
		wp_safe_redirect( admin_url( 'options-general.php?page=slim-seo' ) );
		die;
	}
}

<?php
namespace SlimSEOPro\ContentAnalysis;

use eLightUp\SlimSEO\Common\Helpers\Data as CommonHelpersData;
use SlimSEOPro\Assets;

class Post {
	public function __construct() {
		add_action( 'init', [ $this, 'setup' ] );
	}

	public function setup() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_css' ] );
		add_action( 'slim_seo_meta_box_enqueue', [ $this, 'enqueue' ] );
		add_filter( 'slim_seo_meta_box_tabs', [ $this, 'tabs' ], 40 );
		add_filter( 'slim_seo_meta_box_panels', [ $this, 'panels' ], 40 );
		add_action( 'admin_init', [ $this, 'setup_admin_columns' ] );
		add_action( 'save_post', [ $this, 'save' ] );
	}

	public function enqueue_css(): void {
		// CSS for management posts page and edit post page
		$screen = get_current_screen();

		if ( ! in_array( $screen->post_type, array_keys( CommonHelpersData::get_post_types() ), true ) ) {
			return;
		}

		wp_enqueue_style( 'slim-seo-pro-post', SLIM_SEO_PRO_URL . 'css/slim-seo-pro-post.css', [ 'wp-components' ], filemtime( SLIM_SEO_PRO_DIR . '/css/slim-seo-pro-post.css' ) );
	}

	public function enqueue(): void {
		// Use components from Slim SEO: tabs, status icons.
		wp_enqueue_style( 'slim-seo-components', 'https://cdn.jsdelivr.net/gh/elightup/slim-seo@master/css/components.css', [], '1.0.0' );

		$post_id               = get_the_ID();
		$data                  = get_post_meta( $post_id, 'slim_seo_pro', true );
		$data                  = is_array( $data ) && ! empty( $data ) ? $data : [];
		$content_analysis_data = $data['content_analysis'] ?? [];

		Assets::enqueue_build_js( 'content-analysis', 'SSPro', [
			'homeURL'          => untrailingslashit( home_url() ),
			'rest'             => untrailingslashit( rest_url() ),
			'nonce'            => wp_create_nonce( 'wp_rest' ),
			'keywords'         => $content_analysis_data['keywords'] ?? '',
			'mainKeyword'      => $content_analysis_data['main_keyword'] ?? '',
			'SSLMActivated'    => defined( 'SLIM_SEO_LINK_MANAGER_VER' ),
			'SSSettingsPage'   => esc_url( admin_url( 'options-general.php?page=slim-seo' ) ),
			'siteLocale'       => get_locale(),
			'supportThumbnail' => post_type_supports( get_post_type(), 'thumbnail' ),
			'postID'           => $post_id,
			'builtWithBuilder' => apply_filters( 'slim_seo_pro_built_with_builder', false, $post_id ),
		] );
	}

	public function tabs( array $tabs ): array {
		$tabs['content-analysis'] = __( 'Writing assistant', 'slim-seo-pro' );

		return $tabs;
	}

	public function panels( array $panels ): array {
		ob_start();

		wp_nonce_field( 'save', 'ssp_content_analysis_nonce' );
		?>

		<div id="content-analysis-app"></div>

		<?php
		$panels['content-analysis'] = ob_get_clean();

		return $panels;
	}

	public function admin_columns( array $columns ): array {
		$columns['content_analysis'] = esc_html__( 'Content', 'slim-seo-pro' );

		return $columns;
	}

	public function admin_column_render( string $column, int $post_id ): void {
		if ( 'content_analysis' !== $column ) {
			return;
		}

		$data                  = get_post_meta( $post_id, 'slim_seo_pro', true ) ?: [];
		$content_analysis_data = $data['content_analysis'] ?? [];

		if ( ! isset( $content_analysis_data['good_keywords'] ) ) {
			return;
		}

		$ok = ! empty( $content_analysis_data['good_keywords'] ) && ! empty( $content_analysis_data['good_words_count'] );

		if ( isset( $content_analysis_data['good_media'] ) ) {
			$ok = $ok && ! empty( $content_analysis_data['good_media'] );
		}

		if ( $ok ) {
			echo '<span class="ssp-success" role="img" aria-label="' . esc_html__( 'Success', 'slim-seo-pro' ) . '"></span>';
		} else {
			echo '<span class="ssp-warning" role="img" aria-label="' . esc_html__( 'Warning', 'slim-seo-pro' ) . '"></span>';
		}
	}

	public function setup_admin_columns(): void {
		$post_types = array_keys( CommonHelpersData::get_post_types() );

		foreach ( $post_types as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", [ $this, 'admin_columns' ] );
			add_action( "manage_{$post_type}_posts_custom_column", [ $this, 'admin_column_render' ], 10, 2 );
		}
	}

	public function save( int $post_id ): void {
		if ( ! check_ajax_referer( 'save', 'ssp_content_analysis_nonce', false ) || empty( $_POST ) ) {
			return;
		}

		$data = isset( $_POST['slim_seo_pro'] ) ? wp_unslash( $_POST['slim_seo_pro'] ) : []; // phpcs:ignore

		if ( empty( $data ) ) {
			delete_post_meta( $post_id, 'slim_seo_pro' );
		} else {
			update_post_meta( $post_id, 'slim_seo_pro', $data );
		}
	}
}

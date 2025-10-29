<?php
namespace SlimSEOPro\LinkManager;

use eLightUp\PluginUpdater\Manager;

class Post {
	private $manager;

	public function __construct( Manager $manager ) {
		$this->manager = $manager;

		add_action( 'slim_seo_meta_box_enqueue', [ $this, 'enqueue' ] );
		add_filter( 'slim_seo_meta_box_tabs', [ $this, 'tabs' ], 20 );
		add_filter( 'slim_seo_meta_box_panels', [ $this, 'panels' ], 20 );
		add_action( 'save_post', [ $this, 'save' ] );
		add_action( 'post_updated', [ $this, 'post_updated' ] );
		add_action( 'wp_trash_post', [ $this, 'post_trashed' ] );
	}

	public function enqueue() {
		if ( ! $this->is_valid() ) {
			return;
		}

		wp_enqueue_style( 'slim-seo-link-manager', SLIM_SEO_LINK_MANAGER_URL . 'css/link-manager.css', [ 'wp-components' ], filemtime( SLIM_SEO_LINK_MANAGER_DIR . '/css/link-manager.css' ) );

		if ( $this->manager->option->get_license_status() !== 'active' ) {
			return;
		}

		global $post;

		// Use component: status icon.
		wp_enqueue_style( 'slim-seo-components', 'https://cdn.jsdelivr.net/gh/elightup/slim-seo@master/css/components.css', [], SLIM_SEO_LINK_MANAGER_VER );

		wp_enqueue_script( 'slim-seo-link-manager', SLIM_SEO_LINK_MANAGER_URL . 'js/post.js', [ 'wp-element', 'wp-components', 'wp-i18n', 'jquery' ], filemtime( SLIM_SEO_LINK_MANAGER_DIR . '/js/post.js' ), true );

		$localized_data = [
			'rest'                    => untrailingslashit( rest_url() ),
			'nonce'                   => wp_create_nonce( 'wp_rest' ),
			'postID'                  => $post->ID,
			'postType'                => $post->post_type,
			'postURL'                 => get_permalink( $post->ID ),
			'postStatus'              => get_post_status( $post->ID ),
			'showExternalSuggestions' => LinkSuggestions\Common::is_enable_interlink_external_sites(),
		];

		wp_localize_script( 'slim-seo-link-manager', 'SSLinkManager', $localized_data );

		wp_set_script_translations( 'slim-seo-link-manager', 'slim-seo-link-manager', SLIM_SEO_LINK_MANAGER_DIR . '/languages' );
	}

	private function is_valid(): bool {
		global $post;

		$post_types = Helper::get_post_types();

		return in_array( $post->post_type, $post_types, true );
	}

	public function tabs( array $tabs ): array {
		if ( ! $this->is_valid() ) {
			return $tabs;
		}

		$tabs['links'] = esc_html__( 'Links', 'slim-seo-link-manager' );

		return $tabs;
	}

	public function panels( array $panels ): array {
		if ( ! $this->is_valid() ) {
			return $panels;
		}

		ob_start();

		$status = $this->manager->option->get_license_status();

		if ( $status === 'active' ) {
			wp_nonce_field( 'save-link-manager', 'sslm_nonce' );

			echo '<div id="sslm-post"></div>';

			$panels['links'] = ob_get_clean();

			return $panels;
		}

		$messages = Helper::plugin_warning_messages();
		?>
		<div class="ss-license-warning">
			<h2>
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e( 'License Warning', 'slim-seo-link-manager' ) ?>
			</h2>
			<?= wp_kses_post( sprintf( $messages[ $status ], admin_url( 'options-general.php?page=slim-seo#license' ), 'https://elu.to/sua' ) ); ?>
		</div>

		<?php
		$panels['links'] = ob_get_clean();

		return $panels;
	}

	public function save( $post_id ) {
		if ( ! check_ajax_referer( 'save-link-manager', 'sslm_nonce', false ) ) {
			return;
		}

		$allow_save_post = apply_filters( 'slim_seo_link_manager_allow_save_post', true, $post_id );

		if ( ! $allow_save_post ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$sslm      = $_POST['sslm'] ?? [];
		$source_id = wp_is_post_revision( $post_id ) ?: $post_id;
		$tbl_links = new Database\Links();
		$tbl_links->delete_all( $source_id, get_post_type( $source_id ) );

		if ( 'trash' === get_post_status( $post_id ) ) {
			return;
		}

		// Convert outbound links
		$outbound_links = array_map( function ( $outbound_link ) {
			return json_decode( stripslashes( $outbound_link ), true );
		}, $sslm['outbound_links'] ?? [] );
		$outbound_links = apply_filters( 'slim_seo_link_manager_outbound_links', $outbound_links, $source_id );

		$tbl_links->add( $outbound_links );
	}

	public function post_updated( $post_id ) {
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		$tbl_links = new Database\Links();
		$links     = $tbl_links->get_links_by_object( $post_id, get_post_type( $post_id ), 'target' );

		if ( empty( $links ) ) {
			return;
		}

		$new_permalink = untrailingslashit( get_permalink( $post_id ) );

		LinkUpdater\Common::update_links( $links, $new_permalink );
	}

	public function post_trashed( $post_id ) {
		$tbl_links = new Database\Links();
		$tbl_links->delete_all( $post_id, get_post_type( $post_id ) );
	}
}

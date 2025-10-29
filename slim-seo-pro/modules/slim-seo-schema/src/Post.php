<?php
namespace SlimSEOPro\Schema;

use eLightUp\PluginUpdater\Manager;
use SlimSEOPro\Schema\Support\Data;

class Post {
	private $manager;

	public function __construct( Manager $manager ) {
		$this->manager = $manager;

		add_action( 'slim_seo_meta_box_enqueue', [ $this, 'enqueue' ] );
		add_filter( 'slim_seo_meta_box_tabs', [ $this, 'tabs' ], 30 );
		add_filter( 'slim_seo_meta_box_panels', [ $this, 'panels' ], 30 );
		add_action( 'save_post', [ $this, 'save' ] );
	}

	public function enqueue() {
		wp_enqueue_style( 'slim-seo-schema', SLIM_SEO_SCHEMA_URL . 'css/schema.css', [ 'wp-components' ], filemtime( SLIM_SEO_SCHEMA_DIR . '/css/schema.css' ) );

		if ( $this->manager->option->get_license_status() !== 'active' ) {
			return;
		}

		global $post;

		wp_enqueue_style( 'slim-seo-settings', 'https://cdn.jsdelivr.net/gh/elightup/slim-seo@master/css/settings.css', [], '1' );
		wp_enqueue_media();

		$asset = require SLIM_SEO_SCHEMA_DIR . '/js/build/post/post.asset.php';
		wp_enqueue_script( 'slim-seo-schema', SLIM_SEO_SCHEMA_URL . 'js/build/post/post.js', $asset['dependencies'], $asset['version'], true );

		$localized_data = [
			'rest'            => untrailingslashit( rest_url() ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'mediaPopupTitle' => __( 'Select An Image', 'slim-seo-schema' ),
			'schema'          => get_post_meta( $post->ID, 'slim_seo_schema', true ) ?: [],
			'schemas'         => $this->get_schemas_for_js(),
			'variables'       => Data::get_variables(),
			'types'           => Data::get_types(),
		];
		wp_localize_script( 'slim-seo-schema', 'SSSchema', $localized_data );

		wp_set_script_translations( 'slim-seo-schema', 'slim-seo-schema', SLIM_SEO_SCHEMA_DIR . '/languages' );
	}

	public function tabs( array $tabs ): array {
		$tabs['schema'] = esc_html__( 'Schema', 'slim-seo-schema' );

		return $tabs;
	}

	public function panels( array $panels ): array {
		ob_start();

		$status = $this->manager->option->get_license_status();

		if ( $status === 'active' ) {
			wp_nonce_field( 'save-schema', 'sss_nonce' );

			echo '<div id="sss-post"></div>';

			$panels['schema'] = ob_get_clean();

			return $panels;
		}

		$messages = Data::plugin_warning_messages();
		?>
		<div class="ss-license-warning">
			<h2>
				<span class="dashicons dashicons-warning"></span>
				<?php esc_html_e( 'License Warning', 'slim-seo-schema' ) ?>
			</h2>
			<?= wp_kses_post( sprintf( $messages[ $status ], admin_url( 'options-general.php?page=slim-seo#license' ), 'https://elu.to/sua' ) ); ?>
		</div>

		<?php
		$panels['schema'] = ob_get_clean();

		return $panels;
	}

	public function save( $post_id ) {
		// Verify nonce.
		$nonce = isset( $_POST['sss_nonce'] ) ? (string) $_POST['sss_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'save-schema' ) ) {
			return;
		}

		// Save data to the post, not revisions.
		$parent  = wp_is_post_revision( $post_id );
		$post_id = $parent ? $parent : $post_id;

		// Get only one schema.
		$schema = isset( $_POST['schemas'] ) ? wp_unslash( $_POST['schemas'] ) : [];
		$schema = isset( $schema['post'] ) ? $schema['post'] : [];

		if ( empty( $schema ) ) {
			delete_post_meta( $post_id, 'slim_seo_schema' );
		} else {
			update_post_meta( $post_id, 'slim_seo_schema', $schema );
		}
	}

	private function get_schemas_for_js(): array {
		$schemas = Settings::get_all_schemas();
		foreach ( $schemas as $id => &$schema ) {
			$schema['id'] = $id;
		}
		return array_values( $schemas );
	}
}

<?php
namespace SlimSEOPro\Schema;

use eLightUp\PluginUpdater\Manager;
use SlimSEOPro\Schema\Support\Data;

class Settings {
	public const OPTION_NAME = 'slim_seo_schemas';
	private $manager;

	public function __construct( Manager $manager ) {
		$this->manager = $manager;

		add_filter( 'slim_seo_settings_tabs', [ $this, 'add_tab' ] );
		add_filter( 'slim_seo_settings_panes', [ $this, 'add_pane' ] );
		add_action( 'admin_print_styles-settings_page_slim-seo', [ $this, 'enqueue' ] );

		add_filter( 'slim_seo_upgradeable', '__return_false' );
	}

	public function add_tab( $tabs ) {
		$tabs['schema'] = __( 'Schema', 'slim-seo-schema' );
		return $tabs;
	}

	public function add_pane( $panes ) {
		$status = $this->manager->option->get_license_status();

		if ( $status === 'active' ) {
			$panes['schema'] = '<div id="schema" class="ss-tab-pane"></div>';
			return $panes;
		}

		$messages = Data::plugin_warning_messages();

		ob_start();
		?>
		<div id="schema" class="ss-tab-pane">
			<div class="ss-license-warning">
				<h2>
					<span class="dashicons dashicons-warning"></span>
					<?php esc_html_e( 'License Warning', 'slim-seo-schema' ) ?>
				</h2>
				<?= wp_kses_post( sprintf( $messages[ $status ], $this->manager->settings_page, $this->manager->my_account_url ) ); ?>
			</div>
		</div>
		<?php
		$panes['schema'] = ob_get_clean();
		return $panes;
	}

	public function enqueue() {
		wp_enqueue_style( 'slim-seo-schema', SLIM_SEO_SCHEMA_URL . 'css/schema.css', [ 'wp-components' ], filemtime( SLIM_SEO_SCHEMA_DIR . '/css/schema.css' ) );

		if ( $this->manager->option->get_license_status() !== 'active' ) {
			return;
		}

		wp_enqueue_media();

		$asset = require SLIM_SEO_SCHEMA_DIR . '/js/build/schema/schema.asset.php';
		wp_enqueue_script( 'slim-seo-schema', SLIM_SEO_SCHEMA_URL . 'js/build/schema/schema.js', $asset['dependencies'], $asset['version'], true );

		$location_settings = new Location\Settings;
		$localized_data    = array_merge( $location_settings->get_localized_data(), [
			'rest'            => untrailingslashit( rest_url() ),
			'nonce'           => wp_create_nonce( 'wp_rest' ),
			'postTypes'       => $this->get_post_types(),
			'mediaPopupTitle' => __( 'Select An Image', 'slim-seo-schema' ),
			'schemas'         => $this->get_schemas_for_js(),
			'variables'       => Data::get_variables(),
			'types'           => Data::get_types(),
		] );
		wp_localize_script( 'slim-seo-schema', 'SSSchema', $localized_data );

		wp_set_script_translations( 'slim-seo-schema', 'slim-seo-schema', SLIM_SEO_SCHEMA_DIR . '/languages' );

		do_action( 'slim_seo_schema_settings_enqueue' );
	}

	public function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		unset( $post_types['attachment'] );
		$post_types = array_map( function ( $post_type ) {
			return [
				'slug' => $post_type->name,
				'name' => $post_type->labels->singular_name,
			];
		}, $post_types );

		return array_values( $post_types );
	}

	public static function get_all_schemas(): array {
		return get_option( self::OPTION_NAME, Defaults::get() ) ?: [];
	}

	public static function get_active_schemas(): array {
		$schemas = self::get_all_schemas();
		return array_filter( $schemas, function ( $schema ) {
			return ! isset( $schema['active'] ) || $schema['active'] === true || $schema['active'] === 'true';
		} );
	}

	private function get_schemas_for_js(): array {
		$schemas = self::get_all_schemas();
		foreach ( $schemas as $id => &$schema ) {
			$schema['id'] = $id;
		}
		return array_values( $schemas );
	}
}

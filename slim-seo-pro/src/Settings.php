<?php
namespace SlimSEOPro;

use eLightUp\SlimSEO\Common\Settings\Page;

class Settings {
	public static $defaults = [
		'features' => [
			'link-manager',
			'schema',
			'analytics',
			'content-analysis',
		],
	];

	public function __construct() {
		$this->hooks();
	}

	public function hooks() {
		add_action( 'admin_print_styles-settings_page_slim-seo', [ $this, 'enqueue' ], 1 );
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'slim_seo_save', [ $this, 'save' ], 1 );
	}

	public function init() {
		if ( Common::all_plugin_deactivated() ) {
			Page::setup();
		}

		if ( defined( 'SLIM_SEO_VER' ) ) {
			add_action( 'slim_seo_general_tab_content', [ $this, 'features' ] );
		} else {
			add_filter( 'slim_seo_settings_tabs', [ $this, 'add_tabs' ], 1 );
			add_filter( 'slim_seo_settings_panes', [ $this, 'add_panes' ], 1 );
		}
	}

	public function enqueue(): void {
		wp_enqueue_style( 'slim-seo-pro', SLIM_SEO_PRO_URL . 'css/slim-seo-pro.css', [], filemtime( SLIM_SEO_PRO_DIR . '/css/slim-seo-pro.css' ) );
	}

	public function add_tabs( array $tabs ): array {
		$tabs['general'] = __( 'Features', 'slim-seo-pro' );

		return $tabs;
	}

	public function add_panes( array $panes ): array {
		ob_start();
		?>
		<div id="general" class="ss-tab-pane">
			<p><?php esc_html_e( 'Toggle the features you want to use on your website.', 'slim-seo-pro' ); ?></p>

			<?php
			$this->features();

			submit_button( __( 'Save Changes', 'slim-seo-pro' ) );
			?>
		</div>
		<?php
		$panes['general'] = ob_get_clean();

		return $panes;
	}

	public function features() {
		$features = [
			'link-manager'     => [ __( 'Link Manager (Pro)', 'slim-seo-pro' ), __( 'Build, audit and monitor links in your websites easily.', 'slim-seo-pro' ) ],
			'schema'           => [ __( 'Schema (Pro)', 'slim-seo-pro' ), __( 'Add structured data to your website visually.', 'slim-seo-pro' ) ],
			'analytics'        => [ __( 'Analytics (Pro)', 'slim-seo-pro' ), __( 'Analyze your search performance with the Google Search Console integration.', 'slim-seo-pro' ) ],
			'content-analysis' => [ __( 'Writing assistant (Pro)', 'slim-seo-pro' ), __( 'Analyze your content and get suggestions to improve it.', 'slim-seo-pro' ) ],
		];
		?>
		<div class="ssp-features">
			<?php
			foreach ( $features as $key => $text ) {
				self::feature_box( 'slim_seo_pro[features][]', $key, $this->is_feature_active( $key ), $text[0], $text[1] );
			}
			?>
		</div>
		<?php
	}

	public function save() {
		$data   = isset( $_POST['slim_seo_pro'] ) ? wp_unslash( $_POST['slim_seo_pro'] ) : []; // @codingStandardsIgnoreLine.
		$option = get_option( 'slim_seo_pro' );
		$option = $option ?: [];
		$option = array_merge( $option, $data );
		$option = apply_filters( 'slim_seo_pro_option', $option, $data );
		$option = $this->sanitize( $option );

		if ( empty( $data['features'] ) ) {
			$option['features'] = [];
		}

		update_option( 'slim_seo_pro', $option );
	}

	private function sanitize( array $option ): array {
		$option = array_merge( self::$defaults, $option );

		return array_filter( $option );
	}

	public static function is_feature_active( string $feature ): bool {
		$defaults = self::$defaults['features'];
		$option   = get_option( 'slim_seo_pro' );
		$features = $option['features'] ?? $defaults;

		return in_array( $feature, $features, true ) || ! in_array( $feature, $defaults, true );
	}

	public static function toggle( string $name, string $value, bool $checked, string $title = '' ): void {
		?>
		<label class="ss-toggle">
			<input type="checkbox" id="<?php echo esc_attr( $name ) ?>" name="<?php echo esc_attr( $name ) ?>" value="<?php echo esc_attr( $value ) ?>"<?php checked( $checked ) ?>>
			<div class="ss-toggle__switch"></div>
			<?php echo $title ? esc_html( $title ) : '' ?>
		</label>
		<?php
	}

	public static function feature_box( string $name, string $value, bool $checked, string $title, string $description ): void {
		?>
		<div class="featureBox">
			<?php self::toggle( $name, $value, $checked ) ?>
			<div class="featureBox_body">
				<div class="featureBox_title"><?php echo wp_kses_post( $title ) ?></div>
				<div class="featureBox_description"><?php echo wp_kses_post( $description ) ?></div>
			</div>
		</div>
		<?php
	}
}

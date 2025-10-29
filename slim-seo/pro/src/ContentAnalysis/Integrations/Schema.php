<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class Schema {
	public function __construct() {
		add_action( 'init', [ $this, 'hooks' ] );
	}

	public function hooks() {
		if ( ! defined( 'SLIM_SEO_SCHEMA_DIR' ) ) {
			return;
		}

		add_filter( 'slim_seo_schema_variables', [ $this, 'add_variables' ], 20 );
		add_filter( 'slim_seo_schema_data', [ $this, 'add_data' ], 20 );
	}

	public function add_variables( array $variables ): array {
		$label   = 'Slim SEO';
		$options = [
			'slim_seo.main_keyword' => __( 'Main keyword', 'slim-seo-pro' ),
		];

		foreach ( $variables as &$data ) {
			if ( $data['label'] === $label ) {
				$data['options'] = array_merge( $data['options'], $options );

				return $variables;
			}
		}

		$variables[] = [
			'label'   => $label,
			'options' => $options,
		];

		return $variables;
	}

	public function add_data( array $data ): array {
		$post_id = is_singular() ? get_queried_object_id() : get_the_ID();

		if ( empty( $post_id ) ) {
			return $data;
		}

		$ssp_data              = get_post_meta( $post_id, 'slim_seo_pro', true ) ?: [];
		$content_analysis_data = $ssp_data['content_analysis'] ?? [];

		if ( isset( $content_analysis_data['main_keyword'] ) ) {
			$data['slim_seo']                 = $data['slim_seo'] ?? [];
			$data['slim_seo']['main_keyword'] = $content_analysis_data['main_keyword'];
		}

		return $data;
	}
}

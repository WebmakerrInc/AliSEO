<?php
namespace SlimSEOPro\ContentAnalysis\Integrations;

class SlimSEO {
	public function __construct() {
		add_action( 'init', [ $this, 'hooks' ] );
	}

	public function hooks() {
		if ( ! defined( 'SLIM_SEO_VER' ) ) {
			return;
		}

		add_filter( 'slim_seo_variables', [ $this, 'add_variables' ] );
		add_filter( 'slim_seo_data', [ $this, 'add_data' ], 10, 2 );
	}

	public function add_variables( array $variables ): array {
		$variables[] = [
			'label'   => 'Slim SEO',
			'options' => [
				'slim_seo.main_keyword' => __( 'Main keyword', 'slim-seo-pro' ),
			],
		];

		return $variables;
	}

	public function add_data( array $data, int $post_id ): array {
		$post_id = $post_id ?: ( is_singular() ? get_queried_object_id() : get_the_ID() );

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

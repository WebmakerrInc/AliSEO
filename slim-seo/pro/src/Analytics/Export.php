<?php
namespace SlimSEOPro\Analytics;

use WP_REST_Server;
use WP_REST_Request;
use SlimSEOPro\Api\Base;

class Export extends Base {
	private $analytics_data;

	public function __construct( Data $analytics_data ) {
		parent::__construct();

		$this->analytics_data = $analytics_data;
	}

	public function register_routes() {
		register_rest_route( self::NAMESPACE, 'analytics/export_csv', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'export_csv' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function export_csv( WP_REST_Request $request ) {
		$type                = $request->get_param( 'type' );
		$selected_time       = (array) $request->get_param( 'selectedTime' );
		$order_by            = $request->get_param( 'orderBy' ) ?? 'clicks';
		$order               = $request->get_param( 'order' ) ?? 'DESC';
		$args                = [];
		$args                = array_merge( $args, Helper::get_selected_dates( $selected_time ) );
		$data                = [];
		$header_first_column = '';
		$main_column         = '';

		switch ( $type ) {
			case 'keywords':
				$data                = $this->analytics_data->get_keywords_data( $args, $order_by, $order, null );
				$header_first_column = esc_html( __( 'Keyword', 'slim-seo-pro' ) );
				$main_column         = 'keyword';

				break;
			case 'pages':
				$data                = $this->analytics_data->get_pages_data( $args, $order_by, $order, null );
				$header_first_column = esc_html( __( 'Page', 'slim-seo-pro' ) );
				$main_column         = 'page';

				break;
		}

		if ( empty( $data ) ) {
			return __( 'No data', 'slim-seo-pro' );
		}

		$file_name = "ssp-gsc-{$type}.csv";
		$file_data = [
			[
				$header_first_column,
				esc_html( __( 'Clicks', 'slim-seo-pro' ) ),
				esc_html( __( 'Diff Clicks', 'slim-seo-pro' ) ),
				esc_html( __( 'Impressions', 'slim-seo-pro' ) ),
				esc_html( __( 'Dff Impressions', 'slim-seo-pro' ) ),
				esc_html( __( 'CTR', 'slim-seo-pro' ) ),
				esc_html( __( 'Diff CTR', 'slim-seo-pro' ) ),
				esc_html( __( 'Position', 'slim-seo-pro' ) ),
				esc_html( __( 'Diff Position', 'slim-seo-pro' ) ),
			],
		];

		foreach ( $data as $row ) {
			$file_data[] = [
				$row[ $main_column ],
				$row['clicks'],
				$row['diff']['clicks'] ?? '',
				$row['impressions'],
				$row['diff']['impressions'] ?? '',
				$row['ctr'],
				$row['diff']['ctr'] ?? '',
				$row['position'],
				$row['diff']['position'] ?? '',
			];
		}

		return [
			'file_name' => $file_name,
			'data'      => $file_data,
		];
	}
}

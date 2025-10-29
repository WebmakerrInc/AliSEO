<?php
namespace SlimSEOPro\Analytics;

use WP_REST_Server;
use WP_REST_Request;
use SlimSEOPro\Api\Base;

class Api extends Base {
	private $analytics_data;

	public function __construct( Data $analytics_data ) {
		parent::__construct();

		$this->analytics_data = $analytics_data;
	}

	public function register_routes() {
		register_rest_route( self::NAMESPACE, 'analytics/count_data', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'count_data' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );

		register_rest_route( self::NAMESPACE, 'analytics/get_data', [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'get_data' ],
			'permission_callback' => [ $this, 'has_permission' ],
		] );
	}

	public function count_data( WP_REST_Request $request ) {
		$type          = $request->get_param( 'type' );
		$selected_time = (array) $request->get_param( 'selectedTime' );
		$args          = [];
		$args          = array_merge( $args, Helper::get_selected_dates( $selected_time ) );

		switch ( $type ) {
			case 'keywords':
				return count( $this->analytics_data->get_keywords( $args ) );

			case 'pages':
				return count( $this->analytics_data->get_pages( $args ) );
		}
	}

	public function get_data( WP_REST_Request $request ) {
		$type          = $request->get_param( 'type' );
		$selected_time = (array) $request->get_param( 'selectedTime' );
		$limit         = intval( $request->get_param( 'limit' ) ?? 10 );
		$offset        = intval( $request->get_param( 'offset' ) ?? 0 );
		$location      = $request->get_param( 'location' ) ?? 'page';
		$order_by      = $request->get_param( 'orderBy' ) ?? 'clicks';
		$order         = $request->get_param( 'order' ) ?? 'DESC';
		$args          = [];
		$args          = array_merge( $args, Helper::get_selected_dates( $selected_time ) );

		switch ( $type ) {
			case 'days':
				return $this->analytics_data->get_days_data( $args );

			case 'keywords':
				return $this->analytics_data->get_keywords_data( $args, $order_by, $order, $limit, $offset, $location );

			case 'pages':
				return $this->analytics_data->get_pages_data( $args, $order_by, $order, $limit, $offset, $location );
		}
	}
}

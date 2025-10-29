<?php
namespace SlimSEOPro\Analytics;

use Exception;

class GoogleServiceWebmasters {
	private $service;
	private $client;

	public function __construct( GoogleClient $google_client ) {
		$this->client = $google_client;

		if ( $google_client->is_token_expired() ) {
			return;
		}

		$this->service = new \Google_Service_Webmasters( $google_client->get_client() );
	}

	public function get_sites() {
		if ( $this->client->is_token_expired() ) {
			return [];
		}

		$sites = $this->service->sites->listSites()->getSiteEntry();

		return ! empty( $sites ) ? $sites : [];
	}

	public function search_analytics( $args = [] ) {
		if ( $this->client->is_token_expired() ) {
			return [];
		}

		try {
			$request = new \Google_Service_Webmasters_SearchAnalyticsQueryRequest();

			$request->setSearchType( $args['search_type'] );
			$request->setStartDate( $args['start_date'] );
			$request->setEndDate( $args['end_date'] );
			$request->setRowLimit( $args['limit'] );
			$request->setDimensions( $args['dimensions'] );

			$response = $this->service->searchanalytics->query( $args['site'], $request );
			$rows     = $response->getRows();

			return ! empty( $rows ) ? $rows : [];
		} catch ( Exception $e ) {
			return [];
		}
	}
}

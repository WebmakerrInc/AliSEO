<?php
namespace SlimSEOPro\Analytics;

use SlimSEOPro\Common;
use SlimSEOPro\Cache;

class Data {
	private $google_service_webmasters;

	public function __construct( GoogleServiceWebmasters $google_service_webmasters ) {
		$this->google_service_webmasters = $google_service_webmasters;
	}

	public function get_domains(): array {
		$cache_name = Helper::CACHE_PREFIX . 'domain';
		$cache_data = Cache::get( $cache_name );

		if ( ! empty( $cache_data ) ) {
			return $cache_data;
		}

		$sites = $this->google_service_webmasters->get_sites();

		if ( empty( $sites ) ) {
			return [];
		}

		$domains = [];

		foreach ( $sites as $site ) {
			$site_url = $site->getSiteUrl();

			if ( 'siteRestrictedUser' === $site->getPermissionLevel() ) {
				continue;
			}

			$domains[] = $site_url;
		}

		Cache::set( $cache_name, $domains );

		return $domains;
	}

	public function get_analytics_data( $key, $args = [] ): array {
		$args = array_merge( [
			'search_type' => 'web',
			'start_date'  => gmdate( 'Y-m-d', strtotime( '-6 days' ) ),
			'end_date'    => gmdate( 'Y-m-d' ),
			'limit'       => 25000,
			'dimensions'  => 'date',
			'site'        => Helper::get_current_site(),
		], $args );

		$cache_name = Helper::CACHE_PREFIX . $args['dimensions'] . '_' . $args['start_date'] . '_' . $args['end_date'];
		$cache_data = Cache::get( $cache_name );

		if ( ! empty( $cache_data ) ) {
			return $cache_data;
		}

		$rows = $this->google_service_webmasters->search_analytics( $args );
		$data = [];

		if ( empty( $rows ) ) {
			return $data;
		}

		foreach ( $rows as $row ) {
			$data[] = [
				$key          => reset( $row->keys ),
				'clicks'      => $row->clicks,
				'impressions' => $row->impressions,
				'ctr'         => round( $row->ctr * 100, 2 ),
				'position'    => round( $row->position, 2 ),
			];
		}

		Cache::set( $cache_name, $data );

		return $data;
	}

	public function get_days_data( $args = [] ): array {
		$days = $this->get_analytics_data( 'date', $args );

		if ( empty( $days ) ) {
			return [];
		}

		$data             = Helper::get_stats_summary( $days );
		$same_period_days = $this->get_analytics_data( 'date', array_merge( $args, Helper::get_same_period_dates( $args ) ) );

		if ( ! empty( $same_period_days ) ) {
			$same_period_data = Helper::get_stats_summary( $same_period_days );

			$data = array_merge( $data, [
				'diff_total_clicks'      => $data['total_clicks'] - $same_period_data['total_clicks'],
				'diff_total_impressions' => $data['total_impressions'] - $same_period_data['total_impressions'],
				'diff_avg_ctr'           => $data['avg_ctr'] - $same_period_data['avg_ctr'],
				'diff_avg_position'      => $same_period_data['avg_position'] - $data['avg_position'],
			] );
		}

		foreach ( $data as $key => $value ) {
			$data[ $key ] = Common::number_format( $value );
		}

		$data['days'] = array_map( function ( $day ) {
			$day['date'] = wp_date( 'd M', strtotime( $day['date'] ) );

			return $day;
		}, $days );

		return $data;
	}

	public function get_keywords( $args = [] ): array {
		$args = array_merge( [
			'dimensions' => 'query',
		], $args );

		$keywords = $this->get_analytics_data( 'keyword', $args );

		return $keywords;
	}

	public function get_keywords_data( $args, $order_by, $order, $limit, $offset = 0, $location = '' ): array {
		$keywords = $this->get_keywords( $args );

		if ( empty( $keywords ) ) {
			return [];
		}

		$keywords = Helper::add_diff_data( 'keyword', $keywords, $this->get_keywords( array_merge( $args, Helper::get_same_period_dates( $args ) ) ) );

		usort( $keywords, function ( $keyword1, $keyword2 ) use ( $order_by, $order ) {
			$value1 = '';
			$value2 = '';

			if ( false !== stripos( $order_by, 'diff_' ) ) {
				$order_by = str_replace( 'diff_', '', $order_by );
				$value1   = $keyword1['diff'][ $order_by ] ?? 0;
				$value2   = $keyword2['diff'][ $order_by ] ?? 0;
			} else {
				$value1 = $keyword1[ $order_by ] ?? 0;
				$value2 = $keyword2[ $order_by ] ?? 0;
			}

			if ( 'ASC' === $order ) {
				return $value1 > $value2;
			} else {
				return $value2 > $value1;
			}
		} );

		$clicks = array_slice( $keywords, $offset, $limit );

		if ( 'widget' !== $location ) {
			return Helper::rows_number_format( $clicks );
		}

		usort( $keywords, function ( $keyword1, $keyword2 ) {
			return ( $keyword1['diff']['clicks'] ?? 0 ) < ( $keyword2['diff']['clicks'] ?? 0 );
		} );

		$winning = array_slice( $keywords, $offset, $limit );
		$losing  = array_slice( array_reverse( $keywords ), $offset, $limit );
		$data    = [
			'clicks'  => Helper::rows_number_format( $clicks ),
			'winning' => Helper::rows_number_format( $winning ),
			'losing'  => Helper::rows_number_format( $losing ),
		];

		return $data;
	}

	public function get_pages( $args = [] ): array {
		$args = array_merge( [
			'dimensions' => 'page',
		], $args );

		$pages        = $this->get_analytics_data( 'url', $args );
		$current_site = Common::get_current_site();
		$pages        = array_filter( $pages, function ( $page ) use ( $current_site ) {
			return str_starts_with( $page['url'], $current_site );
		} );

		return $pages;
	}

	public function get_pages_data( $args, $order_by, $order, $limit, $offset = 0, $location = '' ): array {
		$pages = $this->get_pages( $args );

		if ( empty( $pages ) ) {
			return [];
		}

		$pages_data = Common::get_pages_data();
		$pages      = Helper::add_diff_data( 'url', $pages, $this->get_pages( array_merge( $args, Helper::get_same_period_dates( $args ) ) ) );

		usort( $pages, function ( $page1, $page2 ) use ( $order_by, $order ) {
			$value1 = '';
			$value2 = '';

			if ( false !== stripos( $order_by, 'diff_' ) ) {
				$order_by = str_replace( 'diff_', '', $order_by );
				$value1   = $page1['diff'][ $order_by ] ?? 0;
				$value2   = $page2['diff'][ $order_by ] ?? 0;
			} else {
				$value1 = $page1[ $order_by ] ?? 0;
				$value2 = $page2[ $order_by ] ?? 0;
			}

			if ( 'ASC' === $order ) {
				return $value1 > $value2;
			} else {
				return $value2 > $value1;
			}
		} );

		$clicks = array_slice( $pages, $offset, $limit );
		$clicks = Helper::get_pages_output( $clicks, $pages_data );

		if ( 'widget' !== $location ) {
			return Helper::rows_number_format( $clicks );
		}

		usort( $pages, function ( $page1, $page2 ) {
			return ( $page1['diff']['clicks'] ?? 0 ) < ( $page2['diff']['clicks'] ?? 0 );
		} );

		$winning = array_slice( $pages, $offset, $limit );
		$winning = Helper::get_pages_output( $winning, $pages_data );
		$losing  = array_slice( array_reverse( $pages ), $offset, $limit );
		$losing  = Helper::get_pages_output( $losing, $pages_data );
		$data    = [
			'clicks'  => Helper::rows_number_format( $clicks ),
			'winning' => Helper::rows_number_format( $winning ),
			'losing'  => Helper::rows_number_format( $losing ),
		];

		return $data;
	}
}

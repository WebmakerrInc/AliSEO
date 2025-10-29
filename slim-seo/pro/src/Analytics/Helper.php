<?php
namespace SlimSEOPro\Analytics;

use SlimSEOPro\Common;

class Helper {
	const CACHE_PREFIX        = 'ssp_analytics_cache_';
	const CURRENT_SITE_OPTION = 'ssp_analytics_current_site';

	public static function get_current_site() {
		return get_option( self::CURRENT_SITE_OPTION );
	}

	public static function set_current_site( $site ) {
		return update_option( self::CURRENT_SITE_OPTION, $site );
	}

	public static function delete_current_site() {
		return delete_option( self::CURRENT_SITE_OPTION );
	}

	public static function time_filter_options(): array {
		$options = [
			7        => __( '7 days', 'slim-seo-pro' ),
			28       => __( '28 days', 'slim-seo-pro' ),
			90       => __( '3 months', 'slim-seo-pro' ),
			'custom' => __( 'Custom', 'slim-seo-pro' ),
		];

		return $options;
	}

	public static function get_selected_dates( $selected_time ): array {
		$args = [];

		if ( 'custom' === $selected_time['days'] ) {
			$args['start_date'] = $selected_time['start_date'];
			$args['end_date']   = $selected_time['end_date'];
		} else {
			$args['start_date'] = gmdate( 'Y-m-d', strtotime( '-' . ( intval( $selected_time['days'] ) + 2 ) . 'days' ) );
			$args['end_date']   = gmdate( 'Y-m-d', strtotime( '-3 days' ) );
		}

		return $args;
	}

	public static function get_same_period_dates( $args ): array {
		$start_date = new \DateTime( $args['start_date'] );
		$end_date   = new \DateTime( $args['end_date'] );
		$interval   = $end_date->diff( $start_date )->days;

		$start_date->modify( '-' . ( $interval + 1 ) . ' days' );
		$end_date->modify( '-' . ( $interval + 1 ) . ' days' );

		return [
			'start_date' => $start_date->format( 'Y-m-d' ),
			'end_date'   => $end_date->format( 'Y-m-d' ),
		];
	}

	public static function add_diff_data( $key, $rows1, $rows2 ): array {
		if ( empty( $rows2 ) ) {
			return $rows1;
		}

		foreach ( $rows1 as $row1_index => $row1 ) {
			foreach ( $rows2 as $row2 ) {
				if ( $row1[ $key ] !== $row2[ $key ] ) {
					continue;
				}

				$rows1[ $row1_index ]['diff'] = [
					'clicks'      => Common::number_format( $row1['clicks'] - $row2['clicks'] ),
					'impressions' => Common::number_format( $row1['impressions'] - $row2['impressions'] ),
					'ctr'         => Common::number_format( $row1['ctr'] - $row2['ctr'] ),
					'position'    => Common::number_format( $row2['position'] - $row1['position'] ),
				];

				break;
			}
		}

		return $rows1;
	}

	public static function get_pages_output( $pages, $pages_data = [] ): array {
		$pages_data = ! empty( $pages_data ) ? $pages_data : Common::get_pages_data();

		foreach ( $pages as $page_index => $page ) {
			$pages[ $page_index ]['page'] = $page['url'];

			foreach ( $pages_data as $page_data ) {
				if ( $page_data['url'] === $page['url'] ) {
					$pages[ $page_index ]['page']    = $page_data['title'];
					$pages[ $page_index ]['editURL'] = $page_data['editURL'];

					break;
				}
			}
		}

		return $pages;
	}

	public static function get_stats_summary( $rows ): array {
		$total_clicks      = 0;
		$total_impressions = 0;
		$total_ctr         = 0;
		$total_position    = 0;

		foreach ( $rows as $row ) {
			$total_clicks      += $row['clicks'];
			$total_impressions += $row['impressions'];
			$total_ctr         += $row['ctr'];
			$total_position    += $row['position'];
		}

		$total_rows = count( $rows );
		$data       = [
			'total_clicks'      => $total_clicks,
			'total_impressions' => $total_impressions,
			'avg_ctr'           => $total_rows > 0 ? $total_ctr / $total_rows : 0,
			'avg_position'      => $total_rows > 0 ? $total_position / $total_rows : 0,
		];

		return $data;
	}

	public static function rows_number_format( $rows ) {
		return array_map( function ( $row ) {
			$columns = [ 'clicks', 'impressions', 'ctr', 'position' ];

			foreach ( $columns as $column ) {
				if ( isset( $row[ $column ] ) ) {
					$row[ $column ] = Common::number_format( $row[ $column ] );
				}
			}

			return $row;
		}, $rows );
	}
}

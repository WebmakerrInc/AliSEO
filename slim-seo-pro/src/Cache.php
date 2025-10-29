<?php
// phpcs:ignoreFile
namespace SlimSEOPro;

class Cache {
	public static function set( $name, $value, $expiration = DAY_IN_SECONDS ) {
		set_transient( $name, $value, $expiration );
	}

	public static function get( $name ) {
		return get_transient( $name );
	}

	public static function delete_all( $name ) {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_{$name}%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '_transient_timeout_{$name}%'" );
	}
}

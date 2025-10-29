<?php
namespace SlimSEOPro\LinkManager\Helpers;

class Data {
	public static function get_content( int $post_id ): string {
		$content = get_post_field( 'post_content', $post_id );
		$content = do_shortcode( $content );
		$content = do_blocks( $content );

		return $content;
	}
}

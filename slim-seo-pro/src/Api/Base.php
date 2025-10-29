<?php
namespace SlimSEOPro\Api;

abstract class Base {
	const NAMESPACE = 'slim-seo-pro';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	abstract public function register_routes();

	public function has_permission(): bool {
		return current_user_can( 'edit_posts' );
	}
}

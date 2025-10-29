<?php
namespace SlimSEOPro\Schema\Renderer\Factory;

class RankMath extends Base {
	use MergeSchemasTrait;

	public function setup() {
		add_filter( 'rank_math/json_ld', [ $this, 'merge_schemas' ], 99 );
	}
}

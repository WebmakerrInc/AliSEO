<?php
namespace SlimSEOPro\Schema\SchemaTypes;

return [
	[
		'type'     => 'Textarea',
		'id'       => 'code',
		'rows'     => 10,
		'label'    => 'JSON-LD code',
		'required' => true,
		'tooltip'  => __( 'Enter a valid JSON-LD code for a schema. You can insert dynamic variables if needed.', 'slim-seo-schema' ),
		// Translators: %s is a link to the JSLint service.
		'description' => sprintf( __( 'Enter a valid JSON-LD code for a schema. Use a service like <a href="%s" target="_blank">JSLint</a> to validate it. You can insert dynamic variables if needed.', 'slim-seo-schema' ), 'https://jsonlint.com' ),
	],
];

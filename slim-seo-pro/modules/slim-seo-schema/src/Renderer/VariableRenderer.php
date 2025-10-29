<?php
namespace SlimSEOPro\Schema\Renderer;

use SlimTwig\Data as SlimTwigData;

class VariableRenderer {
	private $data_object;
	private $data = [];

	public function __construct( Data $data_object ) {
		$this->data_object = $data_object;
	}

	/**
	 * Render a single prop.
	 *
	 * A prop can be rendered as an array (multiple/cloneable fields from Meta Box) or a simple string.
	 * Because of that, we have to render each variable and combine later.
	 */
	public function render( &$value, bool $is_faq = false ): void {
		// JSON value can be null, false, true, etc.
		if ( empty( $value ) || ! is_string( $value ) ) {
			return;
		}

		// Don't render if no variables.
		if ( ! str_contains( $value, '{{' ) ) {
			// Parse shortcodes, blocks, etc.
			$value = Normalizer::normalize( $value, $is_faq );
			return;
		}

		preg_match_all( '#{{\s*?([^}\s]+?)\s*?}}#', $value, $matches, PREG_PATTERN_ORDER );

		// One variable.
		if ( count( $matches[0] ) === 1 ) {
			$var       = $matches[0][0];
			$var_value = $this->render_variable( $var );
			// Allows to parse post.categories as array. In case of string, allow both dynamic and static texts.
			$value = is_array( $var_value ) ? $var_value : strtr( $value, [ $var => $var_value ] );

			// Parse shortcodes, blocks, etc.
			$value = Normalizer::normalize( $value );
			return;
		}

		// If many variables in the prop, render each one and replace later.
		$replacements = [];
		$count        = 1;
		foreach ( $matches[0] as $var ) {
			$var_value            = $this->render_variable( $var );
			$replacements[ $var ] = $var_value;
			if ( is_array( $var_value ) ) {
				$count = max( $count, count( $var_value ) );
			}
		}

		$return = [];
		for ( $i = 0; $i < $count; $i++ ) {
			$row_replacements = [];
			foreach ( $replacements as $var => $var_value ) {
				$replacement              = is_array( $var_value ) ? ( $var_value[ $i ] ?? '' ) : $var_value;
				$row_replacements[ $var ] = $replacement;
			}
			$return[] = strtr( $value, $row_replacements );
		}

		$value = $count === 1 ? reset( $return ) : $return;

		// Parse shortcodes, blocks, etc.
		$value = Normalizer::normalize( $value, $is_faq );
	}

	private function render_variable( $variable ) {
		$variable = trim( $variable, ' {}' );
		ActiveVariable::set( $variable );
		$value = SlimTwigData::get( $this->get_data(), $variable );
		$value = Normalizer::normalize( $value );
		ActiveVariable::pop();

		return $value;
	}

	private function get_data(): array {
		if ( ! empty( $this->data ) ) {
			return $this->data;
		}

		// Collect all data.
		$this->data = $this->data_object->collect();

		// Parse variable in Slim SEO's metas.
		if ( ! empty( $this->data['slim_seo'] ) ) {
			array_walk( $this->data['slim_seo'], [ $this, 'render' ] );
		}

		if ( empty( $this->data['schemas'] ) ) {
			return $this->data;
		}

		// Render schemas' @id which can be like `{{ current.url }}#webpage`.
		foreach ( $this->data['schemas'] as &$id_value ) {
			$this->render( $id_value['@id'] );
		}

		return $this->data;
	}
}

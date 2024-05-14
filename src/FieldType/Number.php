<?php
namespace WPGraphQLAcf\FieldType;

class Number {

	/**
	 * @return void
	 */
	public static function register_field_type(): void {
		register_graphql_acf_field_type(
			'number',
			[
				'graphql_type' => 'Float',
			] 
		);
	}

}

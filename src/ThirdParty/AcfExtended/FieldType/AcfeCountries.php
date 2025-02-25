<?php

namespace WPGraphQLAcf\ThirdParty\AcfExtended\FieldType;

class AcfeCountries {

	/**
	 * @param array|string $countries
	 *
	 * @return array|null
	 */
	public static function resolve_countries( $countries ): ?array {
		if ( empty( $countries ) ) {
			return null;
		}

		if ( ! function_exists( 'acfe_get_country' ) ) {
			return null;
		}

		if ( ! is_array( $countries ) ) {
			$countries = [ $countries ];
		}

		return array_filter( array_map( static function ( $country ) {
			return acfe_get_country( $country );
		}, $countries ) );
	}

	/**
	 * @return void
	 */
	public static function register_field_type(): void {
		register_graphql_acf_field_type( 'acfe_countries', [
			'graphql_type' => function () {
				return [ 'list_of' => 'ACFE_Country' ];
			},
			'resolve'      => static function ( $root, $args, $context, $info, $field_type, $field_config ) {
				$value = $field_config->resolve_field( $root, $args, $context, $info );

				return self::resolve_countries( $value );
			},
		]);
	}

}

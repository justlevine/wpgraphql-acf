<?php

namespace WPGraphQLAcf\ThirdParty\AcfExtended\FieldType;

class AcfeCurrencies {

	/**
	 * @param string|array $currencies
	 *
	 * @return array|null
	 */
	public static function resolve_currencies( $currencies ): ?array {

		if ( empty( $currencies ) ) {
			return null;
		}

		if ( ! function_exists( 'acfe_get_currency' ) ) {
			return null;
		}

		if ( ! is_array( $currencies ) ) {
			$currencies = [ $currencies ];
		}

		return array_filter( array_map( static function ( $currency ) {
			return acfe_get_currency( $currency );
		}, $currencies ) );

	}

	/**
	 * @return void
	 */
	public static function register_field_type(): void {
		register_graphql_acf_field_type( 'acfe_currencies', [
			'graphql_type' => function () {
				return [ 'list_of' => 'ACFE_Currency' ];
			},
			'resolve'      => static function ( $root, $args, $context, $info, $field_type, $field_config ) {
				$value = $field_config->resolve_field( $root, $args, $context, $info );

				return self::resolve_currencies( $value );
			},
		]);
	}

}

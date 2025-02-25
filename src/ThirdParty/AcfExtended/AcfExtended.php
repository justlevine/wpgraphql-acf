<?php
namespace WPGraphQLAcf\ThirdParty\AcfExtended;

use Exception;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeAdvancedLink;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeCodeEditor;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeCountries;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeCurrencies;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeDateRangePicker;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeImageSelector;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeImageSizes;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeLanguages;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeMenuLocations;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeMenus;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfePhoneNumber;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfePostFormats;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeTaxonomies;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeTaxonomyTerms;
use WPGraphQLAcf\ThirdParty\AcfExtended\FieldType\AcfeUserRoles;

class AcfExtended {

	/**
	 * Initialize support for ACF Extended
	 *
	 * @return void
	 */
	public function init(): void {

		// if ACFE is not active, don't add support for ACFE Features
		if ( ! self::is_acfe_active() ) {
			return;
		}

		// ACF Extended Pro
		add_filter( 'graphql_acf_should_field_group_show_in_graphql', [ $this, 'filter_out_acfe_dynamic_groups' ], 10, 2 );
		add_action( 'graphql_register_types', [ $this, 'register_initial_types' ] );
		add_action( 'graphql_acf_registry_init', [ $this, 'register_field_types' ] );

	}

	/**
	 * Whether ACF Extended is active
	 *
	 * @return bool
	 */
	public static function is_acfe_active(): bool {

		$is_active = class_exists( 'ACFE' ) || defined( 'TESTS_ACF_EXTENDED_IS_ACTIVE' );

		// Filter the response. This is helpful for test environments to mock tests as if the plugin were active
		return (bool) apply_filters( 'graphql_acf_is_acfe_active', $is_active );
	}

	/**
	 * Prevent ACF Extended's "dynamic form" and "dynamic form side" field groups from being mapped to the WPGraphQL Schema as they cause infinite recursion.
	 *
	 * @param bool $should
	 * @param array $acf_field_group
	 *
	 * @return bool
	 */
	public function filter_out_acfe_dynamic_groups( bool $should, array $acf_field_group ): bool {
		if ( ! empty( $acf_field_group['key'] ) && in_array( $acf_field_group['key'], [ 'group_acfe_dynamic_form', 'group_acfe_dynamic_form_side' ], true ) ) {
			$should = false;
		}

		return $should;
	}

	/**
	 * Register initial types for ACF Extended field types to use
	 *
	 * @return void
	 * @throws Exception
	 */
	public function register_initial_types(): void {

		register_graphql_object_type( 'ACFE_Country', [
			'description' => __( 'ACFE Country Object', 'wp-graphql-acf' ),
			'fields'      => [
				'code'       => [
					// @todo: CountryCode Scalar. See: https://github.com/Urigo/graphql-scalars/blob/master/src/scalars/CountryCode.ts
					'type'        => 'String',
					'description' => __( 'A country code as defined by ISO 3166-1 alpha-2', 'wp-graphql-acf' ),
				],
				'name'       => [
					'type'        => 'String',
					'description' => __( 'The name of the country', 'wp-graphql-acf' ),
				],
				'localized'  => [
					'type'        => 'String',
					'description' => __( 'The name of the country, localized to the current locale', 'wp-graphql-acf' ),
				],
				'native'     => [
					'type'        => 'String',
					'description' => __( 'The name of the country, in the country\'s native dialect', 'wp-graphql-acf' ),
				],
				'dial'       => [
					'type'        => 'Integer',
					'description' => __( 'The calling code for the country', 'wp-graphql-acf' ),
				],
				'capital'    => [
					'type'        => 'String',
					'description' => __( 'The name of the Country\'s capital city', 'wp-graphql-acf' ),
				],
				'people'     => [
					'type'        => 'String',
					'description' => __( 'The term used to denote the inhabitants of the country', 'wp-graphql-acf' ),
				],
				'continent'  => [
					'type'        => 'String',
					'description' => __( 'The name of the continent the country is in', 'wp-graphql-acf' ),
				],
				'latitude'   => [
					'type'        => 'Float',
					'description' => __( 'The latitude of the country\'s position', 'wp-graphql-acf' ),
					'resolve'     => function ( $country ) {
						return $country['coords']['lat'] ?? null;
					},
				],
				'longitude'  => [
					'type'        => 'Float',
					'description' => __( 'The longitude of the country\'s position', 'wp-graphql-acf' ),
					'resolve'     => function ( $country ) {
						return $country['coords']['lng'] ?? null;
					},
				],
				'languages'  => [
					'type'        => [ 'list_of' => 'ACFE_Language' ],
					'description' => __( 'A list of languages spoken in the country', 'wp-graphql-acf' ),
					'resolve'     => function ( $root ) {
						$value = $root['languages'] ?? null;
						return AcfeLanguages::resolve_languages( $value );
					},
				],
				'currencies' => [
					'type'        => [ 'list_of' => 'ACFE_Currency' ],
					'description' => __( 'A list of currencies used in the country', 'wp-graphql-acf' ),
					'resolve'     => function ( $root ) {
						$value = $root['currencies'] ?? null;
						return AcfeCurrencies::resolve_currencies( $value );
					},
				],
			],
		]);

		register_graphql_object_type( 'ACFE_Currency', [
			'fields' => [
				'code'      => [
					'type'        => 'String',
					'description' => __( 'The currency code according to ISO 4217: https://en.wikipedia.org/wiki/ISO_4217', 'wp-graphql-acf' ),
				],
				'name'      => [
					'type'        => 'String',
					'description' => __( 'The name of the currency', 'wp-graphql-acf' ),
				],
				'symbol'    => [
					'type'        => 'String',
					'description' => __( 'The symbol used to represent the currency (i.e. $)', 'wp-graphql-acf' ),
				],
				'flag'      => [
					'type'        => 'String',
					'description' => __( 'The code representing the flag for the country', 'wp-graphql-acf' ),
				],
				'continent' => [
					'type'        => 'String',
					'description' => __( 'The name of the continent the currency is used in', 'wp-graphql-acf' ),
				],
				'countries' => [
					'type'        => [ 'list_of' => 'ACFE_Country' ],
					'description' => __( 'A list of countries the currency is used in', 'wp-graphql-acf' ),
					'resolve'     => function ( $root ) {
						$value = $root['countries'] ?? null;
						return AcfeCountries::resolve_countries( $value );
					},
				],
				'languages' => [
					'type'        => [ 'list_of' => 'ACFE_Language' ],
					'description' => __( 'A list of languages spoken in the country', 'wp-graphql-acf' ),
					'resolve'     => function ( $root ) {
						$value = $root['languages'] ?? null;
						return AcfeLanguages::resolve_languages( $value );
					},
				],
			],
		]);

		register_graphql_object_type( 'ACFE_Language', [
			'description' => __( 'ACFE Language Object', 'wp-graphql-acf' ),
			'fields'      => [
				'code'       => [
					'type'        => 'String',
					'description' => __( 'A 2-letter language code as defined by ISO 639-1. https://en.wikipedia.org/wiki/ISO_639-1', 'wp-graphql-acf' ),
				],
				'locale'     => [
					'type'        => 'String',
					'description' => __( 'The locale in the format of a BCP 47 (RFC 5646) standard string', 'wp-graphql-acf' ),
				],
				'alt'        => [
					'type'        => 'String',
					'description' => __( 'Alternative locale in the format of a BCP 47 (RFC 5646) standard string', 'wp-graphql-acf' ),
				],
				'name'       => [
					'type'        => 'String',
					'description' => __( 'The name of the language', 'wp-graphql-acf' ),
				],
				'native'     => [
					'type'        => 'String',
					'description' => __( 'The name of the language, in the country\'s native dialect', 'wp-graphql-acf' ),
				],
				'dir'        => [
					'type'        => 'String',
					'description' => __( 'The direction of the language. (i.e. ltr / rtl / ttb, btt )', 'wp-graphql-acf' ),
				],
				'flag'       => [
					'type'        => 'String',
					'description' => __( 'The code representing the flag for the country', 'wp-graphql-acf' ),
				],
				'countries'  => [
					'type'    => [ 'list_of' => 'ACFE_Country' ],
					'resolve' => function ( $root ) {
						$value = $root['countries'] ?? null;
						return AcfeCountries::resolve_countries( $value );
					},
				],
				'currencies' => [
					'type'    => [ 'list_of' => 'ACFE_Currency' ],
					'resolve' => function ( $root ) {
						$value = $root['currencies'] ?? null;
						return AcfeCurrencies::resolve_currencies( $value );
					},
				],
			],
		] );

		register_graphql_interface_type( 'ACFE_AdvancedLink', [
			'fields'      => [
				'linkText'              => [
					'type'    => 'String',
					'resolve' => function ( $link ) {
						return $link['title'] ?? null;
					},
				],
				'shouldOpenInNewWindow' => [
					'type'    => 'Boolean',
					'resolve' => function ( $link ) {
						return (bool) $link['target'];
					},
				],
			],
			'resolveType' => function ( $node ) {

				$type = 'ACFE_AdvancedLink_Url';

				if ( ! isset( $node['type'] ) ) {
					return $type;
				}

				switch ( $node['type'] ) {
					case 'post':
						$type = 'ACFE_AdvancedLink_ContentNode';
						break;
					case 'term':
						$type = 'ACFE_AdvancedLink_TermNode';
						break;
				}

				return $type;
			},
		]);

		register_graphql_object_type( 'ACFE_AdvancedLink_Url', [
			'interfaces' => [ 'ACFE_AdvancedLink' ],
			'fields'     => [
				'url' => [
					'type' => 'String',
				],
			],
		]);

		register_graphql_object_type( 'ACFE_AdvancedLink_ContentNode', [
			'interfaces'      => [ 'ACFE_AdvancedLink' ],
			'eagerlyLoadType' => true,
			'fields'          => [
				'contentNode' => [
					'type'    => 'ContentNode',
					'resolve' => function ( $source, $args, $context, $info ) {

						if ( empty( $source['value'] ) ) {
							return null;
						}

						return $context->get_loader( 'post' )->load_deferred( absint( $source['value'] ) );
					},
				],
			],
		]);

		register_graphql_object_type( 'ACFE_AdvancedLink_TermNode', [
			'interfaces'      => [ 'ACFE_AdvancedLink' ],
			'eagerlyLoadType' => true,
			'fields'          => [
				'term' => [
					'type'    => 'TermNode',
					'resolve' => function ( $source, $args, $context, $info ) {
						if ( empty( $source['value'] ) ) {
							return null;
						}

						return $context->get_loader( 'term' )->load_deferred( absint( $source['value'] ) );
					},
				],
			],
		]);

		register_graphql_object_type( 'ACFE_Date_Range', [
			'description' => __( 'A date range made up of a start date and end date', 'wp-graphql-acf' ),
			'fields'      => [
				'startDate' => [
					// @todo: DATETIME Scalar
					'type'        => 'String',
					'description' => __( 'The start date of a date range returned as an RFC 3339 time string', 'wp-graphql-acf' ),
				],
				'endDate'   => [
					// @todo: DATETIME Scalar
					'type'        => 'String',
					'description' => __( 'The start date of a date range RFC 3339 time string', 'wp-graphql-acf' ),
				],
			],
		]);

		register_graphql_object_type( 'ACFE_Image_Size', [
			'description' => __( 'Registered image size', 'wp-graphql-acf' ),
			'fields'      => [
				'name'   => [
					'type'        => 'String',
					'description' => __( 'Image size identifier.', 'wp-graphql-acf' ),
				],
				'width'  => [
					'type'        => 'Int',
					'description' => __( 'Image width in pixels. Default 0.', 'wp-graphql-acf' ),
				],
				'height' => [
					'type'        => 'Int',
					'description' => __( 'Image height in pixels. Default 0.', 'wp-graphql-acf' ),
				],
				'crop'   => [
					'type'        => 'Boolean',
					'description' => __( 'Image cropping behavior. If false, the image will be scaled (default), If true, image will be cropped to the specified dimensions using center positions', 'wp-graphql-acf' ),
				],
			],
		]);

	}

	/**
	 * @return void
	 */
	public function register_field_types(): void {

		// not supported in the schema:
		// - acfe_button
		// - acfe_hidden
		// - acfe_recaptcha (not supported. see: https://www.acf-extended.com/features/fields/recaptcha)
		//   "The value cannot be retrieved, as the field isn’t saved as custom meta"
		// - acfe_post_statuses
		//   @todo: There seems to be general bugs with Post Statuses in core WPGraphQL Still
		// - acfe_block_types
		//   @todo
		// - acfe_field_groups
		//   @todo
		// - acfe_field_types
		//   @todo
		// - acfe_fields
		//   @todo
		// - acfe_forms
		//   @todo
		// - acfe_options_pages
		//   @todo
		// - acfe_templates
		//   @todo
		// - acfe_payment
		//   @todo
		// - acfe_payment_cart
		//   Not supported: ACFE Docs state: The value cannot be retrieved as the field isn’t saved as meta data.
		// - acfe_payment_selector
		//   NOT supported: ACFE Docs state: The value cannot be retrieved as the field isn’t saved as meta data.
		// - acfe_column
		//   Not supported. ACFE docs state: The value cannot be retrieved as the field isn’t saved as meta data.
		// - acfe_dynamic_render
		//   Not supported. ACFE docs state: "The value cannot be retrieved as the field isn’t saved as meta data."
		// - acfe_post_field
		//   Not supported. ACFE docs state: "The value cannot be retrieved as the field isn’t saved as meta data. Values are saved directly within the WP Post Object instead."


		// Supported Fields

		AcfeCodeEditor::register_field_type();
		AcfeCountries::register_field_type();
		AcfeCurrencies::register_field_type();
		AcfeImageSelector::register_field_type();
		AcfeLanguages::register_field_type();
		AcfeAdvancedLink::register_field_type();
		AcfeTaxonomyTerms::register_field_type();
		AcfeDateRangePicker::register_field_type();
		AcfePhoneNumber::register_field_type();
		AcfeImageSizes::register_field_type();
		AcfeMenuLocations::register_field_type();
		AcfeMenus::register_field_type();
		AcfePostFormats::register_field_type();
		AcfeTaxonomies::register_field_type();
		AcfeUserRoles::register_field_type();

	}

}

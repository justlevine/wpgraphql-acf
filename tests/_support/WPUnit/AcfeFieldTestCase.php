<?php
namespace Tests\WPGraphQLAcf\WPUnit;

abstract class AcfeFieldTestCase extends \Tests\WPGraphQLAcf\WPUnit\AcfFieldTestCase {

	public function _setUp() {

		// if the plugin version is before 6.1, we're not testing this functionality
		if ( ! class_exists( 'ACFE_PRO' ) ) {
			$this->markTestSkipped( 'ACF Extended Pro is not active so this test will not run.' );
		}

		parent::_setUp(); // TODO: Change the autogenerated stub
	}

	public function _tearDown() {
		parent::_tearDown(); // TODO: Change the autogenerated stub
	}

	/**
	 * Return the acf "field_type". ex. "text", "textarea", "flexible_content", etc
	 * @return string
	 */
	abstract public function get_field_type(): string;

	public function testFieldExists(): void {
		$field_types = acf_get_field_types();
		$this->assertTrue( array_key_exists( $this->get_field_type(), $field_types ) );
	}

}

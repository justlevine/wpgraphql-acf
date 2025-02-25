<?php

class PostObjectFieldTest extends \Tests\WPGraphQLAcf\WPUnit\AcfFieldTestCase {

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
	}


	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();
	}

	public function get_field_type(): string {
		return 'post_object';
	}

	public function get_expected_field_resolve_type(): ?string {
		return 'AcfTestGroupTestPostObjectToSingleContentNodeConnectionEdge';
	}

	public function get_expected_field_resolve_kind(): ?string {
		return 'OBJECT';
	}

}

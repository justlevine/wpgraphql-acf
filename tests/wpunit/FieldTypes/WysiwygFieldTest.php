<?php

class WysiwygFieldTest extends \Tests\WPGraphQLAcf\WPUnit\AcfFieldTestCase {

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
		return 'wysiwyg';
	}

	public function get_expected_field_resolve_type(): ?string {
		return 'String';
	}

	public function queryForPostByDatabaseId() {
		return '
		query GetPost($id:ID!){
		  post(id:$id idType:DATABASE_ID) {
		    __typename
		    ...OnWithAcfAcfTestGroup {
		      acfTestGroup {
		        ' . $this->get_formatted_field_name(). '
		      }
		    }
		  }
		}
		';
	}

//	public function testQueryFieldOnPostReturnsExpectedValue() {
//
//		$value = 'test content';
//
//		update_field( $this->published_post->ID, $this->get_field_name(), $value );
//
//		$actual = $this->graphql([
//			'query' => $this->queryForPostByDatabaseId(),
//			'variables' => [
//				'id' => $this->published_post->ID,
//			],
//		]);
//
//		codecept_debug( $actual );
//
//	}

}

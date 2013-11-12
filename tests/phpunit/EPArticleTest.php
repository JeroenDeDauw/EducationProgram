<?php

namespace EducationProgram\Tests;

use EducationProgram\Course;
use EducationProgram\EPArticle;

/**
 * Tests for the EPArticle class.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 0.3
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group EPArticleTest
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPArticleTest extends \PHPUnit_Framework_TestCase {

	public function constructorProvider() {
		$argLists = array();

		$argLists[] = array( 1, 42, 23, 9001, 'Foobar', array() );
		$argLists[] = array( null, 42, 23, 9001, 'Foobar', array() );
		$argLists[] = array( null, 42, 23, 9001, 'Foobar', array( 1, 2, 3 ) );
		$argLists[] = array( 42, 1, 2, 3, 'Foo_bar_baz!', array( 8822, 5566, 1144, 5, 6, 7 ) );
		$argLists[] = array( 1, 1, 1, 1, 'A', array( 2 ) );

		return $argLists;
	}

	/**
	 * @dataProvider constructorProvider
	 *
	 * @since 0.3
	 *
	 * @param int|null $id
	 * @param int $courseId
	 * @param int $userId
	 * @param int $pageId
	 * @param string $pageTitle
	 * @param int[] $reviewers
	 */
	public function testConstructor( $id, $courseId, $userId, $pageId, $pageTitle, array $reviewers ) {
		$article = new EPArticle( $id, $courseId, $userId, $pageId, $pageTitle, $reviewers );

		$this->assertTrue( is_null( $article->getId() ) || is_int( $id ), '$id needs to be null or int' );
		$this->assertEquals( $id, $article->getId() );

		$this->assertInternalType( 'int', $article->getCourseId() );
		$this->assertEquals( $courseId, $article->getCourseId() );

		$this->assertInternalType( 'int', $article->getUserId() );
		$this->assertEquals( $userId, $article->getUserId() );

		$this->assertInternalType( 'int', $article->getPageId() );
		$this->assertEquals( $pageId, $article->getPageId() );

		$this->assertInternalType( 'string', $article->getPageTitle() );
		$this->assertEquals( $pageTitle, $article->getPageTitle() );

		$this->assertInternalType( 'array', $article->getReviewers() );
		$this->assertContainsOnly( 'int', $article->getReviewers() );
		$this->assertEquals( $reviewers, $article->getReviewers() );
	}

	public function addReviewerProvider() {
		$argLists = array();

		$argLists[] = array(
			array(),
			array(),
			array(),
			array(),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array(),
			array(),
			array( 1, 2, 3 ),
		);

		$argLists[] = array(
			array(),
			array( 1, 2, 3 ),
			array( 1, 2, 3 ),
			array( 1, 2, 3 ),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array( 1, 2, 3 ),
			array(),
			array( 1, 2, 3 ),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array( 4, 5 ),
			array( 4, 5 ),
			array( 1, 2, 3, 4, 5 ),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array( 4, 5, 1, 2 ),
			array( 4, 5 ),
			array( 1, 2, 3, 4, 5 ),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array( 1, 2, 5, 1, 2 ),
			array( 5 ),
			array( 1, 2, 3, 5 ),
		);

		$argLists[] = array(
			array(),
			array( 1, 1, 1 ),
			array( 1 ),
			array( 1 ),
		);

		return $argLists;
	}

	/**
	 * @dataProvider addReviewerProvider
	 *
	 * @param array $original
	 * @param array $new
	 * @param array $expectedAdded
	 * @param array $expected
	 */
	public function testAddReviewers( array $original, array $new, array $expectedAdded, array $expected ) {
		$article = new EPArticle( 1, 2, 3, 4, 'Nyan', $original );

		$added = $article->addReviewers( $new );

		$this->assertInternalType( 'array', $added );
		$this->assertContainsOnly( 'int', $added );

		$this->assertListEquals(
			$expectedAdded,
			$added
		);

		$this->assertListEquals(
			$expected,
			$article->getReviewers()
		);
	}

	/**
	 * Does not care about order or keys.
	 */
	protected function assertListEquals( array $expected, array $actual ) {
		sort( $expected );
		sort( $actual );

		$expected = array_values( $expected );
		$actual = array_values( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function removeReviewerProvider() {
		$argLists = array();

		$argLists[] = array(
			array(),
			array(),
			array(),
			array(),
		);

		$argLists[] = array(
			array( 1, 2, 3 ),
			array(),
			array(),
			array( 1, 2, 3 ),
		);

		$argLists[] = array(
			array(),
			array( 1, 2, 3 ),
			array(),
			array(),
		);

		$argLists[] = array(
			array( 1, 2 , 3 ),
			array( 1 ),
			array( 1 ),
			array( 2, 3 ),
		);

		$argLists[] = array(
			array( 1, 2 , 3 ),
			array( 1, 3 ),
			array( 1, 3 ),
			array( 2 ),
		);

		$argLists[] = array(
			array( 1, 2 , 3 ),
			array( 1, 3, 2 ),
			array( 1, 3, 2 ),
			array(),
		);

		$argLists[] = array(
			array( 1, 2 , 3 ),
			array( 4, 2, 5, 3, 42 ),
			array( 2, 3 ),
			array( 1 ),
		);

		$argLists[] = array(
			array( 1, 2 , 3 ),
			array( 1, 1, 1 ),
			array( 1 ),
			array( 2, 3 ),
		);

		return $argLists;
	}

	/**
	 * @dataProvider removeReviewerProvider
	 *
	 * @param array $original
	 * @param array $new
	 * @param array $expectedRemoved
	 * @param array $expected
	 */
	public function testRemoveReviewers( array $original, array $new, array $expectedRemoved, array $expected ) {
		$article = new EPArticle( 1, 2, 3, 4, 'Nyan', $original );

		$removed = $article->removeReviewers( $new );

		$this->assertInternalType( 'array', $removed );
		$this->assertContainsOnly( 'int', $removed );

		$this->assertListEquals(
			$expectedRemoved,
			$removed
		);

		$this->assertListEquals(
			$expected,
			$article->getReviewers()
		);
	}

	public function testLogAdditionNonExistingPage() {
		$course = $this->getMock( 'EducationProgram\Course' );

		$course->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foobar' ) ) );

		$article = $this->getMock(
			'EducationProgram\EPArticle',
			array( 'getCourse', 'getUser' ),
			array( 1, 2, 3, 0, 'sdncjsdhbfkdhbgsfxdfg', array() )
		);

		$article->expects( $this->any() )
			->method( 'getCourse' )
			->will( $this->returnValue( $course ) );

		$user = new MockSuperUser();

		$article->expects( $this->any() )
			->method( 'getUser' )
			->will( $this->returnValue( $user ) );

		$article->logAddition(
			$user,
			false
		);

		$this->assertTrue( true );
	}

}

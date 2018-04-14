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
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 *
 * @covers \EducationProgram\EPArticle
 */
class EPArticleTest extends \PHPUnit\Framework\TestCase {

	public function constructorProvider() {
		$argLists = [];

		$argLists[] = [ 1, 42, 23, 9001, 'Foobar', [] ];
		$argLists[] = [ null, 42, 23, 9001, 'Foobar', [] ];
		$argLists[] = [ null, 42, 23, 9001, 'Foobar', [ 1, 2, 3 ] ];
		$argLists[] = [ 42, 1, 2, 3, 'Foo_bar_baz!', [ 8822, 5566, 1144, 5, 6, 7 ] ];
		$argLists[] = [ 1, 1, 1, 1, 'A', [ 2 ] ];

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
	public function testConstructor(
		$id, $courseId, $userId, $pageId, $pageTitle, array $reviewers
	) {
		$article = new EPArticle( $id, $courseId, $userId, $pageId, $pageTitle, $reviewers );

		$this->assertTrue( is_null( $article->getId() ) || is_int( $id ),
			'$id needs to be null or int' );
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
		$argLists = [];

		$argLists[] = [
			[],
			[],
			[],
			[],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[],
			[],
			[ 1, 2, 3 ],
		];

		$argLists[] = [
			[],
			[ 1, 2, 3 ],
			[ 1, 2, 3 ],
			[ 1, 2, 3 ],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[ 1, 2, 3 ],
			[],
			[ 1, 2, 3 ],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[ 4, 5 ],
			[ 4, 5 ],
			[ 1, 2, 3, 4, 5 ],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[ 4, 5, 1, 2 ],
			[ 4, 5 ],
			[ 1, 2, 3, 4, 5 ],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[ 1, 2, 5, 1, 2 ],
			[ 5 ],
			[ 1, 2, 3, 5 ],
		];

		$argLists[] = [
			[],
			[ 1, 1, 1 ],
			[ 1 ],
			[ 1 ],
		];

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
	public function testAddReviewers(
		array $original, array $new, array $expectedAdded, array $expected
	) {
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
		$argLists = [];

		$argLists[] = [
			[],
			[],
			[],
			[],
		];

		$argLists[] = [
			[ 1, 2, 3 ],
			[],
			[],
			[ 1, 2, 3 ],
		];

		$argLists[] = [
			[],
			[ 1, 2, 3 ],
			[],
			[],
		];

		$argLists[] = [
			[ 1, 2 , 3 ],
			[ 1 ],
			[ 1 ],
			[ 2, 3 ],
		];

		$argLists[] = [
			[ 1, 2 , 3 ],
			[ 1, 3 ],
			[ 1, 3 ],
			[ 2 ],
		];

		$argLists[] = [
			[ 1, 2 , 3 ],
			[ 1, 3, 2 ],
			[ 1, 3, 2 ],
			[],
		];

		$argLists[] = [
			[ 1, 2 , 3 ],
			[ 4, 2, 5, 3, 42 ],
			[ 2, 3 ],
			[ 1 ],
		];

		$argLists[] = [
			[ 1, 2 , 3 ],
			[ 1, 1, 1 ],
			[ 1 ],
			[ 2, 3 ],
		];

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
	public function testRemoveReviewers(
		array $original, array $new, array $expectedRemoved, array $expected
	) {
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
		$course = $this->getMockBuilder( Course::class )->getMock();

		$course->expects( $this->any() )
			->method( 'getTitle' )
			->will( $this->returnValue( \Title::newFromText( 'Foobar' ) ) );

		$article = $this->getMockBuilder( EPArticle::class )
			->setConstructorArgs( [ 1, 2, 3, 0, 'sdncjsdhbfkdhbgsfxdfg', [] ] )
			->setMethods( [ 'getCourse', 'getUser' ] )
			->getMock();

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

<?php

namespace EducationProgram\Tests;

use EducationProgram\ArticleStore;
use EducationProgram\EPArticle;

/**
 * Tests for the EducationProgram\ArticleStore class.
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
 * @group ArticleStoreTest
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleStoreTest extends \MediaWikiTestCase {

	public function testConstructor() {
		new ArticleStore( 'ep_articles' );
		$this->assertTrue( true );
	}

	public function articleProvider() {
		$articles = array();

		$articles[] = new EPArticle(
			null,
			2,
			3,
			4,
			'Foo_bar',
			array()
		);

		$articles[] = new EPArticle(
			null,
			2000,
			3000,
			4000,
			'A',
			array( 1, 2, 5, 42, 3 )
		);

		return $this->arrayWrap( $articles );
	}

	/**
	 * @dataProvider articleProvider
	 */
	public function testInsertAndUpdateArticle( EPArticle $article ) {
		$store = $this->newStore();

		$this->assertFalse(
			$store->hasArticle( $article->getId() ),
			'store does not have article before save'
		);

		$insertId = $store->insertArticle( $article );

		$this->assertInternalType(
			'int',
			$insertId,
			'insertArticle returned a new id'
		);

		$this->assertTrue(
			$store->hasArticle( $insertId ),
			'store has article after save'
		);

		$newArticle = new EPArticle(
			$insertId,
			$article->getCourseId() + 1,
			$article->getUserId() + 1,
			$article->getPageId() + 1,
			'Nyan',
			array( 9001 )
		);

		$this->assertTrue(
			$store->updateArticle( $newArticle ),
			'updateArticle returned true'
		);

		$actualArticle = $store->getArticle( $insertId );

		$this->assertInstanceOf( 'EducationProgram\EPArticle', $actualArticle );

		$this->assertEquals( $newArticle, $actualArticle );

		$this->assertTrue(
			$store->deleteArticle( $insertId ),
			'deleteArticle returned true'
		);

		$this->assertFalse(
			$store->hasArticle( $insertId ),
			'store does not have article after delete'
		);
	}

	protected function newStore() {
		return new ArticleStore( 'ep_articles' );
	}

	public function testDeleteNonExistingArticle() {
		$this->assertTrue( $this->newStore()->deleteArticle( 99999 ) );
	}

	public function testInsertWithId() {
		$this->setExpectedException( 'InvalidArgumentException' );

		$this->newStore()->insertArticle(
			new EPArticle(
				1,
				1,
				1,
				1,
				'foo',
				array()
			)
		);
	}

	public function testUpdateWithoutId() {
		$this->setExpectedException( 'InvalidArgumentException' );

		$this->newStore()->updateArticle(
			new EPArticle(
				null,
				1,
				1,
				1,
				'foo',
				array()
			)
		);
	}

	/**
	 * @dataProvider articleProvider
	 */
	public function testHasArticleWith( EPArticle $article ) {
		$store = $this->newStore();

		$this->assertFalse(
			$store->hasArticleWith(
				4567891,
				4567892,
				4567893
			),
			'non-existing article not found'
		);

		$this->assertInternalType(
			'int',
			$store->insertArticle( $article )
		);

		$this->assertTrue(
			$store->hasArticleWith(
				$article->getCourseId(),
				$article->getUserId(),
				$article->getPageId()
			),
			'existing article found'
		);
	}

	public function testDeleteByCourseAndUsers() {
		$store = $this->newStore();

		$id0 = $store->insertArticle(
			new EPArticle(
				null,
				1,
				1,
				1,
				'foo',
				array()
			)
		);

		$id1 = $store->insertArticle(
			new EPArticle(
				null,
				1,
				1,
				2,
				'bar',
				array()
			)
		);

		$id2 = $store->insertArticle(
			new EPArticle(
				null,
				2,
				1,
				3,
				'baz',
				array()
			)
		);

		$id3 = $store->insertArticle(
			new EPArticle(
				null,
				1,
				2,
				4,
				'bah',
				array()
			)
		);

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( 0, 1 ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( 1, 0 ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertHasArticles( $store, array( $id0, $id1, $id2, $id3 ) );

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( 1, 1 ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertHasArticles( $store, array( $id2, $id3 ) );
		$this->assertNotHasArticles( $store, array( $id0, $id1 ) );

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( array( 1 ), array( 1 ) ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( array( 2, 3, 4 ), array( 1, 2, 3, 4 ) ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertHasArticles( $store, array( $id3 ) );
		$this->assertNotHasArticles( $store, array( $id2 ) );

		$this->assertTrue(
			$store->deleteArticleByCourseAndUsers( array( 1, 2, 3 ), 2 ),
			'deleteArticleByCourseAndUsers returned true'
		);

		$this->assertNotHasArticles( $store, array( $id3 ) );
	}

	/**
	 * @param ArticleStore $store
	 * @param int[] $articleIds
	 */
	protected function assertHasArticles( ArticleStore $store, array $articleIds ) {
		foreach ( $articleIds as $articleId ) {
			$this->assertTrue(
				$store->hasArticle( $articleId ),
				"The store should have an article with id '$articleId'"
			);
		}
	}

	/**
	 * @param ArticleStore $store
	 * @param int[] $articleIds
	 */
	protected function assertNotHasArticles( ArticleStore $store, array $articleIds ) {
		foreach ( $articleIds as $articleId ) {
			$this->assertFalse(
				$store->hasArticle( $articleId ),
				"The store should NOT have an article with id '$articleId'"
			);
		}
	}

	public function testDeleteArticleByCourseAndUsersWithEmptyArrayAsFirstArgument() {
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->newStore()->deleteArticleByCourseAndUsers( array(), 2 );
	}

	public function testDeleteArticleByCourseAndUsersWithEmptyArrayAsSecondArgument() {
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->newStore()->deleteArticleByCourseAndUsers( array( 2 ), array() );
	}

	public function testGetArticlesByCourseAndUsersEmptyArrayAsSecondArgument() {
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->newStore()->getArticlesByCourseAndUsers( array( 2 ), array() );
	}

	public function testGetArticlesByCourseAndUsersEmptyArrayAsFirstArgument() {
		$this->setExpectedException( 'InvalidArgumentException' );
		$this->newStore()->getArticlesByCourseAndUsers( array(), 2 );
	}

	public function testGetArticlesByCourseAndUsers() {
		$store = $this->newStore();

		$id0 = $store->insertArticle(
			new EPArticle(
				null,
				33331,
				44441,
				1,
				'foo',
				array()
			)
		);

		$id1 = $store->insertArticle(
			new EPArticle(
				null,
				33331,
				44441,
				2,
				'bar',
				array()
			)
		);

		$id2 = $store->insertArticle(
			new EPArticle(
				null,
				33332,
				44441,
				3,
				'baz',
				array()
			)
		);

		$id3 = $store->insertArticle(
			new EPArticle(
				null,
				33331,
				44442,
				4,
				'bah',
				array()
			)
		);

		$this->assertHasArticlesMatchingConditions(
			array(),
			array( 667788 ),
			array( 667788 )
		);

		$this->assertHasArticlesMatchingConditions(
			array(),
			array( 33331, 33332 ),
			array( 667788 )
		);

		$this->assertHasArticlesMatchingConditions(
			array( $id3 ),
			array( 33331, 33332 ),
			array( 44442 )
		);

		$this->assertHasArticlesMatchingConditions(
			array( $id0, $id1, $id2, $id3 ),
			array( 33331, 33332 ),
			array( 44441, 44442 )
		);
	}

	protected function assertHasArticlesMatchingConditions( array $expectedIds, array $courseIds, array $userIds ) {
		$articles = $this->newStore()->getArticlesByCourseAndUsers( $courseIds, $userIds );

		$this->assertInternalType( 'array', $articles );
		$this->assertContainsOnlyInstancesOf( 'EducationProgram\EPArticle', $articles );
		$this->assertSameSize( $expectedIds, $articles );

		$actualIds = array();

		foreach ( $articles as $article ) {
			$actualIds[] = $article->getId();
		}

		$this->assertArrayEquals( $expectedIds, $actualIds );
	}

}
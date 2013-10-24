<?php

namespace EducationProgram\Tests;

use EducationProgram\ArticleAdder;
use EducationProgram\ArticleStore;
use EducationProgram\EPArticle;

/**
 * Tests for the EducationProgram\ArticleAdder class.
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
 * @since 0.4
 *
 * @ingroup EducationProgramTest
 *
 * @group EducationProgram
 * @group ArticleAdderTest
 * @group Database
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleAdderTest extends \MediaWikiTestCase {

	/**
	 * @return ArticleStore
	 */
	protected function newStore() {
		return new ArticleStore( 'ep_articles' );
	}

	/**
	 * @return ArticleAdder
	 */
	protected function newAdder() {
		$adder = new ArticleAdder( $this->newStore() );
		$adder->doLog = false;
		return $adder;
	}

	public function addArticleProvider() {
		$argLists = array();

		$argLists[] = array(
			new MockSuperUser(), // Action user
			10, // Course id
			20, // User id
			30, // Page id,
			'Page_title',
			array( 4, 5, 6 ) // Reviewer user ids
		);

		return $argLists;
	}

	/**
	 * @dataProvider addArticleProvider
	 *
	 * @param int $courseId
	 * @param int $userId
	 * @param int $pageId
	 * @param string $pageTitle
	 * @param int[] $reviewers
	 */
	public function testAddArticle( $actionUser, $courseId, $userId, $pageId, $pageTitle, array $reviewers ) {
		$adder = $this->newAdder();

		$success = $adder->addArticle( $actionUser, $courseId, $userId, $pageId, $pageTitle, $reviewers );
		$this->assertTrue( $success, 'addArticle should return true' );

		$hasArticle = $this->newStore()->hasArticleWith( $courseId, $userId, $pageId );
		$this->assertTrue( $hasArticle, 'the article is present after calling addArticle' );

		$success = $adder->addArticle( $actionUser, $courseId, $userId, $pageId, $pageTitle, $reviewers );
		$this->assertFalse( $success, 'addArticle should return false when called a second time' );
	}

}
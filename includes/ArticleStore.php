<?php

namespace EducationProgram;

use DatabaseBase;
use InvalidArgumentException;

/**
 * Store for EPArticle objects.
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
 * @file
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleStore {

	/**
	 * @since 0.3
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * @since 0.3
	 *
	 * @var int
	 */
	protected $readConnectionId;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param string $tableName
	 * @param int $readConnectionId
	 */
	public function __construct( $tableName, $readConnectionId = DB_SLAVE ) {
		$this->readConnectionId = $readConnectionId;
		$this->tableName = $tableName;
	}

	/**
	 * @since 0.3
	 *
	 * @param int|null $articleId
	 *
	 * @return boolean
	 */
	public function hasArticle( $articleId ) {
		if ( is_null( $articleId ) ) {
			return false;
		}

		return $this->getReadConnection()->selectRow(
			$this->tableName,
			array( 'article_id' ),
			array( 'article_id' => $articleId ),
			__METHOD__
		) !== false;
	}

	/**
	 * Attempt to isolate from MediaWiki global state access,
	 * really ought to have a connection provider injected.
	 *
	 * @since 0.3
	 *
	 * @return DatabaseBase
	 */
	protected function getReadConnection() {
		return wfGetDB( $this->readConnectionId );
	}

	/**
	 * Attempt to isolate from MediaWiki global state access,
	 * really ought to have a connection provider injected.
	 *
	 * @since 0.3
	 *
	 * @return DatabaseBase
	 */
	protected function getWriteConnection() {
		return wfGetDB( DB_MASTER );
	}

	/**
	 * Save the article in the store.
	 *
	 * @since 0.3
	 *
	 * @param EPArticle $article
	 *
	 * @return int|boolean The id for the inserted article or false on failure
	 * @throws InvalidArgumentException
	 */
	public function insertArticle( EPArticle $article ) {
		$dbw = $this->getWriteConnection();

		if ( $article->getId() !== null ) {
			throw new InvalidArgumentException( 'Cannot insert an article that already has an id' );
		}

		$success = $dbw->insert(
			$this->tableName,
			$this->getWriteFields( $article ),
			__METHOD__
		) !== false;

		return $success ? $dbw->insertId() : false;
	}

	/**
	 * Save the article in the store.
	 *
	 * @since 0.3
	 *
	 * @param EPArticle $article
	 *
	 * @return boolean
	 * @throws InvalidArgumentException
	 */
	public function updateArticle( EPArticle $article ) {
		if ( $article->getId() === null ) {
			throw new InvalidArgumentException( 'Cannot update an article that has no id' );
		}

		$success = $this->getWriteConnection()->update(
			$this->tableName,
			$this->getWriteFields( $article ),
			array( 'article_id' => $article->getId() ),
			__METHOD__
		) !== false;

		return $success;
	}

	/**
	 * @since 0.3
	 *
	 * @param EPArticle $article
	 *
	 * @return array
	 */
	protected function getWriteFields( EPArticle $article ) {
		return array(
			'article_course_id' => $article->getCourseId(),
			'article_user_id' => $article->getUserId(),
			'article_page_id' => $article->getPageId(),
			'article_page_title' => $article->getPageTitle(),
			'article_reviewers' => serialize( $article->getReviewers() ),
		);
	}

	/**
	 * Returns the article with provided id
	 * or null if there is no such article.
	 *
	 * @since 0.3
	 *
	 * @param int $articleId
	 *
	 * @return EPArticle|null
	 */
	public function getArticle( $articleId ) {
		$row = $this->getReadConnection()->selectRow(
			$this->tableName,
			$this->getReadFields(),
			array(
				 'article_id' => $articleId
			),
			__METHOD__
		);

		return $this->newArticleFromRow( $row );
	}

	/**
	 * Constructs and returns a new EPArticle given a result row.
	 *
	 * @since 0.3
	 *
	 * @param object $row
	 *
	 * @return EPArticle
	 */
	protected function newArticleFromRow( $row ) {
		return new EPArticle(
			(int)$row->article_id,
			(int)$row->article_course_id,
			(int)$row->article_user_id,
			(int)$row->article_page_id,
			$row->article_page_title,
			unserialize( $row->article_reviewers )
		);
	}

	/**
	 * @since 0.3
	 *
	 * @return string[]
	 */
	protected function getReadFields() {
		return array(
			'article_id',

			'article_course_id',
			'article_user_id',
			'article_page_id',
			'article_page_title',
			'article_reviewers',
		);
	}

	/**
	 * Deletes the article with the provided id if there is such an article.
	 *
	 * @since 0.3
	 *
	 * @param int $articleId
	 *
	 * @return boolean Success indicator
	 */
	public function deleteArticle( $articleId ) {
		return $this->getWriteConnection()->delete(
			$this->tableName,
			array(
				 'article_id' => $articleId
			),
			__METHOD__
		) !== false;
	}

	/**
	 * Returns if there is an article with provided data.
	 *
	 * @since 0.3
	 *
	 * @param int $courseId
	 * @param int $userId
	 * @param int $pageId
	 *
	 * @return boolean
	 */
	public function hasArticleWith( $courseId, $userId, $pageId ) {
		return $this->getReadConnection()->selectRow(
			$this->tableName,
			array( 'article_id' ),
			array(
				 'article_course_id' => $courseId,
				 'article_user_id' => $userId,
				 'article_page_id' => $pageId,
			),
			__METHOD__
		) !== false;
	}

	/**
	 * Deletes all articles that match the provided courses and users.
	 *
	 * @since 0.3
	 *
	 * @param int[]|int $courseIds
	 * @param int[]|int $userIds
	 *
	 * @return boolean
	 * @throws InvalidArgumentException
	 */
	public function deleteArticleByCourseAndUsers( $courseIds, $userIds ) {
		$courseIds = (array)$courseIds;
		$userIds = (array)$userIds;

		if ( empty( $courseIds ) || empty( $userIds ) ) {
			throw new InvalidArgumentException( '$courseIds and $userIds cannot be empty' );
		}

		return $this->getWriteConnection()->delete(
			$this->tableName,
			array(
				'article_course_id' => $courseIds,
				'article_user_id' => $userIds,
			),
			__METHOD__
		) !== false;
	}

	/**
	 * Returns all articles that match the provided courses and users.
	 *
	 * @since 0.3
	 *
	 * @param int[]|int $courseIds
	 * @param int[]|int $userIds
	 *
	 * @return EPArticle[]
	 * @throws InvalidArgumentException
	 */
	public function getArticlesByCourseAndUsers( $courseIds, $userIds ) {
		$courseIds = (array)$courseIds;
		$userIds = (array)$userIds;

		if ( empty( $courseIds ) || empty( $userIds ) ) {
			throw new InvalidArgumentException( '$courseIds and $userIds cannot be empty' );
		}

		$articleRows = $this->getReadConnection()->select(
			$this->tableName,
			$this->getReadFields(),
			array(
				 'article_course_id' => $courseIds,
				 'article_user_id' => $userIds,
			),
			__METHOD__
		);

		$articles = array();

		foreach ( $articleRows as $articleRow ) {
			$articles[] = $this->newArticleFromRow( $articleRow );
		}

		return $articles;
	}

}
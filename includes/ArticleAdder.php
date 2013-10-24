<?php

namespace EducationProgram;

/**
 * Use case for adding EPArticle's.
 *
 * TODO: when the logging gets factored out of EPArticle, we'll need to have a logger injected here.
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
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ArticleAdder {

	/**
	 * @since 0.3
	 *
	 * @var ArticleStore
	 */
	protected $articleStore;

	/**
	 * Constructor.
	 *
	 * @since 0.3
	 *
	 * @param ArticleStore $articleStore
	 */
	public function __construct( ArticleStore $articleStore ) {
		$this->articleStore = $articleStore;
	}

	/**
	 * Adds an EPArticle to the extension.
	 *
	 * @since 0.3
	 *
	 * @param User $actionUser The user performing this action
	 * @param int $courseId
	 * @param int $userId
	 * @param int $pageId
	 * @param string $pageTitle
	 * @param int[] $reviewers Ids of the users that are reviewers for this article
	 *
	 * @return boolean Indicates if the article was actually added. False means it already existed.
	 */
	public function addArticle( $actionUser, $courseId, $userId, $pageId, $pageTitle, $reviewers = array() ) {
		$articleExists = $this->articleStore->hasArticleWith(
			$courseId,
			$userId,
			$pageId
		);

		if ( $articleExists ) {
			return false;
		}

		$article = new EPArticle(
			null,
			$courseId,
			$userId,
			$pageId,
			$pageTitle,
			$reviewers
		);

		if ( $this->articleStore->insertArticle( $article ) && $this->doLog ) {
			$article->logAddition( $actionUser );
		}

		return true;
	}

	/**
	 * TODO: this is to disable logging during tests since we cannot easily
	 * stub out the needed course yet. Getting rid of this once logging code is fixed up.
	 * @deprecated
	 */
	public $doLog = true;

}
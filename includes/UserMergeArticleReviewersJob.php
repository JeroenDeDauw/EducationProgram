<?php

namespace EducationProgram;

use Job;
use Title;

/**
 * Job class for merging users in the article_reviewers column of the
 * ep_articles table.
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
 * @since 0.5.0 alpha
 *
 * @file
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Andrew Green <agreen@wikimedia.org>
 */
class UserMergeArticleReviewersJob extends Job {

	/**
	 * @see Job::__construct() for info on the parameters passed through.
	 */
	public function __construct( Title $title, $params ) {
		parent::__construct(
			'educationProgramUserMergeArticleReviewers',
			$title,
			$params
		);
	}

	/**
	 * @see Job::run()
	 */
	public function run() {
		$dbw = wfGetDB( DB_MASTER );
		$oldId = $this->params['oldUserId'];
		$newId = $this->params['newUserId'];

		// Some users who don't have a role in a course can still become
		// reviewers, so we have to go through _all_ article assignments
		// to merge users on the reviewers blob. ArticleStore doesn't have the
		// funcitonality we need, so we'll talk to the db directly.

		$articleRows = $dbw->select(
				'ep_articles',
				[ 'article_id', 'article_reviewers' ],
				[],
				__METHOD__
		);

		foreach ( $articleRows as $articleRow ) {
			$reviewerIds = unserialize( $articleRow->article_reviewers );

			if ( $this->mergeIds( $reviewerIds, $oldId, $newId ) ) {
				$dbw->update(
						'ep_articles',
						[ 'article_reviewers' => serialize( $reviewerIds ) ],
						[ 'article_id' => $articleRow->article_id ],
						__METHOD__
				);
			}
		}

		return true;
	}

	/**
	 * Usermerge in an array of ids. Returns true if there were changes.
	 *
	 * @param int[] &$ids
	 * @param int $oldId
	 * @param int $newId
	 *
	 * @return bool
	 */
	protected function mergeIds( array &$ids, $oldId, $newId ) {
		$i = array_search( $oldId, $ids );

		if ( $i !== false ) {
			$ids[$i] = $newId;
			$ids = array_unique( $ids );
			return true;
		}

		return false;
	}
}

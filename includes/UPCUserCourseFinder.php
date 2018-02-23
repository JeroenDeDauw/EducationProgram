<?php

namespace EducationProgram;

use Wikimedia\Rdbms\IDatabase;

/**
 * Implementation of the UserCourseFinder interface that works by doing
 * a SQL join between the ep_users_per_course and ep_courses tables.
 *
 * TODO: properly inject dependencies
 * - Profiler
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
class UPCUserCourseFinder implements UserCourseFinder {

	/**
	 * @var IDatabase
	 */
	private $db;

	/**
	 * @since 0.3
	 *
	 * @param IDatabase $db
	 */
	public function __construct( IDatabase $db ) {
		$this->db = $db;
	}

	/**
	 * @see UserCourseFinder::getCoursesForUsers
	 *
	 * @since 0.3
	 *
	 * @param int|int[] $userIds
	 * @param int|int[] $roles
	 *
	 * @return int[]
	 */
	public function getCoursesForUsers( $userIds, $roles = [] ) {
		$userIds = (array)$userIds;
		$roles = (array)$roles;

		$conditions = [];

		if ( !empty( $userIds ) ) {
			$conditions['upc_user_id'] = $userIds;
		}

		if ( !empty( $roles ) ) {
			$conditions['upc_role'] = $roles;
		}

		$upcRows = $this->db->select(
			[ 'ep_users_per_course', 'ep_courses' ],
			[ 'upc_course_id' ],
			array_merge(
				$conditions,
				Courses::getStatusConds( 'current', true ) // TODO: get as argument
			),
			__METHOD__,
			[ 'DISTINCT' ],
			[
				'ep_courses' => [ 'INNER JOIN', [ 'upc_course_id=course_id' ] ],
			]
		);

		$courseIds = [];

		foreach ( $upcRows as $upcRow ) {
			$courseIds[] = (int)$upcRow->upc_course_id;
		}

		return $courseIds;
	}

}

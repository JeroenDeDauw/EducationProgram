<?php

namespace EducationProgram;

/**
 * Interface for objects that can find the ids of the (currently active)
 * courses connected to the specified user(s).
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
interface UserCourseFinder {

	/**
	 * Returns the ids of the currently active courses the specified
	 * user is enrolled in.
	 *
	 * @since 0.3
	 *
	 * @param int|int[] $userIds
	 * @param int|int[] $roles
	 *
	 * @return int[]
	 */
	public function getCoursesForUsers( $userIds, $roles = array() );

}

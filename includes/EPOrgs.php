<?php

/**
 * Class representing the ep_orgs table.
 *
 * @since 0.1
 *
 * @file EPOrgs.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOrgs extends EPPageTable {

	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDBTable()
	 * @since 0.1
	 * @return string
	 */
	public function getDBTable() {
		return 'ep_orgs';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldPrefix()
	 * @since 0.1
	 * @return string
	 */
	public function getFieldPrefix() {
		return 'org_';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDataObjectClass()
	 * @since 0.1
	 * @return string
	 */
	public function getDataObjectClass() {
		return 'EPOrg';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getFieldTypes()
	 * @since 0.1
	 * @return array
	 */
	public function getFieldTypes() {
		return array(
			'id' => 'id',

			'name' => 'str',
			'city' => 'str',
			'country' => 'str',

			'active' => 'bool',
			'course_count' => 'int',
			'student_count' => 'int',
			'instructor_count' => 'int',
			'ca_count' => 'int',
			'oa_count' => 'int',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see DBTable::getDefaults()
	 * @since 0.1
	 * @return array
	 */
	public function getDefaults() {
		return array(
			'name' => '',
			'city' => '',
			'country' => '',

			'active' => false,
			'course_count' => 0,
			'student_count' => 0,
			'instructor_count' => 0,
			'ca_count' => 0,
			'oa_count' => 0,
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see DBTable::getSummaryFields()
	 * @since 0.1
	 * @return array
	 */
	public function getSummaryFields() {
		return array(
			'active',
			'course_count',
			'student_count',
			'instructor_count',
			'ca_count',
			'oa_count',
		);
	}


	/**
	 * Returns a list of orgs in an array that can be fed to select inputs.
	 *
	 * @since 0.1
	 *
	 * @param array|null $orgs
	 *
	 * @return array
	 */
	public function getOrgOptions( array /* EPOrg */ $orgs = null ) {
		$options = array();

		if ( is_null( $orgs ) ) {
			$orgs = $this->select( array( 'name', 'id' ) );
		}

		foreach ( $orgs as /* EPOrg */ $org ) {
			$options[$org->getField( 'name' )] = $org->getId();
		}

		return $options;
	}
	
}

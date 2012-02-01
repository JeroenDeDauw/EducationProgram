<?php

/**
 * Class representing a single campus ambassador.
 *
 * @since 0.1
 *
 * @file EPCA.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPCA extends EPDBObject implements EPIRole {

	/**
	 * Field for caching the linked user.
	 *
	 * @since 0.1
	 * @var User|false
	 */
	protected $user = false;

	/**
	 * Create a new instructor object from a user id.
	 * 
	 * @since 0.1
	 * 
	 * @param integer $userId
	 * 
	 * @return EPCA
	 */
	public static function newFromUserId( $userId ) {
		$data = array( 'user_id' => $userId );
		$ca = static::selectRow( null, $data );
		return $ca === false ? new static( $data, true ) : $ca;
	}
	
	/**
	 * Create a new instructor object from a User object.
	 * 
	 * @since 0.1
	 * 
	 * @param User $user
	 * 
	 * @return EPCA|false
	 */
	public static function newFromUser( User $user ) {
		return self::newFromUserId( $user->getId() );
	}
	
	/**
	 * @see parent::getFieldTypes
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	protected static function getFieldTypes() {
		return array(
			'id' => 'id',
			'user_id' => 'id',
		
			'bio' => 'str',
			'photo' => 'str',
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see EPDBObject::getDefaults()
	 */
	public static function getDefaults() {
		return array(
			'bio' => '',
			'photo' => '',
		);
	}
	
	/**
	 * Returns the user that this instructor is.
	 * 
	 * @since 0.1
	 * 
	 * @return User
	 */
	public function getUser() {
		if ( $this->user === false ) {
			$this->user = User::newFromId( $this->getField( 'user_id' ) );
		}
		
		return $this->user;
	}
	
	/**
	 * Returns the name of the instroctor, using their real name when available.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->getUser()->getRealName() === '' ? $this->getUser()->getName() : $this->getUser()->getRealName();
	}

	/**
	 * Display a pager with campus ambassadors.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $conditions
	 */
	public static function displayPager( IContextSource $context, array $conditions = array() ) {
		$pager = new EPOAPager( $context, $conditions );

		if ( $pager->getNumRows() ) {
			$context->getOutput()->addHTML(
				$pager->getFilterControl() .
					$pager->getNavigationBar() .
					$pager->getBody() .
					$pager->getNavigationBar() .
					$pager->getMultipleItemControl()
			);
		}
		else {
			$context->getOutput()->addHTML( $pager->getFilterControl( true ) );
			$context->getOutput()->addWikiMsg( 'ep-ca-noresults' );
		}
	}
	
	/**
	 * Returns the tool links for this ambassador.
	 * 
	 * @since 0.1
	 * 
	 * @param IContextSource $context
	 * @param EPCourse|null $course
	 * 
	 * @return string
	 */
	public function getToolLinks( IContextSource $context, EPCourse $course = null ) {
		return EPUtils::getRoleToolLinks( $this, $context, $course );
	}

	/**
	 * @since 0.1
	 * @see EPIRole::getRoleName
	 */
	public function getRoleName() {
		return 'campus';
	}
	
	/**
	 * Retruns the user link for this ambassador, using their real name when available.
	 * 
	 * @since 0.1
	 * 
	 * @return string
	 */
	public function getUserLink() {
		return Linker::userLink(
			$this->getUser()->getId(),
			$this->getUser()->getName(),
			$this->getName()
		);
	}
	
}

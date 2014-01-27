<?php

namespace EducationProgram;
use Page, IContextSource, IORMRow, Html;

/**
 * Action for viewing an org.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewOrgAction extends ViewAction {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 */
	public function __construct( Page $page, IContextSource $context = null ) {
		parent::__construct( $page, $context, Orgs::singleton() );

		// Only cache for anon users, to avoid potential confusion due to
		// cached versions
		$this->cacheEnabled = $this->cacheEnabled && $this->getUser()->isAnon();
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
			$this->getOutput()->addModules( 'ep.addcourse' );
		}

		return parent::onView();
	}

	/**
	 * @see Action::getName()
	 */
	public function getName() {
		return 'vieworg';
	}

	/**
	 * @see ViewAction::getPageHTML()
	 * @param IORMRow $org
	 * @return string
	 */
	public function getPageHTML( IORMRow $org ) {
		$html = parent::getPageHTML( $org );

		$html .= Html::element( 'h2', array(), $this->msg( 'ep-institution-courses' )->text() );

		$html .= CoursePager::getPager( $this->getContext(), array( 'org_id' => $org->getId() ) );
		$this->getOutput()->addModules( CoursePager::getModules() );

		if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
			$html .= Html::element( 'h2', array(), $this->msg( 'ep-institution-add-course' )->text() );
			$html .= Course::getAddNewControl( $this->getContext(), array( 'org' => $org->getId() ) );
		}

		return $html;
	}

	/**
	 * Gets the summary data.
	 *
	 * @since 0.1
	 *
	 * @param Org|IORMRow $org
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $org ) {
		$stats = array();

		$stats['name'] = $org->getField( 'name' );
		$stats['city'] = $org->getField( 'city' );

		$countries = \CountryNames::getNames( $this->getLanguage()->getCode() );
		$stats['country'] = $countries[$org->getField( 'country' )];

		$status_msg = $org->isActive() ?
			'ep-institution-active' : 'ep-institution-inactive';

		$stats['status'] = $this->msg( $status_msg )->escaped();

		$stats['courses'] = $this->getLanguage()->formatNum( $org->getField( 'course_count' ) );
		$stats['students'] = $this->getLanguage()->formatNum( $org->getField( 'student_count' ) );

		foreach ( $stats as &$stat ) {
			$stat = htmlspecialchars( $stat );
		}

		if ( $org->getField( 'course_count' ) > 0 ) {
			$stats['courses'] = \Linker::linkKnown(
				\SpecialPage::getTitleFor( 'Courses' ),
				$stats['courses'],
				array(),
				array( 'org_id' => $org->getId() )
			);
		}

		return $stats;
	}

	/**
	 * @see CachedAction::getCacheKey
	 * @return array
	 */
	protected function getCacheKey() {
		$user = $this->getUser();

		return array_merge( array(
			$user->isAllowed( 'ep-org' ),
			$user->isAllowed( 'ep-course' ),
			$user->isAllowed( 'ep-bulkdelcourses' ) && $user->getOption( 'ep_bulkdelcourses' ),
		), parent::getCacheKey() );
	}
}

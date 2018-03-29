<?php

namespace EducationProgram;

use Page;
use IContextSource;
use Html;

/**
 * Action for viewing an org.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class ViewOrgAction extends ViewAction {

	/**
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
	 *
	 * @param IORMRow $org
	 * @return string
	 */
	public function getPageHTML( IORMRow $org ) {
		$html = parent::getPageHTML( $org );

		$html .= Html::element( 'h2', [], $this->msg( 'ep-institution-courses' )->text() );

		$html .= CoursePager::getPager( $this->getContext(), [ 'org_id' => $org->getId() ] );
		$this->getOutput()->addModules( CoursePager::getModules() );

		if ( $this->getUser()->isAllowed( 'ep-course' ) ) {
			$html .= Html::element( 'h2', [], $this->msg( 'ep-institution-add-course' )->text() );
			$html .= Course::getAddNewControl( $this->getContext(), [ 'org' => $org->getId() ] );
		}

		return $html;
	}

	/**
	 * Gets the summary data.
	 *
	 * @param Org|IORMRow $org
	 *
	 * @return array
	 */
	protected function getSummaryData( IORMRow $org ) {
		$stats = [];

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
				[],
				[ 'org_id' => $org->getId() ]
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

		return array_merge( [
			$user->isAllowed( 'ep-org' ),
			$user->isAllowed( 'ep-course' ),
			$user->isAllowed( 'ep-bulkdelcourses' ) && $user->getOption( 'ep_bulkdelcourses' ),
		], parent::getCacheKey() );
	}
}

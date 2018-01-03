<?php

namespace EducationProgram;

/**
 * Abstract action class holding common EP functionality.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class Action extends \CachedAction {

	/**
	 * @see Action::$page
	 *
	 * @var EducationPage
	 */
	protected $page;

	/**
	 * Display a warning that the page has been deleted together with the first
	 * few items from its deletion log.
	 */
	public function displayDeletionLog() {
		$out = $this->getOutput();

		\LogEventsList::showLogExtract(
			$out,
			[ $this->page->getLogType() ],
			$this->getTitle(),
			'',
			[
				'lim' => 10,
				'conds' => [ 'log_action' => 'remove' ],
				'showIfEmpty' => false,
				'msgKey' => [ $this->prefixMsg( 'deleted' ) ]
			]
		);
	}

	/**
	 * Display an undeletion link if the user is allowed to undelete and
	 * if there are any previous revisions that can be used to undelete.
	 *
	 * Note: as of version 0.4 alpha, moving check for user rights out of here,
	 * to the callers.
	 */
	public function displayUndeletionLink() {
		$revisionCount = Revisions::singleton()->count( [
			'object_identifier' => $this->getTitle()->getText()
		] );

		if ( $revisionCount > 0 ) {
			$this->getOutput()->addHTML( $this->msg(
				$this->prefixMsg( 'undelete-revisions' ),
				\Message::rawParam( \Linker::linkKnown(
					$this->getTitle(),
					$this->msg( $this->prefixMsg( 'undelete-link' ) )
						->numParams( $revisionCount )->escaped(),
					[],
					[ 'action' => 'epundelete' ]
				) )
			)->text() );
		}
	}

	/**
	 * Returns a prefixed message name.
	 *
	 * @param string $name
	 *
	 * @return string
	 */
	protected function prefixMsg( $name ) {
		$class = str_replace( 'educationprogram\\', '', strtolower( get_class( $this->page ) ) );

		return $class . '-' . $this->getName() . '-' . $name;
	}

	/**
	 * Returns a salt based on the action and the page name.
	 *
	 * @return string
	 */
	protected function getSalt() {
		return get_class( $this->page ) . $this->getTitle()->getLocalURL();
	}

}

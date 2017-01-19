<?php

namespace EducationProgram;

use ContextSource;
use Title;
use WikiPage;
use Exception;
use Language;

/**
 * Abstract Page for interacting with a PageObject.
 *
 * Forced to implement a bunch of stuff that better should be in Page... :/
 *
 * TODO: refactor this away as per bug https://bugzilla.wikimedia.org/show_bug.cgi?id=43975
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EducationPage extends ContextSource implements \Page {

	/**
	 * Returns a list of actions this page can handle.
	 * Array keys are action names, their values are the names of the handling Action classes.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	abstract public function getActions();

	/**
	 * Returns an instance of the PageTable class for the PageObject being handled.
	 *
	 * @since 0.1
	 *
	 * @return PageTable
	 */
	abstract public function getTable();

	/**
	 * @since 0.1
	 * @var WikiPage
	 */
	protected $page;

	public function __construct( Title $title ) {
		$this->page = new WikiPage( $title );
	}

	/**
	 * Returns a new instance based on the namespace of the provided title,
	 * or throws an exception if the namespace is not handled.
	 *
	 * @since 0.1
	 *
	 * @param Title $title
	 *
	 * @return EducationPage
	 * @throws Exception
	 */
	public static function factory( Title $title ) {
		if ( $title->getNamespace() == EP_NS ) {
			$class = Utils::isCourse( $title ) ? 'EducationProgram\CoursePage' : 'EducationProgram\OrgPage';
			return new $class( $title );
		} else {
			throw new Exception( 'Namespace not handled by Page' );
		}
	}

	public function view() {

	}

	public function getPage() {
		return $this->page;
	}

	public function isRedirect() {
		return false;
	}

	public function getTitle() {
		return $this->page->getTitle();
	}

	public function getActionOverrides() {
		$actions = $this->getActions();

		foreach ( $GLOBALS['wgActions'] as $actionName => $actionValue ) {
			if ( !array_key_exists( $actionName, $actions ) ) {
				$actions[$actionName] = false;
			}
		}

		return $actions;
	}

	public function getTouched() {
		return '19700101000000';
	}

	public function getEditRight() {
		return static::$info['edit-right'];
	}

	/**
	 * Override if some degree if editing is available for some users.
	 *
	 * @since 0.4 alpha
	 */
	public function getLimitedEditRight() {
		return static::$info['edit-right'];
	}

	public function getListPage() {
		return static::$info['list'];
	}

	public function getLogType() {
		return static::$info['log-type'];
	}

	public function loadPageData( $from = 'fromdb' ) {
	}

	public function exists() {
		return $this->getTitle()->exists();
	}

}

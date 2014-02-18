<?php

namespace EducationProgram;
use IContextSource, Title, WikiPage, MWException, Language;

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
abstract class EducationPage implements \Page, IContextSource {

	/**
	 * Returns a list of actions this page can handle.
	 * Array keys are action names, their values are the names of the handling Action classes.
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public abstract function getActions();

	/**
	 * Returns an instance of the PageTable class for the PageObject being handled.
	 *
	 * @since 0.1
	 *
	 * @return PageTable
	 */
	public abstract function getTable();

	/**
	 * @since 0.1
	 * @var IContextSource
	 */
	protected $context;

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
	 * @throws MWException
	 */
	public static function factory( Title $title ) {
		if ( $title->getNamespace() == EP_NS ) {
			$class = Utils::isCourse( $title ) ? 'EducationProgram\CoursePage' : 'EducationProgram\OrgPage';
			return new $class( $title );
		}
		else {
			throw new MWException( 'Namespace not handled by Page' );
		}
	}

	public function view() {

	}

	public function setContext( IContextSource $context ) {
		$this->context = $context;
	}

	public function getContext() {
		return $this->context;
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

	public function getRequest() {
		return $this->getContext()->getRequest();
	}

	public function canUseWikiPage() {
		return $this->getContext()->canUseWikiPage();
	}

	public function getWikiPage() {
		return $this->getContext()->getWikiPage();
	}

	public function getOutput() {
		return $this->getContext()->getOutput();
	}

	public function getUser() {
		return $this->getContext()->getUser();
	}

	public function getLanguage() {
		return $this->getContext()->getLanguage();
	}

	public function getSkin() {
		return $this->getContext()->getSkin();
	}

	public function getConfig() {
		return $this->getContext()->getConfig();
	}

	public function exportSession() {
		return $this->getContext()->exportSession();
	}

	public function msg( /* $args */ ) {
		$args = func_get_args();
		return call_user_func_array( array( $this->getContext(), 'msg' ), $args );
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

	/**
	 * @deprecated
	 * @return Language
	 */
	public function getLang() {
		wfDeprecated( __METHOD__, '1.19' );
		return $this->getLanguage();
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

	public function loadPageData( $from = 'fromdb' ) {}

	public function exists() {
		return $this->getTitle()->exists();
	}

}

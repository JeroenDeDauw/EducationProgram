<?php

/**
 * Abstract Page for interacting with a EPPageObject.
 * 
 * Forced to implement a bunch of stuff that better should be in Page... :/
 *
 * @since 0.1
 *
 * @file EPPage.php
 * @ingroup EducationProgram
 * @ingroup Page
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPPage extends Page implements IContextSource {

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
	 * Returns an instance of the EPPageTable class for the EPPageObject being handled.
	 *
	 * @since 0.1
	 *
	 * @return EPPageTable
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
	 * @return EPPage
	 * @throws MWException
	 */
	public static function factory( Title $title ) {
		switch ( $title->getNamespace() ) {
			case EP_NS_COURSE:
				return new CoursePage( $title );
				break;
			case EP_NS_INSTITUTION:
				return new OrgPage( $title );
				break;
			default:
				throw new MWException( 'Namespace not handled by EPPage' );
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

	public function getListPage() {
		return static::$info['list'];
	}
	
	public function getLogType() {
		return static::$info['log-type'];
	}
	
	public static function displayDeletionLog( IContextSource $context, $messageKey ) {
		$out = $context->getOutput();

		LogEventsList::showLogExtract(
			$out,
			array( static::$info['log-type'] ),
			$context->getTitle(),
			'',
			array(
				'lim' => 10,
				'conds' => array( 'log_action' => 'remove' ),
				'showIfEmpty' => false,
				'msgKey' => array( $messageKey )
			)
		);
	}
	
}

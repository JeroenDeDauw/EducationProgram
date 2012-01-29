<?php

abstract class EPPage extends Page implements IContextSource {

	protected $context;
	protected $page;
	
	public function __construct( Title $title ) {
		$this->page = new WikiPage( $title );
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
	
	protected abstract function getActions();
	
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
	
	public function getLang() {
		wfDeprecated( __METHOD__, '1.19' );
		return $this->getLanguage();
	}
	
}

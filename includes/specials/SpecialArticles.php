<?php

/**
 * Special page that lists articles worked on as part of the Education Program.
 *
 * @since 0.1
 *
 * @file SpecialArticles.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class SpecialArticles extends SpecialEPPage {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 */
	public function __construct() {
		parent::__construct( 'Articles' );
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$this->displayNavigation();

		$this->startCache( 3600 );
		$this->addCachedHTML( array( $this, 'getPagerHTML' ), $this->getContext() );
	}

	/**
	 * @since 0.2
	 *
	 * @return string
	 */
	public function getPagerHTML() {
		return EPArticle::getPagerHTML( $this->getContext() );
	}

	/**
	 * @see SpecialCachedPage::getCacheKey
	 *
	 * @since 0.2
	 *
	 * @return array
	 */
	protected function getCacheKey() {
		return array_merge( $this->getRequest()->getValues(), parent::getCacheKey() );
	}
}

<?php

/**
 * Abstract special page class with scaffolding for caching the HTML output.
 *
 * @since 0.1
 *
 * @file SpecialCachedPage.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SpecialCachedPage extends SpecialPage {

	/**
	 * The time to live for the cache, in seconds.
	 *
	 * @since 0.1
	 * @var integer
	 */
	protected $cacheExpiry = 3600;

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		parent::execute( $subPage );

		$cache = wfGetCache( CACHE_ANYTHING );
		$cacheKey = $this->getCacheKey();
		$cachedHTML = $cache->get( $cacheKey );

		$out = $this->getOutput();

		if ( $this->getRequest()->getText( 'action' ) !== 'purge' && is_string( $cachedHTML ) ) {
			$html = $cachedHTML;
		}
		else {
			$this->displayCachedContent();

			$html = $out->getHTML();
			$cache->set( $cacheKey, $html, $this->cacheExpiry );
		}

		$out->clearHTML();

		$this->displayBeforeCached();
		$out->addHTML( $html );
		$this->displayAfterCached();
	}

	/**
	 * Sets the time to live for the cache, in seconds.
	 *
	 * @since 0.1
	 *
	 * @param integer $cacheExpiry
	 */
	protected function setExpirey( $cacheExpiry ) {
		$this->cacheExpiry = $cacheExpiry;
	}

	/**
	 * Returns the cache key to use to cache this page's HTML output.
	 * Is constructed from the special page name and language code.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getCacheKey() {
		return wfMemcKey( $this->mName, $this->getLanguage()->getCode() );
	}

	/**
	 * Display the cached content. Everything added to the output here
	 * will be cached.
	 *
	 * @since 0.1
	 */
	protected function displayCachedContent() {

	}

	/**
	 * Display non-cached content that will be added to the final output
	 * before the cached HTML.
	 *
	 * @since 0.1
	 */
	protected function displayBeforeCached() {

	}

	/**
	 * Display non-cached content that will be added to the final output
	 * after the cached HTML.
	 *
	 * @since 0.1
	 */
	protected function displayAfterCached() {

	}

}

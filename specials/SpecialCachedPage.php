<?php

class CachedOutput {

	/**
	 * The time to live for the cache, in seconds or a unix timestamp indicating the point of expiry.
	 *
	 * @since 0.1
	 * @var integer
	 */
	protected $cacheExpiry = 3600;

	protected $cachedChunks;

	protected $hasCached = null;

	protected $out;

	public function __construct( OutputPage $out ) {
		$this->out = $out;
	}

	public function invalidateCache() {
		$this->hasCached = false;
	}

	/**
	 * Initializes the caching.
	 * Should be called ONCE before any of the caching functionality is used,
	 * only when $this->hasCached is null.
	 *
	 * @since 0.1
	 */
	protected function initCaching() {
		$cachedChunks = wfGetCache( CACHE_ANYTHING )->get( $this->getCacheKey() );

		$this->hasCached = is_array( $cachedChunks );
		$this->cachedChunks = $this->hasCached ? $cachedChunks : array();
	}

	/**
	 * Add some HTML to be cached.
	 * This is done by providing a callback function that should
	 * return the HTML to be added. It will only be called if the
	 * item is not in the cache yet or when the cache has been invalidated.
	 *
	 * @since 0.1
	 *
	 * @param {function} $callback
	 * @param array $args
	 * @param string|null $key
	 */
	public function addCachedHTML( $callback, $args = array(), $key = null ) {
		if ( is_null( $this->hasCached ) ) {
			$this->initCaching();
		}

		if ( $this->hasCached ) {
			$html = '';

			if ( is_null( $key ) ) {
				$itemKey = array_flip( array_slice( $this->cachedChunks, 0, 1 ) );
				$itemKey = array_shift( $itemKey );

				if ( is_integer( $itemKey ) ) {
					wfWarn( "Attempted to get item with non-numeric key while the next item in the queue has a key ($itemKey) in " . __METHOD__ );
				}
				elseif ( is_null( $itemKey ) ) {
					wfWarn( "Attempted to get an item while the queue is empty in " . __METHOD__ );
				}
				else {
					$html = array_shift( $key );
				}
			}
			else {
				if ( array_key_exists( $key, $this->cachedChunks ) ) {
					$html = $this->cachedChunks[$key];
					unset( $this->cachedChunks[$key] );
				}
				else {
					wfWarn( "There is no item with key '$key' in this->cachedChunks in " . __METHOD__ );
				}
			}
		}
		else {
			$html = call_user_func_array( $callback, $args );

			if ( is_null( $key ) ) {
				$this->cachedChunks[] = $html;
			}
			else {
				$this->cachedChunks[$key] = $html;
			}
		}

		$this->addHTML( $html );
	}

	public function addHTML( $html ) {
		$this->out->addHTML( $html );
	}

	/**
	 * Saves the HTML to the cache in case it got recomputed.
	 * Should be called after the last time anything is added via addCachedHTML.
	 *
	 * @since 0.1
	 */
	public function saveCache() {
		if ( !$this->hasCached && !empty( $this->cachedChunks ) ) {
			wfGetCache( CACHE_ANYTHING )->set( $this->getCacheKey(), $this->cachedChunks, $this->cacheExpiry );
		}
	}

	/**
	 * Sets the time to live for the cache, in seconds or a unix timestamp indicating the point of expiry..
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

}

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
	 * The time to live for the cache, in seconds or a unix timestamp indicating the point of expiry.
	 *
	 * @since 0.1
	 * @var integer|null
	 */
	protected $cacheExpiry = null;

	protected $cachedOutput = null;

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 */
	public function execute( $subPage ) {
		//parent::execute( $subPage );

		if ( !is_null( $this->cacheExpiry ) ) {
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
	}

	/**
	 * Sets the time to live for the cache, in seconds or a unix timestamp indicating the point of expiry..
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

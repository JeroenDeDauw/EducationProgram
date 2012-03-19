<?php

/**
 * Abstract special page class with scaffolding for caching the HTML output.
 * This copy is kept for compatibility with MW < 1.20.
 * As of 1.20, this class can be found at includes/specials/SpecialCachedPage.php
 *
 * TODO: uncomment when done w/ dev (double declaration makes PhpStorm mad :)
 *
 * @since 0.1
 *
 * @file SpecialCachedPage.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
//abstract class SpecialCachedPage extends SpecialPage {
//
//	/**
//	 * The time to live for the cache, in seconds or a unix timestamp indicating the point of expiry.
//	 *
//	 * @since 0.1
//	 * @var integer|null
//	 */
//	protected $cacheExpiry = null;
//
//	/**
//	 * List of HTML chunks to be cached (if !hasCached) or that where cashed (of hasCached).
//	 * If no cached already, then the newly computed chunks are added here,
//	 * if it as cached already, chunks are removed from this list as they are needed.
//	 *
//	 * @since 0.1
//	 * @var array
//	 */
//	protected $cachedChunks;
//
//	/**
//	 * Indicates if the to be cached content was already cached.
//	 * Null if this information is not available yet.
//	 *
//	 * @since 0.1
//	 * @var boolean|null
//	 */
//	protected $hasCached = null;
//
//	/**
//	 * Main method.
//	 *
//	 * @since 0.1
//	 *
//	 * @param string|null $subPage
//	 */
//	public function execute( $subPage ) {
//		if ( $this->getRequest()->getText( 'action' ) === 'purge' ) {
//			$this->hasCached = false;
//		}
//
//		if ( !is_null( $this->cacheExpiry ) ) {
//			$this->initCaching();
//
//			if ( $this->hasCached === true ) {
//				$this->getOutput()->setSubtitle( $this->getCachedNotice( $subPage ) );
//			}
//		}
//	}
//
//	/**
//	 * Returns a message that notifies the user he/she is looking at
//	 * a cached version of the page, including a refresh link.
//	 *
//	 * @since 0.1
//	 *
//	 * @param string|null $subPage
//	 *
//	 * @return string
//	 */
//	protected function getCachedNotice( $subPage ) {
//		$refreshArgs = $this->getRequest()->getQueryValues();
//		unset( $refreshArgs['title'] );
//		$refreshArgs['action'] = 'purge';
//
//		$refreshLink = Linker::link(
//			$this->getTitle( $subPage ),
//			$this->msg( 'cachedspecial-refresh-now' )->escaped(),
//			array(),
//			$refreshArgs
//		);
//
//		if ( $this->cacheExpiry < 86400 * 3650 ) {
//			$message = $this->msg(
//				'cachedspecial-viewing-cached-ttl',
//				$this->getDurationText( $this->cacheExpiry )
//			)->escaped();
//		}
//		else {
//			$message = $this->msg(
//				'cachedspecial-viewing-cached-ts'
//			)->escaped();
//		}
//
//		return $message . ' ' . $refreshLink;
//	}
//
//	/**
//	 * Returns a message with the time to live of the cache.
//	 * Takes care of compatibility with MW < 1.20, in which Language::formatDuration was introduced.
//	 *
//	 * @since 0.1
//	 *
//	 * @param integer $seconds
//	 * @param array $chosenIntervals
//	 *
//	 * @return string
//	 */
//	protected function getDurationText( $seconds, array $chosenIntervals = array( 'years', 'days', 'hours', 'minutes', 'seconds' ) ) {
//		if ( method_exists( $this->getLanguage(), 'formatDuration' ) ) {
//			return $this->getLanguage()->formatDuration( $seconds, $chosenIntervals );
//		}
//		else {
//			$intervals = array(
//				'years' => 31557600, // 86400 * 365.25
//				'weeks' => 604800,
//				'days' => 86400,
//				'hours' => 3600,
//				'minutes' => 60,
//				'seconds' => 1,
//			);
//
//			if ( !empty( $chosenIntervals ) ) {
//				$intervals = array_intersect_key( $intervals, array_flip( $chosenIntervals ) );
//			}
//
//			$segments = array();
//
//			foreach ( $intervals as $name => $length ) {
//				$value = floor( $seconds / $length );
//
//				if ( $value > 0 || ( $name == 'seconds' && empty( $segments ) ) ) {
//					$seconds -= $value * $length;
//					$segments[] = $this->msg( 'duration-' . $name, array( $value ) )->escaped();
//				}
//			}
//
//			return $this->getLanguage()->listToText( $segments );
//		}
//	}
//
//	/**
//	 * Initializes the caching if not already done so.
//	 * Should be called before any of the caching functionality is used.
//	 *
//	 * @since 0.1
//	 */
//	protected function initCaching() {
//		if ( is_null( $this->hasCached ) ) {
//			$cachedChunks = wfGetCache( CACHE_ANYTHING )->get( $this->getCacheKey() );
//
//			$this->hasCached = is_array( $cachedChunks );
//			$this->cachedChunks = $this->hasCached ? $cachedChunks : array();
//		}
//	}
//
//	/**
//	 * Add some HTML to be cached.
//	 * This is done by providing a callback function that should
//	 * return the HTML to be added. It will only be called if the
//	 * item is not in the cache yet or when the cache has been invalidated.
//	 *
//	 * @since 0.1
//	 *
//	 * @param {function} $callback
//	 * @param array $args
//	 * @param string|null $key
//	 */
//	public function addCachedHTML( $callback, $args = array(), $key = null ) {
//		$this->initCaching();
//
//		if ( $this->hasCached ) {
//			$html = '';
//
//			if ( is_null( $key ) ) {
//				$itemKey = array_keys( array_slice( $this->cachedChunks, 0, 1 ) );
//				$itemKey = array_shift( $itemKey );
//
//				if ( !is_integer( $itemKey ) ) {
//					wfWarn( "Attempted to get item with non-numeric key while the next item in the queue has a key ($itemKey) in " . __METHOD__ );
//				}
//				elseif ( is_null( $itemKey ) ) {
//					wfWarn( "Attempted to get an item while the queue is empty in " . __METHOD__ );
//				}
//				else {
//					$html = array_shift( $this->cachedChunks );
//				}
//			}
//			else {
//				if ( array_key_exists( $key, $this->cachedChunks ) ) {
//					$html = $this->cachedChunks[$key];
//					unset( $this->cachedChunks[$key] );
//				}
//				else {
//					wfWarn( "There is no item with key '$key' in this->cachedChunks in " . __METHOD__ );
//				}
//			}
//		}
//		else {
//			$html = call_user_func_array( $callback, $args );
//
//			if ( is_null( $key ) ) {
//				$this->cachedChunks[] = $html;
//			}
//			else {
//				$this->cachedChunks[$key] = $html;
//			}
//		}
//
//		$this->getOutput()->addHTML( $html );
//	}
//
//	/**
//	 * Saves the HTML to the cache in case it got recomputed.
//	 * Should be called after the last time anything is added via addCachedHTML.
//	 *
//	 * @since 0.1
//	 */
//	public function saveCache() {
//		if ( $this->hasCached === false && !empty( $this->cachedChunks ) ) {
//			wfGetCache( CACHE_ANYTHING )->set( $this->getCacheKey(), $this->cachedChunks, $this->cacheExpiry );
//		}
//	}
//
//	/**
//	 * Sets the time to live for the cache, in seconds or a unix timestamp indicating the point of expiry..
//	 *
//	 * @since 0.1
//	 *
//	 * @param integer $cacheExpiry
//	 */
//	protected function setExpirey( $cacheExpiry ) {
//		$this->cacheExpiry = $cacheExpiry;
//	}
//
//	/**
//	 * Returns the cache key to use to cache this page's HTML output.
//	 * Is constructed from the special page name and language code.
//	 *
//	 * @since 0.1
//	 *
//	 * @return string
//	 */
//	protected function getCacheKey() {
//		return wfMemcKey( $this->mName, $this->getLanguage()->getCode() );
//	}
//
//}

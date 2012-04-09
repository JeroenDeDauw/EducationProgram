<?php

/**
 * Education Program timeline.
 *
 * @since 0.1
 *
 * @file EPTimeline.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPTimeline extends ContextSource {

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param IContextSource $context
	 * @param array $events
	 */
	public function __construct( IContextSource $context, array /* of EPEvent */ $events ) {
		$this->setContext( $context );
	}


	/**
	 * Builds and returns the HTML for the timeline.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {

		return '';
	}

	/**
	 * Displays the timeline.
	 *
	 * @since 0.1
	 */
	public function display() {
		$this->getOutput()->addHTML( $this->getHTML() );
	}

}

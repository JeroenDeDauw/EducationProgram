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
	 * List of events to display in this timeline.
	 *
	 * @since 0.1
	 * @var array
	 */
	protected $events;

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
		$this->events = $events;
	}


	/**
	 * Builds and returns the HTML for the timeline.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getHTML() {
		$segments = array();

		foreach ( $this->events as /* EPEvent */ $event ) {
			$segments[] = $event->getEventDisplay()->getHTML();
		}

		return implode( '<br />', $segments ); // TODO
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

<?php

namespace EducationProgram;

/**
 * Notification formatter for course-related events. Extends
 * EchoEditFormatter to support the custom 'short-title-text' message parameter.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
class CourseFormatter extends \EchoEditFormatter {

	/**
	 * @param $event EchoEvent
	 * @param $param string
	 * @param $message Message
	 * @param $user User
	 */
	protected function processParam( $event, $param, $message, $user ) {
		if ( $param === 'short-title-text' ) {

			$title = $event->getTitle();

			// FIXME Temporary measure for deleted pages, improve once Echo
			// provides revision information for such cases.
			if ( !( $title instanceof \Title ) ) {

				$message->params( '' );

			} else {

				// TODO Here we're adding yet another bit of unencapsulated code
				// that depends on the standard org/course (term) format.
				// Other patches currently in the pipeline face the same issue.
				// (See https://gerrit.wikimedia.org/r/#/c/98183/6/includes/rows/Course.php)
				// Once they're through we can consider a general solution.
				$fullTitle = $title->getText();
				$titleParts = explode( '/', $fullTitle, 2 );
				$message->params( $titleParts[1] );
			}

		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}
};
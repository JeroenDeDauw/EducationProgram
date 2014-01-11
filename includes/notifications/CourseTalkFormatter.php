<?php

namespace EducationProgram;

/**
 * Notification formatter for edits to a course talk page. Extends
 * EchoEditFormatter to support the custom 'short-title-text' message parameter.
 *
 * @since 0.4 alpha
 *
 * @ingroup EducationProgram
 *
 * @license GNU GPL v2+
 * @author Andrew Green < agreen at wikimedia dot org >
 */
class CourseTalkFormatter extends \EchoEditFormatter {

	/**
	 * @param $event EchoEvent
	 * @param $param string
	 * @param $message Message
	 * @param $user User
	 */
	protected function processParam( $event, $param, $message, $user ) {
		if ( $param === 'short-title-text' ) {

			// TODO Here we're adding yet another bit of unencapsulated code
			// that depends on the standard org/course (term) format.
			// Other patches currently in the pipeline face the same issue.
			// (See https://gerrit.wikimedia.org/r/#/c/98183/6/includes/rows/Course.php)
			// Once they're through we can consider a general solution.
			$fullTitle = $event->getTitle()->getText();
			$titleParts = explode( '/', $fullTitle, 2 );
			$message->params( $titleParts[1] );

		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}
};
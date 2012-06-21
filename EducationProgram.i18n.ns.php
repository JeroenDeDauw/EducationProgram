<?php

/**
 * Namespace internationalization for the Education Program extension.
 *
 * @since 0.1
 *
 * @file EducationProgram.i18n.ns.php
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */

if ( !defined( 'EP_NS_COURSE' ) ) {
	define( 'EP_NS_COURSE',				442 + 0 );
	define( 'EP_NS_COURSE_TALK', 		442 + 1 );
	define( 'EP_NS_INSTITUTION', 		442 + 2 );
	define( 'EP_NS_INSTITUTION_TALK', 	442 + 3 );
}

$namespaceNames = array();

$namespaceNames['en'] = array(
	EP_NS_COURSE		  	=> 'Course',
	EP_NS_COURSE_TALK  		=> 'Course_talk',
	EP_NS_INSTITUTION		=> 'Institution',
	EP_NS_INSTITUTION_TALK  => 'Institution_talk',
);

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

}
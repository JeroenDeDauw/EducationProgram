<?php

/**
 * Abstract action for deleting EPPageObject items.
 *
 * @since 0.1
 *
 * @file EPDeleteAction.php
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @licence GNU GPL v3+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EPDeleteAction extends FormlessAction {

	/**
	 * @since 0.1
	 * @var DBTable
	 */
	protected $table;

	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @param Page $page
	 * @param IContextSource $context
	 * @param DBTable $table
	 */
	protected function __construct( Page $page, IContextSource $context = null, DBTable $table ) {
		$this->table = $table;
		parent::__construct( $page, $context );
	}

	/**
	 * (non-PHPdoc)
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return $this->msg( 'backlinksubtitle' )->rawParams( Linker::link( $this->getTitle() ) );
	}

	/**
	 * Do something exciting on successful processing of the form.  This might be to show
	 * a confirmation message (watch, rollback, etc) or to redirect somewhere else (edit,
	 * protect, etc).
	 */
	public function onSuccess() {
		$title = SpecialPage::getTitleFor( $this->table->getListPage() );
		$this->getOutput()->getRedirect( $title->getLocalURL( array( 'deleted' => $this->getTitle()->getText() ) ) );
	}

	/**
	 * (non-PHPdoc)
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		$this->getOutput()->setPageTitle( $this->getPageTitle() );

		$object = $this->table->get( $this->getTitle()->getText() );

		if ( $object === false ) {
			// TODO
			throw new ErrorPageError( $this->getTitle(), $this->prefixMsg( 'none' ) );
		}
		else {
			$this->displayForm( $object );
		}

		return '';
	}

	protected function displayForm( EPPageObject $object ) {
		$out = $this->getOutput();

		$out->addWikiMsg( $this->prefixMsg( 'text' ), $object->getField( 'name' ) );

		$out->addHTML( Html::openElement(
			'form',
			array(
				'method' => 'post',
				'action' => $this->getTitle()->getLocalURL(),
			)
		) );

		$out->addHTML( '&#160;' . Xml::inputLabel(
			wfMsg( $this->prefixMsg( 'summary' ) ),
			'summary',
			'summary',
			65,
			false,
			array(
				'maxlength' => 250,
				'spellcheck' => true,
			)
		) );

		$out->addHTML( '<br />' );

		$out->addHTML( Html::input(
			'delete',
			wfMsg( $this->prefixMsg( 'delete-button' ) ),
			'submit',
			array(
				'class' => 'ep-disenroll',
			)
		) );

		$out->addElement(
			'button',
			array(
				'class' => 'ep-delete-cancel',
				'target-url' => $this->getTitle()->getLocalURL(),
			),
			wfMsg( $this->prefixMsg( 'cancel-button' ) )
		);

		$out->addHTML( Html::hidden( 'deleteToken', $this->getUser()->getEditToken( 'delete' . $this->getTitle()->getLocalURL() ) ) );

		$out->addHTML( '</form>' );
	}

	protected function prefixMsg( $name ) {
		return strtolower( get_called_class() ) . '-' . $name;
	}

	/**
	 * Returns the page title.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		return wfMsgExt(
			$this->prefixMsg( 'title' ),
			'parsemag',
			$this->getTitle()->getText()
		);
	}

}
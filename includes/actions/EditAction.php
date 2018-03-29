<?php

namespace EducationProgram;

use Page;
use IContextSource;
use HTMLForm;
use Title;
use Message;

/**
 * Abstract action for editing PageObject items.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 * @ingroup Action
 *
 * @license GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class EditAction extends Action {

	/**
	 * Instance of the object being edited or created.
	 *
	 * @var PageObject|bool false
	 */
	protected $item = false;

	/**
	 * If the action is in insert mode rather then edit mode.
	 *
	 * @var bool|null
	 */
	protected $isNew = null;

	/**
	 * @var PageTable
	 */
	protected $table;

	/**
	 * @param Page $page
	 * @param IContextSource $context
	 * @param PageTable|IORMTable $table
	 */
	public function __construct( Page $page, IContextSource $context = null, PageTable $table ) {
		$this->table = $table;
		parent::__construct( $page, $context );
	}

	public function doesWrites() {
		return true;
	}

	/**
	 * @see FormlessAction::onView()
	 */
	public function onView() {
		Utils::displayResult( $this->getContext() );

		$this->getOutput()->addModules( 'ep.formpage' );

		if ( $this->getRequest()->wasPosted() && $this->getUser()->matchEditToken(
			$this->getRequest()->getVal( 'wpEditToken' ) )
		) {
			$this->showForm();
		} else {
			$this->showContent();
		}

		return '';
	}

	/**
	 * Returns the page title.
	 *
	 * @return string
	 */
	protected function getPageTitle() {
		$action = $this->isNew() ? 'add' : 'edit';
		return $this->msg(
			$this->prefixMsg( 'title-' . $action ),
			$this->getTitle()->getText()
		)->text();
	}

	/**
	 * Returns the base string for generating message keys.
	 *
	 * Used by getDescription() and showContent()
	 *
	 * @return string
	 */
	abstract protected function getMessageKeyBase();

	/**
	 * @see Action::getDescription()
	 */
	protected function getDescription() {
		return $this->msg( $this->getMessageKeyBase() )->escaped();
	}

	/**
	 * Display the form and set the item field, or redirect the user.
	 */
	protected function showContent() {
		$out = $this->getOutput();

		$data = $this->getNewData();

		$object = $this->table->selectRow( null, $data );

		if ( $object !== false && $this->getRequest()->getText( 'redlink' ) === '1' ) {
			$out->redirect( $this->getTitle()->getLocalURL() );
		} else {
			if ( $object === false ) {
				// Note: this fragment of code is only executed by
				// EditOrgAction, that is, when the user tries to edit an
				// institution. The conditions for displaying an undeletion
				// link for courses are checked in EditCourseAction::onView().
				if ( $this->getUser()->isAllowed( $this->page->getEditRight() ) ) {
					$this->displayUndeletionLink();
				}

				$this->displayDeletionLog();

				$this->isNew = true;
				$object = $this->table->newRow( $data, true );
			} elseif ( $this->isNewPost() ) {
				// Give grep a chance to find the usages:
				// ep-editorg-exists-already, ep-editcourse-exists-already
				$this->showWarning( $this->msg( $this->getMessageKeyBase() . '-exists-already' ) );
			}

			$out->setPageTitle( $this->getPageTitle() );
			$out->setSubtitle( $this->getDescription() );

			$this->item = $object;
			$this->showForm();
		}
	}

	/**
	 * Show a message in a warning box.
	 *
	 * @param Message $message
	 */
	protected function showWarning( Message $message ) {
		$this->getOutput()->addHTML(
			'<p class="visualClear warningbox">' . $message->parse() . '</p>'
			. '<hr style="display: block; clear: both; visibility: hidden;" />'
		);
	}

	/**
	 * Returns if the page should work in insertion mode rather then modification mode.
	 *
	 * @return bool
	 */
	protected function isNew() {
		if ( is_null( $this->isNew ) ) {
			$this->isNew = $this->isNewPost();
		}

		return $this->isNew;
	}

	protected function isNewPost() {
		return $this->getRequest()->wasPosted() &&
			( $this->getRequest()->getCheck( 'isnew' ) ||
				$this->getRequest()->getCheck( 'wpisnew' ) );
	}

	/**
	 * Show the form.
	 */
	protected function showForm() {
		$form = $this->getForm();

		if ( $this->getRequest()->wasPosted() && $this->getRequest()->getCheck( 'isnew' ) ) {
			$form->prepareForm();
			$form->displayForm( \Status::newGood() );
		} else {
			if ( $form->show() ) {
				$this->onSuccess();
			}
		}
	}

	/**
	 * Returns the data to use as condition for selecting the object,
	 * or in case nothing matches the selection, the data to initialize
	 * it with. This is typically an identifier such as name or id.
	 *
	 * @return array
	 */
	protected function getNewData() {
		$data = [];

		if ( $this->isNewPost() ) {
			$data['name'] = $this->getRequest()->getVal( 'newname' );
		} else {
			$data['name'] = $this->getTitle()->getText();
		}

		$data['name'] = $this->getLanguage()->ucfirst( $data['name'] );

		return $data;
	}

	/**
	 * Get the query conditions to obtain the item based on the page title.
	 *
	 * @return array
	 */
	protected function getTitleConditions() {
		return [ $this->getTitleField() => $this->getTitle()->getText() ];
	}

	/**
	 * Returns the name of the field holding the title of the PageObject.
	 *
	 * @since 0.2
	 *
	 * @return string
	 */
	abstract protected function getTitleField();

	/**
	 * @see FormSpecialPage::getForm()
	 *
	 * @return HTMLForm
	 */
	protected function getForm() {
		$fields = $this->getFormFields();

		if ( $this->isNew() ) {
			$fields['isnew'] = [
				'type' => 'hidden',
				'default' => 1,
			];
		}

		if ( $this->getRequest()->getCheck( 'wpreturnto' ) ) {
			$fields['returnto'] = [
				'type' => 'hidden',
				'default' => $this->getRequest()->getText( 'wpreturnto' ),
			];
		}

		$form = new FailForm( $fields, $this->getContext() );

		$form->setQuery( [ 'action' => 'edit' ] );

		$form->setSubmitCallback( [ $this, 'handleSubmission' ] );
		$form->setSubmitText( $this->msg( 'educationprogram-org-submit' )->text() );
		$form->setSubmitTooltip( 'ep-form-save' );
		$form->setShowSummary( !$this->isNew() );
		$form->setShowMinorEdit( !$this->isNew() );

		$action = $this->isNew() ? 'add' : 'edit';
		$form->setWrapperLegend( $this->msg( $this->prefixMsg( 'legend-' . $action ) ) );

		$form->addButton(
			'cancelEdit',
			$this->msg( 'cancel' )->text(),
			'cancelEdit',
			[
				'data-target-url' => $this->getReturnToTitle()->getFullURL(),
				'class' => 'ep-cancel',
			]
		);

		return $form;
	}

	/**
	 * @see FormSpecialPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = [];

		$fields['id'] = [ 'type' => 'hidden' ];

		$req = $this->getRequest();

		// This sort of sucks as well. Meh, HTMLForm is odd.
		if ( $req->wasPosted()
			&& $this->getUser()->matchEditToken( $req->getVal( 'wpEditToken' ) )
			&& $req->getCheck( 'wpitem-id' ) ) {
			$fields['id']['default'] = $req->getInt( 'wpitem-id' );
		}

		return $fields;
	}

	/**
	 * Populates the form fields with the data of the item
	 * and prefixes their names.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	protected function processFormFields( array $fields ) {
		if ( $this->item !== false ) {
			foreach ( $fields as $name => &$data ) {
				if ( !array_key_exists( 'default', $data ) ) {
					$data['default'] = $this->getDefaultFromItem( $this->item, $name );
				}
			}
		}

		$mappedFields = [];

		foreach ( $fields as $name => $field ) {
			if ( $this->getRequest()->getCheck( 'isnew' ) ) {
				// HTML form is being a huge pain in running the validation on post,
				// so just remove it if when not appropriate.
				unset( $field['validation-callback'] );
				unset( $field['required'] );
			}

			$mappedFields['item-' . $name] = $field;
		}

		return $mappedFields;
	}

	/**
	 * Gets the default value for a field from the item.
	 *
	 * @param PageObject $item
	 * @param string $name
	 *
	 * @return mixed
	 */
	protected function getDefaultFromItem( PageObject $item, $name ) {
		return $item->getField( $name );
	}

	/**
	 * Gets called after the form is saved.
	 */
	public function onSuccess() {
		$this->getOutput()->redirect( $this->getReturnToTitle( true )->getLocalURL() );
	}

	/**
	 * Returns the title to return to after the form has been submitted,
	 * or when form use is aborted for some other reason.
	 *
	 * @param bool $addedItem
	 *
	 * @return Title
	 */
	protected function getReturnToTitle( $addedItem = false ) {
		if ( $this->getRequest()->getCheck( 'wpreturnto' ) ) {
			return Title::newFromText( $this->getRequest()->getText( 'wpreturnto' ) );
		} elseif ( !$addedItem && $this->isNew() ) {
			return \SpecialPage::getTitleFor( $this->page->getListPage() );
		} elseif ( $this->item !== false ) {
			return $this->item->getTitle();
		} else {
			return $this->getIdentifierFromRequestArgs();
		}
	}

	/**
	 * @return Title
	 */
	protected function getIdentifierFromRequestArgs() {
		$fieldName = 'wpitem-id';
		$req = $this->getRequest();

		$title = null;

		if ( $req->getCheck( $fieldName ) ) {
			$title = $this->table->selectFieldsRow(
				$this->getTitleField(), [ 'id' => $req->getInt( $fieldName ) ]
			);
			if ( $title ) {
				$title = $this->table->getTitleFor( $title );
			}
		}

		return $title ?: $this->getTitle();
	}

	/**
	 * Process the form.  At this point we know that the user passes all the criteria in
	 * userCanExecute().
	 *
	 * @param array $data
	 *
	 * @return bool|array
	 */
	public function handleSubmission( array $data ) {
		$fields = [];
		$unknownValues = [];

		foreach ( $data as $name => $value ) {
			$matches = [];

			if ( preg_match( '/item-(.+)/', $name, $matches ) ) {
				if ( $matches[1] === 'id' && ( $value === '' || $value === '0' ) ) {
					$value = null;
				}

				if ( $this->table->canHaveField( $matches[1] ) ) {
					$fields[$matches[1]] = $value;
				} else {
					$unknownValues[$matches[1]] = $value;
				}
			}
		}

		$keys = array_keys( $fields );
		$fields = array_combine(
			$keys,
			array_map(
				[ $this, 'handleKnownField' ],
				$keys,
				$fields
			)
		);

		$this->handleKnownFields( $fields );

		/**
		 * @var PageObject $item
		 */
		$item = $this->table->newRow( $fields, is_null( $fields['id'] ) );

		foreach ( $unknownValues as $name => $value ) {
			$this->handleUnknownField( $item, $name, $value );
		}

		$revAction = new RevisionAction();
		$revAction->setUser( $this->getUser() );
		$revAction->setComment( $this->getRequest()->getText( 'wpSummary' ) );
		$revAction->setMinor( $this->getRequest()->getCheck( 'wpMinoredit' ) );

		$success = $item->revisionedSave( $revAction );

		if ( $success ) {
			return true;
		} else {
			return [ 'ep-err-failed-to-save' ];
		}
	}

	/**
	 * @since 0.2
	 *
	 * @param array &$fields
	 */
	protected function handleKnownFields( array &$fields ) {
	}

	/**
	 * Gets called for evey unknown submitted value, so they can be dealt with if needed.
	 *
	 * @param IORMRow $item
	 * @param string $name
	 * @param string $value This is a string, since it comes from request data, but might be a
	 *   number or other type.
	 */
	protected function handleUnknownField( IORMRow $item, $name, $value ) {
		// Override to use.
	}

	/**
	 * Gets called for evey known submitted value, so they can be dealt with if needed.
	 *
	 * @param string $name
	 * @param string $value This is a string, since it comes from request data, but might be a
	 *   number or other type.
	 *
	 * @return mixed The new value
	 */
	protected function handleKnownField( $name, $value ) {
		// Override to use.
		return $value;
	}
}

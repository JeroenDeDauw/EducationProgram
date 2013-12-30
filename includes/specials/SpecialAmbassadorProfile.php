<?php

namespace EducationProgram;

/**
 * Abstract profile page for ambassadors.
 *
 * @since 0.1
 *
 * @ingroup EducationProgram
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class SpecialAmbassadorProfile extends \FormSpecialPage {
	/**
	 * Returns the name of the ambassador class.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected abstract function getClassName();

	/**
	 * Returns if the user can access the page or not.
	 *
	 * @since 0.1
	 *
	 * @return boolean
	 */
	protected abstract function userCanAccess();

	/**
	 * @since 0.1
	 *
	 * @return string
	 */
	protected abstract function getMsgPrefix();

	/**
	 * Returns if the special page should be listed on Special:SpecialPages and similar interfaces.
	 *
	 * @since 0.1
	 *
	 * @return boolean
	 */
	public function isListed() {
		return $this->userCanAccess();
	}

	/**
	 * Main method.
	 *
	 * @since 0.1
	 *
	 * @param string $subPage
	 *
	 * @return string
	 */
	public function execute( $subPage ) {
		if ( !$this->userCanAccess() ) {
			$this->displayRestrictionError();
			return;
		}

		$this->displayNavigation();

		if ( $this->getRequest()->getSessionData( 'epprofilesaved' ) ) {
			$messageKey = $this->getMsgPrefix() . 'profile-saved';
			$this->getOutput()->addHTML(
				'<div class="successbox"><strong><p>' . $this->msg( $messageKey )->escaped() . '</p></strong></div>'
					. '<hr style="display: block; clear: both; visibility: hidden;" />'
			);
			$this->getRequest()->setSessionData( 'epprofilesaved', false );
		}

		parent::execute( $subPage );

		$this->getOutput()->addModules( 'ep.ambprofile' );
	}

	protected function getForm() {
		$form = parent::getForm();
		$form->setSubmitTooltip( 'ep-form-save' );
		return $form;
	}

	/**
	 * @see FormSpecialPage::getFormFields()
	 * @return array
	 */
	protected function getFormFields() {
		$fields = array();

		$class = $this->getClassName();
		$ambassador = $class::newFromUser( $this->getUser(), true );

		$msgPrefix = $this->getMsgPrefix();

		// Messages that can be used here:
		// * epoa-visible
		// * epca-visible
		$fields['visible'] = array(
			'type' => 'check',
			'label' => wfMessage( $this->getMsgPrefix() . 'visible', $this->getUser() )->text(),
			'required' => true,
			'rows' => 10,
			'default' => $ambassador->getField( 'visible' ),
		);

		// Messages that can be used here:
		// * epoa-profile-bio
		// * epca-profile-bio
		// * epoa-profile-invalid-bio
		// * epca-profile-invalid-bio
		$fields['bio'] = array(
			'type' => 'textarea',
			'label-message' => $this->getMsgPrefix() . 'profile-bio',
			'required' => true,
			'validation-callback' => function ( $value, array $alldata = null ) use( $msgPrefix ) {
				return strlen( $value ) < 10 ? wfMessage( $msgPrefix . 'profile-invalid-bio', 10 )->text() : true;
			},
			'rows' => 10,
			'id' => 'wpTextbox1',
			'default' => $ambassador->getField( 'bio' ),
		);

		// Messages that can be used here:
		// * epoa-profile-photo
		// * epca-profile-photo
		// * epoa-profile-photo-help
		// * epca-profile-photo-help
		$fields['photo'] = array(
			'type' => 'text',
			'label-message' => $this->getMsgPrefix() . 'profile-photo',
			'help-message' => array( $this->getMsgPrefix() . 'profile-photo-help', Settings::get( 'ambassadorCommonsUrl' ) ),
			'default' => $ambassador->getField( 'photo' ),
			'cssclass' => 'commons-input',
		);

		if ( !is_null( $ambassador->getId() ) ) {
			$fields['id'] = array(
				'type' => 'hidden',
				'default' => $ambassador->getId(),
			);
		}

		return $fields;
	}

	/**
	 * Gets called after the form is saved.
	 *
	 * @since 0.1
	 */
	public function onSuccess() {
		$class = $this->getClassName();

		Utils::log( array(
			'type' => $class::newFromUser( $this->getUser() )->getRoleName(),
			'subtype' => 'profilesave',
			'user' => $this->getUser(),
			'title' => $this->getPageTitle(),
		) );

		$this->getRequest()->setSessionData( 'epprofilesaved', true );
		$this->getOutput()->redirect( $this->getPageTitle()->getLocalURL() );
	}

	/**
	 * Process the form.  At this point we know that the user passes all the criteria in
	 * userCanExecute().
	 *
	 * @param array $data
	 *
	 * @return Bool|Array
	 */
	public function onSubmit( array $data ) {
		$class = $this->getClassName();

		$ambassador = $class::newFromUser( $this->getUser() );
		$ambassador->setFields( $data );

		return $ambassador->save() ? true : array();
	}

	/**
	 * Displays the navigation menu.
	 *
	 * @since 0.1
	 */
	protected function displayNavigation() {
		$menu = new Menu( $this->getContext() );
		$menu->display();
	}
}

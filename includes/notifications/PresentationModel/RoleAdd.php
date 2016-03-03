<?php

namespace EducationProgram\PresentationModel;

class RoleAdd extends \EchoEventPresentationModel {
	/**
	 * {@inheritdoc}
	 */
	public function canRender() {
		return $this->event->getTitle() instanceof \Title;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIconType() {
		return 'ep-added-to-course-icon';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPrimaryLink() {
		return array(
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'ep-role-add-link-text-view-course' )->text(),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSecondaryLinks() {
		return array( $this->getAgentLink() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();

		$truncatedCourseName = $this->language->truncate(
			$this->getCourseName(),
			self::PAGE_NAME_RECOMMENDED_LENGTH,
			'...',
			false
		);
		$msg->params( $truncatedCourseName );

		return $msg;
	}

	private function getCourseName() {
		// TODO Here we're adding yet another bit of unencapsulated code
		// that depends on the standard org/course (term) format.
		// Other patches currently in the pipeline face the same issue.
		// (See https://gerrit.wikimedia.org/r/#/c/98183/6/includes/rows/Course.php)
		// Once they're through we can consider a general solution.
		$fullTitle = $this->event->getTitle()->getText();
		$titleParts = explode( '/', $fullTitle, 2 );
		return $titleParts[1];
	}
}

<?php

namespace EducationProgram\PresentationModel;

class RoleAdd extends \EchoEventPresentationModel {

	/**
	 * @inheritDoc
	 */
	public function canRender() {
		return $this->event->getTitle() instanceof \Title;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconType() {
		return 'ep-added-to-course-icon';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrimaryLink() {
		return [
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'ep-role-add-link-text-view-course' )->text(),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getSecondaryLinks() {
		return [ $this->getAgentLink() ];
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaderMessage() {
		$msg = parent::getHeaderMessage();

		$truncatedCourseName = $this->language->embedBidi( $this->language->truncate(
			$this->getCourseName(),
			self::PAGE_NAME_RECOMMENDED_LENGTH,
			'...',
			false
		) );
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

<?php

namespace EducationProgram\PresentationModel;

class CourseTalk extends RoleAdd {
	/**
	 * {@inheritdoc}
	 */
	public function canRender() {
		return $this->event->getTitle() instanceof \Title && $this->event->getExtraParam( 'revid' ) !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIconType() {
		return 'ep-course-talk-icon';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPrimaryLink() {
		return array(
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'ep-course-talk-link-text-view-message' )->text(),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSecondaryLinks() {
		$viewChangesLink = array(
			'url' => $this->event->getTitle()->getLocalURL( array(
				'oldid' => 'prev',
				'diff' => $this->event->getExtraParam( 'revid' )
			) ),
			'label' => $this->msg( 'ep-course-talk-link-text-view-changes' )->text(),
			'description' => '',
			'icon' => false,
			'prioritized' => true,
		);
		return array( $this->getAgentLink(), $viewChangesLink );
	}
}

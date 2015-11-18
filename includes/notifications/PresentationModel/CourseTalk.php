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
			$this->event->getTitle()->getFullURL(),
			$this->msg( 'ep-course-talk-link-text-view-message' )->text()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSecondaryLinks() {
		return array(
			$this->event->getTitle()->getLocalURL( array(
				'oldid' => 'prev',
				'diff' => $this->event->getExtraParam( 'revid' )
			) ) => $this->msg( 'ep-course-talk-link-text-view-changes' )->text()
		);
	}
}

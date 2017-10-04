<?php

namespace EducationProgram\PresentationModel;

class CourseTalk extends RoleAdd {

	/**
	 * @inheritDoc
	 */
	public function canRender() {
		return $this->event->getTitle() instanceof \Title &&
			$this->event->getExtraParam( 'revid' ) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function getIconType() {
		return 'ep-course-talk-icon';
	}

	/**
	 * @inheritDoc
	 */
	public function getPrimaryLink() {
		return [
			'url' => $this->event->getTitle()->getFullURL(),
			'label' => $this->msg( 'ep-course-talk-link-text-view-message' )->text(),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function getSecondaryLinks() {
		$viewChangesLink = [
			'url' => $this->event->getTitle()->getLocalURL( [
				'oldid' => 'prev',
				'diff' => $this->event->getExtraParam( 'revid' )
			] ),
			'label' => $this->msg( 'ep-course-talk-link-text-view-changes' )->text(),
			'description' => '',
			'icon' => 'changes',
			'prioritized' => true,
		];
		return [ $this->getAgentLink(), $viewChangesLink ];
	}

}

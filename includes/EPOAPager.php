<?php

/**
 * Online ambassador pager.
 *
 * @since 0.1
 *
 * @file EPOAPager.php
 * @ingroup EductaionProgram
 *
 * @licence GNU GPL v3 or later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class EPOAPager extends EPPager {
	/**
	 * Constructor.
	 *
	 * @param IContextSource $context
	 * @param array $conds
	 */
	public function __construct( IContextSource $context, array $conds = array() ) {
		$this->mDefaultDirection = true;
		parent::__construct( $context, $conds, 'EPOA' );
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFields()
	 */
	public function getFields() {
		return array(
			'photo',
			'user_id',
			'bio',
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getRowClass()
	 */
	function getRowClass( $row ) {
		return 'ep-oa-row';
	}

	/**
	 * (non-PHPdoc)
	 * @see TablePager::getTableClass()
	 */
	public function getTableClass() {
		return 'TablePager ep-oas';
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFormattedValue()
	 */
	protected function getFormattedValue( $name, $value ) {
		switch ( $name ) {
			case 'photo':
				$title = Title::newFromText( $value, NS_FILE );
				$value = '';

				if ( is_object( $title ) ) {
					$api = new ApiMain( new FauxRequest( array(
						'action' => 'query',
						'format' => 'json',
						'prop' => 'imageinfo',
						'iiprop' => 'url',
						'titles' => $title->getFullText(),
						'iiurlwidth' => 200
					), true ), true );

					$api->execute();
					$result = $api->getResultData();

					if ( array_key_exists( 'query', $result ) && array_key_exists( 'pages', $result['query'] ) ) {
						foreach ( $result['query']['pages'] as $page ) {
							foreach ( $page['imageinfo'] as $imageInfo ) {
								$value = Html::element(
									'img',
									array(
										'src' => $imageInfo['thumburl']
									)
								);
								break;
							}
						}
					}
				}
				break;
			case 'user_id':
				$oa = EPOA::newFromUserId( $value );
				$value = Linker::userLink( $value, $oa->getName() ) . Linker::userToolLinks( $value, $oa->getName() );
				break;
			case 'bio':
				$value = $this->getOutput()->parseInline( $value );
				break;
		}

		return $value;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getSortableFields()
	 */
	protected function getSortableFields() {
		return array(
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::hasActionsColumn()
	 */
	protected function hasActionsColumn() {
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see EPPager::getFieldNames()
	 */
	public function getFieldNames() {
		$fields = parent::getFieldNames();

		return $fields;
	}

}

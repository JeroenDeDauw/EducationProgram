<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'EducationProgram' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['EducationProgram'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['EducationProgramAlias'] = __DIR__ . '/EducationProgram.i18n.alias.php';
	$wgExtensionMessagesFiles['EPNamespaces'] = __DIR__ . '/EducationProgram.i18n.ns.php';
	/*wfWarn(
		'Deprecated PHP entry point used for EducationProgram extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die( 'This version of the EducationProgram extension requires MediaWiki 1.28+' );
}

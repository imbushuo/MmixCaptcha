<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'ConfirmEdit/MmixCaptcha' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MmixCaptcha'] = __DIR__ . '/i18n';
	/* wfWarn(
		'Deprecated PHP entry point used for MmixCaptcha extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return;
} else {
	die( 'This version of the MmixCaptcha extension requires MediaWiki 1.25+' );
}

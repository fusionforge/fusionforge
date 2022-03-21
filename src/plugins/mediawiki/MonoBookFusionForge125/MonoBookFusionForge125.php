<?php

if ( function_exists( 'wfLoadSkin' ) ) {
	wfLoadSkin( 'MonoBookFusionForge125' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MonoBookFusionForge125'] = __DIR__ . '/i18n';
	/* wfWarn(
		'Deprecated PHP entry point used for MonoBook skin. Please use wfLoadSkin instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return true;
} else {
	die( 'This version of the MonoBookFusionForge skin requires MediaWiki 1.25+' );
}

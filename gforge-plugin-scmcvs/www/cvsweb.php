<?php

/**
  *
  * Gforge cvsweb php wrapper
  *
  * Copyright 2003-2004 (c) Gforge 
  * http://gforge.org
  *
  * @version   $Id$
  *
  */

require_once('pre.php');    // Initial db and session library, opens session
require_once('www/scm/include/scm_utils.php');

if (!$sys_use_scm) {
	exit_disabled();
}

$supportedContentTypes = array('text/html', 'text/x-cvsweb-markup');
$plainTextDiffTypes = array('c', 's', 'u', '');

$contentType = 'text/html';
if(getStringFromGet('cvsroot') && strpos(getStringFromGet('cvsroot'), ';') === false) {
	$projectName = getStringFromGet('cvsroot');
	if(getStringFromGet('r1') && getStringFromGet('r2') && in_array(getStringFromGet('f'), $plainTextDiffTypes)) {
		$contentType = 'text/plain';
	}
} else {
	$queryString = getStringFromServer('QUERY_STRING');
	if(preg_match_all('/[;]?([^\?;=]+)=([^;]+)/', $queryString, $matches, PREG_SET_ORDER)) {
		for($i = 0, $size = sizeof($matches); $i < $size; $i++) {
			$query[$matches[$i][1]] = urldecode($matches[$i][2]);
		}
		$projectName = $query['cvsroot'];
		if(isset($query['content-type'])) {
			$contentType = $query['content-type'];
		}
		if(isset($query['r1']) && isset($query['r2']) && (!isset($query['f']) || in_array($query['f'], $plainTextDiffTypes))) {
			$contentType = 'text/plain';
		}
	}
}
// Remove eventual leading /cvsroot/ or cvsroot/
$projectName = ereg_replace('^..[^/]*/','', $projectName);

if ($projectName) {
	$Group =& group_get_object_by_name($projectName);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	if (!$Group->isProject()) {
		exit_error('Error',$Language->getText('scm_index','error_only_projects_can_use_cvs'));
	}
	if (!$Group->usesSCM()) {
		exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
	}
	$perm = & $Group->getPermission(session_get_user());
	if ((!$Group->enableAnonSCM() && !($perm && is_object($perm) && $perm->isMember())) || !isset($GLOBALS['sys_path_to_scmweb']) || !is_file($GLOBALS['sys_path_to_scmweb'].'/cvsweb')) {
		exit_permission_denied();
	}
	if (in_array($contentType, $supportedContentTypes)) {
		scm_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$Group->getID()));
		echo '<div id="cvsweb">';
	} else {
		header("Content-type: $contentType" );
	}
	
	ob_start();
	passthru('PHP_WRAPPER="1" SCRIPT_NAME="'.getStringFromServer('SCRIPT_NAME').'" PATH_INFO="'.getStringFromServer('PATH_INFO').'" QUERY_STRING="'.getStringFromServer('QUERY_STRING').'" '.$GLOBALS['sys_path_to_scmweb'].'/cvsweb 2>&1');
	$content = ob_get_contents();
	ob_end_clean();
	
	if(extension_loaded('mbstring')) {
		$encoding = mb_detect_encoding($content);
		if($encoding != 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}
	}
	
	echo $content;
	
	if (in_array($contentType, $supportedContentTypes)) {
		echo '</div>';
		scm_footer(array());
	}
} else {
	exit_no_group();
}

?>

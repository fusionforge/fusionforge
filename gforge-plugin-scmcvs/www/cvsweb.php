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

if (!$sys_use_scm) {
	exit_disabled();
}

$supportedContentTypes = array('text/html', 'text/x-cvsweb-markup');

$contentType = 'text/html';
if(getStringFromGet('cvsroot') && strpos(getStringFromGet('cvsroot'), ';') === false) {
	$projectName = getStringFromGet('cvsroot');
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
		site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$Group->getID(),'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($Group->getPublicName())));
	} else {
		header("Content-type: $contentType" );
	}
	
	ob_start();
	passthru('PHP_WRAPPER="1" SCRIPT_NAME="'.getStringFromServer('SCRIPT_NAME').'" PATH_INFO="'.getStringFromServer('PATH_INFO').'" QUERY_STRING="'.getStringFromServer('QUERY_STRING').'" '.$GLOBALS['sys_path_to_scmweb'].'/cvsweb 2>&1');
	$content = ob_get_contents();
	ob_end_clean();
	
	if(extension_loaded('mb_string')) {
		$encoding = mb_detect_encoding($content);
		if($encoding != 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}
	}
	
	echo $content;
	
	if (in_array($contentType, $supportedContentTypes)) {
		site_project_footer(array());
	}
} else {
	exit_no_group();
}

?>

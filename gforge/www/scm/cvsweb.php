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

if (!$sys_use_cvs) {
	exit_disabled();
}

$projectName = getStringFromGet('cvsroot');

if ($projectName) {
	$Group =& group_get_object_by_name($projectName);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	if (!$Group->isProject()) {
		exit_error('Error',$Language->getText('scm_index','error_only_projects_can_use_cvs'));
	}
	if (!$Group->usesCVS()) {
		exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
	}
	$perm = & $Group->getPermission(session_get_user());
	if ((!$Group->enableAnonCVS() && !($perm && is_object($perm) && $perm->isMember())) || !isset($GLOBALS['sys_path_to_cvsweb']) || !is_file($GLOBALS['sys_path_to_cvsweb'].'/cvsweb')) {
		exit_permission_denied();
	}
	if ($contenttype != 'text/plain') {
		site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$Group->getID(),'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($Group->getPublicName())));
	} else {
		header("Content-type: $contenttype" );
	}

	passthru('PHPWRAPPER='.getStringFromServer('SCRIPT_NAME').' '.$GLOBALS['sys_path_to_cvsweb'].'/cvsweb "'.getStringFromServer('PATH_INFO').'" "'.getStringFromServer('QUERY_STRING').'" ');

	if ($contenttype != 'text/plain') {
		site_project_footer(array());
	}
} else {
	exit_no_group();
}

?>

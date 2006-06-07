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

// content types with html output
$supportedContentTypes = array('text/html', 'text/x-cvsweb-markup');

$plainTextDiffTypes = array('c', 's', 'u', '');

$contentType = 'text/html';

// we analyze the query to find the needed information
// this will allow us to determine the project name and the content type
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
// remove eventual leading /cvsroot/ or cvsroot/
$projectName = ereg_replace('^..[^/]*/','', $projectName);

// we found a project name in the query
if ($projectName) {
	$Group =& group_get_object_by_name($projectName);
	if (!$Group || !is_object($Group) || $Group->isError()) {
		exit_no_group();
	}
	if (!$Group->usesSCM()) {
		exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
	}
	
	// check if the scm_box is located in another server
	$scm_box = $Group->getSCMBox();
	//$external_scm = (gethostbyname($sys_default_domain) != gethostbyname($scm_box)); 
	$external_scm = !$sys_scm_single_host;

	if (session_loggedin()) {
		if (user_ismember($Group->getID())) {
			$perm = & $Group->getPermission(session_get_user());
			
			if (!($perm && is_object($perm) && $perm->isCVSReader()) && !$Group->enableAnonSCM()) {
				exit_permission_denied();
			}
		} else if (!$Group->enableAnonSCM()) {
			exit_permission_denied();
		}
		
	} else if (!$Group->enableAnonSCM()) {		// user is not logged in... check if group accepts anonymous CVS
		exit_permission_denied();
	}

	// User has access to the CVS... check for valid script
	// (only if we're working on a local CVS server, if the CVS server is external the variable
	// $sys_path_to_scmweb points to the path of the cvsweb script on the remote server)
	if (!isset($GLOBALS['sys_path_to_scmweb']) || (!$external_scm && !is_file($GLOBALS['sys_path_to_scmweb'].'/cvsweb'))) {
		exit_error('Error',"cvsweb script doesn't exist");
	}

	// should we output html ?
	$isHtml = in_array($contentType, $supportedContentTypes);

	// If we are accessing an external SCM box, execute the cvsweb script remotely and
	// pipe the results
	if ($external_scm) {
		//$server_script = "/cgi-bin/cvsweb";
		
		$scmweb = $GLOBALS["sys_path_to_scmweb"];
		// remove trailing slash
		$scmweb = preg_replace("/\\/\$/", "", $scmweb);
		
		$server_script = $scmweb."/cvsweb";
		// remove leading / (if any)
		$server_script = preg_replace("/^\\//", "", $server_script); 
		
		// pass the parameters passed to this script to the remote script in the same fashion
		$script_url = "http://".$scm_box."/".$server_script.$_SERVER["PATH_INFO"]."?".$_SERVER["QUERY_STRING"];
		$fh = @fopen($script_url, "r");
		if (!$fh) {
			exit_error('Error', 'Could not open script <b>'.$script_url.'</b>.');
		}
		
		// start reading the output of the script (in 8k chunks)
		$content = "";
		while (!feof($fh)) {
			$content .= fread($fh, 8192);
		}
		
		if ($isHtml) {
			// Now, we must replace the occurencies of $server_script with this script
			// (do this only of outputting HTML)
			// We must do this because we can't pass the environment variable SCRIPT_NAME
			// to the cvsweb script (maybe this can be fixed in the future?)
			$content = str_replace("/".$server_script, $_SERVER["SCRIPT_NAME"], $content);
		}
		
	} else {
		// SCM Box is the same server as this... execute the cvsweb script locally
		ob_start();
		// call to CVSWeb cgi. We use environment variables to pass parameters to the cgi.
		passthru('PHP_WRAPPER="1" SCRIPT_NAME="'.getStringFromServer('SCRIPT_NAME').'" PATH_INFO="'.getStringFromServer('PATH_INFO').'" QUERY_STRING="'.getStringFromServer('QUERY_STRING').'" '.$GLOBALS['sys_path_to_scmweb'].'/cvsweb 2>&1');
		$content = ob_get_contents();
		ob_end_clean();
	}
	

	if ($isHtml) {
		scm_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$Group->getID()));
		echo '<div id="cvsweb">';
	} else {
		header("Content-type: $contentType" );
	}
	
	// if we output html and we found the mbstring extension, we should try to encode the output of CVSWeb in UTF-8
	if($isHtml && extension_loaded('mbstring')) {
		$encoding = mb_detect_encoding($content);
		if($encoding != 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
		}
	}
	
	echo $content;
	
	if ($isHtml) {
		echo '</div>';
		scm_footer(array());
	}
} else {
	exit_no_group();
}

?>

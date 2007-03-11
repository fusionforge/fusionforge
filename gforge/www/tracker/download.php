<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFile.class');
//require_once('www/tracker/include/ArtifactFileHtml.class');
require_once('common/tracker/ArtifactType.class');
require_once('common/tracker/ArtifactType.class');

if (!session_loggedin()) {
	exit_not_logged_in();	
}

$arr=explode('/',getStringFromServer('REQUEST_URI'));
$group_id=$arr[3];
$atid=$arr[4];
$aid=$arr[5];
$file_id=$arr[6];

if (!$group_id) {
	exit_no_group();
}
//
//  get the Group object
//
$group =& group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

//
//  Create the ArtifactType object
//
$ath = new ArtifactType($group,$atid);
if (!$ath || !is_object($ath)) {
	exit_error('Error','ArtifactType could not be created');
}
if ($ath->isError()) {
	exit_error('Error',$ath->getErrorMessage());
}

$ah=new Artifact($ath,$aid);
if (!$ah || !is_object($ah)) {
	exit_error('ERROR','Artifact Could Not Be Created');
} else if ($ah->isError()) {
	exit_error('ERROR',$ah->getErrorMessage());
} else {
	$afh=new ArtifactFile($ah,$file_id);
	if (!$afh || !is_object($afh)) {
		exit_error('ERROR','ArtifactFile Could Not Be Created');
	} else if ($afh->isError()) {
		exit_error('ERROR',$afh->getErrorMessage());
	} else {
		Header ('Content-disposition: attachment');
		Header ('Content-type: '.$afh->getType());
		echo $afh->getData();
	}
}

?>

<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */

$no_gz_buffer=true;

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';


$arr=explode('/',getStringFromServer('REQUEST_URI'));
$group_id=(int)$arr[3];
$atid=(int)$arr[4];
$aid=(int)$arr[5];
$file_id=(int)$arr[6];

if (!$group_id) {
	exit_no_group();
}
//
//  get the Project object
//
$group = group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

//
//  Create the ArtifactType object
//
$ath = new ArtifactType($group,$atid);
if (!$ath || !is_object($ath)) {
	exit_error(_('ArtifactType could not be created'),'tracker');
}
if ($ath->isError()) {
	exit_error($ath->getErrorMessage(),'tracker');
}

$ah=new Artifact($ath,$aid);
if (!$ah || !is_object($ah)) {
	exit_error(_('Artifact Could Not Be Created'),'tracker');
} else if ($ah->isError()) {
	exit_error($ah->getErrorMessage(), 'tracker');
} else {
	$afh=new ArtifactFile($ah,$file_id);
	if (!$afh || !is_object($afh)) {
		exit_error(_('ArtifactFile Could Not Be Created'),'tracker');
	} else if ($afh->isError()) {
		exit_error($afh->getErrorMessage(),'tracker');
	} else {
		Header ('Content-disposition: filename="'.str_replace('"', '', $afh->getName()).'"');
		Header ("Content-type: ".$afh->getType());
		echo $afh->getData();
	}
}

?>

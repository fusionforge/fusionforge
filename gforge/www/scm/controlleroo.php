<?php
/**
  *
  * SourceForge CVS Frontend
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    
require_once('common/include/account.php');
require_once('common/include/cvsweb/ErrorHandler.class');
require_once('common/include/cvsweb/DirectoryHandler.class');
require_once('common/include/cvsweb/FileHandler.class');
require_once('common/include/cvsweb/RCSHandler.class');

//only projects can use cvs, and only if they have it turned on
$project =& group_get_object($group_id);
$cvsroot = $project->getUnixName();
$sys_cvsroot_dir = '/cvsroot/';

if (!$project->isProject()) {
	exit_error($Language->getText('scm_index','error_only_projects_can_use_cvs'));
}
if (!$project->usesCVS()) {
	exit_error($language->getText('scm_index','error_this_project_has_turned_off'));
}

site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$group_id,'toptab'=>'scm_index','pagename'=>'scm_index','sectionvals'=>array($project->getPublicName())));

if($project->enableAnonCVS()) {
	$allow = 1;
} else {
	if(session_loggedin()) {
		$user =& session_get_user();
		$memberRole = $project->getMemberRole($user->getID());
		if($memberRole == 1 || $memberRole == 2) {//only for Project Manager & Developer - others must not view sources
			$allow = 1;
		} else {
			$allow = 0;
		}
	} else {
		$allow = 0;
	}
}

if ($allow) {
	$DHD = new DirectoryHandler();
	$FHD = new FileHandler();
	$RCH = new RCSHandler();
	$CVSROOT = $sys_cvsroot_dir.$cvsroot;
	$DIRNAME = ($dir_name != "")?"/$dir_name":"";
	$DIRNAME = $CVSROOT.$DIRNAME;
	$DIRPATH = explode("/",$dir_name);
	echo("Current directory: ");
	for($i=0;$i<count($DIRPATH);$i++) {
		$LINKPATH = array();
		for($j=0; $j<=$i; $j++) {
	    $LINKPATH[] = $DIRPATH[$j];
		}
		$LINK = implode("/",$LINKPATH);
		$value = ($DIRPATH[$i] == "")?$project->getPublicName():$DIRPATH[$i];
		echo("<b><a href=\"http://".$GLOBALS['sys_default_domain']."/scm/controlleroo.php?group_id=$group_id&dir_name=$LINK&hide_attic=$hide_attic\">[$value]</a>/</b>");
	}

	if($hide_attic) {
		echo("<br><br>[<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/controlleroo.php?group_id=$group_id&dir_name=$dir_name&hide_attic=0\">Unhide Attic</a>]");
	} else {
		echo("<br><br>[<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/controlleroo.php?group_id=$group_id&dir_name=$dir_name&hide_attic=1\">Hide Attic</a>]");
	}
	echo("<br>");

	// hide some files and directories
	$hideFile = ".|CVS";
	if($hide_attic) {
		$hideFile .= "|Attic";
	}

	$DHD->hideFiles($hideFile);

	if (false === ($dirContent = $DHD->readDirectory($DIRNAME))) {
		echo("Error: ".$DHD->getError());
	}

	echo("<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\">");
	echo("<tr>");
	echo("<td width=\"20%\" bgcolor=\"#AAAAAA\"><b>File</b></td>");
	echo("<td width=\"10%\" bgcolor=\"#AAAAAA\"><b>REV</b></td>");
	echo("<td width=\"10%\" bgcolor=\"#AAAAAA\"><b>Age</b></td>");
	echo("<td width=\"10%\" bgcolor=\"#AAAAAA\"><b>Author</b></td>");
	echo("<td width=\"50%\" bgcolor=\"#AAAAAA\"><b>Last log entry</b></td>");
	echo("</tr>");
	$i = 0;
	foreach($dirContent AS $k=>$v) { 
		$bgc = "#F0F0F0";
		if ($i % 2 == 0) {
		    $bgc = "#FFFFFF";
		}
		$i++;
		if($FHD->getFileType($DIRNAME."/".$v) == 1) {
			echo("<tr><td colspan=\"5\" width=\"100%\" bgcolor=\"".$bgc."\"><b>&nbsp;&nbsp;&nbsp;");
			echo("<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/controlleroo.php?group_id=$group_id&dir_name=$dir_name/$v&hide_attic=$hide_attic\">$v</a>");
			echo("</b></td></tr>");
		} else {	
			$rcsFile = $DIRNAME."/".$v;
			$fileName = substr($v,0,-2);
			if(false === ($rcsInfo = $RCH->getRevisionInfo($rcsFile))) {
				echo($RCH->getError());
			}
			$fileLink = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$dir_name/$fileName&hide_attic=$hide_attic\">$fileName</a>";
			// create 'Age' string
			$age = time() - strtotime($rcsInfo['DATE']);
			if($age < 24*3600) {
				$age /= 3600;
				$age = floor($age)." hour(s)";
			} elseif($age < 30*24*3600) {
				$age /= 24*3600;
				$age = floor($age)." day(s)";
			} else {
				$age /= 30*24*3600;
				$age = floor($age)." month(s)";
			}
			echo("<tr>");
			echo("<td width=\"20%\" bgcolor=\"".$bgc."\">&#187;&nbsp;&nbsp;".$fileLink."</td>");
			echo("<td width=\"10%\" bgcolor=\"".$bgc."\">".$rcsInfo['REV']."</td>");
			echo("<td width=\"10%\" bgcolor=\"".$bgc."\">".$age."</td>");
			echo("<td width=\"10%\" bgcolor=\"".$bgc."\">".$rcsInfo['AUTHOR']."</td>");
			echo("<td width=\"50%\" bgcolor=\"".$bgc."\">".$rcsInfo['LOG']."</td>");
			echo("</tr>");
		}
	}
	echo("</table>");
} else {
	echo("Error: u don't have access rights to <b>".$project->getPublicName()."</b> CVS root = $cvsroot");
}
site_project_footer(array());
?>

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
	$CVSROOT = $GLOBALS['sys_cvsroot_dir'].$cvsroot;
	$DIRNAME = ($file_name != "")?"$file_name":"";
	$DIRNAME = $CVSROOT.$DIRNAME;
	$DIRPATH = explode("/",$file_name);
	for($i=0;$i<count($DIRPATH)-1;$i++) {
		$LINKPATH = array();
		for($j=0;$j<=$i;$j++) {
			$LINKPATH[] = $DIRPATH[$j];
		}
		$LINK = implode("/",$LINKPATH);
		$value = ($DIRPATH[$i] == "")?"CVSROOT":$DIRPATH[$i];
		echo("<b><a href=\"http://".$GLOBALS['sys_default_domain']."/scm/index.php?group_id=$group_id&dir_name=$LINK&hide_attic=$hide_attic\">$value</a>/</b>");
	}

	echo("<br>");
	echo("<br>");
	$value = "<font size=\"+2\"><b>".$DIRPATH[count($DIRPATH)-1]."</b></font>";
	$fileLink = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&r2=$r2&hide_attic=$hide_attic\">$value</a>";
	echo($fileLink);
	echo("<br>");
	echo("<br>");

	if($view_action == "") {
		$view_action = "l";
	}

	$RCSFile = $DIRNAME.",v";
	switch($view_action) {
	case "l":
		if(false === $RCH->getRCSLog($RCSFile)) {
			echo("Error: ".$RCH->getError());
		}
		if(false === ($revisions = $RCH->handleRCSLog())) {
			echo("Error: ".$RCH->getError());
		} else {
		$diffr = "";
		foreach($revisions AS $k=>$v) {
			$viewLink = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=v&r=".$v['REV']."&hide_attic=$hide_attic\">view file</a>";
			$diffLink = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=d&r2=$diffr&r1=".$v['REV']."&hide_attic=$hide_attic\">revision $diffr</a>";
			if(isset($r2) && $r2 != "") {
				$diffLink .= ", or selected <a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=d&r2=$r2&r1=".$v['REV']."&hide_attic=$hide_attic\">revision $r2</a>";
			}
			$selectForDiff = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=l&r2=".$v['REV']."&hide_attic=$hide_attic\">select for diff</a>";
			echo("<b>Revision:</b> ".$v['REV']." ($viewLink) - ($selectForDiff)<br>");
			echo("<b>Date:</b> ".$v['DATE']."<br>");
			echo("<b>Author:</b> ".$v['AUTHOR']."<br>");
			echo("<b>Branch:</b> ".$v['BRANCH']."<br>");
			echo("<b>Log:</b> ".$v['LOG']."<br>");
			echo("<b>Diff To:</b> $diffLink<br>");
			echo("<hr>");
			$diffr = $v['REV'];
		}
	}
	break;
	case "v":
		if(false === ($content = $RCH->getRCSContent($RCSFile,$r))) {
			echo("Error: ".$RCH->getError());
		}
		$content = str_replace("\n","<br>",$content);
		$content = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$content);
		echo($content);
		break;
	case "d":
		$diffL1 = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=d&r2=$r2&r1=$r1&diff_type=SC&hide_attic=$hide_attic\">Short Color Diff</a>";
		$diffL2 = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=d&r2=$r2&r1=$r1&diff_type=LC&hide_attic=$hide_attic\">Long Color Diff</a>";
		$diffL3 = "<a href=\"http://".$GLOBALS['sys_default_domain']."/scm/viewFile.php?group_id=$group_id&file_name=$file_name&view_action=d&r2=$r2&r1=$r1&diff_type=U&hide_attic=$hide_attic\">Unified Diff</a>";
		echo("<b>".$diffL1." | ".$diffL2." | ".$diffL3."</b><br><br>");
		if($diff_type == "") {
			$diff_type = "LC";
		}
		if(false === ($content = $RCH->doDiff($RCSFile,$r1,$diff_type,$r2))) {
			echo("Error: ".$RCH->getError());
		}
		echo($content);
		break;
	}
} else {
	echo("Error: you don't have access rights to <b>".$project->getPublicName()."</b> CVS root");
}
site_project_footer(array());
?>

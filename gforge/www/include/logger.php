<?php
/**
 * logger.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/*
	Determine group
*/
$group_id=getIntFromRequest('group_id');
if (isset($group_id) && $group_id) {
	$log_group=$group_id;
} else if (isset($form_grp) && $form_grp) {
	$log_group=$form_grp;
} else if (isset($group_name) && $group_name) {
	$group =& group_get_object_by_name($group_name);
	if ($group) $log_group=$group->getID();
} else {
	//
	//
	//	This is a hack to allow the logger to have a group_id present
	//	for foundry and project summary pages
	//
	//
	if ( $GLOBALS['sys_urlprefix'] != '' ) {
		$pathwithoutprefix=ereg_replace($GLOBALS['sys_urlprefix'],'',getStringFromServer('REQUEST_URI'));
	} else {
		$pathwithoutprefix=getStringFromServer('REQUEST_URI');
	}
	//$expl_pathinfo = explode('/',getStringFromServer('REQUEST_URI'));
	$expl_pathinfo = explode('/',$pathwithoutprefix);
	if (($expl_pathinfo[1]=='foundry') || ($expl_pathinfo[1]=='projects')) {
		$res_grp=db_query("
			SELECT *
			FROM groups
			WHERE unix_group_name='$expl_pathinfo[2]'
			AND status IN ('A','H')
		");
		
		// store subpage id for analyzing later
		$subpage = @$expl_pathinfo[3];
		$subpage2 = @$expl_pathinfo[4];

		//set up the group_id
	   	$group_id=db_result($res_grp,0,'group_id');
		//set up a foundry object for reference all over the place
		if ($group_id) {
			$grp =& group_get_object($group_id,$res_grp);
			if ($grp) {
				//this is a project - so set up the project var properly
				$project =& $grp;
				//echo "IS PROJECT: ".$group_id;
				$log_group=$group_id;
			} else {
				$log_group=0;
			}
		} else {
			$log_group=0;
		}
	}

	// Is it a Personal wiki URL (see phpwiki plugin)
	if (($expl_pathinfo[1]=='wiki') && ($expl_pathinfo[2]=='u')) {
		// URLs are /wiki/u/<user_name>/<page_name>
		// Fake group_name which is in fact the user_name.
		$group_name = $expl_pathinfo[3];
	}

	// Is it a Project wiki URL (see phpwiki plugin)
	if (($expl_pathinfo[1]=='wiki') && ($expl_pathinfo[2]=='g')) {
		// URLs are /wiki/g/<user_name>/<page_name>
		$group_name = $expl_pathinfo[3];
		$res_grp=db_query("
			SELECT *
			FROM groups
			WHERE unix_group_name='$group_name'
			AND status IN ('A','H')
		");
		
		// store subpage id for analyzing later
		$subpage = @$expl_pathinfo[4];
		$subpage2 = @$expl_pathinfo[5];

		//set up the group_id
	   	$group_id=db_result($res_grp,0,'group_id');
		//set up a foundry object for reference all over the place
		if ($group_id) {
			$grp =& group_get_object($group_id,$res_grp);
			if ($grp) {
				//this is a project - so set up the project var properly
				$project =& $grp;
				//echo "IS PROJECT: ".$group_id;
				$log_group=$group_id;
			} else {
				$log_group=0;
			}
		} else {
			$log_group=0;
		}
	}
	$log_group=0;
}

$sql =	"INSERT INTO activity_log "
	. "(day,hour,group_id,browser,ver,platform,time,page,type) "
	. "VALUES (" . date('Ymd', mktime()) . ",'" . date('H', mktime())
	. "','$log_group','" . browser_get_agent() . "','" . browser_get_version() 
	. "','" . browser_get_platform() . "','" . time() . "','".getStringFromServer('PHP_SELF')."','0');";

$res_logger = db_query ( $sql );

//
//	temp hack
//
$sys_db_is_dirty=false;

if (!$res_logger) {
	echo "An error occured in the logger.\n";
	echo htmlspecialchars(db_error());
	exit;
}

?>

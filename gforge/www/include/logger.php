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

if ($group_id) {
	$log_group=$group_id;
} else if ($form_grp) {
	$log_group=$form_grp;
} else {
	//
	//
	//	This is a hack to allow the logger to have a group_id present
	//	for foundry and project summary pages
	//
	//
	$expl_pathinfo = explode('/',$REQUEST_URI);
	if (($expl_pathinfo[1]=='foundry') || ($expl_pathinfo[1]=='projects')) {
		$res_grp=db_query("
			SELECT *
			FROM groups
			WHERE unix_group_name='$expl_pathinfo[2]'
			AND status IN ('A','H')
		");
		
		// store subpage id for analyzing later
		$subpage = $expl_pathinfo[3];
		$subpage2 = $expl_pathinfo[4];

		//set up the group_id
	   	$group_id=db_result($res_grp,0,'group_id');
		//set up a foundry object for reference all over the place
		if ($group_id) {
			$grp =& group_get_object($group_id,$res_grp);
			if ($grp) {
				if ($grp->isFoundry()) {
					//this is a foundry - so set up the foundry var properly
					$foundry =& $grp;
					//echo "IS FOUNDRY: ".$group_id;
				} else {
					//this is a project - so set up the project var properly
					$project =& $grp;
					//echo "IS PROJECT: ".$group_id;
				}
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
	. "','" . browser_get_platform() . "','" . time() . "','$PHP_SELF','0');";

$res_logger = db_query ( $sql );

//
//	temp hack
//
$sys_db_is_dirty=false;

if (!$res_logger) {
	echo "An error occured in the logger.\n";
	echo db_error();
	exit;
}

?>

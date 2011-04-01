<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
	Determine group
*/
$group_id=getIntFromRequest('group_id');
$form_grp=getIntFromRequest('form_grp');
if (isset($group_id) && is_numeric($group_id) && $group_id) {
	$log_group=$group_id;
} else if (isset($form_grp) && is_numeric($form_grp) && $form_grp) {
	$log_group=$form_grp;
} else if (isset($group_name) && $group_name) {
	$group = group_get_object_by_name($group_name);
	if ($group) {
		$log_group=$group->getID();
	} else {
		$log_group=0;
	}
} else {
	//
	//
	//	This is a hack to allow the logger to have a group_id present
	//	for foundry and project summary pages
	//
	//
	$pos = strpos (getStringFromServer('REQUEST_URI'),
		       normalized_urlprefix ());
	if (($pos !== false) && ($pos == 0)) {
		$pathwithoutprefix = substr (getStringFromServer('REQUEST_URI'),
					     strlen (normalized_urlprefix ()) - 1);
	}
	$expl_pathinfo = explode('/',$pathwithoutprefix);
	if (($expl_pathinfo[1]=='foundry') || ($expl_pathinfo[1]=='projects')) {
		$res_grp = db_query_params ('
			SELECT *
			FROM groups
			WHERE unix_group_name=$1
			AND status IN ($2,$3)',
					    array ($expl_pathinfo[2],
						   'A',
						   'H'));
		
		// store subpage id for analyzing later
		$subpage  = isset($expl_pathinfo[3])?$expl_pathinfo[3]:'';
		$subpage2 = isset($expl_pathinfo[4]) ? $expl_pathinfo[4] : '';

		//set up the group_id
	   	$group_id=db_result($res_grp,0,'group_id');
		//set up a foundry object for reference all over the place
		if ($group_id) {
			$grp = group_get_object($group_id,$res_grp);
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
		$res_grp=db_query_params ('
			SELECT *
			FROM groups
			WHERE unix_group_name=$1
			AND status IN ($2,$3)
		',
			array($group_name,
				'A',
				'H'));
		
		// store subpage id for analyzing later
		$subpage = @$expl_pathinfo[4];
		$subpage2 = @$expl_pathinfo[5];

		//set up the group_id
	   	$group_id=db_result($res_grp,0,'group_id');
		//set up a foundry object for reference all over the place
		if ($group_id) {
			$grp = group_get_object($group_id,$res_grp);
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

$sql =	"INSERT INTO activity_log 
(day,hour,group_id,browser,ver,platform,time,page,type) 
VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9);";

$res_logger = db_query_params ($sql, array(date('Ymd'), date('H'),
	$log_group, browser_get_agent(), browser_get_version(), browser_get_platform(),
	time(), getStringFromServer('PHP_SELF'), '0'));

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

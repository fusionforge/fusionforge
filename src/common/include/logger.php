<?php
/**
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/*
	Determine group
*/

$group_id=getIntFromRequest('group_id');
$form_grp=getIntFromRequest('form_grp');

$log_group=0;

if (isset($group_id) && is_numeric($group_id) && $group_id) {
	$log_group=$group_id;
} elseif (isset($form_grp) && is_numeric($form_grp) && $form_grp) {
	$log_group=$form_grp;
} elseif (isset($group_name) && $group_name) {
	$group = group_get_object_by_name($group_name);
	if ($group) {
		$log_group=$group->getID();
	}
} else {
	//
	//	This is a hack to allow the logger to have a group_id present for project summary pages
	//
	$expl_pathinfo = explode('/', getStringFromServer('REQUEST_URI'));
	if ($expl_pathinfo[1] == 'projects') {
		$group_name = $expl_pathinfo[2];
		if ($group_name) {
			$res_grp = db_query_params ('
				SELECT *
				FROM groups
				WHERE unix_group_name=$1
				AND status IN ($2,$3)',
						    array ($group_name,
							   'A', 'H', 'P'));

			// store subpage id for analyzing later
			// This will later be used in the www/projects for instance
			$subpage  = isset($expl_pathinfo[3]) ? $expl_pathinfo[3] : '';
			$subpage2 = isset($expl_pathinfo[4]) ? $expl_pathinfo[4] : '';

			global $RESTPATH_PROJECTS_GROUP_ID;
			global $RESTPATH_PROJECTS_PROJECT;

			//set up the group_id
		   	$group_id=db_result($res_grp,0,'group_id');

			if ($group_id) {
				$grp = group_get_object($group_id,$res_grp);
				if ($grp) {
					//this is a project - so set up the project var properly
					$project =& $grp;
					//echo "IS PROJECT: ".$group_id;
					$log_group=$group_id;
				}

				// This will later be used in the www/projects for instance
				$RESTPATH_PROJECTS_PROJECT = $project;
				$RESTPATH_PROJECTS_GROUP_ID = $group_id;

				global $RESTPATH_PROJECTS_SUBPAGE;
				$RESTPATH_PROJECTS_SUBPAGE = $subpage;

				global $RESTPATH_PROJECTS_SUBPAGE2;
				$RESTPATH_PROJECTS_SUBPAGE2 = $subpage2;
			}
			else {
				$RESTPATH_PROJECTS_GROUP_ID = -1;
			}
		}
	}

	if ($expl_pathinfo[1]=='wiki') {
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

		//set up the group_id
		$group_id=db_result($res_grp,0,'group_id');
		if ($group_id) {
			$grp = group_get_object($group_id,$res_grp);
			if ($grp) {
				//this is a project - so set up the project var properly
				$project =& $grp;
				//echo "IS PROJECT: ".$group_id;
				$log_group=$group_id;
			}
		}
	}

}

$log_user_id = user_getid();
if (!$log_user_id) {
	$log_user_id = 0;
}

$res_logger = db_query_params ('INSERT INTO activity_log
	(day,hour,group_id,browser,ver,platform,time,page,type, user_id)
	VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)',
	array(date('Ymd'), date('H'),
		$log_group, browser_get_agent(), browser_get_version(), browser_get_platform(),
		time(), getStringFromServer('PHP_SELF'), '0', $log_user_id));

if (!$res_logger) {
	echo "An error occured in the logger.\n";
	echo htmlspecialchars(db_error());
	exit;
}

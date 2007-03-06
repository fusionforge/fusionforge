<?php
/**
 * Project Admin: Module of common functions
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*

	Standard header to be used on all /project/admin/* pages

*/

function project_admin_header($params) {
	global $group_id,$feedback,$HTML,$Language;

	$params['toptab']='admin';
	$params['group']=$group_id;

	$project =& group_get_object($group_id);
	if (!$project || !is_object($project)) {
		return;
	}

	$perm =& $project->getPermission( session_get_user() );
	if (!$perm || !is_object($perm)) {
		return;
	}

	/*
		Enforce Project Admin Perms
	*/
	if (!$perm->isAdmin()) {
		exit_permission_denied();
	}

	site_project_header($params);
	
	$labels = array();
	$links = array();
	
	$labels[] = $Language->getText('project_admin_utils','admin');
	$labels[] = $Language->getText('project_admin_utils','edit_public_info');
	$labels[] = $Language->getText('project_admin_utils','project_history');
	if($GLOBALS['sys_use_people']) {
		$labels[] = $Language->getText('project_admin_utils','post_jobs');
		$labels[] = $Language->getText('project_admin_utils','edit_jobs');
	}
	if($GLOBALS['sys_use_project_multimedia']) {
		$labels[] = $Language->getText('project_admin_utils','multimedia_data');
	}
	if($GLOBALS['sys_use_project_vhost']) {
		$labels[] = $Language->getText('project_admin_utils','vhosts');
	}
	if($GLOBALS['sys_use_project_database']) {
		$labels[] = $Language->getText('project_admin_utils','database_admin');
	}
	$labels[] = $Language->getText('project_admin_utils','stats');
	
	$links[] = '/project/admin/?group_id='.$group_id;
	$links[] = '/project/admin/editgroupinfo.php?group_id='.$group_id;
	$links[] = '/project/admin/history.php?group_id='.$group_id;
	if($GLOBALS['sys_use_people']) {
		$links[] = '/people/createjob.php?group_id='.$group_id;
		$links[] = '/people/?group_id='.$group_id;
	}
	if($GLOBALS['sys_use_project_multimedia']) {
		$links[] = '/project/admin/editimages.php?group_id='.$group_id;
	}
	if($GLOBALS['sys_use_project_vhost']) {
		$links[] = '/project/admin/vhost.php?group_id='.$group_id;
	}
	if($GLOBALS['sys_use_project_database']) {
		$links[] = '/project/admin/database.php?group_id='.$group_id;
	}
	$links[] = '/project/stats/?group_id='.$group_id;
	echo ($HTML->beginSubMenu());	
	echo $HTML->printSubMenu($labels, $links);
	plugin_hook ("groupadminmenu", $params) ;
	echo ($HTML->endSubMenu());
}

/*

	Standard footer to be used on all /project/admin/* pages

*/

function project_admin_footer($params) {
	site_project_footer($params);
}

/*

	The following three functions are for group
	audit trail

	When changes like adduser/rmuser/change status
	are made to a group, a row is added to audit trail
	using group_add_history()

*/

function group_get_history ($group_id=false) {
	$sql="SELECT group_history.field_name,group_history.old_value,group_history.adddate,users.user_name ".
		 "FROM group_history,users ".
		 "WHERE group_history.mod_by=users.user_id ".
		 "AND group_id='$group_id' ORDER BY group_history.adddate DESC";
	return db_query($sql);
}		   
	
function group_add_history ($field_name,$old_value,$group_id) {
	$group=group_get_object($group_id);
	$group->addHistory($field_name,$old_value);
}		   

/*

	Nicely html-formatted output of this group's audit trail

*/

function show_grouphistory ($group_id) {
	/*	  
		show the group_history rows that are relevant to 
		this group_id
	*/
	global $sys_datefmt, $Language;
	$result=group_get_history($group_id);
	$rows=db_numrows($result);
	
	if ($rows > 0) {
	
		echo '
		<h3>'.$Language->getText('project_admin_utils','change_history').'</h3>
		<p/>';
		$title_arr=array();
		$title_arr[]=$Language->getText('project_admin_utils','field');
		$title_arr[]=$Language->getText('project_admin_utils','old_value');
		$title_arr[]=$Language->getText('project_admin_utils','date');
		$title_arr[]=$Language->getText('project_admin_utils','by');
		
		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
		for ($i=0; $i < $rows; $i++) { 
			$field=db_result($result, $i, 'field_name');
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.$field.'</td><td>';
			
			if ($field=='removed user') {
				echo user_getname(db_result($result, $i, 'old_value'));
			} else {
				echo db_result($result, $i, 'old_value');
			}			
			echo '</td>'.
				'<td>'.date($sys_datefmt,db_result($result, $i, 'adddate')).'</td>'.
				'<td>'.db_result($result, $i, 'user_name').'</td></tr>';
		}		   

		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo '  
		<h3>'.$Language->getText('project_admin_utils','no_changes').'</h3>';
	}	   
}	   

/*
	prdb_namespace_seek - check that a projects' potential db name hasn't
	already been used.  If it has - add a 1..20 to the end of it.  If it 
	iterates through twenty times and still fails - namespace depletion - 
	throw an error.

 */
function prdb_namespace_seek($namecheck) {

	$query = "select * "
		."from prdb_dbs "
		."where dbname = '$namecheck'";

	$res_dbl = db_query($query);

	if (db_numrows($res_dbl) > 0) {
		//crap, we're going to have issues
		$curr_num = 1;

		while ((db_numrows($res_dbl) > 0) && ($curr_num < 20)) {
			
			$curr_num++;
			$namecheck .= "$namecheck"."$curr_num";
					
			$query = "select * "
				."from prdb_dbs "
				."where dbname = '$namecheck'";

			$res_dbl = db_query($query);
		}

		// if we reached 20, then the namespace is depleted - eject eject
		if ($curr_num == 20) {
			exit_error("Namespace Failure","Failed to find namespace for database");
		}

	}
	return $namecheck;

} //end prdb_namespace_seek()

function random_pwgen() {

	srand ( (double) microtime()*10000000); 
	$rnpw = "";

	for ($i = 0; $i < 10; $i++) {

		$rn = rand(1,2);

		if ($rn == 1) {
			$rnpw .= rand(1,9);
		} else {
			$rnpw .= chr(rand(65,122));
		}

	}
	return $rnpw;
}

function permissions_blurb() {
	global $Language;
	
	return $Language->getText('project_admin_utils','permission_blurb');
}

?>

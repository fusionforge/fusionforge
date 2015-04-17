<?php
/**
 * Project Members Information
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2014-2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once '../../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';

global $HTML;

function GetTime ($timedifference) {

	if ($timedifference >= 0) {

		$mval = ($timedifference / 2592000);
		$monthval = intval($mval);
		if ($monthval > 1 ) {
			return "$monthval Months Ago";
		}

		$wval = ($timedifference / 604800);
		$weektime = intval($wval);
		if ($weektime > 1 ) {
			return "$weektime Weeks Ago";
		}

		$dval = ($timedifference / 86400) ;
		$daytime = intval($dval);
		if ($daytime > 1 ) {
			return "$daytime Days Ago";
		}

		$hval = ($timedifference / 3600);
		$hourtime = intval($hval);
		if ($hourtime > 1 ) {
			return "$hourtime Hours Ago";
		}

		$mval = ($timedifference  / 60);
		$minutetime = intval($mval);
		if ( $minutetime > 1 ) {
			return "$minutetime Minutes Ago";
		}

		return "$timedifference Seconds Ago";

	} else {

		$timedifference=abs($timedifference);

		$mval = ($timedifference / 2592000);
		$monthval = intval($mval);
		if ($monthval > 1 ) {
			return "Next $monthval Months";
		}

		$wval = ($timedifference / 604800);
		$weektime = intval($wval);
		if ($weektime > 1 ) {
			return "Next $weektime Weeks";
		}

		$dval = ($timedifference / 86400) ;
		$daytime = intval($dval);
		if ($daytime > 1 ) {
			return "Next $daytime Days";
		}

		$hval = ($timedifference / 3600);
		$hourtime = intval($hval);
		if ($hourtime > 1 ) {
			return "Next $hourtime Hours";
		}

		$mval = ($timedifference  / 60);
		$minutetime = intval($mval);
		if ( $minutetime > 1 ) {
			return "Next $minutetime Minutes";
		}

		return "Next $timedifference Seconds";

	}

}

//
//      get the Project object
//
$group_id = getIntFromRequest('group_id');

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
	exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage(),'admin');
	} else {
		exit_error($group->getErrorMessage(),'admin');
	}
}

if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

$title = _('Project Member List');
site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'memberlist'));

echo html_e('p', array(), _('If you would like to contribute to this project by becoming a member, contact one of the project admins, designated in bold text below.'));

$title_arr=array();
$title_arr[]=_('Developer');
$title_arr[]=_('Summary');
$title_arr[]=_('Open Date');
$title_arr[]=_('Last Modified');
echo $HTML->listTableTop($title_arr);

// list members
foreach ($group->getUsers() as $member) {
	$cells = array();
	$link = util_make_link_u ($member->getUnixName(), $member->getID(), $member->getRealName()) ;
	if ( RBACEngine::getInstance()->isActionAllowedForUser($member,'project_admin',$group->getID())) {
		$cells[][] = html_e('strong', array(), $link);
	} else {
		$cells[][] = $link;
	}
	$cells[][] = util_make_link('/sendmessage.php?touser='.$member->getId(), sprintf(_('Contact %s'), $member->getRealName()));
	$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($member) ;
	sortRoleList ($roles) ;
	$role_names = array () ;
	foreach ($roles as $role) {
		if ($role->getHomeProject() && $role->getHomeProject()->getID() == $group->getID()) {
			$role_names[] = $role->getName() ;
		}
	}
	$role_string = implode (', ', $role_names) ;
	$cells[] = array($role_string, 'class' => 'align-center');
	if(forge_get_config('use_people')) {
		$cells[] = array(util_make_link('/people/viewprofile.php?user_id='.$member->getID(),_('View')), 'class' => 'align-center');
	} else {
		$cells[][] = '';
	}
	echo $HTML->multiTableRow(array(), $cells);

	// print out all the artifacts assigned to this person
	$afu = new ArtifactsForUser($member);
	$artifacts = $afu->getAssignedArtifactsByGroup();
	foreach ($artifacts as $artifact) {
		if (!$artifact->isError() && $artifact->ArtifactType->Group->getID() == $group_id) {
			$cells = array();
			$cells[][] = util_make_link('/tracker/?func=detail&aid='. $artifact->getID() .'&group_id='.$artifact->ArtifactType->Group->getID().'&atid='.$artifact->ArtifactType->getID(), $artifact->ArtifactType->getName().' '.$artifact->getID());
			$cells[][] = $artifact->getSummary();
			$cells[][] = GetTime( time() - $artifact->getOpenDate());
			$messages = db_query_params("select adddate FROM artifact_message_user_vw ".
							"WHERE artifact_id=$1 ".
							"ORDER by adddate DESC", array($artifact->getID()));
			if ( db_numrows($messages)) {
				$cells[][] = GetTime(time() - db_result($messages, 0, 'adddate'));
			} else {
				$cells[][] = GetTime(time() - $artifact->getOpenDate());;
			}
			echo $HTML->multiTableRow(array('class' => 'priority'.$artifact->getPriority()), $cells);
		}
	}
	$ptfu = new ProjectTasksForUser($member);
	$tasks = $ptfu->getTasksByGroupProjectName();
	foreach ($tasks as $task) {
		if (!$task->isError() && $task->ProjectGroup->Group->getID() == $group_id && $task->getPercentComplete() != 100) {
			$cells = array();
			$cells[][] = util_make_link('/pm/task.php?func=detailtask&project_task_id='. $task->getID().'&group_id='.$task->ProjectGroup->Group->getID().'&group_project_id='.$task->ProjectGroup->getID(),_('Task').' '.$task->getID());
			$cells[][] = $task->getSummary();
			$cells[][] = GetTime(time()-$task->getStartDate());
			$cells[][] = $task->getPercentComplete().'% '._('done');
			echo $HTML->multiTableRow(array('class' => 'priority'.$task->getPriority()), $cells);
		}
	}
	$cells = array();
	$cells[] = array('', 'colspan' => 4);
	echo $HTML->multiTableRow(array(), $cells);
}

echo $HTML->listTableBottom();

site_project_footer();

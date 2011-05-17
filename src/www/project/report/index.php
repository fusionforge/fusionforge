<?php
/**
 * Project Members Information
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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


require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
#require_once('common/tracker/ArtifactGroup.class.php');

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

echo '<p>';
echo _('If you would like to contribute to this project by becoming a developer, contact one of the project admins, designated in bold text below.');
echo '</p>';

        $title_arr=array();
        $title_arr[]=_('Developer');
        $title_arr[]=_('Summary');
        $title_arr[]=_('Open Date');
        $title_arr[]=_('Last Modified');
echo $GLOBALS['HTML']->listTableTop ($title_arr);

// list members
foreach ($group->getUsers() as $member) {
	echo '
		<tr><td>';
	$link = util_make_link_u ($member->getUnixName(), $member->getID(), $member->getRealName()) ;
	if ( RBACEngine::getInstance()->isActionAllowedForUser($member,'project_admin',$group->getID())) {
		echo '<strong>'.$link.'</strong>' ;
	} else {
		echo $link ;
	}
	echo '
			</td><td>'.
		util_make_link ('/sendmessage.php?touser='.$member->getId(),
				sprintf (_('Contact %s'),$member->getRealName())).'
			</td>';
	if (USE_PFO_RBAC) {
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($member) ;
		sortRoleList ($roles) ;
		$role_names = array () ;
		foreach ($roles as $role) {
			if ($role->getHomeProject() && $role->getHomeProject()->getID() == $group->getID()) {
				$role_names[] = $role->getName() ;
			}
		}
		$role_string = implode (', ', $role_names) ;
	} else {
		$role_string = $user->getRole ($group)->getName() ;
	}

				echo '
			<td align="center">'.$role_string.'
			</td>';
	if(forge_get_config('use_people')) {
		echo '
			<td align="center">'.util_make_link('/people/viewprofile.php?user_id='.$member->getID(),_('View')).'
			</td>';
	}
	echo '
		</tr>';

	// print out all the artifacts assigned to this person 
        $artifact_group=db_query_params("SELECT group_artifact_id, name
                                  FROM artifact_group_list
                                  WHERE group_id=$1
                                  ORDER BY group_artifact_id DESC", array($group_id));

 	while ( $artifact_type =db_fetch_array($artifact_group) ) {
		$artifacts=db_query_params("SELECT * FROM artifact_vw
                                     WHERE assigned_to=$1
                                       AND status_id='1'
                                       AND group_artifact_id=$2
                                     ORDER BY priority DESC", array($member->getID(), $artifact_type['group_artifact_id']));

                $num_artifacts=db_numrows($artifacts);
                for ($m=0; $m < $num_artifacts; $m++) {
			echo '
		<tr class="priority'.db_result($artifacts, $m, 'priority').'">
			<td>'.util_make_link ('/tracker/?func=detail&amp;aid='. db_result($artifacts, $m, 'artifact_id') .'&amp;group_id='.$group_id.'&atid='.$artifact_type['group_artifact_id'], $artifact_type['name'].' '.db_result($artifacts, $m, 'artifact_id')).'
			</td>
			<td>'.db_result($artifacts, $m, 'summary').'</td>';
			echo '
			<td>'.GetTime( time() - db_result($artifacts, $m, 'open_date'))	.'
			</td>';

			$messages=db_query_params("select adddate FROM artifact_message_user_vw ".
						"WHERE artifact_id=$1 ".
						"ORDER by adddate DESC", array(db_result($artifacts, $m, 'artifact_id')));
			if ( db_numrows($messages)) {
				echo '
			<td>'. GetTime( time () - db_result($messages, 0, 'adddate')).'</td>';
			} else {
				echo '
			<td>'. GetTime( time () - db_result($artifacts, $m, 'open_date')).'</td>';
			}
			echo '
		</tr>';
                }
	}
	$task_group=db_query_params("SELECT ptv.*,g.group_name,pgl.project_name
                        FROM project_task_vw ptv,
                                project_assigned_to pat,
                                groups g,
                                project_group_list pgl
                        WHERE ptv.project_task_id=pat.project_task_id
                                AND pgl.group_id=$1
                                AND g.group_id=$1
                                AND pgl.group_project_id=ptv.group_project_id
                                AND ptv.status_id=1
                                AND pat.assigned_to_id=$2
                        ORDER BY group_name,project_name",
                        array($group_id, $member->getID()));

	while ( $task_type = db_fetch_array($task_group) ) {
		if ( $task_type['percent_complete'] != 100 ) {
                	echo '
		<tr class="priority'.$task_type['priority'].'">
			<td>'.util_make_link ('/pm/task.php?func=detailtask&amp;project_task_id='. $task_type['project_task_id'].'&amp;group_id='.$group_id.'&amp;group_project_id='.$task_type['group_project_id'],_('Task').' '.$task_type['project_task_id']).'
			</td>
			<td>'.$task_type['summary'].'
			</td>
			<td>'.GetTime(time()-$task_type['start_date']).'
			</td>
			<td>'.$task_type['percent_complete'].'% done'.'
			</td>
		</tr>';
		}
	}
	echo '
		<tr>
			<td><br /></td>
		</tr>';
}

echo $GLOBALS['HTML']->listTableBottom();

site_project_footer(array());

?>

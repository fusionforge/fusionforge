<?php
/**
 * Project Members Information
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA  */


require_once('../../env.inc.php');
require_once('pre.php');
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
//      get the Group object
//
$group_id = getIntFromRequest('group_id');

$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_no_group();
}
if ($group->isError()) {
        if($group->isPermissionDeniedError()) {
                exit_permission_denied($group->getErrorMessage());
        } else {
                exit_error(_('Error'), $group->getErrorMessage());
        }
}


if (!$group_id && $form_grp) {
	$group_id = $form_grp;
}

site_project_header(array('title'=>_('Project Member List'),'group'=>$group_id,'toptab'
=>'memberlist'));

echo _('<p>If you would like to contribute to this project by becoming a developer, contact one of the project admins, designated in bold text below.</p>');

        $title_arr=array();
        $title_arr[]=_('Developer');
        $title_arr[]=_('Summary');
        $title_arr[]=_('Open Date');
        $title_arr[]=_('Last Modified');
echo $GLOBALS['HTML']->listTableTop ($title_arr);


// list members
$query = "SELECT users.*,user_group.admin_flags,people_job_category.name AS role
	FROM users,user_group 
	LEFT JOIN people_job_category ON user_group.member_role=people_job_category.category_id
	WHERE users.user_id=user_group.user_id 
	AND user_group.group_id='$group_id' 
	AND users.status='A'
	ORDER BY users.user_name ";

$res_memb = db_query($query);

while ( $row_memb=db_fetch_array($res_memb) ) {
	echo "\n\n\t\t<tr>";
	if ( trim($row_memb['admin_flags'])=='A' ) {
		echo "\n\t\t<td><strong>".$row_memb['realname']."</strong></td>\n";
	} else {
		echo "\n\t\t<td>".$row_memb['realname']."</td>\n";
	}
	echo "\t\t<td><a href=\"".$GLOBALS['sys_urlprefix']."/sendmessage.php?touser=".$row_memb['user_id']."\">" . _('Contact') . "
</a>".
		"<a href=\"".$GLOBALS['sys_urlprefix']."/users/".
		$row_memb['user_name']."/\">".$row_memb['user_name']."</a></td>
		<td align=\"center\">".$row_memb['role']."</td>\n";
	if($GLOBALS['sys_use_people']) {
		echo "\t\t<td align=\"center\"><a href=\"".$GLOBALS['sys_urlprefix']."/people/viewprofile.php?user_id=".
			$row_memb['user_id']."\">"._('View').
			"</a></td>";
	}
	echo "</tr>\n";

	// print out all the artifacts assigned to this person 
        $artifact_group=db_query("SELECT group_artifact_id, name
                                  FROM artifact_group_list
                                  WHERE group_id=".$group_id."
                                  ORDER BY group_artifact_id DESC");

 	while ( $artifact_type =db_fetch_array($artifact_group) ) {
		$artifacts=db_query("SELECT * FROM artifact_vw
                                     WHERE assigned_to=".$row_memb['user_id']."
                                       AND status_id='1'
                                       AND group_artifact_id=".$artifact_type['group_artifact_id']."
                                     ORDER BY priority DESC");

                $num_artifacts=db_numrows($artifacts);
                for ($m=0; $m < $num_artifacts; $m++) {
			echo "\t\t";
			echo '<tr class="priority'.db_result($artifacts, $m, 'priority').'">';
			echo "\n\t\t";
			echo '<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?func=detail&amp;aid='.
				db_result($artifacts, $m, 'artifact_id').
				'&amp;group_id='.$group_id.
				'&atid='.$artifact_type['group_artifact_id'].
				'">'.$artifact_type['name'].' '.db_result($artifacts, $m, 'artifact_id').
				'</a></td>';
			echo "\n\t\t";
			echo '<td>'.db_result($artifacts, $m, 'summary').'</td>';
			echo "\n\t\t";
		
			echo '<td>'.GetTime( time() - db_result($artifacts, $m, 'open_date'))	.'</td>';
		#	echo '<td>'. date($sys_datefmt,db_result($artifacts, $m, 'open_date')).'</td>';

			$messages=db_query("select adddate FROM artifact_message_user_vw ".
						"WHERE artifact_id='".db_result($artifacts, $m, 'artifact_id')."' ".
						"ORDER by adddate DESC");
			if ( db_numrows($messages)) {
				echo '<td>'. GetTime( time () - db_result($messages, 0, 'adddate')).'</td>';
			} else {
				echo '<td>'. GetTime( time () - db_result($artifacts, $m, 'open_date')).'</td>';
			}
			echo "</tr>\n";
                }
	}
	$task_group=db_query("SELECT ptv.*,g.group_name,pgl.project_name
                        FROM project_task_vw ptv,
                                project_assigned_to pat,
                                groups g,
                                project_group_list pgl
                        WHERE ptv.project_task_id=pat.project_task_id
                                AND pgl.group_id=".$group_id."
				AND g.group_id=".$group_id."
                                AND pgl.group_project_id=ptv.group_project_id
                                AND ptv.status_id=1
                                AND pat.assigned_to_id='".$row_memb['user_id']."'
                        ORDER BY group_name,project_name");

	while ( $task_type = db_fetch_array($task_group) ) {
		if ( $task_type['percent_complete'] != 100 ) {
                	echo '<tr class="priority'.$task_type['priority'].'">';

			echo '<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?func=detailtask&amp;project_task_id='.
				$task_type['project_task_id'].
				'&amp;group_id='.$group_id.
				'&amp;group_project_id='.$task_type['group_project_id'].
				'">Task '.$task_type['project_task_id'].'</a></td>';
			echo '<td>'.$task_type['summary'].'</td>';
			echo "\n\t\t";
			echo '<td>'.GetTime(time()-$task_type['start_date']).'</td>';
			echo '<td>'.$task_type['percent_complete'].'% done</td>';
			echo '</tr>';
		}
	}
	echo "<tr><td><BR></td></tr>";
}

echo $HTML->boxBottom();

site_project_footer(array());

?>

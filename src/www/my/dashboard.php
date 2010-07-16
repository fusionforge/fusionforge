<?php
/**
 * FusionForge User's Personal Page
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge Team
 * Copyright 2009, Jean-Pierre Fortune/Spirtech
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'forum/ForumsForUser.class.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';

if (!session_loggedin()) {
	exit_not_logged_in();
} else {
	echo site_user_header(array('title'=>sprintf(_('Personal Page For %s'),user_getname())));
	?>

	<table width="100%" border="0">
	<tr><td valign="top" width="100%">
	<?php

		 echo $HTML->boxTop(_('All trackers for my projects'),false,false);
	// Include both groups and foundries; developers should be similarly
	// aware of membership in either.
	$result = db_query_params ('SELECT groups.group_name,
groups.group_id,
groups.unix_group_name,
groups.status,
groups.type_id,
user_group.admin_flags 
FROM groups,user_group 
WHERE groups.group_id=user_group.group_id 
AND user_group.user_id=$1 
AND groups.status=$2 
ORDER BY group_name',
			array(user_getid() ,
				'A'));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<strong>'._('You\'re not a member of any active projects').'</strong>';
		echo db_error();
	} else {
		$display_col=array('summary'=>1,
				'changed'=>1,
				'status'=>0,
				'priority'=>1,
				'assigned_to'=>1,
				'submitted_by'=>1,
				'related_tasks'=>1);

		$title_arr=array();

		$title_arr[]=_('ID');
		if ($display_col['summary'])
			$title_arr[]=_('Summary');
		if ($display_col['changed'])
			$title_arr[]=_('Changed');
		if ($display_col['status'])
			$title_arr[]=_('Status');
		if ($display_col['priority'])
			$title_arr[]=_('Priority');
		if ($display_col['assigned_to'])
			$title_arr[]=_('Assigned to');
		if ($display_col['submitted_by'])
			$title_arr[]=_('Submitted by');
		if ($display_col['related_tasks'])
			$title_arr[]=_('Tasks');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

                for ($i=0; $i<$rows; $i++) {
                        $admin_flags = db_result($result, $i, 'admin_flags');

                        if (db_result($result, $i, 'type_id')==2) {
                                $type = 'foundry';
                        } else {
                                $type = 'projects';
                        }

                        $group_id = db_result($result,$i,'group_id');

                        //  get the Group object
                        //
                        $group =& group_get_object($group_id);
                        if (!$group || !is_object($group) || $group->isError()) {
                                exit_no_group();
                        }

                        $atf = new ArtifactTypeFactory($group);
                        if (!$group || !is_object($group) || $group->isError()) {
                                exit_error('Error','Could Not Get ArtifactTypeFactory');
                        }

                        $at_arr =& $atf->getArtifactTypes();

			$art_found = 0;
			
			if(count($at_arr) > 0) {
 	                       	echo '
        	                <tr>
                	        <td colspan="' . (array_sum($display_col)+1) . '" align="left" style="background-color: #CADACA; padding-top: 4px; border-top: 1px dotted darkgreen; border-bottom: 1px solid darkgreen; font-size: larger; color: darkgreen;"><strong>&nbsp;&bull;&nbsp;' .
					util_make_link ('/tracker/?group_id='.$group->getID(),
							$group->getPublicName())
					. '</strong></td></tr>';
                        	foreach($at_arr as $at) {
					$art_found = 1;
	                                //
        	                        //      Create the ArtifactType object
                	                //
                        	        $ath = new ArtifactTypeHtml($group,$at->getID());
	                                // create artifact object, setup object
        	                        $af = new ArtifactFactory($ath);
                	                $af->setup(0,"priority","DESC",0,"",0,1,null);
                        	        // get artifacts from object
                                	$art_arr =& $af->getArtifacts();
	                                if (count($art_arr) > 0) {
        	                                echo '<tr><td colspan="' . (array_sum($display_col)+1) . '" align="left" style="color: darkred; border-bottom: 1px solid #A0A0C0; border-top: 1px dotted #A0A0C0; background-color: #CACADA;"><strong>&nbsp;&middot;&nbsp;'. 
							util_make_link ('/tracker/?group_id='.$at->Group->getID().'&atid='.$at->getID(),
									$at->getName()) . '</strong></td></tr>';
                	                        $toggle=0;
                        	                foreach($art_arr as $art) {
                                	                echo '<tr '. $HTML->boxGetAltRowStyle($toggle++) . ' valign="top"><td align="center">'. $art->getID() .'</td>';
                                        	        if ($display_col['summary'])
                                                	echo '<td align="left"><a href="/tracker/?func=detail&aid='.
	                                                        $art->getID() .
        	                                                '&group_id='. $group_id .'&atid='.
                	                                        $ath->getID().'">'.
                        	                                $art->getSummary().
                                	                        '</a></td>';
                                        	        if ($display_col['changed'])
                                                	        echo '<td width="150">'
                                                        	        .date(_('Y-m-d'),$art->getLastModifiedDate()) .'</td>';
	                                                if ($display_col['status'])
        	                                                echo '<td>'. $art->getStatusName() .'</td>';
                	                                if ($display_col['priority'])
								echo '<td class="priority'.$art->getPriority() .'" align="center">'. $art->getPriority() .'</td>';
                                	                if ($display_col['assigned_to'])
                                        	                echo '<td>'. $art->getAssignedRealName() .'</td>';
                                                	if ($display_col['submitted_by'])
                                                        	echo '<td>'. $art->getSubmittedRealName() .'</td>';
                                                	if ($display_col['related_tasks']) {
								$result_tasks = $art->getRelatedTasks();
								if($result_tasks) {
									$taskcount = db_numrows($art->relatedtasks);
                                                                	echo '<td>';
								        if ($taskcount > 0) {
    										for ($itask = 0; $itask < $taskcount; $itask++) {
											if($itask>0)
												echo '<br/>';
											$taskinfo = db_fetch_array($art->relatedtasks, $itask);
               										$taskid = $taskinfo['project_task_id']; 
								                	$projectid = $taskinfo['group_project_id'];
									        	$groupid   = $taskinfo['group_id'];
											$g =& group_get_object($groupid);
											$pg = new ProjectGroup($g, $projectid, $arrtasks);
											echo $pg->getName().'<br/>';
     										        $summary   = util_unconvert_htmlspecialchars($taskinfo['summary']);
											echo '<a href="../pm/task.php?func=detailtask&project_task_id='.$taskid.'&group_id='.$groupid.'&group_project_id='.$projectid.'">';
											echo $summary;
											echo '</a>';
										}
									}
                                                                	echo '</td>';
								}
							}
                                                	echo '</tr>';
                                        	}
					}
				}
				if (!$art_found) {
					echo '
        		        	<tr>
                		        <td colspan="' . (array_sum($display_col)+1) . '" align="left"><strong>&nbsp;--</strong></td></tr>';
				}
			}
		}
                echo $GLOBALS['HTML']->listTableBottom();
	}

	echo $HTML->boxBottom();

//second column of "my" page

	echo '</td><td valign="top"></td></tr>


	<!--  Bottom Row   -->
<!--
	<tr><td colspan="2">';
	echo show_priority_colors_key();
	echo '
	</td></tr>
-->
	</table>';

	echo site_user_footer(array());

}

?>

<?php
/**
 * GForge User's Personal Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
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

require_once('pre.php');
require_once('vote_function.php');
require_once('common/tracker/ArtifactsForUser.class');
require_once('common/forum/ForumsForUser.class');
require_once('common/pm/ProjectTasksForUser.class');

if (!session_loggedin()) { // || $sf_user_hash) {

	exit_not_logged_in();

} else {


	/*
//needs security audit
	 *  If user has valid "remember-me" hash, instantiate not-logged in
	 *  session for one.
	 * /
	if (!session_loggedin()) {
			list($user_id,$hash)=explode('_',$sf_user_hash);
			$sql="SELECT *
			FROM users
			WHERE user_id='".$user_id."' AND user_pw LIKE '".$hash."%'";

		$result=db_query($sql);
		$rows=db_numrows($result);
		if (!$result || $rows != 1) {
			exit_not_logged_in();
		}
		$user_id=db_result($result,0,'user_id');
		session_get_user()=user_get_object($user_id,$result);
	}
*/
	echo site_user_header(array('title'=>$Language->getText('my','title',user_getname())));
	?>

	<p>
    <?php echo $Language->getText('my', 'about_blurb'); ?>
	</p>
	<table width="100%" border="0">
	<tr><td valign="top" width="50%">
	<?php
	/*
		Artifacts
	*/
	$last_group=0;
	echo $HTML->boxTop($Language->getText('my', 'assigneditems'));
	$artifactsForUser = new ArtifactsForUser(session_get_user());
	$assignedArtifacts =& $artifactsForUser->getAssignedArtifactsByGroup();
	if (count($assignedArtifacts) > 0) {
		foreach($assignedArtifacts as $art) {
			if($art->ArtifactType->Group->getStatus() == 'A') {
				echo '</td></tr>';
				if ($art->ArtifactType->getID() != $last_group) {
					echo '
					<tr><td colspan="2"><strong><a href="/tracker/?group_id='.
					$art->ArtifactType->Group->getID().'&atid='.
					$art->ArtifactType->getID().'">'.
					$art->ArtifactType->Group->getPublicName().' - '.
					$art->ArtifactType->getName().'</a></strong></td></tr>';
	
				}
				echo '
				<tr style="background-color:'.html_get_priority_color($art->getPriority()).'">
				<td width="10%">'.$art->getID().'</td>
				<td><a href="/tracker/?func=detail&amp;aid='.
				$art->getID().
				'&amp;group_id='.$art->ArtifactType->Group->getID().
				'&amp;atid='.$art->ArtifactType->getID().'">' . $art->getSummary() . '</a>';
	
				$last_group = $art->ArtifactType->getID();
			}
		}
	}
	if($last_group == '0') {
		echo '
			<strong>'.$Language->getText('my', 'no_tracker_items_assigned').'</strong>';
	}

	$last_group="0";
	echo $HTML->boxMiddle($Language->getText('my', 'submitteditems'),false,false);
	$submittedArtifacts =& $artifactsForUser->getSubmittedArtifactsByGroup();
	if (count($submittedArtifacts) > 0) {
		foreach ($submittedArtifacts as $art) {
			if($art->ArtifactType->Group->getStatus() == 'A') {
				echo '</td></tr>';
				if ($art->ArtifactType->getID() != $last_group) {
					echo '
					<tr><td colspan="2"><strong><a href="/tracker/?group_id='.
					$art->ArtifactType->Group->getID().'&atid='.
					$art->ArtifactType->getID().'">'.
					$art->ArtifactType->Group->getPublicName().' - '.
					$art->ArtifactType->getName().'</a></strong></td></tr>';
				}
				echo '
				<tr style="background-color:'.html_get_priority_color($art->getPriority()).'">
				<td width="10%">'.$art->getID().'</td>
				<td><a href="/tracker/?func=detail&amp;aid='.
	      $art->getID().
	      '&amp;group_id='.$art->ArtifactType->Group->getID().
	      '&amp;atid='.$art->ArtifactType->getID().'">' . $art->getSummary() .'</a>';
	
				$last_group = $art->ArtifactType->getID();
			}
		}
	}
	if($last_group == '0') {
		echo '
		<strong>'.$Language->getText('my', 'no_tracker_items_submitted').'</strong>';
	}

	/*
		Forums that are actively monitored
	*/
	$last_group=0;
	echo $HTML->boxMiddle($Language->getText('my', 'monitoredforum'),false,false);
	$forumsForUser = new ForumsForUser(session_get_user());
	$forums =& $forumsForUser->getMonitoredForums();
	if (count($forums) < 1) {
		echo '<strong>'.$Language->getText('my', 'no_monitored_forums').'</strong>'.$Language->getText('my', 'no_monitored_forums_details');
	} else {
		foreach ($forums as $f) {
			echo '</td></tr>';
			$group = $f->getGroup();
			if ($group->getID() != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2"><strong><a href="/forum/?group_id='.
				$group->getID().'">'.
				$group->getPublicName().'</a></strong></td></tr';
			}

			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center"><a href="/forum/monitor.php?forum_id='.$f->getID().
			'&amp;stop=1&amp;group_id='.$group->getID().'"><img src="'. $HTML->imgroot . '/ic/trash.png" height="16" width="16" '.
			'border="0" alt="" /></a></td><td width="99%"><a href="/forum/forum.php?forum_id='.
			$f->getID().'">'.
			$f->getName().'</a>';

			$last_group= $group->getID();
		}
	}

	/*
		Filemodules that are actively monitored
	*/
	$last_group=0;

	echo $HTML->boxMiddle($Language->getText('my', 'monitoredfile'),false,false);

	$sql="SELECT groups.group_name,groups.group_id,frs_package.name,filemodule_monitor.filemodule_id ".
		"FROM groups,filemodule_monitor,frs_package ".
		"WHERE groups.group_id=frs_package.group_id AND groups.status = 'A' ".
		"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
		"AND filemodule_monitor.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<strong>'.$Language->getText('my', 'no_monitored_filemodules').'</strong>'.$Language->getText('my', 'no_monitored_filemodules_details');
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '</td></tr>';
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2"><strong><a href="/project/?group_id='.
				db_result($result,$i,'group_id').'">'.
				db_result($result,$i,'group_name').'</a></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center"><a href="/frs/monitor.php?filemodule_id='.
			db_result($result,$i,'filemodule_id').
			'&amp;group_id='.db_result($result,$i,'group_id'). '&amp;stop=1"><img src="'. $HTML->imgroot.'/ic/trash.png" height="16" width="16" '.
			'BORDER=0"></a></td><td width="99%"><a href="/frs/?group_id='.
			db_result($result,$i,'group_id').'">'.
			db_result($result,$i,'name').'</a>';

			$last_group=db_result($result,$i,'group_id');
		}
	}

	echo $HTML->boxBottom();

//second column of "my" page

	?>
	</td><td valign="top" width="50%">
	<?php
	/*
		Tasks assigned to me
	*/
	$last_group=0;
	echo $HTML->boxTop($Language->getText('my', 'tasks'));
	//echo "<a href=. onclick=\"window.open('/pm/ganttofuser.php')\"><strong>".$Language->getText('pm_include_grouphtml','gantt_chart')."</strong></a>";
	$projectTasksForUser = new ProjectTasksForUser(session_get_user());
	$userTasks =& $projectTasksForUser->getTasksByGroupProjectName();

	if (count($userTasks) > 0) {
		foreach ($userTasks as $task) {
			echo '</td></tr>';
			/* Deduce summary style */
			$style_begin='';
			$style_end='';
			if ($task->getPercentComplete()==100) {
				$style_begin='<span style="text-decoration:underline">';
				$style_end='</span>';
			}
			//if ($task->getProjectGroup()->getID() != $last_group) {
			$projectGroup =& $task->getProjectGroup();
			$group =& $projectGroup->getGroup();
			if ($projectGroup->getID() != $last_group) {
				echo '
				<tr><td colspan="2"><strong><a href="/pm/task.php?group_id='.
				$group->getID().
				'&amp;group_project_id='.
				$projectGroup->getID().'">'.
				$group->getPublicName().' - '.
				$projectGroup->getName().'</a></strong></td></tr>';
			}
			echo '
			<tr style="background-color:'.html_get_priority_color($task->getPriority()).'">
			<td width="10%">'.$task->getID().'</td>
			<td><a href="/pm/task.php?func=detailtask&amp;project_task_id='.
			$task->getID().
			'&amp;group_id='.$group->getID().
			'&amp;group_project_id='.$projectGroup->getID().'">'.$style_begin.$task->getSummary().$style_end.'</a>';

			$last_group = $projectGroup->getID();
		}
	} else {
		echo '
		<strong>'.$Language->getText('my', 'no_open_tasks').'</strong>';
		echo db_error();
	}


	/*
		DEVELOPER SURVEYS

		This needs to be updated manually to display any given survey
	*/

	$sql="SELECT * from survey_responses ".
		"WHERE survey_id='1' AND user_id='".user_getid()."' AND group_id='1'";

	$result=db_query($sql);

	echo $HTML->boxMiddle($Language->getText('my', 'survey'),false,false);

	if (db_numrows($result) < 1) {
		show_survey(1,1);
	} else {
		echo '
		<strong>'.$Language->getText('my','survey_taken').'</strong>';
	}

	/*
	 * Pending projects and news bytes
	 */
	$admingroup = group_get_object (1) ;
	if (!$admingroup || !is_object($admingroup)) {
		//don't have perm to see this
	} elseif ($admingroup->isError()) {
		//don't have perm to see this
	} else {
		$perm =& $admingroup->getPermission( session_get_user() );
		if ($perm && is_object($perm) && $perm->isAdmin()) {
			$sql="SELECT group_name FROM groups where status='P';";
			$result=db_query($sql);
			$rows=db_numrows($result);
			if ($rows) {
				echo $HTML->boxMiddle($Language->getText('my','pending_projects'), false, false);

				if ($rows==1){
					echo $Language->getText('my','pending_projects_1');
				} else {
					echo $Language->getText('my','pending_projects_2',$rows);
				}
			
				echo " <a href=\"/admin/approve-pending.php\">";
				echo $Language->getText('my','pending_projects_3');
				echo "</a>.";
			}
		}
	}
	$newsgroup = group_get_object ($GLOBALS['sys_news_group']) ;
	if (!$newsgroup || !is_object($newsgroup)) {
		//don't have perm to see this
	} elseif ($newsgroup->isError()) {
		//don't have perm to see this
	} else {
		$perm =& $newsgroup->getPermission( session_get_user() );
		if ($perm && is_object($perm) && $perm->isAdmin()) {
			$sql="SELECT * FROM news_bytes nb, groups g WHERE nb.is_approved=0 and nb.group_id = g.group_id and g.status = 'A'";
			$result=db_query($sql);
			$rows=db_numrows($result);
			if ($rows) {
				echo $HTML->boxMiddle($Language->getText('my','pending_news_bytes'), false, false);

				if ($rows==1){
					echo $Language->getText('my','pending_news_bytes_1');
				} else{
					echo $Language->getText('my','pending_news_bytes_2',$rows);
				}

				echo " <a href=\"/news/admin/?group_id=".$GLOBALS['sys_news_group']."\">";

				echo $Language->getText('my','pending_news_bytes_3');
				echo "</a>.";
			}
		}
	}
	/*
		   Personal bookmarks
	*/
	echo $HTML->boxMiddle($Language->getText('my', 'bookmarks'),false,false);

	$result = db_query("SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where ".
		"user_id='". user_getid() ."' ORDER BY bookmark_title");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<strong>'.$Language->getText('my', 'no_bookmarks').'</strong>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '</td></tr>
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center">
			<a href="/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">
			<img src="'.$HTML->imgroot.'/ic/trash.png" height="16" width="16" border="0" alt="" /></a></td>
			<td><strong><a href="'. db_result($result,$i,'bookmark_url') .'">'.
			db_result($result,$i,'bookmark_title') .'</a></strong> &nbsp;
			<span style="font-size:small"><a href="/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">['.$Language->getText('general','edit').']</a></span>';
		}
	}

	/*
		PROJECT LIST
	*/

	echo $HTML->boxMiddle($Language->getText('my', 'projects'),false,false);
	// Include both groups and foundries; developers should be similarly
	// aware of membership in either.
	$result = db_query("SELECT groups.group_name,"
		. "groups.group_id,"
		. "groups.unix_group_name,"
		. "groups.status,"
		. "groups.type_id,"
		. "user_group.admin_flags "
		. "FROM groups,user_group "
		. "WHERE groups.group_id=user_group.group_id "
		. "AND user_group.user_id='". user_getid() ."' "
		. "AND groups.status='A' "
		. "ORDER BY group_name");
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<strong>'.$Language->getText('my', 'no_projects').'</strong>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '</td></tr>';
			$admin_flags = db_result($result, $i, 'admin_flags');
			if (stristr($admin_flags, 'A')) {
				$img="trash-x.png";
			} else {
				$img="trash.png";
			}

			if (db_result($result, $i, 'type_id')==2) {
				$type = 'foundry';
			} else {
				$type = 'projects';
			}

			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td align="center">
			<a href="rmproject.php?group_id='. db_result($result,$i,'group_id') .'">
			<img src="'.$HTML->imgroot.'ic/'.$img.'" alt="Delete" height="16" width="16" border="0" /></a></td>
			<td><a href="/'.$type.'/'. db_result($result,$i,'unix_group_name') .'/">'. htmlspecialchars(db_result($result,$i,'group_name')) .'</a>';
		}
	}
	echo $HTML->boxBottom();

	echo '</td></tr>


	<!--  Bottom Row   -->


	<tr><td colspan="2">';

	echo show_priority_colors_key();

	echo '
	</td></tr>

	</table>';

	echo site_user_footer(array());

}

?>

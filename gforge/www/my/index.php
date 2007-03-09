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

require_once('../env.inc.php');
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
		if (!$result || $rows != 1) { exit_not_logged_in();
		}
		$user_id=db_result($result,0,'user_id');
		session_get_user()=user_get_object($user_id,$result);
	}
*/
	echo site_user_header(array('title'=>$Language->getText('my','title',user_getname())));
	$tabcnt=0;	
	?>
<script type="text/javascript" src="<?php echo $GLOBALS['sys_urlprefix']; ?>/tabber/tabber.js"></script>
<div id="tabber" class="tabber" <? plugin_hook('call_user_js');?>>
<div class="tabbertab" 
title="<?php echo $Language->getText('my','assignedartifacts'); ?>">
	<?php
	/*
		Artifacts
	*/
	$last_group=0;
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('tracker','id');
	$order_name_arr[]=$Language->getText('tracker','priority');
	$order_name_arr[]=$Language->getText('tracker','summary');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);

	$artifactsForUser = new ArtifactsForUser(session_get_user());
	$assignedArtifacts =& $artifactsForUser->getAssignedArtifactsByGroup();
	if (count($assignedArtifacts) > 0) {
		$i=0;
		foreach($assignedArtifacts as $art) {
			if ($art->ArtifactType->getID() != $last_group) {
				echo '
				<tr><td colspan="3" class="tablecontent"><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?group_id='.
				$art->ArtifactType->Group->getID().'&amp;atid='.
				$art->ArtifactType->getID().'">'.
				$art->ArtifactType->Group->getPublicName().' - '.
				$art->ArtifactType->getName().'</a></td></tr>';

			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$art->getID().'</td>
			<td width="10%" class="priority'.$art->getPriority().'">'.$art->getPriority().'</td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?func=detail&amp;aid='.
			$art->getID().
			'&amp;group_id='.$art->ArtifactType->Group->getID().
			'&amp;atid='.$art->ArtifactType->getID().'">' . $art->getSummary() . '</a>';
			echo '</td></tr>';

			$last_group = $art->ArtifactType->getID();
		}
	} else {
		echo '
			<tr><td colspan="3">'.$Language->getText('my', 'no_tracker_items_assigned').'</td></tr>';
	}
	echo $HTML->listTableBottom();
?>
</div>
  
<div class="tabbertab" 
title="<?php echo $Language->getText('my','assignedtasks'); ?>">
<?php
	/*
		Tasks assigned to me
	*/
	$tabcnt++;
	$last_group=0;
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('tracker','id');
	$order_name_arr[]=$Language->getText('tracker','priority');
	$order_name_arr[]=$Language->getText('tracker','summary');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);
	$projectTasksForUser = new ProjectTasksForUser(session_get_user());
	$userTasks =& $projectTasksForUser->getTasksByGroupProjectName();

	if (count($userTasks) > 0) {
		$i=0;
		foreach ($userTasks as $task) {
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
				<tr><td colspan="3" class="tablecontent"><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?group_id='.
				$group->getID().
				'&amp;group_project_id='.
				$projectGroup->getID().'">'.
				$group->getPublicName().' - '.
				$projectGroup->getName().'</a></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$task->getID().'</td>
			<td width="10%" class="priority'.$task->getPriority().'">'.$task->getPriority().'</td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/pm/task.php?func=detailtask&amp;project_task_id='.
			$task->getID().
			'&amp;group_id='.$group->getID().
			'&amp;group_project_id='.$projectGroup->getID().'">'.$style_begin.$task->getSummary().$style_end.'</a>';
			echo '</td></tr>';

			$last_group = $projectGroup->getID();
		}
	} else {
		echo '
		<tr><td colspan="3" class="tablecontent">'.$Language->getText('my', 'no_open_tasks').'</td></tr>';
		echo db_error();
	}
	echo $HTML->listTableBottom();
?>
</div>
<div class="tabbertab" 
title="<?php echo $Language->getText('my','submittedartifacts'); ?>">
<?php
	$tabcnt++;
	$last_group="0";
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('tracker','id');
	$order_name_arr[]=$Language->getText('tracker','priority');
	$order_name_arr[]=$Language->getText('tracker','summary');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);
	$submittedArtifacts =& $artifactsForUser->getSubmittedArtifactsByGroup();
	if (count($submittedArtifacts) > 0) {
		$i=0;
		foreach ($submittedArtifacts as $art) {
			if ($art->ArtifactType->getID() != $last_group) {
				echo '
				<tr><td colspan="3" class="tablecontent"><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?group_id='.
				$art->ArtifactType->Group->getID().'&amp;atid='.
				$art->ArtifactType->getID().'">'.
				$art->ArtifactType->Group->getPublicName().' - '.
				$art->ArtifactType->getName().'</a></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$art->getID().'</td>
			<td width="10%" class="priority'.$art->getPriority().'">'.$art->getPriority().'</td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?func=detail&amp;aid='.
      $art->getID().
      '&amp;group_id='.$art->ArtifactType->Group->getID().
      '&amp;atid='.$art->ArtifactType->getID().'">' . $art->getSummary() .'</a>';
			echo '</td></tr>';

			$last_group = $art->ArtifactType->getID();
		}
	} else {
		echo '
		<tr><td colspan="3" class="tablecontent">'.$Language->getText('my', 'no_tracker_items_submitted').'</td></tr>';
	}
	echo $HTML->listTableBottom();
?>
</div>
<div class="tabbertab" title="<?php echo $Language->getText('mytab','monitoring'); ?>" >
<?php
	/*
		Forums that are actively monitored
	*/
	$tabcnt++;
	$last_group=0;
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('general','remove');
	$order_name_arr[]=$Language->getText('my','monitoredforum');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);
	$forumsForUser = new ForumsForUser(session_get_user());
	$forums =& $forumsForUser->getMonitoredForums();
	if (count($forums) < 1) {
		echo '<tr><td colspan="2" bgcolor="#FFFFFF"><center><strong>'.$Language->getText('my', 'no_monitored_forums').'</strong></center></td></tr>';
	} else {
		echo '<tr><td colspan="2" bgcolor="#FFFFFF"><center><strong><a href="'.$GLOBALS['sys_urlprefix'].'/forum/myforums.php">' . $Language->getText('forum_myforums','myforums') . '</a></strong></center></td></tr>';
		foreach ($forums as $f) {
			$group = $f->getGroup();
			if ($group->getID() != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle(1) .'><td colspan="2"><a href="'.$GLOBALS['sys_urlprefix'].'/forum/?group_id='.
				$group->getID().'">'.
				$group->getPublicName().'</a></td></tr>';
			}

			echo '
			<tr '. $HTML->boxGetAltRowStyle(0) .'><td align="center"><a href="'.$GLOBALS['sys_urlprefix'].'/forum/monitor.php?forum_id='.$f->getID().
			'&amp;stop=1&amp;group_id='.$group->getID().'"><img src="'. $HTML->imgroot . '/ic/trash.png" height="16" width="16" '.
			'border="0" alt="" /></a></td><td width="99%"><a href="'.$GLOBALS['sys_urlprefix'].'/forum/forum.php?forum_id='.
			$f->getID().'">'.
			$f->getName().'</a></td></tr>';

			$last_group= $group->getID();
		}
	}
	echo $HTML->listTableBottom();

	/*
		Filemodules that are actively monitored
	*/
	$last_group=0;
	$tabcnt++;
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('general','remove');
	$order_name_arr[]=$Language->getText('my','monitoredfile');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);

	$sql="SELECT groups.group_name,groups.group_id,frs_package.name,filemodule_monitor.filemodule_id ".
		"FROM groups,filemodule_monitor,frs_package ".
		"WHERE groups.group_id=frs_package.group_id AND groups.status = 'A' ".
		"AND frs_package.package_id=filemodule_monitor.filemodule_id ".
		"AND filemodule_monitor.user_id='".user_getid()."' ORDER BY group_name DESC";
	$result=db_query($sql);
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<tr><td colspan="2" bgcolor="#FFFFFF"><center><strong>'.$Language->getText('my', 'no_monitored_filemodules').'</strong></center></td></tr>';
	} else {
		for ($i=0; $i<$rows; $i++) {
			if (db_result($result,$i,'group_id') != $last_group) {
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2"><a href="'.$GLOBALS['sys_urlprefix'].'/project/?group_id='.
				db_result($result,$i,'group_id').'">'.
				db_result($result,$i,'group_name').'</a></td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td style="text-align:center"><a href="'.$GLOBALS['sys_urlprefix'].'/frs/monitor.php?filemodule_id='.
			db_result($result,$i,'filemodule_id').
			'&amp;group_id='.db_result($result,$i,'group_id'). '&amp;stop=1"><img src="'. $HTML->imgroot.'/ic/trash.png" height="16" width="16" '.
			'border="0" alt=""/></a></td><td width="99%"><a href="'.$GLOBALS['sys_urlprefix'].'/frs/?group_id='.
			db_result($result,$i,'group_id').'">'.
			db_result($result,$i,'name').'</a></td></tr>';

			$last_group=db_result($result,$i,'group_id');
		}
	}
	echo $HTML->listTableBottom();
?>
</div>

<div class="tabbertab" title="<?php echo $Language->getText('mytab','project'); ?>" >
<?php
	/*
		   Personal bookmarks
	* /
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
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td style="text-align:center">
			<a href="'.$GLOBALS['sys_urlprefix'].'/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">
			<img src="'.$HTML->imgroot.'/ic/trash.png" height="16" width="16" border="0" alt="" /></a></td>
			<td><strong><a href="'. db_result($result,$i,'bookmark_url') .'">'.
			db_result($result,$i,'bookmark_title') .'</a></strong> &nbsp;
			<a href="'.$GLOBALS['sys_urlprefix'].'/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id') .'">['.$Language->getText('general','edit').']</a>';
		}
	}

	/*
		PROJECT LIST
	*/
	$tabcnt++;
	$order_name_arr=array();
	$order_name_arr[]=$Language->getText('general','remove');
	$order_name_arr[]=$Language->getText('my','projects');
	echo $HTML->listTableTop($order_name_arr,'',$tabcnt);

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
		echo '<tr><td colspan="2" bgcolor="#FFFFFF"><strong>'.$Language->getText('my', 'no_projects').'</strong></td></tr>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			$admin_flags = db_result($result, $i, 'admin_flags');
			if (stristr($admin_flags, 'A')) {
				$img="trash-x.png";
			} else {
				$img="trash.png";
			}

			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td style="text-align:center">
			<a href="rmproject.php?group_id='. db_result($result,$i,'group_id') .'">
			<img src="'.$HTML->imgroot.'ic/'.$img.'" alt="Delete" height="16" width="16" border="0" /></a></td>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/projects/'. db_result($result,$i,'unix_group_name') .'/">'. htmlspecialchars(db_result($result,$i,'group_name')) .'</a></td></tr>';
		}
	}
	echo $HTML->listTableBottom();
?>
</div>
<?php
//link to webcal
plugin_hook('call_user_cal') ;
?>
</div>
<?php
	echo site_user_footer(array());

}

?>

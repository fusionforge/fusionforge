<?php
/**
 * FusionForge User's Personal Page
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge Team
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
require_once 'my_utils.php';
require_once $gfwww.'include/vote_function.php';
require_once $gfcommon.'tracker/ArtifactsForUser.class.php';
require_once $gfcommon.'forum/ForumsForUser.class.php';
require_once $gfcommon.'pm/ProjectTasksForUser.class.php';
require_once('common/widget/WidgetLayoutManager.class.php');
if (!session_loggedin()) { // || $sf_user_hash) {

	exit_not_logged_in();

} else {
	site_user_header(array('title'=>sprintf(_('Personal Page For %s'),user_getname())));
	?>

<script type="text/javascript" src="<?php echo util_make_uri ('/tabber/tabber.js'); ?>"></script>
<?php
$user = UserManager::instance()->getCurrentUser();
if(HTTPRequest::instance()->exist('enable_widget')) {
        $user->setPreference('enable_widget','1');
}
if(HTTPRequest::instance()->exist('disable_widget')) {
        $user->setPreference('enable_widget','0');
}

if($user->getPreference('enable_widget')) {
        echo util_make_link('/my/?disable_widget=1','['._('Disable widgets').']');
	$lm = new WidgetLayoutManager();
	$lm->displayLayout(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER);
}       
else {  
        echo util_make_link('/my/?enable_widget=1','['._('Enable widgets').']');

?>
<div id="tabber" class="tabber tabber-user-homepage" <?php plugin_hook('call_user_js');?>>

<?php if (forge_get_config('use_tracker')) { ?>
<div class="tabbertab" 
title="<?php echo _('Assigned Artifacts'); ?>">
	<?php
	/*
		Artifacts
	*/
	$last_group=0;
	$order_name_arr=array();
	$order_name_arr[]=_('ID');
	$order_name_arr[]=_('Priority');
	$order_name_arr[]=_('Summary');
    echo $HTML->listTableTop($order_name_arr);

	$artifactsForUser = new ArtifactsForUser(session_get_user());
	$assignedArtifacts =& $artifactsForUser->getAssignedArtifactsByGroup();
	if (count($assignedArtifacts) > 0) {
		$i=0;
		foreach($assignedArtifacts as $art) {
			if ($art->ArtifactType->getID() != $last_group) {
				echo '
				<tr><td colspan="3" class="tablecontent">'.
				util_make_link ( '/tracker/?group_id='.$art->ArtifactType->Group->getID().'&amp;atid='.$art->ArtifactType->getID(), $art->ArtifactType->Group->getPublicName().' - '.$art->ArtifactType->getName()).'</td></tr>';

			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$art->getID().'</td>
			<td width="10%" class="priority'.$art->getPriority().'">'.$art->getPriority().'</td>
			<td>'.
			util_make_link ('/tracker/?func=detail&amp;aid='.$art->getID().'&amp;group_id='.$art->ArtifactType->Group->getID().'&amp;atid='.$art->ArtifactType->getID(),$art->getSummary()).'</td></tr>';

			$last_group = $art->ArtifactType->getID();
		}
	} else {
		echo '
			<tr><td colspan="3" class="tablecontent">'._('You have no open tracker items assigned to you.').'</td></tr>';
	}
	echo $HTML->listTableBottom();
?>
</div>
<?php } ?>
<?php if (forge_get_config('use_pm')) { ?>
<div class="tabbertab" 
title="<?php echo _('Assigned Tasks'); ?>">
<?php
	/*
		Tasks assigned to me
	*/
	$last_group=0;
	$order_name_arr=array();
	$order_name_arr[]=_('ID');
	$order_name_arr[]=_('Priority');
	$order_name_arr[]=_('Summary');
    echo $HTML->listTableTop($order_name_arr);
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
			// Hack to prevent errors when there is an error.
			if (!$projectGroup) 
				continue;
			$group =& $projectGroup->getGroup();
			if ($projectGroup->getID() != $last_group) {
				echo '
				<tr><td colspan="3" class="tablecontent">'.
				util_make_link ('/pm/task.php?group_id='.$group->getID().'&amp;group_project_id='.$projectGroup->getID(),$group->getPublicName().' - '.$projectGroup->getName()).'</td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$task->getID().'</td>
			<td width="10%" class="priority'.$task->getPriority().'">'.$task->getPriority().'</td>
			<td>'.util_make_link ('/pm/task.php?func=detailtask&amp;project_task_id='.$task->getID().'&amp;group_id='.$group->getID().'&amp;group_project_id='.$projectGroup->getID(),$style_begin.$task->getSummary().$style_end).'</td></tr>';

			$last_group = $projectGroup->getID();
		}
	} else {
		echo '
		<tr><td colspan="3" class="tablecontent">'._('You have no open tasks assigned to you.').'</td></tr>';
		echo db_error();
	}
	echo $HTML->listTableBottom();
?>
</div>
<?php } ?>
<?php if (forge_get_config('use_tracker')) { ?>
<div class="tabbertab" 
title="<?php echo _('Submitted Artifacts'); ?>">
<?php
	$last_group="0";
	$order_name_arr=array();
	$order_name_arr[]=_('ID');
	$order_name_arr[]=_('Priority');
	$order_name_arr[]=_('Summary');
    echo $HTML->listTableTop($order_name_arr);
	$artifactsForUser = new ArtifactsForUser(session_get_user());
	$submittedArtifacts =& $artifactsForUser->getSubmittedArtifactsByGroup();
	if (count($submittedArtifacts) > 0) {
		$i=0;
		foreach ($submittedArtifacts as $art) {
			if ($art->ArtifactType->getID() != $last_group) {
				echo '
				<tr><td colspan="3" class="tablecontent">'.
				util_make_link ('/tracker/?group_id='.$art->ArtifactType->Group->getID().'&amp;atid='.$art->ArtifactType->getID(),$art->ArtifactType->Group->getPublicName().' - '.$art->ArtifactType->getName()).'</td></tr>';
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i++) .'>
			<td width="10%">'.$art->getID().'</td>
			<td width="10%" class="priority'.$art->getPriority().'">'.$art->getPriority().'</td>
			<td>'.util_make_link ('/tracker/?func=detail&amp;aid='.$art->getID().'&amp;group_id='.$art->ArtifactType->Group->getID().'&amp;atid='.$art->ArtifactType->getID(),$art->getSummary()).'</td></tr>';

			$last_group = $art->ArtifactType->getID();
		}
	} else {
		echo '
		<tr><td colspan="3" class="tablecontent">'._('You have no open tracker items submitted by you.').'</td></tr>';
	}
	echo $HTML->listTableBottom();
?>
</div>
<?php } ?>
<?php if (forge_get_config('use_forum') || forge_get_config('use_frs') || forge_get_config('use_tracker')) { ?>
<div class="tabbertab" title="<?php echo _('Monitored Items'); ?>" >
<?php
	/*
		Trackers that are actively monitored
	*/
	if (forge_get_config('use_tracker')) {
		$last_group=0;

		$display_col=array('summary'=>1,
				   'changed'=>1,
				   'status'=>0,
				   'priority'=>1,
				   'assigned_to'=>1,
				   'submitted_by'=>1,
				   'related_tasks'=>1);
		
		$order_name_arr=array();

		$order_name_arr[]=_('Remove');
		$order_name_arr[]=_('Monitored trackers');

		echo $HTML->listTableTop($order_name_arr);
		
		$result = db_query_params ('SELECT groups.group_name,groups.group_id,groups.unix_group_name,groups.status,groups.type_id,user_group.admin_flags,role.role_name
			FROM groups,user_group,role 
			WHERE groups.group_id=user_group.group_id 
			AND user_group.user_id=$1
			AND groups.status=$2 
			AND user_group.role_id=role.role_id 
			ORDER BY group_name',
					   array (user_getid(),
						  'A')) ;
		$rows = db_numrows ($result);
		$at_found = 0;
		if ($result && $rows >= 1) {
			$last_group = -1 ;
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

				if (count ($at_arr) >= 1) {
					foreach($at_arr as $at) {
						if (!$at->isMonitoring()) {
							continue ;
						}
						$at_found++ ;
						if ($group->getID() != $last_group) {
							echo '
					<tr '. $HTML->boxGetAltRowStyle(1) .'><td colspan="2">'.util_make_link ('/tracker/?group_id='.$group->getID(),$group->getPublicName()).'</td></tr>';
						}
						$last_group = $group->getID() ;
						
						echo '<tr '. $HTML->boxGetAltRowStyle(0) .'><td align="center">' ;
						echo util_make_link ('/tracker/?group_id='.$group->getID().'&atid='.$at->getID().'&func=monitor',
								     '<img src="'. $HTML->imgroot . '/ic/trash.png" height="16" width="16" '.'border="0" alt="'._('Stop monitoring').'" />') ;
						echo '</td><td width="99%">' ;
						echo util_make_link ('/tracker/?group_id='.$group->getID().'&atid='.$at->getID(),
								     $at->getName()) ;
						echo '</td></tr>';
					}
				}
			}
		}
		if (!$at_found) {
			echo '<tr><td colspan="2" bgcolor="#FFFFFF"><center><strong>'._('You are not monitoring any trackers.').'</strong></center></td></tr>';
		}
		echo $HTML->listTableBottom();
	}
	/*
		Forums that are actively monitored
	*/
	if (forge_get_config('use_forum')) {
		$last_group=0;
		$order_name_arr=array();
		$order_name_arr[]=_('Remove');
		$order_name_arr[]=_('Monitored Forums');
        echo $HTML->listTableTop($order_name_arr);
		$forumsForUser = new ForumsForUser(session_get_user());
		$forums = $forumsForUser->getMonitoredForums();
		if (count($forums) < 1) {
			echo '<tr><td colspan="2"><strong>'._('You are not monitoring any forums.').'</strong></td></tr>';
		} else {
			echo '<tr><td colspan="2"><strong>'.util_make_link ('/forum/myforums.php',_('My Monitored Forums')).'</strong></td></tr>';
			foreach ($forums as $f) {
				$group = $f->getGroup();
				if ($group->getID() != $last_group) {
					echo '
					<tr '. $HTML->boxGetAltRowStyle(1) .'><td colspan="2">'.util_make_link ('/forum/?group_id='.$group->getID(),$group->getPublicName()).'</td></tr>';
				}

				echo '
				<tr '. $HTML->boxGetAltRowStyle(0) .'><td class="align-center"><a href="'.util_make_url ('/forum/monitor.php?forum_id='.$f->getID().'&amp;stop=1&amp;group_id='.$group->getID()).'"><img src="'. $HTML->imgroot . '/ic/trash.png" height="16" width="16" '.
				'border="0" alt="" /></a></td><td width="99%">'.util_make_link ('/forum/forum.php?forum_id='.$f->getID(),$f->getName()).'</td></tr>';

				$last_group= $group->getID();
			}
		}
		echo $HTML->listTableBottom();
	}
	/*
		Filemodules that are actively monitored
	*/
	if (forge_get_config('use_frs')) {
		$last_group=0;
		$order_name_arr=array();
		$order_name_arr[]=_('Remove');
		$order_name_arr[]=_('Monitored FileModules');
        echo $HTML->listTableTop($order_name_arr);


		$result=db_query_params ('SELECT groups.group_name,groups.unix_group_name,groups.group_id,frs_package.name,filemodule_monitor.filemodule_id 
FROM groups,filemodule_monitor,frs_package 
WHERE groups.group_id=frs_package.group_id AND groups.status = $1 
AND frs_package.package_id=filemodule_monitor.filemodule_id 
AND filemodule_monitor.user_id=$2 ORDER BY group_name DESC',
			array('A',
				user_getid()));
		$rows=db_numrows($result);
		if (!$result || $rows < 1) {
			echo '<tr><td colspan="2"><strong>'._('You are not monitoring any files.').'</strong></td></tr>';
		} else {
			for ($i=0; $i<$rows; $i++) {
				if (db_result($result,$i,'group_id') != $last_group) {
					echo '
					<tr '. $HTML->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_link_g (db_result($result,$i,'unix_group_name'),db_result($result,$i,'group_id'),db_result($result,$i,'group_name')).'</td></tr>';
				}
				echo '
				<tr '. $HTML->boxGetAltRowStyle($i) .'><td class="align-center"><a href="'.util_make_url ('/frs/monitor.php?filemodule_id='.db_result($result,$i,'filemodule_id').'&amp;group_id='.db_result($result,$i,'group_id').'&amp;stop=1').'"><img src="'. $HTML->imgroot.'/ic/trash.png" height="16" width="16" '.
				'border="0" alt=""/></a></td><td width="99%">'.util_make_link ('/frs/?group_id='.db_result($result,$i,'group_id'),db_result($result,$i,'name')).'</td></tr>';

				$last_group=db_result($result,$i,'group_id');
			}
		}
		echo $HTML->listTableBottom();
	}
	plugin_hook ("monitored_element",false);
?>
</div>
<?php } ?>
<?php if ($GLOBALS['sys_use_bookmarks']) { ?>
<div class="tabbertab" title="<?php echo _('My Bookmarks'); ?>" >
<?php
	/*
		   Personal bookmarks
	*/
	echo $HTML->boxTop(_('My Bookmarks'), 'My_Bookmarks');

	echo '<a href="'.util_make_url ('/my/bookmark_add.php').'">'._('Add bookmark').'</a><br/><br/>';
	$result = db_query_params ('SELECT bookmark_url, bookmark_title, bookmark_id from user_bookmarks where 
user_id=$1 ORDER BY bookmark_title',
			array(user_getid() ));
	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '
		<strong>'._('You currently do not have any bookmarks saved.').'</strong>';
		echo db_error();
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo '</td></tr>
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td class="align-center">
			<a href="'.util_make_url ('/my/bookmark_delete.php?bookmark_id='. db_result($result,$i,'bookmark_id')).'">
			<img src="'.$HTML->imgroot.'/ic/trash.png" height="16" width="16" border="0" alt="" /></a></td>
			<td><strong><a href="'. db_result($result,$i,'bookmark_url') .'">'.
			db_result($result,$i,'bookmark_title') .'</a></strong> &nbsp;'.
			util_make_link ('/my/bookmark_edit.php?bookmark_id='. db_result($result,$i,'bookmark_id'),_('[Edit]'));
		}
	}
	echo $HTML->boxBottom();
?>
</div>
<?php } ?>
<div class="tabbertab" title="<?php echo _('Projects'); ?>" >
<?php

	/*
		PROJECT LIST
	*/
	$order_name_arr=array();
	$order_name_arr[]=_('Remove');
	$order_name_arr[]=_('My Projects');
	$order_name_arr[]=_('My Roles');
	echo $HTML->listTableTop($order_name_arr);
	
	$groups = $user->getGroups() ;
	sortProjectList ($groups) ;

	if (count ($groups) < 1) {
		echo '<tr><td colspan="3"><strong>'._('You\'re not a member of any active projects').'</strong></td></tr>';
	} else {
		$roles = RBACEngine::getInstance()->getAvailableRolesForUser ($user) ;
		sortRoleList ($roles) ;
		foreach ($groups as $g) {
			$img="trash.png";
			$role_names = array () ;
			foreach ($roles as $r) {
				if ($r instanceof RoleExplicit
				    && $r->getHomeProject() != NULL
				    && $r->getHomeProject()->getID() == $g->getID()) {
					$role_names[] = $r->getName() ;
					if ($r->hasPermission ('project_admin', $g->getID())) {
						$img="trash-x.png";
					}
				}
			}
			echo '
			<tr '. $HTML->boxGetAltRowStyle($i) .'><td class="align-center">' ;
			echo util_make_link ("/my/rmproject.php?group_id=" . $g->getID(),
					     '<img src="'.$HTML->imgroot.'ic/'.$img.'" alt="'._('Delete').'" height="16" width="16" border="0" />') ;

			echo '</td>
			<td>'.util_make_link_g ($g->getUnixName(),$g->getID(),$g->getPublicName()).'</td>
			<td>'. htmlspecialchars (implode (', ', $role_names)) .'</td></tr>';


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
}
	site_user_footer(array());

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

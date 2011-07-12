<?php
/**
 * Task Mgr And Tracker Integration
 *
 * Copyright 2003 GForge, LLC
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once $gfcommon.'pm/ProjectGroupFactory.class.php';
require_once $gfcommon.'pm/ProjectTaskFactory.class.php';

$group_project_id = getIntFromRequest('group_project_id');
$project_task_id = getIntFromRequest('project_task_id');
$aid = getIntFromRequest('aid');

$a=new Artifact($ath,$aid);
if (!$a || !is_object($a)) {
	exit_error(_('Artifact Could Not Be Created'),'tracker');
}

//
//	Add a relationship from this artifact to an existing task
//
if (getStringFromRequest('add_to_task')) {
	$offset = getStringFromRequest('offset');
	$_order = getStringFromRequest('_order');
	$max_rows = getIntFromRequest('max_rows');
	$set = getStringFromRequest('set');
	$_assigned_to = getStringFromRequest('_assigned_to');
	$_status = getStringFromRequest('_status');
	$_category_id = getIntFromRequest('_category_id');

	// $group object is created in tracker.php

	$pg=new ProjectGroup($group,$group_project_id);
	if (!$pg || !is_object($pg)) {
		exit_error(_('Could Not Get ProjectGroup'),'tracker');
	} elseif ($pg->isError()) {
		exit_error($pg->getErrorMessage(),'tracker');
	}

	$ptf = new ProjectTaskFactory($pg);
	if (!$ptf || !is_object($ptf)) {
		exit_error(_('Could Not Get ProjectTaskFactory'),'tracker');
	} elseif ($ptf->isError()) {
		exit_error($ptf->getErrorMessage(),'tracker');
	}

	$ptf->setup($offset,$_order,$max_rows,$set,$_assigned_to,$_status,$_category_id);
	if ($ptf->isError()) {
		exit_error($ptf->getErrorMessage(),'tracker');
	}

	$pt_arr =& $ptf->getTasks();
	if (!$pt_arr) {
		if ($ptf->isError()) {
			exit_error($ptf->getErrorMessage(),'tracker');
		} else {
			exit_error(_('No Available Tasks Found'),'tracker');
		}
	}

	$related_tasks = $a->getRelatedTasks();
	$skip = array();
	while ($row = db_fetch_array($related_tasks)) {
		$skip[$row['project_task_id']] = true;
	}
	$tasks = array();
	foreach($pt_arr as $p) {
		$id = $p->getID();
		if (!isset($skip[$id])) {
			$tasks[] = $p;
		}
	}
	if (empty($tasks)) {
		exit_error(_('No Available Tasks Found'));
	}

	$ath->header(array('titlevals'=>array($ath->getName()),
		'atid'=>$ath->getID(),
		'title'=>_('Build Relationship Between Tracker Items and Tasks')));

	echo '
		<form name="foo" action="'. getStringFromServer('PHP_SELF') .'?func=taskmgr&amp;group_id='.$group_id.'&amp;atid='.$atid.'&amp;aid='.$aid.'" method="post">
		<p><strong>'._('Tracker Item').':</strong> [#'.$a->getID().'] '.$a->getSummary().'</p>
		<p><strong>'._('Tasks Project').':</strong><br />';
	echo $pg->getName().'
		<input type="hidden" name="group_project_id" value="'.$pg->getID().'" /></p>
		<p>
		<strong>'._('Task').':</strong></p>
		<select name="project_task_id">';
	foreach($tasks as $task) {
		echo '<option value="'.$task->getID().'">'.$task->getSummary().'</option>';
	}
	echo '</select><br />
		<input type="submit" name="done_adding" value="'._('Add Relationship to Selected Task') . '" />
		</form>';

//
//	Add the relationship and display finished message
//
} elseif (getStringFromRequest('done_adding')) {

	session_redirect('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&project_task_id='.$project_task_id.'&func=addartifact&add_artifact_id='. $a->getID());

//
//	Create a new task and relate it to this artifact
//
} elseif (getStringFromRequest('new_task')) {

	session_redirect ('/pm/task.php?group_id='.$group_id.'&group_project_id='.$group_project_id.'&func=addtask&related_artifact_summary='. urlencode($a->getSummary()) .'&related_artifact_id='. $a->getID());

//
//	Show the list of ProjectGroups available
//
} else {

	$pgf=new ProjectGroupFactory($group);
	if (!$pgf || !is_object($pgf)) {
		exit_error(_('Could Not Get Factory'),'tracker');
	} elseif ($pgf->isError()) {
		exit_error($pgf->getErrorMessage(),'tracker');
	}

	$pg_arr = $pgf->getProjectGroups();
	if (!$pg_arr) {
		if ($pgf->isError()) {
			exit_error($pgf->getErrorMessage(),'tracker');
		} else {
			exit_error(_('No Existing Project Groups Found'),'tracker');
		}
	}

	$ath->header(array('titlevals'=>array($ath->getName()),
		'atid'=>$ath->getID(),
		'title'=>_('Build Relationship Between Tracker Items and Tasks')));

	echo '<form name="foo" action="'. getStringFromServer('PHP_SELF') .'?func=taskmgr&amp;group_id='.$group_id.'&amp;atid='.$atid.'&amp;aid='.$aid.'" method="post">
		<p><strong>'._('Tracker Item').':</strong> [#'.$a->getID().'] '.$a->getSummary().'</p>
		<p><strong>'._('Tasks Project').':</strong></p>
		<select name="group_project_id">';
	for ($i=0; $i<count($pg_arr); $i++) {
		echo '<option value="'.$pg_arr[$i]->getID().'">'.$pg_arr[$i]->getName().'</option>';
	}
	echo '</select>
		<p>
		<input type="submit" name="add_to_task" value="'._('Add Relation to Existing Task').'" />
		<input type="submit" name="new_task" value="'._('Create New Task').'" />
		</p>
		</form>';

}

$ath->footer(array());

?>

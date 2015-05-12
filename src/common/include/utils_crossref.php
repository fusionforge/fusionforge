<?php
/**
 * utils_crossref.php - Misc utils common to all aspects of the site
 *
 * Copyright 1999-2001 (c) Alcatel-Lucent
 * Copyright 2009, Roland Mas
 * Copyright 2014,2015 Franck Villaume - TrivialDev
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

function util_gen_cross_ref ($text, $group_id) {

	// Some important information.
	$prj = group_getunixname ($group_id);

	// Handle URL in links, replace them with hyperlinks.
	$text = util_make_links($text);

	// Handle gforge [#nnn] Syntax => links to tracker.
	$text = preg_replace_callback('/\[\#(\d+)\]/', create_function('$matches', 'return _artifactid2url($matches[1]);'), $text);

	// Handle gforge [Tnnn] Syntax => links to task.
	$text = preg_replace_callback('/\[\T(\d+)\]/', create_function('$matches', 'return _taskid2url($matches[1],'.$group_id.');'), $text);

	// Handle [wiki:<pagename>] syntax
	$text = preg_replace_callback('/\[wiki:(.*?)\]/', create_function('$matches', 'return _page2url('.$prj.',$matches[1]);'), $text);

	// Handle [forum:<thread_id>] Syntax => links to forum.
	$text = preg_replace_callback('/\[forum:(\d+)\]/', create_function('$matches', 'return _forumid2url($matches[1]);'), $text);

	return $text;
}

function _page2url($prj,$page) {
	return util_make_link('/wiki/g/'.$prj.'/'.rawurlencode($page), $page);
}

function _artifactid2url ($id, $mode='') {
	$text = '[#'.$id.']';
	$res = db_query_params ('SELECT group_id, artifact.group_artifact_id, summary, status_id
			FROM artifact, artifact_group_list
			WHERE artifact_id=$1
			AND artifact.group_artifact_id=artifact_group_list.group_artifact_id',
				array ($id)) ;
	if (db_numrows($res) == 1) {
		$row = db_fetch_array($res);
		$url = '/tracker/?func=detail&aid='.$id.'&group_id='.$row['group_id'].'&atid='.$row['group_artifact_id'];
		$arg['title'] = util_html_secure($row['summary']);
		if ($row['status_id'] == 2) {
			$arg['class'] = 'artifact_closed';
		}
		if ($mode == 'title') {
			return util_make_link($url, $text, $arg).util_make_link($url, $row['summary']).'<br />';
		} else {
			return util_make_link($url, $text, $arg);
		}
	}
	return $text;
}

/**
 * _taskid2url - transform text [T##] to clickable URL
 *
 * @param	int	$id		the Task ID
 * @param	int	$group_id	the group id owner of the task id
 * @return	string	the clickable link
 */
function _taskid2url($id, $group_id) {
	$text = '[T'.$id.']';
	$res = db_query_params('SELECT group_id, project_task.group_project_id, summary, status_id
			FROM project_task, project_group_list
			WHERE project_task_id=$1
			AND project_task.group_project_id=project_group_list.group_project_id
			AND group_id = $2',
				array ($id, $group_id));
	if (db_numrows($res) == 1) {
		$row = db_fetch_array($res);
		$url = '/pm/task.php?func=detailtask&project_task_id='.$id.'&group_id='.$row['group_id'].'&group_project_id='.$row['group_project_id'];
		$arg['title'] = util_html_secure($row['summary']);
		if ($row['status_id'] == 2) {
			$arg['class'] = 'task_closed';
		}
		return util_make_link($url, $text, $arg);
	}
	return $text;
}

function _forumid2url($id) {
	$text = '[forum:'.$id.']';
	$res = db_query_params ('SELECT group_id, forum.group_forum_id, subject
			FROM forum, forum_group_list
			WHERE msg_id=$1
			AND forum.group_forum_id=forum_group_list.group_forum_id',
				array ($id));
	if (db_numrows($res) == 1) {
		$row = db_fetch_array($res);
		$url = '/forum/message.php?msg_id='.$id.'&group_id='.$row['group_id'];
		$arg['title'] = $row['subject'];
		return util_make_link($url, $text, $arg);
	}
	return $text;
}

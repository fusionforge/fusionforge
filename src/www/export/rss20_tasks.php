<?php
/**
 * FusionForge Rss export for tasks
 * Previous Copyright: FusionForge Team
 * Copyright 2016, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';
require_once $gfcommon.'pm/ProjectGroupFactory.class.php';

//Default Vars
$number_items = 10;
$max_number_items = 100;
$number = 10;
$max_number = 100;
$additive = ' AND ';
$btwg='';
$btwp='';
$project='';
$us='';

//group and project and user, or group or project or user?
//take care: status means AND to all (user or group, but AND status)
if (isset($_GET['OR'])) {
	$additive = ' OR ';
}

$user = '';
$user_ids = getStringFromRequest('user_ids');
$user_arr = array();
if ($user_ids) {
	$user_arr = explode(',', $user_ids);
}
if (count($user_arr) > 0) {
	foreach($user_arr AS $single_user_id) {
		$user.= ' OR (a.assigned_to_id = '.$single_user_id.')';
	}
	$user = '('.substr($user,4).')';
}

//group_ids
$projects = array();
$group_ids = getStringFromRequest('group_ids');
$groups = array();
if ($group_ids) {
	$groups = explode(',', $group_ids);
}
foreach($groups AS $group) {
	$pm = new ProjectGroupFactory(group_get_object($group));
	$pm_group_list = $pm->getProjectGroups();
	$projects = array_merge($projects, $pm_group_list);
}

$p = array();
$group_project_ids = getStringFromRequest('group_project_ids');
if ($group_project_ids) {
	$p = explode(',', $group_project_ids);
	foreach($p AS $key => $p_unit) {
		if (!forge_check_perm('pm', $p_unit, 'read')) {
			unset($p[$key]);
		}
	}
	$p = array_values($p);

}
$projects = array_unique(array_merge($projects, $p)); //die projekte der getvars kommen dazu
$project_sq = '' ;
if (count($projects) > 0) {
	foreach ($projects AS $project) {
		$project_sq .= ' OR (group_project_id = '.$project.')';
	}
	$project_sq = '('.substr($project_sq,4).')';
}

foreach (handle_getvar('status_ids') AS $status_id) {
	$status .= ' OR (status_id = '.$status_id.')';
}
if (isset($status)) {
	$status = '('.substr($status,4).')';
}

//important for correct sql-syntax
if (!empty($status)) {
	$status = ' AND '.$status;
}
if (!empty($project_sq) OR !empty($user) OR !empty($status)) {
	$us = ' AND ';
}
if (!empty($project_sq) AND !empty($user)) {
	$btwp = $additive;
}

$requestedNumber = getIntFromRequest('number', $number);

//calculates number of shown
if ($requestedNumber <= $max_number AND $requestedNumber > 0) {
	$number = $requestedNumber;
} elseif ($requestedNumber > $max_number) {
	$number = $max_number;
}

//creating, sending, and using the query
$qpa = db_construct_qpa(false, 'SELECT DISTINCT
				pt.*,u.realname AS user_realname
				FROM
				project_task pt,users u,project_assigned_to a
				WHERE ', array());
$qpa = db_construct_qpa($qpa, is_needed('(').$project_sq.' '.$btwp.' '.$user.' '.$status.is_needed(')').' '.$us.'u.user_id = pt.created_by AND pt.project_task_id=a.project_task_id', array());
$qpa = db_construct_qpa($qpa, ' ORDER BY last_modified_date', array());

$res = db_query_qpa($qpa, $number);
$i = 0;

beginTaskFeed(forge_get_config('forge_name')._(': ')._('Current Tasks'), forge_get_config('web_host'), _('See all the tasks you want to see!'));
if (0 < db_numrows($res)) {
	while ($i < db_numrows($res)) {
		$res1 = db_query_params('SELECT group_id, project_name FROM project_group_list WHERE group_project_id = $1', array(db_result($res, $i, 'group_project_id')));
		if(db_numrows($res1)==1) {
			$row1 = db_fetch_array($res1);
			$project_c[db_result($res,$i,'group_project_id')]['group_id'] = $row1['group_id'];
			if(isset($row1['project_name'])) {
				$project_c[db_result($res, $i, 'group_project_id')]['project_name'] = $row1['project_name'];
			} else {
				$project_c[db_result($res, $i, 'group_project_id')]['project_name'] = 'Wrong or deleted project';
			}

			$res2 = db_query_params('SELECT group_name FROM groups WHERE group_id = $1', array($row1['group_id']));
			$row2 = db_fetch_array($res2);
			if(isset($row2['group_name'])) {
				$group_c[$row1['group_id']] = $row2['group_name'];
			} else {
				$group_c[$row1['group_id']] = 'Wrong or deleted group';
			}

			$item_cat = $group_c[$project_c[db_result($res, $i, 'group_project_id')]['group_id']]." - ".$project_c[db_result($res, $i, 'group_project_id')]['project_name']." -- ".db_result($res, $i, 'summary');
			$ar['project_task_id'] = db_result($res, $i, 'project_task_id');
			$ar['group_project_id'] = db_result($res, $i, 'group_project_id');
			$ar['group_id'] = $project_c[db_result($res, $i, 'group_project_id')]['group_id'];
			$ar['most_recent_date'] = db_result($res, $i, 'last_modified_date');
			$ar['subject'] = db_result($res, $i, 'summary');
			$ar['user_realname'] = db_result($res, $i, 'user_realname');
			$ar['details'] = db_result($res, $i, 'details');
			writeTaskFeed($ar, $item_cat);
		}
		$i++;
	}
} else {
	displayError('No tasks found! Please check for invalid params.');
}
endFeed();

//*********************** HELPER FUNCTIONS ***************************************

function is_needed($str) {
	global $project_sq,$user,$status;
	if(!empty($project_sq) OR !empty($user) OR !empty($status)) {
		return $str;
	} else {
		return '';
	}
}
function handle_getvar($name) {
	$return = array();
	if(isset($_GET[$name])) {
		$vars = array_unique(explode(" ",$_GET[$name]));
		foreach ($vars as $var) {
			if(ctype_digit($var)) {
				$return[]=$var;
			}
		}
	}
	return $return;
}

function beginTaskFeed($feed_title, $feed_link, $feed_desc) {

	header("Content-Type: text/xml");
	print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	print "<rss version=\"2.0\">\n";
	print " <channel>\n";
	print "  <title>".$feed_title."</title>\n";
	print "  <link>".$feed_link."</link>\n";
	print "  <description>".$feed_desc."</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright 2000-".date("Y")." ".forge_get_config('forge_name')."</copyright>\n";
	print "  <webMaster>".forge_get_config('admin_email')."</webMaster>\n";
	print "  <lastBuildDate>".gmdate('D, d M Y G:i:s',time())." GMT</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
}

function writeTaskFeed($msg, $item_cat){

    //------------ build one feed item ------------
    print "  <item>\n";
        print "   <title>".$msg['subject']."</title>\n";
        print "   <link>" . util_make_url("/pm/t_follow.php/" . $msg['project_task_id']) . "</link>\n";
        print "   <category>".$item_cat."</category>\n";
        print "   <description>".$msg['details']."</description>\n";
        print "   <author>".$msg['user_realname']."</author>\n";
                //print "   <comment></comment>\n";
        print "   <pubDate>".gmdate('D, d M Y G:i:s',$msg['most_recent_date'])." GMT</pubDate>\n";
	print "   <guid>" . util_make_url("/pm/t_lookup.php?tid=" . $msg['project_task_id']) . "</guid>\n";
    print "  </item>\n";

}

function displayError($errorMessage) {
	print "  <title>Error</title>\n".
		"  <description>".$errorMessage."</description>";
}

function endFeed() {
    print "\n </channel>\n</rss>";
    exit();
}

function endOnError($errorMessage) {
	displayError($errorMessage);
	endFeed();
}

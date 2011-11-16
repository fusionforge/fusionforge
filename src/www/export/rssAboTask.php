<?php
/*-
 * FusionForge RSS feed for Tasks abonnement
 *
 * Copyright © 2010
 *	Patrick Apel <p.apel@tarent.de>
 *	Thorsten Glaser <t.glaser@tarent.de>
 * All rights reserved.
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
 *-
 * RSS feed used in connection with the Tasks Abonnement feature.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

require_once $gfcommon.'include/Error.class.php';

require_once $gfcommon.'pm/ProjectTask.class.php';
require_once $gfcommon.'pm/ProjectGroup.class.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'pm/ProjectTaskSqlQueries.php';

global $gfwww, $gfcommon;

$tid = getIntFromRequest('tid');
if (!$tid)
	$tid = util_path_info_last_numeric_component();
if (!$tid) {
	header("HTTP/1.0 404 Not Found");
	echo "You forgot to pass the tid.\n";
	exit;
}

$tinfo = getGroupProjectIdGroupId($tid);

if (!$tinfo) {
	header("HTTP/1.0 404 Not Found");
	echo "There is no task with id ".$tid."!\n";
	exit;
}

$group_id = $tinfo['group_id'];
$group_project_id = $tinfo['group_project_id'];
$project_task_id = $tinfo['project_task_id'];

$objGroup =& group_get_object($group_id);
if (!$objGroup || !is_object($objGroup)) {
	exit_no_group();
} elseif ($objGroup->isError()) {
	exit_error('Error',$objGroup->getErrorMessage());
}

$objProjectGroup = &projectgroup_get_object($group_project_id);
if(!$objProjectGroup || !is_object($objProjectGroup)) {
	exit_error('Error',_('No project group was found for this task.'));
}

$objProjectTask = projecttask_get_object($project_task_id);
if(!$objProjectTask || !is_object($objProjectTask)) {
	exit_error('Error',_('No project task was found.'));
}

$arrDbMessages = $objProjectTask->getMessages();
$arrDbHistory = $objProjectTask->getHistory();

$arrMessages = array();
$arrHistory = array();

while ($arr = db_fetch_array($arrDbMessages)) {
//realname|email|user_name|project_message_id|project_task_id|body|posted_by|postdate
	$arr_ = array($arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], 'message', $arr[6], $arr[7]);
	array_push($arrMessages, $arr_);
}

while ($arr = db_fetch_array($arrDbHistory)) {
//realname|email|user_name|project_history_id|project_task_id|field_name|>>old_value<<|mod_by|mod_date
	$arr_ = array($arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], $arr[7], $arr[8]);
	array_push($arrHistory, $arr_);
}

$arrHistoryMessages = array_merge($arrMessages, $arrHistory);

/* Bubblesort should be good enough as solution */
$j = count($arrHistoryMessages);
$l = $j;

for($i=0; $i<$j; $i++){
	for($k=0; $k<$l; $k++){
		if($arrHistoryMessages[$i][8]<$arrHistoryMessages[$k][8]){
			$tmp=$arrHistoryMessages[$k];
			$arrHistoryMessages[$k]=$arrHistoryMessages[$i];
			$arrHistoryMessages[$i]=$tmp;
		}
	}
}

$updates = array_reverse($arrHistoryMessages);

writeRssFeedBegin($objProjectTask, $objGroup, $objProjectGroup);
writeRssFeedItem($objProjectTask, $updates);
writeRssFeedEnd();

/* the rss feed may not be out of format! Otherwise the rss feed would not be shown correctly
 * & has to be &amp; because of utf-8 encondig
 **/
function writeRssFeedBegin($objProjectTask, $objGroup, $objProjectGroup) {
	//UTF-8 encoded. A special sign like & for example needed to be written like &amp;
	/* It is possible to format a rss feed with cascading style sheet and xsl, but it does not work with
	* Firefox version 3.6.6, so I did not make use of it. */

	header('Content-Type: application/rss+xml; charset=utf-8');

	print'<?xml version="1.0" encoding="utf-8"?>';
	print'<!DOCTYPE rss PUBLIC "-//Netscape Communications//DTD RSS 0.91//EN" "http://my.netscape.com/publish/formats/rss-0.91.dtd">';
	print'<rss version="2.0">';
	print'<channel>';
	print '<link>' . util_make_url("/pm/t_follow.php/" . $objProjectTask->getID()) . '</link>';
	print'<title>';
	print $objGroup->getPublicName().' '.' - ';
	print $objProjectTask->getSummary();
	print'</title>';
	print'<description>';

	printf(_('Update history of the task with the name %1$s and the ID %2$d.') . ' ', $objProjectTask->getSummary(), $objProjectTask->getID());
	print _('Current values of the task’s…').' => ';

	print ' '._('Subproject:').' '.$objProjectGroup->getName().' |';
	print ' '._('Summary:').' '.$objProjectTask->getSummary().' |';
	print ' '._('Complete:').' '.$objProjectTask->getPercentComplete().'% |';
	print ' '._('Status:').' '.$objProjectTask->getStatusName().' |';
	print ' '._('Details:').' '.$objProjectTask->getDetails().' |';

	print'</description>';
	//print'<language>en-us</language>';
	print'<copyright>'.'Copyright 2000-'.date('Y').' '.forge_get_config('forge_name').'</copyright>';
	print'<webMaster>'.forge_get_config('admin_email').'</webMaster>';
	print'<lastBuildDate>'.gmdate('D, d M Y G:i:s',time()). ' GMT'.'</lastBuildDate>';
	print'<docs>http://blogs.law.harvard.edu/tech/rss</docs>';

}

/* Chronological order of updates as rss feed items */
function writeRssFeedItem($objProjectTask, $updates) {

	foreach($updates as $update){
	$update[6]==='message' ? $title = _('Comment') : $title = $update[5];
	$update[6]==='message' ? $description = $update[5] : $description = $update[6];
	$objGfUser = &user_get_object($update[7]);
		print'<item>';
		print'<title>';
		print $title;
		print'</title>';
		print'<description>';
		print _('Updated value').': ';
		print $description.' | ';
		print _('Posted by').': ';
		print $objGfUser->getUnixName().' | ';
		print _('Update time').': ';
		print gmdate('D, d M Y G:i:s',$update[8]);
		print'</description>';
		print '<link>' . util_make_url("/pm/t_follow.php/" . $objProjectTask->getID()) . '</link>';
		print'<author>';
		print $objGfUser->getEmail();
		print'</author>';
		print '<guid isPermaLink="true">' . util_make_url("/export/rssAboTask.php?tid=" . $objProjectTask->getID()) . '</guid>';
		print'<pubDate>';
		print gmdate('D, d M Y G:i:s',$update[8]);
		print'</pubDate>';
		print'</item>';

	}
}

function writeRssFeedEnd() {
	print'</channel>';
	print'</rss>';
}

?>

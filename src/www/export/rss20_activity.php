<?php
/**
 *
 * Copyright 2006 Daniel A. Perez <daniel@gforgegroup.com>
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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
require_once $gfcommon.'include/Activity.class.php';
require_once $gfwww.'export/rss_utils.inc';

global $HTML;

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit', 10);

if ($limit > 100) $limit = 100;

if ($group_id) {
	session_require_perm('project_read', $group_id);

	$res = db_query_params ('SELECT group_name FROM groups WHERE group_id=$1',
				array($group_id),
				1);
	$row = db_fetch_array($res);
	$title = $row['group_name']." - ";
	$link = "?group_id=$group_id";
	$description = " of ".$row['group_name'];

	$admins = RBACEngine::getInstance()->getUsersByAllowedAction ('project_admin', $group_id) ;
	if (count ($admins)) {
		$webmaster = $admins[0]->getUnixName()."@".forge_get_config('users_host')." (".$admins[0]->getRealName().")";
	} else {
		$webmaster = forge_get_config('admin_email');
	}

	$sysdebug_enable = false;
	// ## one time output
	header("Content-Type: text/xml; charset=utf-8");
	print '<?xml version="1.0" encoding="UTF-8"?>
		<rss version="2.0">
		';
	print " <channel>\n";
	print "  <title>".forge_get_config('forge_name')." $title Activity</title>\n";
	print "  <link>".util_make_url("/activity/$link")."</link>\n";
	print "  <description>".forge_get_config('forge_name')." Project Activity$description</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright ".date("Y")." ".forge_get_config ('forge_name')."</copyright>\n";
	print "  <webMaster>$webmaster</webMaster>\n";
	print "  <lastBuildDate>".rss_date(time())."</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
	print "  <generator>".forge_get_config ('forge_name')." RSS generator</generator>\n";
	print "  <image>\n";
	print "    <url>".util_make_url('/images/icon.png')."</url>\n";
	print "    <title>".forge_get_config('forge_name')."</title>\n";
	print "    <link>".util_make_url()."</link>\n";
	print "    <width>124</width>\n";
	print "    <heigth>32</heigth>\n";
	print "  </image>\n";

	$res = db_query_params('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
		AND group_id=$3 ORDER BY activity_date DESC',
				array(time() - 30*86400,
				      time(),
				      $group_id),
				$limit);
	$results = array();
	while ($arr = db_fetch_array($res)) {
		$results[] = $arr;
	}

	// If plugins wants to add activities.
	$ids = array();
	$texts = array();
	$show = array();

	$hookParams['group_id'] = $group_id ;
	$hookParams['results'] = &$results;
	$hookParams['show'] = &$show;
	$hookParams['begin'] = time()-(30*86400);
	$hookParams['end'] = time();
	$hookParams['ids'] = &$ids;
	$hookParams['texts'] = &$texts;
	plugin_hook ("activity", $hookParams) ;

	$ffactivity = new Activity();
	usort($results, 'Activity::date_compare');

	// ## item outputs
	$cached_perms = array();
	foreach ($results as $arr) {
		if (!$ffactivity->check_perm_for_activity($arr, $cached_perms)) {
			continue;
		}

		print "  <item>\n";
		switch ($arr['section']) {
			case 'scm': {
				print "   <title>".htmlspecialchars('Commit :'.$arr['description'])."</title>\n";
				print "   <link>".util_make_url('/scm/'.htmlentities($arr['ref_id'].$arr['subref_id']))."</link>\n";
				print "   <comments>".util_make_url('/scm/'.htmlentities($arr['ref_id'].$arr['subref_id']))."</comments>\n";
				$arr['category'] = _('Source Code');
				break;
			}
			case 'trackeropen': {
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Opened')."</title>\n";
				print "   <link>".util_make_url("/tracker/a_follow.php/".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/tracker/a_follow.php/".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('Trackers');
				break;
			}
			case 'trackerclose': {
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Closed')."</title>\n";
				print "   <link>".util_make_url("/tracker/a_follow.php/".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/tracker/a_follow.php/".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('Trackers');
				break;
			}
			case 'frsrelease': {
				print "   <title>".htmlspecialchars('FRS Release [#'.$arr['description'].']')."</title>\n";
				print "   <link>".util_make_url("/frs/?release_id=".$arr['subref_id'].'&amp;group_id='.$arr['group_id'])."</link>\n";
				print "   <comments>".util_make_url("/frs/?release_id=".$arr['subref_id'].'&amp;group_id='.$arr['group_id'])."</comments>\n";
				$arr['category'] = _('File Release System');
				break;
			}
			case 'forumpost': {
				print "   <title>".htmlspecialchars('Forum Post [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>".util_make_url("/forum/message.php?forum_id=".$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'])."</link>\n";
				print "   <comments>".util_make_url("/forum/message.php?forum_id=".$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id'])."</comments>\n";
				$arr['category'] = _('Forums');
				break;
			}
			case 'news': {
				print "   <title>".htmlspecialchars('News Post [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>".util_make_url("/forum/forum.php?forum_id=".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/forum/forum.php?forum_id=".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('News');
				break;
			}
			case 'docmannew': {
				print "   <title>".htmlspecialchars('New Document '.$arr['description'])."</title>\n";
				print "   <link>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['ref_id'])."</link>\n";
				print "   <comment>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['ref_id'])."</comment>\n";
				$arr['category'] = _('Documents');
				break;
			}
			case 'docmanupdate': {
				print "   <title>".htmlspecialchars('Updated Document '.$arr['description'])."</title>\n";
				print "   <link>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['ref_id'])."</link>\n";
				print "   <comment>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['ref_id'])."</comment>\n";
				$arr['category'] = _('Documents');
				break;
			}
			case 'docgroupnew': {
				print "   <title>".htmlspecialchars('New Document Directory '.$arr['description'])."</title>\n";
				print "   <link>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['subref_id'])."</link>\n";
				print "   <comment>".util_make_url("/docman/?group_id=".$arr['group_id']."&amp;view=listfile&amp;dirid=".$arr['subref_id'])."</comment>\n";
				$arr['category'] = _('Documents');
				break;
			}
			case 'taskopen': {
				print "   <title>".htmlspecialchars('Task Item [#'.$arr['subref_id'].' '.$arr['description'].'] Open')."</title>\n";
				print "   <link>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('Tasks');
				break;
			}
			case 'taskclose': {
				print "   <title>".htmlspecialchars('Task Item [#'.$arr['subref_id'].' '.$arr['description'].'] Closed')."</title>\n";
				print "   <link>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('Tasks');
				break;
			}
			case 'taskdelete': {
				print "   <title>".htmlspecialchars('Task Item [#'.$arr['subref_id'].' '.$arr['description'].'] Deleted')."</title>\n";
				print "   <link>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</link>\n";
				print "   <comments>".util_make_url("/pm/t_follow.php/".$arr['subref_id'])."</comments>\n";
				$arr['category'] = _('Tasks');
				break;
			}
			default: {
				print "   <title>".htmlspecialchars($arr['title'])."</title>\n";
				print "   <link>".util_make_url($arr['link'])."</link>\n";
				print "   <comment>".util_make_url($arr['link'])."</comment>\n";
			}
		}

		print "   <description>".rss_description($arr['description'])."</description>\n";
		if (isset($arr['category']) && $arr['category']) {
			print "   <category>".$arr['category']."</category>\n";
		}
//TODO: fix the HTML in the arr['realname']. Get a new way to display user info in activity.
// 		if (isset($arr['user_name']) && $arr['user_name']) {
// 			print "   <author>".$arr['user_name']."@".forge_get_config('users_host')." (".$arr['realname'].")</author>\n";
// 		} else {
// 			print "   <author>".$arr['realname']."</author>\n";
// 		}
		print "   <pubDate>".rss_date($arr['activity_date'])."</pubDate>\n";
		print "  </item>\n";
	}
	// ## end output
	print " </channel>\n";
	print "</rss>\n";

} else {
	// Print error showing no group was selected
	echo $HTML->error_msg(_('Error: No group selected'));
}

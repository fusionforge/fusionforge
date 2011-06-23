<?php
/**
 *
 * Copyright 2006 Daniel A. Perez <daniel@gforgegroup.com>
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

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit', 10);

if ($limit > 100) $limit = 100;

$url = util_make_url ('/');
$url = rtrim($url, '/');

if ($group_id) {
	session_require_perm('project_read', $group_id);

	$res = db_query_params ('SELECT group_name FROM groups WHERE group_id=$1',
				array($group_id),
				1);
	$row = db_fetch_array($res);
	$title = $row['group_name'];
	$link = "?group_id=$group_id";
	$description = " of ".$row['group_name'];

	$admins = RBACEngine::getInstance()->getUsersByAllowedAction ('project_admin', $group_id) ;
	if (count ($admins)) {
		$webmaster = $admins[0]->getUnixName()."@".forge_get_config('users_host')." (".$admins[0]->getRealName().")";
	} else {
		$webmaster = forge_get_config('admin_email');
	}

	// ## one time output
	header("Content-Type: text/xml; charset=utf-8");
	print '<?xml version="1.0" encoding="UTF-8"?>
       <rss version="2.0">
       ';
	print " <channel>\n";
	print "  <title>".forge_get_config ('forge_name')." $title Activity</title>\n";
	print "  <link>$url/activity/$link</link>\n";
	print "  <description>".forge_get_config ('forge_name')." Project Activity$description</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright ".date("Y")." ".forge_get_config ('forge_name')."</copyright>\n";
	print "  <webMaster>$webmaster</webMaster>\n";
	print "  <lastBuildDate>".rss_date(time())."</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
	print "  <generator>".forge_get_config ('forge_name')." RSS generator</generator>\n";

	$res = db_query_params ('SELECT * FROM activity_vw WHERE activity_date BETWEEN $1 AND $2 AND group_id=$3 ORDER BY activity_date DESC',
				array(time() - 30*86400,
				      time(),
				      $group_id),
				$limit);

	// ## item outputs
	while ($arr = db_fetch_array($res)) {

		switch ($arr['section']) {
			case 'commit': {
				if (!forge_check_perm('tracker',$arr['ref_id'],'read')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('Commit for Tracker Item [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comments>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comments>\n";
				break;
			}
			case 'trackeropen': {
				if (!forge_check_perm('tracker',$arr['ref_id'],'read')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Opened')."</title>\n";
				print "   <link>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comments>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comments>\n";
				break;
			}
			case 'trackerclose': {
				if (!forge_check_perm('tracker',$arr['ref_id'],'read')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Closed')."</title>\n";
				print "   <link>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comments>$url/tracker/?func=detail&amp;atid=".$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comments>\n";
				break;
			}
			case 'frsrelease': {
				if (!forge_check_perm('frs',$arr['group_id'],'read_public')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('FRS Release [#'.$arr['description'].']')."</title>\n";
				print "   <link>$url/frs/?release_id=".$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comments>$url/frs/?release_id=".$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comments>\n";
				break;
			}
			case 'forumpost': {
				if (!forge_check_perm('forum',$arr['ref_id'],'read')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('Forum Post [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>$url/forum/message.php?forum_id=".$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comments>$url/forum/message.php?forum_id=".$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comments>\n";
				break;
			}
			case 'news': {
				if (!forge_check_perm('forum',$arr['subref_id'],'read')) {
					continue (2);
				}
				print "  <item>\n";
				print "   <title>".htmlspecialchars('News Post [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>$url/forum/forum.php?forum_id=".$arr['subref_id']."</link>\n";
				print "   <comments>$url/forum/forum.php?forum_id=".$arr['subref_id']."</comments>\n";
				break;
			}
		}

		print "   <description>".rss_description($arr['description'])."</description>\n";
		print "   <author>".$arr['user_name']."@".forge_get_config('users_host')." (".$arr['realname'].")</author>\n";
		print "   <pubDate>".rss_date($arr['activity_date'])."</pubDate>\n";
		print "  </item>\n";
	}
	// ## end output
	print " </channel>\n";
	print "</rss>\n";

} else {
	// Print error showing no group was selected

	echo "<div class='error'>"._('Error: No group selected')."</div>";
}

?>

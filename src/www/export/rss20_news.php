<?php
/**
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

// export projects release news in RSS 2.0
// Author: Scott Grayban <sgrayban@borgnet.us>
//

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
';
$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit', 10);
if ($limit > 100) $limit = 100;

if ($group_id) {
	forge_require_perm('project_read', $group_id);
	
	$res = db_query_params ('SELECT group_name FROM groups WHERE group_id=$1',
				array($group_id),
				1);
	$row = db_fetch_array($res);
	$title = ": ".$row['group_name']." - ";
	$link = "?group_id=$group_id";
	$description = " of ".$row['group_name'];

	$admins = RBACEngine::getInstance()->getUsersByAllowedAction ('project_admin', $group_id) ;
	if (count ($admins)) {
		$webmaster = $admins[0]->getUnixName()."@".forge_get_config('users_host')." (".$admins[0]->getRealName().")";
	} else {
		$webmaster = forge_get_config('admin_email');
	}
} else {
	$title = "";
	$link = "";
	$description = "";
	$webmaster = forge_get_config('admin_email');
}

$rssTitle = forge_get_config ('forge_name')." Project$title News";
$rssLink = util_make_url("/news/$link");

// ## one time output
print " <channel>\n";
print "  <title>".$rssTitle."</title>\n";
print "  <link>".$rssLink."</link>\n";
print "  <description>".forge_get_config ('forge_name')." Project News$description</description>\n";
print "  <language>en-us</language>\n";
print "  <copyright>Copyright ".date("Y")." ".forge_get_config ('forge_name')."</copyright>\n";
print "  <webMaster>$webmaster</webMaster>\n";
print "  <lastBuildDate>".rss_date(time())."</lastBuildDate>\n";
print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
print "  <generator>".forge_get_config ('forge_name')." RSS generator</generator>\n";

$res = db_query_params ('SELECT forum_id,summary,post_date,details,g.group_id,g.group_name,u.realname,u.user_name
FROM news_bytes, groups g,users u
WHERE news_bytes.group_id=g.group_id
AND u.user_id=news_bytes.submitted_by
AND g.status=$1
AND news_bytes.is_approved <> 4
AND (g.group_id=$2 OR 1 != $3)
AND (is_approved=1 OR 1 != $4)
ORDER BY post_date DESC',
			array ('A',
			       $group_id,
			       $group_id ? 1 : 0,
			       $group_id ? 0 : 1),
			$limit) ;

// ## item outputs
while ($row = db_fetch_array($res)) {
	print "  <item>\n";
	print "   <title>".htmlspecialchars($row['summary'])."</title>\n";
	// if news group, link is main page
	if ($row['group_id'] != forge_get_config('news_group')) {
		print "   <link>http://".forge_get_config('web_host')."/forum/forum.php?forum_id=".$row['forum_id']."</link>\n";
	} else {
		print "   <link>http://".forge_get_config('web_host')."/</link>\n";
	}
	print "   <description>".rss_description($row['details'])."</description>\n";
	print "   <author>".$row['user_name']."@".forge_get_config('users_host')." (".$row['realname'].")</author>\n";
	print "   <pubDate>".rss_date($row['post_date'])."</pubDate>\n";
	if ($row['group_id'] != forge_get_config('news_group')) {
		print "   <guid>http://".forge_get_config('web_host')."/forum/forum.php?forum_id=".$row['forum_id']."</guid>\n";
	} else {
		print "   <guid>http://".forge_get_config('web_host')."/</guid>\n";
	}
	// if news group, comment is main page
	if ($row['group_id'] != forge_get_config('news_group')) {
		print "   <comments>http://".forge_get_config('web_host')."/forum/forum.php?forum_id=".$row['forum_id']."</comments>\n";
	} else {
		print "   <comments>http://".forge_get_config('web_host')."/</comments>\n";
	}
	print "  </item>\n";
}
// ## end output
print " </channel>\n";
?>
</rss>

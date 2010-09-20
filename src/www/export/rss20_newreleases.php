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
	$res = db_query_params ('SELECT group_name FROM groups WHERE group_id=$1 AND is_public=1',
				array ($group_id)) ;
	$row = db_fetch_array($res);
	$title = ": ".$row['group_name']." - ";
	$link = "/project/showfiles.php?group_id=$group_id";
	$description = " of ".$row['group_name'];
	$reswm = db_query_params ('SELECT users.user_name,users.realname FROM user_group,users WHERE group_id=$1 AND admin_flags=$2 AND users.user_id=user_group.user_id ORDER BY users.add_date',
				  array($group_id,
					'A'));
	if ($rowwm = db_fetch_array($reswm)) {
	  $webmaster = $rowwm['user_name']."@".forge_get_config('users_host')." (".$rowwm['realname'].")";
	} else {
	  $webmaster = forge_get_config('admin_email');
	}
} else {
	$title = "";
	$link = "/new/";
	$description = "";
	$webmaster = forge_get_config('admin_email');
}

// ## one time output
print " <channel>\n";
print "  <title>".forge_get_config ('forge_name')." Project$title Releases</title>\n";
print "  <link>http://".forge_get_config('web_host')."$link</link>\n";
print "  <description>".forge_get_config ('forge_name')." Project Releases$description</description>\n";
print "  <language>en-us</language>\n";
print "  <copyright>Copyright ".date("Y")." ".forge_get_config ('forge_name')."</copyright>\n";
print "  <webMaster>$webmaster</webMaster>\n";
print "  <lastBuildDate>".rss_date(time())."</lastBuildDate>\n";
print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";

$res = db_query_params ('SELECT groups.group_name AS group_name,
	frs_package.group_id AS group_id,
	groups.unix_group_name AS unix_group_name,
	groups.short_description AS short_description,
	groups.license AS license,
	users.user_name AS user_name,
	users.user_id AS user_id,
	users.realname AS realname,
        frs_package.name AS package_name,
	frs_release.package_id AS filemodule_id,
	frs_release.name AS module_name,
	frs_release.notes AS module_notes,
	frs_release.status_id AS release_status,
	frs_release.release_date AS release_date,
	frs_file.release_time AS release_time,
	frs_file.filename AS filename,
	frs_file.release_id AS filerelease_id
FROM users,frs_file,frs_release,frs_package,groups
WHERE frs_release.released_by=users.user_id
  AND frs_release.package_id=frs_package.package_id
  AND frs_package.group_id=groups.group_id
  AND frs_release.status_id=1
  AND groups.is_public=1
  AND (frs_package.group_id=$1 OR 1!=$2)
  AND frs_file.release_id=frs_release.release_id
ORDER BY frs_file.release_time DESC',
			array ($group_id,
			       $group_id ? 1 : 0),
			$limit * 3);


// ## item outputs
$outputtotal = 0;
$seen = array();
while ($row = db_fetch_array($res)) {
	if (!isset ($seen[$row['filerelease_id']])) {
		print "  <item>\n";
		print "   <title>".htmlspecialchars($row['package_name'])." ".htmlspecialchars($row['module_name'])."</title>\n";
		print "   <link>http://".forge_get_config('web_host')."/project/showfiles.php?group_id=".$row['group_id']."&amp;release_id=".$row['filerelease_id']."</link>\n";
		print "   <description>".rss_description($row['module_notes'])."</description>\n";
		print "   <author>".$row['user_name']."@".forge_get_config('users_host')." (".$row['realname'].")</author>\n";
		print "   <comments>http://".forge_get_config('web_host')."/project/shownotes.php?group_id=".$row['group_id']."&amp;release_id=".$row['filerelease_id']."</comments>\n";
		print "   <pubDate>".rss_date($row['release_date'])."</pubDate>\n";
		print "   <guid>http://".forge_get_config('web_host')."/project/showfiles.php?group_id=".$row['group_id']."&amp;release_id=".$row['filerelease_id']."</guid>\n";
		print "  </item>\n";
		$outputtotal++;
	}
	// eliminate dupes, only do $limit of these
	$seen[$row['filerelease_id']] = 1;
	if ($outputtotal >= $limit) break;
}
// ## end output
print " </channel>\n";
?>
</rss>

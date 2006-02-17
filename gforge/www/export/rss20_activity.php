<?php

/**
 * GForge
 *
 * Author: 2006,  Daniel A. Perez <daniel@gforgegroup.com>
 * http://gforge.org/
 *
 * @version   
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

include "pre.php";
include "rss_utils.inc";
header("Content-Type: text/xml");
print '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
';
// ## default limit
if (!$limit) $limit = 10;
if ($limit > 100) $limit = 100;

$group_id = getIntFromRequest('group_id');

if ($group_id) {
	$where = "group_id=$group_id and is_public=1";
	$query = "SELECT group_name FROM groups WHERE $where";
	$res = db_query($query,1);
	$row = db_fetch_array($res);
	$title = "".$row[group_name]." - ";
	$link = "?group_id=$group_id";
	$description = " of ".$row[group_name];
	$querywm =  "SELECT users.user_name,users.realname FROM user_group,users WHERE group_id=$group_id AND admin_flags='A' AND users.user_id=user_group.user_id ORDER BY users.add_date";
	$reswm = db_query($querywm,1);
	if ($rowwm = db_fetch_array($reswm)) {
	  $webmaster = $rowwm[user_name]."@".$GLOBALS[sys_users_host]." (".$rowwm[realname].")";
	} else {
	  $webmaster = "admin@".$GLOBALS[sys_default_domain];
	}
	$sql="SELECT * FROM activity_vw WHERE activity_date BETWEEN '".(time()-(30*86400))."' AND '".time()."'
	AND activity_vw.group_id = groups.group_id AND groups.is_public=1 AND activity_vw.group_id='$group_id' ORDER BY activity_date DESC";
	
	// ## one time output
	print " <channel>\n";
	print "  <title>".$GLOBALS[sys_default_name]." $title Activity</title>\n";
	print "  <link>http://".$GLOBALS[sys_default_domain]."/activity/$link</link>\n";
	print "  <description>".$GLOBALS[sys_name]." Project Activity$description</description>\n";
	print "  <language>en-us</language>\n";
	print "  <copyright>Copyright 2000-".date("Y")." ".$GLOBALS[sys_name]." OSI</copyright>\n";
	print "  <webMaster>$webmaster</webMaster>\n";
	print "  <lastBuildDate>".gmdate('D, d M Y G:i:s',time())." GMT</lastBuildDate>\n";
	print "  <docs>http://blogs.law.harvard.edu/tech/rss</docs>\n";
	print "  <generator>".$GLOBALS[sys_name]." RSS generator</generator>\n";
	print "  <image>\n";
	print "    <url>http://".$GLOBALS[sys_default_domain]."/images/bflogo-88.png</url>\n";
	print "    <title>".$GLOBALS[sys_name]." Developer</title>\n";
	print "    <link>http://".$GLOBALS[sys_default_domain]."/</link>\n";
	print "    <width>124</width>\n";
	print "    <heigth>32</heigth>\n";
	print "  </image>\n";
	
	$res = db_query($sql, $limit);
	
	// ## item outputs
	while ($arr = db_fetch_array($res)) {
		print "  <item>\n";
		
		switch ($arr['section']) {
			case 'commit': {
				print "   <title>".htmlspecialchars('Commit for Tracker Item [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comment>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comment>\n";
				break;
			}
			case 'trackeropen': {
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Opened')."</title>\n";
				print "   <link>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comment>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comment>\n";
				break;
			}
			case 'trackerclose': {
				print "   <title>".htmlspecialchars('Tracker Item [#'.$arr['subref_id'].' '.$arr['description'].'] Closed')."</title>\n";
				print "   <link>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comment>http://".$GLOBALS[sys_default_domain].'/tracker/?func=detail&amp;atid='.$arr['ref_id'].'&amp;aid='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comment>\n";
				break;
			}
			case 'frsrelease': {
				print "   <title>".htmlspecialchars('FRS Release [#'.$arr['description'].']')."</title>\n";
				print "   <link>http://".$GLOBALS[sys_default_domain].'/frs/?release_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comment>http://".$GLOBALS[sys_default_domain].'/frs/?release_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comment>\n";
				break;
			}
			case 'forumpost': {
				print "   <title>".htmlspecialchars('Forum Post [#'.$arr['subref_id'].'] '.$arr['description'])."</title>\n";
				print "   <link>http://".$GLOBALS[sys_default_domain].'/forum/message.php?forum_id='.$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</link>\n";
				print "   <comment>http://".$GLOBALS[sys_default_domain].'/forum/message.php?forum_id='.$arr['ref_id'].'&amp;msg_id='.$arr['subref_id'].'&amp;group_id='.$arr['group_id']."</comment>\n";
				break;
			}		
		}	
	
		print "   <description>".rss_description($arr[description])."</description>\n";
		print "   <author>".$row[user_name]."@".$GLOBALS[sys_users_host]." (".$row[realname].")</author>\n";
		print "   <pubDate>".gmdate('D, d M Y G:i:s',$row[activity_date])." GMT</pubDate>\n";
		print "  </item>\n";
	}
	// ## end output
	print " </channel>\n";	
	
} else {
	// don´t display anything
	/*$sql="SELECT * FROM activity_vw,groups WHERE activity_date BETWEEN '".(time()-(30*86400))."' AND '".time()."'
	 AND activity_vw.group_id = groups.group_id AND groups.is_public=1 ORDER BY activity_date DESC";*/
}


?>
</rss>

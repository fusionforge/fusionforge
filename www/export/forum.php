<?php
/**
 * FusionForge Exports: Export project forums in RSS
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010, Roland Mas
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';

header("Content-Type: text/plain");

$group_id = getIntFromRequest('group_id');

session_require_perm ('project_read', $group_id) ;
$group = group_get_object ($group_id) ;


print '<?xml version="1.0"?>
<!DOCTYPE sf_forum SYSTEM "http://'.forge_get_config('web_host').'/exports/sf_forum_0.1.dtd">
';
print '<group name="'.$group->getPublicName().'">';

$res_forum = db_query_params ('SELECT group_forum_id,forum_name
	FROM forum_group_list
	WHERE group_id=$1',
			      array ($group_id));

while ($row_forum = db_fetch_array($res_forum)) {
	if (!forge_check_perm ('forum', $row_forum['group_forum_id'], 'read')) {
		continue ;
	}
	print ' <forum name="'.$row_forum['forum_name'].'">'."\n";

	$res_post = db_query_params ('SELECT forum.msg_id AS msg_id,forum.subject AS subject,
			forum.body AS body,forum.date AS date,
			users.user_name AS user_name,
			users.realname AS realname
		FROM forum,users
		WHERE forum.posted_by=users.user_id
		AND forum.group_forum_id=$1',
				     array ($row_forum['group_forum_id']));


	// ## item outputs
	while ($row_post = db_fetch_array($res_post)) {
		print "  <nitf version=\"XMLNews/DTD XMLNEWS-STORY 1.8//EN\">\n";
		print "   <head>\n";
		print "    <title>".$row_post['subject']."</title>\n";
		print "   </head>\n";
		print "   <body><body.content><block>\n";
		print $row_post['body'];
		print "   </block></body.content></body>\n";
		print "  </nitf>\n";
	}
	print " </forum>\n";
}

print " </group>\n";
?>

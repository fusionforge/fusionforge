<?php
echo "Disabled Until Security Audited and Using Proper Accessor Functions";
exit;

/**
  *
  * SourceForge Exports: Export project forums in RSS
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';

header("Content-Type: text/plain");

$group_id = getIntFromRequest('group_id');

// group_id must be specified
$res_grp = db_query("
	SELECT group_id,group_name
	FROM groups 
	WHERE is_public=1
	AND status='A'
	AND group_id='$group_id'
");
if (db_numrows($res_grp) < 1) {
	print 'ERROR: This URL must be called with a valid group_id parameter';
	exit;
} else {
	$row_grp = db_fetch_array($res_grp);
}

print '<?xml version="1.0"?>
<!DOCTYPE sf_forum SYSTEM "http://'.$GLOBALS['sys_default_domain'].'/exports/sf_forum_0.1.dtd">
';
print '<group name="'.$row_grp['group_name'].'">';

$res_forum = db_query("
	SELECT group_forum_id,forum_name
	FROM forum_group_list 
	WHERE group_id='$group_id'
");

while ($row_forum = db_fetch_array($res_forum)) {
	print ' <forum name="'.$row_forum['forum_name'].'">'."\n";

	$res_post = db_query("
		SELECT forum.msg_id AS msg_id,forum.subject AS subject,
			forum.body AS body,forum.date AS date,
			users.user_name AS user_name,
			users.realname AS realname
		FROM forum,users 
		WHERE forum.posted_by=users.user_id
		AND forum.group_forum_id='".$row_forum['group_forum_id']."'
	");


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

<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

// ## export SourceForge project list in RSS

include "pre.php";
include "rss_utils.inc";
header("Content-Type: text/plain");
print '<?xml version="1.0"?>
<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
';
$res = db_query(
	 'SELECT group_id,group_name,unix_group_name,homepage,short_description '
	.'FROM groups '
	.'WHERE is_public=1 AND status="A" '
        .'ORDER BY group_id',$limit);

rss_dump_project_result_set($res,'SourceForge Full Project Listing');
?>
</rss>

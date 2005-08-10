<?php
/**
  *
  * SourceForge Exports: Export new releases info in RSS
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('rss_utils.inc');

$limit = getIntFromRequest('limit');

header("Content-Type: text/plain");
print '<?xml version="1.0"?>
<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
';
// ## default limit
if (!$limit < 1) {
	$limit = 10;
}
if ($limit > 100) {
	$limit = 100;
}

$res=db_query("SELECT 
					groups.group_id,
					groups.group_name,
					groups.unix_group_name,
					groups.type_id,
					news_bytes.forum_id,
					news_bytes.summary,
					news_bytes.post_date,
					news_bytes.details 
				FROM 
					news_bytes,
					groups 
				WHERE 
					news_bytes.group_id=groups.group_id 
					AND groups.status='A'
					AND groups.is_public = 1
				ORDER BY 
					post_date 
				DESC",($limit * 3));


// ## one time output
print " <channel>\n";
print "  <copyright>Copyright 1999-2000 VA Linux Systems, Inc.</copyright>\n";
print "  <pubDate>".gmdate('D, d M Y g:i:s',time())." GMT</pubDate>\n";
print "  <description>".$GLOBALS['sys_name']." New Releases</description>\n";
print "  <link>http://$GLOBALS[sys_default_domain]</link>\n";
print "  <title>".$GLOBALS['sys_name']." New Releases</title>\n";
print "  <webMaster>webmaster@$GLOBALS[sys_default_domain]</webMaster>\n";
print "  <language>en-us</language>\n";
// ## item outputs
$outputtotal = 0;
while ($row = db_fetch_array($res)) {
	if (!$G_RELEASE["$row[group_id]"]) {
		print "  <item>\n";
		print "   <title>".htmlspecialchars($row[group_name])."</title>\n";
		print "   <link>http://$GLOBALS[sys_default_domain]/project/showfiles.php?group_id=$row[group_id]</link>\n";
		print "   <description>".rss_description($row[summary])."</description>\n";
		print "  </item>\n";
		$outputtotal++;
	}
	// ## eliminate dupes, only do $limit of these
	$G_RELEASE["$row[group_id]"] = 1;
	if ($outputtotal >= $limit) break;
}
// ## end output
print " </channel>\n";
?>
</rss>

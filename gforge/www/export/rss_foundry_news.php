<?php
/*
 * Export of foundry news in RSS
 * Author: Darrell Brogdon
 *
 * $Id: rss_foundry_news.php,v 1.1 2000/11/21 21:43:36 dbrogdon Exp $
 */

include "pre.php";
include "rss_utils.inc";
header("Content-Type: text/plain");
print '<?xml version="1.0"?>
<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
';
// ## default limit
if (!$limit) $limit = 10;
if ($limit > 100) $limit = 100;

if( isset($foundry_id) ) {
	$res = db_query("SELECT 
						* 
				     FROM 
						news_bytes nb, 
						foundry_news fn 
				     WHERE 
						foundry_id='$foundry_id' AND 
						nb.id=fn.news_id 
				     ORDER BY 
						date
				     DESC", $limit);

	// ## one time output
	print " <channel>\n";
	print "  <copyright>Copyright 1999-2000 VA Linux Systems, Inc.</copyright>\n";
	print "  <pubDate>".gmdate('D, d M Y g:i:s',time())." GMT</pubDate>\n";
	print "  <description>SourceForge Project News Highlights</description>\n";
	print "  <link>http://$GLOBALS[sys_default_domain]</link>\n";
	print "  <title>SourceForge Project News</title>\n";
	print "  <webMaster>webmaster@$GLOBALS[sys_default_domain]</webMaster>\n";
	print "  <language>en-us</language>\n";
	// ## item outputs
	while ($row = db_fetch_array($res)) {
		print "  <item>\n";
		print "   <title>".htmlspecialchars($row[summary])."</title>\n";
		// if news group, link is main page
		if ($row[group_id] != 714) {
			print "   <link>http://$GLOBALS[sys_default_domain]/project/?group_id=$row[group_id]</link>\n";
		} else {
			print "   <link>http://$GLOBALS[sys_default_domain]/</link>\n";
		}
		print "   <description>".rss_description($row[details])."</description>\n";
		print "  </item>\n";
	}
	// ## end output
	print " </channel>\n";
}
?>
</rss>

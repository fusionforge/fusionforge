<?php
/**
  *
  * SourceForge Exports: Export front page news in RSS
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: rss_foundry_news.php,v 1.5 2001/06/08 22:59:17 jbyers Exp $
  *
  */

require_once('pre.php');
require_once('rss_utils.inc');

header("Content-Type: text/plain");
print '<?xml version="1.0"?>
<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">
<rss version="0.91">
';

// default limit
//
if (!$limit) $limit = 10;
if ($limit > 100) $limit = 100;

// execute query
//

$query = "SELECT groups.group_name,
       groups.unix_group_name, 
       users.user_name, 
       news_bytes.summary, 
       news_bytes.date, 
       news_bytes.details 
FROM   users, 
       news_bytes,
       groups,
       foundry_news 
WHERE  foundry_news.foundry_id='$foundry_id' 
       AND users.user_id=news_bytes.submitted_by 
       AND foundry_news.news_id=news_bytes.id 
       AND news_bytes.group_id=groups.group_id 
       AND foundry_news.is_approved=1 
ORDER BY news_bytes.date";

$res = db_query($query, $limit);

// one time output
//
print " <channel>\n";
print "  <copyright>Copyright 1999-2000 VA Linux Systems, Inc.</copyright>\n";
print "  <pubDate>".gmdate('D, d M Y g:i:s',time())." GMT</pubDate>\n";
print "  <description>".$GLOBALS['sys_name']." Project News Highlights</description>\n";
print "  <link>http://$GLOBALS[sys_default_domain]</link>\n";
print "  <title>".$GLOBALS['sys_name']." Project News</title>\n";
print "  <webMaster>webmaster@$GLOBALS[sys_default_domain]</webMaster>\n";
print "  <language>en-us</language>\n";

// item outputs
//
while ($row = db_fetch_array($res)) {

	print "  <item>\n";
	print "   <title> " . htmlspecialchars($row[summary]) . " - " . htmlspecialchars($row[user_name]) . " - " . date("Y-M-d g:i:s", $row[date]) . " - " . htmlspecialchars($row[group_name]). "</title>\n";
  print "   <link>http://$GLOBALS[sys_default_domain]/projects/" . htmlspecialchars($row[unix_group_name]) . "/</link>\n";
	print "   <description>".rss_description($row[details])."</description>\n";
	print "  </item>\n";
}
// ## end output
print " </channel>\n";
?>
</rss>

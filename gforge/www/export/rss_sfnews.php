<?php
/**
  *
  * SourceForge Exports: Export front page news in RSS
  *
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'export/rss_utils.inc';

$group_id = getIntFromRequest('group_id');
$limit = getIntFromRequest('limit', 10);
if ($limit > 100) $limit = 100;

header("Content-Type: text/xml; charset=utf-8");
print '<?xml version="1.0" encoding="UTF-8"?>

<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns="http://purl.org/rss/1.0/"
	xmlns:taxo="http://purl.org/rss/1.0/modules/taxonomy/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:syn="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:admin="http://webns.net/mvcb/" >';

if ($group_id) {
	$where = " AND g.group_id='$group_id'";
} else {
	$where = " AND is_approved=1";
}
$sql = "SELECT forum_id,summary,post_date,details,g.group_id,g.group_name,u.realname 
	FROM news_bytes, groups g,users u 
	WHERE news_bytes.group_id=g.group_id
	AND u.user_id=news_bytes.submitted_by
	AND g.is_public='1'
	AND g.status='A'
	$where
	order by post_date desc";
	$res = db_query($sql, $limit);

print "\n <channel rdf:about=".'"'."http://$GLOBALS[sys_default_domain]/export/rss_sfnews.php".'"'.">\n";
$grsql = "SELECT group_name from groups where group_id='.$group_id'";
$grres = db_query($sql,$limit);
$grrow = db_fetch_array($grres);

print "  <title>".$GLOBALS['sys_name']." Project News</title>\n";
print "  <link>http://$GLOBALS[sys_default_domain]</link>\n";
print "  <description>".$GLOBALS['sys_name']." Project News Highlights</description>\n";
// ## item outputs
print " <items>\n";
print " <rdf:Seq>\n";
while ($row = db_fetch_array($res)) {
	print " <rdf:li rdf:resource=".'"'."http://$GLOBALS[sys_default_domain]/forum/forum.php?forum_id=$row[forum_id]".'"'." />\n";
}
print " </rdf:Seq>\n";
print " </items>\n";
print " </channel>\n";
$res = db_query($sql, $limit);
while ($row = db_fetch_array($res)) {
	print "\n <item rdf:about=".'"'."http://$GLOBALS[sys_default_domain]/forum/forum.php?forum_id=$row[forum_id]".'"'.">\n";
	print "   <title>".htmlspecialchars($row['summary'])."</title>\n";
	// if news group, link is main page
	if ($row['group_id'] != $sys_news_group) {
		print "   <link>http://$GLOBALS[sys_default_domain]/forum/forum.php?forum_id=$row[forum_id]</link>\n";
	} else {
		print "   <link>http://$GLOBALS[sys_default_domain]/</link>\n";
	}
	print "   <description>".rss_description($row['details'])."</description>\n";
	print "   <dc:subject>".$row['group_name']."</dc:subject>\n";
	print "   <dc:creator>".$row['realname']."</dc:creator>\n";
	print "  <dc:date>".gmdate('c', $row['post_date'])."</dc:date>\n";
	print "  </item>\n";
}
// ## end output
?>
</rdf:RDF>

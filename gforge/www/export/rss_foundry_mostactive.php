<?php
/**
 * rss_foundry_mostactive.php - Stats export page for Foundry most active projects.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @author		Darrell Brogdon <dbrogdon@valinux.com>
 * @date		2001-06-06
 * @version		$Id: rss_foundry_mostactive.php,v 1.2 2001/06/14 21:54:23 dbrogdon Exp $
 *
 */
require_once('pre.php');
require_once('rss_utils.inc');

header("Content-Type: text/plain");
print '<?xml version="1.0" encoding="utf-8"?>';
?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="http://www.xml.com/xml/news.rss">
        <title>Most Active Projects</title>
        <description><?php echo $GLOBALS['sys_name']; ?> Site Statistics</description>
<?php
	$sql="SELECT 
				*
		  FROM 
				foundry_project_rankings_agg
		  WHERE 
				foundry_id='$foundry_id'
		  ORDER BY 
				foundry_id ASC, 
				ranking ASC";
	$result=db_query($sql, 20, 0, SYS_DB_STATS);
	if (!$result || db_numrows($result) < 1) {
		echo "	<error/>\n";
	} else {
		while ($row=db_fetch_array($result)) {
			echo "	<item>\n";
			echo '		<title>' . $row['group_name'] . "</title>\n";
			echo '		<description>' . $row['percentile'] . "%</description>\n";
			echo '		<link>http://' . $GLOBALS[sys_default_domain] . '/projects/' . $row['unix_group_name'] . "</link>\n";
			echo "	</item>\n";
		}
	}
?>
    </channel>
</rdf:RDF>

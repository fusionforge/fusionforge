<?php
/**
 * rss_foundry_topdownloads.php - Stats export page for Foundry top downloads.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @author		Darrell Brogdon <dbrogdon@valinux.com>
 * @date		2001-06-06
 * @version		$Id: rss_foundry_topdownloads.php,v 1.2 2001/06/14 21:54:23 dbrogdon Exp $
 *
 */
require_once('pre.php');
require_once('rss_utils.inc');

header("Content-Type: text/plain");
print '<?xml version="1.0" encoding="utf-8"?>';
?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="http://www.xml.com/xml/news.rss">
        <title>Top Downloads</title>
        <description>SourceForge.net Site Statistics</description>
<?php
	$res_topdown = db_query("SELECT 
								* 
							 FROM 
								foundry_project_downloads_agg
							 WHERE
								foundry_id='$foundry_id'
							 ORDER BY 
								foundry_id DESC, 
								downloads DESC", 10, 0, SYS_DB_STATS);
	if (!$res_topdown || db_numrows($res_topdown) < 1) {
		echo "	<item>\n";
		echo "		<title>No Projects</title>\n";
		echo "	</item>\n";
	} else {
		while ($row_topdown = db_fetch_array($res_topdown)) {
			echo "	<item>\n";
			echo '		<title>' . $row_topdown['group_name'] . "</title>\n";
			echo '		<description>' . $row_topdown['downloads'] . "</description>\n";
			echo '		<link>http://' . $GLOBALS[sys_default_domain] . '/projects/' . $row_topdown['group_name'] . "</link>\n";
			echo "	</item>\n";
		}
	}
?>
    </channel>
</rdf:RDF>

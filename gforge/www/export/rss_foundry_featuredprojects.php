<?php
/**
 * rss_foundry_featuredprojects.php - Stats export page for Foundry featured projects.
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @author		Darrell Brogdon <dbrogdon@valinux.com>
 * @date		2001-06-06
 * @version		$Id: rss_foundry_featuredprojects.php,v 1.2 2001/06/14 21:54:23 dbrogdon Exp $
 *
 */
require_once('pre.php');
require_once('rss_utils.inc');

header("Content-Type: text/plain");
print '<?xml version="1.0" encoding="utf-8"?>';
?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="http://www.xml.com/xml/news.rss">
        <title>Featured Projects</title>
        <description>SourceForge.net Site Statistics</description>
<?php
	$sql="SELECT 
			groups.group_name,
			groups.unix_group_name,
			groups.group_id,
			foundry_preferred_projects.rank 
		  FROM 
			groups,
			foundry_preferred_projects 
		  WHERE 
			foundry_preferred_projects.group_id=groups.group_id 
		  AND 
			foundry_preferred_projects.foundry_id='$foundry_id' 
		  ORDER BY 
			rank ASC";
	$res_grp=db_query($sql);
	$rows=db_numrows($res_grp);

	if (!$res_grp || $rows < 1) {
		echo "	No Projects\n";
	} else {
		for ($i=0; $i<$rows; $i++) {
			echo "	<item>\n";
			echo "		<title>Project</title>\n";
			echo '		<description>' . db_result($res_grp,$i,'group_name') . "</description>\n";
			echo '		<link>http://' . $GLOBALS[sys_default_domain] . '/projects/' . db_result($res_grp,$i,'unix_group_name') . "</link>\n";
			echo "	</item>\n";
		}
	}
?>
    </channel>
</rdf:RDF>

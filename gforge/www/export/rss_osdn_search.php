<?php
/**
 * rss_osdn_search.php - Global OSDN search export
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @author		Darrell Brogdon <dbrogdon@valinux.com>
 * @date		2001-06-15
 * @version		$Id: rss_osdn_search.php,v 1.2 2001/06/21 21:38:00 jbyers Exp $
 *
 */
require_once('pre.php');
require_once('rss_utils.inc');

header("Content-Type: text/plain");
print '<?xml version="1.0" encoding="utf-8"?>';
?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
    <channel rdf:about="http://www.xml.com/xml/news.rss">
        <title>OSDN Universal Search</title>
        <description><?php echo $GLOBALS['sys_name']; ?> export for OSDN Universal search</description>
<?php
	$result=search_projects();
	if (!$result || db_numrows($result) < 1) {
		echo "	<item>\n";
		echo "		<title>No Data</title>\n";
		echo "	</item>\n";
	} else {
		while ($row=db_fetch_array($result)) {
			$weight = 10;
			if (stristr($row['group_name'], $query_text)) {
				$weight += 10;
			}

			if (stristr($row['short_description'], $query_text)) {
				$weight += 10;
			}

			echo "	<item>\n";
			echo '		<title>' . $row['group_name'] . "</title>\n";
			echo '		<description>' . $row['short_description'] . "</description>\n";
			echo '		<link>http://' . $GLOBALS[sys_default_domain] . '/projects/' . $row['unix_group_name'] . "</link>\n";
			echo "		<osdn:weight>$weight</osdn:weight>\n";
			echo "	</item>\n";
		}
	}
?>
    </channel>
</rdf:RDF>

<?php
	function search_projects() {
		global $query_text, $offset;

		// If multiple words in the query text, separate them and put ILIKE (pgsql's 
		// case-insensitive LIKE) in between
		// XXX:SQL: this assumes db understands backslash-quoting

		$array=explode(" ",quotemeta($query_text));
		// we need to use double-backslashes in SQL
		$array_re=explode(" ",addslashes(quotemeta($query_text)));
	
		$query_text1="group_name ILIKE '%" . implode($array,"%' $crit group_name ILIKE '%") ."%'";
		$query_text2="short_description ILIKE '%" . implode($array,"%' $crit short_description ILIKE '%") ."%'";
		$query_text3="unix_group_name ILIKE '%" . implode($array,"%' $crit unix_group_name ILIKE '%") . "%'";
	
		$sql = "SELECT 
					group_name,
					unix_group_name,
					type,
					group_id,
					short_description 
				FROM 
					groups 
				WHERE 
					status='A' 
				AND 
					is_public='1' 
				AND 
					(($query_text1) OR ($query_text2) OR ($query_text3))";
		return db_query($sql, 15, $offset);			
	}
?>

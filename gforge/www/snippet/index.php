<?php
/**
  *
  * SourceForge Code Snippets Repository
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */
require_once('pre.php');
require_once('common/include/vars.php');
require_once('www/snippet/snippet_utils.php');

/**
 * create_snippet_hash() - A little utility function to reduce duplicated code in snippet_mainpage()
 * 
 * @param	sql	String	A SQL query to fetch either snippets or categories from the database
 * @param	field	String	The field name - either 'language' or 'category'
 * @return An associative array filled with the results of the SQL query
 */
function create_snippet_hash($sql, $field) {
	$res = db_query($sql);
	$target = array();
	while ($row = db_fetch_array($res)) {
		$target[$row[$field]] = $row['count'];
	}
	return $target;
}

/**
 * snippet_mainpage() - Show the main page for the snippet library.
 */
function snippet_mainpage() {
	global $SCRIPT_LANGUAGE,$SCRIPT_CATEGORY;
	global $Language;
	$return .=
	'<div style="font-family:arial,helvetica">
	'.$Language->getText('snippet_caching','intro').'
	<br />
	<p>
	<table width="100%" border="0">
	<tr><td>
	</td></tr>
	<tr><td>
	<strong>'.$Language->getText('snippet_caching','browse_by_language').':</strong>
	<ul>';

	$existing_snippets = create_snippet_hash("SELECT language, count(*) as count from snippet group by language", "language");
	for ($i=1; $i<count($SCRIPT_LANGUAGE); $i++) {
		$return .= '<li><a href="/snippet/browse.php?by=lang&lang='.$i.'">'.$SCRIPT_LANGUAGE[$i].'</a> (';
		if ($existing_snippets[$i]) {
			$return .= $existing_snippets[$i].')</li>';
		} else {
			$return .= '0)</li>';
		}
	}

	$return .= 	
	'</ul></td><td>
	<strong>'.$Language->getText('snippet_caching','browse_by_category').':</strong>
	<ul>';
	
	$existing_categories = create_snippet_hash("SELECT category, count(*) as count from snippet group by category", "category");
	for ($i=1; $i<count($SCRIPT_CATEGORY); $i++) {
		$return .= '<li><a href="/snippet/browse.php?by=cat&cat='.$i.'">'.$SCRIPT_CATEGORY[$i].'</a> (';
		if ($existing_categories[$i]) {
			$return .= $existing_categories[$i].')</li>';
		} else {
			$return .= '0)</li>';
		}
	}

	$return .= '</ul></td> </tr> </table></div>';
	return $return;
}

snippet_header(array('title'=>$Language->getText('snippet_index','title'), 'header'=>'Snippet Library','pagename'=>'snippet'));
echo snippet_mainpage();
snippet_footer(array());

?>

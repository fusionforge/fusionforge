<?php
/**
 * snippet_caching.php
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

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

	$count=count($SCRIPT_LANGUAGE);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE language=$i";
		$result = db_query ($sql);

		$return .= '
		<li><a href="/snippet/browse.php?by=lang&lang='.$i.'">'.$SCRIPT_LANGUAGE[$i].'</a> ('.db_result($result,0,0).')</li>';
	}

	$return .= 	
	'</ul></td>
	<td>
	<strong>'.$Language->getText('snippet_caching','browse_by_category').':</strong>
	<ul>';

	$count=count($SCRIPT_CATEGORY);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE category=$i";
		$result = db_query ($sql);

		$return .= '
		<li><a href="/snippet/browse.php?by=cat&cat='.$i.'">'.$SCRIPT_CATEGORY[$i].'</a> ('.db_result($result,0,0).')</li>';
	}


	$return .=
	'</ul></td>
	</tr>
	</table></div>';

	return $return;

}

?>

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

	$return .=
	'<div style="font-family:arial,helvetica">

	<p>The purpose of this archive is to let you share your code snippets, scripts,
	and functions with the Open Source Software Community.</p>

	<p>You can create a "new snippet", then post additional versions of that
	snippet quickly and easily.</p>

	<p>Once you have snippets posted, you can then create a "Package" of snippets.
	That package can contain multiple, specific versions of other snippets.</p>
	<p>&nbsp;</p>
	<h3>Browse Snippets</h3>
	
	<p>You can browse the snippet library quickly:</p>
	<br />
	<p>
	<table width="100%" border="0">
	<tr><td>

	</td></tr>

	<tr><td>
	<strong>Browse by Language:</strong>
	<p>';

	$count=count($SCRIPT_LANGUAGE);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE language=$i";
		$result = db_query ($sql);

		$return .= '
		<li><a href="/snippet/browse.php?by=lang&lang='.$i.'">'.$SCRIPT_LANGUAGE[$i].'</a> ('.db_result($result,0,0).')</li>';
	}

	$return .= 	
	'</p></td>
	<td>
	<strong>Browse by Category:</strong>
	<p>';

	$count=count($SCRIPT_CATEGORY);
	for ($i=1; $i<$count; $i++) {
		$sql="SELECT count(*) FROM snippet WHERE category=$i";
		$result = db_query ($sql);

		$return .= '
		<li><a href="/snippet/browse.php?by=cat&cat='.$i.'">'.$SCRIPT_CATEGORY[$i].'</a> ('.db_result($result,0,0).')</li>';
	}


	$return .=
	'</p></td>
	</tr>
	</table></div>';

	return $return;

}

?>

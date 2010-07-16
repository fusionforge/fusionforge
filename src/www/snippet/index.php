<?php
/**
  *
  * SourceForge Code Snippets Repository
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  */
require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'snippet/snippet_utils.php';

/**
 * create_snippet_hash() - A little utility function to reduce duplicated code in snippet_mainpage()
 * 
 * @param	qpa	Array	A query+parameter array
 * @param	field	String	The field name - either 'language' or 'category'
 * @return An associative array filled with the results of the SQL query
 */
function create_snippet_hash($qpa, $field) {
	$res = db_query_qpa($qpa);
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
	$return =_('<p>The purpose of this archive is to let you share your code snippets, scripts,	and functions with the Open Source Software Community.</p><p>You can create a "new snippet", then post additional versions of that snippet quickly and easily.</p><p>Once you have snippets posted, you can then create a "Package" of snippets. That package can contain multiple, specific versions of other snippets.</p><p>&nbsp;</p><h3>Browse Snippets</h3>	<p>You can browse the snippet library quickly:</p>').'
	<br />
	<p/>
	<table width="100%" border="0">
	<tr><td>
	</td></tr>
	<tr><td>
	<strong>'._('Browse by Language').':</strong>
	<ul>';

	$qpa = db_construct_qpa (false, 'SELECT language, count(*) as count from snippet group by language') ;
	$existing_snippets = create_snippet_hash($qpa, "language");
	for ($i=1; $i<count($SCRIPT_LANGUAGE); $i++) {
		$return .= '<li>'.util_make_link ('/snippet/browse.php?by=lang&amp;lang='.$i,$SCRIPT_LANGUAGE[$i]).' (';

		if (isset($existing_snippets[$i])) {
			$return .= $existing_snippets[$i].')</li>';
		} else {
			$return .= '0)</li>';
		}
	}

	$return .= 	
	'</ul></td><td>
	<strong>'._('Browse by Category').':</strong>
	<ul>';
	
	$qpa = db_construct_qpa (false, 'SELECT category, count(*) as count from snippet group by category') ;
	$existing_categories = create_snippet_hash($qpa, "category");
	for ($i=1; $i<count($SCRIPT_CATEGORY); $i++) {
		// Remove warning
		@$return .= '<li>'.util_make_link ('/snippet/browse.php?by=cat&amp;cat='.$i,$SCRIPT_CATEGORY[$i]).' (';

		if (isset($existing_categories[$i])) {
			$return .= $existing_categories[$i].')</li>';
		} else {
			$return .= '0)</li>';
		}
	}

	$return .= '</ul></td> </tr> </table>';
	return $return;
}

snippet_header(array('title'=>_('Snippet Library'), 'header'=>'Snippet Library'));
echo snippet_mainpage();
snippet_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

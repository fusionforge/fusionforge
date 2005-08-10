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
require_once('www/snippet/snippet_utils.php');

/**
 * createSnippetQuery - Creates the SQL query for loading data about snippets
 *
 * @param	string	clause - the last part of the where clause
 */
function createSnippetQuery($clause) {
	return "SELECT users.realname,users.user_name,snippet.description,snippet.snippet_id,snippet.name FROM snippet,users WHERE users.user_id=snippet.created_by AND ".$clause;
}

/**
 * createPackageQuery - Creates the SQL query for loading data about packages
 *
 * @param	string	clause - the last part of the where clause
 */
function createPackageQuery($clause) {
	return "SELECT users.realname,users.user_name,snippet_package.description,snippet_package.snippet_package_id,snippet_package.name FROM snippet_package,users WHERE users.user_id=snippet_package.created_by AND ".$clause;
}

snippet_header(array('title'=>$Language->getText('snippet_browse','title'), 'header'=>'','pagename'=>'snippet_browse'));

$by = getStringFromRequest('by');

if ($by=='lang') {
	$lang = getStringFromRequest('lang');
	$sql=createSnippetQuery("snippet.language='$lang'");
	$sql2=createPackageQuery("snippet_package.language='$lang'");
	echo '<h2>' .$Language->getText('snippet_browse','snippets_by_language', array($SCRIPT_LANGUAGE[$lang])).'</h2>';
} else if ($by=='cat') {
	$cat = getStringFromRequest('cat');
	$sql=createSnippetQuery("snippet.category='$cat'");
	$sql2=createPackageQuery("snippet_package.category='$cat'");
	echo '<h2>' .$Language->getText('snippet_browse','snippet_by_category', array($SCRIPT_CATEGORY[$cat])).'</h2>';
} else {
	exit_error($Language->getText('general','error'),$Language->getText('snippet_browse','error_bad_url'));
}

$result=db_query($sql);
$rows=db_numrows($result);

$result2=db_query($sql2);
$rows2=db_numrows($result2);

if ((!$result || $rows < 1) && (!$result2 || $rows2 < 1)) {
	echo '<h2>' .$Language->getText('snippet_browse','no_snippets_found').'</h2>';
} else {

	$title_arr=array();
	$title_arr[]= $Language->getText('snippet_browse','snippet_id');
	$title_arr[]= $Language->getText('snippet_browse','snippet_title');
	$title_arr[]= $Language->getText('snippet_browse','Creator');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	//	List packages if there are any
	if ($rows2 > 0) {
		echo '
			<tr style="background-color:#efefef"><td colspan="3"><strong>' .$Language->getText('snippet_browse','packages_of_snippets').'</strong><td';
	}
	for ($i=0; $i<$rows2; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2"><a href="/snippet/detail.php?type=package&amp;id='.
			db_result($result2,$i,'snippet_package_id').'"><strong>'.
			db_result($result2,$i,'snippet_package_id').'</strong></a></td><td><strong>'.
			db_result($result2,$i,'name').'</td><td>'.
			$GLOBALS['HTML']->createLinkToUserHome(db_result($result2, $i, 'user_name'), db_result($result2, $i, 'realname')).'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result2,$i,'description'))).'</td></tr>';
	}

	//	List snippets if there are any
	if ($rows > 0) {
		echo '
			<tr style="background-color:#efefef"><td colspan="3"><strong>' .$Language->getText('snippet_browse','snippets').'</strong></td>';
	}
	for ($i=0; $i<$rows; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2"><a href="/snippet/detail.php?type=snippet&amp;id='.
			db_result($result,$i,'snippet_id').'"><strong>'.
			db_result($result,$i,'snippet_id').'</strong></a></td><td><strong><a href="/snippet/detail.php?type=snippet&amp;id='.
			db_result($result,$i,'snippet_id').'">'.db_result($result,$i,'name').'</a></td><td>'.
			$GLOBALS['HTML']->createLinkToUserHome(db_result($result, $i, 'user_name'), db_result($result, $i, 'realname')).'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result,$i,'description'))).'</td></tr>';
	}
	echo $GLOBALS['HTML']->listTableBottom();
}
snippet_footer(array());
?>

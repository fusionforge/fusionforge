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

snippet_header(array('title'=>'Snippet Library', 'header'=>'','pagename'=>'snippet_browse'));

if ($by=='lang') {

	$sql="SELECT users.user_name,snippet.description,snippet.snippet_id,snippet.name ".
		"FROM snippet,users ".
		"WHERE users.user_id=snippet.created_by AND snippet.language='$lang'";

	$sql2="SELECT users.user_name,snippet_package.description,snippet_package.snippet_package_id,snippet_package.name ".
		"FROM snippet_package,users ".
		"WHERE users.user_id=snippet_package.created_by AND snippet_package.language='$lang'";

	echo '<h2>Snippets by language: '.$SCRIPT_LANGUAGE[$lang].'</h2>';

} else if ($by=='cat') {

	$sql="SELECT users.user_name,snippet.description,snippet.snippet_id,snippet.name ".
		"FROM snippet,users ".
		"WHERE users.user_id=snippet.created_by AND snippet.category='$cat'";

	$sql2="SELECT users.user_name,snippet_package.description,snippet_package.snippet_package_id,snippet_package.name ".
		"FROM snippet_package,users ".
		"WHERE users.user_id=snippet_package.created_by AND snippet_package.category='$cat'";

	echo '<h2>Snippets by category: '.$SCRIPT_CATEGORY[$cat].'</h2>';

} else {

	exit_error('Error','Error - bad url?');

}

$result=db_query($sql);
$rows=db_numrows($result);

$result2=db_query($sql2);
$rows2=db_numrows($result2);

if ((!$result || $rows < 1) && (!$result2 || $rows2 < 1)) {
	echo '<h2>No snippets found</h2>';
} else {

	$title_arr=array();
	$title_arr[]='Snippet ID';
	$title_arr[]='Title';
	$title_arr[]='Creator';

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	/*
		List packages if there are any
	*/
	if ($rows2 > 0) {
		echo '
			<tr style="background-color:#efefef"><td colspan="3"><strong>Packages Of Snippets</strong><td';
	}
	for ($i=0; $i<$rows2; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2"><a href="/snippet/detail.php?type=package&amp;id='.
			db_result($result2,$i,'snippet_package_id').'"><strong>'.
			db_result($result2,$i,'snippet_package_id').'</strong></a></td><td><strong>'.
			db_result($result2,$i,'name').'</td><td>'.
			db_result($result2,$i,'user_name').'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result2,$i,'description'))).'</td></tr>';
	}


	/*
		List snippets if there are any
	*/

	if ($rows > 0) {
		echo '
			<tr style="background-color:#efefef"><td colspan="3"><strong>Snippets</strong></td>';
	}
	for ($i=0; $i<$rows; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2"><a href="/snippet/detail.php?type=snippet&amp;id='.
			db_result($result,$i,'snippet_id').'"><strong>'.
			db_result($result,$i,'snippet_id').'</strong></a></td><td><strong>'.
			db_result($result,$i,'name').'</td><td>'.
			db_result($result,$i,'user_name').'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result,$i,'description'))).'</td></tr>';
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

snippet_footer(array());

?>

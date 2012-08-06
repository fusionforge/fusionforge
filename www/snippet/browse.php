<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'snippet/snippet_utils.php';

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
	return "SELECT users.realname,users.user_name,users.user_id,snippet_package.description,snippet_package.snippet_package_id,snippet_package.name FROM snippet_package,users WHERE users.user_id=snippet_package.created_by AND ".$clause;
}

snippet_header(array('title'=>_('Snippet Library'), 'header'=>''));

$by = getStringFromRequest('by');

$qpa = db_construct_qpa (false, 'SELECT users.realname,users.user_name,snippet.description,snippet.snippet_id,snippet.name FROM snippet,users WHERE users.user_id=snippet.created_by ') ;
$qpa2 = db_construct_qpa (false, 'SELECT users.realname,users.user_name,users.user_id,snippet_package.description,snippet_package.snippet_package_id,snippet_package.name FROM snippet_package,users WHERE users.user_id=snippet_package.created_by ') ;

if ($by=='lang') {
	$lang = getStringFromRequest('lang');

	$qpa = db_construct_qpa ($qpa, ' AND snippet.language=$1', array ($lang)) ;
	$qpa2 = db_construct_qpa ($qpa2, ' AND snippet_package.language=$1', array ($lang)) ;

	echo '<h2>' .sprintf(_('Snippets by language: %1$s'), $SCRIPT_LANGUAGE[$lang]).'</h2>';
} else if ($by=='cat') {
	$cat = getStringFromRequest('cat');

	$qpa = db_construct_qpa ($qpa, ' AND snippet.category=$1', array ($cat)) ;
	$qpa2 = db_construct_qpa ($qpa2, ' AND snippet_package.category=$1', array ($cat)) ;

	echo '<h2>' .sprintf(_('Snippets by category: %1$s'), $SCRIPT_CATEGORY[$cat]).'</h2>';
} else {
	exit_error(_('Error - bad url?'));
}

$result = db_query_qpa ($qpa) ;
$rows=db_numrows($result);
$result2 = db_query_qpa ($qpa2) ;
$rows2=db_numrows($result2);

if ((!$result || $rows < 1) && (!$result2 || $rows2 < 1)) {
	echo '<h2>' ._('No snippets found').'</h2>';
} else {

	$title_arr=array();
	$title_arr[]= _('Snippet ID');
	$title_arr[]= _('Title');
	$title_arr[]= _('Creator');

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	//	List packages if there are any
	if ($rows2 > 0) {
		echo '
			<tr class="tableheading"><td colspan="3">' ._('Packages Of Snippets').'<td>';
	}
	for ($i=0; $i<$rows2; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2">'.
			util_make_link ('/snippet/detail.php?type=package&amp;id='.db_result($result2,$i,'snippet_package_id'),'<strong>'.db_result($result2,$i,'snippet_package_id').'</strong>').'</td><td><strong>'.
			db_result($result2,$i,'name').'</td><td>'.
			util_make_link_u (db_result($result2, $i, 'user_name'), db_result($result2, $i, 'user_id'), db_result($result2, $i, 'realname')).'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result2,$i,'description'))).'</td></tr>';
	}

	//	List snippets if there are any
	if ($rows > 0) {
		echo '
			<tr class="tableheading"><td colspan="3">' ._('Snippets').'</td>';
	}
	for ($i=0; $i<$rows; $i++) {
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td rowspan="2">'.
			util_make_link ('/snippet/detail.php?type=snippet&amp;id='.db_result($result,$i,'snippet_id'),'<strong>'.db_result($result,$i,'snippet_id').'</strong>').
			'</td><td><strong>'.
			util_make_link ('/snippet/detail.php?type=snippet&amp;id='.db_result($result,$i,'snippet_id'),db_result($result,$i,'name')).
			'</strong></td><td>'.
			util_make_link_u (db_result($result, $i, 'user_name'), db_result($result, $i, 'user_id'), db_result($result, $i, 'realname')).'</td></tr>';
		echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="2">'.util_make_links(nl2br(db_result($result,$i,'description'))).'</td></tr>';
	}
	echo $GLOBALS['HTML']->listTableBottom();
}
snippet_footer(array());
?>

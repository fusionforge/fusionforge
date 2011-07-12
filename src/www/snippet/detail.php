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

/*
	Show a detail page for either a snippet or a package
	or a specific version of a package
*/

$type = getStringFromRequest('type');
$id = getIntFromRequest('id');

if ($type=='snippet') {
	/*
		View a snippet and show its versions
		Expand and show the code for the latest version
	*/

	snippet_header(array('title'=>_('Snippet Library')));

	snippet_show_snippet_details($id);

	/*
		Get all the versions of this snippet
	*/
	$result=db_query_params("SELECT users.realname,users.user_name,users.user_id,snippet_version.snippet_version_id,snippet_version.version,snippet_version.post_date,snippet_version.changes
FROM snippet_version,users
WHERE users.user_id=snippet_version.submitted_by AND snippet_id=$1
ORDER BY snippet_version.snippet_version_id DESC", array($id));

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<div class="error">' ._('Error - no versions found').'</div>';
	} else {
		echo '
		<h3>' ._('Versions Of This Snippet:').'</h3>
		<p>';
		$title_arr=array();
		$title_arr[]= _('Snippet ID');
		$title_arr[]= _('Download Version');
		$title_arr[]= _('Date Posted');
		$title_arr[]= _('Author');
		$title_arr[]= _('Delete');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		/*
			get the newest version of this snippet, so we can display its code
		*/
		$newest_version=db_result($result,0,'snippet_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
				<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.db_result($result,$i,'snippet_version_id').
				'</td><td>'.
				util_make_link ('/snippet/download.php?type=snippet&amp;id='.db_result($result,$i,'snippet_version_id'),'<strong>'. db_result($result,$i,'version').'</strong>').'</td><td>'.
				date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</td><td>'.
				util_make_link_u (db_result($result, $i, 'user_name'), db_result($result, $i, 'user_id'),db_result($result, $i, 'realname')).'</td>'.
				'<td style="text-align:center"><a href="'.util_make_url ('/snippet/delete.php?type=snippet&amp;snippet_version_id='.db_result($result,$i,'snippet_version_id')).'">' . html_image("ic/trash.png","16","16",array("border"=>"0")) . '</a></td></tr>';

				if ($i != ($rows - 1)) {
					echo '
					<tr'.$row_color.'><td colspan="5">' ._('Changes since last version:').'<br />'.
					nl2br(db_result($result,$i,'changes')).'</td></tr>';
				}
		}

		echo $GLOBALS['HTML']->listTableBottom();

		echo '
		</p><p>'._('Download a raw-text version of this code by clicking on &quot;<strong>Download Version</strong>&quot;').'
		</p>';
	/*
		show the latest version of this snippet's code
	*/
	$result=db_query_params ('SELECT code,version FROM snippet_version WHERE snippet_version_id=$1',
			array($newest_version));

	echo '
		<p>&nbsp;</p>
		<hr />
		<h2>'._('Latest Snippet Version: ').db_result($result,0,'version').'</h2>
		<p>
		<span class="snippet-detail">'. db_result($result,0,'code') .'
		</span>
		</p>';
	/*
		Show a link so you can add a new version of this snippet
	*/
	echo '
	<h3><a href="'.util_make_url ('/snippet/addversion.php?type=snippet&amp;id='.htmlspecialchars($id)).'"><span class="important">'._('Submit a new version').'</span></a></h3>
	<p>' ._('You can submit a new version of this snippet if you have modified it and you feel it is appropriate to share with others.').'.</p>';

	}
	snippet_footer(array());

} else if ($type=='package') {
	/*


		View a package and show its versions
		Expand and show the snippets for the latest version


	*/

	snippet_header(array('title'=>_('Snippet Library')));

	snippet_show_package_details($id);

	/*
		Get all the versions of this package
	*/
	$result = db_query_params ('SELECT users.realname,users.user_name,users.user_id,snippet_package_version.snippet_package_version_id,
snippet_package_version.version,snippet_package_version.post_date
FROM snippet_package_version,users
WHERE users.user_id=snippet_package_version.submitted_by AND snippet_package_id=$1
ORDER BY snippet_package_version.snippet_package_version_id DESC',
				   array($id));

	$rows=db_numrows($result);
	if (!$result || $rows < 1) {
		echo '<div class="error">' ._('Error - no versions found').'</div>';
	} else {
		echo '
		<h3>' ._('Versions Of This Package:').'</h3>
		<p>';
		$title_arr=array();
		$title_arr[]= _('Package Version');
		$title_arr[]= _('Date Posted');
		$title_arr[]= _('Author');
		$title_arr[]= _('Edit/Del');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		/*
			determine the newest version of this package,
			so we can display the snippets that it contains
		*/
		$newest_version=db_result($result,0,'snippet_package_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.
			util_make_link ('/snippet/detail.php?type=packagever&amp;id='.db_result($result,$i,'snippet_package_version_id'),'<strong>'.db_result($result,$i,'version').'</strong>').'</td><td>'.
				date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</td><td>'.
				util_make_link_u (db_result($result, $i, 'user_name'), db_result($result, $i, 'user_id'),db_result($result, $i, 'realname')).'</td>'.
				'<td style="text-align:center"><a href="'.util_make_url ('/snippet/add_snippet_to_package.php?snippet_package_version_id='.db_result($result,$i,'snippet_package_version_id')).
				'">' . html_image("ic/pencil.png","20","25") .
				'</a> &nbsp; &nbsp; &nbsp; <a href="'.
				util_make_url ('/snippet/delete.php?type=package&snippet_package_version_id='.db_result($result,$i,'snippet_package_version_id')).
				'">' . html_image("ic/trash.png","16","16") . '</a></td></tr>';
		}

		echo $GLOBALS['HTML']->listTableBottom();

		echo '
		</p><p>' ._('Download a raw-text version of this code by clicking on &quot;<strong>Download Version</strong>&quot;').'
		</p>';

	/*
		show the latest version of the package
		and its snippets
	*/

	echo '
		<p>&nbsp;</p>
		<hr />
		<h2>' ._('Latest Package Version: ').db_result($result,0,'version').'</h2>
		<p>&nbsp;</p>
		<p>&nbsp;</p>';
	snippet_show_package_snippets($newest_version);

	/*
		Show a form so you can add a new version of this package
	*/
	echo '
	<h3><a href="'.util_make_url ('/snippet/addversion.php?type=package&amp;id='.$id).'"><span class="important">' ._('Submit a new version').'</span></a></h3>
	<p>' ._('You can submit a new version of this package if you have modified it and you feel it is appropriate to share with others.').'.</p>';

	}
	snippet_footer(array());

} else if ($type=='packagever') {
	/*
		Show a specific version of a package and its specific snippet versions
	*/

	snippet_header(array('title'=>_('Snippet Library')));

	snippet_show_package_details($id);

	snippet_show_package_snippets($id);

	snippet_footer(array());

} else {

	exit_error(_('Error - was the URL mangled?'));

}

?>

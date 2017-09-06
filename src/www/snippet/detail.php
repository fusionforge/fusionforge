<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2013, French Ministry of National Education
 * Copyright 2014,2016-2017, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'snippet/snippet_utils.php';

global $HTML;

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
		echo $HTML->error_msg(_('Error')._(': ')._('no versions found'));
	} else {
		echo html_e('h3', array(), _('Versions Of This Snippet')._(':'));
		$title_arr = array();
		$title_arr[] = _('Snippet ID');
		$title_arr[] = _('Download Version');
		$title_arr[] = _('Date Posted');
		$title_arr[] = _('Author');
		$title_arr[] = _('Delete');

		echo $HTML->listTableTop($title_arr);

		/*
			get the newest version of this snippet, so we can display its code
		*/
		$newest_version = db_result($result,0,'snippet_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
				<tr><td>'.db_result($result,$i,'snippet_version_id').
				'</td><td>'.
				util_make_link('/snippet/download.php?type=snippet&id='.db_result($result,$i,'snippet_version_id'), '<strong>'. db_result($result,$i,'version').'</strong>').'</td><td>'.
				date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</td><td>'.
				util_make_link_u(db_result($result, $i, 'user_name'), db_result($result, $i, 'user_id'), db_result($result, $i, 'realname')).'</td>'.
				'<td class="align-center">'.util_make_link('/snippet/delete.php?type=snippet&snippet_version_id='.db_result($result,$i,'snippet_version_id'), $HTML->getDeletePic(_('Delete this version'), _('Delete'))).'</td></tr>';

				if ($i != ($rows - 1)) {
					echo '
					<tr><td colspan="5">' ._('Changes since last version:').'<br />'.
					nl2br(db_result($result,$i,'changes')).'</td></tr>';
				}
		}

		echo $HTML->listTableBottom();

		echo html_e('p', array(), _('Download a raw-text version of this code by clicking on “Download Version”'));
	/*
		show the latest version of this snippet's code
	*/
	$result=db_query_params ('SELECT code,version FROM snippet_version WHERE snippet_version_id=$1',
			array($newest_version));

	echo html_e('hr').html_e('h2', array(), _('Latest Snippet Version')._(': ').db_result($result,0,'version'));
	echo '
		<p>
		<span class="snippet-detail"><pre>'. db_result($result,0,'code') .'
		</pre></span>
		</p>';
	/*
		Show a link so you can add a new version of this snippet
	*/
	echo html_e('h3', array(), util_make_link('/snippet/addversion.php?type=snippet&id='.htmlspecialchars($id), _('Add a new version'))).
		html_e('p', array(), _('You can submit a new version of this snippet if you have modified it and you feel it is appropriate to share with others.'));

	}
	snippet_footer();

} elseif ($type=='package') {
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
		echo $HTML->error_msg(_('Error')._(': ')._('no versions found'));
	} else {
		echo '
		<h3>' ._('Versions Of This Package')._(':').'</h3>
		<p>';
		$title_arr=array();
		$title_arr[]= _('Package Version');
		$title_arr[]= _('Date Posted');
		$title_arr[]= _('Author');
		$title_arr[]= _('Actions');

		echo $HTML->listTableTop($title_arr);

		/*
			determine the newest version of this package,
			so we can display the snippets that it contains
		*/
		$newest_version=db_result($result,0,'snippet_package_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr><td>'.
			util_make_link('/snippet/detail.php?type=packagever&id='.db_result($result,$i,'snippet_package_version_id'), '<strong>'.db_result($result,$i,'version').'</strong>').'</td><td>'.
				date(_('Y-m-d H:i'),db_result($result,$i,'post_date')).'</td><td>'.
				util_make_link_u (db_result($result, $i, 'user_name'), db_result($result, $i, 'user_id'),db_result($result, $i, 'realname')).'</td>'.
				'<td class="align-center">'.
				util_make_link('/snippet/add_snippet_to_package.php?snippet_package_version_id='.db_result($result,$i,'snippet_package_version_id'), html_image("ic/pencil.png", 20, 25)).
				'&nbsp; &nbsp; &nbsp; '.
				util_make_link('/snippet/delete.php?type=package&snippet_package_version_id='.db_result($result,$i,'snippet_package_version_id'), $HTML->getDeletePic(_('Delete this snippet'), _('Delete'))).'</td></tr>';
		}

		echo $HTML->listTableBottom();

		echo '
		</p><p>'._('Download a raw-text version of this code by clicking on “Download Version”').'
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
		<h3>'.util_make_link('/snippet/addversion.php?type=package&id='.$id, _('Add a new version')).'</h3>
		<p>' ._('You can submit a new version of this package if you have modified it and you feel it is appropriate to share with others.').'.</p>';

	}
	snippet_footer();

} elseif ($type=='packagever') {
	/*
		Show a specific version of a package and its specific snippet versions
	*/

	snippet_header(array('title'=>_('Snippet Library')));
	snippet_show_package_details($id);
	snippet_show_package_snippets($id);
	snippet_footer();

} else {

	exit_error(_('Error: mangled URL?'));

}

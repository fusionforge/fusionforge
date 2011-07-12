<?php
/**
 * Code Snippets Repository
 *
 * Copyright 1999-2001 (c) VA Linux Systems - Tim Perdue
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

$SCRIPT_CATEGORY[0]= _('Choose One');
$SCRIPT_CATEGORY[1]= _('UNIX Admin');
$SCRIPT_CATEGORY[2]= _('HTML Manipulation');
$SCRIPT_CATEGORY[3]= _('BBS Systems');
$SCRIPT_CATEGORY[4]= _('Auctions');
$SCRIPT_CATEGORY[5]= _('Calendars');
$SCRIPT_CATEGORY[6]= _('Database Manipulation');
$SCRIPT_CATEGORY[7]= _('Searching');
$SCRIPT_CATEGORY[8]= _('File Management');
$SCRIPT_CATEGORY[9]= _('Games');
$SCRIPT_CATEGORY[10]= _('Voting');
$SCRIPT_CATEGORY[11]= _('Shopping Carts');
$SCRIPT_CATEGORY[12]= _('Other');
$SCRIPT_CATEGORY[13]= _('Math Functions');

$SCRIPT_TYPE[0]= _('Choose One');
$SCRIPT_TYPE[1]= _('Function');
$SCRIPT_TYPE[2]= _('Full Script');
$SCRIPT_TYPE[3]= _('Sample Code (HOWTO)');
$SCRIPT_TYPE[4]= _('README');
$SCRIPT_TYPE[5]= _('Class');

$SCRIPT_LICENSE = array();
$SCRIPT_LICENSE[0] = 'GNU General Public License';
$SCRIPT_LICENSE[1] = 'GNU Library Public License';
$SCRIPT_LICENSE[2] = 'BSD License';
$SCRIPT_LICENSE[3] = 'MIT/X Consortium License';
$SCRIPT_LICENSE[4] = 'Artistic License';
$SCRIPT_LICENSE[5] = 'Mozilla Public License';
$SCRIPT_LICENSE[6] = 'Qt Public License';
$SCRIPT_LICENSE[7] = 'IBM Public License';
$SCRIPT_LICENSE[8] = 'Collaborative Virtual Workspace License';
$SCRIPT_LICENSE[9] = 'Ricoh Source Code Public License';
$SCRIPT_LICENSE[10] = 'Python License';
$SCRIPT_LICENSE[11] = 'zlib/libpng License';
$SCRIPT_LICENSE[12] = 'WebSite Only';
$SCRIPT_LICENSE[13] = 'Other';
$SCRIPT_LICENSE[12] = _('WebSite Only');
$SCRIPT_LICENSE[13] = _('Other');

$SCRIPT_LANGUAGE = array();
$SCRIPT_LANGUAGE[0] = _('Choose One');
$SCRIPT_LANGUAGE[1] = _('Other Language');
$SCRIPT_LANGUAGE[2] = 'C';
$SCRIPT_LANGUAGE[3] = 'C++';
$SCRIPT_LANGUAGE[4] = 'Perl';
$SCRIPT_LANGUAGE[5] = 'PHP';
$SCRIPT_LANGUAGE[6] = 'Python';
$SCRIPT_LANGUAGE[7] = 'Unix Shell';
$SCRIPT_LANGUAGE[8] = 'Java';
$SCRIPT_LANGUAGE[9] = 'AppleScript';
$SCRIPT_LANGUAGE[10] = 'Visual Basic';
$SCRIPT_LANGUAGE[11] = 'TCL';
$SCRIPT_LANGUAGE[12] = 'Lisp';
$SCRIPT_LANGUAGE[13] = 'Mixed';
$SCRIPT_LANGUAGE[14] = 'JavaScript';
$SCRIPT_LANGUAGE[15] = 'SQL';
$SCRIPT_LANGUAGE[16] = 'C#';

$SCRIPT_EXTENSION = array();
$SCRIPT_EXTENSION[0] = '.txt';
$SCRIPT_EXTENSION[1] = '.txt';
$SCRIPT_EXTENSION[2] = '.c';
$SCRIPT_EXTENSION[3] = '.cpp';
$SCRIPT_EXTENSION[4] = '.pl';
$SCRIPT_EXTENSION[5] = '.php';
$SCRIPT_EXTENSION[6] = '.py';
$SCRIPT_EXTENSION[7] = '.sh';
$SCRIPT_EXTENSION[8] = '.java';
$SCRIPT_EXTENSION[9] = '.as';
$SCRIPT_EXTENSION[10] = '.vb';
$SCRIPT_EXTENSION[11] = '.tcl';
$SCRIPT_EXTENSION[12] = '.lisp';
$SCRIPT_EXTENSION[13] = '.txt';
$SCRIPT_EXTENSION[14] = '.js';
$SCRIPT_EXTENSION[15] = '.sql';
$SCRIPT_EXTENSION[16] = '.cs';

function snippet_header($params) {
	global $HTML;
	global $feedback;
	global $warning_msg;
	global $error_msg;

	if (!forge_get_config('use_snippet')) {
		exit_disabled();
	}

	$HTML->header($params);
	if (!empty($error_msg)) {
		html_feedback_top($error_msg);
	}
	if (!empty($warning_msg)) {
		html_feedback_top($warning_msg);
	}
	if (!empty($feedback)) {
		html_feedback_top($feedback);
	}

	/*
		Show horizontal links
	*/

	echo '<p><strong>';
	echo util_make_link ('/snippet/',_('Browse')).'
		 | '.util_make_link ('/snippet/submit.php',_('Submit A New Snippet')).'
		 | '.util_make_link ('/snippet/package.php',_('Create A Package')).'</strong>';
	echo '</p>';
}

function snippet_footer($params) {
	GLOBAL $HTML;
	$HTML->footer($params);
}

function snippet_show_package_snippets($version) {
	//show the latest version
	$result=db_query_params("SELECT snippet_package_item.snippet_version_id, snippet_version.version,snippet.name,users.user_name
FROM snippet,snippet_version,snippet_package_item,users
WHERE snippet.snippet_id=snippet_version.snippet_id
AND users.user_id=snippet_version.submitted_by
AND snippet_version.snippet_version_id=snippet_package_item.snippet_version_id
AND snippet_package_item.snippet_package_version_id=$1", array($version));

	$rows=db_numrows($result);
	echo '
	<p>&nbsp;</p>
	<h3>' ._('Snippets In This Package:').':</h3>
	<p>&nbsp;</p>';

	$title_arr=array();
	$title_arr[]= _('Snippet ID');
	$title_arr[]= _('Download Version');
	$title_arr[]= _('Title');
	$title_arr[]= _('Author');

	echo $GLOBALS['HTML']->listTableTop ($title_arr,$links_arr);

	if (!$result || $rows < 1) {
		echo db_error();
		echo '
			<tr><td colspan="4"><h3>' ._('No Snippets Are In This Package Yet').'</h3></td></tr>';
	} else {

		//get the newest version, so we can display it's code
		$newest_version=db_result($result,0,'snippet_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.db_result($result,$i,'snippet_version_id').
				'</td><td>'.
				util_make_link ('/snippet/download.php?type=snippet&amp;id='.db_result($result,$i,'snippet_version_id'),db_result($result,$i,'version')).
				'</td><td>'.
				db_result($result,$i,'name').'</td><td>'.
				db_result($result,$i,'user_name').'</td></tr>';
		}
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

function snippet_show_package_details($id) {
	global $SCRIPT_CATEGORY,$SCRIPT_LANGUAGE;

	$result=db_query_params("SELECT * FROM snippet_package WHERE snippet_package_id=$1", array($id));

	echo '
	<p>
	<table width="100%" border="0" cellspacing="1" cellpadding="2">

	<tr><td colspan="2">
	<h2>'. db_result($result,0,'name').'</h2>
	</td></tr>

	<tr>
		<td><strong>Category:</strong><br />
		'.$SCRIPT_CATEGORY[db_result($result,0,'category')].'
		</td>

		<td><strong>Language:</strong><br />
		'.$SCRIPT_LANGUAGE[db_result($result,0,'language')].'
		</td>
	</tr>

	<tr><td colspan="2">&nbsp;<br /><strong>Description:</strong><br />
	'. util_make_links(nl2br(db_result($result,0,'description'))).'
	</td></tr>

	</table></p>';

}

function snippet_show_snippet_details($id) {
	global $SCRIPT_TYPE,$SCRIPT_CATEGORY,$SCRIPT_LICENSE,$SCRIPT_LANGUAGE;

	$result=db_query_params("SELECT * FROM snippet WHERE snippet_id=$1", array($id));

	echo '
	<p>
	<table width="100%" border="0" cellspacing="1" cellpadding="2">

	<tr><td colspan="2">
	<h2>'. db_result($result,0,'name').'</h2>
	</td></tr>

	<tr><td><strong>Type:</strong><br />
		'.$SCRIPT_TYPE[db_result($result,0,'type')].'</td>
	<td><strong>Category:</strong><br />
		'.$SCRIPT_CATEGORY[db_result($result,0,'category')].'
	</td></tr>

	<tr><td><strong>License:</strong><br />
		'.$SCRIPT_LICENSE[db_result($result,0,'license')].'</td>
	<td><strong>Language:</strong><br />
		'.$SCRIPT_LANGUAGE[db_result($result,0,'language')].'
	</td></tr>

	<tr><td colspan="2">&nbsp;<br />
	<strong>Description:</strong><br />
	'. util_make_links(nl2br(db_result($result,0,'description'))).'
	</td></tr>

	</table></p>';
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

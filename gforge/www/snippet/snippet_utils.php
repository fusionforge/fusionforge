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


/*
	Code Snippet System
	By Tim Perdue, Sourceforge, Jan 2000
*/

$SCRIPT_CATEGORY[0]= $Language->getText('snippet_utils','choose_one');
$SCRIPT_CATEGORY[1]= $Language->getText('snippet_utils','unix_admin');
$SCRIPT_CATEGORY[2]= $Language->getText('snippet_utils','html_manipulation');
$SCRIPT_CATEGORY[3]= $Language->getText('snippet_utils','bbs_systems');
$SCRIPT_CATEGORY[4]= $Language->getText('snippet_utils','auctions');
$SCRIPT_CATEGORY[5]= $Language->getText('snippet_utils','calendars');
$SCRIPT_CATEGORY[6]= $Language->getText('snippet_utils','database_manipulation');
$SCRIPT_CATEGORY[7]= $Language->getText('snippet_utils','searching');
$SCRIPT_CATEGORY[8]= $Language->getText('snippet_utils','file_management');
$SCRIPT_CATEGORY[9]= $Language->getText('snippet_utils','games');
$SCRIPT_CATEGORY[10]= $Language->getText('snippet_utils','voting');
$SCRIPT_CATEGORY[11]= $Language->getText('snippet_utils','shopping_carts');
$SCRIPT_CATEGORY[12]= $Language->getText('snippet_utils','other');
$SCRIPT_CATEGORY[13]= $Language->getText('snippet_utils','math_functions');

$SCRIPT_TYPE[0]= $Language->getText('snippet_utils','choose_one');
$SCRIPT_TYPE[1]= $Language->getText('snippet_utils','function');
$SCRIPT_TYPE[2]= $Language->getText('snippet_utils','full_script');
$SCRIPT_TYPE[3]= $Language->getText('snippet_utils','sample_code');
$SCRIPT_TYPE[4]= $Language->getText('snippet_utils','readme');
$SCRIPT_TYPE[5]= $Language->getText('snippet_utils','class');

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
$SCRIPT_LICENSE[12] = $Language->getText('snippet_utils','website_only');
$SCRIPT_LICENSE[13] = $Language->getText('snippet_utils','other');

$SCRIPT_LANGUAGE = array();
$SCRIPT_LANGUAGE[0] = $Language->getText('snippet_utils','choose_one');
$SCRIPT_LANGUAGE[1] = $Language->getText('snippet_utils','other_language');
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

function snippet_header($params) {
	global $HTML, $Language, $sys_use_snippet;

	if (!$sys_use_snippet) {
		exit_disabled();
	}

	$HTML->header($params);
	/*
		Show horizontal links
	*/
	
	echo '<p><strong>';
	echo '<a href="/snippet/">' .$Language->getText('snippet_utils','browse').'</a>
		 | <a href="/snippet/submit.php">' .$Language->getText('snippet_utils','submit_a_new_snippet').'</a>
		 | <a href="/snippet/package.php">' .$Language->getText('snippet_utils','create_a_package').'</a></strong></p>';
	echo '<p>&nbsp;</p>';
}

function snippet_footer($params) {
	GLOBAL $HTML;
	global $feedback;
	html_feedback_bottom($feedback);
	$HTML->footer($params);
}

function snippet_show_package_snippets($version) {

	global $Language;
	//show the latest version
	$sql="SELECT snippet_package_item.snippet_version_id, snippet_version.version,snippet.name,users.user_name ".
		"FROM snippet,snippet_version,snippet_package_item,users ".
		"WHERE snippet.snippet_id=snippet_version.snippet_id ".
		"AND users.user_id=snippet_version.submitted_by ".
		"AND snippet_version.snippet_version_id=snippet_package_item.snippet_version_id ".
		"AND snippet_package_item.snippet_package_version_id='$version'";

	$result=db_query($sql);
	$rows=db_numrows($result);
	echo '
	<p>&nbsp;</p>
	<h3>' .$Language->getText('snippet_utils','snippet_in_this_package').':</h3>
	<p>&nbsp;</p>';

	$title_arr=array();
	$title_arr[]= $Language->getText('snippet_utils','snippet_id');
	$title_arr[]= $Language->getText('snippet_utils','download_version');
	$title_arr[]= $Language->getText('snippet_utils','snippet_title');
	$title_arr[]= $Language->getText('snippet_utils','author');

	echo $GLOBALS['HTML']->listTableTop ($title_arr,$links_arr);

	if (!$result || $rows < 1) {
		echo db_error();
		echo '
			<tr><td colspan="4"><h3>' .$Language->getText('snippet_utils','no_snippets_are_in_this_package').'</h3></td></tr>';
	} else {

		//get the newest version, so we can display it's code
		$newest_version=db_result($result,0,'snippet_version_id');

		for ($i=0; $i<$rows; $i++) {
			echo '
			<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>'.db_result($result,$i,'snippet_version_id').
				'</td><td><a href="/snippet/download.php?type=snippet&amp;id='.
				db_result($result,$i,'snippet_version_id').'">'.
				db_result($result,$i,'version').'</a></td><td>'.
				db_result($result,$i,'name').'</td><td>'.
				db_result($result,$i,'user_name').'</td></tr>';
		}
	}

	echo $GLOBALS['HTML']->listTableBottom();

}

function snippet_show_package_details($id) {
	global $SCRIPT_CATEGORY,$SCRIPT_LANGUAGE;

	$sql="SELECT * FROM snippet_package WHERE snippet_package_id='$id'";
	$result=db_query($sql);

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

	$sql="SELECT * FROM snippet WHERE snippet_id='$id'";
	$result=db_query($sql);

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

?>

<?php
/**
  *
  * SourceForge New Releases Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/vote_function.php';

$HTML->header(array("title"=>_('New File Releases')));

$offset = getIntFromRequest('offset');

if ( !$offset || $offset < 0 || !is_numeric($offset) ) {
	$offset = 0;
}

// For expediancy, list only the filereleases in the past three days.
$start_time = time() - (30 * 86400);

$query	= "SELECT groups.group_name,
	groups.group_id,
	groups.unix_group_name,
	groups.short_description,
	users.user_name,
	users.user_id,
	frs_release.release_id,
	frs_release.name AS release_version,
	frs_release.release_date,
	frs_release.released_by,
	frs_package.name AS module_name, 
	frs_dlstats_grouptotal_vw.downloads 
	FROM groups,users,frs_package,frs_release,frs_dlstats_grouptotal_vw 
	WHERE ( frs_release.release_date > '$start_time' 
	AND frs_release.package_id = frs_package.package_id 
	AND frs_package.group_id = groups.group_id 
	AND frs_release.released_by = users.user_id 
	AND frs_package.group_id = frs_dlstats_grouptotal_vw.group_id 
	AND frs_release.status_id=1 
	AND frs_package.is_public=1 ) 
	ORDER BY frs_release.release_date DESC";
$res_new = db_query($query, 21, $offset, SYS_DB_STATS);

if (!$res_new || db_numrows($res_new) < 1) {
	print db_error();
	print '<h1>'._('No new releases found.').'</h1>';
} else {

	if ( db_numrows($res_new) > 20 ) {
		$rows = 20;
	} else {
		$rows = db_numrows($res_new);
	}

	print '
		<table width="100%" cellpadding="0" cellspacing="0" border="0">';
	$seen = array();
	for ($i=0; $i<$rows; $i++) {
		$row_new = db_fetch_array($res_new);
		// avoid dupulicates of different file types
		if (!isset($seen[$row_new['group_id']])) {
			print '
			<tr valign="top">
				<td colspan="2">'.
				util_make_link_g ($row_new['unix_group_name'],$row_new['group_id'],'<strong>'.$row_new['group_name'].'</strong>').'
				</td>
				<td nowrap="nowrap"><em>'._('Released by:').
				util_make_link_u ($row_new['user_name'],$row_new['user_id'],'<em>'.$row_new['user_name'].'</em>').'
				</td>
			</tr>
			<tr>
				<td>'._('Module:').' '.$row_new['module_name'].'
				</td>
				<td>'._('Version:').' '.$row_new['release_version'].'
				</td>
				<td>'.date("M d, h:iA",$row_new['release_date']).'
				</td>
			</tr>
			<tr valign="top">
				<td colspan="2">&nbsp;<br />';
		if ($row_new['short_description']) {
			print '<em>'.$row_new['short_description'].'</em>';
		} else {
			print '<em>'._('This project has not submitted a description').'</em>';
		}
		print '
				</td>
				<td style="text-align:center" nowrap="nowrap">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3">';
				// link to whole file list for downloads
				print '&nbsp;<br />'.
				util_make_link ('/frs/?group_id='.$row_new['group_id'].'&amp;release_id='.$row_new['release_id'],_('Download')).
				' ('._('Project Total:') .$row_new['downloads'].') | ';
				// notes for this release
				print util_make_link ('/frs/shownotes.php?release_id='.$row_new['release_id'],_('Notes &amp; Changes')).'
				<hr />
				</td>
			</tr>';
		$seen[$row_new['group_id']] = 1;
		}
	}

	print '
			<tr class="content">
				<td>';
        if ($offset != 0) {
        	print '<a href="'.util_make_url ('/new/?offset='.($offset-20)).'">'.
		html_image("t2.png","15","15",array("border"=>"0","align"=>"middle")).
		' <strong>'._('Newer Releases').'</strong></a>';
        } else {
        	print '&nbsp;';
        }

	print '
				</td>
				<td colspan="2" style="text-align:right">';
	if (db_numrows($res_new)>$rows) {
		print '<a href="'.util_make_url ('/new/?offset='.($offset+20).'"><strong>'._('Older Releases').'</strong> ') .
		html_image("t.png","15","15",array("border"=>"0","align"=>"middle")) .
		'</a>';
	} else {
		print "&nbsp;";
	}
	print '
				</td>
			</tr>
		</table>
';

}

$HTML->footer(array());

?>

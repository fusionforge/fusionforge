<?php
/**
 * Project File Information/Download Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('pre.php');	
require_once('www/frs/include/frs_utils.php');

$cur_group =& group_get_object($group_id);

if (!$cur_group) {
	exit_error($Language->getText('project_showfiles','no_group_title'),
		$Language->getText('project_showfiles','no_group'));
}

//
//	Members of projects can see all packages
//	Non-members can only see public packages
//
if (session_loggedin()) {
	if (user_ismember($group_id) || user_ismember(1,'A')) {
		$pub_sql='';
	} else {
		$pub_sql=' AND is_public=1 ';
	}
} else {
	$pub_sql=' AND is_public=1 ';
}

$sql = "SELECT *
	FROM frs_package 
	WHERE group_id='$group_id' 
	AND status_id='1' 
	$pub_sql
	ORDER BY name";
$res_package = db_query( $sql );
$num_packages = db_numrows( $res_package );

if ( $num_packages < 1) {
	exit_error($Language->getText('project_showfiles','error_no_packages_defined_title'),$Language->getText('project_showfiles','error_no_packages_defined_text'));
}

frs_header(array('title'=>$Language->getText('project_showfiles','title'),'group'=>$group_id,'pagename'=>'project_showfiles','sectionvals'=>group_getname($group_id)));

echo '<p>'.$Language->getText('project_showfiles','intro').' ';
if ($release_id) {
	echo $Language->getText('project_showfiles','release_highlighted').' ';
}
echo $Language->getText('project_showfiles','intro2').'
</p>
';

// check the permissions and see if this user is a release manager.
// If so, offer the opportunity to create a release

$perm =& $cur_group->getPermission(session_get_user());

if ($perm->isReleaseTechnician()) {
	echo "<p><a href=\"admin/qrs.php?package=&group_id=$group_id\">";
	echo $Language->getText('project_showfiles','new_release');
	echo "</a></p>";
}

// get unix group name for path
$group_unix_name=group_getunixname($group_id);

echo '
<table width="100%" border="0" cellspacing="1" cellpadding="1">';
$cell_data=array();
$cell_data[] = array($Language->getText('project_showfiles','package'),'rowspan="2"');
$cell_data[] = array($Language->getText('project_showfiles','release_notes'),'rowspan="2"');
$cell_data[] = array($Language->getText('project_showfiles','filename'),'rowspan="2"');
$cell_data[] = array($Language->getText('project_showfiles','date'),'colspan="4"');

echo $GLOBALS[HTML]->multiTableRow('', $cell_data, TRUE);

$cell_data=array();
$cell_data[] = array($Language->getText('project_showfiles','size'));
$cell_data[] = array($Language->getText('project_showfiles','downloads'));
$cell_data[] = array($Language->getText('project_showfiles','architecture'));
$cell_data[] = array($Language->getText('project_showfiles','file_type'));

echo $GLOBALS[HTML]->multiTableRow('',$cell_data, TRUE);

$proj_stats['packages'] = $num_packages;

// Iterate and show the packages
for ( $p = 0; $p < $num_packages; $p++ ) {
	$cur_style = $GLOBALS['HTML']->boxGetAltRowStyle($p);
	
	print '<tr '.$cur_style.'><td colspan="3"><h3>'.db_result($res_package,$p,'name').'
	<a href="/frs/monitor.php?filemodule_id='. db_result($res_package,$p,'package_id') .'&group_id='.db_result($res_package,$p,'group_id').'&start=1">'.
	html_image('ic/mail16w.png','20','20',array('alt'=>$Language->getText('project_showfiles','monitor_package'))) .
	'</a></h3></td><td colspan="4">&nbsp;</td></tr>';

	// get the releases of the package
	$sql = "SELECT * FROM frs_release 
		WHERE package_id='". db_result($res_package,$p,'package_id') . "'
		AND status_id=1 ORDER BY release_date DESC, name ASC";
	$res_release = db_query( $sql );
	$num_releases = db_numrows( $res_release );

	$proj_stats['releases'] += $num_releases;

	if ( !$res_release || $num_releases < 1 ) {
		print '<tr '.$cur_style.'><td colspan="3">&nbsp;&nbsp;<em>'.$Language->getText('project_showfiles','no_releases').'</em></td><td colspan="4">&nbsp;</td></tr>'."\n";
	} else {
		// iterate and show the releases of the package
		for ( $r = 0; $r < $num_releases; $r++ ) {
		
			$cell_data=array();
			
			$package_release = db_fetch_array( $res_release );

		   	// Highlight the release if one was chosen
			if ( $release_id && $release_id == $package_release['release_id'] ) {
				$bgstyle = 'BGCOLOR="pink"';
			} else {
				$bgstyle = $cur_style;
			}
			$cell_data[] = array('&nbsp;<strong>
				<a href="shownotes.php?release_id='.$package_release['release_id'].'">'.$package_release['name'] .'</a></strong>',
				'colspan="3"');

			$cell_data[] = array('&nbsp;<strong>
				'.date($sys_datefmt, $package_release['release_date'] ) .'</strong>',
				'colspan="4" align="middle"');
			
			print $GLOBALS[HTML]->multiTableRow($bgstyle, $cell_data, FALSE);
			// get the files in this release....
			$sql = "SELECT frs_file.filename AS filename,
				frs_file.file_size AS file_size,
				frs_file.file_id AS file_id,
				frs_file.release_time AS release_time,
				frs_filetype.name AS type,
				frs_processor.name AS processor,
				frs_dlstats_filetotal_agg.downloads AS downloads 
				FROM frs_filetype,frs_processor,
				frs_file LEFT JOIN frs_dlstats_filetotal_agg ON frs_dlstats_filetotal_agg.file_id=frs_file.file_id 
				WHERE release_id='". $package_release['release_id'] ."' 
				AND frs_filetype.type_id=frs_file.type_id 
				AND frs_processor.processor_id=frs_file.processor_id 
				ORDER BY filename";
			$res_file = db_query($sql);
			$num_files = db_numrows( $res_file );

			$proj_stats['files'] += $num_files;

			if ( !$res_file || $num_files < 1 ) {
				print '<tr '.$bgstyle.'><td colspan="3"><dd><em>No Files</em></td><td colspan="4">&nbsp;</td></tr>'."\n";
			} else {
				// now iterate and show the files in this release....
				for ( $f = 0; $f < $num_files; $f++ ) {
					$file_release = db_fetch_array( $res_file );
					
					$cell_data=array();
					
					$cell_data[] = array('<dd>
						<a href="/frs/download.php/'.$file_release['file_id'].'/'.$file_release['filename'].'">'
						. $file_release['filename'] .'</a>',
						'colspan=3');
						
					$cell_data[] = array(human_readable_bytes($file_release['file_size']),'align="right"');
					$cell_data[] = array( ($file_release['downloads'] ? number_format($file_release['downloads'], 0) : '0'), 'align="right"');
					$cell_data[] = array($file_release['processor']);
					$cell_data[] = array($file_release['type']);

					print $GLOBALS[HTML]->multiTableRow($bgstyle, $cell_data, FALSE);

					$proj_stats['size'] += $file_release['file_size'];
					$proj_stats['downloads'] += $file_release['downloads'];
				}	
			}
		}
	}
}

if ( $proj_stats['size'] ) {
	print '<tr><td colspan="8">&nbsp;</tr>'."\n";
	print '<tr><td><strong>'.$Language->getText('project_showfiles','project_totals').'</strong></td>'
		. '<td align="right"><strong><em>' . $proj_stats['releases'] . '</em></strong></td>'
		. '<td align="right"><strong><em>' . $proj_stats['files'] . '</em></strong></td>'
		. '<td align="right"><strong><em>' . human_readable_bytes($proj_stats['size']) . '</em></strong></td>'
		. '<td align="right"><strong><em>' . $proj_stats['downloads'] . '</em></strong></td>'
		. '<td colspan="3">&nbsp;</td></tr>'."\n";
}

print "</table>\n\n";

frs_footer();

?>

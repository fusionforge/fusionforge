<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require ('pre.php');    

$sql = "SELECT * FROM frs_package WHERE group_id='$group_id' AND status_id='1'";
$res_package = db_query( $sql );
$num_packages = db_numrows( $res_package );

if ( $num_packages < 1) {
	exit_error("No File Packages","There are no file packages defined for this project.");
}

site_project_header(array('title'=>'Project Filelist','group'=>$group_id,'toptab'=>'downloads'));

echo '
<p>
Below is a list of all files of the project. ';
if ($release_id) {
	echo 'The release you have chosen is <span style="background-color:pink">highlighted</span>. ';
}
echo 'Before downloading, you may want to read Release Notes and ChangeLog
(accessible by clicking on release version).
</p>
';

$title_arr = array();
$title_arr[] = 'Package';
$title_arr[] = 'Release<BR>&amp; Notes';
$title_arr[] = 'Filename';
$title_arr[] = 'Size';
$title_arr[] = 'D/L';
$title_arr[] = 'Arch.';
$title_arr[] = 'Type';
$title_arr[] = 'Date';

   // get unix group name for path
$group_unix_name=group_getunixname($group_id);

   // print the header row
//echo html_build_list_table_top($title_arr) . "\n";
function col_heading($title)
{
  return '<FONT COLOR="'.
	$HTML->FONTCOLOR_HTMLBOX_TITLE.'"><B>'.$title.'</B></FONT>';
}

global $HTML;
echo '
<table width="100%" border="0" cellspacing="1" cellpadding="1">
<tr align="middle" BGCOLOR="'. $HTML->COLOR_HTMLBOX_TITLE .'">'.
'<td rowspan="2">'.col_heading('Package').'</td>'.
'<td rowspan="2">'.col_heading('Release<BR>&amp; Notes').'</td>'.
'<td rowspan="2">'.col_heading('Filename').'</td>'.
'<td colspan="4">'.col_heading('Date').'</td>'.
'</tr>
<tr align="middle" BGCOLOR="'. $HTML->COLOR_HTMLBOX_TITLE .'">'.
'<td>'.col_heading('Size').'</td>'.
'<td>'.col_heading('D/L').'</td>'.
'<td>'.col_heading('Arch.').'</td>'.
'<td>'.col_heading('Type').'</td>'.
'</tr>
';

$proj_stats['packages'] = $num_packages;

   // Iterate and show the packages
for ( $p = 0; $p < $num_packages; $p++ ) {
	$cur_color = html_get_alt_row_color($p);
	print '<TR bgcolor="'.$cur_color.'"><TD colspan="3"><h3>'.db_result($res_package,$p,'name').'</h3></TD><TD COLSPAN="4">&nbsp;</TD></TR>'."\n";

	   // get the releases of the package
	$sql	= "SELECT * FROM frs_release WHERE package_id='". db_result($res_package,$p,'package_id') . "' "
		. "AND status_id=1 ORDER BY release_date DESC";
	$res_release = db_query( $sql );
	$num_releases = db_numrows( $res_release );

	$proj_stats['releases'] += $num_releases;

	if ( !$res_release || $num_releases < 1 ) {
		print '<TR bgcolor="'.$cur_color.'"><TD colspan="3">&nbsp;&nbsp;<i>No Releases</i></TD><TD COLSPAN="4">&nbsp;</TD></TR>'."\n";
	} else {
		   // iterate and show the releases of the package
		for ( $r = 0; $r < $num_releases; $r++ ) {
			$package_release = db_fetch_array( $res_release );

		    	// Highlight the release if one was chosen
		      	if ( $release_id && $release_id == $package_release['release_id'] ) {
		      		$bgcolor = "pink";
		      	} else {
		      		$bgcolor = $cur_color;
		      	}
			print "\t" . '<TR BGCOLOR="'. $bgcolor .'"><TD colspan="3">&nbsp;&nbsp;&nbsp;&nbsp;<B>'
				. '<A HREF="shownotes.php?release_id='.$package_release['release_id'].'">'
				. $package_release['name'] .'</A></B></TD><TD COLSPAN="4" align="middle">'
				. '<b>'.date( 'Y-m-d H:i'/*$sys_datefmt*/, $package_release['release_date'] ) .'</b></TD></TR>'."\n";

			   // get the files in this release....
			$sql = "SELECT frs_file.filename AS filename,"
				. "frs_file.file_size AS file_size,"
				. "frs_file.file_id AS file_id,"
				. "frs_file.release_time AS release_time,"
				. "frs_filetype.name AS type,"
				. "frs_processor.name AS processor,"
				. "frs_dlstats_filetotal_agg.downloads AS downloads "
				. "FROM frs_filetype,frs_processor,"
				. "frs_file LEFT JOIN frs_dlstats_filetotal_agg ON frs_dlstats_filetotal_agg.file_id=frs_file.file_id "
				. "WHERE release_id='". $package_release['release_id'] ."' "
				. "AND frs_filetype.type_id=frs_file.type_id "
				. "AND frs_processor.processor_id=frs_file.processor_id "
                                . "ORDER BY filename";
			$res_file = db_query( $sql );
			$num_files = db_numrows( $res_file );

			$proj_stats['files'] += $num_files;

			if ( !$res_file || $num_files < 1 ) {
				print '<TR bgcolor="'.$bgcolor.'"><TD colspan="3"><dd><i>No Files</i></TD><TD COLSPAN="4">&nbsp;</TD></TR>'."\n";
			} else {
				   // now iterate and show the files in this release....
				for ( $f = 0; $f < $num_files; $f++ ) {
					$file_release = db_fetch_array( $res_file );
					print "\t\t" . '<TR bgcolor="' . $bgcolor .'">'
						. '<TD colspan=3><dd>'
						. '<A HREF="http://'.$sys_download_host.'/'.$group_unix_name.'/'.$file_release['filename'].'">'
						. $file_release['filename'] .'</A></TD>'
						. '<TD align="right">'. $file_release['file_size'] .' </TD>'
						. '<TD align="right">'. ($file_release['downloads'] ? $file_release['downloads'] : '0') .' </TD>'
						. '<TD>'. $file_release['processor'] .'</TD>'
						. '<TD>'. $file_release['type'] .'</TD>'
						//. '<TD>'. /*date( 'Y-m-d H:i', $file_release['release_time'] ) .*/'&nbsp;</TD>'
						. '</TR>' . "\n";

					$proj_stats['size'] += $file_release['file_size'];
					$proj_stats['downloads'] += $file_release['downloads'];
				}	
			}
		}
	}

}

if ( $proj_stats['size'] ) {
	print '<TR><TD COLSPAN="8">&nbsp;</TR>'."\n";
	print '<TR><TD><B>Project Totals: </B></TD>'
		. '<TD><B><I>' . $proj_stats['releases'] . '</I></B></TD>'
		. '<TD><B><I>' . $proj_stats['files'] . '</I></B></TD>'
		. '<TD><B><I>' . $proj_stats['size'] . '</I></B></TD>'
		. '<TD><B><I>' . $proj_stats['downloads'] . '</I></B></TD>'
		. '<TD COLSPAN="3">&nbsp;</TD></TR>'."\n";
}

print "</TABLE>\n\n";

site_project_footer(array());

?>

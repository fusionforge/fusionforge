<?php
/**
  *
  * Project File Information/Download Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');    

$sql = "SELECT * "
       ."FROM frs_package "
       ."WHERE group_id='$group_id' AND status_id='1' "
       ."ORDER BY name";
$res_package = db_query( $sql );
$num_packages = db_numrows( $res_package );

if ( $num_packages < 1) {
	exit_error("No File Packages","There are no file packages defined for this project.");
}

site_project_header(array('title'=>'Project Filelist','group'=>$group_id,'toptab'=>'downloads','pagename'=>'project_showfiles','sectionvals'=>group_getname($group_id)));

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

   // get unix group name for path
$group_unix_name=group_getunixname($group_id);

global $HTML;
echo '
<table width="100%" border="0" cellspacing="1" cellpadding="1">';
$cell_data=array();
$cell_data[] = array('Package','rowspan=2');
$cell_data[] = array('Release<BR>&amp; Notes','rowspan=2');
$cell_data[] = array('Filename','rowspan=2');
$cell_data[] = array('Date','colspan=4');

echo $GLOBALS[HTML]->multiTableRow('', $cell_data, TRUE);

$cell_data=array();
$cell_data[] = array('Size');
$cell_data[] = array('D/L');
$cell_data[] = array('Arch');
$cell_data[] = array('Type');

echo $GLOBALS[HTML]->multiTableRow("",$cell_data, TRUE);

$proj_stats['packages'] = $num_packages;

   // Iterate and show the packages
for ( $p = 0; $p < $num_packages; $p++ ) {
	$cur_style = $GLOBALS['HTML']->boxGetAltRowStyle($p);
	
	print '<TR '.$cur_style.'><TD colspan="3"><H3>'.db_result($res_package,$p,'name').'
	<A HREF="/project/filemodule_monitor.php?filemodule_id='. db_result($res_package,$p,'package_id') .'&group_id='.db_result($res_package,$p,'group_id').'&start=1">'.
		html_image('ic/mail16w.png','20','20',array('alt'=>'Monitor This Package')) .
		'</A></H3></TD><TD COLSPAN="4">&nbsp;</TD></TR>';

	   // get the releases of the package
	$sql	= "SELECT * FROM frs_release WHERE package_id='". db_result($res_package,$p,'package_id') . "' "
		. "AND status_id=1 ORDER BY release_date DESC, name ASC";
	$res_release = db_query( $sql );
	$num_releases = db_numrows( $res_release );

	$proj_stats['releases'] += $num_releases;

	if ( !$res_release || $num_releases < 1 ) {
		print '<TR '.$cur_style.'><TD colspan="3">&nbsp;&nbsp;<i>No Releases</i></TD><TD COLSPAN="4">&nbsp;</TD></TR>'."\n";
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
		    $cell_data[] = array('&nbsp;<B>
				<A HREF="shownotes.php?release_id='.$package_release['release_id'].'">'.$package_release['name'] .'</A></B>',
				"colspan=3");

		    $cell_data[] = array('&nbsp;<B>
				'.date( 'Y-m-d H:i'/*$sys_datefmt*/, $package_release['release_date'] ) .'</b>',
				'colspan=4 align="middle"');
		    
		    print $GLOBALS[HTML]->multiTableRow($bgstyle, $cell_data, FALSE);
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
			$res_file = db_query($sql, -1, 0, SYS_DB_STATS);
			$num_files = db_numrows( $res_file );

			$proj_stats['files'] += $num_files;

			if ( !$res_file || $num_files < 1 ) {
				print '<TR '.$bgstyle.'><TD colspan="3"><dd><i>No Files</i></TD><TD COLSPAN="4">&nbsp;</TD></TR>'."\n";
			} else {
				   // now iterate and show the files in this release....
				for ( $f = 0; $f < $num_files; $f++ ) {
					$file_release = db_fetch_array( $res_file );
					
					$cell_data=array();
					
					$cell_data[] = array('<dd>
						<A HREF="/download.php/'.$file_release['file_id'].'/'.$file_release['filename'].'">'
						. $file_release['filename'] .'</A>',
						'colspan=3');
						
					$cell_data[] = array($file_release['file_size'],'align="right"');
					$cell_data[] = array( ($file_release['downloads'] ? number_format($file_release['downloads'], 0) : '0'),
						'align="right"');
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
	print '<TR><TD COLSPAN="8">&nbsp;</TR>'."\n";
	print '<TR><TD><B>Project Totals: </B></TD>'
		. '<TD align="right"><B><I>' . $proj_stats['releases'] . '</I></B></TD>'
		. '<TD align="right"><B><I>' . $proj_stats['files'] . '</I></B></TD>'
		. '<TD align="right"><B><I>' . $proj_stats['size'] . '</I></B></TD>'
		. '<TD align="right"><B><I>' . $proj_stats['downloads'] . '</I></B></TD>'
		. '<TD COLSPAN="3">&nbsp;</TD></TR>'."\n";
}

print "</TABLE>\n\n";

site_project_footer(array());

?>

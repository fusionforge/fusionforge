<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: shownotes.php,v 1.5 2000/08/31 21:58:49 tperdue Exp $

require ('pre.php');

$result=db_query("SELECT frs_release.notes,frs_release.changes,frs_release.preformatted,frs_release.name,frs_package.group_id ".
		"FROM frs_release,frs_package ".
		"WHERE frs_release.package_id=frs_package.package_id AND frs_release.release_id='$release_id'");

if (!$result || db_numrows($result) < 1) {
	echo db_error();
	exit_error("Error - Not Found","That Release Was Not Found");
} else {

	$group_id=db_result($result,0,'group_id');

	site_project_header(array('title'=>"File Release Notes and Changelog",'group'=>$group_id,'toptab'=>'downloads'));

	$HTML->box1_top('Notes');

	echo '<h3>Release Name: <A HREF="showfiles.php?group_id='.db_result($result,0,'group_id').'">'.db_result($result,0,'name').'</A></H3>
		<P>';

/*
	Show preformatted or plain notes/changes
*/
	if (db_result($result,0,'preformatted')) {
		echo '<PRE><B>Notes:</B>
'.db_result($result,0,'notes').'

<HR NOSHADE>
<B>Changes:</B>
'.db_result($result,0,'changes').'</PRE>';

	} else {
		echo '<B>Notes:</B>
'.db_result($result,0,'notes').'

<HR NOSHADE>
<B>Changes:</B>
'.db_result($result,0,'changes');

	}

	$HTML->box1_bottom();

	site_project_footer(array());

}

?>

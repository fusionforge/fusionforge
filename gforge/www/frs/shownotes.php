<?php
/**
  *
  * Show Release Notes/ChangeLog Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('www/frs/include/frs_utils.php');

$result=db_query("SELECT frs_release.notes,frs_release.changes,
		frs_release.preformatted,frs_release.name,frs_package.group_id
		FROM frs_release,frs_package 
		WHERE frs_release.package_id=frs_package.package_id 
		AND frs_release.release_id='$release_id'");

if (!$result || db_numrows($result) < 1) {
	echo db_error();
	exit_error($Language->getText('general','error'), $Language->getText('project_shownotes','error_release_not_found'));
} else {

	$group_id=db_result($result,0,'group_id');

	frs_header(array('title'=>$Language->getText('project_shownotes','title'),'group'=>$group_id,'pagename'=>'project_shownotes','sectionvals'=>array(group_getname($group_id))));

	echo $HTML->boxTop($Language->getText('project_shownotes','notes'));

	echo '<h3>'.$Language->getText('project_shownotes','release_name').' <a href="/frs/?group_id='.db_result($result,0,'group_id').'">'.db_result($result,0,'name').'</a></h3>
		<p>';

/*
	Show preformatted or plain notes/changes
*/
	if (db_result($result,0,'preformatted')) {
		echo '<pre><strong>'.$Language->getText('project_shownotes','notes').'</strong>
'.db_result($result,0,'notes').'

<hr noshade="noshade" />
<strong>'.$Language->getText('project_shownotes','changes').'</strong>
'.db_result($result,0,'changes').'</pre>';

	} else {
		echo '<strong>'.$Language->getText('project_shownotes','notes').'</strong>
'.db_result($result,0,'notes').'

<hr noshade="noshade" />
<strong>'.$Language->getText('project_shownotes','changes').'</strong>
'.db_result($result,0,'changes') . '</p>';

	}

	echo $HTML->boxBottom();

	frs_footer();

}

?>

<?php
/**
 * Show Release Notes/ChangeLog Page
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

$release_id = getIntFromRequest('release_id');

$result=db_query("SELECT frs_release.notes,frs_release.changes,
		frs_release.preformatted,frs_release.name,frs_package.group_id,frs_package.is_public
		FROM frs_release,frs_package 
		WHERE frs_release.package_id=frs_package.package_id 
		$pub_sql
		AND frs_release.release_id='$release_id'");

if (!$result || db_numrows($result) < 1) {
	exit_error($Language->getText('general','error'), $Language->getText('project_shownotes','error_release_not_found'));
} else {

	$group_id=db_result($result,0,'group_id');
	$is_public =db_result($result,0,'is_public');

	//  Members of projects can see all packages
	//  Non-members can only see public packages
	if(!$is_public) {
		if (!session_loggedin() || (!user_ismember($group_id) && !user_ismember(1,'A'))) {
			exit_permission_denied();
		}
	}

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

<hr />
<strong>'.$Language->getText('project_shownotes','changes').'</strong>
'.db_result($result,0,'changes').'</pre>';

	} else {
		echo '<strong>'.$Language->getText('project_shownotes','notes').'</strong>
'.db_result($result,0,'notes').'

<hr />
<strong>'.$Language->getText('project_shownotes','changes').'</strong>
'.db_result($result,0,'changes') . '</p>';

	}

	echo $HTML->boxBottom();

	frs_footer();

}

?>

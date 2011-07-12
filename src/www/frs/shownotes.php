<?php
/**
 * Show Release Notes/ChangeLog Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
 * http://fusionforge.org/
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'frs/include/frs_utils.php';

$release_id = getIntFromRequest('release_id');

$result=db_query_params ('SELECT frs_release.notes,frs_release.changes,
		frs_release.preformatted,frs_release.name,frs_package.group_id,frs_package.is_public
		FROM frs_release,frs_package
		WHERE frs_release.package_id=frs_package.package_id
		AND frs_release.release_id=$1',
			array($release_id));

if (!$result || db_numrows($result) < 1) {
	exit_error(_('That Release Was Not Found'),'frs');
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

	frs_header(array('title'=>_('File Release Notes and Changelog'),'group'=>$group_id));

	echo '<h2>'._('Release Name:').' <a href="/frs/?group_id='.db_result($result,0,'group_id').'&amp;release_id='.$release_id.'">'.db_result($result,0,'name').'</a></h2>';

	/*
	 Show preformatted or plain notes/changes
	 */
	if (db_result($result,0,'preformatted')) {
		$opening = '<pre>';
		$closing = '</pre>';
	} else {
		$opening = '<p>';
		$closing = '</p>';
	}

	if (db_result($result,0,'notes')) {
		echo $HTML->boxTop(_('Release Notes'));
		echo "$opening".db_result($result,0,'notes')."$closing";
		echo $HTML->boxBottom();
	}

	if (db_result($result,0,'changes')) {
		echo $HTML->boxTop(_('Change Log'));
		echo "$opening".db_result($result,0,'changes')."$closing";
		echo $HTML->boxBottom();
	}

	frs_footer();
}

?>

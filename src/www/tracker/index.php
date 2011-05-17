<?php
/**
 * Tracker Front Page
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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
require_once $gfwww.'include/note.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';

if (!forge_get_config('use_tracker')) {
	exit_disabled('home');
}

$aid = getIntFromRequest('aid');
$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');

$feedback = htmlspecialchars(getStringFromRequest('feedback'));
$warning_msg = htmlspecialchars(getStringFromRequest('warning_msg'));
$error_msg = htmlspecialchars(getStringFromRequest('error_msg'));

//if the ATID and GID are not provided, but
//the artifact_id is, then fetch the other vars
if ($aid && (!$group_id && !$atid)) {
	$a =& artifact_get_object($aid);
	if (!$a || !is_object($a) || $a->isError()) {
		exit_error(_('Could Not Get Artifact Object'),'tracker');
	} else {
		$group_id=$a->ArtifactType->Group->getID();
		$atid=$a->ArtifactType->getID();
		$func='detail';
	}
}

$group = group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_no_group();
}
if ($group->isError()) {
	if($group->isPermissionDeniedError()) {
		exit_permission_denied($group->getErrorMessage(),'tracker');
	} else {
		exit_error($group->getErrorMessage(),'tracker');
	}
}

if ($group_id && $atid) {
	include $gfwww.'tracker/tracker.php';

} elseif ($group_id) {
	include $gfwww.'tracker/ind.php';

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

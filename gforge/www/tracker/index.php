<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/note.php';
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactFile.class.php';
require_once $gfwww.'tracker/include/ArtifactFileHtml.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfwww.'tracker/include/ArtifactTypeHtml.class.php';
require_once $gfwww.'tracker/include/ArtifactHtml.class.php';
require_once $gfcommon.'tracker/ArtifactCanned.class.php';
require_once $gfcommon.'tracker/ArtifactTypeFactory.class.php';

if (!$sys_use_tracker) {
	exit_disabled();
}

$aid = getIntFromRequest('aid');
$group_id = getIntFromRequest('group_id');
$atid = getIntFromRequest('atid');

//if the ATID and GID are not provided, but
//the artifact_id is, then fetch the other vars
if ($aid && (!$group_id || !$atid)) {
	$a =& artifact_get_object($aid);
	if (!$a || !is_object($a) || $a->isError()) {
		exit_error('Error','Could Not Get Artifact Object');
	} else {
		$group_id=$a->ArtifactType->Group->getID();
		$atid=$a->ArtifactType->getID();
		$func='detail';
	}
}

if ($group_id && $atid) {
	include $gfwww.'tracker/tracker.php';

} elseif ($group_id) {

	include $gfwww.'tracker/ind.php';

} else {

	exit_no_group();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

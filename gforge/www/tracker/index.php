<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('note.php');
require_once('common/tracker/Artifact.class');
require_once('common/tracker/ArtifactFile.class');
require_once('www/tracker/include/ArtifactFileHtml.class');
require_once('common/tracker/ArtifactType.class');
require_once('www/tracker/include/ArtifactTypeHtml.class');
require_once('www/tracker/include/ArtifactHtml.class');
require_once('common/tracker/ArtifactGroup.class');
require_once('common/tracker/ArtifactCategory.class');
require_once('common/tracker/ArtifactCanned.class');
require_once('common/tracker/ArtifactResolution.class');
require_once('common/tracker/ArtifactTypeFactory.class');

if (!$sys_use_tracker) {
	exit_disabled();
}


if ($group_id && $atid) {

	include('tracker.php');

} elseif ($group_id) {

	include('ind.php');

} else {

	exit_no_group();

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

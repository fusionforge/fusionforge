<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 */


/*
    Message Forums
    By Tim Perdue, Sourceforge, 11/99

    Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

    Complete OO rewrite by Tim Perdue 12/2002
*/


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'forum/include/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';

if (session_loggedin()) {
	/*
		User obviously has to be logged in to save place
	*/

	$forum_id = getIntFromRequest('forum_id');
	$group_id = getIntFromRequest('group_id');
	if ($forum_id && $group_id) {
		//
		//  Set up local objects
		//
		$g =& group_get_object($group_id);
		if (!$g || !is_object($g) || $g->isError()) {
			exit_no_group();
		}

		$f=new Forum($g,$forum_id);
		if (!$f || !is_object($f)) {
			exit_error(_('Error'),'Error Getting Forum');
		} elseif ($f->isError()) {
			exit_error(_('Error'),$f->getErrorMessage());
		}

		if (!$f->savePlace()) {
			exit_error(_('Error'),$f->getErrorMessage());
		} else {
			header ("Location: ".util_make_url ("/forum/forum.php?forum_id=$forum_id&group_id=$group_id&feedback=".urlencode(_('Forum Position Saved. New messages will be highlighted when you return'))));
		}
	} else {
		exit_missing_param();
	}

} else {
	exit_not_logged_in();
}

?>

<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */


/*
    Message Forums
    By Tim Perdue, Sourceforge, 11/99

    Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

    Complete OO rewrite by Tim Perdue 12/2002
*/


require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');

if (session_loggedin()) {
	/*
		User obviously has to be logged in to save place 
	*/

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
			exit_error('Error','Error Getting Forum');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		}

		if (!$f->savePlace()) {
			exit_error('Error',$f->getErrorMessage());
		} else {
			header ("Location: /forum/forum.php?forum_id=$forum_id&feedback=".urlencode("Forum Position Saved. New messages will be highlighted when you return"));
		}
	} else {
		exit_missing_params();
	}

} else {
	exit_not_logged_in();
}

?>

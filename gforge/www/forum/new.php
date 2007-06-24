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


require_once('../env.inc.php');
require_once('pre.php');
require_once('www/forum/include/ForumHTML.class.php');
require_once('common/forum/Forum.class.php');
require_once('www/forum/include/AttachManager.class.php');

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
		exit_error('Error','Error Getting Forum');
	} elseif ($f->isError()) {
		exit_error('Error',$f->getErrorMessage());
	}

	$fh=new ForumHTML($f);
	if (!$fh || !is_object($fh)) {
		exit_error('Error','Error Getting ForumHTML');
	} elseif ($fh->isError()) {
		exit_error('Error',$fh->getErrorMessage());
	}

	if (session_loggedin() || $f->allowAnonymous()) {
		if (!$f->allowAnonymous() && !$f->savePlace()) {
			exit_error('Error',$f->getErrorMessage());
		} else {
			forum_header(array('title'=>$f->getName(),'forum_id'=>$forum_id));
			echo '<div align="center"><h3>'._('Start New Thread').'</h3></div>';
			$fh->showPostForm();
			forum_footer(array());
		}
	} else {
		exit_not_logged_in();
	}
} else {
	exit_missing_param();
}


?>

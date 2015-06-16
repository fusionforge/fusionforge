<?php
/**
 * FusionForge Forum Attachments download Page
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * Coyright 2005, Daniel Perez
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/ForumStorage.class.php';
require_once $gfcommon.'forum/ForumPendingStorage.class.php';

/**
 *  goodbye - Just prints a message and a close button.
 *
 *  @param  string	 $msg	The message.
 */

function goodbye($msg) {
	forum_header(array('title'=>_('Attachments')));
	html_feedback_top($msg);
	echo '<form method="post"><input type="button" value="'._('Close Window').'" onclick="window.close()" /></form>';
	site_footer();
	exit();
}

$attachid = getIntFromRequest("attachid");
$delete = getStringFromRequest("delete");
$edit = getStringFromRequest("edit");
$doedit = getStringFromRequest("doedit");
$pending = getStringFromRequest("pending");
$msg_id = getIntFromRequest("msg_id");
$group_id = getIntFromRequest("group_id");
$forum_id = getIntFromRequest("forum_id");

if ( !($forum_id) || !($group_id) ) {
	exit_missing_param();
}

$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$f=new Forum($g,$forum_id);
if (!$f || !is_object($f)) {
	exit_error(_('Error getting Forum'), 'forums');
} elseif ($f->isError()) {
	exit_error($f->getErrorMessage(), 'forums');
}

if ($delete == "yes") {
	session_require_perm('forum', $f->getID(), 'post') ;

	//only the user that created the attach  or forum admin can delete it (safecheck)
	if (!$pending) { //pending messages aren't deleted from this page
		$res = db_query_params('SELECT userid FROM forum_attachment WHERE attachmentid=$1',
					array($attachid));
	} else {
		$res = false;
	}
	if ( (!$res) ) {
		exit_error("Attachment Download error: ".db_error(),'forums');
	}
	if (! ((db_result($res,0,'userid') == user_getid()) || (forge_check_perm ('forum_admin', $f->Group->getID()))) ) {
		goodbye(_('You cannot delete this attachment'));
	} else {
		if (!$pending) {
			if (db_query_params('DELETE FROM forum_attachment WHERE attachmentid=$1',
						array($attachid))) {
				ForumStorage::instance()->delete($attachid)->commit();
				goodbye(_('Attachment deleted'));
			} else {
				exit_error(db_error(), 'forums');
			}
		}
	}
}

if ($edit == "yes") {
	session_require_perm('forum', $f->getID(), 'post') ;

	//only the user that created the attach  or forum admin can edit it (safecheck)
	if (!$pending) { //pending messages aren't deleted from this page
		$res = db_query_params ('SELECT filename FROM forum_attachment WHERE attachmentid=$1',
					array($attachid));
		$res2 = db_query_params('SELECT posted_by FROM forum WHERE msg_id=$1',
					array($msg_id));
	} else {
		$res = false;
		$res2 = false;
	}
	if ( (!$res) || (!$res2) ) {
		exit_error("Attachment error:".db_error(), 'forums');
	}
	if (! ((db_result($res2, 0, 'posted_by') == user_getid()) || (forge_check_perm('forum_admin', $f->Group->getID())))) {
		goodbye(_('You cannot edit this attachment'));
	} else {
		if ($doedit=="1") {
			//actually edit the attach and save the info
			$am = new AttachManager();
			$fm = new ForumMessage($f, $msg_id, array(), false);
			$am->SetForumMsg($fm);
			$attach = getUploadedFile("attachment1");
			if ($attachid) {
				//update existing one
				$attachok = $am->attach($attach,$group_id,$attachid,$msg_id);
				if ($attachok!=false) {
					$fm->fetchData($msg_id);
					$fm->sendAttachNotice($attachok);
				}
			} else {
				//add new one
				$attachok = $am->attach($attach,$group_id,false,$msg_id);
				if ($attachok!=false) {
					$fm->fetchData($msg_id);
					$fm->sendAttachNotice($attachok);
				}
			}
			foreach ($am->Getmessages() as $item) {
				$feedback .= "<br />" . $item;
			}
			goodbye(_('Attachment saved'));
			forum_footer();
			exit();
		} else {
			//show the form to edit the attach
			forum_header(array('title' => _('Attachments')));
			$fh = new ForumHTML($f);
			if (!$fh || !is_object($fh)) {
				exit_error(_('Error getting new ForumHTML'), 'forums');
			} elseif ($fh->isError()) {
				exit_error($fh->getErrorMessage(), 'forums');
			}
			if (!db_result($res, 0, 'filename')) {
				$filename = _('No attachment found');
			} else {
				$filename = db_result($res, 0, 'filename');
			}
			echo $fh->LinkAttachEditForm($filename, $group_id, $forum_id, $attachid, $msg_id);
			forum_footer();
			exit();
		}

	}
}

session_require_perm('forum', $f->getID(), 'read');

if (!$attachid) {
	exit_missing_param();
}

if ($pending == "yes") {
	$res = db_query_params('SELECT * FROM forum_pending_attachment WHERE attachmentid=$1',
			array($attachid));
} else {
	$res = db_query_params('SELECT * FROM forum_attachment WHERE attachmentid=$1',
			array($attachid));
}

if (!$res || !db_numrows($res) ) {
	exit_error("Attachment Download error: ".db_error(),'forums');
}

$sysdebug_enable = false;

$last = gmdate('D, d M Y H:i:s', db_result($res,0,'dateline'));
header('Last-Modified: ' . $last . ' GMT');
header('ETag: "' . db_result($res,0,'attachmentid').db_result($res,0,'filehash') . '"');
$filename = db_result($res,0,'filename');
$mimetype = db_result($res,0,'mimetype');
$filesize = db_result($res,0,'filesize');
utils_headers_download($filename, $mimetype, $filesize);

if ($pending) {
	readfile_chunked(ForumPendingStorage::instance()->get($attachid));
} else {
	readfile_chunked(ForumStorage::instance()->get($attachid));
}
flush();

//increase the attach count
if (!$pending) { //we don't care for the pending attach counter, it's just for administrative purposes
	db_query_params('UPDATE forum_attachment SET counter=counter+1 WHERE attachmentid=$1',
			array($attachid));
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

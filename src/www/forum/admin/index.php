<?php
/**
 * Forum Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'forum/ForumHTML.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumAdmin.class.php';
require_once $gfcommon.'forum/ForumFactory.class.php';
require_once $gfcommon.'forum/ForumMessageFactory.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';
require_once $gfcommon.'include/TextSanitizer.class.php'; // to make the HTML input by the user safe to store

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$deleteforum = getStringFromRequest('deleteforum');
$feedback = htmlspecialchars(getStringFromRequest('feedback'));

global $HTML;

if (!$group_id) {
	exit_no_group();
}

//
//  Set up local objects
//
$g = group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

session_require_perm ('forum_admin', $group_id) ;

if (getStringFromRequest('post_changes')) {
	/*
	  Update the DB to reflect the changes
	*/

	if ($deleteforum) {
		/*
		  Deleting entire forum
		*/
		$fa = new ForumAdmin($group_id);
		$feedback .= $fa->ExecuteAction("delete_forum");
		$group_forum_id=0;
		$deleteforum=0;
	} else if (getStringFromRequest('add_forum')) {
		if (!form_key_is_valid(getStringFromRequest('form_key'))) {
			exit_form_double_submit('forums');
		}

		if (check_email_available($g, $g->getUnixName() . '-' . getStringFromRequest('forum_name'), $error_msg)) {
			$fa = new ForumAdmin($group_id);
			$feedback .= $fa->ExecuteAction("add_forum");
			$g->normalizeAllRoles () ;
		}
	} else if (getStringFromRequest('change_status')) {
		$fa = new ForumAdmin($group_id);
		$feedback .= $fa->ExecuteAction("change_status");
	}
}

if (getStringFromRequest('add_forum')) {
	/*
	  Show the form for adding forums
	*/
	forum_header(array('title'=>_('Add forum')));

	echo '
			<form method="post" action="'.getStringFromServer('PHP_SELF').'">
			<p>
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_forum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="form_key" value="' . form_generate_key() . '" />
			<strong>'._('Forum Name').':</strong><br />
			<input type="text" name="forum_name" value="" size="20" maxlength="30" />
			</p>
			<p>
			<strong>'._('Description').':</strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" />
			</p>
			<p>
			<strong>'._('Is Public?').'</strong><br />
			<input type="radio" name="is_public" value="1" checked="checked" />'._('Yes').' <br />
			<input type="radio" name="is_public" value="0" />'._('No').'
			</p>
			<p>
			<strong>'._('Allow Anonymous Posts?').'</strong><br />
			<input type="radio" name="allow_anonymous" value="1" />'._('Yes').'<br />
			<input type="radio" name="allow_anonymous" value="0" checked="checked" />'._('No').'
			</p>
			<p>'.

		html_build_select_box_from_assoc(array("0" => _('No Moderation') ,"1" => _('Moderated Level 1'),"2" => _('Moderated Level 2') ),"moderation_level",0) . '
				<br />' . _('Moderated Level 1') . ': ' . _('To moderate anonymous posts (if allowed in public forum) and posts from non-member users.') . '<br />' . _('Moderated Level 2') . ': ' . _('To moderate ALL posts.') . '<p>
				
			<strong>'._('Email All Posts To:').'</strong><br />
			<input type="text" name="send_all_posts_to" value="" size="60" />
			</p>
			<p>
			<input type="submit" name="submit" value="'._('Add This Forum').'" />
			</p>
			</form>';

	forum_footer(array());

} else if (getStringFromRequest('change_status')) {
	/*
	  Change a forum
	*/

	$f = new Forum ($g,$group_forum_id);

	forum_header(array('title'=>_('Change forum status')));
	echo '<p>'._('You can adjust forum features from here. Please note that private forums can still be viewed by members of your project, not the general public.').'</p>';
	$fa = new ForumAdmin($f->Group->getID());
	$fa->PrintAdminPendingOption($group_forum_id);
		
	echo '
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
				<p>
				<input type="hidden" name="post_changes" value="y" />
				<input type="hidden" name="change_status" value="y" />
				<input type="hidden" name="group_forum_id" value="'. $f->getID() .'" />
				<input type="hidden" name="group_id" value="'.$group_id.'" />
				
				<strong>'._('Allow Anonymous Posts?').'</strong><br />
				<input type="radio" name="allow_anonymous" value="1"'.(($f->AllowAnonymous() == 1)?' checked="checked"':'').' /> '._('Yes').'<br />
				<input type="radio" name="allow_anonymous" value="0"'.(($f->AllowAnonymous() == 0)?' checked="checked"':'').'/> '._('No').'
				</p>
				<p>
				<strong>'._('Is Public?').'</strong><br />
				<input type="radio" name="is_public" value="1"'.(($f->isPublic() == 1)?' checked="checked"':'').' /> '._('Yes').'<br />
				<input type="radio" name="is_public" value="0"'.(($f->isPublic() == 0)?' checked="checked"':'').' /> '._('No').'<br />
				<input type="radio" name="is_public" value="9"'.(($f->isPublic() == 9)?' checked="checked"':'').' />'._('Suspended').'<br />
				<p>' .
		html_build_select_box_from_assoc(array("0" => _('No Moderation') ,"1" => _('Moderated Level 1'),"2" => _('Moderated Level 2') ),"moderation_level",$f->getModerationLevel()) . '
				<br />' . _('Moderated Level 1') . ': ' . _('To moderate anonymous posts (if allowed in public forum) and posts from non-member users.') . '<br />' . _('Moderated Level 2') . ': ' . _('To moderate ALL posts.') . '<p>
				

				<strong>'._('Forum Name').':</strong><br />
				<input type="text" name="forum_name" value="'. $f->getName() .'" size="20" maxlength="30" />
				</p>
				<p>
				<strong>'._('Email All Posts To:').'</strong><br />
				<input type="text" name="send_all_posts_to" value="'. $f->getSendAllPostsTo() .'" size="60" />
				</p>
				<p>
				<strong>'._('Description').':</strong><br />
				<input type="text" name="description" value="'. $f->getDescription() .'" size="60" maxlength="80" /><br />
				</p>
				<p>
				<input type="submit" name="submit" value="'._('Update').'" />
				</p>
			</form><p>';
	//echo '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_forum_id='.$group_forum_id.'&amp;delete=1">'._('Delete Message').'</a><br />';
	echo '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_forum_id='.$group_forum_id.'&amp;deleteforum=1">'._('Delete entire forum and all content').'</a></p>';
	forum_footer(array());

} elseif ($deleteforum && $group_forum_id) {

	$f = new Forum ($g,$group_forum_id);

	forum_header(array('title'=>_('Delete')));
	echo '<p>
			<strong>'._('You are about to permanently and irretrievably delete this entire forum and all its contents!').'</strong><br />
			</p>
			<form method="post" action="'.getStringFromServer('PHP_SELF').'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="deleteforum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="group_forum_id" value="'.$group_forum_id.'" />
			<input type="checkbox" name="sure" value="1" />'._('I\'m Sure').'<br />
			<input type="checkbox" name="really_sure" value="1" />'._('I\'m Really Sure').'<br />
			<input type="submit" name="submit" value="'._('Delete').'" />
			</form>';
	forum_footer(array());

} elseif ( getStringFromRequest("deletemsg") ) {
	// delete message handling
		
	$forum_id = getIntFromRequest("forum_id");
	$thread_id = getIntFromRequest("thread_id");
	$msg_id = getIntFromRequest("deletemsg");

	$f = forum_get_object ($forum_id) ;

	$fa = new ForumAdmin($f->Group->getID());

	if (getStringFromRequest("ok")) {
		//actually delete the message
		$feedback .= $fa->ExecuteAction("delete");
		forum_header(array('title'=>_('Delete a Message')));
		echo '<p>'.util_make_link ('/forum/forum.php?forum_id=' . $forum_id, _("Return to the forum")) . '</p>';
		forum_footer(array());
	} elseif (getStringFromRequest("cancel")) {
		// the user cancelled the request, go back to forum
		//if thread_id is 0, then we came from message.php. else, we came from forum.php
		if (!$thread_id) {
            session_redirect('/forum/message.php?msg_id='.$msg_id);
		} else {
            session_redirect('/forum/forum.php?thread_id='.$thread_id.'&forum_id='.$forum_id);
		}
		exit;
	} else {
		//print the delete message confirmation
		forum_header(array('title'=>_('Delete a Message')));
		echo '<center>
							<form action="'.getStringFromServer('PHP_SELF').'" method="post">
							<h3>' . _('WARNING! You are about to permanently delete a message and all of its followups!!') . '</h3>
							<p>
							<input type="submit" name="ok" value="' . _('Yes') . '" />
							<input type="submit" name="cancel" value="' . _('No') . '" />    
							<input type="hidden" name="deletemsg" value="'.$msg_id.'" />
							<input type="hidden" name="group_id" value="'.$group_id.'" />
							<input type="hidden" name="forum_id" value="'.$forum_id.'" />
							<input type="hidden" name="thread_id" value="'.$thread_id.'" />
							</p>
							</form>
							</center>';
		forum_footer(array());
	}
} elseif (getStringFromRequest("editmsg")) {
	// edit message handling
	$forum_id = getIntFromRequest("forum_id");
	$thread_id = getIntFromRequest("thread_id");
	$msg_id = getIntFromRequest("editmsg");

	$f = forum_get_object ($forum_id) ;
	$fa = new ForumAdmin($f->Group->getID());

	if (getStringFromRequest("ok")) {
		//actually finish editing the message and save the contents
		$f = new Forum ($fa->GetGroupObject(),$forum_id);
		if (!$f || !is_object($f)) {
			exit_error(_('Error Getting Forum'),'forums');
		} elseif ($f->isError()) {
			exit_error($f->getErrorMessage(),'forums');
		}
		$fm=new ForumMessage($f,$msg_id,false,false);
		if (!$fm || !is_object($fm)) {
			exit_error(_('Error getting new forum message'),'forums');
		} elseif ($fm->isError()) {
			exit_error($fm->getErrorMessage(),'forums');
		}
		$subject = getStringFromRequest('subject');
		$body = getStringFromRequest('body');
			
		$sanitizer = new TextSanitizer();
		$body = $sanitizer->SanitizeHtml($body);
			
		$is_followup_to = getStringFromRequest('is_followup_to');
		$form_key = getStringFromRequest('form_key');
		$posted_by = getStringFromRequest('posted_by');
		$post_date = getStringFromRequest('post_date');
		$is_followup_to = getStringFromRequest('is_followup_to');
		$has_followups = getStringFromRequest('has_followups');
		$most_recent_date = getStringFromRequest('most_recent_date');
		if ($fm->updatemsg($forum_id,$posted_by,$subject,$body,$post_date,$is_followup_to,$thread_id,$has_followups,$most_recent_date)) {
			$feedback .= _('Message Edited Successfully');
		} else {
			$error_msg .= $fm->getErrorMessage();
		}
		forum_header(array('title'=>_('Edit a Message')));
		echo '<p>'.util_make_link ('/forum/forum.php?forum_id=' . $forum_id, _("Return to the forum")) ;
		forum_footer(array());
	} elseif (getStringFromRequest("cancel")) {
		// the user cancelled the request, go back to forum
		session_redirect('/forum/message.php?msg_id='.$msg_id);
	} else { 
		//print the edit message confirmation
			
		$f = new Forum ($fa->GetGroupObject(),$forum_id);
		if (!$f || !is_object($f)) {
			exit_error(_('Error Getting Forum'),'forums');
		} elseif ($f->isError()) {
			exit_error($f->getErrorMessage(),'forums');
		}
			
		$fm=new ForumMessage($f,$msg_id,false,false);
		if (!$fm || !is_object($fm)) {
			exit_error(_('Error Getting ForumMessage'),'forums');
		} elseif ($fm->isError()) {
			exit_error($fm->getErrorMessage(),'forums');
		}
			
		$fh = new ForumHTML($f);
		if (!$fh || !is_object($fh)) {
			exit_error(_('Error Getting ForumHTML'),'forums');
		} elseif ($fh->isError()) {
			exit_error($fh->getErrorMessage(),'forums');
		}
			
		forum_header(array('title'=>_('Edit a Message')));
		$fh->showEditForm($fm);
		forum_footer(array());
	}
} elseif (getStringFromRequest("movethread")) {
	$thread_id = getIntFromRequest("movethread");
	$msg_id = getStringFromRequest("msg_id");
	$forum_id = getIntFromRequest("forum_id");
	$return_to_message = getIntFromRequest("return_to_message");
	$new_forum_id = getIntFromRequest("new_forum_id");
	$f = forum_get_object ($forum_id) ;
	$fa = new ForumAdmin($f->Group->getID());

	if (getStringFromRequest("ok")) {
		if ($forum_id == $new_forum_id) {
			$feedback .= _('Thread not moved');
		}
		else {
			// Move message in another forum
			$f_from = new Forum ($fa->GetGroupObject(),$forum_id);
			if (!$f_from || !is_object($f_from)) {
				exit_error(_('Could Not Get Forum Object'),'forums');
			} elseif ($f_from->isError()) {
				exit_error($f_from->getErrorMessage(),'forums');
			}
			$f_to = new Forum ($fa->GetGroupObject(),$new_forum_id);
			if (!$f_to || !is_object($f_to)) {
				exit_error(_('Could Not Get Forum Object'),'forums');
			} elseif ($f_to->isError()) {
				exit_error($f_to->getErrorMessage(),'forums');
			}

			$ff = new ForumFactory($g);
			if (!$ff || !is_object($ff) || $ff->isError()) {
				exit_error($ff->getErrorMessage(),'forums');
			}

			if ($ff->moveThread($new_forum_id,$thread_id,$forum_id)) {
				$feedback .= sprintf(_('Thread successfully moved from %1$s forum to %2$s forum'), $f_from->getName(),$f_to->getName());
			} else {
				$error_msg .= $ff->getErrorMessage();
			}
		}

		forum_header(array('title'=>_('Edit a Message')));
		echo '<p><a href="/forum/forum.php?forum_id=' . $new_forum_id . '">Return to the forum</a></p>';
		echo '<p><a href="/forum/forum.php?thread_id='.$thread_id.'&amp;forum_id=' . $new_forum_id . '">Return to the thread</a></p>';
		forum_footer(array());
	} elseif (getStringFromRequest("cancel")) {
		// the user cancelled the request, go back to forum
		if ($return_to_message) {
			session_redirect('/forum/message.php?msg_id='.$msg_id);
		} else {
			session_redirect('/forum/forum.php?thread_id='.$thread_id.'&forum_id='.$forum_id);
		}
		exit;
	} else { 
		// Display select box to select new forum

					forum_header(array('title'=>_('Move Thread')));
					
		$ff = new ForumFactory($g);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			exit_error($ff->getErrorMessage(),'forums');
		}

		$farr = $ff->getForums();

		if ($ff->isError()) {
			echo '<p class="error">'.sprintf(_('No Forums Found For %s'), $g->getPublicName())
                 . $ff->getErrorMessage().'</p>';
			forum_footer(array());
			exit;
		}

		/*
		  List the existing forums so they can be edited.
		*/

		$forums = array();
		for ($j = 0; $j < count($farr); $j++) {
			if (!is_object($farr[$j])) {
				//just skip it - this object should never have been placed here
			} elseif ($farr[$j]->isError()) {
				echo $farr[$j]->getErrorMessage();
			} else {
				$forums[$farr[$j]->getID()] = $farr[$j]->getName();
			}
		}
					
		$f_from = new Forum ($fa->GetGroupObject(),$forum_id);
		if (!$f_from || !is_object($f_from)) {
			exit_error(_('Could Not Get Forum Object'),'forums');
		} elseif ($f_from->isError()) {
			exit_error($f_from->getErrorMessage(),'forums');
		}
					
		echo '<center>
							<form action="'.getStringFromServer('PHP_SELF').'" method="post">
							<p><strong>' . sprintf(_('Move thread from %s forum to the following forum:'), $f_from->getName()) . '</strong></p>
							<p>
							<input type="hidden" name="movethread" value="'.$thread_id.'" />
							<input type="hidden" name="group_id" value="'.$group_id.'" />
							<input type="hidden" name="forum_id" value="'.$forum_id.'" />
							<input type="hidden" name="msg_id" value="'.$msg_id.'" />
							<input type="hidden" name="return_to_message" value="'.$return_to_message.'" />' .
			html_build_select_box_from_assoc($forums,'new_forum_id',$forum_id) .
			'<br /><br />
							<input type="submit" name="ok" value="' . _("Submit") . '" />
							<input type="submit" name="cancel" value="' . _("Cancel") . '" />    
							</p>
							</form>
							</center>';

		forum_footer(array());
	}

} else {
	/*
	  Show main page for choosing
	  either moderator or delete
	*/
		forum_header(array('title'=>_('Forums Administration')));

	//
	//	Add new forum
	//
	$fa = new ForumAdmin($g->getID());

	$fa->PrintAdminOptions();

	if ($f)
		plugin_hook ("blocks", "forum index");

	//
	//	Get existing forums
	//
	$ff=new ForumFactory($g);
	if (!$ff || !is_object($ff) || $ff->isError()) {
		exit_error($ff->getErrorMessage(),'forums');
	}

	$farr = $ff->getForumsAdmin();

	if ($ff->isError()) {
		echo '<p class="error">'.sprintf(_('No Forums Found For %s'), $g->getPublicName())
			. $ff->getErrorMessage().'</p>';
		forum_footer(array());
		exit;
	}

	/*
	  List the existing forums so they can be edited.
	*/

	for ($j = 0; $j < count($farr); $j++) {
		if (!is_object($farr[$j])) {
			//just skip it - this object should never have been placed here
		} elseif ($farr[$j]->isError()) {
			echo $farr[$j]->getErrorMessage();
		} else {
			echo '<p><a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;change_status=1&amp;group_forum_id='. $farr[$j]->getID() .'">'.
				$farr[$j]->getName() .'</a><br />'.$farr[$j]->getDescription().'<br /><a href="monitor.php?group_id='.$group_id.'&amp;group_forum_id='. $farr[$j]->getID() .'">'.
				_('Monitoring Users').'</a></p>';
		}
	}

	forum_footer(array());
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

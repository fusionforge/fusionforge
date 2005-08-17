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

	Heavy RBAC changes 3/17/2004
*/

require_once('pre.php');
require_once('www/forum/include/ForumHTML.class');
require_once('common/forum/Forum.class');
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');

$group_id = getIntFromRequest('group_id');
$group_forum_id = getIntFromRequest('group_forum_id');
$deleteforum = getStringFromRequest('deleteforum');
$feedback = getStringFromRequest('feedback');

if ($group_id) {
	//
	//  Set up local objects
	//
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$p =& $g->getPermission( session_get_user() );
	if (!$p || !is_object($p) || $p->isError()) {
		exit_permission_denied();
	}

	if (getStringFromRequest('post_changes')) {
		/*
			Update the DB to reflect the changes
		*/

		if ($deleteforum) {
			/*
				Deleting entire forum
			*/
			$f=new Forum($g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}
			if (!$f->delete(getStringFromRequest('sure'),getStringFromRequest('really_sure'))) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin','deleted');
				$group_forum_id=0;
				$deleteforum=0;
			}
		} elseif (getStringFromRequest('delete')) {
			$msg_id = getStringFromRequest('msg_id');
			/*
				Deleting messages or threads
			*/
			$f=new Forum($g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}

			$fm=new ForumMessage($f,$msg_id);
			if (!$fm || !is_object($fm)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($fm->isError()) {
				exit_error($Language->getText('general','error'),$fm->getErrorMessage());
			}
			$count=$fm->delete();
			if (!$count || $fm->isError()) {
				exit_error($Language->getText('general','error'),$fm->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin_delete_messages','messages_deleted',$count);
			}

		} else if (getStringFromRequest('add_forum')) {
			if (!form_key_is_valid(getStringFromRequest('form_key'))) {
				exit_form_double_submit();
			}
			$forum_name = getStringFromRequest('forum_name');
			$description = getStringFromRequest('description');
			$is_public = getStringFromRequest('is_public');
			$send_all_posts_to = getStringFromRequest('send_all_posts_to');
			$allow_anonymous = getStringFromRequest('allow_anonymous');
			/*
				Adding forums to this group
			*/
			if (!$p->isForumAdmin()) {
				form_release_key(getStringFromRequest("form_key"));
				exit_permission_denied();
			}
			$f=new Forum($g);
			if (!$f || !is_object($f)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->create($forum_name,$description,$is_public,$send_all_posts_to,1,$allow_anonymous)) {
				form_release_key(getStringFromRequest("form_key"));
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin_addforum','forum_created');
			}

		} else if (getStringFromRequest('change_status')) {
			$forum_name = getStringFromRequest('forum_name');
			$description = getStringFromRequest('description');
			$send_all_posts_to = getStringFromRequest('send_all_posts_to');
			/*
				Change a forum
			*/
			$f=new Forum($g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->userIsAdmin()) {
				exit_permission_denied();
			}
			if (!$f->update($forum_name,$description,$send_all_posts_to)) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin_changestatus','update_successful');
			}
		}

	}

	if (getStringFromRequest('add_forum')) {
		/*
			Show the form for adding forums
		*/
		forum_header(array('title'=>$Language->getText('forum_admin_addforum','title')));

		echo '
			<br>
			<form method="post" action="'.getStringFromServer('PHP_SELF').'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_forum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="form_key" value="' . form_generate_key() . '">
			<strong>'.$Language->getText('forum_admin_addforum','forum_name').':</strong><br />
			<input type="text" name="forum_name" value="" size="20" maxlength="30" /><br />
			<strong>'.$Language->getText('forum_admin_addforum','forum_description').':</strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br />
			<strong>'.$Language->getText('forum_admin_addforum','is_public').'</strong><br />
			<input type="radio" name="is_public" value="1" checked="checked" />'.$Language->getText('general','yes').' <br />
			<input type="radio" name="is_public" value="0" />'.$Language->getText('general','no').'
			<br  /><br />
			<strong>'.$Language->getText('forum_admin_addforum','allow_anonymous').'</strong><br />
			<input type="radio" name="allow_anonymous" value="1" />'.$Language->getText('general','yes').'<br />
			<input type="radio" name="allow_anonymous" value="0" checked="checked" />'.$Language->getText('general','no').'
			<br /><br />
			<strong>'.$Language->getText('forum_admin_addforum','email_posts').'</strong><br />
			<input type="text" name="send_all_posts_to" value="" size="30" maxlength="50" />
			<p>
			<input type="submit" name="submit" value="'.$Language->getText('forum_admin_addforum','add_forum').'" />
			</p>
			</form>';

		forum_footer(array());

	} else if (getStringFromRequest('change_status')) {
		/*
			Change a forum
		*/

		$f = new Forum ($g,$group_forum_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		} elseif (!$f->userIsAdmin()) {
			exit_permission_denied();
		}

		forum_header(array('title'=>$Language->getText('forum_admin_changestatus','change_status')));
		echo '<p>'.$Language->getText('forum_admin_changestatus','intro').'</p>';

		echo '
			<form action="'.getStringFromServer('PHP_SELF').'" method="post">
				<input type="hidden" name="post_changes" value="y" />
				<input type="hidden" name="change_status" value="y" />
				<input type="hidden" name="group_forum_id" value="'. $f->getID() .'" />
				<input type="hidden" name="group_id" value="'.$group_id.'" />
<!--				<span style="font-size:-1">
				<strong>'.$Language->getText('forum_admin_addforum','allow_anonymous').'</strong><br />
				<input type="radio" name="allow_anonymous" value="1"'.(($f->AllowAnonymous() == 1)?' checked="checked"':'').' /> '.$Language->getText('general','yes').'<br />
				<input type="radio" name="allow_anonymous" value="0"'.(($f->AllowAnonymous() == 0)?' checked="checked"':'').'/> '.$Language->getText('general','no').'<br />
				</span>
				<span style="font-size:-1">
				<strong>'.$Language->getText('forum_admin_addforum','is_public').'</strong><br />
				<input type="radio" name="is_public" value="1"'.(($f->isPublic() == 1)?' checked="checked"':'').' /> '.$Language->getText('general','yes').'<br />
				<input type="radio" name="is_public" value="0"'.(($f->isPublic() == 0)?' checked="checked"':'').' /> '.$Language->getText('general','no').'<br />
				<input type="radio" name="is_public" value="9"'.(($f->isPublic() == 9)?' checked="checked"':'').' />'.$Language->getText('general','deleted').'<br />
				</span></td><td>
				<span style="font-size:-1">
-->
				<strong>'.$Language->getText('forum_admin_addforum','forum_name').':</strong><br />
				<input type="text" name="forum_name" value="'. $f->getName() .'" size="20" maxlength="30" />
				<p>
				<strong>'.$Language->getText('forum_admin_addforum','email_posts').'</strong><br />
				<input type="text" name="send_all_posts_to" value="'. $f->getSendAllPostsTo() .'" size="30" maxlength="50" />
				<p>
				<strong>'.$Language->getText('forum_admin_addforum','forum_description').':</strong><br />
				<input type="text" name="description" value="'. $f->getDescription() .'" size="40" maxlength="80" /><br />
				<p>
				<input type="submit" name="submit" value="'.$Language->getText('general','update').'" /></span>
			</form><p>';
			echo '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_forum_id='.$group_forum_id.'&amp;delete=1">'.$Language->getText('forum_admin','delete_message').'</a><br />';
			echo '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;group_forum_id='.$group_forum_id.'&amp;deleteforum=1">'.$Language->getText('forum_admin','delete_forum').'</a><br />';
		forum_footer(array());

	} elseif ($deleteforum && $group_forum_id) {

		$f = new Forum ($g,$group_forum_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		} elseif (!$f->userIsAdmin()) {
			exit_permission_denied();
		}
		forum_header(array('title'=>$Language->getText('forum_admin','delete')));
		echo '<p>
			<strong>'.$Language->getText('forum_admin','delete_warning').'</strong><br />
			<form method="post" action="'.getStringFromServer('PHP_SELF').'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="deleteforum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="group_forum_id" value="'.$group_forum_id.'" />
			<input type="checkbox" name="sure" value="1" />'.$Language->getText('forum_admin','sure').'<br />
			<input type="checkbox" name="really_sure" value="1" />'.$Language->getText('forum_admin','really_sure').'<br />
			<input type="submit" name="submit" value="'.$Language->getText('forum_admin','delete').'" />
			</form>';
		forum_footer(array());

	} elseif (getStringFromRequest('delete') && $group_forum_id) {

		$f = new Forum ($g,$group_forum_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		} elseif (!$f->userIsAdmin()) {
			exit_permission_denied();
		}
		forum_header(array('title'=>$Language->getText('forum_admin_changestatus','change_status')));
		echo '<p>
			<strong>'.$Language->getText('general','delete').'</strong><br />
			<form method="post" action="'.getStringFromServer('PHP_SELF').'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="delete" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="group_forum_id" value="'.$group_forum_id.'" />
			<strong>'.$Language->getText('forum_admin_delete_message','enter_message_id').'</strong><br />
			<input type="text" name="msg_id" value="" />
			<input type="submit" name="submit" value="'.$Language->getText('general','delete').'" />
			</form>';
		forum_footer(array());

	} else {
		/*
			Show main page for choosing
			either moderator or delete
		*/
		forum_header(array('title'=>$Language->getText('forum_admin','title')));

		//
		//	Add new forum
		//
		if ($p->isForumAdmin()) {
			echo '
			<p>
			<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;add_forum=1">'.$Language->getText('forum_admin','add_forum').'</a><br /></p>';
		}
		//
		//	Get existing forums
		//
		$ff=new ForumFactory($g);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			exit_error($Language->getText('general','error'),$ff->getErrorMessage());
		}

		$farr =& $ff->getForums();

		if ($ff->isError()) {
			echo '<h1>'.$Language->getText('forum','error_no_forums_found', array($g->getPublicName())) .'</h1>';
			echo $ff->getErrorMessage();
			forum_footer(array());
			exit;
		}

		/*
			List the existing forums so they can be edited.
		*/

		for ($j = 0; $j < count($farr); $j++) {
			if ($farr[$j]->isError()) {
				echo $farr->getErrorMessage();
			} else {
				echo '<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;change_status=1&amp;group_forum_id='. $farr[$j]->getID() .'">'.
					$farr[$j]->getName() .'</a><br />'.$farr[$j]->getDescription().'<p>';
			}
		}

		forum_footer(array());
	}

} else {
	/*
		Not logged in or insufficient privileges
	*/
	if (!$group_id) {
		exit_no_group();
	} else {
		exit_permission_denied();
	}
}
?>

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
require_once('common/forum/ForumFactory.class');
require_once('common/forum/ForumMessageFactory.class');
require_once('common/forum/ForumMessage.class');

if ($group_id) {
	//
	//  Set up local objects
	//
	$g =& group_get_object($group_id);
	if (!$g || !is_object($g) || $g->isError()) {
		exit_no_group();
	}

	$p =& $g->getPermission( session_get_user() );
	if (!$p || !is_object($p) || $p->isError() || !$p->isForumAdmin()) {
		exit_permission_denied();
	}

	if ($post_changes) {
		/*
			Update the DB to reflect the changes
		*/

		if ($delete) {
			/*
				Deleting messages or threads
			*/
			$res=db_query("SELECT group_forum_id 
				FROM forum 
				WHERE msg_id='$msg_id'");

			if (!$res || db_numrows($res) < 1) {
				exit_error('Error','Error Determining forum_id');
			}
			$f=new Forum($g,db_result($res,0,'group_forum_id'));
			if (!$f || !is_object($f)) {
				exit_error('Error','Error Getting Forum');
			} elseif ($f->isError()) {
				exit_error('Error',$f->getErrorMessage());
			}
			$fm=new ForumMessage($f,$msg_id);
			if (!$fm || !is_object($fm)) {
				exit_error('Error','Error Getting Forum');
			} elseif ($fm->isError()) {
				exit_error('Error',$fm->getErrorMessage());
			}
			$count=$fm->delete();
			if (!$count || $fm->isError()) {
				exit_error('Error',$fm->getErrorMessage());
			} else {
				$feedback .= " $count messages deleted ";
			}

		} else if ($add_forum) {
			/*
				Adding forums to this group
			*/
			$f=new Forum($g);
			if (!$f || !is_object($f)) {
				exit_error('Error','Error Getting Forum');
			} elseif ($f->isError()) {
				exit_error('Error',$f->getErrorMessage());
			}
			if (!$f->create($forum_name,$description,$is_public,$send_all_posts_to,1,$allow_anonymous)) {
				exit_error('Error',$f->getErrorMessage());
			} else {
				$feedback .= ' Forum Created Successfully ';
			}

		} else if ($change_status) {
			/*
				Change a forum to public/private
			*/
			$f=new Forum($g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error('Error','Error Getting Forum');
			} elseif ($f->isError()) {
				exit_error('Error',$f->getErrorMessage());
			}
			if (!$f->update($forum_name,$description,$is_public,$send_all_posts_to,$allow_anonymous)) {
				exit_error('Error',$f->getErrorMessage());
			} else {
				$feedback .= " Forum Info Updated Successfully ";
			}
		}

	} 

	if ($delete) {
		/*
			Show page for deleting messages
		*/
		forum_header(array('title'=>'Delete a message','pagename'=>'forum_admin_delete','sectionvals'=>group_getname($group_id)));

		echo '
			<span style="color:red">WARNING! You are about to permanently delete a
			message and all of its followups!!</span>
			<form method="post" action="'.$PHP_SELF.'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="delete" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<strong>Enter the Message ID</strong><br />
			<input type="text" name="msg_id" value="" />
			<input type="submit" name="submit" value="submit" />
			</form>';

		forum_footer(array());

	} else if ($add_forum) {
		/*
			Show the form for adding forums
		*/
		forum_header(array('title'=>'Add a Forum','pagename'=>'forum_admin_addforum','sectionvals'=>group_getname($group_id)));

		$sql="SELECT forum_name FROM forum_group_list WHERE group_id='$group_id'";
		$result=db_query($sql);
		ShowResultSet($result,'Existing Forums');

		echo '
			<form method="post" action="'.$PHP_SELF.'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_forum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<strong>Forum Name:</strong><br />
			<input type="text" name="forum_name" value="" size="20" maxlength="30" /><br />
			<strong>Description:</strong><br />
			<input type="text" name="description" value="" size="40" maxlength="80" /><br />
			<strong>Is Public?</strong><br />
			<input type="radio" name="is_public" value="1" checked="checked"="checked="checked"" /> Yes<br />
			<input type="radio" name="is_public" value="0" /> No
			<br  /><br />
			<strong>Allow Anonymous Posts?</strong><br />
			<input type="radio" name="allow_anonymous" value="1" /> Yes<br />
			<input type="radio" name="allow_anonymous" value="0" checked="checked"="checked="checked"" /> No
			<br /><br />
			<strong>Email All Posts To:</strong><br />
			<input type="text" name="send_all_posts_to" value="" size="30" maxlength="50" />
			<p>
			<input type="submit" name="submit" value="Add This Forum" />
			</form>';

		forum_footer(array());

	} else if ($change_status) {
		/*
			Change a forum to public/private
		*/

		$ff = new ForumFactory($g);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			exit_error('Error',$ff->getErrorMessage());
		}
		$farr =& $ff->getForums();

		$rows=count($farr);
		if ($ff->isError() || count($farr) < 1) {
			exit_error('Error','No Forums Found: '.$ff->getErrorMessage());
		} else {
			forum_header(array('title'=>'Change Forum Status','pagename'=>'forum_admin_changestatus','sectionvals'=>group_getname($group_id)));
			echo '
			<p>
			You can adjust forum features from here. Please note that private forums 
			can still be viewed by members of your project, not the general public.<p>';

			$title_arr=array();
			$title_arr[]='Forum';
			$title_arr[]='Status';
			$title_arr[]='Update';
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i<$rows; $i++) {
				echo '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="3"><strong>'. $farr[$i]->getName() .'</strong></td></tr>';
				echo '
					<form action="'.$PHP_SELF.'" method="post">
					<input type="hidden" name="post_changes" value="y" />
					<input type="hidden" name="change_status" value="y" />
					<input type="hidden" name="group_forum_id" value="'. $farr[$i]->getID() .'" />
					<input type="hidden" name="group_id" value="'.$group_id.'" />
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td>
						<span style="font-size:-1">
						<strong>Allow Anonymous Posts?</strong><br />
						<input type="radio" name="allow_anonymous" value="1"'.(($farr[$i]->AllowAnonymous() == 1)?' checked="checked"':'').' /> Yes<br />
						<input type="radio" name="allow_anonymous" value="0"'.(($farr[$i]->AllowAnonymous() == 0)?' checked="checked"':'').'/> No<br />
						</span>
					</td>
					<td>
						<span style="font-size:-1">
						<strong>Is Public?</strong><br />
						<input type="radio" name="is_public" value="1"'.(($farr[$i]->isPublic() == 1)?' checked="checked"':'').' /> Yes<br />
						<input type="radio" name="is_public" value="0"'.(($farr[$i]->isPublic() == 0)?' checked="checked"':'').' /> No<br />
						<input type="radio" name="is_public" value="9"'.(($farr[$i]->isPublic() == 9)?' checked="checked"':'').' /> Deleted<br />
					</span></td><td>
						<span style="font-size:-1">
						<input type="submit" name="submit" value="Update Info" /></span>
					</td></tr>
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>
						<strong>Forum Name:</strong><br />
						<input type="text" name="forum_name" value="'. $farr[$i]->getName() .'" size="20" maxlength="30" />
					</td><td colspan="2">
						<strong>Email All Posts To:</strong><br />
						<input type="text" name="send_all_posts_to" value="'. $farr[$i]->getSendAllPostsTo() .'" size="30" maxlength="50" />
					</td></tr>
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="3">
						<strong>Description:</strong><br />
						<input type="text" name="description" value="'. $farr[$i]->getDescription() .'" size="40" maxlength="80" /><br />
					</td></tr></form>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		}

		forum_footer(array());

	} else {
		/*
			Show main page for choosing 
			either moderotor or delete
		*/
		forum_header(array('title'=>'Forum Administration','pagename'=>'forum_admin','sectionvals'=>group_getname($group_id)));

		echo '
			<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&add_forum=1">Add Forum</a><br />
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&delete=1">Delete Message</a><br />
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&change_status=1">Update Forum Info/Status</a></p>';

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

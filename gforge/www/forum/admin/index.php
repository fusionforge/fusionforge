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
			<FONT COLOR="RED" SIZE="3">WARNING! You are about to permanently delete a 
			message and all of its followups!!</FONT>
			<FORM METHOD="POST" ACTION="'.$PHP_SELF.'">
			<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="delete" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
			<B>Enter the Message ID</B><BR>
			<INPUT TYPE="TEXT" NAME="msg_id" VALUE="">
			<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="SUBMIT">
			</FORM>';

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
			<FORM METHOD="POST" ACTION="'.$PHP_SELF.'">
			<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="add_forum" VALUE="y">
			<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
			<B>Forum Name:</B><BR>
			<INPUT TYPE="TEXT" NAME="forum_name" VALUE="" SIZE="20" MAXLENGTH="30"><BR>
			<B>Description:</B><BR>
			<INPUT TYPE="TEXT" NAME="description" VALUE="" SIZE="40" MAXLENGTH="80"><BR>
			<B>Is Public?</B><BR>
			<INPUT TYPE="RADIO" NAME="is_public" VALUE="1" CHECKED> Yes<BR>
			<INPUT TYPE="RADIO" NAME="is_public" VALUE="0"> No<P>
			<P>
			<B>Allow Anonymous Posts?</B><BR>
			<INPUT TYPE="RADIO" NAME="allow_anonymous" VALUE="1"> Yes<BR>
			<INPUT TYPE="RADIO" NAME="allow_anonymous" VALUE="0" CHECKED> No<BR>
			<P>
			<B>Email All Posts To:</B><BR>
			<INPUT TYPE="TEXT" NAME="send_all_posts_to" VALUE="" SIZE="30" MAXLENGTH="50">
			<P>
			<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Add This Forum">
			</FORM>';

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
			<P>
			You can adjust forum features from here. Please note that private forums 
			can still be viewed by members of your project, not the general public.<P>';

			$title_arr=array();
			$title_arr[]='Forum';
			$title_arr[]='Status';
			$title_arr[]='Update';
		
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i<$rows; $i++) {
				echo '
					<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><TD COLSPAN="3"><B>'. $farr[$i]->getName() .'</B></TD></TR>';
				echo '
					<FORM ACTION="'.$PHP_SELF.'" METHOD="POST">
					<INPUT TYPE="HIDDEN" NAME="post_changes" VALUE="y">
					<INPUT TYPE="HIDDEN" NAME="change_status" VALUE="y">
					<INPUT TYPE="HIDDEN" NAME="group_forum_id" VALUE="'. $farr[$i]->getID() .'">
					<INPUT TYPE="HIDDEN" NAME="group_id" VALUE="'.$group_id.'">
					<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<TD>
						<FONT SIZE="-1">
						<B>Allow Anonymous Posts?</B><BR>
						<INPUT TYPE="RADIO" NAME="allow_anonymous" VALUE="1"'.(($farr[$i]->AllowAnonymous() == 1)?' CHECKED':'').'> Yes<BR>
						<INPUT TYPE="RADIO" NAME="allow_anonymous" VALUE="0"'.(($farr[$i]->AllowAnonymous() == 0)?' CHECKED':'').'> No<BR>
						</FONT>
					</TD>
					<TD>
						<FONT SIZE="-1">
						<B>Is Public?</B><BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="1"'.(($farr[$i]->isPublic() == 1)?' CHECKED':'').'> Yes<BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="0"'.(($farr[$i]->isPublic() == 0)?' CHECKED':'').'> No<BR>
						<INPUT TYPE="RADIO" NAME="is_public" VALUE="9"'.(($farr[$i]->isPublic() == 9)?' CHECKED':'').'> Deleted<BR>
					</TD><TD>
						<FONT SIZE="-1">
						<INPUT TYPE="SUBMIT" NAME="SUBMIT" VALUE="Update Info">
					</TD></TR>
					<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><TD>
						<B>Forum Name:</B><BR>
						<INPUT TYPE="TEXT" NAME="forum_name" VALUE="'. $farr[$i]->getName() .'" SIZE="20" MAXLENGTH="30">
					</TD><TD COLSPAN="2">
						<B>Email All Posts To:</B><BR>
						<INPUT TYPE="TEXT" NAME="send_all_posts_to" VALUE="'. $farr[$i]->getSendAllPostsTo() .'" SIZE="30" MAXLENGTH="50">
					</TD></TR>
					<TR '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><TD COLSPAN="3">
						<B>Description:</B><BR>
						<INPUT TYPE="TEXT" NAME="description" VALUE="'. $farr[$i]->getDescription() .'" SIZE="40" MAXLENGTH="80"><BR>
					</TD></TR></FORM>';
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
			<P>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&add_forum=1">Add Forum</A><BR>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&delete=1">Delete Message</A><BR>
			<A HREF="'.$PHP_SELF.'?group_id='.$group_id.'&change_status=1">Update Forum Info/Status</A>';

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

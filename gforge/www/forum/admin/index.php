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

function showAdminRow() {
	$tablearr=array($Language->getText('forum_admin_addforum','existing_forums'));
	echo $HTML->listTableTop($tablearr);
}

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
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_determining_forum_id'));
			}
			$f=new Forum($g,db_result($res,0,'group_forum_id'));
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
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

		} else if ($add_forum) {
			/*
				Adding forums to this group
			*/
			$f=new Forum($g);
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->create($forum_name,$description,$is_public,$send_all_posts_to,1,$allow_anonymous)) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin_addforum','forum_created');
			}

		} else if ($change_status) {
			/*
				Change a forum to public/private
			*/
			$f=new Forum($g,$group_forum_id);
			if (!$f || !is_object($f)) {
				exit_error($Language->getText('general','error'),$Language->getText('forum_errors','error_getting_forum'));
			} elseif ($f->isError()) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			}
			if (!$f->update($forum_name,$description,$is_public,$send_all_posts_to,$allow_anonymous)) {
				exit_error($Language->getText('general','error'),$f->getErrorMessage());
			} else {
				$feedback .= $Language->getText('forum_admin_changestatus','update_successful');
			}
		}

	}

	if ($delete) {
		/*
			Show page for deleting messages
		*/
		forum_header(array('title'=>$Language->getText('forum_admin_delete_message','title'),'pagename'=>'forum_admin_delete','sectionvals'=>group_getname($group_id)));

		echo '
			<span style="color:red">'.$Language->getText('forum_admin_delete_message','warning').'
			</span>
			<form method="post" action="'.$PHP_SELF.'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="delete" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<strong>'.$Language->getText('forum_admin_delete_message','enter_message_id').'</strong><br />
			<input type="text" name="msg_id" value="" />
			<input type="submit" name="submit" value="'.$Language->getText('general','submit').'" />
			</form>';

		forum_footer(array());

	} else if ($add_forum) {
		/*
			Show the form for adding forums
		*/
		forum_header(array('title'=>$Language->getText('forum_admin_addforum','title'),'pagename'=>'forum_admin_addforum','sectionvals'=>group_getname($group_id)));

//		$sql="SELECT forum_name FROM forum_group_list WHERE group_id='$group_id'";
//		$result=db_query($sql);
//		ShowResultSet($result,$Language->getText('forum_admin_addforum','existing_forums'));
		$ff=new ForumFactory($g);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			exit_error($Language->getText('general','error'),$ff->getErrorMessage());
		}
		
		$farr =& $ff->getForums();

		if ($ff->isError()) {
			$tablearr=array($Language->getText('forum_admin_addforum','existing_forums'));
			echo $HTML->listTableTop($tablearr);
			echo '<h1>'.$Language->getText('forum','error_no_forums_found', array($g->getPublicName())) .'</h1>';
			echo $ff->getErrorMessage();
			forum_footer(array());
			exit;
		}

		$tablearr=array($Language->getText('forum_admin_addforum','existing_forums'));
		echo $HTML->listTableTop($tablearr);

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/

		for ($j = 0; $j < count($farr); $j++) {
			if ($farr[$j]->isError()) {
				echo $farr->getErrorMessage();
			} else {
				echo '<tr '. $HTML->boxGetAltRowStyle($j) . '><td><a href="/forum/forum.php?forum_id='. $farr[$j]->getID() .'">'.
					html_image("ic/forum20w.png","20","20",array("border"=>"0")) .
					'&nbsp;' .
					$farr[$j]->getName() .'</a><br />'.$farr[$j]->getDescription().'</td></tr>';
			}
		}
		echo $HTML->listTableBottom();

		echo '
			<br>
			<form method="post" action="'.$PHP_SELF.'">
			<input type="hidden" name="post_changes" value="y" />
			<input type="hidden" name="add_forum" value="y" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
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

	} else if ($change_status) {
		/*
			Change a forum to public/private
		*/

		$ff = new ForumFactory($g);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			exit_error($Language->getText('general','error'),$ff->getErrorMessage());
		}
		$farr =& $ff->getForums();

		$rows=count($farr);
		if ($ff->isError()) {
			exit_error($Language->getText('general','error'),$Language->getText('forum_admin_changestatus','no_forums_found').$ff->getErrorMessage());
		} else {
		
			if ($rows > 0) {
				$title_arr=array();
				forum_header(array('title'=>$Language->getText('forum_admin_changestatus','change_status'),'pagename'=>'forum_admin_changestatus','sectionvals'=>group_getname($group_id)));
				echo '<p>'.$Language->getText('forum_admin_changestatus','intro').'.</p>';
				$title_arr[]=$Language->getText('forum_admin_changestatus','forum');
				$title_arr[]=$Language->getText('forum_admin_changestatus','status');
				$title_arr[]=$Language->getText('forum_admin_changestatus','update');
				echo $GLOBALS['HTML']->listTableTop ($title_arr);
			} else {
  			global $DOCUMENT_ROOT,$HTML,$group_id,$forum_name,$forum_id,$sys_datefmt,$sys_news_group,$Language,$f;
 		 		$params['group']=$group_id;
		  	$params['toptab']='forums';
				site_project_header($params);
				echo '<strong><a href="/forum/admin/?group_id='.$group_id.'">'.$Language->getText('forum_utils','admin').'</a></strong>';
			}

			for ($i=0; $i<$rows; $i++) {
				echo '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="3"><strong>'. $farr[$i]->getName() .'</strong></td></tr>';
				echo '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
					<td colspan="3">
						<form action="'.$PHP_SELF.'" method="post">
						<input type="hidden" name="post_changes" value="y" />
						<input type="hidden" name="change_status" value="y" />
						<input type="hidden" name="group_forum_id" value="'. $farr[$i]->getID() .'" />
						<input type="hidden" name="group_id" value="'.$group_id.'" />
						<table width="100%"><tr><td valign="top">
						<span style="font-size:-1">
						<strong>'.$Language->getText('forum_admin_addforum','allow_anonymous').'</strong><br />
						<input type="radio" name="allow_anonymous" value="1"'.(($farr[$i]->AllowAnonymous() == 1)?' checked="checked"':'').' /> '.$Language->getText('general','yes').'<br />
						<input type="radio" name="allow_anonymous" value="0"'.(($farr[$i]->AllowAnonymous() == 0)?' checked="checked"':'').'/> '.$Language->getText('general','no').'<br />
						</span>
						</td>
						<td valign="top">
						<span style="font-size:-1">
						<strong>'.$Language->getText('forum_admin_addforum','is_public').'</strong><br />
						<input type="radio" name="is_public" value="1"'.(($farr[$i]->isPublic() == 1)?' checked="checked"':'').' /> '.$Language->getText('general','yes').'<br />
						<input type="radio" name="is_public" value="0"'.(($farr[$i]->isPublic() == 0)?' checked="checked"':'').' /> '.$Language->getText('general','no').'<br />
						<input type="radio" name="is_public" value="9"'.(($farr[$i]->isPublic() == 9)?' checked="checked"':'').' />'.$Language->getText('general','deleted').'<br />
					</span></td><td>
						<span style="font-size:-1">
						<input type="submit" name="submit" value="'.$Language->getText('general','update').'" /></span>
					</td></tr>
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td>
						<strong>'.$Language->getText('forum_admin_addforum','forum_name').':</strong><br />
						<input type="text" name="forum_name" value="'. $farr[$i]->getName() .'" size="20" maxlength="30" />
					</td><td colspan="2">
						<strong>'.$Language->getText('forum_admin_addforum','email_posts').'</strong><br />
						<input type="text" name="send_all_posts_to" value="'. $farr[$i]->getSendAllPostsTo() .'" size="30" maxlength="50" />
					</td></tr>
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'><td colspan="3">
						<strong>'.$Language->getText('forum_admin_addforum','forum_description').':</strong><br />
						<input type="text" name="description" value="'. $farr[$i]->getDescription() .'" size="40" maxlength="80" /><br />
					</td></tr></table></form>
				</td></tr>';
			}

			echo $GLOBALS['HTML']->listTableBottom();

		}

		forum_footer(array());

	} else {
		/*
			Show main page for choosing
			either moderotor or delete
		*/
		forum_header(array('title'=>$Language->getText('forum_admin','title'),'pagename'=>'forum_admin','sectionvals'=>group_getname($group_id)));

		echo '
			<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;add_forum=1">'.$Language->getText('forum_admin','add_forum').'</a><br />
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;delete=1">'.$Language->getText('forum_admin','delete_message').'</a><br />
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;change_status=1">'.$Language->getText('forum_admin','update_forum').'</a></p>';

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

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
require_once('note.php');
require_once('www/news/news_utils.php');
require_once('www/forum/admin/ForumAdmin.class.php');
require_once('www/forum/include/AttachManager.class.php');

function forum_header($params) {
	global $HTML,$group_id,$forum_name,$forum_id,$sys_news_group,$f,$sys_use_forum,$group_forum_id;

	if ($group_forum_id) {
		$forum_id=$group_forum_id;
	}
	if (!$sys_use_forum) {
		exit_disabled();
	}

	$params['group']=$group_id;
	$params['toptab']='forums';

	/*
		bastardization for news
		Show icon bar unless it's a news forum
		*/
	if ($group_id == $sys_news_group) {
		//this is a news item, not a regular forum
		if ($forum_id) {
			// Show this news item at the top of the page
			$sql="SELECT submitted_by, post_date, group_id, forum_id, summary, details FROM news_bytes WHERE forum_id='$forum_id'";
			$result=db_query($sql);

			// checks which group the news item belongs to
			$params['group']=db_result($result,0,'group_id');
			$params['toptab']='news';
			$HTML->header($params);


			echo '<table><tr><td valign="top">';
			if (!$result || db_numrows($result) < 1) {
				echo '<h3>'._('Error - this news item was not found').'</h3>';
			} else {
				$user = user_get_object(db_result($result,0,'submitted_by'));
				$group =& group_get_object($params['group']);
				if (!$group || !is_object($group) || $group->isError()) {
					exit_no_group();
				}
				echo '
				<strong>'._('Posted by').':</strong> '.$user->getRealName().'<br />
				<strong>'._('Date').':</strong> '. date(_('Y-m-d H:i'),db_result($result,0,'post_date')).'<br />
				<strong>'._('Summary').':</strong>'.
					util_make_link ('/forum/forum.php?forum_id='.db_result($result,0,'forum_id').'&group_id='.$group_id,
							db_result($result,0,'summary')).'<br/>
				<strong>'._('Project').':</strong>'.
					util_make_link ('/projects/'.$group->getUnixName(),
							$group->getPublicName()).'<br />
				<p>
				'. (util_make_links(nl2br(db_result($result,0,'details'))));

				echo '</p>';
			}
			echo '</td><td valign="top" width="35%">';
			echo $HTML->boxTop(_('Latest News'));
			echo news_show_latest($params['group'],5,false);
			echo $HTML->boxBottom();
			echo '</td></tr></table>';
		} else {
			site_project_header($params);
		}
	} else {
		site_project_header($params);
	}

	$menu_text=array();
	$menu_links=array();

	if ($f){
		if ($f->userIsAdmin()) {
			$menu_text[]=_('Admin');
			$menu_links[]='/forum/admin/?group_id='.$group_id;
		} 
		if ($forum_id) {
			$menu_text[]=_('Discussion Forums:') .' '. $f->getName();
			$menu_links[]='/forum/forum.php?forum_id='.$forum_id;
		}
	} else {
			$gg=&group_get_object($group_id);
			$perm =& $gg->getPermission( session_get_user() );
			if ($perm->isForumAdmin()) {
				$menu_text[]=_('Admin');
				$menu_links[]='/forum/admin/?group_id='.$group_id;
			}
	}
	if (count($menu_text) > 0) {
		echo $HTML->subMenu(
		$menu_text,
		$menu_links
		);
	}
	
	if (session_loggedin() ) {
		if ($f) {
			if ($f->isMonitoring()) {
				echo util_make_link ('/forum/monitor.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'&amp;stop=1',
						     html_image('ic/xmail16w.png','20','20',array()).' '._('Stop Monitoring')).' | ';
			} else {
				echo util_make_link ('/forum/monitor.php?forum_id='.$forum_id.'&amp;group_id='.$group_id.'&amp;start=1',
						     html_image('ic/mail16w.png','20','20',array()).' '._('Monitor Forum')).' | ';
			}
			echo util_make_link ('/forum/save.php?forum_id='.$forum_id.'&amp;group_id='.$group_id,
					     html_image('ic/save.png','24','24',array()) .' '._('Save Place')).' | ';
		}
	}

	if ($f && $forum_id) {
		echo util_make_link ('/forum/new.php?forum_id='.$forum_id.'&amp;group_id='.$group_id,
				     html_image('ic/write16w.png','20','20',array('alt'=>_('Start New Thread'))) .' '.
				     _('Start New Thread'));
	}
}

function forum_footer($params) {
	site_project_footer($params);
}


/**

Wrap many forum functions in this class

**/
class ForumHTML extends Error {
	/**
	 * The Forum object.
	 *
	 * @var  object  $Forum
	 */
	var $Forum;

	function ForumHTML(&$Forum) {
		$this->Error();
		if (!$Forum || !is_object($Forum)) {
			$this->setError('ForumMessage:: No Valid Forum Object');
			return false;
		}
		if ($Forum->isError()) {
			$this->setError('ForumMessage:: '.$Forum->getErrorMessage());
			return false;
		}
		$this->Forum =& $Forum;
		return true;
	}


	/**
	 * Function showPendingMessage
	 *
	 * @param 	object	The message.
	 *
	 * @return 	returns the html output
	 */
	function showPendingMessage ( &$msg) {
		global $HTML,$group_id;

		$am = new AttachManager();
		$ret_val = $am->PrintHelperFunctions();
		html_feedback_top(_('This is the content of the pending message'));
		$ret_val .= '
		<table border="0">
			<tr>
				<td class="tablecontent" nowrap="nowrap">'._('By:').
		$msg->getPosterRealName().
		'<br />
					';
		$msgforum =& $msg->getForum();
		$ret_val .= $am->PrintAttachLink($msg,$group_id,$msgforum->getID()) . '
					<br />
					'.
		html_image('ic/msg.png',"10","12",array("border"=>"0")) .
		$bold_begin. $msg->getSubject() . $bold_end .'&nbsp; '.
		'<br />'. date(_('Y-m-d H:i'),$msg->getPostDate()) .'
				</td>
			</tr>
			<tr>
				<td>
					'.  $msg->getBody() .'
				</td>
			</tr>
		</table>';
		return $ret_val;

	}

	function showNestedMessage ( &$msg ) {
		/*

		accepts a database result handle to display a single message
		in the format appropriate for the nested messages

		*/
		global $HTML,$group_id;
		/*
			See if this message is new or not
			If so, highlite it in bold
			*/
		if ($this->Forum->getSavedDate() < $msg->getPostDate()) {
			$bold_begin='<strong>';
			$bold_end='</strong>';
		} else {
			$bold_begin = '';
			$bold_end = '';
		}
		$am = new AttachManager();
		$fa = new ForumAdmin();
		$msgforum =& $msg->getForum();
		$ret_val =
		'
		<table border="0">
			<tr>
				<td class="tablecontent" nowrap="nowrap">'; if ($msgforum->userIsAdmin()) {$ret_val .= $fa->PrintAdminMessageOptions($msg->getID(),$group_id,$msg->getThreadID(),$msgforum->getID());} $ret_val .= _('By:').' '.util_make_link ('/users/'.$msg->getPosterName() .'/',
																																$msg->getPosterRealName()).'<br />
';
		$ret_val .= $am->PrintAttachLink($msg,$group_id,$msgforum->getID()) . '
					<br />'.util_make_link ('/forum/message.php?msg_id='.$msg->getID() .'&group_id='.$group_id,
		html_image('ic/msg.png',"10","12",array("border"=>"0")) .
								$bold_begin. $msg->getSubject() .' [ '._('reply').' ]'. $bold_end) .' &nbsp; '.
		'<br />'. date(_('Y-m-d H:i'),$msg->getPostDate()) .'
				</td>
			</tr>
			<tr>
				<td>
					'; 
		if (!strstr($msg->getBody(),'<')) {
			$ret_val .= nl2br($msg->getBody()); //backwards compatibility for non html messages
		} else {
			$ret_val .= $msg->getBody();
		}
		$ret_val .= '
				</td>
			</tr>
		</table>';
		return $ret_val;
	}

	/**
	 *  LinkAttachEditForm - Returns the link to the attach form for editing
	 *
	 *	@param 		string	Filename
	 *	@param 		int		group id
	 *	@param 		int		forum id
	 *	@param 		int		attach id
	 *	@param 		int		msg id
	 *
	 *	@return		The HTML output
	 */

	function LinkAttachEditForm($filename,$group_id,$forum_id,$attachid,$msg_id) {
		$return_val = '
			
			<p>
			<form action="' . getStringFromServer('PHP_SELF') . '" method="post" enctype="multipart/form-data">
			<table>
			<tr>
				<td>' . _('Current File') . ": <span class=\"selected\">" . $filename . '</span></td>
			</tr>
			</table>
			
			<fieldset class=\"fieldset\">
			<table>
					
					<tr>
						<td>' . _('Use the "Browse" button to find the file you want to attach') . '</td>
					</tr>
					<tr>
						<td>' . _('File to upload') . ':   <input type="file" name="attachment1"/></td>
					</tr>
					<tr>
						<td class="warning">' . _('Warning : Current file will be deleted permanently') . '</td>
					</tr>
			</table>
			<input type="submit" name="go" value="'._('Update').'">
			<input type="hidden" name="doedit" value="1"/>
			<input type="hidden" name="edit" value="yes"/>
			<input type="hidden" name="forum_id" value="'.$forum_id.'"/>
			<input type="hidden" name="group_id" value="'.$group_id.'"/>
			<input type="hidden" name="attachid" value="'.$attachid.'"/>
			<input type="hidden" name="msg_id" value="'.$msg_id.'"/>
			</fieldset></form><p>';
		return $return_val;
	}

	/**
	 *  LinkAttachForm - echoes the link to the attach form
	 *
	 *	@return		The HTML output echoed
	 */

	function LinkAttachForm() {
		$poststarttime = time();
		$posthash = md5($poststarttime . user_getid() );
		echo "
		<fieldset class=\"fieldset\">
		<table>
				<tr>
					<td>" . _('Use the "Browse" button to find the file you want to attach') . "</td>
				</tr>
				<tr>
					<td>" . _('File to upload') . ":   <input type=\"file\" name=\"attachment1\"/></td>
				</tr>
		</table>
		
		</fieldset>";	

	}


	function showNestedMessages ( &$msg_arr, $msg_id ) {
		global $total_rows;

		$rows=count($msg_arr["$msg_id"]);
		$ret_val='';

		if ($msg_arr["$msg_id"] && $rows > 0) {
			$ret_val .= '
			<ul><li style="list-style: none">';

			/*

			iterate and show the messages in this result

			for each message, recurse to show any submessages

			*/
			$am = new AttachManager();
			for ($i=($rows-1); $i >= 0; $i--) {
				//	  increment the global total count
				$total_rows++;

				//	  show the actual nested message
				$ret_val .= $this->showNestedMessage ($msg_arr["$msg_id"][$i]).'<p />';

				if ($msg_arr["$msg_id"][$i]->hasFollowups()) {
					//	  Call yourself if there are followups
					$ret_val .= $this->showNestedMessages ( $msg_arr,$msg_arr["$msg_id"][$i]->getID() );
				}
			}
			$ret_val .= '
			</li></ul>';
		} else {
			//$ret_val .= "<p><strong>no messages actually follow up to $msg_id</strong>";
		}

		return $ret_val;
	}

	function showSubmessages(&$msg_arr, $msg_id, $level) {
		/*
			Recursive. Selects this message's id in this thread,
			then checks if any messages are nested underneath it.
			If there are, it calls itself, incrementing $level
			$level is used for indentation of the threads.
			*/
		global $total_rows,$forum_id,$current_message,$group_id;

		$rows=count($msg_arr["$msg_id"]);
		$ret_val = "";
		//echo "<p>ShowSubmessages() $msg_id | $rows";
		if ($rows > 0) {
			for ($i=($rows-1); $i >= 0; $i--) {
				/*
					Is this row's background shaded or not?
					*/
				$total_rows++;

				$ret_val .= '
					<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($total_rows) .'><td nowrap="nowrap">';
				/*
					How far should it indent?
					*/
				for ($i2=0; $i2<$level; $i2++) {
					$ret_val .= ' &nbsp; &nbsp; &nbsp; ';
				}

				/*
					If it this is the message being displayed, don't show a link to it
					*/
				if ($current_message != $msg_arr["$msg_id"][$i]->getID()) {
					$ah_begin='<a href="'.util_make_url ('/forum/message.php?msg_id='. $msg_arr["$msg_id"][$i]->getID() .'&group_id='.$group_id).'">';
					$ah_end='</a>';
				} else {
					$ah_begin='';
					$ah_end='';
				}

				$ret_val .= $ah_begin .
				html_image('ic/msg.png',"10","12",array("border"=>"0"));
				/*
					See if this message is new or not
					*/
				if ($this->Forum->getSavedDate() < $msg_arr["$msg_id"][$i]->getPostDate()) {
					$bold_begin='<strong>';
					$bold_end='</strong>';
				} else {
					$bold_begin='';
					$bold_end='';
				}

				$ret_val .= $bold_begin.$msg_arr["$msg_id"][$i]->getSubject() .$bold_end.$ah_end.'</td>'.
					'<td>'.util_make_link ('/users/'.$msg_arr["$msg_id"][$i]->getPosterName(),$msg_arr["$msg_id"][$i]->getPosterRealName()) .'</td>'.
				'<td>'.date(_('Y-m-d H:i'), $msg_arr["$msg_id"][$i]->getPostDate() ).'</td></tr>';

				if ($msg_arr["$msg_id"][$i]->hasFollowups() > 0) {
					/*
						Call yourself, incrementing the level
						*/
					$ret_val .= $this->showSubmessages($msg_arr,$msg_arr["$msg_id"][$i]->getID(),($level+1));
				}
			}
		}
		return $ret_val;
	}

	/**
	 *  showEditForm - Prints the form to edit a message
	 *
	 *	@param 		int		The Message
	 *	@return		The HTML output echoed
	 */

	function showEditForm(&$msg) {
		$thread_id = $msg->getThreadID();
		$msg_id = $msg->getID();
		$posted_by = $msg->getPosterID();
		$subject = $msg->getSubject();
		$body = $msg->getBody();
		$post_date = $msg->getPostDate();
		$is_followup_to = $msg->getParentID();
		$has_followups = $msg->hasFollowups();
		$most_recent_date = $msg->getMostRecentDate();
		$g =& $this->Forum->getGroup();
		$group_id = $g->getID();

		if ($this->Forum->userCanPost()) { // minor control, but anyways it should be an admin at this point
			echo notepad_func();
			?>
<div align="center">
	 <form "enctype="multipart/form-data" action="<? echo util_make_url ('/forum/admin/index.php') ?>"
	method="post"><?php $objid = $this->Forum->getID();?> <input
	type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" /> <input
	type="hidden" name="forum_id" value="<?php echo $objid; ?>" /> <input
	type="hidden" name="editmsg" value="<?php echo $msg_id; ?>" /> <input
	type="hidden" name="is_followup_to"
	value="<?php echo $is_followup_to; ?>" /> <input type="hidden"
	name="form_key" value="<?php echo form_generate_key();?>"> <input
	type="hidden" name="posted_by" value="<?php echo $posted_by;?>"> <input
	type="hidden" name="post_date" value="<?php echo $post_date;?>"> <input
	type="hidden" name="has_followups" value="<?php echo $has_followups;?>">
<input type="hidden" name="most_recent_date"
	value="<?php echo $most_recent_date;?>"> <input type="hidden"
	name="group_id" value="<?php echo $group_id;?>">
<fieldset class="fieldset">
<table>
	<tr>
		<td valign="top"></td>
		<td valign="top"><br>
		<strong><?php echo _('Subject:'); ?></strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="subject" value="<?php echo $subject; ?>"
			size="45" maxlength="45" /> <br>
		<br>
		<strong><?php echo _('Message:'); ?></strong><?php echo notepad_button('document.forms[1].body') ?><?php echo utils_requiredField(); ?><br />
		<?php
                $params = array() ;
		$params['body'] = $body;
		$params['width'] = "800";
		$params['height'] = "500";
		$params['group'] = $group_id;
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don�t have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="body"  rows="10" cols="50" wrap="soft">' . $body . '</textarea>';
		}
		unset($GLOBALS['editor_was_set_up']);
		?> <br>
		<br>

		<p><?php //$this->LinkAttachForm();?>
		
		
		<p><?php
		?> <br />
		
		
		<center><input type="submit" name="ok"
			value="<?php echo _('Update'); ?>" /> <input type="submit"
			name="cancel" value="<?php echo _('Cancel'); ?>" /></center>
		</p>
		</td>
	</tr>
</table>
</fieldset>
</form>
</div>
		<?php
}
}

function showPostForm($thread_id=0, $is_followup_to=0, $subject="") {
	global $group_id;

	$body = '';
	
	if ($this->Forum->userCanPost()) {
		if ($subject) {
			//if this is a followup, put a RE: before it if needed
			if (!eregi('RE:',$subject,$test)) {
				$subject ='RE: '.$subject;
			}
		}
		echo notepad_func();
		?>
<div align="center">
<form "enctype="multipart/form-data"
	action="<? echo url_make_link ('/forum/forum.php?forum_id='.$this->Forum->getID().'&group_id='.$group_id); ?>"
	method="post"><?php $objid = $this->Forum->getID();?> <input
	type="hidden" name="post_message" value="y" /> <input type="hidden"
	name="thread_id" value="<?php echo $thread_id; ?>" /> <input
	type="hidden" name="msg_id" value="<?php echo $is_followup_to; ?>" /> <input
	type="hidden" name="is_followup_to"
	value="<?php echo $is_followup_to; ?>" /> <input type="hidden"
	name="form_key" value="<?php echo form_generate_key();?>">
<fieldset class="fieldset">
<table>
	<tr>
		<td valign="top"></td>
		<td valign="top"><br>
		<strong><?php echo _('Subject:'); ?></strong><?php echo utils_requiredField(); ?><br />
		<input type="text" name="subject" value="<?php echo $subject; ?>"
			size="45" maxlength="45" /> <br>
		<br>
		<strong><?php echo _('Message:'); ?></strong><?php echo notepad_button('document.forms[1].body') ?><?php echo utils_requiredField(); ?><br />

		<?php
		$GLOBALS['editor_was_set_up']=false;
                $params = array();
		$params['body'] = $body;
		$params['width'] = "800";
		$params['height'] = "500";
		$params['group'] = $group_id;
		plugin_hook("text_editor",$params);
		if (!$GLOBALS['editor_was_set_up']) {
			//if we don�t have any plugin for text editor, display a simple textarea edit box
			echo '<textarea name="body"  rows="10" cols="50" wrap="soft">' . $body . '</textarea>';
		}
		unset($GLOBALS['editor_was_set_up']);
		?> <?php //$text_support->displayTextField('body'); ?> <br>
		<br>
		<!--		<span class="selected"><?php echo _('HTML tags will display in your post as text'); ?></span> -->
		<p><?php $this->LinkAttachForm();?>
		
		
		<p><?php
		if (!session_loggedin()) {
			echo '<span class="highlight">';
			printf (_('You are posting anonymously because you are not <a href="%1$s">logged in</a>'),util_make_url ('/account/login.php?return_to='. urlencode(getStringFromServer('REQUEST_URI')))) .'</span>';
		}
		?> <br />
		<input type="submit" name="submit"
			value="<?php echo _('Post Comment'); echo ((!session_loggedin())?' '._('Anonymously'):''); ?>" /><?php
			echo ((session_loggedin()) ? '&nbsp;&nbsp;&nbsp;<input type="checkbox" value="1" name="monitor" />&nbsp;'._('Receive followups via email').'.' : ''); ?>
		</p>
		</td>
	</tr>
</table>
</fieldset>
</form>
</div>
			<?php

} elseif ($this->Forum->allowAnonymous()) {
	echo '<span class="error">';
	printf(_('You could post if you were <a href="%1$s">logged in</a>'), util_make_url ('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI'))));
} elseif (!session_loggedin()) {
	echo '
			<span class="error">'.sprintf(_('Please <a href="%1$s">log in</a>'), util_make_url('/account/login.php?return_to='.urlencode($REQUEST_URI))).'</span><br/></p>';
} else {
	//do nothing
}

}

}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

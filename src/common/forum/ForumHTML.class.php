<?php
/**
 * Forums Facility
 *
 * Copyright 1999-2001, Tim Perdue - Sourceforge
 * Copyright 2002, Tim Perdue - GForge, LLC
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2010-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, French Ministry of National Education
 * Copyright 2013-2016, Franck Villaume - TrivialDev
 * Copyright 2021, Guy Morin - French Ministry of Finances, DGFiP
 * http://fusionforge.org
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

require_once $gfwww.'include/note.php';
require_once $gfwww.'include/trove.php';
require_once $gfwww.'news/news_utils.php';
require_once $gfcommon.'forum/ForumAdmin.class.php';
require_once $gfcommon.'forum/AttachManager.class.php';

function forum_header($params = array()) {
	global $HTML, $group_id, $forum_id, $f, $group_forum_id;

	if ($group_forum_id) {
		$forum_id = $group_forum_id;
	}
	if (!forge_get_config('use_forum')) {
		exit_disabled();
	}

	$params['group'] = $group_id;
	$params['toptab'] = 'forums';

	if ($forum_id) {
		// Check if this is a news item, to display it at the top of the page
		$result = db_query_params('SELECT submitted_by, post_date, group_id, forum_id, summary, details FROM news_bytes WHERE forum_id=$1',
					   array($forum_id));

		if (db_numrows($result) == 1) {

			// checks which group the news item belongs to
			$params['group'] = db_result($result, 0, 'group_id');
			$params['toptab'] = 'news';
			$params['title'] = _('Forum')._(': ').db_result($result,0,'summary');
			$HTML->header($params);

			echo '<table><tr><td class="top">';
			$user = user_get_object(db_result($result,0,'submitted_by'));
			$group = group_get_object($params['group']);
			if (!$group || !is_object($group) || $group->isError()) {
				exit_no_group();
			}
			echo '<p>
				<strong>'._('Posted by')._(': ').'</strong> '.$user->getRealName().'<br />
				<strong>'._('Date')._(': ').'</strong> '. relative_date(db_result($result,0,'post_date')).'<br />
				<strong>'._('Summary')._(': ').'</strong>'.
				util_make_link('/forum/forum.php?forum_id='.db_result($result, 0, 'forum_id').'&group_id='.$group_id,
						db_result($result, 0, 'summary')).'<br/>
				<strong>'._('Project')._(': ').'</strong>'.
				util_make_link_g($group->getUnixName(),db_result($result, 0, 'group_id'),$group->getPublicName()).'<br />
				</p>
				';

			// display classification
			if ($params['group'] == GROUP_IS_NEWS) {
				print stripslashes(trove_getcatlisting(db_result($result,0,'forum_id'),0,1));
			} elseif (forge_get_config('use_trove')) {
				print stripslashes(trove_getcatlisting($params['group'],0,1));
			}

			echo '<p><strong>'._('Content')._(':').'</strong></p>';
			$body = db_result($result,0,'details');
			$body = TextSanitizer::purify($body);
			if (!strstr($body,'<')) {
				//backwards compatibility for non html messages
				echo util_make_links(nl2br($body));
			} else {
				echo util_make_links($body);
			}
			echo '</td><td class="top" style="width:35%">';
			echo $HTML->boxTop(_('Latest News'));
			echo news_show_latest($params['group'],5,false);
			echo $HTML->boxBottom();
			echo '</td></tr></table>';
		} else {
			$HTML->header($params);
		}
	} else {
		$menu_text = array();
		$menu_links = array();

		$menu_text[] = _('View Forums');
		$menu_links[] = '/forum/?group_id='.$group_id;

		if ($f){
			if ($forum_id) {
				$menu_text[]=_('Discussion Forums:') .' '. $f->getName();
				$menu_links[]='/forum/forum.php?forum_id='.$forum_id;
			}
			if (forge_check_perm ('forum_admin', $f->Group->getID())) {
				$menu_text[]=_('Administration');
				$menu_links[]='/forum/admin/?group_id='.$group_id;
			}
		} else {
			if (forge_check_perm ('forum_admin', $group_id)) {
				$menu_text[]=_('Administration');
				$menu_links[]='/forum/admin/?group_id='.$group_id;
			}
		}

		$params['submenu'] =$HTML->subMenu($menu_text,$menu_links);
		site_project_header($params);
	}

	$pluginManager = plugin_manager_get_object();
	if ($f && $pluginManager->PluginIsInstalled('blocks') && plugin_hook("blocks", "forum_".$f->getName())) {
		echo '<br />';
	}
	if (session_loggedin()) {
		if ($f) {
			if ($f->isMonitoring()) {
				echo util_make_link('/forum/monitor.php?forum_id='.$forum_id.'&group_id='.$group_id.'&stop=1',
								 html_image('ic/xmail16w.png').' '._('Stop monitoring')).' | ';
			} else {
				echo util_make_link('/forum/monitor.php?forum_id='.$forum_id.'&group_id='.$group_id.'&start=1',
							 html_image('ic/mail16w.png').' '._('Monitor Forum')).' | ';
			}
			echo util_make_link('/forum/save.php?forum_id='.$forum_id.'&group_id='.$group_id,
						 html_image('ic/save.png') .' '._('Save Place')).' | ';
			// Link to pin or un pin a thread
			$thread_id = getIntFromRequest("thread_id");
			if (forge_check_perm('forum_admin', $f->Group->getID()) && ($thread_id)) {
			    if (getStringFromRequest('pin') !== 't') {
			        if(ForumHTML::getIsPinned($thread_id)) {
			            $pin = 'f';
			            $pin_icon = 'forum_unpin';
			            $pin_text = _('Unpin this thread');
			        } else {
			            $pin = 't';
			            $pin_icon = 'forum_pin';
			            $pin_text = _('Pin this thread');
			        }
			        echo util_make_link('/forum/forum.php?thread_id='.$thread_id
			            .'&forum_id='.$forum_id
			            .'&group_id='.$group_id
			            .'&pin='.$pin, html_image('ic/'.$pin_icon.'.png').' '.$pin_text).' | ';
			    }
			}
		}
	} elseif ($f) {
		echo util_make_link('/forum/monitor.php?forum_id='.$forum_id.'&group_id='.$group_id.'&start=1', html_image('ic/mail16w.png').' '._('Monitor Forum')).' | ';
	}

	if ($f && $forum_id && forge_check_perm ('forum', $forum_id, 'post')) {
		echo util_make_link ('/forum/new.php?forum_id='.$forum_id.'&group_id='.$group_id,
					 html_image('ic/write16w.png', 20, 20, array('alt'=>_('Start New Thread'))) .' '.
					 _('Start New Thread'));
	}
}

function forum_footer($params = array()) {
	site_project_footer($params);
}

/**
 *
 * Wrap many forum functions in this class
 *
 */
class ForumHTML extends FFError {
	/**
	 * The Forum object.
	 *
	 * @var  object  $Forum
	 */
	var $Forum;

	function __construct(&$Forum) {
		parent::__construct();
		if (!$Forum || !is_object($Forum)) {
			$this->setError(_('Invalid Forum Object'));
			return false;
		}
		if ($Forum->isError()) {
			$this->setError('ForumMessage: '.$Forum->getErrorMessage());
			return false;
		}
		$this->Forum =& $Forum;
		return true;
	}

	/**
	 * showPendingMessage - get the HTML code of a pending message
	 *
	 * @param	object	$msg	The message.
	 * @return	string	return the html output
	 */
	function showPendingMessage(&$msg) {
		global $HTML,$group_id;

		$am = new AttachManager();
		$ret_val = $am->PrintHelperFunctions();
		html_feedback_top(_('This is the content of the pending message'));
		$bold_begin='<strong>';
		$bold_end='</strong>';
		$ret_val .= '
		<table>
			<tr>
				<td class="tablecontent" style="white-space: nowrap;">'._('By')._(': ').
					$msg->getPosterRealName().
					'<br />
					';
		$msgforum =& $msg->getForum();
		$ret_val .= $am->PrintAttachLink($msg,$group_id,$msgforum->getID()) . '
					<br />
					'.
		$HTML->getMessagePic() .
		$bold_begin. $msg->getSubject() . $bold_end .
		'<br />'. date(_('Y-m-d H:i'),$msg->getPostDate()) .'
				</td>
			</tr>
			<tr>
				<td>
					'.  util_gen_cross_ref($msg->getBody(), $group_id) .'
				</td>
			</tr>
		</table>';
		return $ret_val;

	}

	function showNestedMessage(&$msg) {
		/*

		accepts a database result handle to display a single message
		in the format appropriate for the nested messages

		*/
		global $HTML,$group_id;
		/*
			See if this message is new or not
			If so, highlight it in bold
		*/
		if ($this->Forum->getSavedDate() < $msg->getPostDate()) {
			$bold_begin='<strong>';
			$bold_end='</strong>';
		} else {
			$bold_begin = '';
			$bold_end = '';
		}
		$am = new AttachManager();
		$msgforum =& $msg->getForum();
		$fa = new ForumAdmin($msgforum->Group->getID());
		$url = util_make_uri('/forum/message.php?msg_id='. $msg->getID() .'&amp;group_id='.$group_id);
		$ret_val = $HTML->listTableTop().'
			<tr>
				<td class="tablecontent top" style="white-space: nowrap;">';

		$params = array('user_id' => $msg->getPosterID(), 'size' => 's', 'content' => '');
		plugin_hook_by_reference("user_logo", $params);
		if ($params['content']) {
			$ret_val .= $params['content'];
		}

		$ret_val .= $bold_begin. $msg->getSubject(). ' <a href="'.$url.'">[ '._("Reply").' ]</a>'. $bold_end;
		$ret_val .= '<br/>'._('By')._(': ').util_make_link_u ($msg->getPosterName(),$msg->getPosterID(),$msg->getPosterRealName());
		$ret_val .= ' on '.date('Y-m-d H:i',$msg->getPostDate());
		$ret_val .= '</td><td class="tablecontent align-right">';
		$ret_val .= '<a href="'.$url.'">[forum:'.$msg->getID().']</a><br/>';
		if (forge_check_perm('forum_admin', $msgforum->Group->getID())) {
			$ret_val .= $fa->PrintAdminMessageOptions($msg->getID(),$group_id,$msg->getThreadID(),$msgforum->getID());
		}
		$ret_val .= $am->PrintAttachLink($msg,$group_id,$msgforum->getID());
		$ret_val .= '
				</td>
			</tr>
			<tr>
				<td colspan="2">
					';
					if (strpos($msg->getBody(),'<') === false) {
						$ret_val .= nl2br(util_gen_cross_ref($msg->getBody(), $group_id)); //backwards compatibility for non html messages
					} else {
						$ret_val .= util_gen_cross_ref($msg->getBody(), $group_id);
					}
					$ret_val .= '
				</td>
			</tr>'.$HTML->listTableBottom();
		return $ret_val;
	}

	/**
	 * LinkAttachEditForm - Returns the link to the attach form for editing
	 *
	 * @param	string	$filename	Filename
	 * @param	int	$group_id	group id
	 * @param	int	$forum_id	forum id
	 * @param	int	$attachid	attach id
	 * @param	int	$msg_id		msg id
	 * @return	string The HTML output
	 */
	function LinkAttachEditForm($filename,$group_id,$forum_id,$attachid,$msg_id) {
		global $HTML;
		$return_val = $HTML->openForm(array('method' => 'post', 'enctype' => 'multipart/form-data', 'action' => '/forum/attachment.php?attach_id='.$attachid.'group='.$group_id.'&forum_id='.$forum_id.'&msg_id='.$msg_id));
		$return_val .='
			<table>
			<tr>
				<td>' . _('Current File') . ": <span class=\"selected\">" . $filename . '</span></td>
			</tr>
			</table>

			<fieldset class=\"fieldset\">
			<table>

					<tr>
						<td>' . _('Use the “Browse” button to find the file you want to attach') . '</td>
					</tr>
					<tr>
						<td>' . _('File to upload') . ':   <input type="file" name="attachment1"/></td>
					</tr>
					<tr>
						<td>'.$HTML->warning_msg(_('Warning: Uploaded file will replace current file')).'</td>
					</tr>
				</table>
			<input type="submit" name="go" value="'._('Update').'" />
			<input type="hidden" name="doedit" value="1" />
			<input type="hidden" name="edit" value="yes" />
			<input type="hidden" name="forum_id" value="'.$forum_id.'" />
			<input type="hidden" name="group_id" value="'.$group_id.'" />
			<input type="hidden" name="attachid" value="'.$attachid.'" />
			<input type="hidden" name="msg_id" value="'.$msg_id.'" />
			</fieldset>';
		$return_val .= $HTML->closeForm();
		return $return_val;
	}

	/**
	 * LinkAttachForm - echoes the link to the attach form
	 */
	function LinkAttachForm() {
		echo '
		<fieldset class="fieldset">
		<legend>' . _('Attachments') . "</legend>
		<table>
				<tr>
					<td>" . _('Use the “Browse” button to find the file you want to attach') . "</td>
				</tr>
				<tr>
					<td>" . _('File to upload') . ":   <input type=\"file\" name=\"attachment1\"/></td>
				</tr>
		</table>

		</fieldset>";
	}

	/**
	 * @param	string	$msg_arr
	 * @param	string	$msg_id
	 * @return	string
	 */
	function showNestedMessages(&$msg_arr, $msg_id) {
		global $total_rows;

		$rows = count($msg_arr["$msg_id"]);
		$ret_val = '';

		if ($msg_arr["$msg_id"] && $rows > 0) {
			$ret_val .= '
			<ul><li style="list-style: none">';

			/*
			iterate and show the messages in this result
			for each message, recurse to show any submessages
			*/
			for ($i=($rows-1); $i >= 0; $i--) {
				//	  increment the global total count
				$total_rows++;

				//	  show the actual nested message
				$ret_val .= $this->showNestedMessage ($msg_arr["$msg_id"][$i]).'<br />';

				if ($msg_arr["$msg_id"][$i]->hasFollowups()) {
					//	  Call yourself if there are followups
					$ret_val .= $this->showNestedMessages ( $msg_arr,$msg_arr["$msg_id"][$i]->getID() );
				}
			}
			$ret_val .= '
			</li></ul>';
		}
		return $ret_val;
	}

	/**
	 * @param	$msg_arr
	 * @param	$msg_id
	 * @param	$level
	 * @return	string
	 */
	function showSubmessages(&$msg_arr, $msg_id, $level) {
		/*
			Recursive. Selects this message's id in this thread,
			then checks if any messages are nested underneath it.
			If there are, it calls itself, incrementing $level
			$level is used for indentation of the threads.
		*/
		global $total_rows,$current_message,$group_id, $HTML;

		if (!isset($msg_arr["$msg_id"])) {
			return "";
		}
		$rows=count($msg_arr["$msg_id"]);
		$ret_val = "";
		if ($rows > 0) {
			for ($i=($rows-1); $i >= 0; $i--) {
				/*
					Is this row's background shaded or not?
				*/
				$total_rows++;

				$ret_val .= '
					<tr><td style="white-space: nowrap;">';
				/*
					How far should it indent?
				*/
				for ($i2=0; $i2<$level; $i2++) {
					$ret_val .= ' &nbsp; &nbsp; &nbsp; ';
				}

				/*
					If it this is the message being displayed, don't show a link to it
				*/
				if ($current_message != $msg_arr[$msg_id][$i]->getID()) {
					$ah_begin='<a href="'.util_make_uri('/forum/message.php?msg_id='. $msg_arr[$msg_id][$i]->getID() .'&amp;group_id='.$group_id).'">';
					$ah_end='</a>';
				} else {
					$ah_begin='';
					$ah_end='';
				}

				$ret_val .= $ah_begin .
					html_image('ic/msg.png').' ';
				/*
					See if this message is new or not
				*/
				if ($this->Forum->getSavedDate() < $msg_arr[$msg_id][$i]->getPostDate()) {
					$bold_begin='<strong>';
					$bold_end='</strong>';
				} else {
					$bold_begin='';
					$bold_end='';
				}

				$ret_val .= $bold_begin.$msg_arr[$msg_id][$i]->getSubject() .$bold_end.$ah_end.'</td>'.
					'<td>'.util_display_user($msg_arr[$msg_id][$i]->getPosterName(),$msg_arr[$msg_id][$i]->getPosterID(),$msg_arr[$msg_id][$i]->getPosterRealName()) .'</td>'.
					'<td>'.relative_date($msg_arr[$msg_id][$i]->getPostDate()).'</td></tr>';

				if ($msg_arr[$msg_id][$i]->hasFollowups() > 0) {
					/*
						Call yourself, incrementing the level
					*/
					$ret_val .= $this->showSubmessages($msg_arr,$msg_arr[$msg_id][$i]->getID(),($level+1));
				}
			}
		}
		return $ret_val;
	}

	/**
	 * showEditForm - Prints the form to edit a message
	 *
	 * @param	int	$msg The Message
	 * @return	The HTML output echoed
	 */
	function showEditForm(&$msg) {
		global $HTML;
		$thread_id = $msg->getThreadID();
		$msg_id = $msg->getID();
		$posted_by = $msg->getPosterID();
		$subject = $msg->getSubject();
		$body = $msg->getBody();
		$post_date = $msg->getPostDate();
		$is_followup_to = $msg->getParentID();
		$has_followups = $msg->hasFollowups();
		$most_recent_date = $msg->getMostRecentDate();
		$g = $this->Forum->getGroup();
		$group_id = $g->getID();

		if (forge_check_perm ('forum', $this->Forum->getID(), 'post')) { // minor control, but anyways it should be an admin at this point
			echo notepad_func();
			?>
			<div style="margin-left: auto; margin-right: auto;">
			<?php echo $HTML->openForm(array('id' => 'ForumEditForm', 'enctype' => 'multipart/form-data', 'action' => '/forum/admin/index.php', 'method' => 'post'));
			$objid = $this->Forum->getID(); ?>
			<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
			<input type="hidden" name="forum_id" value="<?php echo $objid; ?>" />
			<input type="hidden" name="editmsg" value="<?php echo $msg_id; ?>" />
			<input type="hidden" name="is_followup_to" value="<?php echo $is_followup_to; ?>" />
			<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>" />
			<input type="hidden" name="posted_by" value="<?php echo $posted_by;?>" />
			<input type="hidden" name="post_date" value="<?php echo $post_date;?>" />
			<input type="hidden" name="has_followups" value="<?php echo $has_followups;?>" />
			<input type="hidden" name="most_recent_date" value="<?php echo $most_recent_date;?>" />
			<input type="hidden" name="group_id" value="<?php echo $group_id;?>" />
			<fieldset class="fieldset">
			<legend><?php echo _('Edit Message'); ?></legend>
			<table><tr><td class="top">
			</td><td class="top">
			<p>
			<label for="subject"><strong><?php echo _('Subject').utils_requiredField()._(':'); ?></strong></label><br />
				<input id="subject" type="text" autofocus="autofocus" required="required" name="subject" value="<?php echo $subject; ?>" size="80" maxlength="80" />
			</p>
			<p>
			<label for="body"><strong><?php echo _('Message').utils_requiredField()._(': '); ?></strong></label>
			<?php echo notepad_button('document.forms.ForumEditForm.body'); ?>
			</p>
			<?php
			$GLOBALS['editor_was_set_up']=false;
			$params = array() ;
			$params['body'] = $body;
			$params['width'] = "800";
			$params['height'] = "500";
			$params['group'] = $group_id;
			plugin_hook("text_editor",$params);
			if (!$GLOBALS['editor_was_set_up']) {
				//if we don't have any plugin for text editor, display a simple textarea edit box
				echo '<textarea id="body" required="required" name="body" rows="10" cols="70">' . $body . '</textarea>';
			}
			unset($GLOBALS['editor_was_set_up']);
				?>

				<p style="text-align: center">
				<input type="submit" name="ok" value="<?php echo _('Update'); ?>" />
				<input type="submit" name="cancel" formnovalidate="formnovalidate" value="<?php echo _('Cancel'); ?>" />
				</p>
			</td></tr></table></fieldset>
			<?php echo $HTML->closeForm(); ?>
			</div>
			<?php
		}
	}

	/**
	 * getIsPinned - to get is_pinned value from a thread
	 *
	 * @param int $thread_id - thread id
	 *
	 * @return boolean is_pinned - thread is pinned or not
	 */
	public static function getIsPinned($thread_id) {
	    $is_pinned = false;
	    $result = db_query_params('SELECT is_pinned FROM forum WHERE thread_id=$1 AND is_pinned = \'t\'', array($thread_id));
	    if(db_numrows($result) > 0) {
	        $is_pinned = true;
	    }
	    return $is_pinned;
	}

	/**
	 * @param int $thread_id
	 * @param int $is_followup_to
	 * @param string $subject
	 */
	function showPostForm($thread_id=0, $is_followup_to=0, $subject="") {
		global $group_id, $HTML;

		$body = '';

		$rl = RoleLoggedIn::getInstance() ;
		if (forge_check_perm ('forum', $this->Forum->getID(), 'post')) {
			//if this is a followup, put a RE: before it if needed
			if ($subject && !preg_match('/RE:/i',$subject,$test)) {
				$subject ='RE: '.$subject;
			}
			echo notepad_func();
			?>
			<div class="align-center">
			<?php echo $HTML->openForm(array('id' => 'ForumPostForm', 'enctype' => 'multipart/form-data', 'action' => '/forum/forum.php?forum_id='.$this->Forum->getID().'&group_id='.$group_id, 'method' => 'post')); ?>
			<input type="hidden" name="post_message" value="y" />
			<input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>" />
			<input type="hidden" name="msg_id" value="<?php echo $is_followup_to; ?>" />
			<input type="hidden" name="is_followup_to" value="<?php echo $is_followup_to; ?>" />
			<input type="hidden" name="form_key" value="<?php echo form_generate_key();?>" />
			<fieldset class="fieldset"><table><tr>
			<td class="top">
			</td>
			<td class="top">
			<label for="subject"><strong><?php echo _('Subject').utils_requiredField()._(': '); ?></strong></label><br />
				<input id="subject" type="text" autofocus="autofocus" required="required" name="subject" value="<?php echo $subject; ?>" size="80" maxlength="80" />
			<br />
		<strong><?php echo _('Message').utils_requiredField()._(': '); ?></strong>
		<?php echo notepad_button('document.forms.ForumPostForm.body') ?><br />

			<?php
				$GLOBALS['editor_was_set_up']=false;
				$params = array();
				$params['body'] = $body;
				$params['width'] = "800";
				$params['height'] = "500";
				$params['group'] = $group_id;
				plugin_hook("text_editor",$params);
				if (!$GLOBALS['editor_was_set_up']) {
					//if we don't have any plugin for text editor, display a simple textarea edit box
					echo '<textarea required="required" name="body"  rows="10" cols="70">' . $body . '</textarea>';
				}
				unset($GLOBALS['editor_was_set_up']);
			?>
		<br />
		<br />
				<?php $this->LinkAttachForm();?>

		<p><?php
		if (!session_loggedin()) {
			echo '<span class="highlight">';
			printf (_('You are posting anonymously because you are not <a href="%s">logged in</a>'),util_make_url ('/account/login.php?return_to='. urlencode(getStringFromServer('REQUEST_URI'))));
			echo '</span>';
		}
		?> <br />
		<input type="submit" name="submit"
			value="<?php echo _('Post Comment'); echo (!session_loggedin()) ? ' '._('Anonymously') : ''; ?>" />
			<?php if (session_loggedin()) {
				echo '&nbsp;&nbsp;&nbsp;<input type="checkbox" value="1" name="monitor" />&nbsp;'._('Receive comments via email').'.';
			} ?>
		</p>
		</td>
	</tr>
</table>
</fieldset>
<?php echo $HTML->closeForm(); ?>
</div>
			<?php

		} elseif ($rl->hasPermission('forum', $this->Forum->getID(), 'post')) {
			echo $HTML->error_msg(_('You could post if you were <a href="%s">logged in</a>.'), util_make_uri('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI'))));
		} elseif (!session_loggedin()) {
			echo $HTML->error_msg(_('Please <a href="%s">log in</a>'), util_make_uri('/account/login.php?return_to='.urlencode(getStringFromServer('REQUEST_URI'))));
		} else {
			//do nothing
		}
	}
}
// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

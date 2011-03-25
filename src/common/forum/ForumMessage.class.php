<?php
/**
 * FusionForge forums
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
include_once $gfcommon.'include/TextSanitizer.class.php'; // for parsing the mail body
include_once $gfcommon.'include/User.class.php';

class ForumMessage extends Error {

	var $awaits_moderation;//boolean -> true if the message was inserted for approval (pending), false if not
	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $data_array.
	 */
	var $data_array;

	/**
	 * The Forum object.
	 *
	 * @var	 object  $Forum.
	 */
	var $Forum;
	
		/**
	 *  Constructor.
	 *
	 *	@param	object	The Forum object to which this ForumMessage is associated.
	 *  @param  int	 The message_id.
	 *  @param  array   The associative array of data.
	 *	@param 	boolean	Whether the message is a pending one.
	 *	@return	boolean	success.
	 */
	function ForumMessage(&$Forum, $msg_id=false, $arr=false, $pending=false) {
		$this->Error();
		if (!$Forum || !is_object($Forum)) {
			$this->setError(_('ForumMessage:: No Valid Forum Object'));
			return false;
		}
		if ($Forum->isError()) {
			$this->setError('ForumMessage:: '.$Forum->getErrorMessage());
			return false;
		}
		$this->Forum =& $Forum;

		if ($msg_id) {
			if ($pending) {
				//we are going to create the pending message to show it to the admin for moderation
				if (!$this->fetchModeratedData($msg_id)) {
					return false;
				}
				$this->awaits_moderation = true;
			} else {
				$this->awaits_moderation = false;
				if (!$arr || !is_array($arr)) {
					if (!$this->fetchData($msg_id)) {
						return false;
					}
				} else {
					$this->data_array =& $arr;
					//
					//	Verify this message truly belongs to this Forum
					//
					if ($this->data_array['group_forum_id'] != $this->Forum->getID()) {
						$this->setError(_('Group_forum_id in db result does not match Forum Object'));
						$this->data_array=null;
						return false;
					}
				}
			}
		}
		return true;
	}

/**
	*	insertmoderated - inserts the message into the table for moderation (forum_pending_messages)
	 *	@param	string	The subject of the message.
	 *	@param	string	The body of the message.
	 *	@param	int	The thread_id of the message, if known.
	 *	@param	int	The message_id of the parent message, if any.
	 *	@param 	int	The id of the user that is posting the message
	 *	@return	boolean success.
	*/
	
	function insertmoderated($subject, $body, $thread_id='', $is_followup_to='',$user_id) {
		if (!$thread_id) {
			$thread_id=$this->Forum->getNextThreadID();
			$is_followup_to=0;
			if (!$thread_id) {
				$this->setError('ForumMessage::create() '._('Getting next thread_id failed'));
				db_rollback();
				return false;
			}
		}
		
		$result = db_query_params ('INSERT INTO forum_pending_messages (group_forum_id,posted_by,subject,
		body,post_date,is_followup_to,thread_id,most_recent_date) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					   array ($this->Forum->getID(),
						  $user_id,
						  htmlspecialchars($subject),
						  $body,
						  time(),
						  $is_followup_to,
						  $thread_id,
						  time ())) ;
		if (!$result || db_affected_rows($result) < 1) {
			$this->setError(_('ForumMessage::create() Posting Failed').' '.db_error());
			db_rollback();
			return false;
		} else {
			$msg_id=db_insertid($result,'forum_pending_messages','msg_id');
			if (!$this->fetchModeratedData($msg_id)) {
				db_rollback();
				return false;
			}
			if (!$msg_id) {
				db_rollback();
				$this->setError(_('ForumMessage::create() Unable to get new message id'));
				return false;
			} else {
				if (!$this->sendNewModeratedMsgNotice()) {
					db_rollback();
					return false;
				}
				db_commit();
				$this->awaits_moderation = true;
				return true;
			}
		}
	}
	

	/**
	*	insertreleasedmsg - inserts the released message into the main table (forum)
	 *	@param 	string	The Forum id
	 *	@param	string	The subject of the message.
	 *	@param	string	The body of the message.
	 *	@param	string	The post date
	 *	@param	int	The thread_id of the message
	 *	@param	int	The message_id of the parent message, if any.
	 *	@param 	int	The id of the user that is posting the message
	 *	@param 	int	has followups?
	 *	@param 	int	most recent date
	 *	@return	boolean success.
	*/
	
	function insertreleasedmsg($group_forum_id,$subject, $body,$post_date, $thread_id, $is_followup_to,$posted_by,$has_followups,$most_recent_date) {
		if ($is_followup_to != 0) {
			
			//was posted in reply to another thread
			//we must check whether that thread still exists. if it does, post the message. else, set the error
				//
			//  increment the parent's followup count if necessary
			//
			db_begin();
			$res4 = db_query_params ('UPDATE forum SET most_recent_date=$1 WHERE thread_id=$2 AND is_followup_to=0',
						 array (time(),
							$thread_id)) ;
			if (!$res4 || db_affected_rows($res4) < 1) {
				$this->setError(_('Couldn\'t Update Master Thread parent with current time'));
				db_rollback();
				return false;
			} else {
				//
				//  mark the parent with followups as an optimization later
				//
				$res3 = db_query_params ('UPDATE forum SET has_followups=1,most_recent_date=$1 WHERE msg_id=$2',
							 array (time(),
								$is_followup_to)) ;
				if (!$res3) {
					$this->setError(_('Could Not Update Parent'));
					db_rollback();
					return false;
				}
			}
			db_commit();
		}
			
			
		db_begin();
		$result = db_query_params ('INSERT INTO forum (group_forum_id,posted_by,subject,body,post_date,is_followup_to,thread_id,most_recent_date) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					   array ($group_forum_id,
						  $posted_by,
						  htmlspecialchars($subject),
						  $body,
						  $post_date,
						  $is_followup_to,
						  $thread_id,
						  $most_recent_date)) ;

		if (!$result || db_affected_rows($result) < 1) {
			$this->setError(_('ForumMessage::create() Posting Failed').' '.db_error());
			db_rollback();
			return false;
		} else {
			$msg_id=db_insertid($result,'forum','msg_id');
			if (!$this->fetchData($msg_id)) {
				$this->setError(_('ForumMessage::create() Posting Failed').' '.db_error());
				db_rollback();
				return false;
			}
			if (!$msg_id) {
				$this->setError(_('ForumMessage::create() Unable to get new message id'));
				db_rollback();
				return false;
			} else {
				if (!$this->sendNotice()) {
					db_rollback();
					return false;
				}
				db_commit();
				$this->awaits_moderation = false;
				return true;
			}
		}
	}
	
	
	
	/**
	*	insertmsg - inserts the message into the main table (forum)
	 *	@param	string	The subject of the message.
	 *	@param	string	The body of the message.
	 *	@param	int	The thread_id of the message, if known.
	 *	@param	int	The message_id of the parent message, if any.
	 *	@param 	int	The id of the user that is posting the message
	 *	@param  boolean	Whether the message has an attach associated. Defaults to false
	 *	@return	boolean success.
	*/
	
	function insertmsg($subject, $body, $thread_id='', $is_followup_to='',$user_id,$has_attach=false) {
		if (!$thread_id) {
			$thread_id=$this->Forum->getNextThreadID();
			$is_followup_to=0;
			if (!$thread_id) {
				$this->setError('ForumMessage::create() '._('Getting next thread_id failed'));
				db_rollback();
				return false;
			}
		} else {
			//
			//  increment the parent's followup count if necessary
			//
			$res4 = db_query_params ('UPDATE forum SET most_recent_date=$1 WHERE thread_id=$2 AND is_followup_to=0',
						 array (time(),
							$thread_id)) ;
			if (!$res4 || db_affected_rows($res4) < 1) {
				$this->setError(_('Couldn\'t Update Master Thread parent with current time'));
				db_rollback();
				return false;
			} else {
				//
				//  mark the parent with followups as an optimization later
				//
				$res3 = db_query_params ('UPDATE forum SET has_followups=1,most_recent_date=$1 WHERE msg_id=$2',
							 array (time(),
								$is_followup_to)) ;
				if (!$res3) {
					$this->setError(_('Could Not Update Parent'));
					db_rollback();
					return false;
				}
			}
		}
		
		$result = db_query_params ('INSERT INTO forum (group_forum_id,posted_by,subject,body,post_date,is_followup_to,thread_id,most_recent_date) VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					   array ($this->Forum->getID(),
						  $user_id,
						  htmlspecialchars($subject),
						  $body,
						  time(),
						  $is_followup_to,
						  $thread_id,
						  time())) ;
		if (!$result || db_affected_rows($result) < 1) {
			$this->setError(_('ForumMessage::create() Posting Failed').' '.db_error());
			db_rollback();
			return false;
		}

		$msg_id=db_insertid($result,'forum','msg_id');
		if (!$this->fetchData($msg_id)) {
			db_rollback();
			return false;
		}

		if (!$msg_id) {
			$this->setError(_('ForumMessage::create() Unable to get new message id'));
			db_rollback();
			return false;
		}

		if (!$this->sendNotice($has_attach)) {
			db_rollback();
			return false;
		}
//echo "Committing";
		db_commit();
//echo "db_error()".db_error();
		$this->awaits_moderation = false;
		return true;
	}
	
	
	/**
	 *	create - use this function to create a new message in the database.
	 *
	 *	@param	string	The subject of the message.
	 *	@param	string	The body of the message.
	 *	@param	int	The thread_id of the message, if known.
	 *	@param	int	The message_id of the parent message, if any.
	 *	@param  boolean	Whether the message has an attach associated. Defaults to false
	 *	@return	boolean success.
	 */
	function create($subject, $body, $thread_id='', $is_followup_to='',$has_attach=false) {
		if (!$body || !$subject) {
			$this->setError(_('Must Include A Message Body And Subject'));
			return false;
		}
		if (!forge_check_perm ('forum', $this->Forum->getID(), 'post')) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!session_loggedin()) {
			$user_id=100;
		} else {
			$user_id=user_getid();
		}

		if ($is_followup_to) {
			$ParentMessage=new ForumMessage($this->Forum,$is_followup_to);
			if (!$ParentMessage || !is_object($ParentMessage)) {
				$this->setError("ForumMessage::create()"._('ForumMessage::create() No Valid ParentMessage Object'));
				return false;
			}
			if ($ParentMessage->isError()) {
				$this->setError('ForumMessage::create() '.$ParentMessage->getErrorMessage());
				return false;
			}
		}
		if (!$is_followup_to) { 
			$is_followup_to=0; 
		}

		db_begin();
		
		//now we check the moderation status of the forum and act accordingly
		if (forge_check_perm ('forum', $this->Forum->getID(), 'unmoderated_post')) {
			//no moderation
			return $this->insertmsg($subject, $body, $thread_id, $is_followup_to,$user_id,$has_attach);
		} else {
			return $this->insertmoderated($subject, $body, $thread_id, $is_followup_to,$user_id);
		}
	}

	/**
	 *  fetchData - re-fetch the data for this forum_message from the database.
	 *
	 *  @param  int	 The message ID.
	 *  @return boolean	success.
	 */
	function fetchData($msg_id) {
		$res = db_query_params ('SELECT * FROM forum_user_vw WHERE msg_id=$1 AND group_forum_id=$2',
					array ($msg_id,
					       $this->Forum->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('ForumMessage::fetchData() Invalid MessageID').db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}
	
	/**
	 *  fetchModeratedData - re-fetch the data for this forum_message from the database, for pending messages
	 *
	 *  @param  int	 The message ID.
	 *  @return boolean	success.
	 */
	function fetchModeratedData($msg_id) {
		$res = db_query_params ('SELECT * FROM forum_pending_user_vw WHERE msg_id=$1 AND group_forum_id=$2',
					array ($msg_id,
					       $this->Forum->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('ForumMessage::fetchData() Invalid MessageID').db_error());
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getForum - get the Forum object this ForumMessage is associated with.
	 *
	 *	@return	object	The Forum object.
	 */
	function &getForum() {
		return $this->Forum;
	}

	/**
	 *	getID - get this message_id.
	 *
	 *	@return	int	The message_id.
	 */
	function getID() {
		return $this->data_array['msg_id'];
	}

	/**
	 *	getPosterName - get the unix user_name of this message's poster.
	 *
	 *	@return	string	The poster's unix name.
	 */
	function getPosterName() {
		return $this->data_array['user_name'];
	}

	/**
	 *	getPosterID - get this user_id of this message's poster.
	 *
	 *	@return	int	The user_id.
	 */
	function getPosterID() {
		return $this->data_array['posted_by'];
	}

	/**
	 *	getPosterRealName - get the real name of this message's poster.
	 *
	 *	@return	string	The real name.
	 */
	function getPosterRealName() {
		return $this->data_array['realname'];
	}

	/**
	 *	getSubject - get the subject of this message.
	 *
	 *	@return	string	The subject.
	 */
	function getSubject() {
		return $this->data_array['subject'];
	}

	/**
	 *	getBody - get the body of this message.
	 *
	 *	@return	String	The body.
	 */
	function getBody() {
		return $this->data_array['body'];
	}

	/**
	 *	getPostDate - get the post date of this message.
	 *
	 *	@return	int	The post date.
	 */
	function getPostDate() {
		return $this->data_array['post_date'];
	}

	/**
	 *	getParentID - get the id of the parent message, if this is a followup.
	 *
	 *	@return	int	The parent id.
	 */
	function getParentID() {
		return $this->data_array['is_followup_to'];
	}
	
	/**
	 *	isPending - is the message pending, awaiting moderation?
	 *
	 *	@return	int	awaits_moderation
	 */
	function isPending() {
		return $this->awaits_moderation;
	}

	/**
	 *	getThreadID - get the thread_id of the message.
	 *
	 *	@return	int	The thread_id.
	 */
	function getThreadID() {
		return $this->data_array['thread_id'];
	}

	/**
	 *	getMostRecentDate - get the date of the most recent followup.
	 *
	 *	@return	int	The date of the most recent followup.
	 */
	function getMostRecentDate() {
		return $this->data_array['most_recent_date'];
	}

	/**
	 *	hasFollowups - whether this message has any followups.
	 *
	 *	@return boolean has_followups.
	 */
	function hasFollowups() {
		return $this->data_array['has_followups'];
	}
	
	/**
	 *	hasAttach - whether this message has an attachment.
	 *
	 *	@return boolean has_attach.
	 */
	
	function hasAttach() {
		if ($this->isPending()) {
			$res = db_query_params ('SELECT attachmentid FROM forum_pending_attachment WHERE msg_id=$1',
						array ($this->getID())) ;
		} else {
			$res = db_query_params ('SELECT attachmentid FROM forum_attachment WHERE msg_id=$1',
						array ($this->getID())) ;
		}
		if (db_numrows($res) > 0) {
			return true;
		}
		return false;		
	}

	/**
	 *	delete - Delete this message and its followups.
	 *
	 *	@return	int	The count of deleted messages.
	 */
	function delete() {
		$msg_id=$this->getID();
		if (!$msg_id) {
			$this->setError(_('Invalid Message ID'));
			return false;
		}
		
		if (!forge_check_perm ('forum_admin', $this->Forum->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		
		$result = db_query_params ('SELECT msg_id FROM forum 
			WHERE is_followup_to=$1
			AND group_forum_id=$2',
					   array ($msg_id,
						  $this->Forum->getID())) ;
		$rows=db_numrows($result);
		$count=1;

		for ($i=0;$i<$rows;$i++) {
			$msg = new ForumMessage($this->Forum,db_result($result,$i,'msg_id'));
			$count += $msg->delete();
		}
		$toss = db_query_params ('DELETE FROM forum 
			WHERE msg_id=$1
			AND group_forum_id=$2',
					 array ($msg_id,
						$this->Forum->getID())) ;

		$res = db_query_params ('DELETE FROM forum_attachment where msg_id=$1',
					array ($msg_id));

		return $count;
	}

	/**
	 *	removebbcode - workaround to remove bbcode tags.
	 *
	 *	@return string	converted text
	 */
	function removebbcode($text) {
		//$replaced =  preg_replace("/\[[_a-zA-Z]:.+\](.+)\[\/[_a-zA-Z]:.*\]/","$1",$text);
		$replaced =  preg_replace("/\[.+\](.+)\[\/.+\]/","$1",$text);
		return $replaced;
	}
	
	/**
	 *	sendNotice - contains the logic to send out email followups when a message is posted.
	 *
	 *	@param  boolean	Whether the message has an attach associated. Defaults to false
	 *
	 *	@return boolean success.
	 */
	function sendNotice($has_attach=false) {
		$ids = $this->Forum->getMonitoringIDs();

		$recipients = array ();
		foreach ($ids as $id) {
			$recipient = user_get_object ($id) ;
			if ($recipient->isActive()) {
				$recipients[] = $recipient ;
			}
		}
		if ($this->Forum->getSendAllPostsTo()) {
			$sapt = explode (',', $this->Forum->getSendAllPostsTo()) ;
			foreach ($sapt as $r) {
				$recipients[] = $r;
			}
		}

		if (count ($recipients) == 0) {
			return true ;
		}

		foreach ($recipients as $recipient) {
			if (is_a ($recipient, 'GFUser')) {
				setup_gettext_for_user ($recipient) ;
				$dest_email = $recipient->getEmail ();
			} else {
				setup_gettext_from_sys_lang ();
				$dest_email = $recipient ;
			}

			$body = sprintf(_("\nRead and respond to this message at: \n%s"), util_make_url ('/forum/message.php?msg_id='.$this->getID()));
			if (forge_get_config('use_mail')) {
				$body .= stripcslashes(sprintf(_('
Or reply to this e-mail entering your response between the following markers: 
%1$s
(enter your response here)
%1$s'), FORUM_MAIL_MARKER));
			}
			$body .= sprintf(_("\n\n\nBy: %s\n"), $this->getPosterRealName());

			if ($has_attach) {
				//if there's an attachment for the message, make it note.
				//Note: We can't give a link for the attachment here because it hasn't been created yet (first the message needs to be created
				$body .= _("A file has been uploaded with this message.")."\n\n";
			} else {
				$body .= "\n";
			}
			$sanitizer = new TextSanitizer();
			$text = $this->getBody();
			$text = $sanitizer->convertNeededTagsForEmail($text);
			$text= strip_tags($this->removebbcode(util_line_wrap($text)));
			$text = $sanitizer->convertExtendedCharsForEmail($text);
			$body .= sprintf(
				"%s\n\n______________________________________________________________________\n".
				_("You are receiving this email because you elected to monitor this forum.".
				  "\nTo stop monitoring this forum, login to %s and visit: \n%s\n"),
				$text,
				forge_get_config ('forge_name'),
				util_make_url('/forum/monitor.php?forum_id='.$this->Forum->getID().
					      '&group_id='.$this->Forum->Group->getID().'&stop=1')
				);

			$extra_headers = "Return-Path: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Errors-To: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Sender: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Reply-To: ".$this->Forum->getReturnEmailAddress()."\n";
			$extra_headers .= "Precedence: Bulk\n"
				."List-Id: ".$this->Forum->getName()." <forum".$this->Forum->getId()."@".forge_get_config('web_host').">\n"
				."List-Help: ".util_make_url ('/forum/forum.php?id='.$this->Forum->getId())."\n"
				."Message-Id: <forumpost".$this->getId()."@".forge_get_config('web_host').">";
			$parentid = $this->getParentId();
			if (!empty($parentid)) {
				$extra_headers .= "\nIn-Reply-To: ".$this->Forum->getReturnEmailAddress()."\n"
					."References: <forumpost".$this->getParentId()."@".forge_get_config('web_host').">";
			}
			
			$subject="[" . $this->Forum->getUnixName() ."][".$this->getID()."] ".util_unconvert_htmlspecialchars($this->getSubject());

			util_send_message($dest_email,$subject,$body,"noreply@".forge_get_config('web_host'),'','Forum',$extra_headers);
		}

		// Switch back to the user language settings
		setup_gettext_from_context();
		return true;
	}
	
	/**
	 *	sendNewModeratedMsgNotice - contains the logic to send out email notifications to the forum admins when a new moderated message is posted
	 *
	 *	@return boolean success.
	 */
	function sendNewModeratedMsgNotice() {
		$ids = array () ;
		$engine = RBACEngine::getInstance () ;
		$moderators = $engine->getUsersByAllowedAction ('forum', $this->Forum->getID(), 'moderate') ;

		foreach ($moderators as $m) {
			$ids[] = $m->getID () ;
		}
		
		//
		//	See if there is anyone to send messages to
		//
		if (!count($ids) > 0 && !$this->Forum->getSendAllPostsTo()) {
			return true;
		}
		
		$f =& $this->getForum();
		$g =& $f->getGroup();

		$body = "\nRead to this message and approve/reject it at: ".
			"\n".util_make_url('/forum/admin/pending.php?action=view_pending&group_id='. $g->getID() . "&forum_id=" . $f->getID()) .
		"\nBy: " . $this->getPosterRealName() . "\n\n";

		$text = $this->getBody();
		$sanitizer = new TextSanitizer();	
		$text = $sanitizer->convertNeededTagsForEmail($text);
		$text= strip_tags($this->removebbcode(util_line_wrap($text)));
		$text = $sanitizer->convertExtendedCharsForEmail($text);
		$body .= $text .
		"\n\n______________________________________________________________________".
		"\nYou are receiving this email because the forum you administrate has a new moderated message awaiting your approval.";

		//$extra_headers = 'Reply-to: '.$this->Forum->getUnixName().'@'.forge_get_config('web_host');
		$extra_headers = "Return-Path: <noreply@".forge_get_config('web_host').">\n";
		$extra_headers .= "Errors-To: <noreply@".forge_get_config('web_host').">\n";
		$extra_headers .= "Sender: <noreply@".forge_get_config('web_host').">\n";
		$extra_headers .= "Reply-To: ".$this->Forum->getReturnEmailAddress()."\n";
		$extra_headers .= "Precedence: Bulk\n"
			."List-Id: ".$this->Forum->getName()." <forum".$this->Forum->getId()."@".forge_get_config('web_host').">\n"
			."List-Help: ".util_make_url('/forum/forum.php?id='.$this->Forum->getId())."\n"
			."Message-Id: <forumpost".$this->getId()."@".forge_get_config('web_host').">";
		$parentid = $this->getParentId();
		if (!empty($parentid)) {
 			$extra_headers .= "\nIn-Reply-To: ".$this->Forum->getReturnEmailAddress()."\n"
				."References: <forumpost".$this->getParentId()."@".forge_get_config('web_host').">";
		}

		$subject="[" . $this->Forum->getUnixName() ."][".$this->getID()."] ".util_unconvert_htmlspecialchars($this->getSubject());
		if (count($ids) != 0) {
			$bccres = db_query_params ('SELECT email FROM users WHERE status=$1 AND user_id = ANY ($2)',
						   array ('A',
							  db_int_array_to_any_clause ($ids))) ;
		}
		
		$BCC = implode(util_result_column_to_array($bccres),',').','.$this->Forum->getSendAllPostsTo();
		$User = user_get_object($this->getPosterID());
		//util_send_message('',$subject,$body,$User->getEmail(),$BCC,$this->getPosterRealName(),$extra_headers);
		util_send_message('',$subject,$body,"noreply@".forge_get_config('web_host'),$BCC,'Forum',$extra_headers);
//		util_handle_message(array_unique($ids),$subject,$body,$this->Forum->getSendAllPostsTo(),'','forumgateway@'.forge_get_config('web_host'));
		return true;
	}

	/**
	 *	updatemsg - impacts in the DB the new content of the message
	 *	
	 *	@param	string	The forum ID
	 *	@param 	int		The id of the user that is posting the message
	 *	@param	string	The subject of the message.
	 *	@param	string	The body of the message.
	 *	@param	string	The post date
	 *	@param	int		The message_id of the parent message, if any.
	 *	@param	int		The thread_id of the message, if known.
	 *	@param	int		has followups?
	 *	@param	string	The most recent date.
	 *
	 *	@return boolean success.
	 */
	function updatemsg($group_forum_id,$posted_by,$subject,$body,$post_date,$is_followup_to,$thread_id,$has_followups,$most_recent_date) {
		$subject = htmlspecialchars($subject);
		$body = $body;
		$msg_id = $this->getID();
		$res = db_query_params ('UPDATE forum 
			SET group_forum_id=$1, posted_by=$2, subject=$3,
			body=$4, post_date=$5, is_followup_to=$6,
			thread_id=$7, most_recent_date=$8
			WHERE msg_id=$9',
					array ($group_forum_id,
					       $posted_by,
					       $subject,
					       $body,
					       $post_date,
					       $is_followup_to,
					       $thread_id,
					       $most_recent_date,
					       $msg_id)) ;
		if (!$res) {
			$this->setError(db_error());
			return false;
		} else {
			if (db_affected_rows($res)<1) {
				$this->setError("Message not found");
				return false;
			}
			return true;
		}
	}
	
	/**
	 *	sendAttachNotice - contains the logic to send out email attachement followups when a message is posted.
	 *
	 *	@param int	attach_id	- The id of the file that has been attached
	 *
	 *	@return boolean success.
	 */
	function sendAttachNotice($attach_id) {
		if ($attach_id) {
			$ids = $this->Forum->getMonitoringIDs();

			//
			//	See if there is anyone to send messages to
			//
			if (!count($ids) > 0 && !$this->Forum->getSendAllPostsTo()) {
				return true;
			}
			
			$body = "\nRead and respond to this message at: ".
				"\n".util_make_url('/forum/message.php?msg_id='.$this->getID()).
			"\nBy: " . $this->getPosterRealName() . "\n\n";
			
			$body .= "A file has been uploaded to this message, you can download it at: ".
				"\n".util_make_url('/forum/attachment.php?attachid='. $attach_id . "&group_id=" . $this->Forum->Group->getID() . "&forum_id=" . $this->Forum->getID()) . "\n\n";

			$body .=
			"\n\n______________________________________________________________________".
			"\nYou are receiving this email because you elected to monitor this forum.".
			"\nTo stop monitoring this forum, login to ".forge_get_config ('forge_name')." and visit: ".
			"\n".util_make_url ('/forum/monitor.php?forum_id='.$this->Forum->getID() .'&group_id='.$this->Forum->Group->getID().'&stop=1');
	
			$extra_headers = "Return-Path: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Errors-To: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Sender: <noreply@".forge_get_config('web_host').">\n";
			$extra_headers .= "Reply-To: ".$this->Forum->getReturnEmailAddress()."\n";
			$extra_headers .= "Precedence: Bulk\n"
				."List-Id: ".$this->Forum->getName()." <forum".$this->Forum->getId()."@".forge_get_config('web_host').">\n"
				."List-Help: ".util_make_url('/forum/forum.php?id='.$this->Forum->getId())."\n"
				."Message-Id: <forumpost".$this->getId()."@".forge_get_config('web_host').">";
			$parentid = $this->getParentId();
			if (!empty($parentid)) {
				$extra_headers .= "\nIn-Reply-To: ".$this->Forum->getReturnEmailAddress()."\n"
					."References: <forumpost".$this->getParentId()."@".forge_get_config('web_host').">";
			}
	
			$subject="[" . $this->Forum->getUnixName() ."][".$this->getID()."] ".util_unconvert_htmlspecialchars($this->getSubject());
			if (count($ids) != 0) {
				$bccres = db_query_params ('SELECT email FROM users WHERE status=$1 AND user_id = ANY ($2)',
							   array ('A',
								  db_int_array_to_any_clause ($ids))) ;
			}
			$BCC = implode(util_result_column_to_array($bccres),',').','.$this->Forum->getSendAllPostsTo();
			$User = user_get_object($this->getPosterID());
			util_send_message('',$subject,$body,"noreply@".forge_get_config('web_host'),$BCC,'Forum',$extra_headers);
			return true;
		}
		
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

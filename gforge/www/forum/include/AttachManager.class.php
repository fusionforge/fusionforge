<?php

/**
 * GForge Attachment manager class
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
 *
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* 
	by Daniel Perez - 2005
*/

require_once $gfwww.'include/pre.php';

class AttachManager extends Error {
	
	var $attachs = array(); //the attached files
	var $msg_id; //the msg_id that links to the attachs
	var $user_id,$dateline,$filename,$filedata,$filesize,$visible,$filehash,$posthash;
	var $messages = array();
	var $ForumMsg; // The Forum object
	
	function Setmsgid($id) {
		$this->msg_id = $id;
	}
	
	function Getmessages() {
		return $this->messages;
	}
	
	function fillvalues($user_id,$dateline,$filename,$filedata,$filesize,$visible,$filehash,$posthash) {
		$this->user_id = $user_id;
		$this->dateline = $dateline;
		$this->filename = $filename;
		$this->filedata = $filedata;
		$this->visible = $visible;
		$this->filehash = $filehash;
		$this->posthash = $posthash;
	}
	
	/**
	* Function SetForumMsg
	*
	* Sets the forum message associated with the attachment
	*/
	function SetForumMsg(&$ForumMsg) {
		$this->ForumMsg =& $ForumMsg;
	}
	
	/**
	* Function GetAttachId
	*
	* Returns the attach id for the message id passed as a parameter or false if error
	*/
	function GetAttachId($msg_id) {
		$sql = "SELECT attachmentid FROM forum_attachment WHERE msg_id='$msg_id'";
		$res = db_query($sql);
		if ($res) {
			return db_result($res,0,0);
		} else {
			return false;
		}
	}
	
	/**
	* Function PrintHelperFunctions
	*
	*
	* @return 	returns the javascript helper functions
	*/
	
	function PrintHelperFunctions() {
		return '<script type="text/javascript">
		
		function confirmDel() {
			var agree=confirm("Proceed with deletion? ");
			if (agree) return true ;
			else return false ;
		}
		
		function manageattachments(url,del) {
			var newwindow;
			if (del=="yes") {
				if (!confirmDel())
					return;
			}
			newwindow = window.open(url, \'Attach\', \'statusbar=no,menubar=no,toolbar=no,scrollbars=yes,resizable=yes,width=600,height=480\');
			if (window.focus) {newwindow.focus()}
		}
		</script>';
	}
	
	 /**
	 * Function PrintAttachLink
	 *
	 * @param 	object	The message.
	 * @param 	int		The group id.
	 * @param 	int		The forum id.
	 *
	 * @return 	returns link to attachment /delete if corresponding; else returns a message about no attachment found
	 */
	function PrintAttachLink(&$msg,$group_id,$forum_id) {
		
		//ask if the message has an attachment
		$msg_id = $msg->getID();
		if ($msg->isPending()) {
			$sql = "SELECT attachmentid,filename,userid,counter FROM forum_pending_attachment where msg_id='$msg_id'";
			$pend = "&pending=yes";
		} else {
			$sql = "SELECT attachmentid,filename,userid,counter FROM forum_attachment where msg_id='$msg_id'";
			$pend = "";
		}
		
		$res = db_query($sql);
		if ($res) {
			$attachid = db_result($res,0,'attachmentid');
		}
		if ($attachid) {
			$attach = "
			<a href=\"javascript:manageattachments('".util_make_url("/forum/attachment.php?attachid=$attachid&group_id=$group_id&forum_id=$forum_id$pend")."','no');\">" . html_image('ic/cfolder15.png',"15","13",array("border"=>"0")) . db_result($res,0,'filename') . "</a>  (" . db_result($res,0,'counter') . ") downloads";
			$attach_userid = db_result($res,0,'userid');
			
			$f = $msg->getForum();
			if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
			} else {
				if ( ((user_getid() == $attach_userid) || ($f->userIsAdmin())) && (!$msg->isPending()) ) { //only permit the user who created the attach to delete it, or an admin
					$attach .= "   <a href=\"javascript:manageattachments('".util_make_url ("/forum/attachment.php?attachid=$attachid&group_id=$group_id&forum_id=$forum_id&msg_id=$msg_id&edit=yes")."','no');\">" .  "<font size=\"-3\">" .  html_image('ic/forum_edit.gif','37','15',array('alt'=>"Edit")) . "</font></a>";
					$attach .= "     <a href=\"javascript:manageattachments('".util_make_url("/forum/attachment.php?attachid=$attachid&group_id=$group_id&forum_id=$forum_id&delete=yes")."','yes');\">" .  "<font size=\"-3\">" .  html_image('ic/forum_delete.gif','16','18',array('alt'=>"Delete")) . "</font></a>";
				}
			}
		}	else {
			//add attach for existing message
			$f = $msg->getForum();
			if (!$f || !is_object($f)) {
			exit_error('Error','Could Not Get Forum Object');
			} else {
				$attach = html_image('ic/cfolder15.png',"15","13",array("border"=>"0")) . "No attachment found";
				if ( ((user_getid() == $msg->getPosterID()) || ($f->userIsAdmin())) && (!$msg->isPending()) ) { //only permit an admin or the user who created the message to insert an attach
					$attach .= "   <a href=\"javascript:manageattachments('".util_make_url ("/forum/attachment.php?attachid=0&group_id=$group_id&forum_id=$forum_id&msg_id=$msg_id&edit=yes")."','no');\">" .  "<font size=\"-3\">" .  html_image('ic/forum_add.gif','37','15',array('alt'=>"Add")) . "</font></a>";
				}
			}
		}
		
		return $attach;
	}
	
	/**
	 * Function AddToDBOnly : DB Query Only - used for releasing pending messages
	 *
	 *
	 */
	function AddToDBOnly($userid, $dateline, $filename, $filedata, $filesize, $visible, $filehash, $mimetype) {
		$result=db_query("SELECT max(msg_id) AS id FROM forum");
		if (!$result || db_numrows($result) < 1) {
			$this->messages[] = _('Couldn\'t get message id');
		} else {
			$this->msg_id = db_result($result,0,0);
			$sql = "INSERT INTO forum_attachment (userid, dateline, filename, filedata, filesize, visible, msg_id , filehash, mimetype)
					VALUES 
					( $userid , " . $dateline . ", '" . $filename . "',
					'" .  $filedata . "', $filesize, $visible, $this->msg_id,  '" . $filehash . "', '" . $mimetype  . "')";
			if (db_query($sql)) {
				$this->messages[] = _('File uploaded');
			}	else {
				$this->messages[] = _('File not uploaded');
				$this->setError();
			}
		}
	}
	
	
	
	/**
	 * Function attach : saves the file in the DB
	 *
	 * @param 	int		The file to attach
	 * @param 	int		The group.
	 * @param 	int		Whether we are updating an existing attach (attachid to update or cero for new message (inserts using the hights msg id from forum table)
	 * @param 	int		msg id. if update is 0 and we pass a msg_id <> 0, then we are adding an attach for an existing msg
	 *
	 * @return	int	    Attach id on success, false otherwise
	 *
	 */
	function attach($attach,$group_id,$update=0,$msg_id=0) {
		global $_FILES;

		$attachment = trim($attach['tmp_name']);
		$attachment_name = trim($attach['name']);
		$attachment_size = trim($attach['size']);
		$attachment_type = trim($attach['type']);
			
		if ($attachment == 'none' OR empty($attachment) OR empty($attachment_name))
		{
			$this->messages[] = _('No file was provided');
			return false; //no point in continuing if there's no file
		}
		
		$attachment_name2 = strtolower($attachment_name);
		$extension = substr(strrchr($attachment_name2, '.'), 1);
		
		if ($extension == 'exe')
		{
			// invalid extension
			$this->messages[] = _('Invalid Extension');

			@unlink($attachment);
			return false;
		}
		
		if (!is_uploaded_file($attachment) || !($filestuff = @file_get_contents($attachment)) )
		{
			$this->messages[] = _('Error, problem with the attachment file uploaded into the server');
			return false;
		}
		
		if (!session_loggedin()) {
			$user_id = 100;
		}	else {
			$user_id = user_getid();
		}
		
		$id = 0;
			
		if ($this->ForumMsg->isPending()) {
			if ($update) {
				//update the fileinfo
				/*$sql = "UPDATE forum_pending_attachment SET dateline = '" . time() . "' , filedata = '" . base64_encode($filestuff) .  "' , filename = '" . addslashes($attachment_name) . "' , filehash = '" . addslashes(md5($filestuff)) . "' where attachmentid=$update";
				if (db_query($sql)) {
					$this->messages[] = _('File uploaded');
				}	else {
					$this->messages[] = _('File not uploaded');
				}*/
				// not implemented
			} else {
				// add to db
				if ($msg_id!=0) {
					$this->msg_id = $msg_id;
				} else {
					$result=db_query("SELECT max(msg_id) AS id FROM forum_pending_messages");
					if (!$result || db_numrows($result) < 1) {
						$this->messages[] = _('Couldn\'t get message id');
						@unlink($attachment);
						return false;
					} else {
						$this->msg_id = db_result($result,0,0);
					}
				}
				$sql = "INSERT INTO forum_pending_attachment (userid, dateline, filename, filedata, filesize, visible, msg_id , filehash, mimetype)
					VALUES 
					( $user_id , " . time() . ", '" . addslashes($attachment_name) . "',
					'" .  base64_encode($filestuff) . "', $attachment_size, 1, $this->msg_id,  '" . addslashes(md5($filestuff)) . "', '". addslashes($attachment_type) ."')";
				$res = db_query($sql);
				if ($res) {
					$this->messages[] = _('File uploaded');
					$id = db_insertid($res,'forum_pending_attachment','attachmentid');
				}	else {
					$this->messages[] = _('File not uploaded');
				}
			}
		} else {
			if ($update) {
				//update the fileinfo
				$sql = "UPDATE forum_attachment SET dateline = '" . time() . "' , filedata = '" . base64_encode($filestuff) .  "' ,
				 filename = '" . addslashes($attachment_name) . "' , 
				 filehash = '" . addslashes(md5($filestuff)) . "' , 
				 mimetype = '" . addslashes($attachment_type) . "' ,
				 counter = '0' ,
				 filesize = '" . $attachment_size . "' where attachmentid=$update";
				if (db_query($sql)) {
					$this->messages[] = _('File uploaded');
					$this->messages[] = _('File Updated Successfully');
					$id = $update;
				}	else {
					$this->messages[] = _('File not uploaded');
				}
			} else {
				// add to db
				if ($msg_id!=0) {
					$this->msg_id = $msg_id;
				} else {
					$result=db_query("SELECT max(msg_id) AS id FROM forum_pending_messages");
					if (!$result || db_numrows($result) < 1) {
						$this->messages[] = _('Couldn\'t get message id');
						@unlink($attachment);
						return false;
					} else {
						$this->msg_id = db_result($result,0,0);
					}
				}
				$sql = "INSERT INTO forum_attachment (userid, dateline, filename, filedata, filesize, visible, msg_id , filehash, mimetype)
					VALUES 
					( $user_id , " . time() . ", '" . addslashes($attachment_name) . "',
					'" .  base64_encode($filestuff) . "', $attachment_size, 1, $this->msg_id,  '" . addslashes(md5($filestuff)) . "', '" . addslashes($attachment_type) . "')";
				$res = db_query($sql);
				if ($res) {
					$this->messages[] = _('File uploaded');
					$id = db_insertid($res,'forum_attachment','attachmentid');
				}	else {
					$this->messages[] = _('File not uploaded');
				}
			}
		}
		@unlink($attachment);
		return $id;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

#! /usr/bin/php
<?php
/**
 * This script will get mails and store it into forum DB
 *
 * Copyright 2004 GForge, LLC
 *
 * @author Tim Perdue tim@gforge.org
 * @author Sung Kim
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

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/Group.class.php';
require_once $gfcommon.'include/MailParser.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'forum/ForumMessage.class.php';

class ForumGateway extends Error {
	/*
	 * variables
	 */
	var $From = "";
	var $FromName = "";
	var $FromEmail = "";
	var $Subject = "";
	var $ListId = "";
	var $Reference = "";
	var $MsgId = "";
	var $Sender="";
	var $Body="";
	var $ThreadId=0;
	var $IsFollowUp=0;
	var $Forum=-1;
	var $Parent=0;
	var $ForumId=-1;
	var $Message="";

	function ForumGateway() {
		$this->Error();

		/* Copy mail message to tmp file */
		$tmpfile = $this->copyMailTmp();
		//DBG("Tmpname: ". $tmpfile);

		/* parse email */
		$ret = $this->parseMail($tmpfile);

		/* Delete temp file */
		unlink($tmpfile);

		/* Check the return variable from parseMail */
		if (!$ret) {
			return false;
		}

		/* add the info to forum */
		$ret = $this->addMessage();
		if (!$ret) {
			return false;
		}

		return true;
	}


	/**
	 * function - Copy mail(from stdin to tmp and return the tmp file
	 *
	 * @return tmp file name
	 */
	function copyMailTmp() {
		// Unfortunatly we need a temp file
		// mailparse needs to read content several times
		$tmpfile = tempnam ("/tmp", "forum_gateway.".util_randnum()."-".util_randnum());
		$in = fopen("php://stdin", "r");
		$out = fopen($tmpfile, "w");

		while($buffer = fgets($in, 4096)) {
			fputs($out, $buffer);
		}

		fclose($in);
		fclose($out);

		return $tmpfile;
	}


	/*
	 * function - Parse mail and fill all kinds of head and body info
	 *
	 * @param  string tmp file name
	 * @return boolean true if success
	 */
	function parseMail($input_file) {
		global $argv;
//DBG("parseMail start");

		if (!$mp = new MailParser($input_file)) {
			$this->setError('Error In MailParser');
//DBG("parseMail error1: ".$mp->getErrorMessage());
			return false;
		} elseif ($mp->isError()) {
			$this->setError('Error In MailParser '.$mp->getErrorMessage());
//DBG("parseMail error2: ".$mp->getErrorMessage());
 			// even if it is an error, try to get the address of the sender so we
 			// can send him back the error
 			$this->FromEmail = $mp->getFromEmail();
			return false;
		}

		$this->FromEmail = $mp->getFromEmail();
//DBG("email: ".$this->FromEmail);
//echo ")()()()()()()".$this->FromEmail."(*(*(*(*(*";
		//
		//subjects are in this required format: '[group - Forum][123456] My Subject'
		//where 123456 is the msg_id of the forum message.
		//we parse that ID to get the forum and thread that this should post to
		//
		$subj = $mp->getSubject();
		if ($mp->isError())
			$this->setError($mp->getErrorMessage());
		if ($subj === false)
			return false;

//DBG("mp headers: ".implode("**\n",$mp->headers));
//DBG("mp body: ".$mp->body);
//DBG("SUBJ: ".$subj);
//DBG("BODY: ".$mp->getBody());
/*
		$parent_start = (strpos($subj,'[',(strpos($subj,'[')+1))+1);
		$parent_end = (strpos($subj,']',$parent_start)-1);
		$this->Parent = substr($subj,$parent_start,($parent_end-$parent_start+1));
		if (!$this->Parent || !is_numeric($this->Parent)) {
//			$argv[1] - listname
//			echo "No Parent ".$argv[0]."||".$argv[1];
			$this->Parent=0;
			$this->Subject = addslashes($subj);
//			$this->setError('No Valid Parent ID Found in Subject Line');
//			return false;
		} else {
//			echo "Parent: ".$this->Parent."||".$argv[0]."||".$argv[1];
			$this->Subject = addslashes(substr($subj,$parent_end+3));
		}
*/
		if (preg_match('/(\[)([0-9]*)(\])/',$subj,$arr)) {
			$this->Parent=$arr[2];
			$parent_end=(strpos($subj,'['.$arr[2].']')) + strlen('['.$arr[2].']');
			$this->Subject = substr($subj,$parent_end);
		} else {
			$this->Subject = $subj;
			$this->Parent=0;
		}
		$this->Body =& $mp->getBody();
//DBG( "body1:". $this->Body);

		$begin = strpos($this->Body, FORUM_MAIL_MARKER);
		if ($begin === false) { //do nothing
				return true;
		}
		// get the part of the message located after the marker
		$this->Body = substr($this->Body, $begin+strlen(FORUM_MAIL_MARKER));
//DBG( "body2:". $this->Body);
		// now look for the ending marker
		$end = strpos($this->Body, FORUM_MAIL_MARKER);
		if ($end === false) {
			return true;
		}
		$message = substr($this->Body, 0, $end);
		$message = trim($message);

		// maybe the last line was "> (FORUM_MAIL_MARKER)". In that case, delete the last ">"
		$message = preg_replace('/>$/', '', $message);
		$this->Message = $message;

		return true;
	}

	/**
	 * Insert data into the forum db
	 *
	 * @return - true or false
	 */
	function addMessage() {
		//
		//	get user_id
		//
		$user_id = $this->getUserId();
		if ($user_id) {
			//
			//	Set up this user's session before posting
			//
			session_set_new($user_id);
		}

//DBG( "AddMessage 1\n");
		$Forum =& $this->getForum();
		if (!$Forum || !is_object($Forum)) {
			$this->setError("Could Not Get Forum");
			return false;
		} elseif ($Forum->isError()) {
			$this->setError("Forum Error: ".$Forum->getErrorMessage());
			return false;
		}
		if (!$user_id && !$Forum->AllowAnonymous()) {
			$this->setError("Could Not Match Sender Email Address to User and Forum Does Not Allow Anonymous Posts");
			return false;
		}

//DBG( "AddMessage 2\n");
		//
		//	Create a blank forum message
		//
		$ForumMessage = new ForumMessage($Forum);
		if (!$ForumMessage || !is_object($Forum)) {
			$this->setError("Could Not Get Forum Message");
			return false;
		} elseif ($ForumMessage->isError()) {
			$this->setError("ForumMessage Error: ".$ForumMessage->getErrorMessage());
			return false;
		}
//DBG( "AddMessage 3\n");
		if ($this->Message!=""){
			if (!$ForumMessage->create($this->Subject,$this->Message,$this->ThreadId,$this->Parent)) {
//DBG( "AddMessage 4.".$ForumMessage->getErrorMessage()."\n");
				$this->setError("ForumMessage Create Error: ".$ForumMessage->getErrorMessage());
				return false;
			} else {
//DBG( "AddMessage 5.".$ForumMessage->getErrorMessage()."\n");
				return true;
			}
		} else {
			return true;
		}
	}


	/*------------------------------------------------------------------------
	 *  Utility functions
	 *-----------------------------------------------------------------------*/

	/* Find user_id from email */
	function getUserId() {
		// Find User id using email
		$from = strtolower($this->FromEmail);
		// If no user id, user id is 0;
		if (! $from) return 0;
		$res = db_query_params ('SELECT user_id FROM users
			WHERE lower(email) = $1 AND status = $2',
					array (strtolower($from),
					       'A'));
		if (!$res || db_numrows($res) < 1) {
			return false;
		} else {
			$user_id = db_result($res,0,'user_id');
		}
		db_free_result($res);

		return $user_id;
	}

	function &getForum() {
		global $argv;

		if ($this->Forum==-1) {
			$Group = group_get_object_by_name($argv[1]);
			if (!$Group || !is_object($Group)) {
				$this->setError('Could Not Get Group Object');
				return false;
			} elseif ($Group->isError()) {
				$this->setError('Getting Group Object: '.$Group->getErrorMessage());
				return false;
			}
			if ($this->Parent) {
				//
				// Find Forum id by parent
				//
				$res = db_query_params ('SELECT group_forum_id,thread_id
					FROM forum
					WHERE msg_id=$1',
							array ($this->Parent));
			} else {
				//
				//	Find forum by arguments passed by aliases file
				//
				$res = db_query_params ('SELECT group_forum_id, 0 AS thread_id
					FROM forum_group_list
					WHERE forum_name=$1
					AND group_id=$2',
							array ($argv[2],
							       $Group->getID()));
			}
			if (!$res || db_numrows($res) < 1) {
				$this->setError('Getting Forum IDs: '.db_error());
				return false;
			}
			$this->ForumId = db_result($res,0,'group_forum_id');
			$this->ThreadId = db_result($res,0,'thread_id');
			db_free_result($res);

			$this->Forum = new Forum($Group,$this->ForumId);
		}

		return $this->Forum;
	}

}


/**
 * Simple debugging printput
 *
 * Add this in /etc/syslog.conf and see /var/log/debug file:
 * # Debug
 * *.=debug			/var/log/debug
 *
 */
function DBG($str) {
	global $debug;
system("echo \"forum: ".$str."\n\" >> /tmp/forum.log");

	if ($debug==1) {
		syslog(LOG_DEBUG, "forum_gateway: ". $str);
	} else if ($debug==2) {
		echo $str."\n";
	}
}


/* Main routine */
$debug = 0;
$listforum = new ForumGateway();
if ($listforum->isError()) {
	mail ($listforum->FromEmail,'Forum Post Rejected',$listforum->getErrorMessage());
	DBG('Final Message: '.$listforum->getErrorMessage());
} else {
//	DBG("Success!!");
}

?>

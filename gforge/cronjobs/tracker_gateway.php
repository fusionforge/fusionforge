#! /usr/bin/php4 -f
<?php
/**
 * This script will get mails and store it into artifact DB
 *
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @author Sung Kim 
 * @author Francisco Gimeno <kikov@kikov.org>
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
 *
 * This file is based on forum_gateway.php
 */

require_once ('squal_pre.php');
require_once ('common/include/Group.class');
require_once ('common/include/MailParser.class');
require_once ('common/tracker/Artifact.class');
require_once ('common/tracker/ArtifactFactory.class');

class TrackerGateway extends Error {
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
	var $IsFollowUp=0;
	var $ArtifactId=-1;
	var $Artifact=null;

	function TrackerGateway() {
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

		/* add the info to tracker */
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
		$tmpfile = tempnam ("/tmp", "artifact_gateway.".rand()."-".rand());
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
		
		if (!$mp = new MailParser($input_file)) {
			$this->setError('Error In MailParser');
			return false;
		} elseif ($mp->isError()) {
			$this->setError('Error In MailParser '.$mp->getErrorMessage());
			return false;
		}

		$this->FromEmail = $mp->getFromEmail();
		//
		//subjects are in this required format: '[group - tracker_name][123456] My Subject'
		//where 123456 is the artifact_id of the artifact message.
		//we parse that ID to get the artifact that this should post to
		//
		$subj = $mp->getSubject();
		if (ereg('(\[)([0-9]*)(\])',$subj,$arr)) {
		        $this->ArtifactId=$arr[2];
			$artifactid_end=(strpos($subj,'['.$arr[2].']')) + strlen('['.$arr[2].']');
			$this->Subject = addslashes(substr($subj,$artifactid_end));
		} else {
			$this->Subject = addslashes($subj);
			$this->ArtifactId=0; // Not supported at the moment
			$this->setError("ArtifactId needed at the moment. Artifact creation not supported");
			return false;
		}

		$this->Body =& addslashes($mp->getBody());
		return true;
	}
	
	/**
	 * Insert data into the tracker db
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

		$Artifact =& $this->getArtifact();
		if (!$Artifact || !is_object($Artifact)) {
			$this->setError("Could Not Get Artifact");
			return false;
		} 
		if (!$user_id && !$Artifact->ArtifactType->allowsAnon()) {
			$this->setError("Could Not Match Sender Email Address to User and Tracker Does Not Allow Anonymous Posts");
			return false;
		}

		//
		//	Create artifact message
		//
		if ( !$Artifact->addMessage($this->Body,$this->FromName) )
		{
			$this->setError("ArtifactMessage Error:".$Artifact->getErrorMessage());
			return false;
		}
		return true;
	}


	/*------------------------------------------------------------------------
	 *  Utility functions 
	 *-----------------------------------------------------------------------*/

	/* Find user_id from email */
	function getUserId() {
		// Find User id using email
		// If no user id, user id is 0;
		$sql = "SELECT user_id FROM users 
			WHERE lower(email) ='".strtolower($this->FromEmail)."' AND status='A'";
		$res = db_query($sql);
		if (!$res || db_numrows($res) < 1) {
			return false;
		} else {
			$user_id = db_result($res,0,'user_id');
		}
		db_free_result($res);
	
		return $user_id;
	}

	function &getArtifact() {
		global $argv;

			// $Group not needed, but let the code here to support
			// tracker additions in the Future
			$Group =& group_get_object_by_name($argv[1]);
			if (!$Group || !is_object($Group)) {
				$this->setError('Could Not Get Group Object');
				return false;
			} elseif ($Group->isError()) {
				$this->setError('Getting Group Object: '.$Group->getErrorMessage());
				return false;
			}
			// DBG("Artifact_get_object(".$this->ArtifactId.");");
			$this->Artifact =& artifact_get_object($this->ArtifactId);
	
		return $this->Artifact;
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

	if ($debug==1) {
		system("echo \"artifact: ".$str."\n\" >> /tmp/tracker.log");
		syslog(LOG_DEBUG, "artifact_gateway: ". $str);
	} else if ($debug==2) {
		echo $str."\n";
	}
}
 

/* Main routine */
$debug = 0;
$myTrackerGateway = new TrackerGateway();
if ($myTrackerGateway->isError()) {
	mail ($myTrackerGateway->FromEmail,'Forum Post Rejected',$myTrackerGateway->getErrorMessage());
	DBG('Final Message: '.$myTrackerGateway->getErrorMessage());
} else {
//	DBG("Success!!");
}

?>

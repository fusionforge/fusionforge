<?php
/**
 * FusionForge trackers
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002-2004, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2009-2013 Alain Peyrat, Alcatel-Lucent
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2014-2015, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
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

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Artifact ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */
require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'tracker/ArtifactMessage.class.php';
require_once $gfcommon.'tracker/ArtifactExtraField.class.php';
require_once $gfcommon.'tracker/ArtifactWorkflow.class.php';
require_once $gfcommon.'tracker/ArtifactStorage.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

// This string is used when sending the notification mail for identifying the
// user response
define('ARTIFACT_MAIL_MARKER', '#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+#+');

/**
 * Factory method which creates an Artifact from an artifact ID
 *
 * @param	int		$artifact_id	The artifact ID
 * @param	array|bool	$data		The result array, if it's passed in
 * @return	Artifact	Artifact object
 */
function &artifact_get_object($artifact_id,$data=false) {
	global $ARTIFACT_OBJ;
	if (!isset($ARTIFACT_OBJ["_".$artifact_id."_"])) {
		if ($data) {
			//the db result handle was passed in
		} else {
			$res = db_query_params ('SELECT * FROM artifact_vw WHERE artifact_id=$1',
						array ($artifact_id)) ;
			if (db_numrows($res) <1) {
				$ARTIFACT_OBJ["_".$artifact_id."_"]=false;
				return false;
			}
			$data = db_fetch_array($res);
		}
		$ArtifactType =& artifactType_get_object($data["group_artifact_id"]);
		$ARTIFACT_OBJ["_".$artifact_id."_"]= new Artifact($ArtifactType,$data);
	}
	return $ARTIFACT_OBJ["_".$artifact_id."_"];
}

class Artifact extends FFError {

	/**
	 * Resource ID.
	 *
	 * @var	int	$status_res.
	 */
	var $status_res;

	/**
	 * Artifact Type object.
	 *
	 * @var	object	$ArtifactType.
	 */
	var $ArtifactType;

	/**
	 * Array of artifact data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * Array of artifact data for extra fields defined by Admin.
	 *
	 * @var	array	$extra_field_data.
	 */
	var $extra_field_data;

	/**
	 * Array of ArtifactFile objects.
	 *
	 * @var	array	$files
	 */
	var $files;

	/**
	 * Database result set of related tasks
	 *
	 * @var	result	$relatedtasks
	 */
	var $relatedtasks;

	/**
	 * cached return value of getVotes
	 * @var	int|bool	$votes
	 */
	var $votes = false;

	/**
	 * @param	ArtifactType	$ArtifactType	The ArtifactType object.
	 * @param	int|bool	$data		(primary key from database OR complete assoc array)
	 *						ONLY OPTIONAL WHEN YOU PLAN TO IMMEDIATELY CALL ->create()
	 */
	function __construct(&$ArtifactType, $data=false) {
		parent::__construct();

		$this->ArtifactType =& $ArtifactType;

		// Was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError(_('Invalid Artifact Type'));
			return;
		}

		// Did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError($ArtifactType->getErrorMessage());
			return;
		}

		// Make sure this person has permission to view artifacts
		if (!forge_check_perm ('tracker', $this->ArtifactType->getID(), 'read')) {
			$this->setError(_('Only project members can view private artifact types'));
			return;
		}

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
			} else {
				$this->fetchData($data);
			}
		}
	}

	/**
	 * create - construct a new Artifact in the database.
	 *
	 * @param	string	$summary	The artifact summary.
	 * @param	string	$details	Details of the artifact.
	 * @param	int	$assigned_to	The ID of the user to which this artifact is to be assigned.
	 * @param	int	$priority	The artifacts priority.
	 * @param	array	$extra_fields	Array of extra fields like: array(15=>'foobar',22=>'1');
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789, 'nopermcheck' => true, 'nonotice' => true)
	 * @return	bool	id on success / false on failure.
	 */
	function create($summary, $details, $assigned_to=100, $priority=3, $extra_fields=array(), $importData = array()) {
		//
		//	make sure this person has permission to add artifacts
		//

		if (isset($importData['nonotice']) && $importData['nonotice']) {
			$sendNotice = false;
		} else {
			$sendNotice = true;
		}

		//
		//	get the user_id
		//
		if(array_key_exists('user', $importData)){
				$user = $importData['user'];
		} else {
			if (!forge_check_perm ('tracker',$this->ArtifactType->getID(),'submit')) {
				$this->setError(_('You are not currently allowed to submit items to this tracker.'));
				return false;
			}

			$user = ((session_loggedin()) ? user_getid() : 100);
		}

		//
		//	data validation
		//
		if (!$summary) {
			$this->setError(_('Message Summary Is Required'));
			return false;
		}
		if (!$details) {
			$this->setError(_('Message Body Is Required'));
			return false;
		}
		if (!$assigned_to) {
			$assigned_to=100;
		}
		if (!$priority) {
			$priority=3;
		}
		$status_id=1;		// on creation, status is set to "open"

		//
		//	They may be using an extra field "status" box so we have to remap
		//	the status_id based on the extra field - this keeps the counters
		//	accurate
		//
		$status_id = $this->ArtifactType->remapStatus($status_id,$extra_fields);
		if (!$status_id) {
			$this->setError($this->ArtifactType->getErrorMessage());
			return false;
		}

		db_begin();
		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		$res = db_query_params ('INSERT INTO artifact
			(group_artifact_id,status_id,priority,
			submitted_by,assigned_to,open_date,summary,details)
			VALUES ($1,$2,$3,$4,$5,$6,$7,$8)',
					array ($this->ArtifactType->getID(),
					       $status_id,
					       $priority,
					       $user,
					       $assigned_to,
					       $time,
					       htmlspecialchars($summary),
					       htmlspecialchars($details))) ;
		if (!$res) {
			$this->setError(db_error());
			db_rollback();
			return false;
		}

		$artifact_id=db_insertid($res,'artifact','artifact_id');

		if (!$res || !$artifact_id) {
			$this->setError(db_error());
			db_rollback();
			return false;
		} else {
			//
			//	Now set up our internal data structures
			//
			if (!$this->fetchData($artifact_id)) {
				db_rollback();
				return false;
			} else {
				// the changes to the extra fields will be logged in this array.
				// (we won't use it however)
				$extra_field_changes = array();
				if (!$this->updateExtraFields($extra_fields, $extra_field_changes, $importData)) {
					db_rollback();
					return false;
				}
			}
			//
			//	now send an email if appropriate
			//
			if ($sendNotice) {
				$this->mailFollowupEx(0, 1);
			}
			db_commit();

			return $artifact_id;
		}
	}

	/**
	 * fetchData - re-fetch the data for this Artifact from the database.
	 *
	 * @param	int	$artifact_id	The artifact ID.
	 * @return	boolean	success.
	 */
	function fetchData($artifact_id) {
		$this->votes = false;
		$res = db_query_params ('SELECT * FROM artifact_vw WHERE artifact_id=$1 AND group_artifact_id=$2',
					array ($artifact_id,
					       $this->ArtifactType->getID())) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid Artifact ID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getArtifactType - get the ArtifactType Object this Artifact is associated with.
	 *
	 * @return	object	ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}

	/**
	 * getID - get this ArtifactID.
	 *
	 * @return	int	The artifact_id #.
	 */
	function getID() {
		return $this->data_array['artifact_id'];
	}

	/**
	 * getStringID - get a string display for this ArtifactID.
	 *
	 * @return	string	The artifact_id #.
	 */
	function getStringID() {
		return '[#'.$this->data_array['artifact_id'].']';
	}

	/**
	 * getStatusID - get open/closed/deleted flag.
	 *
	 * @return	int	Status: (1) Open, (2) Closed, (3) Deleted.
	 */
	function getStatusID() {
		return $this->data_array['status_id'];
	}

	/**
	 * getStatusName - get open/closed/deleted text.
	 *
	 * @return	string	The status name.
	 */
	function getStatusName() {
		return $this->data_array['status_name'];
	}

	/**
	 * getCustomStatusName - get custom status value text.
	 *
	 * @return	string	The custom status name.
	 */
	function getCustomStatusName() {
		$custom_status_id = $this->ArtifactType->getCustomStatusField();
		if ($custom_status_id) {
			$result = db_query_params ('SELECT element_name FROM artifact_extra_field_elements aefe, artifact_extra_field_data aefd
										WHERE artifact_id=$1 AND aefd.extra_field_id=$2 AND CAST(aefd.field_data AS INTEGER)=aefe.element_id',
								array ($this->getID(), $custom_status_id)) ;
			if ($result) {
				return db_result($result, 0, 'element_name');
			}
		}
		return $this->data_array['status_name'];
	}

	/**
	 * getPriority - get priority flag.
	 *
	 * @return	int	priority.
	 */
	function getPriority() {
		return $this->data_array['priority'];
	}

	/**
	 * getSubmittedBy - get ID of submitter.
	 *
	 * @return	int	user_id of submitter.
	 */
	function getSubmittedBy() {
		return $this->data_array['submitted_by'];
	}

	/**
	 * getSubmittedEmail - get email of submitter.
	 *
	 * @return	string	The email of submitter.
	 */
	function getSubmittedEmail() {
		return $this->data_array['submitted_email'];
	}

	/**
	 * getSubmittedRealName - get real name of submitter.
	 *
	 * @return	string	The real name of submitter.
	 */
	function getSubmittedRealName() {
		return $this->data_array['submitted_realname'];
	}

	/**
	 * getSubmittedUnixName - get login name of submitter.
	 *
	 * @return	string	The unix name of submitter.
	 */
	function getSubmittedUnixName() {
		return $this->data_array['submitted_unixname'];
	}

	/**
	 * getAssignedTo - get ID of assignee.
	 *
	 * @return	int	user_id of assignee.
	 */
	function getAssignedTo() {
		return $this->data_array['assigned_to'];
	}

	/**
	 * getAssignedEmail - get email of assignee.
	 *
	 * @return	string	The email of assignee.
	 */
	function getAssignedEmail() {
		return $this->data_array['assigned_email'];
	}

	/**
	 * getAssignedRealName - get real name of assignee.
	 *
	 * @return	string	The real name of assignee.
	 */
	function getAssignedRealName() {
		return $this->data_array['assigned_realname'];
	}

	/**
	 * getAssignedUnixName - get login name of assignee.
	 *
	 * @return	string	The unix name of assignee.
	 */
	function getAssignedUnixName() {
		return $this->data_array['assigned_unixname'];
	}

	/**
	 * getOpenDate - get unix time of creation.
	 *
	 * @return	int	unix time.
	 */
	function getOpenDate() {
		return $this->data_array['open_date'];
	}

	/**
	 * getCloseDate - get unix time of closure.
	 *
	 * @return	int	unix time.
	 */
	function getCloseDate() {
		return $this->data_array['close_date'];
	}

	/**
	 * getLastModifiedDate - the last_modified_date of this task.
	 *
	 * @return	int	 the last_modified_date.
	 */
	function getLastModifiedDate() {
		return $this->data_array['last_modified_date'];
	}

	/**
	 * getSummary - get text summary of artifact.
	 *
	 * @return	string	The summary (subject).
	 */
	function getSummary() {
		return $this->data_array['summary'];
	}

	/**
	 * getDetails - get text body (message) of artifact.
	 *
	 * @return	string	The body (message).
	 */
	function getDetails() {
		return $this->data_array['details'];
	}

	/**
	 * delete - delete this tracker and all its related data.
	 *
	 * @param	bool	$sure I'm Sure.
	 * @return	bool	true/false;
	 */
	function delete($sure) {
		if (!$sure) {
			$this->setMissingParamsError(_('Please tick all checkboxes.'));
			return false;
		}
		if (!forge_check_perm ('tracker_admin', $this->ArtifactType->Group->getID())) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		$res = db_query_params ('DELETE FROM artifact_extra_field_data WHERE artifact_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting extra field data')._(': ').db_error());
			db_rollback();
			return false;
		}

		ArtifactStorage::instance()->deleteFromQuery('SELECT id FROM artifact_file WHERE artifact_id=$1',
					array ($this->getID())) ;

		$res = db_query_params ('DELETE FROM artifact_file WHERE artifact_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting file from db')._(': ').db_error());
			db_rollback();
			ArtifactStorage::instance()->rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM artifact_message WHERE artifact_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting message')._(': ').db_error());
			db_rollback();
			ArtifactStorage::instance()->rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM artifact_history WHERE artifact_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting history')._(': ').db_error());
			db_rollback();
			ArtifactStorage::instance()->rollback();
			return false;
		}
		$MonitorElementObject = new MonitorElement('artifact');
		if (!$MonitorElementObject->clearMonitor($this->getID())) {
			$this->setError(_('Error deleting monitor')._(': ').db_error());
			db_rollback();
			ArtifactStorage::instance()->rollback();
			return false;
		}
		$res = db_query_params ('DELETE FROM artifact WHERE artifact_id=$1',
					array ($this->getID())) ;
		if (!$res) {
			$this->setError(_('Error deleting artifact')._(': ').db_error());
			db_rollback();
			ArtifactStorage::instance()->rollback();
			return false;
		}

		if ($this->getStatusID() == 1) {
			$res = db_query_params ('UPDATE artifact_counts_agg SET count=count-1,open_count=open_count-1
				WHERE group_artifact_id=$1',
						array ($this->getID())) ;
			if (!$res) {
				$this->setError(_('Error updating artifact counts')._(': ').db_error());
				db_rollback();
				ArtifactStorage::instance()->rollback();
				return false;
			}
		} elseif ($this->getStatusID() == 2) {
			$res = db_query_params ('UPDATE artifact_counts_agg SET count=count-1
				WHERE group_artifact_id=$1',
						array ($this->getID())) ;
			if (!$res) {
				$this->setError(_('Error updating artifact counts')._(': ').db_error());
				db_rollback();
				ArtifactStorage::instance()->rollback();
				return false;
			}
		}

		db_commit();
		ArtifactStorage::instance()->commit();
		return true;
	}

	/**
	 * setMonitor - user can monitor this artifact.
	 *
	 * @return	bool	Always false - always use the getErrorMessage() for feedback
	 */
	function setMonitor() {
		global $feedback;
		if (session_loggedin()) {
			$user_id = user_getid();
		} else {
			$this->setError(_('You can only monitor if you are logged in.'));
			return false;
		}

		$MonitorElementObject = new MonitorElement('artifact');
		if (!$this->isMonitoring()) {
			if (!$MonitorElementObject->enableMonitoringByUserId($this->getID(), $user_id)) {
				$this->setError($MonitorElementObject->getErrorMessage());
				return false;
			}
			$feedback = _('Monitoring Started');
			return true;
		} else {
			if (!$MonitorElementObject->disableMonitoringByUserId($this->getID(), $user_id)) {
				$this->setError($MonitorElementObject->getErrorMessage());
				return false;
			}
			$feedback = _('Monitoring Stopped');
			return true;
		}
		return false;
	}

	function isMonitoring() {
		if (!session_loggedin()) {
			return false;
		}
		$MonitorElementObject = new MonitorElement('artifact');
		return $MonitorElementObject->isMonitoredByUserId($this->getID(), user_getid());
	}

	/**
	 * getMonitorIds - array of email addresses monitoring this Artifact.
	 *
	 * @return	array of email addresses monitoring this Artifact.
	 */
	function getMonitorIds() {
		$MonitorElementObject = new MonitorElement('artifact');
		return $MonitorElementObject->getMonitorUsersIdsInArray($this->getID());
	}

	/**
	 * getHistory - returns a result set of audit trail for this support request.
	 *
	 * @return	resource result set.
	 */
	function getHistory() {
		return db_query_params ('SELECT * FROM artifact_history_user_vw WHERE artifact_id=$1 ORDER BY entrydate DESC, id ASC',
					array ($this->getID())) ;
	}

	/**
	 * getMessages - get the list of messages attached to this artifact.
	 *
	 * @param	string		$order
	 * @return	resource	result set.
	 */
	function getMessages($ascending='up') {
		/*
		 * This is necessary because someone committed a change
		 * to this method in FusionForge trunk that accepts 'up'
		 * as default (luckily, it’s the same!) and 'down' as
		 * alternative probability, whereas FusionForge 5.2 has
		 * false as default and true for ascending order, so we
		 * need to check this out and use === to be sure ☹
		 */
		if ($ascending === 'up') {
			$order = 'DESC';
		} elseif ($ascending === true) {
			$order = 'ASC';
		} elseif ($ascending === false) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}
		return db_query_params('SELECT * FROM artifact_message_user_vw WHERE artifact_id=$1 ORDER BY adddate ' . $order . ', id ASC',
		    array($this->getID()));
	}

	/**
	 * getMessage - get a message attached to this artifact.
	 *
	 * @param	int		$msg_id id of the message.
	 * @access	public
	 * @return	resource	database result set.
	 */
	function getMessage($msg_id) {
		if (!$msg_id) {
			return false;
		}
		return db_query_params ('SELECT * FROM artifact_message_user_vw WHERE id=$1',
			array($msg_id));
	}

	/**
	 * getMessageObjects - get an array of message objects.
	 *
	 * @return	array Of ArtifactMessage objects.
	 */
	function &getMessageObjects() {
		$res=$this->getMessages();
		$return = array();
		while ($arr = db_fetch_array($res)) {
			$return[] = new ArtifactMessage($this, $arr);
		}
		return $return;
	}

	/**
	 * getFiles - get array of ArtifactFile's.
	 *
	 * @return	array of ArtifactFile's.
	 */
	function &getFiles() {
		if (!isset($this->files)) {
			$res = db_query_params ('SELECT id,artifact_id,description,filename,filesize,' .
					'filetype,adddate,submitted_by,user_name,realname
					 FROM artifact_file_user_vw WHERE artifact_id=$1',
						array ($this->getID())) ;
			$rows=db_numrows($res);
			if ($rows > 0) {
				for ($i=0; $i < $rows; $i++) {
					$this->files[$i]=new ArtifactFile($this,db_fetch_array($res));
				}
			} else {
				$this->files=array();
			}
		}
		return $this->files;
	}

	/**
	 * getRelatedTasks - get array of related tasks
	 *
	 * @return	resource	Database result set
	 */
	function getRelatedTasks() {
		if (!$this->relatedtasks) {
			$this->relatedtasks = db_query_params ('SELECT pt.group_project_id,pt.project_task_id,pt.summary,pt.start_date,pt.end_date,pgl.group_id,pt.status_id,pt.percent_complete,ps.status_name
			FROM project_task pt, project_group_list pgl, project_status ps
			WHERE pt.group_project_id = pgl.group_project_id
                        AND ps.status_id = pt.status_id
                        AND EXISTS (SELECT project_task_id FROM project_task_artifact
				WHERE project_task_id=pt.project_task_id
				AND artifact_id = $1)',
							       array ($this->getID())) ;
		}
		return $this->relatedtasks;
	}

	/**
	 * addMessage - attach a text message to this Artifact.
	 *
	 * @param	string	$body		The $string message being attached.
	 * @param	bool	$by		Email $string address of message creator.
	 * @param	bool	$send_followup	Whether $bool to email out a followup.
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789, 'nopermcheck' => true, 'nonotice' => true)
	 *
	 * @return	bool	success.
	 */
	function addMessage($body,$by=false,$send_followup=false,$importData = array()) {
		if (!$body) {
			$this->setMissingParamsError();
			return false;
		}
		if (!forge_check_perm ('tracker',$this->ArtifactType->getID(),'submit')) {
			$this->setError(_('You are not currently allowed to submit items to this tracker.'));
			return false;
		}

		if (isset($importData['nonotice']) && $importData['nonotice']) {
			$sendNotice = false;
		} else {
			$sendNotice = true;
		}

		if(array_key_exists('user', $importData)){
				$user_id = $importData['user'];
				$user = user_get_object($user_id);
				if (!$user || !is_object($user)) {
					$this->setError('Error: Logged In User But Could Not Get User Object');
					return false;
				}
				$by=$user->getEmail();
		} else {
			if (session_loggedin()) {
				$user_id=user_getid();
				$user = user_get_object($user_id);
				if (!$user || !is_object($user)) {
					$this->setError('Error: Logged In User But Could Not Get User Object');
					return false;
				}
				//	we'll store this email even though it will likely never be used -
				//	since we have their correct user_id, we can join the USERS table to get email
				$by=$user->getEmail();
			} else {
				$user_id=100;
				if (!$by || !validate_email($by)) {
					$this->setMissingParamsError();
					return false;
				}
			}
		}
		if(array_key_exists('time', $importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		$res = db_query_params ('INSERT INTO artifact_message (artifact_id,submitted_by,from_email,adddate,body) VALUES ($1,$2,$3,$4,$5)',
					array ($this->getID(),
					       $user_id,
					       $by,
					       $time,
					       htmlspecialchars($body))) ;

		$this->updateLastModifiedDate($importData);

		if ($send_followup && $sendNotice) {
			$this->mailFollowupEx($time, 2, false);
		}
		return $res;
	}

	/**
	 * addHistory - add an entry to audit trail.
	 *
	 * @param	string	$field_name	The name of the field in the database being modified.
	 * @param	string	$old_value	The former value of this field.
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789, 'nopermcheck' => true, 'nonotice' => true)
	 * @access	private
	 * @return	boolean	success.
	 */
	function addHistory($field_name,$old_value, $importData = array()) {
		if (array_key_exists('user', $importData)){
			$user = $importData['user'];
		} else {
			$user = ((session_loggedin()) ? user_getid() : 100);
		}
		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		return db_query_params ('INSERT INTO artifact_history(artifact_id,field_name,old_value,mod_by,entrydate) VALUES ($1,$2,$3,$4,$5)',
					array ($this->getID(),
					       $field_name,
					       $old_value,
					       $user,
					       $time)) ;
	}

	/**
	 * setStatus - set the status of this artifact.
	 *
	 * @param	int	$status_id The artifact status ID.
	 * @param	int|bool	$closingTime Closing date if status = 1
	 *
	 * @return	bool	success.
	 */
	function setStatus($status_id, $closingTime=false) {
		db_begin();
		$qpa = db_construct_qpa(array(), 'UPDATE artifact SET status_id=$1', array ($status_id)) ;
		if ($closingTime && $status_id != 1) {
			$time=$closingTime;
			$qpa = db_construct_qpa($qpa, ', close_date=$1 ', array ($time)) ;
		}
		$qpa = db_construct_qpa($qpa,
					 'WHERE artifact_id=$1 AND group_artifact_id=$2',
					 array ($this->getID(), $this->ArtifactType->getID())) ;
		$result=db_query_qpa($qpa);

		if (!$result || db_affected_rows($result) < 1) {
			$this->setError('Error - update failed!'.db_error());
			db_rollback();
			return false;
		} else {
			if (!$this->fetchData($this->getID())) {
				db_rollback();
				return false;
			}
		}

		//commiting changes
		db_commit();
		return true;
	}

	/**
	 * update - update the fields in this artifact.
	 *
	 * @param	int	$priority		The artifact priority.
	 * @param	int	$status_id		The artifact status ID.
	 * @param	int	$assigned_to		The person to which this artifact is to be assigned.
	 * @param	string	$summary		The artifact summary.
	 * @param	int	$canned_response	The canned response.
	 * @param	string	$details		Attaching another comment.
	 * @param	int	$new_artifact_type_id	Allows you to move an artifact to another type.
	 * @param	array	$extra_fields		Array of extra fields like: array(15=>'foobar',22=>'1');
	 * @param	string  $description		The description.
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789, 'nopermcheck' => true, 'nonotice' => true)
	 * @return	boolean	success.
	 */
	function update($priority,$status_id,
		$assigned_to,$summary,$canned_response,$details,$new_artifact_type_id,
		$extra_fields=array(), $description='', $importData=array()) {

		if (array_key_exists('user', $importData)){
			$user = $importData['user'];
		}
		if (isset($importData['nopermcheck']) && $importData['nopermcheck']) {
			$permCheck = false;
		} else {
			$permCheck = true;
		}

		if (isset($importData['nonotice']) && $importData['nonotice']) {
			$sendNotice = false;
		} else {
			$sendNotice = true;
		}
		/*
			Field-level permission checking
		*/
		if ($permCheck) {
			if (!forge_check_perm ('tracker', $this->ArtifactType->getID(), 'manager')) {
				// Non-managers cannot modify these fields
				$priority=$this->getPriority();
				$summary=htmlspecialchars_decode($this->getSummary());
				$description=htmlspecialchars_decode($this->getDetails());
				$canned_response=100;
				$new_artifact_type_id=$this->ArtifactType->getID();
				$autoAssignField = $this->getArtifactType()->getAutoAssignField();
				if ($autoAssignField!=100) {
					$ef = new ArtifactExtraField($this->getArtifactType(),$autoAssignField);
					if (!$ef || !is_object($ef)) {
						exit_error(_('Unable to create ArtifactExtraField Object'),'tracker');
					} elseif ($ef->isError()) {
						exit_error($ef->getErrorMessage(),'tracker');
					} else {
						$efe = new ArtifactExtraFieldElement($ef,$extra_fields[$autoAssignField]);
						if (!$efe || !is_object($efe)) {
							exit_error(_('Unable to create ArtifactExtraFieldElement Object'),'tracker');
						} elseif ($efe->isError()) {
							exit_error($efe->getErrorMessage(),'tracker');
						} else {
							$assigned_to = $efe->getAutoAssignto();
						}
					}
				} else {
					$assigned_to = $this->getAssignedTo();
				}
				if (!forge_check_perm ('tracker', $this->ArtifactType->getID(), 'tech')) {
					$this->setPermissionDeniedError();
					return false;
				}
			}
		}
		//
		//	They may be using an extra field "status" box so we have to remap
		//	the status_id based on the extra field - this keeps the counters
		//	accurate
		//
		if (count($extra_fields) > 0) {
			$status_id=$this->ArtifactType->remapStatus($status_id,$extra_fields);
		}
		if (!$this->getID()) {
			$this->setMissingParamsError('ID');
			return false;
		}
		if (!$assigned_to) {
			$this->setMissingParamsError(_('Assigned to'));
			return false;
		}
		if (!$status_id) {
			$this->setMissingParamsError(_('State'));
			return false;
		}
		if (!$canned_response) {
			$this->setMissingParamsError(_('Canned Response'));
			return false;
		}
		if (!$new_artifact_type_id) {
			$this->setMissingParamsError(_('Data Type'));
			return false;
		}

		// Check that assigned_to is a tech for the tracker
		if ($assigned_to != 100 && $permCheck) {
			if (!forge_check_perm_for_user ($assigned_to, 'tracker', $this->ArtifactType->getID(), 'tech')) {
				$this->setError(_("Invalid assigned person: must be a technician"));
				return false;
			}
		}

		// Array to record which properties were changed
		$changes = array();
		$update  = false;

		db_begin();

		//
		//	Get a lock on this row in the database
		//
		db_query_params ('SELECT * FROM artifact WHERE artifact_id=$1 FOR UPDATE', array ($this->getID())) ;
		$artifact_type_id = $this->ArtifactType->getID();
		//
		//	Attempt to move this Artifact to a new ArtifactType
		//	need to instantiate new ArtifactType obj and test perms
		//
		if ($new_artifact_type_id != $artifact_type_id) {
			$newArtifactType= new ArtifactType($this->ArtifactType->getGroup(), $new_artifact_type_id);
			if (!is_object($newArtifactType) || $newArtifactType->isError()) {
				$this->setError(_('Could not move to new Artifact Type'). $newArtifactType->getErrorMessage());
				db_rollback();
				return false;
			}
			//	do they have perms for new ArtifactType?
			if (!forge_check_perm ('tracker', $newArtifactType->getID(), 'manager') && $permCheck) {
				$this->setPermissionDeniedError();
				db_rollback();
				return false;
			}

			// Add a message to explain that the tracker was moved.
			$message = sprintf(_('Moved from %1$s to %2$s'),
                               $this->ArtifactType->getName(),
                               $newArtifactType->getName());
			$this->addHistory('type', $this->ArtifactType->getName(), $importData);
			$this->addMessage($message,'',0, $importData);

			// Fake change to send a mail when moved.
			$changes['Type'] = 1;

			// Try to remap extra_fields values when possible.
			// If there is an extra_field with the same alias
			// and if the value exist in the new one, then recode
			// the value to keep it.
			$new_extra_fields = array();
			$ef = $this->ArtifactType->getExtraFields();
			$ef_new = $newArtifactType->getExtraFields();
			foreach($extra_fields as $extra_id => $value) {
				$alias = preg_replace('/^@/', '', $ef[$extra_id]['alias']);
				$type  = $ef[$extra_id]['field_type'];

				// Search if there is an extra field with the same alias.
				$new_id = 0;
				foreach($ef_new as $id => $arr) {
					if (preg_replace('/^@/', '', $arr['alias']) == $alias) {
						$new_id = $id;
					}
				}

				// If we found one, copy for simple fields or
				// search if there is the same value.
				if ($new_id) {
					if ($type == ARTIFACT_EXTRAFIELDTYPE_TEXT ||
						$type == ARTIFACT_EXTRAFIELDTYPE_INTEGER ||
						$type == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA ||
						$type == ARTIFACT_EXTRAFIELDTYPE_RELATION) {
						$new_extra_fields[$new_id] = $value;
					} else {
						$values = $newArtifactType->getExtraFieldElements($new_id);
						if (is_array($value)) {
							foreach($value as $v) {
								$v = $this->ArtifactType->getElementName($v);
								foreach($values as $ev) {
									if ($ev['element_name'] == $v) {
										$new_extra_fields[$new_id][] = $ev['element_id'];
									}
								}
							}
						} else {
							$value = $this->ArtifactType->getElementName($value);
							foreach($values as $ev) {
								if ($ev['element_name'] == $value) {
									$new_extra_fields[$new_id] = $ev['element_id'];
								}
							}
						}
					}
				}
			}

			// Special case if moving to a tracker with custom status (previous has not).
			$custom_status_id = $newArtifactType->getCustomStatusField();
			if ($custom_status_id && !$new_extra_fields[$custom_status_id]) {
				$atw = new ArtifactWorkflow($newArtifactType, $custom_status_id);
				$nodes = $atw->getNextNodes(100);
				if ($nodes) {
					$new_extra_fields[$custom_status_id] = $nodes[0];
				}
			}

			$extra_fields = $new_extra_fields;

			$res = db_query_params ('DELETE FROM artifact_extra_field_data WHERE artifact_id=$1',
						array ($this->getID()));
			if (!$res) {
				$this->setError(_('Removal of old artifact_extra_field_data failed: ').db_error());
				db_rollback();
				return false;
			}

			// Check that assigned_to is a tech in the new tracker
			if ($assigned_to != 100 && $permCheck) {
				if (!forge_check_perm ('tracker', $newArtifactType->getID(), 'tech')) {
					$assigned_to = 100;
				}
			}

			//can't send a canned response when changing ArtifactType
			$canned_response=100;
			$this->ArtifactType =& $newArtifactType;
			$update = true;
		}

		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'UPDATE artifact SET');

		//
		//	handle audit trail
		//
		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}

		if ($this->getStatusID() != $status_id) {
			$this->addHistory('status_id',$this->getStatusID(), $importData);
			$qpa = db_construct_qpa($qpa, ' status_id=$1,', array($status_id));
			$changes['status'] = 1;
			$update = true;

			if ($status_id != 1) {
				$qpa = db_construct_qpa($qpa, ' close_date=$1,', array($time));
			} else {
			  $qpa = db_construct_qpa($qpa, ' close_date=$1,', array(0));
			}
			$this->addHistory('close_date', $this->getCloseDate(), $importData);
		}
		if ($this->getPriority() != $priority) {
			$this->addHistory('priority',$this->getPriority(), $importData);
			$qpa = db_construct_qpa($qpa, ' priority=$1,', array($priority));
			$changes['priority'] = 1;
			$update = true;
		}

		if ($this->getAssignedTo() != $assigned_to) {
			$this->addHistory('assigned_to',$this->getAssignedTo(), $importData);
			$qpa = db_construct_qpa($qpa, ' assigned_to=$1,', array($assigned_to));
			$changes['assigned_to'] = 1;
			$update = true;
		}
		if ($summary && ($this->getSummary() != htmlspecialchars($summary))) {
			$this->addHistory('summary', $this->getSummary(), $importData);
			$qpa = db_construct_qpa($qpa, ' summary=$1,', array(htmlspecialchars($summary)));
			$changes['summary'] = 1;
			$update = true;
		}
		if ($description && ($this->getDetails() != htmlspecialchars($description))) {
			$this->addHistory('details', $this->getDetails(), $importData);
			$qpa = db_construct_qpa($qpa, ' details=$1,', array(htmlspecialchars($description)));
			$changes['details'] = 1;
			$update = true;
		}
		if ($details) {
			$this->addMessage($details, '' ,0 , $importData);
			$changes['details'] = 1;
			$send_message=true;
		}

		/*
			Finally, update the artifact itself
		*/
		if ($update){
		  $qpa = db_construct_qpa($qpa, ' group_artifact_id=$1
				                              WHERE artifact_id=$2 AND group_artifact_id=$3',
                                      array($new_artifact_type_id,
                                            $this->getID(), $artifact_type_id));
		  $result = db_query_qpa($qpa);

			if (!$result || db_affected_rows($result) < 1) {
				$this->setError(_('Update failed').db_error());
				db_rollback();
				return false;
			} else {
				if (!$this->fetchData($this->getID())) {
					db_rollback();
					return false;
				}
			}
		}

		//extra field handling
		$update=true;
		if (!$this->updateExtraFields($extra_fields, $changes, $importData)) {
//TODO - see if anything actually did change
			db_rollback();
			return false;
		}

		/*
			handle canned responses

			Instantiate ArtifactCanned and get the body of the message
		*/
		if ($canned_response != 100) {
			//don't care if this response is for this group - could be hacked
			$acr=new ArtifactCanned($this->ArtifactType,$canned_response);
			if (!$acr || !is_object($acr)) {
				$this->setError(_('Could Not Create Canned Response Object'));
			} elseif ($acr->isError()) {
				$this->setError($acr->getErrorMessage());
			} else {
				$body = $acr->getBody();
				if ($body) {
					if (!$this->addMessage(util_unconvert_htmlspecialchars($body),'',0,$importData)) {
						db_rollback();
						return false;
					} else {
						$send_message=true;
					}
				} else {
					$this->setError(_('Unable to Use Canned Response'));
					return false;
				}
			}
		}

		if ($update || $send_message){
			if (!empty($changes) && $sendNotice) {
				// Send the email with changes
				$this->mailFollowupEx($time, 2, false, $changes);
			}
			db_commit();
			return true;
		} else {
			//nothing changed, so cancel the transaction
			$this->setError(_('Nothing Changed - Update Cancelled'));
			db_rollback();
			return false;
		}

	}

	/**
	 * updateLastModifiedDate - update the last_modified_date attribute of this artifact.
	 * @param	array	$importData	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789)
	 *
	 * @return	bool	true on success / false on failure
	 */
	function updateLastModifiedDate($importData = array()) {
		if (array_key_exists('time',$importData)){
			$time = $importData['time'];
		} else {
			$time = time();
		}
		$res = db_query_params ('UPDATE artifact SET last_modified_date=$1 WHERE artifact_id=$2',
			array ($time, $this->getID()));
		return (!$res);
	}

	/**
	 * assignToMe - assigns this artifact to current user
	 *
	 * @return	bool	true on success / false on failure
	 */
	function assignToMe() {
		if (!session_loggedin() || !(forge_check_perm('tracker', $this->ArtifactType->getID(), 'manager') || forge_check_perm('tracker', $this->ArtifactType->getID(), 'tech'))) {
			$this->setPermissionDeniedError();
			return false;
		}

		$user_id = user_getid();
		$res = db_query_params ('UPDATE artifact SET assigned_to=$1 WHERE artifact_id=$2',
								array ($user_id, $this->getID())) ;
		if (!$res) {
			$this->setError(_('Error updating assigned_to in artifact: ').db_error());
			return false;
		}
		$this->fetchData($this->getID());

		return true;
	}

	/**
	 * updateExtraFields - updates the extra data elements for this artifact
	 * e.g. the extra fields created and defined by the admin.
	 *
	 * @param	array	Array of extra fields like: array(15=>'foobar',22=>'1');
	 * @param	array	Array where changes to the extra fields should be logged
	 * @param	array	Array of data to change submitter and time of submit like:
	 *						array('user' => 127, 'time' => 1234556789)
	 *
	 * @return	bool	true on success / false on failure
	 */
	function updateExtraFields($extra_fields, &$changes, $importData = array()){
/*
	This is extremely complex code - we have take the passed array
	and see if we need to insert it into the db, and may have to
	add history rows for the audit trail

	start by getting all the available extra fields from ArtifactType
		For each field from ArtifacType, check the passed array -
			This prevents someone from passing bogus extra field entries - they will be ignored
			if the passed entry is blank, may have to force a default value
			if the passed array is different from the existing data in db,
				delete old entry and insert new entries, along with possible audit trail
			else
				skip it and continue to next item

*/
		if (isset($importData['nopermcheck']) && $importData['nopermcheck']) {
			$permCheck = false;
		} else {
			$permCheck = true;
		}
		$update = false;

		//get a list of extra fields for this artifact_type
		$ef = $this->ArtifactType->getExtraFields();
		$efk=array_keys($ef);

		if (empty($extra_fields) && empty($ef)) {
			return true;
		}

		$status_changed = false;

		// If there is a status field, then check against the workflow.
		// Unless if we change type.
		if ((!isset($changes['Type']) || !$changes['Type']) && $permCheck) {
			for ($i=0; $i<count($efk); $i++) {
				$efid=$efk[$i];
				$type=$ef[$efid]['field_type'];
				if ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					// Get previous value.
					$res = db_query_params ('SELECT field_data FROM artifact_extra_field_data
							WHERE artifact_id=$1 AND extra_field_id=$2',
						array($this->getID(),
							$efid));
					$from_status = (db_numrows($res)>0) ? db_result($res,0,'field_data') : 100;
					$to_status = $extra_fields[$efid];
						if ($from_status != $to_status) {
						$status_changed = true;
						$atw = new ArtifactWorkflow($this->ArtifactType, $efid);
						if (!$atw->checkEvent($from_status, $to_status)) {
							$this->setError('Workflow error: You are not authorized to change the Status ('.$from_status.' => '.$to_status.')');
							return false;
						}
					}
				}
			}
		}

		if ($status_changed) {
			$CSFid = $this->ArtifactType->getCustomStatusField();
			$wf = new ArtifactWorkflow($this->ArtifactType, $CSFid);
			$rf = $wf->getRequiredFields($from_status, $to_status);
		} else {
			$rf = array();
		}

		//now we'll update this artifact for each extra field
		for ($i=0; $i<count($efk); $i++) {
			$efid=$efk[$i];
			$type=$ef[$efid]['field_type'];

			// check required fields
			if (($ef[$efid]['is_required'] || in_array($efid, $rf)) && $permCheck) {
				if (!array_key_exists($efid, $extra_fields)) {
					if ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
						$this->setError(_('Status Custom Field Must Be Set'));
					}
					else {
						$this->setMissingParamsError($ef[$efid]['field_name']);
					}
					return false;
				}
				else {
					if ($extra_fields[$efid] === '') {
						if ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
							$this->setError(_('Status Custom Field Must Be Set'));
						}
						else {
							$this->setMissingParamsError($ef[$efid]['field_name']);
						}
						return false;
					}
					else {
						if (($type == ARTIFACT_EXTRAFIELDTYPE_SELECT || $type == ARTIFACT_EXTRAFIELDTYPE_RADIO) &&
							$extra_fields[$efid] == '100') {
								$this->setMissingParamsError($ef[$efid]['field_name']);
								return false;
						}
						elseif (($type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT || $type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) &&
								(count($extra_fields[$efid]) == 1 && $extra_fields[$efid][0] == '100')) {
							$this->setMissingParamsError($ef[$efid]['field_name']);
							return false;
						}
					}
				}
			}

			// check parent field
			if (($type == ARTIFACT_EXTRAFIELDTYPE_SELECT ||
					$type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT ||
					$type == ARTIFACT_EXTRAFIELDTYPE_RADIO ||
					$type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX)
					&& $permCheck) {
				$allowed = false;
				if (!is_null($ef[$efid]['parent']) && !empty($ef[$efid]['parent']) && $ef[$efid]['parent']!='100') {
					$aefParentId = $ef[$efid]['parent'];
					$selectedElmnts = (isset($extra_fields[$aefParentId]) ? $extra_fields[$aefParentId] : '');
					$aef = new ArtifactExtraField($this->ArtifactType,$efid);
					$allowed = $aef->getAllowedValues($selectedElmnts);
				}

				if (is_array($allowed)) {
					if (($type == ARTIFACT_EXTRAFIELDTYPE_RADIO) || ($type==ARTIFACT_EXTRAFIELDTYPE_SELECT)) {
						if ($extra_fields[$efid]!='100' && !in_array($extra_fields[$efid],$allowed)) {
							//$aef = new ArtifactExtraField($this->ArtifactType,$efid);
							$aefe = new ArtifactExtraFieldElement($aef,$extra_fields[$efid]);
							$this->setError(sprintf(_('"%1$s" value of the field "%2$s", is not allowed by "%3$s" field values'), $aefe->getName(), $ef[$efid]['field_name'], $ef[$aefParentId]['field_name']));
							return false;
						}
					} else {
						if (is_array($extra_fields[$efid])) {
							//$aef = new ArtifactExtraField($this->ArtifactType,$efid);
							foreach ($extra_fields[$efid] as $val) {
								if ($val!='100' && !in_array($val,$allowed)) {
									$aefe = new ArtifactExtraFieldElement($aef,$val);
									$this->setError(sprintf(_('"%1$s" value of the field "%2$s", is not allowed by "%3$s" field values'), $aefe->getName(), $ef[$efid]['field_name'], $ef[$aefParentId]['field_name']));
									return false;
								}
							}
						}
					}
				}
			}


			// check pattern for text fields
			if ($type == ARTIFACT_EXTRAFIELDTYPE_TEXT &&
					!empty($ef[$efid]['pattern']) &&
					!empty($extra_fields[$efid]) &&
					!preg_match('/'.$ef[$efid]['pattern'].'/', $extra_fields[$efid]) &&
					$permCheck) {
				$this->setError(sprintf(_("Field %s doesn't match the pattern."), $ef[$efid]['field_name']));
				return false;
			}
//
//	Force each field to have some value if it is a numeric field
//	text fields will just be purged and skipped
//
			if (!array_key_exists($efid, $extra_fields) || $extra_fields[$efid] === '') {
				if (($type == ARTIFACT_EXTRAFIELDTYPE_SELECT) || ($type == ARTIFACT_EXTRAFIELDTYPE_RADIO)) {
					$extra_fields[$efid]='100';
				} elseif (($type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT) || ($type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX)) {
					$extra_fields[$efid]=array('100');
				} else {
					db_query_params ('DELETE FROM artifact_extra_field_data WHERE artifact_id=$1 AND extra_field_id=$2',
								   array ($this->getID(),
									  $efid)) ;
					continue;
				}
			}

			//
			//	get the old rows of data
			//
			$resd = db_query_params ('SELECT * FROM artifact_extra_field_data WHERE artifact_id=$1 AND extra_field_id=$2',
						 array ($this->getID(),
							$efid)) ;
			$rows=db_numrows($resd);
			if ($resd && $rows) {
//
//POTENTIAL PROBLEM - no entry was there before, but adding one now - may need history
//
				//
				//	Compare for history purposes
				//

				// these types have arrays associated to them, so they need
				// special handling to check for differences
				if ($type == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT || $type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) {
					// check the differences between the old values and the new values
					$old_values = util_result_column_to_array($resd,"field_data");

					$added_values = array_diff($extra_fields[$efid], $old_values);
					$deleted_values = array_diff($old_values, $extra_fields[$efid]);

					if (!empty($added_values) || !empty($deleted_values))	{	// there are differences...
						$field_name = $ef[$efid]['field_name'];
						if (!preg_match('/^@/', $ef[$efid]['alias'])) {
							$changes["extra_fields"][$efid] = 1;
						}

						$this->addHistory($field_name, $this->ArtifactType->getElementName(array_reverse($old_values)),$importData);
						$update = true;

						db_query_params ('DELETE FROM artifact_extra_field_data WHERE	artifact_id=$1 AND extra_field_id=$2',
									   array ($this->getID(),
										  $efid)) ;
					} else {
						continue;
					}
				} elseif (db_result($resd,0,'field_data') == htmlspecialchars($extra_fields[$efid])) {
					//element did not change
					continue;
				} else {
					//element DID change - do a history entry
					$field_name = $ef[$efid]['field_name'];
					if (!preg_match('/^@/', $ef[$efid]['alias'])) {
						$changes["extra_fields"][$efid] = 1;
					}
					db_query_params ('DELETE FROM artifact_extra_field_data WHERE	artifact_id=$1 AND extra_field_id=$2',
								   array ($this->getID(),
									  $efid)) ;

					// Adding history with previous value.
					if (($type == ARTIFACT_EXTRAFIELDTYPE_SELECT) || ($type == ARTIFACT_EXTRAFIELDTYPE_RADIO) || ($type == ARTIFACT_EXTRAFIELDTYPE_STATUS)) {
						$this->addHistory($field_name,$this->ArtifactType->getElementName(db_result($resd,0,'field_data')),$importData);
					} else {
						$this->addHistory($field_name, db_result($resd,0,'field_data'),$importData);
					}
					$update = true;
				}
			} else {

//no history for this extra field exists

			}

			//
			// Some rewrite & consistency checks on the relation type field.
			//
			// 1) Convert syntax [#NNN] to NNN
			// 2) Allow multiple spaces as separator.
			// 3) Ensure that only integers are given.
			// 4) Ensure that id corresponds to valid tracker id.
			//
			if ($type == ARTIFACT_EXTRAFIELDTYPE_RELATION) {
				$value = preg_replace('/\[\#(\d+)\]/', "\\1", trim($extra_fields[$efid]));
				$value = preg_replace('/\\s+/', ' ', $value);
				$new = '';
				foreach (explode(' ',$value) as $id) {
					if (preg_match('/^(\d+)$/', $id)) {
						// Control that the id is present in the db

						$res = db_query_params ('SELECT artifact_id FROM artifact WHERE artifact_id=$1',
			array($id));
						if (db_numrows($res) == 1) {
							$new .= $id.' ';
						} else {
							$this->setError('Illegal id '.$id.', it\'s not a valid tracker id for field: '.$ef[$efid]['field_name'].'.'); // @todo: lang
							return false;
						}
					} else {
						$this->setError('Illegal value '.$id.', only trackers id are allowed for field: '.$ef[$efid]['field_name'].'.'); // @todo: lang
						return false;
					}
				}
				$extra_fields[$efid] = trim($new);
			}

			// Ensure that only integer are allowed for type ARTIFACT_EXTRAFIELDTYPE_INTEGER
			if ($type == ARTIFACT_EXTRAFIELDTYPE_INTEGER) {
				$extra_fields[$efid] = trim($extra_fields[$efid]);
				if (!preg_match('/^[-+]?(\d+)$/', $extra_fields[$efid])) {
					$this->setError('Illegal value '.$extra_fields[$efid].' for field '.$ef[$efid]['field_name'].': Only integer is allowed.');
					return false;
				}
				if ($extra_fields[$efid] < -2147483648 || $extra_fields[$efid] > 2147483647) {
					$this->setError('Illegal value '.$extra_fields[$efid].' for field '.$ef[$efid]['field_name'].': Integer out of range (-2147483648 to +2147483647).');
					return false;
				}
				$extra_fields[$efid] = intval($extra_fields[$efid]);
			}

			//
			//	See if anything was even passed for this extra_field_id
			//
			if ($extra_fields[$efid] === '') {
				//nothing in field to update - text fields may be blank
			} else {
				//determine the type of field and whether it should have multiple rows supporting it
				$type=$ef[$efid]['field_type'];
				if (($type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) || ($type==ARTIFACT_EXTRAFIELDTYPE_MULTISELECT)) {
					$multi_rows=true;
					$count=count($extra_fields[$efid]);
					for ($fin=0; $fin<$count; $fin++) {
						$res = db_query_params ('INSERT INTO artifact_extra_field_data (artifact_id,extra_field_id,field_data) VALUES ($1,$2,$3)',
									array ($this->getID(),
									       $efid,
									       $extra_fields[$efid][$fin])) ;
						if (!$res) {
							$this->setError(db_error());
							return false;
						}
					}
				} else {
					$multi_rows=false;
					$count=1;
					$res = db_query_params ('INSERT INTO artifact_extra_field_data (artifact_id,extra_field_id,field_data) VALUES ($1,$2,$3)',
								array ($this->getID(),
								       $efid,
								       htmlspecialchars($extra_fields[$efid]))) ;
					if (!$res) {
						$this->setError(db_error());
						return false;
					}
					$update = true;
				}
			}
		}
		unset($this->extra_field_data);

		if ($update)
			$this->updateLastModifiedDate($importData);

		return true;
	}

	/**
	 * getExtraFieldData - get an array of data for the extra fields associated with this artifact
	 *
	 * @return	array	array of data
	 */
	function &getExtraFieldData() {
		if (!isset($this->extra_field_data)) {
			$this->extra_field_data = array();
			$res = db_query_params ('SELECT * FROM artifact_extra_field_data WHERE artifact_id=$1 ORDER BY extra_field_id',
						array ($this->getID())) ;
			$ef = $this->ArtifactType->getExtraFields();
			while ($arr = db_fetch_array($res)) {
				$type=$ef[$arr['extra_field_id']]['field_type'];
				if (($type == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) || ($type==ARTIFACT_EXTRAFIELDTYPE_MULTISELECT)) {
					//accumulate a sub-array of values in cases where you may have multiple rows
					if (!array_key_exists($arr['extra_field_id'], $this->extra_field_data) || !is_array($this->extra_field_data[$arr['extra_field_id']])) {
						$this->extra_field_data[$arr['extra_field_id']] = array();
					}
					$this->extra_field_data[$arr['extra_field_id']][]=$arr['field_data'];
				} else {
					$this->extra_field_data[$arr['extra_field_id']] = $arr['field_data'];
				}
			}
		}
		return $this->extra_field_data;
	}

	/**
	 * marker - adds the > symbol to fields that have been modified for the email message
	 *
	 *
	 */
	function marker($prop_name,$changes,$extra_field_id=0) {
		if ($prop_name == 'extra_fields' && isset($changes[$prop_name][$extra_field_id])) {
			return '>';
		} elseif ($prop_name != 'extra_fields' && isset($changes[$prop_name])) {
			return '>';
		} else {
			return '';
		}
	}

	/**
	 * mailFollowupEx - send out an email update for this artifact.
	 *
	 * @param	time_t	$tm	Time of the change
	 * @param	int	$type	(1) initial/creation (2) update
	 * @param	array	$more_addresses	Array of additional addresses to mail to
	 * @param	array	$changes	Array of fields changed in this update
	 * @access	private
	 * @return	boolean	success.
	 */
	function mailFollowupEx($tm, $type, $more_addresses = false, $changes='') {

		$monitor_ids = array();

		if (!$changes) {
			$changes=array();
		}

		$sess = session_get_user();
		$name = util_unconvert_htmlspecialchars($this->ArtifactType->getName());
		$body = $this->ArtifactType->Group->getUnixName() . '-' . $name .' '. $this->getStringID();

		if ($type == 1) {
			$body .= ' was opened at '.date('Y-m-d H:i', $this->getOpenDate());
		} elseif ($type == 3) {
			$body .= ' was deleted at '.date('Y-m-d H:i', time());
		} else {
			$body .= ' was changed at '.date('Y-m-d H:i', $tm);
		}
		if ($sess) {
			$body .= ' by ' . $sess->getRealName();
		}

		if ($type == 1 || $type == 2) {
			$body .= "\nYou can respond by visiting: ".
				"\n".util_make_url ('/tracker/?func=detail&atid='. $this->ArtifactType->getID() .
					    "&aid=". $this->getID() .
					    "&group_id=". $this->ArtifactType->Group->getID());
			if (false) {  // currently not working
				$body .=
				"\nOr by replying to this e-mail entering your response between the following markers: ".
				"\n".ARTIFACT_MAIL_MARKER.
				"\n(enter your response here, only in plain text format)".
				"\n".ARTIFACT_MAIL_MARKER;
			}
			$body .= "\n";
		}

		$body .= "\n".$this->marker('status',$changes).
			 "Status: ". $this->getStatusName() ."\n".
			$this->marker('priority',$changes).
			 "Priority: ". $this->getPriority() ."\n".
			"Submitted By: ". $this->getSubmittedRealName() .
			" (". $this->getSubmittedUnixName(). ")"."\n".
			$this->marker('assigned_to',$changes).
			 "Assigned to: ". $this->getAssignedRealName() .
			 " (". $this->getAssignedUnixName(). ")"."\n".
			$this->marker('summary',$changes).
			 "Summary: ". util_unconvert_htmlspecialchars($this->getSummary())." \n";

		// Now display the extra fields
		$efd = $this->getExtraFieldDataText();
		foreach ($efd as $efid => $ef) {
			$body .= $this->marker('extra_fields', $changes, $efid);
			$body .= $ef["name"].": ".htmlspecialchars_decode($ef["value"])."\n";
		}

		$subject='['. $this->ArtifactType->Group->getUnixName() . '-' . $name . ']' . $this->getStringID() .' '. util_unconvert_htmlspecialchars($this->getSummary());

		if ($type > 1) {
			// get all the email addresses that are monitoring this request or the ArtifactType
			$monitor_ids = array_merge($this->getMonitorIds(), $this->ArtifactType->getMonitorIds());
		} else {
			// initial creation, we just get the users monitoring the ArtifactType
			$monitor_ids = $this->ArtifactType->getMonitorIds();
		}

		$emails = array();
		if ($more_addresses) {
			$emails[] = $more_addresses;
		}
		//we don't email the current user
		if ($this->getAssignedTo() != user_getid()) {
			$monitor_ids[] = $this->getAssignedTo();
		}
		if ($this->getSubmittedBy() != user_getid()) {
			$monitor_ids[] = $this->getSubmittedBy();
		}
		//initial submission
		if ($type==1) {
			//if an email is set for this ArtifactType
			//add that address to the BCC: list
			if ($this->ArtifactType->getEmailAddress()) {
				$emails[] = $this->ArtifactType->getEmailAddress();
			}
		} else {
			//update
			if ($this->ArtifactType->emailAll()) {
				$emails[] = $this->ArtifactType->getEmailAddress();
			}
		}

		$body .= "\n\nInitial Comment:".
			"\n".util_unconvert_htmlspecialchars($this->getDetails()) .
			"\n\n----------------------------------------------------------------------";

		if ($type > 1) {
			/*
				Now include the followups
			*/
			$result2=$this->getMessages();

			$rows=db_numrows($result2);

			if ($result2 && $rows > 0) {
				for ($i=0; $i<$rows; $i++) {
					//
					//	for messages posted by non-logged-in users,
					//	we grab the email they gave us
					//
					//	otherwise we use the confirmed one from the users table
					//
					if (db_result($result2,$i,'user_id') == 100) {
						$emails[] = db_result($result2,$i,'from_email');
					} else {
						$monitor_ids[] = db_result($result2,$i,'user_id');
					}

					$body .= "\n\n";
					if ($i == 0) {
						$body .= $this->marker('details',$changes);
					}
					$body .= "Comment By: ". db_result($result2,$i,'realname') . " (".db_result($result2,$i,'user_name').")".
					"\nDate: ". date(_('Y-m-d H:i'),db_result($result2,$i,'adddate')).
					"\n\nMessage:".
					"\n".util_unconvert_htmlspecialchars(db_result($result2,$i,'body')).
					"\n\n----------------------------------------------------------------------";
				}
			}

		}

		$body .= "\n\nYou can respond by visiting: ".
			"\n".util_make_url ('/tracker/?func=detail&atid='. $this->ArtifactType->getID() .
					    "&aid=". $this->getID() .
					    "&group_id=". $this->ArtifactType->Group->getID());

		//only send if some recipients were found
		if (count($emails) < 1 && count($monitor_ids) < 1) {
			return true;
		}

		if (count($monitor_ids) < 1) {
			$monitor_ids=array();
		} else {
			$monitor_ids=array_unique($monitor_ids);
		}

		$from = $this->ArtifactType->getReturnEmailAddress();
		$extra_headers = 'Reply-to: '.$from;

		// load the e-mail addresses of the users
		$users = user_get_objects($monitor_ids);
		if (count($users) > 0) {
			foreach ($users as $user) {
				if ($user->getStatus() == "A") { //we are only sending emails to active users
					$emails[] = $user->getEmail();
				}
			}
		}

		//now remove all duplicates from the email list
		if (count($emails) > 0) {
			$bcc = implode(',',array_unique($emails));
			util_send_message('', $subject, $body, $from, $bcc, '', $extra_headers);
		}

		$this->sendSubjectMsg = $subject;
		$this->sendBodyMsg = $body;

		//util_handle_message($monitor_ids,$subject,$body,$BCC);

		return true;
	}

	/**
	* getExtraFieldDataText - Return the extra fields' data in a human-readable form.
	*
	* @return	array	Array containing field ID => field name and value associated to it for
	*			this artifact
	*/
	function getExtraFieldDataText() {
		// First we get the list of extra fields and the data
		// associated to the fields
		$efs = $this->ArtifactType->getExtraFields();
		$efd = $this->getExtraFieldData();

		$return = array();

		foreach ($efs as $efid => $ef) {
			$name = $ef["field_name"];
			$type = $ef["field_type"];

			// Get the value according to the type
			switch ($type) {

				// for these types, the associated value comes straight
				case ARTIFACT_EXTRAFIELDTYPE_TEXT:
				case ARTIFACT_EXTRAFIELDTYPE_TEXTAREA:
				case ARTIFACT_EXTRAFIELDTYPE_RELATION:
				case ARTIFACT_EXTRAFIELDTYPE_INTEGER:
					if (isset($efd[$efid])) {
						$value = $efd[$efid];
					} else {
						$value = '';
					}
					break;

				// the other types have and ID or an array of IDs associated to them
				default:
					if (isset($efd[$efid])) {
						$value = $this->ArtifactType->getElementName($efd[$efid]);
					} else {
						$value = 'None';
					}
			}

			$return[$efid] = array("name" => $name, "value" => $value, 'type' => $type);
		}

		return $return;
	}

	/**
	 * castVote - Vote on this tracker item or retract the vote
	 * @param	bool	$value	true to cast, false to retract
	 * @return	bool	success (false sets error message)
	 */
	function castVote($value = true) {
		if (!($uid = user_getid()) || $uid == 100) {
			$this->setMissingParamsError(_('User ID not passed'));
			return false;
		}
		if (!$this->ArtifactType->canVote()) {
			$this->setPermissionDeniedError();
			return false;
		}
		$has_vote = $this->hasVote($uid);
		if ($has_vote == $value) {
			/* nothing changed */
			return true;
		}
		if ($value) {
			$res = db_query_params('INSERT INTO artifact_votes (artifact_id, user_id) VALUES ($1, $2)',
						array($this->getID(), $uid));
		} else {
			$res = db_query_params('DELETE FROM artifact_votes WHERE artifact_id=$1 AND user_id=$2',
						array($this->getID(), $uid));
		}
		if (!$res) {
			$this->setError(db_error());
			return false;
		}
		return true;
	}

	/**
	 * hasVote - Check if a user has voted on this tracker item
	 *
	 * @param	int	$uid	user ID (default: current user)
	 * @return	bool	true if a vote exists
	 */
	function hasVote($uid=false) {
		if (!$uid) {
			$uid = user_getid();
		}
		if (!$uid || $uid == 100) {
			return false;
		}
		$res = db_query_params('SELECT * FROM artifact_votes WHERE artifact_id=$1 AND user_id=$2',
					array($this->getID(), $uid));
		return (db_numrows($res) == 1);
	}

       /**
        * getVotes - get number of valid cast and potential votes
        *
        * @return	array	(votes, voters, percent)
        */
	function getVotes() {
		if ($this->votes !== false) {
			return $this->votes;
		}

		$voters = $this->ArtifactType->getVoters();
		unset($voters[0]);	/* just in case */
		unset($voters[100]);	/* need users */
		if (($numvoters = count($voters)) < 1) {
			$this->votes = array(0, 0, 0);
			return $this->votes;
		}

		$res = db_query_params('SELECT COUNT(*) AS count FROM artifact_votes WHERE artifact_id=$1 AND user_id=ANY($2)',
					array($this->getID(), db_int_array_to_any_clause($voters)));
		$db_count = db_fetch_array($res);
		$numvotes = $db_count['count'];

		/* check for invalid values */
		if ($numvotes < 0 || $numvoters < $numvotes) {
			$this->votes = array(-1, -1, 0);
		} else {
			$this->votes = array($numvotes, $numvoters,
				(int)($numvotes * 100 / $numvoters + 0.5));
		}
		return $this->votes;
	}

	function hasRelations() {
		$aid = $this->getID();
		$res = db_query_params('SELECT *
					FROM artifact_extra_field_list, artifact_extra_field_data, artifact_group_list, artifact, groups
					WHERE field_type=9
					AND artifact_extra_field_list.extra_field_id=artifact_extra_field_data.extra_field_id
					AND artifact_group_list.group_artifact_id = artifact_extra_field_list.group_artifact_id
					AND artifact.artifact_id = artifact_extra_field_data.artifact_id
					AND groups.group_id = artifact_group_list.group_id
					AND (field_data = $1 OR field_data LIKE $2 OR field_data LIKE $3 OR field_data LIKE $4)
					ORDER BY artifact_group_list.group_id ASC, name ASC, artifact.artifact_id ASC',
					array($aid,
					      "$aid %",
					      "% $aid %",
					      "% $aid"));
		if (db_numrows($res)>0) {
			return true;
		}
		return false;
	}
}

class ArtifactComparator {
	var $criterion = 'artifact_id' ;
	var $order = 'ASC' ;

	function Compare ($a, $b) {
		if ($this->order == 'DESC') {
			$c = $a ; $a = $b ; $b = $c ;
		}
		switch ($this->criterion) {
		case 'summary':
			$namecmp = strcoll ($a->getSummary(),
					    $b->getSummary()) ;
			if ($namecmp != 0) {
				return $namecmp ;
			}
			break ;
		case 'assigned_to':
			$namecmp = strcoll (user_get_object($a->getAssignedTo())->getRealName(),
					    user_get_object($b->getAssignedTo())->getRealName()) ;
			if ($namecmp != 0) {
				return $namecmp ;
			}
			break ;
		case 'submitted_by':
			$namecmp = strcoll (user_get_object($a->getSubmittedBy())->getRealName(),
					    user_get_object($b->getSubmittedBy())->getRealName()) ;
			if ($namecmp != 0) {
				return $namecmp ;
			}
			break ;
		case 'open_date':
			$a_date = $a->getOpenDate() ;
			$b_date = $b->getOpenDate() ;
			return ($a_date < $b_date) ? -1 : 1;
			break;
		case 'close_date':
			$a_date = $a->getCloseDate() ;
			$b_date = $b->getCloseDate() ;
			return ($a_date < $b_date) ? -1 : 1;
			break;
		case 'last_modified_date':
			$a_date = $a->getLastModifiedDate() ;
			$b_date = $b->getLastModifiedDate() ;
			return ($a_date < $b_date) ? -1 : 1;
			break;
		case 'priority':
			$a_priority = $a->getPriority() ;
			$b_priority = $b->getPriority() ;
			return ($a_priority < $b_priority) ? -1 : 1;
			break;
		case '_votes':
			$a->getVotes();
			$a_votes = $a->votes[0];
			$b->getVotes();
			$b_votes = $b->votes[0];
			return ($a_votes < $b_votes) ? -1 : 1;
			break;
		case '_voters':
			$a->getVotes();
			$a_votes = $a->votes[1];
			$b->getVotes();
			$b_votes = $b->votes[1];
			return ($a_votes < $b_votes) ? -1 : 1;
			break;
		case '_votage':
			$a->getVotes();
			$a_votes = $a->votes[2];
			$b->getVotes();
			$b_votes = $b->votes[2];
			return ($a_votes < $b_votes) ? -1 : 1;
			break;
		default:
			$aa=$a->getExtraFieldDataText();
			$ba=$b->getExtraFieldDataText();
			if(!isset($this->criterion) || empty($this->criterion)) {
				$criterion = 1;
			} else {
				$criterion = $this->criterion;
			}
			$af=$aa[$criterion]['value'];
			$bf=$ba[$criterion]['value'];
			$namecmp = strcoll ($af,$bf) ;
			if ($namecmp != 0) {
				return $namecmp ;
			}
			break ;
		}

		// When in doubt, sort on artifact ID
		$aid = $a->getID() ;
		$bid = $b->getID() ;
		if ($aid == $bid) {
			return 0;
		}
		return ($aid < $bid) ? -1 : 1;
	}
}

function sortArtifactList (&$list, $criterion='name', $order='ASC') {
	$cmp = new ArtifactComparator () ;
	$cmp->criterion = $criterion ;
	$cmp->order = $order ;

	return usort ($list, array ($cmp, 'Compare')) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

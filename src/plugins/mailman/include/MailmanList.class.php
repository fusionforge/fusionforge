<?php
/**
 * FusionForge Mailing Lists Facility
 *
 * Copyright 2003 Guillaume Smet
 * http://fusionforge.org/
 *
 * @version   $Id$
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 * 
 * Portions Copyright 2010 (c) Mélanie Le Bail
 */


/*

   This work is based on Tim Perdue's work on the forum stuff

 */

require_once 'MailmanListDao.class.php';
require_once 'ProjectManager.class.php';
require_once 'UserManager.class.php';
require_once 'common/dao/CodendiDataAccess.class.php';
require_once 'common/system_event/SystemEventManager.class.php';
require_once 'common/system_event/SystemEvent.class.php';

class MailmanList extends Error {
	/**
	 * DAO 
	 *
	 * @var	 MailingListDao   $mailingDAO.
	 */
	var $_mailingDAO;

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array   $dataArray.
	 */
	var $dataArray;

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The mailing list id
	 *
	 * @var int $groupMailmanListId
	 */
	var $groupMailmanListId;

	/**
	 *  Constructor.
	 *
	 * @param	object	The Group object to which this mailing list is associated.
	 * @param	int		The group_list_id.
	 * @param	array		The associative array of data.
	 * @return	boolean	success.
	 */
	function MailmanList($group_id, $groupListId = false, $dataArray = false) {
		$pm = ProjectManager::instance();
		$Group = $pm->getProject($group_id);
		$this->_mailingDAO =& new MailmanListDao(CodendiDataAccess::instance());	
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), 'MailmanList'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('MailmanList:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($groupListId) {
			$this->groupMailmanListId = $groupListId;
			if (!$dataArray || !is_array($dataArray)) {
				if (!$this->fetchData($groupListId)) {
					return false;
				}
			} else {
				$this->dataArray =& $dataArray;
				if ($this->dataArray['group_id'] != $this->Group->getID()) {
					$this->setError(_('Group_id in db result does not match Group Object'));
					$this->dataArray = null;
					return false;
				}
			}
			if (!$this->isPublic()) {
				if (!isLogged()) {
					$this->dataArray = null;
					return false;
				}
			}
		}

		return true;
	}

	/**
	 *	create - use this function to create a new entry in the database.
	 *
	 *	@param	string	The name of the mailing list
	 *	@param	string	The description of the mailing list
	 *	@param	int	Pass (1) if it should be public (0) for private.
	 *
	 *	@return	boolean	success.
	 **/

	function create($listName, $description, $isPublic = '1',$creator_id=false) {
		$current_user=UserManager::instance()->getCurrentUser();
		//
		//	During the group creation, the current user_id will not match the admin's id
		//
		if (!$creator_id) {
			$creator_id=$current_user->getID();
			if(!$current_user->isMember($this->Group->getID(),'A')) {
				exit_permission_denied();
				return false;
			}
		}

		if(!$listName || strlen($listName) < 4) {
			$this->setError(_('Must Provide List Name That Is 4 or More Characters Long'));
			return false;
		}

		$realListName = strtolower($this->Group->getUnixName().'-'.$listName);

		if(!validate_email($realListName.'@'.forge_get_config('lists_host'))) {
			$this->setError(_('Invalid List Name') . ': ' .
					$realListName.'@'.forge_get_config('lists_host'));
			return false;
		}
		$result=&$this->_mailingDAO->searchByName($realListName);

		if ($result->valid()) {
			$this->setError(_('List Already Exists'));
			return false;
		}


		$listPassword = substr(md5($GLOBALS['session_hash'] . time() . rand(0,40000)), 0, 16);
		$result = $this->_mailingDAO->insertNewList($this->Group->getID(), $realListName,$isPublic,$listPassword,$creator_id,'1',$description);
		if (!$result) {
			$this->setError(sprintf(_('Error Creating %1$s'), _('Error Creating %1$s')).db_error());
			return false;
		}

		$this->groupMailmanListId = $result;
		// Raise an event
		require_once('mailman/include/events/SystemEvent_MAILMAN_LIST_CREATE.class.php');
		$systemevent =	SystemEventManager::instance();
		$systemevent->createEvent('MAILMAN_LIST_CREATE', $this->groupMailmanListId,SystemEvent::PRIORITY_MEDIUM);
		$this->fetchData($this->groupMailmanListId);
		$user=UserManager::instance()->getUserByID($creator_id);
	
		$userEmail = $user->getEmail();
		if(empty($userEmail) || !validate_email($userEmail)) {
			$this->setInvalidEmailError();
			return false;
		} else {
			sendCreationMail($userEmail,$this);
		}

		return true;
	}
	
	/**
	* activationRequested - LEt us know if an event is present to create this list
	*
	* @return boolean
	*/
	function activationRequested()
	{
		$systemevent =	SystemEventManager::instance();
		$result1 = $systemevent->fetchEvents(0,10,false,SystemEvent::STATUS_NEW,'MAILMAN_LIST_CREATE',$this->getID());
		$result2 = $systemevent->fetchEvents(0,10,false,SystemEvent::STATUS_RUNNING,'MAILMAN_LIST_CREATE',$this->getID());
		if(count($result1)+count($result2)<1) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * recreate - let the admin recrate a list which had a problem during creation
	 *
	 * @return bool
	 */
	function recreate()
	{

		$systemevent =	SystemEventManager::instance();
		$systemevent->createEvent('MAILMAN_LIST_CREATE', $this->getID(),SystemEvent::PRIORITY_MEDIUM);
	}
	/**
	 *  fetchData - re-fetch the data for this mailing list from the database.
	 *
	 *  @param  int	 The list_id.
	 *	@return	boolean	success.
	 */
	function fetchData($groupListId) {
		$res =& $this->_mailingDAO->searchListFromGroup($groupListId, $this->Group->getID());

		if (!$res) {
			$this->setError(sprintf(_('Error Getting %1$s'), _('Error Getting %1$s')));
			return false;
		}
		$this->dataArray =& $res->getRow();
		return true;
	}

	/**
	 *	update - use this function to update an entry in the database.
	 *
	 *	@param	string	The description of the mailing list
	 *	@param	int	Pass (1) if it should be public (0) for private
	 *	@return	boolean	success.
	 */
	function update($description, $isPublic ='1',$status='1') {
		$current_user=UserManager::instance()->getCurrentUser();
		if(!$current_user->isMember($this->Group->getID(),'A')) {
			exit_permission_denied();
			return false;
		}
		$res = $this->_mailingDAO->updateList($this->groupMailmanListId, $this->Group->getID(),$description , $isPublic,$status);
		if (!$res) {
			$this->setError(_('Error On Update:').db_error());
			return false;
		}
		return true;
	}

	/**
	 *	getGroup - get the Group object this mailing list is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - The id of this mailing list
	 *
	 *	@return	int	The group_list_id #.
	 */
	function getID() {
		return $this->dataArray['group_list_id'];
	}


	/**
	 *	isPublic - Is this mailing list open to the general public.
	 *
	 *	@return boolean	is_public.
	 */
	function isPublic() {
		return $this->dataArray['is_public'];
	}

	/**
	 *	getName - get the name of this mailing list
	 *
	 *	@return string	The name of this mailing list
	 */
	function getName() {
		return $this->dataArray['list_name'];
	}


	/**
	 *	getDescription - get the description of this mailing list
	 *
	 *	@return string	The description.
	 */
	function getDescription() {
		return $this->dataArray['description'];
	}

	/**
	 * getPassword - get the password to administrate the mailing list
	 *
	 * @return string The password
	 */
	function getPassword() {
		return $this->dataArray['password'];
	}

	/**
	 * getListAdminID - get the user id who is the admin of this mailing list
	 *
	 * @return id The admin user
	 */
	function getListAdminID() {
		return $this->dataArray['list_admin'];
	}

	/**
	 * getStatus - get the status of this mailing list
	 *
	 * @return int The status
	 */
	function getStatus() {
		return $this->dataArray['status'];
	}

	/**
	 * getArchivesUrl - get the url to see the archives of the list
	 *
	 * @return string url of the archives
	 */
	function getArchivesUrl() {
		if ($this->isPublic()) {
			$iframe_url = '/pipermail/'.$this->getName().'/';
		} else {
			$iframe_url = '/mailman/private/'.$this->getName().'/';
		}
		htmlIframe($iframe_url, array('class' => 'iframe_service'));
	}

	/**
	 * getExternalInfoUrl - get the url to subscribe/unsubscribe
	 *
	 * @return string url of the info page
	 */
	function getExternalInfoUrl() {
		return util_make_url('/mailman/listinfo/'.$this->getName());
	}
	/**
	 * getOptionsUrl - get the url to manage options for user
	 *
	 * @return string url of the info page
	 */
	function getOptionsUrl() {
		$current_user=UserManager::instance()->getCurrentUser();
		$user=$current_user->getEmail();
		$iframe_url = '/mailman/options/'.$this->getName().'/'.$user;
		htmlIframe($iframe_url, array('class' => 'iframe_service'));
	}
	/**
	 * subscribeUrl - add the user to the mailinglist
	 *
	 * @return string url of the info page
	 */
	function subscribe() {
		$current_user=UserManager::instance()->getCurrentUser();
		if(isLogged() && $current_user->isMember($this->Group->getID()) && !$this->isMonitoring())
		{
			$user=$current_user->getEmail();
			$passwd= $current_user->getUserPw();
			$name= $current_user->getRealName();
			$res = $this->_mailingDAO->newSubscriber($user,$name,$passwd,$this->getName());
			if (!$res) {
				$this->setError(_('Error On Update:').db_error());
				return false;
			}
			htmlRedirect('/plugins/mailman/index.php?group_id='.$this->Group->getId());
		}

	}
	/**
	 * unsubscribeUrl - delete the user from the mailing list
	 * 
	 * @return string url of the info page
	 */
	function unsubscribe() {
		$current_user=UserManager::instance()->getCurrentUser();
		$user=$current_user->getEmail();
		$res = $this->_mailingDAO->deleteSubscriber($user,$this->getName());
		if (!$res) {
			$this->setError(_('Error On Update:').db_error());
			return false;
		}
		htmlRedirect('/plugins/mailman/index.php?group_id='.$this->Group->getId());
	}
	/**
	 *	isMonitoring - See if the current user is in the list of people monitoring the forum.
	 *
	 *	@return	boolean	is_monitoring.
	 */
	function isMonitoring() {
		if (!isLogged()) {
			return false;
		}
		$current_user=UserManager::instance()->getCurrentUser();
		$user=$current_user->getEmail();
		$res = $this->_mailingDAO->userIsMonitoring($user,$this->getName());
		if (!$res) {
			$this->setError(_('Error On Query:').db_error());
			return false;
		}
		$row_count = $res->getRow();
		return $row_count['count'] > 0;
	}



	/**
	 * getExternalAdminUrl - get the url to admin the list with the external tools used
	 *
	 * @return string url of the admin
	 */

	function getExternalAdminUrl() {
		$iframe_url = '/mailman/admin/'.$this->getName();
		htmlIframe($iframe_url, array('class' => 'iframe_service'));
	}

	/**
	 *	delete - permanently delete this mailing list
	 *
	 *	@param	boolean	I'm Sure.
	 *	@param	boolean	I'm Really Sure.
	 *	@return	boolean success;
	 */
	function deleteList($sure,$really_sure) {
		$current_user=UserManager::instance()->getCurrentUser();
		if (!$sure || !$really_sure) {
			$this->setError('Missing params');
			return false;
		}

		if (!$current_user->isMember($this->Group->getID(),'A')) {
			exit_permission_denied();
			return false;
		}
		$res = $this->_mailingDAO->deleteList($this->Group->getID(),$this->getID());
		if (!$res) {
			$this->setError('Could Not Delete List: '.db_error());
			return false;
		}
		require_once('mailman/include/events/SystemEvent_MAILMAN_LIST_DELETE.class.php');
		$systemevent =	SystemEventManager::instance();
		$systemevent->createEvent('MAILMAN_LIST_DELETE',  $this->groupMailmanListId,SystemEvent::PRIORITY_MEDIUM);


		return true;

	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

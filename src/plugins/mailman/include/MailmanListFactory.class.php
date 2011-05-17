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
 */
 
 /*
 
 This work is based on Tim Perdue's work on the forum stuff
 
 */


require_once 'MailmanList.class.php';
require_once 'MailmanListDao.class.php';
require_once 'common/dao/CodendiDataAccess.class.php';

class MailmanListFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The mailing lists array.
	 *
	 * @var	 array	$mailingLists.
	 */
	var $mailingLists;
	/**
	 * DAO 
	 *
	 * @var	 MailingListDao   $mailingDAO.
	 */
	var $_mailingDAO;


	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which these mailing lists are associated.
	 */
	function MailmanListFactory(& $Group) {
		$this->_mailingDAO = new MailmanListDao(CodendiDataAccess::instance());	
		$this->Error();
		
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), 'MailmanListFactory'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('MailmanListFactory:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this MailmanListFactory is associated with.
	 *
	 *	@return object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getMailmanLists - get an array of MailmanList objects for this Group.
	 *
	 * @param boolean $admin if we are in admin mode (we want to see deleted lists)
	 *	@return	array	The array of MailmanList objects.
	 */
	function &getMailmanLists() {
		$current_user=UserManager::instance()->getCurrentUser();
		if (isset($this->mailingLists) && is_array($this->mailingLists)) {
			return $this->mailingLists;
		}
		
		if (islogged() && $current_user->isMember($this->Group->getID())) {
			$public_flag='0,1';
		} else {
			$public_flag='1';
		}
		$result =& $this->_mailingDAO->searchByGroupId($this->Group->getID());
	
		
		if (!$result) {
			$this->setError(sprintf(_('Error Getting %1$s'), _('Error Getting %1$s')).db_error());
			return false;
		} else {
			$this->mailingLists = array();
			while ($arr =& $result->getRow()) {
				$this->mailingLists[] = new MailmanList($this->Group->getID(), $arr['group_list_id'], $arr);
			}
		}
		return $this->mailingLists;
	}

	/**
	 * compareInfos - replace mailman user info by forge user info
	 *
	 * @return string url of the info page
	 */
	function compareInfos()
	{
		$current_user=UserManager::instance()->getCurrentUser();
		$mail=$current_user->getEmail();
		
		$passwd= $current_user->getUserPw();
		$name= $current_user->getRealName();
		$result =& $this->_mailingDAO->compareInfos($mail);
		if (!$result) {
			return false;
		}
		else 
		{
			while( $arr =& $result->getRow())
			{
				if($arr['password']!=$passwd || $arr['name']!=$name)
				{
					return true;
				}
			}
		}
		return false;

	}
	/**
	 * updateInfos - replace mailman user info by forge user info
	 *
	 * @return string url of the info page
	 */
	function updateInfos()
	{
		$current_user=UserManager::instance()->getCurrentUser();
		$mail=$current_user->getEmail();
		
		$passwd= $current_user->getUserPw();
		$name= $current_user->getRealName();
		
		$result =& $this->_mailingDAO->updateInfos($mail,$passwd,$name);
		if (!$result) {
			return false;
		}
		htmlRedirect('/plugins/mailman/index.php?group_id='.$this->Group->getId());
		return $result;	
	}


}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

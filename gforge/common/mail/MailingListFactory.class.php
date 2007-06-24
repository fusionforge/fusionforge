<?php
/**
 * GForge Mailing Lists Facility
 *
 * Copyright 2003 Guillaume Smet
 * http://gforge.org/
 *
 * @version   $Id$
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
 
 This work is based on Tim Perdue's work on the forum stuff
 
 */

require_once('common/include/Error.class.php');
require_once('common/mail/MailingList.class.php');

class MailingListFactory extends Error {

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
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which these mailing lists are associated.
	 */
	function MailingListFactory(& $Group) {
		global $Language;
		$this->Error();
		
		if (!$Group || !is_object($Group)) {
			$this->setError(sprintf(_('%1$s:: No Valid Group Object'), 'MailingListFactory'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError('MailingListFactory:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this MailingListFactory is associated with.
	 *
	 *	@return object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getMailingLists - get an array of MailingList objects for this Group.
	 *
	 * @param boolean $admin if we are in admin mode (we want to see deleted lists)
	 *	@return	array	The array of MailingList objects.
	 */
	function &getMailingLists() {
		global $Language;
		if (isset($this->mailingLists) && is_array($this->mailingLists)) {
			return $this->mailingLists;
		}
		
		$public_flag = MAIL__MAILING_LIST_IS_PUBLIC;
		
		$perm = & $this->Group->getPermission(session_get_user());
		if ($perm && is_object($perm) && $perm->isMember()) {
			$public_flag = MAIL__MAILING_LIST_IS_PRIVATE.', '.MAIL__MAILING_LIST_IS_PUBLIC;
		}

		$sql = 'SELECT * '
			. 'FROM mail_group_list '
			. 'WHERE group_id=\''.$this->Group->getID().'\' '
			. 'AND is_public IN ('.$public_flag.') '
			. 'ORDER BY list_name;';
		

		$result = db_query($sql);

		if (!$result) {
			$this->setError(sprintf(_('Error Getting %1$s'), _('Error Getting %1$s')).db_error());
			return false;
		} else {
			$this->mailingLists = array();
			while ($arr = db_fetch_array($result)) {
				$this->mailingLists[] = new MailingList($this->Group, $arr['group_list_id'], $arr);
			}
		}
		return $this->mailingLists;
	}
}

?>

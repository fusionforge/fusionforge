<?php
/**
 * FusionForge mailing lists
 *
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2003, Guillaume Smet
 * Copyright 2009, Roland Mas
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

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'mail/MailingList.class.php';

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
	function getMailingLists() {
		if (isset($this->mailingLists) && is_array($this->mailingLists)) {
			return $this->mailingLists;
		}
		
		$public_flag = MAIL__MAILING_LIST_IS_PUBLIC;
		
		$perm = & $this->Group->getPermission ();
		if ($perm && is_object($perm) && $perm->isMember()) {
			$public_flag = MAIL__MAILING_LIST_IS_PRIVATE.', '.MAIL__MAILING_LIST_IS_PUBLIC;
		}

		$result = db_query_params ('SELECT * FROM mail_group_list WHERE group_id=$1 AND is_public = ANY ($2) ORDER BY list_name',
					   array ($this->Group->getID(),
						  db_int_array_to_any_clause (array (MAIL__MAILING_LIST_IS_PRIVATE,
										     MAIL__MAILING_LIST_IS_PUBLIC)))) ;

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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

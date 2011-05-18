<?php
/**
 * FusionForge Mails Facility
 *
 * Copyright 2002 GForge, LLC
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


require_once 'MailmanList.class.php';

class MailsForUser extends Error {

	/**
	 * The User object.
	 *
	 * @var	 object  $User.
	 */
	var $User;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this  list associated.
	 */
	function MailsForUser(&$user) {
		$this->User =& $user;

		return true;
	}


	/**
	*       getMonitoredForums
	*
	*       @return Forum[] The array of Forums
	*
	*/
	function getMonitoredMails() {
		$lists = array();
		$sql="SELECT groups.group_name,groups.group_id,mail_group_list.group_list_id,mail_group_list.list_name ".
		     "FROM groups,mail_group_list,mailman_sql ".
		     "WHERE groups.group_id=mail_group_list.group_id AND groups.status ='A' ".
		     "AND mail_group_list.list_name=mailman_sql.listname ".
		     "AND mailman_sql.address=$1 ORDER BY group_name DESC";

		$result=db_query_params($sql,array($this->User->getEmail()));
		$rows=db_numrows($result);
		if ($rows < 1) {
		        return $lists;
		}
		$last_group='';
		for ($i=0; $i<$rows; $i++) {
			$group_id = db_result($result,$i,'group_id');
			$list_id = db_result($result,$i,'group_list_id');
			$group =& group_get_object($group_id);
			$list =& new MailmanList($group,$list_id);
			if ($list->isError()) {
				$this->setError($list->getErrorMessage());
			} else {
				$lists[] =& $list;
			}
		}
		return $lists;
	}

}

?>

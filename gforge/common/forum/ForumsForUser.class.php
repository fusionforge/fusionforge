<?php
/**
 * GForge Forums Facility
 *
 * Copyright 2002 GForge, LLC
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


require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'forum/Forum.class.php';
require_once $gfcommon.'include/User.class.php';

class ForumsForUser extends Error {

	/**
	 * The User object.
	 *
	 * @var	 object  $User.
	 */
	var $User;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this forum is associated.
	 */
	function ForumsForUser(&$user) {
		$this->User =& $user;

		return true;
	}


	/**
	*       getMonitoredForums
	*
	*       @return Forum[] The array of Forums
	*
	*/
	function getMonitoredForums() {
		$forums = array();
		$sql="SELECT groups.group_name,groups.group_id,forum_group_list.group_forum_id,forum_group_list.forum_name ".
		     "FROM groups,forum_group_list,forum_monitored_forums ".
		     "WHERE groups.group_id=forum_group_list.group_id AND groups.status ='A' ".
		     "AND forum_group_list.group_forum_id=forum_monitored_forums.forum_id ".
		     "AND forum_monitored_forums.user_id='".$this->User->getID()."' ORDER BY group_name DESC";

		$result=db_query($sql);
		$rows=db_numrows($result);
		if ($rows < 1) {
		        return $forums;
		}
		$last_group='';
		for ($i=0; $i<$rows; $i++) {
			$group_id = db_result($result,$i,'group_id');
			$forum_id = db_result($result,$i,'group_forum_id');
			$group =& group_get_object($group_id);
			$forum =& new Forum($group,$forum_id);
			if ($forum->isError()) {
				$this->setError($forum->getErrorMessage());
			} else {
				$forums[] =& $forum;
			}
		}
		return $forums;
	}

}

?>

<?php
/**
 * FusionForge forums
 *
 * Copyright 1999-2000, Tim Perdue/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'forum/Forum.class.php';

class ForumFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The forums array.
	 *
	 * @var	 array	forums.
	 */
	var $forums;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this forum is associated.
	 */
	function ForumFactory(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Forum:: No Valid Group Object'));
			return false;
		}
		if ($Group->isError()) {
			$this->setError(_('Forum').':: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this ForumFactory is associated with.
	 *
	 *	@return object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getForums - get an array of Forum objects for this Group.
	 *
	 *	@return	array	The array of Forum objects.
	 */
	function &getForums() {
		if ($this->forums) {
			return $this->forums;
		}

		$result = db_query_params ('SELECT * FROM forum_group_list_vw
WHERE group_id=$1
ORDER BY group_forum_id',
					   array ($this->Group->getID())) ;
		
		$rows = db_numrows($result);
		
		if (!$result) {
			$this->setError(_('Forum not found').' : '.db_error());
			$this->forums = false;
		} else {
			while ($arr = db_fetch_array($result)) {
				if (forge_check_perm ('forum', $arr['group_forum_id'], 'read')) {
					$this->forums[] = new Forum($this->Group, $arr['group_forum_id'], $arr);
				}
			}
		}
		return $this->forums;
	}
	
	
	/**
	 *	getForumsAdmin - get an array of all (public, private and suspended) Forum objects for this Group.
	 *
	 *	@return	array	The array of Forum objects.
	 */
	function &getForumsAdmin() {
		if ($this->forums) {
			return $this->forums;
		}

		
		if (session_loggedin()) {
			if (!forge_check_perm ('forum_admin', $this->Group->getID())) {
				$this->setError(_("You don't have a permission to access this page"));
				$this->forums = false;
			} else {
				$result = db_query_params ('SELECT * FROM forum_group_list_vw
WHERE group_id=$1
ORDER BY group_forum_id',
							   array ($this->Group->getID())) ;
			}
		} else {
			$this->setError(_("You don't have a permission to access this page"));
			$this->forums = false;
		}
		
		$rows = db_numrows($result);
		
		if (!$result) {
			$this->setError(_('Forum not found').' : '.db_error());
			$this->forums = false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->forums[] = new Forum($this->Group, $arr['group_forum_id'], $arr);
			}
		}
		return $this->forums;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

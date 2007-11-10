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


/*
	Message Forums
	By Tim Perdue, Sourceforge, 11/99

	Massive rewrite by Tim Perdue 7/2000 (nested/views/save)

	Complete OO rewrite by Tim Perdue 12/2002
*/


require_once('common/include/Error.class.php');
require_once('common/forum/Forum.class.php');

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
		global $Language;
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
		global $Language, $sys_database_type;

		if ($this->forums) {
			return $this->forums;
		}
		if (session_loggedin()) {
			$perm =& $this->Group->getPermission( session_get_user() );
			if (!$perm || !is_object($perm) || !$perm->isMember()) {
				$public_flag='=1';
				$exists = '';
			} else {
				$public_flag='<3';
				if ($perm->isForumAdmin()) {
					$exists='';
				} else {
					$exists=" AND group_forum_id IN (SELECT role_setting.ref_id
					FROM role_setting, user_group
					WHERE role_setting.value >= 0
                                          AND role_setting.section_name = 'forum'
                                          AND role_setting.ref_id=forum_group_list_vw.group_forum_id
                                          
   					  AND user_group.role_id = role_setting.role_id
					  AND user_group.user_id='".user_getid()."') ";
				}
			}
		} else {
			$public_flag='=1';
			$exists = '';
		}

		if ($sys_database_type == "mysql") {
			$sql="SELECT fgl.*,
					(SELECT count(*) AS `count`
						FROM (
							SELECT DISTINCT group_forum_id, thread_id FROM forum
						) AS tmp
						WHERE tmp.group_forum_id = fgl.group_forum_id
					) AS threads 
				FROM forum_group_list_vw AS fgl";
		} else {
			$sql="SELECT *
				FROM forum_group_list_vw";
		}
		$sql .= "
			WHERE group_id='". $this->Group->getID() ."' 
			AND is_public $public_flag 
			$exists
			ORDER BY group_forum_id;";

		$result = db_query ($sql);

		$rows = db_numrows($result);

		if (!$result) {
			$this->setError(_('Forum not found').db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->forums[] = new Forum($this->Group, $arr['group_forum_id'], $arr);
			}
		}
		return $this->forums;
	}

}

?>

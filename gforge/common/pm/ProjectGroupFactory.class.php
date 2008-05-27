<?php
/**
 * GForge Project Management Facility
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
/*

	Project/Task Manager
	By Tim Perdue, Sourceforge, 11/99
	Heavy rewrite by Tim Perdue April 2000

	Total rewrite in OO and GForge coding guidelines 12/2002 by Tim Perdue
*/

require_once $gfcommon.'include/Error.class.php';
require_once $gfcommon.'pm/ProjectGroup.class.php';

class ProjectGroupFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The projectGroups array.
	 *
	 * @var	 array	projectGroups.
	 */
	var $projectGroups;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this ProjectGroupFactory is associated.
	 *	@return	boolean	success.
	 */
	function ProjectGroupFactory(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError('ProjectGroup:: No Valid Group Object');
			return false;
		}
		if ($Group->isError()) {
			$this->setError('ProjectGroup:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		return true;
	}

	/**
	 *	getGroup - get the Group object this ProjectGroupFactory is associated with.
	 *
	 *	@return	object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getProjectGroups - get an array of ProjectGroup objects.
	 *
	 *	@return	array	The array of ProjectGroups.
	 */
	function &getProjectGroups() {
		if ($this->projectGroups) {
			return $this->projectGroups;
		}
		if (session_loggedin()) {
			$perm =& $this->Group->getPermission( session_get_user() );
			if (!$perm || !is_object($perm) || !$perm->isMember()) {
				$public_flag='=1';
				$exists = '';
			} else {
				$public_flag='<3';
				if ($perm->isPMAdmin()) {
					$exists='';
				} else {
					$exists=" AND group_project_id IN (SELECT role_setting.ref_id
					FROM role_setting, user_group
					WHERE role_setting.value >= 0
                                          AND role_setting.section_name = 'pm'
                                          AND role_setting.ref_id=project_group_list_vw.group_project_id
                                          
   					  AND user_group.role_id = role_setting.role_id
					  AND user_group.user_id='".user_getid()."') ";
				}
			}
		} else {
			$public_flag='=1';
			$exists = '';
		}

		$sql="SELECT *
			FROM project_group_list_vw
			WHERE group_id='". $this->Group->getID() ."' 
			AND is_public $public_flag $exists
			ORDER BY group_project_id;";

		$result = db_query ($sql);

		$rows = db_numrows($result);

		if (!$result || $rows < 1) {
			$this->setError(_('No ProjectGroups Found').db_error());
			$this->projectGroups=NULL;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->projectGroups[] = new ProjectGroup($this->Group, $arr['group_project_id'], $arr);
			}
		}
		return $this->projectGroups;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

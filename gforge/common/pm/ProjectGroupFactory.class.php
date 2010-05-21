<?php
/**
 * FusionForge project manager
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
		$result = db_query_params ('SELECT * FROM project_group_list_vw WHERE group_id=$1 ORDER BY group_project_id',
							   array ($this->Group->getID())) ;
		}
		$rows = db_numrows($result);

		if (!$result || $rows < 1) {
			$this->setError(_('No ProjectGroups Found').db_error());
			$this->projectGroups=NULL;
		} else {
			while ($arr = db_fetch_array($result)) {
				if (forge_check_perm ('pm', $arr['group_project_id'], 'read')) {
					$this->projectGroups[] = new ProjectGroup($this->Group, $arr['group_project_id'], $arr);
				}
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

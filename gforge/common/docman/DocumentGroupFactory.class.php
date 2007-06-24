<?php
/**
 * GForge Doc Mgr Facility
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
	Document Groups
*/

require_once('common/include/Error.class.php');
require_once('common/forum/ForumMessage.class.php');

class DocumentGroupFactory extends Error {
	/**
	 * This variable holds the document groups
	 */
	var $flat_groups;

	/**
	 * This variable holds the document groups for reading them in nested form
	 */
	var $nested_groups;

	/**
	 * The Group object
	 */
	var $Group;

	/**
	 *  Constructor.
	 *
	 *	@return	boolean	success.
	 */
	function DocumentGroupFactory(&$Group) {
		$this->Error();
		
		if (!$Group || !is_object($Group)) {
			$this->setError("DocumentGroupFactory:: Invalid Group");
			return false;
		}
		if ($Group->isError()) {
			$this->setError('DocumentGroupFactory:: '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;


		return true;
	}

	/**
	 *	getNested - Return an array of DocumentGroup objects arranged for nested views.
	 *
	 *	@return	array	The array of DocumentGroup.
	 */
	function &getNested() {
		if ($this->nested_groups) {
			return $this->nested_groups;
		}
		
		$sql="SELECT * FROM doc_groups
		WHERE group_id='".$this->Group->getID()."' 
		ORDER BY groupname ASC";

		$result=db_query($sql);
		$rows = db_numrows($result);
		
		if (!$result || $rows < 1) {
			$this->setError('No Groups Found '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->flat_groups[] = new DocumentGroup($this->Group, $arr);
			}
		}
		
		// Build the nested array
		$count = count($this->flat_groups);
		for ($i=0; $i < $count; $i++) {
			$this->nested_groups["".$this->flat_groups[$i]->getParentID()][] =& $this->flat_groups[$i];
			
		}

		
		return $this->nested_groups;

	}
		/**
	 *	getDocumentGroups - Return an array of DocumentGroup objects.
	 *
	 *	@return	array	The array of DocumentGroup.
	 */
	function &getDocumentGroups() {
		if ($this->flat_groups) {
			return $this->flat_groups;
		}
		
		$sql="SELECT * FROM doc_groups
		WHERE group_id='".$this->Group->getID()."' 
		ORDER BY groupname ASC";

		$result=db_query($sql);
		$rows = db_numrows($result);
		
		if (!$result || $rows < 1) {
			$this->setError('No Groups Found '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->flat_groups[] = new DocumentGroup($this->Group, $arr);
			}
		}
		

		
		return $this->flat_groups;

	}
}

?>

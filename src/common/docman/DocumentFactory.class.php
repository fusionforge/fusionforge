<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * http://fusionforge.org
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
require_once $gfcommon.'docman/Document.class.php';

class DocumentFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * The Documents dictionary.
	 *
	 * @var	array	Contains doc_group_id as key and the array of documents associated to that id. 
	 */
	var $Documents;

	var $stateid;
	var $docgroupid;
	var $sort='group_name, title';

	/**
	 * Constructor.
	 *
	 * @param	object	The Group object to which this DocumentFactory is associated.
	 * @return	boolean	success.
	 */
	function DocumentFactory(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('ProjectGroup:: No Valid Group Object'));
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
	 * getGroup - get the Group object this DocumentFactory is associated with.
	 *
	 * @return	object	the Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * setStateID - call this before getDocuments() if you want to limit to a specific state.
	 *
	 * @param	int	The stateid from the doc_states table.
	 */
	function setStateID($stateid) {
		$this->stateid=$stateid;
	}

	/**
	 * setDocGroupID - call this before getDocuments() if you want to limit to a specific doc_group.
	 *
	 * @param	int	The doc_group from the doc_groups table.
	 */
	function setDocGroupID($docgroupid) {
		$this->docgroupid=$docgroupid;
	}

	/**
	 * setSort - call this before getDocuments() if you want to control the sorting.
	 *
	 * @param	string	The name of the field to sort on.
	 */
	function setSort($sort) {
		$this->sort=$sort;
	}

	/**
	 * getDocuments - returns an array of Document objects.
	 *
	 * @return	array	Document objects.
	 */
	function &getDocuments() {
		if (!$this->Documents) {
			$this->getFromDB();
		}
		
		$return = array();
		// If the document group is specified, we should only check that group in
		// the Documents array. If not, we should check ALL the groups.
		if ($this->docgroupid) {
			$keys = array($this->docgroupid);
		} else {
			$keys = array_keys($this->Documents);
		}
		
		foreach ($keys as $key) {
			if (!array_key_exists($key, $this->Documents)) continue;	// Should not happen
			$count = count($this->Documents[$key]);
			
			for ($i=0; $i < $count; $i++) {
				$valid = true;		// do we need to return this document?
				$doc =& $this->Documents[$key][$i];
				
				if (!$this->stateid) {
					if (session_loggedin()) {
						$perm =& $this->Group->getPermission ();
						if (!$perm || !is_object($perm) || !$perm->isMember()) {
							if ($doc->getStateID() != 1) {		// non-active document?
								$valid = false;
							}
						} else {
							if ($doc->getStateID() != 1 &&		/* not active */
								$doc->getStateID() != 4 &&			/* not hidden */
								$doc->getStateID() != 5) {			/* not private */
								$valid = false;
							}
						}
					} else {
						if ($doc->getStateID() != 1) {		// non-active document?
							$valid = false;
						}
					}
				} else {
					if ($this->stateid != "ALL" && $doc->getStateID() != $this->stateid) {
						$valid = false;
					}
				}

				if ($valid) {
					$return[] =& $doc;
				}
			}
		}

		if (count($return) == 0) {
			$this->setError(_('No Documents Found'));
			$return = NULL;
			return $return;
		}

		return $return;
	}

	/**
	 * getFromDB - Retrieve documents from database.
	 */
	function getFromDB() {
		$this->Documents = array();
		$result = db_query_params ('SELECT * FROM docdata_vw ORDER BY title',
					   array());
		if (!$result) {
			exit_error(db_error(),'docman');
		}
		
		while ($arr = db_fetch_array($result)) {
			$doc_group_id = $arr['doc_group'];
			if (!is_array(@$this->Documents[$doc_group_id])) {
				$this->Documents[$doc_group_id] = array();
			}
			
			$this->Documents[$doc_group_id][] = new Document($this->Group, $arr['docid'], $arr);
		}
	}

	/**
	 * getStates - Return an array of states that have documents associated to them
	 */
	function getUsedStates() {
		$result = db_query_params ('SELECT DISTINCT doc_states.stateid,doc_states.name 
			FROM doc_states,doc_data
			WHERE doc_data.stateid=doc_states.stateid
			ORDER BY doc_states.name ASC',
					   array());
		if (!$result) {
			exit_error(db_error(),'docman');
		}
		
		$return = array();
		while ($arr = db_fetch_array($result)) {
			$return[] = $arr;
		}
		
		return $return;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

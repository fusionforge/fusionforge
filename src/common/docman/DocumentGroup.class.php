<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010, Franck Villaume - Capgemini
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

class DocumentGroup extends Error {

	/**
	 * The Group object.
	 *
	 * @var		object	$Group.
	 */
	var $Group; //object

	/**
	 * Array of data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *  DocumentGroup - constructor.
	 *
	 *  Use this constructor if you are modifying an existing doc_group.
	 *
	 *	@param	object	Group object.
	 *  @param	array	(all fields from doc_groups) OR doc_group from database.
	 *  @return boolean	success.
	 */
	function DocumentGroup(&$Group, $data=false) {
		$this->Error();

		//was Group legit?
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Document Directory: No Valid Project'));
			return false;
		}
		//did Group have an error?
		if ($Group->isError()) {
			$this->setError(_('Document Directory:').' '.$Group->getErrorMessage());
			return false;
		}
		$this->Group =& $Group;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
//
//	should verify group_id
//
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
	}

	/**
	 *	create - create a new item in the database.
	 *
	 *	@param	string	Item name.
	 *  @return id on success / false on failure.
	 */
	function create($name,$parent_doc_group=0) {
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('DocumentGroup: name is Required'));
			return false;
		}
		
		if ($parent_doc_group) {
			// check if parent group exists
			$res = db_query_params ('SELECT * FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
						array ($parent_doc_group,
						       $this->Group->getID())) ;
			if (!$res || db_numrows($res) < 1) {
				$this->setError(_('DocumentGroup: Invalid Document Directory parent ID'));
				return false;
			}
		} else {
			$parent_doc_group = 0;
		}

		$perm =& $this->Group->getPermission ();
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		
		$res=db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
				array($name,
					$parent_doc_group,
					$this->Group->getID()));
		if ($res && db_numrows($res) > 0) {
			$this->setError(_('Directory name already exists'));
			return false;
		}

		$result = db_query_params ('INSERT INTO doc_groups (group_id,groupname,parent_doc_group,stateid) VALUES ($1, $2, $3, $4)',
					   array ($this->Group->getID(),
						  htmlspecialchars($name),
                          $parent_doc_group,
                          '1')) ;
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
		} else {
			$this->setError(_('DocumentGroup::create() Error Adding Directory:').' '.db_error());
			return false;
		}

		$doc_group = db_insertid($result, 'doc_groups', 'doc_group');

		//	Now set up our internal data structures
		if (!$this->fetchData($doc_group)) {
			return false;
		}

		return true;
	}

	/**
	 * delete - delete a DocumentGroup.
	 *          delete is recursive and permanent
	 * @param	int		Document Group Id, integer Project Group Id
	 * @return	boolean	success
	 */
	function delete($doc_groupid,$project_group_id) {
		$perm =& $this->Group->getPermission ();
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		/* delete documents in directory */
		$result = db_query_params ('DELETE FROM doc_data where doc_group = $1 and group_id = $2',
					array($doc_groupid,
						$project_group_id));

		/* delete directory */
		$result = db_query_params ('DELETE FROM doc_groups where doc_group = $1 and group_id = $2',
					array($doc_groupid,
						$project_group_id));

		db_commit();

		/* is there any subdir ? */
		$subdir = db_query_params ('select doc_group from doc_groups where parent_doc_group = $1 and group_id = $2',
					array($doc_groupid,
						$project_group_id));
		/* make a recursive call */
		while ($arr = db_fetch_array($subdir)) {
			$this->delete($arr['doc_group'],$project_group_id);
		}

		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 *	fetchData - re-fetch the data for this DocumentGroup from the database.
	 *
	 *	@param	int		ID of the doc_group.
	 *	@return boolean	success
	 */
	function fetchData($id) {
		$res = db_query_params ('SELECT * FROM doc_groups WHERE doc_group=$1',
					array ($id)) ;
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('DocumentGroup: Invalid Document Directory ID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 *	getGroup - get the Group Object this DocumentGroup is associated with.
	 *
	 *	@return Object Group.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	getID - get this DocumentGroup's ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['doc_group'];
	}
	
	/**
	 *	getID - get parent DocumentGroup's id.
	 *
	 *	@return	int	The id #.
	 */
	function getParentID() {
		return $this->data_array['parent_doc_group'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	String	The name.
	 */
	function getName() {
		return $this->data_array['groupname'];
	}

	/**
	 *	getState - get the state id.
	 *
	 *	@return	Int	The state id.
	 */
	function getState() {
		return $this->data_array['stateid'];
	}

	/**
	 *  update - update a DocumentGroup.
	 *
	 *  @param	string	Name of the category.
	 *  @return boolean.
	 */
	function update($name,$parent_doc_group) {
		$perm =& $this->Group->getPermission ();
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		
		if ($parent_doc_group) {
			// check if parent group exists
			$res = db_query_params ('SELECT * FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
						array ($parent_doc_group,
						       $this->Group->getID())) ;
			if (!$res || db_numrows($res) < 1) {
				$this->setError(_('DocumentGroup: Invalid Document Directory parent ID'));
				return false;
			}
		} else {
			$parent_doc_group=0;
		}

		$res=db_query_params ('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
				array($name,
						$parent_doc_group,
						$this->Group->getID()));
		if ($res && db_numrows($res) > 0) {
			$this->setError(_('Directory name already exists'));
			return false;
		}

		$result = db_query_params ('UPDATE doc_groups SET groupname=$1, parent_doc_group=$2 WHERE doc_group=$3 AND group_id=$4',
					   array(htmlspecialchars($name),
						 $parent_doc_group,
						 $this->getID(),
						 $this->Group->getID())) ;
		if ($result && db_affected_rows($result) > 0) {
			return true;
		} else {
			$this->setOnUpdateError(_('DocumentGroup:').' '.db_error());
			return false;
		}
	}
		
	/**
	* hasDocuments - Recursive function that checks if this group or any of it childs has documents associated to it
	*
	* A group has associated documents if and only if there are documents associated to this
	* group or to any of its childs
	*
	* @param	array	Array of nested groups information, fetched from DocumentGroupFactory class
	* @param	object	The DocumentFactory object
	* @param	int		(optional) State of the documents
	* @return	boolean	success
	*/
	function hasDocuments(&$nested_groups, &$document_factory, $stateid=0) {
		$doc_group_id = $this->getID();
		static $result = array();	// this function will probably be called several times so we better store results in order to speed things up
		if (!array_key_exists($stateid, $result) || !is_array($result[$stateid])) $result[$stateid] = array();

		if (array_key_exists($doc_group_id, $result[$stateid])) return $result[$stateid][$doc_group_id];

		// check if it has documents
		if ($stateid) {
			$document_factory->setStateID($stateid);
		}
		$document_factory->setDocGroupID($doc_group_id);
		$docs = $document_factory->getDocuments();
		if (is_array($docs) && count($docs) > 0) {		// this group has documents
			$result[$stateid][$doc_group_id] = true;
			return true;
		}
		
		// this group doesn't have documents... check recursively on the childs
		if (array_key_exists($doc_group_id,$nested_groups) && is_array($nested_groups[$doc_group_id])) {
			$count = count($nested_groups[$doc_group_id]);
			for ($i=0; $i < $count; $i++) {
				if ($nested_groups[$doc_group_id][$i]->hasDocuments($nested_groups, $document_factory, $stateid)) {
					// child has documents
					$result[$stateid][$doc_group_id] = true;
					return true;
				}
			}
			// no child has documents, then this group doesn't have associated documents
			$result[$stateid][$doc_group_id] = false;
			return false;
		} else {	// this group doesn't have childs
			$result[$stateid][$doc_group_id] = false;
			return false;
		}
	}

	/**
	* hasSubgroup - Checks if this group has a specified subgroup associated to it
	*
	* @param	array	Array of nested groups information, fetched from DocumentGroupFactory class
	* @param	int		ID of the subgroup
	* @return	boolean	success
	*/
	function hasSubgroup(&$nested_groups, $doc_subgroup_id) {
		$doc_group_id = $this->getID();

		if (is_array(@$nested_groups[$doc_group_id])) {
			$count = count($nested_groups[$doc_group_id]);
			for ($i=0; $i < $count; $i++) {
				// child is a match?
				if ($nested_groups[$doc_group_id][$i]->getID() == $doc_subgroup_id) {
					return true;
				} else {
					// recursively check if this child has this subgroup
					if ($nested_groups[$doc_group_id][$i]->hasSubgroup($nested_groups, $doc_subgroup_id)) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * setStateID - set the state id of this document group
	 *
	 * @param	int		State ID
	 * @return	boolean success
	 */
	function setStateID($stateid) {
		$res = db_query_params ('UPDATE doc_groups SET stateid=$1
								WHERE doc_group=$2
								AND group_id=$3',
								array ($stateid,$this->getID(),$this->Group->getID()));

		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(_('DocumentGroup:').' '.db_error());
			return false;
		}
		return true;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

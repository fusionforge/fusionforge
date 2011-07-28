<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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

class DocumentGroup extends Error {

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * Array of data.
	 *
	 * @var	array	$data_array.
	 */
	var $data_array;

	/**
	 * DocumentGroup - constructor.
	 *
	 * Use this constructor if you are modifying an existing doc_group.
	 *
	 * @param	object	Group object.
	 * @param	array	(all fields from doc_groups) OR doc_group id from database.
	 * @return	boolean	True on success.
	 * @access	public
	 */
	function DocumentGroup(&$Group, $data = false) {
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
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('DocumentGroup:: '. _('Group_id in db result does not match Group Object'));
					$this->data_array = null;
					return false;
				}
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
		return true;
	}

	/**
	 * create - create a new item in the database.
	 *
	 * @param	string	Item name.
	 * @return	boolean	on success / false on failure.
	 * @access	public
	 */
	function create($name, $parent_doc_group = 0) {
		//
		//	data validation
		//
		if (!$name) {
			$this->setError(_('Name is required'));
			return false;
		}

		if ($parent_doc_group) {
			// check if parent group exists
			$res = db_query_params('SELECT * FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
						array($parent_doc_group, $this->Group->getID()));
			if (!$res || db_numrows($res) < 1) {
				$this->setError(_('Invalid Documents folder parent ID'));
				return false;
			}
		} else {
			$parent_doc_group = 0;
		}

		if ($parent_doc_group || $name != 'Uncategorized Submissions') {
			$perm =& $this->Group->getPermission();
			if (!$perm || !$perm->isDocEditor()) {
				$this->setPermissionDeniedError();
				return false;
				}
		}

		$res = db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
					array($name,
						$parent_doc_group,
						$this->Group->getID())
					);
		if ($res && db_numrows($res) > 0) {
			$this->setError(_('Folder name already exists'));
			return false;
		}

		$result = db_query_params('INSERT INTO doc_groups (group_id,groupname,parent_doc_group,stateid) VALUES ($1, $2, $3, $4)',
						array ($this->Group->getID(),
							htmlspecialchars($name),
							$parent_doc_group,
							'1')
						);
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
		} else {
			$this->setError(_('Error Adding Folder:').' '.db_error());
			return false;
		}

		$doc_group = db_insertid($result, 'doc_groups', 'doc_group');

		// Now set up our internal data structures
		if (!$this->fetchData($doc_group)) {
			return false;
		}

		return true;
	}

	/**
	 * delete - delete a DocumentGroup.
	 * WARNING delete is recursive and permanent
	 * @param	integer	Document Group Id, integer Project Group Id
	 * @return	boolean	success
	 * @access	public
	 */
	function delete($doc_groupid, $project_group_id) {
		$perm =& $this->Group->getPermission();
		if (!$perm || !$perm->isDocEditor()) {
			$this->setPermissionDeniedError();
			return false;
		}
		db_begin();
		/* delete documents in directory */
		$result = db_query_params('DELETE FROM doc_data where doc_group = $1 and group_id = $2',
					array($doc_groupid, $project_group_id));

		/* delete directory */
		$result = db_query_params('DELETE FROM doc_groups where doc_group = $1 and group_id = $2',
					array($doc_groupid, $project_group_id));

		db_commit();

		/* is there any subdir ? */
		$subdir = db_query_params('select doc_group from doc_groups where parent_doc_group = $1 and group_id = $2',
					array($doc_groupid, $project_group_id));
		/* make a recursive call */
		while ($arr = db_fetch_array($subdir)) {
			$this->delete($arr['doc_group'], $project_group_id);
		}

		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * injectArchive - extract the attachment and create the directory tree if needed
	 *
	 * @param	array	uploaded data
	 * @return	boolean	success or not
	 * @access	public
	 */
	function injectArchive($uploaded_data) {
		if (!is_uploaded_file($uploaded_data['tmp_name'])) {
			$this->setError(_('Invalid file name.'));
			return false;
		}
		if (function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$uploaded_data_type = finfo_file($finfo, $uploaded_data['tmp_name']);
		} else {
			$uploaded_data_type = $uploaded_data['type'];
		}

		switch ($uploaded_data_type) {
			case "application/zip": {
				$returned = $this->__injectZip($uploaded_data);
				break;
			}
			case "application/x-rar-compressed": {
				$returned = $this->__injectRar($uploaded_data);
				break;
			}
			default: {
				$this->setError( _('Unsupported injected file:') . ' ' .$uploaded_data_type);
				$returned = false;
			}
		}
		return $returned;
	}

	/**
	 * fetchData - re-fetch the data for this DocumentGroup from the database.
	 *
	 * @param	integer	ID of the doc_group.
	 * @return	boolean	success
	 * @access	public
	 */
	function fetchData($id) {
		$res = db_query_params('SELECT * FROM doc_groups WHERE doc_group = $1 and group_id = $2',
					array($id, $this->Group->getID()));
		if (!$res || db_numrows($res) < 1) {
			$this->setError(_('Invalid Document Folder ID'));
			return false;
		}
		$this->data_array = db_fetch_array($res);
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group Object this DocumentGroup is associated with.
	 *
	 * @return	Object Group.
	 * @access	public
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getID - get this DocumentGroup's ID.
	 *
	 * @return	integer	The id #.
	 * @access	public
	 */
	function getID() {
		return $this->data_array['doc_group'];
	}

	/**
	 * getID - get parent DocumentGroup's id.
	 *
	 * @return	integer	The id #.
	 * @access	public
	 */
	function getParentID() {
		return $this->data_array['parent_doc_group'];
	}

	/**
	 * getName - get the name.
	 *
	 * @return	string	The name.
	 * @access	public
	 */
	function getName() {
		return $this->data_array['groupname'];
	}

	/**
	 * getState - get the state id.
	 *
	 * @return	integer	The state id.
	 * @access	public
	 */
	function getState() {
		return $this->data_array['stateid'];
	}

	/**
	 * update - update a DocumentGroup.
	 *
	 * @param	string	Name of the category.
	 * @param	integer	the doc_group id of the parent. default = 0
	 * @return	boolean	success or not
	 * @access	public
	 */
	function update($name, $parent_doc_group = 0) {
		$perm =& $this->Group->getPermission();
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
			$res = db_query_params('SELECT * FROM doc_groups WHERE doc_group=$1 AND group_id=$2',
						array($parent_doc_group,
							$this->Group->getID())
						);
			if (!$res || db_numrows($res) < 1) {
				$this->setError(_('Invalid Documents Folder parent ID'));
				return false;
			}
		}

		$res=db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
					array($name,
						$parent_doc_group,
						$this->Group->getID())
					);
		if ($res && db_numrows($res) > 0) {
			$this->setError(_('Documents Folder name already exists'));
			return false;
		}

		$result = db_query_params('UPDATE doc_groups SET groupname=$1, parent_doc_group=$2 WHERE doc_group=$3 AND group_id=$4',
						array(htmlspecialchars($name),
							$parent_doc_group,
							$this->getID(),
							$this->Group->getID())
					);
		if ($result && db_affected_rows($result) > 0) {
			$this->fetchData($this->getID()) ;
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
	 * @param	int	(optional) State of the documents
	 * @return	boolean	success
	 * @access	public
	 */
	function hasDocuments(&$nested_groups, &$document_factory, $stateid = 0) {
		$doc_group_id = $this->getID();
		static $result = array();	// this function will probably be called several times so we better store results in order to speed things up
		if (!array_key_exists($stateid, $result) || !is_array($result[$stateid]))
			$result[$stateid] = array();

		if (array_key_exists($doc_group_id, $result[$stateid]))
			return $result[$stateid][$doc_group_id];

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
		if (array_key_exists($doc_group_id, $nested_groups) && is_array($nested_groups[$doc_group_id])) {
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
	 * @param	int	ID of the subgroup
	 * @return	boolean	success
	 * @access	public
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

	function getSubgroup($docGroupId, $stateId = 1) {
		$returnArr = array();
		$res = db_query_params('SELECT doc_group from doc_groups where parent_doc_group = $1 and stateid = $2 and group_id = $3 order by groupname',
							array($docGroupId, $stateId, $this->Group->getID()));
		if (!$res) {
			return $returnArr;
		}

		while ($row = db_fetch_array($res)) {
			$returnArr[] = $row['doc_group'];
		}

		return $returnArr;
	}

	/**
	 * getPath - return the complete_path
	 *
	 * @param	boolean	does path is url clickable (default is false)
	 * @param	boolean	does path include this document group name ? (default is true)
	 * @return	string	the complete_path
	 * @access	public
	 */
	function getPath($url = false, $includename = true) {
		$returnPath = '/';
		if ($this->getParentID()) {
			$parentDg = new DocumentGroup($this->Group, $this->getParentID());
			$returnPath = $parentDg->getPath($url);
		}
		if ($includename) {
			if ($url) {
				$returnPath .= util_make_link('/docman/?group_id='.$this->Group->getID().'&view=listfile&dirid='.$this->getID(),$this->getName(), array('title' => _('Browse this folder'), 'class' => 'tabtitle'));
			} else {
				$returnPath .= $this->getName();
			}
		}
		return $returnPath;
	}

	/**
	 * setStateID - set the state id of this document group.
	 *
	 * @param	int	State ID.
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setStateID($stateid) {
		return $this->__setValueinDB('stateid', $stateid);
	}

	/**
	 * setParentDocGroupId - set the parent doc_group id of this document group.
	 *
	 * @param	int	Parent Doc_group Id.
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setParentDocGroupId($parentDocGroupId) {
		return $this->__setValueinDB('parent_doc_group', $parentDocGroupId);
	}

	/**
	 * __injectZip - private method to inject a zip archive tree and files
	 *
	 * @param	array	uploaded zip
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __injectZip($uploadedZip) {
		$zip = new ZipArchive();
		if ($zip->open($uploadedZip['tmp_name'])) {
			$extractDir = forge_get_config('data_path').'/'.uniqid();
			if ($zip->extractTo($extractDir)) {
				$zip->close();
				if ($this->__injectContent($extractDir)) {
					rmdir($extractDir);
					return true;
				} else {
					$this->setError(_('Unable inject zipfile.'));
					return false;
				}
			} else {
				$this->setError(_('Unable to extract zipfile.'));
				$zip->close();
				return false;
			}
		}
		$this->setError(_('Unable to open zipfile.'));
		return false;
	}

	/**
	 * __injectRar - private method to inject a rar archive tree and files
	 *
	 * @param	array	uploaded rar
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __injectRar($uploadedRar) {
		return true;
	}

	/**
	 * __injectContent - private method to inject a directory tree and files
	 *
	 * @param	string	the directory to inject
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __injectContent($directory) {
		if (is_dir($directory)) {
			$dir_arr = scandir($directory);
			for ($i = 0; $i < count($dir_arr); $i++) {
				if ($dir_arr[$i] != '.' && $dir_arr[$i] != '..') {
					if (is_dir($directory.'/'.$dir_arr[$i])) {
						if ($this->create($dir_arr[$i], $this->getID())) {
							if (!$this->__injectContent($directory.'/'.$dir_arr[$i])) {
								$this->setError(_('Unable to open directory for inject into tree'));
								return false;
							} else {
								rmdir($directory.'/'.$dir_arr[$i]);
							}
						}
					} else {
						$d = new Document($this->getGroup());
						if (function_exists('finfo_open')) {
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$dir_arr_type = finfo_file($finfo, $directory.'/'.$dir_arr[$i]);
						} else {
							$dir_arr_type = 'application/binary';
						}
						$data = fread(fopen($directory.'/'.$dir_arr[$i], 'r'), filesize($directory.'/'.$dir_arr[$i]));
						if (!$d->create($dir_arr[$i], $dir_arr_type, $data, $this->getID(), 'no title', 'no description')) {
							$this->setError(_('Unable to add document from zip injection.'));
							return false;
						} else {
							unlink($directory.'/'.$dir_arr[$i]);
						}
					}
				}
			}
			return true;
		} else {
			$this->setError(_('Unable to open directory for inject into tree'));
		}
	}

	/**
	 * __setValueinDB - private function to update columns in db
	 *
	 * @param	string	the column to update
	 * @param	int	the value to store
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __setValueinDB($column, $value) {
		switch ($column) {
			case "stateid":
			case "parent_doc_group": {
				$qpa = db_construct_qpa();
				$qpa = db_construct_qpa($qpa, 'UPDATE doc_groups SET ');
				$qpa = db_construct_qpa($qpa, $column);
				$qpa = db_construct_qpa($qpa, '=$1
								WHERE group_id=$2
								AND doc_group=$3',
								array($value,
									$this->Group->getID(),
									$this->getID()));
				$res = db_query_qpa($qpa);
				if (!$res || db_affected_rows($res) < 1) {
					$this->setOnUpdateError(db_error().print_r($res));
					return false;
				}
				break;
			}
			default:
				$this->setOnUpdateError(_('wrong column name'));
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

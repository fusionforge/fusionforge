<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2016, Franck Villaume - TrivialDev
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

require_once $gfcommon.'include/FFError.class.php';
require_once $gfcommon.'include/MonitorElement.class.php';

$DOCUMENTGROUP_OBJ = array();

/**
 * documentgroup_get_object() - Get document group object by document group ID.
 * documentgroup_get_object is useful so you can pool document group objects/save database queries
 * You should always use this instead of instantiating the object directly
 *
 * @param	int		$docgroup_id	The ID of the document group - required
 * @param	int		$group_id	Group ID of the project - required
 * @param	int|bool	$res	The result set handle ("SELECT * FROM doc_groups WHERE doc_group = $1")
 * @return	DocumentGroup	a document group object or false on failure
 */
function &documentgroup_get_object($docgroup_id, $group_id, $res = false) {
	global $DOCUMENTGROUP_OBJ;
	if (!isset($DOCUMENTGROUP_OBJ["_".$docgroup_id."_"])) {
		if ($res) {
			//the db result handle was passed in
		} else {
			$res = db_query_params('SELECT * FROM doc_groups WHERE doc_group = $1 and group_id = $2',
						array($docgroup_id, $group_id));
		}
		if (!$res || db_numrows($res) < 1) {
			$DOCUMENTGROUP_OBJ["_".$docgroup_id."_"] = false;
		} else {
			$DOCUMENTGROUP_OBJ["_".$docgroup_id."_"] = new DocumentGroup(group_get_object($group_id), db_fetch_array($res));
		}
	}
	return $DOCUMENTGROUP_OBJ["_".$docgroup_id."_"];
}

class DocumentGroup extends FFError {

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
	 * Use this constructor if you are modifying an existing doc_group.
	 *
	 * @param	$Group
	 * @param	bool	$data
	 * @internal	param	\Group $object object.
	 * @internal	param	array $OR doc_group id from database.
	 */
	function __construct(&$Group, $data = false) {
		parent::__construct();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}
		if ($Group->isError()) {
			$this->setError(_('Document Folder')._(': ').$Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
				if ($this->data_array['group_id'] != $this->Group->getID()) {
					$this->setError('DocumentGroup'._(': '). _('group_id in db result does not match Group Object'));
					$this->data_array = null;
					return;
				}
			} else {
				$this->fetchData($data);
			}
		}
		if ($this->getState() == 5 && !forge_check_perm('docman', $this->Group->getID(), 'approve')) {
			$this->data_array = null;
			$this->setError(_('Permission refused'));
			return;
		}
	}

	/**
	 * create - create a new item in the database.
	 *
	 * @param	string	$name			The name of the directory to create
	 * @param	int	$parent_doc_group	The ID of the parent directory
	 * @param	int	$state			The status of this directory: default is 1 = public.
	 *						Valid values are :
	 *							1 = public
	 *							2 = deleted
	 *							5 = private
	 * @param	int	$createtimestamp	Timestamp of the directory creation
	 * @internal	param	\Item $string name.
	 * @return	boolean	true on success / false on failure.
	 * @access	public
	 */
	function create($name, $parent_doc_group = 0, $state = 1, $createtimestamp = null, $forcecreate = false) {
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
				$this->setError(_('Invalid Documents Folder parent ID'));
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

		if (!$forcecreate) {
			$res = db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
						array($name,
							$parent_doc_group,
							$this->Group->getID())
						);
			if ($res && db_numrows($res) > 0) {
				$this->setError(_('Folder name already exists'));
				return false;
			}
		}

		$createtimestamp = (($createtimestamp) ? $createtimestamp : time());
		$user_id = ((session_loggedin()) ? user_getid() : 100);
		$result = db_query_params('INSERT INTO doc_groups (group_id, groupname, parent_doc_group, stateid, createdate, created_by) VALUES ($1, $2, $3, $4, $5, $6)',
						array ($this->Group->getID(),
							htmlspecialchars($name),
							$parent_doc_group,
							$state,
							$createtimestamp,
							$user_id)
						);
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
		} else {
			$this->setError(_('Error Adding Folder')._(': ').db_error());
			return false;
		}

		$doc_group = db_insertid($result, 'doc_groups', 'doc_group');

		// Now set up our internal data structures
		if (!$this->fetchData($doc_group)) {
			return false;
		}

		if ($parent_doc_group) {
			/* update the parent */
			$parentDg = documentgroup_get_object($parent_doc_group, $this->Group->getID());
			$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1, $parentDg->getState());
		}
		$this->sendNotice(true);
		return true;
	}

	/**
	 * delete - delete a DocumentGroup.
	 * WARNING delete is recursive and permanent
	 *
	 * @param	int	$doc_groupid
	 * @param	int	$project_group_id
	 * @internal	param	\Document $integer Group Id, integer Project Group Id
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

		if (!$result) {
			return false;
		}

		/* update the parent if any */
		if ($this->getParentID()) {
			$parentDg = documentgroup_get_object($this->getParentID(), $this->Group->getID());
			$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1, $parentDg->getState());
		}
		/* is there any subdir ? */
		$subdir = db_query_params('select doc_group from doc_groups where parent_doc_group = $1 and group_id = $2',
					array($doc_groupid, $project_group_id));
		/* make a recursive call */
		while ($arr = db_fetch_array($subdir)) {
			$this->delete($arr['doc_group'], $project_group_id);
		}

		return true;
	}

	/**
	 * injectArchive - extract the attachment and create the directory tree if needed
	 *
	 * @param	array	$uploaded_data	uploaded data
	 * @return	bool	success or not
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
			case 'application/zip': {
				$returned = $this->injectZip($uploaded_data);
				break;
			}
			case 'application/x-rar-compressed': {
				$returned = $this->injectRar($uploaded_data);
				break;
			}
			default: {
				$this->setError( _('Unsupported injected file')._(': ').$uploaded_data_type);
				$returned = false;
			}
		}
		return $returned;
	}

	/**
	 * fetchData - re-fetch the data for this DocumentGroup from the database.
	 *
	 * @param	int	$id	ID of the doc_group.
	 * @return	bool	success
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
		$this->data_array['numberFiles'] = array();
		db_free_result($res);
		return true;
	}

	/**
	 * getGroup - get the Group Object this DocumentGroup is associated with.
	 *
	 * @return	Object	Group.
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
	 * getCreatedate - get the creation date.
	 *
	 * @return	integer	The creation date.
	 * @access	public
	 */
	function getCreatedate() {
		return $this->data_array['createdate'];
	}

	/**
	 * getUpdatedate - get the update date.
	 *
	 * @return	integer	The update date.
	 * @access	public
	 */
	function getUpdatedate() {
		return $this->data_array['updatedate'];
	}

	/**
	 * getLastModifyDate - get the bigger value between update date and creation date.
	 *
	 * @return	integer	The last modified date.
	 * @access	public
	 */
	function getLastModifyDate() {
		if($this->data_array['updatedate']) {
			return $this->data_array['updatedate'];
		} else {
			return $this->data_array['createdate'];
		}

	}

	/**
	 * getMonitoredUserEmailAddress - get the email addresses of users who monitor this directory
	 *
	 * @return	string	The list of emails comma separated
	 */
	function getMonitoredUserEmailAddress() {
		$MonitorElementObject = new MonitorElement('docgroup');
		return $MonitorElementObject->getAllEmailsInCommatSeparated($this->getID());
	}

	/**
	 * isMonitoredBy - get the monitored status of this document directory for a specific user id.
	 *
	 * @param	string	$userid
	 * @internal	param	\User $int ID
	 * @return	boolean	true if monitored by this user
	 */
	function isMonitoredBy($userid = 'ALL') {
		$MonitorElementObject = new MonitorElement('docgroup');
		if ( $userid == 'ALL' ) {
			return $MonitorElementObject->isMonitoredByAny($this->getID());
		} else {
			return $MonitorElementObject->isMonitoredByUserId($this->getID(), $userid);
		}
	}

	/**
	 * removeMonitoredBy - remove this document directory for a specific user id for monitoring.
	 *
	 * @param	int	$userid	User ID
	 * @return	boolean	true if success
	 */
	function removeMonitoredBy($userid) {
		$MonitorElementObject = new MonitorElement('docgroup');
		if (!$MonitorElementObject->disableMonitoringByUserId($this->getID(), $userid)) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	/**
	 * addMonitoredBy - add this document for a specific user id for monitoring.
	 *
	 * @param	int	$userid	User ID
	 * @return	boolean	true if success
	 */
	function addMonitoredBy($userid) {
		$MonitorElementObject = new MonitorElement('docgroup');
		if (!$MonitorElementObject->enableMonitoringByUserId($this->getID(), $userid)) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	/**
	 * clearMonitor - remove all entries of monitoring for this document.
	 *
	 * @return	boolean	true if success.
	 */
	function clearMonitor() {
		$MonitorElementObject = new MonitorElement('docgroup');
		if (!$MonitorElementObject->clearMonitor($this->getID())) {
			$this->setError($MonitorElementObject->getErrorMessage());
			return false;
		}
		return true;
	}

	/**
	 * getCreated_by - get the creator (user) id.
	 *
	 * @return	integer	The User id.
	 * @access	public
	 */
	function getCreated_by() {
		return $this->data_array['created_by'];
	}

	/**
	 * update - update a DocumentGroup.
	 *
	 * @param	string	$name			Name of the category.
	 * @param	int	$parent_doc_group	the doc_group id of the parent. default = 0
	 * @param	int	$metadata		update only the metadata : created_by, updatedate
	 * @param	int	$state			state of the directory. Default is 1 = public. See create function for valid values
	 * @param	int	$updatetimestamp	Timestamp of the update
	 * @return	boolean	success or not
	 * @access	public
	 */
	function update($name, $parent_doc_group = 0, $metadata = 0, $state = 1, $updatetimestamp = null) {
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

		if (!$metadata) {
			$res = db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
						array($name,
							$parent_doc_group,
							$this->Group->getID())
						);
			if ($res && db_numrows($res) > 0) {
				$this->setError(_('Documents Folder name already exists'));
				return false;
			}
		}

		$user_id = ((session_loggedin()) ? user_getid() : 100);
		$updatetimestamp = (($updatetimestamp) ? $updatetimestamp : time());
		$colArr = array('groupname', 'parent_doc_group', 'updatedate', 'created_by', 'locked', 'locked_by', 'stateid');
		$valArr = array(htmlspecialchars($name), $parent_doc_group, $updatetimestamp, $user_id, 0, NULL, $state);
		if ($this->setValueinDB($colArr, $valArr)) {
			$parentDg = new DocumentGroup($this->Group, $parent_doc_group);
			if ($parentDg->getParentID())
				$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1, $parentDg->getState(), $updatetimestamp);

			$this->fetchData($this->getID());
			$this->sendNotice(false);
			return true;
		} else {
			$this->setOnUpdateError(_('Error')._(': '). db_error());
			return false;
		}
	}

	/**
	 * hasDocuments - Recursive function that checks if this group or any of it childs has documents associated to it
	 *
	 * A group has associated documents if and only if there are documents associated to this
	 * group or to any of its childs
	 *
	 * @param	array	$nested_groups
	 * @param	object	$document_factory
	 * @param	int	$stateid
	 * @internal	param	Array $array of nested groups information, fetched from DocumentGroupFactory class
	 * @internal	param	\The $object DocumentFactory object
	 * @internal	param	int $State of the documents
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


		$stateIdDg = 1;
		if (forge_check_perm('docman', $document_factory->Group->getID(), 'approve')) {
			$stateIdDg = 5;
		}
		$document_factory->setDocGroupID($doc_group_id);
		$document_factory->setDocGroupState($stateIdDg);
		// check if it has documents
		if ($stateid) {
			$document_factory->setStateID(array($stateid));
		} else {
			$document_factory->setStateID(array(1, 4, 5));
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

	function hasDocument($filename, $stateid = 1) {
		$result = db_query_params('SELECT docid from docdata_vw where filename = $1 and doc_group = $2 and stateid = $3',
				array($filename, $this->getID(), $stateid));

		if (!$result || db_numrows($result) > 0) {
			$row = db_fetch_array($result);
			return $row['docid'];
		}
		return false;
	}

	/**
	 * getNumberOfDocuments - get the number of files in this doc_group, group_id and for a document state
	 *
	 * @param	int	$stateId	the state id
	 * @return	int	the number of found documents
	 */
	function getNumberOfDocuments($stateId = 1) {
		if (isset($this->data_array['numberFiles'][$stateId]))
			return $this->data_array['numberFiles'][$stateId];

		$res = db_query_params('select count(*) from docdata_vw where doc_group = $1 and group_id = $2 and stateid = $3',
					array($this->getID(), $this->Group->getID(), $stateId));
		if (!$res) {
			return 0;
		}
		$arr = db_fetch_array($res);
		$this->data_array['numberFiles'][$stateId] = $arr[0];
		return $arr[0];
	}

	/**
	 * getSubgroup - Return the ids of any sub folders (first level only) in specific folder
	 *
	 * @param	int	$docGroupId	ID of the specific folder
	 * @param	array	$stateId	the state ids of this specific folder (default is 1)
	 * @return	array	the ids of any sub folders
	 */
	function getSubgroup($docGroupId, $stateId = array(1)) {
		$returnArr = array();
		$res = db_query_params('SELECT doc_group from doc_groups where parent_doc_group = $1 and stateid = ANY ($2) and group_id = $3 order by groupname',
							array($docGroupId, db_int_array_to_any_clause($stateId), $this->Group->getID()));
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
	 * @param	boolean	$url		does path is url clickable (default is false)
	 * @param	boolean	$includename	does path include this document group name ? (default is true)
	 * @return	string|boolean		the complete_path or false if user has not proper access to this path.
	 * @access	public
	 */
	function getPath($url = false, $includename = true) {
		if ($this->getState() != 1 && !forge_check_perm('docman', $this->Group->getID(), 'approve')) {
			return false;
		}
		$returnPath = '/';
		if ($this->getParentID()) {
			$parentDg = documentgroup_get_object($this->getParentID(), $this->Group->getID());
			if ($parentDg->isError()) {
				$this->setError = $parentDg->getErrorMessage();
				return false;
			}
			//need to check if user has access to this path. If not, return false.
			if ($parentDg->getState() != 1) {
				if (!forge_check_perm('docman', $this->Group->getID(), 'approve')) {
					return false;
				}
			}
			$returnPath = $parentDg->getPath($url);
			if (!$returnPath) {
				return false;
			}
		}
		if ($includename) {
			if ($url) {
				if ($this->getState() == 2) {
					$view = 'listtrashfile';
				} else {
					$view = 'listfile';
				}
				$browselink = '/docman/?view='.$view.'&dirid='.$this->getID();
				if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
					$browselink .= '&childgroup_id='.$GLOBALS['childgroup_id'];
				}
				$browselink .= '&group_id='.$this->Group->getID();
				$returnPath .= '/'.util_make_link($browselink, $this->getName(), array('title' => _('Browse this folder')));
			} else {
				$returnPath .= '/'.$this->getName();
			}
		}
		return $returnPath;
	}

	/**
	 * setStateID - set the state id of this document group.
	 *
	 * @param	int	$stateid	State ID.
	 * @param	bool	$recursive	set the state id recursively. (i.e. move the directory and his content to trash)
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setStateID($stateid, $recursive = false) {
		if ($recursive) {
			$df = new DocumentFactory($this->Group);
			if ($df->isError())
				exit_error($df->getErrorMessage(), 'docman');

			$dgf = new DocumentGroupFactory($this->Group);
			if ($dgf->isError())
				exit_error($dgf->getErrorMessage(), 'docman');

			$stateidArr = array(1, 3, 4, 5);
			$nested_groups =& $dgf->getNested($stateidArr);

			$df->setStateID($stateidArr);
			$df->setDocGroupID($this->getID());
			$d_arr =& $df->getDocuments();

			$nested_docs = array();
			/* put the doc objects into an array keyed of the docgroup */
			if (is_array($d_arr)) {
				foreach ($d_arr as $doc) {
					$nested_docs[$doc->getDocGroupID()][] = $doc;
				}
			}

			$localdocgroup_arr = array();
			$localdocgroup_arr[] = $this->getID();
			if (is_array($nested_groups[$this->getID()])) {
				foreach ($nested_groups[$this->getID()] as $dg) {
					if (!$dg->setStateID($stateid))
						return false;

					$localdocgroup_arr[] = $dg->getID();
					$localdf = new DocumentFactory($this->Group);
					$localdf->setDocGroupID($dg->getID());
					$d_arr =& $localdf->getDocuments();
					if (is_array($d_arr)) {
						foreach ($d_arr as $doc) {
							$nested_docs[$doc->getDocGroupID()][] = $doc;
						}
					}
				}
			}

			foreach ($localdocgroup_arr as $docgroup_id) {
				if (isset($nested_docs[$docgroup_id]) && is_array($nested_docs[$docgroup_id])) {
					foreach ($nested_docs[$docgroup_id] as $d) {
						if (!$d->setState($stateid))
							return false;
					}
				}
			}
		}
		return $this->setValueinDB(array('stateid'), array($stateid));
	}

	/**
	 * setParentDocGroupId - set the parent doc_group id of this document group.
	 *
	 * @param	int	$parentDocGroupId	Parent Doc_group Id.
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setParentDocGroupId($parentDocGroupId) {
		return $this->setValueinDB(array('parent_doc_group'), array($parentDocGroupId));
	}

	/**
	 * sendNotice - Notifies of directory submissions
	 *
	 * @param	boolean	$new	true = new directory (default value)
	 * @return	bool
	 */
	function sendNotice($new = true) {
		$BCC = $this->Group->getDocEmailAddress();
		if ($this->isMonitoredBy('ALL')) {
			$BCC .= $this->getMonitoredUserEmailAddress();
		}
		if (strlen($BCC) > 0) {
			$sess = session_get_user();
			if ($new) {
				$status = _('New Folder');
			} else {
				$status = _('Updated folder by').' '.$sess->getRealName();
			}
			$subject = '['.$this->Group->getPublicName().'] '.$status.' - '.$this->getName();
			$body = _('Project')._(': ').$this->Group->getPublicName()."\n";
			$body .= _('Folder')._(': ').$this->getName()."\n";
			$user = user_get_object($this->getCreated_by());
			$body .= _('Submitter')._(': ').$user->getRealName()." (".$user->getUnixName().") \n";
			if (!$new) {
				$body .= _('Updated by')._(': ').$sess->getRealName();
			}
			$body .= "\n\n-------------------------------------------------------\n".
				_('For more info, visit:').
				"\n\n" . util_make_url('/docman/?group_id='.$this->Group->getID().'&view=listfile&dirid='.$this->getID());

			$BCCarray = explode(',',$BCC);
			foreach ($BCCarray as $dest_email) {
				util_send_message($dest_email, $subject, $body, 'noreply@'.forge_get_config('web_host'), '', _('Docman'));
			}
		}
		return true;
	}

	/**
	 * trash - move this directory and his contents to trash
	 *
	 * @return	bool	success or not.
	 */
	function trash() {
		if (!$this->getLocked() || ((time() - $this->getLockdate()) > 600)) {
			//we need to move recursively all docs and all doc_groups in trash
			// aka setStateID to 2.
			if (!$this->setStateID(2, true))
				return false;

			$dm = new DocumentManager($this->Group);
			$this->setParentDocGroupId($dm->getTrashID());
			$this->setLock(0);
			$this->sendNotice(false);
			$this->clearMonitor();
			return true;
		}
		$this->setError(_('Unable to move this folder to trash. Folder locked.'));
		return false;
	}
	/**
	 * injectZip - private method to inject a zip archive tree and files
	 *
	 * @param	array	$uploadedZip	uploaded zip
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function injectZip($uploadedZip) {
		$zip = new ZipArchive();
		if ($zip->open($uploadedZip['tmp_name'])) {
			$extractDir = sys_get_temp_dir().'/'.uniqid();
			if ($zip->extractTo($extractDir)) {
				$zip->close();
				if ($this->injectContent($extractDir)) {
					rmdir($extractDir);
					return true;
				} else {
					rmdir($extractDir);
					return false;
				}
			} else {
				$this->setError(_('Unable to extract ZIP file.'));
				$zip->close();
				return false;
			}
		}
		$this->setError(_('Unable to open ZIP file.'));
		return false;
	}

	/**
	 * injectRar - private method to inject a rar archive tree and files
	 *
	 * @param	array	$uploadedRar	uploaded rar
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function injectRar($uploadedRar) {
		return true;
	}

	/**
	 * injectContent - private method to inject a directory tree and files
	 *
	 * @param	string	$directory	the directory to inject
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function injectContent($directory) {
		if (is_dir($directory)) {
			$dir_arr = scandir($directory);
			for ($i = 0; $i < count($dir_arr); $i++) {
				if ($dir_arr[$i] != '.' && $dir_arr[$i] != '..') {
					if (is_dir($directory.'/'.$dir_arr[$i])) {
						$ndg = new DocumentGroup($this->getGroup());
						if ($ndg->create($dir_arr[$i], $this->getID())) {
							if (!$ndg->injectContent($directory.'/'.$dir_arr[$i])) {
								$this->setError($ndg->getErrorMessage());
								return false;
							}
						}
					} elseif (is_file($directory.'/'.$dir_arr[$i])) {
						$d = new Document($this->getGroup());
						if (function_exists('finfo_open')) {
							$finfo = finfo_open(FILEINFO_MIME_TYPE);
							$dir_arr_type = finfo_file($finfo, $directory.'/'.$dir_arr[$i]);
						} else {
							$dir_arr_type = 'application/binary';
						}
						if (util_is_valid_filename($dir_arr[$i])) {
							// ugly hack in case of ppl injecting zip at / when there is not directory in the ZIP file...
							// force upload in the first directory of the tree ...
							if (!$this->getID()) {
								$subGroupArrID = $this->getSubgroup(0);
								$this->data_array['doc_group'] = $subGroupArrID[0];
							}
							if (strlen($dir_arr[$i]) < 5) {
								$filename = $dir_arr[$i].' '._('(Title must be at least 5 characters.)');
							} else {
								$filename = $dir_arr[$i];
							}
							if (!$d->create($dir_arr[$i], $dir_arr_type, $directory.'/'.$dir_arr[$i], $this->getID(),
								$filename, _('Injected by ZIP:').date(DATE_ATOM))) {
								$this->setError($dir_arr[$i]._(': ').$d->getErrorMessage());
								return false;
							}
						} else {
							$this->setError($dir_arr[$i]._(': ')._('Invalid file name.'));
							return false;
						}
					} else {
						$this->setError($dir_arr[$i]._(': ')._('Unknown item.'));
						return false;
					}
				}
			}
			return true;
		} else {
			$this->setError(_('Unable to open folder for injecting into tree'));
			return false;
		}
	}

	/**
	 * getLocked - get the lock status of this doc_group.
	 *
	 * @return	int	The lock status of this doc_group.
	 */
	function getLocked() {
		return $this->data_array['locked'];
	}

	/**
	 * getLockdate - get the lock time of this doc_group.
	 *
	 * @return	int	The lock time of this doc_group.
	 */
	function getLockdate() {
		return $this->data_array['lockdate'];
	}

	/**
	 * getLockedBy - get the user id who set lock on this doc_group.
	 *
	 * @return	int	The user id who set lock on this doc_group.
	 */
	function getLockedBy() {
		return $this->data_array['locked_by'];
	}

	/**
	 * setLock - set the locking status of the doc_group.
	 * recursive call. we lock all subfolders...
	 *
	 * @param	int	$stateLock	the status to be set
	 * @param	string	$userid		the lock owner
	 * @param	int	$thistime	the epoch time
	 * @internal	param	\The $int status of the lock.
	 * @internal	param	\The $int userid who set the lock.
	 * @return	boolean	success or not.
	 */
	function setLock($stateLock, $userid = NULL, $thistime = 0) {
		$colArr = array('locked', 'locked_by', 'lockdate');
		$valArr = array($stateLock, $userid, $thistime);
		if (!$this->setValueinDB($colArr, $valArr)) {
			return false;
		}
		$this->data_array['locked'] = $stateLock;
		$this->data_array['locked_by'] = $userid;
		$this->data_array['lockdate'] = $thistime;
		$subGroupArray = $this->getSubgroup($this->getID(), array($this->getState()));
		foreach ($subGroupArray as $docgroupId) {
			$ndg = documentgroup_get_object($docgroupId, $this->Group->getID());
			$ndg->setLock($stateLock, $userid, $thistime);
		}
		return true;
	}

	/**
	 * setValueinDB - private function to update columns in db
	 *
	 * @param	array	$colArr	the columns to update in array form array('col1', col2')
	 * @param	int	$valArr	the values to store in array form array('val1', 'val2')
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function setValueinDB($colArr, $valArr) {
		if ((count($colArr) != count($valArr)) || !count($colArr) || !count($valArr)) {
			$this->setOnUpdateError(_('wrong parameters'));
			return false;
		}

		$qpa = db_construct_qpa(false, 'UPDATE doc_groups SET ');
		for ($i = 0; $i < count($colArr); $i++) {
			switch ($colArr[$i]) {
				case 'groupname':
				case 'parent_doc_group':
				case 'updatedate':
				case 'created_by':
				case 'locked':
				case 'locked_by':
				case 'stateid':
				case 'lockdate': {
					if ($i) {
						$qpa = db_construct_qpa($qpa, ',');
					}
					$qpa = db_construct_qpa($qpa, $colArr[$i]);
					$qpa = db_construct_qpa($qpa, '=$1 ', array($valArr[$i]));
					break;
				}
				default: {
					$this->setOnUpdateError(_('wrong column name'));
					return false;
				}
			}
		}
		$qpa = db_construct_qpa($qpa, ' WHERE group_id=$1
						AND doc_group=$2',
						array($this->Group->getID(),
							$this->getID()));
		$res = db_query_qpa($qpa);
		if (!$res || db_affected_rows($res) < 1) {
			$this->setOnUpdateError(db_error());
			return false;
		}
		for ($i = 0; $i < count($colArr); $i++) {
			switch ($colArr[$i]) {
				case 'groupname':
				case 'parent_doc_group':
				case 'updatedate':
				case 'created_by':
				case 'locked':
				case 'locked_by':
				case 'stateid':
				case 'lockdate': {
					$this->data_array[$colArr[$i]] = $valArr[$i];
				}
			}
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010, Franck Villaume - Capgemini
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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
	 * @param	$Group
	 * @param	bool	$data
	 * @internal	param	\Group $object object.
	 * @internal	param	array $OR doc_group id from database.
	 * @return	\DocumentGroup
	 * @access	public
	 */
	function __construct(&$Group, $data = false) {
		$this->Error();

		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
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
					$this->setError('DocumentGroup: '. _('group_id in db result does not match Group Object'));
					$this->data_array = null;
					return;
				}
				return;
			} else {
				$this->fetchData($data);
			}
		}
	}

	/**
	 * create - create a new item in the database.
	 *
	 * @param	$name
	 * @param	int	$parent_doc_group
	 * @internal	param	\Item $string name.
	 * @return	boolean	true on success / false on failure.
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

		$res = db_query_params('SELECT * FROM doc_groups WHERE groupname=$1 AND parent_doc_group=$2 AND group_id=$3',
					array($name,
						$parent_doc_group,
						$this->Group->getID())
					);
		if ($res && db_numrows($res) > 0) {
			$this->setError(_('Folder name already exists'));
			return false;
		}

		$user_id = ((session_loggedin()) ? user_getid() : 100);
		$result = db_query_params('INSERT INTO doc_groups (group_id, groupname, parent_doc_group, stateid, createdate, created_by) VALUES ($1, $2, $3, $4, $5, $6)',
						array ($this->Group->getID(),
							htmlspecialchars($name),
							$parent_doc_group,
							'1',
							time(),
							$user_id)
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

		if ($parent_doc_group) {
			/* update the parent */
			$parentDg = new DocumentGroup($this->Group, $parent_doc_group);
			$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1, 0);
		}
		$this->sendNotice(true);
		return true;
	}

	/**
	 * delete - delete a DocumentGroup.
	 * WARNING delete is recursive and permanent
	 *
	 * @param	$doc_groupid
	 * @param	$project_group_id
	 * @internal	param		\Document $integer Group Id, integer Project Group Id
	 * @return	boolean		success
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

		/* update the parent */
		$parentDg = new DocumentGroup($this->Group, $this->getParentID());
		$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1, 1);
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
				$returned = $this->injectZip($uploaded_data);
				break;
			}
			case "application/x-rar-compressed": {
				$returned = $this->injectRar($uploaded_data);
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
		$this->data_array['numberFiles'] = array();
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
		$result = db_query_params('select users.email from users,docgroup_monitored_docman where users.user_id = docgroup_monitored_docman.user_id and docgroup_monitored_docman.docgroup_id = $1', array ($this->getID()));
		if (!$result || db_numrows($result) < 1) {
			return NULL;
		} else {
			$values = '';
			$comma = '';
			$i = 0;
			while ($arr = db_fetch_array($result)) {
				if ( $i > 0 )
					$comma = ',';

				$values .= $comma.$arr['email'];
				$i++;
			}
		}
		return $values;
	}

	/**
	 * isMonitoredBy - get the monitored status of this document directory for a specific user id.
	 *
	 * @param	string	$userid
	 * @internal	param	\User $int ID
	 * @return	boolean	true if monitored by this user
	 */
	function isMonitoredBy($userid = 'ALL') {
		if ( $userid == 'ALL' ) {
			$condition = '';
		} else {
			$condition = 'user_id = '.$userid.' AND';
		}
		$result = db_query_params('SELECT * FROM docgroup_monitored_docman WHERE '.$condition.' docgroup_id = $1',
						array($this->getID()));

		if (!$result || db_numrows($result) < 1)
			return false;

		return true;
	}

	/**
	 * removeMonitoredBy - remove this document directory for a specific user id for monitoring.
	 *
	 * @param	int	User ID
	 * @return	boolean	true if success
	 */
	function removeMonitoredBy($userid) {
		$result = db_query_params('DELETE FROM docgroup_monitored_docman WHERE docgroup_id = $1 AND user_id = $2',
						array($this->getID(), $userid));

		if (!$result) {
			$this->setError(_('Unable To Remove Monitor').' : '.db_error());
			return false;
		}
		return true;
	}

	/**
	 * addMonitoredBy - add this document for a specific user id for monitoring.
	 *
	 * @param	int	User ID
	 * @return	boolean	true if success
	 */
	function addMonitoredBy($userid) {
		$result = db_query_params('SELECT * FROM docgroup_monitored_docman WHERE user_id=$1 AND docgroup_id = $2',
						array($userid, $this->getID()));

		if (!$result || db_numrows($result) < 1) {
			$result = db_query_params('INSERT INTO docgroup_monitored_docman (docgroup_id,user_id) VALUES ($1,$2)',
							array($this->getID(), $userid));

			if (!$result) {
				$this->setError(_('Unable To Add Monitor').' : '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 * clearMonitor - remove all entries of monitoring for this document.
	 *
	 * @return	boolean	true if success.
	 */
	function clearMonitor() {
		$result = db_query_params('DELETE FROM docgroup_monitored_docman WHERE docgroup_id = $1',
					array($this->getID()));
		if (!$result) {
			$this->setError(_('Unable To Clear Monitor').' : '.db_error());
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
	 * @param	string	Name of the category.
	 * @param	integer	the doc_group id of the parent. default = 0
	 * @param	integer	update only the metadata : created_by, updatedate
	 * @return	boolean	success or not
	 * @access	public
	 */
	function update($name, $parent_doc_group = 0, $metadata = 0) {
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
		$result = db_query_params('UPDATE doc_groups SET groupname=$1, parent_doc_group=$2, updatedate=$3, created_by=$4 WHERE doc_group=$5 AND group_id=$6',
						array(htmlspecialchars($name),
							$parent_doc_group,
							time(),
							$user_id,
							$this->getID(),
							$this->Group->getID())
					);
		if ($result && db_affected_rows($result) > 0) {
			$parentDg = new DocumentGroup($this->Group, $parent_doc_group);
			if ($parentDg->getParentID())
				$parentDg->update($parentDg->getName(), $parentDg->getParentID(), 1);

			$this->fetchData($this->getID());
			$this->sendNotice(false);
			return true;
		} else {
			$this->setOnUpdateError(sprintf(_('Error: %s'), db_error()));
			return false;
		}
	}

	/**
	 * hasDocuments - Recursive function that checks if this group or any of it childs has documents associated to it
	 *
	 * A group has associated documents if and only if there are documents associated to this
	 * group or to any of its childs
	 *
	 * @param	$nested_groups
	 * @param	$document_factory
	 * @param	int		$stateid
	 * @internal	param		Array $array of nested groups information, fetched from DocumentGroupFactory class
	 * @internal	param		\The $object DocumentFactory object
	 * @internal	param		int $State of the documents
	 * @return	boolean		success
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

	/**
	 * getSubgroup - Return the ids of any sub folders (first level only) in specific folder
	 *
	 * @param	int	ID of the specific folder
	 * @param	int	the state id of this specific folder (default is 1)
	 * @return	array	the ids of any sub folders
	 */
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

		$returnPath = '';
		if ($this->getParentID()) {
			$parentDg = new DocumentGroup($this->Group, $this->getParentID());
			$returnPath = $parentDg->getPath($url);
		}
		if ($includename) {
			if ($url) {
				$browselink = '/docman/?view=listfile&dirid='.$this->getID();
				if (isset($GLOBALS['childgroup_id']) && $GLOBALS['childgroup_id']) {
					$browselink .= '&childgroup_id='.$GLOBALS['childgroup_id'];
				}
				$browselink .= '&group_id='.$this->Group->getID();
				$returnPath .= '/'.util_make_link($browselink, $this->getName(), array('title' => _('Browse this folder'), 'class' => 'tabtitle'));
			} else {
				$returnPath .= '/'.$this->getName();
			}
		}
		if (!strlen($returnPath))
			$returnPath = '/';

		return $returnPath;
	}

	/**
	 * setStateID - set the state id of this document group.
	 *
	 * @param	int	$stateid	State ID.
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setStateID($stateid) {
		return $this->setValueinDB('stateid', $stateid);
	}

	/**
	 * setParentDocGroupId - set the parent doc_group id of this document group.
	 *
	 * @param	int	Parent Doc_group Id.
	 * @return	boolean	success or not.
	 * @access	public
	 */
	function setParentDocGroupId($parentDocGroupId) {
		return $this->setValueinDB('parent_doc_group', $parentDocGroupId);
	}

	/**
	 * sendNotice - Notifies of directory submissions
	 *
	 * @param	boolean	true = new directory (default value)
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
	 * @param	array	uploaded rar
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
								$this->setError($dir_arr[$i].': '.$d->getErrorMessage());
								return false;
							}
						} else {
							$this->setError($dir_arr[$i].': '._('Invalid file name.'));
							return false;
						}
					} else {
						$this->setError($dir_arr[$i].': '._('Unknown item.'));
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
	 * setValueinDB - private function to update columns in db
	 *
	 * @param	string	$column	the column to update
	 * @param	int	$value	the value to store
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function setValueinDB($column, $value) {
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

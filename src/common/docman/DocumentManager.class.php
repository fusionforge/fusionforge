<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2011-2014,2016, Franck Villaume - TrivialDev
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, French Ministry of National Education
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
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';
require_once $gfcommon.'docman/DocumentFactory.class.php';

class DocumentManager extends FFError {

	/**
	 * Associative array of data from db.
	 *
	 * @var	 array	$data_array.
	 */
	var $data_array;

	/**
	 * The Group object.
	 *
	 * @var	object	$Group.
	 */
	var $Group;

	/**
	 * @param	$Group
	 * @internal	param	\The $object Group object to which this document is associated.
	 */
	function __construct(&$Group) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('No Valid Group Object'));
			return;
		}
		if ($Group->isError()) {
			$this->setError('DocumentManager: '. $Group->getErrorMessage());
			return;
		}
		$this->Group =& $Group;
	}

	/**
	 * getGroup - get the Group object this Document is associated with.
	 *
	 * @return	Object	The Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 * getTrashID - the trash doc_group id for this DocumentManager.
	 *
	 * @return	integer	The trash doc_group id.
	 */
	function getTrashID() {
		if (isset($this->data_array['trashid']))
			return $this->data_array['trashid'];

		$res = db_query_params('SELECT doc_group from doc_groups
					WHERE groupname = $1
					AND group_id = $2
					AND stateid = $3',
					array('.trash', $this->Group->getID(), '2'));
		if (db_numrows($res) == 1) {
			$arr = db_fetch_array($res);
			$this->data_array['trashid'] = $arr['doc_group'];
			return $this->data_array['trashid'];
		} else {
			$dg = new DocumentGroup($this->Group);
			$dg->create('.trash');
			$dg->setStateID('2');
			return $dg->getID();
		}
	}

	/**
	 * cleanTrash - delete all items in trash for this DocumentManager
	 *
	 * @return	boolean	true on success
	 */
	function cleanTrash() {
		$trashId = $this->getTrashID();
		if ($trashId !== -1) {
			db_begin();
			$result = db_query_params('select docid FROM doc_data WHERE stateid=$1 and group_id=$2', array('2', $this->Group->getID()));
			$emptyFile = db_query_params('DELETE FROM doc_data WHERE stateid=$1 and group_id=$2', array('2', $this->Group->getID()));
			if (!$emptyFile) {
				db_rollback();
				return false;
			}
			$emptyDir = db_query_params('DELETE FROM doc_groups WHERE stateid=$1 and group_id=$2 and groupname !=$3', array('2', $this->Group->getID(), '.trash'));
			if (!$emptyDir) {
				db_rollback();
				return false;
			}
			while ($arr = db_fetch_array($result)) {
				DocumentStorage::instance()->delete($arr['docid'])->commit();
			}
			db_commit();
			return true;
		}
		return false;
	}

	/**
	 * isTrashEmpty - check if the trash is empty
	 * @return	boolean	success or not
	 */
	function isTrashEmpty() {
		if ($this->Group->usesPlugin('projects-hierarchy')) {
			$projectsHierarchy = plugin_get_object('projects-hierarchy');
			$projectIDsArray = $projectsHierarchy->getFamily($this->Group->getID(), 'child', true, 'validated');
		}


		if (isset($projectIDsArray) && is_array($projectIDsArray)) {
			foreach ($projectIDsArray as $projectID) {
				$groupObject = group_get_object($projectID);
				if ($groupObject->usesDocman() && $projectsHierarchy->getDocmanStatus($groupObject->getID())
					&& forge_check_perm('docman', $groupObject->getID(), 'approve')) {
					$groupIdArr[] = $projectID;
				}
			}
		}
		$groupIdArr[] = $this->Group->getID();

		$res = db_query_params('select ( select count(*) from doc_groups where group_id = ANY ($1) and stateid = 2 and groupname !=$2 )
					+ ( select count(*) from docdata_vw where group_id = ANY ($3) and stateid = 2 ) as c',
					array(db_int_array_to_any_clause($groupIdArr), '.trash', db_int_array_to_any_clause($groupIdArr)));

		if (!$res) {
			return false;
		}
		return (db_result($res, 0, 'c') == 0);
	}

	/**
	 *  getTree - display recursively the content of the doc_group. Only doc_groups within doc_groups.
	 *
	 * @param	int	$selecteddir	the selected directory
	 * @param	string	$linkmenu	the type of link in the menu
	 * @param	int	$docGroupId	the doc_group to start: default 0
	 */
	function getTree($selecteddir, $linkmenu, $docGroupId = 0) {
		global $g; // the master group of all the groups .... anyway. Needed to support projects-hierarchy plugin
		$dg = new DocumentGroup($this->Group);
		switch ($linkmenu) {
			case 'listtrashfile': {
				$stateId = 2;
				$doc_group_stateid = array(2);
				break;
			}
			default: {
				$stateId = 1;
				$doc_group_stateid = array(1);
				if (forge_check_perm('docman', $this->Group->getID(), 'approve')) {
					$doc_group_stateid = array(1, 3, 4, 5);
				}
				break;
			}
		}
		$subGroupIdArr = $dg->getSubgroup($docGroupId, $doc_group_stateid);
		if (sizeof($subGroupIdArr)) {
			foreach ($subGroupIdArr as $subGroupIdValue) {
				$localDg = documentgroup_get_object($subGroupIdValue, $this->Group->getID());
				$liclass = 'docman_li_treecontent';
				if ($selecteddir == $localDg->getID()) {
					$liclass = 'docman_li_treecontent_selected';
				}
				// support projects-hierarchy plugin
				if ($this->Group->getID() != $g->getID()) {
					$link = '/docman/?group_id='.$g->getID().'&view='.$linkmenu.'&dirid='.$localDg->getID().'&childgroup_id='.$this->Group->getID();
				} else {
					$link = '/docman/?group_id='.$this->Group->getID().'&view='.$linkmenu.'&dirid='.$localDg->getID();
				}
				$nbDocsLabel = '';
				$nbDocs = $localDg->getNumberOfDocuments($stateId);
				if ($stateId == 1 && forge_check_perm('docman', $this->Group->getID(), 'approve')) {
					$nbDocsPending = $localDg->getNumberOfDocuments(3);
					$nbDocsHidden = $localDg->getNumberOfDocuments(4);
					$nbDocsPrivate = $localDg->getNumberOfDocuments(5);
				}

				if ($stateId == 2 && forge_check_perm('docman', $this->Group->getID(), 'approve')) {
					$nbDocsTrashed = $localDg->getNumberOfDocuments(2);
				}

				if ($nbDocs && (!isset($nbDocsPending) || $nbDocsPending == 0) && (!isset($nbDocsHidden) || $nbDocsHidden == 0) && (!isset($nbDocsPrivate) || $nbDocsPrivate) && (!isset($nbDocsTrashed) || $nbDocsTrashed)) {
					$nbDocsLabel = html_e('span', array('title' => _('Number of documents in this folder')), '('.$nbDocs.')', false);
				}
				if (isset($nbDocsPending) && isset($nbDocsHidden) && isset($nbDocsPrivate)) {
					$nbDocsLabel = html_e('span', array('title' => _('Number of documents in this folder per status. active/pending/hidden/private')), '('.$nbDocs.'/'.$nbDocsPending.'/'.$nbDocsHidden.'/'.$nbDocsPrivate.')', false);
				}
				if (isset($nbDocsTrashed)) {
					$nbDocsLabel = html_e('span', array('title' => _('Number of deleted documents in this folder')), '('.$nbDocsTrashed.')', false);
				}
				if ($localDg->getName() != '.trash') {
					$lititle = '';
					if ($localDg->getCreated_by()) {
						$user = user_get_object($localDg->getCreated_by());
						$lititle .= _('Created by')._(': ').$user->getRealName();
					}
					if ($localDg->getLastModifyDate()) {
						if ($lititle) {
							$lititle .= _('; ');
						}
						$lititle .= _('Last Modified')._(': ').relative_date($localDg->getLastModifyDate());
					}
					$linkname = $localDg->getName();
					if ($localDg->getState() == 5) {
						$linkname .= ' '._('(private)');
					}
					//use &nbsp + inline to support Chrome browser correctly
					echo html_ao('li', array('id' => 'leaf-'.$subGroupIdValue, 'class' => $liclass)).'&nbsp;'.util_make_link($link, $localDg->getName(), array('title'=>$lititle, 'style' => 'display: inline')).$nbDocsLabel;
				} else {
					echo html_ao('li', array('id' => 'leaf-'.$subGroupIdValue, 'class' => $liclass)).'&nbsp;'.util_make_link($link, $localDg->getName(), array('style' => 'display: inline')).$nbDocsLabel;
				}
				if ($dg->getSubgroup($subGroupIdValue, $doc_group_stateid)) {
					echo html_ao('ul', array('class' => 'simpleTreeMenu'));
					$this->getTree($selecteddir, $linkmenu, $subGroupIdValue);
					echo html_ac(html_ap() - 1);
				}
				echo html_ac(html_ap() -1);
			}
		}
	}

	/**
	 * getStatusNameList - get all status for documents
	 *
	 * @param	string	$format		format of the return values. json returns : { name: id, }. Default is DB object.
	 * @param	string	$removedval	skipped status id
	 * @return	resource|string
	 */
	function getStatusNameList($format = '', $removedval = '') {
		if (!empty($removedval)) {
			$stateQuery = db_query_params('select * from doc_states where stateid not in ($1) order by stateid', array($removedval));
		} else {
			$stateQuery = db_query_params('select * from doc_states order by stateid', array());
		}
		switch ($format) {
			case 'json': {
				$returnString = '{';
				while ($stateArr = db_fetch_array($stateQuery)) {
					$returnString .= util_html_secure($stateArr['name']).': \''.$stateArr['stateid'].'\',';
				}
				$returnString .= '}';
				return $returnString;
			}
			default: {
				return $stateQuery;
			}
		}
	}

	/**
	 * getDocGroupList - Returns as a string used in javascript the list of available folders
	 *
	 * @param	array	$nested_groups
	 * @param	string	$format		must be json which is wrong, this function does not return any json object
	 * @param	bool	$allow_none	allow the "None" which is the "/"
	 * @param	int	$selected_id	the selected folder id
	 * @param	array	$dont_display	folders id to not display
	 * @return	string
	 */
	function getDocGroupList($nested_groups, $format = '', $allow_none = true, $selected_id = 0, $dont_display = array()) {
		$id_array = array();
		$text_array = array();
		$this->buildArrays($nested_groups, $id_array, $text_array, $dont_display);
		$rows = count($id_array);
		switch ($format) {
			case 'json': {
				$returnString = '[';
				for ($i = 0; $i < $rows; $i++) {
					$returnString .= '['.$id_array[$i].',\''.util_html_secure(addslashes($text_array[$i])).'\'],';
				}
				$returnString .= ']';
				break;
			}
		}
		return $returnString;
	}

	/**
	 * showSelectNestedGroups - Display the tree of document groups inside a <select> tag
	 *
	 * @param	array	$group_arr	Array of groups.
	 * @param	string	$select_name	The name that will be assigned to the input
	 * @param	bool	$allow_none	Allow selection of "None"
	 * @param	int	$selected_id	The ID of the group that should be selected by default (if any)
	 * @param	array	$dont_display	Array of IDs of groups that should not be displayed
	 * @param	bool	$display_files	Display filename instead of directory name only.
	 * @return	string	html select box code
	 */
	function showSelectNestedGroups($group_arr, $select_name, $allow_none = true, $selected_id = 0, $dont_display = array(), $display_files = false) {
		// Build arrays for calling html_build_select_box_from_arrays()
		$id_array = array();
		$text_array = array();

		if ($allow_none) {
			// First option to be displayed
			$id_array[] = 0;
			$text_array[] = _('None');
		}

		// Recursively build the document group tree
		$this->buildArrays($group_arr, $id_array, $text_array, $dont_display, 0, 0, $display_files);

		return html_build_select_box_from_arrays($id_array, $text_array, $select_name, $selected_id, false);
	}

	/**
	 * buildArrays - Build the arrays to call html_build_select_box_from_arrays()
	 *
	 * @param	array	$group_arr	Array of groups.
	 * @param	array	$id_array	Reference to the array of ids that will be build
	 * @param	array	$text_array	Reference to the array of group names
	 * @param	array	$dont_display	Array of IDs of groups that should not be displayed
	 * @param	int	$parent		The ID of the parent whose childs are being showed (0 for root groups)
	 * @param	int	$level		The current level
	 * @param	bool	$display_files	Set filename instead of directory name.
	 */
	function buildArrays($group_arr, &$id_array, &$text_array, &$dont_display, $parent = 0, $level = 0, $display_files = false) {
		if (!is_array($group_arr) || !array_key_exists("$parent", $group_arr)) return;

		$child_count = count($group_arr["$parent"]);
		for ($i = 0; $i < $child_count; $i++) {
			$doc_group =& $group_arr["$parent"][$i];

			// Should we display this element?
			if (in_array($doc_group->getID(), $dont_display)) continue;

			$margin = str_repeat("--", $level);

			if (!$display_files) {
				$id_array[] = $doc_group->getID();
				$text_array[] = $margin.$doc_group->getName();
			} else {
				$df = new DocumentFactory($doc_group->getGroup());
				$df->setDocGroupID($doc_group->getID());
				if (forge_check_perm('docman', $this->getGroup()->getID(), 'approve')) {
					$stateIdDg = 5;
				}
				$df->setStateID(array(1, 4, 5));
				$df->setDocGroupState($stateIdDg);
				$docs = $df->getDocuments();
				if (is_array($docs)) {
					foreach ($docs as $doc) {
						if (!$doc->isURL()) {
							$id_array[] = $doc->getID();
							$text_array[] = $margin.$doc_group->getName().'/'.$doc->getFileName();
						}
					}
				}
			}
			// Show childs (if any)
			$this->buildArrays($group_arr, $id_array, $text_array, $dont_display, $doc_group->getID(), $level+1, $display_files);
		}
	}

	/**
	 * getActivity - return the number of searched actions per sections between two dates
	 *
	 * @param	array	$sections	Sections to search for activity
	 * @param	int	$begin		the start date time format time()
	 * @param	int	$end		the end date time format time()
	 * @return	array	number per section of activities found between begin and end values
	 */
	function getActivity($sections, $begin, $end) {
		$results = array();
		for ($i = 0; $i < count($sections); $i++) {
			$results[$sections[$i]] = 0;
			$union = 0;
			if (count($sections) >= 1 && $i != count($sections) -1) {
				$union = 1;
			}
			$qpa = db_construct_qpa(false, 'SELECT count(*) FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
							AND group_id = $3 AND section = $4 ',
							array($begin,
								$end,
								$this->getGroup()->getID(),
								$sections[$i]));
			if ($union) {
				$qpa = db_construct_qpa($qpa, ' UNION ALL ', array());
			}
		}
		$res = db_query_qpa($qpa);
		$j = 0;
		while ($arr = db_fetch_array($res)) {
			$results[$sections[$j]] = $arr['0'];
			$j++;
		}
		return $results;
	}
}

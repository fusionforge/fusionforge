<?php
/**
 * FusionForge document manager
 *
 * Copyright 2011-2012, Franck Villaume - TrivialDev
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'include/User.class.php';
require_once $gfcommon.'docman/DocumentGroup.class.php';

class DocumentManager extends Error {

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
	 * Constructor.
	 *
	 * @param	object	The Group object to which this document is associated.
	 * @return	boolean	success.
	 */
	function __construct(&$Group) {
		$this->Error();
		if (!$Group || !is_object($Group)) {
			$this->setNotValidGroupObjectError();
			return false;
		}
		if ($Group->isError()) {
			$this->setError('DocumentManager:: '. $Group->getErrorMessage());
			return false;
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
		return false;
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
	 *  getTree - display recursively the content of the doc_group. Only doc_groups within doc_groups.
	 *
	 * @param	integer	the selected directory
	 * @param	string	the type of link in the menu
	 * @param	integer	the doc_group to start: default 0
	 */
	function getTree($selecteddir, $linkmenu, $docGroupId = 0) {
		global $g; // the master group of all the groups .... anyway.
		$dg = new DocumentGroup($this->Group);
		switch ($linkmenu) {
			case "listtrashfile": {
				$stateId = 2;
				break;
			}
			default: {
				$stateId = 1;
				break;
			}
		}
		$subGroupIdArr = $dg->getSubgroup($docGroupId, $stateId);
		if (sizeof($subGroupIdArr)) {
			foreach ($subGroupIdArr as $subGroupIdValue) {
				$localDg = new DocumentGroup($this->Group, $subGroupIdValue);
				$liclass = 'docman_li_treecontent';
				if ($selecteddir == $localDg->getID()) {
					$liclass = 'docman_li_treecontent_selected';
				}
				if ($this->Group->getID() != $g->getID()) {
					$link = '/docman/?group_id='.$g->getID().'&amp;view='.$linkmenu.'&amp;dirid='.$localDg->getID().'&amp;childgroup_id='.$this->Group->getID();
				} else {
					$link = '/docman/?group_id='.$this->Group->getID().'&amp;view='.$linkmenu.'&amp;dirid='.$localDg->getID();
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
					$nbDocsLabel = '<span class="tabtitle-nw" title="'._('Number of documents in this folder').'" >('.$nbDocs.')</span>';
				}
				if (isset($nbDocsPending) && isset($nbDocsHidden) && isset($nbDocsPrivate)) {
					$nbDocsLabel = '<span class="tabtitle-nw" title="'._('Number of documents in this folder per status. active/pending/hidden/private').'" >('.$nbDocs.'/'.$nbDocsPending.'/'.$nbDocsHidden.'/'.$nbDocsPrivate.')</span>';
				}
				if (isset($nbDocsTrashed)) {
					$nbDocsLabel = '<span class="tabtitle-nw" title="'._('Number of deleted documents in this folder').'" >('.$nbDocsTrashed.')</span>';
				}
				if ($localDg->getName() != '.trash') {
					$user = user_get_object($localDg->getCreated_by());
					$lititle = _('Created_by:').$user->getRealName()._('; Last modified:').date(_('Y-m-d H:i'), $localDg->getLastModifyDate());
					echo '<li id="leaf-'.$subGroupIdValue.'" class="'.$liclass.'">'.util_make_link($link, $localDg->getName(), array('class'=>'tabtitle-nw', 'title'=>$lititle)).$nbDocsLabel;
				} else {
					echo '<li id="leaf-'.$subGroupIdValue.'" class="'.$liclass.'">'.util_make_link($link, $localDg->getName()).$nbDocsLabel;
				}
				if ($dg->getSubgroup($subGroupIdValue, $stateId)) {
					echo '<ul>';
					$this->getTree($selecteddir, $linkmenu, $subGroupIdValue);
					echo '</ul>';
				}
				echo '</li>';
			}
		}
	}

	/**
	 * getStatusNameList - get all status for documents
	 *
	 * @param	string	format of the return values. json returns : { name: id, }. Default is DB object.
	 * @param	string	skipped status id
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
				break;
			}
			default: {
				return $stateQuery;
			}
		}
	}

	function getDocGroupList($nested_groups, $format = '', $allow_none = true, $selected_id = 0, $dont_display = array()) {
		$id_array = array();
		$text_array = array();
		$this->buildArrays($nested_groups, $id_array, $text_array, $dont_display);
		$rows = count($id_array);
		switch ($format) {
			case "json": {
				$returnString = '{';
				for ($i=0; $i<$rows; $i++) {
					$returnString .= '\''.util_html_secure($text_array[$i]).'\':'.$id_array[$i].',';
				}
				$returnString .= '}';
				break;
			}
		}
		return $returnString;
	}

	/**
	 * buildArrays - Build the arrays to call html_build_select_box_from_arrays()
	 *
	 * @param	array	Array of groups.
	 * @param	array	Reference to the array of ids that will be build
	 * @param	array	Reference to the array of group names
	 * @param	array	Array of IDs of groups that should not be displayed
	 * @param	int	The ID of the parent whose childs are being showed (0 for root groups)
	 * @param	int	The current level
	 */
	function buildArrays($group_arr, &$id_array, &$text_array, &$dont_display, $parent = 0, $level = 0) {
		if (!is_array($group_arr) || !array_key_exists("$parent", $group_arr)) return;

		$child_count = count($group_arr["$parent"]);
		for ($i = 0; $i < $child_count; $i++) {
			$doc_group =& $group_arr["$parent"][$i];

			// Should we display this element?
			if (in_array($doc_group->getID(), $dont_display)) continue;

			$margin = str_repeat("--", $level);

			$id_array[] = $doc_group->getID();
			$text_array[] = $margin.$doc_group->getName();

			// Show childs (if any)
			$this->buildArrays($group_arr, $id_array, $text_array, $dont_display, $doc_group->getID(), $level+1);
		}
	}

	/**
	 * getActivity - return the number of searched actions per sections between two dates
	 *
	 * @param	array	Sections to search for activity
	 * @param	int	the start date time format time()
	 * @param	int	the end date time format time()
	 * @return	array	number per section of activities found between begin and end values
	 */
	function getActivity($sections, $begin, $end) {
		$qpa = db_construct_qpa(false);
		for ($i = 0; $i < count($sections); $i++) {
			$union = 0;
			if (count($sections) >= 1 && $i != count($sections) -1) {
				$union = 1;
			}
			$qpa = db_construct_qpa($qpa, 'SELECT count(*) FROM activity_vw WHERE activity_date BETWEEN $1 AND $2
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
		$results = array();
		$j = 0;
		while ($arr = db_fetch_array($res)) {
			$results[$sections[$j]] = $arr['0'];
			$j++;
		}
		return $results;
	}
}

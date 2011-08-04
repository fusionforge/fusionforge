<?php
/**
 * FusionForge document manager
 *
 * Copyright 2011, Franck Villaume - TrivialDev
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
	function DocumentManager(&$Group) {
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

		return true;
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
			$emptyFile = db_query_params('DELETE FROM doc_data WHERE stateid=$1 and group_id=$2', array('2', $this->Group->getID()));
			if (!$emptyFile)	{
				db_rollback();
				return false;
			}
			$emptyDir = db_query_params('DELETE FROM doc_groups WHERE stateid=$1 and group_id=$2 and groupname !=$3', array('2', $this->Group->getID(), '.trash'));
			if (!$emptyDir) {
				db_rollback();
				return false;
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
			echo '<ul>';
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

				if ($nbDocs && (!isset($nbDocsPending) || $nbDocsPending == 0) && (!isset($nbDocsHidden) || $nbDocsHidden == 0) && (!isset($nbDocsPrivate) || $nbDocsPrivate)) {
					$nbDocsLabel = '<span class="tabtitle" title="'._('Number of documents in this folder').'" >('.$nbDocs.')</span>';
				}
				if (isset($nbDocsPending) && isset($nbDocsHidden) && isset($nbDocsPrivate)) {
					$nbDocsLabel = '<span class="tabtitle" title="'._('Number of documents in this folder per status. active/pending/hidden/private').'" >('.$nbDocs.'/'.$nbDocsPending.'/'.$nbDocsHidden.'/'.$nbDocsPrivate.')';
				}
				echo '<li class="'.$liclass.'">'.util_make_link($link, $localDg->getName()).$nbDocsLabel.'</li>';
				$this->getTree($selecteddir, $linkmenu, $subGroupIdValue);
			}
			echo '</ul>';
		}
	}
}

?>

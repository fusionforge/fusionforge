<?php
/**
 * FusionForge document manager
 *
 * Copyright 2011, Franck Villaume - TrivialDev
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
	 * @param	int	The docid.
	 * @param	array	The associative array of data.
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
	 * @return	int	The trash doc_group id.
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
			$this->setError('DocumentManager:: trash not found');
			return -1;
		}
	}
}

?>

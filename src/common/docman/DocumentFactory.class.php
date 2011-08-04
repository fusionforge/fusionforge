<?php
/**
 * FusionForge document manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
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

	/**
	 * The stateid limit
	 * @var	integer	Contains the stateid to limit return documents in getDocuments.
	 */
	var $stateid;

	/**
	 * The doc_group_id limit
	 * @var	integer	Contains the doc_group id to limit return documents in getDocuments.
	 */
	var $docgroupid;

	/**
	 * The sort order
	 * @var	string	Contains the order to return documents in getDocuments.
	 *		Default value is ASC
	 */
	var $sort = 'ASC';

	/**
	 * The columns order
	 * @var	array	Contains the order of columns to sort before return documents in getDocuments.
	 *		Default value is title order
	 */
	var $order = array('title');

	/**
	 * The limit
	 * @var	integer	Contains the limit of documents retrieve by getDocuments.
	 *		Default value is 0 which means NO LIMIT
	 */
	var $limit = 0;

	/**
	 * Constructor.
	 *
	 * @param	object	The Group object to which this DocumentFactory is associated.
	 * @return	boolean	success.
	 * @access	public
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
	 * @param	integer	The stateid from the doc_states table.
	 * @access	public
	 */
	function setStateID($stateid) {
		$this->stateid = $stateid;
	}

	/**
	 * setDocGroupID - call this before getDocuments() if you want to limit to a specific doc_group.
	 *
	 * @param	integer	The doc_group from the doc_groups table.
	 * @access	public
	 */
	function setDocGroupID($docgroupid) {
		$this->docgroupid = $docgroupid;
	}

	/**
	 * setSort - call this before getDocuments() if you want to order the query.
	 *
	 * @param	string	ASC or DESC : default ASC
	 * @access	public
	 */
	function setSort($sort) {
		if ( $sort != 'ASC' && $sort != 'DESC') {
			$this->sort = 'ASC';
		} else {
			$this->sort = $sort;
		}
	}

	/**
	 * setOrder - call this before getDocuments() if you want to sort the query.
	 *
	 * @param	array	Ordered Columns names: default title
	 * @access	public
	 */
	function setOrder($columns = array('title')) {
		// validate columns names
		$localColumns = array();
		foreach ($columns as $column) {
			switch ($column) {
				case "title": {
					$localColumns[] = "title";
					break;
				}
				case "createdate": {
					$localColumns[] = "createdate";
					break;
				}
				case "updatedate": {
					$localColumns[] = "updatedate";
					break;
				}
				case "user_name": {
					$localColumns[] = "user_name";
					break;
				}
				case "realname": {
					$localColumns[] = "realname";
					break;
				}
				case "email": {
					$localColumns[] = "email";
					break;
				}
				case "group_id": {
					$localColumns[] = "group_id";
					break;
				}
				case "docid": {
					$localColumns[] = "docid";
					break;
				}
				case "stateid": {
					$localColumns[] = "stateid";
					break;
				}
				case "create_by": {
					$localColumns[] = "create_by";
					break;
				}
				case "doc_group": {
					$localColumns[] = "doc_group";
					break;
				}
				case "description": {
					$localColumns[] = "description";
					break;
				}
				case "filename": {
					$localColumns[] = "filename";
					break;
				}
				case "filetype": {
					$localColumns[] = "filetype";
					break;
				}
				case "reserved": {
					$localColumns[] = "reserved";
					break;
				}
				case "reserved_by": {
					$localColumns[] = "reserved_by";
					break;
				}
				case "locked": {
					$localColumns[] = "locked";
					break;
				}
				case "locked_by": {
					$localColumns[] = "locked_by";
					break;
				}
				case "lockdate": {
					$localColumns[] = "lockdate";
					break;
				}
				case "state_name": {
					$localColumns[] = "state_name";
					break;
				}
				case "group_name": {
					$localColumns[] = "group_name";
					break;
				}
				default: {
					//unknown column: skip it
					break;
				}
			}
		}
		$this->order = $localColumns;
	}

	/**
	 * setLimit - call this before getDocuments() if you want to limit number of documents retrieve.
	 * default value is 0 which means : no limit.
	 *
	 * @param	int	The limit of documents
	 * @access	public
	 */
	function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 * getDocuments - returns an array of Document objects.
	 *
	 * @param	integer	no cache : force reinit $this->Documents : default: cache is used
	 * @return	array	Document objects.
	 * @access	public
	 */
	function &getDocuments($nocache = 0) {
		if (!$this->Documents || $nocache) {
			$this->__getFromStorage();
		}

		$return = array();

		// limit scope to the doc_group_id if any. Useful when you retrieve all documents
		if ($this->docgroupid) {
			$keys = array($this->docgroupid);
		} else {
			$keys = array_keys($this->Documents);
		}

		foreach ($keys as $key) {
			if (!array_key_exists($key, $this->Documents))	continue;		// Should not happen

			$count = count($this->Documents[$key]);

			for ($i=0; $i < $count; $i++) {
				$valid = true;							// do we need to return this document?
				$doc =& $this->Documents[$key][$i];

				if (!$this->stateid) {
					$perm =& $this->Group->getPermission();
					if (!$perm || !is_object($perm)) {
						if ($doc->getStateID() != 1) {
							$valid = false;
						}
						if ($perm->isEditor()) {
							$valid = true;
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

		if (count($return) === 0) {
			$this->setError(_('No Documents Found'));
			$return = NULL;
			return $return;
		}

		return $return;
	}

	/**
	 * __getFromStorage - Retrieve documents from storage API
	 *
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __getFromStorage() {
		$returned = false;
		switch ($this->Group->getStorageAPI()) {
			case 'DB': {
				if ($this->__getFromDB())
					$returned = true;
				break;
			}
			default: {
				$this->setError(_('No Storage API Found'));
				break;
			}
		}
		return $returned;
	}

	/**
	 * __getFromDB - Retrieve documents from database.
	 * you can limit query to speed up: warning, once $this->documents is retrieve, it's cached.
	 *
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function __getFromDB() {
		$this->Documents = array();
		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'SELECT * FROM docdata_vw WHERE group_id = $1 ',
						array($this->Group->getID()));

		if ($this->docgroupid) {
			$qpa = db_construct_qpa($qpa, 'AND doc_group = $1 ', array($this->docgroupid));
		}

		$qpa = db_construct_qpa($qpa, 'ORDER BY ');
		for ($i=0; $i<count($this->order); $i++) {
			$qpa = db_construct_qpa($qpa, $this->order[$i]);
			if (count($this->order) != $i + 1) {
				$qpa = db_construct_qpa($qpa, ',');
			} else {
				$qpa = db_construct_qpa($qpa, ' ');
			}
		}

		$qpa = db_construct_qpa($qpa, $this->sort);

		if ($this->limit !== 0 ) {
			$qpa = db_construct_qpa($qpa, ' LIMIT $1', array($this->limit));
		}

		$result = db_query_qpa($qpa);
		if (!$result) {
			$this->setError('getFromDB::'.db_error());
			return false;
		}

		while ($arr = db_fetch_array($result)) {
			$doc_group_id = $arr['doc_group'];
			if (!is_array(@$this->Documents[$doc_group_id])) {
				$this->Documents[$doc_group_id] = array();
			}
			$this->Documents[$doc_group_id][] = new Document($this->Group, $arr['docid'], $arr);
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

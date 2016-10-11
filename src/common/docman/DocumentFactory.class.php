<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2012 Alain Peyrat - Alcatel-Lucent
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
require_once $gfcommon.'docman/Document.class.php';

class DocumentFactory extends FFError {

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
	 * The stateid Array limit
	 * @var	array	Contains the different stateid to limit return documents in getDocuments.
	 */
	var $stateidArr = array();

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
	 * The docgroupstate
	 * @var	int	Contains the valid state of documentgroups to retrieve documents using getDocuments.
	 *		Default value is 1 which means public
	 */
	var $docgroupstate = 1;

	/**
	 * The offset
	 * @var	integer	Contains the offset of the query used to retrive documents using getDocuments.
	 *		Default value is 0 which means NO OFFSET
	 */
	var $offset = 0;

	var $validdocumentgroups = array();

	/**
	 * @param	$Group
	 * @internal	param	\The $object Group object to which this DocumentFactory is associated.
	 */
	function __construct(&$Group) {
		parent::__construct();
		if (!$Group || !is_object($Group)) {
			$this->setError(_('Invalid Project'));
			return;
		}

		if ($Group->isError()) {
			$this->setError('ProjectGroup: '.$Group->getErrorMessage());
			return;
		}

		$this->Group =& $Group;
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
	 * setStateID - call this before getDocuments() if you want to limit to some specific states.
	 *
	 * @param	array	$stateidArr	Array of stateid from the doc_states table.
	 * @access	public
	 */
	function setStateID($stateidArr) {
		$this->stateidArr = $stateidArr;
	}

	/**
	 * setDocGroupID - call this before getDocuments() if you want to limit to a specific doc_group.
	 *
	 * @param	int	$docgroupid	The doc_group from the doc_groups table.
	 * @access	public
	 */
	function setDocGroupID($docgroupid) {
		$this->docgroupid = $docgroupid;
	}

	/**
	 * setSort - call this before getDocuments() if you want to order the query.
	 *
	 * @param	string	$sort	ASC or DESC : default ASC
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
	 * @param	array	$columns	Ordered Columns names: default title
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
	 * @param	int	$limit	The limit of documents
	 * @access	public
	 */
	function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 * setDocGroupState - call this before getDocuments() to setup correct permission settings for retrieve authorized documents.
	 * default value is 1 which means : public.
	 *
	 * @param	array	$state	The array of valid state of documentgroups
	 * @access	public
	 */
	function setDocGroupState($state) {
		$this->docgroupstate = $state;
	}

	/**
	 * setOffset - call this before getDocuments() if you want to move to the offset in the query used to retrieve documents.
	 * default value is 0 which means : no offset.
	 *
	 * @param	int	$offset	The offset to use
	 * @access	public
	 */
	function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 * getDocuments - returns an array of Document objects.
	 *
	 * @param	int	$nocache	Force to reset the cached data if any available.
	 * @internal	param	\no $integer cache : force reinit $this->Documents : default: cache is used
	 * @return	array	Document objects.
	 * @access	public
	 */
	function getDocuments($nocache = 0) {
		if (!$this->Documents || $nocache) {
			$this->getFromStorage();
		}

		$return = array();

		// limit scope to the doc_group_id if any. Useful when you retrieve all documents in cache then filter
		if ($this->docgroupid) {
			$keys = array($this->docgroupid);
		} else {
			$keys = array_keys($this->Documents);
		}

		foreach ($keys as $key) {
			if (!array_key_exists($key, $this->Documents))	continue;		// Should not happen

			$count = count($this->Documents[$key]);
			for ($i=0; $i < $count; $i++) {
				$doc =& $this->Documents[$key][$i];
				if (in_array($doc->getStateID(), $this->stateidArr)) {
					$return[] =& $doc;
				}
			}
		}

		if (count($return) === 0) {
			$this->setError(_('No Documents Found'));
			return NULL;
		}

		return $return;
	}

	private function ValidDocumentGroups() {
		$this->validdocumentgroups = array();
		// recursive query to find if documentgroups are visible thru the tree of documentgroups
		$qpa = db_construct_qpa(false, 'WITH RECURSIVE doc_groups_parent(parent_doc_group, doc_group, stateid, group_id) AS
						( (SELECT parent_doc_group as anc, doc_group as desc, stateid as desc_stateid, group_id FROM doc_groups)
						UNION
						(select doc_groups_parent.parent_doc_group as anc, doc_groups.doc_group as desc, doc_groups_parent.stateid as desc_stateid, doc_groups.group_id
						FROM doc_groups_parent, doc_groups
						WHERE doc_groups_parent.doc_group = doc_groups.parent_doc_group ))
						select max(stateid), doc_group from doc_groups_parent where group_id = $1 group by doc_group having max(stateid) <= $2',
						array($this->Group->getID(), $this->docgroupstate));
		$result = db_query_qpa($qpa);
		if (!$result) {
			$this->setError('ValidDocumentGroups:'.db_error());
			return false;
		}
		while ($arr = db_fetch_array($result)) {
			$this->validdocumentgroups[] = $arr['doc_group'];
		}
	}

	/**
	 * getFromStorage - Retrieve documents from storage (database for all informations).
	 * you can limit query to speed up: warning, once $this->documents is retrieve, it's cached.
	 *
	 * @return	boolean	success or not
	 * @access	private
	 */
	private function getFromStorage() {
		$this->Documents = array();

		$qpa = db_construct_qpa(false, 'SELECT docdata_vw.* from docdata_vw, doc_groups
						WHERE docdata_vw.doc_group = doc_groups.doc_group
						and docdata_vw.group_id = $1 ', array($this->Group->getID()));

		if ($this->docgroupid) {
			$qpa = db_construct_qpa($qpa, 'AND docdata_vw.doc_group = $1 ', array($this->docgroupid));
		} else {
			$this->ValidDocumentGroups();
			$qpa = db_construct_qpa($qpa, 'AND docdata_vw.doc_group = ANY ($1) ',array(db_int_array_to_any_clause($this->validdocumentgroups)));
		}

		$qpa = db_construct_qpa($qpa, 'AND docdata_vw.stateid = ANY ($1) ', array(db_int_array_to_any_clause($this->stateidArr)));

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

		$result = db_query_qpa($qpa, $this->limit, $this->offset);
		if (!$result) {
			$this->setError('getFromStorage:'.db_error());
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

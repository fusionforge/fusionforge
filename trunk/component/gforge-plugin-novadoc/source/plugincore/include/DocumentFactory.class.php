<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: DocumentFactory.class.php,v 1.4 2006/11/22 10:17:24 pascal Exp $
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("common/include/Error.class.php");
require_once ("plugins/novadoc/include/Document.class.php");

class DocumentFactory extends Error {

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The Documents dictionary.
	 *
	 * @var	 array	Contains doc_group_id as key and the array of documents associated to that id. 
	 */
	var $Documents;
	var $stateid;
	var $languageid;
	var $docgroupid;
	var $sort='group_name, title';

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this DocumentFactory is associated.
	 *	@return	boolean	success.
	 */
	function DocumentFactory(&$Group) {
		$this->Error ();
		if (!$Group || !is_object($Group)) {
			$this->setError('ProjectGroup:: No Valid Group Object');
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
	 *	getGroup - get the Group object this DocumentFactory is associated with.
	 *
	 *	@return object	the Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	setStateID - call this before getDocuments() if you want to limit to a specific state.
	 *
	 *	@param	int	The stateid from the doc_states table.
	 */
	function setStateID($stateid) {
		$this->stateid=$stateid;
	}

	/**
	 *	setLanguageID - call this before getDocuments() if you want to limit to a specific language.
	 *
	 *	@param	int	The language_id from the supported_languages table.
	 */
	function setLanguageID($languageid) {
		$this->languageid=$languageid;
	}

	/**
	 *	setDocGroupID - call this before getDocuments() if you want to limit to a specific doc_group.
	 *
	 *	@param	int	The doc_group from the doc_groups table.
	 */
	function setDocGroupID($docgroupid) {
		$this->docgroupid=$docgroupid;
	}

	/**
	 *	setSort - call this before getDocuments() if you want to control the sorting.
	 *
	 *	@param	string	The name of the field to sort on.
	 */
	function setSort($sort) {
		$this->sort=$sort;
	}

	/**
	 *	getDocuments - returns an array of Document objects.
	 *
	 *	@return	array	Document objects.
	 */
	function &getDocuments() {
		global $Language;
		if (!$this->Documents) {
			$this->getFromDB();
		}
		
		$return = array();
		// If the document group is specified, we should only check that group in
		// the Documents array. If not, we should check ALL the groups.
		if ($this->docgroupid) {
			$keys = array($this->docgroupid);
		} else {
			$keys = array_keys($this->Documents);
		}
		
		foreach ($keys as $key) {
			if (!array_key_exists($key, $this->Documents)) continue;	// Should not happen
			$count = count($this->Documents[$key]);
			
			for ($i=0; $i < $count; $i++) {
				$valid = true;		// do we need to return this document?
				$doc =& $this->Documents[$key][$i];
				
				if (!$this->stateid) {
					if (session_loggedin()) {
						$perm =& $this->Group->getPermission( session_get_user() );
						if (!$perm || !is_object($perm) || !$perm->isMember()) {
							if ($doc->getStateID() != 1) {		// non-active document?
								$valid = false;
							}
						} else {
							if ($doc->getStateID() != 1 &&		/* not active */
								$doc->getStateID() != 4 &&			/* not hidden */
								$doc->getStateID() != 5) {			/* not private */
								$valid = false;
							}
						}
					} else {
						if ($doc->getStateID() != 1) {		// non-active document?
							$valid = false;
						}
					}
				} else {
					if ($this->stateid != "ALL" && $doc->getStateID() != $this->stateid) {
						$valid = false;
					}
				}
				
				if ($this->languageid && $doc->getLanguageID() != $this->languageid) {
					$valid = false;
				}
				
				
				if ($valid) {
					$return[] =& $doc;
				}
			}
		}
		
		if (count($return) == 0) {
			$this->setError(dgettext('gforge-plugin-novadoc','no_docs'));
			$r = false;
			return $r;
		}

		return $return;
	}
	
	/**
	 * getFromDB - Retrieve documents from database.
	 */
	function getFromDB() {
		$this->Documents = array();
		$sql = 'SELECT * FROM plugin_docs_docdata_vw WHERE is_current=\'1\' ' 
		        . ' AND group_id=\'' . $this->Group->getID() . '\''
		        . 'ORDER BY title ';
		$result = db_query($sql);
		if (!$result) {
			exit_error('Error', db_error());
		}
		
		while ($arr =& db_fetch_array($result)) {
			$doc_group_id = $arr['doc_group'];
			if (!isset($this->Documents[$doc_group_id]) || !is_array($this->Documents[$doc_group_id])) {
				$this->Documents[$doc_group_id] = array();
			}
			
			$this->Documents[$doc_group_id][] = new Document($this->Group, $arr['docid'], $arr);
		}
	}
	
	/**
	 * getStates - Return an array of states that have documents associated to them
	 */
	function getUsedStates() {
		$sql = "SELECT DISTINCT plugin_docs_doc_states.stateid,plugin_docs_doc_states.name 
			FROM plugin_docs_doc_states,plugin_docs_doc_data
			WHERE plugin_docs_doc_data.stateid=plugin_docs_doc_states.stateid
			ORDER BY plugin_docs_doc_states.name ASC";
		$result = db_query($sql);
		if (!$result) {
			exit_error('error', db_error());
		}
		
		$return = array();
		while ($arr = db_fetch_array($result)) {
			$return[] = $arr;
		}
		
		return $return;
	}

}

?>

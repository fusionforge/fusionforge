<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: FileFactory.class.php,v 1.4 2006/11/22 10:17:24 pascal Exp $
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
	File Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("common/include/Error.class.php");
require_once ("plugins/novafrs/include/File.class.php");

class FileFactory extends Error
{

	/**
	 * The Group object.
	 *
	 * @var	 object  $Group.
	 */
	var $Group;

	/**
	 * The Files dictionary.
	 *
	 * @var	 array	Contains fr_group_id as key and the array of files associated to that id. 
	 */
	var $Files;
	var $stateid;
	var $languageid;
	var $frgroupid;
	var $sort='group_name, title';

	/**
	 *  Constructor.
	 *
	 *	@param	object	The Group object to which this FileFactory is associated.
	 *	@return	boolean	success.
	 */
	function FileFactory(&$Group) {
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
	 *	getGroup - get the Group object this FileFactory is associated with.
	 *
	 *	@return object	the Group object.
	 */
	function &getGroup() {
		return $this->Group;
	}

	/**
	 *	setStateID - call this before getFiles() if you want to limit to a specific state.
	 *
	 *	@param	int	The stateid from the fr_states table.
	 */
	function setStateID($stateid) {
		$this->stateid=$stateid;
	}

	/**
	 *	setLanguageID - call this before getFiles() if you want to limit to a specific language.
	 *
	 *	@param	int	The language_id from the supported_languages table.
	 */
	function setLanguageID($languageid) {
		$this->languageid=$languageid;
	}

	/**
	 *	setFrGroupID - call this before getFiles() if you want to limit to a specific fr_group.
	 *
	 *	@param	int	The fr_group from the fr_groups table.
	 */
	function setFrGroupID($frgroupid) {
		$this->frgroupid=$frgroupid;
	}

	/**
	 *	setSort - call this before getFiles() if you want to control the sorting.
	 *
	 *	@param	string	The name of the field to sort on.
	 */
	function setSort($sort) {
		$this->sort=$sort;
	}

	/**
	 *	getFiles - returns an array of File objects.
	 *
	 *	@return	array	File objects.
	 */
	function &getFiles() {
		global $Language;
		if (!$this->Files) {
			$this->getFromDB();
		}
		
		$return = array();
		// If the file group is specified, we should only check that group in
		// the Files array. If not, we should check ALL the groups.
		if ($this->frgroupid) {
			$keys = array($this->frgroupid);
		} else {
			$keys = array_keys($this->Files);
		}
		
		foreach ($keys as $key) {
			if (!array_key_exists($key, $this->Files)) continue;	// Should not happen
			$count = count($this->Files[$key]);
			
			for ($i=0; $i < $count; $i++) {
				$valid = true;		// do we need to return this file?
				$fr =& $this->Files[$key][$i];
				
				if (!$this->stateid) {
					if (session_loggedin()) {
						$perm =& $this->Group->getPermission( session_get_user() );
						if (!$perm || !is_object($perm) || !$perm->isMember()) {
							if ($fr->getStateID() != 1) {		// non-active file?
								$valid = false;
							}
						} else {
							if ($fr->getStateID() != 1 &&		/* not active */
								$fr->getStateID() != 4 &&			/* not hidden */
								$fr->getStateID() != 5) {			/* not private */
								$valid = false;
							}
						}
					} else {
						if ($fr->getStateID() != 1) {		// non-active file?
							$valid = false;
						}
					}
				} else {
					if ($this->stateid != "ALL" && $fr->getStateID() != $this->stateid) {
						$valid = false;
					}
				}
				
				if ($this->languageid && $fr->getLanguageID() != $this->languageid) {
					$valid = false;
				}
				
				
				if ($valid) {
					$return[] =& $fr;
				}
			}
		}
		
		if (count($return) == 0) {
			$this->setError(dgettext('gforge-plugin-novafrs','no_frs'));
			return false;
		}

		return $return;
	}
	
	/**
	 * getFromDB - Retrieve files from database.
	 */
	function getFromDB() {
		$this->Files = array();
		$sql = 'SELECT * FROM plugin_frs_frdata_vw WHERE is_current=\'1\' ' 
		        . ' AND group_id=\'' . $this->Group->getID() . '\''
		        . 'ORDER BY title ';
		$result = db_query($sql);
		if (!$result) {
			exit_error('Error', db_error());
		}
		
		while ($arr =& db_fetch_array($result)) {
			$fr_group_id = $arr['fr_group'];
			if (!isset($this->Files[$fr_group_id]) || !is_array($this->Files[$fr_group_id])) {
				$this->Files[$fr_group_id] = array();
			}
			
			$this->Files[$fr_group_id][] = new File($this->Group, $arr['frid'], $arr);
		}
	}
	
	/**
	 * getStates - Return an array of states that have files associated to them
	 */
	function getUsedStates() {
		$sql = "SELECT DISTINCT plugin_frs_fr_states.stateid,plugin_frs_fr_states.name 
			FROM plugin_frs_fr_states,plugin_frs_fr_data
			WHERE plugin_frs_fr_data.stateid=plugin_frs_fr_states.stateid
			ORDER BY plugin_frs_fr_states.name ASC";
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

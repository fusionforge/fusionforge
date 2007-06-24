<?php
/**
 * ArtifactQuery.class.php - Class to handle user defined artifacts
 *
 * Copyright 2005 (c) GForge Group, LLC; Anthony J. Pugliese,
 *
 * @version   $Id$
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
require_once('common/include/Error.class.php');

define('ARTIFACT_QUERY_ASSIGNEE',1);
define('ARTIFACT_QUERY_STATE',2);
define('ARTIFACT_QUERY_MODDATE',3);
define('ARTIFACT_QUERY_EXTRAFIELD',4);
define('ARTIFACT_QUERY_SORTCOL',5);
define('ARTIFACT_QUERY_SORTORD',6);
define('ARTIFACT_QUERY_OPENDATE',7);
define('ARTIFACT_QUERY_CLOSEDATE',8);

require_once('common/tracker/ArtifactType.class.php');

class ArtifactQuery extends Error {
	/** 
	 * The artifact type object.
	 *
	 * @var		object	$ArtifactType.
	 */
	var $ArtifactType; //object

	/**
	 * Array of artifact data.
	 *
	 * @var		array	$data_array.
	 */
	var $data_array;

	/**
	 *	ArtifactQuery - Constructer
	 *
	 *	@param	object	ArtifactType object.
	 *	@param 	
	 *  	@return	boolean	success.
	 */
	function ArtifactQuery(&$ArtifactType, $data = false) {
		$this->Error(); 

		//was ArtifactType legit?
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactQuery: No Valid ArtifactType');
			return false;
		}
		//did ArtifactType have an error?
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactQuery: '.$ArtifactType->getErrorMessage());
			return false;
		}
		$this->ArtifactType =& $ArtifactType;

		if ($data) {
			if (is_array($data)) {
				$this->data_array =& $data;
				return true;
			} else {
				if (!$this->fetchData($data)) {
					return false;
				} else {
					return true;
				}
			}
		}
	}

	/**
	 *	create - create a row in the table that stores a saved query for 	 *	a tracker.   
	 *
	 *	@param	string	Name of the saved query.
	 *  	@return 	true on success / false on failure.
	 */
	function create($name,$status,$assignee,$moddaterange,$sort_col,$sort_ord,$extra_fields,$opendaterange=0,$closedaterange=0) {
		global $Language;
		
		//
		//	data validation
		//
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		if (!session_loggedin()) {
			$this->setError('Must Be Logged In');
			return false;
		}

		if ($this->Exist(htmlspecialchars($name))) {
			$this->setError(_('Query already exists'));
			return false;
		}

		$sql="INSERT INTO artifact_query (group_artifact_id,query_name,user_id) 
			VALUES ('".$this->ArtifactType->getID()."','".htmlspecialchars($name)."','".user_getid()."')";

		db_begin();
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
			$this->clearError();
			$id=db_insertid($result,'artifact_query','artifact_query_id');
			if (!$id) {
				$this->setError('Error getting id '.db_error());
				db_rollback();
				return false;
			} else {
				if (!$this->insertElements($id,$status,$assignee,$moddaterange,$sort_col,$sort_ord,$extra_fields,$opendaterange,$closedaterange)) {
					db_rollback();
					return false;
				}
			}
		} else {
			$this->setError(db_error());
			db_rollback();
			return false;
		}
		//
		//	Now set up our internal data structures
		//
		if ($this->fetchData($id)) {
			db_commit();
			return true;
		} else {
			db_rollback();
			return false;
		}
	}

	/**
	 *	fetchData - re-fetch the data for this ArtifactQuery from the database.
	 *
	 *	@param	int		ID of saved query.
	 *	@return	boolean	success.
	 */
	function fetchData($id) {
		$res=db_query("SELECT * FROM artifact_query WHERE artifact_query_id='$id'");
		
		if (!$res || db_numrows($res) < 1) {
			$this->setError('ArtifactQuery: Invalid ArtifactQuery ID'.db_error());
			return false;
		}
		$this->data_array =& db_fetch_array($res);
		db_free_result($res);
		$res=db_query("SELECT * FROM artifact_query_fields WHERE artifact_query_id='$id'");
		unset($this->element_array);
		while ($arr = db_fetch_array($res)) {
			//
			//	Some things may have been saved as comma-separated items
			//
			if (strstr($arr['query_field_values'],',')) {
				$arr['query_field_values']=explode(',',$arr['query_field_values']);
			}
			$this->element_array[$arr['query_field_type']][$arr['query_field_id']]=$arr['query_field_values'];
		}
		return true;
	}

	/**
	 *	getArtifactType - get the ArtifactType Object this ArtifactExtraField is associated with.
	 *
	 *	@return object	ArtifactType.
	 */
	function &getArtifactType() {
		return $this->ArtifactType;
	}

	/**
	 *
	 *
	 */
	function insertElements($id,$status,$assignee,$moddaterange,$sort_col,$sort_ord,$extra_fields,$opendaterange,$closedaterange) {
		$res=db_query("DELETE FROM artifact_query_fields WHERE artifact_query_id='$id'");
		if (!$res) {
			$this->setError('Deleting Old Elements: '.db_error());
			return false;
		}
		$id = intval($id);
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_STATE."','0','".intval($status)."')");
		if (!$res) {
			$this->setError('Setting Status: '.db_error());
			return false;
		}
	
		if (is_array($assignee)) {
				for($e=0; $e<count($assignee); $e++) {
					$assignee[$e]=intval($assignee[$e]); 
				}
				$assignee=implode(',',$assignee);
		} else {
			$assignee = intval($assignee);
		}	
		
		if (preg_match("/[^[:alnum:]_]/", $string)) {
			$this->setError('ArtifactQuery: not valid sort_col');
			return false;
		}
		
		if (preg_match("/[^[:alnum:]_]/", $string)) {
			$this->setError('ArtifactQuery: not valid sort_ord');
			return false;
		}

		//CSV LIST OF ASSIGNEES
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_ASSIGNEE."','0','".$assignee."')");
		if (!$res) {
			$this->setError('Setting Assignee: '.db_error());
			return false;
		}

		//MOD DATE RANGE  YYYY-MM-DD YYYY-MM-DD format
		if ($moddaterange && !$this->validateDateRange($moddaterange)) {
			$this->setError('Invalid Mod Date Range');
			return false;
		}
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_MODDATE."','0','".$moddaterange."')");
		if (!$res) {
			$this->setError('Setting Last Modified Date Range: '.db_error());
			return false;
		}

		//OPEN DATE RANGE YYYY-MM-DD YYYY-MM-DD format
		if ($opendaterange && !$this->validateDateRange($opendaterange)) {
			$this->setError('Invalid Open Date Range');
			return false;
		}
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_OPENDATE."','0','".$opendaterange."')");
		if (!$res) {
			$this->setError('Setting Open Date Range: '.db_error());
			return false;
		}

		//CLOSE DATE RANGE YYYY-MM-DD YYYY-MM-DD format
		if ($closedaterange && !$this->validateDateRange($closedaterange)) {
			$this->setError('Invalid Close Date Range');
			return false;
		}
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_CLOSEDATE."','0','".$closedaterange."')");
		if (!$res) {
			$this->setError('Setting Close Date Range: '.db_error());
			return false;
		}

		// SORT COLUMN
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_SORTCOL."','0','".$sort_col."')");
		if (!$res) {
			$this->setError('Setting Sort Col: '.db_error());
			return false;
		}
		$res=db_query("INSERT INTO artifact_query_fields 
			(artifact_query_id,query_field_type,query_field_id,query_field_values) 
			VALUES ('$id','".ARTIFACT_QUERY_SORTORD."','0','".$sort_ord."')");
		if (!$res) {
			$this->setError('Setting Sort Order: '.db_error());
			return false;
		}

		if (!$extra_fields) {
			$extra_fields=array();
		}
		
		$keys=array_keys($extra_fields);
		$vals=array_values($extra_fields);
		for ($i=0; $i<count($keys); $i++) {
			if (!$vals[$i]) {
				continue;
			}
			//
			//	Checkboxes and multi-select may be arrays so store it comma-separated
			//
			if (is_array($vals[$i])) {
				for($e=0; $e<count($vals[$i]); $e++) {
					$vals[$i][$e]=intval($vals[$i][$e]); 
				}
				$vals[$i]=implode(',',$vals[$i]);
			} else {
				$vals[$i] =	 intval($vals[$i]);
			}
			$res=db_query("INSERT INTO artifact_query_fields 
				(artifact_query_id,query_field_type,query_field_id,query_field_values) 
				VALUES ('$id','".ARTIFACT_QUERY_EXTRAFIELD."','".((int)$keys[$i]) ."','". $vals[$i] ."')");
			if (!$res) {
				$this->setError('Setting values: '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
	 *	getID - get this ArtifactQuery ID.
	 *
	 *	@return	int	The id #.
	 */
	function getID() {
		return $this->data_array['artifact_query_id'];
	}

	/**
	 *	getName - get the name.
	 *
	 *	@return	string	The name.
	 */
	function getName() {
		return $this->data_array['query_name'];
	}

	/**
	 *	getSortCol - the column that you're sorting on
	 *
	 *	@return	string	The column name.
	 */
	function getSortCol() {
		return $this->element_array[ARTIFACT_QUERY_SORTCOL][0];
	}

	/**
	 *	getSortOrd - ASC or DESC
	 *
	 *	@return	string	ASC or DESC
	 */
	function getSortOrd() {
		return $this->element_array[ARTIFACT_QUERY_SORTORD][0];
	}

	/**
	 *	getModDateRange - get the range of dates to include in a query
	 *
	 *	@return	string	mod date range.
	 */
	function getModDateRange() {
		if ($this->element_array[ARTIFACT_QUERY_MODDATE][0]) {
			return $this->element_array[ARTIFACT_QUERY_MODDATE][0];
		} else {
			return false;
		}
	}

	/**
	 *	getOpenDateRange - get the range of dates to include in a query
	 *
	 *	@return	string	Open date range.
	 */
	function getOpenDateRange() {
		if ($this->element_array[ARTIFACT_QUERY_OPENDATE][0]) {
			return $this->element_array[ARTIFACT_QUERY_OPENDATE][0];
		} else {
			return false;
		}
	}

	/**
	 *	getCloseDateRange - get the range of dates to include in a query
	 *
	 *	@return	string	Close date range.
	 */
	function getCloseDateRange() {
		if ($this->element_array[ARTIFACT_QUERY_CLOSEDATE][0]) {
			return $this->element_array[ARTIFACT_QUERY_CLOSEDATE][0];
		} else {
			return false;
		}
	}

	/**
	 *	getAssignee
	 *
	 *	@return	string	Assignee ID
	 */
	function getAssignee() {
		return $this->element_array[ARTIFACT_QUERY_ASSIGNEE][0];
	}

	/**
	 *	getStatus
	 *
	 *	@return	string	Status ID
	 */
	function getStatus() {
		return $this->element_array[ARTIFACT_QUERY_STATE][0];
	}

	/**
	 *	getExtraFields - complex multi-dimensional array of extra field IDs/Vals
	 *
	 *	@return	array	Complex Array
	 */
	function getExtraFields() {
		return $this->element_array[ARTIFACT_QUERY_EXTRAFIELD];
	}

	/**
	 *	validateDateRange - validate a date range in this format '1999-05-01 1999-06-01'.
	 *
	 *	@return	boolean	true/false.
	 */
	function validateDateRange($daterange) {
		return preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{4}-[0-9]{2}-[0-9]{2}/',$daterange);
	}

	/**
	 *  update - update a row in the table used to query names 
	 *  for a tracker.  
	 *
	 *  	@param	int	 Id of the saved query
	 *	@param	string	The name of the saved query
	 *  @return	boolean	success.
	 */
	function update($name,$status,$assignee,$moddaterange,$sort_col,$sort_ord,$extra_fields,$opendaterange='',$closedaterange='') {
		global $Language;
		if (!$name) {
			$this->setMissingParamsError();
			return false;
		}
		if (!session_loggedin()) {
			$this->setError('Must Be Logged In');
			return false;
		}
		if (!$this->Exist(htmlspecialchars($name))) {
			$this->setError(_('Query does not exist'));
			return false;
		}
		$sql="UPDATE artifact_query
			SET 
			query_name='".htmlspecialchars($name)."'
			WHERE artifact_query_id='".$this->getID()."'
			AND user_id='".user_getid()."'";
		db_begin();
		$result=db_query($sql);
		if ($result && db_affected_rows($result) > 0) {
			if (!$this->insertElements($this->getID(),$status,$assignee,$moddaterange,$sort_col,$sort_ord,$extra_fields,$opendaterange,$closedaterange)) {
				db_rollback();
				return false;
			} else {
				db_commit();
				$this->fetchData($this->getID());
				return true;
			}
		} else {
			$this->setError('Error Updating: '.db_error());
			db_rollback();
			return false;
		}
	}

	/**
	 *  makeDefault - set this as the default query
	 *
	 *  @return	boolean	success.
	 */
	function makeDefault() {
		if (!session_loggedin()) {
			$this->setError('Must Be Logged In');
			return false;
		}
		$usr =& session_get_user();
		return $usr->setPreference('art_query'.$this->ArtifactType->getID(),$this->getID());
	}

	function delete() {
		$res=db_query("DELETE FROM artifact_query WHERE artifact_query_id='".$this->getID()."'
            AND user_id='".user_getid()."'");
		$res=db_query("DELETE FROM user_preferences WHERE preference_value='".$this->getID()."'
            AND preference_name 'art_query".$this->ArtifactType->getID()."'");
		unset($this->data_array);
		unset($this->element_array);
	}

	/**
	 *  Exist - check if already exist a query with the same name , user_id and artifact_id
	 *
	 *  @return	boolean	exist
	 */
	function Exist($name) {
		$user_id = user_getid();
		$art_id = $this->ArtifactType->getID();
		$sql = "SELECT * FROM artifact_query WHERE group_artifact_id = '$art_id' AND query_name = '$name' AND user_id = '$user_id'";
		$res = db_query($sql);
		if (db_numrows($res)>0) {
			return true;
		} else {
			return false;
		}
	}
}

?>

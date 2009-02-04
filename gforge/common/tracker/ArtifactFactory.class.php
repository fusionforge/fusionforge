<?php
/**
 * FusionForge trackers
 *
 * Copyright 2002, GForge, LLC
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
require_once $gfcommon.'tracker/Artifact.class.php';
require_once $gfcommon.'tracker/ArtifactType.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';

class ArtifactFactory extends Error {

	/**
	 * The ArtifactType object.
	 *
	 * @var	 object  $ArtifactType.
	 */
	var $ArtifactType;

	/**
	 * The artifacts array.
	 *
	 * @var  array  artifacts.
	 */
	var $artifacts = array();
	var $order_col;
	var $sort;
	var $status;
	var $changed_from;
	var $last_changed;
	var $assigned_to;
	var $offset;
	var $max_rows;
	var $fetched_rows;
	var $extra_fields;
	var $defaultquery;
	var $moddaterange;
	var $opendaterange;
	var $closedaterange;

	/**
	 *  Constructor.
	 *
	 *	@param	object	The ArtifactType object to which this ArtifactFactory is associated.
	 *	@return	boolean	success.
	 */
	function ArtifactFactory(&$ArtifactType) {
		$this->Error();
		if (!$ArtifactType || !is_object($ArtifactType)) {
			$this->setError('ArtifactFactory:: No Valid ArtifactType Object');
			return false;
		}
		if ($ArtifactType->isError()) {
			$this->setError('ArtifactFactory:: '.$ArtifactType->getErrorMessage());
			return false;
		}
		$this->ArtifactType =& $ArtifactType;
		$this->changed_from = 0x7ffffff; // Any

		return true;
	}

	/**
	 *	setup - sets up limits and sorts before you call getTasks().
	 *
	 *	@param	int	The offset - number of rows to skip.
	 *	@param	string	The column to sort on.
	 *	@param	string	The way to order - ASC or DESC.
	 *	@param	int	The max number of rows to return.
	 *	@param	string	Whether to set these prefs into the user_prefs table - use "custom".
	 *	@param	int	Include this param if you want to limit to a certain assignee.
	 *	@param	int	Include this param if you want to limit to a particular status.
	 *	@param	array	Array of extra fields & elements to limit the query to.
	 */
	function setup($offset,$order_col,$sort,$max_rows,$set,$_assigned_to,$_status,$_extra_fields=array()) {
//echo "<br />offset: $offset| order: $order|max_rows: $max_rows|_assigned_to: $_assigned_to|_status: $_status";

		if ((!$offset) || ($offset < 0)) {
			$this->offset=0;
		} else {
			$this->offset=$offset;
		}

		if (session_loggedin()) {
			$u =& session_get_user();
		}
		if (!is_array($_extra_fields)) {
			$_extra_fields=array();
		}

		if (!$set) {
			/*
				if no set is passed in, see if a preference was set
				if no preference or not logged in, use open set
			*/
			if (session_loggedin()) {
				$default_query=$u->getPreference('art_query'.$this->ArtifactType->getID());
				$this->defaultquery = $default_query;
				if ($default_query) {
					$aq = new ArtifactQuery($this->ArtifactType,$default_query);
					$_extra_fields=$aq->getExtraFields();
					$order_col=$aq->getSortCol();
					$sort=$aq->getSortOrd();
					$_assigned_to=$aq->getAssignee();
					$_status=$aq->getStatus();
					$this->moddaterange = $aq->getModDateRange();
					$this->opendaterange = $aq->getOpenDateRange();
					$this->closedaterange = $aq->getCloseDateRange();
				} else {
					$custom_pref=$u->getPreference('art_cust'.$this->ArtifactType->getID());
					if ($custom_pref) {
//$_assigned_to.'|'.$_status.'|'.$_order_col.'|'.$_sort_ord.'|'.$_changed.'|'.serialize($_extra_fields);
						$pref_arr=explode('|',$custom_pref);
						$_assigned_to=$pref_arr[0];
						$_status=$pref_arr[1];
						$order_col=$pref_arr[2];
						$sort=$pref_arr[3];
						$_changed=$pref_arr[4];
						if ($this->ArtifactType->usesCustomStatuses()) {
							$_extra_fields=unserialize($pref_arr[5]);
						} else {
							$_status=$pref_arr[1];
						}
						$set='custom';
					} else {
						//default to open
						$_assigned_to=0;
						$_status=1;
						$_changed=0;
					}
				}
			} else {
				//default to open
				$_assigned_to=0;
				$_status=1;
				$_changed=0;
			}
		}

		//
		//  validate the column names and sort order passed in from user
		//  before saving it to prefs
		//
		if ($order_col=='artifact_id' || $order_col=='summary' || $order_col=='open_date' ||
			$order_col=='close_date' || $order_col=='assigned_to' || $order_col=='submitted_by' || $order_col=='priority') {
			$_order_col=$order_col;
			if (($sort == 'ASC') || ($sort == 'DESC')) {
				$_sort_ord=$sort;
			} else {
				$_sort_ord='ASC';
			}
		} else {
			$_order_col='artifact_id';
			$_sort_ord='ASC';
		}

		if ($set=='custom') {
			if (session_loggedin()) {
				/*
					if this custom set is different than the stored one, reset preference
				*/
				if (is_array($_assigned_to)) {
					$_assigned_to='';
				}
				$aux_extra_fields = array();
				if (is_array($_extra_fields)){
					//print_r($_extra_fields);
					$keys=array_keys($_extra_fields);
					
					foreach ($keys as $key) {
						if ($_extra_fields[$key] != 'Array') {
							$aux_extra_fields[$key] = $_extra_fields[$key];
						}
					}
				}

				$extra_pref = '';
				if (count($aux_extra_fields)>0) {
					$extra_pref = '|'.serialize($aux_extra_fields);
				}
				
				$pref_=$_assigned_to.'|'.$_status.'|'.$_order_col.'|'.$_sort_ord.'|'.$_changed.$extra_pref;
				if ($pref_ != $u->getPreference('art_cust'.$this->ArtifactType->getID())) {
					$u->setPreference('art_cust'.$this->ArtifactType->getID(),$pref_);
				}
				$default_query=$u->getPreference('art_query'.$this->ArtifactType->getID());
				if ($default_query) {
					$u->deletePreference('art_query'.$this->ArtifactType->getID());
				}
			}
			$_changed=0;
		}

		$this->sort=$_sort_ord;
		$this->order_col=$_order_col;
		$this->status=$_status;
		if ($_assigned_to != 'Array') {
			$this->assigned_to=$_assigned_to;
		}
		$this->extra_fields=$_extra_fields;
		$this->setChangedFrom($_changed);

		// if $max_rows == 0 it means we want all the rows
		if (is_null($max_rows) || $max_rows < 0) {
			$max_rows=50;
		}
		if ($default_query) {
			$this->max_rows=0;
		} else {
			$this->max_rows=$max_rows;
		}
	}

	
	/**
	 *	setChangedFrom - sets up changed-from and last-changed before you call getTasks().
	 *
	 *	@param	int	The changed_from - offset time(sec) from now
	 */
	function setChangedFrom($changed_from) {
		$this->changed_from = ($changed_from <= 0) ? 0x7fffffff : $changed_from;
		$this->last_changed = time() - $this->changed_from;
	}

	/**
	 *	getDefaultQuery - get the default query
	 *
	 *	@return	int	
	 */
	function getDefaultQuery() {
		return $this->defaultquery;
	}
	
	/**
	 *	getArtifacts - get an array of Artifact objects.
	 *
	 *	@return	array	The array of Artifact objects.
	 */
	function &getArtifacts() {
		global $sys_database_type;

		if (!empty($this->artifacts)) {
			return $this->artifacts;
		}

		//if status selected, and more to where clause
		if ($this->status && ($this->status != 100)) {
			//for open tasks, add status=100 to make sure we show all
			$status_str="AND status_id='".$this->status."'";
		} else {
			//no status was chosen, so don't add it to where clause
			$status_str='';
		}

		//if assigned to selected, and more to where clause
		if ($this->assigned_to) {
			if (is_array($this->assigned_to)) {
				$assigned_str="AND assigned_to IN (".implode(',',$this->assigned_to).")";
			} else {
				$assigned_str="AND assigned_to='".$this->assigned_to."'";
			}
		} else {
			//no assigned to was chosen, so don't add it to where clause
			$assigned_str='';
		}

		if (is_array($this->extra_fields) && !empty($this->extra_fields)) {
			$keys=array_keys($this->extra_fields);
			$vals=array_values($this->extra_fields);
			$ef_where_str='';
			$ef_table_str='';
			for ($i=0; $i<count($keys); $i++) {
				if (empty($vals[$i])) {
					continue;
				}
				if (is_array($vals[$i]) && !empty($vals[$i])) {
					$vals[$i]=implode("','",$vals[$i]);
				}
				$ef_table_str.=", artifact_extra_field_data aefd$i ";
				$ef_where_str.=" AND aefd$i.extra_field_id='".$keys[$i]."' AND aefd$i.field_data IN ('".$vals[$i]."') AND aefd$i.artifact_id=artifact_vw.artifact_id ";
			}
		} else {
			$ef_table_str='';
			$ef_where_str='';
		}

		if ($this->last_changed > 0) {
			$last_changed_str=" AND last_modified_date > '" . $this->last_changed . "' ";
		} else {
			$last_changed_str='';
		}

		//add constraint of range of modified dates
		if ($this->moddaterange) {
			$range_arr=explode(' ',$this->moddaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$moddatesql= " AND last_modified_date BETWEEN '$begin_int' AND '$end_int' ";
		} else {
			$moddatesql= '';
		}
		//add constraint of range of open dates
		if ($this->opendaterange) {
			$range_arr=explode(' ',$this->opendaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$opendatesql= " AND open_date BETWEEN '$begin_int' AND '$end_int' ";
		} else {
			$opendatesql= '';
		}
		//add constraint of range of close dates
		if ($this->closedaterange) {
			$range_arr=explode(' ',$this->closedaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$closedatesql= " AND close_date BETWEEN '$begin_int' AND '$end_int' ";
		} else {
			$closedatesql= '';
		}
		
		// these are currently not being used
		$submitted_by_str = '';
		
		//
		//  now run the query using the criteria chosen above
		//
		if ($sys_database_type == "mysql") {
			$sql="SELECT * FROM (SELECT DISTINCT artifact_vw.* FROM artifact_vw $ef_table_str ";
		} else {
			$sql="SELECT * FROM (SELECT DISTINCT ON (group_artifact_id, artifact_id) artifact_vw.* FROM artifact_vw $ef_table_str ";
		}
		$sql.="
			WHERE 
			group_artifact_id='". $this->ArtifactType->getID() ."'
			$opendatesql $moddatesql $closedatesql $submitted_by_str
			 $status_str $assigned_str $last_changed_str $ef_where_str ) AS Artifacts
			ORDER BY Artifacts.group_artifact_id ".$this->sort.", Artifacts.". $this->order_col ." ".$this->sort;
//echo "$sql";
//exit;

		$result=db_query($sql);//,($this->max_rows),$this->offset);
		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		if (db_error()) {
			$this->setError('Database Error: '.db_error());
			return false;
		} else {
			while ($arr =& db_fetch_array($result)) {
				$this->artifacts[] = new Artifact($this->ArtifactType, $arr);
			}
		}
		return $this->artifacts;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

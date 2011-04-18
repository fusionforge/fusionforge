<?php
/**
 * FusionForge trackers
 *
 * Copyright 2002, GForge, LLC
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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
	var $summary;
	var $description;
	var $followups;
	
	var $query_type;		// query, default, custom
	var $query_id;			// id of the query (when query_type=query)

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

		if ((!$offset) || ($offset < 0)) {
			$this->offset=0;
		} else {
			$this->offset=$offset;
		}

		// $max_rows == 0 means we want all the rows
		if (is_null($max_rows) || $max_rows < 0) {
			$this->max_rows = 50 ;
		} else {
			$this->max_rows = $max_rows ;
		}

		if (session_loggedin()) {
			$u =& session_get_user();
		}
		if (!is_array($_extra_fields)) {
			$_extra_fields=array();
		}

		$_changed=0;
		if (!$set) {
			/*
				if no set is passed in, see if a preference was set
				if no preference or not logged in, use open set
			*/
			$this->query_type = '';
			if (session_loggedin()) {
				$query_id=$u->getPreference('art_query'.$this->ArtifactType->getID());
				if ($query_id) {
					$this->query_type = 'query';
					$this->query_id = $query_id;
				} else {
					$custom_pref=$u->getPreference('art_cust'.$this->ArtifactType->getID());
					if ($custom_pref) {
//$_assigned_to.'|'.$_status.'|'.$_order_col.'|'.$_sort_ord.'|'.$_changed.'|'.serialize($_extra_fields);
						$this->query_type = 'custom';
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
					}
				}
			} elseif (isset($_COOKIE["GFTrackerQuery"])) {
				$gf_tracker = unserialize($_COOKIE["GFTrackerQuery"]);
				$query_id = (int)$gf_tracker[$this->ArtifactType->getID()];
				if ($query_id) { 
					$this->query_type = 'query';
					$this->query_id = $query_id;
				}
			}

			if (!$this->query_type) {
				$res = db_query_params ('SELECT artifact_query_id FROM artifact_query
					WHERE group_artifact_id=$1
					AND query_type=2',
							array($this->ArtifactType->getID()));
				if (db_numrows($res)>0) {
					$this->query_type = 'query';
					$this->query_id = db_result($res, 0, 'artifact_query_id');
				}
			}

			if (!$this->query_type) {
				//default to all opened
				$this->query_type = 'default';
				$_assigned_to=0;
				$_status=1;
				$_changed=0;
			}

			if ($this->query_type == 'query') {
				$aq = new ArtifactQuery($this->ArtifactType, $this->query_id);
				$_assigned_to=$aq->getAssignee();
				$_status=$aq->getStatus();
				$_extra_fields=$aq->getExtraFields();
				$this->moddaterange = $aq->getModDateRange();
				$this->opendaterange = $aq->getOpenDateRange();
				$this->closedaterange = $aq->getCloseDateRange();
				$this->summary = $aq->getSummary();
				$this->description = $aq->getDescription();
				$this->followups = $aq->getFollowups();
				$order_col=$aq->getSortCol();
				$sort=$aq->getSortOrd();
			}
		}

		//
		//  validate the column names and sort order passed in from user
		//  before saving it to prefs
		//
		$allowed_order_col = array ('artifact_id',
					   'summary',
					   'open_date',
					   'close_date',
					   'assigned_to',
					   'submitted_by',
					   'priority',
					   'last_modified_date') ;
		$efarr = $this->ArtifactType->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_TEXT,
						    ARTIFACT_EXTRAFIELDTYPE_TEXTAREA,
						    ARTIFACT_EXTRAFIELDTYPE_INTEGER,
						    ARTIFACT_EXTRAFIELDTYPE_SELECT,
						    ARTIFACT_EXTRAFIELDTYPE_RADIO,
						    ARTIFACT_EXTRAFIELDTYPE_STATUS));
		$keys=array_keys($efarr);
		for ($k=0; $k<count($keys); $k++) {
			$i=$keys[$k];
			$allowed_order_col[] = $efarr[$i]['extra_field_id'];
		}
		
		$_order_col = util_ensure_value_in_set ($order_col,
							$allowed_order_col);
		$_sort_ord = util_ensure_value_in_set ($sort,
						       array ('ASC', 'DESC')) ;
		if ($set=='custom') {
			$this->query_type = 'custom';
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
		$this->assigned_to=$_assigned_to;
		$this->extra_fields=$_extra_fields;
		$this->setChangedFrom($_changed);
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
		if ($this->query_type == 'query')
			return $this->query_id;
		else
			return '';
	}
	
	/**
	 *	getArtifacts - get an array of Artifact objects.
	 *
	 *	@return	array	The array of Artifact objects.
	 */
	function &getArtifacts() {
		if (!empty($this->artifacts)) {
			return $this->artifacts;
		}

		$params = array() ;
		$paramcount = 1 ;
		
		$selectsql = 'SELECT DISTINCT ON (group_artifact_id, artifact_id) artifact_vw.* FROM artifact_vw';

		$wheresql = ' WHERE group_artifact_id=$'.$paramcount++ ;
		$params[] = $this->ArtifactType->getID() ;

		if (is_array($this->extra_fields) && !empty($this->extra_fields)) {
			$keys=array_keys($this->extra_fields);
			$vals=array_values($this->extra_fields);
			for ($i=0; $i<count($keys); $i++) {
				if (empty($vals[$i])) {
					continue;
				}
				$selectsql .= ', artifact_extra_field_data aefd'.$i;
				$wheresql .= ' AND aefd'.$i.'.extra_field_id=$'.$paramcount++ ;
				$params[] = $keys[$i] ;

				// Hack: Determine the type of the element to get the right search query.
				$res = db_query_params ('SELECT field_type FROM artifact_extra_field_list WHERE extra_field_id=$1',
							array($keys[$i])) ;
				$type = db_result($res,0,'field_type');
				if ($type == 4 or $type == 6) {
					$wheresql .= ' AND aefd'.$i.'.field_data LIKE $'.$paramcount++ ;
					$params[] = $vals[$i];
				} else {
					if (is_array($vals[$i])) {
						$wheresql .= ' AND aefd'.$i.'.field_data = ANY ($'.$paramcount++ .')' ;
						$params[] = db_string_array_to_any_clause ($vals[$i]) ;
					} else {
						$wheresql .= ' AND aefd'.$i.'.field_data = $'.$paramcount++ ;
						$params[] = $vals[$i];
					}
				}
				$wheresql .= ' AND aefd'.$i.'.artifact_id=artifact_vw.artifact_id' ;
			}
		}

		//if status selected, and more to where clause
		if ($this->status && ($this->status != 100)) {
			//for open tasks, add status=100 to make sure we show all
			$wheresql .= ' AND status_id=$'.$paramcount++ ;
			$params[] = $this->status;
		}

		//if assigned to selected, and more to where clause
		if ($this->assigned_to) {
			if (is_array($this->assigned_to)) {
				$wheresql .= ' AND assigned_to = ANY ($'.$paramcount++ ;
				$params[] = db_int_array_to_any_clause ($this->assigned_to) ;
				$wheresql .= ')' ;
			} else {
				$wheresql .= ' AND assigned_to = $'.$paramcount++ ;
				$params[] = $this->assigned_to ;
			}
		}

		if ($this->last_changed > 0) {
			$wheresql .= ' AND last_modified_date > $'.$paramcount++ ;
			$params[] = $this->last_changed ;
		}

		//add constraint of range of modified dates
		if ($this->moddaterange) {
			$range_arr=explode(' ',$this->moddaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$wheresql .= ' AND (last_modified_date BETWEEN $'.$paramcount++ ;
			$params[] = $begin_int ;
			$wheresql .= ' AND $'.$paramcount++ ;
			$params[] = $end_int ;
			$wheresql .= ')' ;
		}
		//add constraint of range of open dates
		if ($this->opendaterange) {
			$range_arr=explode(' ',$this->opendaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$wheresql .= ' AND (open_date BETWEEN $'.$paramcount++ ;
			$params[] = $begin_int ;
			$wheresql .= ' AND $'.$paramcount++ ;
			$params[] = $end_int ;
			$wheresql .= ')' ;
		}
		//add constraint of range of close dates
		if ($this->closedaterange) {
			$range_arr=explode(' ',$this->closedaterange);
			$begin_int = strtotime($range_arr[0]);
			$end_int=strtotime($range_arr[1])+(24*60*60);
			$wheresql .= ' AND (close_date BETWEEN $'.$paramcount++ ;
			$params[] = $begin_int ;
			$wheresql .= ' AND $'.$paramcount++ ;
			$params[] = $end_int ;
			$wheresql .= ')' ;
		}

		//add constraint on the summary string.
		if ($this->summary) {
			$wheresql .= ' AND summary LIKE $'.$paramcount++ ;
			$params[] = $this->summary;
		}
		//add constraint on the description string.
		if ($this->description) {
			$wheresql .= ' AND details LIKE $'.$paramcount++ ;
			$params[] = $this->description;
		}
		//add constraint on the followups string.
		if ($this->followups) {
			$wheresql .= 'LEFT OUTER JOIN artifact_message am USING (artifact_id)
						WHERE am.body LIKE $'.$paramcount++;
			$params[] = $this->followups;
		}

		$sortorder = util_ensure_value_in_set ($this->sort,
						       array ('ASC', 'DESC')) ;
		
		$sortcol = util_ensure_value_in_set ($this->order_col,
						     array ('extra',
							    'artifact_id',
							    'summary',
							    'open_date',
							    'close_date',
							    'assigned_to',
							    'submitted_by',
							    'priority'));

		if ($sortcol != 'extra') {
			$ordersql = " ORDER BY Artifacts.group_artifact_id $sortorder, Artifacts.$sortcol $sortorder" ;
		} else {
			$ordersql = ''  ;
		}
			
		$result = db_query_params ('SELECT * FROM (' . $selectsql . $wheresql . ') AS Artifacts' . $ordersql,
					   $params) ;
		$rows = db_numrows($result);
		$this->fetched_rows=$rows;
		if (db_error()) {
			$this->setError('Database Error: '.db_error());
			return false;
		} else {
			while ($arr = db_fetch_array($result)) {
				$this->artifacts[] = new Artifact($this->ArtifactType, $arr);
			}
		}
		if ($sortcol == 'extra') {
			sortArtifactList ($this->artifacts, $this->order_col, $this->sort) ;
		}
		return $this->artifacts;
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

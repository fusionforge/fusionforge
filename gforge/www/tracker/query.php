<?php
/**
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
require_once('common/tracker/ArtifactQuery.class');

if (!session_loggedin()) {
	exit_not_logged_in();
}

if ($submit) {
	//
	//  Create a Saved Query
	//
	if ($query_action == 1) {
		$aq = new ArtifactQuery($ath);
		if (!$aq || !is_object($aq)) {
			exit_error('Error',$aq->getErrorMessage());
		}
		if (!$aq->create($query_name,$_status,$_POST["_assigned_to"],$_changed_from,$_sort_col,$_sort_ord,$_POST["extra_fields"])) {
			exit_error('Error',$aq->getErrorMessage());
		} else {
			$feedback .= 'Successfully Created';
		}
		$aq->makeDefault();
		$query_id=$aq->getID();
	//	
/*
	// Make the displayed query the default
	//
	} elseif ($query_action == 2) {
		$aq = new ArtifactQuery($ath,$query_id);
		if (!$aq || !is_object($aq)) {
			exit_error('Error',$aq->getErrorMessage());
		}
		if (!$aq->makeDefault()) {
			$feedback .= $aq->getErrorMessage();
		} else {
			$feedback .= 'Query Made Default';
		}	
*/	//
	// Update the name and or fields of the displayed saved query
	//
	} elseif ($query_action == 3) {
		$aq = new ArtifactQuery($ath,$query_id);
		if (!$aq || !is_object($aq)) {
			exit_error('Error',$aq->getErrorMessage());
		}
		if (!$aq->update($query_name,$_status,$_POST["_assigned_to"],$_changed_from,$_sort_col,$_sort_ord,$_POST["extra_fields"])) {
			exit_error('Error',$aq->getErrorMessage());
		} else {
			$feedback .= 'Query Updated';
		}
		$aq->makeDefault();
		$query_id=$aq->getID();
	//
	//	Just load the query
	//
	} elseif ($query_action == 4) {
		$aq = new ArtifactQuery($ath,$query_id);
		if (!$aq || !is_object($aq)) {
			exit_error('Error',$aq->getErrorMessage());
		}
		$aq->makeDefault();
	//
	//	Delete the query
	//
	} elseif ($query_action == 5) {
		$aq = new ArtifactQuery($ath,$query_id);
		if (!$aq || !is_object($aq)) {
			exit_error('Error',$aq->getErrorMessage());
		}
		if (!$aq->delete()) {
			$feedback .= $aq->getErrorMessage();
		} else {
			$feedback .= 'Query Deleted';
		}
		$query_id=0;
	}	
} else {
	$user=session_get_user();
	$query_id=$user->getPreference('art_query'.$ath->getID());
	$aq = new ArtifactQuery($ath,$query_id);
	if (!$aq || !is_object($aq)) {
		exit_error('Error',$aq->getErrorMessage());
	}
	$aq->makeDefault();
}

//
//  setup the query
//
$_assigned_to=$aq->getAssignee();
$_status=$aq->getStatus();
$_changed_from=$aq->getChanged();
$extra_fields =& $aq->getExtraFields();
$_sort_col=$aq->getSortCol();
$_sort_ord=$aq->getSortOrd();
//
//	creating a custom technician box which includes "any" and "unassigned"
$tech_box=$ath->technicianBox ('_assigned_to[]',$_assigned_to,true,'none','-1',false,true);


//
//	custom order by arrays to build a pop-up box
//
$order_name_arr=array();
$order_name_arr[]=$Language->getText('tracker','id');
$order_name_arr[]=$Language->getText('tracker','priority');
$order_name_arr[]=$Language->getText('tracker','summary');
$order_name_arr[]=$Language->getText('tracker','open_date');
$order_name_arr[]=$Language->getText('tracker','close_date');
$order_name_arr[]=$Language->getText('tracker','submitter');
$order_name_arr[]=$Language->getText('tracker','assignee');


$order_arr=array();
$order_arr[]='artifact_id';
$order_arr[]='priority';
$order_arr[]='summary';
$order_arr[]='open_date';
$order_arr[]='close_date';
$order_arr[]='submitted_by';
$order_arr[]='assigned_to';

//
//	custom sort arrays to build pop-up box
//
$sort_name_arr=array();
$sort_name_arr[]=$Language->getText('tracker_browse','ascending');
$sort_name_arr[]=$Language->getText('tracker_browse','descending');


$sort_arr=array();
$sort_arr[]='ASC';
$sort_arr[]='DESC'; 

//
//	custom changed arrays to build pop-up box
//
$changed_name_arr=array();
$changed_name_arr[]=$Language->getText('tracker_browse','changed_any');
$changed_name_arr[]=$Language->getText('tracker_browse','hour24');
$changed_name_arr[]=$Language->getText('tracker_browse','day7');
$changed_name_arr[]=$Language->getText('tracker_browse','week2');
$changed_name_arr[]=$Language->getText('tracker_browse','month1');

$changed_arr=array();
$changed_arr[]= 0;
$changed_arr[]= 3600 * 24;	 // 24 hour
$changed_arr[]= 3600 * 24 * 7; // 1 week
$changed_arr[]= 3600 * 24 * 14;// 2 week
$changed_arr[]= 3600 * 24 * 30;// 1 month

//
//	get queries for this user
//
$res=db_query("SELECT artifact_query_id,query_name 
	FROM artifact_query WHERE user_id='".user_getid()."' AND group_artifact_id='".$ath->getID()."'");


//
//	Show the new pop-up boxes to select assigned to, status, etc
//
echo '<html>
<title>Query</title><body>
<h1>'. $feedback .'</h1>

<table border="3" cellpadding="4" rules="groups" frame="box">
	<form action="'.$PHP_SELF.'?func=query&group_id='.$group_id.'&atid='.$ath->getID().'" method="post">
	<tr>
		<td><span style="font-size:smaller">
			<input type="submit" name="submit" value="'.$Language->getText('tracker','saved_queries').'" />
		</td>
		<td>';
	if(db_numrows($res)>0) {
		echo html_build_select_box($res,'query_id',$query_id,false).'</span>';
	}
	echo '
		</td>
	</tr>
	<tr>
		<td><span style="font-size:">
		<input type="radio" name="query_action" value="1" '.((!$query_id) ? 'checked' : '' ).'>'.$Language->getText('tracker','query_name').'<br />';
	if(db_numrows($res)>0) {
		echo '
		<input type="radio" name="query_action" value="4">'.$Language->getText('tracker','query_load').'<br />';
	}
	if ($query_id) {
		echo '
		<input type="radio" name="query_action" value="3" checked>'.$Language->getText('tracker','query_update').'<br />
		<input type="radio" name="query_action" value="5">'.$Language->getText('tracker','query_delete').'</span>';
	}
	echo '
		</td>
		<td valign="top"><span style="font-size:">
		<input type="text" name="query_name" value="'.$aq->getName().'" size="20" maxlength="30" /></span></td>
	</tr>
</table>';

echo'
<table>
	<tr>
		<td><span style="font-size:smaller">'.$Language->getText('tracker','assignee').':</a><br />'. $tech_box .'</span></td>
		<td>';
		if (!$ath->usesCustomStatuses()) {
			echo '<span style="font-size:smaller">'.$Language->getText('tracker','status').':&nbsp;<br />'. $ath->statusBox('_status',$_status,true,$Language->getText('tracker','status_any')) .'</span>';
		}
		echo '</td>
	</tr>';
	$ath->renderExtraFields($extra_fields,true,'None',true,'Any',ARTIFACT_EXTRAFIELD_FILTER_INT,false,'QUERY');
	
echo '
	<tr>
		<td><span style="font-size:smaller">'.$Language->getText('tracker_browse','sort_by').':</span><br />
		<span style="font-size:smaller">'. 
		html_build_select_box_from_arrays($order_arr,$order_name_arr,'_sort_col',$_sort_col,false) .'</td>
		<td><span style="font-size:smaller">&nbsp;</span><br />
		<span style="font-size:smaller">'.html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'_sort_ord',$_sort_ord,false) .'</td>
	</tr>
	</form></table></body></html>';

?>

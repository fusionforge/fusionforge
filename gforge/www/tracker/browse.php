<?php
/**
  *
  * SourceForge Generic Tracker facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

//
//  make sure this person has permission to view artifacts
//
if (!$ath->userCanView()) {
	exit_permission_denied();
}

if (session_loggedin()) {
	$u =& session_get_user();
}

if (!$offset || $offset < 0) {
	$offset=0;
}

if (!$set) {
	/*
		if no set is passed in, see if a preference was set
		if no preference or not logged in, use open set
	*/
	if (session_loggedin()) {
		$custom_pref=$u->getPreference('art_cust'.$ath->getID());
		if ($custom_pref) {
			$pref_arr=explode('|',$custom_pref);
			$_assigned_to=$pref_arr[0];
			$_status=$pref_arr[1];
			$_category=$pref_arr[2];
			$_group=$pref_arr[3];
			$order=$pref_arr[4];
			$sort=$pref_arr[5];
			$set='custom';
		} else {
			//default to open
			$_assigned_to=0;
			$_status=1;
		}
	} else {
		//default to open
		$_assigned_to=0;
		$_status=1;
	}
}

//
//	validate the column names and sort order passed in from user
//	before saving it to prefs
//
if ($order=='artifact_id' || $order=='summary' || $order=='open_date' || $order=='close_date' || $order=='assigned_to' || $order=='submitted_by' || $order=='priority') {
	$_sort_col=$order;
	if (($sort == 'ASC') || ($sort == 'DESC')) {
		$_sort_ord=$sort;
	} else {
		$_sort_ord='ASC';
	}
} else {
	$_sort_col='artifact_id';
	$_sort_ord='ASC';
}

if ($set=='custom') {
	if (session_loggedin()) {
		/*
			if this custom set is different than the stored one, reset preference
		*/
		$pref_=$_assigned_to.'|'.$_status.'|'.$_category.'|'.$_group.'|'.$_sort_col.'|'.$_sort_ord;
		if ($pref_ != $u->getPreference('art_cust'.$ath->getID())) {
			$u->setPreference('art_cust'.$ath->getID(),$pref_);
		}
	}
}

/*
	Display items based on the form post - by user or status or both
*/

//if status selected, add more to where clause
if ($_status && ($_status != 100)) {
	//for open tasks, add status=100 to make sure we show all
	$status_str="AND artifact.status_id='$_status'";
} else {
	//no status was chosen, so don't add it to where clause
	$status_str='';
}

//if assigned to selected, add to where clause
if ($_assigned_to) {
	$assigned_str="AND artifact.assigned_to='$_assigned_to'";
} else {
	//no assigned to was chosen, so don't add it to where clause
	$assigned_str='';
}

//if category selected, add to where clause
if ($_category && ($_category != 100)) {
	$category_str="AND artifact.category_id='$_category'";
} else {
	//no assigned to was chosen, so don't add it to where clause
	$category_str='';
}

//if artgroup selected, add to where clause
if ($_group && ($_group != 100)) {
	$group_str="AND artifact.artifact_group_id='$_group'";
} else {
	//no artgroup to was chosen, so don't add it to where clause
	$group_str='';
}

//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status
$ath->header(array('titlevals'=>array($ath->getName()),'pagename'=>'tracker_browse',
	'atid'=>$ath->getID(),'sectionvals'=>array($group->getPublicName())));

/**
 *
 *	Build the powerful browsing options pop-up boxes
 *
 */

//
//	creating a custom technician box which includes "any" and "unassigned"
//
$res_tech= $ath->getTechnicians();

$tech_id_arr=util_result_column_to_array($res_tech,0);
$tech_id_arr[]='0';  //this will be the 'any' row

$tech_name_arr=util_result_column_to_array($res_tech,1);
$tech_name_arr[]='Any';

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,'Unassigned');


//
//	custom order by arrays to build a pop-up box
//
$order_name_arr=array();
$order_name_arr[]='ID';
$order_name_arr[]='Priority';
$order_name_arr[]='Summary';
$order_name_arr[]='Open Date';
$order_name_arr[]='Close Date';
$order_name_arr[]='Submitter';
$order_name_arr[]='Assignee';


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
$sort_name_arr[]='Ascending';
$sort_name_arr[]='Descending';

$sort_arr=array();
$sort_arr[]='ASC';
$sort_arr[]='DESC';

//
//	Show the new pop-up boxes to select assigned to, status, etc
//

echo '
<table width="10%" border="0">
	<form action="'. $PHP_SELF .'?group_id='.$group_id.'&atid='.$ath->getID().'" method="post">
	<input type="hidden" name="set" value="custom" />
	<tr>
		<td><span style="font-size:smaller">Assignee:&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><strong>(?)</strong></a><br />'. $tech_box .'</span></td>'.
	'<td><span style="font-size:smaller">Status:&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=status\')"><strong>(?)</strong></a><br />'. $ath->statusBox('_status',$_status,true,'Any') .'</span></td>'.
	'<td><span style="font-size:smaller">Category:&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=category\')"><strong>(?)</strong></a><br />'. $ath->categoryBox ('_category',$_category,'Any') .'</span></td>'.
	'<td><span style="font-size:smaller">Group:&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=group\')"><strong>(?)</strong></a><br />'. $ath->artifactGroupBox ('_group',$_group,'Any') .'</span></td>
	</tr>
	<tr>
		<td align="right"><span style="font-size:smaller">Sort By:&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=sort_by\')"><strong>(?)</strong></a></span></td>'.
		'<td><span style="font-size:smaller">'. 
		html_build_select_box_from_arrays($order_arr,$order_name_arr,'order',$_sort_col,false) .'</td>'.
		'<td><span style="font-size:smaller">'.html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'sort',$_sort_ord,false) .'</td>'.
		'<td><span style="font-size:smaller"><input type="submit" name="submit" value="Browse" /></td>
	</tr>
	</form></table>';

/*
	Show the free-form text submitted by the project admin
*/
echo $ath->getBrowseInstructions();

//
//	now run the query using the criteria chosen above
//
$sql="SELECT artifact.priority,artifact.group_artifact_id,artifact.artifact_id,artifact.summary,
	artifact.open_date AS date,users.user_name AS submitted_by,user2.user_name AS assigned_to 
	FROM artifact,users,users user2 
	WHERE users.user_id=artifact.submitted_by 
	 $status_str $assigned_str $category_str $group_str 
	AND user2.user_id=artifact.assigned_to 
	AND group_artifact_id='". $ath->getID() ."'
	ORDER BY group_artifact_id $_sort_ord, $_sort_col $_sort_ord";

$result=db_query($sql,51,$offset);

if ($result && db_numrows($result) > 0) {

	if ($set=='custom') {
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status.'&_category='.$_category.'&_group='.$_group.'&order='.$_sort_col.'&sort='.$_sort_ord;
	}

	$ath->showBrowseList($result,$offset,$set);

	echo '* Denotes Requests > '. ($ath->getDuePeriod()/86400) .' Days Old';
	show_priority_colors_key();

} else {

	echo '
		<h1>No Items Match Your Criteria</h1>';
	echo db_error();
	//echo "<!-- $sql -->";

}

$ath->footer(array());

?>

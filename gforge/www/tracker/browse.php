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
require_once('common/tracker/ArtifactFactory.class');
//
//  make sure this person has permission to view artifacts
//
if (!$ath->userCanView()) {
	exit_permission_denied();
}

$af = new ArtifactFactory($ath);
if (!$af || !is_object($af)) {
	exit_error('Error','Could Not Get Factory');
} elseif ($af->isError()) {
	exit_error('Error',$af->getErrorMessage());
}

$af->setup($offset,$_sort_col,$_sort_ord,$max_rows,$set,$_assigned_to,$_status,$_category,$_group);
$_sort_col=$af->order_col;
$_sort_ord=$af->sort;
$_status=$af->status;
$_assigned_to=$af->assigned_to;
$_category=$af->category;
$_group=$af->group;

$art_arr =& $af->getArtifacts();
if (!$art_arr && $af->isError()) {
	exit_error('Error',$af->getErrorMessage());
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
$tech_name_arr[]=$Language->getText('tracker','any');

$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,$Language->getText('tracker','unassigned'));


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
//	Show the new pop-up boxes to select assigned to, status, etc
//

echo '
<table width="10%" border="0">
	<form action="'. $PHP_SELF .'?group_id='.$group_id.'&atid='.$ath->getID().'" method="post">
	<input type="hidden" name="set" value="custom" />
	<tr>
		<td><span style="font-size:smaller">'.$Language->getText('tracker','assignee').':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><strong>(?)</strong></a><br />'. $tech_box .'</span></td>'.
	'<td><span style="font-size:smaller">'.$Language->getText('tracker','status').':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=status\')"><strong>(?)</strong></a><br />'. $ath->statusBox('_status',$_status,true,$Language->getText('tracker','status_any')) .'</span></td>'.
	'<td><span style="font-size:smaller">'.$Language->getText('tracker','category').':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=category\')"><strong>(?)</strong></a><br />'. $ath->categoryBox ('_category',$_category,$Language->getText('tracker','category_any')) .'</span></td>'.
	'<td><span style="font-size:smaller">'.$Language->getText('tracker','group').':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=group\')"><strong>(?)</strong></a><br />'. $ath->artifactGroupBox ('_group',$_group,$Language->getText('tracker','group_any')) .'</span></td>
	</tr>
	<tr>
		<td align="right"><span style="font-size:smaller">'.$Language->getText('tracker_browse','sort_by').':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=sort_by\')"><strong>(?)</strong></a></span></td>'.
		'<td><span style="font-size:smaller">'. 
		html_build_select_box_from_arrays($order_arr,$order_name_arr,'_sort_col',$_sort_col,false) .'</td>'.
		'<td><span style="font-size:smaller">'.html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'_sort_ord',$_sort_ord,false) .'</td>'.
		'<td><span style="font-size:smaller"><input type="submit" name="submit" value="'.$Language->getText('general','browse').'" /></td>
	</tr>
	</form></table>';

/*
	Show the free-form text submitted by the project admin
*/
echo $ath->getBrowseInstructions();

if ($art_arr && count($art_arr) > 0) {

	if ($set=='custom') {
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status.'&_category='.$_category.'&_group='.$_group.'&_sort_col='.$_sort_col.'&_sort_ord='.$_sort_ord;
	}

	$title_arr=array();
	$title_arr[]=$Language->getText('tracker','id');
	$title_arr[]=$Language->getText('tracker','summary');
	$title_arr[]=$Language->getText('tracker','open_date');
	$title_arr[]=$Language->getText('tracker','assigned_to');
	$title_arr[]=$Language->getText('tracker','submitted_by');

	$IS_ADMIN=$ath->userIsAdmin();

	if ($IS_ADMIN) {
		echo '
		<form name="artifactList" action="'. $PHP_SELF .'?group_id='.$group_id.'&atid='.$ath->getID().'" METHOD="POST">
		<input type="hidden" name="func" value="massupdate">';
	}

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$then=(time()-$ath->getDuePeriod());
	$rows=count($art_arr);
	for ($i=0; $i < $rows; $i++) {
		echo '
		<tr bgcolor="'. html_get_priority_color( $art_arr[$i]->getPriority() ) .'">'.
		'<td NOWRAP>'.
		($IS_ADMIN?'<input type="CHECKBOX" name="artifact_id_list[]" value="'.
			$art_arr[$i]->getID() .'"> ':'').
			$art_arr[$i]->getID() .
			'</td>'.
		'<td><a href="'.$PHP_SELF.'?func=detail&aid='.
			$art_arr[$i]->getID() .
			'&group_id='. $group_id .'&atid='.
			$ath->getID().'">'.
			$art_arr[$i]->getSummary() .
			'</a></td>'.
		'<td>'. (($set != 'closed' && $art_arr[$i]->getOpenDate() < $then)?'* ':'&nbsp; ') .
				date($sys_datefmt,$art_arr[$i]->getOpenDate()) .'</td>'.
		'<td>'. $art_arr[$i]->getAssignedRealName() .'</td>'.
		'<td>'. $art_arr[$i]->getSubmittedRealName() .'</td></tr>';
	}

	/*
		Show extra rows for <-- Prev / Next -->
	*/
	if (($offset > 0) || ($rows >= 50)) {
		echo '
			<tr><td colspan="2">';
		if ($offset > 0) {
			echo '<a href="'.$PHP_SELF.'?func=browse&group_id='.$group_id.'&atid='.$ath->getID().'&set='.
			$set.'&offset='.($offset-50).'"><strong><-- '.$Language->getText('tracker_browse','previous').'</strong></a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td><td>&nbsp;</td><td colspan="2">';
		if ($rows >= 50) {
			echo '<a href="'.$PHP_SELF.'?func=browse&group_id='.$group_id.'&atid='.$ath->getID().'&set='.
			$set.'&offset='.($offset+50).'"><strong>'.$Language->getText('tracker_browse','next').' --></strong></a>';
		} else {
			echo '&nbsp;';
		}
		echo '</td></tr>';
	}

	echo $GLOBALS['HTML']->listTableBottom();
	/*
		Mass Update Code
	*/
	if ($IS_ADMIN) {
		echo '<script language="JavaScript">
	<!--
	function checkAll(val) {
		al=document.artifactList;
		len = al.elements.length;
		var i=0;
		for( i=0 ; i<len ; i++) {
			if (al.elements[i].name==\'artifact_id_list[]\') {
				al.elements[i].checked=val;
			}
		}
	}
	//-->
	</script>

			<table width="100%" border="0">
			<tr><td colspan="2">
<font size=1>
<a href="javascript:checkAll(1)">'.$Language->getText('tracker_browse','check_all').'</a>
-
  <a href="javascript:checkAll(0)">'.$Language->getText('tracker_browse','clear_all').'</a>
</font>
<p>
<FONT COLOR="#FF0000">'.$Language->getText('tracker_browse','admin_mass_update').'
			</td></tr>

			<tr>
			<td><strong>'.$Language->getText('tracker','category').' <a href="javascript:help_window(\'/help/tracker.php?helpname=category\')"><strong>(?)</strong></a>
				</strong><br />'. $ath->categoryBox ('category_id','xzxz',$Language->getText('tracker_browse','no_change')) .'</td>
			<td><strong>'.$Language->getText('tracker','group').' <a href="javascript:help_window(\'/help/tracker.php?helpname=group\')"><strong>(?)</strong></a></strong>
				<br />'. $ath->artifactGroupBox ('artifact_group_id','xzxz',$Language->getText('tracker_browse','no_change')) .'</td>
			</tr>

			<tr>
			<td><strong>'.$Language->getText('tracker','priority').': <a href="javascript:help_window(\'/help/tracker.php?helpname=priority\')"><strong>(?)</strong></a>
				</strong><br />';
			echo build_priority_select_box ('priority', '100', true);
			echo '</td><td>';
			if ($ath->useResolution()) {
				echo '
				<strong>'.$Language->getText('tracker_browse','resolution').': <a href="javascript:help_window(\'/help/tracker.php?helpname=resolution\')"><strong>(?)</strong></a>
					</strong><br />';
				echo $ath->resolutionBox('resolution_id','xzxz',true,$Language->getText('tracker_browse','no_change'));
			} else {
				echo '&nbsp;
				<input type="hidden" name="resolution_id" value="100">';
			}

			echo '</td>
			</tr>

			<tr>
			<td><strong>'.$Language->getText('tracker','assigned_to').': <a href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><strong>(?)</strong></a>
				</strong><br />'. $ath->technicianBox ('assigned_to','xzxz',true,$Language->getText('tracker_browse','no_change')) .'</td>
			<td><strong>'.$Language->getText('tracker','status').': <a href="javascript:help_window(\'/help/tracker.php?helpname=status\')"><strong>(?)</strong></a></strong>
				<br />'. $ath->statusBox ('status_id','xzxz',true,$Language->getText('tracker_browse','no_change')) .'</td>
			</tr>

			<tr><td colspan="2"><strong>'.$Language->getText('tracker_browse','canned_response').'
				<a href="javascript:help_window(\'/help/tracker.php?helpname=canned_response\')"><strong>(?)</strong></a>
				</strong><br />'. $ath->cannedResponseBox ('canned_response') .'</td></tr>

			<tr><td colspan="3" align="MIDDLE"><input type="SUBMIT" name="submit" value="'.$Language->getText('tracker_browse','mass_update').'"></td></tr>

			</TABLE>
		</form>';
	}

	echo $Language->getText('tracker_browse','old_requests',array(($ath->getDuePeriod()/86400) ));
	show_priority_colors_key();

} else {

	echo '
		<h1>'.$Language->getText('tracker_browse','no_items').'</h1>';
	echo db_error();
	//echo "<!-- $sql -->";

}

$ath->footer(array());

?>

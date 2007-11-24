<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */
require_once('common/tracker/ArtifactFactory.class.php');
require_once('common/tracker/ArtifactQuery.class.php');
//
//  make sure this person has permission to view artifacts
//
if (!$ath->userCanView()) {
	exit_permission_denied();
}

//
//	The browse page can be powered by a pre-saved query
//	or by select boxes chosen by the user
//
//	If there is a $query_id coming from the request OR the pref
//	was already saved, use the artifact factory that way.
//
//	If the query_id = -1, unset the pref and use regular browse boxes
//
if (session_loggedin()) {
	$query_id = getIntFromRequest('query_id');

	if($query_id) {
		if ($query_id == '-1') {
			$u =& session_get_user();
			$u->setPreference('art_query'.$ath->getID(),'');
		} else {
			$aq = new ArtifactQuery($ath,$query_id);
			if (!$aq || !is_object($aq)) {
				exit_error('Error',$aq->getErrorMessage());
			}
			$aq->makeDefault();
		}
	} else {
		$u =& session_get_user();
		$query_id=$u->getPreference('art_query'.$ath->getID(),'');
	}
}

$af = new ArtifactFactory($ath);

if (!$af || !is_object($af)) {
	exit_error('Error','Could Not Get Factory');
} elseif ($af->isError()) {
	exit_error('Error',$af->getErrorMessage());
}

$offset = @getStringFromRequest('offset',$offset);
$_sort_col = @getStringFromRequest('_sort_col',$_sort_col);
$_sort_ord = @getStringFromRequest('_sort_ord',$_sort_ord);
$max_rows = @getStringFromRequest('max_rows',$max_rows);
$set = @getStringFromRequest('set',$set);
$_assigned_to = @getStringFromRequest('_assigned_to',$_assigned_to);
$_status = @getStringFromRequest('_status',$_status);
if ($set == 'custom') {
	//
	//may be past in next/prev url
	//
	if ($_GET['extra_fields'][$ath->getCustomStatusField()]) {
		$_extra_fields[$ath->getCustomStatusField()] = $_GET['extra_fields'][$ath->getCustomStatusField()];
	} else {
		$_extra_fields[$ath->getCustomStatusField()] = $_POST['extra_fields'][$ath->getCustomStatusField()];
	}
}

if (@is_array($_extra_fields)){
	$keys=array_keys($_extra_fields);
	foreach ($keys as $key) {
		if ($_extra_fields[$key] != 'Array') {
			$aux_extra_fields[$key] = $_extra_fields[$key];
		}
	}
} else {
	if (isset($_extra_fields)){
		$aux_extra_fields = $_extra_fields;
	} else {
		$aux_extra_fields = '';
	}
}

$af->setup($offset,$_sort_col,$_sort_ord,null,$set,$_assigned_to,$_status,$aux_extra_fields);
//
//	These vals are sanitized and/or retrieved from ArtifactFactory stored settings
//
$_sort_col=$af->order_col;
$_sort_ord=$af->sort;
$_status=$af->status;
$_assigned_to=$af->assigned_to;
$_extra_fields=$af->extra_fields;

$art_arr =& $af->getArtifacts();

if (!$art_arr && $af->isError()) {
	exit_error('Error',$af->getErrorMessage());
}

//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status
$ath->header(array('atid'=>$ath->getID()));

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
$tech_name_arr[]=_('Any');

if (is_array($_assigned_to)) {
	$_assigned_to='';
}
$tech_box=html_build_select_box_from_arrays ($tech_id_arr,$tech_name_arr,'_assigned_to',$_assigned_to,true,_('Unassigned'));


//
//	custom order by arrays to build a pop-up box
//
$order_name_arr=array();
$order_name_arr[]=_('ID');
$order_name_arr[]=_('Priority');
$order_name_arr[]=_('Summary');
$order_name_arr[]=_('Open Date');
$order_name_arr[]=_('Close Date');
$order_name_arr[]=_('Submitter');
$order_name_arr[]=_('Assignee');


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
$sort_name_arr[]=_('Ascending');
$sort_name_arr[]=_('Descending');

$sort_arr=array();
$sort_arr[]='ASC';
$sort_arr[]='DESC';

//
//	custom changed arrays to build pop-up box
//
$changed_name_arr=array();
$changed_name_arr[]=_('Any changes');
$changed_name_arr[]=_('Last 24H');
$changed_name_arr[]=_('Last 7days');
$changed_name_arr[]=_('Last 2weeks');
$changed_name_arr[]=_('Last 1month');

$changed_arr=array();
$changed_arr[]= 0x7fffffff;	 // Any
$changed_arr[]= 3600 * 24;	 // 24 hour
$changed_arr[]= 3600 * 24 * 7; // 1 week
$changed_arr[]= 3600 * 24 * 14;// 2 week
$changed_arr[]= 3600 * 24 * 30;// 1 month

//
//	statuses can be custom in GForge 4.5+
//
if ($ath->usesCustomStatuses()) {
	$aux_extra_fields = array();
	if (is_array($_extra_fields)){
		$keys=array_keys($_extra_fields);
		foreach ($keys as $key) {
			if (!is_array($_extra_fields[$key])) {
				$aux_extra_fields[$key] = $_extra_fields[$key];
			}
		}
	} else {
		$aux_extra_fields = $_extra_fields;
	}
	$status_box=$ath->renderSelect ($ath->getCustomStatusField(),$aux_extra_fields[$ath->getCustomStatusField()],false,'',true,_('Any'));
} else {
	if (is_array($_status)) {
		$_status='';
	}
	$status_box = $ath->statusBox('_status',$_status,true,_('Any'));
}
echo '
<table width="100%" border="0">';

echo '
	<tr>';
/*
	Logged in users get the option of seeing a power-browse box
*/
if (session_loggedin()) {
	echo '<td rowspan="2">';
	echo '<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&atid='.$ath->getID().'" method="post">';
	echo '<input type="hidden" name="power_query" value="1">';
	$res=db_query("SELECT artifact_query_id,query_name 
	FROM artifact_query WHERE user_id='".user_getid()."' AND group_artifact_id='".$ath->getID()."'");

	if (db_numrows($res)>0) {
	echo 
		html_build_select_box($res,'query_id',$af->getDefaultQuery(),false).'<br />
		<input type="submit" name="run" value="'._('Power Query').'"></input>
		<strong><a href="javascript:admin_window('.$GLOBALS['sys_urlprefix'].'\'/tracker/?func=query&group_id='.$group_id.'&atid='. $ath->getID().'\')">'.
		_('Build Query').'</a></strong>';
	} else {
		echo '<strong>
		<a href="javascript:admin_window('.$GLOBALS['sys_urlprefix'].'\'/tracker/?func=query&group_id='.$group_id.'&atid='. $ath->getID().'\')">'._('Build Query').'</a></strong>';
	}
	echo '
		</form>
		</td>';
}
echo '
	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&atid='.$ath->getID().'" method="post">
	<input type="hidden" name="set" value="custom" />
	<td>'._('Assignee').':&nbsp;<br />'. $tech_box .'</td>'.
	'<td>'._('State').':&nbsp;<br />'. $status_box .'</td>';
	echo '
</tr>

<input type="hidden" name="query_id" value="-1">';

	echo '
	<tr>
		<td align="right">'._('Order by').':&nbsp;<a href="javascript:help_window(\''.$GLOBALS['sys_urlprefix'].'/help/tracker.php?helpname=sort_by\')"><strong>(?)</strong></a></span></td>'.
		'<td>'. 
		html_build_select_box_from_arrays($order_arr,$order_name_arr,'_sort_col',$_sort_col,false) .
		html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'_sort_ord',$_sort_ord,false) .
		'<input type="submit" name="submit" value="'._('Quick Browse').'" /></span></td>
	</tr>';


echo '
	</form>
</table>';
/**
 *
 *	Show the free-form text submitted by the project admin
 */
echo $ath->getBrowseInstructions();

if ($art_arr && count($art_arr) > 0) {

	if ($set=='custom') {
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status.'&extra_fields['.$ath->getCustomStatusField().']='.$extra_fields[$ath->getCustomStatusField()].'&_sort_col='.$_sort_col.'&_sort_ord='.$_sort_ord;
	}


	$IS_ADMIN=$ath->userIsAdmin();

	if ($IS_ADMIN) {
		echo '
		<form name="artifactList" action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&atid='.$ath->getID().'" METHOD="POST">
		<input type="hidden" name="form_key" value="'.form_generate_key().'">
		<input type="hidden" name="func" value="massupdate">';
	}

	$display_col=array('summary'=>1,
		'open_date'=>1,
		'status'=>0,
		'priority'=>1,
		'assigned_to'=>1,
		'submitted_by'=>1);

	$title_arr=array();
	$title_arr[]=_('ID');
	if ($display_col['summary'])
		$title_arr[]=_('Summary');
	if ($display_col['open_date'])
		$title_arr[]=_('Open Date');
	if ($display_col['status'])
		$title_arr[]=_('State');
	if ($display_col['priority'])
		$title_arr[]=_('Priority');
	if ($display_col['assigned_to'])
		$title_arr[]=_('Assigned to');
	if ($display_col['submitted_by'])
		$title_arr[]=_('Submitted by');


	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$then=(time()-$ath->getDuePeriod());

	if (!isset($_GET['start'])) {
		$start=0;
	} else {
		$start=$_GET['start'];
	}
	$max = ((count($art_arr) > ($start + 25)) ? ($start+25) : count($art_arr) );
//echo "max: $max";
	for ($i=$start; $i<$max; $i++) {
		echo '
		<tr '. $HTML->boxGetAltRowStyle($i) . '>'.
		'<td>'.
		($IS_ADMIN?'<input type="CHECKBOX" name="artifact_id_list[]" value="'.
			$art_arr[$i]->getID() .'"> ':'').
			$art_arr[$i]->getID() .
			'</td>';
		if ($display_col['summary'])
		 echo '<td><a href="'.getStringFromServer('PHP_SELF').'?func=detail&amp;aid='.
			$art_arr[$i]->getID() .
			'&amp;group_id='. $group_id .'&amp;atid='.
			$ath->getID().'">'.
			$art_arr[$i]->getSummary().
			'</a></td>';
		if ($display_col['open_date'])
			echo '<td>'. (($set != 'closed' && $art_arr[$i]->getOpenDate() < $then)?'* ':'&nbsp; ') .
				date(_('Y-m-d H:i'),$art_arr[$i]->getOpenDate()) .'</td>';
		if ($display_col['status'])
			echo '<td>'. $art_arr[$i]->getStatusName() .'</td>';
		if ($display_col['priority'])
			echo '<td class="priority'.$art_arr[$i]->getPriority()  .'">'. $art_arr[$i]->getPriority() .'</td>';
		if ($display_col['assigned_to'])
			echo '<td>'. $art_arr[$i]->getAssignedRealName() .'</td>';
		if ($display_col['submitted_by'])
			echo '<td>'. $art_arr[$i]->getSubmittedRealName() .'</td>';
		echo '</tr>';
	}

	/*
		Show extra rows for <-- Prev / Next -->
	* /
	//only show this if we�re not using a power query
	if ($af->max_rows > 0) {
		if (($offset > 0) || ($rows >= 50)) {
			echo '
				<tr><td colspan="2">';
			if ($offset > 0) {
				echo '<a href="'.getStringFromServer('PHP_SELF').'?func=browse&amp;group_id='.$group_id.'&amp;atid='.$ath->getID().'&set='.
				$set.'&offset='.($offset-50).'"><strong><-- '._('Previous 50').'</strong></a>';
			} else {
				echo '&nbsp;';
			}
			echo '</td><td>&nbsp;</td><td colspan="2">';
			if ($rows >= 50) {
				echo '<a href="'.getStringFromServer('PHP_SELF').'?func=browse&amp;group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;set='.
				$set.'&offset='.($offset+50).'"><strong>'._('Next 50').' --></strong></a>';
			} else {
				echo '&nbsp;';
			}
			echo '</td></tr>';
		}
	}
	*/
	echo $GLOBALS['HTML']->listTableBottom();
	$pages = count($art_arr) / 25;
	$currentpage = intval($start / 25);
//echo "Item Count: ".count($arr)."Pages: $pages";
	$skipped_pages=false;
	for ($j=0; $j<$pages; $j++) {
		if ($pages > 20) {
			if ((($j > 4) && ($j < ($currentpage-5))) || (($j > ($currentpage+5)) && ($j < ($pages-5)))) {
				if (!$skipped_pages) {
					$skipped_pages=true;
					echo "....&nbsp;";
				}
				continue;
			} else {
				$skipped_pages=false;
			}
		}
		if ($j == $currentpage) {
			echo '<strong>'.($j+1).'</strong>&nbsp;&nbsp;';
		} else {
			echo '<a href="'.getStringFromServer('PHP_SELF')."?func=browse&amp;group_id=".$group_id.'&atid='.$ath->getID().'&set='. $set.'&start='.($j*25).'"><strong>'.($j+1).'</strong></a>&nbsp;&nbsp;';
		}
	}

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

<a href="javascript:checkAll(1)">'._('Check &nbsp;all').'</a>
-
  <a href="javascript:checkAll(0)">'._('Clear &nbsp;all').'</a>

<p>
<span class="important">'._('<strong>Admin:</strong> If you wish to apply changes to all items selected above, use these controls to change their properties and click once on "Mass Update".').'
			</td></tr>';


		//
		//	build custom fields
		//
	$ef =& $ath->getExtraFields(ARTIFACT_EXTRAFIELD_FILTER_INT);
	$keys=array_keys($ef);

	$sel=array();
	for ($i=0; $i<count($keys); $i++) {
		if (($ef[$keys[$i]]['field_type']==ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) || ($ef[$keys[$i]]['field_type']==ARTIFACT_EXTRAFIELDTYPE_MULTISELECT)) {
			$sel[$keys[$i]]=array('100');
		} else {
			$sel[$keys[$i]]='100';
		}
	}
	$ath->renderExtraFields($sel,true,_('No Change'),false,'',ARTIFACT_EXTRAFIELD_FILTER_INT,true);
		echo   '<tr>
			<td><strong>'._('Priority').': <a href="javascript:help_window(\'/help/tracker.php?helpname=priority\')"><strong>(?)</strong></a>
				</strong><br />';
		echo build_priority_select_box ('priority', '100', true);
		echo '</td><td>';

		echo '</td>
			</tr>

			<tr>
			<td><strong>'._('Assigned to').': <a href="javascript:help_window(\'/help/tracker.php?helpname=assignee\')"><strong>(?)</strong></a>
				</strong><br />'. $ath->technicianBox ('assigned_to','100.1',true,_('Nobody'),'100.1',_('No Change')) .'</td>
			<td>';
		if (!$ath->usesCustomStatuses()) {
		echo '<strong>'._('State').': <a href="javascript:help_window(\'/help/tracker.php?helpname=status\')"><strong>(?)</strong></a></strong>
				<br />'. $ath->statusBox ('status_id','xzxz',true,_('No Change'));
		}
		echo '</td>
			</tr>

			<tr><td colspan="2"><strong>'._('Canned Response').':
				<a href="javascript:help_window(\'/help/tracker.php?helpname=canned_response\')"><strong>(?)</strong></a>
				</strong><br />'. $ath->cannedResponseBox ('canned_response') .'</td></tr>

			<tr><td colspan="3" align="MIDDLE"><input type="SUBMIT" name="submit" value="'._('Mass update').'"></td></tr>

			</TABLE>
		</form>';
	}

	printf(_('* Denotes requests > %1$s Days Old'), ($ath->getDuePeriod()/86400));
	show_priority_colors_key();

} else {

	echo '
		<h1>'._('No items found').'</h1>';
	echo db_error();
	//echo "<!-- $sql -->";

}

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

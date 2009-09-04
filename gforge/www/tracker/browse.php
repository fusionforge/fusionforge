<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */
require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';
//
//  make sure this person has permission to view artifacts
//
if (!$ath->userCanView()) {
	exit_permission_denied();
}

$query_id = getIntFromRequest('query_id');

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
} elseif ($query_id) {
	// If user is not logged, then use a cookie to store the current query.
	if (isset($_COOKIE["GFTrackerQuery"])) {
		$gf_tracker = unserialize($_COOKIE["GFTrackerQuery"]);
	} else {
		$gf_tracker = array();
	}
	$gf_tracker[$ath->getID()] = $query_id;
	// Send the query_id as a cookie to save it.
	setcookie("GFTrackerQuery", serialize($gf_tracker));
	$_COOKIE["GFTrackerQuery"] = serialize($gf_tracker);
} elseif (isset($_COOKIE["GFTrackerQuery"])) {
	$gf_tracker = unserialize($_COOKIE["GFTrackerQuery"]);
	$query_id = (int)$gf_tracker[$ath->getID()];
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
$_extra_fields = array() ;
$aux_extra_fields = array() ;
if ($set == 'custom') {
	//
	//may be past in next/prev url
	//
	if (isset($_GET['extra_fields'][$ath->getCustomStatusField()])) {
		$_extra_fields[$ath->getCustomStatusField()] = $_GET['extra_fields'][$ath->getCustomStatusField()];
	} elseif (isset($_POST['extra_fields'][$ath->getCustomStatusField()])) {
		$_extra_fields[$ath->getCustomStatusField()] = $_POST['extra_fields'][$ath->getCustomStatusField()];
	}
}

if (is_array($_extra_fields)){
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

/**
 *
 *	Show the free-form text submitted by the project admin
 */
echo $ath->renderBrowseInstructions();

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
echo '<script type="text/javascript" src="/tabber/tabber.js"></script>'."\n";
echo '
<div id="tabber" class="tabber">
	<div class="tabbertab" title="'._('Advanced queries').'">';

if (session_loggedin()) {
	$res = db_query_params ('SELECT artifact_query_id,query_name, CASE WHEN query_type>0 THEN 1 ELSE 0 END as type
	FROM artifact_query
	WHERE group_artifact_id=$1 AND (user_id=$2 OR query_type>0)
	ORDER BY type ASC, query_name ASC',
				array ($ath->getID(),
				       user_getid()));
} else {
	$res = db_query_params ('SELECT artifact_query_id,query_name, CASE WHEN query_type>0 THEN 1 ELSE 0 END as type
	FROM artifact_query
	WHERE group_artifact_id=$1 AND query_type>0
	ORDER BY type ASC, query_name ASC',
				array ($ath->getID()));
}


if (db_numrows($res)>0) {
	echo '<form action="'. getStringFromServer('PHP_SELF') .'" method="get">';
	echo '<input type="hidden" name="group_id" value="'.$group_id.'" />';
	echo '<input type="hidden" name="atid" value="'.$ath->getID().'" />';
	echo '<input type="hidden" name="power_query" value="1" />';
	echo '	<table width="100%" cellspacing="0">
	<tr>
	<td>
	';
	$optgroup['key'] = 'type';
	$optgroup['values'][0] = 'Private queries';
	$optgroup['values'][1] = 'Project queries';
	echo '<span style="font-size:smaller">';
	echo '<select name="query_id">';
	echo '<option value="100">Select One</option>';
	$current = '';
	$selected = $af->getDefaultQuery();
	while ($row = db_fetch_array($res)) {
		if ($current != $row['type']) {
			if ($current !== '') 
				echo '</optgroup>';
			$label = $row['type'] ? 'Project' : 'Private';
			echo '<optgroup label="'.$label.'">';
			$current = $row['type'];
		}
		echo '<option value="'.$row['artifact_query_id'].'"';
		if ($row['artifact_query_id'] == $selected)
			echo ' selected="selected"';
		echo '>'. $row['query_name'] .'</option>'."\n";
	}
	if ($current !== '') 
		echo '</optgroup>';
	echo '</select>';
	echo '</span>
	<input type="submit" name="run" value="'._('Power Query').'" />
	&nbsp;&nbsp;<a href="/tracker/?atid='. $ath->getID().'&amp;group_id='.$group_id.'&amp;func=query">'.
	_('Build Query').'</a>
	</td></tr></table>
	</form>';
} else {
	echo '<strong>
	<a href="/tracker/?atid='. $ath->getID().'&amp;group_id='.$group_id.'&amp;func=query">'._('Build Query').'</a></strong>';
}
echo '
	</div>
	<div class="tabbertab'.($af->query_type == 'custom' ? ' tabbertabdefault' : '').'" title="'._('Simple Filtering and Sorting').'">
	<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID().'" method="post">
	<input type="hidden" name="query_id" value="-1" />
	<input type="hidden" name="set" value="custom" />
	<table width="100%" cellspacing="0">
	<tr>
	<td>
	'._('Assignee').':&nbsp;'. $tech_box .'
	</td>
	<td align="center">
	'._('State').':&nbsp;'. $status_box .'
	</td>
	<td align="right">';

echo _('Order by').
	':&nbsp;<a href="javascript:help_window(\'/help/tracker.php?helpname=sort_by\')">' .
	'<strong>(?)</strong></a>'.
	html_build_select_box_from_arrays($order_arr,$order_name_arr,'_sort_col',$_sort_col,false) .
	html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'_sort_ord',$_sort_ord,false) .
	'<input type="submit" name="submit" value="'._('Quick Browse').'" />';

echo '
	</td>
	</tr>
	</table>
	</form>
	</div>';
if ($af->query_type == 'default') {
	echo '<div class="tabbertab tabbertabdefault" title="'._('Default').'">';
	echo '<strong>'._('Viewing only opened records by default, use \'Advanced queries\' or \'Simple Filtering and Sorting\' to change.').'</strong>';
	echo '</div>';
}
echo '
</div>';

if ($art_arr && count($art_arr) > 0) {

	if ($set=='custom') {
		$set .= '&amp;_assigned_to='.$_assigned_to.'&amp;_status='.$_status.'&amp;_sort_col='.$_sort_col.'&amp;_sort_ord='.$_sort_ord;
		if (array_key_exists($ath->getCustomStatusField(),$_extra_fields)) {
			$set .= '&amp;extra_fields['.$ath->getCustomStatusField().']='.$_extra_fields[$ath->getCustomStatusField()];
		}
	}


	$IS_ADMIN=$ath->userIsAdmin();

	if ($IS_ADMIN) {
		echo '
		<form name="artifactList" action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID().'" method="post">
		<input type="hidden" name="form_key" value="'.form_generate_key().'" />
		<input type="hidden" name="func" value="massupdate" />';
	}

	$browse_fields = explode(',', "id,".$ath->getBrowseList());
	$title_arr=array();
	foreach ($browse_fields as $f) {
		if (intval($f) > 0) {
    		$title = $ath->getExtraFieldName($f);
		} else {
			if ($f == 'id')
				$title=_('ID');
			if ($f == 'summary')
				$title=_('Summary');
			if ($f == 'details')
				$title=_('Description');
			if ($f == 'open_date')
				$title=_('Open Date');
			if ($f == 'close_date')
				$title=_('Close Date');
			if ($f == 'status_id')
				$title=_('State');
			if ($f == 'priority')
				$title=_('Priority');
			if ($f == 'assigned_to')
				$title=_('Assigned to');
			if ($f == 'submitted_by')
				$title=_('Submitted by');
		}
		$title_arr[] = $title;
	}

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
 		$extra_data = $art_arr[$i]->getExtraFieldDataText();
		echo '
		<tr '. $HTML->boxGetAltRowStyle($i) . '>';
 		foreach ($browse_fields as $f) {
			if ($f == 'id') {
				echo '<td nowrap="nowrap">'.
				($IS_ADMIN?'<input type="checkbox" name="artifact_id_list[]" value="'.
				$art_arr[$i]->getID() .'" /> ':'').
				'<a href="'.getStringFromServer('PHP_SELF').'?func=detail&amp;aid='.
				$art_arr[$i]->getID() .
				'&amp;group_id='. $group_id .'&amp;atid='.
				$ath->getID().'">'.$art_arr[$i]->getID() .
				'</a></td>';
			} else if ($f == 'summary') {
		 		echo '<td><a href="'.getStringFromServer('PHP_SELF').'?func=detail&amp;aid='.
				$art_arr[$i]->getID() .
				'&amp;group_id='. $group_id .'&amp;atid='.
				$ath->getID().'">'.
				$art_arr[$i]->getSummary().
				'</a></td>';
			} else if ($f == 'open_date') {
				echo '<td>'. (($set != 'closed' && $art_arr[$i]->getOpenDate() < $then)?'* ':'&nbsp; ') .
				date(_('Y-m-d H:i'),$art_arr[$i]->getOpenDate()) .'</td>';
			} else if ($f == 'status_id') {
				echo '<td>'. $art_arr[$i]->getStatusName() .'</td>';
			} else if ($f == 'priority') {
				echo '<td class="priority'.$art_arr[$i]->getPriority()  .'">'. $art_arr[$i]->getPriority() .'</td>';
			} else if ($f == 'assigned_to') {
				echo '<td>'. $art_arr[$i]->getAssignedRealName() .'</td>';
			} else if ($f == 'submitted_by') {
				echo '<td>'. $art_arr[$i]->getSubmittedRealName() .'</td>';
			} else if ($f == 'close_date') {
				echo '<td>'. ($art_arr[$i]->getCloseDate() ? 
				date(_('Y-m-d H:i'),$art_arr[$i]->getCloseDate()) :'&nbsp; ') .'</td>';
			} else if ($f == 'details') {
				echo '<td>'. $art_arr[$i]->getDetails() .'</td>';
			} else if (intval($f) > 0) {
				// Now display extra-fields (fields are numbers).
				$value = $extra_data[$f]['value'];
				if ($extra_data[$f]['type'] == 9) {
					$value = preg_replace('/\b(\d+)\b/e', "_artifactid2url('\\1')", $value);
				}
				echo '<td>' . $value .'</td>';
			} else {
				// Display ? for unknown values.
				echo '<td>?</td>';
			}
 		}
		echo '</tr>';
	}

	/*
		Show extra rows for <-- Prev / Next -->
	* /
	//only show this if we're not using a power query
	if ($af->max_rows > 0) {
		if (($offset > 0) || ($rows >= 50)) {
			echo '
				<tr><td colspan="2">';
			if ($offset > 0) {
				echo '<a href="'.getStringFromServer('PHP_SELF').'?func=browse&amp;group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;set='.
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
	if ($pages >= 1) {
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
				echo '<a href="'.getStringFromServer('PHP_SELF')."?func=browse&amp;group_id=".$group_id.'&amp;atid='.$ath->getID().'&amp;set='. $set.'&amp;start='.($j*25).'"><strong>'.($j+1).'</strong></a>&nbsp;&nbsp;';
			}
		}
	}

	/*
		Mass Update Code
	*/
	if ($IS_ADMIN) {
		echo '<script type="text/javascript">
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

			<table width="100%" border="0" id="admin_mass_update">
			<tr><td colspan="2">

<a href="javascript:checkAll(1)">'._('Check &nbsp;all').'</a>
-
  <a href="javascript:checkAll(0)">'._('Clear &nbsp;all').'</a>

<p>
<span class="important">'._('<strong>Admin:</strong> If you wish to apply changes to all items selected above, use these controls to change their properties and click once on "Mass Update".').'</span></p>
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

			<tr><td colspan="3" align="center"><input type="submit" name="submit" value="'._('Mass update').'" /></td></tr>
			</table>
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

<?php
/**
 * FusionForge Tracker
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';
//
//  make sure this person has permission to view artifacts
//
session_require_perm ('tracker', $ath->getID(), 'read') ;

$query_id = getIntFromRequest('query_id');
$start = getIntFromRequest('start');
$paging = 0;

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
	$u =& session_get_user();
	if (getStringFromRequest('setpaging')) {
		/* store paging preferences */
		$paging = getIntFromRequest('nres');
		if (!$paging) {
			$paging = 25;
		}
		$u->setPreference("paging", $paging);
	}
	
	if($query_id) {
		if ($query_id == '-1') {
			$u->setPreference('art_query'.$ath->getID(),'');
		} else {
			$aq = new ArtifactQuery($ath,$query_id);
			if (!$aq || !is_object($aq)) {
				exit_error($aq->getErrorMessage(),'tracker');
			}
			$aq->makeDefault();
		}
	} else {
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
	exit_error(_('Could Not Get Factory'),'tracker');
} elseif ($af->isError()) {
	exit_error($af->getErrorMessage(),'tracker');
}

if (!isset($_sort_col)) {
	/* default sort order: highest priority first */
	$_sort_col = 'priority';
	$_sort_ord = 'DESC';
}
$offset = getStringFromRequest('offset');
$_sort_col = getStringFromRequest('_sort_col',$_sort_col);
$_sort_ord = getStringFromRequest('_sort_ord',$_sort_ord);
$max_rows = getIntFromRequest('max_rows', 25);
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getIntFromRequest('_status');
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

$af->setup($offset,$_sort_col,$_sort_ord,$paging,$set,$_assigned_to,$_status,$aux_extra_fields);
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
	exit_error($af->getErrorMessage(),'tracker');
}

//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status
use_javascript('/tabber/tabber.js');

$ath->header(array('atid'=>$ath->getID(), 'title'=>$ath->getName()));

/**
 *
 *	Build the powerful browsing options pop-up boxes
 *
 */

//
//	creating a custom technician box which includes "any" and "unassigned"
//
$engine = RBACEngine::getInstance () ;
$techs = $engine->getUsersByAllowedAction ('tracker', $ath->getID(), 'tech') ;

$tech_id_arr = array () ;
$tech_name_arr = array () ;

foreach ($techs as $tech) {
	$tech_id_arr[] = $tech->getID() ;
	$tech_name_arr[] = $tech->getRealName() ;
}
$tech_id_arr[]='0';  //this will be the 'any' row
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

if ($art_arr && ($art_cnt = count($art_arr)) > 0) {
	$focus = getIntFromRequest('focus');
} else {
	$art_cnt = 0;
	$start = 0;
	$focus = 0;
}
$paging = 0;
if (session_loggedin()) {
	/* logged in users get configurable paging */
	$paging = $u->getPreference("paging");
	echo '<form action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;start='.
		$start.'" method="post">'."\n";
}
if (!$paging) {
	$paging = 25;
}
if ($art_cnt) {
	if ($focus) {
		for ($i = 0; $i < $art_cnt; ++$i)
			if ($art_arr[$i]->getID() == $focus) {
				$start = $i;
				break;
			}
	}
	$max = ($art_cnt > ($start + $paging)) ? ($start + $paging) : $art_cnt;
} else {
	$max = 0;
}

printf('<p>' . _('Displaying results %1$d‒%2$d out of %3$d total.'),
       $start + 1, $max, $art_cnt);
if (session_loggedin()) {
	printf(' ' . _('Displaying %2$s results.') . "\n\t<input " .
	       'type="submit" name="setpaging" value="%1$s" />' .
	       "\n</p>\n</form>\n", _('Change'),
	       html_build_select_box_from_array(array(
							'10', '25', '50', '100', '1000'), 'nres', $paging, 1));
} else {
	echo "</p>\n";
}

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

// start of RDFa
$proj_name = $group->getUnixName();
$proj_url = util_make_url_g($group->getUnixName(),$group_id);
// the tracker's URIs are constructed in order to support addition of an OSLC-CM REST server
// inside /tracker/cm/. There each tracker has a URL in the form .../project/PROJ_NAME/atid/ATID
$tracker_stdzd_uri = util_make_url('/tracker/cm/project/'. $proj_name .'/atid/'. $ath->getID());
print '<div about="'. $tracker_stdzd_uri
	.'" typeof="sioc:Container" xmlns:sioc="http://rdfs.org/sioc/ns#" xmlns:doap="http://usefulinc.com/ns/doap#">'."\n";
print '<span rel="http://www.w3.org/2002/07/owl#sameAs" resource="" />'."\n";
print '<span rev="doap:bug-database sioc:space_of" resource="'. $proj_url .'" />'."\n";
print "</div>\n"; // end of about

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

// Compute the list of fields which can be sorted.
$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_TEXT,
				    ARTIFACT_EXTRAFIELDTYPE_TEXTAREA,
				    ARTIFACT_EXTRAFIELDTYPE_INTEGER,
				    ARTIFACT_EXTRAFIELDTYPE_SELECT,
				    ARTIFACT_EXTRAFIELDTYPE_RADIO,
				    ARTIFACT_EXTRAFIELDTYPE_STATUS));
$keys=array_keys($efarr);
for ($k=0; $k<count($keys); $k++) {
	$i=$keys[$k];
	$order_name_arr[] = $efarr[$i]['field_name'];
	$order_arr[] = $efarr[$i]['extra_field_id'];
}

echo _('Order by').
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

if ($art_cnt > 0) {

	if ($query_id) {
		$aq = new ArtifactQuery($ath,$query_id);
		$has_bargraph = (in_array('bargraph', $aq->getQueryOptions()));
	} else {
		$has_bargraph = false;
	}
	
	if ($has_bargraph) {
		// Display the roadmap block based on the values of the Status field.
		$colors = array('#a71d16', '#ffa0a0', '#f5f5b5', '#bae0ba', '#16a716');
		$count = array();
		$percent = array();
		foreach($art_arr as $art) {
			if ($ath->usesCustomStatuses()) {
				$custom_id = $ath->getCustomStatusField();
				$extra_data = $art->getExtraFieldDataText();
				$count[ $extra_data[$custom_id]['value'] ]++;
			} else {
				$count[ $art->getStatusName()]++;
			}
		}
		foreach($count as $n => $c) {
			$percent[$n] = round(100*$c/count($art_arr));
		}
		
		$i=0;
		$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
		$keys=array_keys($efarr);
		$field_id = $keys[0];
		$states = $ath->getExtraFieldElements($field_id);
		$graph = '';
		$legend = '';
		if (is_array($states)) {
			foreach($states as $state) {
				$name = $state['element_name'];
				if ($count[$name]) {
					$graph  .= '<td style="background: '.$colors[$i].'; width: '.$percent[$name].'%;">&nbsp;</td>';
					$legend .= '<td style="white-space: nowrap; width: '.$percent[$name].'%;">'."<i>$name: $count[$name] ($percent[$name]%)</i></td>";
				}
				$i++;
			}
		}
	
		if ($graph) {
		?>
		<table class="progress">
      	<tbody>
      		<tr><?php echo $graph; ?></tr>
      	</tbody>
      	</table>
      	<table class="progress_legend">
      		<tr><?php echo $legend ?></tr>
      	</table>
	<?php
		}
	}
	
	if ($set=='custom') {
		$set .= '&amp;_assigned_to='.$_assigned_to.'&amp;_status='.$_status.'&amp;_sort_col='.$_sort_col.'&amp;_sort_ord='.$_sort_ord;
		if (array_key_exists($ath->getCustomStatusField(),$_extra_fields)) {
			$set .= '&amp;extra_fields['.$ath->getCustomStatusField().']='.$_extra_fields[$ath->getCustomStatusField()];
		}
	}


	$IS_ADMIN = forge_check_perm ('tracker', $ath->getID(), 'manager') ;

	if ($IS_ADMIN) {
		echo '
		<form name="artifactList" action="'. getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID().'" method="post">
		<input type="hidden" name="form_key" value="'.form_generate_key().'" />
		<input type="hidden" name="func" value="massupdate" />';
	}

	$browse_fields = explode(',', "id,".$ath->getBrowseList());
	$title_arr=array();
	foreach ($browse_fields as $f) {
		$title=$f;
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
			if ($f == 'related_tasks')
				$title=_('Related tasks');
		}
		$title_arr[] = $title;
	}

	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	$then=(time()-$ath->getDuePeriod());

	for ($i=$start; $i<$max; $i++) {
 		$extra_data = $art_arr[$i]->getExtraFieldDataText();
		echo '
		<tr '. $HTML->boxGetAltRowStyle($i) . '>';
 		foreach ($browse_fields as $f) {
			if ($f == 'id') {
				echo '<td style="white-space: nowrap;">'.
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
			} else if ($f == 'related_tasks') {
				echo '<td>';
				$tasks_res = $art_arr[$i]->getRelatedTasks();
				$s ='';
				while ($rest = db_fetch_array($tasks_res)) {
					$link = '/pm/task.php?func=detailtask&amp;project_task_id='.$rest['project_task_id'].
						'&amp;group_id='.$group_id.'&amp;group_project_id='.$rest['group_project_id'];
					$title = '[T'.$rest['project_task_id'].']';
					if ($rest['status_id'] == 2) {
						$title = '<strike>'.$title.'</strike>';
					}
					print $s.'<a href="'.$link.'" title="'.$rest['summary'].'">'.$title.'</a>';
					$s = ' ';
				}
				echo '</td>';
			} else if (intval($f) > 0) {
				// Now display extra-fields (fields are numbers).
				$value = $extra_data[$f]['value'];
				if ($extra_data[$f]['type'] == 9) {
					$value = preg_replace('/\b(\d+)\b/e', "_artifactid2url('\\1')", $value);
				} else if ($extra_data[$f]['type'] == 7) {
					if ($art_arr[$i]->getStatusID() == 2) {
						$value = '<strike>'.$value.'</strike>';
					}
					
				}
				echo '<td>' . $value .'</td>';
			} else {
				// Display ? for unknown values.
				echo '<td>?</td>';
			}
 		}
		echo '</tr>';
	}

	echo $GLOBALS['HTML']->listTableBottom();
	$pages = $art_cnt / $paging;
	$currentpage = intval($start / $paging);

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
			if ($j * $paging == $start) {
				echo '<strong>'.($j+1).'</strong>&nbsp;&nbsp;';
			} else {
				echo '<a href="'.getStringFromServer('PHP_SELF')."?func=browse&amp;group_id=".$group_id.'&amp;atid='.$ath->getID().'&amp;set='. $set.'&amp;start='.($j*$paging).'"><strong>'.($j+1).'</strong></a>&nbsp;&nbsp;';
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
<div class="important">'._('<strong>Admin:</strong> If you wish to apply changes to all items selected above, use these controls to change their properties and click once on "Mass Update".').'</div></p>
			</td></tr>';

		//
		//	build custom fields
		//
	$ef = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELD_FILTER_INT));
	$keys=array_keys($ef);

	$sel=array();
	for ($i=0; $i<count($keys); $i++) {
		if (($ef[$keys[$i]]['field_type']==ARTIFACT_EXTRAFIELDTYPE_CHECKBOX) || ($ef[$keys[$i]]['field_type']==ARTIFACT_EXTRAFIELDTYPE_MULTISELECT)) {
			$sel[$keys[$i]]=array('100');
		} else {
			$sel[$keys[$i]]='100';
		}
	}
	$ath->renderExtraFields($sel,true,_('No Change'),false,'',array(ARTIFACT_EXTRAFIELD_FILTER_INT),true);
		echo   '<tr>
			<td><strong>'._('Priority').':</strong><br />';
		echo build_priority_select_box ('priority', '100', true);
		echo '</td><td>';

		echo '</td>
			</tr>

			<tr>
			<td><strong>'._('Assigned to').':</strong><br />'. 
				$ath->technicianBox ('assigned_to','100.1',true,_('Nobody'),'100.1',_('No Change')) .'</td>
			<td>';
		if (!$ath->usesCustomStatuses()) {
		echo '<strong>'._('State').':</strong>
				<br />'. $ath->statusBox ('status_id','xzxz',true,_('No Change'));
		}
		echo '</td>
			</tr>

			<tr><td colspan="2"><strong>'._('Canned Response').':</strong><br />'.
				$ath->cannedResponseBox ('canned_response') .'</td></tr>

			<tr><td colspan="3" align="center"><input type="submit" name="submit" value="'._('Mass update').'" /></td></tr>
			</table>
		</form>';
	}

	printf(_('* Denotes requests > %1$s Days Old'), ($ath->getDuePeriod()/86400));

	if (in_array('priority', $browse_fields)) {
		show_priority_colors_key();
	}
} else {
	echo '<p class="warning_msg">'._('No items found').'</p>';
	echo db_error();
}

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

<?php
/**
 * FusionForge Tracker
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 Roland Mas
 * Copyright (C) 2011-2012 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Iñigo Martinez
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2012-2016, Franck Villaume - TrivialDev
 * Copyright 2014, Stéphane-Eymeric Bredthauer
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'tracker/ArtifactFactory.class.php';
require_once $gfcommon.'tracker/ArtifactQuery.class.php';

global $ath;
global $group_id;
global $group;
global $HTML;
global $LUSER;

//
//  make sure this person has permission to view artifacts
//
session_require_perm('tracker', $ath->getID(), 'read');

$query_id = getIntFromRequest('query_id');
$start = getIntFromRequest('start');

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
	if (getStringFromRequest('setpaging')) {
		/* store paging preferences */
		$paging = getIntFromRequest('nres');
		if (!$paging) {
			$paging = 25;
		}
		$LUSER->setPreference("paging", $paging);
	}
	/* logged in users get configurable paging */
	$paging = $LUSER->getPreference("paging");

	if($query_id) {
		if ($query_id == '-1') {
			$LUSER->setPreference('art_query'.$ath->getID(),'');
		} else {
			$aq = new ArtifactQuery($ath,$query_id);
			if (!$aq || !is_object($aq)) {
				exit_error($aq->getErrorMessage(),'tracker');
			}
			$aq->makeDefault();
		}
	} else {
		$query_id= $LUSER->getPreference('art_query'.$ath->getID(),'');
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

if(!isset($paging) || !$paging)
	$paging = 25;

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

if (!isset($_changed)) {
	$_changed = 0x7fffffff;
}

$_sort_col = getStringFromRequest('_sort_col',$_sort_col);
$_sort_ord = getStringFromRequest('_sort_ord',$_sort_ord);
$_changed = getStringFromRequest('_changed', $_changed);
$set = getStringFromRequest('set');
$_assigned_to = getIntFromRequest('_assigned_to');
$_status = getIntFromRequest('_status');
$_extra_fields = array() ;
$aux_extra_fields = array() ;

if ($set == 'custom') {
	/* may be past in next/prev url */
	$i = $ath->getCustomStatusField();
	$tmp_extra_fields = getArrayFromRequest('extra_fields');
	if (isset($tmp_extra_fields[$i])) {
		$_extra_fields[$i] = $tmp_extra_fields[$i];
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

$af->setup($start, $_sort_col, $_sort_ord, $paging, $set, $_assigned_to, $_status, $aux_extra_fields, time() - $_changed);
//
//	These vals are sanitized and/or retrieved from ArtifactFactory stored settings
//
$_sort_col=$af->order_col;
$_sort_ord=$af->sort;
$_status=$af->status;
$_assigned_to=$af->assigned_to;
$_extra_fields=$af->extra_fields;

$art_arr = $af->getArtifacts();

if (!$art_arr && $af->isError()) {
	exit_error($af->getErrorMessage(),'tracker');
}

//build page title to make bookmarking easier
//if a user was selected, add the user_name to the title
//same for status
html_use_jqueryui();
html_use_coolfieldset();

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
$order_name_arr[]=_('Id');
$order_name_arr[]=_('Priority');
$order_name_arr[]=_('Summary');
$order_name_arr[]=_('Open Date');
$order_name_arr[]=_('Last Modified Date');
$order_name_arr[]=_('Last Modifier');
$order_name_arr[]=_('Close Date');
$order_name_arr[]=_('Submitter');
$order_name_arr[]=_('Assignee');
$order_name_arr[]=_('# Votes');
$order_name_arr[]=_('# Voters');
$order_name_arr[]=_('% Votes');

$order_arr=array();
$order_arr[]='artifact_id';
$order_arr[]='priority';
$order_arr[]='summary';
$order_arr[]='open_date';
$order_arr[]='last_modified_date';
$order_arr[]='last_modified_by';
$order_arr[]='close_date';
$order_arr[]='submitted_by';
$order_arr[]='assigned_to';
$order_arr[]='_votes';
$order_arr[]='_voters';
$order_arr[]='_votage';

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
$changed_name_arr[]=_('Last 24 h');
$changed_name_arr[]=_('Last 7 days');
$changed_name_arr[]=_('Last 2 weeks');
$changed_name_arr[]=_('Last month');

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

echo $HTML->paging_top($start, $paging, $art_cnt, $max, '/tracker/?group_id='.$group_id.'&atid='.$ath->getID());

/**
 *
 *	Show the free-form text submitted by the project admin
 */
echo $ath->renderBrowseInstructions();

//
//	statuses can be custom in FusionForge 4.5+
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
	$checked_status = isset($aux_extra_fields[$ath->getCustomStatusField()]) ? $aux_extra_fields[$ath->getCustomStatusField()] : '';
	$status_box=$ath->renderSelect ($ath->getCustomStatusField(), $checked_status, false, '', true, _('Any'));
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
	.'" typeof="sioc:Container">'."\n";
print '<span rel="http://www.w3.org/2002/07/owl#sameAs" resource="" ></span>'."\n";
print '<span rev="doap:bug-database sioc:space_of" resource="'. $proj_url .'" ></span>'."\n";
print "</div>\n"; // end of about

echo '
<script type="text/javascript">//<![CDATA[
jQuery(document).ready(function() {';
if ($af->query_type == 'custom') {
	echo '	jQuery("#tabber").tabs({active: 1});';
}
echo '
});
//]]></script>
<div id="tabber" class="tabber">
	<ul>
	<li><a href="#tabber-advancedquery" title="'._('Use project queries or build and use your own queries.').'">'._('Advanced queries').'</a></li>
	<li><a href="#tabber-simplefiltering" title="'._('Filtering by assignee, state, priority.').'">'._('Simple Filtering and Sorting').'</a></li>
	</ul>
	<div id="tabber-advancedquery">';

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
	echo $HTML->openForm(array('action' => '/tracker/', 'method' => 'get'));
	echo '<input type="hidden" name="group_id" value="'.$group_id.'" />';
	echo '<input type="hidden" name="atid" value="'.$ath->getID().'" />';
	echo '<input type="hidden" name="power_query" value="1" />';
	echo '	<table class="fullwidth">
	<tr>
	<td>
	';
	$optgroup['key'] = 'type';
	$optgroup['values'][0] = 'Private queries';
	$optgroup['values'][1] = 'Project queries';
	echo '<select name="query_id" id="query_id">';
	echo '<option value="100">' . _('Select One') . '</option>';
	$current = '';
	$selected = $af->getDefaultQuery();
	while ($row = db_fetch_array($res)) {
		if ($current != $row['type']) {
			if ($current !== '')
				echo '</optgroup>';
			$label = $row['type'] ? _('Project') : _('Private');
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
	echo '</select>
	<noscript><input type="submit" name="run" value="'._('Power Query').'" /></noscript>
	&nbsp;&nbsp;'.util_make_link('/tracker/?atid='. $ath->getID().'&group_id='.$group_id.'&func=query', _('Build Query')).'
	</td></tr></table>';
	echo $HTML->closeForm();
	echo '
		<script type="text/javascript">//<![CDATA[
		jQuery("#query_id").change(function() {
			location.href = '.util_make_url('/tracker/?group_id='.$group_id.'&atid='.$ath->getID().'&power_query=1&query_id=').'+jQuery("#query_id").val();
		});
		//]]></script>';
} else {

	echo util_make_link('/tracker/?atid='.$ath->getID().'&group_id='.$group_id.'&func=query','<strong>'._('Build Query').'</strong>');
}
echo '
	</div>
	<div id="tabber-simplefiltering">';
echo $HTML->openForm(array('action' => '/tracker/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
echo '
	<input type="hidden" name="query_id" value="-1" />
	<input type="hidden" name="set" value="custom" />
	<table>
	<tr>
	<td>
	'._('Assignee')._(':').'<br>'. $tech_box .'
	</td>
	<td>
	'._('State')._(':').'<br>'. $status_box .'
	</td>
	<td>
	'._('Changed')._(':').'<br>'.html_build_select_box_from_arrays($changed_arr, $changed_name_arr, '_changed', $_changed, false).'
	<td>';

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

echo _('Order by')._(': ').'<br>'.
	html_build_select_box_from_arrays($order_arr,$order_name_arr,'_sort_col',$_sort_col,false) .
	html_build_select_box_from_arrays($sort_arr,$sort_name_arr,'_sort_ord',$_sort_ord,false) .
	'</td>
	<td><br><input type="submit" name="submit" value="'._('Quick Browse').'" /></td>';

echo '
	</tr>
	</table>';
echo $HTML->closeForm();
echo '</div>';
if ($af->query_type == 'default') {
	echo '<div class="tabbertab tabbertabdefault" >';
	echo '<strong>';
	echo sprintf(_('Viewing only opened records by default, use “%1$s” or “%2$s” to change.'),
				_('Advanced queries'),
				_('Simple Filtering and Sorting'));
	echo '</strong>';
	echo '</div>' . "\n";
}
echo '</div>' . "\n";

$art_cnt = count($art_arr);
if ($art_arr && $art_cnt > 0) {

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
				@$count[ $extra_data[$custom_id]['value'] ]++;
			} else {
				@$count[ $art->getStatusName()]++;
			}
		}
		foreach($count as $n => $c) {
			$percent[$n] = round(100*$c/$art_cnt);
		}
		if ($ath->getCustomStatusField()) {
			$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
			$keys=array_keys($efarr);
			$field_id = $keys[0];
			$custom_states = $ath->getExtraFieldElements($field_id);
			$states = array();
			if (is_array($custom_states)) {
				foreach($custom_states as $state) {
					$states[] = $state['element_name'];
				}
			}
		} else {
			$colors = array('#ffa0a0', '#bae0ba');
			$res = $ath->getStatuses();
			while ($row = db_fetch_array($res)) {
				$states[] = $row['status_name'];
			}
		}

		$i=0;
		$graph = '';
		$legend = '';
		if (is_array($states)) {
			foreach($states as $name) {
				if (isset($count[$name]) && $count[$name]) {
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
		$set .= '&_assigned_to='.$_assigned_to.'&_status='.$_status.'&_sort_col='.$_sort_col.'&_sort_ord='.$_sort_ord;
		if (array_key_exists($ath->getCustomStatusField(),$_extra_fields)) {
			$set .= '&extra_fields['.$ath->getCustomStatusField().']='.$_extra_fields[$ath->getCustomStatusField()];
		}
	}


	$IS_ADMIN = forge_check_perm ('tracker', $ath->getID(), 'manager') ;

	if ($IS_ADMIN) {
		echo $HTML->openForm(array('name' => 'artifactList', 'action' => '/tracker/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
		echo '<input type="hidden" name="form_key" value="'.form_generate_key().'" />
		<input type="hidden" name="func" value="massupdate" />';
	}

	$check_all = '';
	if ($IS_ADMIN) {
		$check_all = '
		<a href="javascript:checkAllArtifacts(1)">'._('Check all').'</a>
		-
		<a href="javascript:checkAllArtifacts(0)">'._('Clear all').'</a>';
	}

	$pager = '';
	$browse_fields = explode(',', "id,".$ath->getBrowseList());
	if (!in_array($_sort_col,$browse_fields) && $_sort_col!='artifact_id') {
		$browse_fields[] = $_sort_col;
	}

	$title_arr=array();
	foreach ($browse_fields as $f) {
		$title=$f;
		switch ($f) {
			case 'id':
				$title=_('Id');
				break;
			case 'summary':
				$title=_('Summary');
				break;
			case 'details':
				$title=_('Description');
				break;
			case 'open_date':
				$title=_('Open Date');
				break;
			case 'close_date':
				$title=_('Close Date');
				break;
			case 'status_id':
				$title=_('State');
				break;
			case 'priority':
				$title=_('Priority');
				break;
			case 'assigned_to':
				$title=_('Assigned to');
				break;
			case 'submitted_by':
				$title=_('Submitted by');
				break;
			case 'related_tasks':
				$title=_('Related Tasks');
				break;
			case 'last_modified_date':
				$title=_('Last Modified Date');
				break;
			case 'last_modified_by':
				$title=_('Last Modifier');
				break;
			case '_votes':
				$title = _('# Votes');
				break;
			case '_voters':
				$title = _('# Voters');
				break;
			case '_votage':
				$title = _('% Voted');
				break;
			default:
				if (intval($f) > 0) {
					$title = $ath->getExtraFieldName($f);
				}
		}
		$title_arr[] = $title;
	}

	if ($start < $max) {
		echo $HTML->listTableTop ($title_arr);
	}

	$then=(time()-$ath->getDuePeriod());
	$voters = count($ath->getVoters());

	for ($i=$start; $i<$max; $i++) {
		$extra_data = $art_arr[$i]->getExtraFieldDataText();
		echo '
		<tr class=priority'. $art_arr[$i]->getPriority().'>';
		foreach ($browse_fields as $f) {
			switch ($f) {
				case 'id':
					echo '<td style="white-space: nowrap;">'.
						($IS_ADMIN?'<input type="checkbox" name="artifact_id_list[]" value="'.
						$art_arr[$i]->getID() .'" /> ':'').
						util_make_link('/tracker/?func=detail&aid='.
						$art_arr[$i]->getID().
						'&group_id='. $group_id .'&atid='.
						$ath->getID(),
						$art_arr[$i]->getID()).
						'</td>';
					break;
				case 'summary':
					echo '<td>'.
						util_make_link('/tracker/?func=detail&aid='.
						$art_arr[$i]->getID() .
						'&group_id='. $group_id .'&atid='.
						$ath->getID(),
						$art_arr[$i]->getSummary()).
						'</td>';
					break;
				case 'open_date':
					echo '<td>'. (($set != 'closed' && $art_arr[$i]->getOpenDate() < $then)?'* ':'&nbsp; ') .
						date(_('Y-m-d H:i'),$art_arr[$i]->getOpenDate()) .'</td>';
					break;
				case 'status_id':
					echo '<td>'. $art_arr[$i]->getStatusName() .'</td>';
					break;
				case 'priority':
					echo '<td class="priority'.$art_arr[$i]->getPriority()  .'">'. $art_arr[$i]->getPriority() .'</td>';
					break;
				case 'assigned_to':
					echo '<td>'. $art_arr[$i]->getAssignedRealName() .'</td>';
					break;
				case 'submitted_by':
					echo '<td>'. $art_arr[$i]->getSubmittedRealName() .'</td>';
					break;
				case 'last_modified_by':
					echo '<td>'. $art_arr[$i]->getLastModifiedRealName() .'</td>';
					break;
				case 'close_date':
					echo '<td>'. ($art_arr[$i]->getCloseDate() ?
						date(_('Y-m-d H:i'),$art_arr[$i]->getCloseDate()) :'&nbsp; ') .'</td>';
					break;
				case 'details':
					echo '<td>'. $art_arr[$i]->getDetails() .'</td>';
					break;
				case 'related_tasks':
					echo '<td>';
					$tasks_res = $art_arr[$i]->getRelatedTasks();
					$s ='';
					while ($rest = db_fetch_array($tasks_res)) {
						$link = '/pm/task.php?func=detailtask&project_task_id='.$rest['project_task_id'].
							'&group_id='.$group_id.'&group_project_id='.$rest['group_project_id'];
						$title = '[T'.$rest['project_task_id'].']';
						if ($rest['status_id'] == 2) {
							$title = '<span class="strike">'.$title.'</span>';
						}
						echo $s.util_make_link($link, $title, array( 'title' => util_html_secure($rest['summary'])));
						$s = ' ';
					}
					echo '</td>';
					break;
				case 'last_modified_date':
					echo '<td>'. ($art_arr[$i]->getLastModifiedDate() ?
						date(_('Y-m-d H:i'),$art_arr[$i]->getLastModifiedDate()) :'&nbsp; ') .'</td>';
					break;
				case '_votes':
					$v = $art_arr[$i]->getVotes();
					echo html_e('td', array(), $v[0], false);
					break;
				case '_voters':
					echo html_e('td', array(), $voters, false);
					break;
				case '_votage':
					$v = $art_arr[$i]->getVotes();
					echo html_e('td', array(), $v[2], false);
					break;
				default:
					if (intval($f) > 0) {
						// Now display extra-fields (fields are numbers).
						$value = $extra_data[$f]['value'];
						if ($extra_data[$f]['type'] == 9) {
							$value = preg_replace('/\b(\d+)\b/e', "_artifactid2url('\\1')", $value);
						} elseif ($extra_data[$f]['type'] == 7) {
							if ($art_arr[$i]->getStatusID() == 2) {
								$value = '<span class="strike">'.$value.'</span>';
							}
						}
						echo '<td>' . $value .'</td>';
					} else {
						// Display ? for unknown values.
						echo '<td>?</td>';
					}
			}
		}
		echo '</tr>';
	}

	if ($start < $max) {
		echo $HTML->listTableBottom();
	}
	echo $HTML->paging_bottom($start, $paging, $art_cnt, '/tracker/?func=browse&group_id='.$group_id.'&atid='.$ath->getID().'&set='. $set);

	echo '<div style="display:table;width:100%">';
	echo '<div style="display:table-row">';

	echo '<div style="display:table-cell">';
	printf(_('* Denotes requests > %s Days Old'), ($ath->getDuePeriod()/86400));
	echo '</div>';

	if (in_array('priority', $browse_fields)) {
		echo '<div style="display:table-cell;text-align:right">';
		show_priority_colors_key();
		echo '</div>';
	}
	echo '</div>';

	echo '<div style="display:table-row">';

	echo '<div style="display:table-cell">'.$check_all.'</div>';
	echo '<div style="display:table-cell;text-align:right">'.$pager.'</div>'."\n";

	echo '</div>';
	echo '</div>';

	/*
		Mass Update Code
	*/
	if ($IS_ADMIN) {
		echo '<fieldset id="fieldset1_closed" class="coolfieldset">
	<legend>'._('Mass Update').'</legend>
	<div>
		<table class="fullwidth" id="admin_mass_update">
			<tr><td colspan="2">';
		echo $HTML->information(_('If you wish to apply changes to all items selected above, use these controls to change their properties and click once on “Mass Update”.')).'
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
		$ath->renderExtraFields($sel,true,_('No Change'),false,'', array(ARTIFACT_EXTRAFIELD_FILTER_INT),true,'UPDATE');
		echo '<tr>';
		echo '<td><strong>'._('Priority')._(':').'</strong><br />';
		echo build_priority_select_box ('priority', '100', true);
		echo '</td>';
		echo '<td>';
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td><strong>'._('Assigned to')._(':').'</strong><br />';
		echo $ath->technicianBox ('assigned_to','100.1',true,_('Nobody'),'100.1',_('No Change'));
		echo'</td>';
		echo '<td>';
		if (!$ath->usesCustomStatuses()) {
		echo '<strong>'._('State')._(': ').'</strong>
				<br />'. $ath->statusBox ('status_id','xzxz',true,_('No Change'));
		}
		echo '</td>';
		echo '</tr>';

		echo '<tr>';
		echo '<td colspan="2"><strong>'._('Canned Response')._(':').'</strong><br />';
		echo $ath->cannedResponseBox ('canned_response') .'</td></tr>

			<tr><td colspan="3" class="align-center"><input type="submit" name="submit" value="'._('Mass Update').'" /></td></tr>
			</table>';
		echo '</div>
		</fieldset>';
		echo $HTML->closeForm();
	}

} else {
	echo $HTML->information(_('No items found'));
	echo db_error();
}

$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

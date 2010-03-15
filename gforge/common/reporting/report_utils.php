<?php
/**
 * FusionForge reporting system
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
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

function report_header($title) {
	global $HTML ;
	echo $HTML->header(array('title'=>" "._('Reporting').": " . $title));
	if (isset($GLOBALS['feedback'])) {
		echo html_feedback_top($GLOBALS['feedback']);
	}
	echo "<h1>".sprintf(_('%1$s Reporting'), forge_get_config ('forge_name'))."</h1>\n";
}

function report_footer() {
	global $HTML;
	echo $HTML->footer(array());
}

function report_span_box($name='SPAN', $selected='1', $suppress_daily=false) {
	if ($suppress_daily) {
		$vals=array(2,3);
		$titles=array(_('Weekly'),
			_('Monthly'));
	} else {
		$vals=array(1,2,3);
		$titles=array(_('Daily'),
			_('Weekly'),
			_('Monthly'));
	}
	return html_build_select_box_from_arrays ($vals,$titles,$name,$selected,false);
}

function report_weeks_box($Report, $name='week', $selected=false) {
	$arr =& $Report->getWeekStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date(_('Y-m-d'), $arr[$i]) .' '._('to').' '. date(_('Y-m-d'), ($arr[$i]+6*24*60*60));
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_day_adjust_box($Report, $name='days_adjust', $selected=false) {
	$days[]='0';
	$days[]='1';
	$days[]='2';
	$days[]='3';
	$days[]='4';
	$days[]='5';
	$days[]='6';
	$names[]=_('Sunday');
	$names[]=_('Monday');
	$names[]=_('Tuesday');
	$names[]=_('Wednesday');
	$names[]=_('Thursday');
	$names[]=_('Friday');
	$names[]=_('Saturday');
	return html_build_select_box_from_arrays ($days,$names,$name,$selected,false);

//	return html_build_select_box_from_arrays (array_reverse(array_values($Report->adjust_days)),array_reverse(array_keys($Report->adjust_days)),$name,$selected,false);
}

function report_months_box($Report, $name='month', $selected=false) {
	$arr =& $Report->getMonthStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date(_('Y-m'),$arr[$i]);
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_useract_box($name='dev_id', $selected='1', $start_with='') {
	global $sys_database_type;

	if ($start_with) {
		if ( $sys_database_type == "mysql" ) {
			$res = db_query_mysql ("SELECT user_id,realname FROM users WHERE status='A' AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) AND (lower(lastname) LIKE lower('$start_with%') OR lower(user_name) LIKE lower('$start_with%')) ORDER BY lastname") ;
		} else {
			$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) AND (lower(lastname) LIKE $2 OR lower(user_name) LIKE $3) ORDER BY lastname',
						array ('A',
						       strtolower("$start_with%"),
						       strtolower("$start_with%"))) ;
		}
	} else {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) ORDER BY lastname',
					array ('A')) ;
	}
	return html_build_select_box($res, $name, $selected, false);
}

function report_usertime_box($name='dev_id', $selected='1', $start_with='') {
	global $sys_database_type;

	if ($start_with) {
		if ( $sys_database_type == "mysql" ) {
			$res = db_query_mysql ("SELECT user_id,realname FROM users WHERE status='A' AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) AND (lower(lastname) LIKE lower('$start_with%') OR lower(user_name) LIKE lower('$start_with%')) ORDER BY lastname") ;
		} else {
			$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) AND (lower(lastname) LIKE $2 OR lower(user_name) LIKE $3) ORDER BY lastname',
						array ('A',
						       strtolower("$start_with%"),
						       strtolower("$start_with%"))) ;
		}
	} else {
		$res = db_query_params ('SELECT user_id,realname FROM users WHERE status=$1 AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) ORDER BY lastname',
					array ('A')) ;
	}
	return html_build_select_box($res, $name, $selected, false);
}

function report_group_box($name='g_id', $selected='1') {

	$res = db_query_params ('SELECT group_id,group_name FROM groups WHERE status=$1 ORDER BY group_name',
				array ('A')) ;
	return html_build_select_box($res, $name, $selected, false);
}

function report_area_box($name='area', $selected='1', $Group=false) {
  if ($Group) {
    $sys_use_tracker = $Group->usesTracker();
    $sys_use_forum = $Group->usesForum();
    $sys_use_docman = $Group->usesDocman();
    $sys_use_pm = $Group->usesPM();
    $sys_use_frs = $Group->usesFRS();
  } else {
    $sys_use_tracker = $GLOBALS['sys_use_tracker'];
    $sys_use_forum = $GLOBALS['sys_use_forum'];
    $sys_use_docman = $GLOBALS['sys_use_docman'];
    $sys_use_pm = $GLOBALS['sys_use_pm'];
    $sys_use_frs = $GLOBALS['sys_use_frs'];
  }
  if ($sys_use_tracker) {
    $arr[]='tracker';
    $arr2[]=_('Tracker');
  }
  if ($sys_use_forum) {
    $arr[]='forum';
    $arr2[]=_('Forums');
  }
  if ($sys_use_docman) {
    $arr[]='docman';
    $arr2[]=_('Docs');
  }
  if ($sys_use_pm) {
    $arr[]='taskman';
    $arr2[]=_('Tasks');
  }
  if ($sys_use_frs) {
    $arr[]='downloads';
    $arr2[]=_('Downloads');
  }

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_tracker_box($name='datatype', $selected='1') {
  if ($GLOBALS['sys_use_tracker']) {
    $arr[]=_('Bugs');
    $arr[]=_('Support');
    $arr[]=_('Patches');
    $arr[]=_('Feature Requests');
    $arr[]=_('Other Trackers');
    $arr2[]='1';
    $arr2[]='2';
    $arr2[]='3';
    $arr2[]='4';
    $arr2[]='0';
  }
  if ($GLOBALS['sys_use_forum']) {
    $arr[]=_('Forum Messages');
    $arr2[]='5';
  }
  if ($GLOBALS['sys_use_pm']) {
    $arr[]=_('Tasks');
    $arr2[]='6';
  }
  if ($GLOBALS['sys_use_frs']) {
    $arr[]=_('Downloads');
    $arr2[]='7';
  }
	return html_build_select_box_from_arrays ($arr2,$arr,$name,$selected,false);
}

function report_time_category_box($name='category',$selected=false) {
	global $report_time_category_res;
	if (!$report_time_category_res) {
		$report_time_category_res = db_query_params ('SELECT * FROM rep_time_category',
							     array()) ;
	}
	return html_build_select_box($report_time_category_res,$name,$selected,false);
}

//
//	Takes an array of labels and an array values and removes vals < 2% and sets up an "other"
//
function report_pie_arr($labels, $vals, $format=1) {
	global $pie_labels,$pie_vals;
	//first get sum of all values
	for ($i=0; $i<count($vals); $i++) {
		$total += $vals[$i];
	}

	//now prune out vals where < 2%
	for ($i=0; $i<count($vals); $i++) {
		if (($vals[$i]/$total) < .02) {
			$rem += $vals[$i];
		} else {
			$pie_labels[]=utf8_decode(util_unconvert_htmlspecialchars($labels[$i]))." (". number_format($vals[$i],$format) .") ";
			$pie_vals[]=$vals[$i];
		}
	}
	if ($rem > 0) {
		$pie_labels[]=_('Other')." (". number_format($rem,$format) .") ";
		$pie_vals[]=$rem;
	}
	
}

function report_package_box($group_id, $name='dev_id', $selected='') {

	$res = db_query_params ('SELECT package_id, name FROM frs_package WHERE frs_package.group_id = $1',
				array ($group_id));
	return html_build_select_box($res, $name, $selected, false);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

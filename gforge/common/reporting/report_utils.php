<?php
/**
 * Reporting System
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2003-03-16
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function report_header($title) {
	global $HTML,$sys_name,$Language;
	echo $HTML->header(array('title'=>" ".$Language->getText('reporting','title').": " . $title));
	echo html_feedback_top($GLOBALS['feedback']);
	echo "<h2>".$Language->getText('reporting','subtitle',$sys_name)."</h2><p>";
}

function report_footer() {
	global $HTML;
	echo html_feedback_bottom($GLOBALS['feedback']);
	echo $HTML->footer(array());
}

function report_span_box($name='SPAN', $selected='1', $suppress_daily=false) {
	global $Language;
	if ($suppress_daily) {
		$vals=array(2,3);
		$titles=array($Language->getText('reporting','weekly'),
			$Language->getText('reporting','monthly'));
	} else {
		$vals=array(1,2,3);
		$titles=array($Language->getText('reporting','daily'),
			$Language->getText('reporting','weekly'),
			$Language->getText('reporting','monthly'));
	}
	return html_build_select_box_from_arrays ($vals,$titles,$name,$selected,false);
}

function report_weeks_box($Report, $name='week', $selected=false) {
	global $sys_shortdatefmt, $Language;
	$arr =& $Report->getWeekStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date($sys_shortdatefmt, $arr[$i]) .' '.$Language->getText('general', 'to').' '. date($sys_shortdatefmt, ($arr[$i]+6*24*60*60));
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_day_adjust_box($Report, $name='days_adjust', $selected=false) {
	global $Language;
	$days[]='0.0';
	$days[]='1';
	$days[]='2';
	$days[]='3';
	$days[]='4';
	$days[]='5';
	$days[]='6';
	$names[]=$Language->getText('calendar','sunday');
	$names[]=$Language->getText('calendar','monday');
	$names[]=$Language->getText('calendar','tuesday');
	$names[]=$Language->getText('calendar','wednesday');
	$names[]=$Language->getText('calendar','thursday');
	$names[]=$Language->getText('calendar','friday');
	$names[]=$Language->getText('calendar','saturday');
	return html_build_select_box_from_arrays ($days,$names,$name,$selected,false);

//	return html_build_select_box_from_arrays (array_reverse(array_values($Report->adjust_days)),array_reverse(array_keys($Report->adjust_days)),$name,$selected,false);
}

function report_months_box($Report, $name='month', $selected=false) {
	global $Language;
	$arr =& $Report->getMonthStartArr();

	$arr2=array();
	for ($i=0; $i<count($arr); $i++) {
		$arr2[$i]=date($Language->getText('calendar', 'monthdatefmt'),$arr[$i]);
	}

	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_useract_box($name='dev_id', $selected='1', $start_with='') {

	if ($start_with) {
		$sql2=" AND lastname ILIKE '$start_with%' ";
	}

	$res=db_query("SELECT user_id,realname 
		FROM users 
		WHERE status='A' $sql2 
		AND (exists (SELECT user_id FROM rep_user_act_daily WHERE user_id=users.user_id)) ORDER BY lastname");
	return html_build_select_box($res, $name, $selected, false);
}

function report_usertime_box($name='dev_id', $selected='1', $start_with='') {

	if ($start_with) {
		$sql2=" AND lastname ILIKE '$start_with%' ";
	}

	$res=db_query("SELECT user_id,realname 
		FROM users 
		WHERE status='A' $sql2 
		AND (exists (SELECT user_id FROM rep_time_tracking WHERE user_id=users.user_id)) ORDER BY lastname");
	return html_build_select_box($res, $name, $selected, false);
}

function report_group_box($name='g_id', $selected='1') {

	$res=db_query("SELECT group_id,group_name FROM groups WHERE status='A' ORDER BY group_name");
	return html_build_select_box($res, $name, $selected, false);
}

function report_area_box($name='area', $selected='1') {
	global $Language;
	$arr[]='tracker';
	$arr[]='forum';
	$arr[]='docman';
	$arr[]='taskman';
	$arr[]='downloads';

	$arr2[]=$Language->getText('group','short_tracker');
	$arr2[]=$Language->getText('group','short_forum');
	$arr2[]=$Language->getText('group','short_docman');
	$arr2[]=$Language->getText('group','short_pm');
	$arr2[]=$Language->getText('stats_site_utils','downloads');
	return html_build_select_box_from_arrays ($arr,$arr2,$name,$selected,false);
}

function report_tracker_box($name='datatype', $selected='1') {
	global $Language;
	$arr[]=$Language->getText('group','short_bugs');
	$arr[]=$Language->getText('group','short_support');
	$arr[]=$Language->getText('group','short_patch');
	$arr[]=$Language->getText('reporting','feature_req');
	$arr[]=$Language->getText('reporting','other_trackers');
	$arr[]=$Language->getText('reporting','forum_messages');
	$arr[]=$Language->getText('group','short_pm');
	$arr[]=$Language->getText('stats_site_utils','downloads');

	$arr2[]='1';
	$arr2[]='2';
	$arr2[]='3';
	$arr2[]='4';
	$arr2[]='0';
	$arr2[]='5';
	$arr2[]='6';
	$arr2[]='7';
	return html_build_select_box_from_arrays ($arr2,$arr,$name,$selected,false);
}

function report_time_category_box($name='category',$selected=false) {
	global $report_time_category_res;
	if (!$report_time_category_res) {
		$report_time_category_res=db_query("SELECT * FROM rep_time_category");
	}
	return html_build_select_box($report_time_category_res,$name,$selected,false);
}

//
//	Takes an array of labels and an array values and removes vals < 2% and sets up an "other"
//
function report_pie_arr($labels, $vals) {
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
			$pie_labels[]=util_unconvert_htmlspecialchars($labels[$i])." (". number_format($vals[$i],1) .") ";
			$pie_vals[]=number_format($vals[$i],1);
		}
	}
	if ($rem > 0) {
		$pie_labels[]='Other'." (". number_format($rem,1) .") ";
		$pie_vals[]=number_format($rem,1);
	}
	
}

?>

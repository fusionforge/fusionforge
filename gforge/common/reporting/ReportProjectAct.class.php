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

require_once('common/reporting/Report.class.php');

class ReportProjectAct extends Report {

var $res;

function ReportProjectAct($span,$group_id,$start=0,$end=0) {
	$this->Report();

	if (!$start) {
		$start=mktime(0,0,0,date('m'),1,date('Y'));;
	}
	if (!$end) {
		$end=time();
	} else {
		$end--;
	}

	if (!$group_id) {
		$this->setError('No group_id');
		return false;
	}
	if (!$span || $span == REPORT_TYPE_MONTHLY) {

		$res=db_query("SELECT * FROM rep_group_act_monthly 
			WHERE group_id='$group_id' AND month BETWEEN '$start' AND '$end' ORDER BY month");

	} elseif ($span == REPORT_TYPE_WEEKLY) {

		$res=db_query("SELECT * FROM rep_group_act_weekly 
			WHERE group_id='$group_id' AND week BETWEEN '$start' AND '$end' ORDER BY week");

	} elseif ($span == REPORT_TYPE_DAILY) {

		$res=db_query("SELECT * FROM rep_group_act_daily 
			WHERE group_id='$group_id' AND day BETWEEN '$start' AND '$end' ORDER BY day ASC");

	}

	$this->start_date=$start;
	$this->end_date=$end;

	if (!$res || db_error()) {
		$this->setError('ReportProjectAct:: '.db_error());
		return false;
	}
	$this->setSpan($span);
	$this->setDates($res,1);
	$this->res=$res;
	return true;
}

function &getTrackerOpened() {
	return util_result_column_to_array($this->res,2);
}

function &getTrackerClosed() {
	return util_result_column_to_array($this->res,3);
}

function &getForum() {
	return util_result_column_to_array($this->res,4);
}

function &getDocs() {
	return util_result_column_to_array($this->res,5);
}

function &getDownloads() {
	return util_result_column_to_array($this->res,6);
}

function &getCVSCommits() {
	return util_result_column_to_array($this->res,7);
}

function &getTaskOpened() {
	return util_result_column_to_array($this->res,8);
}

function &getTaskClosed() {
	return util_result_column_to_array($this->res,9);
}

}

?>

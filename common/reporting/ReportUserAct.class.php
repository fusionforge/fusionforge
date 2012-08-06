<?php
/**
 * FusionForge reporting system
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
 * Copyright 2009, Roland Mas
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

require_once $gfcommon.'reporting/Report.class.php';

class ReportUserAct extends Report {

var $res;

function ReportUserAct($span,$user_id,$start=0,$end=0) {
	$this->Report();

	if (!$start) {
		$start=mktime(0,0,0,date('m'),1,date('Y'));;
	}
	if (!$end) {
		$end=time();
	} else {
		$end--;
	}

	if (!$user_id) {
		$this->setError('No User_id');
		return false;
	}
	if (!$span || $span == REPORT_TYPE_MONTHLY) {
		$res = db_query_params ('SELECT * FROM rep_user_act_monthly WHERE user_id=$1 AND month BETWEEN $2 AND $3 ORDER BY month ASC',
					array ($user_id,
					       $start,
					       $end)) ;
	} elseif ($span == REPORT_TYPE_WEEKLY) {
		$res = db_query_params ('SELECT * FROM rep_user_act_weekly WHERE user_id=$1 AND week BETWEEN $2 AND $3 ORDER BY week ASC',
					array ($user_id,
					       $start,
					       $end)) ;
	} elseif ($span == REPORT_TYPE_DAILY) {
		$res = db_query_params ('SELECT * FROM rep_user_act_daily WHERE user_id=$1 AND day BETWEEN $2 AND $3 ORDER BY day ASC',
					array ($user_id,
					       $start,
					       $end)) ;
	}

	$this->start_date=$start;
	$this->end_date=$end;

	if (!$res || db_error()) {
		$this->setError('ReportUserAct:: '.db_error());
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

function &getCVSCommits() {
	return util_result_column_to_array($this->res,6);
}

function &getTaskOpened() {
	return util_result_column_to_array($this->res,7);
}

function &getTaskClosed() {
	return util_result_column_to_array($this->res,8);
}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

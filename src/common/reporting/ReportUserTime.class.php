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

class ReportUserTime extends Report {

function ReportUserTime($user_id,$type,$start=0,$end=0) {
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

	//
	//	Task report
	//
	if (!$type || $type=='tasks') {
		$res = db_query_params ('SELECT pt.summary,sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, project_task pt
			WHERE rtt.user_id=$1 
			AND rtt.report_date BETWEEN $2 AND $3
			AND rtt.project_task_id=pt.project_task_id
			GROUP BY pt.summary
			ORDER BY hours DESC',
					array ($user_id,
					       $start,
					       $end)) ;
	//
	//	Category report
	//
	} elseif ($type=='category') {
		$res = db_query_params ('SELECT rtc.category_name, sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, rep_time_category rtc
			WHERE rtt.user_id=$1 
			AND rtt.report_date BETWEEN $2 AND $3
			AND rtt.time_code=rtc.time_code
			GROUP BY rtc.category_name
			ORDER BY hours DESC',
					array ($user_id,
					       $start,
					       $end)) ;
	//
	//	Percentage this user spent on a specific subproject
	//
	} elseif ($type=='subproject') {
		$res = db_query_params ('SELECT pgl.project_name, sum(rtt.hours) AS hours 
			FROM rep_time_tracking rtt, project_task pt, project_group_list pgl
			WHERE rtt.user_id=$1 
			AND rtt.report_date BETWEEN $2 AND $3
			AND rtt.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			GROUP BY pgl.project_name
			ORDER BY hours DESC',
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

	$this->labels = util_result_column_to_array($res,0);
	$this->setData($res,1);
	return true;
}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

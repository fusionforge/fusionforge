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

class ReportTrackerAct extends Report {

var $res;
var $avgtime;
var $opencount;
var $stillopencount;

function ReportTrackerAct($span,$group_id,$atid,$start=0,$end=0) {
	$this->Report();

	if (!$start) {
		$start=mktime(0,0,0,date('m'),1,date('Y')-1);
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

		$arr =& $this->getMonthStartArr();

		for ($i=1; $i<count($arr); $i++) {
			if ($arr[$i]<$start || $arr[$i]>$end) {
				//skip this month as it's not in the range
			} else {

				$this->labels[]=date('M d',$arr[$i]).' <-> '.date('M d',($arr[$i-1]-1));
				$this->avgtime[]=$this->getAverageTime($atid,$arr[$i],($arr[$i-1]-1));
				$this->opencount[]=$this->getOpenCount($atid,$arr[$i],($arr[$i-1]-1));
				$this->stillopencount[]=$this->getStillOpenCount($atid,$arr[$i],($arr[$i-1]-1));

			}
		}

	} elseif ($span == REPORT_TYPE_WEEKLY) {

		$arr =& $this->getWeekStartArr();

		for ($i=1; $i<count($arr); $i++) {
			if ($arr[$i]<$start || $arr[$i]>$end) {
				//skip this month as it's not in the range
			} else {

				$this->labels[]=date('M d',$arr[$i]).' <-> '.date('M d',($arr[$i-1]-1));
				$this->avgtime[]=$this->getAverageTime($atid,$arr[$i],($arr[$i-1]-1));
				$this->opencount[]=$this->getOpenCount($atid,$arr[$i],($arr[$i-1]-1));
				$this->stillopencount[]=$this->getStillOpenCount($atid,$arr[$i],($arr[$i-1]-1));

			}
		}

	}

	$this->start_date=$start;
	$this->end_date=$end;

	$this->setSpan($span);
	$this->setDates($res,1);
	$this->res=$res;
	return true;
}

function getAverageTime($atid,$start,$end) {
	$sql="SELECT avg((close_date-open_date)/(24*60*60)) AS avgtime
		FROM artifact
		WHERE group_artifact_id='$atid'
		AND close_date > 0
		AND (open_date >= '$start' AND open_date <= '$end')";
	$res=db_query($sql);
echo db_error();
	return db_result($res,0,0);
}

function getOpenCount($atid,$start,$end) {
	$sql="SELECT count(*)
		FROM artifact
		WHERE 
		group_artifact_id='$atid'
		AND open_date >= '$start'
		AND open_date <= '$end'";

	$res=db_query($sql);
echo db_error();
	return db_result($res,0,0);
}

function getStillOpenCount($atid,$start,$end) {
	$sql="SELECT count(*)
		FROM artifact
		WHERE 
		group_artifact_id='$atid'
		AND open_date <= '$end'
		AND (close_date >= '$end' OR close_date < 1 OR close_date is null)";

	$res=db_query($sql);
echo db_error();
	return db_result($res,0,0);
}

function &getAverageTimeData() {
	return $this->avgtime;
}

function &getOpenCountData() {
	return $this->opencount;
}

function &getStillOpenCountData() {
	return $this->stillopencount;
}

}

?>

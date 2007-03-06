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

DEFINE('REPORT_DAY_SPAN',24*60*60);
DEFINE('REPORT_WEEK_SPAN',7*24*60*60);
DEFINE('REPORT_MONTH_SPAN',30*24*60*60);

DEFINE('REPORT_TYPE_DAILY',1);
DEFINE('REPORT_TYPE_WEEKLY',2);
DEFINE('REPORT_TYPE_MONTHLY',3);
DEFINE('REPORT_TYPE_OA',4);

class Report extends Error {

//var $adjust_days=array('Sun'=>0, 'Sat'=>6, 'Fri'=>5, 'Thu'=>4, 'Wed'=>3, 'Tue'=>2, 'Mon'=>1);
var $adjust_days=array('Sun'=>'0.0', 'Sat'=>1, 'Fri'=>2, 'Thu'=>3, 'Wed'=>4, 'Tue'=>5, 'Mon'=>6);
var $month_start_arr=array();
var $week_start_arr=array();
var $site_start_date;
var $data;
var $labels;
var $span;
var $start_date;
var $end_date;
var $span_name=array(1=>'Daily',2=>'Weekly',3=>'Monthly',4=>'OverAll');
var $graph_interval=array(1=>7,2=>1,3=>1,4=>1);

function Report() {
	$this->Error();
	//
	//	All reporting action will be done in GMT timezone
	//
	putenv('TZ=GMT');
}

/**
 *	get the unix time that this install was setup.
 */
function getMinDate() {
	if (!$this->site_start_date) {
		$res=db_query("select min(add_date) AS start_date from users where add_date > 0;");
		$this->site_start_date=db_result($res,0,'start_date');
	}
	return $this->site_start_date;
}

function &getMonthStartArr() {
	if (count($this->month_start_arr) < 1) {
		$min_date=$this->getMinDate();
		for ($i=0; $i<24; $i++) {
			$this->month_start_arr[]=mktime(0,0,0,date('m')+1-$i,1,date('Y'));
			if ($this->month_start_arr[$i] < $min_date) {
				break;
			}
		}
	}
	return $this->month_start_arr;
}

function &getWeekStartArr() {
	if (count($this->week_start_arr) < 1) {
		$min_date=$this->getMinDate();
		$start=mktime(0,0,0,date('m'),(date('d')+$this->adjust_days[date('D')]),date('Y'));
		for ($i=0; $i<104; $i++) {
			$this->week_start_arr[]=($start-REPORT_WEEK_SPAN*$i);
			if ($this->week_start_arr[$i] < $min_date) {
				break;
			}
		}
	}
	return $this->week_start_arr;
}

function setSpan($span) {
	$this->span=$span;
}

function getSpanName() {
	return $this->span_name[$this->span];
}

function setData($result,$column) {
	$this->data =& util_result_column_to_array($result,$column);
}

function setDates($result,$column) {
	$arr =& util_result_column_to_array($result,$column);
	for ($i=0; $i<count($arr); $i++) {
		$this->labels[$i] = date('M d',$arr[$i]);
	}
}

function getGraphInterval() {
	return $this->graph_interval[$this->span];
}

function &getData() {
	return $this->data;
}

function &getDates() {
	return $this->labels;
}

function getStartDate() {
	return $this->start_date;
}

function getEndDate() {
	return $this->end_date;
}

}

?>

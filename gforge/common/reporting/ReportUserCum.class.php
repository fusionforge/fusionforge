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

require_once $gfcommon.'reporting/Report.class.php';

class ReportUserCum extends Report {

function ReportUserCum($span,$start=0,$end=0) {
	$this->Report();

	if (!$start) {
		$start=mktime(0,0,0,date('m'),1,date('Y'));;
	}
	if (!$end) {
		$end=time();
	} else {
		$end--;
	}

	if (!$span || $span == REPORT_TYPE_MONTHLY) {

		$res=db_query("SELECT * FROM rep_users_cum_monthly 
			WHERE month BETWEEN '$start' AND '$end' ORDER BY month");

	} elseif ($span == REPORT_TYPE_WEEKLY) {

		$res=db_query("SELECT * FROM rep_users_cum_weekly 
			WHERE week BETWEEN '$start' AND '$end' ORDER BY week");

	} elseif ($span == REPORT_TYPE_DAILY) {

		$res=db_query("SELECT * FROM rep_users_cum_daily 
			WHERE day BETWEEN '$start' AND '$end' ORDER BY day ASC");

	}

	$this->start_date=$start;
	$this->end_date=$end;

	if (!$res || db_error()) {
		$this->setError('ReportUserAdded:: '.db_error());
		return false;
	}
	$this->setSpan($span);
	$this->setDates($res,0);
	$this->setData($res,1);
	return true;
}

}

?>

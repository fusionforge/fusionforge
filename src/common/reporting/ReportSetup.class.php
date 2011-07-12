<?php
/**
 * FusionForge reporting system
 *
 * Copyright 2003-2004, Tim Perdue/GForge, LLC
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

class ReportSetup extends Report {

function ReportSetup() {
	$this->Report();

}

function initialSetup() {
	$this->createTables();
	if (!$this->initialData()) {
		return false;
	} else {
		return true;
	}
}

function createTables() {
//time tracking
//DROP TABLE rep_time_category;
	$sql[]="CREATE TABLE rep_time_category (
	time_code serial UNIQUE,
	category_name text
	);";
	//$sql[]="DROP TABLE rep_time_tracking;";
	$sql1="CREATE TABLE rep_time_tracking (
		week int not null,
		report_date int not null,
		user_id int not null,
		project_task_id int not null,
		time_code int not null CONSTRAINT reptimetrk_timecode REFERENCES rep_time_category(time_code),
                hours float not null);";
	$sql[]=$sql1;
//	$sql[]="CREATE UNIQUE INDEX reptimetrk_weekusrtskcde ON
//		rep_time_tracking (week,user_id,project_task_id,time_code);";
	$sql[]="CREATE INDEX reptimetracking_userdate ON
		rep_time_tracking (user_id,week);";

	$sql[]="INSERT INTO rep_time_category VALUES ('1','Coding');";
	$sql[]="INSERT INTO rep_time_category VALUES ('2','Testing');";
	$sql[]="INSERT INTO rep_time_category VALUES ('3','Meeting');";
	$sql[]="SELECT setval('rep_time_category_time_code_seq',(SELECT max(time_code) FROM rep_time_category));";

//added users
	$sql[]="DROP TABLE rep_users_added_daily;";
	$sql[]="CREATE TABLE rep_users_added_daily (
	day int not null primary key,
	added int not null default 0);";

	$sql[]="DROP TABLE rep_users_added_weekly";
	$sql[]="CREATE TABLE rep_users_added_weekly (
	week int not null primary key,
	added int not null default 0);";

	$sql[]="DROP TABLE rep_users_added_monthly";
	$sql[]="CREATE TABLE rep_users_added_monthly (
	month int not null primary key,
	added int not null default 0);";

//cumulative users
	$sql[]="DROP TABLE rep_users_cum_daily";
	$sql[]="CREATE TABLE rep_users_cum_daily (
	day int not null primary key,
	total int not null default 0);";

	$sql[]="DROP TABLE rep_users_cum_weekly";
	$sql[]="CREATE TABLE rep_users_cum_weekly (
	week int not null primary key,
	total int not null default 0);";

	$sql[]="DROP TABLE rep_users_cum_monthly";
	$sql[]="CREATE TABLE rep_users_cum_monthly (
	month int not null primary key,
	total int not null default 0);";

//added groups
	$sql[]="DROP TABLE rep_groups_added_daily;";
	$sql[]="CREATE TABLE rep_groups_added_daily (
	day int not null primary key,
	added int not null default 0);";

	$sql[]="DROP TABLE rep_groups_added_weekly";
	$sql[]="CREATE TABLE rep_groups_added_weekly (
	week int not null primary key,
	added int not null default 0);";

	$sql[]="DROP TABLE rep_groups_added_monthly";
	$sql[]="CREATE TABLE rep_groups_added_monthly (
	month int not null primary key,
	added int not null default 0);";

//cumulative groups
	$sql[]="DROP TABLE rep_groups_cum_daily";
	$sql[]="CREATE TABLE rep_groups_cum_daily (
	day int not null primary key,
	total int not null default 0);";

	$sql[]="DROP TABLE rep_groups_cum_weekly";
	$sql[]="CREATE TABLE rep_groups_cum_weekly (
	week int not null primary key,
	total int not null default 0);";

	$sql[]="DROP TABLE rep_groups_cum_monthly";
	$sql[]="CREATE TABLE rep_groups_cum_monthly (
	month int not null primary key,
	total int not null default 0);";

//per-user activity
	$sql[]="DROP TABLE rep_user_act_daily";
	$sql[]="CREATE TABLE rep_user_act_daily (
	user_id int not null,
	day int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (user_id,day));";

	$sql[]="DROP TABLE rep_user_act_weekly";
	$sql[]="CREATE TABLE rep_user_act_weekly (
	user_id int not null,
	week int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (user_id,week));";

	$sql[]="DROP TABLE rep_user_act_monthly";
	$sql[]="CREATE TABLE rep_user_act_monthly (
	user_id int not null,
	month int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (user_id,month));";

	$sql[]="DROP VIEW rep_user_act_oa_vw";
	$sql[]="CREATE VIEW rep_user_act_oa_vw AS
	SELECT user_id,
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_user_act_monthly
	GROUP BY user_id;";

//per-project activity
	$sql[]="DROP TABLE rep_group_act_daily";
	$sql[]="CREATE TABLE rep_group_act_daily (
	group_id int not null,
	day int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	downloads int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (group_id,day));";

	$sql[]="DROP INDEX repgroupactdaily_day";
	$sql[]="CREATE INDEX repgroupactdaily_day ON rep_group_act_daily(day)";

	$sql[]="DROP TABLE rep_group_act_weekly";
	$sql[]="CREATE TABLE rep_group_act_weekly (
	group_id int not null,
	week int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	downloads int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (group_id,week));";

	$sql[]="DROP INDEX repgroupactweekly_week";
	$sql[]="CREATE INDEX repgroupactweekly_week ON rep_group_act_weekly(week)";

	$sql[]="DROP TABLE rep_group_act_monthly";
	$sql[]="CREATE TABLE rep_group_act_monthly (
	group_id int not null,
	month int not null,
	tracker_opened int not null,
	tracker_closed int not null,
	forum int not null,
	docs int not null,
	downloads int not null,
	cvs_commits int not null,
	tasks_opened int not null,
	tasks_closed int not null,
	PRIMARY KEY (group_id,month));";

	$sql[]="DROP INDEX repgroupactmonthly_month";
	$sql[]="CREATE INDEX repgroupactmonthly_month ON rep_group_act_monthly(month)";

	$sql[]="DROP VIEW rep_group_act_oa_vw";
	$sql[]="CREATE VIEW rep_group_act_oa_vw AS
	SELECT group_id,
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(downloads) AS downloads,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_group_act_monthly
	GROUP BY group_id;";

//overall activity
	$sql[]="DROP VIEW rep_site_act_daily_vw";
	$sql[]="CREATE VIEW rep_site_act_daily_vw AS
	SELECT day,
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(downloads) AS downloads,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_group_act_daily
	GROUP BY day;";

	$sql[]="DROP VIEW rep_site_act_weekly_vw";
	$sql[]="CREATE VIEW rep_site_act_weekly_vw AS
	SELECT week,
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(downloads) AS downloads,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_group_act_weekly
	GROUP BY week;";

	$sql[]="DROP VIEW rep_site_act_monthly_vw";
	$sql[]="CREATE VIEW rep_site_act_monthly_vw AS
	SELECT month,
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(downloads) AS downloads,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_group_act_monthly
	GROUP BY month;";

	$sql[]="DROP VIEW rep_site_act_oa_vw";
	$sql[]="CREATE VIEW rep_site_act_oa_vw AS
	sum(tracker_opened) AS tracker_opened,
	sum(tracker_closed) AS tracker_closed,
	sum(forum) AS forum,
	sum(docs) AS docs,
	sum(downloads) AS downloads,
	sum(cvs_commits) AS cvs_commits,
	sum(tasks_opened) AS tasks_opened,
	sum(tasks_closed) AS tasks_closed
	FROM rep_group_act_monthly;";

	for ($i=0; $i<count($sql); $i++) {

		$res=db_query_params($sql[$i], array());

	}

}

function initialData() {
	if (!$this->backfill_users_added_daily()) {
		return false;
	}
	if (!$this->backfill_users_added_weekly()) {
		return false;
	}
	if (!$this->backfill_users_added_monthly()) {
		return false;
	}
	if (!$this->backfill_users_cum_daily()) {
		return false;
	}
	if (!$this->backfill_users_cum_weekly()) {
		return false;
	}
	if (!$this->backfill_users_cum_monthly()) {
		return false;
	}
	if (!$this->backfill_groups_added_daily()) {
		return false;
	}
	if (!$this->backfill_groups_added_weekly()) {
		return false;
	}
	if (!$this->backfill_groups_added_monthly()) {
		return false;
	}
	if (!$this->backfill_groups_cum_daily()) {
		return false;
	}
	if (!$this->backfill_groups_cum_weekly()) {
		return false;
	}
	if (!$this->backfill_groups_cum_monthly()) {
		return false;
	}
	if (!$this->backfill_user_act_daily()) {
		return false;
	}
	if (!$this->backfill_user_act_weekly()) {
		return false;
	}
	if (!$this->backfill_user_act_monthly()) {
		return false;
	}
	if (!$this->backfill_group_act_daily()) {
		return false;
	}
	if (!$this->backfill_group_act_weekly()) {
		return false;
	}
	if (!$this->backfill_group_act_monthly()) {
		return false;
	}
	return true;

}

function dailyData() {
	if (!$this->backfill_users_added_daily(1)) {
		return false;
	}
	if (!$this->backfill_users_added_weekly(1)) {
		return false;
	}
	if (!$this->backfill_users_added_monthly(2)) {
		return false;
	}
	if (!$this->backfill_users_cum_daily(1)) {
		return false;
	}
	if (!$this->backfill_users_cum_weekly(1)) {
		return false;
	}
	if (!$this->backfill_users_cum_monthly(2)) {
		return false;
	}
	if (!$this->backfill_user_act_daily(1)) {
		return false;
	}
	if (!$this->backfill_user_act_weekly(1)) {
		return false;
	}
	if (!$this->backfill_user_act_monthly(2)) {
		return false;
	}
	if (!$this->backfill_group_act_daily(1)) {
		return false;
	}
	if (!$this->backfill_group_act_weekly(1)) {
		return false;
	}
	if (!$this->backfill_group_act_monthly(2)) {
		return false;
	}
	return true;
}
/**
 *	Add a row to the users_added_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function users_added_daily($day) {
	db_query_params ('DELETE FROM rep_users_added_daily WHERE day=$1',
			array($day)) ;


	return db_query_params ('INSERT INTO rep_users_added_daily (day,added)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN $3 AND $4 ))',
			array($day,
				'A',
				$day,
				($day + REPORT_DAY_SPAN - 1) ));
}

/**
 *	Populate the users_added_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_users_added_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_users_added_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=($today-($i*REPORT_DAY_SPAN));
		if (!$this->users_added_daily($day)) {
			$this->setError('backfill_users_added_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *	Add a row to the groups_added_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function groups_added_daily($day) {
	db_query_params ('DELETE FROM rep_groups_added_daily WHERE day=$1',
			array($day)) ;


	return db_query_params ('INSERT INTO rep_groups_added_daily (day,added)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN $3 AND $4 ))',
			array($day,
				'A',
				$day,
				($day + REPORT_DAY_SPAN - 1) ));
}

/**
 *	Populate the groups_added_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_groups_added_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_groups_added_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=($today-($i*REPORT_DAY_SPAN));
		if (!$this->groups_added_daily($day)) {
			$this->setError('backfill_groups_added_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the users_added_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function users_added_weekly($week) {
	db_query_params ('DELETE FROM rep_users_added_weekly WHERE week=$1',
			array($week)) ;


	return db_query_params ('INSERT INTO rep_users_added_weekly (week,added)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN $3 AND $4 ))',
			array($week,
				'A',
				$week,
				($week+REPORT_WEEK_SPAN-1) ));
}

/**
 *  Populate the users_added_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_users_added_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->users_added_weekly($arr[$i])) {
			$this->setError('backfill_users_added_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the groups_added_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function groups_added_weekly($week) {
	db_query_params ('DELETE FROM rep_groups_added_weekly WHERE week=$1',
			array($week)) ;


	return db_query_params ('INSERT INTO rep_groups_added_weekly (week,added)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN $3 AND $4 ))',
			array($week,
				'A',
				$week,
				($week+REPORT_WEEK_SPAN-1) ));
}

/**
 *  Populate the users_added_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_groups_added_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->groups_added_weekly($arr[$i])) {
			$this->setError('backfill_groups_added_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the users_added_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function users_added_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_users_added_monthly WHERE month=$1',
			array($month)) ;


	return db_query_params ('INSERT INTO rep_users_added_monthly (month,added)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN $3 AND $4 ))',
			array($month,
				'A',
				$month,
				$end));
}

/**
 *  Populate the users_added_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_users_added_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

//skipping first one
	for ($i=1; $i<count($arr); $i++) {
		if (!$this->users_added_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_users_added_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the groups_added_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function groups_added_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_groups_added_monthly WHERE month=$1',
			array($month)) ;


	return db_query_params ('INSERT INTO rep_groups_added_monthly (month,added)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN $3 AND $4 ))',
			array($month,
				'A',
				$month,
				$end));
}

/**
 *  Populate the groups_added_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_groups_added_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

//skipping first one
	for ($i=1; $i<count($arr); $i++) {
		if (!$this->groups_added_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_groups_added_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}


// ******************************


/**
 *	Add a row to the users_cum_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function users_cum_daily($day) {
	db_query_params ('DELETE FROM rep_users_cum_daily WHERE day=$1',
			array($day)) ;


	return db_query_params ('INSERT INTO rep_users_cum_daily (day,total)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN 0 AND $3))',
				array($day,
				      'A',
				      $day));
}

/**
 *	Populate the users_cum_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_users_cum_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_users_cum_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=$today-($i*REPORT_DAY_SPAN);
		if (!$this->users_cum_daily($day)) {
			$this->setError('backfill_users_cum_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *	Add a row to the groups_cum_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function groups_cum_daily($day) {
	db_query_params ('DELETE FROM rep_groups_cum_daily WHERE day=$1',
			array($day)) ;


	return db_query_params ('INSERT INTO rep_groups_cum_daily (day,total)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN 0 AND $3))',
				array($day,
				      'A',
				      $day));
}

/**
 *	Populate the groups_cum_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_groups_cum_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_groups_cum_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=$today-($i*REPORT_DAY_SPAN);
		if (!$this->groups_cum_daily($day)) {
			$this->setError('backfill_groups_cum_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the users_cum_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function users_cum_weekly($week) {
	db_query_params ('DELETE FROM rep_users_cum_weekly WHERE week=$1',
			array($week)) ;


	return db_query_params ('INSERT INTO rep_users_cum_weekly (week,total)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN $3 AND $4))',
			array($week,
				'A',
				0,
				($week+REPORT_WEEK_SPAN-1 )));
}

/**
 *  Populate the users_cum_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_users_cum_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->groups_cum_weekly($arr[$i])) {
			$this->setError('backfill_users_cum_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the groups_cum_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function groups_cum_weekly($week) {
	db_query_params ('DELETE FROM rep_groups_cum_weekly WHERE week=$1',
			array($week)) ;


	return db_query_params ('INSERT INTO rep_groups_cum_weekly (week,total)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN $3 AND $4))',
			array($week,
				'A',
				0,
				($week+REPORT_WEEK_SPAN-1 )));
}

/**
 *  Populate the groups_cum_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_groups_cum_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->users_cum_weekly($arr[$i])) {
			$this->setError('backfill_groups_cum_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the users_cum_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function users_cum_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_users_cum_monthly WHERE month=$1',
			array($month)) ;


	return db_query_params ('INSERT INTO rep_users_cum_monthly (month,total)
		VALUES ($1,(SELECT count(*) FROM users WHERE status=$2 AND add_date
		BETWEEN 0 AND $3))',
				array($month,
				      'A',
				      $end));
}

/**
 *  Populate the users_cum_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_users_cum_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

//skip first one
	for ($i=1; $i<count($arr); $i++) {
		if (!$this->users_cum_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_users_cum_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the groups_cum_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function groups_cum_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_groups_cum_monthly WHERE month=$1',
			array($month)) ;


	return db_query_params ('INSERT INTO rep_groups_cum_monthly (month,total)
		VALUES ($1,(SELECT count(*) FROM groups WHERE status=$2 AND register_time
		BETWEEN 0 AND $3))',
				array($month,
				      'A',
				      $end));
}

/**
 *  Populate the groups_cum_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_groups_cum_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

//skip first one
	for ($i=1; $i<count($arr); $i++) {
		if (!$this->groups_cum_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_groups_cum_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}


// ************************


/**
 *	Add a row to the user_act_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function user_act_daily($day) {
	db_query_params ('DELETE FROM rep_user_act_daily WHERE day=$1',
			array($day)) ;

	$end_day=$day+REPORT_DAY_SPAN-1;

	return db_query_params ('INSERT INTO rep_user_act_daily
		SELECT user_id,day,coalesce(tracker_opened,0) AS tracker_opened,
			coalesce(tracker_closed,0) AS tracker_closed,
			coalesce(forum,0) AS forum,
			coalesce(docs,0) AS docs,
			coalesce(cvs_commits,0) AS cvs_commits,
			coalesce(tasks_opened,0) AS tasks_opened,
			coalesce(tasks_closed,0) AS tasks_closed
			FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
			(SELECT submitted_by AS user_id, $1::int AS day, count(*) AS tracker_opened
			FROM artifact
			WHERE open_date BETWEEN $1 AND $2
			GROUP BY user_id,day) aopen

		FULL OUTER JOIN
			(SELECT assigned_to AS user_id, $1::int AS day, count(*) AS tracker_closed
			FROM artifact
			WHERE close_date BETWEEN $1 AND $2
			GROUP BY user_id,day ) aclosed USING (user_id,day)) foo1

		FULL OUTER JOIN
			(SELECT posted_by AS user_id, $1::int AS day, count(*) AS forum
			FROM forum
			WHERE post_date BETWEEN $1 AND $2
			GROUP BY user_id,day ) forum USING (user_id,day)) foo2

		FULL OUTER JOIN
			(SELECT created_by AS user_id, $1::int AS day, count(*) AS docs
			FROM doc_data
			WHERE createdate BETWEEN $1 AND $2
			GROUP BY user_id,day ) docs USING (user_id,day)) foo3

		FULL OUTER JOIN
			(SELECT user_id, $1::int AS day, sum(commits) AS cvs_commits
			FROM stats_cvs_user
			WHERE month=$3 AND day=$2
			GROUP BY user_id,day ) cvs USING (user_id,day)) foo4

		FULL OUTER JOIN
			(SELECT created_by AS user_id, $1::int AS day, count(*) AS tasks_opened
			FROM project_task
			WHERE start_date BETWEEN $1 AND $2
			GROUP BY user_id,day ) topen USING (user_id,day)) foo5

		FULL OUTER JOIN
			(SELECT mod_by AS user_id, $1::int AS day, count(*) AS tasks_closed
			FROM project_history
			WHERE mod_date BETWEEN $1 AND $2
			AND old_value=$4 AND field_name=$5
			GROUP BY user_id,day ) tclosed USING (user_id,day)) foo6',
				array($day,
				      $end_day,
				      date ('Ym'),
				      1,
				      'status_id'));
}

/**
 *	Populate the user_act_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_user_act_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_user_act_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=$today-($i*REPORT_DAY_SPAN);
		if (!$this->user_act_daily($day)) {
			$this->setError('backfill_user_act_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the user_act_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function user_act_weekly($week) {
	db_query_params ('DELETE FROM rep_user_act_weekly WHERE week=$1',
			array($week)) ;

	return db_query_params ('
INSERT INTO rep_user_act_weekly (user_id, week, tracker_opened, tracker_closed,
       forum, docs, cvs_commits, tasks_opened, tasks_closed)
SELECT user_id,$1::int AS week, sum(tracker_opened) AS tracker_opened,
       sum(tracker_closed) AS tracker_closed, sum(forum) AS forum,
       sum(docs) AS docs, sum(cvs_commits) AS cvs_commits,
       sum(tasks_opened) AS tasks_opened, sum(tasks_closed) AS tasks_closed
FROM rep_user_act_daily
WHERE DAY BETWEEN $1 AND $2
GROUP BY user_id,week',
				array ($week,
				       $week+REPORT_WEEK_SPAN-1)) ;
}

/**
 *  Populate the user_act_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_user_act_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->user_act_weekly($arr[$i])) {
			$this->setError('backfill_user_act_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the user_act_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function user_act_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_user_act_monthly WHERE month=$1',
			array($month)) ;

	return db_query_params ('
INSERT INTO rep_user_act_monthly (user_id, month, tracker_opened,
       tracker_closed, forum, docs, cvs_commits, tasks_opened, tasks_closed)
SELECT user_id, $1::int AS month, sum(tracker_opened) AS tracker_opened,
       sum(tracker_closed) AS tracker_closed, sum(forum) AS forum,
       sum(docs) AS docs, sum(cvs_commits) AS cvs_commits,
       sum(tasks_opened) AS tasks_opened, sum(tasks_closed) AS tasks_closed
FROM rep_user_act_daily
WHERE DAY BETWEEN $1 AND $2
GROUP BY user_id, month',
				array ($month, $end)) ;
}

/**
 *  Populate the user_act_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_user_act_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

	for ($i=1; $i<count($arr); $i++) {
		if (!$this->user_act_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_user_act_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

// ************************


/**
 *	Add a row to the group_act_daily report table.
 *
 *	@param	int	Day - the unix time of the beginning of the day.
 *	@return	boolean	Success.
 */
function group_act_daily($day) {
	db_query_params ('DELETE FROM rep_group_act_daily WHERE day=$1',
			array($day)) ;

	$end_day=$day+REPORT_DAY_SPAN-1;

	return db_query_params ('INSERT INTO rep_group_act_daily
		SELECT group_id,day,coalesce(tracker_opened,0) AS tracker_opened,
			coalesce(tracker_closed,0) AS tracker_closed,
			coalesce(forum,0) AS forum,
			coalesce(docs,0) AS docs,
			coalesce(downloads,0) AS downloads,
			coalesce(cvs_commits,0) AS cvs_commits,
			coalesce(tasks_opened,0) AS tasks_opened,
			coalesce(tasks_closed,0) AS tasks_closed
			FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
		(SELECT * FROM
			(SELECT agl.group_id, $1::int AS day, count(*) AS tracker_opened
			FROM artifact a, artifact_group_list agl
			WHERE a.open_date BETWEEN $1 AND $2
			AND a.group_artifact_id=agl.group_artifact_id
			GROUP BY group_id,day) aopen

		FULL OUTER JOIN
			(SELECT agl.group_id, $1::int AS day, count(*) AS tracker_closed
			FROM artifact a, artifact_group_list agl
			WHERE a.close_date BETWEEN $1 AND $2
			AND a.group_artifact_id=agl.group_artifact_id
			GROUP BY group_id,day ) aclosed USING (group_id,day)) foo1

		FULL OUTER JOIN
			(SELECT fgl.group_id, $1::int AS day, count(*) AS forum
			FROM forum f, forum_group_list fgl
			WHERE f.post_date BETWEEN $1 AND $2
			AND f.group_forum_id=fgl.group_forum_id
			GROUP BY group_id,day ) forum USING (group_id,day)) foo2

		FULL OUTER JOIN
			(SELECT group_id, $1::int AS day, count(*) AS docs
			FROM doc_data
			WHERE createdate BETWEEN $1 AND $2
			GROUP BY group_id,day ) docs USING (group_id,day)) foo3

		FULL OUTER JOIN
			(SELECT fp.group_id, $1::int AS day, count(*) AS downloads
			FROM frs_package fp, frs_release fr, frs_file ff, frs_dlstats_file fdf
			WHERE fp.package_id=fr.package_id
			AND fr.release_id=ff.release_id
			AND ff.file_id=fdf.file_id
			AND fdf.month = $3 AND fdf.day = $4
			GROUP BY fp.group_id,day ) docs USING (group_id,day)) foo4

		FULL OUTER JOIN
			(SELECT group_id, $1::int AS day, sum(commits) AS cvs_commits
			FROM stats_cvs_group
			WHERE month=$3 AND day=$4
			GROUP BY group_id,day ) cvs USING (group_id,day)) foo5

		FULL OUTER JOIN
			(SELECT pgl.group_id, $1::int AS day,count(*) AS tasks_opened
			FROM project_task pt, project_group_list pgl
			WHERE pt.start_date BETWEEN $1 AND $2
			AND pt.group_project_id=pgl.group_project_id
			GROUP BY group_id,day ) topen USING (group_id,day)) foo6

		FULL OUTER JOIN
			(SELECT pgl.group_id, $1::int AS day, count(*) AS tasks_closed
			FROM project_history ph, project_task pt, project_group_list pgl
			WHERE ph.mod_date BETWEEN $1 AND $2
			AND ph.old_value=$5
			AND ph.field_name=$6
			AND ph.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			GROUP BY group_id,day ) tclosed USING (group_id,day)) foo7',
				array($day,
				      $end_day,
				      date('Ym', $day),
				      date('d',$day),
				      1,
				      'status_id'));


}

/**
 *	Populate the group_act_daily report table.
 *
 *	@return	boolean	Success.
 */
function backfill_group_act_daily($count=10000) {
	$today=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
	if (!$start_date=$this->getMinDate()) {
		$this->setError('backfill_group_act_daily:: Could Not Get Start Date');
		return false;
	}
	$i = 0;
	while (true) {
		$day=$today-($i*REPORT_DAY_SPAN);
		if (!$this->group_act_daily($day)) {
			$this->setError('backfill_group_act_daily:: Error adding daily row: '.db_error());
			return false;
		}
		if ($day < $start_date) {
			break;
		}
		$i++;
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the group_act_weekly report table.
 *
 *  @param  int Week - the unix time of the beginning of the sunday for this week.
 *  @return boolean Success.
 */
function group_act_weekly($week) {
	db_query_params ('DELETE FROM rep_group_act_weekly WHERE week=$1',
			array($week)) ;

	return db_query_params ('
INSERT INTO rep_group_act_weekly (group_id, week, tracker_opened,
       tracker_closed, forum, docs, downloads, cvs_commits, tasks_opened,
       tasks_closed)
SELECT group_id, $1::int AS week, sum(tracker_opened) AS tracker_opened,
       sum(tracker_closed) AS tracker_closed, sum(forum) AS forum,
       sum(docs) AS docs, sum(downloads) AS downloads,
       sum(cvs_commits) AS cvs_commits, sum(tasks_opened) AS tasks_opened,
       sum(tasks_closed) AS tasks_closed
FROM rep_group_act_daily
WHERE DAY BETWEEN $1 AND $2
GROUP BY group_id, week',
				array ($week,
				       $week+REPORT_WEEK_SPAN-1)) ;
}

/**
 *  Populate the group_act_weekly report table.
 *
 *  @return boolean Success.
 */
function backfill_group_act_weekly($count=10000) {

	$arr =& $this->getWeekStartArr();

	for ($i=0; $i<count($arr); $i++) {
		if (!$this->group_act_weekly($arr[$i])) {
			$this->setError('backfill_user_act_weekly:: Error adding weekly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the group_act_monthly report table.
 *
 *  @param  int month_start - the unix time of the beginning of the month.
 *  @param  int month_end - the unix time of the end of the month.
 *  @return boolean Success.
 */
function group_act_monthly($month,$end) {
	db_query_params ('DELETE FROM rep_group_act_monthly WHERE month=$1',
			array($month)) ;

	return db_query_params ('
INSERT INTO rep_group_act_monthly (group_id, month, tracker_opened,
       tracker_closed, forum, docs, downloads, cvs_commits, tasks_opened,
       tasks_closed)
SELECT group_id, $1::int AS month, sum(tracker_opened) AS tracker_opened,
       sum(tracker_closed) AS tracker_closed, sum(forum) AS forum,
       sum(docs) AS docs, sum(downloads) AS downloads,
       sum(cvs_commits) AS cvs_commits,
       sum(tasks_opened) AS tasks_opened,
       sum(tasks_closed) AS tasks_closed
FROM rep_group_act_daily
WHERE DAY BETWEEN $1 AND $2
GROUP BY group_id,month',
				array ($month, $end)) ;
}

/**
 *  Populate the group_act_monthly report table.
 *
 *  @return boolean Success.
 */
function backfill_group_act_monthly($count=10000) {

	$arr =& $this->getMonthStartArr();

	for ($i=1; $i<count($arr); $i++) {
		if (!$this->group_act_monthly($arr[$i],($arr[$i-1]-1))) {
			$this->setError('backfill_group_act_monthly:: Error adding monthly row: '.db_error());
			return false;
		}
		if ($i >= $count) {
			break;
		}
	}
	return true;
}

/**
 *  Add a row to the rep_time_category table.
 *
 *	@param	string	The category name.
 *  @return boolean Success.
 */
function addTimeCode($category_name) {
	return db_query_params ('INSERT INTO rep_time_category (category_name) VALUES ($1)',
			array($category_name)) ;
}

/**
 *  Update the rep_time_category table.
 *
 *	@param	string	The category name.
 *  @return boolean Success.
 */
function updateTimeCode($time_code, $category_name) {
	return db_query_params ('UPDATE rep_time_category SET category_name=$1 WHERE time_code=$2',
			array($category_name,
			$time_code)) ;
}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>

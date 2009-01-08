--
-- Novaforge is a registered trade mark from Bull S.A.S
-- Copyright (C) 2007 Bull S.A.S.
-- 
-- http://novaforge.org/
--
--
-- This file has been developped within the Novaforge(TM) project from Bull S.A.S
-- and contributed back to GForge community.
--
-- GForge is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- GForge is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this file; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
--
--
-- This script creates the reporting tables
--
-- Source: function createTables() in common/reporting/ReportSetup.class
--

-- time tracking

CREATE TABLE rep_time_category (
   time_code serial UNIQUE,
   category_name text
);

CREATE TABLE rep_time_tracking (
   week int not null,
   report_date int not null,
   user_id int not null,
   project_task_id int not null,
   time_code int not null CONSTRAINT reptimetrk_timecode REFERENCES rep_time_category(time_code),
   hours float not null
);

CREATE INDEX reptimetracking_userdate ON rep_time_tracking (user_id,week);

INSERT INTO rep_time_category VALUES ('1','Coding');
INSERT INTO rep_time_category VALUES ('2','Testing');
INSERT INTO rep_time_category VALUES ('3','Meeting');
SELECT setval('rep_time_category_time_code_seq',(SELECT max(time_code) FROM rep_time_category));

-- added users

CREATE TABLE rep_users_added_daily (
   day int not null primary key,
   added int not null default 0
);

CREATE TABLE rep_users_added_weekly (
   week int not null primary key,
   added int not null default 0
);

CREATE TABLE rep_users_added_monthly (
   month int not null primary key,
   added int not null default 0
);

-- cumulative users

CREATE TABLE rep_users_cum_daily (
   day int not null primary key,
   total int not null default 0
);

CREATE TABLE rep_users_cum_weekly (
   week int not null primary key,
   total int not null default 0
);

CREATE TABLE rep_users_cum_monthly (
   month int not null primary key,
   total int not null default 0
);

-- added groups

CREATE TABLE rep_groups_added_daily (
   day int not null primary key,
   added int not null default 0
);

CREATE TABLE rep_groups_added_weekly (
   week int not null primary key,
   added int not null default 0
);

CREATE TABLE rep_groups_added_monthly (
   month int not null primary key,
   added int not null default 0
);

-- cumulative groups

CREATE TABLE rep_groups_cum_daily (
   day int not null primary key,
   total int not null default 0
);

CREATE TABLE rep_groups_cum_weekly (
   week int not null primary key,
   total int not null default 0
);

CREATE TABLE rep_groups_cum_monthly (
   month int not null primary key,
   total int not null default 0
);

-- per-user activity

CREATE TABLE rep_user_act_daily (
   user_id int not null,
   day int not null,
   tracker_opened int not null,
   tracker_closed int not null,
   forum int not null,
   docs int not null,
   cvs_commits int not null,
   tasks_opened int not null,
   tasks_closed int not null,
   PRIMARY KEY (user_id,day)
);

CREATE TABLE rep_user_act_weekly (
   user_id int not null,
   week int not null,
   tracker_opened int not null,
   tracker_closed int not null,
   forum int not null,
   docs int not null,
   cvs_commits int not null,
   tasks_opened int not null,
   tasks_closed int not null,
   PRIMARY KEY (user_id,week)
);

CREATE TABLE rep_user_act_monthly (
   user_id int not null,
   month int not null,
   tracker_opened int not null,
   tracker_closed int not null,
   forum int not null,
   docs int not null,
   cvs_commits int not null,
   tasks_opened int not null,
   tasks_closed int not null,
   PRIMARY KEY (user_id,month)
);

CREATE VIEW rep_user_act_oa_vw AS
   SELECT user_id,
   sum(tracker_opened) AS tracker_opened,
   sum(tracker_closed) AS tracker_closed,
   sum(forum) AS forum, 
   sum(docs) AS docs, 
   sum(cvs_commits) AS cvs_commits,
   sum(tasks_opened) AS tasks_opened,
   sum(tasks_closed) AS tasks_closed 
   FROM rep_user_act_monthly
   GROUP BY user_id;

-- per-project activity

CREATE TABLE rep_group_act_daily (
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
   PRIMARY KEY (group_id,day)
);

CREATE INDEX repgroupactdaily_day ON rep_group_act_daily(day);

CREATE TABLE rep_group_act_weekly (
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
   PRIMARY KEY (group_id,week)
);

CREATE INDEX repgroupactweekly_week ON rep_group_act_weekly(week);

CREATE TABLE rep_group_act_monthly (
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
   PRIMARY KEY (group_id,month)
);

CREATE INDEX repgroupactmonthly_month ON rep_group_act_monthly(month);

CREATE VIEW rep_group_act_oa_vw AS
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
   GROUP BY group_id;

-- overall activity

CREATE VIEW rep_site_act_daily_vw AS 
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
   GROUP BY day;

CREATE VIEW rep_site_act_weekly_vw AS 
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
   GROUP BY week;

CREATE VIEW rep_site_act_monthly_vw AS
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
   GROUP BY month;

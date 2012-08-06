-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 23, 2007 at 08:35 PM
-- Server version: 5.0.27
-- PHP Version: 5.1.6
--
-- Database: `gforge`
--
-- CREATE DATABASE `gforge` DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `canned_responses`
--

CREATE TABLE IF NOT EXISTS `canned_responses` (
  `response_id` int(11) NOT NULL auto_increment,
  `response_title` varchar(25) NOT NULL default '',
  `response_text` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`response_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `db_images`
--

CREATE TABLE IF NOT EXISTS `db_images` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `bin_data` mediumblob NOT NULL,
  `filename` varchar(25) NOT NULL default '',
  `filesize` int(11) NOT NULL default '0',
  `filetype` varchar(10) NOT NULL default '',
  `width` int(11) NOT NULL default '0',
  `height` int(11) NOT NULL default '0',
  `upload_date` int(11) default NULL,
  `version` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `db_images_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `doc_data`
--

CREATE TABLE IF NOT EXISTS `doc_data` (
  `docid` int(11) NOT NULL auto_increment,
  `stateid` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `data` varchar(255) NOT NULL default '',
  `updatedate` int(11) NOT NULL default '0',
  `createdate` int(11) NOT NULL default '0',
  `created_by` int(11) NOT NULL default '0',
  `doc_group` int(11) NOT NULL default '0',
  `description` varchar(255) default NULL,
  `language_id` int(11) NOT NULL default '1',
  `filename` varchar(25) default NULL,
  `filetype` varchar(10) default NULL,
  `group_id` int(11) default NULL,
  `filesize` int(11) NOT NULL default '0',
  PRIMARY KEY  (`docid`),
  KEY `docdata_groupid` (`group_id`,`doc_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `doc_groups`
--

CREATE TABLE IF NOT EXISTS `doc_groups` (
  `doc_group` int(11) NOT NULL auto_increment,
  `groupname` varchar(25) NOT NULL default '',
  `group_id` int(11) NOT NULL default '0',
  `parent_doc_group` int(11) NOT NULL default '0',
  PRIMARY KEY  (`doc_group`),
  KEY `doc_groups_group` (`group_id`),
  KEY `docgroups_parentdocgroup` (`parent_doc_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `doc_states`
--

CREATE TABLE IF NOT EXISTS `doc_states` (
  `stateid` int(11) NOT NULL auto_increment,
  `name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`stateid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `filemodule_monitor`
--

CREATE TABLE IF NOT EXISTS `filemodule_monitor` (
  `id` int(11) NOT NULL auto_increment,
  `filemodule_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `filemodulemonitor_useridfilemoduleid` (`user_id`,`filemodule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum`
--

CREATE TABLE IF NOT EXISTS `forum` (
  `msg_id` int(11) NOT NULL auto_increment,
  `group_forum_id` int(11) NOT NULL default '0',
  `posted_by` int(11) NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  `body` text NOT NULL,
  `post_date` int(11) NOT NULL default '0',
  `is_followup_to` tinyint(1) NOT NULL default '0',
  `thread_id` int(11) NOT NULL default '0',
  `has_followups` int(11) default '0',
  `most_recent_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`msg_id`),
  KEY `group_forum_id` (`group_forum_id`,`msg_id`),
  KEY `forum_group_forum_id` (`group_forum_id`),
  KEY `forum_forumid_threadid_mostrecent` (`group_forum_id`,`thread_id`,`most_recent_date`),
  KEY `forum_threadid_isfollowupto` (`thread_id`,`is_followup_to`),
  KEY `forum_forumid_isfollto_mostrecent` (`group_forum_id`,`is_followup_to`,`most_recent_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_agg_msg_count`
--

CREATE TABLE IF NOT EXISTS `forum_agg_msg_count` (
  `group_forum_id` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_group_list`
--

CREATE TABLE IF NOT EXISTS `forum_group_list` (
  `group_forum_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `forum_name` varchar(25) NOT NULL default '',
  `is_public` tinyint(1) NOT NULL default '0',
  `description` varchar(255) default NULL,
  `allow_anonymous` int(11) NOT NULL default '0',
  `send_all_posts_to` varchar(25) default NULL,
  `moderation_level` int(11) default '0',
  PRIMARY KEY  (`group_forum_id`),
  KEY `forum_group_list_group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_monitored_forums`
--

CREATE TABLE IF NOT EXISTS `forum_monitored_forums` (
  `monitor_id` int(11) NOT NULL auto_increment,
  `forum_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`monitor_id`),
  KEY `forummonitoredforums_useridforumid` (`user_id`,`forum_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_saved_place`
--

CREATE TABLE IF NOT EXISTS `forum_saved_place` (
  `saved_place_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `forum_id` int(11) NOT NULL default '0',
  `save_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`saved_place_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_file`
--

CREATE TABLE IF NOT EXISTS `frs_file` (
  `file_id` int(11) NOT NULL auto_increment,
  `filename` varchar(25) default NULL,
  `release_id` int(11) NOT NULL default '0',
  `type_id` int(11) NOT NULL default '0',
  `processor_id` int(11) NOT NULL default '0',
  `release_time` int(11) NOT NULL default '0',
  `file_size` int(11) NOT NULL default '0',
  `post_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`file_id`),
  KEY `frs_file_date` (`post_date`),
  KEY `frs_file_release_id` (`release_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_filetype`
--

CREATE TABLE IF NOT EXISTS `frs_filetype` (
  `type_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_package`
--

CREATE TABLE IF NOT EXISTS `frs_package` (
  `package_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `name` varchar(25) default NULL,
  `status_id` int(11) NOT NULL default '0',
  `is_public` tinyint(1) default '1',
  PRIMARY KEY  (`package_id`),
  KEY `package_group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_processor`
--

CREATE TABLE IF NOT EXISTS `frs_processor` (
  `processor_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`processor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_release`
--

CREATE TABLE IF NOT EXISTS `frs_release` (
  `release_id` int(11) NOT NULL auto_increment,
  `package_id` int(11) NOT NULL default '0',
  `name` varchar(25) default NULL,
  `notes` varchar(255) default NULL,
  `changes` varchar(255) default NULL,
  `status_id` int(11) NOT NULL default '0',
  `preformatted` int(11) NOT NULL default '0',
  `release_date` int(11) NOT NULL default '0',
  `released_by` int(11) NOT NULL default '0',
  PRIMARY KEY  (`release_id`),
  KEY `frs_release_package` (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_status`
--

CREATE TABLE IF NOT EXISTS `frs_status` (
  `status_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `group_history`
--

CREATE TABLE IF NOT EXISTS `group_history` (
  `group_history_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `field_name` varchar(25) NOT NULL default '',
  `old_value` varchar(100) NOT NULL default '',
  `mod_by` int(11) NOT NULL default '0',
  `adddate` int(11) default NULL,
  PRIMARY KEY  (`group_history_id`),
  KEY `group_history_group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `group_id` int(11) NOT NULL auto_increment,
  `group_name` varchar(40) default NULL,
  `homepage` varchar(128) default NULL,
  `is_public` tinyint(1) NOT NULL default '0',
  `status` char(1) NOT NULL default '',
  `unix_group_name` varchar(30) NOT NULL default '',
  `unix_box` varchar(20) NOT NULL default 'shell1',
  `http_domain` varchar(80) default NULL,
  `short_description` varchar(255) default NULL,
  `register_purpose` varchar(100) default NULL,
  `license_other` varchar(25) default NULL,
  `register_time` int(11) NOT NULL default '0',
  `rand_hash` varchar(32) default NULL,
  `use_mail` tinyint(1) NOT NULL default '1',
  `use_survey` tinyint(1) NOT NULL default '1',
  `use_forum` tinyint(1) NOT NULL default '1',
  `use_pm` tinyint(1) NOT NULL default '1',
  `use_scm` tinyint(1) NOT NULL default '1',
  `use_news` tinyint(1) NOT NULL default '1',
  `type_id` int(11) NOT NULL default '1',
  `use_docman` tinyint(1) NOT NULL default '1',
  `new_doc_address` varchar(100) NOT NULL default '',
  `send_all_docs` tinyint(1) NOT NULL default '0',
  `use_pm_depend_box` tinyint(1) NOT NULL default '1',
  `use_ftp` tinyint(1) default '1',
  `use_tracker` tinyint(1) default '1',
  `use_frs` tinyint(1) default '1',
  `use_stats` tinyint(1) default '1',
  `enable_pserver` tinyint(1) default '1',
  `enable_anonscm` tinyint(1) default '1',
  `license` int(11) default '100',
  `scm_box` varchar(80) default NULL,
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `group_unix_uniq` (`unix_group_name`),
  KEY `groups_type` (`type_id`),
  KEY `groups_public` (`is_public`),
  KEY `groups_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mail_group_list`
--

CREATE TABLE IF NOT EXISTS `mail_group_list` (
  `group_list_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `list_name` varchar(25) default NULL,
  `is_public` tinyint(1) NOT NULL default '0',
  `password` varchar(16) default NULL,
  `list_admin` int(11) NOT NULL default '0',
  `status` int(11) NOT NULL default '0',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`group_list_id`),
  KEY `mail_group_list_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `news_bytes`
--

CREATE TABLE IF NOT EXISTS `news_bytes` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `submitted_by` int(11) NOT NULL default '0',
  `is_approved` tinyint(1) NOT NULL default '0',
  `post_date` int(11) NOT NULL default '0',
  `forum_id` int(11) NOT NULL default '0',
  `summary` text,
  `details` text,
  PRIMARY KEY  (`id`),
  KEY `news_bytes_group` (`group_id`),
  KEY `news_bytes_approved` (`is_approved`),
  KEY `news_bytes_forum` (`forum_id`),
  KEY `news_group_date` (`group_id`,`post_date`),
  KEY `news_approved_date` (`is_approved`,`post_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_job`
--

CREATE TABLE IF NOT EXISTS `people_job` (
  `job_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `created_by` int(11) NOT NULL default '0',
  `title` varchar(25) default NULL,
  `description` varchar(255) default NULL,
  `post_date` int(11) NOT NULL default '0',
  `status_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`job_id`),
  KEY `people_job_group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_job_category`
--

CREATE TABLE IF NOT EXISTS `people_job_category` (
  `category_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  `private_flag` int(11) NOT NULL default '0',
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_job_inventory`
--

CREATE TABLE IF NOT EXISTS `people_job_inventory` (
  `job_inventory_id` int(11) NOT NULL auto_increment,
  `job_id` int(11) NOT NULL default '0',
  `skill_id` int(11) NOT NULL default '0',
  `skill_level_id` int(11) NOT NULL default '0',
  `skill_year_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`job_inventory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_job_status`
--

CREATE TABLE IF NOT EXISTS `people_job_status` (
  `status_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_skill`
--

CREATE TABLE IF NOT EXISTS `people_skill` (
  `skill_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`skill_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_skill_inventory`
--

CREATE TABLE IF NOT EXISTS `people_skill_inventory` (
  `skill_inventory_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `skill_id` int(11) NOT NULL default '0',
  `skill_level_id` int(11) NOT NULL default '0',
  `skill_year_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`skill_inventory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_skill_level`
--

CREATE TABLE IF NOT EXISTS `people_skill_level` (
  `skill_level_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`skill_level_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `people_skill_year`
--

CREATE TABLE IF NOT EXISTS `people_skill_year` (
  `skill_year_id` int(11) NOT NULL auto_increment,
  `name` varchar(25) default NULL,
  PRIMARY KEY  (`skill_year_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_assigned_to`
--

CREATE TABLE IF NOT EXISTS `project_assigned_to` (
  `project_assigned_id` int(11) NOT NULL auto_increment,
  `project_task_id` int(11) NOT NULL default '0',
  `assigned_to_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`project_assigned_id`),
  KEY `projectassigned_assignedtotaskid` (`assigned_to_id`,`project_task_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_dependencies`
--

CREATE TABLE IF NOT EXISTS `project_dependencies` (
  `project_depend_id` int(11) NOT NULL auto_increment,
  `project_task_id` int(11) NOT NULL default '0',
  `is_dependent_on_task_id` int(11) NOT NULL default '0',
  `link_type` char(2) default NULL,
  PRIMARY KEY  (`project_depend_id`),
  KEY `projectdep_isdepon_projtaskid` (`is_dependent_on_task_id`,`project_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_group_list`
--

CREATE TABLE IF NOT EXISTS `project_group_list` (
  `group_project_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `project_name` varchar(25) NOT NULL default '',
  `is_public` tinyint(1) NOT NULL default '0',
  `description` varchar(255) default NULL,
  `send_all_posts_to` varchar(25) default NULL,
  PRIMARY KEY  (`group_project_id`),
  KEY `project_group_list_group_id` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_history`
--

CREATE TABLE IF NOT EXISTS `project_history` (
  `project_history_id` int(11) NOT NULL auto_increment,
  `project_task_id` int(11) NOT NULL default '0',
  `field_name` varchar(25) NOT NULL default '',
  `old_value` varchar(25) NOT NULL default '',
  `mod_by` int(11) NOT NULL default '0',
  `mod_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`project_history_id`),
  KEY `project_history_task_id` (`project_task_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_metric`
--

CREATE TABLE IF NOT EXISTS `project_metric` (
  `ranking` int(11) NOT NULL auto_increment,
  `percentile` double default NULL,
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ranking`),
  KEY `project_metric_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_metric_tmp1`
--

CREATE TABLE IF NOT EXISTS `project_metric_tmp1` (
  `ranking` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `value` double default NULL,
  PRIMARY KEY  (`ranking`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_status`
--

CREATE TABLE IF NOT EXISTS `project_status` (
  `status_id` int(11) NOT NULL auto_increment,
  `status_name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_task`
--

CREATE TABLE IF NOT EXISTS `project_task` (
  `project_task_id` int(11) NOT NULL auto_increment,
  `group_project_id` int(11) NOT NULL default '0',
  `summary` text NOT NULL,
  `details` text NOT NULL,
  `percent_complete` int(11) NOT NULL default '0',
  `priority` int(11) NOT NULL default '3',
  `hours` double NOT NULL default '0',
  `start_date` int(11) NOT NULL default '0',
  `end_date` int(11) NOT NULL default '0',
  `created_by` int(11) NOT NULL default '0',
  `status_id` int(11) NOT NULL default '0',
  `category_id` int(11) default NULL,
  `duration` int(11) default '0',
  `parent_id` int(11) default '0',
  `last_modified_date` int(11) default NULL,
  PRIMARY KEY  (`project_task_id`),
  KEY `projecttask_projid_status` (`group_project_id`,`status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_weekly_metric`
--

CREATE TABLE IF NOT EXISTS `project_weekly_metric` (
  `ranking` int(11) NOT NULL auto_increment,
  `percentile` double default NULL,
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`ranking`),
  KEY `projectweeklymetric_ranking` (`ranking`),
  KEY `project_metric_weekly_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_session`
--

CREATE TABLE IF NOT EXISTS `user_session` (
  `user_id` int(11) NOT NULL default '0',
  `session_hash` char(32) NOT NULL default '',
  `ip_addr` char(15) NOT NULL default '',
  `time` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `session_user_id` (`user_id`),
  KEY `session_time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snippet`
--

CREATE TABLE IF NOT EXISTS `snippet` (
  `snippet_id` int(11) NOT NULL auto_increment,
  `created_by` int(11) NOT NULL default '0',
  `name` varchar(25) default NULL,
  `description` varchar(255) default NULL,
  `type` int(11) NOT NULL default '0',
  `language` int(11) NOT NULL default '0',
  `license` varchar(25) NOT NULL default '',
  `category` int(11) NOT NULL default '0',
  PRIMARY KEY  (`snippet_id`),
  KEY `snippet_language` (`language`),
  KEY `snippet_category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snippet_package`
--

CREATE TABLE IF NOT EXISTS `snippet_package` (
  `snippet_package_id` int(11) NOT NULL auto_increment,
  `created_by` int(11) NOT NULL default '0',
  `name` varchar(25) default NULL,
  `description` varchar(255) default NULL,
  `category` int(11) NOT NULL default '0',
  `language` int(11) NOT NULL default '0',
  PRIMARY KEY  (`snippet_package_id`),
  KEY `snippet_package_language` (`language`),
  KEY `snippet_package_category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snippet_package_item`
--

CREATE TABLE IF NOT EXISTS `snippet_package_item` (
  `snippet_package_item_id` int(11) NOT NULL auto_increment,
  `snippet_package_version_id` int(11) NOT NULL default '0',
  `snippet_version_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`snippet_package_item_id`),
  KEY `snippet_package_item_pkg_ver` (`snippet_package_version_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snippet_package_version`
--

CREATE TABLE IF NOT EXISTS `snippet_package_version` (
  `snippet_package_version_id` int(11) NOT NULL auto_increment,
  `snippet_package_id` int(11) NOT NULL default '0',
  `changes` varchar(255) default NULL,
  `version` varchar(25) default NULL,
  `submitted_by` int(11) NOT NULL default '0',
  `post_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`snippet_package_version_id`),
  KEY `snippet_package_version_pkg_id` (`snippet_package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snippet_version`
--

CREATE TABLE IF NOT EXISTS `snippet_version` (
  `snippet_version_id` int(11) NOT NULL auto_increment,
  `snippet_id` int(11) NOT NULL default '0',
  `changes` varchar(255) default NULL,
  `version` varchar(25) default NULL,
  `submitted_by` int(11) NOT NULL default '0',
  `post_date` int(11) NOT NULL default '0',
  `code` text,
  PRIMARY KEY  (`snippet_version_id`),
  KEY `snippet_version_snippet_id` (`snippet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_agg_logo_by_day`
--

CREATE TABLE IF NOT EXISTS `stats_agg_logo_by_day` (
  `day` int(11) default NULL,
  `count` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_agg_pages_by_day`
--

CREATE TABLE IF NOT EXISTS `stats_agg_pages_by_day` (
  `day` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  KEY `pages_by_day_day` (`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_site_pages_by_month`
--

CREATE TABLE IF NOT EXISTS `stats_site_pages_by_month` (
  `month` int(11) default NULL,
  `site_page_views` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_question_types`
--

CREATE TABLE IF NOT EXISTS `survey_question_types` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_questions`
--

CREATE TABLE IF NOT EXISTS `survey_questions` (
  `question_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `question` varchar(100) NOT NULL default '',
  `question_type` int(11) NOT NULL default '0',
  PRIMARY KEY  (`question_id`),
  KEY `survey_questions_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_rating_aggregate`
--

CREATE TABLE IF NOT EXISTS `survey_rating_aggregate` (
  `type` int(11) NOT NULL default '0',
  `id` int(11) NOT NULL default '0',
  `response` double NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  KEY `survey_rating_aggregate_type_id` (`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_rating_response`
--

CREATE TABLE IF NOT EXISTS `survey_rating_response` (
  `user_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `id` int(11) NOT NULL default '0',
  `response` int(11) NOT NULL default '0',
  `post_date` int(11) NOT NULL default '0',
  KEY `survey_rating_responses_user_ty` (`user_id`,`type`,`id`),
  KEY `survey_rating_responses_type_id` (`type`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `survey_responses`
--

CREATE TABLE IF NOT EXISTS `survey_responses` (
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `survey_id` int(11) NOT NULL default '0',
  `question_id` int(11) NOT NULL default '0',
  `response` varchar(100) NOT NULL default '',
  `post_date` int(11) NOT NULL default '0',
  KEY `survey_responses_group_id` (`group_id`),
  KEY `survey_responses_user_survey_qu` (`user_id`,`survey_id`,`question_id`),
  KEY `survey_responses_survey_questio` (`survey_id`,`question_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE IF NOT EXISTS `surveys` (
  `survey_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `survey_title` varchar(100) NOT NULL default '',
  `survey_questions` varchar(100) NOT NULL default '',
  `is_active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`survey_id`),
  KEY `surveys_group` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trove_cat`
--

CREATE TABLE IF NOT EXISTS `trove_cat` (
  `trove_cat_id` int(11) NOT NULL auto_increment,
  `version` int(11) NOT NULL default '0',
  `parent` int(11) NOT NULL default '0',
  `root_parent` int(11) NOT NULL default '0',
  `shortname` varchar(80) default NULL,
  `fullname` varchar(80) default NULL,
  `description` varchar(255) default NULL,
  `count_subcat` int(11) NOT NULL default '0',
  `count_subproj` int(11) NOT NULL default '0',
  `fullpath` text NOT NULL,
  `fullpath_ids` text,
  PRIMARY KEY  (`trove_cat_id`),
  KEY `trovecat_parentid` (`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trove_group_link`
--

CREATE TABLE IF NOT EXISTS `trove_group_link` (
  `trove_group_id` int(11) NOT NULL auto_increment,
  `trove_cat_id` int(11) NOT NULL default '0',
  `trove_cat_version` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `trove_cat_root` int(11) NOT NULL default '0',
  PRIMARY KEY  (`trove_group_id`),
  KEY `trovegrouplink_groupidcatid` (`group_id`,`trove_cat_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_bookmarks`
--

CREATE TABLE IF NOT EXISTS `user_bookmarks` (
  `bookmark_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `bookmark_url` varchar(100) default NULL,
  `bookmark_title` varchar(25) default NULL,
  PRIMARY KEY  (`bookmark_id`),
  KEY `user_bookmark_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_diary`
--

CREATE TABLE IF NOT EXISTS `user_diary` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `date_posted` int(11) NOT NULL default '0',
  `summary` text,
  `details` text,
  `is_public` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_diary_user_date` (`user_id`,`date_posted`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_diary_monitor`
--

CREATE TABLE IF NOT EXISTS `user_diary_monitor` (
  `monitor_id` int(11) NOT NULL auto_increment,
  `monitored_user` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`monitor_id`),
  KEY `userdiarymon_useridmonitoredid` (`user_id`,`monitored_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `user_group_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `admin_flags` char(16) NOT NULL default '',
  `forum_flags` int(11) NOT NULL default '0',
  `project_flags` int(11) NOT NULL default '2',
  `doc_flags` int(11) NOT NULL default '0',
  `cvs_flags` int(11) NOT NULL default '1',
  `member_role` int(11) NOT NULL default '100',
  `release_flags` int(11) NOT NULL default '0',
  `artifact_flags` int(11) default NULL,
  `role_id` int(11) default '1',
  PRIMARY KEY  (`user_group_id`),
  KEY `usergroup_useridgroupid` (`user_id`,`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_metric`
--

CREATE TABLE IF NOT EXISTS `user_metric` (
  `ranking` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `times_ranked` int(11) NOT NULL default '0',
  `avg_raters_importance` double NOT NULL default '0',
  `avg_rating` double NOT NULL default '0',
  `metric` double NOT NULL default '0',
  `percentile` double NOT NULL default '0',
  `importance_factor` double NOT NULL default '0',
  PRIMARY KEY  (`ranking`),
  UNIQUE KEY `usermetric_userid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_metric0`
--

CREATE TABLE IF NOT EXISTS `user_metric0` (
  `ranking` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `times_ranked` int(11) NOT NULL default '0',
  `avg_raters_importance` double NOT NULL default '0',
  `avg_rating` double NOT NULL default '0',
  `metric` double NOT NULL default '0',
  `percentile` double NOT NULL default '0',
  `importance_factor` double NOT NULL default '0',
  PRIMARY KEY  (`ranking`),
  KEY `user_metric0_user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `user_id` int(11) NOT NULL default '0',
  `preference_name` varchar(20) NOT NULL default '',
  `dead1` varchar(20) default NULL,
  `set_date` int(11) NOT NULL default '0',
  `preference_value` varchar(255) default NULL,
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE IF NOT EXISTS `user_ratings` (
  `rated_by` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `rate_field` int(11) NOT NULL default '0',
  `rating` int(11) NOT NULL default '0',
  KEY `user_ratings_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(25) NOT NULL default '',
  `email` text NOT NULL,
  `user_pw` varchar(32) NOT NULL default '',
  `realname` varchar(32) NOT NULL default '',
  `status` char(1) NOT NULL default 'A',
  `shell` varchar(20) NOT NULL default '/bin/bash',
  `unix_pw` varchar(40) NOT NULL default '',
  `unix_status` char(1) NOT NULL default 'N',
  `unix_uid` int(11) NOT NULL default '0',
  `unix_box` varchar(10) NOT NULL default 'shell1',
  `add_date` int(11) NOT NULL default '0',
  `confirm_hash` varchar(32) default NULL,
  `mail_siteupdates` int(11) NOT NULL default '0',
  `mail_va` int(11) NOT NULL default '0',
  `authorized_keys` varchar(100) default NULL,
  `email_new` varchar(25) default NULL,
  `people_view_skills` int(11) NOT NULL default '0',
  `people_resume` varchar(255) NOT NULL default '',
  `timezone` varchar(64) default 'GMT',
  `language` int(11) NOT NULL default '1',
  `block_ratings` int(11) default '0',
  `jabber_address` varchar(100) default NULL,
  `jabber_only` int(11) default NULL,
  `address` varchar(100) default NULL,
  `phone` varchar(25) default NULL,
  `fax` varchar(25) default NULL,
  `title` varchar(25) default NULL,
  `firstname` varchar(60) default NULL,
  `lastname` varchar(60) default NULL,
  `address2` varchar(100) default NULL,
  `ccode` char(2) default 'US',
  `theme_id` int(11) default NULL,
  `type_id` int(11) default '1',
  `unix_gid` int(11) default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `users_namename_uniq` (`user_name`),
  KEY `users_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_sums_agg`
--

CREATE TABLE IF NOT EXISTS `project_sums_agg` (
  `group_id` int(11) NOT NULL default '0',
  `type` char(4) NOT NULL default '',
  `count` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prdb_dbs`
--

CREATE TABLE IF NOT EXISTS `prdb_dbs` (
  `dbid` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `dbname` varchar(255) NOT NULL default '',
  `dbusername` varchar(25) NOT NULL default '',
  `dbuserpass` varchar(32) NOT NULL default '',
  `requestdate` int(11) NOT NULL default '0',
  `dbtype` int(11) NOT NULL default '0',
  `created_by` int(11) NOT NULL default '0',
  `state` int(11) NOT NULL default '0',
  PRIMARY KEY  (`dbid`),
  UNIQUE KEY `idx_prdb_dbname` (`dbname`),
  KEY `prdbdbs_groupid` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prdb_states`
--

CREATE TABLE IF NOT EXISTS `prdb_states` (
  `stateid` int(11) NOT NULL default '0',
  `statename` text,
  KEY `prdbstates_stateid` (`stateid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prdb_types`
--

CREATE TABLE IF NOT EXISTS `prdb_types` (
  `dbtypeid` int(11) NOT NULL default '0',
  `dbservername` varchar(25) NOT NULL default '',
  `dbsoftware` varchar(25) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `prweb_vhost`
--

CREATE TABLE IF NOT EXISTS `prweb_vhost` (
  `vhostid` int(11) NOT NULL auto_increment,
  `vhost_name` varchar(255) default NULL,
  `docdir` varchar(255) default NULL,
  `cgidir` varchar(255) default NULL,
  `group_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`vhostid`),
  UNIQUE KEY `idx_vhost_hostnames` (`vhost_name`),
  KEY `idx_vhost_groups` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_group_list`
--

CREATE TABLE IF NOT EXISTS `artifact_group_list` (
  `group_artifact_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `name` text,
  `description` text,
  `is_public` tinyint(1) NOT NULL default '0',
  `allow_anon` tinyint(1) NOT NULL default '0',
  `email_all_updates` tinyint(1) NOT NULL default '0',
  `email_address` text NOT NULL,
  `due_period` int(11) NOT NULL default '2592000',
  `submit_instructions` text,
  `browse_instructions` text,
  `datatype` int(11) NOT NULL default '0',
  `status_timeout` int(11) default NULL,
  `custom_status_field` int(11) NOT NULL default '0',
  `custom_renderer` text,
  PRIMARY KEY  (`group_artifact_id`),
  KEY `artgrouplist_groupid_public` (`group_id`,`is_public`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_perm`
--

CREATE TABLE IF NOT EXISTS `artifact_perm` (
  `id` int(11) NOT NULL auto_increment,
  `group_artifact_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `perm_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `artperm_groupartifactid_userid` (`group_artifact_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `artifactperm_user_vw` AS
    SELECT ap.id, ap.group_artifact_id, ap.user_id, ap.perm_level, users.user_name, users.realname
	FROM artifact_perm AS ap, users
	WHERE users.user_id = ap.user_id;


CREATE OR REPLACE VIEW `artifactperm_artgrouplist_vw` AS
    SELECT agl.group_artifact_id, agl.name, agl.description, agl.group_id, ap.user_id, ap.perm_level
	FROM artifact_perm AS ap, artifact_group_list AS agl
	WHERE ap.group_artifact_id = agl.group_artifact_id;


-- --------------------------------------------------------

--
-- Table structure for table `artifact_status`
--

CREATE TABLE IF NOT EXISTS `artifact_status` (
  `id` int(11) NOT NULL auto_increment,
  `status_name` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact`
--

CREATE TABLE IF NOT EXISTS `artifact` (
  `artifact_id` int(11) NOT NULL auto_increment,
  `group_artifact_id` int(11) NOT NULL default '0',
  `status_id` int(11) NOT NULL default '1',
  `priority` int(11) NOT NULL default '3',
  `submitted_by` int(11) NOT NULL default '100',
  `assigned_to` int(11) NOT NULL default '100',
  `open_date` int(11) NOT NULL default '0',
  `close_date` int(11) NOT NULL default '0',
  `summary` text NOT NULL,
  `details` text NOT NULL,
  `last_modified_date` int(11) default NULL,
  PRIMARY KEY  (`artifact_id`),
  KEY `art_groupartid` (`group_artifact_id`),
  KEY `art_groupartid_statusid` (`group_artifact_id`,`status_id`),
  KEY `art_groupartid_assign` (`group_artifact_id`,`assigned_to`),
  KEY `art_groupartid_submit` (`group_artifact_id`,`submitted_by`),
  KEY `art_submit_status` (`submitted_by`,`status_id`),
  KEY `art_assign_status` (`assigned_to`,`status_id`),
  KEY `art_groupartid_artifactid` (`group_artifact_id`,`artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_history`
--

CREATE TABLE IF NOT EXISTS `artifact_history` (
  `id` int(11) NOT NULL auto_increment,
  `artifact_id` int(11) NOT NULL default '0',
  `field_name` text NOT NULL,
  `old_value` text NOT NULL,
  `mod_by` int(11) NOT NULL default '0',
  `entrydate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `arthistory_artid_entrydate` (`artifact_id`,`entrydate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `artifact_history_user_vw` AS
    SELECT ah.id, ah.artifact_id, ah.field_name, ah.old_value, ah.entrydate, users.user_name
	FROM artifact_history AS ah, users
	WHERE ah.mod_by = users.user_id;


-- --------------------------------------------------------

--
-- Table structure for table `artifact_file`
--

CREATE TABLE IF NOT EXISTS `artifact_file` (
  `id` int(11) NOT NULL auto_increment,
  `artifact_id` int(11) NOT NULL default '0',
  `description` text NOT NULL,
  `bin_data` mediumblob NOT NULL,
  `filename` text NOT NULL,
  `filesize` int(11) NOT NULL default '0',
  `filetype` text NOT NULL,
  `adddate` int(11) NOT NULL default '0',
  `submitted_by` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `artfile_artid_adddate` (`artifact_id`,`adddate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `artifact_file_user_vw` AS
    SELECT af.id, af.artifact_id, af.description, af.bin_data, af.filename, af.filesize, af.filetype, af.adddate, af.submitted_by, users.user_name, users.realname
	FROM artifact_file AS af, users
	WHERE af.submitted_by = users.user_id;


-- --------------------------------------------------------

--
-- Table structure for table `artifact_message`
--

CREATE TABLE IF NOT EXISTS `artifact_message` (
  `id` int(11) NOT NULL auto_increment,
  `artifact_id` int(11) NOT NULL default '0',
  `submitted_by` int(11) NOT NULL default '0',
  `from_email` text NOT NULL,
  `adddate` int(11) NOT NULL default '0',
  `body` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `artmessage_artid_adddate` (`artifact_id`,`adddate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `artifact_message_user_vw` AS
    SELECT am.id, am.artifact_id, am.from_email, am.body, am.adddate, users.user_id, users.email, users.user_name, users.realname
	FROM artifact_message AS am, users
	WHERE am.submitted_by = users.user_id;


-- --------------------------------------------------------

--
-- Table structure for table `artifact_monitor`
--

CREATE TABLE IF NOT EXISTS `artifact_monitor` (
  `id` int(11) NOT NULL auto_increment,
  `artifact_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `email` text,
  PRIMARY KEY  (`id`),
  KEY `artmonitor_useridartid` (`user_id`,`artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_canned_responses`
--

CREATE TABLE IF NOT EXISTS `artifact_canned_responses` (
  `id` int(11) NOT NULL auto_increment,
  `group_artifact_id` int(11) NOT NULL default '0',
  `title` text NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `artifactcannedresponses_groupid` (`group_artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_counts_agg`
--

CREATE TABLE IF NOT EXISTS `artifact_counts_agg` (
  `group_artifact_id` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `open_count` int(11) default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_site_pages_by_day`
--

CREATE TABLE IF NOT EXISTS `stats_site_pages_by_day` (
  `month` int(11) default NULL,
  `day` int(11) default NULL,
  `site_page_views` int(11) default NULL,
  KEY `statssitepagesbyday_month_day` (`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `massmail_queue`
--

CREATE TABLE IF NOT EXISTS `massmail_queue` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(8) NOT NULL default '',
  `subject` varchar(100) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  `queued_date` int(11) NOT NULL default '0',
  `last_userid` int(11) NOT NULL default '0',
  `failed_date` int(11) NOT NULL default '0',
  `finished_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_agg_site_by_group`
--

CREATE TABLE IF NOT EXISTS `stats_agg_site_by_group` (
  `month` int(11) default NULL,
  `day` int(11) default NULL,
  `group_id` int(11) default NULL,
  `count` int(11) default NULL,
  UNIQUE KEY `statssitebygroup_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_project_metric`
--

CREATE TABLE IF NOT EXISTS `stats_project_metric` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `ranking` int(11) NOT NULL default '0',
  `percentile` double NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  UNIQUE KEY `statsprojectmetric_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_agg_logo_by_group`
--

CREATE TABLE IF NOT EXISTS `stats_agg_logo_by_group` (
  `month` int(11) default NULL,
  `day` int(11) default NULL,
  `group_id` int(11) default NULL,
  `count` int(11) default NULL,
  UNIQUE KEY `statslogobygroup_month_day_grou` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_subd_pages`
--

CREATE TABLE IF NOT EXISTS `stats_subd_pages` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `pages` int(11) NOT NULL default '0',
  UNIQUE KEY `statssubdpages_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_cvs_user`
--

CREATE TABLE IF NOT EXISTS `stats_cvs_user` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `checkouts` int(11) NOT NULL default '0',
  `commits` int(11) NOT NULL default '0',
  `adds` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_cvs_group`
--

CREATE TABLE IF NOT EXISTS `stats_cvs_group` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `checkouts` int(11) NOT NULL default '0',
  `commits` int(11) NOT NULL default '0',
  `adds` int(11) NOT NULL default '0',
  UNIQUE KEY `statscvsgroup_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_project_developers`
--

CREATE TABLE IF NOT EXISTS `stats_project_developers` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `developers` int(11) NOT NULL default '0',
  UNIQUE KEY `statsprojectdev_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_project`
--

CREATE TABLE IF NOT EXISTS `stats_project` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `file_releases` int(11) default '0',
  `msg_posted` int(11) default '0',
  `msg_uniq_auth` int(11) default '0',
  `bugs_opened` int(11) default '0',
  `bugs_closed` int(11) default '0',
  `support_opened` int(11) default '0',
  `support_closed` int(11) default '0',
  `patches_opened` int(11) default '0',
  `patches_closed` int(11) default '0',
  `artifacts_opened` int(11) default '0',
  `artifacts_closed` int(11) default '0',
  `tasks_opened` int(11) default '0',
  `tasks_closed` int(11) default '0',
  `help_requests` int(11) default '0',
  UNIQUE KEY `statsproject_month_day_group` (`month`,`day`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_site`
--

CREATE TABLE IF NOT EXISTS `stats_site` (
  `month` int(11) default NULL,
  `day` int(11) default NULL,
  `uniq_users` int(11) default NULL,
  `sessions` int(11) default NULL,
  `total_users` int(11) default NULL,
  `new_users` int(11) default NULL,
  `new_projects` int(11) default NULL,
  UNIQUE KEY `statssite_month_day` (`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE IF NOT EXISTS `activity_log` (
  `day` int(11) NOT NULL default '0',
  `hour` int(11) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `browser` varchar(8) NOT NULL default 'OTHER',
  `ver` double NOT NULL default '0',
  `platform` varchar(8) NOT NULL default 'OTHER',
  `time` int(11) NOT NULL default '0',
  `page` text,
  `type` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_metric_history`
--

CREATE TABLE IF NOT EXISTS `user_metric_history` (
  `month` int(11) NOT NULL default '0',
  `day` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `ranking` int(11) NOT NULL default '0',
  `metric` double NOT NULL default '0',
  KEY `usermetrichistory_useridmonthday` (`user_id`,`month`,`day`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_dlstats_filetotal_agg`
--

CREATE TABLE IF NOT EXISTS `frs_dlstats_filetotal_agg` (
  `file_id` int(11) NOT NULL default '0',
  `downloads` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_project_months`
--

CREATE TABLE IF NOT EXISTS `stats_project_months` (
  `month` int(11) default NULL,
  `group_id` int(11) default NULL,
  `developers` int(11) default NULL,
  `group_ranking` int(11) default NULL,
  `group_metric` double default NULL,
  `logo_showings` int(11) default NULL,
  `downloads` int(11) default NULL,
  `site_views` int(11) default NULL,
  `subdomain_views` int(11) default NULL,
  `page_views` int(11) default NULL,
  `file_releases` int(11) default NULL,
  `msg_posted` int(11) default NULL,
  `msg_uniq_auth` int(11) default NULL,
  `bugs_opened` int(11) default NULL,
  `bugs_closed` int(11) default NULL,
  `support_opened` int(11) default NULL,
  `support_closed` int(11) default NULL,
  `patches_opened` int(11) default NULL,
  `patches_closed` int(11) default NULL,
  `artifacts_opened` int(11) default NULL,
  `artifacts_closed` int(11) default NULL,
  `tasks_opened` int(11) default NULL,
  `tasks_closed` int(11) default NULL,
  `help_requests` int(11) default NULL,
  `cvs_checkouts` int(11) default NULL,
  `cvs_commits` int(11) default NULL,
  `cvs_adds` int(11) default NULL,
  KEY `statsprojectmonths_groupid` (`group_id`),
  KEY `statsprojectmonths_groupid_mont` (`group_id`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stats_site_months`
--

CREATE TABLE IF NOT EXISTS `stats_site_months` (
  `month` int(11) default NULL,
  `site_page_views` int(11) default NULL,
  `downloads` int(11) default NULL,
  `subdomain_views` int(11) default NULL,
  `msg_posted` int(11) default NULL,
  `bugs_opened` int(11) default NULL,
  `bugs_closed` int(11) default NULL,
  `support_opened` int(11) default NULL,
  `support_closed` int(11) default NULL,
  `patches_opened` int(11) default NULL,
  `patches_closed` int(11) default NULL,
  `artifacts_opened` int(11) default NULL,
  `artifacts_closed` int(11) default NULL,
  `tasks_opened` int(11) default NULL,
  `tasks_closed` int(11) default NULL,
  `help_requests` int(11) default NULL,
  `cvs_checkouts` int(11) default NULL,
  `cvs_commits` int(11) default NULL,
  `cvs_adds` int(11) default NULL,
  KEY `statssitemonths_month` (`month`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trove_agg`
--

CREATE TABLE IF NOT EXISTS `trove_agg` (
  `trove_cat_id` int(11) default NULL,
  `group_id` int(11) default NULL,
  `group_name` varchar(40) default NULL,
  `unix_group_name` varchar(30) default NULL,
  `status` char(1) default NULL,
  `register_time` int(11) default NULL,
  `short_description` varchar(255) default NULL,
  `percentile` double default NULL,
  `ranking` int(11) default NULL,
  KEY `troveagg_trovecatid_ranking` (`trove_cat_id`,`ranking`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `trove_treesums`
--

CREATE TABLE IF NOT EXISTS `trove_treesums` (
  `trove_treesums_id` int(11) NOT NULL auto_increment,
  `trove_cat_id` int(11) NOT NULL default '0',
  `limit_1` int(11) NOT NULL default '0',
  `subprojects` int(11) NOT NULL default '0',
  PRIMARY KEY  (`trove_treesums_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `frs_dlstats_file`
--

CREATE TABLE IF NOT EXISTS `frs_dlstats_file` (
  `ip_address` varchar(25) default NULL,
  `file_id` int(11) default NULL,
  `month` int(11) default NULL,
  `day` int(11) default NULL,
  `user_id` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `group_cvs_history`
--

CREATE TABLE IF NOT EXISTS `group_cvs_history` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `user_name` varchar(80) NOT NULL default '',
  `cvs_commits` int(11) NOT NULL default '0',
  `cvs_commits_wk` int(11) NOT NULL default '0',
  `cvs_adds` int(11) NOT NULL default '0',
  `cvs_adds_wk` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `groupcvshistory_groupid` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `theme_id` int(11) NOT NULL auto_increment,
  `dirname` varchar(80) default NULL,
  `fullname` varchar(80) default NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`theme_id`),
  UNIQUE KEY `themes_theme_id_key` (`theme_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `supported_languages`
--

CREATE TABLE IF NOT EXISTS `supported_languages` (
  `language_id` int(11) NOT NULL auto_increment,
  `name` text,
  `filename` varchar(25) default NULL,
  `classname` varchar(25) default NULL,
  `language_code` varchar(5) default NULL,
  PRIMARY KEY  (`language_id`),
  UNIQUE KEY `supportedlanguage_code` (`language_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `skills_data_types`
--

CREATE TABLE IF NOT EXISTS `skills_data_types` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(25) NOT NULL default '',
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `skills_data`
--

CREATE TABLE IF NOT EXISTS `skills_data` (
  `skills_data_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `type` int(11) NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `start` int(11) NOT NULL default '0',
  `finish` int(11) NOT NULL default '0',
  `keywords` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`skills_data_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `frs_file_vw` AS
    SELECT frs_file.file_id, frs_file.filename, frs_file.release_id, frs_file.type_id, frs_file.processor_id, frs_file.release_time, frs_file.file_size, frs_file.post_date, frs_filetype.name AS filetype, frs_processor.name AS processor, frs_dlstats_filetotal_agg.downloads
	FROM frs_filetype, frs_processor, (frs_file LEFT JOIN frs_dlstats_filetotal_agg ON frs_dlstats_filetotal_agg.file_id = frs_file.file_id)
	WHERE frs_filetype.type_id = frs_file.type_id AND frs_processor.processor_id = frs_file.processor_id;


-- --------------------------------------------------------

--
-- Table structure for table `project_category`
--

CREATE TABLE IF NOT EXISTS `project_category` (
  `category_id` int(11) NOT NULL auto_increment,
  `group_project_id` int(11) default NULL,
  `category_name` varchar(25) default NULL,
  PRIMARY KEY  (`category_id`),
  UNIQUE KEY `project_categor_category_id_key` (`category_id`),
  KEY `projectcategory_groupprojectid` (`group_project_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_task_artifact`
--

CREATE TABLE IF NOT EXISTS `project_task_artifact` (
  `project_task_id` int(11) NOT NULL default '0',
  `artifact_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`project_task_id`),
  KEY `projecttaskartifact_artidprojtaskid` (`artifact_id`,`project_task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `project_history_user_vw` AS
    SELECT users.realname, users.email, users.user_name, project_history.project_history_id, project_history.project_task_id, project_history.field_name, project_history.old_value, project_history.mod_by, project_history.mod_date
	FROM users, project_history
	WHERE project_history.mod_by = users.user_id;


-- --------------------------------------------------------

--
-- Table structure for table `project_messages`
--

CREATE TABLE IF NOT EXISTS `project_messages` (
  `project_message_id` int(11) NOT NULL auto_increment,
  `project_task_id` int(11) NOT NULL default '0',
  `body` varchar(255) default NULL,
  `posted_by` int(11) NOT NULL default '0',
  `postdate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`project_message_id`),
  UNIQUE KEY `project_messa_project_messa_key` (`project_message_id`),
  KEY `projectmsgs_projtaskidpostdate` (`project_task_id`,`postdate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `project_message_user_vw` AS
    SELECT users.realname, users.email, users.user_name, project_messages.project_message_id, project_messages.project_task_id, project_messages.body, project_messages.posted_by, project_messages.postdate
	FROM users, project_messages
	WHERE project_messages.posted_by = users.user_id;


CREATE OR REPLACE VIEW `frs_dlstats_file_agg_vw` AS
    SELECT frs_dlstats_file.`month`, frs_dlstats_file.`day`, frs_dlstats_file.file_id, count(*) AS downloads
	FROM frs_dlstats_file
	GROUP BY frs_dlstats_file.`month`, frs_dlstats_file.`day`, frs_dlstats_file.file_id;


CREATE OR REPLACE VIEW `frs_dlstats_grouptotal_vw` AS
    SELECT frs_package.group_id, sum(frs_dlstats_filetotal_agg.downloads) AS downloads
	FROM frs_package, frs_release, frs_file, frs_dlstats_filetotal_agg
	WHERE frs_package.package_id = frs_release.package_id AND frs_release.release_id = frs_file.release_id AND frs_file.file_id = frs_dlstats_filetotal_agg.file_id
	GROUP BY frs_package.group_id;


CREATE OR REPLACE VIEW `frs_dlstats_group_vw` AS
    SELECT frs_package.group_id, fdfa.`month`, fdfa.`day`, sum(fdfa.downloads) AS downloads
	FROM frs_package, frs_release, frs_file, frs_dlstats_file_agg_vw AS fdfa
	WHERE frs_package.package_id = frs_release.package_id AND frs_release.release_id = frs_file.release_id AND frs_file.file_id = fdfa.file_id
	GROUP BY frs_package.group_id, fdfa.`month`, fdfa.`day`;


CREATE OR REPLACE VIEW `stats_project_vw` AS
    SELECT spd.group_id, spd.`month`, spd.`day`, spd.developers, spm.ranking AS group_ranking, spm.percentile AS group_metric, salbg.count AS logo_showings, fdga.downloads, sasbg.count AS site_views, ssp.pages AS subdomain_views, (CASE WHEN (sasbg.count IS NOT NULL) THEN sasbg.count WHEN (0 IS NOT NULL) THEN 0 ELSE NULL END + CASE WHEN (ssp.pages IS NOT NULL) THEN ssp.pages WHEN (0 IS NOT NULL) THEN 0 ELSE NULL END) AS page_views, sp.file_releases, sp.msg_posted, sp.msg_uniq_auth, sp.bugs_opened, sp.bugs_closed, sp.support_opened, sp.support_closed, sp.patches_opened, sp.patches_closed, sp.artifacts_opened, sp.artifacts_closed, sp.tasks_opened, sp.tasks_closed, sp.help_requests, scg.checkouts AS cvs_checkouts, scg.commits AS cvs_commits, scg.adds AS cvs_adds
	FROM (((((((stats_project_developers AS spd
		LEFT JOIN stats_project AS sp USING (`month`, `day`, group_id))
		LEFT JOIN stats_project_metric AS spm USING (`month`, `day`, group_id))
		LEFT JOIN stats_cvs_group AS scg USING (`month`, `day`, group_id))
		LEFT JOIN stats_agg_site_by_group AS sasbg USING (`month`, `day`, group_id))
		LEFT JOIN stats_agg_logo_by_group AS salbg USING (`month`, `day`, group_id))
		LEFT JOIN stats_subd_pages AS ssp USING (`month`, `day`, group_id))
		LEFT JOIN frs_dlstats_group_vw AS fdga USING (`month`, `day`, group_id));


CREATE OR REPLACE VIEW `stats_project_all_vw` AS
    SELECT
		stats_project_months.group_id,
		avg(stats_project_months.developers) AS developers,
		avg(stats_project_months.group_ranking) AS group_ranking,
		avg(stats_project_months.group_metric) AS group_metric,
		sum(stats_project_months.logo_showings) AS logo_showings,
		sum(stats_project_months.downloads) AS downloads,
		sum(stats_project_months.site_views) AS site_views,
		sum(stats_project_months.subdomain_views) AS subdomain_views,
		sum(stats_project_months.page_views) AS page_views,
		sum(stats_project_months.file_releases) AS file_releases,
		sum(stats_project_months.msg_posted) AS msg_posted,
		avg(stats_project_months.msg_uniq_auth) AS msg_uniq_auth,
		sum(stats_project_months.bugs_opened) AS bugs_opened,
		sum(stats_project_months.bugs_closed) AS bugs_closed,
		sum(stats_project_months.support_opened) AS support_opened,
		sum(stats_project_months.support_closed) AS support_closed,
		sum(stats_project_months.patches_opened) AS patches_opened,
		sum(stats_project_months.patches_closed) AS patches_closed,
		sum(stats_project_months.artifacts_opened) AS artifacts_opened,
		sum(stats_project_months.artifacts_closed) AS artifacts_closed,
		sum(stats_project_months.tasks_opened) AS tasks_opened,
		sum(stats_project_months.tasks_closed) AS tasks_closed,
		sum(stats_project_months.help_requests) AS help_requests,
		sum(stats_project_months.cvs_checkouts) AS cvs_checkouts,
		sum(stats_project_months.cvs_commits) AS cvs_commits,
		sum(stats_project_months.cvs_adds) AS cvs_adds
	FROM stats_project_months
	GROUP BY stats_project_months.group_id;


CREATE OR REPLACE VIEW `stats_site_vw` AS
    SELECT p.`month`, p.`day`, sspbd.site_page_views, sum(p.downloads) AS downloads, sum(p.subdomain_views) AS subdomain_views, sum(p.msg_posted) AS msg_posted, sum(p.bugs_opened) AS bugs_opened, sum(p.bugs_closed) AS bugs_closed, sum(p.support_opened) AS support_opened, sum(p.support_closed) AS support_closed, sum(p.patches_opened) AS patches_opened, sum(p.patches_closed) AS patches_closed, sum(p.artifacts_opened) AS artifacts_opened, sum(p.artifacts_closed) AS artifacts_closed, sum(p.tasks_opened) AS tasks_opened, sum(p.tasks_closed) AS tasks_closed, sum(p.help_requests) AS help_requests, sum(p.cvs_checkouts) AS cvs_checkouts, sum(p.cvs_commits) AS cvs_commits, sum(p.cvs_adds) AS cvs_adds
	FROM stats_project_vw AS p, stats_site_pages_by_day AS sspbd
	WHERE p.`month` = sspbd.`month` AND p.`day` = sspbd.`day`
	GROUP BY p.`month`, p.`day`, sspbd.site_page_views;


CREATE OR REPLACE VIEW `stats_site_all_vw` AS
    SELECT sum(stats_site_months.site_page_views) AS site_page_views, sum(stats_site_months.downloads) AS downloads, sum(stats_site_months.subdomain_views) AS subdomain_views, sum(stats_site_months.msg_posted) AS msg_posted, sum(stats_site_months.bugs_opened) AS bugs_opened, sum(stats_site_months.bugs_closed) AS bugs_closed, sum(stats_site_months.support_opened) AS support_opened, sum(stats_site_months.support_closed) AS support_closed, sum(stats_site_months.patches_opened) AS patches_opened, sum(stats_site_months.patches_closed) AS patches_closed, sum(stats_site_months.artifacts_opened) AS artifacts_opened, sum(stats_site_months.artifacts_closed) AS artifacts_closed, sum(stats_site_months.tasks_opened) AS tasks_opened, sum(stats_site_months.tasks_closed) AS tasks_closed, sum(stats_site_months.help_requests) AS help_requests, sum(stats_site_months.cvs_checkouts) AS cvs_checkouts, sum(stats_site_months.cvs_commits) AS cvs_commits, sum(stats_site_months.cvs_adds) AS cvs_adds
	FROM stats_site_months;


-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `plugin_id` int(11) NOT NULL auto_increment,
  `plugin_name` varchar(32) NOT NULL default '',
  `plugin_desc` varchar(255) default NULL,
  PRIMARY KEY  (`plugin_id`),
  UNIQUE KEY `plugins_plugin_name_key` (`plugin_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `group_plugin`
--

CREATE TABLE IF NOT EXISTS `group_plugin` (
  `group_plugin_id` int(11) NOT NULL auto_increment,
  `group_id` int(11) default NULL,
  `plugin_id` int(11) default NULL,
  PRIMARY KEY  (`group_plugin_id`),
  KEY `groupplugin_groupid` (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_plugin`
--

CREATE TABLE IF NOT EXISTS `user_plugin` (
  `user_plugin_id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `plugin_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`user_plugin_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cron_history`
--

CREATE TABLE IF NOT EXISTS `cron_history` (
  `rundate` int(11) NOT NULL default '0',
  `job` varchar(255) default NULL,
  `output` text default NULL,
  KEY `cronhist_rundate` (`rundate`),
  KEY `cronhist_jobrundate` (`job`,`rundate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


-- --------------------------------------------------------

--
-- Table structure for table `country_code`
--

CREATE TABLE IF NOT EXISTS `country_code` (
  `country_name` varchar(80) default NULL,
  `ccode` char(2) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE IF NOT EXISTS `licenses` (
  `license_id` varchar(10) NOT NULL default '',
  `license_name` varchar(100) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_type`
--

CREATE TABLE IF NOT EXISTS `user_type` (
  `type_id` int(11) NOT NULL auto_increment,
  `type_name` varchar(25) default NULL,
  PRIMARY KEY  (`type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(10) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0',
  `role_name` varchar(25) default NULL,
  PRIMARY KEY  (`role_id`),
  UNIQUE KEY `role_groupidroleid` (`group_id`,`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_perm`
--

CREATE TABLE IF NOT EXISTS `project_perm` (
  `id` varchar(10) NOT NULL default '',
  `group_project_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `perm_level` int(11) NOT NULL default '0',
  KEY `projectperm_useridgroupprojid` (`user_id`,`group_project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `forum_perm`
--

CREATE TABLE IF NOT EXISTS `forum_perm` (
  `id` int(11) NOT NULL auto_increment,
  `group_forum_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `perm_level` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `role_setting`
--

CREATE TABLE IF NOT EXISTS `role_setting` (
  `role_id` int(11) NOT NULL default '0',
  `section_name` varchar(25) NOT NULL default '',
  `ref_id` int(11) NOT NULL default '0',
  `value` char(2) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_extra_field_list`
--

CREATE TABLE IF NOT EXISTS `artifact_extra_field_list` (
  `extra_field_id` int(11) NOT NULL auto_increment,
  `group_artifact_id` int(11) NOT NULL default '0',
  `field_name` varchar(255) NOT NULL,
  `field_type` int(11) default '1',
  `attribute1` int(11) default '0',
  `attribute2` int(11) default '0',
  `is_required` tinyint(1) NOT NULL default '0',
  `alias` text,
  PRIMARY KEY  (`extra_field_id`),
  KEY `artifactextrafieldlist_groupartid` (`group_artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_extra_field_elements`
--

CREATE TABLE IF NOT EXISTS `artifact_extra_field_elements` (
  `element_id` int(11) NOT NULL auto_increment,
  `extra_field_id` int(11) NOT NULL default '0',
  `element_name` text NOT NULL,
  `status_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`element_id`),
  KEY `artifactextrafldlmts_extrafieldid` (`extra_field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_extra_field_data`
--

CREATE TABLE IF NOT EXISTS `artifact_extra_field_data` (
  `data_id` int(11) NOT NULL auto_increment,
  `artifact_id` int(11) NOT NULL default '0',
  `field_data` text,
  `extra_field_id` int(11) default '0',
  PRIMARY KEY  (`data_id`),
  KEY `artifactextrafielddata_artifactid` (`artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `project_counts_agg`
--

CREATE TABLE IF NOT EXISTS `project_counts_agg` (
  `group_project_id` int(11) NOT NULL default '0',
  `count` int(11) NOT NULL default '0',
  `open_count` int(11) default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `project_group_list_vw` AS
    SELECT project_group_list.group_project_id, group_id, project_name, is_public, description, send_all_posts_to, `count`, open_count
	FROM (project_group_list
	LEFT JOIN project_counts_agg ON project_counts_agg.group_project_id = project_group_list.group_project_id);


-- --------------------------------------------------------

--
-- Table structure for table `project_task_external_order`
--

CREATE TABLE IF NOT EXISTS `project_task_external_order` (
  `project_task_id` int(11) NOT NULL default '0',
  `external_id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `project_depend_vw` AS
    SELECT pt.project_task_id, pd.is_dependent_on_task_id, pd.link_type, pt.end_date, pt.start_date
	FROM (project_task pt NATURAL JOIN project_dependencies pd);


CREATE OR REPLACE VIEW `project_dependon_vw` AS
    SELECT pd.project_task_id, pd.is_dependent_on_task_id, pd.link_type, pt.end_date, pt.start_date
	FROM (project_task AS pt
		LEFT JOIN project_dependencies AS pd ON pd.is_dependent_on_task_id = pt.project_task_id)
	UNION
    SELECT pd.project_task_id, pd.is_dependent_on_task_id, pd.link_type, pt.end_date, pt.start_date
	FROM (project_task AS pt
		RIGHT JOIN project_dependencies AS pd ON pd.is_dependent_on_task_id = pt.project_task_id);


-- --------------------------------------------------------

--
-- Table structure for table `group_join_request`
--

CREATE TABLE IF NOT EXISTS `group_join_request` (
  `group_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `comments` varchar(255) default NULL,
  `request_date` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `project_task_vw` AS
    SELECT
		project_task.project_task_id,
		project_task.group_project_id,
		project_task.summary,
		project_task.details,
		project_task.percent_complete,
		project_task.priority,
		project_task.hours,
		project_task.start_date,
		project_task.end_date,
		project_task.created_by,
		project_task.status_id,
		project_task.category_id,
		project_task.duration,
		project_task.parent_id,
		project_task.last_modified_date,
		project_category.category_name,
		project_status.status_name,
		users.user_name,
		users.realname
	FROM ((project_task
		LEFT JOIN project_category ON project_category.category_id = project_task.category_id)
		LEFT JOIN users ON users.user_id = project_task.created_by)
		LEFT JOIN project_status ON project_status.status_id = project_task.status_id;


-- --------------------------------------------------------

--
-- Table structure for table `artifact_type_monitor`
--

CREATE TABLE IF NOT EXISTS `artifact_type_monitor` (
  `group_artifact_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_cvstracker_data_artifact`
--

CREATE TABLE IF NOT EXISTS `plugin_cvstracker_data_artifact` (
  `id` int(11) NOT NULL auto_increment,
  `kind` int(11) NOT NULL default '0',
  `group_artifact_id` int(11) default NULL,
  `project_task_id` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `plugin_cvstracker_group_artifact_id` (`group_artifact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_cvstracker_data_master`
--

CREATE TABLE IF NOT EXISTS `plugin_cvstracker_data_master` (
  `id` int(11) NOT NULL auto_increment,
  `holder_id` int(11) NOT NULL default '0',
  `log_text` varchar(255) default NULL,
  `file` varchar(25) NOT NULL default '',
  `prev_version` varchar(25) default NULL,
  `actual_version` varchar(25) default NULL,
  `author` varchar(25) NOT NULL default '',
  `cvs_date` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

CREATE OR REPLACE VIEW `nss_passwd` AS
    SELECT users.unix_uid AS uid, users.unix_gid AS gid, users.user_name AS login, users.unix_pw AS passwd, users.realname AS gecos, users.shell, users.user_name AS homedir, users.status
	FROM users
	WHERE users.unix_status = 'A';


CREATE OR REPLACE VIEW `nss_shadow` AS
    SELECT users.user_name AS login, users.unix_pw AS passwd, 'n' AS expired, 'n' AS pwchange
	FROM users
	WHERE users.unix_status = 'A';

--
-- Table structure for table `nss_groups`
--

CREATE TABLE IF NOT EXISTS `nss_groups` (
  `user_id` int(11) default NULL,
  `group_id` int(11) default NULL,
  `name` varchar(30) default NULL,
  `gid` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nss_usergroups`
--

CREATE TABLE IF NOT EXISTS `nss_usergroups` (
  `uid` int(11) default NULL,
  `gid` int(11) default NULL,
  `user_id` int(11) default NULL,
  `group_id` int(11) default NULL,
  `user_name` varchar(25) default NULL,
  `unix_group_name` varchar(30) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_mailing_lists`
--

CREATE TABLE IF NOT EXISTS `deleted_mailing_lists` (
  `mailing_list_name` varchar(30) default NULL,
  `delete_date` int(11) default NULL,
  `isdeleted` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deleted_groups`
--

CREATE TABLE IF NOT EXISTS `deleted_groups` (
  `unix_group_name` varchar(30) default NULL,
  `delete_date` int(11) default NULL,
  `isdeleted` int(11) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_query`
--

CREATE TABLE IF NOT EXISTS `artifact_query` (
  `artifact_query_id` varchar(10) NOT NULL default '',
  `group_artifact_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `query_name` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `artifact_query_fields`
--

CREATE TABLE IF NOT EXISTS `artifact_query_fields` (
  `artifact_query_id` int(11) NOT NULL default '0',
  `query_field_type` text NOT NULL,
  `query_field_id` int(11) NOT NULL default '0',
  `query_field_values` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `artifact_group_list_vw` AS
    SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description, agl.is_public, agl.allow_anon, agl.email_all_updates, agl.email_address, agl.due_period, agl.submit_instructions, agl.browse_instructions, agl.datatype, agl.status_timeout, agl.custom_status_field, agl.custom_renderer, aca.count, aca.open_count
	FROM (artifact_group_list AS agl
		LEFT JOIN artifact_counts_agg AS aca USING (group_artifact_id));


CREATE OR REPLACE VIEW `artifact_vw` AS
    SELECT artifact.artifact_id, artifact.group_artifact_id, artifact.status_id, artifact.priority, artifact.submitted_by, artifact.assigned_to, artifact.open_date, artifact.close_date, artifact.summary, artifact.details, u.user_name AS assigned_unixname, u.realname AS assigned_realname, u.email AS assigned_email, u2.user_name AS submitted_unixname, u2.realname AS submitted_realname, u2.email AS submitted_email, artifact_status.status_name, artifact.last_modified_date
	FROM users u, users u2, artifact_status, artifact
	WHERE artifact.assigned_to = u.user_id AND artifact.submitted_by = u2.user_id AND artifact.status_id = artifact_status.id;

CREATE OR REPLACE VIEW `docdata_vw` AS
    SELECT
		users.user_name,
		users.realname,
		users.email,
		d.group_id,
		d.docid,
		d.stateid,
		d.title,
		d.updatedate,
		d.createdate,
		d.created_by,
		d.doc_group,
		d.description,
		d.language_id,
		d.filename,
		d.filetype,
		d.filesize,
		doc_states.name AS state_name,
		doc_groups.groupname AS group_name,
		sl.name AS language_name
	FROM ((((doc_data d
		NATURAL JOIN doc_states)
			NATURAL JOIN doc_groups)
				JOIN supported_languages sl ON sl.language_id = d.language_id)
					JOIN users ON users.user_id = d.created_by);


CREATE TABLE IF NOT EXISTS `form_keys` (
    key_id int(11) NOT NULL auto_increment,
    `key` char(32) NOT NULL,
    creation_date int(11) NOT NULL,
    is_used tinyint(1) default 0 NOT NULL,
	PRIMARY KEY  (`key_id`),
	UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `forum_attachment` (
    attachmentid INT(11) NOT NULL AUTO_INCREMENT,
    userid INT(11) default 100 NOT NULL,
    dateline INT(11) default 0 NOT NULL,
    filename VARCHAR(100) DEFAULT '' NOT NULL,
    filedata LONGBLOB NOT NULL,
    visible SMALLINT default 0 NOT NULL,
    counter SMALLINT default 0 NOT NULL,
    filesize INT(11) default 0 NOT NULL,
    msg_id INT(11) default 0 NOT NULL,
    filehash VARCHAR(32) DEFAULT '' NOT NULL,
    mimetype VARCHAR(255) DEFAULT '' NOT NULL,
    PRIMARY KEY  (`attachmentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `forum_attachment_type` (
    extension character varying(20) DEFAULT '' NOT NULL,
    mimetype character varying(255) DEFAULT '' NOT NULL,
    size int(11) DEFAULT 0 NOT NULL,
    width smallint DEFAULT 0 NOT NULL,
    height smallint DEFAULT 0 NOT NULL,
    enabled smallint DEFAULT 1 NOT NULL,
	PRIMARY KEY  (`extension`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE OR REPLACE VIEW `forum_group_list_vw` AS
    SELECT
		forum_group_list.group_forum_id,
		forum_group_list.group_id,
		forum_group_list.forum_name,
		forum_group_list.is_public,
		forum_group_list.description,
		forum_group_list.allow_anonymous,
		forum_group_list.send_all_posts_to,
		forum_group_list.moderation_level,
		forum_agg_msg_count.count AS total,
		(SELECT max(forum.post_date) FROM forum
		 WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS recent,
		(SELECT count(distinct forum.thread_id) FROM forum
		 WHERE (forum.group_forum_id = forum_group_list.group_forum_id)) AS threads
	FROM (forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id));


CREATE TABLE IF NOT EXISTS `forum_pending_messages` (
    msg_id int(11) NOT NULL auto_increment,
    group_forum_id int(11) DEFAULT 0 NOT NULL,
    posted_by int(11) DEFAULT 0 NOT NULL,
    subject text DEFAULT '' NOT NULL,
    body text DEFAULT '' NOT NULL,
    post_date int(11) DEFAULT 0 NOT NULL,
    is_followup_to int(11) DEFAULT 0 NOT NULL,
    thread_id int(11) DEFAULT 0 NOT NULL,
    has_followups int(11) DEFAULT 0,
    most_recent_date int(11) DEFAULT 0 NOT NULL,
	PRIMARY KEY (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `forum_pending_attachment` (
    attachmentid int(11) NOT NULL auto_increment,
    userid int(11) DEFAULT 100 NOT NULL,
    dateline int(11) DEFAULT 0 NOT NULL,
    filename character varying(100) DEFAULT '' NOT NULL,
    filedata mediumblob NOT NULL,
    visible smallint DEFAULT 0 NOT NULL,
    counter smallint DEFAULT 0 NOT NULL,
    filesize int(11) DEFAULT 0 NOT NULL,
    msg_id int(11) DEFAULT 0 NOT NULL,
    filehash character varying(32) DEFAULT '' NOT NULL,
	PRIMARY KEY (`attachmentid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE OR REPLACE VIEW `forum_user_vw` AS
    SELECT
		forum.msg_id,
		forum.group_forum_id,
		forum.posted_by,
		forum.subject,
		forum.body,
		forum.post_date,
		forum.is_followup_to,
		forum.thread_id,
		forum.has_followups,
		forum.most_recent_date,
		users.user_name,
		users.realname
	FROM forum, users
	WHERE (forum.posted_by = users.user_id);



CREATE OR REPLACE VIEW `forum_pending_user_vw` AS
    SELECT
		forum_pending_messages.msg_id,
		forum_pending_messages.group_forum_id,
		forum_pending_messages.posted_by,
		forum_pending_messages.subject,
		forum_pending_messages.body,
		forum_pending_messages.post_date,
		forum_pending_messages.is_followup_to,
		forum_pending_messages.thread_id,
		forum_pending_messages.has_followups,
		forum_pending_messages.most_recent_date,
		users.user_name, users.realname
		FROM forum_pending_messages, users
		WHERE (forum_pending_messages.posted_by = users.user_id);



CREATE TABLE IF NOT EXISTS `group_activity_monitor` (
    group_id int(11) NOT NULL,
    user_id int(11) NOT NULL,
    filter text,
	PRIMARY KEY  (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE OR REPLACE VIEW `activity_vw` AS
	(SELECT
		agl.group_id,
		'trackeropen' AS section,
		agl.group_artifact_id AS ref_id,
		a.artifact_id AS subref_id,
		a.summary AS description,
		a.open_date AS activity_date,
		u.user_id, u.user_name,
		u.realname
		FROM (artifact_group_list agl
			JOIN artifact a USING (group_artifact_id)), users u
		WHERE (u.user_id = a.submitted_by))
	UNION
	(SELECT
		agl.group_id,
		'trackerclose' AS section,
		agl.group_artifact_id AS ref_id,
		a.artifact_id AS subref_id,
		a.summary AS description,
		a.close_date AS activity_date,
		u.user_id,
		u.user_name,
		u.realname
	FROM (artifact_group_list agl
	JOIN artifact a USING (group_artifact_id)), users u
	WHERE ((u.user_id = a.assigned_to) AND (a.close_date > 0)))
	UNION
	(SELECT
		agl.group_id,
		'commit' AS section,
		agl.group_artifact_id AS ref_id,
		a.artifact_id AS subref_id,
		pcdm.log_text AS description,
		pcdm.cvs_date AS activity_date,
		u.user_id, u.user_name, u.realname
	FROM (artifact_group_list agl JOIN artifact a
		USING (group_artifact_id)), plugin_cvstracker_data_master pcdm, plugin_cvstracker_data_artifact pcda, users u
	WHERE (((pcdm.holder_id = pcda.id) AND (pcda.group_artifact_id = a.artifact_id)) AND (u.user_name = pcdm.author)))
	UNION
	(SELECT
		frsp.group_id,
		'frsrelease' AS section,
		frsp.package_id AS ref_id,
		frsr.release_id AS subref_id,
		frsr.name AS description,
		frsr.release_date AS activity_date,
		u.user_id, u.user_name,
		u.realname
	FROM (frs_package frsp JOIN frs_release frsr USING (package_id)), users u
	WHERE (u.user_id = frsr.released_by))
	UNION
	(SELECT
		fgl.group_id,
		'forumpost' AS section,
		fgl.group_forum_id AS ref_id,
		forum.msg_id AS subref_id,
		forum.subject AS description,
		forum.post_date AS activity_date,
		u.user_id,
		u.user_name,
		u.realname
	FROM (forum_group_list fgl
	JOIN forum USING (group_forum_id)), users u
	WHERE (u.user_id = forum.posted_by))
	UNION
	(SELECT
		news_bytes.group_id,
		'news' AS section,
		news_bytes.id AS ref_id,
		news_bytes.forum_id AS subref_id,
		news_bytes.summary AS description,
		news_bytes.post_date AS activity_date,
		u.user_id,
		u.user_name,
		u.realname
	FROM news_bytes, users u
	WHERE (u.user_id = news_bytes.submitted_by));



-- --------------------------------------------------------

--
-- Table structure for table `forum_thread_seq`
--

CREATE TABLE IF NOT EXISTS `forum_thread_seq` (
  `value` int(11) NOT NULL default '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP PROCEDURE IF EXISTS newval;
DELIMITER //
CREATE PROCEDURE newval (IN tablename VARCHAR(64), OUT result INT)
  BEGIN
    SET @s=CONCAT('SELECT value INTO @newvalue FROM ',tablename);
    SET @u=CONCAT('UPDATE ',tablename,' SET value=value+1 WHERE value=@newvalue;');
    PREPARE select_stmt FROM @s;
    PREPARE update_stmt FROM @u;

    update_loop: LOOP
      EXECUTE select_stmt;
      IF @newvalue = NULL THEN
          LEAVE update_loop;
      END IF;
      EXECUTE update_stmt;
      IF row_count() = 1 THEN
        LEAVE update_loop;
      END IF;
    END LOOP update_loop;

    DEALLOCATE PREPARE select_stmt;
    DEALLOCATE PREPARE update_stmt;

    SET result=@newvalue;
  END;
//
DELIMITER ;


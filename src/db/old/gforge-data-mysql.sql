-- phpMyAdmin SQL Dump
-- version 2.9.2
-- http://www.phpmyadmin.net
--
--
-- Database: `gforge`
--

--
-- Dumping data for table `activity_log`
--


--
-- Dumping data for table `artifact`
--


--
-- Dumping data for table `artifact_canned_responses`
--


--
-- Dumping data for table `artifact_counts_agg`
--

INSERT INTO `artifact_counts_agg` (`group_artifact_id`, `count`, `open_count`) VALUES
(100, 0, 0);

--
-- Dumping data for table `artifact_extra_field_data`
--


--
-- Dumping data for table `artifact_extra_field_elements`
--


--
-- Dumping data for table `artifact_extra_field_list`
--


--
-- Dumping data for table `artifact_file`
--


--
-- Dumping data for table `artifact_group_list`
--

INSERT INTO `artifact_group_list` (`group_artifact_id`, `group_id`, `name`, `description`, `is_public`, `allow_anon`, `email_all_updates`, `email_address`, `due_period`, `submit_instructions`, `browse_instructions`, `datatype`, `status_timeout`, `custom_status_field`, `custom_renderer`) VALUES
(100, 1, 'Default', 'Default Data - Dont Edit', 3, 0, 0, '', 2592000, NULL, NULL, 0, NULL, 0, NULL);

--
-- Dumping data for table `artifact_history`
--


--
-- Dumping data for table `artifact_message`
--


--
-- Dumping data for table `artifact_monitor`
--


--
-- Dumping data for table `artifact_perm`
--


--
-- Dumping data for table `artifact_query`
--


--
-- Dumping data for table `artifact_query_fields`
--


--
-- Dumping data for table `artifact_status`
--

INSERT INTO `artifact_status` (`id`, `status_name`) VALUES
(1, 'Open'),
(2, 'Closed');

--
-- Dumping data for table `artifact_type_monitor`
--


--
-- Dumping data for table `canned_responses`
--


--
-- Dumping data for table `country_code`
--

INSERT INTO `country_code` (`country_name`, `ccode`) VALUES
('AFGHANISTAN', 'AF'),
('ALBANIA', 'AL'),
('ALGERIA', 'DZ'),
('AMERICAN SAMOA', 'AS'),
('ANDORRA', 'AD'),
('ANGOLA', 'AO'),
('ANGUILLA', 'AI'),
('ANTARCTICA', 'AQ'),
('ANTIGUA AND BARBUDA', 'AG'),
('ARGENTINA', 'AR'),
('ARMENIA', 'AM'),
('ARUBA', 'AW'),
('AUSTRALIA', 'AU'),
('AUSTRIA', 'AT'),
('AZERBAIJAN', 'AZ'),
('BAHAMAS', 'BS'),
('BAHRAIN', 'BH'),
('BANGLADESH', 'BD'),
('BARBADOS', 'BB'),
('BELARUS', 'BY'),
('BELGIUM', 'BE'),
('BELIZE', 'BZ'),
('BENIN', 'BJ'),
('BERMUDA', 'BM'),
('BHUTAN', 'BT'),
('BOLIVIA', 'BO'),
('BOSNIA AND HERZEGOVINA', 'BA'),
('BOTSWANA', 'BW'),
('BOUVET ISLAND', 'BV'),
('BRAZIL', 'BR'),
('BRITISH INDIAN OCEAN TERRITORY', 'IO'),
('BRUNEI DARUSSALAM', 'BN'),
('BULGARIA', 'BG'),
('BURKINA FASO', 'BF'),
('BURUNDI', 'BI'),
('CAMBODIA', 'KH'),
('CAMEROON', 'CM'),
('CANADA', 'CA'),
('CAPE VERDE', 'CV'),
('CAYMAN ISLANDS', 'KY'),
('CENTRAL AFRICAN REPUBLIC', 'CF'),
('CHAD', 'TD'),
('CHILE', 'CL'),
('CHINA', 'CN'),
('CHRISTMAS ISLAND', 'CX'),
('COCOS (KEELING) ISLANDS', 'CC'),
('COLOMBIA', 'CO'),
('COMOROS', 'KM'),
('CONGO', 'CG'),
('CONGO, THE DEMOCRATIC REPUBLIC OF THE', 'CD'),
('COOK ISLANDS', 'CK'),
('COSTA RICA', 'CR'),
('COTE D''IVOIRE', 'CI'),
('CROATIA', 'HR'),
('CUBA', 'CU'),
('CYPRUS', 'CY'),
('CZECH REPUBLIC', 'CZ'),
('DENMARK', 'DK'),
('DJIBOUTI', 'DJ'),
('DOMINICA', 'DM'),
('DOMINICAN REPUBLIC', 'DO'),
('EAST TIMOR', 'TP'),
('ECUADOR', 'EC'),
('EGYPT', 'EG'),
('EL SALVADOR', 'SV'),
('EQUATORIAL GUINEA', 'GQ'),
('ERITREA', 'ER'),
('ESTONIA', 'EE'),
('ETHIOPIA', 'ET'),
('FALKLAND ISLANDS (MALVINAS)', 'FK'),
('FAROE ISLANDS', 'FO'),
('FIJI', 'FJ'),
('FINLAND', 'FI'),
('FRANCE', 'FR'),
('FRENCH GUIANA', 'GF'),
('FRENCH POLYNESIA', 'PF'),
('FRENCH SOUTHERN TERRITORIES', 'TF'),
('GABON', 'GA'),
('GAMBIA', 'GM'),
('GEORGIA', 'GE'),
('GERMANY', 'DE'),
('GHANA', 'GH'),
('GIBRALTAR', 'GI'),
('GREECE', 'GR'),
('GREENLAND', 'GL'),
('GRENADA', 'GD'),
('GUADELOUPE', 'GP'),
('GUAM', 'GU'),
('GUATEMALA', 'GT'),
('GUINEA', 'GN'),
('GUINEA-BISSAU', 'GW'),
('GUYANA', 'GY'),
('HAITI', 'HT'),
('HEARD ISLAND AND MCDONALD ISLANDS', 'HM'),
('HOLY SEE (VATICAN CITY STATE)', 'VA'),
('HONDURAS', 'HN'),
('HONG KONG', 'HK'),
('HUNGARY', 'HU'),
('ICELAND', 'IS'),
('INDIA', 'IN'),
('INDONESIA', 'ID'),
('IRAN, ISLAMIC REPUBLIC OF', 'IR'),
('IRAQ', 'IQ'),
('IRELAND', 'IE'),
('ISRAEL', 'IL'),
('ITALY', 'IT'),
('JAMAICA', 'JM'),
('JAPAN', 'JP'),
('JORDAN', 'JO'),
('KAZAKSTAN', 'KZ'),
('KENYA', 'KE'),
('KIRIBATI', 'KI'),
('KOREA, DEMOCRATIC PEOPLE''S REPUBLIC OF', 'KP'),
('KOREA, REPUBLIC OF', 'KR'),
('KUWAIT', 'KW'),
('KYRGYZSTAN', 'KG'),
('LAO PEOPLE''S DEMOCRATIC REPUBLIC', 'LA'),
('LATVIA', 'LV'),
('LEBANON', 'LB'),
('LESOTHO', 'LS'),
('LIBERIA', 'LR'),
('LIBYAN ARAB JAMAHIRIYA', 'LY'),
('LIECHTENSTEIN', 'LI'),
('LITHUANIA', 'LT'),
('LUXEMBOURG', 'LU'),
('MACAU', 'MO'),
('MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF', 'MK'),
('MADAGASCAR', 'MG'),
('MALAWI', 'MW'),
('MALAYSIA', 'MY'),
('MALDIVES', 'MV'),
('MALI', 'ML'),
('MALTA', 'MT'),
('MARSHALL ISLANDS', 'MH'),
('MARTINIQUE', 'MQ'),
('MAURITANIA', 'MR'),
('MAURITIUS', 'MU'),
('MAYOTTE', 'YT'),
('MEXICO', 'MX'),
('MICRONESIA, FEDERATED STATES OF', 'FM'),
('MOLDOVA, REPUBLIC OF', 'MD'),
('MONACO', 'MC'),
('MONGOLIA', 'MN'),
('MONTSERRAT', 'MS'),
('MOROCCO', 'MA'),
('MOZAMBIQUE', 'MZ'),
('MYANMAR', 'MM'),
('NAMIBIA', 'NA'),
('NAURU', 'NR'),
('NEPAL', 'NP'),
('NETHERLANDS', 'NL'),
('NETHERLANDS ANTILLES', 'AN'),
('NEW CALEDONIA', 'NC'),
('NEW ZEALAND', 'NZ'),
('NICARAGUA', 'NI'),
('NIGER', 'NE'),
('NIGERIA', 'NG'),
('NIUE', 'NU'),
('NORFOLK ISLAND', 'NF'),
('NORTHERN MARIANA ISLANDS', 'MP'),
('NORWAY', 'NO'),
('OMAN', 'OM'),
('PAKISTAN', 'PK'),
('PALAU', 'PW'),
('PALESTINIAN TERRITORY, OCCUPIED', 'PS'),
('PANAMA', 'PA'),
('PAPUA NEW GUINEA', 'PG'),
('PARAGUAY', 'PY'),
('PERU', 'PE'),
('PHILIPPINES', 'PH'),
('PITCAIRN', 'PN'),
('POLAND', 'PL'),
('PORTUGAL', 'PT'),
('PUERTO RICO', 'PR'),
('QATAR', 'QA'),
('REUNION', 'RE'),
('ROMANIA', 'RO'),
('RUSSIAN FEDERATION', 'RU'),
('RWANDA', 'RW'),
('SAINT HELENA', 'SH'),
('SAINT KITTS AND NEVIS', 'KN'),
('SAINT LUCIA', 'LC'),
('SAINT PIERRE AND MIQUELON', 'PM'),
('SAINT VINCENT AND THE GRENADINES', 'VC'),
('SAMOA', 'WS'),
('SAN MARINO', 'SM'),
('SAO TOME AND PRINCIPE', 'ST'),
('SAUDI ARABIA', 'SA'),
('SENEGAL', 'SN'),
('SEYCHELLES', 'SC'),
('SIERRA LEONE', 'SL'),
('SINGAPORE', 'SG'),
('SLOVAKIA', 'SK'),
('SLOVENIA', 'SI'),
('SOLOMON ISLANDS', 'SB'),
('SOMALIA', 'SO'),
('SOUTH AFRICA', 'ZA'),
('SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', 'GS'),
('SPAIN', 'ES'),
('SRI LANKA', 'LK'),
('SUDAN', 'SD'),
('SURINAME', 'SR'),
('SVALBARD AND JAN MAYEN', 'SJ'),
('SWAZILAND', 'SZ'),
('SWEDEN', 'SE'),
('SWITZERLAND', 'CH'),
('SYRIAN ARAB REPUBLIC', 'SY'),
('TAIWAN, PROVINCE OF CHINA', 'TW'),
('TAJIKISTAN', 'TJ'),
('TANZANIA, UNITED REPUBLIC OF', 'TZ'),
('THAILAND', 'TH'),
('TOGO', 'TG'),
('TOKELAU', 'TK'),
('TONGA', 'TO'),
('TRINIDAD AND TOBAGO', 'TT'),
('TUNISIA', 'TN'),
('TURKEY', 'TR'),
('TURKMENISTAN', 'TM'),
('TURKS AND CAICOS ISLANDS', 'TC'),
('TUVALU', 'TV'),
('UGANDA', 'UG'),
('UKRAINE', 'UA'),
('UNITED ARAB EMIRATES', 'AE'),
('UNITED STATES', 'US'),
('UNITED STATES MINOR OUTLYING ISLANDS', 'UM'),
('URUGUAY', 'UY'),
('UZBEKISTAN', 'UZ'),
('VANUATU', 'VU'),
('VENEZUELA', 'VE'),
('VIET NAM', 'VN'),
('VIRGIN ISLANDS, BRITISH', 'VG'),
('VIRGIN ISLANDS, U.S.', 'VI'),
('WALLIS AND FUTUNA', 'WF'),
('WESTERN SAHARA', 'EH'),
('YEMEN', 'YE'),
('YUGOSLAVIA', 'YU'),
('ZAMBIA', 'ZM'),
('ZIMBABWE', 'ZW'),
('UNITED KINGDOM', 'UK');

--
-- Dumping data for table `cron_history`
--

--
-- Dumping data for table `forum_attachment_type`
--

INSERT INTO `forum_attachment_type` (`extension`, `mimetype`, `size`, `width`, `height`, `enabled`) VALUES
("gif", "Content-type: image/gif", 20000, 620, 280, 1),
("jpeg", "Content-type: image/jpeg", 20000, 620, 280, 1),
("jpg", "Content-type: image/jpeg", 100000, 0, 0, 1),
("jpe", "Content-type: image/jpeg", 20000, 620, 280, 1),
("png", "Content-type: image/png", 20000, 620, 280, 1),
("doc", "Accept-ranges: bytes\nContent-type: application/msword", 20000, 0, 0, 1),
("pdf", "Content-type: application/pdf", 20000, 0, 0, 1),
("bmp", "Content-type: image/bitmap", 20000, 620, 280, 1),
("psd", "Content-type: unknown/unknown", 20000, 0, 0, 1),
("zip", "Content-type: application/zip", 100000, 0, 0, 1),
("txt", "Content-type: plain/text", 20000, 0, 0, 1);


--
-- Dumping data for table `cron_history`
--



--
-- Dumping data for table `db_images`
--


--
-- Dumping data for table `deleted_groups`
--


--
-- Dumping data for table `deleted_mailing_lists`
--


--
-- Dumping data for table `doc_data`
--


--
-- Dumping data for table `doc_groups`
--


--
-- Dumping data for table `doc_states`
--

INSERT INTO `doc_states` (`stateid`, `name`) VALUES
(1, 'active'),
(2, 'deleted'),
(3, 'pending'),
(4, 'hidden'),
(5, 'private');

--
-- Dumping data for table `filemodule_monitor`
--


--
-- Dumping data for table `forum`
--


--
-- Dumping data for table `forum_agg_msg_count`
--


--
-- Dumping data for table `forum_group_list`
--


--
-- Dumping data for table `forum_monitored_forums`
--


--
-- Dumping data for table `forum_perm`
--


--
-- Dumping data for table `forum_saved_place`
--


--
-- Dumping data for table `frs_dlstats_file`
--


--
-- Dumping data for table `frs_dlstats_filetotal_agg`
--


--
-- Dumping data for table `frs_file`
--


--
-- Dumping data for table `frs_filetype`
--

INSERT INTO `frs_filetype` (`type_id`, `name`) VALUES
(1000, '.deb'),
(2000, '.rpm'),
(3000, '.zip'),
(4000, '.bz2'),
(4500, '.gz'),
(5000, 'Source .zip'),
(5010, 'Source .bz2'),
(5020, 'Source .gz'),
(5100, 'Source .rpm'),
(5900, 'Other Source File'),
(8000, '.jpg'),
(9000, 'text'),
(9100, 'html'),
(9200, 'pdf'),
(9999, 'Other');

--
-- Dumping data for table `frs_package`
--


--
-- Dumping data for table `frs_processor`
--

INSERT INTO `frs_processor` (`processor_id`, `name`) VALUES
(1000, 'i386'),
(6000, 'IA64'),
(7000, 'Alpha'),
(8000, 'Any'),
(2000, 'PPC'),
(3000, 'MIPS'),
(4000, 'Sparc'),
(5000, 'UltraSparc'),
(9999, 'Other');

--
-- Dumping data for table `frs_release`
--


--
-- Dumping data for table `frs_status`
--

INSERT INTO `frs_status` (`status_id`, `name`) VALUES
(1, 'Active'),
(3, 'Hidden');

--
-- Dumping data for table `group_cvs_history`
--


--
-- Dumping data for table `group_history`
--


--
-- Dumping data for table `group_join_request`
--


--
-- Dumping data for table `group_plugin`
--

INSERT INTO `group_plugin` (`group_plugin_id`, `group_id`, `plugin_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1);

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`group_id`, `group_name`, `homepage`, `is_public`, `status`, `unix_group_name`, `unix_box`, `http_domain`, `short_description`, `register_purpose`, `license_other`, `register_time`, `rand_hash`, `use_mail`, `use_survey`, `use_forum`, `use_pm`, `use_scm`, `use_news`, `type_id`, `use_docman`, `new_doc_address`, `send_all_docs`, `use_pm_depend_box`, `use_ftp`, `use_tracker`, `use_frs`, `use_stats`, `enable_pserver`, `enable_anonscm`, `license`, `scm_box`) VALUES
(1, 'Master Group', NULL, 0, 'A', 'gforge', 'shell1', NULL, NULL, NULL, NULL, 0, NULL, 1, 1, 1, 1, 1, 1, 1, 1, '', 0, 1, 1, 1, 1, 1, 1, 1, 100, 'cvs1'),
(2, 'Stats Group', NULL, 0, 'A', 'stats', 'shell1', NULL, NULL, NULL, NULL, 0, NULL, 1, 1, 1, 1, 1, 1, 1, 1, '', 0, 1, 1, 1, 1, 1, 1, 1, 100, 'cvs1'),
(3, 'News Group', NULL, 0, 'A', 'news', 'shell1', NULL, NULL, NULL, NULL, 0, NULL, 1, 1, 1, 1, 1, 1, 1, 1, '', 0, 1, 1, 1, 1, 1, 1, 1, 100, 'cvs1'),
(4, 'Peer Ratings Group', NULL, 0, 'A', 'peerrating', 'shell1', NULL, NULL, NULL, NULL, 0, NULL, 1, 1, 1, 1, 1, 1, 1, 1, '', 0, 1, 1, 1, 1, 1, 1, 1, 100, 'cvs1'),
(5, 'Template Project', NULL, 1, 'P', 'template', 'shell1', NULL, 'Project to house templates used to build other projects', NULL, NULL, 1120266772, NULL, 1, 1, 1, 1, 1, 1, 1, 1, '', 0, 1, 1, 1, 1, 1, 1, 1, 100, NULL);

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`license_id`, `license_name`) VALUES
('100', 'None'),
('101', 'GNU General Public License (GPL)'),
('102', 'GNU Library Public License (LGPL)'),
('103', 'BSD License'),
('104', 'MIT License'),
('105', 'Artistic License'),
('106', 'Mozilla Public License 1.0 (MPL)'),
('107', 'Qt Public License (QPL)'),
('108', 'IBM Public License'),
('109', 'MITRE Collaborative Virtual Workspace License (CVW License)'),
('110', 'Ricoh Source Code Public License'),
('111', 'Python License'),
('112', 'zlib/libpng License'),
('113', 'Apache Software License'),
('114', 'Vovida Software License 1.0'),
('115', 'Sun Internet Standards Source License (SISSL)'),
('116', 'Intel Open Source License'),
('117', 'Mozilla Public License 1.1 (MPL 1.1)'),
('118', 'Jabber Open Source License'),
('119', 'Nokia Open Source License'),
('120', 'Sleepycat License'),
('121', 'Nethack General Public License'),
('122', 'IBM Common Public License'),
('123', 'Apple Public Source License'),
('124', 'Public Domain'),
('125', 'Website Only'),
('126', 'Other/Proprietary License');

--
-- Dumping data for table `mail_group_list`
--


--
-- Dumping data for table `massmail_queue`
--


--
-- Dumping data for table `news_bytes`
--


--
-- Dumping data for table `nss_groups`
--

INSERT INTO `nss_groups` (`user_id`, `group_id`, `name`, `gid`) VALUES
(0, 1, 'gforge', 10001),
(0, 2, 'stats', 10002),
(0, 3, 'news', 10003),
(0, 4, 'peerrating', 10004),
(0, 1, 'scm_gforge', 50001),
(0, 2, 'scm_stats', 50002),
(0, 3, 'scm_news', 50003),
(0, 4, 'scm_peerrating', 50004);

--
-- Dumping data for table `nss_usergroups`
--


--
-- Dumping data for table `people_job`
--


--
-- Dumping data for table `people_job_category`
--

INSERT INTO `people_job_category` (`category_id`, `name`, `private_flag`) VALUES
(1, 'Developer', 0),
(2, 'Project Manager', 0),
(3, 'Unix Admin', 0),
(4, 'Doc Writer', 0),
(5, 'Tester', 0),
(6, 'Support Manager', 0),
(7, 'Graphic/Other Designer', 0);

--
-- Dumping data for table `people_job_inventory`
--


--
-- Dumping data for table `people_job_status`
--

INSERT INTO `people_job_status` (`status_id`, `name`) VALUES
(1, 'Open'),
(2, 'Filled'),
(3, 'Deleted');

--
-- Dumping data for table `people_skill`
--


--
-- Dumping data for table `people_skill_inventory`
--


--
-- Dumping data for table `people_skill_level`
--

INSERT INTO `people_skill_level` (`skill_level_id`, `name`) VALUES
(1, 'Want to Learn'),
(2, 'Competent'),
(3, 'Wizard'),
(4, 'Wrote The Book'),
(5, 'Wrote It');

--
-- Dumping data for table `people_skill_year`
--

INSERT INTO `people_skill_year` (`skill_year_id`, `name`) VALUES
(1, '< 6 Months'),
(2, '6 Mo - 2 yr'),
(3, '2 yr - 5 yr'),
(4, '5 yr - 10 yr'),
(5, '> 10 years');

--
-- Dumping data for table `plugin_cvstracker_data_artifact`
--


--
-- Dumping data for table `plugin_cvstracker_data_master`
--


--
-- Dumping data for table `plugins`
--

INSERT INTO `plugins` (`plugin_id`, `plugin_name`, `plugin_desc`) VALUES
(1, 'scmsvn', 'SVN Plugin');

--
-- Dumping data for table `prdb_dbs`
--


--
-- Dumping data for table `prdb_states`
--


--
-- Dumping data for table `prdb_types`
--


--
-- Dumping data for table `project_assigned_to`
--


--
-- Dumping data for table `project_category`
--

INSERT INTO `project_category` (`category_id`, `group_project_id`, `category_name`) VALUES
(100, 1, 'None');

--
-- Dumping data for table `project_counts_agg`
--

INSERT INTO `project_counts_agg` (`group_project_id`, `count`, `open_count`) VALUES
(1, 1, 1);

--
-- Dumping data for table `project_dependencies`
--


--
-- Dumping data for table `project_group_list`
--

INSERT INTO `project_group_list` (`group_project_id`, `group_id`, `project_name`, `is_public`, `description`, `send_all_posts_to`) VALUES
(1, 1, 'Default', 0, 'Default Project - Don''t Change', NULL);

--
-- Dumping data for table `project_history`
--


--
-- Dumping data for table `project_messages`
--


--
-- Dumping data for table `project_metric`
--


--
-- Dumping data for table `project_metric_tmp1`
--


--
-- Dumping data for table `project_perm`
--


--
-- Dumping data for table `project_status`
--

INSERT INTO `project_status` (`status_id`, `status_name`) VALUES
(1, 'Open'),
(2, 'Closed');

--
-- Dumping data for table `project_sums_agg`
--


--
-- Dumping data for table `project_task`
--

INSERT INTO `project_task` (`project_task_id`, `group_project_id`, `summary`, `details`, `percent_complete`, `priority`, `hours`, `start_date`, `end_date`, `created_by`, `status_id`, `category_id`, `duration`, `parent_id`, `last_modified_date`) VALUES
(1, 1, '', '', 0, 0, 0, 0, 0, 100, 1, 100, 0, 0, 1108701981);

--
-- Dumping data for table `project_task_artifact`
--


--
-- Dumping data for table `project_task_external_order`
--


--
-- Dumping data for table `project_weekly_metric`
--


--
-- Dumping data for table `prweb_vhost`
--


--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `group_id`, `role_name`) VALUES
(1, 1, 'Default'),
(2, 2, 'Admin'),
(3, 2, 'Senior Developer'),
(4, 2, 'Junior Developer'),
(5, 2, 'Doc Writer'),
(6, 2, 'Support Tech'),
(7, 3, 'Admin'),
(8, 3, 'Senior Developer'),
(9, 3, 'Junior Developer'),
(10, 3, 'Doc Writer'),
(11, 3, 'Support Tech'),
(12, 4, 'Admin'),
(13, 4, 'Senior Developer'),
(14, 4, 'Junior Developer'),
(15, 4, 'Doc Writer'),
(16, 4, 'Support Tech'),
(17, 1, 'Admin'),
(18, 1, 'Senior Developer'),
(19, 1, 'Junior Developer'),
(20, 1, 'Doc Writer'),
(21, 1, 'Support Tech');

--
-- Dumping data for table `role_setting`
--

INSERT INTO `role_setting` (`role_id`, `section_name`, `ref_id`, `value`) VALUES
(2, 'projectadmin', 0, 'A'),
(2, 'frs', 0, '1'),
(2, 'scm', 0, '1'),
(2, 'docman', 0, '1'),
(2, 'forumadmin', 0, '2'),
(2, 'trackeradmin', 0, '2'),
(2, 'pmadmin', 0, '2'),
(3, 'projectadmin', 0, '0'),
(3, 'frs', 0, '1'),
(3, 'scm', 0, '1'),
(3, 'docman', 0, '1'),
(3, 'forumadmin', 0, '2'),
(3, 'trackeradmin', 0, '2'),
(3, 'pmadmin', 0, '2'),
(4, 'projectadmin', 0, '0'),
(4, 'frs', 0, '0'),
(4, 'scm', 0, '1'),
(4, 'docman', 0, '0'),
(4, 'forumadmin', 0, '0'),
(4, 'trackeradmin', 0, '0'),
(4, 'pmadmin', 0, '0'),
(5, 'projectadmin', 0, '0'),
(5, 'frs', 0, '0'),
(5, 'scm', 0, '0'),
(5, 'docman', 0, '1'),
(5, 'forumadmin', 0, '0'),
(5, 'trackeradmin', 0, '0'),
(5, 'pmadmin', 0, '0'),
(6, 'projectadmin', 0, '0'),
(6, 'frs', 0, '0'),
(6, 'scm', 0, '0'),
(6, 'docman', 0, '1'),
(6, 'forumadmin', 0, '0'),
(6, 'trackeradmin', 0, '0'),
(6, 'pmadmin', 0, '0'),
(7, 'projectadmin', 0, 'A'),
(7, 'frs', 0, '1'),
(7, 'scm', 0, '1'),
(7, 'docman', 0, '1'),
(7, 'forumadmin', 0, '2'),
(7, 'trackeradmin', 0, '2'),
(7, 'pmadmin', 0, '2'),
(8, 'projectadmin', 0, '0'),
(8, 'frs', 0, '1'),
(8, 'scm', 0, '1'),
(8, 'docman', 0, '1'),
(8, 'forumadmin', 0, '2'),
(8, 'trackeradmin', 0, '2'),
(8, 'pmadmin', 0, '2'),
(9, 'projectadmin', 0, '0'),
(9, 'frs', 0, '0'),
(9, 'scm', 0, '1'),
(9, 'docman', 0, '0'),
(9, 'forumadmin', 0, '0'),
(9, 'trackeradmin', 0, '0'),
(9, 'pmadmin', 0, '0'),
(10, 'projectadmin', 0, '0'),
(10, 'frs', 0, '0'),
(10, 'scm', 0, '0'),
(10, 'docman', 0, '1'),
(10, 'forumadmin', 0, '0'),
(10, 'trackeradmin', 0, '0'),
(10, 'pmadmin', 0, '0'),
(11, 'projectadmin', 0, '0'),
(11, 'frs', 0, '0'),
(11, 'scm', 0, '0'),
(11, 'docman', 0, '1'),
(11, 'forumadmin', 0, '0'),
(11, 'trackeradmin', 0, '0'),
(11, 'pmadmin', 0, '0'),
(12, 'projectadmin', 0, 'A'),
(12, 'frs', 0, '1'),
(12, 'scm', 0, '1'),
(12, 'docman', 0, '1'),
(12, 'forumadmin', 0, '2'),
(12, 'trackeradmin', 0, '2'),
(12, 'pmadmin', 0, '2'),
(13, 'projectadmin', 0, '0'),
(13, 'frs', 0, '1'),
(13, 'scm', 0, '1'),
(13, 'docman', 0, '1'),
(13, 'forumadmin', 0, '2'),
(13, 'trackeradmin', 0, '2'),
(13, 'pmadmin', 0, '2'),
(14, 'projectadmin', 0, '0'),
(14, 'frs', 0, '0'),
(14, 'scm', 0, '1'),
(14, 'docman', 0, '0'),
(14, 'forumadmin', 0, '0'),
(14, 'trackeradmin', 0, '0'),
(14, 'pmadmin', 0, '0'),
(15, 'projectadmin', 0, '0'),
(15, 'frs', 0, '0'),
(15, 'scm', 0, '0'),
(15, 'docman', 0, '1'),
(15, 'forumadmin', 0, '0'),
(15, 'trackeradmin', 0, '0'),
(15, 'pmadmin', 0, '0'),
(16, 'projectadmin', 0, '0'),
(16, 'frs', 0, '0'),
(16, 'scm', 0, '0'),
(16, 'docman', 0, '1'),
(16, 'forumadmin', 0, '0'),
(16, 'trackeradmin', 0, '0'),
(16, 'pmadmin', 0, '0'),
(17, 'projectadmin', 0, 'A'),
(17, 'frs', 0, '1'),
(17, 'scm', 0, '1'),
(17, 'docman', 0, '1'),
(17, 'forumadmin', 0, '2'),
(17, 'trackeradmin', 0, '2'),
(17, 'tracker', 100, '3'),
(17, 'pmadmin', 0, '2'),
(17, 'pm', 1, '3'),
(18, 'projectadmin', 0, '0'),
(18, 'frs', 0, '1'),
(18, 'scm', 0, '1'),
(18, 'docman', 0, '1'),
(18, 'forumadmin', 0, '2'),
(18, 'trackeradmin', 0, '2'),
(18, 'tracker', 100, '2'),
(18, 'pmadmin', 0, '2'),
(18, 'pm', 1, '2'),
(19, 'projectadmin', 0, '0'),
(19, 'frs', 0, '0'),
(19, 'scm', 0, '1'),
(19, 'docman', 0, '0'),
(19, 'forumadmin', 0, '0'),
(19, 'trackeradmin', 0, '0'),
(19, 'tracker', 100, '1'),
(19, 'pmadmin', 0, '0'),
(19, 'pm', 1, '1'),
(20, 'projectadmin', 0, '0'),
(20, 'frs', 0, '0'),
(20, 'scm', 0, '0'),
(20, 'docman', 0, '1'),
(20, 'forumadmin', 0, '0'),
(20, 'trackeradmin', 0, '0'),
(20, 'tracker', 100, '0'),
(20, 'pmadmin', 0, '0'),
(20, 'pm', 1, '0'),
(21, 'projectadmin', 0, '0'),
(21, 'frs', 0, '0'),
(21, 'scm', 0, '0'),
(21, 'docman', 0, '1'),
(21, 'forumadmin', 0, '0'),
(21, 'trackeradmin', 0, '0'),
(21, 'tracker', 100, '2'),
(21, 'pmadmin', 0, '0'),
(21, 'pm', 1, '0');

--
-- Dumping data for table `skills_data`
--


--
-- Dumping data for table `skills_data_types`
--

INSERT INTO `skills_data_types` (`type_id`, `type_name`) VALUES
(1, 'Unspecified'),
(2, 'Project'),
(3, 'Training'),
(4, 'Proposal'),
(5, 'Investigation');

--
-- Dumping data for table `snippet`
--


--
-- Dumping data for table `snippet_package`
--


--
-- Dumping data for table `snippet_package_item`
--


--
-- Dumping data for table `snippet_package_version`
--


--
-- Dumping data for table `snippet_version`
--


--
-- Dumping data for table `stats_agg_logo_by_day`
--


--
-- Dumping data for table `stats_agg_logo_by_group`
--


--
-- Dumping data for table `stats_agg_pages_by_day`
--


--
-- Dumping data for table `stats_agg_site_by_group`
--


--
-- Dumping data for table `stats_cvs_group`
--


--
-- Dumping data for table `stats_cvs_user`
--


--
-- Dumping data for table `stats_project`
--


--
-- Dumping data for table `stats_project_developers`
--


--
-- Dumping data for table `stats_project_metric`
--


--
-- Dumping data for table `stats_project_months`
--


--
-- Dumping data for table `stats_site`
--


--
-- Dumping data for table `stats_site_months`
--


--
-- Dumping data for table `stats_site_pages_by_day`
--


--
-- Dumping data for table `stats_site_pages_by_month`
--


--
-- Dumping data for table `stats_subd_pages`
--


--
-- Dumping data for table `supported_languages`
--

INSERT INTO `supported_languages` (`language_id`, `name`, `filename`, `classname`, `language_code`) VALUES
(1, 'English', 'English.class', 'English', 'en'),
(2, 'Japanese', 'Japanese.class', 'Japanese', 'ja'),
(4, 'Spanish', 'Spanish.class', 'Spanish', 'es'),
(5, 'Thai', 'Thai.class', 'Thai', 'th'),
(6, 'German', 'German.class', 'German', 'de'),
(7, 'French', 'French.class', 'French', 'fr'),
(8, 'Italian', 'Italian.class', 'Italian', 'it'),
(9, 'Swedish', 'Swedish.class', 'Swedish', 'sv'),
(10, 'Trad.Chinese', 'Chinese.class', 'Chinese', 'zh-tw'),
(11, 'Dutch', 'Dutch.class', 'Dutch', 'nl'),
(12, 'Catalan', 'Catalan.class', 'Catalan', 'ca'),
(13, 'Pt. Brazilian', 'PortugueseBrazilian.class', 'PortugueseBrazilian', 'pt-br'),
(14, 'Russian', 'Russian.class', 'Russian', 'ru'),
(15, 'Bulgarian', 'Bulgarian.class', 'Bulgarian', 'bg'),
(16, 'Korean', 'Korean.class', 'Korean', 'ko'),
(17, 'Smpl.Chinese', 'SimplifiedChinese.class', 'SimplifiedChinese', 'zh-cn');

--
-- Dumping data for table `survey_question_types`
--

INSERT INTO `survey_question_types` (`id`, `type`) VALUES
(1, 'Radio Buttons 1-5'),
(2, 'Text Area'),
(3, 'Radio Buttons Yes/No'),
(4, 'Comment Only'),
(5, 'Text Field'),
(100, 'None');

--
-- Dumping data for table `survey_questions`
--


--
-- Dumping data for table `survey_rating_aggregate`
--


--
-- Dumping data for table `survey_rating_response`
--


--
-- Dumping data for table `survey_responses`
--


--
-- Dumping data for table `surveys`
--


--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`theme_id`, `dirname`, `fullname`, `enabled`) VALUES
(1, 'gforge', 'Default Theme', 1),
(2, 'ultralite', 'Ultra-Lite Text-only', 1),
(3, 'osx', 'OSX', 1);

--
-- Dumping data for table `trove_agg`
--


--
-- Dumping data for table `trove_cat`
--

INSERT INTO `trove_cat` (`trove_cat_id`, `version`, `parent`, `root_parent`, `shortname`, `fullname`, `description`, `count_subcat`, `count_subproj`, `fullpath`, `fullpath_ids`) VALUES
(1, 2000031601, 0, 0, 'audience', 'Intended Audience', 'The main class of people likely to be interested in this resource.', 0, 0, 'Intended Audience', '1'),
(2, 2000032401, 1, 1, 'endusers', 'End Users/Desktop', 'Programs and resources for software end users. Software for the desktop.', 0, 0, 'Intended Audience :: End Users/Desktop', '1 :: 2'),
(3, 2000041101, 1, 1, 'developers', 'Developers', 'Programs and resources for software developers, to include libraries.', 0, 0, 'Intended Audience :: Developers', '1 :: 3'),
(4, 2000031601, 1, 1, 'sysadmins', 'System Administrators', 'Programs and resources for people who administer computers and networks.', 0, 0, 'Intended Audience :: System Administrators', '1 :: 4'),
(5, 2000040701, 1, 1, 'other', 'Other Audience', 'Programs and resources for an unlisted audience.', 0, 0, 'Intended Audience :: Other Audience', '1 :: 5'),
(6, 2000031601, 0, 0, 'developmentstatus', 'Development Status', 'An indication of the development status of the software or resource.', 0, 0, 'Development Status', '6'),
(7, 2000040701, 6, 6, 'planning', '1 - Planning', 'This resource is in the planning stages only. There is no code.', 0, 0, 'Development Status :: 1 - Planning', '6 :: 7'),
(8, 2000040701, 6, 6, 'prealpha', '2 - Pre-Alpha', 'There is code for this project, but it is not usable except for further development.', 0, 0, 'Development Status :: 2 - Pre-Alpha', '6 :: 8'),
(9, 2000041101, 6, 6, 'alpha', '3 - Alpha', 'Resource is in early development, and probably incomplete and/or extremely buggy.', 0, 0, 'Development Status :: 3 - Alpha', '6 :: 9'),
(10, 2000040701, 6, 6, 'beta', '4 - Beta', 'Resource is in late phases of development. Deliverables are essentially complete, but may still have significant bugs.', 0, 0, 'Development Status :: 4 - Beta', '6 :: 10'),
(11, 2000040701, 6, 6, 'production', '5 - Production/Stable', 'Deliverables are complete and usable by the intended audience.', 0, 0, 'Development Status :: 5 - Production/Stable', '6 :: 11'),
(12, 2000040701, 6, 6, 'mature', '6 - Mature', 'This resource has an extensive history of successful use and has probably undergone several stable revisions.', 0, 0, 'Development Status :: 6 - Mature', '6 :: 12'),
(13, 2000031601, 0, 0, 'license', 'License', 'License terms under which the resource is distributed.', 0, 0, 'License', '13'),
(14, 2000032401, 13, 13, 'osi', 'OSI Approved', 'Licenses that have been approved by OSI as approved', 0, 0, 'License :: OSI Approved', '13 :: 14'),
(15, 2000032001, 14, 13, 'gpl', 'GNU General Public License (GPL)', 'GNU General Public License.', 0, 0, 'License :: OSI Approved :: GNU General Public License (GPL)', '13 :: 14 :: 15'),
(16, 2000050801, 14, 13, 'lgpl', 'GNU Lesser General Public License (LGPL)', 'GNU Lesser General Public License', 0, 0, 'License :: OSI Approved :: GNU Lesser General Public License (LGPL)', '13 :: 14 :: 16'),
(17, 2000032001, 14, 13, 'artistic', 'Artistic License', 'The Perl Artistic License', 0, 0, 'License :: OSI Approved :: Artistic License', '13 :: 14 :: 17'),
(18, 2000031601, 0, 0, 'topic', 'Topic', 'Topic categorization.', 0, 0, 'Topic', '18'),
(19, 2000032001, 136, 18, 'archiving', 'Archiving', 'Tools for maintaining and searching software or document archives.', 0, 0, 'Topic :: System :: Archiving', '18 :: 136 :: 19'),
(20, 2000032401, 18, 18, 'communications', 'Communications', 'Programs intended to facilitate communication between people.', 0, 0, 'Topic :: Communications', '18 :: 20'),
(21, 2000031601, 20, 18, 'bbs', 'BBS', 'Bulletin Board systems.', 0, 0, 'Topic :: Communications :: BBS', '18 :: 20 :: 21'),
(22, 2000031601, 20, 18, 'chat', 'Chat', 'Programs to support real-time communication over the Internet.', 0, 0, 'Topic :: Communications :: Chat', '18 :: 20 :: 22'),
(23, 2000031601, 22, 18, 'icq', 'ICQ', 'Programs to support ICQ.', 0, 0, 'Topic :: Communications :: Chat :: ICQ', '18 :: 20 :: 22 :: 23'),
(24, 2000041101, 22, 18, 'irc', 'Internet Relay Chat', 'Programs to support Internet Relay Chat.', 0, 0, 'Topic :: Communications :: Chat :: Internet Relay Chat', '18 :: 20 :: 22 :: 24'),
(25, 2000031601, 22, 18, 'talk', 'Unix Talk', 'Programs to support Unix Talk protocol.', 0, 0, 'Topic :: Communications :: Chat :: Unix Talk', '18 :: 20 :: 22 :: 25'),
(26, 2000031601, 22, 18, 'aim', 'AOL Instant Messanger', 'Programs to support AOL Instant Messanger.', 0, 0, 'Topic :: Communications :: Chat :: AOL Instant Messanger', '18 :: 20 :: 22 :: 26'),
(27, 2000031601, 20, 18, 'conferencing', 'Conferencing', 'Software to support real-time conferencing over the Internet.', 0, 0, 'Topic :: Communications :: Conferencing', '18 :: 20 :: 27'),
(28, 2000031601, 20, 18, 'email', 'Email', 'Programs for sending, processing, and handling electronic mail.', 0, 0, 'Topic :: Communications :: Email', '18 :: 20 :: 28'),
(29, 2000031601, 28, 18, 'filters', 'Filters', 'Content-driven filters and dispatchers for Email.', 0, 0, 'Topic :: Communications :: Email :: Filters', '18 :: 20 :: 28 :: 29'),
(30, 2000031601, 28, 18, 'listservers', 'Mailing List Servers', 'Tools for managing electronic mailing lists.', 0, 0, 'Topic :: Communications :: Email :: Mailing List Servers', '18 :: 20 :: 28 :: 30'),
(31, 2000031601, 28, 18, 'mua', 'Email Clients (MUA)', 'Programs for interactively reading and sending Email.', 0, 0, 'Topic :: Communications :: Email :: Email Clients (MUA)', '18 :: 20 :: 28 :: 31'),
(32, 2000031601, 28, 18, 'mta', 'Mail Transport Agents', 'Email transport and gatewaying software.', 0, 0, 'Topic :: Communications :: Email :: Mail Transport Agents', '18 :: 20 :: 28 :: 32'),
(33, 2000031601, 28, 18, 'postoffice', 'Post-Office', 'Programs to support post-office protocols, including POP and IMAP.', 0, 0, 'Topic :: Communications :: Email :: Post-Office', '18 :: 20 :: 28 :: 33'),
(34, 2000031601, 33, 18, 'pop3', 'POP3', 'Programs to support POP3 (Post-Office Protocol, version 3).', 0, 0, 'Topic :: Communications :: Email :: Post-Office :: POP3', '18 :: 20 :: 28 :: 33 :: 34'),
(35, 2000031601, 33, 18, 'imap', 'IMAP', 'Programs to support IMAP protocol (Internet Message Access Protocol).', 0, 0, 'Topic :: Communications :: Email :: Post-Office :: IMAP', '18 :: 20 :: 28 :: 33 :: 35'),
(36, 2000031601, 20, 18, 'fax', 'Fax', 'Tools for sending and receiving facsimile messages.', 0, 0, 'Topic :: Communications :: Fax', '18 :: 20 :: 36'),
(37, 2000031601, 20, 18, 'fido', 'FIDO', 'Tools for FIDOnet mail and echoes.', 0, 0, 'Topic :: Communications :: FIDO', '18 :: 20 :: 37'),
(38, 2000031601, 20, 18, 'hamradio', 'Ham Radio', 'Tools and resources for amateur radio.', 0, 0, 'Topic :: Communications :: Ham Radio', '18 :: 20 :: 38'),
(39, 2000031601, 20, 18, 'usenet', 'Usenet News', 'Software to support USENET news.', 0, 0, 'Topic :: Communications :: Usenet News', '18 :: 20 :: 39'),
(40, 2000031601, 20, 18, 'internetphone', 'Internet Phone', 'Software to support real-time speech communication over the Internet.', 0, 0, 'Topic :: Communications :: Internet Phone', '18 :: 20 :: 40'),
(41, 2000031601, 19, 18, 'packaging', 'Packaging', 'Tools for packing and unpacking multi-file formats. Includes data-only formats and software package systems.', 0, 0, 'Topic :: System :: Archiving :: Packaging', '18 :: 136 :: 19 :: 41'),
(42, 2000031601, 19, 18, 'compression', 'Compression', 'Tools and libraries for data compression.', 0, 0, 'Topic :: System :: Archiving :: Compression', '18 :: 136 :: 19 :: 42'),
(43, 2000031601, 18, 18, 'security', 'Security', 'Security-related software, to include system administration and cryptography.', 0, 0, 'Topic :: Security', '18 :: 43'),
(44, 2000031601, 43, 18, 'cryptography', 'Cryptography', 'Cryptography programs, algorithms, and libraries.', 0, 0, 'Topic :: Security :: Cryptography', '18 :: 43 :: 44'),
(45, 2000031601, 18, 18, 'development', 'Software Development', 'Software used to aid software development.', 0, 0, 'Topic :: Software Development', '18 :: 45'),
(46, 2000031601, 45, 18, 'build', 'Build Tools', 'Software for the build process.', 0, 0, 'Topic :: Software Development :: Build Tools', '18 :: 45 :: 46'),
(47, 2000031601, 45, 18, 'debuggers', 'Debuggers', 'Programs for controlling and monitoring the execution of compiled binaries.', 0, 0, 'Topic :: Software Development :: Debuggers', '18 :: 45 :: 47'),
(48, 2000031601, 45, 18, 'compilers', 'Compilers', 'Programs for compiling high-level languges into machine code.', 0, 0, 'Topic :: Software Development :: Compilers', '18 :: 45 :: 48'),
(49, 2000031601, 45, 18, 'interpreters', 'Interpreters', 'Programs for interpreting and executing high-level languages directly.', 0, 0, 'Topic :: Software Development :: Interpreters', '18 :: 45 :: 49'),
(50, 2000031601, 45, 18, 'objectbrokering', 'Object Brokering', 'Object brokering libraries and tools.', 0, 0, 'Topic :: Software Development :: Object Brokering', '18 :: 45 :: 50'),
(51, 2000031601, 50, 18, 'corba', 'CORBA', 'Tools for implementation and use of CORBA.', 0, 0, 'Topic :: Software Development :: Object Brokering :: CORBA', '18 :: 45 :: 50 :: 51'),
(52, 2000031601, 45, 18, 'versioncontrol', 'Version Control', 'Tools for managing multiple versions of evolving sources or documents.', 0, 0, 'Topic :: Software Development :: Version Control', '18 :: 45 :: 52'),
(53, 2000031601, 52, 18, 'cvs', 'CVS', 'Tools for CVS (Concurrent Versioning System).', 0, 0, 'Topic :: Software Development :: Version Control :: CVS', '18 :: 45 :: 52 :: 53'),
(54, 2000031601, 52, 18, 'rcs', 'RCS', 'Tools for RCS (Revision Control System).', 0, 0, 'Topic :: Software Development :: Version Control :: RCS', '18 :: 45 :: 52 :: 54'),
(55, 2000031601, 18, 18, 'desktop', 'Desktop Environment', 'Accessories, managers, and utilities for your GUI desktop.', 0, 0, 'Topic :: Desktop Environment', '18 :: 55'),
(56, 2000031601, 55, 18, 'windowmanagers', 'Window Managers', 'Programs that provide window control and application launching.', 0, 0, 'Topic :: Desktop Environment :: Window Managers', '18 :: 55 :: 56'),
(57, 2000031601, 55, 18, 'kde', 'K Desktop Environment (KDE)', 'Software for the KDE desktop.', 0, 0, 'Topic :: Desktop Environment :: K Desktop Environment (KDE)', '18 :: 55 :: 57'),
(58, 2000031601, 55, 18, 'gnome', 'Gnome', 'Software for the Gnome desktop.', 0, 0, 'Topic :: Desktop Environment :: Gnome', '18 :: 55 :: 58'),
(59, 2000031601, 56, 18, 'enlightenment', 'Enlightenment', 'Software for the Enlightenment window manager.', 0, 0, 'Topic :: Desktop Environment :: Window Managers :: Enlightenment', '18 :: 55 :: 56 :: 59'),
(60, 2000031601, 59, 18, 'themes', 'Themes', 'Themes for the Enlightenment window manager.', 0, 0, 'Topic :: Desktop Environment :: Window Managers :: Enlightenment :: Themes', '18 :: 55 :: 56 :: 59 :: 60'),
(61, 2000031601, 57, 18, 'themes', 'Themes', 'Themes for KDE.', 0, 0, 'Topic :: Desktop Environment :: K Desktop Environment (KDE) :: Themes', '18 :: 55 :: 57 :: 61'),
(62, 2000031601, 55, 18, 'screensavers', 'Screen Savers', 'Screen savers and lockers.', 0, 0, 'Topic :: Desktop Environment :: Screen Savers', '18 :: 55 :: 62'),
(63, 2000032001, 18, 18, 'editors', 'Text Editors', 'Programs for editing code and documents.', 0, 0, 'Topic :: Text Editors', '18 :: 63'),
(64, 2000031601, 63, 18, 'emacs', 'Emacs', 'GNU Emacs and its imitators and tools.', 0, 0, 'Topic :: Text Editors :: Emacs', '18 :: 63 :: 64'),
(65, 2000031601, 63, 18, 'ide', 'Integrated Development Environments (IDE)', 'Complete editing environments for code, including cababilities such as compilation and code building assistance.', 0, 0, 'Topic :: Text Editors :: Integrated Development Environments (IDE)', '18 :: 63 :: 65'),
(66, 2000031601, 18, 18, 'database', 'Database', 'Front ends, engines, and tools for database work.', 0, 0, 'Topic :: Database', '18 :: 66'),
(67, 2000031601, 66, 18, 'engines', 'Database Engines/Servers', 'Programs that manage data and provide control via some query language.', 0, 0, 'Topic :: Database :: Database Engines/Servers', '18 :: 66 :: 67'),
(68, 2000031601, 66, 18, 'frontends', 'Front-Ends', 'Clients and front-ends for generating queries to database engines.', 0, 0, 'Topic :: Database :: Front-Ends', '18 :: 66 :: 68'),
(69, 2000031601, 63, 18, 'documentation', 'Documentation', 'Tools for the creation and use of documentation.', 0, 0, 'Topic :: Text Editors :: Documentation', '18 :: 63 :: 69'),
(70, 2000031601, 63, 18, 'wordprocessors', 'Word Processors', 'WYSIWYG word processors.', 0, 0, 'Topic :: Text Editors :: Word Processors', '18 :: 63 :: 70'),
(71, 2000031601, 18, 18, 'education', 'Education', 'Programs and tools for educating yourself or others.', 0, 0, 'Topic :: Education', '18 :: 71'),
(72, 2000031601, 71, 18, 'cai', 'Computer Aided Instruction (CAI)', 'Programs for authoring or using Computer Aided Instrution courses.', 0, 0, 'Topic :: Education :: Computer Aided Instruction (CAI)', '18 :: 71 :: 72'),
(73, 2000031601, 71, 18, 'testing', 'Testing', 'Tools for testing someone''s knowledge on a subject.', 0, 0, 'Topic :: Education :: Testing', '18 :: 71 :: 73'),
(74, 2000042701, 136, 18, 'emulators', 'Emulators', 'Emulations of foreign operating systme and machines.', 0, 0, 'Topic :: System :: Emulators', '18 :: 136 :: 74'),
(75, 2000031701, 129, 18, 'financial', 'Financial', 'Programs related to finance.', 0, 0, 'Topic :: Office/Business :: Financial', '18 :: 129 :: 75'),
(76, 2000031601, 75, 18, 'accounting', 'Accounting', 'Checkbook balancers and accounting programs.', 0, 0, 'Topic :: Office/Business :: Financial :: Accounting', '18 :: 129 :: 75 :: 76'),
(77, 2000031601, 75, 18, 'investment', 'Investment', 'Programs for assisting in financial investment.', 0, 0, 'Topic :: Office/Business :: Financial :: Investment', '18 :: 129 :: 75 :: 77'),
(78, 2000031601, 75, 18, 'spreadsheet', 'Spreadsheet', 'Spreadsheet applications.', 0, 0, 'Topic :: Office/Business :: Financial :: Spreadsheet', '18 :: 129 :: 75 :: 78'),
(79, 2000031601, 75, 18, 'pointofsale', 'Point-Of-Sale', 'Point-Of-Sale applications.', 0, 0, 'Topic :: Office/Business :: Financial :: Point-Of-Sale', '18 :: 129 :: 75 :: 79'),
(80, 2000031601, 18, 18, 'games', 'Games/Entertainment', 'Games and Entertainment software.', 0, 0, 'Topic :: Games/Entertainment', '18 :: 80'),
(81, 2000031601, 80, 18, 'realtimestrategy', 'Real Time Strategy', 'Real Time strategy games', 0, 0, 'Topic :: Games/Entertainment :: Real Time Strategy', '18 :: 80 :: 81'),
(82, 2000031601, 80, 18, 'firstpersonshooters', 'First Person Shooters', 'First Person Shooters.', 0, 0, 'Topic :: Games/Entertainment :: First Person Shooters', '18 :: 80 :: 82'),
(83, 2000032401, 80, 18, 'turnbasedstrategy', 'Turn Based Strategy', 'Turn Based Strategy', 0, 0, 'Topic :: Games/Entertainment :: Turn Based Strategy', '18 :: 80 :: 83'),
(84, 2000031601, 80, 18, 'rpg', 'Role-Playing', 'Role-Playing games', 0, 0, 'Topic :: Games/Entertainment :: Role-Playing', '18 :: 80 :: 84'),
(85, 2000031601, 80, 18, 'simulation', 'Simulation', 'Simulation games', 0, 0, 'Topic :: Games/Entertainment :: Simulation', '18 :: 80 :: 85'),
(86, 2000031601, 80, 18, 'mud', 'Multi-User Dungeons (MUD)', 'Massively-multiplayer text based games.', 0, 0, 'Topic :: Games/Entertainment :: Multi-User Dungeons (MUD)', '18 :: 80 :: 86'),
(87, 2000031601, 18, 18, 'internet', 'Internet', 'Tools to assist human access to the Internet.', 0, 0, 'Topic :: Internet', '18 :: 87'),
(88, 2000031601, 87, 18, 'finger', 'Finger', 'The Finger protocol for getting information about users.', 0, 0, 'Topic :: Internet :: Finger', '18 :: 87 :: 88'),
(89, 2000031601, 87, 18, 'ftp', 'File Transfer Protocol (FTP)', 'Programs and tools for file transfer via FTP.', 0, 0, 'Topic :: Internet :: File Transfer Protocol (FTP)', '18 :: 87 :: 89'),
(90, 2000031601, 87, 18, 'www', 'WWW/HTTP', 'Programs and tools for the World Wide Web.', 0, 0, 'Topic :: Internet :: WWW/HTTP', '18 :: 87 :: 90'),
(91, 2000031601, 90, 18, 'browsers', 'Browsers', 'Web Browsers', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Browsers', '18 :: 87 :: 90 :: 91'),
(92, 2000031601, 90, 18, 'dynamic', 'Dynamic Content', 'Common Gateway Interface scripting and server-side parsing.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Dynamic Content', '18 :: 87 :: 90 :: 92'),
(93, 2000031601, 90, 18, 'indexing', 'Indexing/Search', 'Indexing and search tools for the Web.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Indexing/Search', '18 :: 87 :: 90 :: 93'),
(94, 2000031601, 92, 18, 'counters', 'Page Counters', 'Scripts to count numbers of pageviews.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Dynamic Content :: Page Counters', '18 :: 87 :: 90 :: 92 :: 94'),
(95, 2000031601, 92, 18, 'messageboards', 'Message Boards', 'Online message boards', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Dynamic Content :: Message Boards', '18 :: 87 :: 90 :: 92 :: 95'),
(96, 2000031601, 92, 18, 'cgi', 'CGI Tools/Libraries', 'Tools for the Common Gateway Interface', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Dynamic Content :: CGI Tools/Libraries', '18 :: 87 :: 90 :: 92 :: 96'),
(97, 2000042701, 18, 18, 'scientific', 'Scientific/Engineering', 'Scientific applications, to include research, applied and pure mathematics and sciences.', 0, 0, 'Topic :: Scientific/Engineering', '18 :: 97'),
(98, 2000031601, 97, 18, 'mathematics', 'Mathematics', 'Software to support pure and applied mathematics.', 0, 0, 'Topic :: Scientific/Engineering :: Mathematics', '18 :: 97 :: 98'),
(99, 2000031601, 18, 18, 'multimedia', 'Multimedia', 'Graphics, sound, video, and multimedia.', 0, 0, 'Topic :: Multimedia', '18 :: 99'),
(100, 2000031601, 99, 18, 'graphics', 'Graphics', 'Tools and resources for computer graphics.', 0, 0, 'Topic :: Multimedia :: Graphics', '18 :: 99 :: 100'),
(101, 2000031601, 100, 18, 'capture', 'Capture', 'Support for scanners, cameras, and screen capture.', 0, 0, 'Topic :: Multimedia :: Graphics :: Capture', '18 :: 99 :: 100 :: 101'),
(102, 2000031601, 101, 18, 'scanners', 'Scanners', 'Support for graphic scanners.', 0, 0, 'Topic :: Multimedia :: Graphics :: Capture :: Scanners', '18 :: 99 :: 100 :: 101 :: 102'),
(103, 2000031601, 101, 18, 'cameras', 'Digital Camera', 'Digital Camera', 0, 0, 'Topic :: Multimedia :: Graphics :: Capture :: Digital Camera', '18 :: 99 :: 100 :: 101 :: 103'),
(104, 2000031601, 101, 18, 'screencapture', 'Screen Capture', 'Screen capture tools and processors.', 0, 0, 'Topic :: Multimedia :: Graphics :: Capture :: Screen Capture', '18 :: 99 :: 100 :: 101 :: 104'),
(105, 2000031701, 100, 18, 'conversion', 'Graphics Conversion', 'Programs which convert between graphics formats.', 0, 0, 'Topic :: Multimedia :: Graphics :: Graphics Conversion', '18 :: 99 :: 100 :: 105'),
(106, 2000031701, 100, 18, 'editors', 'Editors', 'Drawing, painting, and structured editing programs.', 0, 0, 'Topic :: Multimedia :: Graphics :: Editors', '18 :: 99 :: 100 :: 106'),
(107, 2000031701, 106, 18, 'vector', 'Vector-Based', 'Vector-Based drawing programs.', 0, 0, 'Topic :: Multimedia :: Graphics :: Editors :: Vector-Based', '18 :: 99 :: 100 :: 106 :: 107'),
(108, 2000031701, 106, 18, 'raster', 'Raster-Based', 'Raster/Bitmap based drawing programs.', 0, 0, 'Topic :: Multimedia :: Graphics :: Editors :: Raster-Based', '18 :: 99 :: 100 :: 106 :: 108'),
(109, 2000031701, 100, 18, '3dmodeling', '3D Modeling', 'Programs for working with 3D Models.', 0, 0, 'Topic :: Multimedia :: Graphics :: 3D Modeling', '18 :: 99 :: 100 :: 109'),
(110, 2000031701, 100, 18, '3drendering', '3D Rendering', 'Programs which render 3D models.', 0, 0, 'Topic :: Multimedia :: Graphics :: 3D Rendering', '18 :: 99 :: 100 :: 110'),
(111, 2000031701, 100, 18, 'presentation', 'Presentation', 'Tools for generating presentation graphics and slides.', 0, 0, 'Topic :: Multimedia :: Graphics :: Presentation', '18 :: 99 :: 100 :: 111'),
(112, 2000031701, 100, 18, 'viewers', 'Viewers', 'Programs that can display various graphics formats.', 0, 0, 'Topic :: Multimedia :: Graphics :: Viewers', '18 :: 99 :: 100 :: 112'),
(113, 2000031701, 99, 18, 'sound', 'Sound/Audio', 'Tools for generating, editing, analyzing, and playing sound.', 0, 0, 'Topic :: Multimedia :: Sound/Audio', '18 :: 99 :: 113'),
(114, 2000031701, 113, 18, 'analysis', 'Analysis', 'Sound analysis tools, to include frequency analysis.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Analysis', '18 :: 99 :: 113 :: 114'),
(115, 2000031701, 113, 18, 'capture', 'Capture/Recording', 'Sound capture and recording.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Capture/Recording', '18 :: 99 :: 113 :: 115'),
(116, 2000031701, 113, 18, 'cdaudio', 'CD Audio', 'Programs to play and manipulate audio CDs.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: CD Audio', '18 :: 99 :: 113 :: 116'),
(117, 2000031701, 116, 18, 'cdplay', 'CD Playing', 'CD Playing software, to include jukebox software.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: CD Audio :: CD Playing', '18 :: 99 :: 113 :: 116 :: 117'),
(118, 2000031701, 116, 18, 'cdripping', 'CD Ripping', 'Software to convert CD Audio to other digital formats.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: CD Audio :: CD Ripping', '18 :: 99 :: 113 :: 116 :: 118'),
(119, 2000031701, 113, 18, 'conversion', 'Conversion', 'Programs to convert between audio formats.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Conversion', '18 :: 99 :: 113 :: 119'),
(120, 2000031701, 113, 18, 'editors', 'Editors', 'Programs to edit/manipulate sound data.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Editors', '18 :: 99 :: 113 :: 120'),
(121, 2000031701, 113, 18, 'mixers', 'Mixers', 'Programs to mix audio.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Mixers', '18 :: 99 :: 113 :: 121'),
(122, 2000031701, 113, 18, 'players', 'Players', 'Programs to play audio files to a sound device.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Players', '18 :: 99 :: 113 :: 122'),
(123, 2000031701, 122, 18, 'mp3', 'MP3', 'Programs to play MP3 audio files.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Players :: MP3', '18 :: 99 :: 113 :: 122 :: 123'),
(124, 2000031701, 113, 18, 'speech', 'Speech', 'Speech manipulation and intepretation tools.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Speech', '18 :: 99 :: 113 :: 124'),
(125, 2000031701, 99, 18, 'video', 'Video', 'Video capture, editing, and playback.', 0, 0, 'Topic :: Multimedia :: Video', '18 :: 99 :: 125'),
(126, 2000031701, 125, 18, 'capture', 'Capture', 'Video capture tools.', 0, 0, 'Topic :: Multimedia :: Video :: Capture', '18 :: 99 :: 125 :: 126'),
(127, 2000031701, 125, 18, 'conversion', 'Conversion', 'Programs which convert between video formats.', 0, 0, 'Topic :: Multimedia :: Video :: Conversion', '18 :: 99 :: 125 :: 127'),
(128, 2000031701, 125, 18, 'display', 'Display', 'Programs which display various video formats.', 0, 0, 'Topic :: Multimedia :: Video :: Display', '18 :: 99 :: 125 :: 128'),
(129, 2000031701, 18, 18, 'office', 'Office/Business', 'Software for assisting and organizing work at your desk.', 0, 0, 'Topic :: Office/Business', '18 :: 129'),
(130, 2000031701, 129, 18, 'scheduling', 'Scheduling', 'Projects for scheduling time, to include project management.', 0, 0, 'Topic :: Office/Business :: Scheduling', '18 :: 129 :: 130'),
(131, 2000032001, 129, 18, 'suites', 'Office Suites', 'Integrated office suites (word processing, presentation, spreadsheet, database, etc).', 0, 0, 'Topic :: Office/Business :: Office Suites', '18 :: 129 :: 131'),
(132, 2000032001, 18, 18, 'religion', 'Religion', 'Programs relating to religion and sacred texts.', 0, 0, 'Topic :: Religion', '18 :: 132'),
(133, 2000032001, 97, 18, 'ai', 'Artificial Intelligence', 'Artificial Intelligence.', 0, 0, 'Topic :: Scientific/Engineering :: Artificial Intelligence', '18 :: 97 :: 133'),
(134, 2000032001, 97, 18, 'astronomy', 'Astronomy', 'Software and tools related to astronomy.', 0, 0, 'Topic :: Scientific/Engineering :: Astronomy', '18 :: 97 :: 134'),
(135, 2000032001, 97, 18, 'visualization', 'Visualization', 'Software for scientific visualization.', 0, 0, 'Topic :: Scientific/Engineering :: Visualization', '18 :: 97 :: 135'),
(136, 2000032001, 18, 18, 'system', 'System', 'Operating system core and administration utilities.', 0, 0, 'Topic :: System', '18 :: 136'),
(137, 2000032001, 19, 18, 'backup', 'Backup', 'Programs to manage and sequence system backup.', 0, 0, 'Topic :: System :: Archiving :: Backup', '18 :: 136 :: 19 :: 137'),
(138, 2000032001, 136, 18, 'benchmark', 'Benchmark', 'Programs for benchmarking system performance.', 0, 0, 'Topic :: System :: Benchmark', '18 :: 136 :: 138'),
(139, 2000032001, 136, 18, 'boot', 'Boot', 'Programs for bootstrapping your OS.', 0, 0, 'Topic :: System :: Boot', '18 :: 136 :: 139'),
(140, 2000032001, 139, 18, 'init', 'Init', 'Init-time programs to start system services after boot.', 0, 0, 'Topic :: System :: Boot :: Init', '18 :: 136 :: 139 :: 140'),
(141, 2000032001, 136, 18, 'clustering', 'Clustering/Distributed Networks', 'Tools for automatically distributing computation across a network.', 0, 0, 'Topic :: System :: Clustering/Distributed Networks', '18 :: 136 :: 141'),
(142, 2000032001, 136, 18, 'filesystems', 'Filesystems', 'Support for creating, editing, reading, and writing file systems.', 0, 0, 'Topic :: System :: Filesystems', '18 :: 136 :: 142'),
(143, 2000032001, 144, 18, 'linux', 'Linux', 'The Linux kernel, patches, and modules.', 0, 0, 'Topic :: System :: Operating System Kernels :: Linux', '18 :: 136 :: 144 :: 143'),
(144, 2000032001, 136, 18, 'kernels', 'Operating System Kernels', 'OS Kernels, patches, modules, and tools.', 0, 0, 'Topic :: System :: Operating System Kernels', '18 :: 136 :: 144'),
(145, 2000032001, 144, 18, 'bsd', 'BSD', 'Code relating to any of the BSD kernels.', 0, 0, 'Topic :: System :: Operating System Kernels :: BSD', '18 :: 136 :: 144 :: 145'),
(146, 2000032001, 136, 18, 'hardware', 'Hardware', 'Tools for direct, non-kernel control and configuration of hardware.', 0, 0, 'Topic :: System :: Hardware', '18 :: 136 :: 146'),
(147, 2000032001, 136, 18, 'setup', 'Installation/Setup', 'Tools for installation and setup of the operating system and other programs.', 0, 0, 'Topic :: System :: Installation/Setup', '18 :: 136 :: 147'),
(148, 2000032001, 136, 18, 'logging', 'Logging', 'Utilities for clearing, rotating, and digesting system logs.', 0, 0, 'Topic :: System :: Logging', '18 :: 136 :: 148'),
(149, 2000032001, 87, 18, 'dns', 'Name Service (DNS)', 'Domain name system servers and utilities.', 0, 0, 'Topic :: Internet :: Name Service (DNS)', '18 :: 87 :: 149'),
(150, 2000032001, 136, 18, 'networking', 'Networking', 'Network configuration and administration.', 0, 0, 'Topic :: System :: Networking', '18 :: 136 :: 150'),
(151, 2000032001, 150, 18, 'firewalls', 'Firewalls', 'Firewalls and filtering systems.', 0, 0, 'Topic :: System :: Networking :: Firewalls', '18 :: 136 :: 150 :: 151'),
(152, 2000032001, 150, 18, 'monitoring', 'Monitoring', 'System monitoring, traffic analysis, and sniffers.', 0, 0, 'Topic :: System :: Networking :: Monitoring', '18 :: 136 :: 150 :: 152'),
(153, 2000032001, 136, 18, 'power', 'Power (UPS)', 'Code for communication with uninterruptible power supplies.', 0, 0, 'Topic :: System :: Power (UPS)', '18 :: 136 :: 153'),
(154, 2000032001, 18, 18, 'printing', 'Printing', 'Tools, daemons, and utilities for printer control.', 0, 0, 'Topic :: Printing', '18 :: 154'),
(155, 2000032001, 152, 18, 'watchdog', 'Hardware Watchdog', 'Software to monitor and perform actions or shutdown on hardware trouble detection.', 0, 0, 'Topic :: System :: Networking :: Monitoring :: Hardware Watchdog', '18 :: 136 :: 150 :: 152 :: 155'),
(156, 2000032001, 18, 18, 'terminals', 'Terminals', 'Terminal emulators, terminal programs, and terminal session utilities.', 0, 0, 'Topic :: Terminals', '18 :: 156'),
(157, 2000032001, 156, 18, 'serial', 'Serial', 'Dialup, terminal emulation, and file transfer over serial lines.', 0, 0, 'Topic :: Terminals :: Serial', '18 :: 156 :: 157'),
(158, 2000032001, 156, 18, 'virtual', 'Terminal Emulators/X Terminals', 'Programs to handle multiple terminal sessions. Includes terminal emulations for X and other window systems.', 0, 0, 'Topic :: Terminals :: Terminal Emulators/X Terminals', '18 :: 156 :: 158'),
(159, 2000032001, 156, 18, 'telnet', 'Telnet', 'Support for telnet; terminal sessions across Internet links.', 0, 0, 'Topic :: Terminals :: Telnet', '18 :: 156 :: 159'),
(160, 2000032001, 0, 0, 'language', 'Programming Language', 'Language in which this program was written, or was meant to support.', 0, 0, 'Programming Language', '160'),
(161, 2000032001, 160, 160, 'apl', 'APL', 'APL', 0, 0, 'Programming Language :: APL', '160 :: 161'),
(162, 2000032001, 160, 160, 'assembly', 'Assembly', 'Assembly-level programs. Platform specific.', 0, 0, 'Programming Language :: Assembly', '160 :: 162'),
(163, 2000051001, 160, 160, 'ada', 'Ada', 'Ada', 0, 0, 'Programming Language :: Ada', '160 :: 163'),
(164, 2000032001, 160, 160, 'c', 'C', 'C', 0, 0, 'Programming Language :: C', '160 :: 164'),
(165, 2000032001, 160, 160, 'cpp', 'C++', 'C++', 0, 0, 'Programming Language :: C++', '160 :: 165'),
(166, 2000032401, 160, 160, 'eiffel', 'Eiffel', 'Eiffel', 0, 0, 'Programming Language :: Eiffel', '160 :: 166'),
(167, 2000032001, 160, 160, 'euler', 'Euler', 'Euler', 0, 0, 'Programming Language :: Euler', '160 :: 167'),
(168, 2000032001, 160, 160, 'forth', 'Forth', 'Forth', 0, 0, 'Programming Language :: Forth', '160 :: 168'),
(169, 2000032001, 160, 160, 'fortran', 'Fortran', 'Fortran', 0, 0, 'Programming Language :: Fortran', '160 :: 169'),
(170, 2000032001, 160, 160, 'lisp', 'Lisp', 'Lisp', 0, 0, 'Programming Language :: Lisp', '160 :: 170'),
(171, 2000041101, 160, 160, 'logo', 'Logo', 'Logo', 0, 0, 'Programming Language :: Logo', '160 :: 171'),
(172, 2000032001, 160, 160, 'ml', 'ML', 'ML', 0, 0, 'Programming Language :: ML', '160 :: 172'),
(173, 2000032001, 160, 160, 'modula', 'Modula', 'Modula-2 or Modula-3', 0, 0, 'Programming Language :: Modula', '160 :: 173'),
(174, 2000032001, 160, 160, 'objectivec', 'Objective C', 'Objective C', 0, 0, 'Programming Language :: Objective C', '160 :: 174'),
(175, 2000032001, 160, 160, 'pascal', 'Pascal', 'Pascal', 0, 0, 'Programming Language :: Pascal', '160 :: 175'),
(176, 2000032001, 160, 160, 'perl', 'Perl', 'Perl', 0, 0, 'Programming Language :: Perl', '160 :: 176'),
(177, 2000032001, 160, 160, 'prolog', 'Prolog', 'Prolog', 0, 0, 'Programming Language :: Prolog', '160 :: 177'),
(178, 2000032001, 160, 160, 'python', 'Python', 'Python', 0, 0, 'Programming Language :: Python', '160 :: 178'),
(179, 2000032001, 160, 160, 'rexx', 'Rexx', 'Rexx', 0, 0, 'Programming Language :: Rexx', '160 :: 179'),
(180, 2000032001, 160, 160, 'simula', 'Simula', 'Simula', 0, 0, 'Programming Language :: Simula', '160 :: 180'),
(181, 2000032001, 160, 160, 'smalltalk', 'Smalltalk', 'Smalltalk', 0, 0, 'Programming Language :: Smalltalk', '160 :: 181'),
(182, 2000032001, 160, 160, 'tcl', 'Tcl', 'Tcl', 0, 0, 'Programming Language :: Tcl', '160 :: 182'),
(183, 2000032001, 160, 160, 'php', 'PHP', 'PHP', 0, 0, 'Programming Language :: PHP', '160 :: 183'),
(184, 2000032001, 160, 160, 'asp', 'ASP', 'Active Server Pages', 0, 0, 'Programming Language :: ASP', '160 :: 184'),
(185, 2000032001, 160, 160, 'shell', 'Unix Shell', 'Unix Shell', 0, 0, 'Programming Language :: Unix Shell', '160 :: 185'),
(186, 2000032001, 160, 160, 'visualbasic', 'Visual Basic', 'Visual Basic', 0, 0, 'Programming Language :: Visual Basic', '160 :: 186'),
(187, 2000032001, 14, 13, 'bsd', 'BSD License', 'BSD License', 0, 0, 'License :: OSI Approved :: BSD License', '13 :: 14 :: 187'),
(188, 2000032001, 14, 13, 'mit', 'MIT/X Consortium License', 'MIT License, also the X Consortium License.', 0, 0, 'License :: OSI Approved :: MIT/X Consortium License', '13 :: 14 :: 188'),
(189, 2000032001, 14, 13, 'mpl', 'Mozilla Public License (MPL)', 'Mozilla Public License (MPL)', 0, 0, 'License :: OSI Approved :: Mozilla Public License (MPL)', '13 :: 14 :: 189'),
(190, 2000032001, 14, 13, 'qpl', 'QT Public License (QPL)', 'QT Public License', 0, 0, 'License :: OSI Approved :: QT Public License (QPL)', '13 :: 14 :: 190'),
(192, 2000032001, 14, 13, 'cvw', 'MITRE Collaborative Virtual Workspace License (CVW)', 'MITRE Collaborative Virtual Workspace License (CVW)', 0, 0, 'License :: OSI Approved :: MITRE Collaborative Virtual Workspace License (CVW)', '13 :: 14 :: 192'),
(193, 2000032001, 14, 13, 'ricoh', 'Ricoh Source Code Public License', 'Ricoh Source Code Public License', 0, 0, 'License :: OSI Approved :: Ricoh Source Code Public License', '13 :: 14 :: 193'),
(194, 2000032001, 14, 13, 'python', 'Python License', 'Python License', 0, 0, 'License :: OSI Approved :: Python License', '13 :: 14 :: 194'),
(195, 2000032001, 14, 13, 'zlib', 'zlib/libpng License', 'zlib/libpng License', 0, 0, 'License :: OSI Approved :: zlib/libpng License', '13 :: 14 :: 195'),
(196, 2000040701, 13, 13, 'other', 'Other/Proprietary License', 'Non OSI-Approved/Proprietary license.', 0, 0, 'License :: Other/Proprietary License', '13 :: 196'),
(197, 2000032001, 13, 13, 'publicdomain', 'Public Domain', 'Public Domain. No author-retained rights.', 0, 0, 'License :: Public Domain', '13 :: 197'),
(198, 2000032001, 160, 160, 'java', 'Java', 'Java', 0, 0, 'Programming Language :: Java', '160 :: 198'),
(199, 2000032101, 0, 0, 'os', 'Operating System', 'What operating system the program requires to run, if any.', 0, 0, 'Operating System', '199'),
(200, 2000032101, 199, 199, 'posix', 'POSIX', 'POSIX plus standard Berkeley socket facilities. Don''t list a more specific OS unless your program requires it.', 0, 0, 'Operating System :: POSIX', '199 :: 200'),
(201, 2000032101, 200, 199, 'linux', 'Linux', 'Any version of Linux. Don''t specify a subcategory unless the program requires a particular distribution.', 0, 0, 'Operating System :: POSIX :: Linux', '199 :: 200 :: 201'),
(202, 2000032101, 200, 199, 'bsd', 'BSD', 'Any variant of BSD. Don''t specify a subcategory unless the program requires a particular BSD flavor.', 0, 0, 'Operating System :: POSIX :: BSD', '199 :: 200 :: 202'),
(203, 2000041101, 202, 199, 'freebsd', 'FreeBSD', 'FreeBSD', 0, 0, 'Operating System :: POSIX :: BSD :: FreeBSD', '199 :: 200 :: 202 :: 203'),
(204, 2000032101, 202, 199, 'netbsd', 'NetBSD', 'NetBSD', 0, 0, 'Operating System :: POSIX :: BSD :: NetBSD', '199 :: 200 :: 202 :: 204'),
(205, 2000032101, 202, 199, 'openbsd', 'OpenBSD', 'OpenBSD', 0, 0, 'Operating System :: POSIX :: BSD :: OpenBSD', '199 :: 200 :: 202 :: 205'),
(206, 2000032101, 202, 199, 'bsdos', 'BSD/OS', 'BSD/OS', 0, 0, 'Operating System :: POSIX :: BSD :: BSD/OS', '199 :: 200 :: 202 :: 206'),
(207, 2000032101, 200, 199, 'sun', 'SunOS/Solaris', 'Any Sun Microsystems OS.', 0, 0, 'Operating System :: POSIX :: SunOS/Solaris', '199 :: 200 :: 207'),
(208, 2000032101, 200, 199, 'sco', 'SCO', 'SCO', 0, 0, 'Operating System :: POSIX :: SCO', '199 :: 200 :: 208'),
(209, 2000032101, 200, 199, 'hpux', 'HP-UX', 'HP-UX', 0, 0, 'Operating System :: POSIX :: HP-UX', '199 :: 200 :: 209'),
(210, 2000032101, 200, 199, 'aix', 'AIX', 'AIX', 0, 0, 'Operating System :: POSIX :: AIX', '199 :: 200 :: 210'),
(211, 2000032101, 200, 199, 'irix', 'IRIX', 'IRIX', 0, 0, 'Operating System :: POSIX :: IRIX', '199 :: 200 :: 211'),
(212, 2000032101, 200, 199, 'other', 'Other', 'Other specific POSIX OS, specified in description.', 0, 0, 'Operating System :: POSIX :: Other', '199 :: 200 :: 212'),
(213, 2000032101, 160, 160, 'other', 'Other', 'Other programming language, specified in description.', 0, 0, 'Programming Language :: Other', '160 :: 213'),
(214, 2000032101, 199, 199, 'microsoft', 'Microsoft', 'Microsoft operating systems.', 0, 0, 'Operating System :: Microsoft', '199 :: 214'),
(215, 2000032101, 214, 199, 'msdos', 'MS-DOS', 'Microsoft Disk Operating System (DOS)', 0, 0, 'Operating System :: Microsoft :: MS-DOS', '199 :: 214 :: 215'),
(216, 2000032101, 214, 199, 'windows', 'Windows', 'Windows software, not specific to any particular version of Windows.', 0, 0, 'Operating System :: Microsoft :: Windows', '199 :: 214 :: 216'),
(217, 2000032101, 216, 199, 'win31', 'Windows 3.1 or Earlier', 'Windows 3.1 or Earlier', 0, 0, 'Operating System :: Microsoft :: Windows :: Windows 3.1 or Earlier', '199 :: 214 :: 216 :: 217'),
(218, 2000032101, 216, 199, 'win95', 'Windows 95/98/2000', 'Windows 95, Windows 98, and Windows 2000.', 0, 0, 'Operating System :: Microsoft :: Windows :: Windows 95/98/2000', '199 :: 214 :: 216 :: 218'),
(219, 2000041101, 216, 199, 'winnt', 'Windows NT/2000', 'Windows NT and Windows 2000.', 0, 0, 'Operating System :: Microsoft :: Windows :: Windows NT/2000', '199 :: 214 :: 216 :: 219'),
(220, 2000032101, 199, 199, 'os2', 'OS/2', 'OS/2', 0, 0, 'Operating System :: OS/2', '199 :: 220'),
(221, 2000032101, 199, 199, 'macos', 'MacOS', 'MacOS', 0, 0, 'Operating System :: MacOS', '199 :: 221'),
(222, 2000032101, 216, 199, 'wince', 'Windows CE', 'Windows CE', 0, 0, 'Operating System :: Microsoft :: Windows :: Windows CE', '199 :: 214 :: 216 :: 222'),
(223, 2000032101, 199, 199, 'palmos', 'PalmOS', 'PalmOS (for Palm Pilot)', 0, 0, 'Operating System :: PalmOS', '199 :: 223'),
(224, 2000032101, 199, 199, 'beos', 'BeOS', 'BeOS', 0, 0, 'Operating System :: BeOS', '199 :: 224'),
(225, 2000032101, 0, 0, 'environment', 'Environment', 'Run-time environment required for this program.', 0, 0, 'Environment', '225'),
(226, 2000041101, 225, 225, 'console', 'Console (Text Based)', 'Console-based programs.', 0, 0, 'Environment :: Console (Text Based)', '225 :: 226'),
(227, 2000032401, 226, 225, 'curses', 'Curses', 'Curses-based software.', 0, 0, 'Environment :: Console (Text Based) :: Curses', '225 :: 226 :: 227'),
(228, 2000040701, 226, 225, 'newt', 'Newt', 'Newt', 0, 0, 'Environment :: Console (Text Based) :: Newt', '225 :: 226 :: 228'),
(229, 2000040701, 225, 225, 'x11', 'X11 Applications', 'Programs that run in an X windowing environment.', 0, 0, 'Environment :: X11 Applications', '225 :: 229'),
(230, 2000040701, 225, 225, 'win32', 'Win32 (MS Windows)', 'Programs designed to run in a graphical Microsoft Windows environment.', 0, 0, 'Environment :: Win32 (MS Windows)', '225 :: 230'),
(231, 2000040701, 229, 225, 'gnome', 'Gnome', 'Programs designed to run in a Gnome environment.', 0, 0, 'Environment :: X11 Applications :: Gnome', '225 :: 229 :: 231'),
(232, 2000040701, 229, 225, 'kde', 'KDE', 'Programs designed to run in a KDE environment.', 0, 0, 'Environment :: X11 Applications :: KDE', '225 :: 229 :: 232'),
(233, 2000040701, 225, 225, 'other', 'Other Environment', 'Programs designed to run in an environment other than one listed.', 0, 0, 'Environment :: Other Environment', '225 :: 233'),
(234, 2000040701, 18, 18, 'other', 'Other/Nonlisted Topic', 'Topic does not fit into any listed category.', 0, 0, 'Topic :: Other/Nonlisted Topic', '18 :: 234'),
(235, 2000041001, 199, 199, 'independent', 'OS Independent', 'This software does not depend on any particular operating system.', 0, 0, 'Operating System :: OS Independent', '199 :: 235'),
(236, 2000040701, 199, 199, 'other', 'Other OS', 'Program is designe for a nonlisted operating system.', 0, 0, 'Operating System :: Other OS', '199 :: 236'),
(237, 2000041001, 225, 225, 'web', 'Web Environment', 'This software is designed for a web environment.', 0, 0, 'Environment :: Web Environment', '225 :: 237'),
(238, 2000041101, 225, 225, 'daemon', 'No Input/Output (Daemon)', 'This program has no input or output, but is intended to run in the background as a daemon.', 0, 0, 'Environment :: No Input/Output (Daemon)', '225 :: 238'),
(239, 2000041301, 144, 18, 'gnuhurd', 'GNU Hurd', 'Kernel code and modules for GNU Hurd.', 0, 0, 'Topic :: System :: Operating System Kernels :: GNU Hurd', '18 :: 136 :: 144 :: 239'),
(240, 2000041301, 200, 199, 'gnuhurd', 'GNU Hurd', 'GNU Hurd', 0, 0, 'Operating System :: POSIX :: GNU Hurd', '199 :: 200 :: 240'),
(241, 2000050101, 251, 18, 'napster', 'Napster', 'Clients and servers for the Napster file sharing protocol.', 0, 0, 'Topic :: Communications :: File Sharing :: Napster', '18 :: 20 :: 251 :: 241'),
(242, 2000042701, 160, 160, 'scheme', 'Scheme', 'Scheme programming language.', 0, 0, 'Programming Language :: Scheme', '160 :: 242'),
(243, 2000042701, 90, 18, 'sitemanagement', 'Site Management', 'Tools for maintanance and management of web sites.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Site Management', '18 :: 87 :: 90 :: 243'),
(244, 2000042701, 243, 18, 'linkchecking', 'Link Checking', 'Tools to assist in checking for broken links.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: Site Management :: Link Checking', '18 :: 87 :: 90 :: 243 :: 244'),
(245, 2000042701, 87, 18, 'loganalysis', 'Log Analysis', 'Software to help analyze various log files.', 0, 0, 'Topic :: Internet :: Log Analysis', '18 :: 87 :: 245'),
(246, 2000042701, 97, 18, 'eda', 'Electronic Design Automation (EDA)', 'Tools for circuit design, schematics, board layout, and more.', 0, 0, 'Topic :: Scientific/Engineering :: Electronic Design Automation (EDA)', '18 :: 97 :: 246'),
(247, 2000042701, 20, 18, 'telephony', 'Telephony', 'Telephony related applications, to include automated voice response systems.', 0, 0, 'Topic :: Communications :: Telephony', '18 :: 20 :: 247'),
(248, 2000042801, 113, 18, 'midi', 'MIDI', 'Software related to MIDI synthesis and playback.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: MIDI', '18 :: 99 :: 113 :: 248'),
(249, 2000042801, 113, 18, 'synthesis', 'Sound Synthesis', 'Software for creation and synthesis of sound.', 0, 0, 'Topic :: Multimedia :: Sound/Audio :: Sound Synthesis', '18 :: 99 :: 113 :: 249'),
(250, 2000042801, 90, 18, 'httpservers', 'HTTP Servers', 'Software designed to serve content via the HTTP protocol.', 0, 0, 'Topic :: Internet :: WWW/HTTP :: HTTP Servers', '18 :: 87 :: 90 :: 250'),
(251, 2000050101, 20, 18, 'filesharing', 'File Sharing', 'Software for person-to-person online file sharing.', 0, 0, 'Topic :: Communications :: File Sharing', '18 :: 20 :: 251'),
(252, 2000071101, 97, 18, 'bioinformatics', 'Bio-Informatics', 'Category for gene software (e.g. Gene Ontology)', 0, 0, 'Topic :: Scientific/Engineering :: Bio-Informatics', '18 :: 97 :: 252'),
(253, 2000071101, 136, 18, 'sysadministration', 'Systems Administration', 'Systems Administration Software (e.g. configuration apps.)', 0, 0, 'Topic :: System :: Systems Administration', '18 :: 136 :: 253'),
(254, 2000071101, 160, 160, 'plsql', 'PL/SQL', 'PL/SQL Programming Language', 0, 0, 'Programming Language :: PL/SQL', '160 :: 254'),
(255, 2000071101, 160, 160, 'progress', 'PROGRESS', 'PROGRESS Programming Language', 0, 0, 'Programming Language :: PROGRESS', '160 :: 255'),
(256, 2000071101, 125, 18, 'nonlineareditor', 'Non-Linear Editor', 'Video Non-Linear Editors', 0, 0, 'Topic :: Multimedia :: Video :: Non-Linear Editor', '18 :: 99 :: 125 :: 256'),
(257, 2000071101, 136, 18, 'softwaredist', 'Software Distribution', 'Systems software for distributing other software.', 0, 0, 'Topic :: System :: Software Distribution', '18 :: 136 :: 257'),
(258, 2000071101, 160, 160, 'objectpascal', 'Object Pascal', 'Object Pascal', 0, 0, 'Programming Language :: Object Pascal', '160 :: 258'),
(259, 2000071401, 45, 18, 'codegen', 'Code Generators', 'Code Generators', 0, 0, 'Topic :: Software Development :: Code Generators', '18 :: 45 :: 259'),
(260, 2000071401, 52, 18, 'SCCS', 'SCCS', 'SCCS', 0, 0, 'Topic :: Software Development :: Version Control :: SCCS', '18 :: 45 :: 52 :: 260'),
(261, 2000072501, 160, 160, 'xbasic', 'XBasic', 'XBasic programming language', 0, 0, 'Programming Language :: XBasic', '160 :: 261'),
(262, 2000073101, 160, 160, 'coldfusion', 'Cold Fusion', 'Cold Fusion Language', 0, 0, 'Programming Language :: Cold Fusion', '160 :: 262'),
(263, 2000080401, 160, 160, 'euphoria', 'Euphoria', 'Euphoria programming language - http://www.rapideuphoria.com/', 0, 0, 'Programming Language :: Euphoria', '160 :: 263'),
(264, 2000080701, 160, 160, 'erlang', 'Erlang', 'Erlang - developed by Ericsson - http://www.erlang.org/', 0, 0, 'Programming Language :: Erlang', '160 :: 264'),
(265, 2001032001, 160, 160, 'Delphi', 'Delphi/Kylix', 'Borland/Inprise Delphi or other Object-Pascal based languages', 0, 0, 'Programming Language :: Delphi/Kylix', '160 :: 265'),
(266, 2000081601, 97, 18, 'medical', 'Medical Science Apps.', 'Medical / BioMedical Science Apps.', 0, 0, 'Topic :: Scientific/Engineering :: Medical Science Apps.', '18 :: 97 :: 266'),
(267, 2000082001, 160, 160, 'zope', 'Zope', 'Zope Object Publishing', 0, 0, 'Programming Language :: Zope', '160 :: 267'),
(268, 2000082101, 80, 18, 'Puzzles', 'Puzzle Games', 'Puzzle Games', 0, 0, 'Topic :: Games/Entertainment :: Puzzle Games', '18 :: 80 :: 268'),
(269, 2000082801, 160, 160, 'asm', 'Assembly', 'ASM programming', 0, 0, 'Programming Language :: Assembly', '160 :: 269'),
(270, 2000083101, 87, 18, 'WAP', 'WAP', 'Wireless Access Protocol', 0, 0, 'Topic :: Internet :: WAP', '18 :: 87 :: 270'),
(271, 2000092001, 160, 160, 'csharp', 'C#', 'Microsoft''s C++/Java Language', 0, 0, 'Programming Language :: C#', '160 :: 271'),
(272, 2000100501, 97, 18, 'HMI', 'Human Machine Interfaces', 'This applies to the Factory/Machine control/Automation fields where there are already thousands of applications and millions of installations.', 0, 0, 'Topic :: Scientific/Engineering :: Human Machine Interfaces', '18 :: 97 :: 272'),
(273, 2000102001, 160, 160, 'Pike', 'Pike', 'Pike, see http://pike.roxen.com/.', 0, 0, 'Programming Language :: Pike', '160 :: 273'),
(274, 2000102401, 0, 0, 'natlanguage', 'Natural Language', 'The oral/written language for the development and use of this software.', 0, 0, 'Natural Language', '274'),
(275, 2000102401, 274, 274, 'english', 'English', 'English', 0, 0, 'Natural Language :: English', '274 :: 275'),
(276, 2000102401, 274, 274, 'french', 'French', 'French', 0, 0, 'Natural Language :: French', '274 :: 276'),
(277, 2000102401, 274, 274, 'spanish', 'Spanish', 'Spanish', 0, 0, 'Natural Language :: Spanish', '274 :: 277'),
(278, 2000102601, 274, 274, 'japanese', 'Japanese', 'Projects using the Japanese language', 0, 0, 'Natural Language :: Japanese', '274 :: 278'),
(279, 2000102601, 274, 274, 'german', 'German', 'Projects using the German language', 0, 0, 'Natural Language :: German', '274 :: 279'),
(280, 2000110101, 160, 160, 'JavaScript', 'JavaScript', 'Java Scripting Language', 0, 0, 'Programming Language :: JavaScript', '160 :: 280'),
(281, 2000111401, 160, 160, 'REBOL', 'REBOL', 'REBOL Programming Language', 0, 0, 'Programming Language :: REBOL', '160 :: 281'),
(282, 2000121901, 18, 18, 'Sociology', 'Sociology', 'Social / Informational - Family / etc.', 0, 0, 'Topic :: Sociology', '18 :: 282'),
(283, 2000121901, 282, 18, 'History', 'History', 'History / Informational', 0, 0, 'Topic :: Sociology :: History', '18 :: 282 :: 283'),
(284, 2000121901, 282, 18, 'Genealogy', 'Genealogy', 'Family History / Genealogy', 0, 0, 'Topic :: Sociology :: Genealogy', '18 :: 282 :: 284'),
(285, 2001032001, 63, 18, 'textprocessing', 'Text Processing', 'Programs or libraries that are designed to batch process text documents', 0, 0, 'Topic :: Text Editors :: Text Processing', '18 :: 63 :: 285'),
(286, 2001032001, 251, 18, 'gnutella', 'Gnutella', 'Projects based around the gnutella protocol.', 0, 0, 'Topic :: Communications :: File Sharing :: Gnutella', '18 :: 20 :: 251 :: 286'),
(287, 2001032001, 80, 18, 'boardgames', 'Board Games', 'Board Games', 0, 0, 'Topic :: Games/Entertainment :: Board Games', '18 :: 80 :: 287'),
(288, 2001032001, 80, 18, 'sidescrolling', 'Side-Scrolling/Arcade Games', 'Arcade-style side-scrolling games', 0, 0, 'Topic :: Games/Entertainment :: Side-Scrolling/Arcade Games', '18 :: 80 :: 288'),
(289, 2001032001, 253, 18, 'authentication', 'Authentication/Directory', 'Authentication and directory services', 0, 0, 'Topic :: System :: Systems Administration :: Authentication/Directory', '18 :: 136 :: 253 :: 289'),
(290, 2001032001, 289, 18, 'nis', 'NIS', 'NIS services', 0, 0, 'Topic :: System :: Systems Administration :: Authentication/Directory :: NIS', '18 :: 136 :: 253 :: 289 :: 290'),
(291, 2001032001, 289, 18, 'ldap', 'LDAP', 'Leightweight directory access protocol', 0, 0, 'Topic :: System :: Systems Administration :: Authentication/Directory :: LDAP', '18 :: 136 :: 253 :: 289 :: 291'),
(292, 2001032001, 146, 18, 'drivers', 'Hardware Drivers', 'Hardware Drivers', 0, 0, 'Topic :: System :: Hardware :: Hardware Drivers', '18 :: 136 :: 146 :: 292'),
(293, 2001032001, 160, 160, 'ruby', 'Ruby', 'Ruby programming language', 0, 0, 'Programming Language :: Ruby', '160 :: 293'),
(294, 2001032001, 136, 18, 'shells', 'System Shells', 'System Shells', 0, 0, 'Topic :: System :: System Shells', '18 :: 136 :: 294'),
(295, 2001040601, 274, 274, 'russian', 'Russian', 'Projects having something to do with Russian Language', 0, 0, 'Natural Language :: Russian', '274 :: 295'),
(296, 2001041701, 14, 13, 'asl', 'Apache Software License', 'Apache Software License', 0, 0, 'License :: OSI Approved :: Apache Software License', '13 :: 14 :: 296'),
(297, 2001041701, 14, 13, 'vsl', 'Vovida Software License', 'Vovida Software License', 0, 0, 'License :: OSI Approved :: Vovida Software License', '13 :: 14 :: 297'),
(298, 2001041701, 14, 13, 'sissl', 'Sun Internet Standards Source License', 'Sun Internet Standards Source License', 0, 0, 'License :: OSI Approved :: Sun Internet Standards Source License', '13 :: 14 :: 298'),
(299, 2001041701, 14, 13, 'iosl', 'Intel Open Source License', 'Intel Open Source License', 0, 0, 'License :: OSI Approved :: Intel Open Source License', '13 :: 14 :: 299'),
(300, 2001041701, 14, 13, 'josl', 'Jabber Open Source License', 'Jabber Open Source License', 0, 0, 'License :: OSI Approved :: Jabber Open Source License', '13 :: 14 :: 300'),
(301, 2001041701, 14, 13, 'nosl', 'Nokia Open Source License', 'Nokia Open Source License', 0, 0, 'License :: OSI Approved :: Nokia Open Source License', '13 :: 14 :: 301'),
(302, 2001041701, 14, 13, 'sleepycat', 'Sleepycat License', 'Sleepycat License', 0, 0, 'License :: OSI Approved :: Sleepycat License', '13 :: 14 :: 302'),
(303, 2001041701, 14, 13, 'nethack', 'Nethack General Public License', 'Nethack General Public License', 0, 0, 'License :: OSI Approved :: Nethack General Public License', '13 :: 14 :: 303');
INSERT INTO `trove_cat` (`trove_cat_id`, `version`, `parent`, `root_parent`, `shortname`, `fullname`, `description`, `count_subcat`, `count_subproj`, `fullpath`, `fullpath_ids`) VALUES
(304, 2001041701, 189, 13, 'mpl10', 'Mozilla Public License 1.0', 'Mozilla Public License 1.0', 0, 0, 'License :: OSI Approved :: Mozilla Public License (MPL) :: Mozilla Public License 1.0', '13 :: 14 :: 189 :: 304'),
(305, 2001041701, 189, 13, 'mpl11', 'Mozilla Public License 1.1', 'Mozilla Public License 1.1', 0, 0, 'License :: OSI Approved :: Mozilla Public License (MPL) :: Mozilla Public License 1.1', '13 :: 14 :: 189 :: 305');

--
-- Dumping data for table `trove_group_link`
--


--
-- Dumping data for table `trove_treesums`
--


--
-- Dumping data for table `user_bookmarks`
--


--
-- Dumping data for table `user_diary`
--


--
-- Dumping data for table `user_diary_monitor`
--


--
-- Dumping data for table `user_group`
--


--
-- Dumping data for table `user_metric`
--


--
-- Dumping data for table `user_metric0`
--


--
-- Dumping data for table `user_metric_history`
--


--
-- Dumping data for table `user_plugin`
--


--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`user_id`, `preference_name`, `dead1`, `set_date`, `preference_value`) VALUES
(101, 'forum_style', NULL, 1144591173, 'ultimate|25');

--
-- Dumping data for table `user_ratings`
--


--
-- Dumping data for table `user_session`
--

INSERT INTO `user_session` (`user_id`, `session_hash`, `ip_addr`, `time`) VALUES
(100, '867b0c76e3a110d924f98029a28baa95', '', 1096480068);

--
-- Dumping data for table `user_type`
--

INSERT INTO `user_type` (`type_id`, `type_name`) VALUES
(1, 'User'),
(2, 'UserPool');

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `email`, `user_pw`, `realname`, `status`, `shell`, `unix_pw`, `unix_status`, `unix_uid`, `unix_box`, `add_date`, `confirm_hash`, `mail_siteupdates`, `mail_va`, `authorized_keys`, `email_new`, `people_view_skills`, `people_resume`, `timezone`, `language`, `block_ratings`, `jabber_address`, `jabber_only`, `address`, `phone`, `fax`, `title`, `firstname`, `lastname`, `address2`, `ccode`, `theme_id`, `type_id`, `unix_gid`) VALUES
(2,	'noreply', '', '', '', 'D', '/bin/bash', '', 'N', 20002, 'shell1', 0, NULL, 0, 0, NULL, NULL, 0, '', 'GMT', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'US', 1, 1, 20002),
(100, 'None', 'noreply@sourceforge.net', '*********34343', 'Nobody', 'D', '/bin/bash', '', 'N', 20100, 'shell1', 0, NULL, 0, 0, NULL, NULL, 0, '', 'GMT', 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'Nobody', NULL, NULL, 'US', 1, 1, 20100);

--
-- Dumping data for table `forum_thread_seq`
--

INSERT INTO `forum_thread_seq` (`value`) VALUES (1);


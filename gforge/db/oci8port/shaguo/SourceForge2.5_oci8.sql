--
-- Selected TOC Entries:
--
--\connect - tperdue
--
-- TOC Entry ID 2 (OID 18138427)
--
-- Name: bug_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_pk_seq START WITH 1;

--
-- TOC Entry ID 182 (OID 18138445)
--
-- Name: bug Type: TABLE Owner: tperdue
--

CREATE TABLE bug (
	bug_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	status_id number(*) DEFAULT '0' NOT NULL,
	priority number(*) DEFAULT '0' NOT NULL,
	category_id number(*) DEFAULT '0' NOT NULL,
	submitted_by number(*) DEFAULT '0' NOT NULL,
	assigned_to number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000),
	details varchar2(4000),
	close_date number(*),
	bug_group_id number(*) DEFAULT '0' NOT NULL,
	resolution_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_id)
);

--
-- TOC Entry ID 4 (OID 18138495)
--
-- Name: bug_bug_dependencies_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_bug_dependencies_pk_seq START WITH 1;

--
-- TOC Entry ID 183 (OID 18138513)
--
-- Name: bug_bug_dependencies Type: TABLE Owner: tperdue
--

CREATE TABLE bug_bug_dependencies (
	bug_depend_id number(*) NOT NULL,
	bug_id number(*) DEFAULT '0' NOT NULL,
	is_dependent_on_bug_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_depend_id)
);

--
-- TOC Entry ID 6 (OID 18138531)
--
-- Name: bug_canned_responses_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_canned_responses_pk_seq START WITH 1;

--
-- TOC Entry ID 184 (OID 18138549)
--
-- Name: bug_canned_responses Type: TABLE Owner: tperdue
--

CREATE TABLE bug_canned_responses (
	bug_canned_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	title varchar2(4000),
	body varchar2(4000),
	PRIMARY KEY (bug_canned_id)
);

--
-- TOC Entry ID 8 (OID 18138582)
--
-- Name: bug_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_category_pk_seq START WITH 1;

--
-- TOC Entry ID 185 (OID 18138600)
--
-- Name: bug_category Type: TABLE Owner: tperdue
--

CREATE TABLE bug_category (
	bug_category_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	category_name varchar2(4000),
	PRIMARY KEY (bug_category_id)
);

--
-- TOC Entry ID 10 (OID 18138632)
--
-- Name: bug_filter_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_filter_pk_seq START WITH 1;

--
-- TOC Entry ID 186 (OID 18138650)
--
-- Name: bug_filter Type: TABLE Owner: tperdue
--

CREATE TABLE bug_filter (
	filter_id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	sql_clause varchar2(4000) DEFAULT ' ' NOT NULL,
	is_active number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (filter_id)
);

--
-- TOC Entry ID 12 (OID 18138687)
--
-- Name: bug_group_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_group_pk_seq START WITH 1;

--
-- TOC Entry ID 187 (OID 18138705)
--
-- Name: bug_group Type: TABLE Owner: tperdue
--

CREATE TABLE bug_group (
	bug_group_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	group_name varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (bug_group_id)
);

--
-- TOC Entry ID 14 (OID 18138738)
--
-- Name: bug_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_history_pk_seq START WITH 1;

--
-- TOC Entry ID 188 (OID 18138756)
--
-- Name: bug_history Type: TABLE Owner: tperdue
--

CREATE TABLE bug_history (
	bug_history_id number(*) NOT NULL,
	bug_id number(*) DEFAULT '0' NOT NULL,
	field_name varchar2(4000) DEFAULT ' ' NOT NULL,
	old_value varchar2(4000) DEFAULT ' ' NOT NULL,
	mod_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*),
	PRIMARY KEY (bug_history_id)
);

--
-- TOC Entry ID 16 (OID 18138794)
--
-- Name: bug_resolution_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_resolution_pk_seq START WITH 1;

--
-- TOC Entry ID 189 (OID 18138812)
--
-- Name: bug_resolution Type: TABLE Owner: tperdue
--

CREATE TABLE bug_resolution (
	resolution_id number(*) NOT NULL,
	resolution_name varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (resolution_id)
);

--
-- TOC Entry ID 18 (OID 18138843)
--
-- Name: bug_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_status_pk_seq START WITH 1;

--
-- TOC Entry ID 190 (OID 18138861)
--
-- Name: bug_status Type: TABLE Owner: tperdue
--

CREATE TABLE bug_status (
	status_id number(*) NOT NULL,
	status_name varchar2(4000),
	PRIMARY KEY (status_id)
);

--
-- TOC Entry ID 20 (OID 18138891)
--
-- Name: bug_task_dependencies_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE bug_task_dependencies_pk_seq START WITH 1;

--
-- TOC Entry ID 191 (OID 18138909)
--
-- Name: bug_task_dependencies Type: TABLE Owner: tperdue
--

CREATE TABLE bug_task_dependencies (
	bug_depend_id number(*) NOT NULL,
	bug_id number(*) DEFAULT '0' NOT NULL,
	is_dependent_on_task_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_depend_id)
);

--
-- TOC Entry ID 22 (OID 18138927)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE canned_responses_pk_seq START WITH 1;

--
-- TOC Entry ID 192 (OID 18138946)
--
-- Name: canned_responses Type: TABLE Owner: tperdue
--

CREATE TABLE canned_responses (
	response_id number(*) NOT NULL,
	response_title character varying(25),
	response_text varchar2(4000),
	PRIMARY KEY (response_id)
);

--
-- TOC Entry ID 24 (OID 18138977)
--
-- Name: db_images_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE db_images_pk_seq START WITH 1;

--
-- TOC Entry ID 193 (OID 18138995)
--
-- Name: db_images Type: TABLE Owner: tperdue
--

CREATE TABLE db_images (
	id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	description varchar2(4000) DEFAULT ' ' NOT NULL,
	bin_data varchar2(4000) DEFAULT ' ' NOT NULL,
	filename varchar2(4000) DEFAULT ' ' NOT NULL,
	filesize number(*) DEFAULT '0' NOT NULL,
	filetype varchar2(4000) DEFAULT ' ' NOT NULL,
	width number(*) DEFAULT '0' NOT NULL,
	height number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 26 (OID 18139040)
--
-- Name: doc_data_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE doc_data_pk_seq START WITH 1;

--
-- TOC Entry ID 194 (OID 18139058)
--
-- Name: doc_data Type: TABLE Owner: tperdue
--

CREATE TABLE doc_data (
	docid number(*) NOT NULL,
	stateid number(*) DEFAULT '0' NOT NULL,
	title character varying(255) DEFAULT ' ' NOT NULL,
	data varchar2(4000) DEFAULT ' ' NOT NULL,
	updatedate1 number(*) DEFAULT '0' NOT NULL,
	createdate1 number(*) DEFAULT '0' NOT NULL,
	created_by number(*) DEFAULT '0' NOT NULL,
	doc_group number(*) DEFAULT '0' NOT NULL,
	description varchar2(4000),
	language_id number(*) DEFAULT '1' NOT NULL,
	PRIMARY KEY (docid)
);

--
-- TOC Entry ID 28 (OID 18139104)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE doc_groups_pk_seq START WITH 1;

--
-- TOC Entry ID 195 (OID 18139122)
--
-- Name: doc_groups Type: TABLE Owner: tperdue
--

CREATE TABLE doc_groups (
	doc_group number(*) NOT NULL,
	groupname character varying(255) DEFAULT ' ' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (doc_group)
);

--
-- TOC Entry ID 30 (OID 18139140)
--
-- Name: doc_states_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE doc_states_pk_seq START WITH 1;

--
-- TOC Entry ID 196 (OID 18139158)
--
-- Name: doc_states Type: TABLE Owner: tperdue
--

CREATE TABLE doc_states (
	stateid number(*) NOT NULL,
	name character varying(255) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (stateid)
);

--
-- TOC Entry ID 32 (OID 18139174)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE filemodule_monitor_pk_seq START WITH 1;

--
-- TOC Entry ID 197 (OID 18139192)
--
-- Name: filemodule_monitor Type: TABLE Owner: tperdue
--

CREATE TABLE filemodule_monitor (
	id number(*) NOT NULL,
	filemodule_id number(*) DEFAULT '0' NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 34 (OID 18139210)
--
-- Name: forum_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE forum_pk_seq START WITH 1;

--
-- TOC Entry ID 198 (OID 18139228)
--
-- Name: forum Type: TABLE Owner: tperdue
--

CREATE TABLE forum (
	msg_id number(*) NOT NULL,
	group_forum_id number(*) DEFAULT '0' NOT NULL,
	posted_by number(*) DEFAULT '0' NOT NULL,
	subject varchar2(4000) DEFAULT ' ' NOT NULL,
	body varchar2(4000) DEFAULT ' ' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	is_followup_to number(*) DEFAULT '0' NOT NULL,
	thread_id number(*) DEFAULT '0' NOT NULL,
	has_followups number(*) DEFAULT '0',
	most_recent_date number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (msg_id)
);

--
-- TOC Entry ID 199 (OID 18139275)
--
-- Name: forum_agg_msg_count Type: TABLE Owner: tperdue
--

CREATE TABLE forum_agg_msg_count (
	group_forum_id number(*) DEFAULT '0' NOT NULL,
	count number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (group_forum_id)
);

--
-- TOC Entry ID 36 (OID 18139291)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE forum_group_list_pk_seq START WITH 1;

--
-- TOC Entry ID 200 (OID 18139309)
--
-- Name: forum_group_list Type: TABLE Owner: tperdue
--

CREATE TABLE forum_group_list (
	group_forum_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	forum_name varchar2(4000) DEFAULT ' ' NOT NULL,
	is_public number(*) DEFAULT '0' NOT NULL,
	description varchar2(4000),
	allow_anonymous number(*) DEFAULT '0' NOT NULL,
	send_all_posts_to varchar2(4000),
	PRIMARY KEY (group_forum_id)
);

--
-- TOC Entry ID 38 (OID 18139348)
--
-- Name: forum_monitor_forums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE forum_monitor_forums_pk_seq START WITH 1;

--
-- TOC Entry ID 201 (OID 18139366)
--
-- Name: forum_monitored_forums Type: TABLE Owner: tperdue
--

CREATE TABLE forum_monitored_forums (
	monitor_id number(*) NOT NULL,
	forum_id number(*) DEFAULT '0' NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (monitor_id)
);

--
-- TOC Entry ID 40 (OID 18139384)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE forum_saved_place_pk_seq START WITH 1;

--
-- TOC Entry ID 202 (OID 18139402)
--
-- Name: forum_saved_place Type: TABLE Owner: tperdue
--

CREATE TABLE forum_saved_place (
	saved_place_id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	forum_id number(*) DEFAULT '0' NOT NULL,
	save_date number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (saved_place_id)
);

--
-- TOC Entry ID 203 (OID 18139454)
--
-- Name: foundry_data Type: TABLE Owner: tperdue
--

CREATE TABLE foundry_data (
	foundry_id number(*) DEFAULT '0' NOT NULL,
	freeform1_html varchar2(4000),
	freeform2_html varchar2(4000),
	sponsor1_html varchar2(4000),
	sponsor2_html varchar2(4000),
	guide_image_id number(*) DEFAULT '0' NOT NULL,
	logo_image_id number(*) DEFAULT '0' NOT NULL,
	trove_categories varchar2(4000),
	PRIMARY KEY (foundry_id)
);

--
-- TOC Entry ID 42 (OID 18139492)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE foundry_news_pk_seq START WITH 1;

--
-- TOC Entry ID 204 (OID 18139510)
--
-- Name: foundry_news Type: TABLE Owner: tperdue
--

CREATE TABLE foundry_news (
	foundry_news_id number(*) NOT NULL,
	foundry_id number(*) DEFAULT '0' NOT NULL,
	news_id number(*) DEFAULT '0' NOT NULL,
	approve_date number(*) DEFAULT '0' NOT NULL,
	is_approved number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (foundry_news_id)
);

--
-- TOC Entry ID 44 (OID 18139532)
--
-- Name: foundry_prefer_proj_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE foundry_prefer_proj_pk_seq START WITH 1;

--
-- TOC Entry ID 205 (OID 18139550)
--
-- Name: foundry_preferred_projects Type: TABLE Owner: tperdue
--

CREATE TABLE foundry_preferred_projects (
	foundry_project_id number(*) NOT NULL,
	foundry_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	rank number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (foundry_project_id)
);

--
-- TOC Entry ID 46 (OID 18139570)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE foundry_projects_pk_seq START WITH 1;

--
-- TOC Entry ID 206 (OID 18139588)
--
-- Name: foundry_projects Type: TABLE Owner: tperdue
--

CREATE TABLE foundry_projects (
	id number(*) NOT NULL,
	foundry_id number(*) DEFAULT '0' NOT NULL,
	project_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 207 (OID 18139606)
--
-- Name: frs_dlstats_agg Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_agg (
	file_id number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	downloads_http number(*) DEFAULT '0' NOT NULL,
	downloads_ftp number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 208 (OID 18139623)
--
-- Name: frs_dlstats_file_agg Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_file_agg (
	file_id number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 209 (OID 18139638)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_filetotal_agg (
	file_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (file_id)
);

--
-- TOC Entry ID 210 (OID 18139654)
--
-- Name: frs_dlstats_filetotal_agg_old Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_filetotal_agg_old (
	file_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 211 (OID 18139667)
--
-- Name: frs_dlstats_group_agg Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_group_agg (
	group_id number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 212 (OID 18139682)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE Owner: tperdue
--

CREATE TABLE frs_dlstats_grouptotal_agg (
	group_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 48 (OID 18139695)
--
-- Name: frs_file_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_file_pk_seq START WITH 1;

--
-- TOC Entry ID 213 (OID 18139714)
--
-- Name: frs_file Type: TABLE Owner: tperdue
--

CREATE TABLE frs_file (
	file_id number(*) NOT NULL,
	filename varchar2(200),
	release_id number(*) DEFAULT '0' NOT NULL,
	type_id number(*) DEFAULT '0' NOT NULL,
	processor_id number(*) DEFAULT '0' NOT NULL,
	release_time number(*) DEFAULT '0' NOT NULL,
	file_size number(*) DEFAULT '0' NOT NULL,
	post_date number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (file_id)
);

--
-- TOC Entry ID 50 (OID 18139756)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_filetype_pk_seq START WITH 1;

--
-- TOC Entry ID 214 (OID 18139774)
--
-- Name: frs_filetype Type: TABLE Owner: tperdue
--

CREATE TABLE frs_filetype (
	type_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (type_id)
);

--
-- TOC Entry ID 52 (OID 18139804)
--
-- Name: frs_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_package_pk_seq START WITH 1;

--
-- TOC Entry ID 215 (OID 18139822)
--
-- Name: frs_package Type: TABLE Owner: tperdue
--

CREATE TABLE frs_package (
	package_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	name varchar2(4000),
	status_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (package_id)
);

--
-- TOC Entry ID 54 (OID 18139856)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_processor_pk_seq START WITH 1;

--
-- TOC Entry ID 216 (OID 18139874)
--
-- Name: frs_processor Type: TABLE Owner: tperdue
--

CREATE TABLE frs_processor (
	processor_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (processor_id)
);

--
-- TOC Entry ID 56 (OID 18139904)
--
-- Name: frs_release_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_release_pk_seq START WITH 1;

--
-- TOC Entry ID 217 (OID 18139922)
--
-- Name: frs_release Type: TABLE Owner: tperdue
--

CREATE TABLE frs_release (
	release_id number(*) NOT NULL,
	package_id number(*) DEFAULT '0' NOT NULL,
	name varchar2(4000),
	notes varchar2(4000),
	changes varchar2(4000),
	status_id number(*) DEFAULT '0' NOT NULL,
	preformatted number(*) DEFAULT '0' NOT NULL,
	release_date number(*) DEFAULT '0' NOT NULL,
	released_by number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (release_id)
);

--
-- TOC Entry ID 58 (OID 18139964)
--
-- Name: frs_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE frs_status_pk_seq START WITH 1;

--
-- TOC Entry ID 218 (OID 18139982)
--
-- Name: frs_status Type: TABLE Owner: tperdue
--

CREATE TABLE frs_status (
	status_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (status_id)
);

--
-- TOC Entry ID 60 (OID 18140012)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE group_cvs_history_pk_seq START WITH 1;

--
-- TOC Entry ID 219 (OID 18140030)
--
-- Name: group_cvs_history Type: TABLE Owner: tperdue
--

CREATE TABLE group_cvs_history (
	id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	user_name character varying(80) DEFAULT ' ' NOT NULL,
	cvs_commits number(*) DEFAULT '0' NOT NULL,
	cvs_commits_wk number(*) DEFAULT '0' NOT NULL,
	cvs_adds number(*) DEFAULT '0' NOT NULL,
	cvs_adds_wk number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 62 (OID 18140056)
--
-- Name: group_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE group_history_pk_seq START WITH 1;

--
-- TOC Entry ID 220 (OID 18140074)
--
-- Name: group_history Type: TABLE Owner: tperdue
--

CREATE TABLE group_history (
	group_history_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	field_name varchar2(4000) DEFAULT ' ' NOT NULL,
	old_value varchar2(4000) DEFAULT ' ' NOT NULL,
	mod_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*),
	PRIMARY KEY (group_history_id)
);

--
-- TOC Entry ID 64 (OID 18140112)
--
-- Name: group_type_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE group_type_pk_seq START WITH 1;

--
-- TOC Entry ID 221 (OID 18140130)
--
-- Name: group_type Type: TABLE Owner: tperdue
--

CREATE TABLE group_type (
	type_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (type_id)
);

--
-- TOC Entry ID 66 (OID 18140160)
--
-- Name: groups_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE groups_pk_seq START WITH 1;

--
-- TOC Entry ID 222 (OID 18140178)
--
-- Name: groups Type: TABLE Owner: tperdue
--

CREATE TABLE groups (
	group_id number(*) NOT NULL,
	group_name character varying(40),
	homepage character varying(128),
	is_public number(*) DEFAULT '0' NOT NULL,
	status character(1) DEFAULT 'A' NOT NULL,
	unix_group_name character varying(30) DEFAULT ' ' NOT NULL,
	unix_box character varying(20) DEFAULT 'shell1' NOT NULL,
	http_domain character varying(80),
	short_description character varying(255),
	cvs_box character varying(20) DEFAULT 'cvs1' NOT NULL,
	license character varying(16),
	register_purpose varchar2(4000),
	license_other varchar2(4000),
	register_time number(*) DEFAULT '0' NOT NULL,
	use_bugs number(*) DEFAULT '1' NOT NULL,
	rand_hash varchar2(4000),
	use_mail number(*) DEFAULT '1' NOT NULL,
	use_survey number(*) DEFAULT '1' NOT NULL,
	use_patch number(*) DEFAULT '1' NOT NULL,
	use_forum number(*) DEFAULT '1' NOT NULL,
	use_pm number(*) DEFAULT '1' NOT NULL,
	use_cvs number(*) DEFAULT '1' NOT NULL,
	use_news number(*) DEFAULT '1' NOT NULL,
	use_support number(*) DEFAULT '1' NOT NULL,
	new_bug_address varchar2(4000) DEFAULT ' ' NOT NULL,
	new_patch_address varchar2(4000) DEFAULT ' ' NOT NULL,
	new_support_address varchar2(4000) DEFAULT ' ' NOT NULL,
	type number(*) DEFAULT '1' NOT NULL,
	use_docman number(*) DEFAULT '1' NOT NULL,
	send_all_bugs number(*) DEFAULT '0' NOT NULL,
	send_all_patches number(*) DEFAULT '0' NOT NULL,
	send_all_support number(*) DEFAULT '0' NOT NULL,
	new_task_address varchar2(4000) DEFAULT ' ' NOT NULL,
	send_all_tasks number(*) DEFAULT '0' NOT NULL,
	use_bug_depend_box number(*) DEFAULT '1' NOT NULL,
	use_pm_depend_box number(*) DEFAULT '1' NOT NULL,
	PRIMARY KEY (group_id)
);

--
-- TOC Entry ID 223 (OID 18140269)
--
-- Name: intel_agreement Type: TABLE Owner: tperdue
--

CREATE TABLE intel_agreement (
	user_id number(*) DEFAULT '0' NOT NULL,
	message varchar2(4000),
	is_approved number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (user_id)
);

--
-- TOC Entry ID 68 (OID 18140301)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE mail_group_list_pk_seq START WITH 1;

--
-- TOC Entry ID 224 (OID 18140319)
--
-- Name: mail_group_list Type: TABLE Owner: tperdue
--

CREATE TABLE mail_group_list (
	group_list_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	list_name varchar2(4000),
	is_public number(*) DEFAULT '0' NOT NULL,
	password character varying(16),
	list_admin number(*) DEFAULT '0' NOT NULL,
	status number(*) DEFAULT '0' NOT NULL,
	description varchar2(4000),
	PRIMARY KEY (group_list_id)
);

--
-- TOC Entry ID 70 (OID 18140359)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE news_bytes_pk_seq START WITH 1;

--
-- TOC Entry ID 225 (OID 18140377)
--
-- Name: news_bytes Type: TABLE Owner: tperdue
--

CREATE TABLE news_bytes (
	id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	submitted_by number(*) DEFAULT '0' NOT NULL,
	is_approved number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	forum_id number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000),
	details varchar2(4000),
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 72 (OID 18140419)
--
-- Name: patch_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE patch_pk_seq START WITH 1;

--
-- TOC Entry ID 226 (OID 18140437)
--
-- Name: patch Type: TABLE Owner: tperdue
--

CREATE TABLE patch (
	patch_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	patch_status_id number(*) DEFAULT '0' NOT NULL,
	patch_category_id number(*) DEFAULT '0' NOT NULL,
	submitted_by number(*) DEFAULT '0' NOT NULL,
	assigned_to number(*) DEFAULT '0' NOT NULL,
	open_date number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000),
	code varchar2(4000),
	close_date number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (patch_id)
);

--
-- TOC Entry ID 74 (OID 18140483)
--
-- Name: patch_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE patch_category_pk_seq START WITH 1;

--
-- TOC Entry ID 227 (OID 18140501)
--
-- Name: patch_category Type: TABLE Owner: tperdue
--

CREATE TABLE patch_category (
	patch_category_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	category_name varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (patch_category_id)
);

--
-- TOC Entry ID 76 (OID 18140534)
--
-- Name: patch_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE patch_history_pk_seq START WITH 1;

--
-- TOC Entry ID 228 (OID 18140552)
--
-- Name: patch_history Type: TABLE Owner: tperdue
--

CREATE TABLE patch_history (
	patch_history_id number(*) NOT NULL,
	patch_id number(*) DEFAULT '0' NOT NULL,
	field_name varchar2(4000) DEFAULT ' ' NOT NULL,
	old_value varchar2(4000) DEFAULT ' ' NOT NULL,
	mod_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*),
	PRIMARY KEY (patch_history_id)
);

--
-- TOC Entry ID 78 (OID 18140590)
--
-- Name: patch_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE patch_status_pk_seq START WITH 1;

--
-- TOC Entry ID 229 (OID 18140608)
--
-- Name: patch_status Type: TABLE Owner: tperdue
--

CREATE TABLE patch_status (
	patch_status_id number(*) NOT NULL,
	status_name varchar2(4000),
	PRIMARY KEY (patch_status_id)
);

--
-- TOC Entry ID 80 (OID 18140638)
--
-- Name: people_job_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_job_pk_seq START WITH 1;

--
-- TOC Entry ID 230 (OID 18140656)
--
-- Name: people_job Type: TABLE Owner: tperdue
--

CREATE TABLE people_job (
	job_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	created_by number(*) DEFAULT '0' NOT NULL,
	title varchar2(4000),
	description varchar2(4000),
	date1 number(*) DEFAULT '0' NOT NULL,
	status_id number(*) DEFAULT '0' NOT NULL,
	category_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (job_id)
);

--
-- TOC Entry ID 82 (OID 18140697)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_job_category_pk_seq START WITH 1;

--
-- TOC Entry ID 231 (OID 18140715)
--
-- Name: people_job_category Type: TABLE Owner: tperdue
--

CREATE TABLE people_job_category (
	category_id number(*) NOT NULL,
	name varchar2(4000),
	private_flag number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (category_id)
);

--
-- TOC Entry ID 84 (OID 18140747)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_job_inventory_pk_seq START WITH 1;

--
-- TOC Entry ID 232 (OID 18140765)
--
-- Name: people_job_inventory Type: TABLE Owner: tperdue
--

CREATE TABLE people_job_inventory (
	job_inventory_id number(*) NOT NULL,
	job_id number(*) DEFAULT '0' NOT NULL,
	skill_id number(*) DEFAULT '0' NOT NULL,
	skill_level_id number(*) DEFAULT '0' NOT NULL,
	skill_year_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (job_inventory_id)
);

--
-- TOC Entry ID 86 (OID 18140787)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_job_status_pk_seq START WITH 1;

--
-- TOC Entry ID 233 (OID 18140805)
--
-- Name: people_job_status Type: TABLE Owner: tperdue
--

CREATE TABLE people_job_status (
	status_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (status_id)
);

--
-- TOC Entry ID 88 (OID 18140835)
--
-- Name: people_skill_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_skill_pk_seq START WITH 1;

--
-- TOC Entry ID 234 (OID 18140853)
--
-- Name: people_skill Type: TABLE Owner: tperdue
--

CREATE TABLE people_skill (
	skill_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (skill_id)
);

--
-- TOC Entry ID 90 (OID 18140884)
--
-- Name: people_skill_inv_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_skill_inv_pk_seq START WITH 1;

--
-- TOC Entry ID 235 (OID 18140902)
--
-- Name: people_skill_inventory Type: TABLE Owner: tperdue
--

CREATE TABLE people_skill_inventory (
	skill_inventory_id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	skill_id number(*) DEFAULT '0' NOT NULL,
	skill_level_id number(*) DEFAULT '0' NOT NULL,
	skill_year_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (skill_inventory_id)
);

--
-- TOC Entry ID 92 (OID 18140924)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_skill_level_pk_seq START WITH 1;

--
-- TOC Entry ID 236 (OID 18140942)
--
-- Name: people_skill_level Type: TABLE Owner: tperdue
--

CREATE TABLE people_skill_level (
	skill_level_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (skill_level_id)
);

--
-- TOC Entry ID 94 (OID 18140972)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE people_skill_year_pk_seq START WITH 1;

--
-- TOC Entry ID 237 (OID 18140990)
--
-- Name: people_skill_year Type: TABLE Owner: tperdue
--

CREATE TABLE people_skill_year (
	skill_year_id number(*) NOT NULL,
	name varchar2(4000),
	PRIMARY KEY (skill_year_id)
);

--
-- TOC Entry ID 96 (OID 18141020)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_assigned_to_pk_seq START WITH 1;

--
-- TOC Entry ID 238 (OID 18141038)
--
-- Name: project_assigned_to Type: TABLE Owner: tperdue
--

CREATE TABLE project_assigned_to (
	project_assigned_id number(*) NOT NULL,
	project_task_id number(*) DEFAULT '0' NOT NULL,
	assigned_to_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_assigned_id)
);

--
-- TOC Entry ID 239 (OID 18141056)
--
-- Name: project_counts_tmp Type: TABLE Owner: tperdue
--

CREATE TABLE project_counts_tmp (
	group_id number(*),
	type varchar2(4000),
	count double precision
);

--
-- TOC Entry ID 240 (OID 18141083)
--
-- Name: project_counts_weekly_tmp Type: TABLE Owner: tperdue
--

CREATE TABLE project_counts_weekly_tmp (
	group_id number(*),
	type varchar2(4000),
	count double precision
);

--
-- TOC Entry ID 98 (OID 18141110)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_dependencies_pk_seq START WITH 1;

--
-- TOC Entry ID 241 (OID 18141128)
--
-- Name: project_dependencies Type: TABLE Owner: tperdue
--

CREATE TABLE project_dependencies (
	project_depend_id number(*) NOT NULL,
	project_task_id number(*) DEFAULT '0' NOT NULL,
	is_dependent_on_task_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_depend_id)
);

--
-- TOC Entry ID 100 (OID 18141146)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_group_list_pk_seq START WITH 1;

--
-- TOC Entry ID 242 (OID 18141164)
--
-- Name: project_group_list Type: TABLE Owner: tperdue
--

CREATE TABLE project_group_list (
	group_project_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	project_name varchar2(4000) DEFAULT ' ' NOT NULL,
	is_public number(*) DEFAULT '0' NOT NULL,
	description varchar2(4000),
	PRIMARY KEY (group_project_id)
);

--
-- TOC Entry ID 102 (OID 18141200)
--
-- Name: project_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_history_pk_seq START WITH 1;

--
-- TOC Entry ID 243 (OID 18141218)
--
-- Name: project_history Type: TABLE Owner: tperdue
--

CREATE TABLE project_history (
	project_history_id number(*) NOT NULL,
	project_task_id number(*) DEFAULT '0' NOT NULL,
	field_name varchar2(4000) DEFAULT ' ' NOT NULL,
	old_value varchar2(4000) DEFAULT ' ' NOT NULL,
	mod_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_history_id)
);

--
-- TOC Entry ID 104 (OID 18141257)
--
-- Name: project_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_metric_pk_seq START WITH 1;

--
-- TOC Entry ID 244 (OID 18141275)
--
-- Name: project_metric Type: TABLE Owner: tperdue
--

CREATE TABLE project_metric (
	ranking number(*) NOT NULL,
	percentile double precision,
	group_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 106 (OID 18141292)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_metric_tmp1_pk_seq START WITH 1;

--
-- TOC Entry ID 245 (OID 18141310)
--
-- Name: project_metric_tmp1 Type: TABLE Owner: tperdue
--

CREATE TABLE project_metric_tmp1 (
	ranking number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	value double precision,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 108 (OID 18141327)
--
-- Name: proj_metric_weekly_tm_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE proj_metric_weekly_tm_pk_seq START WITH 1;

--
-- TOC Entry ID 246 (OID 18141346)
--
-- Name: project_metric_weekly_tmp1 Type: TABLE Owner: tperdue
--

CREATE TABLE project_metric_weekly_tmp1 (
	ranking number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	value double precision,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 110 (OID 18141363)
--
-- Name: project_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_status_pk_seq START WITH 1;

--
-- TOC Entry ID 247 (OID 18141381)
--
-- Name: project_status Type: TABLE Owner: tperdue
--

CREATE TABLE project_status (
	status_id number(*) NOT NULL,
	status_name varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (status_id)
);

--
-- TOC Entry ID 112 (OID 18141412)
--
-- Name: project_task_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_task_pk_seq START WITH 1;

--
-- TOC Entry ID 248 (OID 18141430)
--
-- Name: project_task Type: TABLE Owner: tperdue
--

CREATE TABLE project_task (
	project_task_id number(*) NOT NULL,
	group_project_id number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000) DEFAULT ' ' NOT NULL,
	details varchar2(4000) DEFAULT ' ' NOT NULL,
	percent_complete number(*) DEFAULT '0' NOT NULL,
	priority number(*) DEFAULT '0' NOT NULL,
	hours double precision DEFAULT '0.00' NOT NULL,
	start_date number(*) DEFAULT '0' NOT NULL,
	end_date number(*) DEFAULT '0' NOT NULL,
	created_by number(*) DEFAULT '0' NOT NULL,
	status_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_task_id)
);

--
-- TOC Entry ID 114 (OID 18141479)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE project_weekly_metric_pk_seq START WITH 1;

--
-- TOC Entry ID 249 (OID 18141497)
--
-- Name: project_weekly_metric Type: TABLE Owner: tperdue
--

CREATE TABLE project_weekly_metric (
	ranking number(*) NOT NULL,
	percentile double precision,
	group_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 250 (OID 18141514)
--
-- Name: session Type: TABLE Owner: tperdue
--

CREATE TABLE session1 (
	user_id number(*) DEFAULT '0' NOT NULL,
	session_hash character(32) DEFAULT ' ' NOT NULL,
	ip_addr character(15) DEFAULT ' ' NOT NULL,
	time number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (session_hash)
);

--
-- TOC Entry ID 116 (OID 18141534)
--
-- Name: snippet_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE snippet_pk_seq START WITH 1;

--
-- TOC Entry ID 251 (OID 18141552)
--
-- Name: snippet Type: TABLE Owner: tperdue
--

CREATE TABLE snippet (
	snippet_id number(*) NOT NULL,
	created_by number(*) DEFAULT '0' NOT NULL,
	name varchar2(4000),
	description varchar2(4000),
	type number(*) DEFAULT '0' NOT NULL,
	language number(*) DEFAULT '0' NOT NULL,
	license varchar2(4000) DEFAULT ' ' NOT NULL,
	category number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_id)
);

--
-- TOC Entry ID 118 (OID 18141593)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE snippet_package_pk_seq START WITH 1;

--
-- TOC Entry ID 252 (OID 18141611)
--
-- Name: snippet_package Type: TABLE Owner: tperdue
--

CREATE TABLE snippet_package (
	snippet_package_id number(*) NOT NULL,
	created_by number(*) DEFAULT '0' NOT NULL,
	name varchar2(4000),
	description varchar2(4000),
	category number(*) DEFAULT '0' NOT NULL,
	language number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_id)
);

--
-- TOC Entry ID 120 (OID 18141648)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE snippet_package_item_pk_seq START WITH 1;

--
-- TOC Entry ID 253 (OID 18141666)
--
-- Name: snippet_package_item Type: TABLE Owner: tperdue
--

CREATE TABLE snippet_package_item (
	snippet_package_item_id number(*) NOT NULL,
	snippet_package_version_id number(*) DEFAULT '0' NOT NULL,
	snippet_version_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_item_id)
);

--
-- TOC Entry ID 122 (OID 18141684)
--
-- Name: snippet_package_ver_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE snippet_package_ver_pk_seq START WITH 1;

--
-- TOC Entry ID 254 (OID 18141702)
--
-- Name: snippet_package_version Type: TABLE Owner: tperdue
--

CREATE TABLE snippet_package_version (
	snippet_package_version_id number(*) NOT NULL,
	snippet_package_id number(*) DEFAULT '0' NOT NULL,
	changes varchar2(4000),
	version varchar2(4000),
	submitted_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_version_id)
);

--
-- TOC Entry ID 124 (OID 18141739)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE snippet_version_pk_seq START WITH 1;

--
-- TOC Entry ID 255 (OID 18141757)
--
-- Name: snippet_version Type: TABLE Owner: tperdue
--

CREATE TABLE snippet_version (
	snippet_version_id number(*) NOT NULL,
	snippet_id number(*) DEFAULT '0' NOT NULL,
	changes varchar2(4000),
	version varchar2(4000),
	submitted_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL,
	code varchar2(4000),
	PRIMARY KEY (snippet_version_id)
);

--
-- TOC Entry ID 256 (OID 18141795)
--
-- Name: stats_agg_logo_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_logo_by_day (
	day number(*),
	count integer
);

--
-- TOC Entry ID 257 (OID 18141806)
--
-- Name: stats_agg_logo_by_group Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_logo_by_group (
	day number(*),
	group_id number(*),
	count integer
);

--
-- TOC Entry ID 258 (OID 18141818)
--
-- Name: stats_agg_pages_by_browser Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_pages_by_browser (
	browser character varying(8),
	count integer
);

--
-- TOC Entry ID 259 (OID 18141829)
--
-- Name: stats_agg_pages_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_pages_by_day (
	day number(*) DEFAULT '0' NOT NULL,
	count number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 260 (OID 18141842)
--
-- Name: stats_agg_pages_by_day_old Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_pages_by_day_old (
	day number(*),
	count integer
);

--
-- TOC Entry ID 261 (OID 18141853)
--
-- Name: stats_agg_site_by_day Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_site_by_day (
	day number(*) DEFAULT '0' NOT NULL,
	count number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 262 (OID 18141866)
--
-- Name: stats_agg_site_by_group Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agg_site_by_group (
	day number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	count number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 263 (OID 18141881)
--
-- Name: stats_agr_filerelease Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agr_filerelease (
	filerelease_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 264 (OID 18141896)
--
-- Name: stats_agr_project Type: TABLE Owner: tperdue
--

CREATE TABLE stats_agr_project (
	group_id number(*) DEFAULT '0' NOT NULL,
	group_ranking number(*) DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers number(*) DEFAULT '0' NOT NULL,
	file_releases number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL,
	site_views number(*) DEFAULT '0' NOT NULL,
	logo_views number(*) DEFAULT '0' NOT NULL,
	msg_posted number(*) DEFAULT '0' NOT NULL,
	msg_uniq_auth number(*) DEFAULT '0' NOT NULL,
	bugs_opened number(*) DEFAULT '0' NOT NULL,
	bugs_closed number(*) DEFAULT '0' NOT NULL,
	support_opened number(*) DEFAULT '0' NOT NULL,
	support_closed number(*) DEFAULT '0' NOT NULL,
	patches_opened number(*) DEFAULT '0' NOT NULL,
	patches_closed number(*) DEFAULT '0' NOT NULL,
	tasks_opened number(*) DEFAULT '0' NOT NULL,
	tasks_closed number(*) DEFAULT '0' NOT NULL,
	help_requests number(*) DEFAULT '0' NOT NULL,
	cvs_checkouts number(*) DEFAULT '0' NOT NULL,
	cvs_commits number(*) DEFAULT '0' NOT NULL,
	cvs_adds number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 265 (OID 18141949)
--
-- Name: stats_ftp_downloads Type: TABLE Owner: tperdue
--

CREATE TABLE stats_ftp_downloads (
	day number(*) DEFAULT '0' NOT NULL,
	filerelease_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 266 (OID 18141966)
--
-- Name: stats_http_downloads Type: TABLE Owner: tperdue
--

CREATE TABLE stats_http_downloads (
	day number(*) DEFAULT '0' NOT NULL,
	filerelease_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 267 (OID 18141983)
--
-- Name: stats_project Type: TABLE Owner: tperdue
--

CREATE TABLE stats_project (
	month number(*) DEFAULT '0' NOT NULL,
	week number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	group_ranking number(*) DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers number(*) DEFAULT '0' NOT NULL,
	file_releases number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL,
	site_views number(*) DEFAULT '0' NOT NULL,
	subdomain_views number(*) DEFAULT '0' NOT NULL,
	msg_posted number(*) DEFAULT '0' NOT NULL,
	msg_uniq_auth number(*) DEFAULT '0' NOT NULL,
	bugs_opened number(*) DEFAULT '0' NOT NULL,
	bugs_closed number(*) DEFAULT '0' NOT NULL,
	support_opened number(*) DEFAULT '0' NOT NULL,
	support_closed number(*) DEFAULT '0' NOT NULL,
	patches_opened number(*) DEFAULT '0' NOT NULL,
	patches_closed number(*) DEFAULT '0' NOT NULL,
	tasks_opened number(*) DEFAULT '0' NOT NULL,
	tasks_closed number(*) DEFAULT '0' NOT NULL,
	help_requests number(*) DEFAULT '0' NOT NULL,
	cvs_checkouts number(*) DEFAULT '0' NOT NULL,
	cvs_commits number(*) DEFAULT '0' NOT NULL,
	cvs_adds number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 268 (OID 18142042)
--
-- Name: stats_project_tmp Type: TABLE Owner: tperdue
--

CREATE TABLE stats_project_tmp (
	month number(*) DEFAULT '0' NOT NULL,
	week number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	group_ranking number(*) DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers number(*) DEFAULT '0' NOT NULL,
	file_releases number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL,
	site_views number(*) DEFAULT '0' NOT NULL,
	subdomain_views number(*) DEFAULT '0' NOT NULL,
	msg_posted number(*) DEFAULT '0' NOT NULL,
	msg_uniq_auth number(*) DEFAULT '0' NOT NULL,
	bugs_opened number(*) DEFAULT '0' NOT NULL,
	bugs_closed number(*) DEFAULT '0' NOT NULL,
	support_opened number(*) DEFAULT '0' NOT NULL,
	support_closed number(*) DEFAULT '0' NOT NULL,
	patches_opened number(*) DEFAULT '0' NOT NULL,
	patches_closed number(*) DEFAULT '0' NOT NULL,
	tasks_opened number(*) DEFAULT '0' NOT NULL,
	tasks_closed number(*) DEFAULT '0' NOT NULL,
	help_requests number(*) DEFAULT '0' NOT NULL,
	cvs_checkouts number(*) DEFAULT '0' NOT NULL,
	cvs_commits number(*) DEFAULT '0' NOT NULL,
	cvs_adds number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 269 (OID 18142101)
--
-- Name: stats_site Type: TABLE Owner: tperdue
--

CREATE TABLE stats_site (
	month number(*) DEFAULT '0' NOT NULL,
	week number(*) DEFAULT '0' NOT NULL,
	day number(*) DEFAULT '0' NOT NULL,
	site_views number(*) DEFAULT '0' NOT NULL,
	subdomain_views number(*) DEFAULT '0' NOT NULL,
	downloads number(*) DEFAULT '0' NOT NULL,
	uniq_users number(*) DEFAULT '0' NOT NULL,
	sessions number(*) DEFAULT '0' NOT NULL,
	total_users number(*) DEFAULT '0' NOT NULL,
	new_users number(*) DEFAULT '0' NOT NULL,
	new_projects number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 126 (OID 18142132)
--
-- Name: support_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_pk_seq START WITH 1;

--
-- TOC Entry ID 270 (OID 18142150)
--
-- Name: support Type: TABLE Owner: tperdue
--

CREATE TABLE support (
	support_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	support_status_id number(*) DEFAULT '0' NOT NULL,
	support_category_id number(*) DEFAULT '0' NOT NULL,
	priority number(*) DEFAULT '0' NOT NULL,
	submitted_by number(*) DEFAULT '0' NOT NULL,
	assigned_to number(*) DEFAULT '0' NOT NULL,
	open_date number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000),
	close_date number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (support_id)
);

--
-- TOC Entry ID 128 (OID 18142196)
--
-- Name: support_canned_res_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_canned_res_pk_seq START WITH 1;

--
-- TOC Entry ID 271 (OID 18142214)
--
-- Name: support_canned_responses Type: TABLE Owner: tperdue
--

CREATE TABLE support_canned_responses (
	support_canned_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	title varchar2(4000),
	body varchar2(4000),
	PRIMARY KEY (support_canned_id)
);

--
-- TOC Entry ID 130 (OID 18142247)
--
-- Name: support_category_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_category_pk_seq START WITH 1;

--
-- TOC Entry ID 272 (OID 18142265)
--
-- Name: support_category Type: TABLE Owner: tperdue
--

CREATE TABLE support_category (
	support_category_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	category_name varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (support_category_id)
);

--
-- TOC Entry ID 132 (OID 18142298)
--
-- Name: support_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_history_pk_seq START WITH 1;

--
-- TOC Entry ID 273 (OID 18142316)
--
-- Name: support_history Type: TABLE Owner: tperdue
--

CREATE TABLE support_history (
	support_history_id number(*) NOT NULL,
	support_id number(*) DEFAULT '0' NOT NULL,
	field_name varchar2(4000) DEFAULT ' ' NOT NULL,
	old_value varchar2(4000) DEFAULT ' ' NOT NULL,
	mod_by number(*) DEFAULT '0' NOT NULL,
	date1 number(*),
	PRIMARY KEY (support_history_id)
);

--
-- TOC Entry ID 134 (OID 18142354)
--
-- Name: support_messages_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_messages_pk_seq START WITH 1;

--
-- TOC Entry ID 274 (OID 18142372)
--
-- Name: support_messages Type: TABLE Owner: tperdue
--

CREATE TABLE support_messages (
	support_message_id number(*) NOT NULL,
	support_id number(*) DEFAULT '0' NOT NULL,
	from_email varchar2(4000),
	date1 number(*) DEFAULT '0' NOT NULL,
	body varchar2(4000),
	PRIMARY KEY (support_message_id)
);

--
-- TOC Entry ID 136 (OID 18142407)
--
-- Name: support_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE support_status_pk_seq START WITH 1;

--
-- TOC Entry ID 275 (OID 18142425)
--
-- Name: support_status Type: TABLE Owner: tperdue
--

CREATE TABLE support_status (
	support_status_id number(*) NOT NULL,
	status_name varchar2(4000),
	PRIMARY KEY (support_status_id)
);

--
-- TOC Entry ID 138 (OID 18142455)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE supported_languages_pk_seq START WITH 1;

--
-- TOC Entry ID 276 (OID 18142473)
--
-- Name: supported_languages Type: TABLE Owner: tperdue
--

CREATE TABLE supported_languages (
	language_id number(*) NOT NULL,
	name varchar2(4000),
	filename varchar2(4000),
	classname varchar2(4000),
	language_code character(2),
	PRIMARY KEY (language_id)
);

--
-- TOC Entry ID 140 (OID 18142506)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE survey_question_types_pk_seq START WITH 1;

--
-- TOC Entry ID 277 (OID 18142524)
--
-- Name: survey_question_types Type: TABLE Owner: tperdue
--

CREATE TABLE survey_question_types (
	id number(*) NOT NULL,
	type varchar2(4000) DEFAULT ' ' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 142 (OID 18142555)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE survey_questions_pk_seq START WITH 1;

--
-- TOC Entry ID 278 (OID 18142573)
--
-- Name: survey_questions Type: TABLE Owner: tperdue
--

CREATE TABLE survey_questions (
	question_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	question varchar2(4000) DEFAULT ' ' NOT NULL,
	question_type number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (question_id)
);

--
-- TOC Entry ID 279 (OID 18142608)
--
-- Name: survey_rating_aggregate Type: TABLE Owner: tperdue
--

CREATE TABLE survey_rating_aggregate (
	type number(*) DEFAULT '0' NOT NULL,
	id number(*) DEFAULT '0' NOT NULL,
	response double precision DEFAULT '0' NOT NULL,
	count number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 280 (OID 18142625)
--
-- Name: survey_rating_response Type: TABLE Owner: tperdue
--

CREATE TABLE survey_rating_response (
	user_id number(*) DEFAULT '0' NOT NULL,
	type number(*) DEFAULT '0' NOT NULL,
	id number(*) DEFAULT '0' NOT NULL,
	response number(*) DEFAULT '0' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 281 (OID 18142644)
--
-- Name: survey_responses Type: TABLE Owner: tperdue
--

CREATE TABLE survey_responses (
	user_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	survey_id number(*) DEFAULT '0' NOT NULL,
	question_id number(*) DEFAULT '0' NOT NULL,
	response varchar2(4000) DEFAULT ' ' NOT NULL,
	date1 number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 144 (OID 18142680)
--
-- Name: surveys_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE surveys_pk_seq START WITH 1;

--
-- TOC Entry ID 282 (OID 18142698)
--
-- Name: surveys Type: TABLE Owner: tperdue
--

CREATE TABLE surveys (
	survey_id number(*) NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	survey_title varchar2(4000) DEFAULT ' ' NOT NULL,
	survey_questions varchar2(4000) DEFAULT ' ' NOT NULL,
	is_active number(*) DEFAULT '1' NOT NULL,
	PRIMARY KEY (survey_id)
);

--
-- TOC Entry ID 146 (OID 18142735)
--
-- Name: system_history_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE system_history_pk_seq START WITH 1;

--
-- TOC Entry ID 148 (OID 18142787)
--
-- Name: system_machines_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE system_machines_pk_seq START WITH 1;

--
-- TOC Entry ID 150 (OID 18142836)
--
-- Name: system_news_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE system_news_pk_seq START WITH 1;

--
-- TOC Entry ID 152 (OID 18142895)
--
-- Name: system_services_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE system_services_pk_seq START WITH 1;

--
-- TOC Entry ID 154 (OID 18142944)
--
-- Name: system_status_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE system_status_pk_seq START WITH 1;

--
-- TOC Entry ID 283 (OID 18142992)
--
-- Name: theme_prefs Type: TABLE Owner: tperdue
--

CREATE TABLE theme_prefs (
	user_id number(*) DEFAULT '0' NOT NULL,
	user_theme number(*) DEFAULT '0' NOT NULL,
	body_font character(80) DEFAULT '',
	body_size character(5) DEFAULT '',
	titlebar_font character(80) DEFAULT '',
	titlebar_size character(5) DEFAULT '',
	color_titlebar_back character(7) DEFAULT '',
	color_ltback1 character(7) DEFAULT '',
	PRIMARY KEY (user_id)
);

--
-- TOC Entry ID 156 (OID 18143020)
--
-- Name: themes_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE themes_pk_seq START WITH 1;

--
-- TOC Entry ID 284 (OID 18143038)
--
-- Name: themes Type: TABLE Owner: tperdue
--

CREATE TABLE themes (
	theme_id number(*) NOT NULL,
	dirname character varying(80),
	fullname character varying(80),
	PRIMARY KEY (theme_id)
);

--
-- TOC Entry ID 285 (OID 18143054)
--
-- Name: tmp_projs_releases_tmp Type: TABLE Owner: tperdue
--

CREATE TABLE tmp_projs_releases_tmp (
	year number(*) DEFAULT '0' NOT NULL,
	month number(*) DEFAULT '0' NOT NULL,
	total_proj number(*) DEFAULT '0' NOT NULL,
	total_releases number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 286 (OID 18143071)
--
-- Name: top_group Type: TABLE Owner: tperdue
--

CREATE TABLE top_group (
	group_id number(*) DEFAULT '0' NOT NULL,
	group_name character varying(40),
	downloads_all number(*) DEFAULT '0' NOT NULL,
	rank_downloads_all number(*) DEFAULT '0' NOT NULL,
	rank_downloads_all_old number(*) DEFAULT '0' NOT NULL,
	downloads_week number(*) DEFAULT '0' NOT NULL,
	rank_downloads_week number(*) DEFAULT '0' NOT NULL,
	rank_downloads_week_old number(*) DEFAULT '0' NOT NULL,
	userrank number(*) DEFAULT '0' NOT NULL,
	rank_userrank number(*) DEFAULT '0' NOT NULL,
	rank_userrank_old number(*) DEFAULT '0' NOT NULL,
	forumposts_week number(*) DEFAULT '0' NOT NULL,
	rank_forumposts_week number(*) DEFAULT '0' NOT NULL,
	rank_forumposts_week_old number(*) DEFAULT '0' NOT NULL,
	pageviews_proj number(*) DEFAULT '0' NOT NULL,
	rank_pageviews_proj number(*) DEFAULT '0' NOT NULL,
	rank_pageviews_proj_old number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 158 (OID 18143113)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE trove_cat_pk_seq START WITH 1;

--
-- TOC Entry ID 287 (OID 18143131)
--
-- Name: trove_cat Type: TABLE Owner: tperdue
--

CREATE TABLE trove_cat (
	trove_cat_id number(*) NOT NULL,
	version number(*) DEFAULT '0' NOT NULL,
	parent number(*) DEFAULT '0' NOT NULL,
	root_parent number(*) DEFAULT '0' NOT NULL,
	shortname character varying(80),
	fullname character varying(80),
	description character varying(255),
	count_subcat number(*) DEFAULT '0' NOT NULL,
	count_subproj number(*) DEFAULT '0' NOT NULL,
	fullpath varchar2(4000) DEFAULT ' ' NOT NULL,
	fullpath_ids varchar2(4000),
	PRIMARY KEY (trove_cat_id)
);

--
-- TOC Entry ID 160 (OID 18143176)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE trove_group_link_pk_seq START WITH 1;

--
-- TOC Entry ID 288 (OID 18143194)
--
-- Name: trove_group_link Type: TABLE Owner: tperdue
--

CREATE TABLE trove_group_link (
	trove_group_id number(*) NOT NULL,
	trove_cat_id number(*) DEFAULT '0' NOT NULL,
	trove_cat_version number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	trove_cat_root number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (trove_group_id)
);

--
-- TOC Entry ID 162 (OID 18143216)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE trove_treesums_pk_seq START WITH 1;

--
-- TOC Entry ID 289 (OID 18143234)
--
-- Name: trove_treesums Type: TABLE Owner: tperdue
--

CREATE TABLE trove_treesums (
	trove_treesums_id number(*) NOT NULL,
	trove_cat_id number(*) DEFAULT '0' NOT NULL,
	limit_1 number(*) DEFAULT '0' NOT NULL,
	subprojects number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (trove_treesums_id)
);

--
-- TOC Entry ID 164 (OID 18143286)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_bookmarks_pk_seq START WITH 1;

--
-- TOC Entry ID 290 (OID 18143304)
--
-- Name: user_bookmarks Type: TABLE Owner: tperdue
--

CREATE TABLE user_bookmarks (
	bookmark_id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	bookmark_url varchar2(4000),
	bookmark_title varchar2(4000),
	PRIMARY KEY (bookmark_id)
);

--
-- TOC Entry ID 166 (OID 18143337)
--
-- Name: user_diary_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_diary_pk_seq START WITH 1;

--
-- TOC Entry ID 291 (OID 18143355)
--
-- Name: user_diary Type: TABLE Owner: tperdue
--

CREATE TABLE user_diary (
	id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	date_posted number(*) DEFAULT '0' NOT NULL,
	summary varchar2(4000),
	details varchar2(4000),
	is_public number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--
-- TOC Entry ID 168 (OID 18143392)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_diary_monitor_pk_seq START WITH 1;

--
-- TOC Entry ID 292 (OID 18143410)
--
-- Name: user_diary_monitor Type: TABLE Owner: tperdue
--

CREATE TABLE user_diary_monitor (
	monitor_id number(*) NOT NULL,
	monitored_user number(*) DEFAULT '0' NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (monitor_id)
);

--
-- TOC Entry ID 170 (OID 18143428)
--
-- Name: user_group_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_group_pk_seq START WITH 1;

--
-- TOC Entry ID 293 (OID 18143446)
--
-- Name: user_group Type: TABLE Owner: tperdue
--

CREATE TABLE user_group (
	user_group_id number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	admin_flags character(16) DEFAULT ' ' NOT NULL,
	bug_flags number(*) DEFAULT '0' NOT NULL,
	forum_flags number(*) DEFAULT '0' NOT NULL,
	project_flags number(*) DEFAULT '2' NOT NULL,
	patch_flags number(*) DEFAULT '1' NOT NULL,
	support_flags number(*) DEFAULT '1' NOT NULL,
	doc_flags number(*) DEFAULT '0' NOT NULL,
	cvs_flags number(*) DEFAULT '1' NOT NULL,
	member_role number(*) DEFAULT '100' NOT NULL,
	release_flags number(*) DEFAULT '0' NOT NULL,
	PRIMARY KEY (user_group_id)
);

--
-- TOC Entry ID 172 (OID 18143484)
--
-- Name: user_metric_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_metric_pk_seq START WITH 1;

--
-- TOC Entry ID 294 (OID 18143502)
--
-- Name: user_metric Type: TABLE Owner: tperdue
--

CREATE TABLE user_metric (
	ranking number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	times_ranked number(*) DEFAULT '0' NOT NULL,
	avg_raters_importance double precision DEFAULT '0.00000000' NOT NULL,
	avg_rating double precision DEFAULT '0.00000000' NOT NULL,
	metric double precision DEFAULT '0.00000000' NOT NULL,
	percentile double precision DEFAULT '0.00000000' NOT NULL,
	importance_factor double precision DEFAULT '0.00000000' NOT NULL,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 174 (OID 18143530)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE user_metric0_pk_seq START WITH 1;

--
-- TOC Entry ID 295 (OID 18143548)
--
-- Name: user_metric0 Type: TABLE Owner: tperdue
--

CREATE TABLE user_metric0 (
	ranking number(*) NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	times_ranked number(*) DEFAULT '0' NOT NULL,
	avg_raters_importance double precision DEFAULT '0.00000000' NOT NULL,
	avg_rating double precision DEFAULT '0.00000000' NOT NULL,
	metric double precision DEFAULT '0.00000000' NOT NULL,
	percentile double precision DEFAULT '0.00000000' NOT NULL,
	importance_factor double precision DEFAULT '0.00000000' NOT NULL,
	PRIMARY KEY (ranking)
);

--
-- TOC Entry ID 296 (OID 18143576)
--
-- Name: user_preferences Type: TABLE Owner: tperdue
--

CREATE TABLE user_preferences (
	user_id number(*) DEFAULT '0' NOT NULL,
	preference_name character varying(20),
	preference_value character varying(20),
	set_date number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 297 (OID 18143591)
--
-- Name: user_ratings Type: TABLE Owner: tperdue
--

CREATE TABLE user_ratings (
	rated_by number(*) DEFAULT '0' NOT NULL,
	user_id number(*) DEFAULT '0' NOT NULL,
	rate_field number(*) DEFAULT '0' NOT NULL,
	rating number(*) DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 176 (OID 18143608)
--
-- Name: users_pk_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE users_pk_seq START WITH 1;

--
-- TOC Entry ID 298 (OID 18143626)
--
-- Name: users Type: TABLE Owner: tperdue
--

CREATE TABLE users (
	user_id number(*) NOT NULL,
	user_name varchar2(200) DEFAULT ' ' NOT NULL,
	email varchar2(4000) DEFAULT ' ' NOT NULL,
	user_pw character varying(32) DEFAULT ' ' NOT NULL,
	realname character varying(32) DEFAULT ' ' NOT NULL,
	status character(1) DEFAULT 'A' NOT NULL,
	shell character varying(20) DEFAULT '/bin/bash' NOT NULL,
	unix_pw character varying(40) DEFAULT ' ' NOT NULL,
	unix_status character(1) DEFAULT 'N' NOT NULL,
	unix_uid number(*) DEFAULT '0' NOT NULL,
	unix_box character varying(10) DEFAULT 'shell1' NOT NULL,
	add_date number(*) DEFAULT '0' NOT NULL,
	confirm_hash character varying(32),
	mail_siteupdates number(*) DEFAULT '0' NOT NULL,
	mail_va number(*) DEFAULT '0' NOT NULL,
	authorized_keys varchar2(4000),
	email_new varchar2(4000),
	people_view_skills number(*) DEFAULT '0' NOT NULL,
	people_resume varchar2(4000) DEFAULT ' ' NOT NULL,
	timezone character varying(64) DEFAULT 'GMT',
	language number(*) DEFAULT '1' NOT NULL,
	PRIMARY KEY (user_id)
);

--
-- TOC Entry ID 178 (OID 27311232)
--
-- Name: unix_uid_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE unix_uid_seq START WITH 1;

--
-- TOC Entry ID 180 (OID 27311250)
--
-- Name: forum_thread_seq Type: SEQUENCE Owner: tperdue
--

CREATE SEQUENCE forum_thread_seq START WITH 1;

--
-- TOC Entry ID 299 (OID 27311451)
--
-- Name: trove_agg Type: TABLE Owner: tperdue
--

CREATE TABLE trove_agg (
	trove_cat_id number(*),
	group_id number(*),
	group_name character varying(40),
	unix_group_name character varying(30),
	status character(1),
	register_time number(*),
	short_description character varying(255),
	percentile double precision,
	ranking integer
);

--
-- TOC Entry ID 302 (OID 30136736)
--
-- Name: activity_log Type: TABLE Owner: www
--

CREATE TABLE activity_log (
	day number(*) DEFAULT '0' NOT NULL,
	hour number(*) DEFAULT '0' NOT NULL,
	group_id number(*) DEFAULT '0' NOT NULL,
	browser character varying(8) DEFAULT 'OTHER' NOT NULL,
	ver double precision DEFAULT '0.00' NOT NULL,
	platform character varying(8) DEFAULT 'OTHER' NOT NULL,
	time number(*) DEFAULT '0' NOT NULL,
	page varchar2(4000),
	type number(*) DEFAULT '0' NOT NULL
);

--\connect - tperdue
--
-- TOC Entry ID 316 (OID 18138445)
--
-- Name: bug_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_group_id on bug ( group_id );

--
-- TOC Entry ID 466 (OID 18138445)
--
-- Name: bug_groupid_statusid Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_groupid_statusid on bug ( group_id, status_id );

--
-- TOC Entry ID 467 (OID 18138445)
--
-- Name: bug_groupid_assign_statusid Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_groupid_assign_statusid on bug ( group_id, assigned_to, status_id );

--
-- TOC Entry ID 317 (OID 18138513)
--
-- Name: bug_bug_dependencies_bug_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_bug_dependencies_bug_id on bug_bug_dependencies ( bug_id );

--
-- TOC Entry ID 318 (OID 18138513)
--
-- Name: bug_bug_dependent_on_task_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_bug_dependent_on_task_id on bug_bug_dependencies ( is_dependent_on_bug_id );

--
-- TOC Entry ID 319 (OID 18138549)
--
-- Name: bug_canned_response_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_canned_response_group_id on bug_canned_responses ( group_id );

--
-- TOC Entry ID 320 (OID 18138600)
--
-- Name: bug_category_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_category_group_id on bug_category ( group_id );

--
-- TOC Entry ID 321 (OID 18138705)
--
-- Name: bug_group_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_group_group_id on bug_group ( group_id );

--
-- TOC Entry ID 322 (OID 18138756)
--
-- Name: bug_history_bug_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_history_bug_id on bug_history ( bug_id );

--
-- TOC Entry ID 323 (OID 18138909)
--
-- Name: bug_task_dependencies_bug_id Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_task_dependencies_bug_id on bug_task_dependencies ( bug_id );

--
-- TOC Entry ID 324 (OID 18138909)
--
-- Name: bug_task_dependent_on_task_i Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_task_dependent_on_task_i on bug_task_dependencies ( is_dependent_on_task_id );

--
-- TOC Entry ID 325 (OID 18138995)
--
-- Name: db_images_group Type: INDEX Owner: tperdue
--

CREATE  INDEX db_images_group on db_images ( group_id );

--
-- TOC Entry ID 326 (OID 18139058)
--
-- Name: doc_group_doc_group Type: INDEX Owner: tperdue
--

CREATE  INDEX doc_group_doc_group on doc_data ( doc_group );

--
-- TOC Entry ID 327 (OID 18139122)
--
-- Name: doc_groups_group Type: INDEX Owner: tperdue
--

CREATE  INDEX doc_groups_group on doc_groups ( group_id );

--
-- TOC Entry ID 328 (OID 18139192)
--
-- Name: filemodule_monitor_id Type: INDEX Owner: tperdue
--

CREATE  INDEX filemodule_monitor_id on filemodule_monitor ( filemodule_id );

--
-- TOC Entry ID 329 (OID 18139228)
--
-- Name: forum_forumid_msgid Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_forumid_msgid on forum ( group_forum_id, msg_id );

--
-- TOC Entry ID 330 (OID 18139228)
--
-- Name: forum_group_forum_id Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_group_forum_id on forum ( group_forum_id );

--
-- TOC Entry ID 331 (OID 18139228)
--
-- Name: forum_forumid_isfollowupto Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_forumid_isfollowupto on forum ( group_forum_id, is_followup_to );

--
-- TOC Entry ID 332 (OID 18139228)
--
-- Name: forum_forumid_threadid_mrec Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_forumid_threadid_mrec on forum ( group_forum_id, thread_id, most_recent_date );

--
-- TOC Entry ID 333 (OID 18139228)
--
-- Name: forum_threadid_isfollowupto Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_threadid_isfollowupto on forum ( thread_id, is_followup_to );

--
-- TOC Entry ID 334 (OID 18139228)
--
-- Name: forum_forumid_isfollto_mrec Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_forumid_isfollto_mrec on forum ( group_forum_id, is_followup_to, most_recent_date );

--
-- TOC Entry ID 335 (OID 18139309)
--
-- Name: forum_group_list_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_group_list_group_id on forum_group_list ( group_id );

--
-- TOC Entry ID 336 (OID 18139366)
--
-- Name: forum_monitor_combo_id Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_monitor_combo_id on forum_monitored_forums ( forum_id, user_id );

--
-- TOC Entry ID 337 (OID 18139366)
--
-- Name: forum_monitor_thread_id Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_monitor_thread_id on forum_monitored_forums ( forum_id );

--
-- TOC Entry ID 338 (OID 18139510)
--
-- Name: foundry_news_foundry_app_d Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_news_foundry_app_d on foundry_news ( foundry_id, is_approved, approve_date );

--
-- TOC Entry ID 339 (OID 18139510)
--
-- Name: foundry_news_foundry_approved Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_news_foundry_approved on foundry_news ( foundry_id, is_approved );

--
-- TOC Entry ID 340 (OID 18139510)
--
-- Name: foundry_news_foundry Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_news_foundry on foundry_news ( foundry_id );

--
-- TOC Entry ID 463 (OID 18139510)
--
-- Name: foundrynews_foundry_date_app Type: INDEX Owner: tperdue
--

CREATE  INDEX foundrynews_foundry_date_app on foundry_news ( foundry_id, approve_date, is_approved );

--
-- TOC Entry ID 341 (OID 18139550)
--
-- Name: foundry_project_group_rank Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_project_group_rank on foundry_preferred_projects ( group_id, rank );

--
-- TOC Entry ID 342 (OID 18139550)
--
-- Name: foundry_project_group Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_project_group on foundry_preferred_projects ( group_id );

--
-- TOC Entry ID 343 (OID 18139588)
--
-- Name: foundry_projects_foundry Type: INDEX Owner: tperdue
--

CREATE  INDEX foundry_projects_foundry on foundry_projects ( foundry_id );

--
-- TOC Entry ID 344 (OID 18139606)
--
-- Name: downloads_http_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX downloads_http_idx on frs_dlstats_agg ( downloads_http );

--
-- TOC Entry ID 345 (OID 18139606)
--
-- Name: downloads_ftp_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX downloads_ftp_idx on frs_dlstats_agg ( downloads_ftp );

--
-- TOC Entry ID 346 (OID 18139606)
--
-- Name: file_id_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX file_id_idx on frs_dlstats_agg ( file_id );

--
-- TOC Entry ID 347 (OID 18139606)
--
-- Name: day_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX day_idx on frs_dlstats_agg ( day );

--
-- TOC Entry ID 348 (OID 18139623)
--
-- Name: dlstats_file_down Type: INDEX Owner: tperdue
--

CREATE  INDEX dlstats_file_down on frs_dlstats_file_agg ( downloads );

--
-- TOC Entry ID 349 (OID 18139623)
--
-- Name: dlstats_file_file_id Type: INDEX Owner: tperdue
--

CREATE  INDEX dlstats_file_file_id on frs_dlstats_file_agg ( file_id );

--
-- TOC Entry ID 350 (OID 18139623)
--
-- Name: dlstats_file_day Type: INDEX Owner: tperdue
--

CREATE  INDEX dlstats_file_day on frs_dlstats_file_agg ( day );

--
-- TOC Entry ID 351 (OID 18139638)
--
-- Name: stats_agr_tmp_fid Type: INDEX Owner: tperdue
--

--CREATE  INDEX stats_agr_tmp_fid on frs_dlstats_filetotal_agg ( file_id );

--
-- TOC Entry ID 352 (OID 18139654)
--
-- Name: frs_dlstats_filet_agg_old_f Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_dlstats_filet_agg_old_f on frs_dlstats_filetotal_agg_old ( file_id );

--
-- TOC Entry ID 303 (OID 18139667)
--
-- Name: frsdlstatsgroupagg_day_dls Type: INDEX Owner: tperdue
--

CREATE  INDEX frsdlstatsgroupagg_day_dls on frs_dlstats_group_agg ( day, downloads );

--
-- TOC Entry ID 353 (OID 18139667)
--
-- Name: group_id_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX group_id_idx on frs_dlstats_group_agg ( group_id );

--
-- TOC Entry ID 355 (OID 18139667)
--
-- Name: frs_dlstats_group_agg_day Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_dlstats_group_agg_day on frs_dlstats_group_agg ( day );

--
-- TOC Entry ID 356 (OID 18139682)
--
-- Name: stats_agr_tmp_gid Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_agr_tmp_gid on frs_dlstats_grouptotal_agg ( group_id );

--
-- TOC Entry ID 357 (OID 18139714)
--
-- Name: frs_file_name Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_file_name on frs_file ( filename );

--
-- TOC Entry ID 358 (OID 18139714)
--
-- Name: frs_file_date Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_file_date on frs_file ( post_date );

--
-- TOC Entry ID 359 (OID 18139714)
--
-- Name: frs_file_processor Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_file_processor on frs_file ( processor_id );

--
-- TOC Entry ID 360 (OID 18139714)
--
-- Name: frs_file_release_id Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_file_release_id on frs_file ( release_id );

--
-- TOC Entry ID 361 (OID 18139714)
--
-- Name: frs_file_type Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_file_type on frs_file ( type_id );

--
-- TOC Entry ID 362 (OID 18139822)
--
-- Name: package_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX package_group_id on frs_package ( group_id );

--
-- TOC Entry ID 363 (OID 18139922)
--
-- Name: frs_release_package Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_release_package on frs_release ( package_id );

--
-- TOC Entry ID 364 (OID 18139922)
--
-- Name: frs_release_date Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_release_date on frs_release ( release_date );

--
-- TOC Entry ID 365 (OID 18139922)
--
-- Name: frs_release_by Type: INDEX Owner: tperdue
--

CREATE  INDEX frs_release_by on frs_release ( released_by );

--
-- TOC Entry ID 366 (OID 18140030)
--
-- Name: group_cvs_history_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX group_cvs_history_group_id on group_cvs_history ( group_id );

--
-- TOC Entry ID 367 (OID 18140030)
--
-- Name: user_name_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX user_name_idx on group_cvs_history ( user_name );

--
-- TOC Entry ID 368 (OID 18140074)
--
-- Name: group_history_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX group_history_group_id on group_history ( group_id );

--
-- TOC Entry ID 369 (OID 18140178)
--
-- Name: groups_unix Type: INDEX Owner: tperdue
--

CREATE  INDEX groups_unix on groups ( unix_group_name );

--
-- TOC Entry ID 370 (OID 18140178)
--
-- Name: groups_type Type: INDEX Owner: tperdue
--

CREATE  INDEX groups_type on groups ( type );

--
-- TOC Entry ID 371 (OID 18140178)
--
-- Name: groups_public Type: INDEX Owner: tperdue
--

CREATE  INDEX groups_public on groups ( is_public );

--
-- TOC Entry ID 372 (OID 18140178)
--
-- Name: groups_status Type: INDEX Owner: tperdue
--

CREATE  INDEX groups_status on groups ( status );

--
-- TOC Entry ID 373 (OID 18140319)
--
-- Name: mail_group_list_group Type: INDEX Owner: tperdue
--

CREATE  INDEX mail_group_list_group on mail_group_list ( group_id );

--
-- TOC Entry ID 374 (OID 18140377)
--
-- Name: news_bytes_group Type: INDEX Owner: tperdue
--

CREATE  INDEX news_bytes_group on news_bytes ( group_id );

--
-- TOC Entry ID 375 (OID 18140377)
--
-- Name: news_bytes_approved Type: INDEX Owner: tperdue
--

CREATE  INDEX news_bytes_approved on news_bytes ( is_approved );

--
-- TOC Entry ID 376 (OID 18140377)
--
-- Name: news_bytes_forum Type: INDEX Owner: tperdue
--

CREATE  INDEX news_bytes_forum on news_bytes ( forum_id );

--
-- TOC Entry ID 464 (OID 18140377)
--
-- Name: news_group_date Type: INDEX Owner: tperdue
--

CREATE  INDEX news_group_date on news_bytes ( group_id, date1 );

--
-- TOC Entry ID 465 (OID 18140377)
--
-- Name: news_approved_date Type: INDEX Owner: tperdue
--

CREATE  INDEX news_approved_date on news_bytes ( is_approved, date1 );

--
-- TOC Entry ID 377 (OID 18140437)
--
-- Name: patch_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_group_id on patch ( group_id );

--
-- TOC Entry ID 451 (OID 18140437)
--
-- Name: patch_groupid_assign_status Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_groupid_assign_status on patch ( group_id, assigned_to, patch_status_id );

--
-- TOC Entry ID 452 (OID 18140437)
--
-- Name: patch_groupid_assignedto Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_groupid_assignedto on patch ( group_id, assigned_to );

--
-- TOC Entry ID 453 (OID 18140437)
--
-- Name: patch_groupid_status Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_groupid_status on patch ( group_id, patch_status_id );

--
-- TOC Entry ID 378 (OID 18140501)
--
-- Name: patch_group_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_group_group_id on patch_category ( group_id );

--
-- TOC Entry ID 379 (OID 18140552)
--
-- Name: patch_history_patch_id Type: INDEX Owner: tperdue
--

CREATE  INDEX patch_history_patch_id on patch_history ( patch_id );

--
-- TOC Entry ID 461 (OID 18140656)
--
-- Name: people_job_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX people_job_group_id on people_job ( group_id );

--
-- TOC Entry ID 380 (OID 18141038)
--
-- Name: project_assign_to_assigned_to Type: INDEX Owner: tperdue
--

CREATE  INDEX project_assign_to_assigned_to on project_assigned_to ( assigned_to_id );

--
-- TOC Entry ID 381 (OID 18141038)
--
-- Name: project_assigned_to_task_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_assigned_to_task_id on project_assigned_to ( project_task_id );

--
-- TOC Entry ID 382 (OID 18141128)
--
-- Name: project_dependent_on_task_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_dependent_on_task_id on project_dependencies ( is_dependent_on_task_id );

--
-- TOC Entry ID 383 (OID 18141128)
--
-- Name: project_dependencies_task_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_dependencies_task_id on project_dependencies ( project_task_id );

--
-- TOC Entry ID 384 (OID 18141164)
--
-- Name: project_group_list_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_group_list_group_id on project_group_list ( group_id );

--
-- TOC Entry ID 385 (OID 18141218)
--
-- Name: project_history_task_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_history_task_id on project_history ( project_task_id );

--
-- TOC Entry ID 386 (OID 18141275)
--
-- Name: project_metric_group Type: INDEX Owner: tperdue
--

CREATE  INDEX project_metric_group on project_metric ( group_id );

--
-- TOC Entry ID 387 (OID 18141430)
--
-- Name: project_task_group_project_id Type: INDEX Owner: tperdue
--

CREATE  INDEX project_task_group_project_id on project_task ( group_project_id );

--
-- TOC Entry ID 454 (OID 18141430)
--
-- Name: projecttask_projid_status Type: INDEX Owner: tperdue
--

CREATE  INDEX projecttask_projid_status on project_task ( group_project_id, status_id );

--
-- TOC Entry ID 354 (OID 18141497)
--
-- Name: projectweeklymetric_ranking Type: INDEX Owner: tperdue
--

--CREATE  INDEX projectweeklymetric_ranking on project_weekly_metric ( ranking );

--
-- TOC Entry ID 388 (OID 18141497)
--
-- Name: project_metric_weekly_group Type: INDEX Owner: tperdue
--

CREATE  INDEX project_metric_weekly_group on project_weekly_metric ( group_id );

--
-- TOC Entry ID 389 (OID 18141514)
--
-- Name: session_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX session1_user_id on session1 ( user_id );

--
-- TOC Entry ID 390 (OID 18141514)
--
-- Name: session_time Type: INDEX Owner: tperdue
--

CREATE  INDEX session1_time on session1 ( time );

--
-- TOC Entry ID 391 (OID 18141552)
--
-- Name: snippet_language Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_language on snippet ( language );

--
-- TOC Entry ID 392 (OID 18141552)
--
-- Name: snippet_category Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_category on snippet ( category );

--
-- TOC Entry ID 393 (OID 18141611)
--
-- Name: snippet_package_language Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_package_language on snippet_package ( language );

--
-- TOC Entry ID 394 (OID 18141611)
--
-- Name: snippet_package_category Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_package_category on snippet_package ( category );

--
-- TOC Entry ID 395 (OID 18141666)
--
-- Name: snippet_package_item_pkg_ver Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_package_item_pkg_ver on snippet_package_item ( snippet_package_version_id );

--
-- TOC Entry ID 396 (OID 18141702)
--
-- Name: snippet_package_version_pkg_id Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_package_version_pkg_id on snippet_package_version ( snippet_package_id );

--
-- TOC Entry ID 397 (OID 18141757)
--
-- Name: snippet_version_snippet_id Type: INDEX Owner: tperdue
--

CREATE  INDEX snippet_version_snippet_id on snippet_version ( snippet_id );

--
-- TOC Entry ID 398 (OID 18141829)
--
-- Name: pages_by_day_day Type: INDEX Owner: tperdue
--

CREATE  INDEX pages_by_day_day on stats_agg_pages_by_day ( day );

--
-- TOC Entry ID 399 (OID 18141881)
--
-- Name: stats_agr_filerelease_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_agr_filerelease_group_id on stats_agr_filerelease ( group_id );

--
-- TOC Entry ID 400 (OID 18141881)
--
-- Name: stats_agr_filerel_filerelea Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_agr_filerel_filerelea on stats_agr_filerelease ( filerelease_id );

--
-- TOC Entry ID 401 (OID 18141896)
--
-- Name: project_agr_log_group Type: INDEX Owner: tperdue
--

CREATE  INDEX project_agr_log_group on stats_agr_project ( group_id );

--
-- TOC Entry ID 402 (OID 18141949)
--
-- Name: ftpdl_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX ftpdl_group_id on stats_ftp_downloads ( group_id );

--
-- TOC Entry ID 403 (OID 18141949)
--
-- Name: ftpdl_fid Type: INDEX Owner: tperdue
--

CREATE  INDEX ftpdl_fid on stats_ftp_downloads ( filerelease_id );

--
-- TOC Entry ID 404 (OID 18141949)
--
-- Name: ftpdl_day Type: INDEX Owner: tperdue
--

CREATE  INDEX ftpdl_day on stats_ftp_downloads ( day );

--
-- TOC Entry ID 405 (OID 18141966)
--
-- Name: httpdl_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX httpdl_group_id on stats_http_downloads ( group_id );

--
-- TOC Entry ID 406 (OID 18141966)
--
-- Name: httpdl_fid Type: INDEX Owner: tperdue
--

CREATE  INDEX httpdl_fid on stats_http_downloads ( filerelease_id );

--
-- TOC Entry ID 407 (OID 18141966)
--
-- Name: httpdl_day Type: INDEX Owner: tperdue
--

CREATE  INDEX httpdl_day on stats_http_downloads ( day );

--
-- TOC Entry ID 408 (OID 18141983)
--
-- Name: archive_project_monthday Type: INDEX Owner: tperdue
--

CREATE  INDEX archive_project_monthday on stats_project ( month, day );

--
-- TOC Entry ID 409 (OID 18141983)
--
-- Name: project_log_group Type: INDEX Owner: tperdue
--

CREATE  INDEX project_log_group on stats_project ( group_id );

--
-- TOC Entry ID 410 (OID 18141983)
--
-- Name: archive_project_week Type: INDEX Owner: tperdue
--

CREATE  INDEX archive_project_week on stats_project ( week );

--
-- TOC Entry ID 411 (OID 18141983)
--
-- Name: archive_project_day Type: INDEX Owner: tperdue
--

CREATE  INDEX archive_project_day on stats_project ( day );

--
-- TOC Entry ID 412 (OID 18141983)
--
-- Name: archive_project_month Type: INDEX Owner: tperdue
--

CREATE  INDEX archive_project_month on stats_project ( month );

--
-- TOC Entry ID 413 (OID 18142042)
--
-- Name: stats_project_tmp_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_project_tmp_group_id on stats_project_tmp ( group_id );

--
-- TOC Entry ID 414 (OID 18142042)
--
-- Name: project_stats_week Type: INDEX Owner: tperdue
--

CREATE  INDEX project_stats_week on stats_project_tmp ( week );

--
-- TOC Entry ID 415 (OID 18142042)
--
-- Name: project_stats_month Type: INDEX Owner: tperdue
--

CREATE  INDEX project_stats_month on stats_project_tmp ( month );

--
-- TOC Entry ID 416 (OID 18142042)
--
-- Name: project_stats_day Type: INDEX Owner: tperdue
--

CREATE  INDEX project_stats_day on stats_project_tmp ( day );

--
-- TOC Entry ID 417 (OID 18142101)
--
-- Name: stats_site_monthday Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_site_monthday on stats_site ( month, day );

--
-- TOC Entry ID 418 (OID 18142101)
--
-- Name: stats_site_week Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_site_week on stats_site ( week );

--
-- TOC Entry ID 419 (OID 18142101)
--
-- Name: stats_site_day Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_site_day on stats_site ( day );

--
-- TOC Entry ID 420 (OID 18142101)
--
-- Name: stats_site_month Type: INDEX Owner: tperdue
--

CREATE  INDEX stats_site_month on stats_site ( month );

--
-- TOC Entry ID 421 (OID 18142150)
--
-- Name: support_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX support_group_id on support ( group_id );

--
-- TOC Entry ID 448 (OID 18142150)
--
-- Name: support_groupid_assignedto Type: INDEX Owner: tperdue
--

CREATE  INDEX support_groupid_assignedto on support ( group_id, assigned_to );

--
-- TOC Entry ID 449 (OID 18142150)
--
-- Name: support_groupid_assign_stat Type: INDEX Owner: tperdue
--

CREATE  INDEX support_groupid_assign_stat on support ( group_id, assigned_to, support_status_id );

--
-- TOC Entry ID 450 (OID 18142150)
--
-- Name: support_groupid_status Type: INDEX Owner: tperdue
--

CREATE  INDEX support_groupid_status on support ( group_id, support_status_id );

--
-- TOC Entry ID 422 (OID 18142214)
--
-- Name: support_canned_res_group_i Type: INDEX Owner: tperdue
--

CREATE  INDEX support_canned_res_group_i on support_canned_responses ( group_id );

--
-- TOC Entry ID 423 (OID 18142265)
--
-- Name: support_group_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX support_group_group_id on support_category ( group_id );

--
-- TOC Entry ID 424 (OID 18142316)
--
-- Name: support_history_support_id Type: INDEX Owner: tperdue
--

CREATE  INDEX support_history_support_id on support_history ( support_id );

--
-- TOC Entry ID 425 (OID 18142372)
--
-- Name: support_messages_support_id Type: INDEX Owner: tperdue
--

CREATE  INDEX support_messages_support_id on support_messages ( support_id );

--
-- TOC Entry ID 426 (OID 18142473)
--
-- Name: supported_languages_code Type: INDEX Owner: tperdue
--

CREATE  INDEX supported_languages_code on supported_languages ( language_code );

--
-- TOC Entry ID 427 (OID 18142573)
--
-- Name: survey_questions_group Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_questions_group on survey_questions ( group_id );

--
-- TOC Entry ID 428 (OID 18142608)
--
-- Name: survey_rating_agg_type_id Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_rating_agg_type_id on survey_rating_aggregate ( type, id );

--
-- TOC Entry ID 429 (OID 18142625)
--
-- Name: survey_rating_res_user_ty Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_rating_res_user_ty on survey_rating_response ( user_id, type, id );

--
-- TOC Entry ID 430 (OID 18142625)
--
-- Name: survey_rating_res_type_id Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_rating_res_type_id on survey_rating_response ( type, id );

--
-- TOC Entry ID 431 (OID 18142644)
--
-- Name: survey_responses_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_responses_group_id on survey_responses ( group_id );

--
-- TOC Entry ID 432 (OID 18142644)
--
-- Name: survey_res_user_survey_qu Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_res_user_survey_qu on survey_responses ( user_id, survey_id, question_id );

--
-- TOC Entry ID 433 (OID 18142644)
--
-- Name: survey_responses_user_survey Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_responses_user_survey on survey_responses ( user_id, survey_id );

--
-- TOC Entry ID 434 (OID 18142644)
--
-- Name: survey_res_survey_questio Type: INDEX Owner: tperdue
--

CREATE  INDEX survey_res_survey_questio on survey_responses ( survey_id, question_id );

--
-- TOC Entry ID 435 (OID 18142698)
--
-- Name: surveys_group Type: INDEX Owner: tperdue
--

CREATE  INDEX surveys_group on surveys ( group_id );

--
-- TOC Entry ID 436 (OID 18143071)
--
-- Name: rank_forumposts_week_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX rank_forumposts_week_idx on top_group ( rank_forumposts_week );

--
-- TOC Entry ID 437 (OID 18143071)
--
-- Name: rank_downloads_week_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX rank_downloads_week_idx on top_group ( rank_downloads_week );

--
-- TOC Entry ID 438 (OID 18143071)
--
-- Name: pageviews_proj_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX pageviews_proj_idx on top_group ( pageviews_proj );

--
-- TOC Entry ID 439 (OID 18143071)
--
-- Name: rank_userrank_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX rank_userrank_idx on top_group ( rank_userrank );

--
-- TOC Entry ID 440 (OID 18143071)
--
-- Name: rank_downloads_all_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX rank_downloads_all_idx on top_group ( rank_downloads_all );

--
-- TOC Entry ID 441 (OID 18143131)
--
-- Name: parent_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX parent_idx on trove_cat ( parent );

--
-- TOC Entry ID 442 (OID 18143131)
--
-- Name: root_parent_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX root_parent_idx on trove_cat ( root_parent );

--
-- TOC Entry ID 443 (OID 18143131)
--
-- Name: version_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX version_idx on trove_cat ( version );

--
-- TOC Entry ID 444 (OID 18143194)
--
-- Name: trove_group_link_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX trove_group_link_group_id on trove_group_link ( group_id );

--
-- TOC Entry ID 445 (OID 18143194)
--
-- Name: trove_group_link_cat_id Type: INDEX Owner: tperdue
--

CREATE  INDEX trove_group_link_cat_id on trove_group_link ( trove_cat_id );

--
-- TOC Entry ID 446 (OID 18143304)
--
-- Name: user_bookmark_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_bookmark_user_id on user_bookmarks ( user_id );

--
-- TOC Entry ID 304 (OID 18143355)
--
-- Name: user_diary_user Type: INDEX Owner: tperdue
--

CREATE  INDEX user_diary_user on user_diary ( user_id );

--
-- TOC Entry ID 305 (OID 18143355)
--
-- Name: user_diary_user_date Type: INDEX Owner: tperdue
--

CREATE  INDEX user_diary_user_date on user_diary ( user_id, date_posted );

--
-- TOC Entry ID 306 (OID 18143355)
--
-- Name: user_diary_date Type: INDEX Owner: tperdue
--

CREATE  INDEX user_diary_date on user_diary ( date_posted );

--
-- TOC Entry ID 307 (OID 18143410)
--
-- Name: user_diary_monitor_user Type: INDEX Owner: tperdue
--

CREATE  INDEX user_diary_monitor_user on user_diary_monitor ( user_id );

--
-- TOC Entry ID 308 (OID 18143410)
--
-- Name: user_diary_monitor_monitor_us Type: INDEX Owner: tperdue
--

CREATE  INDEX user_diary_monitor_monitor_us on user_diary_monitor ( monitored_user );

--
-- TOC Entry ID 309 (OID 18143446)
--
-- Name: user_group_group_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_group_group_id on user_group ( group_id );

--
-- TOC Entry ID 310 (OID 18143446)
--
-- Name: bug_flags_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX bug_flags_idx on user_group ( bug_flags );

--
-- TOC Entry ID 311 (OID 18143446)
--
-- Name: project_flags_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX project_flags_idx on user_group ( project_flags );

--
-- TOC Entry ID 312 (OID 18143446)
--
-- Name: user_group_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_group_user_id on user_group ( user_id );

--
-- TOC Entry ID 313 (OID 18143446)
--
-- Name: admin_flags_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX admin_flags_idx on user_group ( admin_flags );

--
-- TOC Entry ID 314 (OID 18143446)
--
-- Name: forum_flags_idx Type: INDEX Owner: tperdue
--

CREATE  INDEX forum_flags_idx on user_group ( forum_flags );

--
-- TOC Entry ID 315 (OID 18143548)
--
-- Name: user_metric0_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_metric0_user_id on user_metric0 ( user_id );

--
-- TOC Entry ID 455 (OID 18143576)
--
-- Name: user_pref_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_pref_user_id on user_preferences ( user_id );

--
-- TOC Entry ID 456 (OID 18143591)
--
-- Name: user_ratings_rated_by Type: INDEX Owner: tperdue
--

CREATE  INDEX user_ratings_rated_by on user_ratings ( rated_by );

--
-- TOC Entry ID 457 (OID 18143591)
--
-- Name: user_ratings_user_id Type: INDEX Owner: tperdue
--

CREATE  INDEX user_ratings_user_id on user_ratings ( user_id );

--
-- TOC Entry ID 447 (OID 18143626)
--
-- Name: users_status Type: INDEX Owner: tperdue
--

CREATE  INDEX users_status on users ( status );

--
-- TOC Entry ID 458 (OID 18143626)
--
-- Name: user_user Type: INDEX Owner: tperdue
--

--CREATE  INDEX user_user on users ( status );

--
-- TOC Entry ID 459 (OID 18143626)
--
-- Name: idx_users_username Type: INDEX Owner: tperdue
--

CREATE  INDEX idx_users_username on users ( user_name );

--
-- TOC Entry ID 462 (OID 18143626)
--
-- Name: users_user_pw Type: INDEX Owner: tperdue
--

CREATE  INDEX users_user_pw on users ( user_pw );

--
-- TOC Entry ID 460 (OID 27311451)
--
-- Name: troveagg_trovecatid Type: INDEX Owner: tperdue
--

CREATE  INDEX troveagg_trovecatid on trove_agg ( trove_cat_id );

--
-- TOC Entry ID 536 (OID 27311269)
--
-- Name: RI_ConstraintTrigger_27311268 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 537 (OID 27311271)
--
-- Name: RI_ConstraintTrigger_27311270 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 538 (OID 27311273)
--
-- Name: RI_ConstraintTrigger_27311272 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 535 (OID 27311275)
--
-- Name: RI_ConstraintTrigger_27311274 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 506 (OID 27311277)
--
-- Name: RI_ConstraintTrigger_27311276 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 505 (OID 27311279)
--
-- Name: RI_ConstraintTrigger_27311278 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 487 (OID 27311281)
--
-- Name: RI_ConstraintTrigger_27311280 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 539 (OID 27311283)
--
-- Name: RI_ConstraintTrigger_27311282 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 540 (OID 27311285)
--
-- Name: RI_ConstraintTrigger_27311284 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 486 (OID 27311287)
--
-- Name: RI_ConstraintTrigger_27311286 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 492 (OID 27311289)
--
-- Name: RI_ConstraintTrigger_27311288 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 491 (OID 27311291)
--
-- Name: RI_ConstraintTrigger_27311290 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 490 (OID 27311293)
--
-- Name: RI_ConstraintTrigger_27311292 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 504 (OID 27311295)
--
-- Name: RI_ConstraintTrigger_27311294 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 503 (OID 27311297)
--
-- Name: RI_ConstraintTrigger_27311296 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 479 (OID 27311299)
--
-- Name: RI_ConstraintTrigger_27311298 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 502 (OID 27311301)
--
-- Name: RI_ConstraintTrigger_27311300 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 501 (OID 27311303)
--
-- Name: RI_ConstraintTrigger_27311302 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 476 (OID 27311305)
--
-- Name: RI_ConstraintTrigger_27311304 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 500 (OID 27311307)
--
-- Name: RI_ConstraintTrigger_27311306 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 499 (OID 27311309)
--
-- Name: RI_ConstraintTrigger_27311308 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 473 (OID 27311311)
--
-- Name: RI_ConstraintTrigger_27311310 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 541 (OID 27311313)
--
-- Name: RI_ConstraintTrigger_27311312 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 542 (OID 27311315)
--
-- Name: RI_ConstraintTrigger_27311314 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 472 (OID 27311317)
--
-- Name: RI_ConstraintTrigger_27311316 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 543 (OID 27311319)
--
-- Name: RI_ConstraintTrigger_27311318 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 544 (OID 27311321)
--
-- Name: RI_ConstraintTrigger_27311320 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 471 (OID 27311323)
--
-- Name: RI_ConstraintTrigger_27311322 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 483 (OID 27311325)
--
-- Name: RI_ConstraintTrigger_27311324 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 482 (OID 27311327)
--
-- Name: RI_ConstraintTrigger_27311326 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 470 (OID 27311329)
--
-- Name: RI_ConstraintTrigger_27311328 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 475 (OID 27311331)
--
-- Name: RI_ConstraintTrigger_27311330 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 474 (OID 27311333)
--
-- Name: RI_ConstraintTrigger_27311332 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 469 (OID 27311335)
--
-- Name: RI_ConstraintTrigger_27311334 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 481 (OID 27311337)
--
-- Name: RI_ConstraintTrigger_27311336 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 480 (OID 27311339)
--
-- Name: RI_ConstraintTrigger_27311338 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 468 (OID 27311341)
--
-- Name: RI_ConstraintTrigger_27311340 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 478 (OID 27311343)
--
-- Name: RI_ConstraintTrigger_27311342 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 477 (OID 27311345)
--
-- Name: RI_ConstraintTrigger_27311344 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 485 (OID 27311347)
--
-- Name: RI_ConstraintTrigger_27311346 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 545 (OID 27311349)
--
-- Name: RI_ConstraintTrigger_27311348 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 546 (OID 27311351)
--
-- Name: RI_ConstraintTrigger_27311350 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 484 (OID 27311353)
--
-- Name: RI_ConstraintTrigger_27311352 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 489 (OID 27311355)
--
-- Name: RI_ConstraintTrigger_27311354 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 488 (OID 27311357)
--
-- Name: RI_ConstraintTrigger_27311356 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 518 (OID 27311359)
--
-- Name: RI_ConstraintTrigger_27311358 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 498 (OID 27311361)
--
-- Name: RI_ConstraintTrigger_27311360 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 497 (OID 27311363)
--
-- Name: RI_ConstraintTrigger_27311362 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 523 (OID 27311365)
--
-- Name: RI_ConstraintTrigger_27311364 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 517 (OID 27311367)
--
-- Name: RI_ConstraintTrigger_27311366 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 516 (OID 27311369)
--
-- Name: RI_ConstraintTrigger_27311368 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 522 (OID 27311371)
--
-- Name: RI_ConstraintTrigger_27311370 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 547 (OID 27311373)
--
-- Name: RI_ConstraintTrigger_27311372 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 548 (OID 27311375)
--
-- Name: RI_ConstraintTrigger_27311374 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 521 (OID 27311377)
--
-- Name: RI_ConstraintTrigger_27311376 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 520 (OID 27311379)
--
-- Name: RI_ConstraintTrigger_27311378 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 519 (OID 27311381)
--
-- Name: RI_ConstraintTrigger_27311380 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 510 (OID 27311383)
--
-- Name: RI_ConstraintTrigger_27311382 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 515 (OID 27311385)
--
-- Name: RI_ConstraintTrigger_27311384 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 514 (OID 27311387)
--
-- Name: RI_ConstraintTrigger_27311386 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 509 (OID 27311389)
--
-- Name: RI_ConstraintTrigger_27311388 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 513 (OID 27311391)
--
-- Name: RI_ConstraintTrigger_27311390 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 512 (OID 27311393)
--
-- Name: RI_ConstraintTrigger_27311392 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 508 (OID 27311395)
--
-- Name: RI_ConstraintTrigger_27311394 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 549 (OID 27311397)
--
-- Name: RI_ConstraintTrigger_27311396 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 550 (OID 27311399)
--
-- Name: RI_ConstraintTrigger_27311398 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 507 (OID 27311401)
--
-- Name: RI_ConstraintTrigger_27311400 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 551 (OID 27311403)
--
-- Name: RI_ConstraintTrigger_27311402 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 552 (OID 27311405)
--
-- Name: RI_ConstraintTrigger_27311404 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 511 (OID 27311407)
--
-- Name: RI_ConstraintTrigger_27311406 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 496 (OID 27311409)
--
-- Name: RI_ConstraintTrigger_27311408 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 495 (OID 27311411)
--
-- Name: RI_ConstraintTrigger_27311410 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 527 (OID 27311413)
--
-- Name: RI_ConstraintTrigger_27311412 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 532 (OID 27311415)
--
-- Name: RI_ConstraintTrigger_27311414 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 531 (OID 27311417)
--
-- Name: RI_ConstraintTrigger_27311416 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 526 (OID 27311419)
--
-- Name: RI_ConstraintTrigger_27311418 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 530 (OID 27311421)
--
-- Name: RI_ConstraintTrigger_27311420 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 529 (OID 27311423)
--
-- Name: RI_ConstraintTrigger_27311422 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 525 (OID 27311425)
--
-- Name: RI_ConstraintTrigger_27311424 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 553 (OID 27311427)
--
-- Name: RI_ConstraintTrigger_27311426 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 554 (OID 27311429)
--
-- Name: RI_ConstraintTrigger_27311428 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 524 (OID 27311431)
--
-- Name: RI_ConstraintTrigger_27311430 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 555 (OID 27311433)
--
-- Name: RI_ConstraintTrigger_27311432 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 556 (OID 27311435)
--
-- Name: RI_ConstraintTrigger_27311434 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 528 (OID 27311437)
--
-- Name: RI_ConstraintTrigger_27311436 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 494 (OID 27311439)
--
-- Name: RI_ConstraintTrigger_27311438 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 493 (OID 27311441)
--
-- Name: RI_ConstraintTrigger_27311440 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 557 (OID 27311443)
--
-- Name: RI_ConstraintTrigger_27311442 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 534 (OID 27311445)
--
-- Name: RI_ConstraintTrigger_27311444 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 533 (OID 27311447)
--
-- Name: RI_ConstraintTrigger_27311446 Type: TRIGGER Owner: tperdue
--


--
-- TOC Entry ID 3 (OID 18138427)
--
-- Name: bug_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_pk_seq;
CREATE SEQUENCE bug_pk_seq START WITH 125359;

--
-- TOC Entry ID 5 (OID 18138495)
--
-- Name: bug_bug_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_bug_dependencies_pk_seq;
CREATE SEQUENCE bug_bug_dependencies_pk_seq START WITH 44691;

--
-- TOC Entry ID 7 (OID 18138531)
--
-- Name: bug_canned_responses_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_canned_responses_pk_seq;
CREATE SEQUENCE bug_canned_responses_pk_seq START WITH 100204;

--
-- TOC Entry ID 9 (OID 18138582)
--
-- Name: bug_category_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_category_pk_seq;
CREATE SEQUENCE bug_category_pk_seq START WITH 5053;

--
-- TOC Entry ID 11 (OID 18138632)
--
-- Name: bug_filter_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_filter_pk_seq;
CREATE SEQUENCE bug_filter_pk_seq START WITH 140;

--
-- TOC Entry ID 13 (OID 18138687)
--
-- Name: bug_group_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_group_pk_seq;
CREATE SEQUENCE bug_group_pk_seq START WITH 2780;

--
-- TOC Entry ID 15 (OID 18138738)
--
-- Name: bug_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_history_pk_seq;
CREATE SEQUENCE bug_history_pk_seq START WITH 106196;

--
-- TOC Entry ID 17 (OID 18138794)
--
-- Name: bug_resolution_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_resolution_pk_seq;
CREATE SEQUENCE bug_resolution_pk_seq START WITH 101;

--
-- TOC Entry ID 19 (OID 18138843)
--
-- Name: bug_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_status_pk_seq;
CREATE SEQUENCE bug_status_pk_seq START WITH 100;

--
-- TOC Entry ID 21 (OID 18138891)
--
-- Name: bug_task_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE bug_task_dependencies_pk_seq;
CREATE SEQUENCE bug_task_dependencies_pk_seq START WITH 44583;

--
-- TOC Entry ID 23 (OID 18138927)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE canned_responses_pk_seq;
CREATE SEQUENCE canned_responses_pk_seq START WITH 5;

--
-- TOC Entry ID 25 (OID 18138977)
--
-- Name: db_images_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE db_images_pk_seq;
CREATE SEQUENCE db_images_pk_seq START WITH 1128;

--
-- TOC Entry ID 27 (OID 18139040)
--
-- Name: doc_data_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE doc_data_pk_seq;
CREATE SEQUENCE doc_data_pk_seq START WITH 2124;

--
-- TOC Entry ID 29 (OID 18139104)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE doc_groups_pk_seq;
CREATE SEQUENCE doc_groups_pk_seq START WITH 1815;

--
-- TOC Entry ID 31 (OID 18139140)
--
-- Name: doc_states_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE doc_states_pk_seq;
CREATE SEQUENCE doc_states_pk_seq START WITH 5;

--
-- TOC Entry ID 33 (OID 18139174)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE filemodule_monitor_pk_seq;
CREATE SEQUENCE filemodule_monitor_pk_seq START WITH 312;

--
-- TOC Entry ID 35 (OID 18139210)
--
-- Name: forum_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE forum_pk_seq;
CREATE SEQUENCE forum_pk_seq START WITH 84486;

--
-- TOC Entry ID 37 (OID 18139291)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE forum_group_list_pk_seq;
CREATE SEQUENCE forum_group_list_pk_seq START WITH 51981;

--
-- TOC Entry ID 39 (OID 18139348)
--
-- Name: forum_monitor_forums_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE forum_monitor_forums_pk_seq;
CREATE SEQUENCE forum_monitor_forums_pk_seq START WITH 14831;

--
-- TOC Entry ID 41 (OID 18139384)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE forum_saved_place_pk_seq;
CREATE SEQUENCE forum_saved_place_pk_seq START WITH 1835;

--
-- TOC Entry ID 43 (OID 18139492)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE foundry_news_pk_seq;
CREATE SEQUENCE foundry_news_pk_seq START WITH 1973;

--
-- TOC Entry ID 45 (OID 18139532)
--
-- Name: foundry_prefer_proj_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE foundry_prefer_proj_pk_seq;
CREATE SEQUENCE foundry_prefer_proj_pk_seq START WITH 165;

--
-- TOC Entry ID 47 (OID 18139570)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE foundry_projects_pk_seq;
CREATE SEQUENCE foundry_projects_pk_seq START WITH 320807;

--
-- TOC Entry ID 49 (OID 18139695)
--
-- Name: frs_file_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_file_pk_seq;
CREATE SEQUENCE frs_file_pk_seq START WITH 29214;

--
-- TOC Entry ID 51 (OID 18139756)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_filetype_pk_seq;
CREATE SEQUENCE frs_filetype_pk_seq START WITH 9999;

--
-- TOC Entry ID 53 (OID 18139804)
--
-- Name: frs_package_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_package_pk_seq;
CREATE SEQUENCE frs_package_pk_seq START WITH 12688;

--
-- TOC Entry ID 55 (OID 18139856)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_processor_pk_seq;
CREATE SEQUENCE frs_processor_pk_seq START WITH 9999;

--
-- TOC Entry ID 57 (OID 18139904)
--
-- Name: frs_release_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_release_pk_seq;
CREATE SEQUENCE frs_release_pk_seq START WITH 17983;

--
-- TOC Entry ID 59 (OID 18139964)
--
-- Name: frs_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE frs_status_pk_seq;
CREATE SEQUENCE frs_status_pk_seq START WITH 3;

--
-- TOC Entry ID 61 (OID 18140012)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE group_cvs_history_pk_seq;
CREATE SEQUENCE group_cvs_history_pk_seq START WITH 1;

--
-- TOC Entry ID 63 (OID 18140056)
--
-- Name: group_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE group_history_pk_seq;
CREATE SEQUENCE group_history_pk_seq START WITH 29283;

--
-- TOC Entry ID 65 (OID 18140112)
--
-- Name: group_type_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE group_type_pk_seq;
CREATE SEQUENCE group_type_pk_seq START WITH 2;

--
-- TOC Entry ID 67 (OID 18140160)
--
-- Name: groups_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE groups_pk_seq;
CREATE SEQUENCE groups_pk_seq START WITH 16379;

--
-- TOC Entry ID 69 (OID 18140301)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE mail_group_list_pk_seq;
CREATE SEQUENCE mail_group_list_pk_seq START WITH 7581;

--
-- TOC Entry ID 71 (OID 18140359)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE news_bytes_pk_seq;
CREATE SEQUENCE news_bytes_pk_seq START WITH 10299;

--
-- TOC Entry ID 73 (OID 18140419)
--
-- Name: patch_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE patch_pk_seq;
CREATE SEQUENCE patch_pk_seq START WITH 102785;

--
-- TOC Entry ID 75 (OID 18140483)
--
-- Name: patch_category_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE patch_category_pk_seq;
CREATE SEQUENCE patch_category_pk_seq START WITH 10607;

--
-- TOC Entry ID 77 (OID 18140534)
--
-- Name: patch_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE patch_history_pk_seq;
CREATE SEQUENCE patch_history_pk_seq START WITH 9813;

--
-- TOC Entry ID 79 (OID 18140590)
--
-- Name: patch_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE patch_status_pk_seq;
CREATE SEQUENCE patch_status_pk_seq START WITH 103;

--
-- TOC Entry ID 81 (OID 18140638)
--
-- Name: people_job_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_job_pk_seq;
CREATE SEQUENCE people_job_pk_seq START WITH 1641;

--
-- TOC Entry ID 83 (OID 18140697)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_job_category_pk_seq;
CREATE SEQUENCE people_job_category_pk_seq START WITH 102;

--
-- TOC Entry ID 85 (OID 18140747)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_job_inventory_pk_seq;
CREATE SEQUENCE people_job_inventory_pk_seq START WITH 1970;

--
-- TOC Entry ID 87 (OID 18140787)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_job_status_pk_seq;
CREATE SEQUENCE people_job_status_pk_seq START WITH 3;

--
-- TOC Entry ID 89 (OID 18140835)
--
-- Name: people_skill_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_skill_pk_seq;
CREATE SEQUENCE people_skill_pk_seq START WITH 33;

--
-- TOC Entry ID 91 (OID 18140884)
--
-- Name: people_skill_inv_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_skill_inv_pk_seq;
CREATE SEQUENCE people_skill_inv_pk_seq START WITH 60179;

--
-- TOC Entry ID 93 (OID 18140924)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_skill_level_pk_seq;
CREATE SEQUENCE people_skill_level_pk_seq START WITH 5;

--
-- TOC Entry ID 95 (OID 18140972)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE people_skill_year_pk_seq;
CREATE SEQUENCE people_skill_year_pk_seq START WITH 5;

--
-- TOC Entry ID 97 (OID 18141020)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_assigned_to_pk_seq;
CREATE SEQUENCE project_assigned_to_pk_seq START WITH 30257;

--
-- TOC Entry ID 99 (OID 18141110)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_dependencies_pk_seq;
CREATE SEQUENCE project_dependencies_pk_seq START WITH 25231;

--
-- TOC Entry ID 101 (OID 18141146)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_group_list_pk_seq;
CREATE SEQUENCE project_group_list_pk_seq START WITH 6360;

--
-- TOC Entry ID 103 (OID 18141200)
--
-- Name: project_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_history_pk_seq;
CREATE SEQUENCE project_history_pk_seq START WITH 27347;

--
-- TOC Entry ID 105 (OID 18141257)
--
-- Name: project_metric_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_metric_pk_seq;
CREATE SEQUENCE project_metric_pk_seq START WITH 13274;

--
-- TOC Entry ID 107 (OID 18141292)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_metric_tmp1_pk_seq;
CREATE SEQUENCE project_metric_tmp1_pk_seq START WITH 13274;

--
-- TOC Entry ID 109 (OID 18141327)
--
-- Name: proj_metric_weekly_tm_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE proj_metric_weekly_tm_pk_seq;
CREATE SEQUENCE proj_metric_weekly_tm_pk_seq START WITH 2213;

--
-- TOC Entry ID 111 (OID 18141363)
--
-- Name: project_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_status_pk_seq;
CREATE SEQUENCE project_status_pk_seq START WITH 100;

--
-- TOC Entry ID 113 (OID 18141412)
--
-- Name: project_task_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_task_pk_seq;
CREATE SEQUENCE project_task_pk_seq START WITH 23295;

--
-- TOC Entry ID 115 (OID 18141479)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE project_weekly_metric_pk_seq;
CREATE SEQUENCE project_weekly_metric_pk_seq START WITH 2213;

--
-- TOC Entry ID 117 (OID 18141534)
--
-- Name: snippet_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE snippet_pk_seq;
CREATE SEQUENCE snippet_pk_seq START WITH 100501;

--
-- TOC Entry ID 119 (OID 18141593)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE snippet_package_pk_seq;
CREATE SEQUENCE snippet_package_pk_seq START WITH 100035;

--
-- TOC Entry ID 121 (OID 18141648)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE snippet_package_item_pk_seq;
CREATE SEQUENCE snippet_package_item_pk_seq START WITH 100100;

--
-- TOC Entry ID 123 (OID 18141684)
--
-- Name: snippet_package_ver_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE snippet_package_ver_pk_seq;
CREATE SEQUENCE snippet_package_ver_pk_seq START WITH 100035;

--
-- TOC Entry ID 125 (OID 18141739)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE snippet_version_pk_seq;
CREATE SEQUENCE snippet_version_pk_seq START WITH 100662;

--
-- TOC Entry ID 127 (OID 18142132)
--
-- Name: support_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_pk_seq;
CREATE SEQUENCE support_pk_seq START WITH 109672;

--
-- TOC Entry ID 129 (OID 18142196)
--
-- Name: support_canned_res_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_canned_res_pk_seq;
CREATE SEQUENCE support_canned_res_pk_seq START WITH 100088;

--
-- TOC Entry ID 131 (OID 18142247)
--
-- Name: support_category_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_category_pk_seq;
CREATE SEQUENCE support_category_pk_seq START WITH 10699;

--
-- TOC Entry ID 133 (OID 18142298)
--
-- Name: support_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_history_pk_seq;
CREATE SEQUENCE support_history_pk_seq START WITH 24027;

--
-- TOC Entry ID 135 (OID 18142354)
--
-- Name: support_messages_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_messages_pk_seq;
CREATE SEQUENCE support_messages_pk_seq START WITH 122077;

--
-- TOC Entry ID 137 (OID 18142407)
--
-- Name: support_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE support_status_pk_seq;
CREATE SEQUENCE support_status_pk_seq START WITH 3;

--
-- TOC Entry ID 139 (OID 18142455)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE supported_languages_pk_seq;
CREATE SEQUENCE supported_languages_pk_seq START WITH 21;

--
-- TOC Entry ID 141 (OID 18142506)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE survey_question_types_pk_seq;
CREATE SEQUENCE survey_question_types_pk_seq START WITH 100;

--
-- TOC Entry ID 143 (OID 18142555)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE survey_questions_pk_seq;
CREATE SEQUENCE survey_questions_pk_seq START WITH 14662;

--
-- TOC Entry ID 145 (OID 18142680)
--
-- Name: surveys_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE surveys_pk_seq;
CREATE SEQUENCE surveys_pk_seq START WITH 11185;

--
-- TOC Entry ID 147 (OID 18142735)
--
-- Name: system_history_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE system_history_pk_seq;
CREATE SEQUENCE system_history_pk_seq START WITH 1;

--
-- TOC Entry ID 149 (OID 18142787)
--
-- Name: system_machines_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE system_machines_pk_seq;
CREATE SEQUENCE system_machines_pk_seq START WITH 1;

--
-- TOC Entry ID 151 (OID 18142836)
--
-- Name: system_news_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE system_news_pk_seq;
CREATE SEQUENCE system_news_pk_seq START WITH 1;

--
-- TOC Entry ID 153 (OID 18142895)
--
-- Name: system_services_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE system_services_pk_seq;
CREATE SEQUENCE system_services_pk_seq START WITH 1;

--
-- TOC Entry ID 155 (OID 18142944)
--
-- Name: system_status_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE system_status_pk_seq;
CREATE SEQUENCE system_status_pk_seq START WITH 1;

--
-- TOC Entry ID 157 (OID 18143020)
--
-- Name: themes_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE themes_pk_seq;
CREATE SEQUENCE themes_pk_seq START WITH 2;

--
-- TOC Entry ID 159 (OID 18143113)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE trove_cat_pk_seq;
CREATE SEQUENCE trove_cat_pk_seq START WITH 281;

--
-- TOC Entry ID 161 (OID 18143176)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE trove_group_link_pk_seq;
CREATE SEQUENCE trove_group_link_pk_seq START WITH 111628;

--
-- TOC Entry ID 163 (OID 18143216)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE trove_treesums_pk_seq;
CREATE SEQUENCE trove_treesums_pk_seq START WITH 765;

--
-- TOC Entry ID 165 (OID 18143286)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_bookmarks_pk_seq;
CREATE SEQUENCE user_bookmarks_pk_seq START WITH 23482;

--
-- TOC Entry ID 167 (OID 18143337)
--
-- Name: user_diary_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_diary_pk_seq;
CREATE SEQUENCE user_diary_pk_seq START WITH 892;

--
-- TOC Entry ID 169 (OID 18143392)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_diary_monitor_pk_seq;
CREATE SEQUENCE user_diary_monitor_pk_seq START WITH 521;

--
-- TOC Entry ID 171 (OID 18143428)
--
-- Name: user_group_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_group_pk_seq;
CREATE SEQUENCE user_group_pk_seq START WITH 27204;

--
-- TOC Entry ID 173 (OID 18143484)
--
-- Name: user_metric_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_metric_pk_seq;
CREATE SEQUENCE user_metric_pk_seq START WITH 115;

--
-- TOC Entry ID 175 (OID 18143530)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE user_metric0_pk_seq;
CREATE SEQUENCE user_metric0_pk_seq START WITH 5;

--
-- TOC Entry ID 177 (OID 18143608)
--
-- Name: users_pk_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE users_pk_seq;
CREATE SEQUENCE users_pk_seq START WITH 120800;

--
-- TOC Entry ID 179 (OID 27311232)
--
-- Name: unix_uid_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE unix_uid_seq;
CREATE SEQUENCE unix_uid_seq START WITH 21044;

--
-- TOC Entry ID 181 (OID 27311250)
--
-- Name: forum_thread_seq Type: SEQUENCE SET Owner: 
--

DROP SEQUENCE forum_thread_seq;
CREATE SEQUENCE forum_thread_seq START WITH 59698;


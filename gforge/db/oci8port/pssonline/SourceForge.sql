-- ORACLE SCRIPT
--   Changes were renaming the "session" table to sf_session
--                renaming "date" columns to sf_date
--                commenting out the create trigger constraints
--                commenting out the select setval statements

--
-- Selected TOC Entries:
--
--\connect - tperdue
--
-- TOC Entry ID 2 (OID 18138427)
--
-- Name: bug_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 182 (OID 18138445)
--
-- Name: bug Type: TABLE 
--

CREATE TABLE bug (
	bug_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	status_id integer DEFAULT '0' NOT NULL,
	priority integer DEFAULT '0' NOT NULL,
	category_id integer DEFAULT '0' NOT NULL,
	submitted_by integer DEFAULT '0' NOT NULL,
	assigned_to integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL,
	summary varchar2(255),
	details varchar2(255),
	close_date integer,
	bug_group_id integer DEFAULT '0' NOT NULL,
	resolution_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_id)
);

CREATE TRIGGER DEFAULT_BUG_ID BEFORE 
    INSERT 
    ON bug REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_pk_seq.nextval into :new.bug_id from dual;
END;
/

--
-- TOC Entry ID 4 (OID 18138495)
--
-- Name: bug_bug_dependencies_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_bug_dependencies_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 183 (OID 18138513)
--
-- Name: bug_bug_dependencies Type: TABLE 
--

CREATE TABLE bug_bug_dependencies (
	bug_depend_id integer DEFAULT '0' NOT NULL,
	bug_id integer DEFAULT '0' NOT NULL,
	is_dependent_on_bug_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_depend_id)
);

CREATE TRIGGER DEFAULT_BUG_DEPEND_ID BEFORE 
    INSERT 
    ON bug_bug_dependencies REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_bug_dependencies_pk_seq.nextval into :new.bug_depend_id from dual;
END;
/

--
-- TOC Entry ID 6 (OID 18138531)
--
-- Name: bug_canned_responses_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_canned_responses_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 184 (OID 18138549)
--
-- Name: bug_canned_responses Type: TABLE 
--

CREATE TABLE bug_canned_responses (
	bug_canned_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	title varchar2(255),
	body varchar2(255),
	PRIMARY KEY (bug_canned_id)
);

CREATE TRIGGER DEFAULT_BUG_CANNED_ID BEFORE 
    INSERT 
    ON bug_canned_responses REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_canned_responses_pk_seq.nextval into :new.bug_canned_id from dual;
END;
/

--
-- TOC Entry ID 8 (OID 18138582)
--
-- Name: bug_category_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_category_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 185 (OID 18138600)
--
-- Name: bug_category Type: TABLE 
--

CREATE TABLE bug_category (
	bug_category_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	category_name varchar2(255),
	PRIMARY KEY (bug_category_id)
);

CREATE TRIGGER DEFAULT_BUG_CATEGORY_ID BEFORE 
    INSERT 
    ON bug_category REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_category_pk_seq.nextval into :new.bug_category_id from dual;
END;
/

--
-- TOC Entry ID 10 (OID 18138632)
--
-- Name: bug_filter_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_filter_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 186 (OID 18138650)
--
-- Name: bug_filter Type: TABLE 
--

CREATE TABLE bug_filter (
	filter_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	sql_clause varchar2(255) DEFAULT '',
	is_active integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (filter_id)
);

CREATE TRIGGER DEFAULT_BUG_FILTER_ID BEFORE 
    INSERT 
    ON bug_filter REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_filter_pk_seq.nextval into :new.filter_id from dual;
END;
/

--
-- TOC Entry ID 12 (OID 18138687)
--
-- Name: bug_group_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_group_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 187 (OID 18138705)
--
-- Name: bug_group Type: TABLE 
--

CREATE TABLE bug_group (
	bug_group_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	group_name varchar2(255) DEFAULT '',
	PRIMARY KEY (bug_group_id)
);

CREATE TRIGGER DEFAULT_BUG_GROUP_ID BEFORE 
    INSERT 
    ON bug_group REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_group_pk_seq.nextval into :new.bug_group_id from dual;
END;
/

--
-- TOC Entry ID 14 (OID 18138738)
--
-- Name: bug_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 188 (OID 18138756)
--
-- Name: bug_history Type: TABLE 
--

CREATE TABLE bug_history (
	bug_history_id integer DEFAULT '0' NOT NULL,
	bug_id integer DEFAULT '0' NOT NULL,
	field_name varchar2(255) DEFAULT '',
	old_value varchar2(255) DEFAULT '',
	mod_by integer DEFAULT '0' NOT NULL,
	sf_date integer,
	PRIMARY KEY (bug_history_id)
);

--nextval('bug_history_pk_seq'::text)
CREATE TRIGGER DEFAULT_BUG_HISTORY_ID BEFORE 
    INSERT 
    ON bug_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_history_pk_seq.nextval into :new.bug_history_id from dual;
END;
/

--
-- TOC Entry ID 16 (OID 18138794)
--
-- Name: bug_resolution_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_resolution_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 189 (OID 18138812)
--
-- Name: bug_resolution Type: TABLE 
--

CREATE TABLE bug_resolution (
	resolution_id integer DEFAULT '0' NOT NULL,
	resolution_name varchar2(255) DEFAULT '',
	PRIMARY KEY (resolution_id)
);

--nextval('bug_resolution_pk_seq'::text)
CREATE TRIGGER DEFAULT_BUG_RESOLUTION_ID BEFORE 
    INSERT 
    ON bug_resolution REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_resolution_pk_seq.nextval into :new.resolution_id from dual;
END;
/

--
-- TOC Entry ID 18 (OID 18138843)
--
-- Name: bug_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 190 (OID 18138861)
--
-- Name: bug_status Type: TABLE 
--

CREATE TABLE bug_status (
	status_id integer DEFAULT '0' NOT NULL,
	status_name varchar2(255),
	PRIMARY KEY (status_id)
);

--nextval('bug_status_pk_seq'::text)
CREATE TRIGGER DEFAULT_BUG_STATUS_ID BEFORE 
    INSERT 
    ON bug_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_status_pk_seq.nextval into :new.status_id from dual;
END;
/

--
-- TOC Entry ID 20 (OID 18138891)
--
-- Name: bug_task_dependencies_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE bug_task_dependencies_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 191 (OID 18138909)
--
-- Name: bug_task_dependencies Type: TABLE 
--

CREATE TABLE bug_task_dependencies (
	bug_depend_id integer DEFAULT '0' NOT NULL,
	bug_id integer DEFAULT '0' NOT NULL,
	is_dependent_on_task_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (bug_depend_id)
);

--nextval('bug_task_dependencies_pk_seq'::text) 
CREATE TRIGGER DEFAULT_BUG_TASK_ID BEFORE 
    INSERT 
    ON bug_task_dependencies REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select bug_task_dependencies_pk_seq.nextval into :new.bug_depend_id from dual;
END;
/

--
-- TOC Entry ID 22 (OID 18138927)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE canned_responses_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 192 (OID 18138946)
--
-- Name: canned_responses Type: TABLE 
--

CREATE TABLE canned_responses (
	response_id integer DEFAULT '0' NOT NULL,
	response_title character varying(25),
	response_text varchar2(255),
	PRIMARY KEY (response_id)
);

--nextval('canned_responses_pk_seq'::text) 
CREATE TRIGGER DEFAULT_CANNED_RESPONSE_ID BEFORE 
    INSERT 
    ON canned_responses REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select canned_responses_pk_seq.nextval into :new.response_id from dual;
END;
/

--
-- TOC Entry ID 24 (OID 18138977)
--
-- Name: db_images_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE db_images_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 193 (OID 18138995)
--
-- Name: db_images Type: TABLE 
--

CREATE TABLE db_images (
	id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	description varchar2(255) DEFAULT '',
	bin_data varchar2(255) DEFAULT '',
	filename varchar2(255) DEFAULT '',
	filesize integer DEFAULT '0' NOT NULL,
	filetype varchar2(255) DEFAULT '',
	width integer DEFAULT '0' NOT NULL,
	height integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--nextval('db_images_pk_seq'::text) 
CREATE TRIGGER DEFAULT_DB_IMAGES_ID BEFORE 
    INSERT 
    ON db_images REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select db_images_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 26 (OID 18139040)
--
-- Name: doc_data_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE doc_data_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 194 (OID 18139058)
--
-- Name: doc_data Type: TABLE 
--

CREATE TABLE doc_data (
	docid integer DEFAULT '0' NOT NULL,
	stateid integer DEFAULT '0' NOT NULL,
	title character varying(255) DEFAULT '',
	data varchar2(255) DEFAULT '',
	updatedate integer DEFAULT '0' NOT NULL,
	createdate integer DEFAULT '0' NOT NULL,
	created_by integer DEFAULT '0' NOT NULL,
	doc_group integer DEFAULT '0' NOT NULL,
	description varchar2(255),
	language_id integer DEFAULT '1' NOT NULL,
	PRIMARY KEY (docid)
);

--nextval('doc_data_pk_seq'::text) 
CREATE TRIGGER DEFAULT_DOC_DATA_ID BEFORE 
    INSERT 
    ON doc_data REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select doc_data_pk_seq.nextval into :new.docid from dual;
END;
/

--
-- TOC Entry ID 28 (OID 18139104)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE doc_groups_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 195 (OID 18139122)
--
-- Name: doc_groups Type: TABLE 
--

CREATE TABLE doc_groups (
	doc_group integer DEFAULT '0' NOT NULL,
	groupname character varying(255) DEFAULT '',
	group_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (doc_group)
);

--nextval('doc_groups_pk_seq'::text) 
CREATE TRIGGER DEFAULT_DOC_GROUP BEFORE 
    INSERT 
    ON doc_groups REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select doc_groups_pk_seq.nextval into :new.doc_group from dual;
END;
/

--
-- TOC Entry ID 30 (OID 18139140)
--
-- Name: doc_states_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE doc_states_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 196 (OID 18139158)
--
-- Name: doc_states Type: TABLE 
--

CREATE TABLE doc_states (
	stateid integer DEFAULT '0' NOT NULL,
	name character varying(255) DEFAULT '',
	PRIMARY KEY (stateid)
);

--nextval('doc_states_pk_seq'::text) 
CREATE TRIGGER DEFAULT_DOC_STATES_ID BEFORE 
    INSERT 
    ON doc_states REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select doc_states_pk_seq.nextval into :new.stateid from dual;
END;
/

--
-- TOC Entry ID 32 (OID 18139174)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE filemodule_monitor_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 197 (OID 18139192)
--
-- Name: filemodule_monitor Type: TABLE 
--

CREATE TABLE filemodule_monitor (
	id integer DEFAULT '0' NOT NULL,
	filemodule_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--nextval('filemodule_monitor_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FILEMODULE_MONITOR_ID BEFORE 
    INSERT 
    ON filemodule_monitor REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select filemodule_monitor_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 34 (OID 18139210)
--
-- Name: forum_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE forum_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 198 (OID 18139228)
--
-- Name: forum Type: TABLE 
--

CREATE TABLE forum (
	msg_id integer DEFAULT '0' NOT NULL,
	group_forum_id integer DEFAULT '0' NOT NULL,
	posted_by integer DEFAULT '0' NOT NULL,
	subject varchar2(255) DEFAULT '',
	body varchar2(255) DEFAULT '',
	sf_date integer DEFAULT '0' NOT NULL,
	is_followup_to integer DEFAULT '0' NOT NULL,
	thread_id integer DEFAULT '0' NOT NULL,
	has_followups integer DEFAULT '0',
	most_recent_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (msg_id)
);

--nextval('forum_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FORUM_MSG_ID BEFORE 
    INSERT 
    ON forum REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select forum_pk_seq.nextval into :new.msg_id from dual;
END;
/


--
-- TOC Entry ID 199 (OID 18139275)
--
-- Name: forum_agg_msg_count Type: TABLE 
--

CREATE TABLE forum_agg_msg_count (
	group_forum_id integer DEFAULT '0' NOT NULL,
	count integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (group_forum_id)
);

--
-- TOC Entry ID 36 (OID 18139291)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE forum_group_list_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 200 (OID 18139309)
--
-- Name: forum_group_list Type: TABLE 
--

CREATE TABLE forum_group_list (
	group_forum_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	forum_name varchar2(255) DEFAULT '',
	is_public integer DEFAULT '0' NOT NULL,
	description varchar2(255),
	allow_anonymous integer DEFAULT '0' NOT NULL,
	send_all_posts_to varchar2(255),
	PRIMARY KEY (group_forum_id)
);

--nextval('forum_group_list_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FORUM_GROUP_ID BEFORE 
    INSERT 
    ON forum_group_list REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select forum_group_list_pk_seq.nextval into :new.group_forum_id from dual;
END;
/

--
-- TOC Entry ID 38 (OID 18139348)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE forum_monitored_forums_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 201 (OID 18139366)
--
-- Name: forum_monitored_forums Type: TABLE 
--

CREATE TABLE forum_monitored_forums (
	monitor_id integer DEFAULT '0' NOT NULL,
	forum_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (monitor_id)
);

--nextval('forum_monitored_forums_pk_seq'::text)
CREATE TRIGGER DEFAULT_FORUM_MON_FORUMS_ID BEFORE 
    INSERT 
    ON forum_monitored_forums REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select forum_monitored_forums_pk_seq.nextval into :new.monitor_id from dual;
END;
/

--
-- TOC Entry ID 40 (OID 18139384)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE forum_saved_place_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 202 (OID 18139402)
--
-- Name: forum_saved_place Type: TABLE 
--

CREATE TABLE forum_saved_place (
	saved_place_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	forum_id integer DEFAULT '0' NOT NULL,
	save_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (saved_place_id)
);

--nextval('forum_saved_place_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FORUM_SAVED_PLACES_ID BEFORE 
    INSERT 
    ON forum_saved_place REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select forum_saved_place_pk_seq.nextval into :new.saved_place_id from dual;
END;
/

--
-- TOC Entry ID 203 (OID 18139454)
--
-- Name: foundry_data Type: TABLE 
--

CREATE TABLE foundry_data (
	foundry_id integer DEFAULT '0' NOT NULL,
	freeform1_html varchar2(255),
	freeform2_html varchar2(255),
	sponsor1_html varchar2(255),
	sponsor2_html varchar2(255),
	guide_image_id integer DEFAULT '0' NOT NULL,
	logo_image_id integer DEFAULT '0' NOT NULL,
	trove_categories varchar2(255),
	PRIMARY KEY (foundry_id)
);

--
-- TOC Entry ID 42 (OID 18139492)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE foundry_news_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 204 (OID 18139510)
--
-- Name: foundry_news Type: TABLE 
--

CREATE TABLE foundry_news (
	foundry_news_id integer DEFAULT '0' NOT NULL,
	foundry_id integer DEFAULT '0' NOT NULL,
	news_id integer DEFAULT '0' NOT NULL,
	approve_date integer DEFAULT '0' NOT NULL,
	is_approved integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (foundry_news_id)
);

--nextval('foundry_news_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FOUNDRY_NEWS_ID BEFORE 
    INSERT 
    ON foundry_news REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select foundry_news_pk_seq.nextval into :new.foundry_news_id from dual;
END;
/

--
-- TOC Entry ID 44 (OID 18139532)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE foundry_preferred_proj_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 205 (OID 18139550)
--
-- Name: foundry_preferred_projects Type: TABLE 
--

CREATE TABLE foundry_preferred_projects (
	foundry_project_id integer DEFAULT '0' NOT NULL,
	foundry_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	rank integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (foundry_project_id)
);

--nextval('foundry_preferred_projec_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FOUNDRY_PREF_PROJS_ID BEFORE 
    INSERT 
    ON foundry_preferred_projects REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select foundry_preferred_proj_pk_seq.nextval into :new.foundry_project_id from dual;
END;
/

--
-- TOC Entry ID 46 (OID 18139570)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE foundry_projects_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 206 (OID 18139588)
--
-- Name: foundry_projects Type: TABLE 
--

CREATE TABLE foundry_projects (
	id integer DEFAULT '0' NOT NULL,
	foundry_id integer DEFAULT '0' NOT NULL,
	project_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--nextval('foundry_projects_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FOUNDRY_PROJS_ID BEFORE 
    INSERT 
    ON foundry_projects REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select foundry_projects_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 207 (OID 18139606)
--
-- Name: frs_dlstats_agg Type: TABLE 
--

CREATE TABLE frs_dlstats_agg (
	file_id integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	downloads_http integer DEFAULT '0' NOT NULL,
	downloads_ftp integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 208 (OID 18139623)
--
-- Name: frs_dlstats_file_agg Type: TABLE 
--

CREATE TABLE frs_dlstats_file_agg (
	file_id integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 209 (OID 18139638)
--
-- Name: frs_dlstats_filetotal_agg Type: TABLE 
--

CREATE TABLE frs_dlstats_filetotal_agg (
	file_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (file_id)
);

--
-- TOC Entry ID 210 (OID 18139654)
--
-- Name: frs_dlstats_filetotal_agg_old Type: TABLE 
--

CREATE TABLE frs_dlstats_filetotal_agg_old (
	file_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 211 (OID 18139667)
--
-- Name: frs_dlstats_group_agg Type: TABLE 
--

CREATE TABLE frs_dlstats_group_agg (
	group_id integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 212 (OID 18139682)
--
-- Name: frs_dlstats_grouptotal_agg Type: TABLE 
--

CREATE TABLE frs_dlstats_grouptotal_agg (
	group_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 48 (OID 18139695)
--
-- Name: frs_file_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_file_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 213 (OID 18139714)
--
-- Name: frs_file Type: TABLE 
--

CREATE TABLE frs_file (
	file_id integer DEFAULT '0' NOT NULL,
	filename varchar2(255),
	release_id integer DEFAULT '0' NOT NULL,
	type_id integer DEFAULT '0' NOT NULL,
	processor_id integer DEFAULT '0' NOT NULL,
	release_time integer DEFAULT '0' NOT NULL,
	file_size integer DEFAULT '0' NOT NULL,
	post_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (file_id)
);

--nextval('frs_file_pk_seq'::text)
CREATE TRIGGER DEFAULT_FRS_FILE_ID BEFORE 
    INSERT 
    ON frs_file REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_file_pk_seq.nextval into :new.file_id from dual;
END;
/

--
-- TOC Entry ID 50 (OID 18139756)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_filetype_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 214 (OID 18139774)
--
-- Name: frs_filetype Type: TABLE 
--

CREATE TABLE frs_filetype (
	type_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (type_id)
);

--nextval('frs_filetype_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FRS_FILETYPE_ID BEFORE 
    INSERT 
    ON frs_filetype REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_filetype_pk_seq.nextval into :new.type_id from dual;
END;
/

--
-- TOC Entry ID 52 (OID 18139804)
--
-- Name: frs_package_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_package_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 215 (OID 18139822)
--
-- Name: frs_package Type: TABLE 
--

CREATE TABLE frs_package (
	package_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	status_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (package_id)
);

--nextval('frs_package_pk_seq'::text)
CREATE TRIGGER DEFAULT_FRS_PACKAGE_ID BEFORE 
    INSERT 
    ON frs_package REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_package_pk_seq.nextval into :new.package_id from dual;
END;
/

--
-- TOC Entry ID 54 (OID 18139856)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_processor_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 216 (OID 18139874)
--
-- Name: frs_processor Type: TABLE 
--

CREATE TABLE frs_processor (
	processor_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (processor_id)
);

--nextval('frs_processor_pk_seq'::text) 
CREATE TRIGGER DEFAULT_FRS_PROCESSOR_ID BEFORE 
    INSERT 
    ON frs_processor REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_processor_pk_seq.nextval into :new.processor_id from dual;
END;
/

--
-- TOC Entry ID 56 (OID 18139904)
--
-- Name: frs_release_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_release_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 217 (OID 18139922)
--
-- Name: frs_release Type: TABLE 
--

CREATE TABLE frs_release (
	release_id integer DEFAULT '0' NOT NULL,
	package_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	notes varchar2(255),
	changes varchar2(255),
	status_id integer DEFAULT '0' NOT NULL,
	preformatted integer DEFAULT '0' NOT NULL,
	release_date integer DEFAULT '0' NOT NULL,
	released_by integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (release_id)
);

--nextval('frs_release_pk_seq'::text)
CREATE TRIGGER DEFAULT_FRS_RELEASE_ID BEFORE 
    INSERT 
    ON frs_release REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_release_pk_seq.nextval into :new.release_id from dual;
END;
/

--
-- TOC Entry ID 58 (OID 18139964)
--
-- Name: frs_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE frs_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 218 (OID 18139982)
--
-- Name: frs_status Type: TABLE 
--

CREATE TABLE frs_status (
	status_id integer DEFAULT '0' NULL,
	name varchar2(255),
	PRIMARY KEY (status_id)
);

--nextval('frs_status_pk_seq'::text)
CREATE TRIGGER DEFAULT_FRS_STATUS_ID BEFORE 
    INSERT 
    ON frs_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select frs_status_pk_seq.nextval into :new.status_id from dual;
END;
/

--
-- TOC Entry ID 60 (OID 18140012)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE group_cvs_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 219 (OID 18140030)
--
-- Name: group_cvs_history Type: TABLE 
--

CREATE TABLE group_cvs_history (
	id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	user_name character varying(80) DEFAULT '',
	cvs_commits integer DEFAULT '0' NOT NULL,
	cvs_commits_wk integer DEFAULT '0' NOT NULL,
	cvs_adds integer DEFAULT '0' NOT NULL,
	cvs_adds_wk integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

--nextval('group_cvs_history_pk_seq'::text)
CREATE TRIGGER DEFAULT_GROUP_CVS_HISTORY_ID BEFORE 
    INSERT 
    ON group_cvs_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select group_cvs_history_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 62 (OID 18140056)
--
-- Name: group_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE group_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 220 (OID 18140074)
--
-- Name: group_history Type: TABLE 
--

CREATE TABLE group_history (
	group_history_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	field_name varchar2(255) DEFAULT '',
	old_value varchar2(255) DEFAULT '',
	mod_by integer DEFAULT '0' NOT NULL,
	sf_date integer,
	PRIMARY KEY (group_history_id)
);

--nextval('group_history_pk_seq'::text) 
CREATE TRIGGER DEFAULT_GROUP_HISTORY_ID BEFORE 
    INSERT 
    ON group_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select group_history_pk_seq.nextval into :new.group_history_id from dual;
END;
/

--
-- TOC Entry ID 64 (OID 18140112)
--
-- Name: group_type_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE group_type_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 221 (OID 18140130)
--
-- Name: group_type Type: TABLE 
--

CREATE TABLE group_type (
	type_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (type_id)
);

--nextval('group_type_pk_seq'::text)
CREATE TRIGGER DEFAULT_GROUP_TYPE_ID BEFORE 
    INSERT 
    ON group_type REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select group_type_pk_seq.nextval into :new.type_id from dual;
END;
/

--
-- TOC Entry ID 66 (OID 18140160)
--
-- Name: groups_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE groups_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 222 (OID 18140178)
--
-- Name: groups Type: TABLE 
--

CREATE TABLE groups (
	group_id integer DEFAULT '0' NOT NULL,
	group_name character varying(40),
	homepage character varying(128),
	is_public integer DEFAULT '0' NOT NULL,
	status character(1) DEFAULT 'A' NOT NULL,
	unix_group_name character varying(30) DEFAULT '',
	unix_box character varying(20) DEFAULT 'shell1' NOT NULL,
	http_domain character varying(80),
	short_description character varying(255),
	cvs_box character varying(20) DEFAULT 'cvs1' NOT NULL,
	license character varying(16),
	register_purpose varchar2(2000),
	license_other varchar2(255),
	register_time integer DEFAULT '0' NOT NULL,
	use_bugs integer DEFAULT '1' NOT NULL,
	rand_hash varchar2(255),
	use_mail integer DEFAULT '1' NOT NULL,
	use_survey integer DEFAULT '1' NOT NULL,
	use_patch integer DEFAULT '1' NOT NULL,
	use_forum integer DEFAULT '1' NOT NULL,
	use_pm integer DEFAULT '1' NOT NULL,
	use_cvs integer DEFAULT '1' NOT NULL,
	use_news integer DEFAULT '1' NOT NULL,
	use_support integer DEFAULT '1' NOT NULL,
	new_bug_address varchar2(255) DEFAULT '',
	new_patch_address varchar2(255) DEFAULT '',
	new_support_address varchar2(255) DEFAULT '',
	type integer DEFAULT '1' NOT NULL,
	use_docman integer DEFAULT '1' NOT NULL,
	send_all_bugs integer DEFAULT '0' NOT NULL,
	send_all_patches integer DEFAULT '0' NOT NULL,
	send_all_support integer DEFAULT '0' NOT NULL,
	new_task_address varchar2(255) DEFAULT '',
	send_all_tasks integer DEFAULT '0' NOT NULL,
	use_bug_depend_box integer DEFAULT '1' NOT NULL,
	use_pm_depend_box integer DEFAULT '1' NOT NULL,
	PRIMARY KEY (group_id)
);

--nextval('groups_pk_seq'::text) 
CREATE TRIGGER DEFAULT_GROUP_ID BEFORE 
    INSERT 
    ON groups REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select groups_pk_seq.nextval into :new.group_id from dual;
END;
/

--
-- TOC Entry ID 223 (OID 18140269)
--
-- Name: intel_agreement Type: TABLE 
--

CREATE TABLE intel_agreement (
	user_id integer DEFAULT '0' NOT NULL,
	message varchar2(255),
	is_approved integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (user_id)
);

--
-- TOC Entry ID 68 (OID 18140301)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE mail_group_list_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 224 (OID 18140319)
--
-- Name: mail_group_list Type: TABLE 
--

CREATE TABLE mail_group_list (
	group_list_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	list_name varchar2(255),
	is_public integer DEFAULT '0' NOT NULL,
	password character varying(16),
	list_admin integer DEFAULT '0' NOT NULL,
	status integer DEFAULT '0' NOT NULL,
	description varchar2(255),
	PRIMARY KEY (group_list_id)
);

--nextval('mail_group_list_pk_seq'::text) 
CREATE TRIGGER DEFAULT_MAIL_GROUP_LIST_ID BEFORE 
    INSERT 
    ON mail_group_list REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select mail_group_list_pk_seq.nextval into :new.group_list_id from dual;
END;
/

--
-- TOC Entry ID 70 (OID 18140359)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE news_bytes_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 225 (OID 18140377)
--
-- Name: news_bytes Type: TABLE 
--

CREATE TABLE news_bytes (
	id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	submitted_by integer DEFAULT '0' NOT NULL,
	is_approved integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL,
	forum_id integer DEFAULT '0' NOT NULL,
	summary varchar2(255),
	details varchar2(255),
	PRIMARY KEY (id)
);

--nextval('news_bytes_pk_seq'::text)
CREATE TRIGGER DEFAULT_NEWS_BYTES_ID BEFORE 
    INSERT 
    ON news_bytes REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select news_bytes_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 72 (OID 18140419)
--
-- Name: patch_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE patch_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 226 (OID 18140437)
--
-- Name: patch Type: TABLE 
--

CREATE TABLE patch (
	patch_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	patch_status_id integer DEFAULT '0' NOT NULL,
	patch_category_id integer DEFAULT '0' NOT NULL,
	submitted_by integer DEFAULT '0' NOT NULL,
	assigned_to integer DEFAULT '0' NOT NULL,
	open_date integer DEFAULT '0' NOT NULL,
	summary varchar2(255),
	code varchar2(255),
	close_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (patch_id)
);

--nextval('patch_pk_seq'::text) 
CREATE TRIGGER DEFAULT_PATCH_ID BEFORE 
    INSERT 
    ON patch REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select patch_pk_seq.nextval into :new.patch_id from dual;
END;
/

--
-- TOC Entry ID 74 (OID 18140483)
--
-- Name: patch_category_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE patch_category_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 227 (OID 18140501)
--
-- Name: patch_category Type: TABLE 
--

CREATE TABLE patch_category (
	patch_category_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	category_name varchar2(255) DEFAULT '',
	PRIMARY KEY (patch_category_id)
);

--nextval('patch_category_pk_seq'::text) 
CREATE TRIGGER DEFAULT_PATCH_CATEGORY_ID BEFORE 
    INSERT 
    ON patch_category REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select patch_category_pk_seq.nextval into :new.patch_category_id from dual;
END;
/

--
-- TOC Entry ID 76 (OID 18140534)
--
-- Name: patch_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE patch_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 228 (OID 18140552)
--
-- Name: patch_history Type: TABLE 
--

CREATE TABLE patch_history (
	patch_history_id integer DEFAULT '0' NOT NULL,
	patch_id integer DEFAULT '0' NOT NULL,
	field_name varchar2(255) DEFAULT '',
	old_value varchar2(255) DEFAULT '',
	mod_by integer DEFAULT '0' NOT NULL,
	sf_date integer,
	PRIMARY KEY (patch_history_id)
);

--nextval('patch_history_pk_seq'::text)
CREATE TRIGGER DEFAULT_PATCH_HISTORY BEFORE 
    INSERT 
    ON patch_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select patch_history_pk_seq.nextval into :new.patch_history_id from dual;
END;
/

--
-- TOC Entry ID 78 (OID 18140590)
--
-- Name: patch_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE patch_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 229 (OID 18140608)
--
-- Name: patch_status Type: TABLE 
--

CREATE TABLE patch_status (
	patch_status_id integer DEFAULT '0' NOT NULL,
	status_name varchar2(255),
	PRIMARY KEY (patch_status_id)
);

--nextval('patch_status_pk_seq'::text)
CREATE TRIGGER DEFAULT_PATCH_STATUS BEFORE 
    INSERT 
    ON patch_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select patch_status_pk_seq.nextval into :new.patch_status_id from dual;
END;
/

--
-- TOC Entry ID 80 (OID 18140638)
--
-- Name: people_job_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_job_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 230 (OID 18140656)
--
-- Name: people_job Type: TABLE 
--

CREATE TABLE people_job (
	job_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	created_by integer DEFAULT '0' NOT NULL,
	title varchar2(255),
	description varchar2(255),
	sf_date integer DEFAULT '0' NOT NULL,
	status_id integer DEFAULT '0' NOT NULL,
	category_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (job_id)
);

--nextval('people_job_pk_seq'::text) 
CREATE TRIGGER DEFAULT_people_job BEFORE 
    INSERT 
    ON people_job REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_job_pk_seq.nextval into :new.job_id from dual;
END;
/


--
-- TOC Entry ID 82 (OID 18140697)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_job_category_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 231 (OID 18140715)
--
-- Name: people_job_category Type: TABLE 
--

CREATE TABLE people_job_category (
	category_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	private_flag integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (category_id)
);

--nextval('people_job_category_pk_seq'::text) 
CREATE TRIGGER DEFAULT_people_job_category BEFORE 
    INSERT 
    ON people_job_category REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_job_category_pk_seq.nextval into :new.category_id from dual;
END;
/

--
-- TOC Entry ID 84 (OID 18140747)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_job_inventory_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 232 (OID 18140765)
--
-- Name: people_job_inventory Type: TABLE 
--

CREATE TABLE people_job_inventory (
	job_inventory_id integer DEFAULT '0' NOT NULL,
	job_id integer DEFAULT '0' NOT NULL,
	skill_id integer DEFAULT '0' NOT NULL,
	skill_level_id integer DEFAULT '0' NOT NULL,
	skill_year_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (job_inventory_id)
);

--nextval('people_job_inventory_pk_seq'::text)
CREATE TRIGGER DEFAULT_people_job_inventory BEFORE 
    INSERT 
    ON people_job_inventory REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_job_inventory_pk_seq.nextval into :new.job_inventory_id from dual;
END;
/

--
-- TOC Entry ID 86 (OID 18140787)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_job_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 233 (OID 18140805)
--
-- Name: people_job_status Type: TABLE 
--

CREATE TABLE people_job_status (
	status_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (status_id)
);

--nextval('people_job_status_pk_seq'::text)
CREATE TRIGGER DEFAULT_people_job_status BEFORE 
    INSERT 
    ON people_job_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_job_status_pk_seq.nextval into :new.status_id from dual;
END;
/

--
-- TOC Entry ID 88 (OID 18140835)
--
-- Name: people_skill_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_skill_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 234 (OID 18140853)
--
-- Name: people_skill Type: TABLE 
--

CREATE TABLE people_skill (
	skill_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (skill_id)
);

--nextval('people_skill_pk_seq'::text) 
CREATE TRIGGER DEFAULT_people_skill BEFORE 
    INSERT 
    ON people_skill REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_skill_pk_seq.nextval into :new.skill_id from dual;
END;
/

--
-- TOC Entry ID 90 (OID 18140884)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_skill_inventory_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 235 (OID 18140902)
--
-- Name: people_skill_inventory Type: TABLE 
--

CREATE TABLE people_skill_inventory (
	skill_inventory_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	skill_id integer DEFAULT '0' NOT NULL,
	skill_level_id integer DEFAULT '0' NOT NULL,
	skill_year_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (skill_inventory_id)
);

--nextval('people_skill_inventory_pk_seq'::text)
CREATE TRIGGER DEFAULT_people_inventory BEFORE 
    INSERT 
    ON people_skill_inventory REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_skill_inventory_pk_seq.nextval into :new.skill_inventory_id from dual;
END;
/

--
-- TOC Entry ID 92 (OID 18140924)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_skill_level_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 236 (OID 18140942)
--
-- Name: people_skill_level Type: TABLE 
--

CREATE TABLE people_skill_level (
	skill_level_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (skill_level_id)
);

--nextval('people_skill_level_pk_seq'::text) 
CREATE TRIGGER DEFAULT_people_skill_level BEFORE 
    INSERT 
    ON people_skill_level REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_skill_level_pk_seq.nextval into :new.skill_level_id from dual;
END;
/

--
-- TOC Entry ID 94 (OID 18140972)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE people_skill_year_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 237 (OID 18140990)
--
-- Name: people_skill_year Type: TABLE 
--

CREATE TABLE people_skill_year (
	skill_year_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	PRIMARY KEY (skill_year_id)
);

--nextval('people_skill_year_pk_seq'::text)
CREATE TRIGGER DEFAULT_people_skill_year BEFORE 
    INSERT 
    ON people_skill_year REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select people_skill_year_pk_seq.nextval into :new.skill_year_id from dual;
END;
/


--
-- TOC Entry ID 96 (OID 18141020)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_assigned_to_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 238 (OID 18141038)
--
-- Name: project_assigned_to Type: TABLE 
--

CREATE TABLE project_assigned_to (
	project_assigned_id integer DEFAULT '0' NOT NULL,
	project_task_id integer DEFAULT '0' NOT NULL,
	assigned_to_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_assigned_id)
);

--nextval('project_assigned_to_pk_seq'::text)
CREATE TRIGGER DEFAULT_project_assigned_to BEFORE 
    INSERT 
    ON project_assigned_to REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_assigned_to_pk_seq.nextval into :new.project_assigned_id from dual;
END;
/

--
-- TOC Entry ID 239 (OID 18141056)
--
-- Name: project_counts_tmp Type: TABLE 
--

CREATE TABLE project_counts_tmp (
	group_id integer,
	type varchar2(255),
	count double precision
);

--
-- TOC Entry ID 240 (OID 18141083)
--
-- Name: project_counts_weekly_tmp Type: TABLE 
--

CREATE TABLE project_counts_weekly_tmp (
	group_id integer,
	type varchar2(255),
	count double precision
);

--
-- TOC Entry ID 98 (OID 18141110)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_dependencies_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 241 (OID 18141128)
--
-- Name: project_dependencies Type: TABLE 
--

CREATE TABLE project_dependencies (
	project_depend_id integer DEFAULT '0' NOT NULL,
	project_task_id integer DEFAULT '0' NOT NULL,
	is_dependent_on_task_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_depend_id)
);

--nextval('project_dependencies_pk_seq'::text)
CREATE TRIGGER DEFAULT_project_dependencies BEFORE 
    INSERT 
    ON project_dependencies REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_dependencies_pk_seq.nextval into :new.project_depend_id from dual;
END;
/

--
-- TOC Entry ID 100 (OID 18141146)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_group_list_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 242 (OID 18141164)
--
-- Name: project_group_list Type: TABLE 
--

CREATE TABLE project_group_list (
	group_project_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	project_name varchar2(255) DEFAULT '',
	is_public integer DEFAULT '0' NOT NULL,
	description varchar2(255),
	PRIMARY KEY (group_project_id)
);

--nextval('project_group_list_pk_seq'::text)
CREATE TRIGGER DEFAULT_project_group_list BEFORE 
    INSERT 
    ON project_group_list REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_group_list_pk_seq.nextval into :new.group_project_id from dual;
END;
/

--
-- TOC Entry ID 102 (OID 18141200)
--
-- Name: project_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 243 (OID 18141218)
--
-- Name: project_history Type: TABLE 
--

CREATE TABLE project_history (
	project_history_id integer DEFAULT '0' NOT NULL,
	project_task_id integer DEFAULT '0' NOT NULL,
	field_name varchar2(255) DEFAULT '',
	old_value varchar2(255) DEFAULT '',
	mod_by integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_history_id)
);

--nextval('project_history_pk_seq'::text)
CREATE TRIGGER DEFAULT_project_history BEFORE 
    INSERT 
    ON project_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_history_pk_seq.nextval into :new.project_history_id from dual;
END;
/

--
-- TOC Entry ID 104 (OID 18141257)
--
-- Name: project_metric_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_metric_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 244 (OID 18141275)
--
-- Name: project_metric Type: TABLE 
--

CREATE TABLE project_metric (
	ranking integer DEFAULT '0' NOT NULL,
	percentile double precision,
	group_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (ranking)
);

--nextval('project_metric_pk_seq'::text) 
CREATE TRIGGER DEFAULT_project_metric BEFORE 
    INSERT 
    ON project_metric REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_metric_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 106 (OID 18141292)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_metric_tmp1_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 245 (OID 18141310)
--
-- Name: project_metric_tmp1 Type: TABLE 
--

CREATE TABLE project_metric_tmp1 (
	ranking integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	value double precision,
	PRIMARY KEY (ranking)
);

--nextval('project_metric_tmp1_pk_seq'::text) 
CREATE TRIGGER DEFAULT_project_metric_tmp1 BEFORE 
    INSERT 
    ON project_metric_tmp1 REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_metric_tmp1_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 108 (OID 18141327)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_metric_week_tm_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 246 (OID 18141346)
--
-- Name: project_metric_weekly_tmp1 Type: TABLE 
--

CREATE TABLE project_metric_weekly_tmp1 (
	ranking integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	value double precision,
	PRIMARY KEY (ranking)
);

--nextval('project_metric_week_tm_pk_seq'::text)
CREATE TRIGGER DEFAULT_proj_metric_week_tmp1 BEFORE 
    INSERT 
    ON project_metric_weekly_tmp1 REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_metric_week_tm_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 110 (OID 18141363)
--
-- Name: project_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 247 (OID 18141381)
--
-- Name: project_status Type: TABLE 
--

CREATE TABLE project_status (
	status_id integer DEFAULT '0' NOT NULL,
	status_name varchar2(255) DEFAULT '',
	PRIMARY KEY (status_id)
);

--nextval('project_status_pk_seq'::text) 
CREATE TRIGGER DEFAULT_project_status BEFORE 
    INSERT 
    ON project_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_status_pk_seq.nextval into :new.status_id from dual;
END;
/

--
-- TOC Entry ID 112 (OID 18141412)
--
-- Name: project_task_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_task_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 248 (OID 18141430)
--
-- Name: project_task Type: TABLE 
--

CREATE TABLE project_task (
	project_task_id integer DEFAULT '0' NOT NULL,
	group_project_id integer DEFAULT '0' NOT NULL,
	summary varchar2(255) DEFAULT '',
	details varchar2(255) DEFAULT '',
	percent_complete integer DEFAULT '0' NOT NULL,
	priority integer DEFAULT '0' NOT NULL,
	hours double precision DEFAULT '0.00' NOT NULL,
	start_date integer DEFAULT '0' NOT NULL,
	end_date integer DEFAULT '0' NOT NULL,
	created_by integer DEFAULT '0' NOT NULL,
	status_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (project_task_id)
);

--nextval('project_task_pk_seq'::text) 
CREATE TRIGGER DEFAULT_project_task BEFORE 
    INSERT 
    ON project_task REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_task_pk_seq.nextval into :new.project_task_id from dual;
END;
/

--
-- TOC Entry ID 114 (OID 18141479)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE project_weekly_metric_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 249 (OID 18141497)
--
-- Name: project_weekly_metric Type: TABLE 
--

CREATE TABLE project_weekly_metric (
	ranking integer DEFAULT '0' NOT NULL,
	percentile double precision,
	group_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (ranking)
);

--nextval('project_weekly_metric_pk_seq'::text) 
CREATE TRIGGER DEFAULT_project_weekly_metric BEFORE 
    INSERT 
    ON project_weekly_metric REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select project_weekly_metric_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 250 (OID 18141514)
--
-- Name: sf_session Type: TABLE 
--

CREATE TABLE sf_session (
	user_id integer DEFAULT '0' NOT NULL,
	session_hash character(32) DEFAULT '',
	ip_addr character(15) DEFAULT '',
	time integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (session_hash)
);

--
-- TOC Entry ID 116 (OID 18141534)
--
-- Name: snippet_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE snippet_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 251 (OID 18141552)
--
-- Name: snippet Type: TABLE 
--

CREATE TABLE snippet (
	snippet_id integer DEFAULT '0' NOT NULL,
	created_by integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	description varchar2(255),
	type integer DEFAULT '0' NOT NULL,
	language integer DEFAULT '0' NOT NULL,
	license varchar2(255) DEFAULT '',
	category integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_id)
);

--nextval('snippet_pk_seq'::text)
CREATE TRIGGER DEFAULT_snippet BEFORE 
    INSERT 
    ON snippet REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select snippet_pk_seq.nextval into :new.snippet_id from dual;
END;
/

--
-- TOC Entry ID 118 (OID 18141593)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE snippet_package_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 252 (OID 18141611)
--
-- Name: snippet_package Type: TABLE 
--

CREATE TABLE snippet_package (
	snippet_package_id integer DEFAULT '0' NOT NULL,
	created_by integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	description varchar2(255),
	category integer DEFAULT '0' NOT NULL,
	language integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_id)
);

--nextval('snippet_package_pk_seq'::text)
CREATE TRIGGER DEFAULT_snippet_package BEFORE 
    INSERT 
    ON snippet_package REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select snippet_package_pk_seq.nextval into :new.snippet_package_id from dual;
END;
/

--
-- TOC Entry ID 120 (OID 18141648)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE snippet_package_item_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 253 (OID 18141666)
--
-- Name: snippet_package_item Type: TABLE 
--

CREATE TABLE snippet_package_item (
	snippet_package_item_id integer DEFAULT '0' NOT NULL,
	snippet_package_version_id integer DEFAULT '0' NOT NULL,
	snippet_version_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_item_id)
);

--nextval('snippet_package_item_pk_seq'::text)
CREATE TRIGGER DEFAULT_snippet_package_item BEFORE 
    INSERT 
    ON snippet_package_item REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select snippet_package_item_pk_seq.nextval into :new.snippet_package_item_id from dual;
END;
/


--
-- TOC Entry ID 122 (OID 18141684)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE snippet_package_version_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 254 (OID 18141702)
--
-- Name: snippet_package_version Type: TABLE 
--

CREATE TABLE snippet_package_version (
	snippet_package_version_id integer DEFAULT '0' NOT NULL,
	snippet_package_id integer DEFAULT '0' NOT NULL,
	changes varchar2(255),
	version varchar2(255),
	submitted_by integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (snippet_package_version_id)
);

--nextval('snippet_package_version_pk_seq'::text) 
CREATE TRIGGER DEFAULT_snippet_package_ver BEFORE 
    INSERT 
    ON snippet_package_version REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select snippet_package_version_pk_seq.nextval into :new.snippet_package_version_id from dual;
END;
/


--
-- TOC Entry ID 124 (OID 18141739)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE snippet_version_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 255 (OID 18141757)
--
-- Name: snippet_version Type: TABLE 
--

CREATE TABLE snippet_version (
	snippet_version_id integer DEFAULT '0' NOT NULL,
	snippet_id integer DEFAULT '0' NOT NULL,
	changes varchar2(255),
	version varchar2(255),
	submitted_by integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL,
	code varchar2(255),
	PRIMARY KEY (snippet_version_id)
);

--nextval('snippet_version_pk_seq'::text) 
CREATE TRIGGER DEFAULT_snippet_version BEFORE 
    INSERT 
    ON snippet_version REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select snippet_version_pk_seq.nextval into :new.snippet_version_id from dual;
END;
/

--
-- TOC Entry ID 256 (OID 18141795)
--
-- Name: stats_agg_logo_by_day Type: TABLE 
--

CREATE TABLE stats_agg_logo_by_day (
	day integer,
	count integer
);

--
-- TOC Entry ID 257 (OID 18141806)
--
-- Name: stats_agg_logo_by_group Type: TABLE 
--

CREATE TABLE stats_agg_logo_by_group (
	day integer,
	group_id integer,
	count integer
);

--
-- TOC Entry ID 258 (OID 18141818)
--
-- Name: stats_agg_pages_by_browser Type: TABLE 
--

CREATE TABLE stats_agg_pages_by_browser (
	browser character varying(8),
	count integer
);

--
-- TOC Entry ID 259 (OID 18141829)
--
-- Name: stats_agg_pages_by_day Type: TABLE 
--

CREATE TABLE stats_agg_pages_by_day (
	day integer DEFAULT '0' NOT NULL,
	count integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 260 (OID 18141842)
--
-- Name: stats_agg_pages_by_day_old Type: TABLE 
--

CREATE TABLE stats_agg_pages_by_day_old (
	day integer,
	count integer
);

--
-- TOC Entry ID 261 (OID 18141853)
--
-- Name: stats_agg_site_by_day Type: TABLE 
--

CREATE TABLE stats_agg_site_by_day (
	day integer DEFAULT '0' NOT NULL,
	count integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 262 (OID 18141866)
--
-- Name: stats_agg_site_by_group Type: TABLE 
--

CREATE TABLE stats_agg_site_by_group (
	day integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	count integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 263 (OID 18141881)
--
-- Name: stats_agr_filerelease Type: TABLE 
--

CREATE TABLE stats_agr_filerelease (
	filerelease_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 264 (OID 18141896)
--
-- Name: stats_agr_project Type: TABLE 
--

CREATE TABLE stats_agr_project (
	group_id integer DEFAULT '0' NOT NULL,
	group_ranking integer DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers integer DEFAULT '0' NOT NULL,
	file_releases integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL,
	site_views integer DEFAULT '0' NOT NULL,
	logo_views integer DEFAULT '0' NOT NULL,
	msg_posted integer DEFAULT '0' NOT NULL,
	msg_uniq_auth integer DEFAULT '0' NOT NULL,
	bugs_opened integer DEFAULT '0' NOT NULL,
	bugs_closed integer DEFAULT '0' NOT NULL,
	support_opened integer DEFAULT '0' NOT NULL,
	support_closed integer DEFAULT '0' NOT NULL,
	patches_opened integer DEFAULT '0' NOT NULL,
	patches_closed integer DEFAULT '0' NOT NULL,
	tasks_opened integer DEFAULT '0' NOT NULL,
	tasks_closed integer DEFAULT '0' NOT NULL,
	help_requests integer DEFAULT '0' NOT NULL,
	cvs_checkouts integer DEFAULT '0' NOT NULL,
	cvs_commits integer DEFAULT '0' NOT NULL,
	cvs_adds integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 265 (OID 18141949)
--
-- Name: stats_ftp_downloads Type: TABLE 
--

CREATE TABLE stats_ftp_downloads (
	day integer DEFAULT '0' NOT NULL,
	filerelease_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 266 (OID 18141966)
--
-- Name: stats_http_downloads Type: TABLE 
--

CREATE TABLE stats_http_downloads (
	day integer DEFAULT '0' NOT NULL,
	filerelease_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 267 (OID 18141983)
--
-- Name: stats_project Type: TABLE 
--

CREATE TABLE stats_project (
	month integer DEFAULT '0' NOT NULL,
	week integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	group_ranking integer DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers integer DEFAULT '0' NOT NULL,
	file_releases integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL,
	site_views integer DEFAULT '0' NOT NULL,
	subdomain_views integer DEFAULT '0' NOT NULL,
	msg_posted integer DEFAULT '0' NOT NULL,
	msg_uniq_auth integer DEFAULT '0' NOT NULL,
	bugs_opened integer DEFAULT '0' NOT NULL,
	bugs_closed integer DEFAULT '0' NOT NULL,
	support_opened integer DEFAULT '0' NOT NULL,
	support_closed integer DEFAULT '0' NOT NULL,
	patches_opened integer DEFAULT '0' NOT NULL,
	patches_closed integer DEFAULT '0' NOT NULL,
	tasks_opened integer DEFAULT '0' NOT NULL,
	tasks_closed integer DEFAULT '0' NOT NULL,
	help_requests integer DEFAULT '0' NOT NULL,
	cvs_checkouts integer DEFAULT '0' NOT NULL,
	cvs_commits integer DEFAULT '0' NOT NULL,
	cvs_adds integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 268 (OID 18142042)
--
-- Name: stats_project_tmp Type: TABLE 
--

CREATE TABLE stats_project_tmp (
	month integer DEFAULT '0' NOT NULL,
	week integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	group_ranking integer DEFAULT '0' NOT NULL,
	group_metric double precision DEFAULT '0.00000' NOT NULL,
	developers integer DEFAULT '0' NOT NULL,
	file_releases integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL,
	site_views integer DEFAULT '0' NOT NULL,
	subdomain_views integer DEFAULT '0' NOT NULL,
	msg_posted integer DEFAULT '0' NOT NULL,
	msg_uniq_auth integer DEFAULT '0' NOT NULL,
	bugs_opened integer DEFAULT '0' NOT NULL,
	bugs_closed integer DEFAULT '0' NOT NULL,
	support_opened integer DEFAULT '0' NOT NULL,
	support_closed integer DEFAULT '0' NOT NULL,
	patches_opened integer DEFAULT '0' NOT NULL,
	patches_closed integer DEFAULT '0' NOT NULL,
	tasks_opened integer DEFAULT '0' NOT NULL,
	tasks_closed integer DEFAULT '0' NOT NULL,
	help_requests integer DEFAULT '0' NOT NULL,
	cvs_checkouts integer DEFAULT '0' NOT NULL,
	cvs_commits integer DEFAULT '0' NOT NULL,
	cvs_adds integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 269 (OID 18142101)
--
-- Name: stats_site Type: TABLE 
--

CREATE TABLE stats_site (
	month integer DEFAULT '0' NOT NULL,
	week integer DEFAULT '0' NOT NULL,
	day integer DEFAULT '0' NOT NULL,
	site_views integer DEFAULT '0' NOT NULL,
	subdomain_views integer DEFAULT '0' NOT NULL,
	downloads integer DEFAULT '0' NOT NULL,
	uniq_users integer DEFAULT '0' NOT NULL,
	sessions integer DEFAULT '0' NOT NULL,
	total_users integer DEFAULT '0' NOT NULL,
	new_users integer DEFAULT '0' NOT NULL,
	new_projects integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 126 (OID 18142132)
--
-- Name: support_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 270 (OID 18142150)
--
-- Name: support Type: TABLE 
--

CREATE TABLE support (
	support_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	support_status_id integer DEFAULT '0' NOT NULL,
	support_category_id integer DEFAULT '0' NOT NULL,
	priority integer DEFAULT '0' NOT NULL,
	submitted_by integer DEFAULT '0' NOT NULL,
	assigned_to integer DEFAULT '0' NOT NULL,
	open_date integer DEFAULT '0' NOT NULL,
	summary varchar2(255),
	close_date integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (support_id)
);

--nextval('support_pk_seq'::text)
CREATE TRIGGER DEFAULT_support BEFORE 
    INSERT 
    ON support REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_pk_seq.nextval into :new.support_id from dual;
END;
/

--
-- TOC Entry ID 128 (OID 18142196)
--
-- Name: support_canned_responses_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_canned_resp_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 271 (OID 18142214)
--
-- Name: support_canned_responses Type: TABLE 
--

CREATE TABLE support_canned_responses (
	support_canned_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	title varchar2(255),
	body varchar2(255),
	PRIMARY KEY (support_canned_id)
);

CREATE TRIGGER DEFAULT_support_canned_resp BEFORE 
    INSERT 
    ON support_canned_responses REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_canned_resp_pk_seq.nextval into :new.support_canned_id from dual;
END;
/

--
-- TOC Entry ID 130 (OID 18142247)
--
-- Name: support_category_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_category_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 272 (OID 18142265)
--
-- Name: support_category Type: TABLE 
--

CREATE TABLE support_category (
	support_category_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	category_name varchar2(255) DEFAULT '',
	PRIMARY KEY (support_category_id)
);

CREATE TRIGGER DEFAULT_support_category BEFORE 
    INSERT 
    ON support_category REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_category_pk_seq.nextval into :new.support_category_id from dual;
END;
/

--
-- TOC Entry ID 132 (OID 18142298)
--
-- Name: support_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 273 (OID 18142316)
--
-- Name: support_history Type: TABLE 
--

CREATE TABLE support_history (
	support_history_id integer DEFAULT '0' NOT NULL,
	support_id integer DEFAULT '0' NOT NULL,
	field_name varchar2(255) DEFAULT '',
	old_value varchar2(255) DEFAULT '',
	mod_by integer DEFAULT '0' NOT NULL,
	sf_date integer,
	PRIMARY KEY (support_history_id)
);

CREATE TRIGGER DEFAULT_support_history BEFORE 
    INSERT 
    ON support_history REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_history_pk_seq.nextval into :new.support_history_id from dual;
END;
/

--
-- TOC Entry ID 134 (OID 18142354)
--
-- Name: support_messages_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_messages_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 274 (OID 18142372)
--
-- Name: support_messages Type: TABLE 
--

CREATE TABLE support_messages (
	support_message_id integer DEFAULT '0' NOT NULL,
	support_id integer DEFAULT '0' NOT NULL,
	from_email varchar2(255),
	sf_date integer DEFAULT '0' NOT NULL,
	body varchar2(255),
	PRIMARY KEY (support_message_id)
);

CREATE TRIGGER DEFAULT_support_messages BEFORE 
    INSERT 
    ON support_messages REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_messages_pk_seq.nextval into :new.support_message_id from dual;
END;
/

--
-- TOC Entry ID 136 (OID 18142407)
--
-- Name: support_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE support_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 275 (OID 18142425)
--
-- Name: support_status Type: TABLE 
--

CREATE TABLE support_status (
	support_status_id integer DEFAULT '0' NOT NULL,
	status_name varchar2(255),
	PRIMARY KEY (support_status_id)
);

CREATE TRIGGER DEFAULT_support_status BEFORE 
    INSERT 
    ON support_status REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select support_status_pk_seq.nextval into :new.support_status_id from dual;
END;
/

--
-- TOC Entry ID 138 (OID 18142455)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE supported_languages_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 276 (OID 18142473)
--
-- Name: supported_languages Type: TABLE 
--

CREATE TABLE supported_languages (
	language_id integer DEFAULT '0' NOT NULL,
	name varchar2(255),
	filename varchar2(255),
	classname varchar2(255),
	language_code character(2),
	PRIMARY KEY (language_id)
);

CREATE TRIGGER DEFAULT_supported_languages BEFORE 
    INSERT 
    ON supported_languages REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select supported_languages_pk_seq.nextval into :new.language_id from dual;
END;
/

--
-- TOC Entry ID 140 (OID 18142506)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE survey_question_types_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 277 (OID 18142524)
--
-- Name: survey_question_types Type: TABLE 
--

CREATE TABLE survey_question_types (
	id integer DEFAULT '0' NOT NULL,
	type varchar2(255) DEFAULT '',
	PRIMARY KEY (id)
);

CREATE TRIGGER DEFAULT_survey_question_types BEFORE 
    INSERT 
    ON survey_question_types REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select survey_question_types_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 142 (OID 18142555)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE survey_questions_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 278 (OID 18142573)
--
-- Name: survey_questions Type: TABLE 
--

CREATE TABLE survey_questions (
	question_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	question varchar2(255) DEFAULT '',
	question_type integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (question_id)
);

CREATE TRIGGER DEFAULT_survey_question BEFORE 
    INSERT 
    ON survey_questions REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select survey_questions_pk_seq.nextval into :new.question_id from dual;
END;
/

--
-- TOC Entry ID 279 (OID 18142608)
--
-- Name: survey_rating_aggregate Type: TABLE 
--

CREATE TABLE survey_rating_aggregate (
	type integer DEFAULT '0' NOT NULL,
	id integer DEFAULT '0' NOT NULL,
	response double precision DEFAULT '0' NOT NULL,
	count integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 280 (OID 18142625)
--
-- Name: survey_rating_response Type: TABLE 
--

CREATE TABLE survey_rating_response (
	user_id integer DEFAULT '0' NOT NULL,
	type integer DEFAULT '0' NOT NULL,
	id integer DEFAULT '0' NOT NULL,
	response integer DEFAULT '0' NOT NULL,
	sf_date integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 281 (OID 18142644)
--
-- Name: survey_responses Type: TABLE 
--

CREATE TABLE survey_responses (
	user_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	survey_id integer DEFAULT '0' NOT NULL,
	question_id integer DEFAULT '0' NOT NULL,
	response varchar2(255) DEFAULT '',
	sf_date integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 144 (OID 18142680)
--
-- Name: surveys_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE surveys_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 282 (OID 18142698)
--
-- Name: surveys Type: TABLE 
--

CREATE TABLE surveys (
	survey_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	survey_title varchar2(255) DEFAULT '',
	survey_questions varchar2(255) DEFAULT '',
	is_active integer DEFAULT '1' NOT NULL,
	PRIMARY KEY (survey_id)
);


CREATE TRIGGER DEFAULT_surveys BEFORE 
    INSERT 
    ON surveys REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select surveys_pk_seq.nextval into :new.survey_id from dual;
END;
/

--
-- TOC Entry ID 146 (OID 18142735)
--
-- Name: system_history_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE system_history_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 148 (OID 18142787)
--
-- Name: system_machines_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE system_machines_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 150 (OID 18142836)
--
-- Name: system_news_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE system_news_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 152 (OID 18142895)
--
-- Name: system_services_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE system_services_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 154 (OID 18142944)
--
-- Name: system_status_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE system_status_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 283 (OID 18142992)
--
-- Name: theme_prefs Type: TABLE 
--

CREATE TABLE theme_prefs (
	user_id integer DEFAULT '0' NOT NULL,
	user_theme integer DEFAULT '0' NOT NULL,
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
-- Name: themes_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE themes_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 284 (OID 18143038)
--
-- Name: themes Type: TABLE 
--

CREATE TABLE themes (
	theme_id integer DEFAULT '0' NOT NULL,
	dirname character varying(80),
	fullname character varying(80),
	PRIMARY KEY (theme_id)
);

CREATE TRIGGER DEFAULT_themes BEFORE 
    INSERT 
    ON themes REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select themes_pk_seq.nextval into :new.theme_id from dual;
END;
/

--
-- TOC Entry ID 285 (OID 18143054)
--
-- Name: tmp_projs_releases_tmp Type: TABLE 
--

CREATE TABLE tmp_projs_releases_tmp (
	year integer DEFAULT '0' NOT NULL,
	month integer DEFAULT '0' NOT NULL,
	total_proj integer DEFAULT '0' NOT NULL,
	total_releases integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 286 (OID 18143071)
--
-- Name: top_group Type: TABLE 
--

CREATE TABLE top_group (
	group_id integer DEFAULT '0' NOT NULL,
	group_name character varying(40),
	downloads_all integer DEFAULT '0' NOT NULL,
	rank_downloads_all integer DEFAULT '0' NOT NULL,
	rank_downloads_all_old integer DEFAULT '0' NOT NULL,
	downloads_week integer DEFAULT '0' NOT NULL,
	rank_downloads_week integer DEFAULT '0' NOT NULL,
	rank_downloads_week_old integer DEFAULT '0' NOT NULL,
	userrank integer DEFAULT '0' NOT NULL,
	rank_userrank integer DEFAULT '0' NOT NULL,
	rank_userrank_old integer DEFAULT '0' NOT NULL,
	forumposts_week integer DEFAULT '0' NOT NULL,
	rank_forumposts_week integer DEFAULT '0' NOT NULL,
	rank_forumposts_week_old integer DEFAULT '0' NOT NULL,
	pageviews_proj integer DEFAULT '0' NOT NULL,
	rank_pageviews_proj integer DEFAULT '0' NOT NULL,
	rank_pageviews_proj_old integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 158 (OID 18143113)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE trove_cat_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 287 (OID 18143131)
--
-- Name: trove_cat Type: TABLE 
--

CREATE TABLE trove_cat (
	trove_cat_id integer DEFAULT '0' NOT NULL,
	version integer DEFAULT '0' NOT NULL,
	parent integer DEFAULT '0' NOT NULL,
	root_parent integer DEFAULT '0' NOT NULL,
	shortname character varying(80),
	fullname character varying(80),
	description character varying(255),
	count_subcat integer DEFAULT '0' NOT NULL,
	count_subproj integer DEFAULT '0' NOT NULL,
	fullpath varchar2(255) DEFAULT '',
	fullpath_ids varchar2(255),
	PRIMARY KEY (trove_cat_id)
);

CREATE TRIGGER DEFAULT_trove_cat BEFORE 
    INSERT 
    ON trove_cat REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select trove_cat_pk_seq.nextval into :new.trove_cat_id from dual;
END;
/

--
-- TOC Entry ID 160 (OID 18143176)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE trove_group_link_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 288 (OID 18143194)
--
-- Name: trove_group_link Type: TABLE 
--

CREATE TABLE trove_group_link (
	trove_group_id integer DEFAULT '0' NOT NULL,
	trove_cat_id integer DEFAULT '0' NOT NULL,
	trove_cat_version integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	trove_cat_root integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (trove_group_id)
);

CREATE TRIGGER DEFAULT_trove_group_link BEFORE 
    INSERT 
    ON trove_group_link REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select trove_group_link_pk_seq.nextval into :new.trove_group_id from dual;
END;
/

--
-- TOC Entry ID 162 (OID 18143216)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE trove_treesums_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 289 (OID 18143234)
--
-- Name: trove_treesums Type: TABLE 
--

CREATE TABLE trove_treesums (
	trove_treesums_id integer DEFAULT '0' NOT NULL,
	trove_cat_id integer DEFAULT '0' NOT NULL,
	limit_1 integer DEFAULT '0' NOT NULL,
	subprojects integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (trove_treesums_id)
);

CREATE TRIGGER DEFAULT_trove_treesums BEFORE 
    INSERT 
    ON trove_treesums REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select trove_treesums_pk_seq.nextval into :new.trove_treesums_id from dual;
END;
/

--
-- TOC Entry ID 164 (OID 18143286)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_bookmarks_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 290 (OID 18143304)
--
-- Name: user_bookmarks Type: TABLE 
--

CREATE TABLE user_bookmarks (
	bookmark_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	bookmark_url varchar2(255),
	bookmark_title varchar2(255),
	PRIMARY KEY (bookmark_id)
);

CREATE TRIGGER DEFAULT_user_bookmarks BEFORE 
    INSERT 
    ON user_bookmarks REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_bookmarks_pk_seq.nextval into :new.bookmark_id from dual;
END;
/

--
-- TOC Entry ID 166 (OID 18143337)
--
-- Name: user_diary_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_diary_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 291 (OID 18143355)
--
-- Name: user_diary Type: TABLE 
--

CREATE TABLE user_diary (
	id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	date_posted integer DEFAULT '0' NOT NULL,
	summary varchar2(255),
	details varchar2(255),
	is_public integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (id)
);

CREATE TRIGGER DEFAULT_user_diary BEFORE 
    INSERT 
    ON user_diary REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_diary_pk_seq.nextval into :new.id from dual;
END;
/

--
-- TOC Entry ID 168 (OID 18143392)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_diary_monitor_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 292 (OID 18143410)
--
-- Name: user_diary_monitor Type: TABLE 
--

CREATE TABLE user_diary_monitor (
	monitor_id integer DEFAULT '0' NOT NULL,
	monitored_user integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (monitor_id)
);

CREATE TRIGGER DEFAULT_user_diary_monitor BEFORE 
    INSERT 
    ON user_diary_monitor REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_diary_monitor_pk_seq.nextval into :new.monitor_id from dual;
END;
/

--
-- TOC Entry ID 170 (OID 18143428)
--
-- Name: user_group_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_group_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 293 (OID 18143446)
--
-- Name: user_group Type: TABLE 
--

CREATE TABLE user_group (
	user_group_id integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	admin_flags character(16) DEFAULT '',
	bug_flags integer DEFAULT '0' NOT NULL,
	forum_flags integer DEFAULT '0' NOT NULL,
	project_flags integer DEFAULT '2' NOT NULL,
	patch_flags integer DEFAULT '1' NOT NULL,
	support_flags integer DEFAULT '1' NOT NULL,
	doc_flags integer DEFAULT '0' NOT NULL,
	cvs_flags integer DEFAULT '1' NOT NULL,
	member_role integer DEFAULT '100' NOT NULL,
	release_flags integer DEFAULT '0' NOT NULL,
	PRIMARY KEY (user_group_id)
);

CREATE TRIGGER DEFAULT_user_group BEFORE 
    INSERT 
    ON user_group REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_group_pk_seq.nextval into :new.user_group_id from dual;
END;
/

--
-- TOC Entry ID 172 (OID 18143484)
--
-- Name: user_metric_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_metric_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 294 (OID 18143502)
--
-- Name: user_metric Type: TABLE 
--

CREATE TABLE user_metric (
	ranking integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	times_ranked integer DEFAULT '0' NOT NULL,
	avg_raters_importance double precision DEFAULT '0.00000000' NOT NULL,
	avg_rating double precision DEFAULT '0.00000000' NOT NULL,
	metric double precision DEFAULT '0.00000000' NOT NULL,
	percentile double precision DEFAULT '0.00000000' NOT NULL,
	importance_factor double precision DEFAULT '0.00000000' NOT NULL,
	PRIMARY KEY (ranking)
);

CREATE TRIGGER DEFAULT_user_metric BEFORE 
    INSERT 
    ON user_metric REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_metric_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 174 (OID 18143530)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE user_metric0_pk_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 295 (OID 18143548)
--
-- Name: user_metric0 Type: TABLE 
--

CREATE TABLE user_metric0 (
	ranking integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	times_ranked integer DEFAULT '0' NOT NULL,
	avg_raters_importance double precision DEFAULT '0.00000000' NOT NULL,
	avg_rating double precision DEFAULT '0.00000000' NOT NULL,
	metric double precision DEFAULT '0.00000000' NOT NULL,
	percentile double precision DEFAULT '0.00000000' NOT NULL,
	importance_factor double precision DEFAULT '0.00000000' NOT NULL,
	PRIMARY KEY (ranking)
);

CREATE TRIGGER DEFAULT_user_metric0 BEFORE 
    INSERT 
    ON user_metric0 REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select user_metric0_pk_seq.nextval into :new.ranking from dual;
END;
/

--
-- TOC Entry ID 296 (OID 18143576)
--
-- Name: user_preferences Type: TABLE 
--

CREATE TABLE user_preferences (
	user_id integer DEFAULT '0' NOT NULL,
	preference_name character varying(20),
	preference_value character varying(20),
	set_date integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 297 (OID 18143591)
--
-- Name: user_ratings Type: TABLE 
--

CREATE TABLE user_ratings (
	rated_by integer DEFAULT '0' NOT NULL,
	user_id integer DEFAULT '0' NOT NULL,
	rate_field integer DEFAULT '0' NOT NULL,
	rating integer DEFAULT '0' NOT NULL
);

--
-- TOC Entry ID 176 (OID 18143608)
--
-- Name: users_pk_seq Type: SEQUENCE 
--

CREATE SEQUENCE users_pk_seq start with 100 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 298 (OID 18143626)
--
-- Name: users Type: TABLE 
--

CREATE TABLE users (
	user_id integer DEFAULT '0' NOT NULL,
	user_name varchar2(255) DEFAULT '',
	email varchar2(255) DEFAULT '',
	user_pw character varying(32) DEFAULT '',
	realname character varying(32) DEFAULT '',
	status character(1) DEFAULT 'A' NOT NULL,
	shell character varying(20) DEFAULT '/bin/bash' NOT NULL,
	unix_pw character varying(40) DEFAULT '',
	unix_status character(1) DEFAULT 'N' NOT NULL,
	unix_uid integer DEFAULT '0' NOT NULL,
	unix_box character varying(10) DEFAULT 'shell1' NOT NULL,
	add_date integer DEFAULT '0' NOT NULL,
	confirm_hash character varying(32),
	mail_siteupdates integer DEFAULT '0' NOT NULL,
	mail_va integer DEFAULT '0' NOT NULL,
	authorized_keys varchar2(255),
	email_new varchar2(255),
	people_view_skills integer DEFAULT '0' NOT NULL,
	people_resume varchar2(255) DEFAULT '',
	timezone character varying(64) DEFAULT 'GMT',
	language integer DEFAULT '1' NOT NULL,
	PRIMARY KEY (user_id)
);

CREATE TRIGGER DEFAULT_users BEFORE 
    INSERT 
    ON users REFERENCING OLD AS old NEW AS new 
    FOR EACH ROW 
BEGIN
  select users_pk_seq.nextval into :new.user_id from dual;
END;
/

--
-- TOC Entry ID 178 (OID 27311232)
--
-- Name: unix_uid_seq Type: SEQUENCE 
--

CREATE SEQUENCE unix_uid_seq start with 600 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 180 (OID 27311250)
--
-- Name: forum_thread_seq Type: SEQUENCE 
--

CREATE SEQUENCE forum_thread_seq start with 1 increment by 1 maxvalue 2147483647 minvalue 1 ;

--
-- TOC Entry ID 299 (OID 27311451)
--
-- Name: trove_agg Type: TABLE 
--

CREATE TABLE trove_agg (
	trove_cat_id integer,
	group_id integer,
	group_name character varying(40),
	unix_group_name character varying(30),
	status character(1),
	register_time integer,
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
	day integer DEFAULT '0' NOT NULL,
	hour integer DEFAULT '0' NOT NULL,
	group_id integer DEFAULT '0' NOT NULL,
	browser character varying(8) DEFAULT 'OTHER' NOT NULL,
	ver double precision DEFAULT '0.00' NOT NULL,
	platform character varying(8) DEFAULT 'OTHER' NOT NULL,
	time integer DEFAULT '0' NOT NULL,
	page varchar2(255),
	type integer DEFAULT '0' NOT NULL
);

--\connect - tperdue
--
-- TOC Entry ID 316 (OID 18138445)
--
-- Name: bug_group_id Type: INDEX 
--

CREATE  INDEX bug_group_id on bug  ( group_id );

--
-- TOC Entry ID 466 (OID 18138445)
--
-- Name: bug_groupid_statusid Type: INDEX 
--

CREATE  INDEX bug_groupid_statusid on bug  ( group_id , status_id );

--
-- TOC Entry ID 467 (OID 18138445)
--
-- Name: bug_groupid_assignedto_statusid Type: INDEX 
--

CREATE  INDEX bug_groupid_assngto_stsid on bug  ( group_id , assigned_to , status_id );

--
-- TOC Entry ID 317 (OID 18138513)
--
-- Name: bug_bug_dependencies_bug_id Type: INDEX 
--

CREATE  INDEX bug_bug_dependencies_bug_id on bug_bug_dependencies  ( bug_id );

--
-- TOC Entry ID 318 (OID 18138513)
--
-- Name: bug_bug_is_dependent_on_task_id Type: INDEX 
--

CREATE  INDEX bug_bug_is_dpnd_on_tsk_id on bug_bug_dependencies  ( is_dependent_on_bug_id );

--
-- TOC Entry ID 319 (OID 18138549)
--
-- Name: bug_canned_response_group_id Type: INDEX 
--

CREATE  INDEX bug_canned_response_group_id on bug_canned_responses  ( group_id );

--
-- TOC Entry ID 320 (OID 18138600)
--
-- Name: bug_category_group_id Type: INDEX 
--

CREATE  INDEX bug_category_group_id on bug_category  ( group_id );

--
-- TOC Entry ID 321 (OID 18138705)
--
-- Name: bug_group_group_id Type: INDEX 
--

CREATE  INDEX bug_group_group_id on bug_group  ( group_id );

--
-- TOC Entry ID 322 (OID 18138756)
--
-- Name: bug_history_bug_id Type: INDEX 
--

CREATE  INDEX bug_history_bug_id on bug_history  ( bug_id );

--
-- TOC Entry ID 323 (OID 18138909)
--
-- Name: bug_task_dependencies_bug_id Type: INDEX 
--

CREATE  INDEX bug_task_dependencies_bug_id on bug_task_dependencies  ( bug_id );

--
-- TOC Entry ID 324 (OID 18138909)
--
-- Name: bug_task_is_dpndnt_on_tsk_i Type: INDEX 
--

CREATE  INDEX bug_task_is_dpndnt_on_tsk_i on bug_task_dependencies  ( is_dependent_on_task_id );

--
-- TOC Entry ID 325 (OID 18138995)
--
-- Name: db_images_group Type: INDEX 
--

CREATE  INDEX db_images_group on db_images  ( group_id );

--
-- TOC Entry ID 326 (OID 18139058)
--
-- Name: doc_group_doc_group Type: INDEX 
--

CREATE  INDEX doc_group_doc_group on doc_data  ( doc_group );

--
-- TOC Entry ID 327 (OID 18139122)
--
-- Name: doc_groups_group Type: INDEX 
--

CREATE  INDEX doc_groups_group on doc_groups  ( group_id );

--
-- TOC Entry ID 328 (OID 18139192)
--
-- Name: filemodule_monitor_id Type: INDEX 
--

CREATE  INDEX filemodule_monitor_id on filemodule_monitor  ( filemodule_id );

--
-- TOC Entry ID 329 (OID 18139228)
--
-- Name: forum_forumid_msgid Type: INDEX 
--

CREATE  INDEX forum_forumid_msgid on forum  ( group_forum_id , msg_id );

--
-- TOC Entry ID 330 (OID 18139228)
--
-- Name: forum_group_forum_id Type: INDEX 
--

CREATE  INDEX forum_group_forum_id on forum  ( group_forum_id );

--
-- TOC Entry ID 331 (OID 18139228)
--
-- Name: forum_forumid_isfollowupto Type: INDEX 
--

CREATE  INDEX forum_forumid_isfollowupto on forum  ( group_forum_id , is_followup_to );

--
-- TOC Entry ID 332 (OID 18139228)
--
-- Name: forum_forumid_threadid_mostrece Type: INDEX 
--

CREATE  INDEX forum_formid_thrdid_mstrc on forum  ( group_forum_id , thread_id , most_recent_date );

--
-- TOC Entry ID 333 (OID 18139228)
--
-- Name: forum_threadid_isfollowupto Type: INDEX 
--

CREATE  INDEX forum_threadid_isfollowupto on forum  ( thread_id , is_followup_to );

--
-- TOC Entry ID 334 (OID 18139228)
--
-- Name: forum_forumid_isfollto_mostrece Type: INDEX 
--

CREATE  INDEX forum_forumid_isfollto_mstrc on forum  ( group_forum_id , is_followup_to , most_recent_date );

--
-- TOC Entry ID 335 (OID 18139309)
--
-- Name: forum_group_list_group_id Type: INDEX 
--

CREATE  INDEX forum_group_list_group_id on forum_group_list  ( group_id );

--
-- TOC Entry ID 336 (OID 18139366)
--
-- Name: forum_monitor_combo_id Type: INDEX 
--

CREATE  INDEX forum_monitor_combo_id on forum_monitored_forums  ( forum_id , user_id );

--
-- TOC Entry ID 337 (OID 18139366)
--
-- Name: forum_monitor_thread_id Type: INDEX 
--

CREATE  INDEX forum_monitor_thread_id on forum_monitored_forums  ( forum_id );

--
-- TOC Entry ID 338 (OID 18139510)
--
-- Name: foundry_news_foundry_approved_d Type: INDEX 
--

CREATE  INDEX foundry_news_fndry_apprvd_d on foundry_news  ( foundry_id , is_approved , approve_date );

--
-- TOC Entry ID 339 (OID 18139510)
--
-- Name: foundry_news_foundry_approved Type: INDEX 
--

CREATE  INDEX foundry_news_foundry_approved on foundry_news  ( foundry_id , is_approved );

--
-- TOC Entry ID 340 (OID 18139510)
--
-- Name: foundry_news_foundry Type: INDEX 
--

CREATE  INDEX foundry_news_foundry on foundry_news  ( foundry_id );

--
-- TOC Entry ID 463 (OID 18139510)
--
-- Name: foundrynews_foundry_dt_apprv Type: INDEX 
--

CREATE  INDEX foundrynews_foundry_dt_apprv on foundry_news  ( foundry_id , approve_date , is_approved );

--
-- TOC Entry ID 341 (OID 18139550)
--
-- Name: foundry_project_group_rank Type: INDEX 
--

CREATE  INDEX foundry_project_group_rank on foundry_preferred_projects  ( group_id , rank );

--
-- TOC Entry ID 342 (OID 18139550)
--
-- Name: foundry_project_group Type: INDEX 
--

CREATE  INDEX foundry_project_group on foundry_preferred_projects  ( group_id );

--
-- TOC Entry ID 343 (OID 18139588)
--
-- Name: foundry_projects_foundry Type: INDEX 
--

CREATE  INDEX foundry_projects_foundry on foundry_projects  ( foundry_id );

--
-- TOC Entry ID 344 (OID 18139606)
--
-- Name: downloads_http_idx Type: INDEX 
--

CREATE  INDEX downloads_http_idx on frs_dlstats_agg  ( downloads_http );

--
-- TOC Entry ID 345 (OID 18139606)
--
-- Name: downloads_ftp_idx Type: INDEX 
--

CREATE  INDEX downloads_ftp_idx on frs_dlstats_agg  ( downloads_ftp );

--
-- TOC Entry ID 346 (OID 18139606)
--
-- Name: file_id_idx Type: INDEX 
--

CREATE  INDEX file_id_idx on frs_dlstats_agg  ( file_id );

--
-- TOC Entry ID 347 (OID 18139606)
--
-- Name: day_idx Type: INDEX 
--

CREATE  INDEX day_idx on frs_dlstats_agg  ( day );

--
-- TOC Entry ID 348 (OID 18139623)
--
-- Name: dlstats_file_down Type: INDEX 
--

CREATE  INDEX dlstats_file_down on frs_dlstats_file_agg  ( downloads );

--
-- TOC Entry ID 349 (OID 18139623)
--
-- Name: dlstats_file_file_id Type: INDEX 
--

CREATE  INDEX dlstats_file_file_id on frs_dlstats_file_agg  ( file_id );

--
-- TOC Entry ID 350 (OID 18139623)
--
-- Name: dlstats_file_day Type: INDEX 
--

CREATE  INDEX dlstats_file_day on frs_dlstats_file_agg  ( day );

--
-- TOC Entry ID 351 (OID 18139638)
--
-- Name: stats_agr_tmp_fid Type: INDEX 
--

-- Removed because is not needed because is created in table definition
-- CREATE  INDEX stats_agr_tmp_fid on frs_dlstats_filetotal_agg  ( file_id );

--
-- TOC Entry ID 352 (OID 18139654)
--
-- Name: frs_dlstats_filetotal_agg_old_f Type: INDEX 
--

CREATE  INDEX frs_dlstats_filettl_agg_old_f on frs_dlstats_filetotal_agg_old  ( file_id );

--
-- TOC Entry ID 303 (OID 18139667)
--
-- Name: frsdlstatsgroupagg_day_dls Type: INDEX 
--

CREATE  INDEX frsdlstatsgroupagg_day_dls on frs_dlstats_group_agg  ( day , downloads );

--
-- TOC Entry ID 353 (OID 18139667)
--
-- Name: group_id_idx Type: INDEX 
--

CREATE  INDEX group_id_idx on frs_dlstats_group_agg  ( group_id );

--
-- TOC Entry ID 355 (OID 18139667)
--
-- Name: frs_dlstats_group_agg_day Type: INDEX 
--

CREATE  INDEX frs_dlstats_group_agg_day on frs_dlstats_group_agg  ( day );

--
-- TOC Entry ID 356 (OID 18139682)
--
-- Name: stats_agr_tmp_gid Type: INDEX 
--

CREATE  INDEX stats_agr_tmp_gid on frs_dlstats_grouptotal_agg  ( group_id );

--
-- TOC Entry ID 357 (OID 18139714)
--
-- Name: frs_file_name Type: INDEX 
--

CREATE  INDEX frs_file_name on frs_file  ( filename );

--
-- TOC Entry ID 358 (OID 18139714)
--
-- Name: frs_file_date Type: INDEX 
--

CREATE  INDEX frs_file_date on frs_file  ( post_date );

--
-- TOC Entry ID 359 (OID 18139714)
--
-- Name: frs_file_processor Type: INDEX 
--

CREATE  INDEX frs_file_processor on frs_file  ( processor_id );

--
-- TOC Entry ID 360 (OID 18139714)
--
-- Name: frs_file_release_id Type: INDEX 
--

CREATE  INDEX frs_file_release_id on frs_file  ( release_id );

--
-- TOC Entry ID 361 (OID 18139714)
--
-- Name: frs_file_type Type: INDEX 
--

CREATE  INDEX frs_file_type on frs_file  ( type_id );

--
-- TOC Entry ID 362 (OID 18139822)
--
-- Name: package_group_id Type: INDEX 
--

CREATE  INDEX package_group_id on frs_package  ( group_id );

--
-- TOC Entry ID 363 (OID 18139922)
--
-- Name: frs_release_package Type: INDEX 
--

CREATE  INDEX frs_release_package on frs_release  ( package_id );

--
-- TOC Entry ID 364 (OID 18139922)
--
-- Name: frs_release_date Type: INDEX 
--

CREATE  INDEX frs_release_date on frs_release  ( release_date );

--
-- TOC Entry ID 365 (OID 18139922)
--
-- Name: frs_release_by Type: INDEX 
--

CREATE  INDEX frs_release_by on frs_release  ( released_by );

--
-- TOC Entry ID 366 (OID 18140030)
--
-- Name: group_cvs_history_group_id Type: INDEX 
--

CREATE  INDEX group_cvs_history_group_id on group_cvs_history  ( group_id );

--
-- TOC Entry ID 367 (OID 18140030)
--
-- Name: user_name_idx Type: INDEX 
--

CREATE  INDEX user_name_idx on group_cvs_history  ( user_name  );

--
-- TOC Entry ID 368 (OID 18140074)
--
-- Name: group_history_group_id Type: INDEX 
--

CREATE  INDEX group_history_group_id on group_history  ( group_id );

--
-- TOC Entry ID 369 (OID 18140178)
--
-- Name: groups_unix Type: INDEX 
--

CREATE  INDEX groups_unix on groups  ( unix_group_name  );

--
-- TOC Entry ID 370 (OID 18140178)
--
-- Name: groups_type Type: INDEX 
--

CREATE  INDEX groups_type on groups  ( type );

--
-- TOC Entry ID 371 (OID 18140178)
--
-- Name: groups_public Type: INDEX 
--

CREATE  INDEX groups_public on groups  ( is_public );

--
-- TOC Entry ID 372 (OID 18140178)
--
-- Name: groups_status Type: INDEX 
--

CREATE  INDEX groups_status on groups  ( status  );

--
-- TOC Entry ID 373 (OID 18140319)
--
-- Name: mail_group_list_group Type: INDEX 
--

CREATE  INDEX mail_group_list_group on mail_group_list  ( group_id );

--
-- TOC Entry ID 374 (OID 18140377)
--
-- Name: news_bytes_group Type: INDEX 
--

CREATE  INDEX news_bytes_group on news_bytes  ( group_id );

--
-- TOC Entry ID 375 (OID 18140377)
--
-- Name: news_bytes_approved Type: INDEX 
--

CREATE  INDEX news_bytes_approved on news_bytes  ( is_approved );

--
-- TOC Entry ID 376 (OID 18140377)
--
-- Name: news_bytes_forum Type: INDEX 
--

CREATE  INDEX news_bytes_forum on news_bytes  ( forum_id );

--
-- TOC Entry ID 464 (OID 18140377)
--
-- Name: news_group_date Type: INDEX 
--

CREATE  INDEX news_group_date on news_bytes  ( group_id , sf_date );

--
-- TOC Entry ID 465 (OID 18140377)
--
-- Name: news_approved_date Type: INDEX 
--

CREATE  INDEX news_approved_date on news_bytes  ( is_approved , sf_date );

--
-- TOC Entry ID 377 (OID 18140437)
--
-- Name: patch_group_id Type: INDEX 
--

CREATE  INDEX patch_group_id on patch  ( group_id );

--
-- TOC Entry ID 451 (OID 18140437)
--
-- Name: patch_groupid_assignedto_status Type: INDEX 
--

CREATE  INDEX patch_groupid_assgndto_sts on patch  ( group_id , assigned_to , patch_status_id );

--
-- TOC Entry ID 452 (OID 18140437)
--
-- Name: patch_groupid_assignedto Type: INDEX 
--

CREATE  INDEX patch_groupid_assignedto on patch  ( group_id , assigned_to );

--
-- TOC Entry ID 453 (OID 18140437)
--
-- Name: patch_groupid_status Type: INDEX 
--

CREATE  INDEX patch_groupid_status on patch  ( group_id , patch_status_id );

--
-- TOC Entry ID 378 (OID 18140501)
--
-- Name: patch_group_group_id Type: INDEX 
--

CREATE  INDEX patch_group_group_id on patch_category  ( group_id );

--
-- TOC Entry ID 379 (OID 18140552)
--
-- Name: patch_history_patch_id Type: INDEX 
--

CREATE  INDEX patch_history_patch_id on patch_history  ( patch_id );

--
-- TOC Entry ID 461 (OID 18140656)
--
-- Name: people_job_group_id Type: INDEX 
--

CREATE  INDEX people_job_group_id on people_job  ( group_id );

--
-- TOC Entry ID 380 (OID 18141038)
--
-- Name: project_assgnd_to_assgnd_to Type: INDEX 
--

CREATE  INDEX project_assgnd_to_assgnd_to on project_assigned_to  ( assigned_to_id );

--
-- TOC Entry ID 381 (OID 18141038)
--
-- Name: project_assigned_to_task_id Type: INDEX 
--

CREATE  INDEX project_assigned_to_task_id on project_assigned_to  ( project_task_id );

--
-- TOC Entry ID 382 (OID 18141128)
--
-- Name: project_is_depndnt_on_tsk_id Type: INDEX 
--

CREATE  INDEX project_is_depndnt_on_tsk_id on project_dependencies  ( is_dependent_on_task_id );

--
-- TOC Entry ID 383 (OID 18141128)
--
-- Name: project_dependencies_task_id Type: INDEX 
--

CREATE  INDEX project_dependencies_task_id on project_dependencies  ( project_task_id );

--
-- TOC Entry ID 384 (OID 18141164)
--
-- Name: project_group_list_group_id Type: INDEX 
--

CREATE  INDEX project_group_list_group_id on project_group_list  ( group_id );

--
-- TOC Entry ID 385 (OID 18141218)
--
-- Name: project_history_task_id Type: INDEX 
--

CREATE  INDEX project_history_task_id on project_history  ( project_task_id );

--
-- TOC Entry ID 386 (OID 18141275)
--
-- Name: project_metric_group Type: INDEX 
--

CREATE  INDEX project_metric_group on project_metric  ( group_id );

--
-- TOC Entry ID 387 (OID 18141430)
--
-- Name: project_task_group_project_id Type: INDEX 
--

CREATE  INDEX project_task_group_project_id on project_task  ( group_project_id );

--
-- TOC Entry ID 454 (OID 18141430)
--
-- Name: projecttask_projid_status Type: INDEX 
--

CREATE  INDEX projecttask_projid_status on project_task  ( group_project_id , status_id );

--
-- TOC Entry ID 354 (OID 18141497)
--
-- Name: projectweeklymetric_ranking Type: INDEX 
--

-- Removed because is not needed because is created in table definition
-- CREATE  INDEX projectweeklymetric_ranking on project_weekly_metric  ( ranking );

--
-- TOC Entry ID 388 (OID 18141497)
--
-- Name: project_metric_weekly_group Type: INDEX 
--

CREATE  INDEX project_metric_weekly_group on project_weekly_metric  ( group_id );

--
-- TOC Entry ID 389 (OID 18141514)
--
-- Name: sf_session_user_id Type: INDEX 
--

CREATE  INDEX sf_session_user_id on sf_session  ( user_id );

--
-- TOC Entry ID 390 (OID 18141514)
--
-- Name: sf_session_time Type: INDEX 
--

CREATE  INDEX sf_session_time on sf_session  ( time );

--
-- TOC Entry ID 391 (OID 18141552)
--
-- Name: snippet_language Type: INDEX 
--

CREATE  INDEX snippet_language on snippet  ( language );

--
-- TOC Entry ID 392 (OID 18141552)
--
-- Name: snippet_category Type: INDEX 
--

CREATE  INDEX snippet_category on snippet  ( category );

--
-- TOC Entry ID 393 (OID 18141611)
--
-- Name: snippet_package_language Type: INDEX 
--

CREATE  INDEX snippet_package_language on snippet_package  ( language );

--
-- TOC Entry ID 394 (OID 18141611)
--
-- Name: snippet_package_category Type: INDEX 
--

CREATE  INDEX snippet_package_category on snippet_package  ( category );

--
-- TOC Entry ID 395 (OID 18141666)
--
-- Name: snippet_package_item_pkg_ver Type: INDEX 
--

CREATE  INDEX snippet_package_item_pkg_ver on snippet_package_item  ( snippet_package_version_id );

--
-- TOC Entry ID 396 (OID 18141702)
--
-- Name: snippet_package_version_pkg_id Type: INDEX 
--

CREATE  INDEX snippet_package_version_pkg_id on snippet_package_version  ( snippet_package_id );

--
-- TOC Entry ID 397 (OID 18141757)
--
-- Name: snippet_version_snippet_id Type: INDEX 
--

CREATE  INDEX snippet_version_snippet_id on snippet_version  ( snippet_id );

--
-- TOC Entry ID 398 (OID 18141829)
--
-- Name: pages_by_day_day Type: INDEX 
--

CREATE  INDEX pages_by_day_day on stats_agg_pages_by_day  ( day );

--
-- TOC Entry ID 399 (OID 18141881)
--
-- Name: stats_agr_filerelease_group_id Type: INDEX 
--

CREATE  INDEX stats_agr_filerelease_group_id on stats_agr_filerelease  ( group_id );

--
-- TOC Entry ID 400 (OID 18141881)
--
-- Name: stats_agr_filerelease_filerelea Type: INDEX 
--

CREATE  INDEX stats_agr_filerelease_flrl on stats_agr_filerelease  ( filerelease_id );

--
-- TOC Entry ID 401 (OID 18141896)
--
-- Name: project_agr_log_group Type: INDEX 
--

CREATE  INDEX project_agr_log_group on stats_agr_project  ( group_id );

--
-- TOC Entry ID 402 (OID 18141949)
--
-- Name: ftpdl_group_id Type: INDEX 
--

CREATE  INDEX ftpdl_group_id on stats_ftp_downloads  ( group_id );

--
-- TOC Entry ID 403 (OID 18141949)
--
-- Name: ftpdl_fid Type: INDEX 
--

CREATE  INDEX ftpdl_fid on stats_ftp_downloads  ( filerelease_id );

--
-- TOC Entry ID 404 (OID 18141949)
--
-- Name: ftpdl_day Type: INDEX 
--

CREATE  INDEX ftpdl_day on stats_ftp_downloads  ( day );

--
-- TOC Entry ID 405 (OID 18141966)
--
-- Name: httpdl_group_id Type: INDEX 
--

CREATE  INDEX httpdl_group_id on stats_http_downloads  ( group_id );

--
-- TOC Entry ID 406 (OID 18141966)
--
-- Name: httpdl_fid Type: INDEX 
--

CREATE  INDEX httpdl_fid on stats_http_downloads  ( filerelease_id );

--
-- TOC Entry ID 407 (OID 18141966)
--
-- Name: httpdl_day Type: INDEX 
--

CREATE  INDEX httpdl_day on stats_http_downloads  ( day );

--
-- TOC Entry ID 408 (OID 18141983)
--
-- Name: archive_project_monthday Type: INDEX 
--

CREATE  INDEX archive_project_monthday on stats_project  ( month , day );

--
-- TOC Entry ID 409 (OID 18141983)
--
-- Name: project_log_group Type: INDEX 
--

CREATE  INDEX project_log_group on stats_project  ( group_id );

--
-- TOC Entry ID 410 (OID 18141983)
--
-- Name: archive_project_week Type: INDEX 
--

CREATE  INDEX archive_project_week on stats_project  ( week );

--
-- TOC Entry ID 411 (OID 18141983)
--
-- Name: archive_project_day Type: INDEX 
--

CREATE  INDEX archive_project_day on stats_project  ( day );

--
-- TOC Entry ID 412 (OID 18141983)
--
-- Name: archive_project_month Type: INDEX 
--

CREATE  INDEX archive_project_month on stats_project  ( month );

--
-- TOC Entry ID 413 (OID 18142042)
--
-- Name: stats_project_tmp_group_id Type: INDEX 
--

CREATE  INDEX stats_project_tmp_group_id on stats_project_tmp  ( group_id );

--
-- TOC Entry ID 414 (OID 18142042)
--
-- Name: project_stats_week Type: INDEX 
--

CREATE  INDEX project_stats_week on stats_project_tmp  ( week );

--
-- TOC Entry ID 415 (OID 18142042)
--
-- Name: project_stats_month Type: INDEX 
--

CREATE  INDEX project_stats_month on stats_project_tmp  ( month );

--
-- TOC Entry ID 416 (OID 18142042)
--
-- Name: project_stats_day Type: INDEX 
--

CREATE  INDEX project_stats_day on stats_project_tmp  ( day );

--
-- TOC Entry ID 417 (OID 18142101)
--
-- Name: stats_site_monthday Type: INDEX 
--

CREATE  INDEX stats_site_monthday on stats_site  ( month , day );

--
-- TOC Entry ID 418 (OID 18142101)
--
-- Name: stats_site_week Type: INDEX 
--

CREATE  INDEX stats_site_week on stats_site  ( week );

--
-- TOC Entry ID 419 (OID 18142101)
--
-- Name: stats_site_day Type: INDEX 
--

CREATE  INDEX stats_site_day on stats_site  ( day );

--
-- TOC Entry ID 420 (OID 18142101)
--
-- Name: stats_site_month Type: INDEX 
--

CREATE  INDEX stats_site_month on stats_site  ( month );

--
-- TOC Entry ID 421 (OID 18142150)
--
-- Name: support_group_id Type: INDEX 
--

CREATE  INDEX support_group_id on support  ( group_id );

--
-- TOC Entry ID 448 (OID 18142150)
--
-- Name: support_groupid_assignedto Type: INDEX 
--

CREATE  INDEX support_groupid_assignedto on support  ( group_id , assigned_to );

--
-- TOC Entry ID 449 (OID 18142150)
--
-- Name: support_groupid_assignedto_stat Type: INDEX 
--

CREATE  INDEX support_groupid_assgndto_stt on support  ( group_id , assigned_to , support_status_id );

--
-- TOC Entry ID 450 (OID 18142150)
--
-- Name: support_groupid_status Type: INDEX 
--

CREATE  INDEX support_groupid_status on support  ( group_id , support_status_id );

--
-- TOC Entry ID 422 (OID 18142214)
--
-- Name: support_canned_response_group_i Type: INDEX 
--

CREATE  INDEX support_canned_rspns_grp_i on support_canned_responses  ( group_id );

--
-- TOC Entry ID 423 (OID 18142265)
--
-- Name: support_group_group_id Type: INDEX 
--

CREATE  INDEX support_group_group_id on support_category  ( group_id );

--
-- TOC Entry ID 424 (OID 18142316)
--
-- Name: support_history_support_id Type: INDEX 
--

CREATE  INDEX support_history_support_id on support_history  ( support_id );

--
-- TOC Entry ID 425 (OID 18142372)
--
-- Name: support_messages_support_id Type: INDEX 
--

CREATE  INDEX support_messages_support_id on support_messages  ( support_id );

--
-- TOC Entry ID 426 (OID 18142473)
--
-- Name: supported_languages_code Type: INDEX 
--

CREATE  INDEX supported_languages_code on supported_languages  ( language_code  );

--
-- TOC Entry ID 427 (OID 18142573)
--
-- Name: survey_questions_group Type: INDEX 
--

CREATE  INDEX survey_questions_group on survey_questions  ( group_id );

--
-- TOC Entry ID 428 (OID 18142608)
--
-- Name: survey_rating_aggregate_type_id Type: INDEX 
--

CREATE  INDEX survey_rating_aggrgt_typ_id on survey_rating_aggregate  ( type , id );

--
-- TOC Entry ID 429 (OID 18142625)
--
-- Name: survey_rating_responses_user_ty Type: INDEX 
--

CREATE  INDEX survey_rating_rspns_usr_ty on survey_rating_response  ( user_id , type , id );

--
-- TOC Entry ID 430 (OID 18142625)
--
-- Name: survey_rating_responses_type_id Type: INDEX 
--

CREATE  INDEX survey_rating_rspns_typ_id on survey_rating_response  ( type , id );

--
-- TOC Entry ID 431 (OID 18142644)
--
-- Name: survey_responses_group_id Type: INDEX 
--

CREATE  INDEX survey_responses_group_id on survey_responses  ( group_id );

--
-- TOC Entry ID 432 (OID 18142644)
--
-- Name: survey_responses_user_survey_qu Type: INDEX 
--

CREATE  INDEX survey_rspns_usr_srvy_qu on survey_responses  ( user_id , survey_id , question_id );

--
-- TOC Entry ID 433 (OID 18142644)
--
-- Name: survey_responses_user_survey Type: INDEX 
--

CREATE  INDEX survey_responses_user_survey on survey_responses  ( user_id , survey_id );

--
-- TOC Entry ID 434 (OID 18142644)
--
-- Name: survey_responses_survey_questio Type: INDEX 
--

CREATE  INDEX survey_rspns_srvy_quest on survey_responses  ( survey_id , question_id );

--
-- TOC Entry ID 435 (OID 18142698)
--
-- Name: surveys_group Type: INDEX 
--

CREATE  INDEX surveys_group on surveys  ( group_id );

--
-- TOC Entry ID 436 (OID 18143071)
--
-- Name: rank_forumposts_week_idx Type: INDEX 
--

CREATE  INDEX rank_forumposts_week_idx on top_group  ( rank_forumposts_week );

--
-- TOC Entry ID 437 (OID 18143071)
--
-- Name: rank_downloads_week_idx Type: INDEX 
--

CREATE  INDEX rank_downloads_week_idx on top_group  ( rank_downloads_week );

--
-- TOC Entry ID 438 (OID 18143071)
--
-- Name: pageviews_proj_idx Type: INDEX 
--

CREATE  INDEX pageviews_proj_idx on top_group  ( pageviews_proj );

--
-- TOC Entry ID 439 (OID 18143071)
--
-- Name: rank_userrank_idx Type: INDEX 
--

CREATE  INDEX rank_userrank_idx on top_group  ( rank_userrank );

--
-- TOC Entry ID 440 (OID 18143071)
--
-- Name: rank_downloads_all_idx Type: INDEX 
--

CREATE  INDEX rank_downloads_all_idx on top_group  ( rank_downloads_all );

--
-- TOC Entry ID 441 (OID 18143131)
--
-- Name: parent_idx Type: INDEX 
--

CREATE  INDEX parent_idx on trove_cat  ( parent );

--
-- TOC Entry ID 442 (OID 18143131)
--
-- Name: root_parent_idx Type: INDEX 
--

CREATE  INDEX root_parent_idx on trove_cat  ( root_parent );

--
-- TOC Entry ID 443 (OID 18143131)
--
-- Name: version_idx Type: INDEX 
--

CREATE  INDEX version_idx on trove_cat  ( version );

--
-- TOC Entry ID 444 (OID 18143194)
--
-- Name: trove_group_link_group_id Type: INDEX 
--

CREATE  INDEX trove_group_link_group_id on trove_group_link  ( group_id );

--
-- TOC Entry ID 445 (OID 18143194)
--
-- Name: trove_group_link_cat_id Type: INDEX 
--

CREATE  INDEX trove_group_link_cat_id on trove_group_link  ( trove_cat_id );

--
-- TOC Entry ID 446 (OID 18143304)
--
-- Name: user_bookmark_user_id Type: INDEX 
--

CREATE  INDEX user_bookmark_user_id on user_bookmarks  ( user_id );

--
-- TOC Entry ID 304 (OID 18143355)
--
-- Name: user_diary_user Type: INDEX 
--

CREATE  INDEX user_diary_user on user_diary  ( user_id );

--
-- TOC Entry ID 305 (OID 18143355)
--
-- Name: user_diary_user_date Type: INDEX 
--

CREATE  INDEX user_diary_user_date on user_diary  ( user_id , date_posted );

--
-- TOC Entry ID 306 (OID 18143355)
--
-- Name: user_diary_date Type: INDEX 
--

CREATE  INDEX user_diary_date on user_diary  ( date_posted );

--
-- TOC Entry ID 307 (OID 18143410)
--
-- Name: user_diary_monitor_user Type: INDEX 
--

CREATE  INDEX user_diary_monitor_user on user_diary_monitor  ( user_id );

--
-- TOC Entry ID 308 (OID 18143410)
--
-- Name: user_diary_monitor_monitored_us Type: INDEX 
--

CREATE  INDEX user_diary_monitor_mntrd_us on user_diary_monitor  ( monitored_user );

--
-- TOC Entry ID 309 (OID 18143446)
--
-- Name: user_group_group_id Type: INDEX 
--

CREATE  INDEX user_group_group_id on user_group  ( group_id );

--
-- TOC Entry ID 310 (OID 18143446)
--
-- Name: bug_flags_idx Type: INDEX 
--

CREATE  INDEX bug_flags_idx on user_group  ( bug_flags );

--
-- TOC Entry ID 311 (OID 18143446)
--
-- Name: project_flags_idx Type: INDEX 
--

CREATE  INDEX project_flags_idx on user_group  ( project_flags );

--
-- TOC Entry ID 312 (OID 18143446)
--
-- Name: user_group_user_id Type: INDEX 
--

CREATE  INDEX user_group_user_id on user_group  ( user_id );

--
-- TOC Entry ID 313 (OID 18143446)
--
-- Name: admin_flags_idx Type: INDEX 
--

CREATE  INDEX admin_flags_idx on user_group  ( admin_flags  );

--
-- TOC Entry ID 314 (OID 18143446)
--
-- Name: forum_flags_idx Type: INDEX 
--

CREATE  INDEX forum_flags_idx on user_group  ( forum_flags );

--
-- TOC Entry ID 315 (OID 18143548)
--
-- Name: user_metric0_user_id Type: INDEX 
--

CREATE  INDEX user_metric0_user_id on user_metric0  ( user_id );

--
-- TOC Entry ID 455 (OID 18143576)
--
-- Name: user_pref_user_id Type: INDEX 
--

CREATE  INDEX user_pref_user_id on user_preferences  ( user_id );

--
-- TOC Entry ID 456 (OID 18143591)
--
-- Name: user_ratings_rated_by Type: INDEX 
--

CREATE  INDEX user_ratings_rated_by on user_ratings  ( rated_by );

--
-- TOC Entry ID 457 (OID 18143591)
--
-- Name: user_ratings_user_id Type: INDEX 
--

CREATE  INDEX user_ratings_user_id on user_ratings  ( user_id );

--
-- TOC Entry ID 447 (OID 18143626)
--
-- Name: users_status Type: INDEX 
--

CREATE  INDEX users_status on users  ( status  );

--
-- TOC Entry ID 458 (OID 18143626)
--
-- Name: user_user Type: INDEX 
--

-- Removed because is not needed because is created in table definition
-- CREATE  INDEX user_user on users  ( status  );

--
-- TOC Entry ID 459 (OID 18143626)
--
-- Name: idx_users_username Type: INDEX 
--

CREATE  INDEX idx_users_username on users  ( user_name );

--
-- TOC Entry ID 462 (OID 18143626)
--
-- Name: users_user_pw Type: INDEX 
--

CREATE  INDEX users_user_pw on users  ( user_pw  );

--
-- TOC Entry ID 460 (OID 27311451)
--
-- Name: troveagg_trovecatid Type: INDEX 
--

CREATE  INDEX troveagg_trovecatid on trove_agg  ( trove_cat_id );

--
-- TOC Entry ID 536 (OID 27311269)
--
-- Name: RI_ConstraintTrigger_27311268 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_user_id_fk AFTER INSERT OR UPdate ON user_group  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 537 (OID 27311271)
--
-- Name: RI_ConstraintTrigger_27311270 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_user_id_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 538 (OID 27311273)
--
-- Name: RI_ConstraintTrigger_27311272 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_user_id_fk AFTER UPdate ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('user_group_user_id_fk', 'user_group', 'users', 'FULL', 'user_id', 'user_id');

--
-- TOC Entry ID 535 (OID 27311275)
--
-- Name: RI_ConstraintTrigger_27311274 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_group_id_fk AFTER INSERT OR UPDATE ON user_group  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 506 (OID 27311277)
--
-- Name: RI_ConstraintTrigger_27311276 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_group_id_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 505 (OID 27311279)
--
-- Name: RI_ConstraintTrigger_27311278 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER user_group_group_id_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('user_group_group_id_fk', 'user_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 487 (OID 27311281)
--
-- Name: RI_ConstraintTrigger_27311280 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER INSERT OR UPDATE ON forum  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 539 (OID 27311283)
--
-- Name: RI_ConstraintTrigger_27311282 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 540 (OID 27311285)
--
-- Name: RI_ConstraintTrigger_27311284 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 486 (OID 27311287)
--
-- Name: RI_ConstraintTrigger_27311286 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER INSERT OR UPDATE ON forum  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 492 (OID 27311289)
--
-- Name: RI_ConstraintTrigger_27311288 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER DELETE ON forum_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 491 (OID 27311291)
--
-- Name: RI_ConstraintTrigger_27311290 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER UPDATE ON forum_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 490 (OID 27311293)
--
-- Name: RI_ConstraintTrigger_27311292 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_list_group_id_fk AFTER INSERT OR UPDATE ON forum_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 504 (OID 27311295)
--
-- Name: RI_ConstraintTrigger_27311294 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_list_group_id_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 503 (OID 27311297)
--
-- Name: RI_ConstraintTrigger_27311296 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_list_group_id_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('forum_group_list_group_id_fk', 'forum_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 479 (OID 27311299)
--
-- Name: RI_ConstraintTrigger_27311298 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_group_fk AFTER INSERT OR UPDATE ON bug_group  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 502 (OID 27311301)
--
-- Name: RI_ConstraintTrigger_27311300 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_group_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 501 (OID 27311303)
--
-- Name: RI_ConstraintTrigger_27311302 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_group_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_group_group_fk', 'bug_group', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 476 (OID 27311305)
--
-- Name: RI_ConstraintTrigger_27311304 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_group_fk AFTER INSERT OR UPDATE ON bug_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 500 (OID 27311307)
--
-- Name: RI_ConstraintTrigger_27311306 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_group_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 499 (OID 27311309)
--
-- Name: RI_ConstraintTrigger_27311308 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_group_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_category_group_fk', 'bug_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 473 (OID 27311311)
--
-- Name: RI_ConstraintTrigger_27311310 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_submitted_by_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 541 (OID 27311313)
--
-- Name: RI_ConstraintTrigger_27311312 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_submitted_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 542 (OID 27311315)
--
-- Name: RI_ConstraintTrigger_27311314 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_submitted_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_submitted_by_fk', 'bug', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 472 (OID 27311317)
--
-- Name: RI_ConstraintTrigger_27311316 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_assigned_to_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 543 (OID 27311319)
--
-- Name: RI_ConstraintTrigger_27311318 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_assigned_to_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 544 (OID 27311321)
--
-- Name: RI_ConstraintTrigger_27311320 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_assigned_to_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_assigned_to_fk', 'bug', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 471 (OID 27311323)
--
-- Name: RI_ConstraintTrigger_27311322 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_status_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 483 (OID 27311325)
--
-- Name: RI_ConstraintTrigger_27311324 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_status_fk AFTER DELETE ON bug_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 482 (OID 27311327)
--
-- Name: RI_ConstraintTrigger_27311326 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_status_fk AFTER UPDATE ON bug_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_status_fk', 'bug', 'bug_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 470 (OID 27311329)
--
-- Name: RI_ConstraintTrigger_27311328 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');

--
-- TOC Entry ID 475 (OID 27311331)
--
-- Name: RI_ConstraintTrigger_27311330 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_fk AFTER DELETE ON bug_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');

--
-- TOC Entry ID 474 (OID 27311333)
--
-- Name: RI_ConstraintTrigger_27311332 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_category_fk AFTER UPDATE ON bug_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_category_fk', 'bug', 'bug_category', 'FULL', 'category_id', 'bug_category_id');

--
-- TOC Entry ID 469 (OID 27311335)
--
-- Name: RI_ConstraintTrigger_27311334 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_resolution_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');

--
-- TOC Entry ID 481 (OID 27311337)
--
-- Name: RI_ConstraintTrigger_27311336 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_resolution_fk AFTER DELETE ON bug_resolution  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');

--
-- TOC Entry ID 480 (OID 27311339)
--
-- Name: RI_ConstraintTrigger_27311338 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_resolution_fk AFTER UPDATE ON bug_resolution  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_resolution_fk', 'bug', 'bug_resolution', 'FULL', 'resolution_id', 'resolution_id');

--
-- TOC Entry ID 468 (OID 27311341)
--
-- Name: RI_ConstraintTrigger_27311340 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_fk AFTER INSERT OR UPDATE ON bug  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');

--
-- TOC Entry ID 478 (OID 27311343)
--
-- Name: RI_ConstraintTrigger_27311342 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_fk AFTER DELETE ON bug_group  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');

--
-- TOC Entry ID 477 (OID 27311345)
--
-- Name: RI_ConstraintTrigger_27311344 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER bug_group_fk AFTER UPDATE ON bug_group  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('bug_group_fk', 'bug', 'bug_group', 'FULL', 'bug_group_id', 'bug_group_id');

--
-- TOC Entry ID 485 (OID 27311347)
--
-- Name: RI_ConstraintTrigger_27311346 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER INSERT OR UPDATE ON forum  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 545 (OID 27311349)
--
-- Name: RI_ConstraintTrigger_27311348 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 546 (OID 27311351)
--
-- Name: RI_ConstraintTrigger_27311350 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_posted_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('forum_posted_by_fk', 'forum', 'users', 'FULL', 'posted_by', 'user_id');

--
-- TOC Entry ID 484 (OID 27311353)
--
-- Name: RI_ConstraintTrigger_27311352 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER INSERT OR UPDATE ON forum  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 489 (OID 27311355)
--
-- Name: RI_ConstraintTrigger_27311354 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER DELETE ON forum_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 488 (OID 27311357)
--
-- Name: RI_ConstraintTrigger_27311356 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER forum_group_forum_id_fk AFTER UPDATE ON forum_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('forum_group_forum_id_fk', 'forum', 'forum_group_list', 'FULL', 'group_forum_id', 'group_forum_id');

--
-- TOC Entry ID 518 (OID 27311359)
--
-- Name: RI_ConstraintTrigger_27311358 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_group_list_group_id_fk AFTER INSERT OR UPDATE ON project_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 498 (OID 27311361)
--
-- Name: RI_ConstraintTrigger_27311360 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_group_list_group_id_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 497 (OID 27311363)
--
-- Name: RI_ConstraintTrigger_27311362 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_group_list_group_id_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('project_group_list_group_id_fk', 'project_group_list', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 523 (OID 27311365)
--
-- Name: RI_ConstraintTrigger_27311364 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_group_project_id_f AFTER INSERT OR UPDATE ON project_task  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 517 (OID 27311367)
--
-- Name: RI_ConstraintTrigger_27311366 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_group_project_id_f AFTER DELETE ON project_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 516 (OID 27311369)
--
-- Name: RI_ConstraintTrigger_27311368 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_group_project_id_f AFTER UPDATE ON project_group_list  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('project_task_group_project_id_f', 'project_task', 'project_group_list', 'FULL', 'group_project_id', 'group_project_id');

--
-- TOC Entry ID 522 (OID 27311371)
--
-- Name: RI_ConstraintTrigger_27311370 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_created_by_fk AFTER INSERT OR UPDATE ON project_task  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 547 (OID 27311373)
--
-- Name: RI_ConstraintTrigger_27311372 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_created_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 548 (OID 27311375)
--
-- Name: RI_ConstraintTrigger_27311374 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_created_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('project_task_created_by_fk', 'project_task', 'users', 'FULL', 'created_by', 'user_id');

--
-- TOC Entry ID 521 (OID 27311377)
--
-- Name: RI_ConstraintTrigger_27311376 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_status_id_fk AFTER INSERT OR UPDATE ON project_task  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 520 (OID 27311379)
--
-- Name: RI_ConstraintTrigger_27311378 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_status_id_fk AFTER DELETE ON project_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 519 (OID 27311381)
--
-- Name: RI_ConstraintTrigger_27311380 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER project_task_status_id_fk AFTER UPDATE ON project_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('project_task_status_id_fk', 'project_task', 'project_status', 'FULL', 'status_id', 'status_id');

--
-- TOC Entry ID 510 (OID 27311383)
--
-- Name: RI_ConstraintTrigger_27311382 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_status_id_fk AFTER INSERT OR UPDATE ON patch  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');

--
-- TOC Entry ID 515 (OID 27311385)
--
-- Name: RI_ConstraintTrigger_27311384 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_status_id_fk AFTER DELETE ON patch_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');

--
-- TOC Entry ID 514 (OID 27311387)
--
-- Name: RI_ConstraintTrigger_27311386 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_status_id_fk AFTER UPDATE ON patch_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('patch_status_id_fk', 'patch', 'patch_status', 'FULL', 'patch_status_id', 'patch_status_id');

--
-- TOC Entry ID 509 (OID 27311389)
--
-- Name: RI_ConstraintTrigger_27311388 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_id_fk AFTER INSERT OR UPDATE ON patch  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');

--
-- TOC Entry ID 513 (OID 27311391)
--
-- Name: RI_ConstraintTrigger_27311390 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_id_fk AFTER DELETE ON patch_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');

--
-- TOC Entry ID 512 (OID 27311393)
--
-- Name: RI_ConstraintTrigger_27311392 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_id_fk AFTER UPDATE ON patch_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('patch_category_id_fk', 'patch', 'patch_category', 'FULL', 'patch_category_id', 'patch_category_id');

--
-- TOC Entry ID 508 (OID 27311395)
--
-- Name: RI_ConstraintTrigger_27311394 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_submitted_by_fk AFTER INSERT OR UPDATE ON patch  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 549 (OID 27311397)
--
-- Name: RI_ConstraintTrigger_27311396 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_submitted_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 550 (OID 27311399)
--
-- Name: RI_ConstraintTrigger_27311398 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_submitted_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('patch_submitted_by_fk', 'patch', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 507 (OID 27311401)
--
-- Name: RI_ConstraintTrigger_27311400 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_assigned_to_fk AFTER INSERT OR UPDATE ON patch  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 551 (OID 27311403)
--
-- Name: RI_ConstraintTrigger_27311402 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_assigned_to_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 552 (OID 27311405)
--
-- Name: RI_ConstraintTrigger_27311404 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_assigned_to_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('patch_assigned_to_fk', 'patch', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 511 (OID 27311407)
--
-- Name: RI_ConstraintTrigger_27311406 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_group_id_fk AFTER INSERT OR UPDATE ON patch_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 496 (OID 27311409)
--
-- Name: RI_ConstraintTrigger_27311408 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_group_id_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 495 (OID 27311411)
--
-- Name: RI_ConstraintTrigger_27311410 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER patch_category_group_id_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('patch_category_group_id_fk', 'patch_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 527 (OID 27311413)
--
-- Name: RI_ConstraintTrigger_27311412 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_status_id_fk AFTER INSERT OR UPDATE ON support  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');

--
-- TOC Entry ID 532 (OID 27311415)
--
-- Name: RI_ConstraintTrigger_27311414 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_status_id_fk AFTER DELETE ON support_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');

--
-- TOC Entry ID 531 (OID 27311417)
--
-- Name: RI_ConstraintTrigger_27311416 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_status_id_fk AFTER UPDATE ON support_status  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('support_status_id_fk', 'support', 'support_status', 'FULL', 'support_status_id', 'support_status_id');

--
-- TOC Entry ID 526 (OID 27311419)
--
-- Name: RI_ConstraintTrigger_27311418 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_id_fk AFTER INSERT OR UPDATE ON support  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');

--
-- TOC Entry ID 530 (OID 27311421)
--
-- Name: RI_ConstraintTrigger_27311420 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_id_fk AFTER DELETE ON support_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');

--
-- TOC Entry ID 529 (OID 27311423)
--
-- Name: RI_ConstraintTrigger_27311422 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_id_fk AFTER UPDATE ON support_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('support_category_id_fk', 'support', 'support_category', 'FULL', 'support_category_id', 'support_category_id');

--
-- TOC Entry ID 525 (OID 27311425)
--
-- Name: RI_ConstraintTrigger_27311424 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_submitted_by_fk AFTER INSERT OR UPDATE ON support  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 553 (OID 27311427)
--
-- Name: RI_ConstraintTrigger_27311426 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_submitted_by_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 554 (OID 27311429)
--
-- Name: RI_ConstraintTrigger_27311428 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_submitted_by_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('support_submitted_by_fk', 'support', 'users', 'FULL', 'submitted_by', 'user_id');

--
-- TOC Entry ID 524 (OID 27311431)
--
-- Name: RI_ConstraintTrigger_27311430 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_assigned_to_fk AFTER INSERT OR UPDATE ON support  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 555 (OID 27311433)
--
-- Name: RI_ConstraintTrigger_27311432 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_assigned_to_fk AFTER DELETE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 556 (OID 27311435)
--
-- Name: RI_ConstraintTrigger_27311434 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_assigned_to_fk AFTER UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('support_assigned_to_fk', 'support', 'users', 'FULL', 'assigned_to', 'user_id');

--
-- TOC Entry ID 528 (OID 27311437)
--
-- Name: RI_ConstraintTrigger_27311436 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_group_id_fk AFTER INSERT OR UPDATE ON support_category  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 494 (OID 27311439)
--
-- Name: RI_ConstraintTrigger_27311438 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_group_id_fk AFTER DELETE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 493 (OID 27311441)
--
-- Name: RI_ConstraintTrigger_27311440 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER support_category_group_id_fk AFTER UPDATE ON groups  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('support_category_group_id_fk', 'support_category', 'groups', 'FULL', 'group_id', 'group_id');

--
-- TOC Entry ID 557 (OID 27311443)
--
-- Name: RI_ConstraintTrigger_27311442 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER users_languageid_fk AFTER INSERT OR UPDATE ON users  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_check_ins ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 534 (OID 27311445)
--
-- Name: RI_ConstraintTrigger_27311444 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER users_languageid_fk AFTER DELETE ON supported_languages  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_del ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 533 (OID 27311447)
--
-- Name: RI_ConstraintTrigger_27311446 Type: TRIGGER 
--

-- CREATE CONSTRAINT TRIGGER users_languageid_fk AFTER UPDATE ON supported_languages  NOT DEFERRABLE INITIALLY IMMEDIATE FOR EACH ROW EXECUTE PROCEDURE RI_FKey_noaction_upd ('users_languageid_fk', 'users', 'supported_languages', 'FULL', 'language', 'language_id');

--
-- TOC Entry ID 3 (OID 18138427)
--
-- Name: bug_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_pk_seq', 125359, 't');

--
-- TOC Entry ID 5 (OID 18138495)
--
-- Name: bug_bug_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_bug_dependencies_pk_seq', 44691, 't');

--
-- TOC Entry ID 7 (OID 18138531)
--
-- Name: bug_canned_responses_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_canned_responses_pk_seq', 100204, 't');

--
-- TOC Entry ID 9 (OID 18138582)
--
-- Name: bug_category_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_category_pk_seq', 5053, 't');

--
-- TOC Entry ID 11 (OID 18138632)
--
-- Name: bug_filter_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_filter_pk_seq', 140, 't');

--
-- TOC Entry ID 13 (OID 18138687)
--
-- Name: bug_group_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_group_pk_seq', 2780, 't');

--
-- TOC Entry ID 15 (OID 18138738)
--
-- Name: bug_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_history_pk_seq', 106196, 't');

--
-- TOC Entry ID 17 (OID 18138794)
--
-- Name: bug_resolution_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_resolution_pk_seq', 101, 't');

--
-- TOC Entry ID 19 (OID 18138843)
--
-- Name: bug_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_status_pk_seq', 100, 't');

--
-- TOC Entry ID 21 (OID 18138891)
--
-- Name: bug_task_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('bug_task_dependencies_pk_seq', 44583, 't');

--
-- TOC Entry ID 23 (OID 18138927)
--
-- Name: canned_responses_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('canned_responses_pk_seq', 5, 't');

--
-- TOC Entry ID 25 (OID 18138977)
--
-- Name: db_images_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('db_images_pk_seq', 1128, 't');

--
-- TOC Entry ID 27 (OID 18139040)
--
-- Name: doc_data_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('doc_data_pk_seq', 2124, 't');

--
-- TOC Entry ID 29 (OID 18139104)
--
-- Name: doc_groups_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('doc_groups_pk_seq', 1815, 't');

--
-- TOC Entry ID 31 (OID 18139140)
--
-- Name: doc_states_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('doc_states_pk_seq', 5, 't');

--
-- TOC Entry ID 33 (OID 18139174)
--
-- Name: filemodule_monitor_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('filemodule_monitor_pk_seq', 312, 't');

--
-- TOC Entry ID 35 (OID 18139210)
--
-- Name: forum_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('forum_pk_seq', 84486, 't');

--
-- TOC Entry ID 37 (OID 18139291)
--
-- Name: forum_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('forum_group_list_pk_seq', 51981, 't');

--
-- TOC Entry ID 39 (OID 18139348)
--
-- Name: forum_monitored_forums_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('forum_monitored_forums_pk_seq', 14831, 't');

--
-- TOC Entry ID 41 (OID 18139384)
--
-- Name: forum_saved_place_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('forum_saved_place_pk_seq', 1835, 't');

--
-- TOC Entry ID 43 (OID 18139492)
--
-- Name: foundry_news_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('foundry_news_pk_seq', 1973, 't');

--
-- TOC Entry ID 45 (OID 18139532)
--
-- Name: foundry_preferred_projec_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('foundry_preferred_projec_pk_seq', 165, 't');

--
-- TOC Entry ID 47 (OID 18139570)
--
-- Name: foundry_projects_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('foundry_projects_pk_seq', 320807, 't');

--
-- TOC Entry ID 49 (OID 18139695)
--
-- Name: frs_file_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_file_pk_seq', 29214, 't');

--
-- TOC Entry ID 51 (OID 18139756)
--
-- Name: frs_filetype_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_filetype_pk_seq', 9999, 't');

--
-- TOC Entry ID 53 (OID 18139804)
--
-- Name: frs_package_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_package_pk_seq', 12688, 't');

--
-- TOC Entry ID 55 (OID 18139856)
--
-- Name: frs_processor_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_processor_pk_seq', 9999, 't');

--
-- TOC Entry ID 57 (OID 18139904)
--
-- Name: frs_release_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_release_pk_seq', 17983, 't');

--
-- TOC Entry ID 59 (OID 18139964)
--
-- Name: frs_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('frs_status_pk_seq', 3, 't');

--
-- TOC Entry ID 61 (OID 18140012)
--
-- Name: group_cvs_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('group_cvs_history_pk_seq', 1, 'f');

--
-- TOC Entry ID 63 (OID 18140056)
--
-- Name: group_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('group_history_pk_seq', 29283, 't');

--
-- TOC Entry ID 65 (OID 18140112)
--
-- Name: group_type_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('group_type_pk_seq', 2, 't');

--
-- TOC Entry ID 67 (OID 18140160)
--
-- Name: groups_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('groups_pk_seq', 16379, 't');

--
-- TOC Entry ID 69 (OID 18140301)
--
-- Name: mail_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('mail_group_list_pk_seq', 7581, 't');

--
-- TOC Entry ID 71 (OID 18140359)
--
-- Name: news_bytes_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('news_bytes_pk_seq', 10299, 't');

--
-- TOC Entry ID 73 (OID 18140419)
--
-- Name: patch_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('patch_pk_seq', 102785, 't');

--
-- TOC Entry ID 75 (OID 18140483)
--
-- Name: patch_category_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('patch_category_pk_seq', 10607, 't');

--
-- TOC Entry ID 77 (OID 18140534)
--
-- Name: patch_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('patch_history_pk_seq', 9813, 't');

--
-- TOC Entry ID 79 (OID 18140590)
--
-- Name: patch_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('patch_status_pk_seq', 103, 't');

--
-- TOC Entry ID 81 (OID 18140638)
--
-- Name: people_job_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_job_pk_seq', 1641, 't');

--
-- TOC Entry ID 83 (OID 18140697)
--
-- Name: people_job_category_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_job_category_pk_seq', 102, 't');

--
-- TOC Entry ID 85 (OID 18140747)
--
-- Name: people_job_inventory_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_job_inventory_pk_seq', 1970, 't');

--
-- TOC Entry ID 87 (OID 18140787)
--
-- Name: people_job_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_job_status_pk_seq', 3, 't');

--
-- TOC Entry ID 89 (OID 18140835)
--
-- Name: people_skill_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_skill_pk_seq', 33, 't');

--
-- TOC Entry ID 91 (OID 18140884)
--
-- Name: people_skill_inventory_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_skill_inventory_pk_seq', 60179, 't');

--
-- TOC Entry ID 93 (OID 18140924)
--
-- Name: people_skill_level_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_skill_level_pk_seq', 5, 't');

--
-- TOC Entry ID 95 (OID 18140972)
--
-- Name: people_skill_year_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('people_skill_year_pk_seq', 5, 't');

--
-- TOC Entry ID 97 (OID 18141020)
--
-- Name: project_assigned_to_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_assigned_to_pk_seq', 30257, 't');

--
-- TOC Entry ID 99 (OID 18141110)
--
-- Name: project_dependencies_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_dependencies_pk_seq', 25231, 't');

--
-- TOC Entry ID 101 (OID 18141146)
--
-- Name: project_group_list_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_group_list_pk_seq', 6360, 't');

--
-- TOC Entry ID 103 (OID 18141200)
--
-- Name: project_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_history_pk_seq', 27347, 't');

--
-- TOC Entry ID 105 (OID 18141257)
--
-- Name: project_metric_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_metric_pk_seq', 13274, 't');

--
-- TOC Entry ID 107 (OID 18141292)
--
-- Name: project_metric_tmp1_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_metric_tmp1_pk_seq', 13274, 't');

--
-- TOC Entry ID 109 (OID 18141327)
--
-- Name: project_metric_weekly_tm_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_metric_weekly_tm_pk_seq', 2213, 't');

--
-- TOC Entry ID 111 (OID 18141363)
--
-- Name: project_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_status_pk_seq', 100, 't');

--
-- TOC Entry ID 113 (OID 18141412)
--
-- Name: project_task_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_task_pk_seq', 23295, 't');

--
-- TOC Entry ID 115 (OID 18141479)
--
-- Name: project_weekly_metric_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('project_weekly_metric_pk_seq', 2213, 't');

--
-- TOC Entry ID 117 (OID 18141534)
--
-- Name: snippet_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('snippet_pk_seq', 100501, 't');

--
-- TOC Entry ID 119 (OID 18141593)
--
-- Name: snippet_package_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('snippet_package_pk_seq', 100035, 't');

--
-- TOC Entry ID 121 (OID 18141648)
--
-- Name: snippet_package_item_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('snippet_package_item_pk_seq', 100100, 't');

--
-- TOC Entry ID 123 (OID 18141684)
--
-- Name: snippet_package_version_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('snippet_package_version_pk_seq', 100035, 't');

--
-- TOC Entry ID 125 (OID 18141739)
--
-- Name: snippet_version_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('snippet_version_pk_seq', 100662, 't');

--
-- TOC Entry ID 127 (OID 18142132)
--
-- Name: support_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_pk_seq', 109672, 't');

--
-- TOC Entry ID 129 (OID 18142196)
--
-- Name: support_canned_responses_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_canned_responses_pk_seq', 100088, 't');

--
-- TOC Entry ID 131 (OID 18142247)
--
-- Name: support_category_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_category_pk_seq', 10699, 't');

--
-- TOC Entry ID 133 (OID 18142298)
--
-- Name: support_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_history_pk_seq', 24027, 't');

--
-- TOC Entry ID 135 (OID 18142354)
--
-- Name: support_messages_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_messages_pk_seq', 122077, 't');

--
-- TOC Entry ID 137 (OID 18142407)
--
-- Name: support_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('support_status_pk_seq', 3, 't');

--
-- TOC Entry ID 139 (OID 18142455)
--
-- Name: supported_languages_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('supported_languages_pk_seq', 21, 't');

--
-- TOC Entry ID 141 (OID 18142506)
--
-- Name: survey_question_types_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('survey_question_types_pk_seq', 100, 't');

--
-- TOC Entry ID 143 (OID 18142555)
--
-- Name: survey_questions_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('survey_questions_pk_seq', 14662, 't');

--
-- TOC Entry ID 145 (OID 18142680)
--
-- Name: surveys_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('surveys_pk_seq', 11185, 't');

--
-- TOC Entry ID 147 (OID 18142735)
--
-- Name: system_history_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('system_history_pk_seq', 1, 'f');

--
-- TOC Entry ID 149 (OID 18142787)
--
-- Name: system_machines_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('system_machines_pk_seq', 1, 'f');

--
-- TOC Entry ID 151 (OID 18142836)
--
-- Name: system_news_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('system_news_pk_seq', 1, 'f');

--
-- TOC Entry ID 153 (OID 18142895)
--
-- Name: system_services_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('system_services_pk_seq', 1, 'f');

--
-- TOC Entry ID 155 (OID 18142944)
--
-- Name: system_status_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('system_status_pk_seq', 1, 'f');

--
-- TOC Entry ID 157 (OID 18143020)
--
-- Name: themes_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('themes_pk_seq', 2, 't');

--
-- TOC Entry ID 159 (OID 18143113)
--
-- Name: trove_cat_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('trove_cat_pk_seq', 281, 't');

--
-- TOC Entry ID 161 (OID 18143176)
--
-- Name: trove_group_link_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('trove_group_link_pk_seq', 111628, 't');

--
-- TOC Entry ID 163 (OID 18143216)
--
-- Name: trove_treesums_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('trove_treesums_pk_seq', 765, 't');

--
-- TOC Entry ID 165 (OID 18143286)
--
-- Name: user_bookmarks_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_bookmarks_pk_seq', 23482, 't');

--
-- TOC Entry ID 167 (OID 18143337)
--
-- Name: user_diary_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_diary_pk_seq', 892, 't');

--
-- TOC Entry ID 169 (OID 18143392)
--
-- Name: user_diary_monitor_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_diary_monitor_pk_seq', 521, 't');

--
-- TOC Entry ID 171 (OID 18143428)
--
-- Name: user_group_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_group_pk_seq', 27204, 't');

--
-- TOC Entry ID 173 (OID 18143484)
--
-- Name: user_metric_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_metric_pk_seq', 115, 't');

--
-- TOC Entry ID 175 (OID 18143530)
--
-- Name: user_metric0_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('user_metric0_pk_seq', 5, 't');

--
-- TOC Entry ID 177 (OID 18143608)
--
-- Name: users_pk_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('users_pk_seq', 120800, 't');

--
-- TOC Entry ID 179 (OID 27311232)
--
-- Name: unix_uid_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('unix_uid_seq', 21044, 't');

--
-- TOC Entry ID 181 (OID 27311250)
--
-- Name: forum_thread_seq Type: SEQUENCE SET Owner: 
--

-- SELECT setval ('forum_thread_seq', 59698, 't');


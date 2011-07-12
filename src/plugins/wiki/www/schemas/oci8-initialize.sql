-- $Id: oci8-initialize.sql 6203 2008-08-26 13:23:56Z vargenau $

set verify off
set feedback off

--================================================================
-- Prefix for table names.
--
-- You should set this to the same value you specify for
-- DATABASE_PREFIX in config.ini
--
-- You have to use a prefix, because some phpWiki tablenames are
-- Oracle reserved words!

define prefix=phpwiki_

--================================================================
--
-- Don't modify below this point unless you know what you are doing.
--
--================================================================

--================================================================
-- Note on Oracle datatypes...
--
-- Most of the 'NOT NULL' constraints on the character columns have been
-- 	dropped since they can contain empty strings which are seen by
--	Oracle as NULL.
-- Oracle CLOBs are used for TEXTs/MEDUIMTEXTs columns.


prompt Initializing PhpWiki tables with:
prompt        prefix =  &prefix
prompt
prompt Expect some 'ORA-00942: table or view does not exist' unless you are
prompt overwriting existing tables.
prompt

define page_tbl=&prefix.page
define page_id=&prefix.page_id
define page_nm=&prefix.page_nm

define version_tbl=&prefix.version
define vers_id=&prefix.vers_id
define vers_mtime=&prefix.vers_mtime

define recent_tbl=&prefix.recent
define recent_id=&prefix.recent_id

define nonempty_tbl=&prefix.nonempty
define nonmt_id=&prefix.nonmt_id

define link_tbl=&prefix.link
define link_from=&prefix.link_from
define link_to=&prefix.link_to
define link_rel=&prefix.link_rel

define session_tbl=&prefix.session
define sess_id=&prefix.sess_id
define sess_date=&prefix.sess_date
define sess_ip=&prefix.sess_ip

define pref_tbl=&prefix.pref
define pref_id=&prefix.pref_id

--define user_tbl=&prefix.user
--define user_id=&prefix.user_id

define member_tbl=&prefix.member
define member_userid=&prefix.member_userid
define member_groupname=&prefix.member_groupname

define rating_tbl=&prefix.rating
define rating_id=&prefix.rating_id
define rating_dimension=&prefix.rating_dimension
define rating_raterpage=&prefix.rating_raterpage
define rating_rateepage=&prefix.rating_rateepage

define accesslog_tbl=&prefix.accesslog
define accesslog_time=&prefix.log_time
define accesslog_host=&prefix.log_host

prompt Creating &page_tbl
CREATE TABLE &page_tbl (
	id		INT NOT NULL,
        pagename	VARCHAR(100) NOT NULL,
	hits		INT DEFAULT 0 NOT NULL,
        pagedata	CLOB DEFAULT '',
	cached_html 	CLOB DEFAULT '',   -- added with 1.3.11
	CONSTRAINT &page_id PRIMARY KEY (id),
	CONSTRAINT &page_nm UNIQUE (pagename)
);

-- we use 0 <=> global_data to satisfy the relation = 0 constraint
INSERT INTO &page_tbl VALUES (0,'global_data',0,'','');

prompt Creating &version_tbl
CREATE TABLE &version_tbl (
	id		INT NOT NULL,
        version		INT NOT NULL,
	mtime		INT NOT NULL,
	minor_edit	INT DEFAULT 0,
        content		CLOB DEFAULT '',
        versiondata	CLOB DEFAULT '',
	CONSTRAINT &vers_id PRIMARY KEY (id,version)
);
CREATE INDEX &vers_mtime ON &version_tbl (mtime);

prompt Creating &recent_tbl
CREATE TABLE &recent_tbl (
	id		INT NOT NULL,
	latestversion	INT,
	latestmajor	INT,
	latestminor	INT,
	CONSTRAINT &recent_id PRIMARY KEY (id)
);

prompt Creating &nonempty_tbl
CREATE TABLE &nonempty_tbl (
	id		INT NOT NULL,
	CONSTRAINT &nonempty_tbl PRIMARY KEY (id)
);

prompt Creating &link_tbl
CREATE TABLE &link_tbl (
        linkfrom	INT NOT NULL,
        linkto		INT NOT NULL,
        relation  	INT
);
CREATE INDEX &link_from ON &link_tbl (linkfrom);
CREATE INDEX &link_to   ON &link_tbl (linkto);
CREATE INDEX &link_rel  ON &link_tbl (relation);

prompt Creating &session_tbl
CREATE TABLE &session_tbl (
	sess_id 	CHAR(32) DEFAULT '',
    	sess_data 	CLOB,
    	sess_date 	INT,
    	sess_ip 	CHAR(40) NOT NULL,
	CONSTRAINT &sess_id PRIMARY KEY (sess_id)
);
CREATE INDEX &sess_date ON &session_tbl (sess_date);
CREATE INDEX &sess_ip   ON &session_tbl (sess_ip);

-- Optional DB Auth and Prefs
-- For these tables below the default table prefix must be used
-- in the DBAuthParam SQL statements also.

prompt Creating &pref_tbl
CREATE TABLE &pref_tbl (
  	userid 	CHAR(48) NOT NULL,
  	prefs  	CLOB DEFAULT '',
	passwd  CHAR(48) DEFAULT '',
	groupname CHAR(48) DEFAULT 'users',
	CONSTRAINT &pref_id PRIMARY KEY (userid)
);

-- better use the extra pref table where such users can be created easily
-- without password.
--prompt Creating &user_tbl
--CREATE TABLE &user_tbl (
--  	userid 	CHAR(48) NOT NULL,
--  	passwd 	CHAR(48) DEFAULT '',
--	prefs  	CLOB DEFAULT '',
--	groupname CHAR(48) DEFAULT 'users',
--  	CONSTRAINT &user_id PRIMARY KEY (userid)
--);

prompt Creating &member_tbl
CREATE TABLE &member_tbl (
	userid    CHAR(48) NOT NULL,
   	groupname CHAR(48) DEFAULT 'users' NOT NULL
);
CREATE INDEX &member_userid ON &member_tbl (userid);
CREATE INDEX &member_groupname ON &member_tbl (groupname);

-- if you plan to use the wikilens theme
prompt Creating &rating_tbl
CREATE TABLE &rating_tbl (
        dimension NUMBER(4) NOT NULL,
        raterpage NUMBER(11) NOT NULL,
        rateepage NUMBER(11) NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion NUMBER(11) NOT NULL,
        tstamp TIMESTAMP NOT NULL,
        CONSTRAINT &rating_id PRIMARY KEY (dimension, raterpage, rateepage)
);
CREATE INDEX &rating_dimension ON &rating_tbl (dimension);
CREATE INDEX &rating_raterpage ON &rating_tbl (raterpage);
CREATE INDEX &rating_rateepage ON &rating_tbl (rateepage);

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
prompt Creating &accesslog_tbl
CREATE TABLE &accesslog_tbl (
-- for OCI 9i+ use:   time_stamp TIMESTAMP,
        time_stamp    DATE,
	remote_host   VARCHAR2(100),
	remote_user   VARCHAR2(50),
        request_method VARCHAR2(10),
	request_line  VARCHAR2(255),
	request_args  VARCHAR2(255),
	request_file  VARCHAR2(255),
	request_uri   VARCHAR2(255),
	request_time  VARCHAR2(28),
	status 	      NUMBER(4),
	bytes_sent    NUMBER,
        referer       VARCHAR(255),
	agent         VARCHAR(255),
	request_duration FLOAT
);
CREATE INDEX &accesslog_time ON &accesslog_tbl (time_stamp);
CREATE INDEX &accesslog_host ON &accesslog_tbl (remote_host);

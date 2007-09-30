-- $Id: mysql-test-initialize.sql,v 1.3 2004/12/22 15:05:18 rurban Exp $
-- for the regression suite

\set prefix 	'test_'

\set page_tbl 		:prefix 'page'
\set version_tbl 	:prefix 'version'
\set recent_tbl		:prefix 'recent'
\set nonempty_tbl	:prefix 'nonempty'
\set link_tbl 		:prefix 'link'
\set session_tbl 	:prefix 'session'
\set pref_tbl 	 	:prefix 'pref'
--\set user_tbl 	 	:prefix 'users'
\set member_tbl  	:prefix 'member'
\set rating_tbl		:prefix 'rating'
\set accesslog_tbl 	:prefix 'accesslog'

\echo Dropping all test relations

DROP TABLE :page_tbl CASCADE;
DROP TABLE :version_tbl CASCADE;
DROP TABLE :recent_tbl CASCADE;
DROP TABLE :nonempty_tbl CASCADE;
DROP TABLE :link_tbl CASCADE;
DROP TABLE :session_tbl CASCADE;
DROP TABLE :pref_tbl CASCADE;
DROP TABLE :member_tbl CASCADE;
DROP TABLE :rating_tbl CASCADE;
DROP TABLE :accesslog_tbl CASCADE;

------------------------------------------------------------

\echo Creating :page_tbl
CREATE TABLE :page_tbl (
	id 		SERIAL PRIMARY KEY,
        pagename 	VARCHAR(100) NOT NULL UNIQUE CHECK (pagename <> ''),
	hits 		INT4 NOT NULL DEFAULT 0,
        pagedata 	TEXT NOT NULL DEFAULT '',
	cached_html  	bytea DEFAULT ''
);
-- CREATE UNIQUE INDEX :page_id_idx ON :page_tbl (id);
-- CREATE UNIQUE INDEX :page_name_idx ON :page_tbl (pagename);

\echo Creating :version_tbl
CREATE TABLE :version_tbl (
	id		INT4 REFERENCES :page_tbl ON DELETE CASCADE,
        version		INT4 NOT NULL,
	mtime		INT4 NOT NULL,
--FIXME: should use boolean, but that returns 't' or 'f'. not 0 or 1. 
	minor_edit	INT2 DEFAULT 0,
        content		TEXT NOT NULL DEFAULT '',
        versiondata	TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX :vers_id_idx ON :version_tbl (id, version);
CREATE INDEX :vers_mtime_idx ON :version_tbl (mtime);

\echo Creating :recent_tbl
CREATE TABLE :recent_tbl (
	id		INT4 REFERENCES :page_tbl ON DELETE CASCADE,
	latestversion	INT4,
	latestmajor	INT4,
	latestminor	INT4
);
CREATE UNIQUE INDEX :recent_id_idx ON :recent_tbl (id);


\echo Creating :nonempty_tbl
CREATE TABLE :nonempty_tbl (
	id		INT4 NOT NULL REFERENCES :page_tbl ON DELETE CASCADE
);
CREATE UNIQUE INDEX :nonmt_id_idx ON :nonempty_tbl (id);

\echo Creating :link_tbl
CREATE TABLE :link_tbl (
        linkfrom	INT4 NOT NULL REFERENCES :page_tbl ON DELETE CASCADE,
        linkto		INT4 NOT NULL REFERENCES :page_tbl ON DELETE CASCADE
);
CREATE INDEX :link_from_idx ON :link_tbl (linkfrom);
CREATE INDEX :link_to_idx   ON :link_tbl (linkto);

-- if you plan to use the wikilens theme
\echo Creating :rating_tbl
CREATE TABLE :rating_tbl (
        dimension INTEGER NOT NULL,
        raterpage INT8 NOT NULL REFERENCES :page_tbl ON DELETE CASCADE,
        rateepage INT8 NOT NULL REFERENCES :page_tbl ON DELETE CASCADE,
        ratingvalue FLOAT NOT NULL,
        rateeversion INT8 NOT NULL,
        tstamp TIMESTAMP NOT NULL
);
CREATE UNIQUE INDEX :rating_id_idx ON :rating_tbl (dimension, raterpage, rateepage);

--================================================================
-- end of page relations
--================================================================

\echo Creating :session_tbl
CREATE TABLE :session_tbl (
	sess_id 	CHAR(32) PRIMARY KEY,
    	sess_data 	bytea NOT NULL,
    	sess_date 	INT4,
    	sess_ip 	CHAR(40) NOT NULL
);
-- CREATE UNIQUE INDEX :sess_id_idx ON :session_tbl (sess_id);
CREATE INDEX :sess_date_idx ON :session_tbl (sess_date);
CREATE INDEX :sess_ip_idx   ON :session_tbl (sess_ip);

-- Optional DB Auth and Prefs
-- For these tables below the default table prefix must be used 
-- in the DBAuthParam SQL statements also.

\echo Creating :pref_tbl
CREATE TABLE :pref_tbl (
  	userid 	CHAR(48) PRIMARY KEY,
  	prefs  	TEXT NULL DEFAULT '',
	passwd  CHAR(48) DEFAULT '',
	groupname CHAR(48) DEFAULT 'users'
);
-- CREATE UNIQUE INDEX :pref_id_idx ON :pref_tbl (userid);

-- Use the member table, if you need it for n:m user-group relations,
-- and adjust your DBAUTH_AUTH_ SQL statements.
CREATE TABLE :member_tbl (
	userid CHAR(48) NOT NULL REFERENCES :pref_tbl ON DELETE CASCADE, 
	groupname CHAR(48) NOT NULL DEFAULT 'users'
);
CREATE INDEX :member_id_idx    ON :member_tbl (userid);
CREATE INDEX :member_group_idx ON :member_tbl (groupname);

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
\echo Creating :accesslog_tbl
CREATE TABLE :accesslog_tbl (
        time_stamp    INT,
	remote_host   VARCHAR(50),
	remote_user   VARCHAR(50),
        request_method VARCHAR(10),
	request_line  VARCHAR(255),
	request_args  VARCHAR(255),
	request_file  VARCHAR(255),
	request_uri   VARCHAR(255),
	request_time  CHAR(28),
	status 	      INT2,
	bytes_sent    INT4,
        referer       VARCHAR(255), 
	agent         VARCHAR(255),
	request_duration FLOAT
);
CREATE INDEX :accesslog_time_idx ON :accesslog_tbl (time_stamp);
CREATE INDEX :accesslog_host_idx ON :accesslog_tbl (remote_host);

\set httpd_user	'wikiuser'

-- FIXME: vacuum needs table owner rights
\echo Applying permissions for role :httpd_user
GRANT SELECT,INSERT,UPDATE,DELETE ON :page_tbl		TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :version_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :recent_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :nonempty_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :link_tbl		TO :httpd_user;

GRANT SELECT,INSERT,UPDATE,DELETE ON :session_tbl	TO :httpd_user;
-- you may want to fine tune this:
GRANT SELECT,INSERT,UPDATE,DELETE ON :pref_tbl		TO :httpd_user;
-- GRANT SELECT ON :user_tbl	TO :httpd_user;
GRANT SELECT ON :member_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :rating_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :accesslog_tbl	TO :httpd_user;

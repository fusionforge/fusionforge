-- $Id: psql-initialize.sql,v 1.4 2005/06/21 05:59:18 rurban Exp $

\set QUIET

--================================================================
-- Prefix for table names.
--
-- You should set this to the same value you specify for
-- DATABASE_PREFIX in config/config.ini

\set prefix 	'plugin_wiki2_'

--================================================================
-- Which postgres user gets access to the tables?
--
-- You should set this to the name of the postgres
-- user who will be accessing the tables.
-- See DATABASE_DSN in config.ini
--
-- NOTE: To be able to vacuum the tables from ordinary page requests
--       :httpd_user must be the table owner.
--       To run autovacuum and disable page requests vacuums edit the 
--       pqsql backend optimize method.
--
-- Commonly, connections from php are made under
-- the user name of 'nobody', 'apache' or 'www'.

\set httpd_user	'gforge'

--================================================================
--
-- Don't modify below this point unless you know what you are doing.
--
--================================================================

\set qprefix '\'' :prefix '\''
\set qhttp_user '\'' :httpd_user '\''

\echo At first init the database with: 
\echo '$ createdb phpwiki'
\echo '$ createuser -S -R -d ' :qhttp_user
\echo '$ psql -U ' :qhttp_user ' phpwiki < /usr/share/postgresql/contrib/tsearch2.sql'
\echo '$ psql -U ' :qhttp_user ' phpwiki < psql-initialize.sql'

\echo Initializing PhpWiki tables with:
\echo '       prefix = ' :qprefix


\echo '   httpd_user = ' :qhttp_user
\echo
\echo 'Expect some \'NOTICE:  CREATE ... will create implicit sequence/index ...\' messages '

\set page_tbl		:prefix 'page'
\set page_id		:prefix 'page_id'
\set page_nm		:prefix 'page_nm'

\set version_tbl	:prefix 'version'
\set vers_id		:prefix 'vers_id'
\set vers_mtime		:prefix 'vers_mtime'

\set recent_tbl		:prefix 'recent'
\set recent_id		:prefix 'recent_id'

\set nonempty_tbl	:prefix 'nonempty'
\set nonmt_id		:prefix 'nonmt_id'

\set link_tbl		:prefix 'link'
\set link_from		:prefix 'link_from'
\set link_to		:prefix 'link_to'

\set session_tbl	:prefix 'session'
\set sess_id		:prefix 'sess_id'
\set sess_date		:prefix 'sess_date'
\set sess_ip		:prefix 'sess_ip'

\set pref_tbl		'pref'
\set pref_id_idx 	'pref_id_idx'
\set pref_pkey 		'pref_pkey'

\set member_tbl  	:prefix 'member'
\set member_id_idx  	:prefix 'member_id_idx'
\set member_group_idx 	:prefix 'member_group_idx'

\set rating_tbl		:prefix 'rating'
\set rating_id		:prefix 'rating_id'

\set accesslog_tbl	:prefix 'accesslog'
\set accesslog_time	:prefix 'log_time'
\set accesslog_host     :prefix 'log_host'

\echo Creating :page_tbl
CREATE TABLE :page_tbl (
	id		INT NOT NULL,
        pagename	VARCHAR(100) NOT NULL,
	hits		INT NOT NULL DEFAULT 0,
        pagedata	TEXT NOT NULL DEFAULT '',
	cached_html 	TEXT DEFAULT ''    -- added with 1.3.11
);
CREATE UNIQUE INDEX :page_id ON :page_tbl (id);
CREATE UNIQUE INDEX :page_nm ON :page_tbl (pagename);

\echo Creating :version_tbl
CREATE TABLE :version_tbl (
	id		INT NOT NULL,
        version		INT NOT NULL,
	mtime		INT NOT NULL,
--FIXME: should use boolean, but that returns 't' or 'f'. not 0 or 1. 
	minor_edit	INT2 DEFAULT 0,
        content		TEXT NOT NULL DEFAULT '',
        versiondata	TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX :vers_id ON :version_tbl (id,version);
CREATE INDEX :vers_mtime ON :version_tbl (mtime);

\echo Creating :recent_tbl
CREATE TABLE :recent_tbl (
	id		INT NOT NULL,
	latestversion	INT,
	latestmajor	INT,
	latestminor	INT
);
CREATE UNIQUE INDEX :recent_id ON :recent_tbl (id);


\echo Creating :nonempty_tbl
CREATE TABLE :nonempty_tbl (
	id		INT NOT NULL
);
CREATE UNIQUE INDEX :nonmt_id
	ON :nonempty_tbl (id);

\echo Creating :link_tbl
CREATE TABLE :link_tbl (
        linkfrom	INT NOT NULL,
        linkto		INT NOT NULL
);
CREATE INDEX :link_from ON :link_tbl (linkfrom);
CREATE INDEX :link_to   ON :link_tbl (linkto);

-- Use the user and member tables - if you need them - from the other schemas
-- and adjust your DBAUTH_AUTH_ SQL statements

-- if you plan to use the wikilens theme
\echo Creating :rating_tbl
CREATE TABLE :rating_tbl (
        dimension INTEGER NOT NULL,
        raterpage BIGINT NOT NULL,
        rateepage BIGINT NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion BIGINT NOT NULL,
        tstamp TIMESTAMP NOT NULL
);
CREATE UNIQUE INDEX :rating_id ON :rating_tbl (dimension, raterpage, rateepage);

--================================================================
-- end of page relations
--================================================================

\echo Creating :session_tbl
CREATE TABLE :session_tbl (
	sess_id 	CHAR(32) PRIMARY KEY,
    	sess_data 	TEXT NOT NULL,
    	sess_date 	INT,
    	sess_ip 	CHAR(40) NOT NULL
);
CREATE INDEX :sess_date ON :session_tbl (sess_date);
CREATE INDEX :sess_ip   ON :session_tbl (sess_ip);

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
	status 	      SMALLINT,
	bytes_sent    INT,
	referer       VARCHAR(255), 
	agent         VARCHAR(255),
	request_duration FLOAT
);
CREATE INDEX :accesslog_time ON :accesslog_tbl (time_stamp);
CREATE INDEX :accesslog_host ON :accesslog_tbl (remote_host);
-- create extra indices on demand (usually referer. see plugin/AccessLogSql)

GRANT SELECT,INSERT,UPDATE,DELETE ON :page_tbl		TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :version_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :recent_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :nonempty_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :link_tbl		TO :httpd_user;

GRANT SELECT,INSERT,UPDATE,DELETE ON :session_tbl	TO :httpd_user;
-- you may want to fine tune this:
GRANT SELECT,INSERT,UPDATE,DELETE ON :pref_tbl		TO :httpd_user;
-- GRANT SELECT ON :user_tbl	TO :httpd_user;
-- GRANT SELECT ON :member_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :rating_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :accesslog_tbl	TO :httpd_user;

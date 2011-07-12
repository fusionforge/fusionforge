-- $Id: psql-initialize.sql 6481 2009-02-05 14:48:07Z vargenau $

\set QUIET

--================================================================
-- Prefix for table names.
--
-- You should set this to the same value you specify for
-- DATABASE_PREFIX in config/config.ini

\set prefix 	''

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
--       pqsql backend optimize method in lib/WikiDB/backend/*_psql.php
--
-- Commonly, connections from php are made under
-- the user name of 'nobody', 'apache' or 'www'.

\set httpd_user	'wikiuser'

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
\echo '$ psql -U ' :qhttp_user ' phpwiki < /usr/share/pgsql/contrib/tsearch2.sql'
\echo '$ psql -U ' :qhttp_user ' phpwiki < psql-initialize.sql'

\echo Initializing PhpWiki tables with:
\echo '       prefix = ' :qprefix
\echo '   httpd_user = ' :qhttp_user
\echo
\echo 'Expect some \'NOTICE:  CREATE ... will create implicit sequence/index ...\' messages '

\set page_tbl 		:prefix 'page'
\set page_id_seq 	:prefix 'page_id_seq'
\set page_id_idx 	:prefix 'page_id_idx'
\set page_name_idx 	:prefix 'page_name_idx'

\set version_tbl 	:prefix 'version'
\set vers_id_idx 	:prefix 'vers_id_idx'
\set vers_mtime_idx 	:prefix 'vers_mtime_idx'

\set recent_tbl  	:prefix 'recent'
\set recent_id_idx 	:prefix 'recent_id_idx'
\set recent_lv_idx 	:prefix 'recent_lv_idx'

\set nonempty_tbl 	:prefix 'nonempty'
\set nonmt_id_idx 	:prefix 'nonmt_id_idx'

\set link_tbl  		:prefix 'link'
\set link_from_idx 	:prefix 'link_from_idx'
\set link_to_idx 	:prefix 'link_to_idx'
\set relation_idx 	:prefix 'relation_idx'

\set pagedata_tbl 	:prefix 'pagedata'
\set pagedata_id_idx 	:prefix 'pagedata_id_idx'
\set versiondata_tbl 	:prefix 'versiondata'
\set pageperm_tbl 	:prefix 'pageperm'
\set pageperm_id_idx	:prefix 'pageperm_id_idx'
\set pageperm_access_idx :prefix 'pageperm_access_idx'
-- \set existing_page_view :prefix 'existing_page'
-- \set curr_page_view	:prefix 'curr_page'

\set session_tbl 	:prefix 'session'
\set sess_id_idx 	:prefix 'sess_id_idx'
\set sess_date_idx 	:prefix 'sess_date_idx'
\set sess_ip_idx 	:prefix 'sess_ip_idx'

\set pref_tbl 	 	:prefix 'pref'
\set pref_id_idx 	:prefix 'pref_id_idx'
--\set user_tbl 	 	:prefix 'users'
--\set user_id_idx  	:prefix 'users_id_idx'
\set member_tbl  	:prefix 'member'
\set member_id_idx  	:prefix 'member_id_idx'
\set member_group_idx 	:prefix 'member_group_idx'

\set rating_tbl		:prefix 'rating'
\set rating_id_idx 	:prefix 'rating_id_idx'

\set accesslog_tbl 	:prefix 'accesslog'
\set accesslog_time_idx :prefix 'log_time_idx'
\set accesslog_host_idx :prefix 'log_host_idx'

\set update_recent_fn	:prefix 'update_recent'
\set prepare_rename_fn	:prefix 'prepare_rename_page'

\echo Creating :page_tbl
CREATE TABLE :page_tbl (
	id 		SERIAL PRIMARY KEY,
        pagename 	VARCHAR(100) NOT NULL UNIQUE CHECK (pagename <> ''),
	hits 		INT4 NOT NULL DEFAULT 0,
        pagedata 	TEXT NOT NULL DEFAULT '',
	--cached_html  	bytea DEFAULT ''
	cached_html  	TEXT DEFAULT ''
);
-- CREATE UNIQUE INDEX :page_id_idx ON :page_tbl (id);
-- CREATE UNIQUE INDEX :page_name_idx ON :page_tbl (pagename);

-- we use 0 <=> global_data to satisfy the relation = 0 constraint
INSERT INTO :page_tbl VALUES (0,'global_data',0,'','');

\echo Creating :version_tbl
CREATE TABLE :version_tbl (
	id		INT4 REFERENCES :page_tbl,
        version		INT4 NOT NULL,
	mtime		INT4 NOT NULL,
-- FIXME: should use boolean, but that returns 't' or 'f'. not 0 or 1.
	minor_edit	INT2 DEFAULT 0,
-- use bytea instead?
        content		TEXT NOT NULL DEFAULT '',
        versiondata	TEXT NOT NULL DEFAULT ''
);
CREATE UNIQUE INDEX :vers_id_idx ON :version_tbl (id, version);
CREATE INDEX :vers_mtime_idx ON :version_tbl (mtime);
-- deletion order: version, recent, nonempty

\echo Creating :recent_tbl
CREATE TABLE :recent_tbl (
	id		INT4 REFERENCES :page_tbl,
	latestversion	INT4,
	latestmajor	INT4,
	latestminor	INT4,
	FOREIGN KEY (id, latestversion) REFERENCES :version_tbl (id, version),
	CHECK (latestminor >= latestmajor)
);
CREATE UNIQUE INDEX :recent_id_idx ON :recent_tbl (id);
CREATE INDEX :recent_lv_idx ON :recent_tbl (latestversion);

\echo Creating :nonempty_tbl
CREATE TABLE :nonempty_tbl (
	id		INT4 NOT NULL REFERENCES :page_tbl
);
CREATE UNIQUE INDEX :nonmt_id_idx ON :nonempty_tbl (id);

\echo Creating experimental pagedata (not yet used)
CREATE TABLE :pagedata_tbl (
	id	INT4 NOT NULL REFERENCES :page_tbl,
	date    INT4,
	locked  BOOLEAN,
        rest	TEXT NOT NULL DEFAULT ''
);
CREATE INDEX :pagedata_id_idx ON pagedata (id);

\echo Creating experimental versiondata (not yet used)
CREATE TABLE :versiondata_tbl (
	id	  INT4 NOT NULL,
	version	  INT4 NOT NULL,
	markup    INT2 DEFAULT 2,
	author    VARCHAR(48),
	author_id VARCHAR(48),
	pagetype  VARCHAR(20) DEFAULT 'wikitext',
        rest	  TEXT NOT NULL DEFAULT '',
	FOREIGN KEY (id, version) REFERENCES :version_tbl (id, version)
);
\echo Creating experimental pageperm (not yet used)
CREATE TABLE :pageperm_tbl (
	id	 INT4 NOT NULL REFERENCES :page_tbl(id),
        -- view,edit,create,list,remove,change,dump
	access   CHAR(12) NOT NULL,
	groupname VARCHAR(48),
	allowed  BOOLEAN
);
CREATE INDEX :pageperm_id_idx ON pageperm (id);
CREATE INDEX :pageperm_access_idx ON pageperm (access);

-- \echo Creating experimental page views (not yet used)
--
-- nonempty versiondata
-- CREATE VIEW :existing_page_view AS
--   SELECT * FROM :page_tbl P INNER JOIN :nonempty_tbl N USING (id);
--
-- latest page version
-- CREATE VIEW :curr_page_view AS
--  SELECT P.id,P.pagename,P.hits,P.pagedata,P.cached_html,
--	 V.version,V.mtime,V.minor_edit,V.content,V.versiondata
--  FROM :page_tbl P
--    JOIN :version_tbl V USING (id)
--    JOIN :recent_tbl  R ON (V.id=R.id AND V.version=R.latestversion);

\echo Creating :link_tbl
CREATE TABLE :link_tbl (
        linkfrom  INT4 NOT NULL REFERENCES :page_tbl,
        linkto 	  INT4 NOT NULL REFERENCES :page_tbl,
        relation  INT4
);
CREATE INDEX :link_from_idx ON :link_tbl (linkfrom);
CREATE INDEX :link_to_idx   ON :link_tbl (linkto);
CREATE INDEX :relation_idx  ON :link_tbl (relation);
-- update:
-- ALTER TABLE link DROP CONSTRAINT link_relation_fkey;

-- if you plan to use the wikilens theme
\echo Creating :rating_tbl
CREATE TABLE :rating_tbl (
        dimension    INTEGER NOT NULL,
        raterpage    INT8 NOT NULL REFERENCES :page_tbl,
        rateepage    INT8 NOT NULL REFERENCES :page_tbl,
        ratingvalue  FLOAT NOT NULL,
        rateeversion INT8 NOT NULL,
        tstamp       TIMESTAMP NOT NULL
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
  	userid 	  CHAR(48) PRIMARY KEY,
  	prefs  	  TEXT NULL DEFAULT '',
	passwd    CHAR(48) DEFAULT '',
	groupname CHAR(48) DEFAULT 'users'
);
-- CREATE UNIQUE INDEX :pref_id_idx ON :pref_tbl (userid);
CREATE INDEX pref_group_idx ON :pref_tbl (groupname);

-- Use the member table, if you need it for n:m user-group relations,
-- and adjust your DBAUTH_AUTH_ SQL statements.
CREATE TABLE :member_tbl (
	userid    CHAR(48) NOT NULL REFERENCES :pref_tbl,
	groupname CHAR(48) NOT NULL DEFAULT 'users'
);
CREATE INDEX :member_id_idx    ON :member_tbl (userid);
CREATE INDEX :member_group_idx ON :member_tbl (groupname);

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
\echo Creating :accesslog_tbl
CREATE TABLE :accesslog_tbl (
        time_stamp       INT,
	remote_host      VARCHAR(100),
	remote_user      VARCHAR(50),
        request_method   VARCHAR(10),
	request_line     VARCHAR(255),
	request_args     VARCHAR(255),
	request_file     VARCHAR(255),
	request_uri      VARCHAR(255),
	request_time     CHAR(28),
	status 	         INT2,
	bytes_sent       INT4,
        referer          VARCHAR(255),
	agent            VARCHAR(255),
	request_duration FLOAT
);
CREATE INDEX :accesslog_time_idx ON :accesslog_tbl (time_stamp);
CREATE INDEX :accesslog_host_idx ON :accesslog_tbl (remote_host);
-- create extra indices on demand (usually referer. see plugin/AccessLogSql)

--================================================================

-- Use the tsearch2 fulltextsearch extension: (recommended) 7.4, 8.0, 8.1
-- at first init it for the database:
-- $ psql phpwiki < /usr/share/postgresql/contrib/tsearch2.sql

-- example of ISpell dictionary
--   UPDATE pg_ts_dict SET dict_initoption='DictFile="/usr/local/share/ispell/russian.dict" ,AffFile ="/usr/local/share/ispell/russian.aff", StopFile="/usr/local/share/ispell/russian.stop"' WHERE dict_name='ispell_template';
-- example of synonym dict
--   UPDATE pg_ts_dict SET dict_initoption='/usr/local/share/ispell/english.syn' WHERE dict_id=5;

\echo Initializing tsearch2 indices
GRANT SELECT ON pg_ts_dict, pg_ts_parser, pg_ts_cfg, pg_ts_cfgmap TO :httpd_user;
ALTER TABLE :version_tbl ADD COLUMN idxFTI tsvector;
UPDATE :version_tbl SET idxFTI=to_tsvector('default', content);
VACUUM FULL ANALYZE;
CREATE INDEX idxFTI_idx ON :version_tbl USING gist(idxFTI);
VACUUM FULL ANALYZE;
CREATE TRIGGER tsvectorupdate BEFORE UPDATE OR INSERT ON :version_tbl
       FOR EACH ROW EXECUTE PROCEDURE tsearch2(idxFTI, content);

-- this might be needed:
-- see http://www.sai.msu.su/~megera/oddmuse/index.cgi/Tsearch_V2_Notes
-- update pg_ts_cfg set locale='en_US.UTF-8' where ts_name='default';

--================================================================

\echo You might want to ignore the following errors or run
\echo /usr/sbin/createuser -S -R -d  :httpd_user

\echo Applying permissions for role :httpd_user
GRANT SELECT,INSERT,UPDATE,DELETE ON :page_tbl		TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :version_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :recent_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :nonempty_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :link_tbl		TO :httpd_user;

GRANT SELECT,INSERT,UPDATE,DELETE ON :session_tbl	TO :httpd_user;
-- you may want to fine tune this:
GRANT SELECT,INSERT,UPDATE,DELETE ON :pref_tbl		TO :httpd_user;
-- GRANT SELECT ON :user_tbl				TO :httpd_user;
GRANT SELECT ON :member_tbl				TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :rating_tbl	TO :httpd_user;
GRANT SELECT,INSERT,UPDATE,DELETE ON :accesslog_tbl	TO :httpd_user;

--================================================================
-- some stored procedures to put unneccesary syntax into the server

\echo Initializing stored procedures

-- id, version
CREATE OR REPLACE FUNCTION :update_recent_fn (INT4, INT4)
	RETURNS integer AS $$
DELETE FROM recent WHERE id = $1;
INSERT INTO recent (id, latestversion, latestmajor, latestminor)
  SELECT id, MAX(version) AS latestversion,
	     MAX(CASE WHEN minor_edit =  0 THEN version END) AS latestmajor,
             MAX(CASE WHEN minor_edit <> 0 THEN version END) AS latestminor
    FROM version WHERE id = $2 GROUP BY id;
DELETE FROM nonempty WHERE id = $1;
INSERT INTO nonempty (id)
  SELECT recent.id
    FROM recent, version
    WHERE recent.id = version.id
          AND version = latestversion
          AND content <> ''
          AND recent.id = $1;
SELECT id FROM nonempty WHERE id = $1;
$$ LANGUAGE SQL;

-- oldid, newid
CREATE OR REPLACE FUNCTION :prepare_rename_fn (INT4, INT4)
        RETURNS void AS $$
DELETE FROM page     WHERE id = $2;
DELETE FROM version  WHERE id = $2;
DELETE FROM recent   WHERE id = $2;
DELETE FROM nonempty WHERE id = $2;
-- We have to fix all referring tables to the old id
UPDATE link SET linkfrom = $1 WHERE linkfrom = $2;
UPDATE link SET linkto = $1   WHERE linkto = $2;
$$ LANGUAGE SQL;

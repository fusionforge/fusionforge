-- $Id: psql-destroy.sql,v 1.10 2006/05/18 06:08:33 rurban Exp $

\set QUIET

--================================================================
-- Prefix for table names.
--
-- You should set this to the same value you specify for
-- DATABASE_PREFIX in config/config.ini

\set prefix 	'plugin_wiki2_'

--================================================================
--
-- Don't modify below this point unless you know what you are doing.
--
--================================================================

\set qprefix '\'' :prefix '\''
\echo Dropping all PhpWiki tables with:
\echo '       prefix = ' :qprefix
\echo

\set page_tbl		:prefix 'page'
\set page_id_seq 	:prefix 'page_id_seq'
\set version_tbl	:prefix 'version'
\set recent_tbl		:prefix 'recent'
\set nonempty_tbl	:prefix 'nonempty'
\set link_tbl		:prefix 'link'
\set session_tbl	:prefix 'session'
\set pref_tbl		:prefix 'pref'
\set user_tbl	        :prefix 'user'
\set member_tbl 	:prefix 'member'
\set rating_tbl		:prefix 'rating'
\set accesslog_tbl	:prefix 'accesslog'

\echo Dropping table :version_tbl
DROP TABLE :version_tbl CASCADE;

\echo Dropping table :recent_tbl
DROP TABLE :recent_tbl CASCADE;

\echo Dropping table :nonempty_tbl
DROP TABLE :nonempty_tbl CASCADE;

\echo Dropping experimental pagedata tables (not yet used)
DROP TABLE pagedata CASCADE;
DROP TABLE versiondata CASCADE;
DROP TABLE pageperm CASCADE;

\echo Dropping table :link_tbl
DROP TABLE :link_tbl;

\echo Dropping table :rating_tbl
DROP TABLE :rating_tbl;

\set existing_page :prefix 'existing_page'
\echo Dropping view :existing_page
DROP VIEW :existing_page;

\set curr_page     :prefix 'curr_page'
\echo Dropping view :curr_page
DROP VIEW :curr_page;

\echo Dropping table :page_tbl
DROP TABLE :page_tbl CASCADE;
\echo Dropping :page_id_seq only needed for postgresql < 7.2

\echo Dropping table :member_tbl
DROP TABLE :member_tbl;

\echo Dropping table :pref_tbl
DROP TABLE :pref_tbl;

--\echo Dropping table :user_tbl
DROP TABLE :user_tbl;

\echo Dropping table :session_tbl
DROP TABLE :session_tbl;

\echo Dropping table :accesslog_tbl
DROP TABLE :accesslog_tbl;

\echo Dropping stored procedures
DROP FUNCTION update_recent (INT4, INT4);
DROP FUNCTION prepare_rename_page (INT4, INT4);

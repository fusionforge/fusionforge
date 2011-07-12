-- $Id: psql-1_3_12.sql 6203 2008-08-26 13:23:56Z vargenau $

\set QUIET

-- Init the database with:
-- $ /usr/sbin/createdb phpwiki
-- $ /usr/sbin/createuser -S -R -d phpwiki # (see httpd_user below)
-- $ /usr/bin/psql phpwiki < /usr/share/postgresql/contrib/tsearch2.sql
-- $ /usr/bin/psql phpwiki < psql-initialize.sql

--================================================================
-- Prefix for table names.
--
-- You should set this to the same value you specified for
-- DATABASE_PREFIX in config.ini

\set prefix 	''

--================================================================
-- Which postgres user gets access to the tables?
--
-- You should set this to the name of the postgres
-- user who will be accessing the tables.
-- See DATABASE_DSN in config.ini
--
-- Commonly, connections from php are made under
-- the user name of 'nobody', 'apache' or 'www'.

\set httpd_user	'phpwiki'

--================================================================
--
-- Don't modify below this point unless you know what you are doing.
--
--================================================================

\set page_tbl 		:prefix 'page'
\set page_id_seq 	:prefix 'page_id_seq'
\set page_id_idx 	:prefix 'page_id_idx'
\set page_name_idx 	:prefix 'page_name_idx'

\set version_tbl 	:prefix 'version'
\set vers_id_idx 	:prefix 'vers_id_idx'
\set vers_mtime_idx 	:prefix 'vers_mtime_idx'

\set recent_tbl		:prefix 'recent'
\set recent_id_idx 	:prefix 'recent_id_idx'

\set nonempty_tbl	:prefix 'nonempty'
\set nonmt_id_idx 	:prefix 'nonmt_id_idx'

\set link_tbl 		:prefix 'link'
\set link_from_idx 	:prefix 'link_from_idx'
\set link_to_idx 	:prefix 'link_to_idx'
\set relation_idx 	:prefix 'relation_idx'

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

--================================================================
\echo schema enhancements

ALTER TABLE :page_tbl
	ALTER COLUMN id TYPE SERIAL /* PRIMARY KEY */,
        ALTER COLUMN pagename TYPE VARCHAR(100),
	ALTER COLUMN pagename SET NOT NULL,
	ADD UNIQUE(pagename),
	ADD CHECK (pagename <> '');
ALTER TABLE :version_tbl
	ALTER COLUMN id TYPE INT4,
        ADD FOREIGN KEY (id) REFERENCES :page_tbl ON DELETE CASCADE;
ALTER TABLE :nonempty_tbl
	ALTER COLUMN id TYPE INT4,
        ADD FOREIGN KEY (id) REFERENCES :page_tbl ON DELETE CASCADE;

\echo Creating experimental page views (not yet used)

-- nonempty versiondata
CREATE VIEW existing_page AS
  SELECT * FROM :page_tbl P INNER JOIN :nonempty_tbl N USING (id);

-- latest page version
CREATE VIEW curr_page AS
  SELECT P.id,P.pagename,P.hits,P.pagedata,P.cached_html,
	 V.version,V.mtime,V.minor_edit,V.content,V.versiondata
  FROM :page_tbl P
    JOIN :version_tbl V USING (id)
    JOIN :recent_tbl  R ON (V.id=R.id AND V.version=R.latestversion);

ALTER TABLE :link_tbl
	ALTER COLUMN linkfrom TYPE INT4,
	ALTER COLUMN linkto   TYPE INT4,
	ADD COLUMN   relation INT4 REFERENCES :page_tbl (id) ON DELETE CASCADE,
        ADD FOREIGN KEY (linkfrom) REFERENCES :page_tbl (id) ON DELETE CASCADE,
        ADD FOREIGN KEY (linkto)   REFERENCES :page_tbl (id) ON DELETE CASCADE;
CREATE INDEX :relation_idx ON :link_tbl (relation);
ALTER TABLE :rating_tbl
	ALTER COLUMN raterpage TYPE INT8,
	ALTER COLUMN rateepage TYPE INT8,
        ADD FOREIGN KEY (raterpage) REFERENCES :page_tbl (id) ON DELETE CASCADE,
        ADD FOREIGN KEY (rateepage) REFERENCES :page_tbl (id) ON DELETE CASCADE;
ALTER TABLE :member_tbl
	ALTER COLUMN userid TYPE CHAR(48),
	ALTER COLUMN userid SET NOT NULL,
	ADD FOREIGN KEY (userid) REFERENCES :pref_tbl;

--================================================================

\echo add tsearch2 fulltextsearch extension
-- Use the tsearch2 fulltextsearch extension: (recommended) 7.4, 8.0, 8.1
-- At first init it for the database:

-- example of ISpell dictionary
--   UPDATE pg_ts_dict SET dict_initoption='DictFile="/usr/local/share/ispell/russian.dict",
--     AffFile ="/usr/local/share/ispell/russian.aff", StopFile="/usr/local/share/ispell/russian.stop"'
--     WHERE dict_name='ispell_template';
-- example of synonym dict
--   UPDATE pg_ts_dict SET dict_initoption='/usr/local/share/ispell/english.syn' WHERE dict_id=5;

GRANT SELECT ON pg_ts_dict, pg_ts_parser, pg_ts_cfg, pg_ts_cfgmap TO :httpd_user;
ALTER TABLE :version_tbl ADD COLUMN idxFTI tsvector;
UPDATE :version_tbl SET idxFTI=to_tsvector('default', content);
VACUUM FULL ANALYZE;
CREATE INDEX idxFTI_idx ON :version_tbl USING gist(idxFTI);
VACUUM FULL ANALYZE;
CREATE TRIGGER tsvectorupdate BEFORE UPDATE OR INSERT ON :version_tbl
       FOR EACH ROW EXECUTE PROCEDURE tsearch2(idxFTI, content);

--================================================================

\echo Initializing stored procedures

CREATE OR REPLACE FUNCTION update_recent (id INT4, version INT4)
	RETURNS void AS '
DELETE FROM recent  WHERE id=$1;
INSERT INTO recent (id, latestversion, latestmajor, latestminor)
  SELECT id, MAX(version), MAX(CASE WHEN minor_edit=0  THEN version END),
	                   MAX(CASE WHEN minor_edit<>0 THEN version END)
    FROM version WHERE id=$2 GROUP BY id;
DELETE FROM nonempty WHERE id=$1;
INSERT INTO nonempty (id)
  SELECT recent.id
    FROM recent, version
    WHERE recent.id=version.id
          AND version=latestversion
          AND content<>''''
          AND recent.id=$1;
' LANGUAGE SQL;

CREATE OR REPLACE FUNCTION prepare_rename_page (oldid INT4, newid INT4)
        RETURNS void AS '
DELETE FROM page     WHERE id=$2;
DELETE FROM version  WHERE id=$2;
DELETE FROM recent   WHERE id=$2;
DELETE FROM nonempty WHERE id=$2;
-- We have to fix all referring tables to the old id
UPDATE link SET linkfrom=$1 WHERE linkfrom=$2;
UPDATE link SET linkto=$1   WHERE linkto=$2;
' LANGUAGE sql;

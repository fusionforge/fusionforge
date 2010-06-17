-- http://www.hezmatt.org/~mpalmer/sqlite-phpwiki/sqlite.sql

-- $Id: sqlite-initialize.sql 6203 2008-08-26 13:23:56Z vargenau $

CREATE TABLE page (
	id              INTEGER PRIMARY KEY,
	pagename        VARCHAR(100) NOT NULL,
	hits            INTEGER NOT NULL DEFAULT 0,
	pagedata        MEDIUMTEXT NOT NULL DEFAULT '',
	cached_html 	MEDIUMTEXT               -- added with 1.3.11
);
CREATE UNIQUE INDEX page_index ON page (pagename);

CREATE TABLE version (
	id              INTEGER NOT NULL,
	version         INTEGER NOT NULL,
	mtime           INTEGER NOT NULL,
	minor_edit      TINYINTEGER DEFAULT 0,
	content         MEDIUMTEXT NOT NULL DEFAULT '',
	versiondata     MEDIUMTEXT NOT NULL DEFAULT '',
	PRIMARY KEY (id,version)
);
CREATE INDEX version_index ON version (mtime);

CREATE TABLE recent (
	id              INTEGER NOT NULL PRIMARY KEY,
	latestversion   INTEGER,
	latestmajor     INTEGER,
	latestminor     INTEGER
);

CREATE TABLE nonempty (
	id              INTEGER NOT NULL
);
CREATE INDEX nonempty_index ON nonempty (id);

CREATE TABLE link (
	linkfrom        INTEGER NOT NULL,
	linkto          INTEGER NOT NULL
);
CREATE INDEX linkfrom_index ON link (linkfrom);
CREATE INDEX linkto_index ON link (linkto);

CREATE TABLE session (
	sess_id   CHAR(32) NOT NULL DEFAULT '' PRIMARY KEY,
	sess_data MEDIUMTEXT NOT NULL,
	sess_date INTEGER UNSIGNED NOT NULL,
	sess_ip   CHAR(40) NOT NULL
);
CREATE INDEX sessdate_index ON session (sess_date);
CREATE INDEX sessip_index ON session (sess_ip);

-- Optional DB Auth and Prefs
-- For these tables below the default table prefix must be used 
-- in the DBAuthParam SQL statements also.

CREATE TABLE pref (
  	userid 	CHAR(48) NOT NULL PRIMARY KEY,
  	prefs  	MEDIUMTEXT NULL DEFAULT '',
  	passwd 	CHAR(48) DEFAULT '',
	groupname CHAR(48) DEFAULT 'users'
);

-- Use the member table, if you need it for n:m user-group relations,
-- and adjust your DBAUTH_AUTH_ SQL statements.
CREATE TABLE member (
	userid    CHAR(48) NOT NULL,
   	groupname CHAR(48) NOT NULL DEFAULT 'users'
);
CREATE INDEX member_userid ON member (userid);
CREATE INDEX member_groupname ON member (groupname);

-- only if you plan to use the wikilens theme
CREATE TABLE rating (
        dimension TINYINTEGER NOT NULL,
        raterpage INTEGER NOT NULL,
        rateepage INTEGER NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion INTEGER NOT NULL,
        tstamp INTEGER UNSIGNED NOT NULL,
        PRIMARY KEY (dimension, raterpage, rateepage)
);
CREATE INDEX rating_dimension ON rating (dimension);
CREATE INDEX rating_raterpage ON rating (raterpage);
CREATE INDEX rating_rateepage ON rating (rateepage);

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
CREATE TABLE accesslog (
        time_stamp    INTEGER UNSIGNED,
	remote_host   VARCHAR(100),
	remote_user   VARCHAR(50),
        request_method VARCHAR(10),
	request_line  VARCHAR(255),
	request_args  VARCHAR(255),
	request_file  VARCHAR(255),
	request_uri   VARCHAR(255),
	request_time  CHAR(28),
	status 	      TINYINTEGER UNSIGNED,
	bytes_sent    TINYINTEGER UNSIGNED,
        referer       VARCHAR(255), 
	agent         VARCHAR(255),
	request_duration FLOAT
);
CREATE INDEX log_time ON accesslog (time_stamp);
CREATE INDEX log_host ON accesslog (remote_host);
-- create extra indices on demand (usually referer. see plugin/AccessLogSql)


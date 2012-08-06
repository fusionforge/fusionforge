-- $Id: sqlsrv-initialize.sql,v 1.5 2006/12/08 08:04:10 rurban Exp $
-- UNTESTED!

-- the CREATE FUNCTION section must be run as a seperate query. cut/paste run before executing the remainder
-- of the contents of this file
CREATE FUNCTION hasContent
	(@ContentField varchar(max))
RETURNS bit
AS
BEGIN

IF @ContentField NOT LIKE ''
	RETURN 1
RETURN 0

END
-- end seperate section


CREATE TABLE page (
	id              INT NOT NULL ,
        pagename        VARCHAR(100) NOT NULL,
	hits            INT NOT NULL DEFAULT 0,
        pagedata        TEXT NOT NULL DEFAULT '',
	cached_html 	TEXT NOT NULL DEFAULT '',   -- added with 1.3.11
        PRIMARY KEY (id),
	UNIQUE (pagename)
);
--SET IDENTITY_INSERT page ON;

CREATE TABLE version (
	id              INT NOT NULL,
        version         INT NOT NULL,
	mtime           INT NOT NULL,
	minor_edit      TINYINT DEFAULT 0,
        content         varchar(max) NOT NULL DEFAULT '',--can't be text
        versiondata     TEXT NOT NULL DEFAULT '',
        PRIMARY KEY (id,version)
);
CREATE INDEX version_mtime ON version (mtime);

CREATE TABLE recent (
	id              INT NOT NULL,
	latestversion   INT,
	latestmajor     INT,
	latestminor     INT,
        PRIMARY KEY (id)
);

CREATE TABLE nonempty (
	id              INT NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE link (
	linkfrom        INT NOT NULL,
        linkto          INT NOT NULL,
	relation        INT
);
CREATE INDEX linkfrom ON link (linkfrom);
CREATE INDEX linkto ON link (linkto);

CREATE TABLE session (
    	sess_id 	CHAR(32) NOT NULL DEFAULT '',
    	sess_data 	IMAGE NOT NULL,
    	sess_date 	BIGINT NOT NULL,
    	sess_ip 	CHAR(40) NOT NULL,
    	PRIMARY KEY (sess_id)
);
CREATE INDEX sessdate_index ON session (sess_date);
CREATE INDEX sessip_index ON session (sess_ip);

-- Optional DB Auth and Prefs
-- For these tables below the default table prefix must be used 
-- in the DBAuthParam SQL statements also.

CREATE TABLE pref (
  	userid 	CHAR(48) NOT NULL,
  	prefs  	TEXT NULL DEFAULT '',
  	passwd 	CHAR(48) DEFAULT '',
	groupname CHAR(48) DEFAULT 'users',
        PRIMARY KEY (userid)
);

-- update to 1.3.12: (see lib/upgrade.php)
-- ALTER TABLE pref ADD passwd 	CHAR(48) BINARY DEFAULT '';
-- ALTER TABLE pref ADD groupname CHAR(48) BINARY DEFAULT 'users';

-- deprecated since 1.3.12. only useful for seperate databases.
-- better use the extra pref table where such users can be created easily 
-- without password.
--CREATE TABLE user (
--  	userid 	CHAR(48) NOT NULL,
--  	passwd 	CHAR(48) DEFAULT '',
--	prefs  	TEXT NULL DEFAULT '',
--	groupname CHAR(48) DEFAULT 'users'
--);

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
        dimension smallINT NOT NULL,
        raterpage INT NOT NULL,
        rateepage INT NOT NULL,
        ratingvalue FLOAT NOT NULL,
        rateeversion INT NOT NULL,
        tstamp bigint NOT NULL,
        PRIMARY KEY (dimension, raterpage, rateepage)
);
CREATE INDEX rating_dimension ON rating (dimension);
CREATE INDEX rating_raterpage ON rating (raterpage);
CREATE INDEX rating_rateepage ON rating (rateepage);

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
CREATE TABLE accesslog (
        time_stamp    BIGINT,
	remote_host   VARCHAR(255),
	remote_user   VARCHAR(50),
        request_method VARCHAR(10),
	request_line  text,
	request_args  text,
	request_file  text,
	request_uri   text,
	request_time  CHAR(28),
	status 	      INT,
	bytes_sent    INT,
        referer       text, 
	agent         text,
	request_duration FLOAT
);
CREATE INDEX log_time ON accesslog (time_stamp);
CREATE INDEX log_host ON accesslog (remote_host);
-- create extra indices on demand (usually referer. see plugin/AccessLogSql)


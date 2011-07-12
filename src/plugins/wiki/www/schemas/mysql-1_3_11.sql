-- $Id: mysql-1_3_11.sql 6203 2008-08-26 13:23:56Z vargenau $
-- phpwiki 1.3.11 upgrade from 1.3.10

-- if ACCESS_LOG_SQL > 0
-- only if you need fast log-analysis (spam prevention, recent referrers)
-- see http://www.outoforder.cc/projects/apache/mod_log_sql/docs-2.0/#id2756178
CREATE TABLE accesslog (
        time_stamp    int unsigned,
	remote_host   varchar(50),
	remote_user   varchar(50),
        request_method varchar(10),
	request_line  varchar(255),
	request_args  varchar(255),
	request_file  varchar(255),
	request_uri   varchar(255),
	request_time  char(28),
	status 	      smallint unsigned,
	bytes_sent    smallint unsigned,
        referer       varchar(255),
	agent         varchar(255),
	request_duration float
);
CREATE INDEX log_time ON accesslog (time_stamp);
CREATE INDEX log_host ON accesslog (remote_host);

-- and use ?action=upgrade as WIKI_ADMIN then
ALTER TABLE page ADD cached_html MEDIUMBLOB;

-- support ipv6
ALTER TABLE session CHANGE sess_ip sess_ip CHAR(40) NOT NULL;

-- $Id: mssql-destroy.sql,v 1.2 2005/02/27 09:33:05 rurban Exp $

DROP TABLE page;
DROP TABLE version;
DROP TABLE recent;
DROP TABLE nonempty;
DROP TABLE link;
DROP TABLE session;

DROP TABLE pref;
--DROP TABLE user;
--DROP TABLE member;

-- wikilens theme
DROP TABLE rating;

DROP TABLE accesslog;

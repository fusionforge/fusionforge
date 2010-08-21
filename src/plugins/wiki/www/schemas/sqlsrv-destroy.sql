-- $Id: mssql-destroy.sql,v 1.3 2005/09/28 19:27:23 rurban Exp $

DROP TABLE page;
DROP TABLE version;
DROP TABLE recent;
DROP TABLE nonempty;
DROP TABLE link;
DROP TABLE session;

DROP TABLE pref;
--DROP TABLE user;
DROP TABLE member;

-- wikilens theme
DROP TABLE rating;

DROP TABLE accesslog;

-- $Id: mysql-destroy.sql 7798 2010-12-23 12:43:55Z rurban $

drop table if exists page;
drop table if exists version;
drop table if exists recent;
drop table if exists nonempty;
drop table if exists link;
drop table if exists session;

-- upgrade from 1.3.7:

drop table if exists pref;
--drop table if exists user;
drop table if exists member;
drop table if exists accesslog;

-- if you plan to use the wikilens theme
drop table if exists rating;

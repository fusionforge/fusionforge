-- $Id: mysql-destroy.sql,v 1.2 2005/09/28 19:27:23 rurban Exp $

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

-- if you plan to use the wikilens theme
drop table if exists rating;

-- $Id: mysql-test-destroy.sql 6206 2008-08-26 15:20:22Z vargenau $

drop table if exists test_page;
drop table if exists test_version;
drop table if exists test_recent;
drop table if exists test_nonempty;
drop table if exists test_link;
drop table if exists test_session;

-- since 1.3.7:

drop table if exists test_pref;
drop table if exists test_user;
drop table if exists test_member;

drop table if exists test_rating;

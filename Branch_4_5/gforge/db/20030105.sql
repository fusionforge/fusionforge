drop sequence group_type_pk_seq;
DROP SEQUENCE project_metric_weekly_tm_pk_seq;
DROP SEQUENCE system_history_pk_seq;
DROP SEQUENCE system_machines_pk_seq;
DROP SEQUENCE system_news_pk_seq;
DROP SEQUENCE system_services_pk_seq;
DROP SEQUENCE system_status_pk_seq;

--
--	getting rid of more obsolete flags
--	PG 7.3 will allow us to DROP all these dead columns
--
ALTER TABLE user_group RENAME COLUMN bug_flags to dead1;
ALTER TABLE user_group RENAME COLUMN patch_flags to dead2;
ALTER TABLE user_group RENAME COLUMN support_flags to dead3;
drop INDEX bug_flags_idx;

--
-- Change Admin user realname if it's 'Sourceforge admin'
--
UPDATE users SET realname='Local GForge Admin' where user_id=101 and realname='Sourceforge admin';

--
-- OSX Theme by Richard Offer
--
INSERT INTO themes (dirname, fullname) VALUES ('osx', 'OSX');

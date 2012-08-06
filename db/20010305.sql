-- r/w of prdb_dbs ||  r/o prweb_vhost

--
--	All these changes were applied 3/8/2001
--

DROP SEQUENCE bug_bug_dependencies_pk_seq;--
DROP SEQUENCE bug_canned_responses_pk_seq;--     | sequence | tperdue
DROP SEQUENCE bug_category_pk_seq       ;--      | sequence | tperdue
DROP SEQUENCE bug_filter_pk_seq         ;--      | sequence | tperdue
DROP SEQUENCE bug_group_pk_seq       ;--         | sequence | tperdue
DROP SEQUENCE bug_history_pk_seq    ;--          | sequence | tperdue
DROP SEQUENCE bug_pk_seq            ;--          | sequence | tperdue
DROP SEQUENCE bug_resolution_pk_seq  ;--         | sequence | tperdue
DROP SEQUENCE bug_status_pk_seq      ;--         | sequence | tperdue
DROP SEQUENCE bug_task_dependencies_pk_seq ;--   | sequence | tperdue
DROP SEQUENCE patch_category_pk_seq   ;--        | sequence | tperdue
DROP SEQUENCE patch_history_pk_seq    ;--        | sequence | tperdue
DROP SEQUENCE patch_pk_seq            ;--        | sequence | tperdue
DROP SEQUENCE patch_status_pk_seq     ;--        | sequence | tperdue
DROP SEQUENCE support_canned_responses_pk_seq;-- | sequence | tperdue
DROP SEQUENCE support_category_pk_seq   ;--      | sequence | tperdue
DROP SEQUENCE support_history_pk_seq    ;--      | sequence | tperdue
DROP SEQUENCE support_messages_pk_seq   ;--      | sequence | tperdue
DROP SEQUENCE support_pk_seq            ;--      | sequence | tperdue
DROP SEQUENCE support_status_pk_seq     ;--      | sequence | tperdue

DROP TABLE bug                 ;--          | table | tperdue
DROP TABLE bug_bug_dependencies  ;--        | table | tperdue
DROP TABLE bug_canned_responses  ;--        | table | tperdue
DROP TABLE bug_category          ;--        | table | tperdue
DROP TABLE bug_filter            ;--        | table | tperdue
DROP TABLE bug_group             ;--        | table | tperdue
DROP TABLE bug_history           ;--        | table | tperdue
DROP TABLE bug_resolution        ;--        | table | tperdue
DROP TABLE bug_status            ;--        | table | tperdue
DROP TABLE bug_task_dependencies ;--        | table | tperdue
DROP TABLE patch                 ;--        | table | tperdue
DROP TABLE patch_category        ;--        | table | tperdue
DROP TABLE patch_history         ;--        | table | tperdue
DROP TABLE patch_status          ;--        | table | tperdue
DROP TABLE support               ;--        | table | tperdue
DROP TABLE support_canned_responses ;--     | table | tperdue
DROP TABLE support_category         ;--     | table | tperdue
DROP TABLE support_history          ;--     | table | tperdue
DROP TABLE support_messages         ;--     | table | tperdue
DROP TABLE support_status           ;--     | table | tperdue

alter table groups rename column use_bugs to dead1;
alter table groups rename column use_patch to dead2;
alter table groups rename column use_support to dead3;
alter table groups rename column new_bug_address to dead4;
alter table groups rename column new_patch_address to dead5;
alter table groups rename column new_support_address to dead6;
alter table groups rename column send_all_bugs to dead7;
alter table groups rename column send_all_patches to dead8;
alter table groups rename column send_all_support to dead9;
alter table groups rename column use_bug_depend_box to dead10;
alter table groups rename column bug_due_period to dead11;
alter table groups rename column patch_due_period to dead12;
alter table groups rename column support_due_period to dead13;

drop index groups_unix;
create unique index group_unix_uniq on groups (unix_group_name);

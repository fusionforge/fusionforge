update users set user_name=lower(user_name);
update groups set unix_group_name=lower(unix_group_name);
--
--	replacing some auto_increment hacks in mysql
--
drop table forum_thread_id;
drop table unix_uids;

drop sequence forum_thread_id_pk_seq;
drop sequence unix_uids_pk_seq;

CREATE SEQUENCE unix_uid_seq;
CREATE SEQUENCE forum_thread_seq;

SELECT setval('unix_uid_seq',(SELECT max(unix_uid) FROM users));
SELECT setval('forum_thread_seq',(SELECT max(thread_id) FROM forum));

--
--	clean up the user_group table
--
begin work;
--	remove rows that have invalid user_id's
delete from user_group where not exists (select user_id from users where user_group.user_id=users.user_id);
--	remove rows that have invalid group_id's
delete from user_group where not exists (select group_id from groups where user_group.group_id=groups.group_id);
commit;

ALTER TABLE user_group ADD CONSTRAINT user_group_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE user_group ADD CONSTRAINT user_group_group_id_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

--
--	clean up and add fkeys to the forum table
--
begin work;
--	delete forum messages that are not attached to any legitimate forums
delete from forum where not exists 
	(select group_forum_id from forum_group_list where forum.group_forum_id=forum_group_list.group_forum_id);
commit;
ALTER TABLE forum ADD CONSTRAINT forum_posted_by_fk 
	FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE forum ADD CONSTRAINT forum_group_forum_id_fk 
	FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) MATCH FULL;
BEGIN WORK;
--	delete forum messages that are attached to invalid forums
DELETE FROM forum WHERE group_forum_id IN (SELECT group_forum_id FROM forum_group_list where not exists
        (select group_id from groups where groups.group_id=forum_group_list.group_id));
--	delete forums that are not attached to valid groups
DELETE FROM forum_group_list where not exists
        (select group_id from groups where groups.group_id=forum_group_list.group_id);
COMMIT;
ALTER TABLE forum_group_list ADD CONSTRAINT forum_group_list_group_id_fk  
        FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

--
--	clean up and add fkeys to the bug table
--
begin work;
--	delete bugs that are not attached to valid users
delete from bug where not exists  
        (select user_id from users where bug.submitted_by=users.user_id);
delete from bug where not exists            
        (select user_id from users where bug.assigned_to=users.user_id);
--	update bugs to category=100 if they have an invalid category
update bug set category_id=100 where not exists
        (select bug_category_id from bug_category where bug.category_id=bug_category.bug_category_id);
commit;
--	update bug_groups that are not attached to valid groups
UPDATE bug_group SET group_id=1 WHERE NOT EXISTS
	(select group_id from groups where bug_group.group_id=groups.group_id);
ALTER TABLE bug_group ADD CONSTRAINT bug_group_group_fk
	FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;
--	update bug_categories that are not attached to valid groups
UPDATE bug_category SET group_id=1 WHERE NOT EXISTS
        (select group_id from groups where bug_category.group_id=groups.group_id);
ALTER TABLE bug_category ADD CONSTRAINT bug_category_group_fk
        FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

ALTER TABLE bug ADD CONSTRAINT bug_submitted_by_fk 
	FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE bug ADD CONSTRAINT bug_assigned_to_fk 
	FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE bug ADD CONSTRAINT bug_status_fk 
	FOREIGN KEY (status_id) REFERENCES bug_status(status_id) MATCH FULL;
ALTER TABLE bug ADD CONSTRAINT bug_category_fk 
	FOREIGN KEY (category_id) REFERENCES bug_category(bug_category_id) MATCH FULL;
ALTER TABLE bug ADD CONSTRAINT bug_resolution_fk 
	FOREIGN KEY (resolution_id) REFERENCES bug_resolution(resolution_id) MATCH FULL;
ALTER TABLE bug ADD CONSTRAINT bug_group_fk 
        FOREIGN KEY (bug_group_id) REFERENCES bug_group(bug_group_id) MATCH FULL;

--
--	clean up and add fkeys to project (pm) tables
--
begin work; 
--	update tasks that are not related to a valid subproject
UPDATE project_task SET group_project_id=1 where not exists  
        (select group_project_id from project_group_list where project_task.group_project_id=project_group_list.group_project_id);
commit;
ALTER TABLE forum ADD CONSTRAINT forum_posted_by_fk 
        FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;
ALTER TABLE forum ADD CONSTRAINT forum_group_forum_id_fk 
        FOREIGN KEY (group_forum_id) REFERENCES forum_group_list(group_forum_id) MATCH FULL;
BEGIN WORK; 
--	delete tasks that are part of subprojects that are invalid
DELETE FROM project_task WHERE group_project_id IN (SELECT group_project_id FROM project_group_list where not exists
        (select group_id from groups where groups.group_id=project_group_list.group_id));
--	now delete the subprojects that are invalid
DELETE FROM project_group_list where not exists
        (select group_id from groups where groups.group_id=project_group_list.group_id);
COMMIT;
ALTER TABLE project_group_list ADD CONSTRAINT project_group_list_group_id_fk
        FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

ALTER TABLE project_task ADD CONSTRAINT project_task_group_project_id_fk
        FOREIGN KEY (group_project_id) REFERENCES project_group_list(group_project_id) MATCH FULL;
begin work; 
--	update tasks that have invalid created_by values
UPDATE project_task SET created_by=100 where not exists  
        (select user_id from users where project_task.created_by=users.user_id);
commit;
ALTER TABLE project_task ADD CONSTRAINT project_task_created_by_fk
        FOREIGN KEY (created_by) REFERENCES users(user_id) MATCH FULL;
--	update tasks that have invalid statuses
UPDATE project_task SET status_id=100 where not exists     
        (select status_id from project_status where project_task.status_id=project_status.status_id);
ALTER TABLE project_task ADD CONSTRAINT project_task_status_id_fk
        FOREIGN KEY (status_id) REFERENCES project_status(status_id) MATCH FULL;

--
--	clean up and add fkeys to the patch tables
--
UPDATE patch SET patch_status_id=100 where not exists  
        (select patch_status_id from patch_status where patch.patch_status_id=patch_status.patch_status_id);
ALTER TABLE patch ADD CONSTRAINT patch_status_id_fk
        FOREIGN KEY (patch_status_id) REFERENCES patch_status(patch_status_id) MATCH FULL;

UPDATE patch SET patch_category_id=100 where not exists   
        (select patch_category_id from patch_category where patch.patch_category_id=patch_category.patch_category_id);
ALTER TABLE patch ADD CONSTRAINT patch_category_id_fk
        FOREIGN KEY (patch_category_id) REFERENCES patch_category(patch_category_id) MATCH FULL;

UPDATE patch SET submitted_by=100 where not exists 
        (select user_id from users where patch.submitted_by=users.user_id);
ALTER TABLE patch ADD CONSTRAINT patch_submitted_by_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;

UPDATE patch SET assigned_to=100 where not exists      
        (select user_id from users where patch.assigned_to=users.user_id);
ALTER TABLE patch ADD CONSTRAINT patch_assigned_to_fk
        FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;

UPDATE patch_category SET group_id=1 where not exists
        (select group_id from groups where patch_category.group_id=groups.group_id);
ALTER TABLE patch_category ADD CONSTRAINT patch_category_group_id_fk
        FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

--
--	clean up and add fkeys to support manager
--
UPDATE support SET support_status_id=3 where not exists
        (select support_status_id from support_status where support.support_status_id=support_status.support_status_id);
ALTER TABLE support ADD CONSTRAINT support_status_id_fk
        FOREIGN KEY (support_status_id) REFERENCES support_status(support_status_id) MATCH FULL;
        
UPDATE support SET support_category_id=100 where not exists
        (select support_category_id from support_category 
	where support.support_category_id=support_category.support_category_id);
ALTER TABLE support ADD CONSTRAINT support_category_id_fk 
        FOREIGN KEY (support_category_id) REFERENCES support_category(support_category_id) MATCH FULL;
        
UPDATE support SET submitted_by=100 where not exists
        (select user_id from users where support.submitted_by=users.user_id);
ALTER TABLE support ADD CONSTRAINT support_submitted_by_fk
        FOREIGN KEY (submitted_by) REFERENCES users(user_id) MATCH FULL;
        
UPDATE support SET assigned_to=100 where not exists
        (select user_id from users where support.assigned_to=users.user_id);
ALTER TABLE support ADD CONSTRAINT support_assigned_to_fk
        FOREIGN KEY (assigned_to) REFERENCES users(user_id) MATCH FULL;
        
UPDATE support_category SET group_id=1 where not exists
        (select group_id from groups where support_category.group_id=groups.group_id);
ALTER TABLE support_category ADD CONSTRAINT support_category_group_id_fk
        FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL;

--
--	enforce user language pref
--
UPDATE users SET language=1 WHERE language<1 or language > 21;
ALTER TABLE users ADD CONSTRAINT users_languageid_fk
        FOREIGN KEY (language) REFERENCES supported_languages(language_id) MATCH FULL;

CREATE INDEX idx_users_username ON users(user_name);

--
--	create the first trove aggregation table
--
CREATE TABLE trove_agg AS
SELECT tgl.trove_cat_id, g.group_id, g.group_name, g.unix_group_name, g.status, g.register_time, g.short_description, 
        project_metric.percentile, project_metric.ranking 
        FROM groups g
        LEFT JOIN project_metric USING (group_id) , 
        trove_group_link tgl 
        WHERE 
        tgl.group_id=g.group_id 
        AND (g.is_public=1) 
        AND (g.type=1) 
        AND (g.status='A') 
        ORDER BY g.group_name;

CREATE INDEX troveagg_trovecatid ON trove_agg(trove_cat_id);

CREATE INDEX people_job_group_id ON people_job(group_id);
CREATE INDEX users_user_pw ON users(user_pw);

DROP TABLE system_news;
DROP TABLE system_history;
DROP TABLE system_status;
DROP TABLE system_services;
DROP TABLE system_machines;

create index foundrynews_foundry_date_approved on foundry_news(foundry_id,approve_date,is_approved);
create index news_group_date on news_bytes(group_id,date);
create index news_date on news_bytes(date);
create index news_approved_date on news_bytes(is_approved,date);
create index bug_groupid_statusid on bug(group_id,status_id);
create index bug_groupid_assignedto_statusid on bug(group_id,assigned_to,status_id);


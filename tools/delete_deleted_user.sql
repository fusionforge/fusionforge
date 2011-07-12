delete from forum where posted_by in
(select user_id  from users where status='D');

delete from project_messages where posted_by in
(select user_id  from users where status='D');

delete from user_group where user_id in
(select user_id  from users where status='D');

delete from artifact_history where artifact_id in (select artifact_id from artifact where assigned_to in
(select user_id  from users where status='D'));

delete from artifact_history where mod_by in
(select user_id  from users where status='D');


delete from artifact_file where artifact_id in (select artifact_id from artifact where assigned_to in
(select user_id  from users where status='D'));

delete from artifact_message where artifact_id in (select artifact_id from artifact where assigned_to in
(select user_id  from users where status='D'));

delete from artifact where assigned_to in
(select user_id  from users where status='D');

delete from artifact where submitted_by in
(select user_id  from users where status='D');

delete from users where status='D';

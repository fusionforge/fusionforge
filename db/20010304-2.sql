--
--
--	database conversion
--
--	The general idea is to move data from the 2
--

--
--
--	Bug TRACKER
--
--
BEGIN;

--
--	set up bug ArtifactTypes for each group
--

UPDATE groups SET bug_due_period='2592000' WHERE bug_due_period is null;
INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+100000,group_id,'Bugs','Bug Tracking System',use_bugs,
1,send_all_bugs,new_bug_address,bug_due_period,1,1
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;

--
--	permissions
--
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+100000,user_id,bug_flags
FROM user_group;

--
--	bug groups conversion
--
INSERT INTO artifact_group (id,group_artifact_id,group_name)
SELECT bug_group_id+100000,group_id+100000,group_name FROM bug_group;

--
--	bug category conversion
--
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT bug_category_id+100000,group_id+100000,category_name,100 FROM bug_category;

--
--	bug
--
--	bug tracker had status_id of 100 (None) and status_id=3 (closed)
--
UPDATE bug SET status_id=1 WHERE status_id=100;
INSERT INTO bug_status (status_id,status_name) VALUES (2,'Open');
UPDATE bug SET status_id=2 WHERE status_id=3;
DELETE FROM bug_status WHERE status_id=3;

UPDATE bug SET close_date=0 WHERE close_date is NULL;
UPDATE bug SET summary=0 WHERE summary is NULL;
UPDATE bug SET details='' WHERE details is NULL;

INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
bug_id+100000,group_id+100000,status_id,category_id+100000,bug_group_id+100000,priority,
submitted_by,assigned_to,date,close_date,summary,details,resolution_id
FROM bug
ORDER BY group_id ASC;

--
--	bug_history
--
--UPDATE bug_history SET old_value=1 WHERE old_value='100' AND field_name='status_id';
UPDATE bug_history SET old_value=2 WHERE old_value='3' AND field_name='status_id';

--BEGIN;
--SELECT * from bug_history
--WHERE NOT EXISTS (select bug_id FROM bug
--where bug.bug_id=bug_history.bug_id);
--COMMIT;

--DELETE FROM bug_history WHERE bug_id=0;

--DELETE FROM bug_history
--WHERE bug_id+100000 NOT IN (SELECT artifact_id FROM artifact);

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,field_name,old_value,mod_by,date
FROM bug_history
WHERE field_name IN ('summary','resolution_id','priority','group_id','close_date','assigned_to','status_id');

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,'artifact_group_id',(old_value::int)+100000,mod_by,date
FROM bug_history
WHERE field_name='bug_group_id';

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
bug_id+100000,field_name,(old_value::int)+100000,mod_by,date
FROM bug_history
WHERE field_name='category_id';

--
--	bug messages
--
INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
bh.bug_id+100000,bh.mod_by,users.email,bh.date,bh.old_value
FROM bug_history bh, users
WHERE bh.mod_by=users.user_id
AND bh.field_name='details';

--
--	bug canned responses
--
delete from bug_canned_responses where title is null;

INSERT INTO artifact_canned_responses
(group_artifact_id,title,body)
SELECT
group_id+100000,title,body
FROM bug_canned_responses
WHERE group_id > 0;

COMMIT;

--
--
--			SUPPORT
--
--

--
--      set up support ArtifactTypes for each group
--
BEGIN;

UPDATE groups SET support_due_period='2592000' WHERE support_due_period is null;

INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+200000,group_id,'Support Requests','Tech Support Tracking System',use_support,
1,send_all_support,new_support_address,support_due_period,0,2
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;

--
--      permissions
--
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+200000,user_id,support_flags
FROM user_group;

--
--      support category conversion
--
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT support_category_id+200000,group_id+200000,category_name,100 FROM support_category;

--
--	support
--
DELETE FROM support WHERE NOT EXISTS
(SELECT group_id FROM groups WHERE support.group_id=groups.group_id);

UPDATE patch SET summary=0 WHERE summary is NULL;
UPDATE patch SET details='' WHERE details is NULL;

INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
support_id+200000,group_id+200000,support_status_id,support_category_id+200000,100,priority,
submitted_by,assigned_to,open_date,close_date,summary,'',100
FROM support
ORDER BY group_id ASC;

--
--	support_history
--
DELETE FROM support_history WHERE support_id=0;

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,field_name,old_value,mod_by,date
FROM support_history
WHERE
field_name IN ('summary','priority','close_date','assigned_to');

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,'category_id',(old_value::int)+200000,mod_by,date
FROM support_history
WHERE
field_name='support_category_id';

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
support_id+200000,'status_id',old_value,mod_by,date
FROM support_history
WHERE
field_name='support_status_id';

--
--      messages
--
DELETE FROM support_messages WHERE NOT EXISTS
(SELECT support_id FROM support WHERE support.support_id=support_messages.support_id);

INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
support_id+200000,100,from_email,date,body
FROM support_messages;

--
--	canned messages
--
INSERT INTO artifact_canned_responses
(group_artifact_id,title,body)
SELECT
group_id+200000,title,body
FROM support_canned_responses
WHERE group_id > 0;

COMMIT;


--
--
--      Patch Manager
--
--
--BEGIN;

--
--      set up patch ArtifactTypes for each group
--
UPDATE groups SET patch_due_period='2592000' WHERE patch_due_period is null;

INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+300000,group_id,'Patches','Patch Tracking System',use_patch,
1,send_all_patches,new_patch_address,patch_due_period,1,3
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;

--
--      permissions
--
INSERT INTO artifact_perm
(group_artifact_id,user_id,perm_level)
SELECT group_id+300000,user_id,patch_flags
FROM user_group;

--
--      patch category conversion
--
INSERT INTO artifact_category (id,group_artifact_id,category_name,auto_assign_to)
SELECT patch_category_id+300000,group_id+300000,category_name,100 FROM patch_category;

--
--	patch table
--
--	moving the odd patch statuses to resolutions
--
ALTER TABLE patch ADD COLUMN resolution_id INT;
UPDATE patch SET resolution_id = 0;
ALTER TABLE patch ALTER COLUMN resolution_id SET NOT NULL;
ALTER TABLE patch ALTER COLUMN resolution_id SET DEFAULT 100;

UPDATE patch SET resolution_id=patch_status_id;
vacuum analyze patch;
update patch set patch_status_id=2 where patch_status_id > 3;
update patch set resolution_id=100 WHERE resolution_id < 4;
INSERT INTO artifact_resolution VALUES (102,'Accepted');
INSERT INTO artifact_resolution VALUES (103,'Out of Date');
INSERT INTO artifact_resolution VALUES (104,'Postponed');
INSERT INTO artifact_resolution VALUES (105,'Rejected');
update patch set resolution_id=104 WHERE resolution_id=4;
update patch set resolution_id=105 WHERE resolution_id=101;

delete from patch where patch_id=100000;
UPDATE patch SET details=' ' WHERE details is null;

INSERT INTO artifact
(artifact_id,group_artifact_id,status_id,category_id,artifact_group_id,priority,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id)
SELECT
patch_id+300000,group_id+300000,patch_status_id,patch_category_id+300000,100,5,
submitted_by,assigned_to,open_date,close_date,summary,details,resolution_id
FROM patch
ORDER BY group_id ASC;

--
--	patch history
--
INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,field_name,old_value,mod_by,date
FROM patch_history
WHERE field_name IN ('summary','close_date','assigned_to','Patch Code');

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,'status_id',old_value,mod_by,date
FROM patch_history
WHERE field_name='patch_status_id';

INSERT INTO artifact_history
(artifact_id,field_name,old_value,mod_by,entrydate)
SELECT
patch_id+300000,'category_id',(old_value::int)+300000,mod_by,date
FROM patch_history
WHERE field_name='patch_category_id';

--
--      patch messages
--
INSERT INTO artifact_message
(artifact_id,submitted_by,from_email,adddate,body)
SELECT
ph.patch_id+300000,ph.mod_by,users.email,ph.date,ph.old_value
FROM patch_history ph, users
WHERE ph.mod_by=users.user_id
AND ph.field_name='details';

--
--	patch code
--
INSERT INTO artifact_file
(artifact_id,description,bin_data,filename,filesize,filetype,adddate,submitted_by)
SELECT patch_id+300000,'None',code,'None',length(code),'text/plain',open_date,submitted_by
FROM patch
WHERE code IS NOT NULL;

--COMMIT;

INSERT INTO artifact_counts_agg
SELECT group_artifact_id,count(*)
FROM artifact
WHERE status_id <> 3
GROUP BY group_artifact_id;

--
--
--	Feature Requests
--
--
INSERT INTO artifact_group_list
(group_artifact_id,group_id,name,description,is_public,
allow_anon,email_all_updates,email_address,due_period,use_resolution,datatype)
SELECT group_id+350000,group_id,'Feature Requests','Feature Request Tracking System',1,
1,0,'',45*24*60*60,0,4
FROM groups
WHERE status != 'I' AND status != 'P'
ORDER BY group_id ASC;

vacuum analyze artifact_perm;
vacuum analyze artifact_group_list;
vacuum analyze artifact;
vacuum analyze artifact_history;
vacuum analyze artifact_category;
vacuum analyze artifact_group;
vacuum analyze artifact_file;
vacuum analyze artifact_message;

--
--	SourceForge: Breaking Down the Barriers to Open Source Development
--	Copyright 1999-2000 (c) The SourceForge Crew
--	http://sourceforge.net
--
--	The values in this file must be inserted in order
--	and must be complete or you will have referential integrity problems
--

--
--      Default Data for 'groups'
--
INSERT INTO groups (group_id) VALUES (1);

--
--      Default Data for 'users'
--
INSERT INTO users (user_id, user_name, email, user_pw)  
VALUES (100,'None','noreply@sourceforge.net','*********34343');

--
--	Default data for table 'bug_resolution'
--
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (1,'Fixed');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (2,'Invalid');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (3,'Wont Fix');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (4,'Later');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (5,'Remind');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (6,'Works For Me');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (100,'None');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (101,'Duplicate');

--
--	Default data for table 'bug_status'
--
INSERT INTO bug_status (status_id, status_name) VALUES (1,'Open');
INSERT INTO bug_status (status_id, status_name) VALUES (3,'Closed');
INSERT INTO bug_status (status_id, status_name) VALUES (100,'None');

--
--	Default data for bug_category
--
INSERT INTO bug_category (bug_category_id, group_id, category_name) VALUES (100,1,'None');

--
--	Default data for bug_group
--
INSERT INTO bug_group (bug_group_id, group_id, group_name) VALUES (100,1,'None');

--
--	Default data for bug - required for dependent tasks/bugs
--
INSERT INTO bug (bug_id,group_id,status_id,category_id,bug_group_id,submitted_by,assigned_to,resolution_id)
	VALUES (100,1,100,100,100,100,100,100);
--NOTE - if using sequences, you may have to update the bug_id sequence here

--
--	Default data for table 'patch_category'
--
INSERT INTO patch_category (patch_category_id, group_id, category_name) VALUES (100,1,'None');

--
--	Default data for table 'patch_status'
--
INSERT INTO patch_status (patch_status_id, status_name) VALUES (1,'Open');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (2,'Closed');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (3,'Deleted');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (4,'Postponed');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (100,'None');

--
--	Default values for patch
--
INSERT INTO patch (group_id,patch_status_id,patch_category_id,submitted_by,assigned_to)
	VALUES (1,100,100,100,100);

--
--	Default data for table 'project_status'
--
INSERT INTO project_status (status_id, status_name) VALUES (1,'Open');
INSERT INTO project_status (status_id, status_name) VALUES (2,'Closed');
INSERT INTO project_status (status_id, status_name) VALUES (100,'None');
INSERT INTO project_status (status_id, status_name) VALUES (3,'Deleted');

--
--	Default data for project_group_list
--
INSERT INTO project_group_list (group_project_id,group_id) VALUES (1,1);
--NOTE - if using sequences, you may have to update the group_project_id sequence here

--
--	Default data for project_task
--
INSERT INTO project_task (group_project_id,created_by,status_id)
	VALUES (1,100,100);

--
--	Default Data for 'survey_question_types'
--
INSERT INTO survey_question_types (id, type) VALUES (1,'Radio Buttons 1-5');
INSERT INTO survey_question_types (id, type) VALUES (2,'Text Area');
INSERT INTO survey_question_types (id, type) VALUES (3,'Radio Buttons Yes/No');
INSERT INTO survey_question_types (id, type) VALUES (4,'Comment Only');
INSERT INTO survey_question_types (id, type) VALUES (5,'Text Field');
INSERT INTO survey_question_types (id, type) VALUES (100,'None');

--
--	Default data for Support System
--
insert into support_status values('1','Open');
insert into support_status values('2','Closed');
insert into support_status values('3','Deleted');

--
--	Default data for 'support_category'
--
insert into support_category VALUES ('100','1','None');

--
--	Default data for Help Wanted System
--
INSERT INTO people_skill_year VALUES ('','< 6 Months');
INSERT INTO people_skill_year VALUES ('','6 Mo - 2 yr');
INSERT INTO people_skill_year VALUES ('','2 yr - 5 yr');
INSERT INTO people_skill_year VALUES ('','5 yr - 10 yr');
INSERT INTO people_skill_year VALUES ('','> 10 years');

INSERT INTO people_skill_level VALUES ('','Want to Learn');
INSERT INTO people_skill_level VALUES ('','Competent');
INSERT INTO people_skill_level VALUES ('','Wizard');
INSERT INTO people_skill_level VALUES ('','Wrote The Book');
INSERT INTO people_skill_level VALUES ('','Wrote It');

INSERT INTO people_job_category VALUES ('','Developer');
INSERT INTO people_job_category VALUES ('','Project Manager');
INSERT INTO people_job_category VALUES ('','Unix Admin');
INSERT INTO people_job_category VALUES ('','Doc Writer');
INSERT INTO people_job_category VALUES ('','Tester');
INSERT INTO people_job_category VALUES ('','Support Manager');
INSERT INTO people_job_category VALUES ('','Graphic/Other Designer');

INSERT INTO people_job_status VALUES ('1','Open');
INSERT INTO people_job_status VALUES ('2','Filled');
INSERT INTO people_job_status VALUES ('3','Deleted');

--
--	Default data for group_type
--
INSERT INTO group_type VALUES ('1','Project');
INSERT INTO group_type VALUES ('2','Foundry');

--
--	Default data for new filerelease system
--
INSERT INTO frs_filetype VALUES ('1000','.deb');
INSERT INTO frs_filetype VALUES ('2000','.rpm');
INSERT INTO frs_filetype VALUES ('3000','.zip');
INSERT INTO frs_filetype VALUES ('','.bz2');
INSERT INTO frs_filetype VALUES ('','.gz');
INSERT INTO frs_filetype VALUES ('5000','Source .zip');
INSERT INTO frs_filetype VALUES ('','Source .bz2');
INSERT INTO frs_filetype VALUES ('','Source .gz');
INSERT INTO frs_filetype VALUES ('5100','Source .rpm');
INSERT INTO frs_filetype VALUES ('5900','Other Source File');
INSERT INTO frs_filetype VALUES ('8000','.jpg');
INSERT INTO frs_filetype VALUES ('','text');
INSERT INTO frs_filetype VALUES ('','html');
INSERT INTO frs_filetype VALUES ('','pdf');
INSERT INTO frs_filetype VALUES ('9999','Other');

INSERT INTO frs_status VALUES ('1','Active');
INSERT INTO frs_status VALUES ('3','Hidden');

INSERT INTO frs_processor VALUES ('1000','i386');
INSERT INTO frs_processor VALUES ('6000','IA64');
INSERT INTO frs_processor VALUES ('7000','Alpha');
INSERT INTO frs_processor VALUES ('8000','Any');
INSERT INTO frs_processor VALUES ('2000','PPC');
INSERT INTO frs_processor VALUES ('3000','MIPS');
INSERT INTO frs_processor VALUES ('4000','Sparc');
INSERT INTO frs_processor VALUES ('5000','UltraSparc');
INSERT INTO frs_processor VALUES ('9999','Other');


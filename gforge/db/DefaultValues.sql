--
--	SourceForge: Breaking Down the Barriers to Open Source Development
--	Copyright 1999-2001 (c) VA Linux Systems
--	http://sourceforge.net
--
--	$Id: DefaultValues.sql,v 1.13 2001/06/27 00:29:32 tperdue Exp $	
--
--	The values in this file must be inserted in order
--	and must be complete or you will have referential integrity problems
--
--
--	IMPORTANT - change '/path/to' before importing
--
copy supported_languages from '/path/to/alexandria/db/languages.tab';
SELECT setval('supported_languages_pk_seq',(SELECT max(language_id) FROM supported_languages));

copy trove_cat from '/path/to/alexandria/db/trove_cat.tab';
SELECT setval('trove_cat_pk_seq',(SELECT max(trove_cat_id) FROM trove_cat));

--
--      Default Data for 'groups'
--
-- MASTER GROUP - site admins are members of this group
INSERT INTO groups (group_name,unix_group_name) VALUES ('Master Group','sourceforge');
-- STATS GROUP - add members who need access to /stats/ directory
-- BE SURE TO SET UP /etc/local.inc accordingly
INSERT INTO groups (group_name,unix_group_name) VALUES ('Stats Group','stats');
-- NEWS GROUP - News stories are attached to this group. Members have news admin privs.
-- BE SURE TO SET UP /etc/local.inc accordingly
INSERT INTO groups (group_name,unix_group_name) VALUES ('News Group','news');
-- PEER RATINGS GROUP - Members with "Admin" powers become seed for PEER RATINGS
INSERT INTO groups (group_name,unix_group_name) VALUES ('Peer Ratings Group','peerrating');

--
--      Default Data for 'users'
--
INSERT INTO users (user_id, user_name, email, user_pw)  
VALUES (100,'None','noreply@sourceforge.net','*********34343');

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
INSERT INTO project_group_list (group_id) VALUES (1);

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
--	Default data for Help Wanted System
--
INSERT INTO people_skill_year (name) VALUES ('< 6 Months');
INSERT INTO people_skill_year (name) VALUES ('6 Mo - 2 yr');
INSERT INTO people_skill_year (name) VALUES ('2 yr - 5 yr');
INSERT INTO people_skill_year (name) VALUES ('5 yr - 10 yr');
INSERT INTO people_skill_year (name) VALUES ('> 10 years');

INSERT INTO people_skill_level (name) VALUES ('Want to Learn');
INSERT INTO people_skill_level (name) VALUES ('Competent');
INSERT INTO people_skill_level (name) VALUES ('Wizard');
INSERT INTO people_skill_level (name) VALUES ('Wrote The Book');
INSERT INTO people_skill_level (name) VALUES ('Wrote It');

INSERT INTO people_job_category (name) VALUES ('Developer');
INSERT INTO people_job_category (name) VALUES ('Project Manager');
INSERT INTO people_job_category (name) VALUES ('Unix Admin');
INSERT INTO people_job_category (name) VALUES ('Doc Writer');
INSERT INTO people_job_category (name) VALUES ('Tester');
INSERT INTO people_job_category (name) VALUES ('Support Manager');
INSERT INTO people_job_category (name) VALUES ('Graphic/Other Designer');

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
INSERT INTO frs_filetype VALUES ('3100','.bz2');
INSERT INTO frs_filetype VALUES ('3110','.gz');
INSERT INTO frs_filetype VALUES ('5000','Source .zip');
INSERT INTO frs_filetype VALUES ('5010','Source .bz2');
INSERT INTO frs_filetype VALUES ('5020','Source .gz');
INSERT INTO frs_filetype VALUES ('5100','Source .rpm');
INSERT INTO frs_filetype VALUES ('5900','Other Source File');
INSERT INTO frs_filetype VALUES ('8000','.jpg');
INSERT INTO frs_filetype VALUES ('8100','text');
INSERT INTO frs_filetype VALUES ('8200','html');
INSERT INTO frs_filetype VALUES ('8300','pdf');
INSERT INTO frs_filetype VALUES ('9999','Other');
SELECT setval('frs_filetype_pk_seq',(SELECT max(type_id) FROM frs_filetype));

INSERT INTO frs_status VALUES ('1','Active');
INSERT INTO frs_status VALUES ('3','Hidden');
SELECT setval('frs_status_pk_seq',(SELECT max(status_id) FROM frs_status));

INSERT INTO frs_processor VALUES ('1000','i386');
INSERT INTO frs_processor VALUES ('6000','IA64');
INSERT INTO frs_processor VALUES ('7000','Alpha');
INSERT INTO frs_processor VALUES ('8000','Any');
INSERT INTO frs_processor VALUES ('2000','PPC');
INSERT INTO frs_processor VALUES ('3000','MIPS');
INSERT INTO frs_processor VALUES ('4000','Sparc');
INSERT INTO frs_processor VALUES ('5000','UltraSparc');
INSERT INTO frs_processor VALUES ('9999','Other');
SELECT setval('frs_processor_pk_seq',(SELECT max(processor_id) FROM frs_processor));

--
--	Default data for artifact mgr
--
INSERT INTO artifact_group_list VALUES (100,1,'Default','Default Data - Dont Edit',3,0,0,'');
INSERT INTO artifact_category VALUES (100,100,'None',100);
INSERT INTO artifact_group VALUES (100,100,'None',100);
INSERT INTO artifact_status VALUES (1,'Open');
INSERT INTO artifact_status VALUES (2,'Closed');
INSERT INTO artifact_status VALUES (3,'Deleted');

SELECT setval('artifact_grou_group_artifac_seq',(SELECT max(group_artifact_id) FROM artifact_group_list));
SELECT setval('artifact_category_id_seq',(SELECT max(id) FROM artifact_category));
SELECT setval('artifact_group_id_seq',(SELECT max(id) FROM artifact_group));
SELECT setval('artifact_status_id_seq',(SELECT max(id) FROM artifact_status));

INSERT INTO themes VALUES (1,'ultralite','Ultra Lite');
SELECT setval('themes_pk_seq',(SELECT max(theme_id) FROM themes));


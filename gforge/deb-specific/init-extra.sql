INSERT INTO supported_languages VALUES (1,'English','English.class','English');
INSERT INTO supported_languages VALUES (2,'Japanese','Japanese.class','Japanese');
INSERT INTO supported_languages VALUES (3,'Hebrew','Hebrew.class','Hebrew');
INSERT INTO supported_languages VALUES (4,'Spanish','Spanish.class','Spanish');
INSERT INTO supported_languages VALUES (5,'Thai','Thai.class','Thai');
INSERT INTO supported_languages VALUES (6,'German','German.class','German');
INSERT INTO supported_languages VALUES (7,'French','French.class','French');
INSERT INTO supported_languages VALUES (8,'Italian','Italian.class','Italian');
INSERT INTO supported_languages VALUES (9,'Norwegian','Norwegian.class','Norwegian');
INSERT INTO supported_languages VALUES (10,'Swedish','Swedish.class','Swedish');
INSERT INTO supported_languages VALUES (11,'Chinese','Chinese.class','Chinese');
INSERT INTO supported_languages VALUES (12,'Dutch','Dutch.class','Dutch');
INSERT INTO supported_languages VALUES (13,'Esperanto','Esperanto.class','Esperanto');
INSERT INTO supported_languages VALUES (14,'Catalan','Catalan.class','Catalan');

INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (1,'Fixed');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (2,'Invalid');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (3,'Wont Fix');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (4,'Later');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (5,'Remind');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (6,'Works For Me');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (100,'None');
INSERT INTO bug_resolution (resolution_id, resolution_name) VALUES (101,'Duplicate');

INSERT INTO bug_status (status_id, status_name) VALUES (1,'Open');
INSERT INTO bug_status (status_id, status_name) VALUES (3,'Closed');
INSERT INTO bug_status (status_id, status_name) VALUES (100,'None');

INSERT INTO patch_status (patch_status_id, status_name) VALUES (1,'Open');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (2,'Closed');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (3,'Deleted');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (4,'Postponed');
INSERT INTO patch_status (patch_status_id, status_name) VALUES (100,'None');

INSERT INTO project_status (status_id, status_name) VALUES (1,'Open');
INSERT INTO project_status (status_id, status_name) VALUES (2,'Closed');
INSERT INTO project_status (status_id, status_name) VALUES (100,'None');
INSERT INTO project_status (status_id, status_name) VALUES (3,'Deleted');

INSERT INTO survey_question_types (id, type) VALUES (1,'Radio Buttons 1-5');
INSERT INTO survey_question_types (id, type) VALUES (2,'Text Area');
INSERT INTO survey_question_types (id, type) VALUES (3,'Radio Buttons Yes/No');
INSERT INTO survey_question_types (id, type) VALUES (4,'Comment Only');
INSERT INTO survey_question_types (id, type) VALUES (5,'Text Field');
INSERT INTO survey_question_types (id, type) VALUES (100,'None');

INSERT INTO support_status values('1','Open');
INSERT INTO support_status values('2','Closed');
INSERT INTO support_status values('3','Deleted');

INSERT INTO people_skill_year VALUES ('0','< 6 Months');
INSERT INTO people_skill_year VALUES ('1','6 Mo - 2 yr');
INSERT INTO people_skill_year VALUES ('2','2 yr - 5 yr');
INSERT INTO people_skill_year VALUES ('3','5 yr - 10 yr');
INSERT INTO people_skill_year VALUES ('4','> 10 years');

INSERT INTO people_skill_level VALUES ('0','Want to Learn');
INSERT INTO people_skill_level VALUES ('1','Competent');
INSERT INTO people_skill_level VALUES ('2','Wizard');
INSERT INTO people_skill_level VALUES ('3','Wrote The Book');
INSERT INTO people_skill_level VALUES ('4','Wrote It');

INSERT INTO people_job_category VALUES ('0','Developer');
INSERT INTO people_job_category VALUES ('1','Project Manager');
INSERT INTO people_job_category VALUES ('2','Unix Admin');
INSERT INTO people_job_category VALUES ('3','Doc Writer');
INSERT INTO people_job_category VALUES ('4','Tester');
INSERT INTO people_job_category VALUES ('5','Support Manager');
INSERT INTO people_job_category VALUES ('6','Graphic/Other Designer');

INSERT INTO people_job_status VALUES ('1','Open');
INSERT INTO people_job_status VALUES ('2','Filled');
INSERT INTO people_job_status VALUES ('3','Deleted');

INSERT INTO group_type VALUES ('1','Project');
INSERT INTO group_type VALUES ('2','Foundry');

INSERT INTO frs_filetype VALUES ('1000','.deb');
INSERT INTO frs_filetype VALUES ('2000','.rpm');
INSERT INTO frs_filetype VALUES ('3000','.zip');
INSERT INTO frs_filetype VALUES ('4000','.bz2');
INSERT INTO frs_filetype VALUES ('4500','.gz');
INSERT INTO frs_filetype VALUES ('5000','Source .zip');
INSERT INTO frs_filetype VALUES ('5010','Source .bz2');
INSERT INTO frs_filetype VALUES ('5020','Source .gz');
INSERT INTO frs_filetype VALUES ('5100','Source .rpm');
INSERT INTO frs_filetype VALUES ('5900','Other Source File');
INSERT INTO frs_filetype VALUES ('8000','.jpg');
INSERT INTO frs_filetype VALUES ('9000','text');
INSERT INTO frs_filetype VALUES ('9100','html');
INSERT INTO frs_filetype VALUES ('9200','pdf');
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

INSERT INTO themes VALUES (1, 'forged', 'Forged Metal');
INSERT INTO themes VALUES (2, 'classic', 'Classic Sourceforge');

INSERT INTO doc_states VALUES (1, 'active');
INSERT INTO doc_states VALUES (2, 'deleted');
INSERT INTO doc_states VALUES (3, 'pending');
INSERT INTO doc_states VALUES (4, 'hidden');
INSERT INTO doc_states VALUES (5, 'private');

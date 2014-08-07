--
-- 	Post pre6 changes
--

DROP VIEW forum_group_list_vw;
CREATE VIEW forum_group_list_vw AS
SELECT forum_group_list.*, forum_agg_msg_count.count as total,
    (SELECT max(date) AS recent FROM forum WHERE group_forum_id=forum_group_list.group_forum_id) AS recent,
    (SELECT count(*) FROM
        (SELECT thread_id
            FROM forum
            WHERE group_forum_id=forum_group_list.group_forum_id GROUP BY thread_id) as tmp) AS threads
    FROM forum_group_list LEFT JOIN forum_agg_msg_count USING (group_forum_id);

--
--	Skills system additions by John Maguire
--
-- DROP SEQUENCE "skills_data_pk_seq";
-- DROP SEQUENCE "skills_data_types_pk_seq";
-- DROP TABLE "skills_data";
-- DROP TABLE "skills_data_types";

CREATE SEQUENCE "skills_data_pk_seq";

CREATE SEQUENCE "skills_data_types_pk_seq" START 0 MINVALUE 0;

CREATE TABLE "skills_data_types"(
	"type_id" integer DEFAULT nextval('skills_data_types_pk_seq'::text) NOT NULL,
	"type_name" character varying(25) DEFAULT '' NOT NULL,
	PRIMARY KEY("type_id")
);

CREATE TABLE "skills_data"(
	"skills_data_id" integer DEFAULT nextval('skills_data_pk_seq'::text) NOT NULL,
	"user_id" integer DEFAULT '0' NOT NULL REFERENCES users(user_id),
	"type" integer DEFAULT '0' NOT NULL REFERENCES skills_data_types (type_id),
	"title" character varying(100) DEFAULT '' NOT NULL,
	"start" integer DEFAULT '0' NOT NULL,
	"finish" integer DEFAULT '0' NOT NULL,
	"keywords" character varying(255) DEFAULT '' NOT NULL,
	PRIMARY KEY("skills_data_id")
);

INSERT INTO skills_data_types (type_name) values('Unspecified');
INSERT INTO skills_data_types (type_name) values('Project');
INSERT INTO skills_data_types (type_name) values('Training');
INSERT INTO skills_data_types (type_name) values('Proposal');
INSERT INTO skills_data_types (type_name) values('Investigation');

UPDATE project_group_list
	set project_name='Default',description='Default Project - Don\'t Change'
	where group_project_id=1;


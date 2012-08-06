CREATE TABLE user_type (
type_id serial unique,
type_name text
);
INSERT into user_type (type_name) VALUES ('User');
INSERT into user_type (type_name) VALUES ('UserPool');

ALTER TABLE users ADD COLUMN type_id INT;
ALTER TABLE users ALTER COLUMN type_id SET DEFAULT 1;
UPDATE users SET type_id=1;
ALTER TABLE users ADD CONSTRAINT users_typeid
        FOREIGN KEY (type_id) REFERENCES user_type(type_id) MATCH FULL;

--
--	Each FRS Package now has public/private flags
--
ALTER TABLE frs_package ADD COLUMN is_public INT;
ALTER TABLE frs_package ALTER COLUMN is_public SET DEFAULT 1;
UPDATE frs_package SET is_public=1;

CREATE TABLE role (
role_id serial unique,
group_id int not null REFERENCES groups(group_id) ON DELETE CASCADE,
role_name text
);
CREATE UNIQUE INDEX role_groupidroleid ON role(group_id,role_id);

INSERT INTO role (group_id,role_name) VALUES (1,'Default');

--DROP TABLE role_section;
--DROP SEQUENCE role_section_section_id_seq;
--DROP TABLE role_value;
--DROP VIEW role_section_value_vw;

--
--
--	This new table will store separate perms for each task manager subproject
--
--
CREATE TABLE project_perm (
id serial unique,
group_project_id int not null REFERENCES project_group_list(group_project_id) ON DELETE CASCADE,
user_id int not null REFERENCES users(user_id) MATCH FULL,
perm_level int not null default 0
);
CREATE UNIQUE INDEX projectperm_groupprojiduserid ON project_perm(group_project_id,user_id);

DELETE FROM project_perm;
INSERT INTO project_perm (group_project_id,user_id,perm_level)
	SELECT project_group_list.group_project_id,user_group.user_id,user_group.project_flags
	FROM user_group,project_group_list
	WHERE project_group_list.group_id=user_group.group_id
	AND NOT EXISTS (SELECT user_id FROM project_perm WHERE project_perm.group_project_id=
	project_group_list.group_project_id);

--
--
--	This new table will store separate perms for each forum
--
--
CREATE TABLE forum_perm (
id serial unique,
group_forum_id int not null REFERENCES forum_group_list(group_forum_id) ON DELETE CASCADE,
user_id int not null REFERENCES users(user_id) MATCH FULL,
perm_level int not null default 0
);
CREATE UNIQUE INDEX forumperm_groupforumiduserid ON forum_perm(group_forum_id,user_id);

DELETE FROM forum_perm;
INSERT INTO forum_perm (group_forum_id,user_id,perm_level)
	SELECT forum_group_list.group_forum_id,user_group.user_id,user_group.forum_flags
	FROM user_group,forum_group_list
	WHERE forum_group_list.group_id=user_group.group_id
	AND NOT EXISTS (SELECT user_id FROM forum_perm WHERE forum_perm.group_forum_id=
	forum_group_list.group_forum_id);


--
--	Add to all trackers
--
update user_group set artifact_flags=0 where artifact_flags is null;
INSERT INTO artifact_perm (group_artifact_id,user_id,perm_level)
	SELECT artifact_group_list.group_artifact_id,user_group.user_id,user_group.artifact_flags
	FROM user_group,artifact_group_list
	WHERE artifact_group_list.group_id=user_group.group_id
	AND NOT EXISTS (SELECT user_id FROM artifact_perm WHERE artifact_perm.group_artifact_id=
	artifact_group_list.group_artifact_id);

--
--	This table contains all the settings for this particular role
--
--	example; 1,'docman',$category_id,1
--
CREATE TABLE role_setting (
role_id int not null REFERENCES role(role_id) ON DELETE CASCADE,
section_name text not null,
ref_id int not null, --optional ID for something like artifact_type_id or doc_category_id
value varchar(2) not null
);
CREATE INDEX rolesetting_roleidsectionid ON role_setting(role_id,section_name);

ALTER TABLE user_group ADD COLUMN role_id INT;
ALTER TABLE user_group ALTER COLUMN role_id SET DEFAULT 1;
UPDATE user_group SET role_id='1';
ALTER TABLE user_group ADD CONSTRAINT usergroup_roleid
        FOREIGN KEY (role_id) REFERENCES role(role_id) MATCH FULL;



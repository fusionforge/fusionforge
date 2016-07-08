CREATE TABLE doc_data_version (
    serial_id       SERIAL PRIMARY KEY,
    version         integer DEFAULT 1 NOT NULL,
    docid          integer REFERENCES doc_data ON DELETE CASCADE,
    current_version integer DEFAULT 1 NOT NULL,
    title           character varying(255) DEFAULT ''::character varying NOT NULL,
    updatedate      integer DEFAULT 0 NOT NULL,
    createdate      integer DEFAULT 0 NOT NULL,
    created_by      integer DEFAULT 0 NOT NULL,
    description     text,
    filename        text,
    filetype        text,
    filesize        integer DEFAULT 0 NOT NULL,
    data_words      text DEFAULT ''::text NOT NULL
);

INSERT INTO doc_data_version (serial_id, docid, title, updatedate, createdate, created_by, description, filename, filetype, filesize, data_words)
       (SELECT docid, docid, title, updatedate, createdate, created_by, description, filename, filetype, filesize, data_words FROM doc_data);

SELECT setval('doc_data_version_serial_id_seq', (SELECT MAX(serial_id) from doc_data_version));

DROP VIEW docdata_vw CASCADE;

ALTER TABLE doc_data ADD COLUMN version integer DEFAULT 1 NOT NULL;
UPDATE doc_data set version = 1;
ALTER TABLE doc_data DROP COLUMN title;
ALTER TABLE doc_data DROP COLUMN created_by;
ALTER TABLE doc_data DROP COLUMN description;
ALTER TABLE doc_data DROP COLUMN filename;
ALTER TABLE doc_data DROP COLUMN filetype;
ALTER TABLE doc_data DROP COLUMN filesize;
ALTER TABLE doc_data DROP COLUMN data_words;

CREATE VIEW docdata_vw AS
  SELECT users.user_name, users.realname, users.email,
         d.group_id, d.docid, d.stateid,
         dv.title, dv.updatedate, d.createdate, dv.created_by, d.doc_group, dv.description,
         dl.downloads AS download,
         dv.filename, dv.filetype, dv.filesize, d.reserved, d.reserved_by, d.locked, d.locked_by, d.lockdate,
         doc_states.name AS state_name, doc_groups.groupname AS group_name, dv.version, dv.serial_id
  FROM doc_data d, users, doc_groups, doc_states, docman_dlstats_doctotal_agg dl, doc_data_version dv
  WHERE dv.created_by = users.user_id
        and doc_groups.doc_group = d.doc_group
        and doc_states.stateid = d.stateid
        and d.docid = dl.docid
        and d.docid = dv.docid
        and dv.current_version = 1;

CREATE OR REPLACE VIEW activity_vw AS
  SELECT
    agl.group_id, 'trackeropen'::text AS section, agl.group_artifact_id AS ref_id,
    a.artifact_id as subref_id, a.summary as description, a.open_date AS activity_date, u.user_id, u.user_name, u.realname
  FROM artifact_group_list agl JOIN artifact a using (group_artifact_id),
    users u WHERE u.user_id=a.submitted_by
  UNION
  SELECT
    agl.group_id, 'trackerclose'::text AS section, agl.group_artifact_id AS ref_id,
    a.artifact_id as subref_id, a.summary as description, a.close_date AS activity_date, u.user_id, u.user_name, u.realname
  FROM artifact_group_list agl JOIN artifact a using (group_artifact_id), users u WHERE u.user_id=a.assigned_to
--actually should join against
  AND a.close_date > 0
  UNION
  SELECT
    frsp.group_id, 'frsrelease'::text as section,frsp.package_id as ref_id,
    frsr.release_id as subref_id, frsp.name::text||' - '||frsr.name::text AS description, frsr.release_date AS activity_date,
    u.user_id, u.user_name, u.realname FROM frs_package frsp JOIN frs_release frsr USING (package_id), users u WHERE
    u.user_id=frsr.released_by
  UNION
  SELECT
    fgl.group_id, 'forumpost'::text as section,fgl.group_forum_id as ref_id, forum.msg_id
    as subref_id, forum.subject AS description, forum.post_date AS activity_date, u.user_id,
    u.user_name, u.realname FROM forum_group_list fgl JOIN forum USING (group_forum_id), users u WHERE
    u.user_id=forum.posted_by
  UNION
  SELECT group_id, 'docmannew'::text AS section, doc_group AS ref_id, docid AS subref_id,
    filename AS description, createdate AS activity_date, created_by as user_id,
    user_name, realname FROM docdata_vw
  UNION
  SELECT group_id, 'docmanupdate'::text AS section, doc_group AS ref_id, docid AS subref_id,
    filename AS description, updatedate AS activity_date, created_by as user_id,
    user_name, realname FROM docdata_vw
  UNION
  SELECT doc_groups.group_id, 'docgroupnew'::text AS section, doc_groups.parent_doc_group AS ref_id, doc_groups.doc_group AS subref_id,
    doc_groups.groupname AS description,  doc_groups.createdate AS activity_date, doc_groups.created_by as user_id,
    users.user_name, users.realname FROM doc_groups, users WHERE doc_groups.created_by = users.user_id
  UNION
  SELECT news_bytes.group_id,'news' AS section,news_bytes.id AS ref_id,news_bytes.forum_id AS subref_id,
    news_bytes.summary AS description, news_bytes.post_date AS activity_date, u.user_id, u.user_name, u.realname
  FROM news_bytes, users u WHERE u.user_id = news_bytes.submitted_by
  UNION
  SELECT pgl.group_id, 'taskopen'::text AS section, p.group_project_id AS ref_id,
    p.project_task_id AS subref_id,	p.summary AS description, p.last_modified_date AS activity_date,
    u.user_id, u.user_name, u.realname
  FROM project_task p
  JOIN project_group_list pgl USING (group_project_id), users u
  WHERE u.user_id = p.created_by AND p.status_id = 1
  UNION
  SELECT pgl.group_id, 'taskclose'::text AS section, p.group_project_id AS ref_id,p.project_task_id AS subref_id,
    p.summary AS description, p.last_modified_date AS activity_date, u.user_id,
    u.user_name, u.realname
  FROM project_task p
  JOIN project_group_list pgl USING (group_project_id), users u
  WHERE u.user_id = p.created_by AND p.status_id = 2
  UNION
  SELECT pgl.group_id, 'taskdelete'::text AS section, p.group_project_id AS ref_id,
    p.project_task_id AS subref_id, p.summary AS description, p.last_modified_date AS activity_date,
    u.user_id, u.user_name, u.realname
  FROM project_task p
  JOIN project_group_list pgl USING (group_project_id), users u
  WHERE u.user_id = p.created_by AND p.status_id = 3;

ALTER TABLE doc_data_idx ADD COLUMN version integer;
ALTER TABLE doc_data_words_idx ADD COLUMN version integer;

CREATE OR REPLACE FUNCTION update_vectors() RETURNS TRIGGER AS '
DECLARE
table_name TEXT;
BEGIN
	table_name := TG_ARGV[0];
	-- **** artifact table ****
	IF table_name = ''artifact'' THEN
		IF TG_OP = ''DELETE'' THEN
		      DELETE FROM artifact_idx WHERE artifact_id=OLD.artifact_id;
		ELSE
		      DELETE FROM artifact_idx WHERE artifact_id=NEW.artifact_id;
		      INSERT INTO artifact_idx (SELECT a.artifact_id, to_tsvector(a.artifact_id::text) || to_tsvector(a.summary) || to_tsvector(a.details) || coalesce(ff_tsvector_agg(to_tsvector(am.body)), to_tsvector('''')) AS vectors FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id) WHERE a.artifact_id=NEW.artifact_id GROUP BY a.artifact_id, a.summary, a.details);
		END IF;
	-- **** artifact_message table ****
	ELSIF table_name = ''artifact_message'' THEN
		IF TG_OP = ''DELETE'' THEN
		      DELETE FROM artifact_idx WHERE artifact_id=OLD.artifact_id;
		ELSE
		      DELETE FROM artifact_idx WHERE artifact_id=NEW.artifact_id;
		      INSERT INTO artifact_idx (SELECT a.artifact_id, to_tsvector(a.artifact_id::text) || to_tsvector(a.summary) || to_tsvector(a.details) || coalesce(ff_tsvector_agg(to_tsvector(am.body)), to_tsvector('''')) AS vectors FROM artifact a LEFT OUTER JOIN artifact_message am USING (artifact_id) WHERE a.artifact_id=NEW.artifact_id GROUP BY a.artifact_id, a.summary, a.details);
		END IF;
	-- **** doc_data_version table ****
	ELSIF table_name = ''doc_data_version'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO doc_data_idx (docid, version, group_id, vectors) VALUES (NEW.docid, NEW.version, (select group_id from doc_data where docid = NEW.docid), to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.description,'''')));
			INSERT INTO doc_data_words_idx (docid, version, group_id, vectors) VALUES (NEW.docid, (select group_id from doc_data where docid = NEW.docid), to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.description,'''') ||'' ''|| coalesce(NEW.filename,'''') ||'' ''|| coalesce(NEW.filetype,'''') ||'' ''|| coalesce(NEW.data_words,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE doc_data_idx SET group_id = (select group_id from doc_data where docid = NEW.docid), vectors=to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.description,'''')) WHERE docid = NEW.docid and version = NEW.version;
			UPDATE doc_data_words_idx SET group_id = (select group_id from doc_data where docid = NEW.docid), vectors=to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.description,'''') ||'' ''|| coalesce(NEW.filename,'''') ||'' ''|| coalesce(NEW.filetype,'''') ||'' ''|| coalesce(NEW.data_words,'''')) WHERE docid = NEW.docid and version = NEW.version;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM doc_data_idx WHERE version = OLD.version;
			DELETE FROM doc_data_words_idx WHERE version = OLD.version;
		END IF;
	-- **** forum table ****
	ELSIF table_name = ''forum'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO forum_idx (msg_id, group_id, vectors) (SELECT f.msg_id, g.group_id, to_tsvector(coalesce(f.subject,'''') ||'' ''||
			coalesce(f.body,'''')) AS vectors FROM forum f, forum_group_list g WHERE f.group_forum_id = g.group_forum_id AND f.msg_id = NEW.msg_id);
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE forum_idx SET vectors=to_tsvector(coalesce(NEW.subject,'''') ||'' ''|| coalesce(NEW.body,'''')) WHERE msg_id=NEW.msg_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM forum_idx WHERE msg_id=OLD.msg_id;
		END IF;
	-- **** frs_file table ****
	ELSIF table_name = ''frs_file'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_file_idx (file_id, release_id, vectors) VALUES (NEW.file_id, NEW.release_id, to_tsvector(coalesce(NEW.filename,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_file_idx SET vectors=to_tsvector(coalesce(NEW.filename,'''')), release_id=NEW.release_id WHERE file_id=NEW.file_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_file_idx WHERE file_id=OLD.file_id;
		END IF;
	-- **** frs_release table ****
	ELSIF table_name = ''frs_release'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_release_idx (release_id, vectors) VALUES (NEW.release_id, to_tsvector(coalesce(NEW.changes,'''') ||'' ''|| coalesce(NEW.notes,'''') ||'' ''|| coalesce(NEW.name,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_release_idx SET vectors=to_tsvector(coalesce(NEW.changes,'''') ||'' ''|| coalesce(NEW.notes,'''') ||'' ''|| coalesce(NEW.name,'''')) WHERE release_id=NEW.release_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_release_idx WHERE release_id=OLD.release_id;
			DELETE FROM frs_file_idx WHERE release_id=OLD.release_id;
		END IF;
	-- **** groups table ****
	ELSIF table_name = ''groups'' THEN
		IF TG_OP = ''DELETE'' THEN
			DELETE FROM groups_idx WHERE group_id=OLD.group_id;
		ELSE
			DELETE FROM groups_idx WHERE group_id=NEW.group_id;
			INSERT INTO groups_idx (group_id, vectors) (SELECT g.group_id, to_tsvector(coalesce(g.group_name,'''') ||'' ''|| coalesce(g.short_description,'''') ||'' ''|| coalesce(g.unix_group_name,'''')||'' ''|| coalesce(ff_tsvector_agg(to_tsvector(t.name)),to_tsvector(''''))) FROM groups g LEFT OUTER JOIN project_tags t USING (group_id) WHERE g.group_id = NEW.group_id GROUP BY g.group_id ORDER BY g.group_id);
		END IF;
	-- **** news_bytes table ****
	ELSIF table_name = ''news_bytes'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO news_bytes_idx (id, vectors) VALUES (NEW.id, to_tsvector(coalesce(NEW.summary,'''') ||'' ''|| coalesce(NEW.details,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE news_bytes_idx SET vectors=to_tsvector(coalesce(NEW.summary,'''') ||'' ''|| coalesce(NEW.details,'''')) WHERE id=NEW.id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM news_bytes_idx WHERE id=OLD.id;
		END IF;
	-- **** project_task table ****
	ELSIF table_name = ''project_task'' THEN
		IF TG_OP = ''DELETE'' THEN
			DELETE FROM project_task_idx WHERE project_task_id=OLD.project_task_id;
		ELSE
			DELETE FROM project_task_idx WHERE project_task_id=NEW.project_task_id;
			INSERT INTO project_task_idx (SELECT t.project_task_id, to_tsvector(t.project_task_id::text) || to_tsvector(t.summary) || to_tsvector(t.details) || coalesce(ff_tsvector_agg(to_tsvector(tm.body)), to_tsvector('''')) AS vectors FROM project_task t LEFT OUTER JOIN project_messages tm USING (project_task_id) WHERE t.project_task_id=NEW.project_task_id GROUP BY t.project_task_id, t.summary, t.details);
		END IF;
	-- **** project_messages table ****
	ELSIF table_name = ''project_messages'' THEN
		IF TG_OP = ''DELETE'' THEN
			DELETE FROM project_task_idx WHERE project_task_id=OLD.project_task_id;
		ELSE
			DELETE FROM project_task_idx WHERE project_task_id=NEW.project_task_id;
			INSERT INTO project_task_idx (SELECT t.project_task_id, to_tsvector(t.summary) || to_tsvector(t.details) || coalesce(ff_tsvector_agg(to_tsvector(tm.body)), to_tsvector('''')) AS vectors FROM project_task t LEFT OUTER JOIN project_messages tm USING (project_task_id) WHERE t.project_task_id=NEW.project_task_id GROUP BY t.project_task_id, t.summary, t.details);
		END IF;
	-- **** skills_data table ****
	ELSIF table_name = ''skills_data'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO skills_data_idx (skills_data_id, vectors) VALUES (NEW.skills_data_id, to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.keywords,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE skills_data_idx SET vectors=to_tsvector(coalesce(NEW.title,'''') ||'' ''|| coalesce(NEW.keywords,'''')) WHERE skills_data_id=NEW.skills_data_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM skills_data_idx WHERE skills_data_id=OLD.skills_data_id;
		END IF;
	-- **** users table ****
	ELSIF table_name = ''users'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO users_idx (user_id, vectors) VALUES (NEW.user_id, to_tsvector(coalesce(NEW.user_name,'''') ||'' ''|| coalesce(NEW.realname,'''')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE users_idx SET vectors=to_tsvector(coalesce(NEW.user_name,'''') ||'' ''|| coalesce(NEW.realname,'''')) WHERE user_id=NEW.user_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM users_idx WHERE user_id=OLD.user_id;
		END IF;
	END IF;

	RETURN NEW;
END;'
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION rebuild_fti_indices() RETURNS void AS $$
BEGIN
       UPDATE groups SET short_description=short_description;
       UPDATE artifact SET summary=summary;
       UPDATE artifact_message SET body=body;
       UPDATE doc_data_version SET title=title;
       UPDATE forum SET subject=subject;
       UPDATE frs_file SET filename=filename;
       UPDATE frs_release SET name=name;
       UPDATE news_bytes SET summary=summary;
       UPDATE project_task SET summary=summary;
       UPDATE project_messages SET body=body;
       UPDATE skills_data SET keywords=keywords;
       UPDATE users SET realname=realname;
END;
$$ LANGUAGE 'plpgsql';

-- Rebuild all indices
SELECT rebuild_fti_indices();

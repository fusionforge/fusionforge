SET client_min_messages TO warning;

-- ********** Create auxiliar tables **********

CREATE TABLE artifact_idx (
	artifact_id integer,
	group_artifact_id integer,
	vectors tsvector
);

CREATE TABLE artifact_message_idx (
	id integer,
	artifact_id integer,
	vectors tsvector
);

CREATE TABLE doc_data_idx (
	docid integer,
	group_id integer,
	vectors tsvector
);

CREATE TABLE forum_idx (
	msg_id integer,
	group_id integer,
	vectors tsvector
);

CREATE TABLE frs_file_idx (
	file_id integer,
	release_id integer,
	vectors tsvector
);

CREATE TABLE frs_release_idx (
	release_id integer,
	vectors tsvector
);

CREATE TABLE groups_idx (
	group_id integer,
	vectors tsvector
);

CREATE TABLE news_bytes_idx (
	id integer,
	vectors tsvector
);

CREATE TABLE project_task_idx (
	project_task_id integer,
	vectors tsvector
);

CREATE TABLE skills_data_idx (
	skills_data_id integer,
	vectors tsvector
);

CREATE TABLE users_idx (
	user_id integer,
	vectors tsvector
);

-- ********** Populate with current data and create index **********

INSERT INTO artifact_idx (artifact_id, group_artifact_id, vectors)
SELECT artifact_id, group_artifact_id, to_tsvector('default', coalesce(details,'') ||'
'|| coalesce(summary,'')) AS vectors
FROM artifact ORDER BY artifact_id;

CREATE INDEX artifact_idxFTI ON artifact_idx USING gist(vectors);

INSERT INTO artifact_message_idx (id, artifact_id, vectors)
SELECT id, artifact_id, to_tsvector('default', coalesce(body,'')) AS vectors
FROM artifact_message ORDER BY id;

CREATE INDEX artifact_message_idxFTI ON artifact_message_idx USING gist(vectors);

INSERT INTO doc_data_idx (docid, group_id, vectors)
SELECT docid, group_id, to_tsvector('default', coalesce(title,'') ||'
'|| coalesce(description,'')) AS vectors
FROM doc_data ORDER BY docid;

CREATE INDEX doc_data_idxFTI ON doc_data_idx USING gist(vectors);

INSERT INTO forum_idx (msg_id, group_id, vectors)
(SELECT f.msg_id, g.group_id, to_tsvector('default', coalesce(f.subject,'') ||'
'|| coalesce(f.body,'')) AS vectors
FROM forum f, forum_group_list g WHERE f.group_forum_id = g.group_forum_id)
ORDER BY msg_id;

CREATE INDEX forum_idxFTI ON forum_idx USING gist(vectors);

INSERT INTO frs_file_idx (file_id, release_id, vectors)
SELECT file_id, release_id, to_tsvector('default', coalesce(filename,'')) AS vectors
FROM frs_file ORDER BY file_id;

CREATE INDEX frs_file_idxFTI ON frs_file_idx USING gist(vectors);

INSERT INTO frs_release_idx (release_id, vectors)
SELECT release_id, to_tsvector('default', coalesce(changes,'') ||'
'|| coalesce(notes,'') ||' '|| coalesce(name,'')) AS vectors
FROM frs_release ORDER BY release_id;

CREATE INDEX frs_release_idxFTI ON frs_release_idx USING gist(vectors);

INSERT INTO groups_idx (group_id, vectors)
SELECT group_id, to_tsvector('default', coalesce(group_name,'') ||'
'|| coalesce(short_description,'') ||' '|| coalesce(unix_group_name,'')) AS vectors
FROM groups ORDER BY group_id;

CREATE INDEX groups_idxFTI ON groups_idx USING gist(vectors);

INSERT INTO news_bytes_idx (id, vectors)
SELECT id, to_tsvector('default', coalesce(summary,'') ||'
'|| coalesce(details,'')) AS vectors
FROM news_bytes ORDER BY id;

CREATE INDEX news_bytes_idxFTI ON news_bytes_idx USING gist(vectors);

INSERT INTO project_task_idx (project_task_id, vectors)
SELECT project_task_id, to_tsvector('default', coalesce(summary,'') ||'
'|| coalesce(details,'')) AS vectors
FROM project_task ORDER BY project_task_id;

--
--	TODO project_messages
--

CREATE INDEX project_task_idxFTI ON project_task_idx USING gist(vectors);

INSERT INTO skills_data_idx (skills_data_id, vectors)
SELECT skills_data_id, to_tsvector('default', coalesce(title,'') ||'
'|| coalesce(keywords,'')) AS vectors
FROM skills_data ORDER BY skills_data_id;

CREATE INDEX skills_data_idxFTI ON skills_data_idx USING gist(vectors);

INSERT INTO users_idx (user_id, vectors)
SELECT user_id, to_tsvector('default', coalesce(user_name,'') ||'
'|| coalesce(realname,'')) AS vectors
FROM users ORDER BY user_id;

CREATE INDEX users_idxFTI ON users_idx USING gist(vectors);

-- VACUUM FULL ANALYZE;

-- ********** Create trigger function to update idx tables **********

CREATE OR REPLACE FUNCTION update_vectors() RETURNS TRIGGER AS '
DECLARE
table_name TEXT;
BEGIN
	table_name := TG_ARGV[0];
	-- **** artifact table ****
	IF table_name = ''artifact'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO artifact_idx (artifact_id, group_artifact_id, vectors) VALUES (NEW.artifact_id, NEW.group_artifact_id, to_tsvector(\'default\', coalesce(NEW.details,\'\') ||\' \'|| coalesce(NEW.summary,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE artifact_idx SET group_artifact_id=NEW.group_artifact_id, vectors=to_tsvector(\'default\', coalesce(NEW.details,\'\') ||\' \'|| coalesce(NEW.summary,\'\')) WHERE artifact_id=NEW.artifact_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM artifact_idx WHERE artifact_id=OLD.artifact_id;
		END IF;
	-- **** artifact_message table ****
	ELSIF table_name = ''artifact_message'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO artifact_message_idx (id, artifact_id, vectors) VALUES (NEW.id, NEW.artifact_id, to_tsvector(\'default\', coalesce(NEW.body,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE artifact_message_idx SET artifact_id=NEW.artifact_id, vectors=to_tsvector(\'default\', coalesce(NEW.body,\'\')) WHERE id=NEW.id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM artifact_message_idx WHERE id=OLD.id;
		END IF;
	-- **** doc_data table ****
	ELSIF table_name = ''doc_data'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO doc_data_idx (docid, group_id, vectors) VALUES (NEW.docid, NEW.group_id, to_tsvector(\'default\', coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.description,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE doc_data_idx SET group_id=NEW.group_id, vectors=to_tsvector(\'default\', coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.description,\'\')) WHERE docid=NEW.docid;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM doc_data_idx WHERE docid=OLD.docid;
		END IF;
	-- **** forum table ****
	ELSIF table_name = ''forum'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO forum_idx (msg_id, group_id, vectors) (SELECT f.msg_id, g.group_id, to_tsvector(\'default\', coalesce(f.subject,\'\') ||\' \'||
			coalesce(f.body,\'\')) AS vectors FROM forum f, forum_group_list g WHERE f.group_forum_id = g.group_forum_id AND f.msg_id = NEW.msg_id);
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE forum_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.subject,\'\') ||\' \'|| coalesce(NEW.body,\'\')) WHERE msg_id=NEW.msg_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM forum_idx WHERE msg_id=OLD.msg_id;
		END IF;
	-- **** frs_file table ****
	ELSIF table_name = ''frs_file'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_file_idx (file_id, release_id, vectors) VALUES (NEW.file_id, NEW.release_id, to_tsvector(\'default\', coalesce(NEW.filename,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_file_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.filename,\'\')), release_id=NEW.release_id WHERE file_id=NEW.file_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_file_idx WHERE file_id=OLD.file_id;
		END IF;
	-- **** frs_release table ****
	ELSIF table_name = ''frs_release'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO frs_release_idx (release_id, vectors) VALUES (NEW.release_id, to_tsvector(\'default\', coalesce(changes,\'\') ||\' \'|| coalesce(notes,\'\') ||\' \'|| coalesce(name,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE frs_release_idx SET vectors=to_tsvector(\'default\', coalesce(changes,\'\') ||\' \'|| coalesce(notes,\'\') ||\' \'|| coalesce(name,\'\')) WHERE release_id=NEW.release_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM frs_release_idx WHERE release_id=OLD.release_id;
			DELETE FROM frs_file_idx WHERE release_id=OLD.release_id;
		END IF;
	-- **** groups table ****
	ELSIF table_name = ''groups'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO groups_idx (group_id, vectors) VALUES (NEW.group_id, to_tsvector(\'default\', coalesce(NEW.group_name,\'\') ||\' \'|| coalesce(NEW.short_description,\'\') ||\' \'|| coalesce(NEW.unix_group_name,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE groups_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.group_name,\'\') ||\' \'|| coalesce(NEW.short_description,\'\') ||\' \'|| coalesce(NEW.unix_group_name,\'\')) WHERE group_id=NEW.group_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM groups_idx WHERE group_id=OLD.group_id;
		END IF;
	-- **** news_bytes table ****
	ELSIF table_name = ''news_bytes'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO news_bytes_idx (id, vectors) VALUES (NEW.id, to_tsvector(\'default\', coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE news_bytes_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')) WHERE id=NEW.id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM news_bytes_idx WHERE id=OLD.id;
		END IF;
	-- **** project_task table ****
	ELSIF table_name = ''project_task'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO project_task_idx (project_task_id, vectors) VALUES (NEW.project_task_id, to_tsvector(\'default\', coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE project_task_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.summary,\'\') ||\' \'|| coalesce(NEW.details,\'\')) WHERE project_task_id=NEW.project_task_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM project_task_idx WHERE project_task_id=OLD.project_task_id;
		END IF;
	-- **** skills_data table ****
	ELSIF table_name = ''skills_data'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO skills_data_idx (skills_data_id, vectors) VALUES (NEW.skill_data_id, to_tsvector(\'default\', coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.keywords,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE skills_data_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.title,\'\') ||\' \'|| coalesce(NEW.keywords,\'\')) WHERE skills_data_id=NEW.skills_data_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM skills_data_idx WHERE skills_data_id=OLD.skills_data_id;
		END IF;
	-- **** users table ****
	ELSIF table_name = ''users'' THEN
		IF TG_OP = ''INSERT'' THEN
			INSERT INTO users_idx (user_id, vectors) VALUES (NEW.user_id, to_tsvector(\'default\', coalesce(NEW.user_name,\'\') ||\' \'|| coalesce(NEW.realname,\'\')));
		ELSIF TG_OP = ''UPDATE'' THEN
			UPDATE users_idx SET vectors=to_tsvector(\'default\', coalesce(NEW.user_name,\'\') ||\' \'|| coalesce(NEW.realname,\'\')) WHERE user_id=NEW.user_id;
		ELSIF TG_OP = ''DELETE'' THEN
			DELETE FROM users_idx WHERE user_id=OLD.user_id;
		END IF;
	END IF;

	RETURN NEW;
END;'
LANGUAGE 'plpgsql';

-- ********** Set up triggers **********

CREATE TRIGGER artifact_ts_update AFTER UPDATE OR INSERT OR DELETE ON artifact
FOR EACH ROW EXECUTE PROCEDURE update_vectors('artifact');

CREATE TRIGGER doc_data_ts_update AFTER UPDATE OR INSERT OR DELETE ON doc_data
FOR EACH ROW EXECUTE PROCEDURE update_vectors('doc_data');

CREATE TRIGGER forum_update AFTER UPDATE OR INSERT OR DELETE ON forum
FOR EACH ROW EXECUTE PROCEDURE update_vectors('forum');

CREATE TRIGGER frs_file_ts_update AFTER UPDATE OR INSERT OR DELETE ON frs_file
FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_file');

CREATE TRIGGER frs_release_ts_update AFTER UPDATE OR INSERT OR DELETE ON frs_release
FOR EACH ROW EXECUTE PROCEDURE update_vectors('frs_release');

CREATE TRIGGER groups_ts_update AFTER UPDATE OR INSERT OR DELETE ON groups
FOR EACH ROW EXECUTE PROCEDURE update_vectors('groups');

CREATE TRIGGER news_bytes_ts_update AFTER UPDATE OR INSERT OR DELETE ON news_bytes
FOR EACH ROW EXECUTE PROCEDURE update_vectors('news_bytes');

CREATE TRIGGER project_task_ts_update AFTER UPDATE OR INSERT OR DELETE ON project_task
FOR EACH ROW EXECUTE PROCEDURE update_vectors('project_task');

CREATE TRIGGER skills_data_ts_update AFTER UPDATE OR INSERT OR DELETE ON skills_data
FOR EACH ROW EXECUTE PROCEDURE update_vectors('skills_data');

CREATE TRIGGER users_ts_update AFTER UPDATE OR INSERT OR DELETE ON users
FOR EACH ROW EXECUTE PROCEDURE update_vectors('users');

-- ********** Create types for results **********

CREATE TYPE artifact_results AS (group_artifact_id integer,
	artifact_id integer,
	summary text,
	open_date integer,
	realname character varying(32)
);

CREATE TYPE doc_data_results AS (docid integer,
	title text,
	description text,
	groupname character varying(255)
);

CREATE TYPE forum_results AS (msg_id integer,
	subject text,
	post_date integer,
	realname character varying(32)
);

CREATE TYPE frs_results AS (package_name text,
	release_name text,
	release_date integer,
	release_id integer,
	realname character varying(32)
);

CREATE TYPE groups_results AS (group_name text,
	unix_group_name text,
	type_id integer,
	group_id integer,
	short_description text
);

CREATE TYPE export_groups_results AS (group_name text,
	unix_group_name text,
	type_id integer,
	group_id integer,
	short_description text,
	license integer,
	register_time integer
);

CREATE TYPE news_bytes_results AS (summary text,
	post_date integer,
	forum_id integer,
	realname text
);

CREATE TYPE project_task_results AS (project_task_id integer,
	summary text,
	percent_complete integer,
	start_date integer,
	end_date integer,
	realname text,
	project_name text,
	group_project_id integer
);

CREATE TYPE skills_data_results AS (skills_data_id integer,
	type integer,
	title text,
	start integer,
	finish integer,
	keywords text
);

CREATE TYPE trackers_results AS (artifact_id integer,
	group_artifact_id integer,
	summary text,
	open_date integer,
	realname character varying(32),
	name text
);

CREATE TYPE users_results AS (user_name text,
	user_id integer,
	realname text
);

-- ********** Create search store procedures **********

CREATE OR REPLACE FUNCTION artifact_search(text, int) RETURNS SETOF artifact_results AS '
	SELECT a.group_artifact_id, a.artifact_id, a.summary, a.open_date, u.realname FROM artifact a,
	(SELECT DISTINCT ON (ai.artifact_id) ai.artifact_id, COUNT(ami.id) AS total FROM artifact_idx ai LEFT OUTER JOIN artifact_message_idx ami USING (artifact_id),
	to_tsquery($1) AS q
	WHERE ai.group_artifact_id=$2
	AND	(ai.vectors @@ q OR ami.vectors @@ q)
	GROUP BY ai.artifact_id) AS idx, users u, to_tsquery($1) AS q, artifact_idx ai
	WHERE idx.artifact_id=a.artifact_id
	AND u.user_id=a.submitted_by
	AND a.artifact_id=ai.artifact_id
	ORDER BY idx.total DESC, rank(ai.vectors, q) DESC'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION doc_data_search(text, integer, text, boolean) RETURNS SETOF doc_data_results AS '
	DECLARE
	data users_results;
	BEGIN
	IF $3 <> \'\' THEN
		IF $4 THEN
			FOR data IN SELECT doc_data.docid, headline(doc_data.title, q) AS title, headline(doc_data.description, q) AS description, doc_groups.groupname
			FROM doc_data, doc_groups, to_tsquery($1) AS q
			WHERE doc_data.doc_group = doc_groups.doc_group
			AND doc_data.group_id = $2
			AND doc_groups.doc_group IN (\'$3\')
			AND doc_data.stateid IN (1, 4, 5)
			AND doc_data.docid IN (SELECT docid FROM doc_data_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT doc_data.docid, headline(doc_data.title, q), headline(doc_data.description, q), doc_groups.groupname
			FROM doc_data, doc_groups, to_tsquery($1) AS q
			WHERE doc_data.doc_group = doc_groups.doc_group
			AND doc_data.group_id = $2
			AND doc_groups.doc_group IN (\'$3\')
			AND doc_data.stateid = 1
			AND doc_data.docid IN (SELECT docid FROM doc_data_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
		IF $4 THEN
			FOR data IN SELECT doc_data.docid, headline(doc_data.title, q), headline(doc_data.description, q), doc_groups.groupname
			FROM doc_data, doc_groups, to_tsquery($1) AS q
			WHERE doc_data.doc_group = doc_groups.doc_group
			AND doc_data.group_id = $2
			AND doc_data.stateid IN (1, 4, 5)
			AND doc_data.docid IN (SELECT docid FROM doc_data_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT doc_data.docid, headline(doc_data.title, q), headline(doc_data.description, q), doc_groups.groupname
			FROM doc_data, doc_groups, to_tsquery($1) AS q
			WHERE doc_data.doc_group = doc_groups.doc_group
			AND doc_data.group_id = $2
			AND doc_data.stateid = 1
			AND doc_data.docid IN (SELECT docid FROM doc_data_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';

CREATE TYPE forums_results AS (msg_id integer,
        subject text,
        post_date integer,
        realname character varying(32),
        forum_name text
);

CREATE OR REPLACE FUNCTION forums_search(text, integer, text, boolean) RETURNS SETOF forums_results AS '
	DECLARE
	data forums_results;
	BEGIN
	IF $3 <> \'\' THEN
		IF $4 THEN
			FOR data IN SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.group_forum_id IN (\'$3\')
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.group_forum_id IN (\'$3\')
			AND forum_group_list.is_public = 1
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
		IF $4 THEN
			FOR data IN SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname, forum_group_list.forum_name
			FROM forum, users, forum_group_list, to_tsquery($1) AS q
			WHERE users.user_id = forum.posted_by
			AND forum_group_list.group_forum_id = forum.group_forum_id
			AND forum_group_list.is_public <> 9
			AND forum_group_list.is_public = 1
			AND forum.msg_id IN (SELECT msg_id FROM forum_idx, to_tsquery($1) AS q
			WHERE group_id = $2 AND vectors @@ q ORDER BY rank(vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION forum_search(text, integer) RETURNS SETOF forum_results AS '
	SELECT forum.msg_id, headline(forum.subject, q) AS subject, forum.post_date, users.realname
	FROM forum, users, to_tsquery($1) AS q
	WHERE users.user_id=forum.posted_by
	AND msg_id IN (SELECT fi.msg_id FROM forum_idx fi, forum f, to_tsquery($1) AS q
	WHERE fi.msg_id = f.msg_id AND f.group_forum_id=$2
	AND vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION frs_search(text, integer, text, boolean) RETURNS SETOF frs_results AS '
	DECLARE
	data frs_results;
	BEGIN
	-- Search in specific sections
	IF $3 <> \'\' THEN
	    -- show non public
		IF $4 THEN
			FOR data IN SELECT headline(frs_package.name, q) AS package_name, headline(frs_release.name, q) AS release_name, frs_release.release_date, frs_release.release_id, users.realname
			FROM frs_file, frs_release, users, frs_package, to_tsquery($1) AS q
			WHERE frs_release.released_by = users.user_id
			AND frs_package.package_id = frs_release.package_id
			AND frs_file.release_id=frs_release.release_id
			AND frs_package.group_id=$2
			AND frs_package.package_id IN (\'$3\')
			AND frs_release.release_id IN (SELECT r.release_id FROM frs_release_idx r
			LEFT JOIN frs_file_idx f ON r.release_id=f.release_id, to_tsquery($1) AS q
			WHERE r.vectors @@ q OR f.vectors @@ q ORDER BY rank(r.vectors, q) DESC, rank(f.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT headline(frs_package.name, q) AS package_name, headline(frs_release.name, q) AS release_name, frs_release.release_date, frs_release.release_id, users.realname
			FROM frs_file, frs_release, users, frs_package, to_tsquery($1) AS q
			WHERE frs_release.released_by = users.user_id
			AND frs_package.package_id = frs_release.package_id
			AND frs_file.release_id=frs_release.release_id
			AND frs_package.group_id=$2
			AND frs_package.package_id IN (\'$3\')
			AND frs_package.is_public=1
			AND frs_release.release_id IN (SELECT r.release_id FROM frs_release_idx r
			LEFT JOIN frs_file_idx f ON r.release_id=f.release_id, to_tsquery($1) AS q
			WHERE r.vectors @@ q OR f.vectors @@ q ORDER BY rank(r.vectors, q) DESC, rank(f.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
		IF $4 THEN
			FOR data IN SELECT headline(frs_package.name, q) AS package_name, headline(frs_release.name, q) AS release_name, frs_release.release_date, frs_release.release_id, users.realname
			FROM frs_file, frs_release, users, frs_package, to_tsquery($1) AS q
			WHERE frs_release.released_by = users.user_id
			AND frs_package.package_id = frs_release.package_id
			AND frs_file.release_id=frs_release.release_id
			AND frs_package.group_id=$2
			AND frs_release.release_id IN (SELECT r.release_id FROM frs_release_idx r
			LEFT JOIN frs_file_idx f ON r.release_id=f.release_id, to_tsquery($1) AS q
			WHERE r.vectors @@ q OR f.vectors @@ q ORDER BY rank(r.vectors, q) DESC, rank(f.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT headline(frs_package.name, q) AS package_name, headline(frs_release.name, q) AS release_name, frs_release.release_date, frs_release.release_id, users.realname
			FROM frs_file, frs_release, users, frs_package, to_tsquery($1) AS q
			WHERE frs_release.released_by = users.user_id
			AND frs_package.package_id = frs_release.package_id
			AND frs_file.release_id=frs_release.release_id
			AND frs_package.group_id=$2
			AND frs_package.is_public=1
			AND frs_release.release_id IN (SELECT r.release_id FROM frs_release_idx r
			LEFT JOIN frs_file_idx f ON r.release_id=f.release_id, to_tsquery($1) AS q
			WHERE r.vectors @@ q OR f.vectors @@ q ORDER BY rank(r.vectors, q) DESC, rank(f.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION groups_search(text) RETURNS SETOF groups_results AS '
	SELECT headline(group_name, q) as group_name,
	headline(unix_group_name, q) as unix_group_name,
	type_id,
	group_id,
	headline(short_description, q) as short_description
	FROM groups, to_tsquery($1) AS q
	WHERE status IN (\'A\', \'H\') AND is_public=\'1\' AND
	group_id IN (SELECT group_id FROM groups_idx, to_tsquery($1) AS q
	WHERE vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION export_groups_search(text) RETURNS SETOF export_groups_results AS '
	SELECT headline(group_name, q) as group_name,
	headline(unix_group_name, q) as unix_group_name,
	type_id,
	group_id,
	headline(short_description, q) as short_description,
	license,
	register_time
	FROM groups, to_tsquery($1) AS q
	WHERE status IN (\'A\', \'H\') AND is_public=\'1\' AND short_description <> \'\' AND
	group_id IN (SELECT group_id FROM groups_idx, to_tsquery($1) AS q
	WHERE vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION news_bytes_search(text, integer) RETURNS SETOF news_bytes_results AS '
	SELECT headline(news_bytes.summary, q) as summary,
	news_bytes.post_date,
	news_bytes.forum_id,
	users.realname
	FROM news_bytes, users, to_tsquery($1) AS q
	WHERE (news_bytes.group_id=$2 AND news_bytes.is_approved <> ''4'' AND news_bytes.submitted_by=users.user_id) AND
	news_bytes.id IN (SELECT id FROM news_bytes_idx,
	to_tsquery($1) AS q WHERE vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION project_task_search(text, integer, text, boolean) RETURNS SETOF project_task_results AS '
	DECLARE
	data project_task_results;
	BEGIN
	-- Search in specific sections
	IF $3 <> \'\' THEN
	    -- show non public
		IF $4 THEN
			FOR data IN SELECT project_task.project_task_id, headline(project_task.summary, q) AS summary, project_task.percent_complete,
			project_task.start_date, project_task.end_date, users.firstname||\' \'||users.lastname AS realname,
			project_group_list.project_name, project_group_list.group_project_id
			FROM project_task, users, project_group_list, to_tsquery($1) AS q
			WHERE project_task.created_by = users.user_id
			AND project_task.group_project_id = project_group_list.group_project_id
			AND project_group_list.group_id = $2
			AND project_group_list.group_project_id IN (\'$3\')
			AND project_task.project_task_id IN (SELECT pti.project_task_id FROM project_task_idx pti, project_task pt, project_group_list pgl, to_tsquery($1) AS q
			WHERE pti.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			AND pgl.group_id = $2
			AND pti.vectors @@ q ORDER BY rank(pti.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT project_task.project_task_id, headline(project_task.summary, q) AS summary, project_task.percent_complete,
			project_task.start_date, project_task.end_date, users.firstname||\' \'||users.lastname AS realname,
			project_group_list.project_name, project_group_list.group_project_id
			FROM project_task, users, project_group_list, to_tsquery($1) AS q
			WHERE project_task.created_by = users.user_id
			AND project_task.group_project_id = project_group_list.group_project_id
			AND project_group_list.group_id = $2
			AND project_group_list.group_project_id IN (\'$3\')
			AND project_group_list.is_public = 1
			AND project_task.project_task_id IN (SELECT pti.project_task_id FROM project_task_idx pti, project_task pt, project_group_list pgl, to_tsquery($1) AS q
			WHERE pti.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			AND pgl.group_id = $2
			AND pti.vectors @@ q ORDER BY rank(pti.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
		IF $4 THEN
			FOR data IN SELECT project_task.project_task_id, headline(project_task.summary, q) AS summary, project_task.percent_complete,
			project_task.start_date, project_task.end_date, users.firstname||\' \'||users.lastname AS realname,
			project_group_list.project_name, project_group_list.group_project_id
			FROM project_task, users, project_group_list, to_tsquery($1) AS q
			WHERE project_task.created_by = users.user_id
			AND project_task.group_project_id = project_group_list.group_project_id
			AND project_group_list.group_id = $2
			AND project_task.project_task_id IN (SELECT pti.project_task_id FROM project_task_idx pti, project_task pt, project_group_list pgl, to_tsquery($1) AS q
			WHERE pti.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			AND pgl.group_id = $2
			AND pti.vectors @@ q ORDER BY rank(pti.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT project_task.project_task_id, headline(project_task.summary, q) AS summary, project_task.percent_complete,
			project_task.start_date, project_task.end_date, users.firstname||\' \'||users.lastname AS realname,
			project_group_list.project_name, project_group_list.group_project_id
			FROM project_task, users, project_group_list, to_tsquery($1) AS q
			WHERE project_task.created_by = users.user_id
			AND project_task.group_project_id = project_group_list.group_project_id
			AND project_group_list.group_id = $2
			AND project_group_list.is_public = 1
			AND project_task.project_task_id IN (SELECT pti.project_task_id FROM project_task_idx pti, project_task pt, project_group_list pgl, to_tsquery($1) AS q
			WHERE pti.project_task_id=pt.project_task_id
			AND pt.group_project_id=pgl.group_project_id
			AND pgl.group_id = $2
			AND pti.vectors @@ q ORDER BY rank(pti.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION skills_data_search(text) RETURNS SETOF skills_data_results AS '
	SELECT skills_data.skills_data_id, skills_data.type, headline(skills_data.title, q) as title,
	skills_data.start,
	skills_data.finish,
	headline(skills_data.keywords, q) as keywords
	FROM skills_data, to_tsquery($1) AS q, users, skills_data_types
	WHERE skills_data.user_id=users.user_id
	AND skills_data.type=skills_data_types.type_id
	AND skills_data.skills_data_id IN (SELECT skills_data_id FROM skills_data_idx,
	to_tsquery($1) AS q WHERE vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

CREATE OR REPLACE FUNCTION trackers_search(text, integer, text, boolean) RETURNS SETOF trackers_results AS '
	DECLARE
	data trackers_results;
	BEGIN
	-- Search in specific sections
	IF $3 <> \'\' THEN
	    -- show non public
		IF $4 THEN
			FOR data IN SELECT DISTINCT ON (artifact.artifact_id) artifact.artifact_id, artifact.group_artifact_id, headline(artifact.summary, q) AS summary,
			artifact.open_date, users.realname, artifact_group_list.name
			FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, to_tsquery($1) AS q
			WHERE users.user_id = artifact.submitted_by
			AND artifact_group_list.group_artifact_id = artifact.group_artifact_id
			AND artifact_group_list.group_id = $2
			AND artifact_group_list.group_artifact_id IN (\'$3\')
			AND artifact.artifact_id IN (SELECT a.artifact_id FROM artifact a,
			(SELECT DISTINCT ON (ai.artifact_id) ai.artifact_id, COUNT(ami.id) AS total FROM artifact_idx ai
			LEFT OUTER JOIN artifact_message_idx ami USING (artifact_id), artifact_group_list agl,
			to_tsquery($1) AS q
			WHERE ai.group_artifact_id=agl.group_artifact_id
			AND agl.group_id = $2
			AND	(ai.vectors @@ q OR ami.vectors @@ q)
			GROUP BY ai.artifact_id) AS idx, users u, to_tsquery($1) AS q, artifact_idx ai
			WHERE idx.artifact_id=a.artifact_id
			AND u.user_id=a.submitted_by
			AND a.artifact_id=ai.artifact_id
			ORDER BY idx.total DESC, rank(ai.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT DISTINCT ON (artifact.artifact_id) artifact.artifact_id, artifact.group_artifact_id, headline(artifact.summary, q) AS summary,
			artifact.open_date, users.realname, artifact_group_list.name
			FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, to_tsquery($1) AS q
			WHERE users.user_id = artifact.submitted_by
			AND artifact_group_list.group_artifact_id = artifact.group_artifact_id
			AND artifact_group_list.group_id = $2
			AND artifact_group_list.group_artifact_id IN (\'$3\')
			AND artifact_group_list.is_public = 1
			AND artifact.artifact_id IN (SELECT a.artifact_id FROM artifact a,
			(SELECT DISTINCT ON (ai.artifact_id) ai.artifact_id, COUNT(ami.id) AS total FROM artifact_idx ai
			LEFT OUTER JOIN artifact_message_idx ami USING (artifact_id), artifact_group_list agl,
			to_tsquery($1) AS q
			WHERE ai.group_artifact_id=agl.group_artifact_id
			AND agl.group_id = $2
			AND	(ai.vectors @@ q OR ami.vectors @@ q)
			GROUP BY ai.artifact_id) AS idx, users u, to_tsquery($1) AS q, artifact_idx ai
			WHERE idx.artifact_id=a.artifact_id
			AND u.user_id=a.submitted_by
			AND a.artifact_id=ai.artifact_id
			ORDER BY idx.total DESC, rank(ai.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	ELSE
	    -- show non public
		IF $4 THEN
			FOR data IN SELECT DISTINCT ON (artifact.artifact_id) artifact.artifact_id, artifact.group_artifact_id, headline(artifact.summary, q) AS summary,
			artifact.open_date, users.realname, artifact_group_list.name
			FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, to_tsquery($1) AS q
			WHERE users.user_id = artifact.submitted_by
			AND artifact_group_list.group_artifact_id = artifact.group_artifact_id
			AND artifact_group_list.group_id = $2
			AND artifact.artifact_id IN (SELECT a.artifact_id FROM artifact a,
			(SELECT DISTINCT ON (ai.artifact_id) ai.artifact_id, COUNT(ami.id) AS total FROM artifact_idx ai
			LEFT OUTER JOIN artifact_message_idx ami USING (artifact_id), artifact_group_list agl,
			to_tsquery($1) AS q
			WHERE ai.group_artifact_id=agl.group_artifact_id
			AND agl.group_id = $2
			AND	(ai.vectors @@ q OR ami.vectors @@ q)
			GROUP BY ai.artifact_id) AS idx, users u, to_tsquery($1) AS q, artifact_idx ai
			WHERE idx.artifact_id=a.artifact_id
			AND u.user_id=a.submitted_by
			AND a.artifact_id=ai.artifact_id
			ORDER BY idx.total DESC, rank(ai.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		ELSE
			FOR data IN SELECT DISTINCT ON (artifact.artifact_id) artifact.artifact_id, artifact.group_artifact_id, headline(artifact.summary, q) AS summary,
			artifact.open_date, users.realname, artifact_group_list.name
			FROM artifact LEFT OUTER JOIN artifact_message USING (artifact_id), users, artifact_group_list, to_tsquery($1) AS q
			WHERE users.user_id = artifact.submitted_by
			AND artifact_group_list.group_artifact_id = artifact.group_artifact_id
			AND artifact_group_list.group_id = $2
			AND artifact_group_list.is_public = 1
			AND artifact.artifact_id IN (SELECT a.artifact_id FROM artifact a,
			(SELECT DISTINCT ON (ai.artifact_id) ai.artifact_id, COUNT(ami.id) AS total FROM artifact_idx ai
			LEFT OUTER JOIN artifact_message_idx ami USING (artifact_id), artifact_group_list agl,
			to_tsquery($1) AS q
			WHERE ai.group_artifact_id=agl.group_artifact_id
			AND agl.group_id = $2
			AND	(ai.vectors @@ q OR ami.vectors @@ q)
			GROUP BY ai.artifact_id) AS idx, users u, to_tsquery($1) AS q, artifact_idx ai
			WHERE idx.artifact_id=a.artifact_id
			AND u.user_id=a.submitted_by
			AND a.artifact_id=ai.artifact_id
			ORDER BY idx.total DESC, rank(ai.vectors, q) DESC) LOOP
				RETURN NEXT data;
			END LOOP;
		END IF;
	END IF;
	RETURN;
	END;'
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION users_search(text) RETURNS SETOF users_results AS '
	SELECT headline(user_name, q) as user_name,
	user_id,
	headline(realname, q) as realname
	FROM users, to_tsquery($1) AS q
	WHERE status = \'A\' AND user_id IN (SELECT user_id FROM users_idx,
	to_tsquery($1) AS q WHERE vectors @@ q ORDER BY rank(vectors, q) DESC);'
LANGUAGE 'SQL';

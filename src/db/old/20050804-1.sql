ALTER TABLE doc_data ADD COLUMN filesize INTEGER;
UPDATE doc_data SET filesize=0;
ALTER TABLE doc_data ALTER COLUMN filesize SET NOT NULL;
ALTER TABLE doc_data ALTER COLUMN filesize SET DEFAULT 0;

DROP VIEW docdata_vw;

CREATE VIEW docdata_vw AS
SELECT users.user_name, users.realname, users.email, d.group_id, d.docid, d.stateid, d.title, d.updatedate, d.createdate, d.created_by, d.doc_group, d.description, d.language_id, d.filename, d.filetype,  d.filesize, doc_states.name AS state_name, doc_groups.groupname AS group_name, sl.name AS language_name
   FROM doc_data d
NATURAL JOIN doc_states
NATURAL JOIN doc_groups
   JOIN supported_languages sl ON sl.language_id = d.language_id
   JOIN users ON users.user_id = d.created_by;

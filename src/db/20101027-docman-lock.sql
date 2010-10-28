ALTER TABLE DOC_DATA ADD COLUMN reserved INT;
ALTER TABLE DOC_DATA ALTER COLUMN reserved SET DEFAULT 0;
UPDATE DOC_DATA SET reserved = 0;
ALTER TABLE DOC_DATA ADD COLUMN reserved_by INT;
ALTER TABLE DOC_DATA ADD COLUMN locked INT;
ALTER TABLE DOC_DATA ALTER COLUMN locked SET DEFAULT 0;
UPDATE DOC_DATA SET locked = 0;
ALTER TABLE DOC_DATA ADD COLUMN locked_by INT;
ALTER TABLE DOC_DATA ADD COLUMN lockdate INT;
ALTER TABLE DOC_DATA ALTER COLUMN lockdate SET DEFAULT 0;
DROP VIEW docdata_vw;
CREATE VIEW docdata_vw AS
    SELECT users.user_name, users.realname, users.email, d.group_id, d.docid, d.stateid, d.title, d.updatedate, d.createdate, d.created_by, d.doc_group, d.description, d.filename, d.filetype, d.filesize, d.reserved, d.reserved_by, d.locked, d.locked_by, d.lockdate, doc_states.name AS state_name, doc_groups.groupname AS group_name FROM doc_data d, users, doc_groups, doc_states where d.created_by = users.user_id and doc_groups.doc_group = d.doc_group and doc_states.stateid = d.stateid;

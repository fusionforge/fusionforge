ALTER TABLE DOC_DATA ADD COLUMN reserved INT;
ALTER TABLE DOC_DATA ALTER COLUMN reserved SET DEFAULT 0;
UPDATE DOC_DATA SET reserved = 0;
ALTER TABLE DOC_DATA ADD COLUMN reserved_by INT;
ALTER TABLE DOC_DATA ADD COLUMN locked INT;
ALTER TABLE DOC_DATA ALTER COLUMN locked SET DEFAULT 0;
UPDATE DOC_DATA SET locked = 0;
CREATE OR REPLACE VIEW docdata_vw AS
    SELECT users.user_name, users.realname, users.email, d.group_id, d.docid, d.stateid, d.title, d.updatedate, d.createdate, d.created_by, d.doc_group, d.description, d.language_id, d.filename, d.filetype, d.filesize, doc_states.name AS state_name, doc_groups.groupname AS group_name, sl.name AS language_name, d.locked, d.reserved, d.reserved_by FROM ((((doc_data d NATURAL JOIN doc_states) NATURAL JOIN doc_groups) JOIN supported_languages sl ON ((sl.language_id = d.language_id))) JOIN users ON ((users.user_id = d.created_by)));

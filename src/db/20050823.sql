ALTER TABLE forum_attachmenttype add column group_id integer;

UPDATE forum_attachmenttype set group_id=-1;

alter table forum_attachment drop constraint filehash;

alter table forum_attachmenttype drop constraint forum_attachmenttype_key;


ALTER TABLE ONLY forum_attachmenttype
	ADD CONSTRAINT forum_attachmenttype_key PRIMARY KEY (extension,group_id);

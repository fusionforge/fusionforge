ALTER TABLE GROUPS ADD COLUMN use_webdav INT;
ALTER TABLE GROUPS ALTER COLUMN use_webdav SET DEFAULT 1;
UPDATE GROUPS SET use_webdav = 0;

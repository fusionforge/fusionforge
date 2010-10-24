ALTER TABLE GROUPS ADD COLUMN use_docman_create_online INT;
ALTER TABLE GROUPS ALTER COLUMN use_docman_create_online SET DEFAULT 0;
UPDATE GROUPS SET use_docman_create_online = 0;

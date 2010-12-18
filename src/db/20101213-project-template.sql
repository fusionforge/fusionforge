ALTER TABLE groups ADD COLUMN is_template INTEGER DEFAULT 0 NOT NULL;
ALTER TABLE groups ADD COLUMN built_from_template INTEGER DEFAULT 0 NOT NULL;

UPDATE groups SET is_template=1 WHERE unix_group_name='template';

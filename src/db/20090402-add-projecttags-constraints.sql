ALTER TABLE project_tags ALTER COLUMN name SET DEFAULT '' ;
ALTER TABLE project_tags ADD FOREIGN KEY (group_id) REFERENCES groups (group_id) MATCH FULL;

INSERT INTO groups (group_name, is_public, status, unix_group_name, short_description, register_time) VALUES
('Template Project',1,'P','template','Project to house templates used to build other projects', extract(EPOCH FROM now()));

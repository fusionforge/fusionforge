CREATE INDEX pforole_group_idx ON pfo_role(home_group_id);
CREATE INDEX pfouserrole_rid_idx ON pfo_user_role(role_id);
CREATE INDEX roleprojrefs_group_idx ON role_project_refs(group_id);
CREATE INDEX pforolesetting_values_idx ON pfo_role_setting (section_name,ref_id);

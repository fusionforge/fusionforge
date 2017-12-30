-- extra_field_id 100 is reserved!
UPDATE artifact_extra_field_list set extra_field_id = (select max(extra_field_id)+1 from artifact_extra_field_list) where extra_field_id = 100;

INSERT INTO artifact_extra_field_list(extra_field_id, group_artifact_id, field_name, alias, description) VALUES (100, 100, 'Default', 'default', 'Default Data - Dont Edit');

ALTER TABLE artifact_group_list
   ADD auto_assign_field integer NOT NULL DEFAULT 100;

ALTER TABLE artifact_group_list
   ADD CONSTRAINT auto_assign_field_extra_field_id_fkey FOREIGN KEY (auto_assign_field)
      REFERENCES artifact_extra_field_list (extra_field_id) MATCH FULL;

DROP VIEW artifact_group_list_vw;

CREATE VIEW artifact_group_list_vw AS 
 SELECT agl.group_artifact_id, agl.group_id, agl.name, agl.description, 
    agl.email_all_updates, agl.email_address, agl.due_period, 
    agl.submit_instructions, agl.browse_instructions, agl.browse_list, 
    agl.datatype, agl.status_timeout, agl.custom_status_field, 
    agl.custom_renderer, agl.auto_assign_field, aca.count, aca.open_count
   FROM artifact_group_list agl
   LEFT JOIN artifact_counts_agg aca USING (group_artifact_id);

ALTER TABLE artifact_extra_field_elements
   ADD auto_assign_to integer NOT NULL DEFAULT 100;

ALTER TABLE artifact_extra_field_elements
   ADD CONSTRAINT artifact_extra_field_elements_user_id_fkey FOREIGN KEY (auto_assign_to)
      REFERENCES users (user_id) MATCH FULL;

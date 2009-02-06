ALTER TABLE artifact_group_selection_box_list RENAME TO artifact_extra_field_list;
ALTER TABLE artifact_extra_field_list RENAME COLUMN selection_box_name TO field_name;
ALTER TABLE artifact_extra_field_list ADD COLUMN field_type INT;
ALTER TABLE artifact_extra_field_list ALTER COLUMN field_type SET DEFAULT 1;
ALTER TABLE artifact_extra_field_list ADD COLUMN attribute1 INT;
ALTER TABLE artifact_extra_field_list ALTER COLUMN attribute1 SET DEFAULT 0;
ALTER TABLE artifact_extra_field_list ADD COLUMN attribute2 INT;
ALTER TABLE artifact_extra_field_list ALTER COLUMN attribute2 SET DEFAULT 0;
UPDATE artifact_extra_field_list SET field_type=1,attribute1=0,attribute2=0; --set all to pop-up box

ALTER TABLE artifact_group_selection_box_options RENAME TO artifact_extra_field_elements;
ALTER TABLE artifact_extra_field_elements RENAME COLUMN box_options_name TO element_name;

ALTER TABLE artifact_extra_field_data ADD COLUMN field_data text;
UPDATE artifact_extra_field_data SET field_data=choice_id;
ALTER TABLE artifact_extra_field_data DROP COLUMN choice_id;
ALTER TABLE artifact_extra_field_data ADD COLUMN extra_field_id int;
ALTER TABLE artifact_extra_field_data ALTER COLUMN extra_field_id SET DEFAULT 0;
update artifact_extra_field_data SET extra_field_id=0;

ALTER TABLE artifact_extra_field_list RENAME COLUMN id TO extra_field_id;
ALTER TABLE artifact_extra_field_elements RENAME COLUMN artifact_box_id TO extra_field_id;
ALTER TABLE artifact_extra_field_elements RENAME COLUMN id TO element_id;
ALTER TABLE artifact_extra_field_data RENAME COLUMN id TO data_id;

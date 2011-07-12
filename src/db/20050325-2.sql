ALTER TABLE artifact_extra_field_list ADD COLUMN is_required INT;
UPDATE artifact_extra_field_list SET is_required=0;
ALTER TABLE artifact_extra_field_list ALTER COLUMN is_required SET NOT NULL;
ALTER TABLE artifact_extra_field_list ALTER COLUMN is_required SET DEFAULT 0;

ALTER TABLE artifact_extra_field_elements ADD COLUMN status_id INT;
UPDATE artifact_extra_field_elements SET status_id=0;
ALTER TABLE artifact_extra_field_elements ALTER COLUMN status_id SET NOT NULL;
ALTER TABLE artifact_extra_field_elements ALTER COLUMN status_id SET DEFAULT 0;

ALTER TABLE artifact_group_list ADD COLUMN custom_status_field INT;
UPDATE artifact_group_list SET custom_status_field=0;
ALTER TABLE artifact_group_list ALTER COLUMN custom_status_field SET NOT NULL;
ALTER TABLE artifact_group_list ALTER COLUMN custom_status_field SET DEFAULT 0;

ALTER TABLE artifact_group_list ADD COLUMN custom_renderer TEXT;

CREATE TABLE artifact_query (
	artifact_query_id SERIAL NOT NULL,
	group_artifact_id integer NOT NULL
		CONSTRAINT artquery_groupartid_fk REFERENCES artifact_group_list(group_artifact_id) ON DELETE CASCADE,
	user_id integer NOT NULL,
	query_name text NOT NULL,
	Constraint artifact_query_pkey Primary Key (artifact_query_id)
);

CREATE TABLE artifact_query_fields (
	artifact_query_id integer NOT NULL
		CONSTRAINT artqueryelmnt_artqueryid REFERENCES artifact_query(artifact_query_id) ON DELETE CASCADE,
	query_field_type text NOT NULL,
	query_field_id int NOT NULL,
	query_field_values text NOT NULL,
	Constraint artifact_query_elements_pkey Primary Key (artifact_query_id,query_field_type,query_field_id)
);

ALTER TABLE doc_groups ADD COLUMN parent_doc_group INTEGER;
UPDATE doc_groups SET parent_doc_group=0;
ALTER TABLE doc_groups ALTER COLUMN parent_doc_group SET NOT NULL;
ALTER TABLE doc_groups ALTER COLUMN parent_doc_group SET DEFAULT 0;
CREATE INDEX docgroups_parentdocgroup ON doc_groups(parent_doc_group);

-- Table: artifact_workflow_required_fields
CREATE TABLE artifact_workflow_required_fields
(
  event_id integer NOT NULL,
  extra_field_id integer NOT NULL,
  CONSTRAINT artifact_workflow_required_fields_pkey PRIMARY KEY (event_id, extra_field_id),
  CONSTRAINT artifact_workflow_required_fields_event_id_fkey FOREIGN KEY (event_id)
      REFERENCES artifact_workflow_event (event_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
);
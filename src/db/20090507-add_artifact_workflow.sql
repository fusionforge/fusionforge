-- DEBUG code, drop before
-- DROP SEQUENCE artifact_workflow_event_id_seq;
-- DROP INDEX artifact_workflow_event_index;
-- DROP TABLE artifact_workflow_event CASCADE;
-- DROP TABLE artifact_workflow_roles CASCADE;
-- DROP TABLE artifact_workflow_notify CASCADE;
-- ALTER TABLE artifact_extra_field_list DROP CONSTRAINT artifact_extra_field_list_unique;

ALTER TABLE artifact_extra_field_list ADD CONSTRAINT artifact_extra_field_list_unique UNIQUE (group_artifact_id, extra_field_id);

-- Table: artifact_workflow_event

CREATE SEQUENCE artifact_workflow_event_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 2147483647
  START 1
  CACHE 1;
-- ALTER TABLE artifact_workflow_event_id_seq OWNER TO gforge;

CREATE TABLE artifact_workflow_event
(
  event_id integer NOT NULL DEFAULT nextval('"artifact_workflow_event_id_seq"'::text),
  group_artifact_id integer NOT NULL,
  field_id integer NOT NULL,
  from_value_id integer NOT NULL,
  to_value_id integer NOT NULL,
  CONSTRAINT artifact_workflow_event_pkey PRIMARY KEY (event_id),
  CONSTRAINT artifact_workflow_event_group_artifact_id_fkey FOREIGN KEY (group_artifact_id, field_id)
	REFERENCES artifact_extra_field_list (group_artifact_id, extra_field_id) MATCH SIMPLE
	ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH OIDS;
-- ALTER TABLE artifact_workflow_event OWNER TO gforge;

-- Index: artifact_workflow_event_index

CREATE INDEX artifact_workflow_event_index
  ON artifact_workflow_event
  USING btree
  (event_id, group_artifact_id, field_id);



-- Table: artifact_workflow_roles

CREATE TABLE artifact_workflow_roles
(
  event_id integer NOT NULL,
  role_id integer NOT NULL,
  CONSTRAINT artifact_workflow_roles_pkey PRIMARY KEY (event_id, role_id),
  CONSTRAINT artifact_workflow_roles_event_id_fkey FOREIGN KEY (event_id)
      REFERENCES artifact_workflow_event (event_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH OIDS;
-- ALTER TABLE artifact_workflow_roles OWNER TO gforge;


-- Table: artifact_workflow_notify

CREATE TABLE artifact_workflow_notify
(
  event_id integer NOT NULL,
  role_id integer NOT NULL,
  CONSTRAINT artifact_workflow_notify_pkey PRIMARY KEY (event_id, role_id),
  CONSTRAINT artifact_workflow_notify_event_id_fkey FOREIGN KEY (event_id)
      REFERENCES artifact_workflow_event (event_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
)
WITH OIDS;
-- ALTER TABLE artifact_workflow_notify OWNER TO gforge;


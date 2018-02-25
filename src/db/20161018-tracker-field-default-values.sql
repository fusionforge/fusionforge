CREATE TABLE artifact_extra_field_default (
    default_id serial NOT NULL,
    extra_field_id integer NOT NULL,
    default_value text NOT NULL,
    CONSTRAINT artifact_extra_field_default_pkey PRIMARY KEY (default_id),
    CONSTRAINT artifact_extra_field_default_extra_field_id_fkey FOREIGN KEY (extra_field_id)
      REFERENCES artifact_extra_field_list (extra_field_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
);
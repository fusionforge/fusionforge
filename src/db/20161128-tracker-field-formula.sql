CREATE TABLE artifact_extra_field_formula (
  formula_id serial NOT NULL,
  extra_field_id integer NOT NULL,
  id integer DEFAULT NULL,
  formula text NOT NULL,
  CONSTRAINT artifact_extra_field_formula_pkey PRIMARY KEY (formula_id),
  CONSTRAINT artifact_extra_field_formula_extra_field_id_fkey FOREIGN KEY (extra_field_id)
      REFERENCES artifact_extra_field_list (extra_field_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE CASCADE
);

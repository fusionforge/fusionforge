
CREATE SEQUENCE roadmap_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 2147483647
  START 1
  CACHE 1;

CREATE TABLE roadmap
(
  roadmap_id integer NOT NULL DEFAULT nextval('roadmap_id_seq'::regclass),
  "name" text,
  group_id integer NOT NULL,
  "enable" integer,
  release_order text,
  is_default integer,
  CONSTRAINT roadmap_pkey PRIMARY KEY (roadmap_id)
)
WITH OIDS;

CREATE TABLE roadmap_list
(
  roadmap_id integer NOT NULL,
  artifact_type_id integer NOT NULL,
  field_id integer NOT NULL,
  CONSTRAINT roadmap_id FOREIGN KEY (roadmap_id)
      REFERENCES roadmap (roadmap_id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH OIDS;



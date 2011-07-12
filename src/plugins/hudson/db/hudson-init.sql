CREATE SEQUENCE plugin_hudson_job_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;
CREATE TABLE plugin_hudson_job (
  job_id INTEGER DEFAULT nextval('plugin_hudson_job_pk_seq'::text) NOT NULL,
  group_id INTEGER NOT NULL ,
  job_url character varying(255) NOT NULL ,
  name character varying(128) NOT NULL ,
  use_svn_trigger INTEGER NOT NULL default 0 ,
  use_cvs_trigger INTEGER NOT NULL default 0 ,
  token character varying(128) NOT NULL,
  PRIMARY KEY(job_id)
);

CREATE SEQUENCE plugin_hudson_widget_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;
CREATE TABLE plugin_hudson_widget (
  id INTEGER DEFAULT nextval('plugin_hudson_widget_pk_seq'::text) NOT NULL,
  widget_name character varying(64) NOT NULL ,
  owner_id INTEGER NOT NULL ,
  owner_type character varying(1) NOT NULL ,
  job_id INTEGER NOT NULL,
  PRIMARY KEY(id)
);


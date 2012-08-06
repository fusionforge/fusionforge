CREATE SEQUENCE docdata_monitored_docman_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE TABLE docdata_monitored_docman (
    monitor_id integer DEFAULT nextval('docdata_monitored_docman_pk_seq'::text) NOT NULL,
    doc_id integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL
);

CREATE SEQUENCE docgroup_monitored_docman_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE TABLE docgroup_monitored_docman (
    monitor_id integer DEFAULT nextval('docgroup_monitored_docman_pk_seq'::text) NOT NULL,
    docgroup_id integer DEFAULT 0 NOT NULL,
    user_id integer DEFAULT 0 NOT NULL
);


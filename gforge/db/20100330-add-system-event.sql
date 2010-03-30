CREATE SEQUENCE system_event_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;



CREATE TABLE system_event (
    id integer DEFAULT nextval('system_event_pk_seq'::text) NOT NULL,
    type character varying(16),
    parameters text,
    priority integer DEFAULT 0 NOT NULL,
    status integer DEFAULT 1 NOT NULL,
    log text,
    create_date integer DEFAULT 0,
    process_date integer DEFAULT 0,
    end_date integer DEFAULT 0
);

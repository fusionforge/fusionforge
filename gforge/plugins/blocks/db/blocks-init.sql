CREATE SEQUENCE plugin_blocks_pk_seq
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;

CREATE TABLE plugin_blocks (
	id integer DEFAULT nextval('plugin_blocks_pk_seq'::text) NOT NULL,
	group_id integer,
	name text,
	content text,
	status integer
) ;

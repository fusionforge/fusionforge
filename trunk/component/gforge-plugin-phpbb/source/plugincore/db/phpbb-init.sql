CREATE SEQUENCE plugin_phpbb_instance_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_phpbb_instance(
    plugin_phpbb_instance_id integer DEFAULT nextval('plugin_phpbb_instance_pk_seq'::text) UNIQUE NOT NULL,
    gforge_group_id integer DEFAULT 0 NOT NULL,
    phpbb_category_id integer DEFAULT 0 NOT NULL,
    name  character varying(150),
    url text,
    encoding character varying(150)
);

CREATE SEQUENCE plugin_phpbb_role_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_phpbb_role(
    plugin_phpbb_role_id integer DEFAULT nextval('plugin_phpbb_role_pk_seq'::text) UNIQUE NOT NULL,
    plugin_phpbb_instance_id integer REFERENCES plugin_phpbb_instance(plugin_phpbb_instance_id) ON DELETE CASCADE,
    gforge_role_id integer DEFAULT 0 NOT NULL,
    phpbb_role_id integer DEFAULT 0 NOT NULL
    
);

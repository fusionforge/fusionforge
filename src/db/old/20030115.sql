CREATE SEQUENCE plugins_pk_seq;
CREATE TABLE plugins (plugin_id integer DEFAULT nextval('plugins_pk_seq'::text) NOT NULL,
	plugin_name varchar(32) UNIQUE NOT NULL,
	plugin_desc text,
	CONSTRAINT plugins_pkey PRIMARY KEY (plugin_id));

CREATE SEQUENCE group_plugin_pk_seq;
CREATE TABLE group_plugin (group_plugin_id integer DEFAULT nextval('group_plugin_pk_seq'::text) NOT NULL,
	group_id integer,
	plugin_id integer,
	CONSTRAINT group_plugin_pkey PRIMARY KEY (group_plugin_id),
	CONSTRAINT group_plugin_group_id_fk FOREIGN KEY (group_id) REFERENCES groups(group_id) MATCH FULL,
	CONSTRAINT group_plugin_plugin_id_fk FOREIGN KEY (plugin_id) REFERENCES plugins(plugin_id) MATCH FULL);

CREATE SEQUENCE user_plugin_pk_seq;
CREATE TABLE user_plugin (user_plugin_id integer DEFAULT nextval('user_plugin_pk_seq'::text) NOT NULL,
	user_id integer,
	plugin_id integer,
	CONSTRAINT user_plugin_pkey PRIMARY KEY (user_plugin_id),
	CONSTRAINT user_plugin_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) MATCH FULL,
	CONSTRAINT user_plugin_plugin_id_fk FOREIGN KEY (plugin_id) REFERENCES plugins(plugin_id) MATCH FULL);

CREATE TABLE plugin_headermenu (
	id_headermenu	serial PRIMARY KEY,
	url		character varying(255),
	name		character varying(255),
	description	character varying(511),
	is_enable	integer DEFAULT 0
);

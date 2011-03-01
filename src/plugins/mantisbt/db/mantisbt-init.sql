CREATE TABLE plugin_mantisbt (
	id_group_mantisbt	serial PRIMARY KEY,
	id_group		integer NOT NULL,
	id_mantisbt		integer,
	url			character varying(255),
	soap_user		character varying(255),
	soap_password		character varying(255),
	sync_roles		integer DEFAULT 0
);

CREATE TABLE plugin_mantisbt_users (
	id_user_mantisbt	serial PRIMARY KEY,
	id_user			integer NOT NULL,
	mantisbt_user		character varying(255),
	mantisbt_password	character varying(255)
);
CREATE TABLE plugin_scmhook (
	id		serial PRIMARY KEY,
	id_group	integer NOT NULL,
	need_update	integer DEFAULT 0,
	hooks		text
);
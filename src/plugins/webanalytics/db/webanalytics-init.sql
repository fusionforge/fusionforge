CREATE TABLE plugin_webanalytics (
	id_webanalytics	serial PRIMARY KEY,
	url		text,
	name		character varying(255),
	is_enable	integer DEFAULT 0,
	code        text
);

CREATE TABLE plugin_extratabs_main (
	group_id int NOT NULL,
	index int NOT NULL,
	tab_name text NOT NULL,
	tab_url text NOT NULL,
	PRIMARY KEY(group_id, index)
) ;

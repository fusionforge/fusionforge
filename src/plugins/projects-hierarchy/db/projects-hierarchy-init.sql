CREATE TABLE plugin_projects_hierarchy (
	project_id integer DEFAULT 0 NOT NULL,
	tree boolean DEFAULT true,
	docman boolean DEFAULT false,
	delegate boolean DEFAULT false,
	globalconf boolean DEFAULT false
);

CREATE TABLE plugin_projects_hierarchy_global (
	tree boolean DEFAULT true,
	docman boolean DEFAULT false,
	delegate boolean DEFAULT false
);

CREATE TABLE plugin_projects_hierarchy_relationship (
	project_id integer DEFAULT 0 NOT NULL,
	sub_project_id integer DEFAULT 0 NOT NULL,
	status boolean DEFAULT false
);

INSERT INTO plugin_projects_hierarchy_global (tree) values (true);
